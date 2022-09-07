<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze trasversali.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID", 
    "nome",
    "descrizione", 
    "data_introduzione",
    "data_termine",
);
$grid_recordset = array();
foreach (MappaturaCompetenze\CompetenzaTrasversale::getAll() as $competenza_trasversale) {
    $grid_recordset[] = array(
        $competenza_trasversale->id,
        $competenza_trasversale->nome,
        $competenza_trasversale->descrizione,
        $competenza_trasversale->data_introduzione,
        $competenza_trasversale->data_termine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "competenze-trasversali";
$oGrid->title = "Competenze trasversali";
$oGrid->resources[] = "competenza-trasversale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "competenze_competenza_trasversale"
);
$oGrid->order_default = "descrizione";
$oGrid->record_id = "competenza-trasversale-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_competenza_trasversale";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->use_paging = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_competenza_trasversale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data termine";
$oGrid->addContent($oField);

//$oGrid->addEvent("on_before_parse_row", "checkObiettiviAreaEliminabile");
$cm->oPage->addContent($oGrid);
/*
function checkObiettiviAreaEliminabile($oGrid) {
    $id_area = $oGrid->key_fields["ID_area"]->value->getValue();
    $area = new ObiettiviArea($id_area);
    $oGrid->display_delete_bt = $area->canDelete();
}*/