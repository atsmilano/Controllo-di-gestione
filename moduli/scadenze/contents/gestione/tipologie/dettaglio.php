<?php
use scadenze\Tipologia;

$tipologia = null;
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $tipologia = new Tipologia($_REQUEST["keys[ID]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-tipologia";
$oRecord->title = $tipologia !== null ? "Modifica ": "Nuova "."tipologia";
$oRecord->resources[] = "tipologia";
$oRecord->src_table  = "scadenze_tipologia";
if ($tipologia !== null) {
    $oRecord->allow_delete = $tipologia->isDeletable();
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oField->widget = "datepicker";  
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oField->widget = "datepicker";  
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oRecord->addEvent("on_do_action", "checkDelete");
function checkDelete($oRecord, $frmAction) {
    //gestione delle azioni sul record
    if ($frmAction == "delete" || $frmAction == "confirmdelete") {            
        $tipologia = new Tipologia($oRecord->key_fields["ID"]->value->getValue());
        if (!$tipologia->isDeletable()) {
            CoreHelper::setError($oRecord, "Impossibile eliminare una tipologia utilizzata in scadenze.");               
            return true;
        } 
    }
}