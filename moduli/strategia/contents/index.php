<?php
//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];

$cdr = $cm->oPage->globals["cdr"]["value"];
if ($cdr == 0) {
    //se l'utente non è responsabile di cdr (cdr = 0) viene recuperato il cdr principale di assegnazione
    $user = LoggedUser::Instance();
    $personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);

    $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
    $date = $cm->oPage->globals["data_riferimento"]["value"];

    $cdr_afferenza_data = $personale->getCdrAfferenzaInData($tipo_piano_cdr, $date->format("Y-m-d"));
    
    if (empty($cdr_afferenza_data)) {
        //viene estratto il CDR di ultima afferenza di livello più alto
        $cdr_afferenza_data = $personale->getCdrUltimaAfferenza($tipo_piano_cdr, $anno)[0];        
        if (empty($cdr_afferenza_data)) {
            ffErrorHandler::raise("Errore: nessun cdr di afferenza nell'anno.");
        }
        else {
            $cdr = $cdr_afferenza_data["cdr"];
        }
    }
    else {
        $perc_max = 0;
        foreach ($cdr_afferenza_data as $cdr_afferenza) {
            if ($cdr_afferenza["peso_cdr"] > $perc_max) {
                $cdr = $cdr_afferenza["cdr"];
                $perc_max = $cdr_afferenza["peso_cdr"];
            }
        }
    }
}

try {
	$descrizione_introduttiva = new StrategiaDescrizioneIntroduttiva($anno);
	$cm->oPage->AddContent($descrizione_introduttiva->descrizione);
}
catch (Exception $ex) {
	
}	

$prospettive_anno = StrategiaProspettiva::getProspettiveAnno($anno);
$cm->oPage->AddContent("<h3>Prospettive:</h3>");
if (count ($prospettive_anno)>0) {	
	foreach ($prospettive_anno as $prospettiva) {
		$cm->oPage->AddContent("<p><strong>".$prospettiva->nome.":</strong> ".$prospettiva->descrizione."</p>");
	}
}
else {
	$cm->oPage->AddContent("<p><strong>Nessuna definita per l'anno selezionato.</p>");
}

$db = ffDb_Sql::factory();

