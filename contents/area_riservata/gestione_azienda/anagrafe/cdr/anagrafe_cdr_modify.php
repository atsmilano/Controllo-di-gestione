<?php
$db = ffDb_Sql::factory();

$isNuovaAnagrafica = true;
$id_responsabile = 0;
$anagrafica = null;

if(isset($_REQUEST["keys[ID]"]) && $_REQUEST["keys[ID]"] != 0) {
    $isNuovaAnagrafica = false;
    $id_anagrafica = $_REQUEST["keys[ID]"];
    try {
        $anagrafica = new AnagraficaCdr($id_anagrafica);
    } catch (Exception $ex) {
         ffErrorHandler::raise($ex->getMessage());
    }
} 

// Definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "anagrafica-cdr-modify";
$oRecord->title = $isNuovaAnagrafica ? "Nuova anagrafica" : "Modifica anagrafica";
$oRecord->resources[] = "anagrafica-cdr";
$oRecord->src_table  = "anagrafica_cdr";

if(isset($anagrafica) && isset($anagrafica->data_termine)) {    
    $oRecord->allow_update = false;
}

if(isset($anagrafica) && !$isNuovaAnagrafica && sizeof(PianoCdr::getPianiCdrCodice($anagrafica->codice, "Cdr")) >= 1) {
    $oRecord->allow_delete = false;
}

$oRecord->addEvent("on_do_action", "checkRelations");

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
$oRecord->addContent($oField);
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oRecord->addContent($oField);
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}

// Select sulla tipologia del CdR
foreach (TipoCdr::getAll() AS $tipo_cdr) {
    $descrizione = $tipo_cdr->descrizione." (".$tipo_cdr->abbreviazione.")";

    $tipo_cdr_select[] = array(
        new ffData($tipo_cdr->id, "Number"),
        new ffData($descrizione, "Text")
    );
}
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_cdr";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipo_cdr_select;
$oField->label = "Tipologia CdR";
$oField->required = true;
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
$oRecord->addContent($oField);
if(isset($anagrafica) && isset($anagrafica->data_termine)) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $data_intro_form = $oRecord->form_fields["data_introduzione"]->value->getValue();
    $data_term_form = $oRecord->form_fields["data_termine"]->value->getValue();
    $codice_cdr_form = $oRecord->form_fields["codice"]->value->getValue();
    $error_periodo_non_valido = "La data di fine validità deve essere successiva o uguale alla data di inizio.";
    $error_periodi_sovrapposti = "Esiste già un'anagrafica per il CdR nel periodo selezionato.";
    $error_data_term_non_accettata = "La data di fine validità deve essere successiva rispetto alla data dell'ultimo POAS di cui fa parte il CdR selezionato.";        
    $error_modifica_non_consentita = "Non possono essere modificate anagrafiche che hanno una data termine";
    $error_tipo_cdr_non_modificabile = "Non può essere modificata la tipologia di CdR di un'anagrafica il cui CdR è appartenente ad un POAS.";
    $error_anagrafica_non_eliminabile = "Non possono essere eliminate anagrafiche il cui CdR appartiene ad un POAS.";
    
    if($frmAction == "update" || $frmAction == "insert") {        
        if(!isset($data_intro_form) || $data_intro_form == "") {
            return true;
        }
        $data_intro = strtotime(DateTime::createFromFormat("d/m/Y", $data_intro_form)->format("Y-m-d"));
        $data_term = $data_term_form != "" ? strtotime(DateTime::createFromFormat("d/m/Y", $data_term_form)->format("Y-m-d"))
                                        : null;

        // Date coerenti
        if($data_term != null && $data_intro >= $data_term) {
            return setError($oRecord, $error_periodo_non_valido);
        }

        foreach(AnagraficaCdr::getAll(array("codice" => $codice_cdr_form)) as $anagrafica) {
            if(isset($anagrafica->data_introduzione) && !isset($anagrafica->data_termine) && $frmAction != "update"
                    || $data_intro <= strtotime($anagrafica->data_termine)
                    || (isset($data_term) && $data_term < strtotime($anagrafica->data_introduzione))) {
                return setError($oRecord, $error_periodi_sovrapposti);
            }
        }

        // data fine successiva rispetto alla data dell'ultimo POAS in cui è presente il cdr.
        if($frmAction == "update" ) {            
            if(!checkDataTerm($data_term, $codice_cdr_form)) {
                return setError($oRecord, $error_data_term_non_accettata);
            }
            
            $tipo_cdr_form = $oRecord->form_fields["ID_tipo_cdr"]->value->getValue();
            $tipo_cdr_form_ori = $oRecord->form_fields["ID_tipo_cdr"]->value_ori->getValue();
            
            if($tipo_cdr_form != $tipo_cdr_form_ori && sizeof(PianoCdr::getPianiCdrCodice($codice_cdr_form, "Cdr")) >= 1) {
                return setError($oRecord, $error_tipo_cdr_non_modificabile);
            }
        }
    }
    
    if($frmAction == "update") {
        $anagrafica = new AnagraficaCdr($oRecord->key_fields["ID"]->value->getValue());
        if(isset($anagrafica->data_termine)) {
            return setError($oRecord, $error_modifica_non_consentita);
        }
    }
    
    if($frmAction == "delete" || $frmAction == "confirmdelete") {
        if(sizeof(PianoCdr::getPianiCdrCodice($codice_cdr_form, "Cdr")) >= 1) {
            return setError($oRecord, $error_anagrafica_non_eliminabile);
        }        
    }
}

function setError($oRecord, $str_error) {
    $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
        ? $oRecord->strError : $str_error;
    return true;
}

function checkDataTerm($data_term, $codice_cdr_form) {    
    if($data_term != null) {
        foreach(PianoCdr::getPianiCdrCodice($codice_cdr_form, "Cdr") as $piano_cdr_codice) {
            if($data_term < strtotime($piano_cdr_codice->data_definizione)) {
                return false;
            }
        }
    }
    return true;
}