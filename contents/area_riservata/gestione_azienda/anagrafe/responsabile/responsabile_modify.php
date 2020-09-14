<?php
$isNuovaAnagrafica = true;
$id_responsabile = 0;
$responsabile = null;

if(isset($_REQUEST["keys[ID]"]) && $_REQUEST["keys[ID]"] != 0) {
    $isNuovaAnagrafica = false;
    $id_responsabile = $_REQUEST["keys[ID]"];
    try {
        $responsabile = new ResponsabileCdr($id_responsabile);
    } catch (Exception $ex) {
         ffErrorHandler::raise($ex->getMessage());
    }
} 

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "responsabile-modify";
$oRecord->title = $isNuovaAnagrafica ? "Nuovo responsabile" :  "Modifica responsabile";
$oRecord->resources[] = "anagrafe-responsabile";
$oRecord->src_table = "responsabile_cdr";

if(isset($responsabile) && isset($responsabile->data_fine)) {   
    $oRecord->allow_update = false;
}

$oRecord->addEvent("on_do_action", "checkRelations");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile";
$oField->label = "Responsabile";
$oField->required = true;
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = getPersonale();

if(!$isNuovaAnagrafica) {
    $oField->store_in_db = false;
    $oField->control_type = "label";
} 
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->label = "Codice cdr";
$oField->required = true;
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = getAnagrafiche();
if(isset($responsabile) && isset($responsabile->data_fine)) {
    $oField->store_in_db = false;
    $oField->control_type = "label";
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->label = "Data inizio";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->required = true;

if(!$isNuovaAnagrafica) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->label = "Data fine";
$oField->base_type = "Date";
$oField->widget = "datepicker";
if(isset($responsabile) && isset($responsabile->data_fine)) {
    $oField->store_in_db = false;
    $oField->control_type = "label";
}
$oRecord->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function getPersonale() {
    $personale = array();
    foreach(Personale::getAll() as $persona) {      
        $personale[] = array(
            new ffData($persona->matricola, "Text"),
            new ffData($persona->cognome . " " . $persona->nome 
                . " (matr. " . $persona->matricola . ")", "Text"),
        );
    }
    return $personale;
    
}

function getAnagrafiche() {
    $anagrafiche = array();
    foreach(AnagraficaCdr::getAll() as $anagrafica) {
        $anagrafiche[] = array(
            new ffData($anagrafica->codice, "Text"),
            new ffData($anagrafica->codice . " - " . $anagrafica->descrizione, "Text"),
        );
    }
    return $anagrafiche;
}

function setError($oRecord, $str_error) {
    $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
        ? $oRecord->strError : $str_error;
    return true;
}

function checkRelations($oRecord, $frmAction) {
    $data_inizio_form = $oRecord->form_fields["data_inizio"]->value->getValue();
    $data_fine_form = $oRecord->form_fields["data_fine"]->value->getValue();
    $codice_cdr_form = $oRecord->form_fields["codice_cdr"]->value->getValue();
    $error_periodo_non_valido = "La data di fine validità deve essere successiva alla data di inizio.";
    $error_periodi_sovrapposti = "Esiste già un responsabile per il cdr nel periodo selezionato.";
    $error_data_fine_non_accettata = "La data di fine validità deve essere successiva rispetto alla data dell'ultimo POAS di cui fa parte il cdr selezionato.";        
    $error_modifica_non_consentita = "Non possono essere modificate anagrafiche dei responsabili che hanno una data fine.";

    if($frmAction == "insert" || $frmAction == "update") {
        if(!isset($data_inizio_form) || $data_inizio_form == "") {
            return true;
        }
        $data_inizio = strtotime(DateTime::createFromFormat("d/m/Y", $data_inizio_form)->format("Y-m-d"));
        $data_fine = $data_fine_form != "" ? strtotime(DateTime::createFromFormat("d/m/Y", $data_fine_form)->format("Y-m-d"))
                                        : null;

        // Verifica coerenza date
        if($data_fine != null && $data_inizio >= $data_fine) {
            return setError($oRecord, $error_periodo_non_valido);
        }

        foreach(ResponsabileCdr::getAll(array("codice_cdr" => $codice_cdr_form)) as $responsabile) {
            if(isset($responsabile->data_inizio) && !isset($responsabile->data_fine) && $frmAction != "update"
                    || $data_inizio < strtotime($responsabile->data_fine)
                    || (isset($data_fine) && $data_fine < strtotime($responsabile->data_inizio))) {
                return setError($oRecord, $error_periodi_sovrapposti);
            }
        }

        // data fiune successiva rispetto alla data dell'ultimo POAS in cui è presente il cdr.
        if(!checkDataTerm($data_fine, $codice_cdr_form)) {
            return setError($oRecord, $error_data_fine_non_accettata);
        }
            
    }
    
    if($frmAction == "update") {
        $responsabile = new ResponsabileCdr($oRecord->key_fields["ID"]->value->getValue());
        if(isset($responsabile->data_fine)) {
            setError($oRecord, $error_modifica_non_consentita);
        }
    }
}

function checkDataTerm($data_fine, $codice_cdr_form) {
    if($data_fine != null) {
        foreach(PianoCdr::getAll() as $piano_cdr) {
            try {
                $cdr = Cdr::factoryFromCodice($codice_cdr_form, $piano_cdr);
                if($data_fine < strtotime($piano_cdr->data_definizione)) {
                    return false;
                }
            } catch (Exception $ex) {
                //Se viene lanciata una exception significa che il cdr non esiste nel piano, si prosegue comunque.
            }
        }
    }     
    return true;
}
