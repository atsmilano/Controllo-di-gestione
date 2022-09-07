<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei valori.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID", 
    "descrizione",
    "valore", 
    "data_introduzione",
    "data_termine",
);
$grid_recordset = array();
foreach (MappaturaCompetenze\Valore::getAll() as $valore_atteso) {
    $grid_recordset[] = array(
        $valore_atteso->id,
        $valore_atteso->descrizione,
        $valore_atteso->valore,
        $valore_atteso->data_introduzione,
        $valore_atteso->data_termine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "valori";
$oGrid->title = "Valori";
$oGrid->resources[] = "valore";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "competenze_valore"
);
$oGrid->order_default = "valore";
$oGrid->record_id = "valore-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_valore";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_valore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore";
$oField->base_type = "Number";
$oField->label = "Valore";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data termine";
$oGrid->addContent($oField);

//$oGrid->addEvent("on_before_parse_row", "checkObiettiviAreaEliminabile");
$cm->oPage->addContent($oGrid);
/*
function checkObiettiviAreaEliminabile($oGrid) {
    $id_area = $oGrid->key_fields["ID_area"]->value->getValue();
    $area = new ObiettiviArea($id_area);
    $oGrid->display_delete_bt = $area->canDelete();
}*/