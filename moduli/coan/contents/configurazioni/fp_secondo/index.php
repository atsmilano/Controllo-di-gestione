<?php
$grid_fields = array(
    "ID_fp_secondo",
    "codice",
    "descrizione",
    "fp_primo"
);

$grid_recordset = array();
foreach (CoanFpSecondo::getAll() as $item) {
    $fp = new CoanFpPrimo($item->id_fp_primo);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $fp->codice." - ".$fp->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fp-secondo";
$oGrid->title = "Fp secondo livello";
$oGrid->resources[] = "fp-secondo";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "coan_fp_secondo"
);
$oGrid->order_default = "ID_fp_secondo";
$oGrid->record_id = "fp-secondo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp_secondo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_secondo";
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
$oField->id = "fp_primo";
$oField->base_type = "Text";
$oField->label = "Fp Secondo";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_fp_secondo"]->value->getValue();
    $item = new CoanFpSecondo($id);
    $oGrid->display_delete_bt = $item->canDelete();
}