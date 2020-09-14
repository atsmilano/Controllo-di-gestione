<?php
$grid_fields = array(
    "ID_periodo_cruscotto",
    "descrizione",
    "data_riferimento_inizio",
    "data_riferimento_fine",
    "ordinamento_anno",
    "anno_budget"
);

$grid_recordset = array();
foreach (IndicatoriPeriodoCruscotto::getAll() as $item) {
    $anno_budget = new AnnoBudget($item->id_anno_budget);   
    
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->data_riferimento_inizio,
        $item->data_riferimento_fine,
        $item->ordinamento_anno,
        $anno_budget->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodo-cruscotto";
$oGrid->title = "Periodo cruscotto";
$oGrid->resources[] = "periodo-cruscotto";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_periodo_cruscotto");
$oGrid->order_default = "ordinamento_anno";
$oGrid->record_id = "periodo-cruscotto-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$record_url = FF_SITE_PATH . $path_info . "config_periodo_cruscotto_modify";
$oGrid->record_url = $record_url;
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_cruscotto";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_budget";
$oField->base_type = "Number";
$oField->label = "Anno";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);