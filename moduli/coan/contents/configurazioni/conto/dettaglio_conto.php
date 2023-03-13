<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_conto]"])) {
    $isEdit = true;
    $id_conto = $_REQUEST["keys[ID_conto]"];

    try {
        $conto = new CoanConto($id_conto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "conto-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Conto";
$oRecord->resources[] = "conto";
$oRecord->src_table  = "coan_conto";
$oRecord->allow_delete = !$isEdit || ($isEdit && $conto->isDeletable());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_conto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$fp_quarto_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach (CoanFpQuarto::getAll(array(), $order) as $item) {
    $fp_quarto_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." - ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_quarto";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $fp_quarto_select;
$oField->required = true;
$oField->label = "Fp quarto";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_quarto = $oRecord->form_fields["ID_fp_quarto"]->value->getValue();
            
            if (!empty(CoanConto::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_quarto" => $id_fp_quarto
                ]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_conto = $oRecord->key_fields["ID_conto"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_quarto = $oRecord->form_fields["ID_fp_quarto"]->value->getValue();
            
            foreach (CoanConto::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_quarto" => $id_fp_quarto
                ]) as $item) {
                if ($id_conto != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_conto = $oRecord->key_fields["ID_conto"]->value->getValue();
            $conto = new CoanConto($id_conto);
            
            if (!$conto->isDeletable()) {
                CoreHelper::setError($oRecord, "Conto utilizzato in consuntivo periodo: impossibile eliminare.");   
            }
            else {
                $conto->delete();
            }
            
            $oRecord->skip_action = true;
            
            break;
    }
}