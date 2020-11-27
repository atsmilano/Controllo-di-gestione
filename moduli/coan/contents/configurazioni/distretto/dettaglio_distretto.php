<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_distretto]"])) {
    $isEdit = true;
    $id_distretto = $_REQUEST["keys[ID_distretto]"];

    try {
        $distretto = new CoanDistretto($id_distretto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "distretto-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Distretto";
$oRecord->resources[] = "distretto";
$oRecord->src_table  = "coan_distretto";
$oRecord->allow_delete = !$isEdit || ($isEdit && $distretto->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_distretto";
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

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            if (!empty(CoanDistretto::getAll(["codice" => $codice, "descrizione" => $descrizione]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_distretto = $oRecord->key_fields["ID_distretto"]->value->getValue();
            
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            foreach(CoanDistretto::getAll(["codice" => $codice, "descrizione" => $descrizione]) as $item) {
                if ($id_distretto != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_distretto = $oRecord->key_fields["ID_distretto"]->value->getValue();
            $distretto = new CoanDistretto($id_distretto);
            
            if (!$distretto->canDelete()) {
                CoreHelper::setError($oRecord, "Impossibile eliminare Distretto perché in uso.");   
            }
            
            break;
    }
}