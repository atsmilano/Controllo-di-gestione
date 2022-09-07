<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei profili per il CdR.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");

$grid_fields = array(
    "ID", 
    "descrizione",
);
$grid_recordset = array();
foreach ($cdr->getProfiliResponsabile($user->matricola_utente_selezionato) as $profilo) {
    $grid_recordset[] = array(
        $profilo->id,
        $profilo->descrizione,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "profili-mappatura";
$oGrid->title = "Profili";
$oGrid->resources[] = "profilo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "competenze_profilo"
);
$oGrid->order_default = "descrizione";
$oGrid->record_id = "profilo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_profilo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_profilo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

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