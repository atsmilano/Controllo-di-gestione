<?php
CoreHelper::includeJqueryUi();
$cm->oPage->widgetLoad("dialog");
$tmp2 = $cm->oPage->widgets["dialog"]->process(
    "open_detail_matricola_dialog" // id del dialog
    , array( // proprietà del dialog
        "name" => "open_detail_matricola_dialog"
        , "title" => ""
        , "url" => ""
        , "callback" => "$('#periodo_non_valutati_select').change()",
    )
    , $cm->oPage // oggetto pagina associato
);

ini_set("max_execution_time", VALUTAZIONI_MAX_EXECUTION_TIME);

$modulo = core\Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("elenco_non_valutati.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_dir . DIRECTORY_SEPARATOR);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//estrazione dei periodi per l'anno
$anno_selezionato = $cm->oPage->globals["anno"]["value"];
$anno_valutazione = new ValutazioniAnnoBudget($anno_selezionato->id);
$periodi_valutazione = $anno_valutazione->getPeriodiAnno();
$tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();

//se è stato definito almeno un periodo per l'anno
if (count($periodi_valutazione) > 0) {
    /* ---------------------------------------------------------------------------- */
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_non_valutati_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_non_valutati_select"];
    } else {
        $periodo_selezionato = $periodi_valutazione[0]->id;
    }
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
    unset($periodo_selezionato);
    unset($periodi_valutazione);
    $tpl->parse("SectSelezionePeriodi", true);
    /* ---------------------------------------------------------------------------- */

    /* ---------------------------------------------------------------------------- */
    //Schede di valutazione attive
    $periodo_valutazione = new ValutazioniPeriodo($periodo);

    /* ---------------------------------------------------------------------------- */
    //Personale non valutato
    $personale_non_valutato = $periodo_valutazione->getNonValutatiPeriodo();
    if (count($personale_non_valutato) > 0) {
        $tpl->set_var("n_non_valutati", count($personale_non_valutato));
        foreach ($personale_non_valutato as $dipendente) {
            $anomalia = false;

            $dipendente = new ValutazioniPersonale($dipendente->id, $periodo_valutazione);
            $tpl->set_var("dipendente", $dipendente->cognome . " " . $dipendente->nome . " (" . $dipendente->matricola . ")");
            foreach ($dipendente->cdr_afferenza as $dipendente->cdr_afferenza) {
                $tipo_cdr = new TipoCdr($dipendente->cdr_afferenza["cdr"]->id_tipo_cdr);
                $tpl->set_var("cdr", $dipendente->cdr_afferenza["cdr"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $dipendente->cdr_afferenza["cdr"]->descrizione . $cdr_commento);
                $tpl->set_var("perc_testa", $dipendente->cdr_afferenza["peso_cdr"]);
                $tpl->parse("SectCdrAssociati", true);
            }
            $tipo_cdr = new TipoCdr($dipendente->cdr_riferimento->id_tipo_cdr);
            $tpl->set_var("cdr_riferimento", $dipendente->cdr_riferimento->codice . " - " . $tipo_cdr->abbreviazione . " " . $dipendente->cdr_riferimento->descrizione);
            $tpl->set_var("valutatore_suggerito", $dipendente->valutatore_suggerito->cognome . " " . $dipendente->valutatore_suggerito->nome . " (" . $dipendente->valutatore_suggerito->matricola_responsabile . ")");
            $tpl->set_var("categoria_dipendente", $dipendente->categoria->descrizione);
            $tpl->set_var("anomalie", $dipendente->anomalie);

            //assegnazione classe css in caso di anomalia
            if ($anomalia == true || strlen($dipendente->anomalie)) {
                $tpl->set_var("personale_class", "error");
            } else {
                $tpl->set_var("personale_class", "");
            }
            
            $tpl->set_var("matricola", $dipendente->matricola);

            $tpl->parse("SectDettagliPersonaleNonValutato", true);
            $tpl->set_var("SectCdrAssociati", "");
        }
        $tpl->parse("SectPersonaleNonValutato", true);

        unset($categoria);
        unset($dipendente);
    } else {
        $tpl->parse("SectNoPersonaleNonValutato", true);
    }
    /* ---------------------------------------------------------------------------- */
}
//se non ci sono periodi per l'anno viene visualizzata una notifica
else {
    $tpl->parse("SectNoPeriodi", true);
}
$cm->oPage->addContent($tpl);