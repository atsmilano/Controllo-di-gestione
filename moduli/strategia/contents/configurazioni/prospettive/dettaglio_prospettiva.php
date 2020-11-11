<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_prospettiva]"])) {
    $isEdit = true;
    $id_prospettiva = $_REQUEST["keys[ID_prospettiva]"];

    try {
        $prospettiva = new StrategiaProspettiva($id_prospettiva);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "prospettiva-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " prospettiva strategica";
$oRecord->resources[] = "prospettiva";
$oRecord->src_table  = "strategia_prospettiva";
$oRecord->allow_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_prospettiva";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oField->required = true;
$oRecord->addContent($oField);

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

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";
$oField->label = "Anno termine";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkRelations");

$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $nome = $oRecord->form_fields["nome"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_termine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (StrategiaProspettiva::getAll(["nome" => $nome]) as $prospettiva) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $prospettiva->anno_introduzione, $prospettiva->anno_termine
                )) {
                    CoreHelper::setError($oRecord, "Nome già utilizzato come prospettiva strategica per l'intervallo definito.");
                    break;
                }
            }
            
            break;
        case "update":
            $id_prospettiva = $oRecord->key_fields["ID_prospettiva"]->value->getValue();
            $nome = $oRecord->form_fields["nome"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_termine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (StrategiaProspettiva::getAll(["nome" => $nome]) as $prospettiva) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $prospettiva->anno_introduzione, $prospettiva->anno_termine
                ) && $id_prospettiva != $prospettiva->id) {
                    CoreHelper::setError($oRecord, "Nome già utilizzato come prospettiva strategica per l'intervallo definito.");

                    break;
                }
            }
            
            break;
    }
}