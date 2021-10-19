<?php
$grid_fields = array(
    "ID_fp_terzo",
    "codice",
    "descrizione",
    "fp_secondo"
);

$grid_recordset = array();
foreach (CoanFpTerzo::getAll() as $item) {
    $fp = new CoanFpSecondo($item->id_fp_secondo);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $fp->codice." - ".$fp->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fp-terzo";
$oGrid->title = "Fp terzo livello";
$oGrid->resources[] = "fp-terzo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "coan_fp_terzo"
);
$oGrid->order_default = "ID_fp_terzo";
$oGrid->record_id = "fp-terzo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp_terzo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_terzo";
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
$oField->id = "fp_secondo";
$oField->base_type = "Text";
$oField->label = "Fp Secondo";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_fp_terzo"]->value->getValue();
    $item = new CoanFpTerzo($id);
    $oGrid->display_delete_bt = $item->canDelete();
}