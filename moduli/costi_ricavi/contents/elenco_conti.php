<?php
$anno = $cm->oPage->globals["anno"]["value"];
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

	//visualizzazione conti associati al fp
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "conto";
	$oGrid->title = "Conti associati";
	$oGrid->resources[] = "costi-ricavi-conto";
	//costruzione della query
	$db = ffDb_Sql::factory();
	$source_sql = "";
	//estrazione del piano cdr
	$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
    $date = $dateTimeObject->format("Y-m-d");
	//recupero del cdr
	$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
	$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
		
	foreach (CostiRicaviConto::getAll(array("ID_anno_budget" => $anno->id)) as $conto) {
		//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
		if (strlen($source_sql))
			$source_sql .= "UNION ";	

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
		
		$fp = new CostiRicaviFp($conto->id_fp);
		$cdr = AnagraficaCdr::factoryFromCodice($conto->codice_cdr, $dateTimeObject);		
		
		$source_sql .= "SELECT			
						".$db->toSql($id_importo_periodo)." AS ID_importo_periodo,
						".$db->toSql($conto->id)." AS ID_conto,
						".$db->toSql($fp->id)." AS ID_fp,	
						".$db->toSql($periodo->id)." AS ID_periodo,
						".$db->toSql($fp->codice)." AS codice_fp,
						".$db->toSql($fp->descrizione)." AS descrizione_fp,
						".$db->toSql($conto->codice)." AS codice,
						".$db->toSql($conto->descrizione)." AS descrizione,
						".$db->toSql($cdr->codice)." AS codice_cdr,
						".$db->toSql($cdr->descrizione)." AS descrizione_cdr,
						CAST(".$db->toSql($importo_campo_1)." AS DECIMAL) AS sum_campo_1,
						CAST(".$db->toSql($importo_campo_2)." AS DECIMAL) AS sum_campo_2,
						CAST(".$db->toSql($importo_campo_3)." AS DECIMAL) AS sum_campo_3,
						CAST(".$db->toSql($importo_campo_4)." AS DECIMAL) AS sum_campo_4
						";										
		
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
										'' AS ID_fp,
										'' AS ID_periodo,
										'' AS codice_fp,
										'' AS descrizione_fp,
										'' AS codice,
										'' AS descrizione,
										'' AS codice_cdr,
										'' AS descrizione_cdr,
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
	$oGrid->display_search = true;
	$oGrid->open_adv_search = true;
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
		$campo_4_importo_label = "Previsione " . $anno->descrizione;
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
	$oField->id = "ID_periodo";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "codice_fp";
	$oField->base_type = "Text";
	$oField->label = "Codice fp";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "descrizione_fp";
	$oField->base_type = "Text";
	$oField->label = "Descrizione fp";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "codice";
	$oField->base_type = "Text";
	$oField->label = "Codice conto";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "descrizione";
	$oField->base_type = "Text";
	$oField->label = "Descrizione conto";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "codice_cdr";
	$oField->base_type = "Text";
	$oField->label = "Codice CDR";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "descrizione_cdr";
	$oField->base_type = "Text";
	$oField->label = "Descrizione CDR";
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
	
	//FILTRI
	//FP 	
	foreach (CostiRicaviFp::getFpAnno($anno) AS $fp_anno){
		$fp_anno_select[] = array(
								new ffData ($fp_anno->id, "Number"),
								new ffData ($fp_anno->codice . " - " . $fp_anno->descrizione, "Text")
								);
	}	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "fp_search";
	$oField->data_source = "ID_fp";	
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";          
	$oField->multi_pairs = $fp_anno_select;
	$oField->multi_select_one_label = "Tutti i fattori produttivi";
	$oField->label = "Fp";
	$oField->src_operation = "ID_fp = [VALUE]";
	$oGrid->addSearchField($oField);
	
	//CDR		
	foreach (CdrCostiRicavi::getCodiciCdrAssociatiContoAnno($anno) AS $codice_cdr_anno){
		$cdr = AnagraficaCdr::factoryFromCodice($codice_cdr_anno, $dateTimeObject);		
		$cdr_anno_select[] = array(
								new ffData ($cdr->codice, "Number"),
								new ffData ($cdr->codice . " - " . $cdr->descrizione, "Text")
								);
	}	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "cdr_search";
	$oField->data_source = "codice_cdr";	
	$oField->base_type = "Text";
	$oField->extended_type = "Selection";          
	$oField->multi_pairs = $cdr_anno_select;
	$oField->multi_select_one_label = "Tutti i cdr";
	$oField->label = "CDR";
	$oField->src_operation = "codice_cdr = [VALUE]";
	$oGrid->addSearchField($oField);
	
	// *********** ADDING TO PAGE ****************
	$cm->oPage->addContent($oGrid);

	//conti in evidenza
	function recordInit ($oGrid){       
		$cm = cm::getInstance();

		$conto = new CostiRicaviConto($oGrid->key_fields["ID_conto"]->getValue());	
		if ($conto->evidenza == true) {
			$oGrid->row_class = "conto_evidenza";	
		}
		else {
			$oGrid->row_class = "";
		}
		
		//formattazione numeri
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
}