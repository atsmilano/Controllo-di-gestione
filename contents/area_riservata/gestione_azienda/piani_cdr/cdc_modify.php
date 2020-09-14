<?php
if (isset($_REQUEST["id_cdr"])) {
    $id_cdr = $_REQUEST["id_cdr"];
    $cdr = getCdr($id_cdr);
    if($cdr->id_cdr_padre != 0) {
        $cdr_padre = getCdr($cdr->id_cdr_padre);
    }
    //viene recuperato anche il piano cdr
    $piano_cdr = getPianoCdr($cdr->id_piano_cdr);
} else
    ffErrorHandler::raise("Errore nel passaggio dei parametri: cdr");

//CONTROLLI DI COERENZA
//viene verificato che l'id del piano dei cdr corrisponda al piano del cdr padre e nel caso del cdr figlio
if (isset($cdr_padre) && $cdr_padre->id !== 0 && ($cdr_padre->id_piano_cdr !== $cdr->id_piano_cdr)) {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: piani dei cdr non corrispondenti");
}

if (isset($_REQUEST["keys[id_cdc]"])) {
    $id_cdc = $_REQUEST["keys[id_cdc]"];
    //se il parametro di selezione del cdr risulta valido viene utilizzato
    if ($id_cdc != 0) {
        try {
            $cdc = new Cdc($id_cdc);
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex->getMessage());
        }
        if ($cdc->id_cdr !== $cdr->id)
            ffErrorHandler::raise("Errore di coerenza nei parametri: cdc - cdr");
    }
} else {
    $id_cdc = 0;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdc-modify";
$oRecord->title = "Centro di costo";
$oRecord->resources[] = "cdc";
$oRecord->src_table = "cdc";

if (isset($piano_cdr->data_introduzione)) {
    $oRecord->allow_insert = false;
    $oRecord->allow_delete = false;
    $oRecord->allow_update = false;
}

$oRecord->addEvent("on_do_action", "validateCdc");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "id_cdc";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

//generazione di un eventuale cambio di cdr
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdr";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
foreach ($piano_cdr->getCdr() as $cdr_piano) {
    $cdr_select[] = array(
        new ffData($cdr_piano->id, "Number"),
        new ffData($cdr_piano->codice . " - " . $cdr_piano->descrizione, "Text")
    );
}
$oField->multi_pairs = $cdr_select;
$oField->label = "Cdr padre";
$oField->default_value = new ffData($cdr->id, "Number");
$oRecord->addContent($oField);

//generazione di un eventuale cambio di anagrafica cdc
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anagrafica_cdc";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
if($id_cdc !== 0) {
    $cdc_anagrafiche = $cdc;    
}
else {
    $cdc = null;
}
$oField->multi_pairs = getAnagraficheCdc($cdr, true, $cdc_anagrafiche);
$oField->label = "Cdc";
$oField->required = true;
$oRecord->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function validateCdc($oRecord, $frmAction) {
    $id_cdr = $oRecord->form_fields["ID_cdr"]->value->getValue();
    $cdr = getCdr($id_cdr);
    if ($frmAction == "insert" || $frmAction == "update" || $frmAction == "delete") {
        $piano_cdr = getPianoCdr($cdr->id_piano_cdr);
        
        if (isset($piano_cdr->data_introduzione)) {
            return true;
        }
    }
    
    if($frmAction == "insert" || $frmAction == "update") {
        $error_anagrafica_esistente = "L'anagrafica selezionata risulta essere già presente all'interno del POAS.";
        $id_anagrafica_cdc = $oRecord->form_fields["ID_anagrafica_cdc"]->value->getValue();
        
        
        if(!in_array($id_anagrafica_cdc, getAnagraficheCdc($cdr, false))) {
            $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
                    ? $oRecord->strError 
                    : $error_anagrafica_esistente;
            return true;
        }
    }
}

function getPianoCdr($id_piano_cdr) {
    try {
        $piano_cdr = new PianoCdr($id_piano_cdr);
        return $piano_cdr;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

function getCdr($id_cdr) {
     try {
        $cdr = new Cdr($id_cdr);
        return $cdr;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

function getAnagraficheCdc($cdr, $selection, Cdc $cdc_edit=null) {    
    $id_anagrafiche_piano = array();
    $piano_cdr = getPianoCdr($cdr->id_piano_cdr);
    foreach($piano_cdr->getCdr() as $cdr_piano) {
        foreach($cdr_piano->getCdc() as $cdc) {
            $id_anagrafiche_piano[] = $cdc->id_anagrafica_cdc;
        }
    }
    $anagrafiche_cdc = array();
    foreach (AnagraficaCdc::getAnagraficaInData(new DateTime($piano_cdr->data_definizione)) as $anagrafica_cdc) {        
        if(!in_array($anagrafica_cdc->id, $id_anagrafiche_piano) || ($cdc_edit!==null && $cdc_edit->id_anagrafica_cdc == $anagrafica_cdc->id)) {            
            if($selection) {
                $anagrafiche_cdc[] = array(
                    new ffData($anagrafica_cdc->id, "Number"),
                    new ffData($anagrafica_cdc->codice . " - " . $anagrafica_cdc->descrizione, "Text")
                );
            } else {
                $anagrafiche_cdc[] = $anagrafica_cdc->id;
            }   
        }
    }
    return $anagrafiche_cdc;
}
