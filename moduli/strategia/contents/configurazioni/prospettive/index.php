<?php
$grid_fields = array(
    "ID_prospettiva",
    "nome",
    "descrizione",
    "anno_introduzione",
    "anno_termine"
);

$grid_recordset = array();
foreach (StrategiaProspettiva::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->nome,
        CoreHelper::stripTagsUTF8Encode($item->descrizione),
        $item->anno_introduzione,
        $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "strategia-prospettiva";
$oGrid->title = "Prospettive strategiche";
$oGrid->resources[] = "prospettiva";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "strategia_prospettiva");
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "prospettiva-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_prospettiva";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_prospettiva";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);
	
$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";		
$oField->label = "Anno Introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";		
$oField->label = "Anno Termine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);