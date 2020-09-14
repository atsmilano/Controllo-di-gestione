<?php
class InvestimentiFonteFinanziamento {		
	public $id;
	public $descrizione;
    public $budget_anno;
    public $id_anno_budget;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_fonte_finanziamento
					WHERE
						investimenti_fonte_finanziamento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
                $this->budget_anno = $db->getField("budget_anno", "Text", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiFonteFinanziamento con ID = ".$id);
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
					investimenti_fonte_finanziamento.*
				FROM
					investimenti_fonte_finanziamento
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $tempi[] = new InvestimentiFonteFinanziamento($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $tempi;
	}    
}