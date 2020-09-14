<?php
class AnagraficaCdc{		
    public $id;
    public $codice;
    public $descrizione;
    public $abbreviazione;
    public $data_introduzione;	
    public $data_termine;
    
    public function __construct($id = null){
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						anagrafica_cdc.*
					FROM
						anagrafica_cdc
					WHERE
						anagrafica_cdc.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()) {
				$this->id = $db->getField("ID", "Number", true);
				$this->codice = $db->getField("codice", "Text", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
				$this->abbreviazione = $db->getField("abbreviazione", "Text", true);
				$this->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $this->data_termine = CoreHelper::getDateValueFromDB($db->getField("data_termine", "Date", true));
			}	
			else {
				throw new Exception("Impossibile creare l'oggetto AnagraficaCdc con ID = ".$id);
            }
		}		
    }	
	
    //metodo per istanziare l'oggetto da codice cdc
    public static function factoryFromCodice($codice, DateTime $date) {        
        $cdc_anagrafica = null;
        
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT 
                anagrafica_cdc.*
            FROM
                anagrafica_cdc
            WHERE
                anagrafica_cdc.codice = " . $db->toSql($codice) 
            ;
        
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdc_anagrafica = new AnagraficaCdc();

                $cdc_anagrafica->id = $db->getField("ID", "Number", true);
                $cdc_anagrafica->codice = $db->getField("codice", "Text", true);
                $cdc_anagrafica->descrizione = $db->getField("descrizione", "Text", true);
                $cdc_anagrafica->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $cdc_anagrafica->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $cdc_anagrafica->data_termine = CoreHelper::getDateValueFromDB($db->getField("data_termine", "Date", true));
                                
                if (strtotime($cdc_anagrafica->data_introduzione) <= strtotime($date->format("Y-m-d")) 
                    && ($cdc_anagrafica->data_termine == null || strtotime($cdc_anagrafica->data_termine) >= strtotime($date->format("Y-m-d")))){
                    break;
                }
            } while ($db->nextRecord());
        }
        
        return $cdc_anagrafica;
    }	
    
    public static function getAll ($filters=array()) {
        $anagrafica = array();		
		
		$db = ffDb_Sql::factory();

		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value)
			$where .= "AND ".$field."=".$db->toSql($value)." ";
		
        $sql = "
                SELECT anagrafica_cdc.*
                FROM anagrafica_cdc
				" . $where . "
				";
        $db->query($sql);
		if ($db->nextRecord()) {
			do {		
				$cdc_anagrafica = new AnagraficaCdc();
                $cdc_anagrafica->id = $db->getField("ID", "Number", true);
				$cdc_anagrafica->codice = $db->getField("codice", "Text", true);
				$cdc_anagrafica->descrizione = $db->getField("descrizione", "Text", true);
				$cdc_anagrafica->abbreviazione = $db->getField("abbreviazione", "Text", true);
				$cdc_anagrafica->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $cdc_anagrafica->data_termine = CoreHelper::getDateValueFromDB($db->getField("data_termine", "Date", true));
                			
				$anagrafica[] = $cdc_anagrafica;					                
            }while ($db->nextRecord());
		}
		return $anagrafica;
    }
            
    //restituisce tutti i record dell'anagrafica attivi in una data specifica
    public static function getAnagraficaInData (DateTime $date) {
        $anagrafica_data = array();	                
        foreach(AnagraficaCdc::getAll() AS $cdc_anagrafica){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($cdc_anagrafica->data_introduzione) <= strtotime($date->format("Y-m-d")) 
                && ($cdc_anagrafica->data_termine == null || strtotime($cdc_anagrafica->data_termine) >= strtotime($date->format("Y-m-d")))){               
                $anagrafica_data[] = $cdc_anagrafica;                				
            }
        }			
        return $anagrafica_data;
    }
    
    //restituisce tutti i record dell'anagrafica attivi in un anno specifico
    public static function getAnagraficaAnno (AnnoBudget $anno) {
        $anagrafica_data = array();    
        foreach(AnagraficaCdc::getAll() AS $cdc_anagrafica){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($cdc_anagrafica->data_introduzione) <= strtotime($anno->descrizione."-12-31") 
                && ($cdc_anagrafica->data_termine == null || strtotime($cdc_anagrafica->data_termine) >= strtotime($anno->descrizione."-01-01"))){               
                $anagrafica_data[] = $cdc_anagrafica;                				
            }
        }			
        return $anagrafica_data;
    }
}