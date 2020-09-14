<?php
class InvestimentiParereDirezioneRiferimento {		
	public $id;
	public $descrizione;
    public $esito;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_parere_direzione_riferimento                       
					WHERE
						investimenti_parere_direzione_riferimento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
                $this->esito = CoreHelper::getBooleanValueFromDB($db->getField("esito", "Number", true));
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiParereDirezioneRiferimento con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$pareri = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_parere_direzione_riferimento.*
				FROM
					investimenti_parere_direzione_riferimento
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {       
                $parere = new InvestimentiParereDirezioneRiferimento();
                        
                $parere->id = $db->getField("ID", "Number", true);
				$parere->descrizione = $db->getField("descrizione", "Text", true);
                $parere->esito = CoreHelper::getBooleanValueFromDB($db->getField("esito", "Number", true));
                
                $pareri[] = $parere;
            } while ($db->nextRecord());
		}	
		return $pareri;
	}    
}