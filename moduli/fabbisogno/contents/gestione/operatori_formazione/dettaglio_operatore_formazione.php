<?php
$user = LoggedUser::getInstance();

$date = $cm->oPage->globals["data_riferimento"]["value"];

//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_admin")&&$user->hasPrivilege("fabbisogno_operatore_formazione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei referenti della formazione.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_operatore_formazione]"])) {
    $isEdit = true;
    try {
        $operatore_formazione = new FabbisognoFormazione\OperatoreFormazione($_REQUEST["keys[ID_operatore_formazione]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-operatore-formazione";
$oRecord->title = $isEdit ? "Modifica operatore formazione": "Nuovo operatore formazione";
$oRecord->resources[] = "operatore";
$oRecord->src_table  = "fabbisogno_operatore_formazione";
//$isDeletable = !$isEdit || ($isEdit && $operatore_formazione->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_operatore_formazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_personale";
$oField->base_type = "Text";
$oField->label = "Operatore:";
$oField->required = true;
$oField->default_value = new ffData($operatore_formazione->matricola_personale, "Text");
$dipendenti = array();
foreach (Personale::getAll() as $dipendente) {
    //if ($dipendente->isAttivoInData($date->format("Y-m-d"))) {
        $dipendenti[] = array(
            new ffData($dipendente->matricola, "Number"),
            new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
        );
    //}
}
$oField->extended_type = "Selection";
$oField->multi_pairs = $dipendenti;
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