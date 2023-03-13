<?php
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipo-piano-cdr-modify";
$oRecord->title = "Tipologia piano cdr";
$oRecord->resources[] = "tipo-piano-cdr";
$oRecord->src_table = "tipo_piano_cdr";
$oRecord->addEvent("on_do_action","checkRelations");

//istanza e gestione della visualizzazione delle action
if(isset($_REQUEST["keys"]["ID"])) {	
	try {
		$tipo_piano_cdr = new TipoPianoCdr($_REQUEST["keys"]["ID"]);	
	}
	catch (Exception $ex) {
		ffErrorHandler::raise($ex->getMessage());
	}
	
    if (!$tipo_piano_cdr->isDeletable()){
        $oRecord->allow_delete = false;
    }
}

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "priorita";
$oField->base_type = "Number";
$oField->label = "Priorità";
$oField->required = true;
$oRecord->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {    
    switch($frmAction){
        case "insert":
        case "update":
            foreach (TipoPianoCdr::getAll() as $tipo_piano_cdr) {
                if ($oRecord->key_fields["ID"]->value->getValue() !== $tipo_piano_cdr->id
                    &&    
                    $oRecord->form_fields["priorita"]->value->getValue() == $tipo_piano_cdr->priorita    
                    ) {
                    $oRecord->tplDisplayError("La priorità inserita è stata già definita per il piano '".$tipo_piano_cdr->descrizione."'.");
                    return true;
                }
            }
        break;
        case "delete":
        case "confirmdelete":
        $tipo_piano_cdr = new TipoPianoCdr($oRecord->key_fields["ID"]->value->getValue());
        if(!$tipo_piano_cdr->isDeletable()){
            $oRecord->tplDisplayError("Il tipo piano è utilizzato per almeno un piano CdR: impossibile eliminare.");
            return true;
        }
        break;
    }
}