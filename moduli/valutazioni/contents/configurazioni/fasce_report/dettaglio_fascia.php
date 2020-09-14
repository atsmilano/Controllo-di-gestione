<?php
$isEdit = false;
if (isset($_REQUEST["keys[id_fascia]"])) {
    $isEdit = true;
    $id_fascia = $_REQUEST["keys[id_fascia]"];

    try {
        $fascia = new ValutazioniFasciaPunteggio($id_fascia);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fascia-modify";
$oRecord->title = $isEdit ? "Modifica fascia di punteggio" : "Nuova fascia di punteggio";
$oRecord->resources[] = "fascia_punteggio";
$oRecord->src_table  = "valutazioni_fascia_punteggio";
$oRecord->allow_delete = ValutazioniFasciaPunteggio::isFasciaEliminabile();

$oField = ffField::factory($cm->oPage);
$oField->id = "id_fascia";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "min";
$oField->base_type = "Number";
$oField->label = "Punteggio minimo";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "max";
$oField->base_type = "Number";
$oField->label = "Punteggio massimo";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "colore";
$oField->base_type = "Text";
$oField->widget = "colorpicker";
$oField->required = true;
$oField->store_in_db = true;
$oField->label = "Colore";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data inizio";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data fine";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $form_fields = $oRecord->form_fields;
    $id = $oRecord->key_fields["id_fascia"]->value->getValue();
    $min_form = $form_fields["min"]->value->getValue();
    $max_form = $form_fields["max"]->value->getValue();

    $data_inizio_form_str =$form_fields["data_inizio"]->value->getValue();
    $data_inizio_form = $data_inizio_form_str != "" ?
        DateTime::createFromFormat("d/m/Y", $data_inizio_form_str) :
        null;
    $data_fine_form_str = $form_fields["data_fine"]->value->getValue();
    $data_fine_form = $data_fine_form_str != "" ?
        DateTime::createFromFormat("d/m/Y", $data_fine_form_str) :
        null;

    switch($frmAction) {
        case "insert":
        case "update":
            $str_error = "Impossibile inserire/modificare la fascia di punteggio: ";
            if($data_inizio_form > $data_fine_form) {
                return CoreHelper::setError($oRecord, $str_error . "la data fine deve essere successiva o uguale alla data inizio");
            }
            foreach(ValutazioniFasciaPunteggio::getAll() as $fascia) {
                $data_inizio = isset($fascia->data_inizio) ?
                    DateTime::createFromFormat("Y-m-d", $fascia->data_inizio) :
                    null;
                $data_fine = isset($fascia->data_fine) ?
                    DateTime::createFromFormat("Y-m-d", $fascia->data_fine) :
                    null;

                if($min_form >= $max_form) {
                    return CoreHelper::setError($oRecord, $str_error . "Il punteggio massimo deve essere superiore al punteggio minimo");
                }

                //condizione di sovrapposizione punteggi
                $sovrapposizione_punteggi = !($max_form < $fascia->min || $min_form > $fascia->max);

                //condizione di sovrapposizione date
                $sovrapposizione_date = !(
                    ($data_inizio_form < $data_inizio && isset($data_fine_form) && $data_fine_form < $data_inizio)
                        ||
                    (isset($data_fine) && $data_inizio_form > $data_fine)
                );

                //Viene verificato che non ci siano sovrapposizioni punteggi/date per le fasce di punteggio
                if ($id != $fascia->id && $sovrapposizione_punteggi && $sovrapposizione_date) {
                    return CoreHelper::setError($oRecord, $str_error . "esiste già una fascia di punteggio nel periodo selezionato.");
                }
            }
            break;
        case "delete":
            $str_error = "Impossibile eliminare la fascia di punteggio: ";
            if(!ValutazioniFasciaPunteggio::isFasciaEliminabile()) {
                return CoreHelper::setError($oRecord, $str_error . "non può essere cancellata l'unica fascia attiva presente");
            }
            break;
    }
}