<?php
class CoanDistretto{	
    public $id;
    public $codice;
	public $descrizione;
    
	public function __construct($id) {				
		$db = ffDb_Sql::factory();
		
		$sql = "
				SELECT 
					coan_distretto.*
				FROM
					coan_distretto
				WHERE
					coan_distretto.ID = " . $db->toSql($id) 
				;
		$db->query($sql);
		if ($db->nextRecord()){
			$this->id = $db->getField("ID", "Number", true);
            $this->codice = $db->getField("codice", "Text", true);
			$this->descrizione = $db->getField("descrizione", "Text", true);
		}	
		else
			throw new Exception("Impossibile creare l'oggetto CoanDistretto con ID = ".$id);
	}
    
    public static function getAll($filters=array()){			
		$coan_distretto = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					coan_distretto.*
				FROM
					coan_distretto
                    " . $where . "
				ORDER BY
					coan_distretto.descrizione ASC
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $coan_distretto[] = new CoanDistretto($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $coan_distretto;
	}
}