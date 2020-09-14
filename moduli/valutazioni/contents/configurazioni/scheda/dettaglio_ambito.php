<?php
if (isset($_REQUEST["keys[ID_ambito]"])) {
    $isEdit = true;
    $id_ambito = $_REQUEST["keys[ID_ambito]"];

    try {
        $ambito = new ValutazioniAmbito($id_ambito);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

if($isEdit) {
    $sezione = new ValutazioniSezione($ambito->id_sezione);
    $title = "Modifica ambito " . $sezione->codice . "." . $ambito->codice . ". " . $ambito->descrizione;
} else {
    $title = "Nuovo ambito";
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ambito-modify";
$oRecord->title = $title;
$oRecord->resources[] = "ambito";
$oRecord->src_table  = "valutazioni_ambito";
$editable = !$isEdit || ($isEdit && $ambito->canDelete());
$oRecord->allow_delete = $editable;

CoreHelper::refreshTabOnDialogClose($oRecord->id);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_ambito";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$sezioni = ValutazioniSezione::getAll();
$sezioni_menu = array();
foreach($sezioni as $sezione) {
    $sezioni_menu[] = array(
        new ffData($sezione->id, "Number"),
        new ffData($sezione->codice . " - " .$sezione->descrizione, "Text"),
    );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sezione";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $sezioni_menu;
$oField->label = "Sezione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->label = "Anno inizio";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";
$oField->label = "Anno fine";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

//on_do_action
function checkRelations($oRecord, $frmAction) {
    $id_ambito = $oRecord->key_fields["ID_ambito"]->value->getValue();
    if(isset($id_ambito) && $id_ambito != "") {
        $ambito = new ValutazioniAmbito($id_ambito);
    }

    switch($frmAction) {
        case "confirmdelete":

            if(!$ambito->delete()) {
                return CoreHelper::setError($oRecord,"L'elemento non può essere cancellato");
            }
            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "L'elemento non può essere modificato";
            if(!$ambito->canDelete()) {
                $non_editable_fields = array(
                    "ID_sezione" => $ambito->id_sezione,
                    "anno_inizio" => $ambito->anno_inizio,
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }

            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();
            if($anno_fine) {
                if($ambito->hasRelationsAfterAnnoFine($anno_fine)) {
                    return CoreHelper::setError($oRecord, "Anno fine non valido: l'ambito ha riferimenti ad altri elementi successivamente all'anno fine impostato");
                }
            }
            break;
    }
}