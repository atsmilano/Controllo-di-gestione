<?php
class RiesameDirezioneSezione {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						riesame_direzione_sezione                        
					WHERE
						riesame_direzione_sezione.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto riesame_direzione_sezione con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$sezioni = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					riesame_direzione_sezione.*
				FROM
					riesame_direzione_sezione
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $sezioni[] = new RiesameDirezioneSezione($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $sezioni;
	}
}