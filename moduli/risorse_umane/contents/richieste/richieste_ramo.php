<?php
$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
//viene recuperato il cdr dal piano di priorità massima definito
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $piano_cdr)->cloneAttributesToNewObject("CdrRU");

$grid_ramo_recordset = array();
foreach ($cdr->getRichiesteCompetenzaRamoCdrAnno($anno) as $richiesta) {
    $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr);
    $is_approvazione_competenza = $richiesta->isApprovazioneCompetenza($cdr, $anno, $data_riferimento, $piano_cdr);
        
    $qualifica = new QualificaInterna($richiesta->id_qualifica_interna);
    $ruolo = new Ruolo($qualifica->id_ruolo);
    $tipologia = new RUTipoRichiesta($richiesta->id_tipo_richiesta);
    $tipo_cdr = new TipoCdr($cdr_creazione->id_tipo_cdr);
    $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();
    $stati_avanzamento = RURichiesta::getStatiAvanzamento();
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column($stati_avanzamento, 'ID'));        
    $stato_avanzamento = $stati_avanzamento[$index_stato_avanzamento];
    if ($stato_avanzamento["esito"] !== "ko") {
        $record = array(
            $richiesta->id,
            $cdr_creazione->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_creazione->descrizione,
            $ruolo->descrizione,
            $qualifica->descrizione,
            $richiesta->qta,
            $tipologia->descrizione,
            $id_stato_avanzamento,
        );

        $grid_ramo_recordset[] = $record;              
    }    
}

$oGrid = RURichiesta::getGridRichieste("richieste-ramo-cdr", "Richieste di competenza", $grid_ramo_recordset, true);
$oGrid->display_new = false;    
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$cm->oPage->addContent($oGrid);