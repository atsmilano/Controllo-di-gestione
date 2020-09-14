<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_punteggio_item]"])) {
    $isEdit = true;
    $id_punteggio_item = $_REQUEST["keys[ID_punteggio_item]"];

    try {
        $punteggio_item = new ValutazioniPunteggioItem($id_punteggio_item);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

if (isset($_REQUEST["keys[ID_item]"])) {
    $id_item = $_REQUEST["keys[ID_item]"];

    try {
        $item = new ValutazioniItem($id_item);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
} else {
    ffErrorHandler::raise("ID_item non definito");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "punteggio-item-modify";
$oRecord->title = $isEdit ? "Modifica punteggio item" : "Nuovo punteggio item";
$oRecord->resources[] = "punteggio-item";
$oRecord->src_table  = "valutazioni_punteggio_item";
$editable = !$isEdit || ($isEdit && $punteggio_item->canDelete());
$oRecord->allow_delete = $editable;
$oRecord->allow_update = $editable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_punteggio_item";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "punteggio";
$oField->base_type = "Text";
$oField->label = "Punteggio";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkRelations");
$oRecord->insert_additional_fields["ID_item"] = new ffData($id_item, "Number");

$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $id_punteggio_item = $oRecord->key_fields["ID_punteggio_item"]->value->getValue();
    if(isset($id_punteggio_item) && $id_punteggio_item != "") {
        $punteggio_item = new ValutazioniPunteggioItem($id_punteggio_item);
    }

    switch($frmAction) {
        case "confirmdelete":
            if(!$punteggio_item->delete()) {
                return CoreHelper::setError($oRecord,"Il punteggio dell'item selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true;
            break;
        case "update":
            if(!$punteggio_item->canDelete()) {
                return CoreHelper::setError($oRecord,"Il punteggio dell'item selezionato non può essere modificato.");
            }
            break;
    }
}