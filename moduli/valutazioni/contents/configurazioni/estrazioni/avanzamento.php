<?php
if (isset ($_GET["periodo"]) && $periodo_valutazione = new ValutazioniPeriodo($_GET["periodo"])) {
	$valutazioni_attive = $periodo_valutazione->getValutazioniAttivePeriodo();
	if (count($valutazioni_attive) > 0) {                
        $xls_file = "avanzamento_".$periodo_valutazione->id."_".date("d-m-Y");
        $nome_foglio_lavoro = "Valutazioni";
    
        //inizializzazione matrice e intestazioni	
        $matrice_dati = array(
            array(
                "ID valutazione",
                "Periodo",
                "Tipologia scheda",
                "Matricola valutato",
                "Cognome valutato",
                "Nome valutato",
                "Matricola valutatore",
                "Cognome valutatore",
                "Nome valutatore",
                "Data chiusura autovalutazione",
                "Data ultimo colloquio",
                "Data firma valutatore",
                "Note valutatore",
                "Data firma valutato",
                "Note valutato",
            )
        );
        			
		foreach ($valutazioni_attive as $valutazione){			
            $record = array();
            try {
                $valutato = Personale::factoryFromMatricola($valutazione->matricola_valutato);  
                $cognome_valutato = $valutato->cognome;
                $nome_valutato = $valutato->nome;
            } catch (Exception $e){
                $cognome_valutato = "";
                $nome_valutato = "";
            }
            try {
                $valutatore = Personale::factoryFromMatricola($valutazione->matricola_valutatore);  
                $cognome_valutatore = $valutatore->cognome;
                $nome_valutatore = $valutatore->nome;
            } catch (Exception $e){
                $cognome_valutatore = "";
                $nome_valutatore = "";
            }
                        
			$record[] = $valutazione->id;
			$record[] = $periodo_valutazione->descrizione;
			$categoria = $valutazione->categoria;
			$record[] = $categoria->descrizione;										
			$record[] = $valutazione->matricola_valutato;
			$record[] = $cognome_valutato;
			$record[] = $nome_valutato;
			$record[] = $valutazione->matricola_valutatore;
			$record[] = $cognome_valutatore;
			$record[] = $nome_valutatore;	
			$record[] = $valutazione->data_chiusura_autovalutazione;
			$record[] = $valutazione->data_ultimo_colloquio;
			$record[] = $valutazione->data_firma_valutatore;
			$record[] = $valutazione->note_valutatore;
			$record[] = $valutazione->data_firma_valutato;
			$record[] = $valutazione->note_valutato;           
            foreach ($valutazione->getTotaliPreCalcolati() as $totale) {
                $record[] = $totale["totale_calcolo"];
            }            
            $matrice_dati[] = $record;
            unset($record);
		}
        CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
	}
}
else {
	mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);
	ffRedirect(FF_SITE_PATH . "/area_riservata/estrazioni");
}
die();