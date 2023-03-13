<?php
use scadenze\AbilitazioneCdr;

if (isset($_REQUEST["keys[ID]"])) {
    try {
        $abilitazione = new AbilitazioneCdr($_REQUEST["keys[ID]"]);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    $abilitazione = null;
}
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-abilitazione";
$oRecord->title = $abilitazione !== null ? "Modifica ": "Nuova "."abilitazione";
$oRecord->resources[] = "abilitazione";
$oRecord->src_table  = "scadenze_abilitazione_cdr";
if ($abilitazione !== null) {
    $oRecord->allow_delete = $abilitazione->isDeletable();
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$anagrafica_cdr_select = array();
foreach(AnagraficaCdr::getAll() as $anagrafica_cdr) {
    $intervallo_validita = " (valido dal ".CoreHelper::formatUiDate($anagrafica_cdr->data_introduzione);
    if ($anagrafica_cdr->data_termine !== null) {
        $intervallo_validita .= " al ".CoreHelper::formatUiDate($anagrafica_cdr->data_termine);
    }
    $intervallo_validita .= ")";
    
    $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
    $anagrafica_cdr_select[] = array(
        new ffData($anagrafica_cdr->codice, "Text"),
        new ffData($anagrafica_cdr->getDescrizioneEstesa().$intervallo_validita, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anagrafica_cdr_select;
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

//detail per i contatti mail
$oRecord->addContent(null, true, "contatti-mail");
$oRecord->groups["contatti-mail"]["title"] = "Contatti Mail";
   
//detail parametri
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailMail";
$oDetail->title = "Indirizzo Mail";
$oDetail->src_table = "scadenze_contatto_mail";
//il secondo ID è il field del record
$oDetail->fields_relationship = array("ID_abilitazione_cdr" => "ID");
$oDetail->order_default = "mail";
$oDetail->min_rows = 1;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_abilitazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "mail";
$oField->base_type = "Text";
$oField->label = "Indirizzo Mail";
$oField->required = true;
$oDetail->addContent($oField); 

$oRecord->addContent($oDetail, "contatti-mail");
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

$oRecord->addEvent("on_do_action", "checkDelete");
function checkDelete($oRecord, $frmAction) {    
    //gestione delle azioni sul record
    if ($frmAction == "delete" || $frmAction == "confirmdelete") {            
        $abilitazione = new AbilitazioneCdr($oRecord->key_fields["ID"]->value->getValue());
        if (!$abilitazione->isDeletable()) {
            CoreHelper::setError($oRecord, "Impossibile eliminare un'abilitazione utilizzata in scadenze.");               
            return true;
        } 
    }
}