<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID_risorse_finanziarie]"])) {
    $isEdit = true;
    $id_risorse_finanziarie = $_REQUEST["keys[ID_risorse_finanziarie]"];

    try {
        $risorsa_finanziaria = new ProgettiRisorseFinanziarieDisponibili($id_risorse_finanziarie);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "risorse-finanziarie-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuova") . " Risorsa Finanziaria Disponibile";
$oRecord->resources[] = "risorse-finanziarie";
$oRecord->src_table  = "progetti_risorse_finanziarie_disponibili";
$oRecord->allow_delete = !$isEdit || ($isEdit && $risorsa_finanziaria->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_risorse_finanziarie";
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
            if (!empty(ProgettiRisorseFinanziarieDisponibili::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Risorsa Finanziaria Disponibile già definito"
                );
            }
            
            break;
        case "update":
            $id_risorse_finanziarie = $oRecord->key_fields["ID_risorse_finanziarie"]->value->getValue();
            $risorsa_finanziaria = new ProgettiRisorseFinanziarieDisponibili($id_risorse_finanziarie);           
            
            // Univocità
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            if (!empty(ProgettiRisorseFinanziarieDisponibili::getAll(["descrizione" => $descrizione]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Risorsa Finanziaria Disponibile già definito"
                );
            }
            break;
        case "delete":
        case "confirmdelete":
            $id_risorse_finanziarie = $oRecord->key_fields["ID_risorse_finanziarie"]->value->getValue();
            $risorsa_finanziaria = new ProgettiRisorseFinanziarieDisponibili($id_risorse_finanziarie);
            
            if (!$risorsa_finanziaria->canDelete()) {
                CoreHelper::setError(
                    $oRecord, 
                    "Impossibile eliminare Risorsa Finanziaria Disponibile perché in uso"
                );   
            }
            break;
    }
}