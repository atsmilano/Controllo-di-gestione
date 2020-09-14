<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_origine]"])) {
    $isEdit = true;
    $id_origine = $_REQUEST["keys[ID_origine]"];
    
    try {
        $origine = new ObiettiviOrigine($id_origine);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "origine-modify";
$oRecord->title = $isEdit ? "Modifica origine '$origine->descrizione'": "Nuova origine";
$oRecord->resources[] = "origine";
$oRecord->src_table  = "obiettivi_origine";
$isEditable = !$isEdit || ($isEdit && $origine->canDelete());
$oRecord->allow_delete = $isEditable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_origine";
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
    $id_origine = $oRecord->key_fields["ID_origine"]->value->getValue();
    if (isset($id_origine) && $id_origine != "") {
        $origine = new ObiettiviOrigine($id_origine);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$origine->delete()) {
                return CoreHelper::setError($oRecord,
                    "L'origine selezionata NON può essere eliminata");
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
            
            $obiettivi_origine = ObiettiviObiettivo::getAll(['ID_origine' => $origine->id]);
            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_origine
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }

            break;
    }
}