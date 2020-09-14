<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_parere_azioni]"])) {
    $isEdit = true;
    $id_parere_azioni = $_REQUEST["keys[ID_parere_azioni]"];
    
    try {
        $parere_azioni = new ObiettiviParereAzioni($id_parere_azioni);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "parere-azioni-modify";
$oRecord->title = $isEdit ? "Modifica parere azioni '$parere_azioni->descrizione'": "Nuovo parere azioni";
$oRecord->resources[] = "parere-azioni";
$oRecord->src_table  = "obiettivi_parere_azioni";
$isEditable = !$isEdit || ($isEdit && $parere_azioni->canDelete());
$oRecord->allow_delete = $isEditable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_parere_azioni";
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
    $id_parere_azioni = $oRecord->key_fields["ID_parere_azioni"]->value->getValue();
    if (isset($id_parere_azioni) && $id_parere_azioni != "") {
        $parere_azioni = new ObiettiviParereAzioni($id_parere_azioni);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$parere_azioni->delete()) {
                return CoreHelper::setError($oRecord,
                    "Il parere azione selezionato NON può essere eliminato");
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
            
            $obiettivi_cdr_parere_azioni = ObiettiviObiettivoCdr::getAll(['ID_parere_azioni' => $parere_azioni->id]);
            foreach($obiettivi_cdr_parere_azioni as $obiettivo_cdr) {
                $obiettivi_parere_azioni[] = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
            }

            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_parere_azioni
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }

            break;
    }
}