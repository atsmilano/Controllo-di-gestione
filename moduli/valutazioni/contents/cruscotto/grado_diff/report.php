<?php
$user = LoggedUser::getInstance();

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$valutazioni_anno_budget = new ValutazioniAnnoBudget($anno->id);
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

//viene caricato il template specifico per la pagina
$modulo = core\Modulo::getCurrentModule();

$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("grado_differenziazione.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_dir . DIRECTORY_SEPARATOR);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("anno", $anno->descrizione);

$valutazioneAnnoBudget = new ValutazioniAnnoBudget($anno->id);
$categorie = $valutazioneAnnoBudget->getCategorieAnno();
$qualifiche_select = ValutazioniCategoria::getQualifiche($categorie);

foreach($qualifiche_select as $qualifica) {
    $tpl->set_var("filter_valutazione_qualifica_gd_id", $qualifica["id_categorie"]);
    $tpl->set_var("filter_valutazione_qualifica_gd_value", $qualifica["descrizione"]);
    $tpl->parse("SectValutazioneQualificaOption", true);
}

$id_categorie_arr = array();
if(isset($_REQUEST["qualifica"])) {
    $id_categorie_arr = explode("_", $_REQUEST["qualifica"]);
} else { //Casistica prima apertura: si considera il primo valore del menu a tendina
    $id_categorie_arr = explode("_", $qualifiche_select[0]["id_categorie"]);
}

foreach($categorie as $categoria) {
    if(in_array($categoria->id, $id_categorie_arr)) {
        $tpl->set_var("filter_valutazione_categoria_gd_id", $categoria->id);
        $tpl->set_var("filter_valutazione_categoria_gd_value", $categoria->descrizione);
        $tpl->parse("SectValutazioneCategoriaOption", true);
    }
}

//Filtro totale
if(isset($_REQUEST["qualifica"])) {
    $id_categorie_arr_totale = explode("_", $_REQUEST["qualifica"]);
    if(!empty($_REQUEST["categoria"])) {
        $id_categorie_arr_totale = [$_REQUEST["categoria"]];
    }
} else {
    $id_categorie_arr_totale = explode("_", $qualifiche_select[0]["id_categorie"]);
}
$valutazioni_totali = $valutazioni_anno_budget->getTotaliAnno();

if(count($valutazioni_totali) > 1) {
    foreach($valutazioni_totali as $valutazioni_totale) {
        $categorie_totale = $valutazioni_totale->getCategorieTotale();
        //Verifica se almeno una delle categorie selezionate è associata al totale, se nessuna lo è, il totale non risulta selezionabile nelle opzioni
        $totale_is_valid_option = false;
        foreach($id_categorie_arr_totale as $id_categoria_arr_totale) {
            if(isset($categorie_totale[$id_categoria_arr_totale])) {
                $totale_is_valid_option = true;
            }
        }
        if($totale_is_valid_option) {
            $tpl->set_var("filter_totale_id", $valutazioni_totale->id);
            $tpl->set_var("filter_totale_value", $valutazioni_totale->descrizione);
            $tpl->parse("SectTotaliOption", true);
        }
    }
    $tpl->parse("SectTotali", false);

    if(isset($_REQUEST["totale"])) {
        $valutazioni_totale = new ValutazioniTotale($_REQUEST["totale"]);
    } else {
        $valutazioni_totale = $valutazioni_totali[0];
    }
} else {
    $valutazioni_totale = $valutazioni_totali[0];
}

if(isset($_REQUEST["die"])) {
    $tpl->parse("GradoDifferenziazione", true);
    die($tpl->rpparse("main", true));
}

if(!empty($_REQUEST["categoria"])) {
    $id_categorie_arr = array(intval($_REQUEST["categoria"]));
}

$abilita_conteggio_anno = true;
$totali_precalcolati = ValutazioniValutazionePeriodica::getTotaliPrecalcolatiCategoriaAnno($valutazioneAnnoBudget, $id_categorie_arr, $valutazioni_totale->id);
$totale_totali_precalcolati = count($totali_precalcolati);
$conteggio_altre_fasce = $totale_totali_precalcolati;
$fasce = ValutazioniFasciaPunteggio::getFasceInData($data_riferimento);
$totale_fasce = 0;
foreach($fasce as $fascia) {
    $conteggio_fascia = 0;

    $data_fine = isset($fascia->data_fine)
        ? DateTime::createFromFormat("Y-m-d", $fascia->data_fine)
        : null;


    $tpl->set_var("fascia", $fascia->min . "-" . $fascia->max);

    foreach($totali_precalcolati as $totale_precalcolato) {
        if(round($totale_precalcolato->valore) >= $fascia->min && round($totale_precalcolato->valore) <= $fascia->max) {
            $conteggio_fascia++;
            $conteggio_altre_fasce--;
        }
    }

    $perc_fascia = number_format(($conteggio_fascia*100)/$totale_totali_precalcolati, 2, ",", ".");

    $tpl->set_var("perc_fascia", $perc_fascia."%");
    $tpl->set_var("conteggio_fascia", $conteggio_fascia);
    $tpl->set_var("colore", $fascia->colore);
    $tpl->parse("FascePunteggi", true);

    $totale_fasce += $conteggio_fascia;
}

$totale_fasce += $conteggio_altre_fasce;
$totale_fasce_perc = number_format(($totale_fasce*100)/$totale_totali_precalcolati, 2, ",", ".");
$perc_altro = number_format(($conteggio_altre_fasce*100)/$totale_totali_precalcolati, 2, ",", ".");

$tpl->set_var("totale_fasce", $totale_fasce);
$tpl->set_var("totale_fasce_perc", $totale_fasce_perc."%");
$tpl->set_var("conteggio_altro", $conteggio_altre_fasce);
$tpl->set_var("perc_altro", $perc_altro."%");
$tpl->set_var("chart_totale_fascia", "{point.y}");
$tpl->set_var("chart_perc_fascia", "{point.percentage:.2f}%");

if($totale_totali_precalcolati == 0) {
    $tpl->set_var("error_msg", " Nessuna valutazione finale per l'anno di budget per i filtri impostati.");
    $tpl->parse("ErrorMsg", true);
} elseif(count($fasce) == 0) {
    $tpl->set_var("error_msg", "Non sono presenti fasce attive per la data di riferimento.");
    $tpl->parse("ErrorMsg", true);
} elseif($conteggio_altre_fasce == $totale_totali_precalcolati) {
    $tpl->set_var("error_msg", "Nessuna valutazione finale per le fasce definite e/o per i filtri impostati.");
    $tpl->parse("ErrorMsg", true);
} else {
    $tpl->parse("ReportGradoDifferenziazione", true);
}

$tpl->parse("GradoDifferenziazione", true);
$tpl->parse("ScriptGd", true);

$html = $tpl->rpparse("main", true);
die($html);