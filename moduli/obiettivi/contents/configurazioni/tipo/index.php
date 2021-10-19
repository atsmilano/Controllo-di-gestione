<?php
$grid_fields = array(
    "ID_tipo", "descrizione", 
    "anno_introduzione", "anno_termine",
    "class"
);

$grid_recordset = array();
foreach (ObiettiviTipo::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->anno_introduzione, $item->anno_termine,
        strlen($item->class)? "Si" : "No"
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipo";
$oGrid->title = "Tipo";
$oGrid->resources[] = "tipo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "obiettivi_tipo"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "tipo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_tipo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";
$oField->label = "Anno termine";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "class";
$oField->base_type = "Text";
$oField->label = "Obiettivo in evidenza";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkObiettiviTipoEliminabile");
$cm->oPage->addContent($oGrid);

function checkObiettiviTipoEliminabile($oGrid) {
    $id_tipo = $oGrid->key_fields["ID_tipo"]->value->getValue();
    $tipo = new ObiettiviTipo($id_tipo);
    $oGrid->display_delete_bt = $tipo->canDelete();
}