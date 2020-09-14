<?php
$user = LoggedUser::Instance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

$cdr_global = $cm->oPage->globals["cdr"]["value"];
$cdr = new Cdr($cdr_global->id);
$anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $dateTimeObject);

$peso_tot_obiettivi = $anagrafica_cdr->getPesoTotaleObiettivi($anno);

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("matrice_pesi_personale.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);

//url della pagina di modifica di cdr_url (con i parametri globali)
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//intestazione della tabella, obiettivi del cdr
$colonna = 1;
$riga = 0;
$obiettivi_cdr_anno = $anagrafica_cdr->getObiettiviCdrAnno($anno);
if (count($obiettivi_cdr_anno)>0) {
	$obiettivi_colspan = 0;
	foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
        $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
        $cod_ob = "";
        for ($i=0; $i<strlen($obiettivo->codice); $i++){
            $cod_ob .= $obiettivo->codice[$i]."<br>";
        }
        if ($obiettivo_cdr->isCoreferenza()) {
            $cod_ob .= $obiettivo->codice[$i]."T";
        }
        if ($obiettivo_cdr->isChiuso()) {
            $cod_ob .= $obiettivo->codice[$i]."*";
        }        
        $tpl->set_var("codice_obiettivo", $cod_ob);
        $tpl->set_var("desc_obiettivo", $obiettivo->titolo);
        $tpl->set_var("riga", $riga);
        $tpl->set_var("colonna", $colonna++);
        $tpl->parse ("Obiettivi", true);

        $obiettivi_colspan++;		
	}	
	$riga++;
	//righe della tabella, personale e per ognuno di essi associazione e peso agli obiettivi
	//ordinamento del personale in base alla categoria dirigente o comparto
	//viene escluso dall'elenco dei dipendenti il responsabile del cdr
	$distr_teste_cdr = $cdr->getPersonaleCdcAfferentiInData($date);	
	$responsabile = $cdr->getResponsabile($dateTimeObject);
	$personale_peso = array();
	$obiettivi_modificabili = false;
	//il personale deve essere considerato una sola volta quindi se afferisce a più cdc dello stesso cdr deve essere considerato univocamente e con la somma dei pesi percentuali
	foreach ($distr_teste_cdr as $personale_cdc) {	
		//il responsabile del cdr non viene considerato nell'elenco del personale           
		if ($responsabile->matricola_responsabile !==  $personale_cdc->matricola_personale) {
			$found = false;
			foreach ($personale_peso as $personale_considerato) {
				if ($personale_cdc->matricola_personale == $personale_considerato["personale"]->matricola){                          
					$personale_considerato["perc_cdc"] += $personale_cdc->percentuale;
					$found = true;
					break;
				}
			}			
			if ($found == false) {
				$personale = PersonaleObiettivi::factoryFromMatricola($personale_cdc->matricola_personale);
				$carriera = $personale->getCarriera($date);		
				$qualifica = new QualificaInterna($carriera->id_qualifica_interna);								
				$personale_peso[] = array(
										"personale" => $personale,								
										"perc_cdc" => $personale_cdc->percentuale,
										"dirigente" => $qualifica->dirigente,
										);
			}
		}
	}
	//viene ordinato l'array del personale in base al fatto che sia dirigente o meno e come secondo criterio in ordine alfabetico
	function personaleCmp ($per1, $per2) {
		if ($per1["dirigente"] == $per2["dirigente"]) {
			if (strcmp($per1["personale"]->cognome.$per1["personale"]->nome, $per2["personale"]->cognome.$per2["personale"]->nome) > 0) {
				return 1;
			}
		}
		else if ($per1["dirigente"] < $per2["dirigente"]){
			return 1;
		}		
	}			
	usort($personale_peso, "personaleCmp");
		
	$categoria = -1;
	foreach($personale_peso as $dipendente_peso){
		//nel momento in cui la categoria dei dipendenti cambi viene visualizzata come riga
		if ($categoria !== $dipendente_peso["dirigente"]) {
			$categoria = $dipendente_peso["dirigente"];
			if ($dipendente_peso["dirigente"] == 1) {
				$desc_categoria = "Dirigenza";
			}
			else {
				$desc_categoria = "Comparto";
			}
			$tpl->set_var("categoria", $desc_categoria);
			$tpl->set_var("obiettivi_colspan", $obiettivi_colspan+1);
			$tpl->parse ("IntestazioneCategoria", false);			
		}
		else {
			$tpl->set_var ("IntestazioneCategoria", false);
		}
						
		$tpl->set_var("riga", $riga++);		
		$colonna = 1;
		
		$obiettivi_cdr_personale_anno = $dipendente_peso["personale"]->getObiettiviCdrPersonaleAnno($anno);		
		$totale_obiettivi = 0;
		$show_firma = false;
		foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
			if ($obiettivo_cdr->data_eliminazione == null){
				$tpl->set_var("colonna", $colonna++);			
				$found = null;					
				foreach($obiettivi_cdr_personale_anno as $key => $obiettivo_cdr_personale_anno){
					if ($obiettivo_cdr_personale_anno->data_eliminazione == null) {
						if ($obiettivo_cdr->id == $obiettivo_cdr_personale_anno->id_obiettivo_cdr){
							$found = array(
											"id" => $obiettivo_cdr_personale_anno->id,
											"peso_obiettivo_personale" => $obiettivo_cdr_personale_anno->peso,
											"data_accettazione" => $obiettivo_cdr_personale_anno->data_accettazione,
											"chiuso" => $obiettivo_cdr->isChiuso(),
									);
							$totale_obiettivi += $obiettivo_cdr_personale_anno->peso;
							unset($obiettivi_cdr_personale_anno[$key]);
							break;
						}
					}
				}			
				if ($found == null){
                    if (!$obiettivo_cdr->isChiuso() && $user->hasPrivilege("resp_cdr_selezionato")){ 
                        $obiettivi_modificabili = true;
                        $tpl->set_var("modificabile_class", "modificabile");
                    }
                    else {                                         
                        $tpl->set_var("modificabile_class", "non_modificabile");
                    }                                                      
					$tpl->set_var("presa_visione_class", false);
				}
				else {								
					if (!$found["chiuso"] && $found["data_accettazione"] == null && $user->hasPrivilege("resp_cdr_selezionato")){
						$obiettivi_modificabili = true;
						$tpl->set_var("modificabile_class", "modificabile");
					}
					else {
						$tpl->set_var("modificabile_class", "non_modificabile");
					}					
					if ($found["data_accettazione"] !== null){
						$tpl->set_var("presa_visione_class", "azioni_definite");	
						$show_firma = true;
					}
					else {
						$tpl->set_var("presa_visione_class", "azioni_non_definite");
					}						
				}
                $tpl->set_var("id_obiettivo_cdr_personale", $found["id"]);
                $tpl->set_var("id_obiettivo_cdr", $obiettivo_cdr->id);
                $tpl->set_var("matricola_personale", $dipendente_peso["personale"]->matricola);
                $tpl->set_var("peso_obiettivo_personale", $found["peso_obiettivo_personale"]);					
                $tpl->parse ("PesoObiettivoCdrPersonale", false);									
				$tpl->parse ("ObiettivoCdrPersonale", true);		
			}
		}	
		$tpl->set_var("nome_dipendente", $dipendente_peso["personale"]->cognome." ".$dipendente_peso["personale"]->nome); 
		
		$dettagli_dipendente = $dipendente_peso["personale"]->cognome." ".$dipendente_peso["personale"]->nome 
											."\nmatricola ".$dipendente_peso["personale"]->matricola
											."\n". $dipendente_peso["perc_cdc"]."% su cdr";
		//visualizzazione note e data firma per presa visione in caso di almeno un'assegnazione firmata
		if ($show_firma == true){
			try {
				$accettazione = ObiettiviAccettazione::factoryFromDipendenteAnno($dipendente_peso["personale"], $anno);
				$dettagli_dipendente .= "\n\nData presa visione: ".date("d/m/Y", strtotime($accettazione->data_accettazione_dipendente))
									."\nNote presa visione: ";			
				if (strlen($accettazione->note_dipendente)>0){
					$dettagli_dipendente .= $accettazione->note_dipendente;
				}
				else {
					$dettagli_dipendente .= "nessuna";
				}
			} catch (Exception $ex) {

			}	
		}
		$tpl->set_var("dettagli_dipendente", $dettagli_dipendente);								
		$tpl->set_var("totale_obiettivi_cdr_personale", $totale_obiettivi);
		$tpl->set_var("colonna", 0);
		$tpl->parse ("Personale", true);        
		$tpl->set_var("ObiettivoCdrPersonale", false);
        $tpl->set_var("totale_obiettivi_cdr_personale", false);
	}
	//se è definito almeno un obiettivo_cdr per l'anno (già verificato perchè ci sitrovi nel ramo) ed è definito almeno un peso e almeno un'assegnazione risulta modificabile
	if (count($personale_peso)>0 && $obiettivi_modificabili == true) {
		$tpl->parse ("AzioniMatrice", true);
	}
	else if (count($personale_peso)==0) {
		$tpl->set_var("obiettivi_colspan", $obiettivi_colspan+2);
		$tpl->parse ("NoPersonale", true);
	}
	$tpl->parse ("MatricePesiCdrPersonale", true);
}
else {
	$tpl->parse ("NoObiettivi", true);
}

//***********************
//Adding contents to page
die($tpl->rpparse("main", true));