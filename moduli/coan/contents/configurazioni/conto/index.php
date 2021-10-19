<?php
$grid_fields = array(
    "ID_conto",
    "codice",
    "descrizione",
    "fp_quarto"
);

$grid_recordset = array();
foreach (CoanConto::getAll() as $item) {
    $fp = new CoanFpQuarto($item->id_fp_quarto);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $fp->codice." - ".$fp->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "conto";
$oGrid->title = "Conto";
$oGrid->resources[] = "conto";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "coan_conto"
);
$oGrid->order_default = "codice";
$oGrid->record_id = "conto-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_conto";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_conto";
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
$oField->id = "fp_quarto";
$oField->base_type = "Text";
$oField->label = "Fp Quarto";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_conto"]->value->getValue();
    $item = new CoanConto($id);
    $oGrid->display_delete_bt = $item->canDelete();
}