<?php
use scadenze\Amministratore;

$grid_fields = array(
    "ID",
    "dipendente",
    "data_riferimento_inizio",
    "data_riferimento_fine",
);

$grid_recordset = array();
foreach (Amministratore::getAll() as $amministratore) {
    $dipendente = Personale::factoryFromMatricola($amministratore->matricola);
    $grid_recordset[] = array(
        $amministratore->id,
        $dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")",
        $amministratore->data_riferimento_inizio,
        $amministratore->data_riferimento_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "amministratori";
$oGrid->title = "Amministratori";
$oGrid->resources[] = "amministratore";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "scadenze_amministratore"
);
$oGrid->order_default = "dipendente";
$oGrid->record_id = "amministratore-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dipendente";
$oField->base_type = "Text";
$oField->label = "Dipendente";
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

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID"]->value->getValue();
    $amministratore = new Amministratore($id);
    $oGrid->display_delete_bt = $amministratore->isDeletable();
}