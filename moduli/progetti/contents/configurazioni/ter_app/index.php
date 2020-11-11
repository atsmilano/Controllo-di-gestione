<?php
$grid_fields = array(
    "ID_territorio_applicazione",
    "descrizione"
);

$grid_recordset = array();
foreach (ProgettiTerritorioApplicazione::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "territorio-applicazione";
$oGrid->title = "Territorio Applicazione";
$oGrid->resources[] = "territorio-applicazione";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "progetti_territorio_applicazione"
);
$oGrid->order_default = "ID_territorio_applicazione";
$oGrid->record_id = "territorio-applicazione-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_territorio_applicazione";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_territorio_applicazione";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_territorio_applicazione"]->value->getValue();
    $item = new ProgettiTerritorioApplicazione($id);
    $oGrid->display_delete_bt = $item->canDelete();
}