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
$oRecord->id = "progetto-fase-tempo-realizzazione";
$oRecord->title = "Fase e tempo di realizzazione";
$oRecord->resources[] = "progetto-fase-tempo-realizzazione";
$oRecord->src_table  = "progetti_progetto_fase_tempo_realizzazione";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_progetti_progetto_fase_tempo_realizzazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_fase";
$oField->base_type = "Text";
$oField->label = "Descrizione fase";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio_fase";
$oField->base_type = "Date";
$oField->label = "Data inizio fase";
$oField->required = true;
$oField->widget = "datepicker";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine_fase";
$oField->base_type = "Date";
$oField->label = "Data fine fase";
$oField->required = true;
$oField->widget = "datepicker";
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_progetto"] = new ffData($progetto->id, "Number");

$cm->oPage->addContent($oRecord);