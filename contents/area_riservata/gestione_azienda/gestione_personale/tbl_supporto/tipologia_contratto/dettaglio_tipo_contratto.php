<?php
$isEdit = false;
$canEdit = false;

if (isset($_REQUEST["keys[id_tipo_contratto]"])) {
    $isEdit = true;
    $id_tipo_contratto = $_REQUEST["keys[id_tipo_contratto]"];
    
    try {
        $tipo_contratto = new TipoContratto($id_tipo_contratto);
        
        $canEdit = checkTipoContrattoEditable($id_tipo_contratto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipo-contratto-modify";
$oRecord->title = $isEdit ? "Modifica tipologia contratto" : "Nuovo tipo contratto";
$oRecord->resources[] = "tipo-contratto";
$oRecord->src_table  = "tipo_contratto";
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
$oField->id = "id_tipo_contratto";
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
            $id_tipo_contratto = $oRecord->key_fields["id_tipo_contratto"]->value->getValue();
            $error_msg = "Impossibile modifica/eliminare la tipologia contratto con ID $id_tipo_contratto perché in uso";
            $isEditable = checkTipoContrattoEditable($id_tipo_contratto);
            if (!$isEditable) {                
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            break;
    }
}

function checkTipoContrattoEditable($id_tipo_contratto) {    
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_tipo_contratto" => $id_tipo_contratto));
    if (empty($carriera_personale_list)) {
        return true;
    }    
    return false;
}