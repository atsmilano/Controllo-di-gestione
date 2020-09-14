<?php
class ResponsabileCdr {		
    public $id;
    public $matricola_responsabile;
    public $codice_cdr;
    public $data_inizio;	
    public $data_fine;
    // Derivanti da JOIN su tbl personale
    public $id_personale;
    public $cognome;
    public $nome;
    public $useSql;
        
    public function __construct($id = null, $useSql = false){
        if ($id !== null) {
            foreach(TableResponsabileCdr::Instance()->getRespCdrInData() as $resp_cdr) {
                if(array_key_exists($id, $resp_cdr)) {
                    $this->useSql = false;
                    $this->id = $resp_cdr[$id]->id;
                    $this->matricola_responsabile = $resp_cdr[$id]->matricola_responsabile;
                    $this->codice_cdr = $resp_cdr[$id]->codice_cdr;
                    $this->data_inizio = $resp_cdr[$id]->data_inizio;
                    $this->data_fine = $resp_cdr[$id]->data_fine;
                    $this->id_personale = $resp_cdr[$id]->id_personale;
                    $this->cognome = $resp_cdr[$id]->cognome;
                    $this->nome = $resp_cdr[$id]->nome;
                } else {
                    $this->useSql = true;
                }
            }
            
            if($this->useSql) {
                $db = ffDb_Sql::factory();

                $sql = "
                    SELECT responsabile_cdr.*, personale.ID AS 'ID_personale', personale.cognome, personale.nome
                    FROM responsabile_cdr
                        INNER JOIN personale ON (responsabile_cdr.matricola_responsabile = personale.matricola)
                    WHERE responsabile_cdr.ID = " . $db->toSql($id) 
                ;

                $db->query($sql);

                if ($db->nextRecord()) {
                    $this->id = $db->getField("ID", "Number", true);
                    $this->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                    $this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                    $this->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                    $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));

                    $this->id_personale = $db->getField("ID_personale", "Number", true);
                    $this->cognome = $db->getField("cognome", "Text", true);
                    $this->nome = $db->getField("nome", "Text", true);
                }	
                else {
                    throw new Exception("Impossibile creare l'oggetto ResponsabileCdr con ID = ".$id);
                }
            }
        } 
    }
    
    //voiene istanziato l'oggetto tramite codice_cdr e una data di riferimento
    public static function factoryFromCodiceCdr($codice_cdr, DateTime $date) {        
        $cm = cm::getInstance();
        $responsabile_cdr = null;
        $dateCm = $cm->oPage->globals["data_riferimento"]["value"];

        if(strcmp($date->format('Y-m-d'), $dateCm->format('Y-m-d')) == 0) {
            foreach(TableResponsabileCdr::Instance()->getRespCdrInData() as $resp_cdr) {                
                if($resp_cdr->codice_cdr == $codice_cdr) {                                                                               
                    $responsabile_cdr = $resp_cdr;
                }
            }
            if ($responsabile_cdr == null){
                throw new Exception("Responsabile Cdr non definito per Cdr: ".$codice_cdr);
            }
        } else { 
            $found = false;
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT responsabile_cdr.*, personale.ID AS 'ID_personale', personale.cognome, personale.nome
                FROM responsabile_cdr
                    INNER JOIN personale ON (responsabile_cdr.matricola_responsabile = personale.matricola)
                WHERE responsabile_cdr.codice_cdr = " . $db->toSql($codice_cdr) 
            ;                                                
            $db->query($sql);
            if ($db->nextRecord()) {               
                do {
                    $responsabile_cdr = new ResponsabileCdr();

                    $responsabile_cdr->id = $db->getField("ID", "Number", true);
                    $responsabile_cdr->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                    $responsabile_cdr->codice_cdr = $db->getField("codice_cdr", "Text", true);
                    $responsabile_cdr->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                    $responsabile_cdr->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));

                    $responsabile_cdr->id_personale = $db->getField("ID_personale", "Number", true);
                    $responsabile_cdr->cognome = $db->getField("cognome", "Text", true);
                    $responsabile_cdr->nome = $db->getField("nome", "Text", true);
                    
                    if (
                        strtotime($responsabile_cdr->data_inizio) <= strtotime($date->format("Y-m-d")) && (
                            $responsabile_cdr->data_fine == null || 
                            strtotime($responsabile_cdr->data_fine) >= strtotime($date->format("Y-m-d"))
                        )
                    ){
                        $found = true;
                        break;
                    }
                } while ($db->nextRecord());
            }
            if ($found == false) {
                throw new Exception("Responsabile Cdr non definito per Cdr: ".$codice_cdr);
            }
        }        
        return $responsabile_cdr;
    }	
    
    public static function getAll ($filters = array()) {
        $responsabile_cdr = array();		
		
        $db = ffDb_Sql::factory();

        $where = "WHERE 1=1 ";
        
        foreach ($filters as $field => $value) {
            $where .= "AND ".$field."=".$db->toSql($value)." ";
        }
		
        $sql = "
            SELECT responsabile_cdr.*, personale.ID AS 'ID_personale', personale.cognome, personale.nome
            FROM responsabile_cdr
                INNER JOIN personale ON (responsabile_cdr.matricola_responsabile = personale.matricola)
            " . $where . "
        ";
        
        $db->query($sql);
        
        if ($db->nextRecord()) {
            do {		
                $item = new ResponsabileCdr();

                $item->id = $db->getField("ID", "Number", true);
                $item->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                $item->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $item->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $item->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                
                $item->id_personale = $db->getField("ID_personale", "Number", true);
                $item->cognome = $db->getField("cognome", "Text", true);
                $item->nome = $db->getField("nome", "Text", true);

                $responsabile_cdr[] = $item;					                
            } while ($db->nextRecord());
        }
            
        return $responsabile_cdr;
    }
    
    
    //restituisce tutti i record attivi in una data specifica
    public static function getResponsabiliCdrInData (DateTime $date) {
        $responsabile_cdr = array();	     
        
        foreach(ResponsabileCdr::getAll() AS $resp_cdr){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa
            if (strtotime($resp_cdr->data_inizio) <= strtotime($date->format("Y-m-d")) 
                && ($resp_cdr->data_fine == null || strtotime($resp_cdr->data_fine) >= strtotime($date->format("Y-m-d")))){               
                $responsabile_cdr[] = $resp_cdr;                				
            }
        }	
        
        return $responsabile_cdr;
    }
    
    //restituisce tutti i record dell'anagrafica attivi in un anno specifico
    public static function getResponsabiliCdrAnno (AnnoBudget $anno) {
        $responsabile_cdr = array();
        
        foreach(ResponsabileCdr::getAll() AS $item){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($item->data_inizio) <= strtotime($anno->descrizione."-12-31") 
                && ($item->data_fine == null || strtotime($item->data_fine) >= strtotime($anno->descrizione."-01-01"))){               
                $responsabile_cdr[] = $item;                				
            }
        }
        
        return $responsabile_cdr;
    }
    
    //restituisce i dipendenti che in un anno di budget hanno terminato il proprio ruolo di responsabile (ultima posizione di responsabilità nell'anno)
    public static function getResponsabiliCdrCessatiInAnno(AnnoBudget $anno, $matricola = null) {
        $responsabili_cdr = array();
                
        foreach(ResponsabileCdr::getAll() as $item) {
            $add = false;
            if (strtotime($item->data_fine) >= strtotime($anno->descrizione."-01-01") &&
                strtotime($item->data_fine) <= strtotime($anno->descrizione."-12-31")) {
                if ($matricola != null) {
                    if ($item->matricola_responsabile == $matricola) {
                        $add = true;
                    }
                    else {
                        $add = false;
                    }
                }
                else {
                    $add = true;
                }
                
                if ($add) {
                    $responsabili_cdr[] = $item;
                }
            }
        }
        
        return $responsabili_cdr;
    }
}