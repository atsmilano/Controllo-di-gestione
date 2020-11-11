<?php
$grid_fields = array(
    "ID_anno",
    "anno_budget",
    "data_chiusura_definizione_strategia",
);

$grid_recordset = array();
foreach (StrategiaAnno::getAll() as $item) {
    $anno_budget = new AnnoBudget($item->id_anno_budget);
    
    $grid_recordset[] = array(
        $item->id,
        $anno_budget->descrizione,
        $item->data_chiusura_definizione_strategia
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "strategia-anno";
$oGrid->title = "Data chiusura";
$oGrid->resources[] = "anno";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "strategia_anno");
$oGrid->order_default = "anno_budget";
$oGrid->record_id = "anno-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_data_chiusura";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
//$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_budget";
$oField->base_type = "Text";
$oField->label = "Anno di budget";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_chiusura_definizione_strategia";
$oField->base_type = "Date";
$oField->label = "Data chiusura definizione strategia";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);