if (STRATEGIA_CDR_PROGRAMMAZIONE !== false || $cdr->id_padre == 0) {
	//nel caso in cui il cdr partecipi alla programmazione strategica viene data la possibilità di modificare nel caso l'utente sia il responsabile del cdr
	$user = LoggedUser::Instance();	
    $programmazione_strategica = false;
    
    foreach (StrategiaCdrProgrammazioneStrategica::getCdrProgrammazioneStrategicaAnno($anno) as $cdr_programmazione_strategica) {
        if ($cdr->codice == $cdr_programmazione_strategica) {            
            $programmazione_strategica = true;
            break;
        }
    }
	if ($programmazione_strategica == true) {
		//******************************************************************************
		//popolamento della grid tramite array		
		$source_sql = "";
		foreach ($prospettive_anno as $prospettiva) {
			//viene selezionata la strategia per l'anno e il cdr per la prospettiva (univoca)
			$strategia = Strategia::getAll(array("ID_anno_budget" => $anno->id, "codice_cdr" => $cdr->codice, "ID_prospettiva" => $prospettiva->id));

			//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
			if (strlen($source_sql))
				$source_sql .= "UNION ";	
			if (strlen($strategia[0]->descrizione) > 0) {
				$descrizione_strategia = $strategia[0]->descrizione;
			} 
			else {
				$descrizione_strategia = "Non definita";
			}

			$source_sql .= "SELECT			
							".$db->toSql($strategia[0]->id)." AS ID,
							".$db->toSql($prospettiva->id)." AS ID_prospettiva,
							".$db->toSql($prospettiva->nome)." AS prospettiva,
							".$db->toSql($descrizione_strategia)." AS descrizione_strategia
						";
		}
		$tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
		//visualizzazione della grid (nel caso in cui ci siano prospettive)
		if (strlen($source_sql) > 0){
			$cm->oPage->addContent("<div id='strategia_modificabile'>");
			$oGrid = ffGrid::factory($cm->oPage);
			$oGrid->id = "strategiaCdr";
            $oGrid->class .= " strategiaCdrGrid";
			$oGrid->title = "Strategia di '".$tipo_cdr->abbreviazione." - ".$cdr->descrizione."'";
			if ($user->hasPrivilege("resp_cdr_selezionato")){
				$oGrid->title .= " (modificabile dall'utente)";			
			}
			$oGrid->resources[] = "strategia";
			$oGrid->source_SQL = "	SELECT *
									FROM (".$source_sql.") AS prospettiva
									[WHERE]
									[HAVING]
									[ORDER]";
			$oGrid->order_default = "prospettiva";
			$oGrid->record_id = "strategia-modify";
			$oGrid->order_method = "labels";		
			$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/strategia_modify";
			//verifica sulla possibilità di modificare i dati
			$data_chiusura_anno = StrategiaAnno::getChiusuraAnno($anno);
			if ( !$user->hasPrivilege("resp_cdr_selezionato") || !($data_chiusura_anno == null || strtotime($data_chiusura_anno) >= strtotime(date("Y-m-d")))) {
				$oGrid->display_edit_url = false;			
			}			
			$oGrid->full_ajax = true;		
			$oGrid->display_new = false;	
			$oGrid->display_delete_bt = false;
			$oGrid->display_search = false;				

			// *********** FIELDS ****************
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->base_type = "Number";
			$oField->label = "id";
			$oGrid->addKeyField($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_prospettiva";
			$oField->base_type = "Number";
			$oField->label = "id_prospettiva";		
			$oGrid->addKeyField($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "prospettiva";
			$oField->base_type = "Text";		
			$oField->label = "Prospettiva";
			$oGrid->addContent($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "descrizione_strategia";
			$oField->base_type = "Text";
			$oField->label = "Descrizione";
			$oField->encode_entities = false;
			$oGrid->addContent($oField);

			// *********** ADDING TO PAGE ****************
			$cm->oPage->addContent($oGrid);
			$cm->oPage->addContent("</div>");
		}
	}
}

//vengono visualizzate tutte le strategie dei superiori gerarchici di programmazione strategica (nel caso non ci si trovi all'elemento radice)
if ($cdr->id_padre !== 0){
	//nel caso in cui sia impostata la costante per la visualizzazione del solo elemento radice viene forzato l'elemento radice come unico cdr di visualizzare la strategia
    if (STRATEGIA_CDR_PROGRAMMAZIONE !== false) {
		$cdr_padre_strategico = $cdr;
	}
	else {
		$piano_cdr = new PianoCdr($cdr->id_piano_cdr);		
		$cdr_padre_strategico = $piano_cdr->getCdrRadice();
	}
	do {	
        $cdr_strategia = new CdrStrategia($cdr_padre_strategico->id, $cdr_padre_strategico->useSql);
        $cdr_padre_strategico = $cdr_strategia->getPadreStrategico($anno);
        
		//******************************************************************************
		//popolamento della grid tramite array		
		$source_sql = "";
		foreach ($prospettive_anno as $prospettiva) {		
			//viene selezionata la strategia per l'anno e il cdr per la prospettiva (univoca)
			$strategia = Strategia::getAll(array("ID_anno_budget" => $anno->id, "codice_cdr" => $cdr_padre_strategico->codice, "ID_prospettiva" => $prospettiva->id));

			//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
			if (strlen($source_sql))
				$source_sql .= "UNION ";	
			if (strlen($strategia[0]->descrizione) > 0) {
				$descrizione_strategia = $strategia[0]->descrizione;
			} 
			else {
				$descrizione_strategia = "Non definita";
			}

			$source_sql .= "SELECT			
							".$db->toSql($strategia[0]->id)." AS ID,
							".$db->toSql($prospettiva->nome)." AS prospettiva,
							".$db->toSql($descrizione_strategia)." AS descrizione_strategia
						";
		}		
		$tipo_cdr_padre_strategico = new TipoCdr($cdr_padre_strategico->id_tipo_cdr);
		//visualizzazione dell'eventuale grid della prospettiva del padre
		if (strlen($source_sql) > 0){
			$oGrid = ffGrid::factory($cm->oPage);
			$oGrid->id = "strategiaPadre_".$cdr_padre_strategico->id;
			$oGrid->title = "Strategia definita da '".$tipo_cdr_padre_strategico->abbreviazione." - ".$cdr_padre_strategico->descrizione."'";
			$oGrid->class .= " strategiaCdrGrid";
            $oGrid->resources[] = "strategia";
			$oGrid->source_SQL = "	SELECT *
									FROM (".$source_sql.") AS prospettiva
									[WHERE]
									[HAVING]
									[ORDER]";
			$oGrid->order_default = "prospettiva";
			$oGrid->record_id = "";
			$oGrid->order_method = "labels";
			$oGrid->full_ajax = false;		
			$oGrid->display_new = false;	
			$oGrid->display_delete_bt = false;
			$oGrid->display_search = false;
			$oGrid->display_edit_url = false;

			// *********** FIELDS ****************
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->base_type = "Number";
			$oField->label = "id";
			$oGrid->addKeyField($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "prospettiva";
			$oField->base_type = "Text";		
			$oField->label = "Prospettiva";
			$oGrid->addContent($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "descrizione_strategia";
			$oField->base_type = "Text";
			$oField->label = "Descrizione";
			$oField->encode_entities = false;
			$oGrid->addContent($oField);

			// *********** ADDING TO PAGE ****************
			$cm->oPage->addContent($oGrid);
		}		
	} while ($cdr_padre_strategico->id_padre!==0);
}