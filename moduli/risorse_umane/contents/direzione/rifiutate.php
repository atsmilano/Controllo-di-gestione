<?php
$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
//viene recuperato il cdr dal piano di priorità massima definito
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $piano_cdr)->cloneAttributesToNewObject("CdrRU");

$grid_direzione_rifiutate = array();
foreach (RURichiesta::getAll(array("ID_anno_budget"=>$anno->id)) as $richiesta) {
    $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr);
    $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();
    $stati_avanzamento = RURichiesta::getStatiAvanzamento();
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column($stati_avanzamento, 'ID'));        
    $stato_avanzamento = $stati_avanzamento[$index_stato_avanzamento];
    
    if ($id_stato_avanzamento >= 11) {       
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
        
        $grid_direzione_rifiutate[] = $record;            
    }
}

$oGrid = RURichiesta::getGridRichieste("richieste-rifiutate", "Richieste di competenza rifiutate", $grid_direzione_rifiutate, true);
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$cm->oPage->addContent($oGrid);