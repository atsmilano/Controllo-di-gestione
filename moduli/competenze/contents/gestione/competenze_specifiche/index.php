<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze specifiche per il CdR.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");

$grid_fields = array(
    "ID", 
    "nome",
    "descrizione",
);
$grid_recordset = array();
foreach ($cdr->getCompetenzeSpecificheResponsabileInData($user->matricola_utente_selezionato, $date) as $competenza_specifica) {
    $grid_recordset[] = array(
        $competenza_specifica->id,
        $competenza_specifica->nome,
        $competenza_specifica->descrizione,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "competenze-specifiche";
$oGrid->title = "Competenze specifiche";
$oGrid->resources[] = "competenza-specifica";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "competenze_competenza_specifica"
);
$oGrid->order_default = "descrizione";
$oGrid->record_id = "competenza-specifica-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_competenza_specifica";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
//verifica privilegi utente
if ($user->hasPrivilege("competenze_admin")) {
    $oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
}


$oField = ffField::factory($cm->oPage);
$oField->id = "ID_competenza_specifica";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

//$oGrid->addEvent("on_before_parse_row", "checkObiettiviAreaEliminabile");
$cm->oPage->addContent($oGrid);
/*
function checkObiettiviAreaEliminabile($oGrid) {
    $id_area = $oGrid->key_fields["ID_area"]->value->getValue();
    $area = new ObiettiviArea($id_area);
    $oGrid->display_delete_bt = $area->canDelete();
}*/