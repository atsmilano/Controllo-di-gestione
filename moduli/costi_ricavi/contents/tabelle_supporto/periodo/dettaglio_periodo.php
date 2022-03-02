<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_periodo]"])) {
    $isEdit = true;
    $id_periodo = $_REQUEST["keys[ID_periodo]"];

    try {
        $periodo = new CostiRicaviPeriodo($id_periodo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Periodo";
$oRecord->resources[] = "periodo";
$oRecord->src_table  = "costi_ricavi_periodo";
$oRecord->allow_delete = !$isEdit || ($isEdit && $periodo->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$anno_budget_select = array();
foreach(AnnoBudget::getAll() as $item) {
    if ($item->attivo == 1){
        $anno_budget_select[] = array(
            new ffData($item->id, "Number"),
            new ffData($item->descrizione, "Text")
        );
    }    
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno_budget";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anno_budget_select;
$oField->required = true;
$oField->label = "Anno budget";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->required = true;
$oField->widget = "datepicker";
$oField->label = "Data riferimento inizio";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->required = true;
$oField->widget = "datepicker";
$oField->label = "Data riferimento fine";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_scadenza";
$oField->base_type = "Date";
$oField->required = true;
$oField->widget = "datepicker";
$oField->label = "Data scadenza";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->required = true;
$oField->label = "Descrizione";
$oRecord->addContent($oField);

$tipo_periodo_select = array();
foreach(CostiRicaviPeriodo::$tipo_periodo as $key => $value) {
    $tipo_periodo_select[] = array(
        new ffData($key, "Number"),
        new ffData($value, "Text"),
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_periodo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipo_periodo_select;
$oField->required = true;
$oField->label = "Tipologia periodo";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "delete":
        case "confirmdelete":
            $id_periodo = $oRecord->key_fields["ID_periodo"]->value->getValue();
            $periodo = new CostiRicaviPeriodo($id_periodo);
            
            if (!$periodo->canDelete()) {
                CoreHelper::setError($oRecord, "Periodo utilizzato per la rendicontazione: impossibile eliminare.");   
            }
            break;
    }
}
