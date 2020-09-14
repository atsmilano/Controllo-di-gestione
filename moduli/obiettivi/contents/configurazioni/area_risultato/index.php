<?php
$grid_fields = array(
    "ID_area_risultato", "descrizione", 
    "anno_introduzione", "anno_termine"
);

$grid_recordset = array();
foreach (ObiettiviAreaRisultato::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id, $item->descrizione,
        $item->anno_introduzione, $item->anno_termine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "area-risultato";
$oGrid->title = "Area risultato";
$oGrid->resources[] = "area-risultato";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "obiettivi_area_risultato"
);
$oGrid->order_default = "anno_introduzione";
$oGrid->record_id = "area-risultato-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_area_risultato";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_risultato";
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

$oGrid->addEvent("on_before_parse_row", "checkObiettiviAreaRisultatoEliminabile");
$cm->oPage->addContent($oGrid);

function checkObiettiviAreaRisultatoEliminabile($oGrid) {
    $id_area_risultato = $oGrid->key_fields["ID_area_risultato"]->value->getValue();
    $area_risultato = new ObiettiviAreaRisultato($id_area_risultato);
    $oGrid->display_delete_bt = $area_risultato->canDelete();
}