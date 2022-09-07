<?php
class IndicatoriIndicatore extends Entity{		
    protected static $tablename = "indicatori_indicatore";
    
    //vengono restituiti tutti i parametri associati all'indicatore, comprensiuvi dell'id della relazione
    public function getParametri() {
        $parametri_indicatore = array();
        //l'ordinamento viene effettuato su ID con direction ASC per coerenza con le specifiche della formula
        foreach(IndicatoriParametroIndicatore::getAll(array("ID_indicatore"=>$this->id),array(array("fieldname"=>"ID", "direction"=>"ASC"))) as $parametro_indicatore) {
            //viene aggiunto al parametro anche l'id della relazione
            $parametro = new IndicatoriParametro ($parametro_indicatore->id_parametro);
            $parametro->parametro_indicatore = $parametro_indicatore;
            $parametri_indicatore[] = $parametro;
        }
        return $parametri_indicatore;
    }
    
    //restituisce tutti gli indicatori attivi in un anno specifico
    public static function getIndicatoriAnno (AnnoBudget $anno) {
        $indicatori_anno = array();    
        foreach(IndicatoriIndicatore::getAll() AS $indicatore){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($indicatore->data_introduzione) <= strtotime($anno->descrizione."-12-31") 
                && ($indicatore->data_termine == null || strtotime($indicatore->data_termine) >= strtotime($anno->descrizione."-01-01"))){               
                $indicatori_anno[] = $indicatore;                				
            }
        }			
        return $indicatori_anno;
    }
    
    //restituisce tutti gli obiettivi in cui l'indicatore è utilizzato (possibilità di specificare un anno), accodando l'ID della relazione
    public function getObiettiviCollegati (AnnoBudget $anno=null) {
        $obiettivi_associati = array();
        $filters = array("ID_indicatore" => $this->id);        
		foreach (IndicatoriObiettivoIndicatore::getAll($filters) AS $obiettivo_indicatore) {      
            $obiettivo = new ObiettiviObiettivo($obiettivo_indicatore->id_obiettivo);
            if ($obiettivo->data_eliminazione == null & ($anno !== null || $anno->id == $obiettivo->id_anno_budget)) {
                $obiettivo->obiettivo_indicatore = $obiettivo_indicatore;
                $obiettivi_associati[] = $obiettivo;
            }			
		}
        return $obiettivi_associati; 
    }
    
    //restituisce tutti i valori target collegati all'indicatore nell'anno
    public function getValoriTargetAnno (AnnoBudget $anno) {
        $filters = array("ID_indicatore" => $this->id, "ID_anno_budget" => $anno->id);
        return IndicatoriValoreTarget::getAll($filters);
    }
    
    //restituisce il valore dell'indicatore nell'anno
    //se cdr == null restituisce valore aziendale
    //se cdr !== null restituisce valore target associato a cdr
    //  oppure il primo associato sui padri gerarchici
    //  oppure il valore aziendale se $force_cdr non è definito a true
    //se non è definito neppure il valore aziendale viene restituito null
    public function getValoreTargetAnno (AnnoBudget $anno, Cdr $cdr = null, $force_cdr = false) {       
        $filters = array("ID_indicatore" => $this->id, "ID_anno_budget" => $anno->id);        
        //se si sta verificando il valore target per il cdr
        if ($cdr !== null) {          
            $filters["codice_cdr"] = $cdr->codice;
            //viene restituito il primo valore target restituito, in tabella dovrebbe esistere al più un valore target definito per indicatore-anno con codice cdr vuoto		
            foreach (IndicatoriValoreTarget::getAll($filters) AS $valore_target_indicatore_anno) {
                return $valore_target_indicatore_anno->valore_target;
            }
            //se non è stato trovato nessun valore target, nel caso sia stato definito un cdr si cerca di recuperare il valore target del padre
            if ($cdr->id_padre !== 0) {
                $cdr_padre = new Cdr ($cdr->id_padre);
                return $this->getValoreTargetAnno ($anno, $cdr_padre);            
            }
        }   
        if ($force_cdr == true) {
            //viene restituito il valore aziendale
            $filters["codice_cdr"] = null;
            foreach (IndicatoriValoreTarget::getAll($filters) AS $valore_target_indicatore_anno) {
                return $valore_target_indicatore_anno->valore_target;
            }
        }
        //se non è stato trovato nessun valore viene restituito null
        return null;
    }   
    
    //viene visualizzata la formula per il calcolo degli indicatori con il nome o il valore dei parametri ($parametri_richiesta valorizzato) utilizzati
    function visualizzazioneFormulaRisultatoIndicatore($parametri_richiesta = false) {
        //vengono sostituiti i parametri nella formula delcalcolo del risultato
        $formula_calcolo_risultato = $this->formula_calcolo_risultato;        
        $parametri_indicatore = array_reverse($this->getParametri());
        foreach ($parametri_indicatore as $key => $parametro) {
            if ($parametri_richiesta !== false) {
                $found = null;
                if(is_array($parametri_richiesta)) {
                    $parametri_richiesta = array_reverse($parametri_richiesta);
                }

                foreach ($parametri_richiesta as $valore_parametro) {
                    if ($valore_parametro["id_parametro_indicatore"] == $parametro->parametro_indicatore->id) {
                        $found = $valore_parametro["valore"];
                        break;
                    }
                }
                if ($found !== null) {
                    $visualizzato = $found;
                }
                else {
                    $visualizzato = "ND";
                }
            }
            else {
                $visualizzato = $parametro->nome;
            }           
            $n_incrementale_parametro_formula = count($parametri_indicatore) - $key;
            $parametro_formula = OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA.$n_incrementale_parametro_formula;
            $formula_calcolo_risultato = str_replace($parametro_formula, " [".$visualizzato."] ", $formula_calcolo_risultato);
        }
        return $formula_calcolo_risultato;
    }
    
    //viene visualizzata la formula per il calcolo del raggiungimento dell'indicatore
    function visualizzazioneFormulaRaggiungimentoIndicatore() {                
        //vengono sostituiti i parametri nella formula delcalcolo del risultato
        $formula_calcolo_raggiungimento = strtolower($this->formula_calcolo_raggiungimento);
        $formula_calcolo_raggiungimento = str_replace(OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA."r", "[risultato]", $formula_calcolo_raggiungimento);
        $formula_calcolo_raggiungimento = str_replace(OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA."t", "[valore target]", $formula_calcolo_raggiungimento);                      
        return $formula_calcolo_raggiungimento;
    }

    //calcolo di risultato, valore target e raggiungimento dell'indicatore di un cdr nel cruscotto degli indicatori
    //restituisce un array con i valori calcolati 
    //array("parametri" => array(), "risultato" => ,"valore_target" => ,"raggiungimento" => );
    //restituisce null se valore non calcolabile    
    function getValoriCruscottoCdr(Cdr $cdr, $anno, IndicatoriPeriodoCruscotto $periodo_cruscotto = null) {        
        //recupero del valore dei parametri per il calcolo del risultato / raggiungimento
        $risultato_indicatore = null;
        
        //se anche un parametro è mancante non viene calcolato il risultato
        $calcolo_risultato = true;
        $parametri_calcolo = array();
        $valore_target_indicatore = $this->getValoreTargetAnno($anno, $cdr);
        $raggiungimento_indicatore = null;
        
        foreach ($this->getParametri() as $parametro) {
            $valore_parametro = $parametro->getUltimoValoreParametroIndicatoreRilevatoCruscotto($periodo_cruscotto, $cdr->codice);
            if ($valore_parametro !== null) {
                $parametri_calcolo[] = array(
                    "id_parametro_indicatore" => $parametro->parametro_indicatore->id, 
                    "valore" => $valore_parametro->valore
                );
            } else {
                $calcolo_risultato = false;
                break;
            }
        }
        if ($calcolo_risultato == true) {
            $risultato = $this->calcoloRisultatoIndicatore($parametri_calcolo);
            if ($risultato["esito"] == "success") {
                $risultato_indicatore = $risultato["risultato"];
                //se è stato definito un valore target si cerca di calcolare il raggiungimento                
                if ($valore_target_indicatore !== null) {
                    $raggiungimento_indicatore = $this->calcoloRaggiungimentoIndicatore(
                        $risultato_indicatore, $valore_target_indicatore
                    );
                    if ($raggiungimento_indicatore["esito"] == "success") {
                        $raggiungimento_indicatore = $raggiungimento_indicatore["risultato"];
                    } else {
                        $raggiungimento_indicatore = null;
                    }
                } else {
                    $valore_target_indicatore = null;
                }
            }
        }
        return array(
            "parametri" => $parametri_calcolo,
            "risultato" => $risultato_indicatore,
            "valore_target" => $valore_target_indicatore,
            "raggiungimento" => $raggiungimento_indicatore,
        );
    }
    
    //viene calcolato esito, risultato e messaggio del calcolo
    //parametri richiesta = array(array("id_parametro_indicatore"=>id_parametro_indicatore, "valore"=>valore))
    public function calcoloRisultatoIndicatore($parametri_richiesta) {
        $risultato = null;
        $errori = false;
        $messaggio = null;

        //vengono recuperati tutti i parametri dell'indicatore in un array
        $parametri_indicatore = array();    
        foreach ($this->getParametri() as $parametro) {
            $parametri_indicatore[] = $parametro;
        }
        //vengono valorizzati i valori dei parametri dell'indicatore in base ai valori passati nella richiesta
        //e sostituiti nella formula delcalcolo del risultato
        $formula_calcolo_risultato = $this->formula_calcolo_risultato;

        //Reverse dei parametri_richiesta per sostituire in formula_calcolo_risoltato da destra verso sinistra
        $parametri_richiesta = array_reverse($parametri_richiesta);
        //Reverse dei parametri_indicatore in modo da non alterare il funzionamento in seguito alla modifica per la sostituzione da
        //destra verso sinistra
        $parametri_indicatore = array_reverse($parametri_indicatore);

        foreach($parametri_richiesta as $parametro_richiesta) {                
            //viene formattato il numero correttamente per il calcolo
            $parametro_richiesta["valore"] = str_replace(",",".",str_replace(".","",$parametro_richiesta["valore"]));
            //se il valore è nullo o non numerico viene sostituito con 0 
            if (!is_numeric($parametro_richiesta["valore"])) {
                $parametro_richiesta["valore"] = 0;
            }
            foreach ($parametri_indicatore as $key => $parametro_indicatore) {             
                if ($parametro_richiesta["id_parametro_indicatore"] == $parametro_indicatore->parametro_indicatore->id) {
                    //$n_incrementale_parametro_formula = $key+1;
                    $n_incrementale_parametro_formula = count($parametri_indicatore) - $key;
                    $parametro_formula = OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA.$n_incrementale_parametro_formula;
                    $formula_calcolo_risultato = str_replace($parametro_formula, $parametro_richiesta["valore"], $formula_calcolo_risultato);
                    break;
                }
            }
        }

        //verifica sulla formula, se sono presenti identificatori di parametri significa che non tutti i parametri necessari sono stati passati     
        if (stripos($formula_calcolo_risultato, OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA) !== false) {
            $errori = true;
            $messaggio = "Errore nel passaggio dei parametri: valorizzazione di tutti i parametri della formula mancanti.";
        }

        //Se non si sono verificati errori viene calcolato il risultato
        if ( $errori !== true) {        
            $evaluator = new Evaluator($formula_calcolo_risultato);        
            $risultato = $evaluator->evaluate();
            if (is_nan($risultato) || is_infinite($risultato)) {
                $errori = true;
                $risultato = null;            
                $messaggio = "Errore nel calcolo del risultato";
            }
        }
        
        //viene restituito il risultato in base all'esito del calcolo
        if ($errori == true) {    
            $esito = "error";
        }
        else {
            $esito = "success";
        }

        //invio della risposta json
        //se numero intero non vengono visualizzati i decimali
        if ((float)$risultato != (int)$risultato) {
            $risultato = number_format($risultato,2,",",".");
        }
        
        //viene restituito un array con le informazioni
        return array(    
            "risultato" => $risultato,    
            "esito" => $esito,
            "messaggio" => $messaggio,
        );
    }
    
    //dato un indicatore e i parametri viene calcolato esito, risultato e messaggio del calcolo
    public function calcoloRaggiungimentoIndicatore($risultato, $valore_target) {
        $raggiungimento = null;
        $errori = false;
        $messaggio = null;

        //viene recuperato il parametro risultato (non necessita di formattazione)
        $risultato = str_replace(",",".",str_replace(".","",$risultato));
        //se il valore è nullo o non numerico viene sostituito con 0 
        if (!is_numeric($risultato)) {
            $risultato = 0;
        }    
        //vengono sostituiti i valori nella formula
        $formula_calcolo_raggiungimento = strtolower($this->formula_calcolo_raggiungimento);
        $formula_calcolo_raggiungimento = str_replace(OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA."r", $risultato, $formula_calcolo_raggiungimento);
        $formula_calcolo_raggiungimento = str_replace(OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA."t", $valore_target, $formula_calcolo_raggiungimento);              

        //Se non si sono verificati errori viene calcolato il risultato
        if ( $errori !== true) {        
            $evaluator = new Evaluator($formula_calcolo_raggiungimento);        
            $raggiungimento = $evaluator->evaluate();
            if (is_nan($raggiungimento) || is_infinite($raggiungimento)) {
                $errori = true;
                $raggiungimento = null;            
                $messaggio = "Errore nel calcolo del raggiungimento";
            }
        }      

        //viene restituito il risultato in base all'esito del calcolo
        if ($errori == true) {    
            $esito = "error";
        }
        else {
            $esito = "success";
        }

        //invio della risposta json
        //se numero intero non vengono visualizzati i decimali
        if ((float)$raggiungimento != (int)$raggiungimento) {
            $raggiungimento = number_format($raggiungimento,2,",",".");
        }
  
        //viene restituito un array con le informazioni
        return array(    
            "risultato" => $raggiungimento,    
            "esito" => $esito,
            "messaggio" => $messaggio,
        );
    }
}