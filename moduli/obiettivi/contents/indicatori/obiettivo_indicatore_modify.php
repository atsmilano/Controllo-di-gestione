<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli indicatori.");	
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

$obiettivo_indicatore = null;
if (isset($_REQUEST["keys[ID_obiettivo_indicatore]"])) {
    $obiettivo_indicatore = new IndicatoriObiettivoIndicatore($_REQUEST["keys[ID_obiettivo_indicatore]"]);
}

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-indicatore-modify";
$oRecord->resources[] = "obiettivo-indicatore";
$oRecord->src_table = "indicatori_obiettivo_indicatore";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_indicatore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addkeyfield($oField);

$oRecord->addContent(null, true, "indicatore-obiettivo");
$oRecord->groups["indicatore-obiettivo"]["title"] = "Indicatore - obiettivo";

if (isset ($_REQUEST["keys[ID_indicatore]"])) {
    try {
        $indicatore = new IndicatoriIndicatore($_REQUEST["keys[ID_indicatore]"]);
        
        $oRecord->title = "Obiettivo collegato all'indicatore '" . $indicatore->nome . "'";
        
        $obiettivi_select = array();
        //vengono estratti per la selezione tutti gli obiettivi per l'anno considerato
        foreach (ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo) {
            if ($obiettivo->data_eliminazione == null) {
                $obiettivi_select[] = array(
                                            new ffData ($obiettivo->id, "Number"),
                                            new ffData ($obiettivo->codice . " - " . CoreHelper::cutText($obiettivo->titolo, 150), "Text")
                                            );
            }       
        }
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo";
        $oField->base_type = "Number";
        $oField->label = "Obiettivo anno " . $anno->descrizione;
        $oField->required = true;
        $oField->extended_type = "Selection";
        $oField->multi_select_one = true;
        $oField->resources[] = "obiettivo";
        $oField->multi_pairs = $obiettivi_select;
        $oRecord->addContent($oField, "indicatore-obiettivo"); 

        $oRecord->insert_additional_fields["ID_indicatore"] =  new ffData($indicatore->id, "Number");
        
        //condizione utilizzata per il detail dei valori target
        $valori_target_where = "ID_indicatore = " . $indicatore->id;
    } catch (Exception $ex) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_indicatore.");
    }
}
else if (isset ($_REQUEST["keys[ID_obiettivo]"])) {
    try {
        $obiettivo = new ObiettiviObiettivo($_REQUEST["keys[ID_obiettivo]"]);
        
        $oRecord->title = "Indicatore collegato all'obiettivo '" . $obiettivo->codice . " - " . $obiettivo->descrizione . "'";
        
        //vengono estratti per la selezione tutti gli indicatori per l'anno considerato
        foreach (IndicatoriIndicatore::getIndicatoriAnno($anno) as $indicatore) {
            $indicatori_select[] = array(
                                            new ffData ($indicatore->id, "Number"),
                                            new ffData ($indicatore->codice . " - " . $indicatore->nome, "Text")
                                            );
        }
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_indicatore";
        $oField->base_type = "Number";
        $oField->label = "Indicatore anno " . $anno->descrizione;
        $oField->widget = "actex";
        $oField->required = true;
        $oField->actex_update_from_db = true;
        //costruzione percorso record
        $path_info_parts = explode("/", $cm->path_info);	
        array_pop($path_info_parts);      
        $oField->actex_dialog_url = FF_SITE_PATH . implode("/", $path_info_parts) . "/indicatore_modify";        
        $oField->resources[] = "indicatore";   
        $oField->multi_pairs = $indicatori_select;
        $oRecord->addContent($oField, "indicatore-obiettivo");

        $oRecord->insert_additional_fields["ID_obiettivo"] =  new ffData($obiettivo->id, "Number");  
        
        //condizione utilizzata per il detail dei valori target
        $valori_target_where = "ID_obiettivo = " . $obiettivo->id;
    } catch (Exception $ex) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo.");
    }
    
}

//valore target aziendale per obiettivo-indicatore
$oField = ffField::factory($cm->oPage);
$oField->id = "valore_target";
$oField->base_type = "Number";
$oField->label = "Valore target aziendale indicatore - obiettivo per l'anno " . $anno->descrizione;
$oRecord->addContent($oField, "indicatore-obiettivo");

//******************************************************************************
$oRecord->addContent(null, true, "valori_target");
$oRecord->groups["valori_target"]["title"] = "Valori target dell'indicatore per l'obiettivo";

//Detail per la definizione dei valori target per obiettivo_indicatore cdr******
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DeatailValoriTarget";
$oDetail->title = "Valori target";
$oDetail->src_table = "indicatori_valore_target_obiettivo_cdr";
//il secondo ID è il field del record
$oDetail->fields_relationship = array("ID_obiettivo_indicatore" => "ID_obiettivo_indicatore");
$oDetail->order_default = "codice_cdr";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_valore_target_obiettivo_indicatore_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr_multipair = array();
//Selezione cdr, utilizzo anagrafica alla data di riferimento
//in inserimento vengono esclusi i cdr ai quali è stato già associato un valore target
foreach (AnagraficaCdrObiettivi::getAnagraficaInData($date) as $anagrafica_cdr){
    $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
    $cdr_multipair[] =
        array(
            new ffData ($anagrafica_cdr->codice),
            new ffData ($anagrafica_cdr->codice." - ". $tipo_cdr->abbreviazione . " - " . $anagrafica_cdr->descrizione, "Number"),						
            );    
}
$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_multipair;
$oField->multi_select_one_label = "Selezionare il cdr...";
$oField->required = true;
$oField->label = "Cdr per assegnazione valore target";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore_target";
$oField->base_type = "Number";
$oField->label = "Valore target";
$oDetail->addContent($oField);
                  
$oRecord->addContent($oDetail, "valori_target");
$cm->oPage->addContent($oDetail);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);