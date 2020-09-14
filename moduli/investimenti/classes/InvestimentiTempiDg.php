<?php
class InvestimentiTempiDg {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_tempi_dg
					WHERE
						investimenti_tempi_dg.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiTempiDg con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$tempi = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_tempi_dg.*
				FROM
					investimenti_tempi_dg
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $tempi[] = new InvestimentiTempiDg($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $tempi;
	}    
}