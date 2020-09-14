<?php
$grid_fields = array("ID_personale", "cognome",
    "nome", "matricola");
$grid_recordset = array();
foreach(Personale::getAll() as $personale) {
    $grid_recordset[] = array(
        $personale->id,
        $personale->cognome, $personale->nome,
        $personale->matricola
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "personale";
$oGrid->title = "Elenco personale";
$oGrid->resources[] = "personale";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "personale"
);
$oGrid->order_default = "cognome";
$oGrid->record_id = "personale-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_ob_individuali";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->use_paging = false;
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_personale";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cognome";
$oField->base_type = "Text";
$oField->label = "Cognome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola";
$oField->base_type = "Text";
$oField->label = "Matricola";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);