<?php
$grid_fields = array(
    "ID_fp_quarto",
    "codice",
    "descrizione",
    "fp_terzo"
);

$grid_recordset = array();
foreach (CoanFpQuarto::getAll() as $item) {
    $fp = new CoanFpTerzo($item->id_fp_terzo);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $fp->codice." - ".$fp->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fp-quarto";
$oGrid->title = "Fp quarto livello";
$oGrid->resources[] = "fp-quarto";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "coan_fp_quarto"
);
$oGrid->order_default = "ID_fp_quarto";
$oGrid->record_id = "fp-quarto-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp_quarto";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_quarto";
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
$oField->id = "fp_terzo";
$oField->base_type = "Text";
$oField->label = "Fp Terzo";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_fp_quarto"]->value->getValue();
    $item = new CoanFpQuarto($id);
    $oGrid->display_delete_bt = $item->canDelete();
}