<?php
use scadenze\Amministratore;

$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//recupero dell'amministratore
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $amministratore = new Amministratore($_REQUEST["keys[ID]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-amministratore";
$oRecord->title = $amministratore !== null ? "Modifica ": "Nuovo "."amministratore";
$oRecord->resources[] = "amministratore";
$oRecord->src_table  = "scadenze_amministratore";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola";
$oField->base_type = "Text";
$oField->label = "Dipendente:";
$oField->required = true;
$oField->default_value = new ffData($amministratore->matricola, "Text");
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
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oField->widget = "datepicker";  
$oField->required = true;
$oRecord->addContent($oField);

//Data amministratore
$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oField->widget = "datepicker";  
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);