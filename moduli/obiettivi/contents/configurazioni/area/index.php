<?php
$grid_fields = array(
    "ID_area", "descrizione", 
    "anno_introduzione", "anno_termine"
);

$grid_recordset = array();
foreach (ObiettiviArea::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->anno_introduzione, $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "area";
$oGrid->title = "Area";
$oGrid->resources[] = "area";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "obiettivi_area"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "area-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_area";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";
$oField->label = "Anno termine";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkObiettiviAreaEliminabile");
$cm->oPage->addContent($oGrid);

function checkObiettiviAreaEliminabile($oGrid) {
    $id_area = $oGrid->key_fields["ID_area"]->value->getValue();
    $area = new ObiettiviArea($id_area);
    $oGrid->display_delete_bt = $area->canDelete();
}