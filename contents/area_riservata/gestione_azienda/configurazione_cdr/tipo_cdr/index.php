<?php
$grid_fields = array(
    "ID",
    "abbreviazione",
    "descrizione",    
    "padri",
);

$grid_recordset = array();
foreach (TipoCdr::getAll() as $tipo_cdr) {
    $grid_recordset[] = array(
        $tipo_cdr->id,
        $tipo_cdr->abbreviazione,
        $tipo_cdr->descrizione,
        $tipo_cdr->padri,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipi-cdr";
$oGrid->title = "Tipologie cdr";
$oGrid->resources[] = "tipo-cdr";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "tipo_cdr");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "tipo-cdr-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "modify";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
$oGrid->addEvent("on_before_parse_row", "getRelations");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "padri";
$oField->base_type = "Text";
$oField->label = "Tipi cdr padre";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function getRelations($oGrid){
	$tipo_cdr = new TipoCdr($oGrid->key_fields["ID"]->value->getValue());
	$tipi_cdr_padre = "";
	foreach ($tipo_cdr->getPadri() as $tipo_cdr_padre){
		$tipi_cdr_padre .= $tipo_cdr_padre->descrizione . "\n";  
	}	   	
	$oGrid->grid_fields["padri"]->setValue($tipi_cdr_padre);	
    
    $tipo_cdr = new TipoCdr($oGrid->key_fields["ID"]->value->getValue());	
    $oGrid->display_delete_bt = $tipo_cdr->isDeletable();
}