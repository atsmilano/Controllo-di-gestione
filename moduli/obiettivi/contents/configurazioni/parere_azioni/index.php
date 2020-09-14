<?php
$grid_fields = array(
    "ID_parere_azioni", "descrizione", 
    "anno_introduzione", "anno_termine"
);

$grid_recordset = array();
foreach (ObiettiviParereAzioni::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->anno_introduzione, $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "parere-azioni";
$oGrid->title = "Parere azioni";
$oGrid->resources[] = "parere-azioni";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "obiettivi_parere_azioni"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "parere-azioni-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_parere_azioni";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_parere_azioni";
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

$oGrid->addEvent("on_before_parse_row", "checkObiettiviParereAzioniEliminabile");
$cm->oPage->addContent($oGrid);

function checkObiettiviParereAzioniEliminabile($oGrid) {
    $id_parere_azioni = $oGrid->key_fields["ID_parere_azioni"]->value->getValue();
    $parere_azioni = new ObiettiviParereAzioni($id_parere_azioni);
    $oGrid->display_delete_bt = $parere_azioni->canDelete();
}