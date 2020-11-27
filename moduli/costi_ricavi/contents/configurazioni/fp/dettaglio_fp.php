<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_fp]"])) {
    $isEdit = true;
    $id_fp = $_REQUEST["keys[ID_fp]"];

    try {
        $fp = new CostiRicaviFp($id_fp);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fp-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Fattore Produttivo";
$oRecord->resources[] = "fp";
$oRecord->src_table  = "costi_ricavi_fp";
$oRecord->allow_delete = !$isEdit || ($isEdit && $fp->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp";
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
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            if (!empty(CostiRicaviFp::getAll(["codice" => $codice, "descrizione" => $descrizione]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_fp = $oRecord->key_fields["ID_fp"]->value->getValue();
            
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            
            foreach (CostiRicaviFp::getAll(["codice" => $codice, "descrizione" => $descrizione]) as $item) {
                if ($id_fp != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_fp = $oRecord->key_fields["ID_fp"]->value->getValue();
            $fp = new CostiRicaviFp($id_fp);
            
            if (!$fp->canDelete()) {
                CoreHelper::setError($oRecord, "Impossibile eliminare Fattore Produttivo perché in uso");   
            }
            break;
    }
}
