<?php
$user = LoggedUser::Instance();
$anno = $cm->oPage->globals["anno"]["value"];
$anno = new ValutazioniAnnoBudget($anno->id);

if (isset($_REQUEST["tipo_cdr"])) {
    $tipo_cdr_list = $_REQUEST["tipo_cdr"];
}
else {
    $tipo_cdr_list[0] = -1;
}

$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR ."tpl");
$tpl->load_file("punteggi_valutazione.html", "main");
$tpl->set_var("module_theme_path", $modulo->module_theme_dir . DIRECTORY_SEPARATOR);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

$tipo_cdr_select = array();
foreach (TipoCdr::getAll() as $tipo_cdr) {
    $descrizione = $tipo_cdr->abbreviazione." - ".$tipo_cdr->descrizione;
        
    $tpl->set_var("filter_tipo_cdr_id", $tipo_cdr->id);
    $tpl->set_var("filter_tipo_cdr_value", $descrizione);
    $tpl->parse("SectFiltroTipoCdr", true);
}

$categorie = $anno->getCategorieAnno();
$qualifiche_select = ValutazioniCategoria::getQualifiche($categorie);
foreach($qualifiche_select as $qualifica) {
    $tpl->set_var("filter_valutazione_qualifica_id", $qualifica["id_categorie"]);
    $tpl->set_var("filter_valutazione_qualifica_value", $qualifica["descrizione"]);
    $tpl->parse("SectValutazioneQualificaOption", true);
}

$id_categorie_arr = array();
if(isset($_REQUEST["valutazione_qualifica"])) {
    $id_categorie_arr = explode("_", $_REQUEST["valutazione_qualifica"]);
}  else {
    //Casistica prima apertura: viene considerato il primo valore del menu a tendina
    $id_categorie_arr = explode("_", $qualifiche_select[0]["id_categorie"]);
}

foreach($categorie as $categoria) {
    if(in_array($categoria->id, $id_categorie_arr)) {
        $tpl->set_var("filter_valutazione_categoria_id", $categoria->id);
        $tpl->set_var("filter_valutazione_categoria_value", $categoria->descrizione);
        $tpl->parse("SectValutazioneCategoriaOption", true);
    }
}
if(!empty($_REQUEST["valutazione_categoria"])) {
    $id_categorie_arr = array(intval($_REQUEST["valutazione_categoria"]));
}

$categoria = new ValutazioniCategoria($id_categorie_arr[0]);
$descrizione_tipologia_scheda = $categoria->dirigenza ? "Dirigenza" : $categoria->descrizione;

foreach (ValutazioniSezione::getAll() as $sezione) {
    $descrizione = "Area $sezione->codice - $sezione->descrizione";
    
    $tpl->set_var("filter_area_id", $sezione->id);
    $tpl->set_var("filter_area_value", $descrizione);
    $tpl->parse("SectAreaOption", true);
}

if (isset($_REQUEST["area"])) {
    $area = $_REQUEST["area"];

    try {
        $sezione = new ValutazioniSezione($area);
        $descrizione_filtro_area = "Area ".$sezione->codice." - ".$sezione->descrizione;
    }
    catch (Exception $e) {
        // Caso in cui è stato selezionato "Tutte le aree" che ha option_value=-1
        $descrizione_filtro_area = "Tutte le aree";    
    }
}
else {
    // Come valore di default viene impostato il -1, che significa "Tutte le aree"
    $area = -1;
    $descrizione_filtro_area = "Tutte le aree";
}

// Recupero il periodo di riferimento
$periodo = ValutazioniPeriodo::getAll(
    array(
        "ID_anno_budget" => $anno->id, 
        "data_fine" => $anno->descrizione."-12-31"
    )
);


