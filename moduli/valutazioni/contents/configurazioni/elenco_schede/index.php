<?php
CoreHelper::includeJqueryUi();
$cm->oPage->widgetLoad("dialog");

$tmp = $cm->oPage->widgets["dialog"]->process(
    "delete_valutazione_action_dialog", // id del dialog
    array(
        "name" => "delete_valutazione_action_dialog",
        "title" => "",
        "padre" => "",
        "url" => "",
        "callback" => "$('#periodo_schede_select').change()",
    ),
    $cm->oPage
);

//viene forzato il caricamento del css del framework (problemi di caricamento automatico in alcune occasioni)
$cm->oPage->tplAddCss("ff.css", array("file" => "ff.css", "path" => FF_THEME_DIR . "/responsive/css"));
$cm->oPage->tplAddCss("ff-skin.css", array("file" => "ff-skin.css", "path" => FF_THEME_DIR . "/responsive/css"));

$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("elenco_schede_valutazione.html", "main");
$globals = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
$tpl->set_var("globals", $globals);

//estrazione dei periodi per l'anno
$anno_selezionato = $cm->oPage->globals["anno"]["value"];
$anno_valutazione = new ValutazioniAnnoBudget($anno_selezionato->id);
$periodi_valutazione = $anno_valutazione->getPeriodiAnno();
$tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();

$periodo_schede_select = null;
if(isset($_SERVER['HTTP_REFERER'])) {
    $get_params = parse_url($_SERVER['HTTP_REFERER'])["query"];
    parse_str($get_params,$parsed_get_params);
    if(isset($parsed_get_params["periodo"])) {
        $periodo_schede_select = $parsed_get_params["periodo"];
    }
}

$tpl->set_var("periodo_schede_select", $periodo_schede_select);

