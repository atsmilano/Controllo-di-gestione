<?php
class InvestimentiTempiDirezioneRiferimento {		
	public $id;
	public $descrizione;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_tempi_direzione_riferimento
					WHERE
						investimenti_tempi_direzione_riferimento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiTempiDirezioneRiferimento con ID = ".$id);
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
					investimenti_tempi_direzione_riferimento.*
				FROM
					investimenti_tempi_direzione_riferimento
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $tempi[] = new InvestimentiTempiDirezioneRiferimento($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $tempi;
	}    
}