<?php
$anno = new AnnoBudget($_GET["anno"]);
$cdr = $cm->oPage->globals["cdr"]["value"];
$date = CoreHelper::getDataRiferimentoBudget($anno);

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

//estrazione dei cdr figli associati all'obiettivo (anche il cdr selezionato)
$cdr_obiettivi = array($cdr);
$cdr_figli = $cdr->getFigli();
foreach($cdr_figli as $cdr_figlio){
    $cdr_obiettivi[] = $cdr_figlio;
}

foreach($cdr_obiettivi as $cdr_assegnazione){        
    $anagrafica_cdr_obiettivo = AnagraficaCdrObiettivi::factoryFromCodice($cdr_assegnazione->codice, $date);
                 	
    foreach ($anagrafica_cdr_obiettivo->getObiettiviCdrAnno($anno) as $obiettivo_cdr) {                
        if ($obiettivo_cdr->data_eliminazione == null){
            $record = array();
            //TODO evitare di istanziare tutte le volte l'obiettivo
            $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);

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
        
            $obiettivi_cdr_associati_found = true;
            if ($obiettivo_cdr->isObiettivoCdrAziendale()){
            $desc_tipo_piano = "Aziendale";
            }
            else {
                $desc_tipo_piano = "Cdr";
            }
            if ($obiettivo_cdr->isCoreferenza()){
                $obiettivo_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale();
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
                $azioni = $obiettivo_cdr->azioni;
                try{
                    $parere_azioni = new ObiettiviParereAzioni($obiettivo_cdr->id_parere_azioni);
                    $parere_azioni_desc = $parere_azioni->descrizione;
                } 				
                catch (Exception $ex) {
                    $parere_azioni_desc = "Non definite";
                }
                $codice_cdr_coreferenza = "";
            }

            if ($obiettivo_cdr->id_tipo_piano_cdr == 0) {
                $desc_tipo_piano = "Aziendale";
            }
            else {
                $tipo_piano = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
                $desc_tipo_piano = $tipo_piano->descrizione;
            }
            $record[] = $obiettivo_cdr->id_tipo_piano_cdr;
            $record[] = $desc_tipo_piano;
            $record[] = $obiettivo_cdr->codice_cdr;                                
            $record[] = $cdr_assegnazione->descrizione;                
            $record[] = $codice_cdr_coreferenza;
            $record[] = $obiettivo_cdr->peso;
            $record[] = $azioni;					
            $record[] = $parere_azioni_desc;
            $record[] = $obiettivo_cdr->note_azioni;
            $record[] = $obiettivo_cdr->data_chiusura_modifiche;
            
            $matrice_dati[] = $record;
        }
    }   
}

CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));