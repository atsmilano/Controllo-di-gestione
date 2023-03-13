<?php
use scadenze\ReferenteCdr;

$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//recupero dell'amministratore
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $referente = new ReferenteCdr($_REQUEST["keys[ID]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-referente";
$oRecord->title = $referente !== null ? "Modifica ": "Nuovo "."referente CdR";
$oRecord->resources[] = "referente";
$oRecord->src_table  = "scadenze_referente_cdr";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR"; 
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_personale";
$oField->base_type = "Text";
$oField->label = "Dipendente:";
$oField->required = true;
$oField->default_value = new ffData($referente->matricola_personale, "Text");
$dipendenti = array();

foreach (Personale::getAll() as $dipendente) {    
    $dipendenti[] = array(
        new ffData($dipendente->matricola, "Number"),
        new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
    );    
}
$oField->extended_type = "Selection";
$oField->multi_pairs = $dipendenti;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oField->widget = "datepicker";  
$oField->required = true;
$oRecord->addContent($oField);

//Data amministratore
$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oField->widget = "datepicker";  
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);