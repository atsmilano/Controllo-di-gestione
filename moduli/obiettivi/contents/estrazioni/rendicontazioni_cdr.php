<?php
if (isset ($_GET["periodo"])) {
    $periodo = new ObiettiviPeriodoRendicontazione($_GET["periodo"]);
	$anno = new AnnoBudget($periodo->id_anno_budget);
	$data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
    $date = $data_riferimento;
    
    $cdr = $cm->oPage->globals["cdr"]["value"];    
		
	$xls_file = "obiettivi-cdr-".$anno->descrizione."_".date("Ymd");
	$nome_foglio_lavoro = "Obiettivi_cdr";
	
    //inizializzazione matrice e intestazioni	
    $matrice_dati = array(
        array(
            "codice obiettivo", 
            "titolo", 
            "descrizione", 
            "indicatori di performance", 
            "origine", 
            "tipo obiettivo", 
            "area_risultato", 
            "area", 
            "ID tipo piano", 
            "descrizione tipo piano",
            "codice cdr",             
            "descrizione cdr",
            "codice cdr coreferenza",
            "peso", 
            "azioni", 
            "parere azioni", 
            "note azioni", 
            "periodo", 
            "azioni", 
            "provvedimenti", 
            "criticità", 
            "misurazione indicatori", 
            "raggiungibile",
            "richiesta_revisione",
            "%raggiungimento", 
            "%nucleo", 
            "note nucleo",
        )
    );
	
    // per ogni cdr della gerarchia del cdr selezionato vengono estratti tutti gli obiettivi-cdr
    foreach ($cdr->getGerarchia() as $cdr_figlio) {         
        $anagrafica_cdr_figlio = AnagraficaCdrObiettivi::factoryFromCodice($cdr_figlio["cdr"]->codice, $date);
        $obiettivi_cdr_figlio_anno = $anagrafica_cdr_figlio->getObiettiviCdrAnno($anno);
        if (count($obiettivi_cdr_figlio_anno)) {            
            foreach($obiettivi_cdr_figlio_anno as $obiettivo_cdr_associato){
                $record = array();
                $obiettivo = new ObiettiviObiettivo($obiettivo_cdr_associato->id_obiettivo);
                //vengono recuperate le descrizioni delle chiavi esterne
                $origine = new ObiettiviOrigine($obiettivo->id_origine);
                $tipo = new ObiettiviTipo($obiettivo->id_tipo);
                $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
                $area = new ObiettiviArea($obiettivo->id_area);		                                                

                $record[] = $obiettivo->codice;
                $record[] = $obiettivo->titolo;
                $record[] = $obiettivo->descrizione;
                $record[] = $obiettivo->indicatori;
                $record[] = $origine->descrizione;
                $record[] = $tipo->descrizione;
                $record[] = $area_risultato->descrizione;
                $record[] = $area->descrizione;
                if ($obiettivo_cdr_associato->isObiettivoCdrAziendale()){
                    $desc_tipo_piano = "Aziendale";
                }
                else {
                    $desc_tipo_piano = "Cdr";
                }
                if ($obiettivo_cdr_associato->isCoreferenza()){
                    $obiettivo_cdr_aziendale = $obiettivo_cdr_associato->getObiettivoCdrAziendale();
                    $rendicontazione = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo);
                    $azioni = $obiettivo_cdr_aziendale->azioni;
                    try{
                        $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr_aziendale->id_parere_azioni);
                        $parere_azioni_desc = $parere_azioni->descrizione;
                    } 				
                    catch (Exception $ex) {
                        $parere_azioni_desc = "Non definite";
                    }
                    $codice_cdr_coreferenza = $obiettivo_cdr_aziendale->codice_cdr;
                }
                else {
                    $rendicontazione = $obiettivo_cdr_associato->getRendicontazionePeriodo($periodo);
                    $azioni = $obiettivo_cdr_associato->azioni;
                    try{
                        $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr_associato->id_parere_azioni);
                        $parere_azioni_desc = $parere_azioni->descrizione;
                    } 				
                    catch (Exception $ex) {
                        $parere_azioni_desc = "Non definite";
                    }
                    $codice_cdr_coreferenza = "";
                }

                if ($obiettivo_cdr_associato->id_tipo_piano_cdr == 0) {
                    $desc_tipo_piano = "Aziendale";
                }
                else {
                    $tipo_piano = new TipoPianoCdr($obiettivo_cdr_associato->id_tipo_piano_cdr);
                    $desc_tipo_piano = $tipo_piano->descrizione;
                }
                
                $record[] = $obiettivo_cdr_associato->id_tipo_piano_cdr;
                $record[] = $desc_tipo_piano;
                $record[] = $obiettivo_cdr_associato->codice_cdr;                                
                $record[] = $cdr_figlio["cdr"]->descrizione;
                $record[] = $codice_cdr_coreferenza;
                $record[] = $obiettivo_cdr_associato->peso;
                $record[] = $azioni;					
                $record[] = $parere_azioni_desc;
                $record[] = $obiettivo_cdr_associato->note_azioni;

                if ($rendicontazione !== null){
                    $record[] = $periodo->descrizione;
                    $record[] = $rendicontazione->azioni;
                    $record[] = $rendicontazione->provvedimenti;
                    $record[] = $rendicontazione->criticita;
                    $record[] = $rendicontazione->misurazione_indicatori;
                    $record[] = $rendicontazione->raggiungibile == 1?"Si":"No";                        
                    $record[] = $rendicontazione->richiesta_revisione == 2?"Si propone la sospensione dell'obiettivo":(
                                            $rendicontazione->richiesta_revisione == 1?"Si propone la revisione dell'obiettivo": 
                                            "Si conferma l'obiettivo assegnato"); 
                    $record[] = $rendicontazione->perc_raggiungimento;
                    $record[] = $rendicontazione->perc_nucleo;
                    $record[] = $rendicontazione->note_nucleo;
                }
                $matrice_dati[] = $record;
            }               
        }
    }
    CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
}
else {
	mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);	
}