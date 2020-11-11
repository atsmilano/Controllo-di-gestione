<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID_territorio_applicazione]"])) {
    $isEdit = true;
    $id_territorio_applicazione = $_REQUEST["keys[ID_territorio_applicazione]"];

    try {
        $territorio_applicazione = new ProgettiTerritorioApplicazione($id_territorio_applicazione);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "territorio-applicazione-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Territorio Applicazione";
$oRecord->resources[] = "territorio-applicazione";
$oRecord->src_table  = "progetti_territorio_applicazione";
$oRecord->allow_delete = !$isEdit || ($isEdit && $territorio_applicazione->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_territorio_applicazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->required = true;
$oField->label = "Descrizione";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            // Univocità
            if (!empty(ProgettiTerritorioApplicazione::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Territorio Applicazione già definito"
                );
            }
            
            break;
        case "update":
            $id_territorio_applicazione = $oRecord->key_fields["ID_territorio_applicazione"]->value->getValue();
            $territorio_applicazione = new ProgettiTerritorioApplicazione($id_territorio_applicazione);           
            
            // Univocità
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            if (!empty(ProgettiTerritorioApplicazione::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Territorio Applicazione già definito"
                );
            }
            break;
        case "delete":
        case "confirmdelete":
            $id_territorio_applicazione = $oRecord->key_fields["ID_territorio_applicazione"]->value->getValue();
            $territorio_applicazione = new ProgettiTerritorioApplicazione($id_territorio_applicazione);
            
            if (!$territorio_applicazione->canDelete()) {
                CoreHelper::setError(
                    $oRecord, 
                    "Impossibile eliminare Territorio Applicazione perché in uso"
                );   
            }
            break;
    }
}