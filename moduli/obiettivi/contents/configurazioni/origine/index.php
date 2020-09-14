<?php
$grid_fields = array(
    "ID_origine", "descrizione", 
    "anno_introduzione", "anno_termine"
);

$grid_recordset = array();
foreach (ObiettiviOrigine::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->anno_introduzione, $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "origine";
$oGrid->title = "Origine";
$oGrid->resources[] = "origine";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "obiettivi_origine"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "origine-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_origine";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_origine";
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

$oGrid->addEvent("on_before_parse_row", "checkObiettiviOrigineEliminabile");
$cm->oPage->addContent($oGrid);

function checkObiettiviOrigineEliminabile($oGrid) {
    $id_origine = $oGrid->key_fields["ID_origine"]->value->getValue();
    $origine = new ObiettiviOrigine($id_origine);
    $oGrid->display_delete_bt = $origine->canDelete();
}