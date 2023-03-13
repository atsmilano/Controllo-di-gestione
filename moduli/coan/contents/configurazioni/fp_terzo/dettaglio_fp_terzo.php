<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_fp_terzo]"])) {
    $isEdit = true;
    $id_fp_terzo = $_REQUEST["keys[ID_fp_terzo]"];

    try {
        $fp_terzo = new CoanFpTerzo($id_fp_terzo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fp-terzo-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Fp terzo livello";
$oRecord->resources[] = "fp-terzo";
$oRecord->src_table  = "coan_fp_terzo";
$oRecord->allow_delete = !$isEdit || ($isEdit && $fp_terzo->isDeletable());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_terzo";
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

$fp_secondo_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach (CoanFpSecondo::getAll(array(), $order) as $item) {
    $fp_secondo_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." - ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_secondo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $fp_secondo_select;
$oField->required = true;
$oField->label = "Fp Secondo";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_secondo = $oRecord->form_fields["ID_fp_secondo"]->value->getValue();
            
            if (!empty(CoanFpTerzo::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_secondo" => $id_fp_secondo
                ]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }
            
            break;
        case "update":
            $id_fp_terzo = $oRecord->key_fields["ID_fp_terzo"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_secondo = $oRecord->form_fields["ID_fp_secondo"]->value->getValue();
            
            foreach (CoanFpTerzo::getAll([
                    "codice" => $codice, 
                    "descrizione" => $descrizione, 
                    "ID_fp_secondo" => $id_fp_secondo
                ]) as $item) {
                if ($id_fp_terzo != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_fp_terzo = $oRecord->key_fields["ID_fp_terzo"]->value->getValue();
            $fp_terzo = new CoanFpTerzo($id_fp_terzo);
            
            if (!$fp_terzo->isDeletable()) {
                CoreHelper::setError($oRecord, "Fp associato ad un fp utilizzato in un conto: impossibile eliminare.");
            }
            else {
                $fp_terzo->delete();
            }
            
            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record
            
            break;
    }
}