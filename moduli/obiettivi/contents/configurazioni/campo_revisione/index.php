<?php
$grid_fields = array(
    "ID", 
    "nome", 
);

$grid_recordset = array();
foreach (ObiettiviCampoRevisione::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, 
        $item->nome,        
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "campo_revisione";
$oGrid->title = "Campo revisione";
$oGrid->resources[] = "campo-revisione";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "obiettivi_campo_revisione"
);
$oGrid->order_default = "nome";
$oGrid->record_id = "campo-revisione-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_campo_revisione";
$oGrid->order_method = "labels";
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->full_ajax = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkObiettiviCampoRevisione");
$cm->oPage->addContent($oGrid);

function checkObiettiviCampoRevisione($oGrid) {    
    $campo_revisione = new ObiettiviCampoRevisione($oGrid->key_fields["ID"]->value->getValue());
    $oGrid->display_delete_bt = $campo_revisione->canDelete();    
}