<?php
$grid_fields = array(
    "ID_fp",
    "codice",
    "descrizione"
);

$grid_recordset = array();
foreach (CostiRicaviFp::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fp";
$oGrid->title = "Fattori Produttivi";
$oGrid->resources[] = "fp";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "costi_ricavi_fp"
);
$oGrid->order_default = "ID_fp";
$oGrid->record_id = "fp-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_fp"]->value->getValue();
    $item = new CostiRicaviFp($id);
    $oGrid->display_delete_bt = $item->canDelete();
}