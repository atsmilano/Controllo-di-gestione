<?php
$isEdit = false;
if (isset($_REQUEST["keys[id_personale]"])) {
    $id_personale = $_REQUEST["keys[id_personale]"];
    
    try {
        $personale = new Personale($id_personale);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    } 
} else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: id_personale");
}

if (isset($_REQUEST["keys[id_cdc_personale]"])) {
    $isEdit = true;
    $id_cdc_personale = $_REQUEST["keys[id_cdc_personale]"];
    
    try {
        $cdc_personale = new CdcPersonale($id_cdc_personale);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$title = $isEdit ? "Modifica " : "Nuova ";
$title .= "allocazione per ".$personale->cognome." ".$personale->nome;
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdc-allocazione";
$oRecord->title = $title;
$oRecord->resources[] = "cdc-allocazione";
$oRecord->src_table  = "cdc_personale";

$oField = ffField::factory($cm->oPage);
$oField->id = "id_cdc_personale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_personale";
$oField->base_type = "Text";
$oField->label = "Matricola";
$oField->default_value = new ffData($personale->matricola, "Text");
$oField->control_type = "label";    
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdc";
$oField->base_type = "Text";
$oField->label = "Codice CdC";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "percentuale";
$oField->base_type = "Number";
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "100";
$oField->step = "1";
$oField->required = true;    
$oField->addValidator("number", array(true, 0, 100, true, true));
$oField->label = "Percentuale";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
$oField->widget = "datepicker";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->label = "Data fine";
$oField->widget = "datepicker";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateAction");
$cm->oPage->addContent($oRecord);

function validateAction($oRecord, $frmAction) {
    switch($frmAction) {
        case "update":
        case "insert":
            $matricola_personale = $oRecord->form_fields["matricola_personale"]->value->getValue();
            $id_cdc_personale = $oRecord->key_fields["id_cdc_personale"]->value->getValue();
            $str_error = "Impossibile inserire/modificare l'evento di carriera per la matricola $matricola_personale: ";
            $codice_cdc_form = $oRecord->form_fields["codice_cdc"]->value->getValue();
            $data_inizio_form_str = $oRecord->form_fields["data_inizio"]->value->getValue();
            $data_inizio_form = $data_inizio_form_str != "" ?
                DateTime::createFromFormat("d/m/Y", $data_inizio_form_str) :
                null;
            $data_fine_form_str = $oRecord->form_fields["data_fine"]->value->getValue();
            $data_fine_form = $data_fine_form_str != "" ?
                DateTime::createFromFormat("d/m/Y", $data_fine_form_str) :
                null;

            //data fine > data inizio => errore
            if(isset($data_fine_form) && $data_inizio_form > $data_fine_form) {
                return setError($oRecord, $str_error . "la data fine deve essere successiva alla data inizio.");
            }

            foreach(CdcPersonale::getAll(array("matricola_personale" => $matricola_personale)) as $cdcPersonale) {
                //incremento il totale dell percentuali di afferenza ai CdC per una persona
                $data_inizio = isset($cdcPersonale->data_inizio) ?
                    DateTime::createFromFormat("Y-m-d", $cdcPersonale->data_inizio) :
                    null;
                $data_fine = isset($cdcPersonale->data_fine) ?
                    DateTime::createFromFormat("Y-m-d", $cdcPersonale->data_fine) :
                    null;
                
                $inizioFineFormPrec = $data_inizio_form < $data_inizio &&
                    isset($data_fine_form) && $data_fine_form < $data_inizio;
                $fineEsistenteInizioFormSucc = isset($data_fine) && $data_inizio_form > $data_fine;

                //Update condizionato nel caso di afferenza allo stess oCdC già presente
                if($cdcPersonale->codice_cdc == $codice_cdc_form && $cdcPersonale->id != $id_cdc_personale) {
                    if(!($inizioFineFormPrec || $fineEsistenteInizioFormSucc)) {
                        return setError($oRecord, $str_error . "esiste già un'afferenza per il CdC selezionato nel periodo selezionato.");
                    }
                }
            }

            //Eseguito solo se viene passato come parametro l'id di una allocazione da chiudere
            if(isset($_REQUEST["keys[id_cdc_personale_to_close]"])) {
                $id_cdc_personale_to_close = $_REQUEST["keys[id_cdc_personale_to_close]"];
                try {
                    //Viene creato l'oggetto CdcPersonale dell'allocazione da chiudere
                    $cdc_personale = new CdcPersonale($id_cdc_personale_to_close);
                    //Viene impostata come data di chiusura la data inizio - 1
                    $data_fine = $data_inizio_form->sub(new DateInterval("P1D"));
                    if($data_fine <= DateTime::createFromFormat("Y-m-d", $cdc_personale->data_inizio)) {
                        return setError($oRecord, $str_error . "la data di inizio dell'afferenza al CdC da inserire"
                            ." deve essere successiva di almeno 2 giorni rispetto alla data di inizio dell'afferenza al CdC da chiudere.");
                    }
                    $cdc_personale->data_fine = $data_fine->format("Y-m-d");
                    //Viene aggiornata l'allocazione al CdC
                    $cdc_personale->update();
                } catch (Exception $ex) {
                    ffErrorHandler::raise($ex->getMessage());
                }
            }

            break;
        default:
            break;
    }
}

function setError($oRecord, $str_error) {
    $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != ""
        ? $oRecord->strError : $str_error;
    return true;
}
