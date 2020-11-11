<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_descrizione_introduttiva]"])) {
    $isEdit = true;
    $id_prospettiva = $_REQUEST["keys[ID_descrizione_introduttiva]"];

    try {
        $prospettiva = new StrategiaDescrizioneIntroduttiva($id_prospettiva);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "desc-intro-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuova") . " descrizione introduttiva";
$oRecord->resources[] = "desc-intro";
$oRecord->src_table  = "strategia_descrizione_introduttiva";
$oRecord->allow_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_descrizione_introduttiva";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->widget = "ckeditor";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $anno = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $isAnnoIntroduzioneUnivoco = empty(StrategiaDescrizioneIntroduttiva::getAll(["anno_introduzione" => $anno]));
            if (!$isAnnoIntroduzioneUnivoco) {
                CoreHelper::setError($oRecord, "Descrizione già definita per l'anno");
            }
            
            break;
        case "update":
            $id_descrizione = $oRecord->key_fields["ID_descrizione_introduttiva"]->value->getValue();
            $descrizione_introduttiva = new StrategiaDescrizioneIntroduttiva($id_descrizione);
            
            $update_anno = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            
            if ($update_anno != $descrizione_introduttiva->anno_introduzione) {
                $isAnnoIntroduzioneUnivoco = empty(StrategiaDescrizioneIntroduttiva::getAll(["anno_introduzione" => $update_anno]));
                if (!$isAnnoIntroduzioneUnivoco) {
                    CoreHelper::setError($oRecord, "Descrizione già definita per l'anno");
                }
            }
            
            break;
    }
}