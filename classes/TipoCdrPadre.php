<?php
class TipoCdrPadre {		
    public $id;
    public $id_tipo_cdr;
	public $id_tipo_cdr_padre;
    
    public function __construct($id = null) {				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						tipo_cdr_padre.*
					FROM
						tipo_cdr_padre
					WHERE
						tipo_cdr_padre.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Text", true);
				$this->id_tipo_cdr_padre = $db->getField("ID_tipo_cdr_padre", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto TipoCdrPadre con ID = ".$id);
		}
    }
	
	//viene istanziato l'oggetto da ID_tipo_cdr e ID_tipo_cdr_padre
	public static function instanceFromRelation(TipoCdr $tipo_cdr, TipoCdr $tipo_cdr_padre){
		$id = 0;
		foreach(TipoCdrPadre::getAll() as $tipo_padre){		
			if ($tipo_padre->id_tipo_cdr == $tipo_cdr->id && $tipo_padre->id_tipo_cdr_padre == $tipo_cdr_padre->id)			
				$id = $tipo_padre->id;			
		}
		return new TipoCdrPadre($id);
	}
    	
	//salvataggio dell'oggetto su db
	//viene passato un array con coppia chiave=>valore nome_campo=>valore per ogni campo
	//inserimento
	public static function saveNew($row){
		$db = ffDB_Sql::factory();		
		$names = "";
		$values = "";		
		foreach($row as $name => $value){
			if (strlen($names))
				$names .= ", ";
			$names .= $name;
			if (strlen($values))
				$values .= ", ";
			$values .= $db->toSql($value);
		}
		$sql = "INSERT INTO tipo_cdr_padre (" . $names . ") VALUES (" . $values . ");";						
		if (!$db->execute($sql)){	
            throw new Exception("Impossibile creare l'oggetto TipoCdrPadre nel DB");
        }
	}
	
	//aggiornamento
	public function save(){
		$db = ffDB_Sql::factory();		
		$sql = "UPDATE tipo_cdr_padre
                SET					
					ID_tipo_cdr = " . (strlen($this->id_tipo_cdr)? $db->toSql($this->id_tipo_cdr):"null") . ",
					ID_tipo_cdr_padre = " . (strlen($this->id_tipo)? $db->toSql($this->id_tipo):"null") . "
				WHERE tipo_cdr_padre.ID = " . $db->toSql($this->id) . "
                ";
		if (!$db->execute($sql)) {		
            throw new Exception("Impossibile salvare l'oggetto TipoCdrPadre con ID = ".$this->id." nel DB");
        }
	}
			
	//eliminazione
	public function delete(){
		$db = ffDB_Sql::factory();		
		$sql = "DELETE FROM tipo_cdr_padre WHERE tipo_cdr_padre.ID = " . $db->toSql($this->id);                
		if (!$db->execute($sql)){		
            throw new Exception("Impossibile eliminare l'oggetto TipoCdrPadre con ID = ".$id." nel DB");
        }
	}
    
	
    //restituisce array con tutti i tipi di cdr ordinati per descrizione
    public static function getAll (){
        $tipi_cdr = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT tipo_cdr_padre.*
                FROM tipo_cdr_padre
                ";
        $db->query($sql);
        if ($db->nextRecord()){            
            do{
				$tipo_cdr_padre = new TipoCdrPadre();
				$tipo_cdr_padre->id = $db->getField("ID", "Number", true);
				$tipo_cdr_padre->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Text", true);
				$tipo_cdr_padre->id_tipo_cdr_padre = $db->getField("ID_tipo_cdr_padre", "Text", true);
				$tipi_cdr[] = $tipo_cdr_padre;										                
            }while ($db->nextRecord());           
        }
        return $tipi_cdr;
    } 		
}