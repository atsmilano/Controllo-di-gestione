<?php
$anno = $cm->oPage->globals["anno"]["value"];

$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("CdrCostiRicavi");

$user = LoggedUser::getInstance();
//estrazione
if ($user->hasPrivilege("costi_ricavi_admin")){
    $cm->oPage->addContent("<a id='costi_ricavi_estrazione_link' class='link_estrazione' href='".FF_SITE_PATH . $cm->path_info ."/estrazione_costi_ricavi.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."'>"
            . "<div id='costi_ricavi_estrazione' class='estrazione link_estrazione'>Estrazione costi / ricavi anno .xls</div></a><br>");
}
//******************************************************************************
//Validazione e selezione parametro periodo
//**********
//Periodo
$periodi = CostiRicaviPeriodo::getAll(array("ID_anno_budget" => $anno->id));
if (count($periodi)>0){
	$periodi_select = array();
	foreach ($periodi AS $periodo){
		$primo_periodo = $periodo;
		$periodi_select[] = array(
								new ffData ($periodo->id, "Number"),
								new ffData ("Periodo: ".$periodo->descrizione." ("
														.date("d/m/Y", strtotime($periodo->data_riferimento_inizio))." - "
														.date("d/m/Y", strtotime($periodo->data_riferimento_fine)).")", "Text")
								);
	}	
	
	if(isset($_GET["periodo"]))
		$periodo = new CostiRicaviPeriodo($_GET["periodo"]);
	else
		$periodo = $primo_periodo;

	//visualizzazione ffield
	$oField = ffField::factory($cm->oPage);
	$oField->id = "periodo";        
	$oField->base_type = "Number";        		
	$oField->extended_type = "Selection";          
	$oField->multi_pairs = $periodi_select;		
	$oField->setValue($periodo->id);
	$oField->multi_select_one = false;
	if(count($periodi_select)<=1){		
		$oField->control_type = "label";
    }
	$oField->properties["onchange"] = "submit();";	
	$cm->oPage->addContent($oField->process());

	//visualizzazione fp
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "fp";
	$oGrid->title = "Fattori produttivi associati a '".$cdr->codice." - ".$cdr->descrizione."'";
	$oGrid->resources[] = "costi-ricavi-fp";
	
	//costruzione della query
	$db = ffDb_Sql::factory();
	$source_sql = "";

	foreach ($cdr->getFpAnno($anno) as $fp_cdr) {					
		//viene calcolato il totale per ogni fatttore produttivo sommando i valori dei campi dei conti associati
		$tot_campo_1 = 0;
		$tot_campo_2 = 0;
		$tot_campo_3 = 0;
		$tot_campo_4 = 0;
		foreach ($fp_cdr->getContiAssociatiAnno($anno, $cdr) as $conto_associato) {
			//per ogni conto viene recuperato l'importo
			$importi_periodo = $conto_associato->getImportiPeriodo($periodo);
			$tot_campo_1 += $importi_periodo->campo_1;
			$tot_campo_2 += $importi_periodo->campo_2;
			$tot_campo_3 += $importi_periodo->campo_3;
			$tot_campo_4 += $importi_periodo->campo_4;
		}				
		//viene verificato se è presente una valutazione periodica
		$valutazione_periodica = CostiRicaviValutazioneFpCdr::factoryFromPeriodoFpCdr($periodo, $fp_cdr, $cdr);
		if ($valutazione_periodica !== null){
			$id = $valutazione_periodica->id;
			$codice_label = "*";
		}
		else {
			$id = "";
			$codice_label = "";
		}
		
		//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
		if (strlen($source_sql))
			$source_sql .= "UNION ";	

		$source_sql .= "SELECT			
						".$db->toSql($id)." AS ID,
						".$db->toSql($fp_cdr->id)." AS ID_fp,	
						".$db->toSql($periodo->id)." AS ID_periodo,	
						".$db->toSql($fp_cdr->codice.$codice_label)." AS codice,
						".$db->toSql($fp_cdr->descrizione)." AS descrizione,
						CAST(".$db->toSql($tot_campo_1)." AS DECIMAL) AS sum_campo_1,
						CAST(".$db->toSql($tot_campo_2)." AS DECIMAL) AS sum_campo_2,
						CAST(".$db->toSql($tot_campo_3)." AS DECIMAL) AS sum_campo_3,
						CAST(".$db->toSql($tot_campo_4)." AS DECIMAL) AS sum_campo_4
					";				
	}

	if (strlen($source_sql) > 0){
		$oGrid->source_SQL = "	SELECT *
								FROM (".$source_sql.") AS costi_ricavi_valutazione_fp_cdr
								[WHERE]
								[HAVING]
								[ORDER]";
	}
	else {
		$oGrid->source_SQL .= "SELECT			
									'' AS ID,			
									'' AS ID_fp,
									'' AS ID_periodo,
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
	$oGrid->order_default = "sum_campo_1";		
	$oGrid->record_id = "costi-ricavi-fp-modify";
	$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_fp";
	$oGrid->order_method = "labels";
	$oGrid->force_no_field_params = true;
	$oGrid->display_navigator = false;
	$oGrid->use_paging = false;

	//definizione di un template personalizzato per la grid
	$modulo = Modulo::getCurrentModule();

	//viene caricato il template specifico per la pagina
	$oGrid->template_dir = $modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl";
	$oGrid->template_file = "grid_costi_ricavi.html";	
	//non sarà possibile aggiungere o eliminare fattori produttivi (importati), aggiunta sempre inibita
	$oGrid->display_new = false;
	$oGrid->display_delete_bt = false;
	$oGrid->display_search = false;
	$oGrid->use_paging = false;
					
	//evento per il calcolo dei totali
	$oGrid->addEvent ("on_before_parse_row", "calcoloTotali");
	//evento per la visualizzazione dei totali
	$oGrid->addEvent("on_after_process_grid", "showTotals");
	//evento per l'inizializzazione del record
	$oGrid->addEvent("on_before_parse_row", "recordInit");
	
	// *********** FIELDS ****************
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_fp";
	$oField->base_type = "Number";	
	$oGrid->addKeyField($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_periodo";
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
	
	//nomi dei campi in base al periodo
	//label dei campi
	if ($periodo->id_tipo_periodo == 1) {
		$label_campo_1 = "Budget anno precedente";
		$label_campo_2 = "Prechiusura anno precedente";
		$label_campo_3 = "Previsione " . $anno->descrizione;
	}
	else if ($periodo->id_tipo_periodo == 2) {
		$label_campo_1 = "Budget";
		$label_campo_2 = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
		$label_campo_3 = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
		$label_campo_4 = "Prechiusura al 31/12/" . $anno->descrizione . " - Assestamento " . $anno->descrizione;
	}
	else if ($periodo->id_tipo_periodo == 3) {
		$label_campo_1 = "Budget";
		$label_campo_2 = "Consuntivo rilevato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
		$label_campo_3 = "Consuntivo stimato al " . date("d/m/Y", strtotime($periodo->data_riferimento_fine));
	}
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sum_campo_1";
	$oField->order_dir = "DESC";
	$oField->base_type = "Number";
	$oField->app_type = "Text";
	$oField->label = $label_campo_1;
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sum_campo_2";
	$oField->base_type = "Number";
	$oField->app_type = "Text";
	$oField->label = $label_campo_2;
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "sum_campo_3";
	$oField->base_type = "Number";
	$oField->app_type = "Text";
	$oField->label = $label_campo_3;
	$oGrid->addContent($oField);
	
	if ($periodo->id_tipo_periodo == 2) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "sum_campo_4";
		$oField->base_type = "Number";
		$oField->app_type = "Text";
		$oField->label = $label_campo_4;
		$oGrid->addContent($oField);
	}
	// *********** ADDING TO PAGE ****************
	$cm->oPage->addContent($oGrid);
	
	//inizializzazione dei parametri globali per il calcolo dei totali
	//settaggio del parametro globale per il calcolo dei totali
	$globals=  ffGlobals::getInstance("totali");
	$globals->tot_campo_1 = 0;
	$globals->tot_campo_2 = 0;
	$globals->tot_campo_3 = 0;
	$globals->tot_campo_4 = 0;			
}
else {
	$cm->oPage->addContent("<p>Nessun periodo definito per l'anno.</p>");		
}

//calcolo totali e formattazione importi
function calcoloTotali ($oGrid){   						
	//aggiornamento dei totali e formattazione numeri
	$globals=  ffGlobals::getInstance("totali");	
	
	$campo_1 = (int)$oGrid->grid_fields["sum_campo_1"]->getValue("Number");
	$globals->tot_campo_1 += $campo_1;
	$oGrid->grid_fields["sum_campo_1"]->setValue(number_format($campo_1, 0, ",", "."), "Number");
	
	$campo_2 = (int)$oGrid->grid_fields["sum_campo_2"]->getValue("Number");
	$globals->tot_campo_2 += $campo_2;
	$oGrid->grid_fields["sum_campo_2"]->setValue(number_format($campo_2, 0, ",", "."), "Number");
			
	$campo_3 = (int)$oGrid->grid_fields["sum_campo_3"]->getValue("Number");
	$globals->tot_campo_3 += $campo_3;
	$oGrid->grid_fields["sum_campo_3"]->setValue(number_format($campo_3, 0, ",", "."), "Number");
	
	if (isset($oGrid->grid_fields["sum_campo_4"])) {
		$campo_4 = (int)$oGrid->grid_fields["sum_campo_4"]->getValue("Number");
		$globals->tot_campo_4 += $campo_4;
		$oGrid->grid_fields["sum_campo_4"]->setValue(number_format($campo_4, 0, ",", "."), "Number");
	}
}

function showTotals ($oGrid){
	//recupero template
	$tpl = $oGrid->tpl[0];
	//recupero variabili globali
	$globals=  ffGlobals::getInstance("totali");
		
	$totale = new ffData($globals->tot_campo_1, "Number");
	$tpl->set_var("tot_campo_1", number_format($totale->getValue(), 0, ",", "."));
	
	$totale = new ffData($globals->tot_campo_2, "Number");
	$tpl->set_var("tot_campo_2", number_format($totale->getValue(), 0, ",", "."));
	
	$totale = new ffData($globals->tot_campo_3, "Number");
	$tpl->set_var("tot_campo_3", number_format($totale->getValue(), 0, ",", "."));
	
	if (isset($oGrid->grid_fields["sum_campo_4"])) {
		$totale = new ffData($globals->tot_campo_4, "Number");		
		$tpl->set_var("tot_campo_4", number_format($totale->getValue(), 0, ",", "."));
		$tpl->parse("SectCampo4", false);
	}
	else {
		$tpl->set_var("SectCampo4", false);
	}
}

//conti in evidenza
function recordInit ($oGrid){       
	$cm = cm::getInstance();
		
	//generazione dell'url
	if ($oGrid->key_fields["ID"]->getValue() !== "")	
		$id = "?keys[ID]=" . $oGrid->key_fields["ID"]->getValue() . "&";
	else
		$id = "?";
	
	$anno = $cm->oPage->globals["anno"]["value"];
        $cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("CdrCostiRicavi");
	$fp = new CostiRicaviFp($oGrid->key_fields["ID_fp"]->getValue());	
	$evidenza = false;
	foreach ($fp->getContiAssociatiAnno($anno) as $conto) {
		//viene evidenziato l'fp solamente se il conto in evidenza è associato al cdr
		if ($conto->evidenza == 1 && $conto->codice_cdr == $cdr->codice){
			$evidenza = true;
			break;
		}		
	}
	//classe per i conti in evidenza
	if ($evidenza == true) {
		$oGrid->row_class = "conto_evidenza";	
	}
	else {
		$oGrid->row_class = "";
	}
}