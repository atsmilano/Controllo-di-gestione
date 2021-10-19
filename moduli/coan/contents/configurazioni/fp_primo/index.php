<?php
$grid_fields = array(
    "ID_fp_primo",
    "codice",
    "descrizione"
);

$grid_recordset = array();
foreach (CoanFpPrimo::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fp-primo";
$oGrid->title = "Fp primo livello";
$oGrid->resources[] = "fp-primo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "coan_fp_primo"
);
$oGrid->order_default = "ID_fp_primo";
$oGrid->record_id = "fp-primo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp_primo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_primo";
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
    $id = $oGrid->key_fields["ID_fp_primo"]->value->getValue();
    $item = new CoanFpPrimo($id);
    $oGrid->display_delete_bt = $item->canDelete();
}