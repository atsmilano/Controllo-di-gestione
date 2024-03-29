<?php
class ObiettiviObiettivo extends Entity{
    protected static $tablename = "obiettivi_obiettivo";

    public function __construct($id = null) {
        parent::__construct($id);
        //generazione codice
        $anno_budget = new AnnoBudget($this->id_anno_budget);
        $this->codice = $anno_budget->descrizione . "-" . str_pad($this->codice_incr_anno, 4, "0", STR_PAD_LEFT) . $this->suffisso_codice;                    
    }

    public static function getAll($where=array(), $order=array()) {                
        //metodo classe entity
        $obiettivi = parent::getAll($where, $order);
        foreach ($obiettivi as $key => $obiettivo) {
            $anno_budget = new AnnoBudget($obiettivo->id_anno_budget);
            $obiettivi[$key]->codice = $anno_budget->descrizione . "-" . str_pad($obiettivo->codice_incr_anno, 4, "0", STR_PAD_LEFT) . $obiettivo->suffisso_codice;            
        }
        return $obiettivi;
    }        

    //entità collegate
    public function getObiettivoCdrAssociati($cdr = null) {
        $ob_cdr_associati = array();
        $filters = array("ID_obiettivo" => $this->id);
        if ($cdr !== null && !isset($cdr->codice)) {
            throw new Exception("Parametro non valido");
        }
        foreach (ObiettiviObiettivoCdr::getAll($filters) AS $cdr_obiettivo) {
            if ($cdr_obiettivo->data_eliminazione == null) {
                $ob_cdr_associati[] = $cdr_obiettivo;
            }
        }
        return $ob_cdr_associati;
    }

    //verifica che un cdr sia associato all'obiettivo
    public function isCdrAssociato(AnagraficaCdr $anagrafica_cdr) {
        $cdr_associato = false;
        foreach ($this->getObiettivoCdrAssociati() as $obiettivo_cdr_associato) {
            if ($obiettivo_cdr_associato->codice_cdr == $anagrafica_cdr->codice) {
                $cdr_associato = true;
                break;
            }
        }
        return $cdr_associato;
    }

    //indicatori
    public function getIndicatoriAssociati() {
        $indicatori_associati = array();
        $filters = array("ID_obiettivo" => $this->id);
        foreach (IndicatoriObiettivoIndicatore::getAll($filters) AS $obiettivo_indicatore) {
            $indicatore = new IndicatoriIndicatore($obiettivo_indicatore->id_indicatore);
            $indicatore->obiettivo_indicatore = $obiettivo_indicatore;
            $indicatori_associati[] = $indicatore;
        }
        return $indicatori_associati;
    }

    //metodo per la visualizzazione delle informazioni dell'obiettivo_cdr in html
    public function showHtmlInfo() {
        //visualizzazione delle informazioni dell'obiettivo
        $origine = new ObiettiviOrigine($this->id_origine);
        $tipo = new ObiettiviTipo($this->id_tipo);
        $area_risultato = new ObiettiviAreaRisultato($this->id_area_risultato);
        $area = new ObiettiviArea($this->id_area);
        $html = "
                <div class='form-group clearfix padding'>
                    <label>Codice</label>
                    <span class='form-control readonly'>" . $this->codice . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Titolo</label>
                    <span class='form-control readonly'>" . $this->titolo . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Descrizione</label>
                    <span class='form-control readonly'>" . $this->descrizione . "</span>
                </div>                
                <div class='form-group clearfix padding'>
                    <label>Origine</label>
                    <span class='form-control readonly'>" . $origine->descrizione . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Tipo</label>
                    <span class='form-control readonly'>" . $tipo->descrizione . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Area Risultato</label>
                    <span class='form-control readonly'>" . $area_risultato->descrizione . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Area</label>
                    <span class='form-control readonly'>" . $area->descrizione . "</span>
                </div>
                <div class='form-group clearfix padding'>
                    <label>Indicatori</label>
                    <span class='form-control readonly'>" . $this->indicatori . "</span>
                </div>                
                ";
        return $html;
    }
    
