<?php
class InvestimentiPrioritaDg {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_priorita_dg                      
					WHERE
						investimenti_priorita_dg.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiPrioritaDg con ID = ".$id);
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
					investimenti_priorita_dg.*
				FROM
					investimenti_priorita_dg
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $priorita[] = new InvestimentiPrioritaDg($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $priorita;
	}    
}