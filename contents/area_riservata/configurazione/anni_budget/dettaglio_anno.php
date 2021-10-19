<?php
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("anni_budget_admin")){
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$isEdit = false;
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $anno = new AnnoBudget($_REQUEST["keys[ID]"]);
        $isEdit = true;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "anno-modify";
$oRecord->title = $isEdit ? "Modifica anno budget": "Nuovo anno budget";
$oRecord->resources[] = "anno";
$oRecord->src_table  = "anno_budget";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oField->addValidator("number", array(true, 2010, 2099, true, true));
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "attivo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = array (
                            array(new ffData("1", "Number"), new ffData("Si", "Text")),
                            array(new ffData("0", "Number"), new ffData("No", "Text")),
           );
$oField->label = "Attivo";           
$oField->default_value = new ffData($anno->attivo==true?"Si":"No", "Text");
$oField->required = true;
$oRecord->addContent($oField);
    
$cm->oPage->addContent($oRecord);