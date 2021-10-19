<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli indicatori.");	
}

if (!isset($_REQUEST["keys[ID_indicatore]"])) {
    ffErrorHandler::raise("Errorenel passaggio dei parametri: ID_indicatore.");
}
else {
    $indicatore = new IndicatoriIndicatore($_REQUEST["keys[ID_indicatore]"]);
}

$valore_target = null;
if (isset($_REQUEST["keys[ID_valore_target_indicatore]"])) {
    try {
        $valore_target = new IndicatoriValoreTarget($_REQUEST["keys[ID_valore_target_indicatore]"]);        
    } catch (Exception $ex) {
        ffErrorHandler::raise("Errorenel passaggio dei parametri: ID_valore_target_indicatore.");
    }
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "valore-target-modify";
$oRecord->resources[] = "valore-target-indicatore";
$oRecord->src_table = "indicatori_valore_target";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_valore_target_indicatore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addkeyfield($oField);

$oRecord->title = "Valore target dell'indicatore '" . $indicatore->nome . "'";

$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $cm->oPage->globals["data_riferimento"]["value"]->format('Y-m-d'));
$cdr_piano = array();

//in modifica viene estratto solamente il cdr selezionato (non modificabile) per evitare calcoli inutili
$show_azienda = false;
if ($valore_target !== null) {
    if (strlen($valore_target->codice_cdr) == 0){
        $show_azienda = true;   
    }
    else {
        $date = $cm->oPage->globals["data_riferimento"]["value"];
        $anagrafica_cdr = AnagraficaCdr::factoryFromCodice($valore_target->codice_cdr, $date);    
        $cdr_piano[] = array(
                new ffData($anagrafica_cdr->codice, "Text"),
                new ffData($anagrafica_cdr->codice . " - " . $anagrafica_cdr->descrizione, "Text"),
        );
    }
}
else {   
    //verifica su CdR del piano
    foreach ($piano_cdr->getCdr() as $cdr) {       
        //vengono esclusi dall'elenco tutti i cdr per i quali è già definito un valore target
        $valore_target = $indicatore->getValoreTargetAnno($anno, $cdr, true);
        if ($valore_target == null) {            
            $cdr_piano[] = array(
                new ffData($cdr->codice, "Text"),
                new ffData($cdr->codice . " - " . $cdr->descrizione, "Text"),
            );
        }
    }
    //verifica su valore target aziendale
    if ($indicatore->getValoreTargetAnno($anno, null, true) == null) {
        $show_azienda = true;
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_piano;
$oField->label = "CdR (" . $tipo_piano_cdr->descrizione . ")";
$oField->required = true;
if ($show_azienda == true) {
    $oField->required = false;
    $oField->multi_select_one_label = "Azienda";
}
if ($valore_target !== null) {        
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore_target";
$oField->base_type = "Number";
$oField->label = "Valore target";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_indicatore"] =  new ffData($indicatore->id, "Number");
$oRecord->insert_additional_fields["ID_anno_budget"] =  new ffData($anno->id, "Number");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);