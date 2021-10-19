<?php
$user = LoggedUser::getInstance();

$date = $cm->oPage->globals["data_riferimento"]["value"];
$anno = $cm->oPage->globals["anno"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"]; 
$anagrafica_cdr = new AnagraficaCdrObiettivi($cdr->id_anagrafica_cdr);

//verifica che sia passato l'id dell'indicatore
if (isset ($_REQUEST["keys[ID_indicatore_cruscotto]"])) {
    $record_desc = "Dettagli Indicatore";
    $indicatore_cdr_cruscotto_anno = new IndicatoriIndicatoreCdrCruscottoAnno($_REQUEST["keys[ID_indicatore_cruscotto]"]);
    $indicatore = new IndicatoriIndicatore($indicatore_cdr_cruscotto_anno->id_indicatore); 

    //viene verificato che l'indicatore sia presente nel cruscotto per l'anno e il cdr selezionato       
    if ($indicatore_cdr_cruscotto_anno->codice_cdr !== $cdr->codice || $indicatore_cdr_cruscotto_anno->id_anno_budget !== $anno->id ) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_indicatore_cruscotto non coerente con cdr e anno selezionati.");
    }
}
else {
    $record_desc = "Nuovo indicatore cruscotto anno " . $anno->descrizione ." per il CDR: '".$cdr->codice." - ".$cdr->descrizione."'";
}

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "indicatore-cruscotto-modify";
$oRecord->title = $record_desc;
$oRecord->resources[] = "indicatore-cruscotto";
$oRecord->src_table = "indicatori_indicatore_cdr_cruscotto_anno";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatore_cruscotto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addkeyfield($oField);

// la modifica è sempre inibita (l'amministratore può eliminare un indicatore e associarne uno nuovo)
$oRecord->allow_update = false;
if (!$user->hasPrivilege("indicatori_edit")){
    $oRecord->allow_insert = false;
    $oRecord->allow_delete = false;
}

