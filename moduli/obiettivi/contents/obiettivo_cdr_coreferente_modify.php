<?php
$user = LoggedUser::getInstance();

if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli obiettivi aziendali.");	
}
//recupero e verifica dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

if (isset ($_REQUEST["keys[ID_obiettivo]"])){
	$obiettivo = new ObiettiviObiettivo($_REQUEST["keys[ID_obiettivo]"]); 
    if ($obiettivo->data_eliminazione !== null) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo di un obiettivo eliminato.");
	}    
    $cm->oPage->title = "Assegnazione CDR coreferente ad obiettivo '" . $obiettivo->codice . " - " . $obiettivo->titolo . "'";
}
else{
	ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo.");
}

$obiettivo_cdr = null;
if (isset ($_REQUEST["keys[ID_obiettivo_cdr]"])){
	//il cdr sarà modificabile solamente in creazione
	$obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr]"]);
	if ($obiettivo_cdr->data_eliminazione !== null) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr di un obiettivo eliminato.");
	}	
	if (!$obiettivo_cdr->isObiettivoCdrAziendale()) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr di un obiettivo non definito dalla direzione.");
	}
    if ($obiettivo_cdr->id_obiettivo !== $obiettivo->id) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr e ID_obiettivo non coerenti.");
    }
}
else {
	ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr.");
}

$obiettivo_cdr_coreferente = null;
if (isset ($_REQUEST["keys[ID_obiettivo_cdr_coreferente]"])){
	//il cdr sarà modificabile solamente in creazione
	$obiettivo_cdr_coreferente = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr_coreferente]"]);  
	$edit_cdr = false;
	if ($obiettivo_cdr_coreferente->data_eliminazione !== null) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr di un obiettivo eliminato.");
	}	
    if ($obiettivo_cdr_coreferente->id_obiettivo !== $obiettivo->id) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr e ID_obiettivo non coerenti.");
    }
}

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-cdr-coreferente-modify";
$oRecord->resources[] = "obiettivo-cdr-coreferente";
$oRecord->src_table = "obiettivi_obiettivo_cdr";

//viene definita sul record l'eliminazione logica del record piuttosto che quella fisica
$db = ffDb_Sql::factory();
$oRecord->del_action = "update";
$oRecord->del_update = "data_eliminazione=".$db->toSql(date("Y-m-d H:i:s"));

$oRecord->addEvent("on_done_action", "editRelations");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_cdr_coreferente";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//cdr selezionabili come coreferenti
$cdr_multipair = array();
if ($obiettivo_cdr_coreferente == null) {
    foreach (AnagraficaCdrObiettivi::getAnagraficaInData($date) as $anagrafica_cdr){
        if (!$obiettivo->isCdrAssociato($anagrafica_cdr)) {
            $cdr_multipair[] =
            array(
                new ffData ($anagrafica_cdr->codice),
                new ffData ($anagrafica_cdr->codice." - ". $tipo_cdr->abbreviazione . " - " . $anagrafica_cdr->descrizione, "Number"),						
            );
        }
    }
}
//in modifica viene semplicemente recuperato il cdr selezionato
else {   
    $anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_coreferente->codice_cdr, $date);
    $tot_peso_cdr = $anagrafica_cdr->getPesoTotaleObiettivi($anno, $obiettivo);
    $cdr_multipair[] =
        array(
            new ffData ($anagrafica_cdr->codice),
            new ffData ($anagrafica_cdr->codice." - " . $anagrafica_cdr->descrizione, "Number"),						
            );  
}
$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_multipair;
$oField->multi_select_one_label = "Selezionare il cdr a cui assegnare all'obiettivo...";
if ($obiettivo_cdr_coreferente == null) {
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oField->label = "Cdr per assegnazione";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->label = "Peso";
if (isset($tot_peso_cdr)) {
	$oField->label .= " (tot. peso obiettivi cdr escluso l'obiettivo corrente: " . $tot_peso_cdr . ")";
}
$oField->base_type = "Number";
$oField->addValidator("number", array(true, OBIETTIVI_MIN_PESO, OBIETTIVI_MAX_PESO, true, true, true));
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_obiettivo"] =  new ffData($obiettivo->id,"Number");
$oRecord->insert_additional_fields["codice_cdr_coreferenza"] = new ffData($obiettivo_cdr->codice_cdr, "Text");

// *********** ADDING TO PAGE ****************    
$cm->oPage->addContent($oRecord);

//propagazione dell'eliminazione sulle relazioni
function editRelations($oRecord, $frmAction){  
    switch($frmAction){   
		case "insert":								
        break;
		case "update":								
        break;
		case "delete":								
        case "confirmdelete":	            
			//recupero parametri
			$obiettivo_cdr = new ObiettiviObiettivoCdr($oRecord->key_fields["ID_obiettivo_cdr_coreferente"]->value->getValue());
            $cm = cm::getInstance();
			$date = $cm->oPage->globals["data_riferimento"]["value"];
            
            //recupero delle dipendenze dell'obiettivo_cdr, eliminazione e propagazione su tutti obiettivi_cdr_personale collegati
            //propagazione dell'eliminazione gestita tramite metodo dell'oggetto
            foreach ($obiettivo_cdr->getDipendenze($date) as $obiettivo_cdr_dipendenza) {                              
                $obiettivo_cdr_dipendenza->logicalDelete();
            }
        break;
    }
}