<?php
use scadenze\ReferenteCdr;

$grid_fields = array(
    "ID",
    "codice_cdr",
    "dipendente",
    "data_introduzione",
    "data_termine",
);

$grid_recordset = array();
foreach (ReferenteCdr::getAll() as $referente) {
    $dipendente = Personale::factoryFromMatricola($referente->matricola_personale);
    $grid_recordset[] = array(
        $referente->id,
        $referente->codice_cdr,
        $dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")",
        $referente->data_introduzione,
        $referente->data_termine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "referenti";
$oGrid->title = "Referenti CdR";
$oGrid->resources[] = "referente";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "scadenze_referente_cdr"
);
$oGrid->order_default = "dipendente";
$oGrid->record_id = "referente-modify";
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
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dipendente";
$oField->base_type = "Text";
$oField->label = "Dipendente";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID"]->value->getValue();
    $referente = new ReferenteCdr($id);
    $oGrid->display_delete_bt = $referente->isDeletable();
}