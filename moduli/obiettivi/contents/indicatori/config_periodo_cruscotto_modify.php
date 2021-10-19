<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei periodi cruscotto.");
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_periodo_cruscotto]"])) {
    $isEdit = true;
    $id_periodo_cruscotto = $_REQUEST["keys[ID_periodo_cruscotto]"];
    try {
        $periodo_cruscotto = new IndicatoriPeriodoCruscotto($id_periodo_cruscotto);
    }
    catch (Exception $e) {
        ffErrorHandler::raise($e->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-cruscotto-modify";
$oRecord->title = $isEdit 
    ? "Modifica periodo cruscotto '".$periodo_cruscotto->descrizione."'" 
    : "Nuovo periodo cruscotto";
$oRecord->resources[] = "periodo-cruscotto";
$oRecord->src_table  = "indicatori_periodo_cruscotto";

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_cruscotto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
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
$oField->required = true;
$oField->label = "Data inizio periodo";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->addValidator("date");
$oField->required = true;
$oField->label = "Data fine periodo";
$oRecord->addContent($oField);

foreach(AnnoBudget::getAll() as $anno_budget) {
    $anno_budget_select[] = array(
        new ffData($anno_budget->id, "Number"),
        new ffData($anno_budget->descrizione, "Text"),
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno_budget";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anno_budget_select;
$oField->label = "Anno budget";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "checkFieldRelation");
// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function checkFieldRelation($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
        case "update":
            // Check data_riferimento_fine >= data_riferimento_inizio
            $data_riferimento_inizio_form = $oRecord->form_fields["data_riferimento_inizio"]->value->getValue();
            $data_riferimento_inizio = DateTime::createFromFormat("d/m/Y", $data_riferimento_inizio_form);
            
            $data_riferimento_fine_form = $oRecord->form_fields["data_riferimento_fine"]->value->getValue();
            $data_riferimento_fine = DateTime::createFromFormat("d/m/Y", $data_riferimento_fine_form);
            
            if ($data_riferimento_fine < $data_riferimento_inizio) {
                return CoreHelper::setError($oRecord, "Data riferimento fine deve essere maggiore o uguale "
                    . "a data riferimento inizio");
            }
            
            // Check anno = YEAR(data_riferimento_fine) e anno = YEAR(data_riferimento_inizio)
            $id_anno_budget = $oRecord->form_fields["ID_anno_budget"]->value->getValue();
            $anno_budget = new AnnoBudget($id_anno_budget);
                
            if ($anno_budget->descrizione != $data_riferimento_inizio->format("Y")) {
                return CoreHelper::setError($oRecord, "Anno budget NON congruente con anno"
                    . " data riferimento inizio");
            }
            
            if ($anno_budget->descrizione != $data_riferimento_fine->format("Y")) {
                return CoreHelper::setError($oRecord, "Anno budget NON congruente con anno"
                    . " data riferimento fine");
            }

            break;
    }
}