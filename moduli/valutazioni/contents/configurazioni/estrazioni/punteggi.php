<?php
if (isset ($_GET["periodo"]) && $periodo_valutazione = new ValutazioniPeriodo($_GET["periodo"])) {
	$valutazioni_estratte = $periodo_valutazione->getValutazioniAttivePeriodo();                  
    //viene ordinato l'array dellle valutazioni in base alla categoria    			
    usort($valutazioni_estratte, "valutazioneCmp");

    $anno_valutazione = new ValutazioniAnnoBudget($periodo_valutazione->id_anno_budget);
    
    $xls_file = "punteggi_".$periodo_valutazione->id."_".date("d-m-Y");
    $nome_foglio_lavoro = "Valutazioni";
    
    //inizializzazione matrice e intestazioni	
    $matrice_dati = array(
        array(
            "ID valutazione",
            "Periodo",
            "Tipologia scheda",
            "Matricola valutato",
            "Matricola valutatore",		
            "Sezione cod.",
            "Sezione desc.",
            "Sezione peso",
            "Ambito cod.",
            "Ambito desc.",
            "Ambito peso",
            "Area Item",
            "Item",
            "Item peso",
            "Punteggio",
        )
    );
            	    
    $id_categoria_prec = -1;

    foreach($valutazioni_estratte as $valutazione){
        $record = array();
                        
        $record[] = $valutazione->id;
        $record[] = $periodo_valutazione->descrizione;

        //se la categoria è cambiata rispetto a quella precedente viene istanziata la nuova categoria
        if ($id_categoria_prec !== $valutazione->id_categoria){
            $categoria = $valutazione->categoria;
            $ambiti_categoria_periodo = $periodo_valutazione->getAmbitiCategoriaPeriodo($categoria);        
            //costruzione dell'array degli ambiti con il relativo peso
            $ambiti_valutazione = array();
            foreach ($ambiti_categoria_periodo as $ambito_valutazione){
                $sezione = new ValutazioniSezione($ambito_valutazione->id_sezione);
                $metodo_val = $ambito_valutazione->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_valutazione);
                if ($metodo_val == 2){                        
                    $items_valutazione = $valutazione->getItemsCategoriaAmbitoValutazione($ambito_valutazione);
                }
                $ambiti_valutazione[] = array(
                                            "ambito" => $ambito_valutazione,
                                            "peso_ambito" => $ambito_valutazione->getPesoAmbitoCategoriaAnno($categoria, $anno_valutazione),
                                            "metodo_valutazione" => $metodo_val,
                                            "sezione" => $sezione,
                                            "peso_sezione_anno" => $sezione->getPesoAnno($anno_valutazione, $categoria),
                                            "items_valutazione" => $items_valutazione,
                                             );
            }                
            $id_categoria_prec = $valutazione->id_categoria;
        }            
        $record[] = $categoria->descrizione;											
        $record[] = $valutazione->matricola_valutato;
        $record[] = $valutazione->matricola_valutatore; 
        $matrice_dati[] = $record;
        $record = array();

        foreach($ambiti_valutazione as $amb){
            $ambito = $amb["ambito"];
            $peso_ambito = $amb["peso_ambito"];
            $metodo_valutazione = $amb["metodo_valutazione"];
            $sezione = $amb["sezione"];
            $peso_sezione = $amb["peso_sezione_anno"];
            $items_valutazione = $amb["items_valutazione"];

            //metodo valutazione: Items
            //neppure gli admin possono modificare il metodo di valutazione
            if ($metodo_valutazione == 2){					
                if(count($items_valutazione) > 0){								
                    foreach($items_valutazione as $item_valutazione){	
                        $record[] = $valutazione->id;
                        $record[] = $periodo_valutazione->descrizione;
                        $record[] = $categoria->descrizione;											
                        $record[] = $valutazione->matricola_valutato;
                        $record[] = $valutazione->matricola_valutatore;
                        $record[] = $sezione->codice;
                        $record[] = $sezione->descrizione;
                        $record[] = $peso_sezione;				
                        $record[] = $ambito->codice;
                        $record[] = $ambito->descrizione;
                        $record[] = $peso_ambito;				
                        $area_item = new ValutazioniAreaItem($item_valutazione->id_area_item);
                        $record[] = $area_item->descrizione;
                        $record[] = $item_valutazione->descrizione;
                        $record[] = $item_valutazione->peso;
                        $record[] = $valutazione->getPunteggioItem($item_valutazione);
                        
                        $matrice_dati[] = $record;
                        $record = array();
                    }
                }                    
            }         
            //metodo valutazione: Ins. backoffice
            //il valore è sempre un raggiungimento percentuale
            else if ($metodo_valutazione == 1){
                $record[] = $valutazione->id;
                $record[] = $periodo_valutazione->descrizione;
                $record[] = $categoria->descrizione;
                $record[] = $valutazione->matricola_valutato;				
                $record[] = $valutazione->matricola_valutatore;
                $record[] = $sezione->codice;
                $record[] = $sezione->descrizione;
                $record[] = $peso_sezione;				
                $record[] = $ambito->codice;
                $record[] = $ambito->descrizione;
                $record[] = $peso_ambito;
                $record[] = $valutazione->getPunteggioAmbito($ambito);
                
                $matrice_dati[] = $record;
                unset($record);
            }						
            else{
                //se nessun metodo specificato ($metodo = 0 casistica che non dovrebbe presentarsi)		
                die("Errore di configurazione ambito " . $nome_ambito);
            }            
        }
    } 			
    CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
}
else {
	mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);
	ffRedirect(FF_SITE_PATH . "/area_riservata/estrazioni");
}
die();

function valutazioneCmp ($val1, $val2) {
    if ($val1->id_categoria > $val2->id_categoria) {
        return 1;		
    }		
}