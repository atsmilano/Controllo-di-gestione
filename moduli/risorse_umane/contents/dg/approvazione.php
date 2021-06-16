<?php
//librerie per tooltip
CoreHelper::includeJqueryUi();

$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
//viene recuperato il cdr dal piano di priorità massima definito
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $piano_cdr)->cloneAttributesToNewObject("CdrRU");

$grid_uo_competente_da_approvare_recordset = array();
$grid_uo_competente_competenza_recordset = array();
$tr_title_js = "";
foreach (RURichiesta::getAll(array("ID_anno_budget"=>$anno->id)) as $richiesta) {
    $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr)->cloneAttributesToNewObject("CdrRU");
    $ultima_accettazione = $richiesta->getUltimaAccettazioneData($data_riferimento);
    $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();
    $stati_avanzamento = RURichiesta::getStatiAvanzamento();
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column($stati_avanzamento, 'ID'));        
    $stato_avanzamento = $stati_avanzamento[$index_stato_avanzamento];
    
    if ($id_stato_avanzamento >= 5 && $stato_avanzamento["esito"] !== "ko") {       
        $qualifica = new QualificaInterna($richiesta->id_qualifica_interna);
        $ruolo = new Ruolo($qualifica->id_ruolo);
        $tipologia = new RUTipoRichiesta($richiesta->id_tipo_richiesta);

        $record = array(
            $richiesta->id,
            $cdr_creazione->codice." - ".$cdr_creazione->descrizione,
            $ruolo->descrizione,
            $qualifica->descrizione,
            $richiesta->qta,
            $tipologia->descrizione,
            $id_stato_avanzamento,
        );
        
        $note_accettazione = ($ultima_accettazione!==null && $ultima_accettazione->data_accettazione!==null)?CoreHelper::formatUiDate($ultima_accettazione->data_accettazione, "Y-m-d H:i:s") . " - " . $ultima_accettazione->note:"Nessuna accettazione";
        $tr_title_js .= "<script>$('.richiesta_".$richiesta->id."').prop('title', '".$note_accettazione."');</script>";

        if ($richiesta->isApprovazioneCompetenza($cdr, $anno, $data_riferimento, $piano_cdr)){
            $grid_uo_competente_da_approvare_recordset[] = $record;
        }
        else {
            $grid_uo_competente_competenza_recordset[] = $record;
        }            
    }
}

$oGrid = RURichiesta::getGridRichieste("richieste-approvazione", "Richieste da approvare", $grid_uo_competente_da_approvare_recordset);
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->addEvent("on_before_parse_row", "initGrid");
$cm->oPage->addContent($oGrid);

$oGrid = RURichiesta::getGridRichieste("richieste-competenza", "Richieste approvate", $grid_uo_competente_competenza_recordset);
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->addEvent("on_before_parse_row", "initGrid");
$cm->oPage->addContent($oGrid);

function initGrid ($oGrid) {    
    $oGrid->row_class = "richiesta_". $oGrid->key_fields["ID"]->value->getValue();
}

//javascript per gestione tooltip
$cm->oPage->addContent("<script>$(document).tooltip();</script>");
$cm->oPage->addContent($tr_title_js);