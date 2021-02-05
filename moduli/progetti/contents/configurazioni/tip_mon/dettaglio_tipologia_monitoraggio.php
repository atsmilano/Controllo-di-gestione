<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID_tipologia_monitoraggio]"])) {
    $isEdit = true;
    $id_tipologia_monitoraggio = $_REQUEST["keys[ID_tipologia_monitoraggio]"];

    try {
        $tipologia_monitoraggio = new ProgettiTipologiaMonitoraggio($id_tipologia_monitoraggio);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipologia-monitoraggio-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuova") . " Tipologia Monitoraggio";
$oRecord->resources[] = "tipologia-monitoraggio";
$oRecord->src_table  = "progetti_tipologia_monitoraggio";
$oRecord->allow_delete = !$isEdit || ($isEdit && $tipologia_monitoraggio->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipologia_monitoraggio";
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
            if (!empty(ProgettiTipologiaMonitoraggio::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Tipologia Monitoraggio già definito"
                );
            }
            
            break;
        case "update":
            $id_tipologia_monitoraggio = $oRecord->key_fields["ID_tipologia_monitoraggio"]->value->getValue();
            $tipologia_monitoraggio = new ProgettiTipologiaMonitoraggio($id_tipologia_monitoraggio);
            
            // Univocità
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            if (!empty(ProgettiTipologiaMonitoraggio::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Tipologia Monitoraggio già definito"
                );
            }
            break;
        case "delete":
        case "confirmdelete":
            $id_tipologia_monitoraggio = $oRecord->key_fields["ID_tipologia_monitoraggio"]->value->getValue();
            $tipologia_monitoraggio = new ProgettiTipologiaMonitoraggio($id_tipologia_monitoraggio);
            
            if (!$tipologia_monitoraggio->canDelete()) {
                CoreHelper::setError(
                    $oRecord, 
                    "Impossibile eliminare Tipologia Monitoraggio perché in uso"
                );   
            }
            break;
    }
}