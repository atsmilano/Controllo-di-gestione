<?php
use scadenze\Tipologia;

$grid_fields = array(
    "ID",
    "descrizione",
    "data_riferimento_inizio",
    "data_riferimento_fine",
);

$grid_recordset = array();
foreach (Tipologia::getAll() as $tipologia) {
    $grid_recordset[] = array(
        $tipologia->id,
        $tipologia->descrizione,
        $tipologia->data_riferimento_inizio,
        $tipologia->data_riferimento_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipologia";
$oGrid->title = "Tipologie";
$oGrid->resources[] = "tipologia";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "scadenze_tipologia"
);
$oGrid->order_default = "descrizione";
$oGrid->record_id = "tipologia-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID"]->value->getValue();
    $tipologia = new Tipologia($id);
    $oGrid->display_delete_bt = $tipologia->isDeletable();
}