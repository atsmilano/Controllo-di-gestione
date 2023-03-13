<?php

$isEdit = false;
if (isset($_REQUEST["keys[ID_fp_primo]"])) {
    $isEdit = true;
    $id_fp_primo = $_REQUEST["keys[ID_fp_primo]"];

    try {
        $fp_primo = new CoanFpPrimo($id_fp_primo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "fp-primo-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Fp primo livello";
$oRecord->resources[] = "fp-primo";
$oRecord->src_table = "coan_fp_primo";
$oRecord->allow_delete = !$isEdit || ($isEdit && $fp_primo->isDeletable());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp_primo";
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

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction)
{
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();

            if (!empty(CoanFpPrimo::getAll(["codice" => $codice, "descrizione" => $descrizione]))) {
                CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
            }

            break;
        case "update":
            $id_fp_primo = $oRecord->key_fields["ID_fp_primo"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $descrizione = $oRecord->form_fields["descrizione"]->value->getValue();

            foreach (CoanFpPrimo::getAll(["codice" => $codice, "descrizione" => $descrizione]) as $item) {
                if ($id_fp_primo != $item->id) {
                    CoreHelper::setError($oRecord, "Codice e descrizione già in uso.");
                }
            }

            break;
        case "delete":
        case "confirmdelete":
            $id_fp_primo = $oRecord->key_fields["ID_fp_primo"]->value->getValue();
            $fp_primo = new CoanFpPrimo($id_fp_primo);

            if (!$fp_primo->isDeletable()) {
                CoreHelper::setError($oRecord, "Fp associato ad un conto: impossibile eliminare.");
            }
            else {
                $fp_primo->delete();
            }

            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record

            break;
    }
}
