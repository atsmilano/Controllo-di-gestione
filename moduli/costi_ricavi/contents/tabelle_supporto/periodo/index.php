<?php
$grid_fields = array(
    "ID_periodo",
    "anno_budget",
    "data_riferimento_inizio",
    "data_riferimento_fine",
    "data_scadenza",
    "ordinamento_anno",
    "descrizione",
    "tipo_periodo"
);

$grid_recordset = array();
foreach (CostiRicaviPeriodo::getAll() as $item) {
    $anno_budget = new AnnoBudget($item->id_anno_budget);
    
    $grid_recordset[] = array(
        $item->id,
        $anno_budget->descrizione,
        $item->data_riferimento_inizio,
        $item->data_riferimento_fine,
        $item->data_scadenza,
        $item->ordinamento_anno,
        $item->descrizione,
        CostiRicaviPeriodo::$tipo_periodo[$item->id_tipo_periodo]
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodo";
$oGrid->title = "Periodo";
$oGrid->resources[] = "periodo";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "costi_ricavi_periodo"
);
$oGrid->order_default = "anno_budget";
$oGrid->record_id = "periodo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_periodo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_budget";
$oField->base_type = "Text";
$oField->label = "Anno Budget";
$oField->order_SQL = "anno_budget ASC, ordinamento_anno ASC";
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
$oField->id = "data_scadenza";
$oField->base_type = "Date";
$oField->label = "Data scadenza";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo_periodo";
$oField->base_type = "Text";
$oField->label = "Tipologia periodo";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_periodo"]->value->getValue();
    $item = new CostiRicaviPeriodo($id);
    $oGrid->display_delete_bt = $item->canDelete();
}