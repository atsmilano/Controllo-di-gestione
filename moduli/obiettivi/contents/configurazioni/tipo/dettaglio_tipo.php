<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_tipo]"])) {
    $isEdit = true;
    $id_tipo = $_REQUEST["keys[ID_tipo]"];
    
    try {
        $tipo = new ObiettiviTipo($id_tipo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipo-modify";
$oRecord->title = $isEdit ? "Modifica tipo '$tipo->descrizione'": "Nuovo tipo";
$oRecord->resources[] = "tipo";
$oRecord->src_table  = "obiettivi_tipo";
$isEditable = !$isEdit || ($isEdit && $tipo->canDelete());
$oRecord->allow_delete = $isEditable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
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

$oField = ffField::factory($cm->oPage);
$oField->id = "class";
$oField->base_type = "Text";
$oField->widget = "colorpicker";
$oField->store_in_db = true;
$oField->label = "Colore evidenza";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $id_tipo = $oRecord->key_fields["ID_tipo"]->value->getValue();
    if (isset($id_tipo) && $id_tipo != "") {
        $tipo = new ObiettiviTipo($id_tipo);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$tipo->delete()) {
                return CoreHelper::setError($oRecord,
                    "Il tipo selezionato NON può essere eliminato");
            }

            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete
        break;
        case "insert":
            if (!ObiettiviObiettivo::isValidRangeAnno(
                    $oRecord->form_fields["anno_introduzione"]->value->getValue(),
                    $oRecord->form_fields["anno_termine"]->value->getValue()
                )) {
                CoreHelper::setError($oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione");
            }
            break;
        case "update":
            $anno_introduzione = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $anno_termine = $oRecord->form_fields["anno_termine"]->value->getValue();
            if (!ObiettiviObiettivo::isValidRangeAnno($anno_introduzione, $anno_termine)) {
                CoreHelper::setError($oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione");
            }
            
            $obiettivi_tipo = ObiettiviObiettivo::getAll(['ID_tipo' => $tipo->id]);
            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_tipo
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }

            break;
    }
}