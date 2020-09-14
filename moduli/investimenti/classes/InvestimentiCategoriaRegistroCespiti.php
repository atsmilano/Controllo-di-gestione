<?php
class InvestimentiCategoriaRegistroCespiti {		
	public $id;
	public $descrizione;
    public $id_anno_budget;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_categoria_registro_cespiti
					WHERE
						investimenti_categoria_registro_cespiti.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiCategoriaRegistroCespiti con ID = ".$id);
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
					investimenti_categoria_registro_cespiti.*
				FROM
					investimenti_categoria_registro_cespiti
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $tempi[] = new InvestimentiCategoriaRegistroCespiti($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $tempi;
	}    
}