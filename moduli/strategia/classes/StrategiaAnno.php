<?php
class StrategiaAnno {	
	public $id;
    public $id_anno_budget;
	public $data_chiusura_definizione_strategia;
	
	public function __construct($id=null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						strategia_anno.*
					FROM
						strategia_anno
					WHERE
						strategia_anno.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);					
				$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->data_chiusura_definizione_strategia = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_definizione_strategia", "Date", true));                						
			}	
			else
				throw new Exception("Impossibile creare l'oggetto StrategiaAnno con ID = ".$id);
		}
    }
	
	public static function getAll ($filters=array()) {
		$strategie_anno = array();
		
		$db = ffDB_Sql::factory();	
        $where = "WHERE 1=1 ";
		foreach ($filters as $field => $value)
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
				       
        $sql = "SELECT strategia_anno.*
                FROM strategia_anno
				" . $where;
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {		
				$strategia_anno = new StrategiaAnno();
				$strategia_anno->id = $db->getField("ID", "Number", true);					
				$strategia_anno->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
				$strategia_anno->data_chiusura_definizione_strategia = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_definizione_strategia", "Date", true));
				$strategie_anno[] = $strategia_anno;				                
            }while ($db->nextRecord());           
        }
        return $strategie_anno;		
	}	
	
	public static function getChiusuraAnno (AnnoBudget $anno) {		
		$db = ffDB_Sql::factory();	
		$strategie_anno = StrategiaAnno::getAll(array("ID_anno_budget"=>$anno->id));
		if (count($strategie_anno)>0){
			return $strategie_anno[0]->data_chiusura_definizione_strategia;
		}			
		else{
			return null;
		}
	}	
}