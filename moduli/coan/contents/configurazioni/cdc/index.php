<?php
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID_cdc",
    "codice",
    "descrizione",
    "cdc_standard_regionale",
    "codice_cdr",
    "distretto",
    "anno_introduzione",
    "anno_termine"
);

$grid_recordset = array();
foreach (CoanCdc::getAll() as $item) {
    $cdc_standard_regionale = new CoanCdcStandardRegionale($item->id_cdc_standard_regionale);
    $distretto = new CoanDistretto($item->id_distretto);
    $cdr = AnagraficaCdr::factoryFromCodice($item->codice_cdr, $date);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $cdc_standard_regionale->codice." - ".$cdc_standard_regionale->descrizione,
        $item->codice_cdr ." - ".$cdr->descrizione,
        $distretto->codice." - ".$distretto->descrizione,
        $item->anno_introduzione,
        $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "cdc";
$oGrid->title = "Cdc";
$oGrid->resources[] = "cdc";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "coan_cdc"
);
$oGrid->order_default = "codice";
$oGrid->record_id = "cdc-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_cdc";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdc";
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

$oField = ffField::factory($cm->oPage);
$oField->id = "cdc_standard_regionale";
$oField->base_type = "Text";
$oField->label = "Cdc standard regionale";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "distretto";
$oField->base_type = "Text";
$oField->label = "Distretto";
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

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_cdc"]->value->getValue();
    $item = new CoanCdc($id);
    $oGrid->display_delete_bt = $item->canDelete();
}