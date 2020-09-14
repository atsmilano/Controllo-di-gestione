<?php
class InvestimentiPrioritaDirezioneRiferimento {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_priorita_direzione_riferimento                       
					WHERE
						investimenti_priorita_direzione_riferimento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiPrioritaDirezioneRiferimento con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$priorita = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_priorita_direzione_riferimento.*
				FROM
					investimenti_priorita_direzione_riferimento
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $priorita[] = new InvestimentiPrioritaDirezioneRiferimento($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $priorita;
	}    
}