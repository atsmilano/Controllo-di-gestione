<?php

$grid_fields = array(
    "ID_distretto",
    "codice",
    "descrizione"
);

$grid_recordset = array();
foreach (CoanDistretto::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "distretto";
$oGrid->title = "Distretto";
$oGrid->resources[] = "distretto";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
                $grid_fields, $grid_recordset,
                "coan_distretto"
);
$oGrid->order_default = "codice";
$oGrid->record_id = "distretto-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_distretto";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#' . $oGrid->id . '").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_distretto";
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

function checkRelations($oGrid)
{
    $id = $oGrid->key_fields["ID_distretto"]->value->getValue();
    $item = new CoanDistretto($id);
    $oGrid->display_delete_bt = $item->isDeletable();
}
