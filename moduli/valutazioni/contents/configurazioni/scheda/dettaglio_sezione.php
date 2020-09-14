<?php
if (isset($_REQUEST["keys[ID_sezione]"])) {
    $isEdit = true;
    $id_sezione = $_REQUEST["keys[ID_sezione]"];

    try {
        $sezione = new ValutazioniSezione($id_sezione);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "sezione-modify";
$oRecord->title = $isEdit ? "Modifica sezione '$sezione->descrizione'" : "Nuova sezione";
$oRecord->resources[] = "sezione";
$oRecord->src_table  = "valutazioni_sezione";
$editable = !$isEdit || ($isEdit && $sezione->canDelete());
$oRecord->allow_delete = $editable;

CoreHelper::refreshTabOnDialogClose($oRecord->id);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sezione";
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

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

//on_do_action
function checkRelations($oRecord, $frmAction) {
    $id_sezione = $oRecord->key_fields["ID_sezione"]->value->getValue();
    if(isset($id_sezione) && $id_sezione != "") {
        $sezione = new ValutazioniSezione($id_sezione);
    }

    switch($frmAction) {
        case "confirmdelete":
            //In questo particolare caso, tale condizione non dovrebbe essere mai vera (vincoli grafici)
            if(!$sezione->delete()) {
                return CoreHelper::setError($oRecord,"La sezione selezionata non può essere eliminata.");
            }
            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete del record
            break;
    }
}