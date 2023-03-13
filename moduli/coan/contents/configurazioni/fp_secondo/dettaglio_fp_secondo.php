<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID_fp_secondo]"])) {
    $isEdit = true;
    $id_fp_secondo = $_REQUEST["keys[ID_fp_secondo]"];

    try {
        $fp_secondo = new CoanFpSecondo($id_fp_secondo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fp-secondo-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Fp secondo livello";
$oRecord->resources[] = "fp-secondo";
$oRecord->src_table = "coan_fp_secondo";
$oRecord->allow_delete = !$isEdit || ($isEdit && $fp_secondo->isDeletable());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_secondo";
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

$fp_primo_select = array();
$order = array(array("fieldname" => "codice", "direction" => "ASC"));
foreach (CoanFpPrimo::getAll(array(), $order) as $item) {
    $fp_primo_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice . " - " . $item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_primo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $fp_primo_select;
$oField->required = true;
$oField->label = "Fp Primo";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction)
{
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_primo = $oRecord->form_fields["ID_fp_primo"]->value->getValue();

            if (!empty(CoanFpSecondo::getAll([
                                "codice" => $codice,
                                "descrizione" => $descrizione,
                                "ID_fp_primo" => $id_fp_primo
                            ]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }

            break;
        case "update":
            $id_fp_secondo = $oRecord->key_fields["ID_fp_secondo"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();
            $id_fp_primo = $oRecord->form_fields["ID_fp_primo"]->value->getValue();

            foreach (CoanFpSecondo::getAll([
                "codice" => $codice,
                "descrizione" => $descrizione,
                "ID_fp_primo" => $id_fp_primo
            ]) as $item) {
                if ($id_fp_secondo != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }

            break;
        case "delete":
        case "confirmdelete":
            $id_fp_secondo = $oRecord->key_fields["ID_fp_secondo"]->value->getValue();
            $fp_secondo = new CoanFpSecondo($id_fp_secondo);

            if (!$fp_secondo->isDeletable()) {
                CoreHelper::setError($oRecord, "Fp associato ad un fp utilizzato in un conto: impossibile eliminare.");
            }
            else {
                $fp_secondo->delete();
            }

            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record

            break;
    }
}
