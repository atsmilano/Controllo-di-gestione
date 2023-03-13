<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_cdc_standard_regionale]"])) {
    $isEdit = true;
    $id_cdc_standard_regionale = $_REQUEST["keys[ID_cdc_standard_regionale]"];

    try {
        $cdc_standard_regionale = new CoanCdcStandardRegionale($id_cdc_standard_regionale);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdc-standard-regionale-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Cdc standard regionale";
$oRecord->resources[] = "cdc-standard-regionale";
$oRecord->src_table  = "coan_cdc_standard_regionale";
$oRecord->allow_delete = !$isEdit || ($isEdit && $cdc_standard_regionale->isDeletable());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdc_standard_regionale";
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
            
            if (!empty(CoanCdcStandardRegionale::getAll(["codice" => $codice, "descrizione" => $descrizione]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_cdc_standard_regionale = $oRecord->key_fields["ID_cdc_standard_regionale"]->value->getValue();
            
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            foreach(CoanCdcStandardRegionale::getAll(["codice" => $codice, "descrizione" => $descrizione]) as $item) {
                if ($id_cdc_standard_regionale != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_cdc_standard_regionale = $oRecord->key_fields["ID_cdc_standard_regionale"]->value->getValue();
            $cdc_standard_regionale = new CoanCdcStandardRegionale($id_cdc_standard_regionale);
            
            if (!$cdc_standard_regionale->isDeletable()) {
                CoreHelper::setError($oRecord, "Cdc standard regionale associato ad un CdC: impossibile eliminare.");   
            }
            
            break;
    }
}