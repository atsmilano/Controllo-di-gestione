<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_periodo]"])) {
    $isEdit = true;
    $id_periodo = $_REQUEST["keys[ID_periodo]"];

    try {
        $periodo = new CoanPeriodo($id_periodo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Periodo";
$oRecord->resources[] = "periodo";
$oRecord->src_table  = "coan_periodo";
$oRecord->allow_delete = !$isEdit || ($isEdit && $periodo->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$anno_budget = array();
foreach (AnnoBudget::getAll() as $item) {
    $anno_budget[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno_budget";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anno_budget;
$oField->required = true;
$oField->label = "Anno budget";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data inizio";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data fine";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
        case "update":
            $id_periodo = $oRecord->key_fields["ID_periodo"]->value->getValue();
            $periodo = new CoanPeriodo($id_periodo);
            
            $id_anno_budget = $oRecord->form_fields["ID_anno_budget"]->value->getValue();
            $ordinamento_anno = $oRecord->form_fields["ordinamento_anno"]->value->getValue();
            
            foreach (CoanPeriodo::getAll(["ID_anno_budget" => $id_anno_budget, "ordinamento_anno" => $ordinamento_anno]) as $item) {
                if ($item->id != $id_periodo) {
                    CoreHelper::setError($oRecord, "Ordinamento anno già utilizzato nell'anno di budget.");       
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_periodo = $oRecord->key_fields["ID_periodo"]->value->getValue();
            $periodo = new CoanPeriodo($id_periodo);
            
            if (!$periodo->canDelete()) {
                CoreHelper::setError($oRecord, "Impossibile eliminare Periodo perché in uso.");
            }
            
            break;
    }
}