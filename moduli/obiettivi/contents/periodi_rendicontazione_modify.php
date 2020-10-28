<?php
$isEdit = false;
$user = LoggedUser::Instance();
//verifica privilegi utente
if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei periodi di rendicontazione.");
}

$anno = $cm->oPage->globals["anno"]["value"];

$cm->oPage->addContent("<div id='periodo_rendicontazione_modify'>");
if (isset($_REQUEST["keys[ID]"])) {
    $isEdit = true;
    $id_periodo_rendicontazione = $_REQUEST["keys[ID]"];
    $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($_REQUEST["keys[ID]"]);
    $cm->oPage->addContent("<a id='estrazione' class='estrazione link_estrazione' href='./estrazioni/estrazione_rendicontazioni.php?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "periodo=" . $_REQUEST["keys[ID]"] . "'>Estrazione rendicontazioni periodo .xls</a><br>");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-rendicontazione-modify";
$oRecord->title = "Periodo rendicontazione";
$oRecord->resources[] = "periodo-rendicontazione";
$oRecord->src_table = "obiettivi_periodo_rendicontazione";
$isEditable = !$isEdit || ($isEdit && $periodo_rendicontazione->canDelete());
$oRecord->allow_delete = $isEditable;

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->addValidator("date");
$oField->label = "Data inizio periodo";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->addValidator("date");
$oField->label = "Data fine periodo";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "allegati";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";    
$oField->multi_pairs = array(
    array(new ffData("1", "Number"), new ffData("Si", "Text")),
    array(new ffData("0", "Number"), new ffData("No", "Text")),
);
$oField->label = "Allegati alla rendicontazione consentiti";
$oRecord->addContent($oField);

$multipairs_campo_revisione = array();
$campi_revisione = ObiettiviCampoRevisione::getAll();
foreach($campi_revisione as $campo){
    $multipairs_campo_revisione[] = array(new ffData($campo->id, "Number"), new ffData($campo->nome, "Text"));
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_campo_revisione";    
$oField->label = "Campo revisione";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Nessun campo revisione";
$oField->multi_pairs = $multipairs_campo_revisione;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine_responsabile";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->addValidator("date");
$oField->label = "Data termine rendicontazione";
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");

// *********** ADDING TO PAGE ****************
$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);
$cm->oPage->addContent("</div>");

function checkRelations($oRecord, $frmAction) {
    $id_periodo_rendicontazione = $oRecord->key_fields["ID"]->value->getValue();
    if (isset($id_periodo_rendicontazione) && $id_periodo_rendicontazione != "") {
        $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($id_periodo_rendicontazione);
    }
    
    switch ($frmAction) {
        case "delete":
        case "confirmdelete":
            if (!$periodo_rendicontazione->canDelete()) {
                return CoreHelper::setError(
                    $oRecord,
                    "Il periodo rendicontazione selezionato non può essere eliminato."
                );
                $oRecord->skip_action = true;
            }
            break;
    }
}