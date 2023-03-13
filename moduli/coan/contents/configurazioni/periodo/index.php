<?php
$grid_fields = array(
    "ID_periodo",
    "descrizione",
    "anno_budget",
    "ordinamento_anno",
    "data_inizio",
    "data_fine"
);

$grid_recordset = array();
foreach (CoanPeriodo::getAll() as $item) {
    $anno_budget = new AnnoBudget($item->id_anno_budget);
    
    $grid_recordset[] = array(
        $item->id,
        $item->descrizione,
        $anno_budget->descrizione,
        $item->ordinamento_anno,
        $item->data_inizio,
        $item->data_fine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodo";
$oGrid->title = "Periodi";
$oGrid->resources[] = "periodo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, "coan_periodo"
);
$oGrid->order_default = "anno_budget";
$oGrid->record_id = "periodo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_periodo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_budget";
$oField->base_type = "Text";
$oField->label = "Anno Budget";
$oField->order_SQL = "anno_budget ASC, ordinamento_anno ASC";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->label = "Data fine";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_periodo"]->value->getValue();
    $item = new CoanPeriodo($id);
    $oGrid->display_delete_bt = $item->isDeletable();
}