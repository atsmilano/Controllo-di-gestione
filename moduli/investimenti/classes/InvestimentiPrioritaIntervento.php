<?php
class InvestimentiPrioritaIntervento {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_priorita_intervento                        
					WHERE
						investimenti_priorita_intervento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiPrioritaIntervento con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$priorita = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_priorita_intervento.*
				FROM
					investimenti_priorita_intervento
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $priorita[] = new InvestimentiPrioritaIntervento($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $priorita;
	}    
}