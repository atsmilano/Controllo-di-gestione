<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"];

if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze specifiche per il CdR.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_competenza_specifica]"])) {
    $isEdit = true;
    try {
        $competenza_specifica = new MappaturaCompetenze\CompetenzaSpecifica($_REQUEST["keys[ID_competenza_specifica]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "competenza-specifica-modify";
$oRecord->title = $isEdit ? "Modifica competenza specifica": "Nuova competenza specifica";
$oRecord->resources[] = "competenza-specifica";
$oRecord->src_table  = "competenze_competenza_specifica";
//$isDeletable = !$isEdit || ($isEdit && $competenza_specifica->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_competenza_specifica";
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

$oRecord->insert_additional_fields["matricola_responsabile"] = new ffData($user->matricola_utente_selezionato, "Text");
$oRecord->insert_additional_fields["codice_cdr"] = new ffData($cdr->codice, "Text");

//$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);
/*
function checkRelations($oRecord, $frmAction) {
    
}*/