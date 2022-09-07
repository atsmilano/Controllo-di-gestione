<?php
$user = LoggedUser::getInstance();

//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze trasversali per il CdR.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_competenza_trasversale]"])) {
    $isEdit = true;
    try {
        $competenza_trasversale = new MappaturaCompetenze\CompetenzaTrasversale($_REQUEST["keys[ID_competenza_trasversale]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "competenza-trasversale-modify";
$oRecord->title = $isEdit ? "Modifica competenza trasversale": "Nuova competenza trasversale";
$oRecord->resources[] = "competenza-trasversale";
$oRecord->src_table  = "competenze_competenza_trasversale";
//$isDeletable = !$isEdit || ($isEdit && $competenza_trasversale->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_competenza_trasversale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->label = "Data introduzione"; 
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->label = "Data termine"; 
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oRecord->addContent($oField);

//$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);
/*
function checkRelations($oRecord, $frmAction) {
    
}*/