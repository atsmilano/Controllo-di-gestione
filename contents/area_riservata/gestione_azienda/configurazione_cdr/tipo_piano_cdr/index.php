<?php
$grid_fields = array(
    "ID",
    "descrizione",    
    "priorita",
);

$grid_recordset = array();
foreach (TipoPianoCdr::getAll() as $tipo_piano_cdr) {
    $grid_recordset[] = array(
        $tipo_piano_cdr->id,
        $tipo_piano_cdr->descrizione,
        $tipo_piano_cdr->priorita,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipi-piano-cdr";
$oGrid->title = "Tipologie piano cdr";
$oGrid->resources[] = "tipo-piano-cdr";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "tipo_piano_cdr");
$oGrid->order_default = "priorita";
$oGrid->record_id = "tipo-piano-cdr-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "modify";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "priorita";
$oField->base_type = "Text";
$oField->label = "Priorità";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid){
	$tipo_piano_cdr = new TipoPianoCdr($oGrid->key_fields["ID"]->value->getValue());	
    $oGrid->display_delete_bt = $tipo_piano_cdr->canDelete();
}