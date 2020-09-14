<?php
if (isset ($_GET["anno"])) {
	$anno = new AnnoBudget($_GET["anno"]);
    $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);

    $xls_file = "obiettivi_cdr_aziendali-".date("Ymd");
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
            "data_chiusura_modifiche",
        )
    );

    // per ogni cdr della gerarchia del cdr selezionato vengono estratti tutti gli obiettivi-cdr
    foreach (ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo) {
		if ($obiettivo->data_eliminazione == null) {    
            $record = array();
            
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
            
            $info_obiettivo = $record;
            
            $obiettivi_cdr_associati = $obiettivo->getObiettivoCdrAssociati();
            if (count($obiettivi_cdr_associati)>0) {
                foreach ($obiettivi_cdr_associati as $obiettivo_cdr_associato){
                    $record = $info_obiettivo;
                    $obiettivi_cdr_associati_found = false;
                    if ($obiettivo_cdr_associato->data_eliminazione == null){
                        $obiettivi_cdr_associati_found = true;
                        if ($obiettivo_cdr_associato->isObiettivoCdrAziendale()){
                        $desc_tipo_piano = "Aziendale";
                        }
                        else {
                            $desc_tipo_piano = "Cdr";
                        }
                        if ($obiettivo_cdr_associato->isCoreferenza()){
                            $obiettivo_cdr_aziendale = $obiettivo_cdr_associato->getObiettivoCdrAziendale();
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
                        $cdr = AnagraficaCdr::factoryFromCodice($obiettivo_cdr_associato->codice_cdr, $data_riferimento);
                        
                        $record[] = $obiettivo_cdr_associato->id_tipo_piano_cdr;
                        $record[] = $desc_tipo_piano;
                        $record[] = $obiettivo_cdr_associato->codice_cdr;                                
                        $record[] = $cdr->descrizione;                
                        $record[] = $codice_cdr_coreferenza;
                        $record[] = $obiettivo_cdr_associato->peso;
                        $record[] = $azioni;					
                        $record[] = $parere_azioni_desc;
                        $record[] = $obiettivo_cdr_associato->note_azioni;
                        $record[] = $obiettivo_cdr_associato->data_chiusura_modifiche;

                        $matrice_dati[] = $record;                        
                    }
                }  
                //se tutti gli obiettivi trovati sono eliminati logicamente è necessario visualizzare comuneuq l'obiettivo
                if ($obiettivi_cdr_associati_found == false) {
                    $matrice_dati[] = $record;
                }
            }
            else {
                $matrice_dati[] = $record;
            }
        }
    }
    CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
}
else {
	mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);	
}