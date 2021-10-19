<?php
/*******************************/
/********** AREA ITEM **********/
/*******************************/
$grid_fields = array(
    "ID_area_item",
    "descrizione",
    "ambito",
);

$grid_recordset = array();
foreach (ValutazioniAreaItem::getAll() as $area_item) {
    $ambito = new ValutazioniAmbito($area_item->id_ambito);
    $sezione = new ValutazioniSezione($ambito->id_sezione);
    $grid_recordset[] = array(
        $area_item->id,
        $area_item->descrizione,
        $sezione->codice . "." . $ambito->codice. ". ".$ambito->descrizione,      
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "valutazioni-area-item";
$oGrid->title = "Area item";
$oGrid->resources[] = "area-item";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_area_item");
$oGrid->order_default = "ID_area_item";
$oGrid->record_id = "area-item-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_area_item";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_item";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ambito";
$oField->base_type = "Text";
$oField->label = "Ambito";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkAreaItemEliminabile");
// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkAreaItemEliminabile($oGrid) {
    $id_area_item = $oGrid->key_fields["ID_area_item"]->value->getValue();
    $area_item = new ValutazioniAreaItem($id_area_item);
    $oGrid->display_delete_bt = $area_item->canDelete();
}