<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_anno]"])) {
    $isEdit = true;
    $id_anno = $_REQUEST["keys[ID_anno]"];

    try {
        $anno = new StrategiaAnno($id_anno);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "anno-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " anno";
$oRecord->resources[] = "anno";
$oRecord->src_table  = "strategia_anno";
$oRecord->allow_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$anno_budget_select = array();
foreach (AnnoBudget::getAll() AS $anno_budget) {
    $isAnnoBudgetUnivoco = empty(StrategiaAnno::getAll(["ID_anno_budget" => $anno_budget->id]));
    
    if ($isAnnoBudgetUnivoco || ($anno_budget->id == $anno->id_anno_budget)) {
        $anno_budget_select[] = array(
            new ffData ($anno_budget->id, "Number"),
            new ffData ($anno_budget->descrizione, "Text")
        );
    }
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno_budget";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anno_budget_select;
$oField->label = "Anno budget";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_chiusura_definizione_strategia";
$oField->base_type = "Date";
$oField->label = "Data chiusura definizione strategia";
$oField->widget = "datepicker"; 
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $id_anno_budget = new ffData($oRecord->form_fields["ID_anno_budget"]->value->getValue(), "Number");
            
            $isAnnoBudgetUnivoco = empty(StrategiaAnno::getAll(["ID_anno_budget" => $id_anno_budget]));
            if (!$isAnnoBudgetUnivoco) {
                CoreHelper::setError($oRecord, "Anno di budget già configurato");
            }
            
            break;
        case "update":
            // Recupero la strategia corrente
            $id_strategia_anno = new ffData($oRecord->key_fields["ID_anno"]->value->getValue(), "Number");
            $strategia_anno = new StrategiaAnno($id_strategia_anno);
            
            // Recupero il nuovo anno di budget
            $id_anno_budget = $oRecord->form_fields["ID_anno_budget"]->value->getValue();

            // Se anno_budget corrente == nuovo anno_budget ALLORA non controllo l'univocità
            // perché non ho apportato modifiche
            if ($strategia_anno->id_anno_budget != $id_anno_budget) {
                $isAnnoBudgetUnivoco = empty(StrategiaAnno::getAll(["ID_anno_budget" => $id_anno_budget]));
                if (!$isAnnoBudgetUnivoco) {
                    CoreHelper::setError($oRecord, "Anno di budget già configurato");
                }
            }
            
            break;
    }
    
}