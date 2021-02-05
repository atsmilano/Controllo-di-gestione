<?php
class TipoCdrPadre extends Entity {		
    protected static $tablename = "tipo_cdr_padre";        
	
	//viene istanziato l'oggetto da ID_tipo_cdr e ID_tipo_cdr_padre
	public static function instanceFromRelation(TipoCdr $tipo_cdr, TipoCdr $tipo_cdr_padre){
        $calling_class = static::class;
		$id = 0;
		foreach($calling_class::getAll() as $tipo_padre){		
			if ($tipo_padre->id_tipo_cdr == $tipo_cdr->id && $tipo_padre->id_tipo_cdr_padre == $tipo_cdr_padre->id)			
				$id = $tipo_padre->id;			
		}
		return new $calling_class($id);
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
		$sql = "INSERT INTO ".self::$tablename." (" . $names . ") VALUES (" . $values . ");";						
		if (!$db->execute($sql)){	
            throw new Exception("Impossibile creare l'oggetto ".static::class." nel DB");
        }
	}
	
	//aggiornamento
	public function save(){
		$db = ffDB_Sql::factory();		
		$sql = "UPDATE ".self::$tablename."
                SET					
					ID_tipo_cdr = " . (strlen($this->id_tipo_cdr)? $db->toSql($this->id_tipo_cdr):"null") . ",
					ID_tipo_cdr_padre = " . (strlen($this->id_tipo)? $db->toSql($this->id_tipo):"null") . "
				WHERE ".self::$tablename.".ID = " . $db->toSql($this->id) . "
                ";
		if (!$db->execute($sql)) {		
            throw new Exception("Impossibile salvare l'oggetto ".static::class." con ID = ".$this->id." nel DB");
        }
	}
			
	//eliminazione
	public function delete(){
		$db = ffDB_Sql::factory();		
		$sql = "DELETE FROM ".self::$tablename." WHERE ".self::$tablename.".ID = " . $db->toSql($this->id);                
		if (!$db->execute($sql)){		
            throw new Exception("Impossibile eliminare l'oggetto ".static::class." con ID = ".$id." nel DB");
        }
	}		
}