if (!isset ($_REQUEST["keys[ID_indicatore_cruscotto]"])) {
    //origine
    foreach (IndicatoriIndicatore::getIndicatoriAnno($anno) AS $indicatore_anno){
        $found = false;        
        foreach ($anagrafica_cdr->getIndicatoriCruscottoAnno($anno) as $indicatore_cruscotto_anno) { 
            if ($indicatore_cruscotto_anno->id == $indicatore_anno->id) {
                $found = true;
            }            
        }
        if ($found == false) {
            $origine_select[] = array(
                                new ffData ($indicatore_anno->id, "Number"),
                                new ffData ($indicatore_anno->nome . " - ".$indicatore_anno->descrizione, "Text")
                                );
        }
    }          
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_indicatore";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $origine_select;
    $oField->label = "Origine";
    $oField->required = true;
    $oRecord->addContent($oField);
}
else {           
    $oField = ffField::factory($cm->oPage);
    $oField->id = "nome";
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oField->default_value = new ffData($indicatore->nome, "Text");    
    $oField->label = "Nome";
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oField->default_value = new ffData($indicatore->descrizione, "Text");    
    $oField->label = "Descrizione";
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "formula_calcolo_risultato";
    $oField->base_type = "Text";		    
    $oField->control_type = "label";
    $oField->data_type = "";
    $oField->store_in_db = false;    
    $oField->label = "Formula per il calcolo del risultato";
    $oField->default_value = new ffData($indicatore->visualizzazioneFormulaRisultatoIndicatore(), "Text");
    $oRecord->addContent($oField);

    //recupero parametri, risultato, valore target e raggiungimento
    $valori_cruscotto_anno = $indicatore->getValoriCruscottoCdr($cdr, $anno);
    $oField = ffField::factory($cm->oPage);
    $oField->id = "calcolo_risultato";
    $oField->base_type = "Text";		    
    $oField->control_type = "label";
    $oField->data_type = "";
    $oField->store_in_db = false;    
    $oField->label = "Risultato"; 
    $oField->default_value = new ffData($indicatore->visualizzazioneFormulaRisultatoIndicatore($valori_cruscotto_anno["parametri"]) . " = " . ($valori_cruscotto_anno["risultato"]!==null?$valori_cruscotto_anno["risultato"]:"ND"), "Text");
    $oRecord->addContent($oField);
        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "raggiungimento";
    $oField->base_type = "Text";		    
    $oField->control_type = "label";
    $oField->data_type = "";
    $oField->store_in_db = false;    
    $oField->label = "Raggiungimento"; 
    $oField->default_value = new ffData($valori_cruscotto_anno["raggiungimento"]!==null?$valori_cruscotto_anno["raggiungimento"]:"ND", "Text");
    $oRecord->addContent($oField);
    
    //Grid con eventuali valori dell'indicatore per i cdr figli        
    $grid_recordset = array();
    foreach ($cdr->getFigli() as $cdr_figlio) {	        
        $anagrafica_cdr_figlio = new AnagraficaCdrObiettivi($cdr_figlio->id_anagrafica_cdr);
        $indicatore_cdr_figlio = $anagrafica_cdr_figlio->getIndicatoriCruscottoAnno($anno, $indicatore);
        if (count($indicatore_cdr_figlio)) {
            $risultati = $indicatore_cdr_figlio[0]->indicatore->getValoriCruscottoCdr($cdr_figlio, $anno);             
            $grid_recordset[] = array(
                                        $indicatore_cdr_figlio[0]->id,
                                        $cdr_figlio->codice." - ".$cdr_figlio->descrizione,
                                        $indicatore->visualizzazioneFormulaRisultatoIndicatore($risultati["parametri"])."=".$risultati["risultato"]!==null?$risultati["risultato"]:"ND",
                                        $risultati["valore_target"]!==null?$risultati["valore_target"]:"ND",
                                        $risultati["raggiungimento"]!==null?$risultati["raggiungimento"]:"ND",
                                    );                        
        }        
    }
    if (count($grid_recordset)) {
        $oRecord->addContent(null, true, "indicatore_cdr_figli");
        $oRecord->groups["indicatore_cdr_figli"]["title"] = "Valorizzazione indicatore dei CDR figli";
        
        $grid_fields = array(
                        "ID",
                        "cdr",
                        "risultato",
                        "valore_target",
                        "raggiungimento",
        );
        
        //visualizzazione della grid (nel caso in cui ci siano obiettivi per l'anno)
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "indicatori_figli";
        $oGrid->title = "";
        $oGrid->resources[] = "indicatore-cruscotto";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_indicatore");
        $oGrid->order_default = "cdr";
        $oGrid->record_id = "";
        $oGrid->order_method = "labels";	        
        $oGrid->record_url = "";	        
        $oGrid->display_new = false;    
        $oGrid->display_delete_bt = false;
        $oGrid->display_edit_url = false;        
        $oGrid->display_navigator = false;        
        $oGrid->use_paging = false;
        $oGrid->display_search = false;

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_indicatore_cruscotto_figlio";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr";
        $oField->base_type = "Text";
        $oField->label = "Codice";		
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "risultato";
        $oField->base_type = "Text";
        $oField->label = "Risultato";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "valore_target";
        $oField->base_type = "Text";		
        $oField->label = "Valore target";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "raggiungimento";
        $oField->base_type = "Text";		
        $oField->label = "Raggiungimento";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $oRecord->addContent($oGrid, "indicatore_cdr_figli");
        $cm->oPage->addContent($oGrid);  
    }  
    
    //Eventuali obiettivi del cdr collegati all'indicatore
    $obiettivi_collegati = array();
    $obiettivi_cdr_anno = $anagrafica_cdr->getObiettiviCdrAnno($anno);    
    foreach ($indicatore->getObiettiviCollegati($anno) as $obiettivo_collegato) {
        $found = false;
        foreach ($obiettivi_cdr_anno as $obiettivo_cdr_anno) {
            if ($obiettivo_cdr_anno->id_obiettivo == $obiettivo_collegato->id) {
                $found = true;
                break;
            }
        }        
        if ($found == true) {
            $obiettivi_collegati[] = $obiettivo_collegato;
        }
    }
    
    if (count($obiettivi_collegati)) {   
        $oRecord->addContent(null, true, "obiettivi_collegati");
        $oRecord->groups["obiettivi_collegati"]["title"] = "Obiettivi " . $anno->descrizione . " collegati all'indicatore";
    
        $grid_fields = array(
                        "ID",
                        "codice",
                        "titolo",
                        "tipo",
                        "area_risultato",
                        "area",                        
                    );
        $grid_recordset = array();
        foreach ($obiettivi_collegati as $obiettivo) {            
            $tipo = new ObiettiviTipo($obiettivo->id_tipo);
            $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
            $area = new ObiettiviArea($obiettivo->id_area);

            $grid_recordset[] = array(
                                    $obiettivo->obiettivo_indicatore->id,
                                    $obiettivo->codice,
                                    $obiettivo->titolo,
                                    $tipo->descrizione,
                                    $area_risultato->descrizione,
                                    $area->descrizione,
                                );		
        }

        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivo-indicatore";
        $oGrid->title = "";
        $oGrid->resources[] = "obiettivo-indicatore";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo");
        $oGrid->order_default = "codice";
        $oGrid->record_id = "obiettivo-indicatore-modify";
        $oGrid->order_method = "labels";	        
        $oGrid->record_url = "";	
        $oGrid->display_new = false;    
        $oGrid->display_delete_bt = false;
        $oGrid->display_edit_url = false;        
        $oGrid->display_navigator = false;        
        $oGrid->use_paging = false;
        $oGrid->display_search = false;
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo_indicatore";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "codice";
        $oField->base_type = "Text";
        $oField->label = "Codice";		
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "titolo";
        $oField->base_type = "Text";		
        $oField->label = "Titolo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "tipo";
        $oField->base_type = "Text";
        $oField->label = "Tipo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "area_risultato";
        $oField->base_type = "Text";
        $oField->label = "Area Risultato";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "area";
        $oField->base_type = "Text";
        $oField->label = "Area";
        $oGrid->addContent($oField);
        
        $oRecord->addContent($oGrid, "obiettivi_collegati");
        $cm->oPage->addContent($oGrid); 
    }
}

$oRecord->insert_additional_fields["ID_anno_budget"] =  new ffData($anno->id, "Number");
$oRecord->insert_additional_fields["codice_cdr"] =  new ffData($cdr->codice, "Text");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);