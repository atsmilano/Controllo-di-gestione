<?php
$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("valutazioni_periodo.html", "main");

$tpl->set_var("ret_url", urlencode($_SERVER["REQUEST_URI"]));

//estrazione dei periodi per l'anno
$anno_selezionato = $cm->oPage->globals["anno"]["value"];
$anno_valutazione = new ValutazioniAnnoBudget($anno_selezionato->id);
$periodi_valutazione = $anno_valutazione->getPeriodiAnno();
$tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();

//se è stato definito almeno un periodo per l'anno
if (count($periodi_valutazione) > 0){
	/* ---------------------------------------------------------------------------- */
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_select"];
    } else {
        $periodo_selezionato = $periodi_valutazione[0]->id;
    }
    $periodo = false;
    for ($i = 0; $i < count($periodi_valutazione); $i++) {
        if ($periodi_valutazione[$i]->id == $periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = $periodo_selezionato;
        } else
            $tpl->set_var("periodo_selected", "");
        $tpl->set_var("periodo_id", $periodi_valutazione[$i]->id);
        $tpl->set_var("periodo_descrizione", $periodi_valutazione[$i]->descrizione);

        $tpl->parse("SectOptionPeriodi", true);
    }
    if ($periodo == false) {
        $periodo = $periodi_valutazione[0]->id;
    }
    unset($periodo_selezionato);
    unset($periodi_valutazione);
    $tpl->parse("SectSelezionePeriodi", true);
    /* ---------------------------------------------------------------------------- */
	//viene definito il path del record
	$tpl->set_var("record_path", FF_SITE_PATH . $cm->path_info . "/valutazione_modify");
    $tpl->set_var("stampa_path", FF_SITE_PATH . $cm->path_info . "/stampa_valutazione");
    $tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));    
		
	//viene recuperata la matricola dell'utente per la visualizzazione delle schede di competenza
    $user = LoggedUser::Instance();
	$periodo_valutazione = new ValutazioniPeriodo($periodo);
		
	//vengono estratte tutte le valutazioni per cui l'utente è 
	$valutazioni_valutato = $periodo_valutazione->getValutazioniValutatoPeriodo($user->matricola_utente_selezionato);
	//vengono estratte tutte le valutazioni per cui l'utente è valutatore
	$valutazioni_valutatore = $periodo_valutazione->getValutazioniValutatorePeriodo($user->matricola_utente_selezionato);
	
	//creazione delle sezioni in base alle valutazioni estratte per il dipendente
	$valutazioni_coinvolte = array ();
	if (count($valutazioni_valutato) > 0){
		$valutazioni_coinvolte[] = array(
											//ruolo 0 = valutato
											"ruolo" => "0",
											"ruolo_desc" => "Valutato",
											"valutazioni" => $valutazioni_valutato, 
										);
	}
	if (count($valutazioni_valutatore) > 0){
		$valutazioni_coinvolte[] = array(
											//ruolo 1 = valutatore
											"ruolo" => "1",
											"ruolo_desc" => "Valutatore",
											"valutazioni" => $valutazioni_valutatore,
										);
	}
	if (count($valutazioni_coinvolte)>0){
        foreach ($valutazioni_coinvolte as $valutazione_coinvolta){        
			foreach($valutazione_coinvolta["valutazioni"] as $valutazioni_valutazione_coinvolta){
				$tpl->set_var("ruolo", $valutazione_coinvolta["ruolo_desc"]);	
				
				$valutazione = new ValutazioniValutazionePeriodica($valutazioni_valutazione_coinvolta);				
				$privilegi_utente_valutazione = $valutazione->getPrivilegiPersonale($user->matricola_utente_selezionato);	
				$valutatore = Personale::factoryFromMatricola($valutazione->matricola_valutatore);
                $valutatore = new Personale($valutatore->id);
				$valutato = Personale::factoryFromMatricola($valutazione->matricola_valutato);
                $valutato = new Personale($valutato->id);
				$show_totals = false;
                $show_stampa = false;
                $show_stampa_autoval = false;
							
				//AUTOVALUTAZIONI***********************************************
				//id dell'autovalutazione se presente								
				$autovalutazione = $valutazione->getAutovalutazioneCollegata();
				if ($autovalutazione !== false) {
					$privilegi_utente_autovalutazione = $autovalutazione->getPrivilegiPersonale($user->matricola_utente_selezionato);
				}								
				//creazione dei link in base ai privilegi utente				
				$tpl->set_var("id_valutazione", $valutazione->id);                
                $categoria = $valutazione->categoria;
				if ($periodo_valutazione->getAutovalutazioneAttivaPeriodo($categoria) == false)
				{
					$tpl->set_var("edit_view_autovalutazione", "");
					$tpl->parse("SectNoAutovalutazione", false);
				}
				else if ($privilegi_utente_autovalutazione["edit_autovalutazione"] === true) {
                    $tpl->set_var("edit_view_autovalutazione", "Modifica");	
                    $show_stamp_autoval = true;
                }																				
				else if ($privilegi_utente_autovalutazione["view_autovalutazione"] === true) {				
					$tpl->set_var("edit_view_autovalutazione", "Visualizza");	
                    $show_stamp_autoval = true;                    
                }
				else {
					$tpl->set_var("edit_view_autovalutazione", "");		                    
                }
				
				if ($autovalutazione !== false)				
					$tpl->set_var("id_autovalutazione", $autovalutazione->id);				
				else
					$tpl->set_var("id_autovalutazione", "");
                
                if ($show_stampa_autoval == true) {
                    $tpl->parse("SectStampaAutovalutazione", false);                    
                }
                else {
                    $tpl->set_var("SectStampaAutovalutazione", "");
                }
				
				$tpl->parse("SectModificaAutovalutazione", false);
				//**************************************************************				
				$tpl->set_var("valutatore", $valutatore->cognome." ".$valutatore->nome." (".$valutatore->matricola.")");
				$tpl->set_var("valutato", $valutato->cognome." ".$valutato->nome." (".$valutato->matricola.")");
				try {
                    //cdr afferenza
                    $cdr_commento = "";
                    $cdr_afferenza = $valutato->getCdrAfferenzaInData($tipo_piano_cdr, $periodo_valutazione->data_fine);            
                    if (count ($cdr_afferenza) == 0) {
                        $cdr_commento = " (ultima afferenza - dipendente cessato nell'anno)";
                        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo_valutazione->data_fine);
                        $ultimi_cdr_afferenza = $valutato->getCdrUltimaAfferenza($tipo_piano_cdr);
                        if (count ($ultimi_cdr_afferenza) > 0) {
                            foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                                try {
                                    $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                                    if ($cdr_attuale->codice == $cdr_aff["cdr"]->codice) {
                                        $cdr_afferenza[] = $cdr_aff;
                                    }
                                } catch (Exception $ex) {

                                }
                            }
                        }
                    }
                                                            
                    foreach ($cdr_afferenza as $cdr_aff) {  
                        $tipo_cdr = new TipoCdr($cdr_aff["cdr"]->id_tipo_cdr);                        
                        $tpl->set_var("cdr", $tipo_cdr->abbreviazione . " " . $cdr_aff["cdr"]->descrizione . " (" . $cdr_aff["cdr"]->codice .") " . $cdr_commento);
                        $tpl->set_var("perc_testa", $cdr_aff["peso_cdr"]);
                        $tpl->parse("SectCdrAssociati", true);                
                    }
				} catch (Exception $ex) {
					$tpl->set_var("cdr", "Non definito");
				}				
												
				if ($valutazione_coinvolta["ruolo"] == 0)
				{								
					if ($privilegi_utente_valutazione["edit_valutato"] === true) {
						$tpl->set_var("edit_view_valutazione", "Presa visione della valutazione");
						$show_totals = true;
                        $show_stampa = true;
					}			
                    elseif ($privilegi_utente_valutazione["view_valutazione"] === true) {
                        $tpl->set_var("edit_view_valutazione", "Visualizza");
                        $show_totals = true;
                        $show_stampa = true;
                    }
					$tpl->parse("SectValutatoreTh", false);
					$tpl->set_var("SectValutatoTh", "");
					$tpl->parse("SectValutatore", false);
					$tpl->set_var("SectValutato", "");
				}
				else if ($valutazione_coinvolta["ruolo"] == 1)
				{					
					if ($privilegi_utente_valutazione["edit_valutatore"] === true){
						$tpl->set_var("edit_view_valutazione", "Modifica");
						$show_totals = true;
                        $show_stampa = true;                        
					}		
                    elseif ($privilegi_utente_valutazione["view_valutazione"] === true) {
                        $tpl->set_var("edit_view_valutazione", "Visualizza");
                        $show_totals = true;
                        $show_stampa = true;
                    }
					$tpl->parse("SectValutatoTh", false);
					$tpl->set_var("SectValutatoreTh", "");
					$tpl->parse("SectValutato", false);
					$tpl->set_var("SectValutatore", "");
				}
				else
				{
					if ($privilegi_utente_valutazione["view_valutazione"] === true) {
						$tpl->set_var("edit_view_valutazione", "Visualizza");                        
						$show_totals = true;
                        $show_stampa = true;
					}
					$tpl->parse("SectValutatoreTh", false);
					$tpl->parse("SectValutatoTh", false);
					$tpl->parse("SectValutatore", false);
					$tpl->parse("SectValutato", false);
				}				
								
				$tpl->set_var("categoria_valutazione", $categoria->descrizione);	
				
                //viene visualizzato un asterisco nel caso ci siano note del valutato
                if(strlen($valutazione->note_valutato)) {
                    $tpl->set_var("note_valutato", "***");
                }
                else{
                    $tpl->set_var("note_valutato", "");
                }
                
				$found = array_search($valutazione->getIdStatoAvanzamento(), array_column(ValutazioniValutazionePeriodica::$stati_valutazione, 'ID'));
				if($found !== false) {
					$tpl->set_var("stato_avanzamento", ValutazioniValutazionePeriodica::$stati_valutazione[$found]["descrizione"]);
                }
                if ($show_stampa == true) {
                    $tpl->parse("SectStampaValutazione", false);
                }
                else {
                    $tpl->set_var("SectStampaValutazione", "");
                }
                
                $tpl->parse("SectModificaValutazione", false);
                                                
				//AUTOVALUTAZIONE***********************************												
				if ($periodo_valutazione->getAutovalutazioneAttivaPeriodo() == true){					
					//TOTALI AUTOVALUTAZIONE*********************************					
					$tpl->parse("SectAutovalutazioneTh1", false);								
					$tpl->parse("SectAutovalutazione", false);
				}			
								
				//TOTALI VALUTAZIONE*********************************	
                
				if ($show_totals == true) {
                    $show_totals = false;
					foreach($valutazione->getTotaliPreCalcolati() as $totale_valutazione){
						$ambiti_totale_attivi = "";
						$ambiti_totale = $totale_valutazione["totale_obj"]->getAmbitiTotale();                        
						foreach($ambiti_totale as $ambito_totale){
                            //viene verificato che il totale non sia inibito
                            if ($periodo_valutazione->getVisualizzazionePunteggiAttivaCategoriaAmbito($categoria, $ambito_totale) == true) {
                                $show_totals = true;
                                if ($valutazione->isAmbitoValutato($ambito_totale))
                                    $nv = "";
                                else
                                    $nv = "(nv)";

                                if (strlen($ambiti_totale_attivi) > 0)
                                    $plus = " + ";
                                else
                                    $plus = "";
                                $sezione = new ValutazioniSezione($ambito_totale->id_sezione);
                                $ambiti_totale_attivi .= $plus.$sezione->codice.".".$ambito_totale->codice.".". $nv ;
                            }
						}			
                        if ($show_totals == true) {
                            $tpl->set_var("totale_valutazione", CoreHelper::cutText($totale_valutazione["totale_obj"]->descrizione, 40)." = ".$totale_valutazione["totale_calcolo"]);		
                            $tpl->parse("SectTotaliValutazione", true);
                        }
                        else {
                            $tpl->set_var("SectTotaliValutazione", "");
                        }
					}
				}
				else {
					$tpl->set_var("SectTotaliValutazione", "");
				}
				//**************************************************								
				$tpl->parse("SectValutazione", true);		
				$tpl->set_var("SectTotaliValutazione", "");				
				$tpl->set_var("SectModificaAutovalutazione", "");
				$tpl->set_var("SectNoAutovalutazione", "");
                $tpl->set_var("SectCdrAssociati", "");
				//**************************************************								
			}					
			$tpl->parse("SectValutazioni", true);				
			$tpl->set_var("SectAutovalutazione", "");
			$tpl->set_var("SectValutazione", "");									
		}						
	}	
    else {
        $tpl->parse("SectNoValutazioni", false);	
    }
}
//se non ci sono periodi per l'anno viene visualizzata una notifica
else {
	$tpl->parse("SectNoPeriodi", true);	
}
	
$cm->oPage->addContent($tpl);