    public function riaperturaObiettiviCdrCollegati() {
        $obiettivo_cdr_associati = $this->getObiettivoCdrAssociati();
        
        foreach ($obiettivo_cdr_associati as $obiettivo_cdr_associato) {
            $obiettivo_cdr_associato->riaperturaObiettivoCdr();
        }
    }
 
    public static function isValidRangeAnno($anno_introduzione, $anno_termine) {
        if (!empty($anno_termine) && $anno_termine < $anno_introduzione) {
            return false;
        }

        return true;
    }
    
    public static function checkVincoliAnniConfigurazioni($anno_introduzione, $anno_termine, $obiettivi) {
        foreach($obiettivi as $obiettivo) {
            $anno_budget_obiettivo = new AnnoBudget($obiettivo->id_anno_budget);

            if(!$obiettivo->isAnnoTermineConfigurazioniValido($anno_budget_obiettivo, $anno_termine)) {
                return "L'anno termine inserito non è valido per l'oggetto selezionato.";
            }

            if(!$obiettivo->isAnnoIntroduzioneConfigurazioniValido($anno_budget_obiettivo, $anno_introduzione)) {
                return "L'anno introduzione inserito non è valido per l'oggetto selezionato.";
            }
        }

        return false;
    }
    
    private function isAnnoTermineConfigurazioniValido(AnnoBudget $anno_budget_obiettivo, $anno_termine) {
        return empty($anno_termine) || $anno_termine >= $anno_budget_obiettivo->descrizione;
    }

    private function isAnnoIntroduzioneConfigurazioniValido(AnnoBudget $anno_budget_obiettivo, $anno_introduzione) {
        return $anno_introduzione <= $anno_budget_obiettivo->descrizione;
    }
    
    //dato un obiettivo e un array del raggiungimento degli indicatori associati viene calcolato il raggiungimento dell'obiettivo
    //array(array("obiettivo_indicatore" => ObiettivoIndicatore , "raggiungimento" => raggiungimento));
    public function calcoloRaggiungimentoObiettivo($raggiungimento_indicatori) {        
        $raggiungimento = null;
        $errori = false;
        $messaggio = null;
        //vengono sostituiti i valori nella formula
        $formula_calcolo_raggiungimento = $this->formula_calcolo_raggiungimento;
        //per ogni indicatore associato all'obiettivo vengono recuperati i parametri
        $n_parametro_calcolo = 0;
        //l'ordinamento dell'array del raggiungimento è lo stesso di quello mantenuto da getIndicatoriAssociati
        foreach($this->getIndicatoriAssociati() as $indicatore) {
            //viene verificata la corrispondenza con i parametri dell'array di raggiungimento passato (ridondanza)
            foreach ($raggiungimento_indicatori as $raggiungimento_indicatore) {
                if ($raggiungimento_indicatore["obiettivo_indicatore"]->id_obiettivo == $this->id
                    && $raggiungimento_indicatore["obiettivo_indicatore"]->id_indicatore == $indicatore->id) {
                    //viene recuperato il raggiungimento (non necessita di formattazione)
                    $raggiungimento = str_replace(",",".",str_replace(".","",$raggiungimento_indicatore["raggiungimento"]));
                    //se il valore è nullo o non numerico viene sostituito con 0 
                    if (!is_numeric($raggiungimento)) {
                        $raggiungimento = 0;
                    }                 
                    $formula_calcolo_raggiungimento = str_replace(OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA.++$n_parametro_calcolo, 
                                                                    $raggiungimento, 
                                                                    $formula_calcolo_raggiungimento); 
                }                
            }                                    
        } 
        
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
        
        //nel caso in cui il raggiungimento sia superiore a 100 viene restituito 100 (valore massimo percentuale)
        if ($raggiungimento > 100) {
            $raggiungimento = 100;
        }
        //invio della risposta json
        //se numero intero non vengono visualizzati i decimali
        else if ((float)$raggiungimento != (int)$raggiungimento) {
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