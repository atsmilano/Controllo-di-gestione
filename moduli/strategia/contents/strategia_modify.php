<?php 
//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
//verifica sulla possibilità di modificare i dati
$data_chiusura_anno = StrategiaAnno::getChiusuraAnno($anno);					
if (!($data_chiusura_anno == null || strtotime($data_chiusura_anno) >= strtotime(date("Y-m-d")))) {
	ffErrorHandler::raise("Non è possibile modificare la strategia, il periodo risulta chiuso.");
}		
//prospettiva***********
$prospettiva = new StrategiaProspettiva($_REQUEST["keys[ID_prospettiva]"]);
//verifica che la prospettiva sia una di quelle definite nell'anno
$prospettive_anno = StrategiaProspettiva::getProspettiveAnno($anno);
if (count ($prospettive_anno)>0) {	
	$found = false;
	foreach ($prospettive_anno as $prospettiva_anno) {
		if ($prospettiva_anno->id == $prospettiva->id){
			$found = true;
		}				
	}
	if ($found == false){
		ffErrorHandler::raise("Errore nel passaggio dei parametri: prospettiva non prevista per l'anno");
	}
}
else {
	ffErrorHandler::raise("Errore nel passaggio dei parametri: nessuna prospettiva definita per l'anno");
}
//cdr********
$cdr = $cm->oPage->globals["cdr"]["value"];

$user = LoggedUser::getInstance();
//verifica che il cdr sia di responsabilità dell'utente
$programmazione_strategica = false;
foreach (StrategiaCdrProgrammazioneStrategica::getCdrProgrammazioneStrategicaAnno($anno) as $cdr_programmazione_strategica) {
    if ($cdr->codice == $cdr_programmazione_strategica) {            
        $programmazione_strategica = true;
        break;
    }
}
if (!($programmazione_strategica == true && $user->hasPrivilege("resp_cdr_selezionato"))) {
	ffErrorHandler::raise("Errore nel passaggio dei parametri: il cdr non è di responsabilità dell'utente o non partecipa alla programmazione strategica");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "strategia-modify";
$oRecord->title = "Strategia";
$oRecord->resources[] = "strategia";
$oRecord->src_table = "strategia_strategia";
$oRecord->allow_delete = false;

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

//la prospettiva è sempre preselezionata dal parametro passato
$prospettive[] = array(
						new ffData ($prospettiva->id, "Number"),
						new ffData ($prospettiva->descrizione, "Text")
						);
$oField = ffField::factory($cm->oPage);
$oField->id = "prospettiva";
$oField->data_source = "ID_prospettiva";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $prospettive;
$oField->label = $prospettiva->nome;
$oField->control_type = "label";
$oField->default_value = new ffData($prospettiva->id, "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->widget = "ckeditor";
$oField->label = "Strategia";
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_anno_budget"] =  new ffData($anno->id, "Text"); 
$oRecord->insert_additional_fields["codice_cdr"] =  new ffData($cdr->codice, "Text"); 
$oRecord->additional_fields["data_ultima_modifica"] =  new ffData(date("Y-m-d"), "Date", "ISO9075");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);