<?php
if (isset ($_GET["periodo"])) {
    $periodo = new ObiettiviPeriodoRendicontazione($_GET["periodo"]);
	$anno = new AnnoBudget($periodo->id_anno_budget);
	$data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
    $date = $data_riferimento;
    if ($periodo->id_campo_revisione != null) {
        $campo_revisione = new ObiettiviCampoRevisione($periodo->id_campo_revisione);
        $scelte_campo_revisione = $campo_revisione->getScelte();
    }
    
    $cdr = $cm->oPage->globals["cdr"]["value"];    
		
	$xls_file = "obiettivi-cdr-".$anno->descrizione."_".date("Ymd");
	$nome_foglio_lavoro = "Obiettivi_cdr";
	
    //inizializzazione matrice e intestazioni	
    $matrice_dati = array(
        array(
            "Codice obiettivo", 
            "Titolo", 
            "Descrizione", 
            "Indicatori di performance",
            "Tipo obiettivo", 
            "CdR",
            "CdR Referente aziendale dell’obiettivo (se diverso)",            
            "Peso", 
            "Azioni programmate", 
            "Periodo", 
            "Azioni", 
            "Provvedimenti (delibere e determinazioni)", 
            "Criticità", 
            "Misurazione indicatori", )            
    );
    if ($periodo->hide_raggiungibile != 1) {
        $matrice_dati[0][] = "Raggiungibile";
    }
    if ($periodo->id_campo_revisione != null) {
        $matrice_dati[0][] = $campo_revisione->nome;
    }
    array_push($matrice_dati[0],
        "allegati",
        "%raggiungimento", 
        "%validata", 
        "Note validazione raggiungimento"
    );
	
    // per ogni cdr della gerarchia del cdr selezionato vengono estratti tutti gli obiettivi-cdr
    foreach ($cdr->getGerarchia() as $cdr_figlio) {         
        $anagrafica_cdr_figlio = AnagraficaCdrObiettivi::factoryFromCodice($cdr_figlio["cdr"]->codice, $date);
        $obiettivi_cdr_figlio_anno = $anagrafica_cdr_figlio->getObiettiviCdrAnno($anno);
        if (count($obiettivi_cdr_figlio_anno)) {            
            foreach($obiettivi_cdr_figlio_anno as $obiettivo_cdr_associato){
                $record = array();
                $obiettivo = new ObiettiviObiettivo($obiettivo_cdr_associato->id_obiettivo);
                $tipo = new ObiettiviTipo($obiettivo->id_tipo);	                                                

                $record[] = $obiettivo->codice;
                $record[] = $obiettivo->titolo;
                $record[] = $obiettivo->descrizione;
                $record[] = $obiettivo->indicatori;
                $record[] = $tipo->descrizione;
                
                $is_coreferenza = $obiettivo_cdr_associato->isCoreferenza();
                $rendicontazione = $obiettivo_cdr_associato->getRendicontazionePeriodo($periodo);
                if ($is_coreferenza) {                    
                    $obiettivo_cdr_aziendale = $obiettivo_cdr_associato->getObiettivoCdrAziendale();
                    $rendicontazione_aziendale = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo);
                    $anagrafica_cdr_coreferenza = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_aziendale->codice_cdr, $date);
                    $tipo_cdr = new TipoCdr($anagrafica_cdr_coreferenza->id_tipo_cdr);
                    $cdr_coreferenza_desc = $anagrafica_cdr_coreferenza->codice . " - " . $tipo_cdr->abbreviazione . " " . $anagrafica_cdr_coreferenza->descrizione;
                    $azioni = $obiettivo_cdr_aziendale->azioni;
                    if ($rendicontazione !== null){
                        $rendicontazione->raggiungibile = true;
                        $rendicontazione->perc_nucleo = $rendicontazione->perc_raggiungimento;
                        $rendicontazione->note_nucleo = "Raggiungimento obiettivo trasversale specifico per il CdR";
                        $rendicontazione_aziendale !== null?$rendicontazione->note_nucleo.=" (Raggiungimento CdR Referente: ".$rendicontazione_aziendale->perc_nucleo."% ).":$rendicontazione->note_nucleo.=" (Raggiungimento referente non ancora validato).";                                                                            
                    }
                    else {
                        $rendicontazione = $rendicontazione_aziendale;
                    }  
                }
                else { 
                    $cdr_coreferenza_desc = "";
                    if ($is_coreferenza) {                       
                        $anagrafica_cdr_coreferenza = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_aziendale->codice_cdr, $date);
                        $tipo_cdr = new TipoCdr($anagrafica_cdr_coreferenza->id_tipo_cdr);
                        $cdr_coreferenza_desc = $anagrafica_cdr_coreferenza->codice . " - " . $tipo_cdr->abbreviazione . " " . $anagrafica_cdr_coreferenza->descrizione;
                    }                                                       
                    $azioni = $obiettivo_cdr_associato->azioni;
                    try {
                        $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr_associato->id_parere_azioni);
                        $parere_azioni_desc = $parere_azioni->descrizione;
                    } catch (Exception $ex) {
                        $parere_azioni_desc = "Non definite";
                    }
                    $codice_cdr_coreferenza = "";
                }       
                $tipo_cdr = new TipoCdr($cdr_figlio["cdr"]->id_tipo_cdr);
                $record[] = $obiettivo_cdr_associato->codice_cdr . " - " . $tipo_cdr->abbreviazione . " " . $cdr_figlio["cdr"]->descrizione;
                $record[] = $cdr_coreferenza_desc;
                $record[] = $obiettivo_cdr_associato->peso;
                $record[] = $azioni;		

                if ($rendicontazione !== null){
                    $record[] = $periodo->descrizione;
                    $record[] = $rendicontazione->azioni;
                    $record[] = $rendicontazione->provvedimenti;
                    $record[] = $rendicontazione->criticita;
                    $record[] = $rendicontazione->misurazione_indicatori;                           
                    if ($periodo->hide_raggiungibile != 1) {
                        $record[] = $rendicontazione->raggiungibile == 1?"Si":"No";
                    }
                    if ($periodo->id_campo_revisione != null) {
                        $scelta_campo_revisione = "";
                        foreach ($scelte_campo_revisione as $scelta) {
                            if ($rendicontazione->id_scelta_campo_revisione == $scelta->id) {
                                $scelta_campo_revisione = $scelta->descrizione;
                                break;
                            }
                        }                                
                        $record[] = $scelta_campo_revisione;
                    } 
                    if (count(ObiettiviRendicontazioneAllegato::getAll(['rendicontazione_id' => $rendicontazione->id]))) {
                        $record [] = "Si";
                    }
                    else {
                        $record[] = "No";
                    }
                    
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