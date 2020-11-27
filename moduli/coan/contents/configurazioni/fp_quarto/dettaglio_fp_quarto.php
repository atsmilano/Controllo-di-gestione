<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_fp_quarto]"])) {
    $isEdit = true;
    $id_fp_quarto = $_REQUEST["keys[ID_fp_quarto]"];

    try {
        $fp_quarto = new CoanFpQuarto($id_fp_quarto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fp-quarto-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Fp quarto livello";
$oRecord->resources[] = "fp-quarto";
$oRecord->src_table  = "coan_fp_quarto";
$oRecord->allow_delete = !$isEdit || ($isEdit && $fp_quarto->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_quarto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Number";
$oField->label = "Codice";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$fp_terzo_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach (CoanFpTerzo::getAll(array(), $order) as $item) {
    $fp_terzo_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." - ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_terzo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $fp_terzo_select;
$oField->required = true;
$oField->label = "Fp Terzo";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_terzo = $oRecord->form_fields["ID_fp_terzo"]->value->getValue();
            
            if (!empty(CoanFpQuarto::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_terzo" => $id_fp_terzo
                ]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_fp_quarto = $oRecord->key_fields["ID_fp_quarto"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_terzo = $oRecord->form_fields["ID_fp_terzo"]->value->getValue();
            
            foreach (CoanFpQuarto::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_terzo" => $id_fp_terzo
                ]) as $item) {
                if ($id_fp_quarto != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_fp_quarto = $oRecord->key_fields["ID_fp_quarto"]->value->getValue();
            $fp_quarto = new CoanFpQuarto($id_fp_quarto);
            
            if (!$fp_quarto->delete()) {
                CoreHelper::setError($oRecord, "Fp associato ad un fp utilizzato in un conto: impossibile eliminare.");
            }
            
            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record
            
            break;
    }
}