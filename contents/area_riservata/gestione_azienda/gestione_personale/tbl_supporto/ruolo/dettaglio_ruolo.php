<?php
$isEdit = false;
$canEdit = false;
if (isset($_REQUEST["keys[id_ruolo]"])) {
    $isEdit = true;
    $id_ruolo = $_REQUEST["keys[id_ruolo]"];
    
    try {
        $ruolo = new Ruolo($id_ruolo);
        $canEdit = checkRuoloEditable($id_ruolo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ruolo-modify";
$oRecord->title = $isEdit ? "Modifica ruolo" : "Nuovo ruolo";
$oRecord->resources[] = "ruolo";
$oRecord->src_table  = "ruolo";
$oRecord->allow_update = false;
if (!$canEdit) {
    $oRecord->allow_delete = false;
    $oRecord->allow_update = false;
}
else {    
    $oRecord->allow_delete = true;
    $oRecord->allow_update = true;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "id_ruolo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
if ($isEdit && !$canEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateAction");
$cm->oPage->addContent($oRecord);

function validateAction($oRecord, $frmAction) {
    switch ($frmAction) {
        case "confirmdelete":
        case "delete":
        case "update":
            $id_ruolo = $oRecord->key_fields["id_ruolo"]->value->getValue();
            $error_msg = "Impossibile modifica/eliminare il ruolo con ID $id_ruolo perché in uso";
            $isEditable = checkRuoloEditable($id_ruolo);
            if (!$isEditable) {                
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            break;
    }
}

function checkRuoloEditable($id_ruolo) {
    $isEditable = true;
    $qualifica_interna_list = QualificaInterna::getAll(array("ID_ruolo" => $id_ruolo));
    
    //se il ruolo è utilizzato per almeno una qualifica_interna non sarà modificabile
    if (empty($qualifica_interna_list)) {
        $isEditable = true;
    }
    else {
        $isEditable = false;
    }    
    return $isEditable;
}