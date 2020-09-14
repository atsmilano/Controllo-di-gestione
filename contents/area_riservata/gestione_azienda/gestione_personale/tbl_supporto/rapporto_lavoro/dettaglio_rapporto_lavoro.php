<?php
$isEdit = false;
$canEdit = false;
if (isset($_REQUEST["keys[id_rapporto_lavoro]"])) {
    $isEdit = true;
    $id_rapporto_lavoro = $_REQUEST["keys[id_rapporto_lavoro]"];
    
    try {
        $rapporto_lavoro = new RapportoLavoro($id_rapporto_lavoro);
        
        $canEdit = checkRapportoLavoroEditable($id_rapporto_lavoro);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "rapporto-lavoro-modify";
$oRecord->title = $isEdit ? "Modifica rapporto lavoro" : "Nuovo rapporto lavoro";
$oRecord->resources[] = "rapporto-lavoro";
$oRecord->src_table  = "rapporto_lavoro";
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
$oField->id = "id_rapporto_lavoro";
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

$oField = ffField::factory($cm->oPage);
$oField->id = "part_time";
$oField->label = "Part-Time";
if ($isEdit && !$canEdit) {
    $oField->base_type = "Text";
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->default_value = $rapporto_lavoro->part_time == 1 ? new ffData("Si", "Text")  : new ffData("No", "Text");
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

$oRecord->addEvent("on_do_action", "validateAction");
$cm->oPage->addContent($oRecord);

function validateAction($oRecord, $frmAction) {
    switch ($frmAction) {
        case "confirmdelete":
        case "delete":
        case "update":
            $id_rapporto_lavoro = $oRecord->key_fields["id_rapporto_lavoro"]->value->getValue();
            $error_msg = "Impossibile modifica/eliminare il rapporto lavoro con ID $id_rapporto_lavoro perché in uso";
            $isEditable = checkRapportoLavoroEditable($id_rapporto_lavoro);
            if (!$isEditable) {                
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            break;
    }
}

function checkRapportoLavoroEditable($id_rapporto_lavoro) {
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_rapporto_lavoro" => $id_rapporto_lavoro));
    if (empty($carriera_personale_list)) {
        return true;
    }
    
    return false;
}