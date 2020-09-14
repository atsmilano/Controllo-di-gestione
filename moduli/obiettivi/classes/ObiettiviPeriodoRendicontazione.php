<?php
class ObiettiviPeriodoRendicontazione {	
    public $id;
    public $descrizione;
    public $data_riferimento_inizio;
    public $data_riferimento_fine;
    public $ordinamento_anno;
    public $id_anno_budget;
    public $data_termine_responsabile;
    public $allegati;
	
    public function __construct($id = null){				
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                        obiettivi_periodo_rendicontazione.*
                FROM
                        obiettivi_periodo_rendicontazione
                WHERE
                        obiettivi_periodo_rendicontazione.ID = " . $db->toSql($id) 
                ;
            $db->query($sql);
            if ($db->nextRecord()){			
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);			
                $this->data_riferimento_inizio = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_inizio", "Date", true));
                $this->data_riferimento_fine = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_fine", "Date", true));	
                $this->ordinamento_anno = $db->getField("ordinamento_anno", "Number", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);  
                $this->data_termine_responsabile = CoreHelper::getDateValueFromDB($db->getField("data_termine_responsabile", "Date", true));
                $this->allegati = CoreHelper::getBooleanValueFromDB($db->getField("allegati", "Text", true));
            }	
            else
                throw new Exception("Impossibile creare l'oggetto ObiettivoPeriodoRendicontazione con ID = ".$id);
        }
    }
	
    public static function getAll ($filters=array(), $order="ASC") {
        $periodi_rendicontazione = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value)." ";		
        }			       	
        $sql = "SELECT obiettivi_periodo_rendicontazione.*
                FROM obiettivi_periodo_rendicontazione                
                " . $where . "
                ORDER BY obiettivi_periodo_rendicontazione.ordinamento_anno " . $order;
        $db->query($sql);
        if ($db->nextRecord()){            
            do {		
                $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione();
                $periodo_rendicontazione->id = $db->getField("ID", "Number", true);
                $periodo_rendicontazione->descrizione = $db->getField("descrizione", "Text", true);
                $periodo_rendicontazione->data_riferimento_inizio = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_inizio", "Date", true));
                $periodo_rendicontazione->data_riferimento_fine = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_fine", "Date", true));               
                $periodo_rendicontazione->ordinamento_anno = $db->getField("ordinamento_anno", "Number", true);
                $periodo_rendicontazione->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);                			
                $periodo_rendicontazione->data_termine_responsabile = CoreHelper::getDateValueFromDB($db->getField("data_termine_responsabile", "Date", true));
                $periodo_rendicontazione->allegati = CoreHelper::getBooleanValueFromDB($db->getField("allegati", "Text", true));

                $periodi_rendicontazione[] = $periodo_rendicontazione;		                
            }while ($db->nextRecord());           
        }
        return $periodi_rendicontazione;		
    }

    //ritorna l'ultimo periodo di rendicontazione definito nell'anno (null se nessuno definito)
    public static function getUltimoDefinitoAnno (AnnoBudget $anno) {		
        //viene considerato l'ultimo periodo attivo nell'anno per la data selezionata			
        $periodi_rendicontazione = ObiettiviPeriodoRendicontazione::getAll(array("ID_anno_budget" => $anno->id));
        if (count($periodi_rendicontazione)>0)
                return end($periodi_rendicontazione);
        else
                return null;
    }
    
    //ritorna tutti i periodi di rendicontazione dell'anno passato come parametro
    public static function getPeriodiRendicontazioneAnno (AnnoBudget $anno) {
        $filters = array("ID_anno_budget" => $anno->id);
        return ObiettiviPeriodoRendicontazione::getAll($filters);
    }
    
    public function canDelete() {
        return (count(ObiettiviRendicontazione::getAll(array("ID_periodo_rendicontazione" => $this->id))) == 0);
    }
}