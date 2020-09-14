<?php
class TipoPianoCdr {		
    public $id;
    public $descrizione;
	public $priorita;
    
    public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						tipo_piano_cdr.*
					FROM
						tipo_piano_cdr
					WHERE
						tipo_piano_cdr.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
				$this->priorita = $db->getField("priorita", "Number", true);
			}	
			else {
				throw new Exception("Impossibile creare l'oggetto TipoPianoCdr con ID = ".$id);
            }
		}
    }
    
    //restituisce array con tutti i tipi di piano cdr ordinati per priorità
    public static function getAll (){
        $tipi_piano = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT tipo_piano_cdr.*
                FROM tipo_piano_cdr
                ORDER BY priorita ASC";
        $db->query($sql);
        if ($db->nextRecord()){            
            do{
				$tipo_piano = new TipoPianoCdr();
				$tipo_piano->id = $db->getField("ID", "Number", true);
				$tipo_piano->descrizione = $db->getField("descrizione", "Text", true);
				$tipo_piano->priorita = $db->getField("priorita", "Number", true);
				$tipi_piano[] = $tipo_piano;				                
            }while ($db->nextRecord());           
        }
        return $tipi_piano;
    } 
    
    //restituisce il tipo piano con priorità più alta
    public static function getPrioritaMassima (){
        $tipi_piano = TipoPianoCdr::getAll();
        if (count($tipi_piano) > 0){
            return $tipi_piano[0];
        }       
        else {
            return null;
        }
    }
    
    //restituisce true se l'oggetto può essere eliminato (nessuna relazione vincolante)
    public function canDelete() {
        $piani_cdr_tipo = PianoCdr::getAll(array("ID_tipo_piano_cdr" => $this->id));
        return empty($piani_cdr_tipo);
    }
}