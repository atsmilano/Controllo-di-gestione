<?php
$user = LoggedUser::Instance();

if (isset ($_REQUEST["keys[ID]"])) {
    try {       
        $progetto = new ProgettiProgetto($_REQUEST["keys[ID]"]);
    }
    catch (Exception $ex){
        ffErrorHandler::raise("Errore nel passaggio dei parametri");
    }
}
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "progetto-finanziamento";
$oRecord->title = "Finanziamenti";
$oRecord->resources[] = "progetto-finanziamento";
$oRecord->src_table  = "progetti_progetto_finanziamento";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_progetti_progetto_finanziamento";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "Importo";
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->label = "Importo";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "origine";
$oField->base_type = "Text";
$oField->label = "Origine";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "atto";
$oField->base_type = "Text";
$oField->label = "Atto";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_progetto"] = new ffData($progetto->id, "Number");

$cm->oPage->addContent($oRecord);