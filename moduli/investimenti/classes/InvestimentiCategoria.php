<?php
class InvestimentiCategoria {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_categoria                        
					WHERE
						investimenti_categoria.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiCategoria con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$categorie = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_categoria.*
				FROM
					investimenti_categoria
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $categorie[] = new InvestimentiCategoria($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $categorie;
	}
    
    //viene restituito il codice dell'uoc competente per la categoria per l'anno passato come parametro (una e una sola)
    public function getCodiceUocCompetenteAnno (AnnoBudget $anno){            
        foreach(InvestimentiCategoriaUocCompetenteAnno::getAll(array("ID_categoria"=>$this->id)) as $uoc_competente_anno) {
            if ($uoc_competente_anno->anno_introduzione <= $anno->descrizione && ($uoc_competente_anno->anno_termine == null || $uoc_competente_anno->anno_termine >= $anno->descrizione)) {
                return $uoc_competente_anno->codice_cdr;
            }            
        }
		return false;        
    }
}