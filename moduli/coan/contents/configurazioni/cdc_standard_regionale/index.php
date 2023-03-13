<?php

$grid_fields = array(
    "ID_cdc_standard_regionale",
    "codice",
    "descrizione"
);

$grid_recordset = array();
foreach (CoanCdcStandardRegionale::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "cdc-standard-regionale";
$oGrid->title = "Cdc standard regionale";
$oGrid->resources[] = "cdc-standard-regionale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
                $grid_fields, $grid_recordset, "coan_cdc_standard_regionale"
);
$oGrid->order_default = "codice";
$oGrid->record_id = "cdc-standard-regionale-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_cdc_standard_regionale";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#' . $oGrid->id . '").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdc_standard_regionale";
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
    $id = $oGrid->key_fields["ID_cdc_standard_regionale"]->value->getValue();
    $item = new CoanCdcStandardRegionale($id);
    $oGrid->display_delete_bt = $item->isDeletable();
}