if(isset($_REQUEST["valutazione_qualifica"])) {
    $id_categorie_arr_totale = explode("_", $_REQUEST["valutazione_qualifica"]);
    if(!empty($_REQUEST["valutazione_categoria"])) {
        $id_categorie_arr_totale = [$_REQUEST["valutazione_categoria"]];
    }
} else {
    $id_categorie_arr_totale = explode("_", $qualifiche_select[0]["id_categorie"]);
}
$valutazioni_totali = $anno->getTotaliAnno();
if(count($valutazioni_totali) > 1) {
    foreach($valutazioni_totali as $valutazioni_totale) {
        $categorie_totale = $valutazioni_totale->getCategorieTotale();

        //Viene verificato che almeno una delle categorie selezionate siaassociata al totale, se nessuna lo è, il totale non è da visualizzare fra le opzioni
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
    die($tpl->rpparse("main", true));
}

if (empty($periodo)) {
    $tpl->set_var("msg_no_periodo", "Periodo finale NON definito per l'anno di budget $anno->descrizione");
    $tpl->parse("NoPeriodo", true);
}
else {
    $periodo = $periodo[0];
    $date = $periodo->data_fine;

    $tpl->set_var("descrizione_filtro_area", $descrizione_filtro_area);
    $tpl->set_var("descrizione_tipologia_scheda", $descrizione_tipologia_scheda);
    $tpl->parse("TableReportHead", true);

    $ambiti_precalcolati = ValutazioniValutazionePeriodica::getAmbitiPrecalcolatiPunteggiValutazione(
        $anno->id, $periodo->id, $id_categorie_arr, $area
    );

    foreach($ambiti_precalcolati as $ambito_precalcolato) {
        try {
           $totale_ambito = ValutazioniTotaleAmbito::factoryFromTotaleAmbito($valutazioni_totale->id, $ambito_precalcolato->id_ambito);
           $ambito = new ValutazioniAmbito($ambito_precalcolato->id_ambito);
        } catch(Exception $ex) {
            //Ambito non associato al totale => si esce dal ciclo
            continue;
        }

        $sezione = new ValutazioniSezione($ambito_precalcolato->id_sezione);
        $categoria = new ValutazioniCategoria($ambito_precalcolato->id_categoria);
        //Ambito non valutato => si evita di effettuare i calcoli
        if($totale_ambito && $ambito->isValutatoCategoriaAnno($categoria, $anno)) {
            $valutato = Personale::factoryFromMatricola($ambito_precalcolato->matricola_valutato);
            $cdr_afferenza = $valutato->getCdrAfferenzaInData($cm->oPage->globals["tipo_piano_cdr"]["value"], $date);

            //TODO può essere ottimizzato il codice, viene replicato del codice già usato nel costruttore di ValutazioniPersonale
            if (count ($cdr_afferenza) == 0) {
                $piano_cdr = PianoCdr::getAttivoInData($cm->oPage->globals["tipo_piano_cdr"]["value"], $date);
                $ultimi_cdr_afferenza = $valutato->getCdrUltimaAfferenza($cm->oPage->globals["tipo_piano_cdr"]["value"]);
                if (count ($ultimi_cdr_afferenza) > 0) {
                    foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                        $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                        if ($cdr_attuale->id == $cdr_aff["cdr"]->id) {
                            $cdr_afferenza[] = $cdr_aff;
                        }
                    }
                }
            }

            foreach($cdr_afferenza as $key => $item) {
                $cdr = $item["cdr"];
                try {
                    $peso_sezione = $sezione->getPesoAnno($anno, $categoria);
                } catch (Exception $ex) {
                    $peso_sezione = null;
                }
                if ((in_array($cdr->id_tipo_cdr, $tipo_cdr_list) || $tipo_cdr_list[0] == -1) && isset($peso_sezione)) {
                    if (!array_key_exists($ambito_precalcolato->id_valutazione, $totale)) {
                        $totale[$ambito_precalcolato->id_valutazione] = array();
                    }

                    if (!array_key_exists($sezione->id, $totale[$ambito_precalcolato->id_valutazione])) {
                        $totale[$ambito_precalcolato->id_valutazione][$sezione->id] = new stdClass();
                    }

                    $totale[$ambito_precalcolato->id_valutazione][$sezione->id]->raggiungimento_sezione += $ambito_precalcolato->valore;

                    $totale[$ambito_precalcolato->id_valutazione][$sezione->id]->peso_sezione = $peso_sezione;
                    $totale[$ambito_precalcolato->id_valutazione][$sezione->id]->totale_ambiti += $ambito_precalcolato->peso;
                }
            }
        }
    }
    unset($sezione);
    unset($categoria);
    unset($valutato);
    unset($cdr_afferenza);
    $plot_value = array();
    $numero_totale_valutazioni = count($totale);
    foreach ($totale as $valutazione => $sezioni) {
        $sezioni_sum = 0;
        foreach ($sezioni as $id_sezione => $obj) {
            $sezioni[$id_sezione]->totale_sezione = round($obj->raggiungimento_sezione * $obj->peso_sezione / $obj->totale_ambiti, 2);
            $sezioni_sum += intval(round($sezioni[$id_sezione]->totale_sezione));
        }
        $totale[$valutazione] = $sezioni;

        if (!array_key_exists($sezioni_sum, $plot_value)) {
            $plot_value[$sezioni_sum] = new stdClass();
            $plot_value[$sezioni_sum]->numero_valutati = 0;
            $plot_value[$sezioni_sum]->valore = $sezioni_sum;
            $plot_value[$sezioni_sum]->percentuale = 0;
        }
        $plot_value[$sezioni_sum]->numero_valutati += 1;        
    }
    unset($totale);

    if (count($plot_value) > 0) {
        // Viene ordinato con direction DESC l'array per key
        krsort($plot_value);

        $totale_numero_valutati = 0;
        $totale_percentuale = 0;
        foreach($plot_value as $value) {
            $value->percentuale = ($value->numero_valutati * 100 / $numero_totale_valutazioni);
            $totale_numero_valutati += $value->numero_valutati;
            $totale_percentuale += $value->percentuale;

            $tpl->set_var("table_report_valutazione", $value->valore);
            $tpl->set_var("table_report_numero_valutati", $value->numero_valutati);
            $tpl->set_var("table_report_percentuale", number_format($value->percentuale, 2, ",", "."));
            $tpl->set_var("point_x", "{point.x}");
            $tpl->set_var("point_y", "{point.y}");

            $tpl->parse("TableReportBody", true);
        }
        $tpl->set_var("table_report_totale_numero_valutati", $totale_numero_valutati);
        $tpl->set_var("table_report_totale_percentuale", round($totale_percentuale, 2));
        $tpl->parse("TotaleTableReportBody", true);
        $tpl->parse("DataReport", true);
    }
    else {
        $tpl->parse("MsgReport", true);
    }
}

$tpl->parse("Script", true);

die($tpl->rpparse("main", true));
