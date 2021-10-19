<?php
$anno = $cm->oPage->globals["anno"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("CdrCostiRicavi");

//verifica privilegi utente
$user = LoggedUser::getInstance();
if (!($user->hasPrivilege("costi_ricavi_view") || $user->hasPrivilege("costi_ricavi_edit"))) {
	ffErrorHandler::raise("Utente non abilitato alla visualizzazione dei costi e ricavi per il CDR.");
}

if (isset($_REQUEST["keys[ID]"]) && strlen($_REQUEST["keys[ID]"])) {
	$valutazione_periodica = new CostiRicaviValutazioneFpCdr($_REQUEST["keys[ID]"]);
	$fp = new CostiRicaviFp($valutazione_periodica->id_fp);
	$periodo = new CostiRicaviPeriodo($valutazione_periodica->id_periodo);
}
else if (isset($_REQUEST["keys[ID_fp]"] ) && isset($_REQUEST["keys[ID_periodo]"]) && strlen($_REQUEST["keys[ID_fp]"]) && strlen($_REQUEST["keys[ID_periodo]"])){
		$valutazione_periodica = null;
		$fp = new CostiRicaviFp($_REQUEST["keys[ID_fp]"]);
		$periodo = new CostiRicaviPeriodo($_REQUEST["keys[ID_periodo]"]);			
}
else {
	ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}

//nomi dei campi in base al periodo
if ($periodo->id_tipo_periodo == 1){
	$campo_1_valutazione_label = "PROGRAMMAZIONE - Coerentemente con i vincoli di sistema si assume il valore di BDG assegnato, verifica il fabbisogno per l'esercizio corrente, pianifica con gli utilizzatori le modalità di assegnazione.";
	$campo_2_valutazione_label = "CONTROLLO - Indica le modalità di controllo che si intendono implementare con la finalità di rilevare periodicamente l'andamento dei consumi, le situazioni critiche, la dimensione degli scostamenti nel rapporto domanda offerta.";
	$campo_3_valutazione_label = "AZIONI - Indica le azioni di  razionalizzazione, mantenimento o altro che  si intendono attuare  a fronte delle situazioni rilevate.";				
}
else {
	$campo_1_valutazione_label = "MONITORAGGIO e AZIONI - Per ogni conto assegnato indicare l'andamento reale dei consumi e gli effetti rispetto ai valori di BDG; esplicitare  gli Interventi correttivi e/o di mantenimento adottati per il rispetto del budget.";
	$campo_2_valutazione_label = "CRITICITA' - Per ogni conto assegnato Indicare e analizzare le situazioni critiche  rilevate e le cause determinanti.";
	$campo_3_valutazione_label = "RELAZIONE AL CET - Descrizione dei valori e motivazioni dello scostamento Commento da inserire nella nota integrativa  descrittiva relativamente al conto regionale di NI(fattore produttivo selezionato), in particolare si chiede di commentare i valori di prechiusura e se presenti i costi anticipati ASST.";
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "costi-ricavi-fp-modify";
$oRecord->resources[] = "costi-ricavi-fp";
$oRecord->src_table = "costi_ricavi_valutazione_fp_cdr";
$oRecord->allow_delete = false;
//privilegi sul record
$edit = false;
//l'obiettivo sarà modificabile solamente da chi ha i privilegi di modifica e se non è stata superata la data di scadenza
if ($user->hasPrivilege("costi_ricavi_edit") && (strtotime(date("Y-m-d")) <= strtotime($periodo->data_scadenza))){
	$edit = true;
}
if ($edit == true) {
	$oRecord->allow_insert = true;	
	$oRecord->allow_update = true;	
}
else{
	$oRecord->allow_insert = false;	
	$oRecord->allow_update = false;	
}

$cm->oPage->addContent("<h2>Fattore produttivo</h2><p>" . $fp->codice . " - " . $fp->descrizione . "</p>"
		. "<h2>Periodo</h2><p>" . $periodo->descrizione . " - anno: " . $anno->descrizione . " (ultima data utile per la rendicontazione: " . date("d/m/Y", strtotime($periodo->data_scadenza)) . ")</p>");
$oRecord->title = "Valutazione periodica";
if ($edit == true) {
	$oRecord->title .= " (modificabile)";
}
else {
	$oRecord->title .= " (NON modificabile)";
}
 
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_1";
$oField->base_type = "Text";	
$oField->label = $campo_1_valutazione_label;
if ($edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData("Non definito", "Text");
}
else{
	$oField->required = true;	
	$oField->extended_type = "Text";
	$oField->properties["rows"] = "6";
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_2";
$oField->base_type = "Text";	
$oField->label = $campo_2_valutazione_label;
if ($edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData("Non definito", "Text");
}
else{
	$oField->required = true;	
	$oField->extended_type = "Text";
	$oField->properties["rows"] = "6";
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_3";
$oField->base_type = "Text";	
$oField->label = $campo_3_valutazione_label;
if ($edit == false){
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oField->default_value = new ffData("Non definito", "Text");
}
else{
	$oField->required = true;	
	$oField->extended_type = "Text";
	$oField->properties["rows"] = "6";
}
$oRecord->addContent($oField);

//campi aggiuntivi per la gestione dell'id fp_cdr e dell'id_inserimento_periodo
if ($valutazione_periodica == null){
	$oRecord->insert_additional_fields["ID_fp"] = new ffData($fp->id, "Number");
	$oRecord->insert_additional_fields["ID_periodo"] = new ffData($periodo->id, "Number");   
	$oRecord->insert_additional_fields["codice_cdr"] = new ffData($cdr->codice, "Text");				
}		

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

//visualizzazione conti associati al fp
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "conto";
$oGrid->title = "Conti associati";
$oGrid->resources[] = "costi-ricavi-conto";
//costruzione della query
$db = ffDb_Sql::factory();
$source_sql = "";
foreach ($fp->getContiAssociatiAnno($anno, $cdr) as $conto) {
	//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
	if (strlen($source_sql)){
		$source_sql .= "UNION ";	
    }

	//vengono estratti solamente i conti del cdr
	if ($conto->codice_cdr == $cdr->codice) {
		//nel caso sia stato importato un importo per il periodo viene visualizzato
		$importo_periodo = CostiRicaviImportoPeriodo::factoryFromPeriodoConto($periodo, $conto);		
		if ($importo_periodo !== null) {			
			$id_importo_periodo = $importo_periodo->id;
			$importo_campo_1 = $importo_periodo->campo_1;
			$importo_campo_2 = $importo_periodo->campo_2;
			$importo_campo_3 = $importo_periodo->campo_3;
			$importo_campo_4 = $importo_periodo->campo_4;
		}
		else {
			$id_importo_periodo = '';
			$importo_campo_1 = '0';
			$importo_campo_2 = '0';
			$importo_campo_3 = '0';
			$importo_campo_4 = '0';
		}
		$source_sql .= "SELECT			
						".$db->toSql($id_importo_periodo)." AS ID_importo_periodo,
						".$db->toSql($conto->id)." AS ID_conto,
						".$db->toSql($conto->codice)." AS codice,
						".$db->toSql($conto->descrizione)." AS descrizione,
						CAST(".$db->toSql($importo_campo_1)." AS DECIMAL) AS sum_campo_1,
						CAST(".$db->toSql($importo_campo_2)." AS DECIMAL) AS sum_campo_2,
						CAST(".$db->toSql($importo_campo_3)." AS DECIMAL) AS sum_campo_3,
						CAST(".$db->toSql($importo_campo_4)." AS DECIMAL) AS sum_campo_4
						";										
	}
	//ridondanza (vengono visualizzati solo i conti degli fp del cdr che vengono associati tramite conti quindi almeno un conto deve essere presente)
	if (strlen($source_sql) > 0){
		$oGrid->source_SQL = "	SELECT *
								FROM (".$source_sql.") AS costi_ricavi_valutazione_fp_cdr
								[WHERE]
								[HAVING]
								[ORDER]";
	}
	else {
		$oGrid->source_SQL .= "SELECT		
									'' ID_importo_periodo,
									'' AS ID_conto,
									'' AS codice,
									'' AS descrizione,														
									'' AS sum_campo_1,							
									'' AS sum_campo_2,
									'' AS sum_campo_3,
									'' AS sum_campo_4
								FROM costi_ricavi_valutazione_fp_cdr
								WHERE 1=0
								[AND]	 
								[WHERE]
								[HAVING]
								[ORDER]
							";
	}
}				
$oGrid->order_default = "sum_campo_1";		
$oGrid->record_id = "costi-ricavi-conto-modify";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);	
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_conto";	
$oGrid->order_method = "labels";
$oGrid->force_no_field_params = true;
$oGrid->display_navigator = false;
//non sarà possibile aggiungere o eliminare conti (importati), aggiunta sempre inibita
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_paging = false;
$oGrid->full_ajax = true;

//evento per l'inizializzazione del record
$oGrid->addEvent("on_before_parse_row", "recordInit");

//label dei campi
if ($periodo->id_tipo_periodo == 1) {
	$campo_1_importo_label = "Budget anno precedente";
	$campo_2_importo_label = "Prechiusura anno precedente";
	$campo_3_importo_label = "Previsione " . $anno->descrizione;
}
else if ($periodo->id_tipo_periodo == 2) {
	$campo_1_importo_label = "Budget";
	$campo_2_importo_label = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo_3_importo_label = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo_4_importo_label = "Prechiusura al 31/12/" . $anno->descrizione . " - Assestamento " . $anno->descrizione;
}
else if ($periodo->id_tipo_periodo == 3) {
	$campo_1_importo_label = "Budget";
	$campo_2_importo_label = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	$campo_3_importo_label = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_importo_periodo";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_conto";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sum_campo_1";
$oField->order_dir = "DESC";
$oField->base_type = "Number";
$oField->app_type = "Text";
$oField->label = $campo_1_importo_label;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sum_campo_2";
$oField->base_type = "Number";
$oField->app_type = "Text";
$oField->label = $campo_2_importo_label;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sum_campo_3";
$oField->base_type = "Number";
$oField->app_type = "Text";
$oField->label = $campo_3_importo_label;
$oGrid->addContent($oField);

if ($periodo->id_tipo_periodo == 2){
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sum_campo_4";
	$oField->base_type = "Number";
	$oField->app_type = "Text";
	$oField->label = $campo_4_importo_label;
	$oGrid->addContent($oField);
}
// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//conti in evidenza e formattazione importi
function recordInit ($oGrid){       
	$cm = cm::getInstance();	
	$conto = new CostiRicaviConto($oGrid->key_fields["ID_conto"]->getValue());	
	if ($conto->evidenza == true) {
		$oGrid->row_class = "conto_evidenza";	
	}
	else {
		$oGrid->row_class = "";
	}
	
	$campo_1 = (int)$oGrid->grid_fields["sum_campo_1"]->getValue("Number");
	$oGrid->grid_fields["sum_campo_1"]->setValue(number_format($campo_1, 0, ",", "."), "Number");
	
	$campo_2 = (int)$oGrid->grid_fields["sum_campo_2"]->getValue("Number");
	$oGrid->grid_fields["sum_campo_2"]->setValue(number_format($campo_2, 0, ",", "."), "Number");
			
	$campo_3 = (int)$oGrid->grid_fields["sum_campo_3"]->getValue("Number");
	$oGrid->grid_fields["sum_campo_3"]->setValue(number_format($campo_3, 0, ",", "."), "Number");
	
	if (isset($oGrid->grid_fields["sum_campo_4"])) {
		$campo_4 = (int)$oGrid->grid_fields["sum_campo_4"]->getValue("Number");
		$oGrid->grid_fields["sum_campo_4"]->setValue(number_format($campo_4, 0, ",", "."), "Number");
	}
}