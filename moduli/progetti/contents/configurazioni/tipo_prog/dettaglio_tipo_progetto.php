<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_tipo_progetto]"])) {
    $isEdit = true;
    $id_tipo_progetto = $_REQUEST["keys[ID_tipo_progetto]"];

    try {
        $tipo_progetto = new ProgettiTipoProgetto($id_tipo_progetto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipo-progetto-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Tipo Progetto";
$oRecord->resources[] = "tipo-progetto";
$oRecord->src_table  = "progetti_tipo_progetto";
$oRecord->allow_delete = !$isEdit || ($isEdit && $tipo_progetto->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_progetto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->required = true;
$oField->label = "Codice";
$oRecord->addContent($oField);

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
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            
            // Univocità
            if (!empty(ProgettiTipoProgetto::getAll(["codice" => $codice]))) {
                CoreHelper::setError(
                    $oRecord, 
                    "Tipo Progetto già definito"
                );
            }
            
            break;
        case "update":
            $id_tipo_progetto = $oRecord->key_fields["ID_tipo_progetto"]->value->getValue();
            $tipo_progetto = new ProgettiTipoProgetto($id_tipo_progetto);
            
            // Univocità
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            if ($codice != $tipo_progetto->codice) {
                if (!empty(ProgettiTipoProgetto::getAll(["codice" => $codice]))) {
                    CoreHelper::setError(
                        $oRecord, 
                        "Tipo Progetto già definito"
                    );
                }
            }
            break;
        case "delete":
        case "confirmdelete":
            $id_tipo_progetto = $oRecord->key_fields["ID_tipo_progetto"]->value->getValue();
            $tipo_progetto = new ProgettiTipoProgetto($id_tipo_progetto);
            
            if (!$tipo_progetto->canDelete()) {
                CoreHelper::setError(
                    $oRecord, 
                    "Impossibile eliminare Tipo Progetto perché in uso"
                );   
            }
            break;
    }
}