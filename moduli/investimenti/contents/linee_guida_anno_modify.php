<?php 
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("investimenti_linee_guida_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina di modifica delle indicazioni.");	
}
//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "linee-guida-modify";
$oRecord->title = "Indicazioni per l'anno " . $anno->descrizione;
$oRecord->resources[] = "linee_guida_anno";
$oRecord->src_table = "investimenti_linee_guida_anno";
$oRecord->allow_delete = false;

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->widget = "ckeditor";
$oField->label = "";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_anno_budget"] =  new ffData($anno->id, "Text"); 

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);
