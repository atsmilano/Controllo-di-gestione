<?php
$user = LoggedUser::getInstance();

//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei valori.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_valore]"])) {
    $isEdit = true;
    try {
        $competenza_trasversale = new MappaturaCompetenze\Valore($_REQUEST["keys[ID_valore]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "valore-modify";
$oRecord->title = $isEdit ? "Modifica valore": "Nuovo valore";
$oRecord->resources[] = "valore";
$oRecord->src_table  = "competenze_valore";
//$isDeletable = !$isEdit || ($isEdit && $competenza_trasversale->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_valore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore";
$oField->base_type = "Number";
$oField->label = "valore";
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