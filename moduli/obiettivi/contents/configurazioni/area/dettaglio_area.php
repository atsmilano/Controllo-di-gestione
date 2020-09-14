<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_area]"])) {
    $isEdit = true;
    $id_area = $_REQUEST["keys[ID_area]"];
    
    try {
        $area = new ObiettiviArea($id_area);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "area-modify";
$oRecord->title = $isEdit ? "Modifica area '$area->descrizione'": "Nuova area";
$oRecord->resources[] = "area";
$oRecord->src_table  = "obiettivi_area";
$isDeletable = !$isEdit || ($isEdit && $area->canDelete());
$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area";
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

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $id_area = $oRecord->key_fields["ID_area"]->value->getValue();
    if (isset($id_area) && $id_area != "") {
        $area = new ObiettiviArea($id_area);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$area->delete()) {
                return CoreHelper::setError($oRecord,
                    "L'area selezionata NON può essere eliminata");
            }

            $oRecord->skip_action = true;
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
            
            $obiettivi_area = ObiettiviObiettivo::getAll(['ID_area' => $area->id]);
            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_area
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }

            break;
    }
}