<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"];

if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze specifiche per il CdR.");	
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_profilo]"])) {
    $isEdit = true;
    try {
        $profilo = new MappaturaCompetenze\Profilo($_REQUEST["keys[ID_profilo]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "profilo-modify";
$oRecord->title = $isEdit ? "Modifica profilo": "Nuovo profilo";
$oRecord->resources[] = "profilo";
$oRecord->src_table  = "competenze_profilo";
//$isDeletable = !$isEdit || ($isEdit && $profilo->canDelete());
//$oRecord->allow_delete = $isDeletable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_profilo";
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
$oField->id = "data_introduzione";
$oField->label = "Data introduzione"; 
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->label = "Data termine"; 
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["matricola_responsabile"] = new ffData($user->matricola_utente_selezionato, "Text");
$oRecord->insert_additional_fields["codice_cdr"] = new ffData($cdr->codice, "Text");

//$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);
/*
function checkRelations($oRecord, $frmAction) {
    $id_profilo = $oRecord->key_fields["ID_profilo"]->value->getValue();
    if (isset($id_profilo) && $id_profilo != "") {
        $profilo = new ObiettiviArea($id_profilo);
    }

    switch ($frmAction) {
        case "confirmdelete":
            if (!$profilo->delete()) {
                return CoreHelper::setError($oRecord,
                    "L'area selezionata NON può essere eliminata");
            }

            $oRecord->skip_action = true;
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
            
            $obiettivi_area = ObiettiviObiettivo::getAll(['ID_profilo' => $profilo->id]);
            $check_result = ObiettiviObiettivo::checkVincoliAnniConfigurazioni(
                $anno_introduzione, $anno_termine, $obiettivi_area
            );
            if(!empty($check_result)) {
                CoreHelper::setError($oRecord, $check_result);
            }

            break;
    }
}*/