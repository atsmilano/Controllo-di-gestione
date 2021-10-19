<?php
$grid_fields = array(
    "ID_descrizione_introduttiva",
    "descrizione",
    "anno_introduzione",
);

$grid_recordset = array();
foreach (StrategiaDescrizioneIntroduttiva::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        CoreHelper::stripTagsUTF8Encode($item->descrizione),
        $item->anno_introduzione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "strategia-desc-intro";
$oGrid->title = "Descrizione Introduttiva";
$oGrid->resources[] = "desc-intro";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, "strategia_descrizione_introduttiva"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "desc-intro-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_desc_intro";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_descrizione_introduttiva";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione introduttiva";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);