<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_area_risultato]"])) {
    $isEdit = true;
    $id_area_risultato = $_REQUEST["keys[ID_area_risultato]"];
    
    try {
        $area_risultato = new ObiettiviAreaRisultato($id_area_risultato);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "area-risultato-modify";
$oRecord->title = $isEdit ? "Modifica area risultato '$area->descrizione'": "Nuova area risultato";
$oRecord->resources[] = "area-risultato";
$oRecord->src_table  = "obiettivi_area_risultato";
$isEditable = !$isEdit || ($isEdit && $area_risultato->canDelete());
$oRecord->allow_delete = $isEditable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_risultato";
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
    $id_area_risultato = $oRecord->key_fields["ID_area_risultato"]->value->getValue();
    if (isset($id_area_risultato) && $id_area_risultato != "") {
        $area_risultato = new ObiettiviAreaRisultato($id_area_risultato);
    }
    
    switch ($frmAction) {
        case "confirmdelete": 
            if (!$area_risultato->delete()) {
                return CoreHelper::setError($oRecord,
                    "L'area risultato selezionata NON può essere eliminata");
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
            $obiettivi_area_risultato = ObiettiviObiettivo::getAll(['ID_area_risultato' => $area_risultato->id]);
            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_area_risultato
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }
            break;
    }    
}