<?php
$isEdit = false;
$canEdit = false;
if (isset($_REQUEST["keys[id_qualifica_interna]"])) {
    $isEdit = true;
    $id_qualifica_interna = $_REQUEST["keys[id_qualifica_interna]"];
    
    try {
        $qualifica_interna = new QualificaInterna($id_qualifica_interna);
        
        $canEdit = checkQualificaInternaEditable($id_qualifica_interna);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "qualifica-interna-modify";
$oRecord->title = $isEdit ? "Modifica qualifica interna" : "Nuovo qualifica interna";
$oRecord->resources[] = "qualifica-interna";
$oRecord->src_table  = "qualifica_interna";
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
$oField->id = "id_qualifica_interna";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
if ($isEdit && !$canEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

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

$oField = ffField::factory($cm->oPage);
$oField->id = "dirigente";
$oField->label = "Dirigente";
if ($isEdit && !$canEdit) {
    $oField->base_type = "Text";
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->default_value = $qualifica_interna->dirigente == 1 ? new ffData("Si", "Text")  : new ffData("No", "Text");
    $oField->store_in_db = false;
} else {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->control_type = "radio";
    $oField->multi_pairs = array(
        array(new ffData("1", "Number"), new ffData("Si", "Text")),
        array(new ffData("0", "Number"), new ffData("No", "Text")),
    );
    $oField->required = true;
}
$oRecord->addContent($oField);

foreach (Ruolo::getAll() as $ruolo) {
    $ruolo_select[] = array(
        new ffData($ruolo->id, "Number"),
        new ffData($ruolo->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "id_ruolo";
$oField->data_source = "ID_ruolo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $ruolo_select;
if ($isEdit) {
    $oField->default_value = new ffData($qualifica_interna->id_ruolo, "Number");
    $oField->data_type = "";
    if (!$canEdit) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
}
$oField->label = "Ruolo";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateAction");
$cm->oPage->addContent($oRecord);

function validateAction($oRecord, $frmAction) {
    switch ($frmAction) {
        case "confirmdelete":
        case "delete":
        case "update":
            $id_qualifica_interna = $oRecord->key_fields["id_qualifica_interna"]->value->getValue();
            $error_msg = "Impossibile modifica/eliminare la qualifica interna con ID $id_qualifica_interna perché in uso";
            $isEditable = checkQualificaInternaEditable($id_qualifica_interna);
            if (!$isEditable) {                
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            break;
    }
}

function checkQualificaInternaEditable($id_qualifica_interna) {
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_qualifica_interna" => $id_qualifica_interna));
    if (empty($carriera_personale_list)) {
        return true;
    }
    
    return false;
}