<?php
class StrategiaProspettiva {	
	public $id;
    public $nome;
    public $descrizione;
    public $anno_introduzione;
	public $anno_termine;
	
	public function __construct($id=null){			
		if($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						strategia_prospettiva.*
					FROM
						strategia_prospettiva
					WHERE
						strategia_prospettiva.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->nome = $db->getField("nome", "Text", true);	
				$this->descrizione = $db->getField("descrizione", "Text", true);			
				$this->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
				if ((int)$db->getField("anno_termine", "Text", true) !== 0) {
					$this->anno_termine = $db->getField("anno_termine", "Text", true);
				}
				else {
					$this->anno_termine = null;
				}            
			}	
			else
				throw new Exception("Impossibile creare l'oggetto Prospettiva con ID = ".$id);			
		}
    }
	
	public static function getAll () {
		$prospettive = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT strategia_prospettiva.*
                FROM strategia_prospettiva
                ORDER BY descrizione DESC";
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {	
				$prospettiva = new StrategiaProspettiva();				
				$prospettiva->id = $db->getField("ID", "Number", true);
				$prospettiva->nome = $db->getField("nome", "Text", true);	
				$prospettiva->descrizione = $db->getField("descrizione", "Text", true);			
				$prospettiva->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
				if ((int)$db->getField("anno_termine", "Text", true) !== 0) {
					$prospettiva->anno_termine = $db->getField("anno_termine", "Text", true);
				}
				else {
					$prospettiva->anno_termine = null;
				}
				$prospettive[] = $prospettiva;				                
            }while ($db->nextRecord());           
        }
        return $prospettive;
		
	}
	
	public static function getProspettiveAnno (AnnoBudget $anno) {
		$prospettive_strategia_anno = array();
		foreach(StrategiaProspettiva::getAll() as $prospettiva) {
			if ($prospettiva->anno_introduzione <= $anno->descrizione && ($prospettiva->anno_termine == null || $prospettiva->anno_termine >= $anno->descrizione)) {
				$prospettive_strategia_anno[] = $prospettiva;
			}
		}
		return $prospettive_strategia_anno;
	}
}