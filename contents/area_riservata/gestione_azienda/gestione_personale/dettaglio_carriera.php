<?php
$isEdit = false;
if (isset($_REQUEST["keys[id_personale]"])) {
    $id_personale = $_REQUEST["keys[id_personale]"];
    
    try {
        $personale = new Personale($id_personale);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }    
}

if (isset($_REQUEST["keys[id_carriera]"])) {
    $isEdit = true;
    $id_carriera = $_REQUEST["keys[id_carriera]"];

    try {
        $carriera = new CarrieraPersonale($id_carriera);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$title = $isEdit ? "Modifica " : "Nuova ";
$title .= "carriera per ".$personale->cognome." ".$personale->nome;
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "carriera-personale";
$oRecord->title = $title;
$oRecord->resources[] = "carriera-personale";
$oRecord->src_table  = "carriera";

$oField = ffField::factory($cm->oPage);
$oField->id = "id_carriera";
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

foreach (TipoContratto::getAll() as $tipo_contratto) {
    $tipo_contratto_select[] = array(
        new ffData($tipo_contratto->id, "Number"),
        new ffData($tipo_contratto->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "id_tipo_contratto";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipo_contratto_select;
if ($isEdit) {
    $oField->default_value = new ffData($carriera->id_tipo_contratto, "Number");
    $oField->data_type = "";
}
$oField->label = "Tipo contratto";
$oField->required = true;
$oRecord->addContent($oField);

foreach (QualificaInterna::getAll() as $qualifica_interna) {
    $qualifica_interna_select[] = array(
        new ffData($qualifica_interna->id, "Number"),
        new ffData($qualifica_interna->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "id_qualifica_interna";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $qualifica_interna_select;
if ($isEdit) {
    $oField->default_value = new ffData($carriera->id_qualifica_interna, "Number");
    $oField->data_type = "";
}
$oField->label = "Qualifica interna";
$oField->required = true;
$oRecord->addContent($oField);

foreach (RapportoLavoro::getAll() as $rapporto_lavoro) {
    $rapporto_lavoro_select[] = array(
        new ffData($rapporto_lavoro->id, "Number"),
        new ffData($rapporto_lavoro->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "id_rapporto_lavoro";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $rapporto_lavoro_select;
if ($isEdit) {
    $oField->default_value = new ffData($carriera->id_rapporto_lavoro, "Number");
    $oField->data_type = "";
}
$oField->label = "Rapporto lavoro";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "perc_rapporto_lavoro";
$oField->base_type = "Number";
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "100";
$oField->step = "1";
$oField->required = true;    
$oField->addValidator("number", array(true, 0, 100, true, true));
$oField->label = "Percentuale rapporto lavorativo";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "posizione_organizzativa";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = array(
    array(new ffData("1", "Number"), new ffData("Si", "Text")),
    array(new ffData("0", "Number"), new ffData("No", "Text")),
);
$oField->label = "Incarico di funzione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
    $oField->widget = "datepicker";
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
            $id_carriera = $oRecord->key_fields["id_carriera"]->value->getValue();
            $str_error = "Impossibile modificare la presente voce di carriera per la matricola $matricola_personale: ";            
            $data_inizio_form = $oRecord->form_fields["data_inizio"]->value->getValue() != "" ?
                DateTime::createFromFormat("d/m/Y", $oRecord->form_fields["data_inizio"]->value->getValue()) :
                null;
            $data_fine_form = $oRecord->form_fields["data_fine"]->value->getValue() != "" ?
                DateTime::createFromFormat("d/m/Y", $oRecord->form_fields["data_fine"]->value->getValue()) :
                null;

            //data fine > data inizio => errore
            if(isset($data_fine_form) && $data_inizio_form > $data_fine_form) {
                return setError($oRecord, $str_error . "la data fine deve essere successiva alla data inizio.");
            }

            //Se il parametro "id_carriera_to_close" non viene passato, è un normale insert, altrimenti è un update+insert
            if(!isset($_REQUEST["keys[id_carriera_to_close]"])) {
                $carriera_personale_list = CarrieraPersonale::getAll(array("matricola_personale" => $matricola_personale));
                foreach($carriera_personale_list as $carriera) {
                    $data_inizio = isset($carriera->data_inizio) ?
                        DateTime::createFromFormat("Y-m-d", $carriera->data_inizio) :
                        null;
                    $data_fine = isset($carriera->data_fine) ?
                        DateTime::createFromFormat("Y-m-d", $carriera->data_fine) :
                        null;
                    /*
                     * Vengono definiti quali sono i casi accettati per l'inserimento dell'afferenza nel caso in cui
                     * venga trovato lo stesso CdC già afferente al personale in questione.
                     */
                    $inizioFineFormPrec = $data_inizio_form < $data_inizio &&
                        isset($data_fine_form) && $data_fine_form < $data_inizio;
                    $fineEsistenteInizioFormSucc = isset($data_fine) &&  $data_inizio_form > $data_fine;
                    //In caso di update viene esclusa la carriera che si sta modificando da quelle trovate nel db
                    if($carriera->id != $id_carriera) {
                        if (!($inizioFineFormPrec || $fineEsistenteInizioFormSucc)) {
                            return setError($oRecord, $str_error . "esiste già una carriera aperta selezionato nel periodo selezionato.");
                        }
                    }
                }
            } else {
                $id_carriera_to_close = $_REQUEST["keys[id_carriera_to_close]"];
                try {
                    //Viene creato l'oggetto CdcPersonale dell'allocazione da chiudere
                    $carriera = new CarrieraPersonale($id_carriera_to_close);
                    //Viene impostata come data di chiusura la data inizio - 1                    
                    $data_fine = $data_inizio_form->sub(new DateInterval("P1D"));
                    if($data_fine <= DateTime::createFromFormat("Y-m-d", $carriera->data_inizio)) {
                        return setError($oRecord, $str_error . "la data di inizio della nuova carriera"
                            ." deve essere successiva di almeno 2 giorni rispetto alla data di inizio della carriera da chiudere.");
                    }
                    $carriera->data_fine = $data_fine->format("Y-m-d");
                    $carriera->update();
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