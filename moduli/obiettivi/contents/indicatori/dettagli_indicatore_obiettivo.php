<?php
$date = $cm->oPage->globals["data_riferimento"]["value"];
//TODO privilegi utente e controlli coerenza parametri
if (isset ($_REQUEST["keys[ID_indicatore]"])) {
    $indicatore = new IndicatoriIndicatore($_REQUEST["keys[ID_indicatore]"]);   
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_indicatore.");
}
if (isset ($_REQUEST["keys[ID_obiettivo_indicatore]"])) {
    $obiettivo_indicatore = new IndicatoriObiettivoIndicatore($_REQUEST["keys[ID_obiettivo_indicatore]"]);   
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_indicatore.");
}

if (isset ($_REQUEST["keys[ID_obiettivo_cdr]"]) && strlen($_REQUEST["keys[ID_obiettivo_cdr]"])){
	$obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr]"]);
    if ($obiettivo_cdr->id_tipo_piano_cdr != null) {
        $tipo_piano = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
    }
    else {
        $tipo_piano = TipoPianoCdr::getPrioritaMassima();
    }
    $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));            
    //in caso di coreferenza viene estratto il padre dell'obiettivo
    if ($obiettivo_cdr->isCoreferenza()) {
        $cdr_valore_target = Cdr::factoryFromCodice($obiettivo_cdr_padre->codice_cdr, $piano_cdr);
    }
    else {    
        $cdr_valore_target = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr.");
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "indicatore-modify";
$oRecord->title = "Indicatori obiettivo ";
$oRecord->resources[] = "indicatore";
$oRecord->src_table = "indicatori_indicatore";

$oRecord->allow_insert = false;
$oRecord->allow_update = false;
$oRecord->allow_delete = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addkeyfield($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->label = "Nome Indicatore";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";		
$oField->extended_type = "Text";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->label = "Descrizione";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "istruzioni";
$oField->base_type = "Text";		
$oField->extended_type = "Text";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->label = "Istruzioni";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "formula_calcolo_risultato";
$oField->base_type = "Text";		
$oField->label = "Formula per il calcolo del risultato";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->data_type = "";
$oField->default_value = new ffData($indicatore->visualizzazioneFormulaRisultatoIndicatore(), "Text");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore_target";
$oField->base_type = "Text";		
$oField->label = "Valore Target";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->data_type = "";
$oField->default_value = new ffData($obiettivo_indicatore->getValoreTarget($cdr_valore_target), "Text");
$oRecord->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);