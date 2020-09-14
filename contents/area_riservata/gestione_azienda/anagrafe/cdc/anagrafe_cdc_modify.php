<?php
$db = ffDb_Sql::factory();

$isNuovaAnagrafica = true;
$id_responsabile = 0;
$anagrafica = null;

if(isset($_REQUEST["keys[ID]"]) && $_REQUEST["keys[ID]"] != 0) {
    $isNuovaAnagrafica = false;
    $id_anagrafica = $_REQUEST["keys[ID]"];
    try {
        $anagrafica = new AnagraficaCdc($id_anagrafica);
    } catch (Exception $ex) {
         ffErrorHandler::raise($ex->getMessage());
    }
} 

// Viene definito il record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "anagrafica-cdc-modify";
$oRecord->title = $isNuovaAnagrafica ? "Nuova anagrafica" : "Modifica anagrafica";
$oRecord->resources[] = "anagrafica-cdc";
$oRecord->src_table  = "anagrafica_cdc";

$oRecord->addEvent("on_do_action", "checkRelations");

if(isset($anagrafica) && isset($anagrafica->data_termine)) {    
    $oRecord->allow_update = false;
}

if(isset($anagrafica) && !$isNuovaAnagrafica && sizeof(PianoCdr::getPianiCdrCodice($anagrafica->codice, "Cdc")) >= 1) {
    $oRecord->allow_delete = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
	
$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oField->required = true;
if (!$isNuovaAnagrafica) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data introduzione";
$oField->widget = "datepicker";
$oField->required = true;
if (!$isNuovaAnagrafica) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data termine";
$oField->widget = "datepicker";
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $data_intro_form = $oRecord->form_fields["data_introduzione"]->value->getValue();
    $data_term_form = $oRecord->form_fields["data_termine"]->value->getValue();
    $codice_cdc_form = $oRecord->form_fields["codice"]->value->getValue();
    $error_periodo_non_valido = "La data di fine validità deve essere successiva o uguale alla data di inizio.";
    $error_periodi_sovrapposti = "Esiste già un'anagrafica per il CdC nel periodo selezionato.";
    $error_data_term_non_accettata = "La data di fine validità deve essere successiva rispetto alla data dell'ultimo POAS di cui fa parte il CdC selezionato.";        
    $error_modifica_non_consentita = "Non possono essere modificate anagrafiche che hanno una data termine";
    $error_anagrafica_non_eliminabile = "Non possono essere eliminate anagrafiche il cui CdC appartiene ad un POAS.";
    
    if($frmAction == "update" || $frmAction == "insert") {
        if(!isset($data_intro_form) || $data_intro_form == "") {
            return true;
        }
        $data_intro = strtotime(DateTime::createFromFormat("d/m/Y", $data_intro_form)->format("Y-m-d"));
        $data_term = $data_term_form != "" ? strtotime(DateTime::createFromFormat("d/m/Y", $data_term_form)->format("Y-m-d"))
                                        : null;
        
        // Controllo sulla coerenza delle date
        if($data_term != null && $data_intro >= $data_term) {
            return setError($oRecord, $error_periodo_non_valido);
        }

        foreach(AnagraficaCdc::getAll(array("codice" => $codice_cdc_form)) as $anagrafica) {
            if(isset($anagrafica->data_introduzione) && !isset($anagrafica->data_termine) && $frmAction != "update"
                    || $data_intro < strtotime($anagrafica->data_termine)
                    || (isset($data_term) && $data_term < strtotime($anagrafica->data_introduzione))) {
                return setError($oRecord, $error_periodi_sovrapposti);
            }
        }

        // data fine successiva rispetto alla data dell'ultimo POAS in cui è presente il cdc.
        // controllo da saltare per l'insert, se viene inserita una nuova anagrafica di sicuro
        // questa non compare in nessun piano cdr al momento dell'inserimento, quindi non sarà presente
        // in alcun piano cdr.
        if($frmAction == "update" ) {
            if(!checkDataTerm($data_term, $codice_cdc_form)) {
                return setError($oRecord, $error_data_term_non_accettata);
            }
        }
    }
    
    if($frmAction == "update") {
        $anagrafica = new AnagraficaCdc($oRecord->key_fields["ID"]->value->getValue());
        if(isset($anagrafica->data_termine)) {
            return setError($oRecord, $error_modifica_non_consentita);
        }
    }
    
    if($frmAction == "delete" || $frmAction == "confirmdelete") {
        if(sizeof(PianoCdr::getPianiCdrCodice($codice_cdc_form, "Cdc")) >= 1) {
            return setError($oRecord, $error_anagrafica_non_eliminabile);
        }        
    }
}

function setError($oRecord, $str_error) {
    $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
        ? $oRecord->strError : $str_error;
    return true;
}

function checkDataTerm($data_term, $codice_cdc_form) {
    if($data_term != null) {
        foreach(PianoCdr::getPianiCdrCodice($codice_cdc_form, "Cdc") as $piano_cdr_codice) {
            if($data_term < strtotime($piano_cdr_codice->data_definizione)) {
                return false;
            }
        }
    }
    return true;
}

function checkEliminabile($oGrid) {
    $codice_cdr = $oGrid->grid_fields["codice"]->value->getValue();

    if (empty(PianoCdr::getPianiCdrCodice($codice_cdr))) {
        $oGrid->display_delete_bt = true;
    } else {
        $oGrid->display_delete_bt = false;
    }
}