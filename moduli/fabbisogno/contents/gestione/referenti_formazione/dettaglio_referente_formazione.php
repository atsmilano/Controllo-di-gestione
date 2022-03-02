<?php
$user = LoggedUser::getInstance();

$date = $cm->oPage->globals["data_riferimento"]["value"];

//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_admin") && !$user->hasPrivilege("fabbisogno_operatore_formazione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei referenti CdR.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_referente_cdr]"])) {
    $isEdit = true;
    try {
        $referente_cdr = new FabbisognoFormazione\ReferenteCdr($_REQUEST["keys[ID_referente_cdr]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-referente-cdr";
$oRecord->title = $isEdit ? "Modifica referente formazione": "Nuovo referente formazione";
$oRecord->resources[] = "referente";
$oRecord->src_table  = "fabbisogno_referente_cdr";
//$isDeletable = !$isEdit || ($isEdit && $referente_cdr->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_referente_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_personale";
$oField->base_type = "Text";
$oField->label = "Referente:";
$oField->required = true;
$oField->default_value = new ffData($referente_cdr->matricola_personale, "Text");
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
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oField->required = true;
$oField->default_value = new ffData($referente_cdr->codice_cdr, "Text");
$cdrs = array();
foreach (AnagraficaCdr::getAll() as $cdr) {
    //if ($dipendente->isAttivoInData($date->format("Y-m-d"))) {
        $cdrs[] = array(
            new ffData($cdr->codice, "Text"),
            new ffData($cdr->codice . " - " . $cdr->descrizione, "Text"),
        );
    //}
}
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdrs;
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