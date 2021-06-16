<?php
$user = LoggedUser::Instance();
//solamente il responsabile del cdr ha la possibilità di visualizzare e modificare l'obiettivo se il periodo è aperto
if (!$user->hasPrivilege("resp_cdr_selezionato") ) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina dell'obiettivo.");
}	
	
//recupero parametri
$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

if (isset($_REQUEST["keys[ID_obiettivo_cdr_personale]"])){
	try {
		$obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale($_REQUEST["keys[ID_obiettivo_cdr_personale]"]);       
		if ($obiettivo_cdr_personale->data_eliminazione !== null) {
			ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr_personale. Il record risulta eliminato.");
		}
	}
	catch (Exception $ex) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr_personale.");
	}
	$obiettivo_cdr = new ObiettiviObiettivoCdr($obiettivo_cdr_personale->id_obiettivo_cdr);
}
else if(isset ($_REQUEST["keys[ID_obiettivo_cdr]"])){
	try {
		$obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr]"]);	
	}
	catch (Exception $ex) {
		ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr.");
	}
}
else {
	ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}
				
//modifica permessa solo nei periodi abilitati
if ($obiettivo_cdr->isChiuso()){
	ffErrorHandler::raise("Errore: non è possibile modificare l''assegnazione dalla data ".$obiettivo_cdr->data_chiusura_modifiche.".");
}
else {
    if ($obiettivo_cdr->id_tipo_piano_cdr != 0) {
        $tipo_piano_cdr = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
    }
    //se l'obiettivo è aziendale viene considerato come tipologia il piano di priorità massima in cui il codice_cdr è presente
    else {
        $tipo_piano_cdr = Cdr::getTipoPianoPriorita($obiettivo_cdr->codice_cdr, $date);        
    }
}

$obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
//verifica sull'eliminazione dell'obiettivo
if ($obiettivo->data_eliminazione !== null || $obiettivo_cdr->data_eliminazione !== null) {
	ffErrorHandler::raise("Errore nel passaggio dei parametri: elemento eliminato.");
}
//******************************************************************************
//recupero del cdr
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
$cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);

$db = ffDb_Sql::factory();

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-cdr-personale-modify";
$oRecord->title = "Assegnazione personale";
$oRecord->resources[] = "obiettivo-cdr-personale";
$oRecord->src_table = "obiettivi_obiettivo_cdr_personale";

//viene definita sul record l'eliminazione logica del record piuttosto che quella fisica
$oRecord->del_action = "update";
$oRecord->del_update = "data_eliminazione=".$db->toSql(date("Y-m-d H:i:s"));

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_cdr_personale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

//id obiettivo collegato
$oRecord->insert_additional_fields["ID_obiettivo_cdr"] =  new ffData($obiettivo_cdr->id,"Number");
//data_ultima_modifica
$oRecord->additional_fields["data_ultima_modifica"] =  new ffData(date("Y-m-d H:i:s"),"DateTime");

//campo per la selezione del personale
$personale_select = array();
//in caso di modifica viene recuperato il dipendente assocaito
if (isset($obiettivo_cdr_personale)){
	$personale = PersonaleObiettivi::factoryFromMatricola($obiettivo_cdr_personale->matricola_personale);	
	$personale_select[] = array(
                                new ffData ($personale->matricola, "Text"),
                                new ffData ($personale->cognome . " " . $personale->nome . " (matr. " . $personale->matricola . ")", "Text")
                                );				
	$tot_peso_personale = $personale->getPesoTotaleObiettivi($anno, $obiettivo_cdr);	
}
//altrimenti vengono visualizzati i dipendenti del cdr non ancora assegnati
else {
	//vengono presi i dipendenti singolarmente (in caso di afferenza multipla su cdc)
	$personale_cdc_afferente = $cdr->getPersonaleCdcAfferentiInData($dateTimeObject);
	$responsabile_cdr = $cdr->getResponsabile($dateTimeObject);
	$personale_cdr = array();
	foreach ($personale_cdc_afferente as $personale_afferente){	
		//se il personale non è il responsabile del cdr	
        if ($responsabile_cdr->matricola_responsabile !== $personale_afferente->matricola_personale){
			$found = false;
			foreach ($personale_cdc_afferente as $personale_confronto){		
				//se il personale non è già assegnato all'obiettivo
				if ($personale_afferente->matricola_personale == $personale_confronto->matricola_personale && $personale_afferente->id !== $personale_confronto->id){		
					$found = true;
					break;
				}		
			}
			if ($found == false) {
				$personale_cdr[] = $personale_afferente;
			}
		}
	}
	//creazione dell'array per la selezione
	//l'obiettivo_cdr_personale non deve essere stato eliminato logicamente e il dipendente non deve essere già associato all'obiettivo
	foreach ($personale_cdr as $personale_cdr_obiettivo) {
		$found = false;	
		foreach ($obiettivo_cdr->getObiettivoCdrPersonaleAssociati() as $ob_cdr_per) {								
			if ($ob_cdr_per->data_eliminazione == null && $personale_cdr_obiettivo->matricola_personale == $ob_cdr_per->matricola_personale) {	
				$found = true;
				break;
			}
		}
		if ($found == false){
			$personale = Personale::factoryFromMatricola($personale_cdr_obiettivo->matricola_personale);						
			$personale_select[] = array(
										new ffData ($personale->matricola, "Text"),
										new ffData ($personale->cognome . " " . $personale->nome . " (matr. " . $personale->matricola . ")", "Text")
										);		
		}
	}
}
//viene ordinato l'array del personale
usort($personale_select, "personaleCmp");
function personaleCmp ($per1, $per2) {
	return ($per1[1]->getValue() > $per2[1]->getValue());		
}
	
$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_personale";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $personale_select;    
$oField->label = "Dipendente associato all'obiettivo";
$oField->required = true;
if (isset($obiettivo_cdr_personale)){
	$oField->control_type = "label";
	$oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->base_type = "Number";		
$oField->label = "Peso";
$oField->addValidator("number", array(true, OBIETTIVI_MIN_PESO, OBIETTIVI_MAX_PESO, true, true, true));
if (isset($tot_peso_personale)) {
	$oField->label .= " (tot. peso obiettivi cdr escluso l'obiettivo corrente: " . $tot_peso_personale . ")";
}
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

//TODO verificare eventuali tentativi duplicati (non dovrebbe verificarsi a meno che non ci sia una forzatura dei parametri)