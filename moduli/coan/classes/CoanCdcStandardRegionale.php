<?php
class CoanCdcStandardRegionale{	
    public $id;
    public $codice;
	public $descrizione;
    
	public function __construct($id) {				
		$db = ffDb_Sql::factory();
		
		$sql = "
				SELECT 
					coan_cdc_standard_regionale.*
				FROM
					coan_cdc_standard_regionale
				WHERE
					coan_cdc_standard_regionale.ID = " . $db->toSql($id) 
				;
		$db->query($sql);
		if ($db->nextRecord()){
			$this->id = $db->getField("ID", "Number", true);
            $this->codice = $db->getField("codice", "Text", true);
			$this->descrizione = $db->getField("descrizione", "Text", true);
		}	
		else
			throw new Exception("Impossibile creare l'oggetto CoanCdcStandardRegionale con ID = ".$id);
	}
    
    public static function getAll($filters=array()){			
		$coan_cdc_standard_regionale = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					coan_cdc_standard_regionale.*
				FROM
					coan_cdc_standard_regionale
                    " . $where . "
				ORDER BY
					coan_cdc_standard_regionale.descrizione ASC
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $coan_cdc_standard_regionale[] = new CoanCdcStandardRegionale($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $coan_cdc_standard_regionale;
	}
}