//se è stato definito almeno un periodo per l'anno
if (count($periodi_valutazione) > 0) {
    /* ---------------------------------------------------------------------------- */
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_schede_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_schede_select"];
    } elseif($periodo_schede_select) {
        $periodo_selezionato = $periodo_schede_select;
    } else {
        $periodo_selezionato = $periodi_valutazione[0]->id;
    }
    $tpl->set_var("id_periodo", $periodo_selezionato);
    $periodo = false;
    for ($i = 0; $i < count($periodi_valutazione); $i++) {
        if ($periodi_valutazione[$i]->id == $periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = $periodo_selezionato;
        } else
            $tpl->set_var("periodo_selected", "");
        $tpl->set_var("periodo_id", $periodi_valutazione[$i]->id);
        $tpl->set_var("periodo_descrizione", $periodi_valutazione[$i]->descrizione);

        $tpl->parse("SectOptionPeriodi", true);
    }
    if ($periodo == false) {
        $periodo = $periodi_valutazione[0]->id;
    }    
    unset($periodi_valutazione);
    $tpl->parse("SectSelezionePeriodi", true);
    /* ---------------------------------------------------------------------------- */

    //Schede di valutazione attive
    $periodo_valutazione = new ValutazioniPeriodo($periodo);
    
    $valutazioni_attive = $periodo_valutazione->getValutazioniAttivePeriodo();
    if (count($valutazioni_attive)>0){
        $tpl->set_var("n_schede_attive", count($valutazioni_attive));
        foreach($valutazioni_attive as $valutazione){      
            $valutato = Personale::factoryFromMatricola($valutazione->matricola_valutato);                                    
            $valutatore = Personale::factoryFromMatricola($valutazione->matricola_valutatore);                                      
            $tpl->set_var("valutato", $valutato->cognome." ".$valutato->nome." (".$valutato->matricola.")");
            $tpl->set_var("valutatore", $valutatore->cognome." ".$valutatore->nome." (".$valutatore->matricola.")");
            $categoria = $valutazione->categoria;
            $tpl->set_var("categoria_valutazione", $categoria->descrizione);            
            //cdr afferenza              
            //se l'utente è cessato alla data fine (non ha cdr d'afferenza) si verifica che 
            //eventualmente l'ultimo cdr di assegnazione sia previsto dal piano attuale (per gestione coerenza storico)
            $cdr_afferenza = $valutato->getCdrAfferenzaInData($tipo_piano_cdr, $periodo_valutazione->data_fine);   
            $cdr_commento = "";
            if (count ($cdr_afferenza) == 0) {
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo_valutazione->data_fine);
                $ultimi_cdr_afferenza = $valutato->getCdrUltimaAfferenza($tipo_piano_cdr);
                if (count ($ultimi_cdr_afferenza) > 0) {
                    $cdr_commento = " (ultima afferenza - dipendente cessato nell'anno)";
                    foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                        try {
                            $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                            if ($cdr_attuale->id == $cdr_aff["cdr"]->id) {
                                $cdr_afferenza[] = $cdr_aff;
                            }
                        } catch (Exception $ex) {

                        }
                    }
                }
            }
            if (count($cdr_afferenza) == 0){
                $anomalia = true;
            }
            foreach ($cdr_afferenza as $cdr_aff) {    
                $tipo_cdr = new TipoCdr($cdr_aff["cdr"]->id_tipo_cdr);
                $tpl->set_var("cdr", $cdr_aff["cdr"]->codice." - ". $tipo_cdr->abbreviazione . " " . $cdr_aff["cdr"]->descrizione . $cdr_commento);
                $tpl->set_var("perc_testa", $cdr_aff["peso_cdr"]);
                $tpl->parse("SectCdrAssociati", true);                
            }
                                
            //estrazione descrizione stato_avanzamento
            $found = false;
            $stato_avanzamento = $valutazione->getIdStatoAvanzamento();
            for($j=0; $j<count(ValutazioniValutazionePeriodica::$stati_valutazione); $j++){
                if (ValutazioniValutazionePeriodica::$stati_valutazione[$j]["ID"] == $stato_avanzamento){
                    $found = $j;
                    $j = count(ValutazioniValutazionePeriodica::$stati_valutazione);
                }
            }
            if($found !== false){
                $tpl->set_var("stato_avanzamento", ValutazioniValutazionePeriodica::$stati_valutazione[$found]["descrizione"]);
            }
            unset ($found);
            
            $path = explode("/", $cm->path_info);
            array_pop($path);
            $path_tabs = implode("/", $path);
            array_pop($path);
            $path_valutazioni = implode("/", $path);

            $tabs_url = FF_SITE_PATH . $path_tabs;
            $valutazioni_url = FF_SITE_PATH . $path_valutazioni . "/valutazione_modify";
            $stampa_valutazioni_url = FF_SITE_PATH . $path_valutazioni . "/stampa_valutazione";

            $valutazioni_params = "?"
                . $globals
                . "keys[ID_valutazione]=".$valutazione->id;

            $tpl->set_var('id_scheda', $valutazione->id);

            $link_valutazione = $valutazioni_url
                . $valutazioni_params
                . "&ret_url=".rawurlencode($tabs_url."?".$globals."gotab=4&periodo=".$periodo_selezionato);

            $link_stampa = $stampa_valutazioni_url
                . $valutazioni_params
                . "&ret_url=".rawurlencode($tabs_url."?".$globals."gotab=4&periodo=".$periodo_selezionato);
            
            $tpl->set_var("link_valutazione", $link_valutazione);
            $tpl->set_var("link_stampa", $link_stampa);

            $link_eliminazione = $valutazioni_url
                . $valutazioni_params
                . "&frmAction=valutazione-modify_delete";

            $tpl->set_var("url_delete", ($link_eliminazione));
            $tpl->parse("SectSchedaValutazione", true);
            $tpl->set_var("SectCdrAssociati", "");
        }
        $tpl->parse("SectValutazioniAttive", true);
        unset($valutazioni_attive);
        unset($valutazione);
        unset($categoria);
        unset($valutatore);
        unset($valutato);
        unset($stato_avanzamento);
    }
    else {
        $tpl->parse("SectNoValutazioniAttive", true);
    }
    /* ---------------------------------------------------------------------------- */     
}
//se non ci sono periodi per l'anno viene visualizzata una notifica
else {
    $tpl->parse("SectNoPeriodi", true);
}
$cm->oPage->addContent($tpl);