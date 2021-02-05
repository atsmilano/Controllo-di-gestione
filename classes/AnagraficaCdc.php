<?php
class AnagraficaCdc extends Entity{	
    protected static $tablename = "anagrafica_cdc";
    
    //metodo per istanziare l'oggetto da codice cdc
    public static function factoryFromCodice($codice, DateTime $date) {   
        $calling_class = static::class;
        $cdc_anagrafica = null;
        
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT 
                ".self::$tablename.".*
            FROM
                ".self::$tablename."
            WHERE
                ".self::$tablename.".codice = " . $db->toSql($codice) 
            ;
        
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdc_anagrafica = new $calling_class();

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
            
    //restituisce tutti i record dell'anagrafica attivi in una data specifica
    public static function getAnagraficaInData (DateTime $date) {
        $calling_class = static::class;
        $anagrafica_data = array();	                
        foreach($calling_class::getAll() AS $cdc_anagrafica){
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
        $calling_class = static::class;
        $anagrafica_data = array();    
        foreach($calling_class::getAll() AS $cdc_anagrafica){
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($cdc_anagrafica->data_introduzione) <= strtotime($anno->descrizione."-12-31") 
                && ($cdc_anagrafica->data_termine == null || strtotime($cdc_anagrafica->data_termine) >= strtotime($anno->descrizione."-01-01"))){               
                $anagrafica_data[] = $cdc_anagrafica;                				
            }
        }			
        return $anagrafica_data;
    }
}