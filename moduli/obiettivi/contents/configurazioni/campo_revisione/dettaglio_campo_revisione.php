<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID]"])) {
    $isEdit = true;
    try {
        $campo_revisione = new ObiettiviCampoRevisione($_REQUEST["keys[ID]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "campo-revisione-modify";
$oRecord->title = $isEdit ? "Modifica campo revisione '$campo_revisione->nome'": "Nuovo campo revisione";
$oRecord->resources[] = "campo-revisione";
$oRecord->src_table  = "obiettivi_campo_revisione";
$oRecord->allow_delete = !$isEdit || ($isEdit && $campo_revisione->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oField->required = true;
$oRecord->addContent($oField);

//detail scelte per il campo
$oRecord->addContent(null, true, "scelte");
$oRecord->groups["parametri"]["title"] = "Scelte";
   
//detail parametri
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DeatailScelte";
$oDetail->title = "Scelte";
$oDetail->src_table = "obiettivi_scelta_campo_revisione";
//il secondo ID è il field del record
$oDetail->fields_relationship = array("ID_campo_revisione" => "ID");
$oDetail->order_default = "descrizione";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_scelta";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oDetail->addContent($oField); 

$oRecord->addContent($oDetail, "scelte");
$cm->oPage->addContent($oDetail);

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {
    $id = $oRecord->key_fields["ID"]->value->getValue();
    if (isset($id) && $id != "") {
        $campo_revisione = new ObiettiviCampoRevisione($id);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$campo_revisione->delete()) {
                return CoreHelper::setError($oRecord,
                    "Il campo revisione NON può essere eliminato.");
            }

            $oRecord->skip_action = true;
        break;        
    }
}