<?php
class RiesameDirezioneIntroduzione {	
	public $id;
    public $testo;
    public $anno_introduzione;
	public $anno_termine;
	
	public function __construct($id=null){			
		if($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						riesame_direzione_introduzione.*
					FROM
						riesame_direzione_introduzione
					WHERE
						riesame_direzione_introduzione.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->testo = $db->getField("testo", "Text", true);			
				$this->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
				if ((int)$db->getField("anno_termine", "Text", true) !== 0) {
					$this->anno_termine = $db->getField("anno_termine", "Text", true);
				}
				else {
					$this->anno_termine = null;
				}            
			}	
			else
				throw new Exception("Impossibile creare l'oggetto RiesameDirezioneIntroduzione con ID = ".$id);			
		}
    }
    
    public static function getAll () {
		$introduzioni = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT riesame_direzione_introduzione.*
                FROM riesame_direzione_introduzione";
        $db->query($sql);
        if ($db->nextRecord()) {                        
            $introduzione = new RiesameDirezioneIntroduzione();				
            $introduzione->id = $db->getField("ID", "Number", true);
            $introduzione->testo = $db->getField("testo", "Text", true);				
            $introduzione->anno_introduzione = $db->getField("anno_introduzione", "Text", true);
            if ((int)$db->getField("anno_termine", "Text", true) !== 0) {
                $introduzione->anno_termine = $db->getField("anno_termine", "Text", true);
            }
            else {
                $introduzione->anno_termine = null;
            }
            $introduzioni[] = $introduzione;				                                  
        }
        return $introduzioni;
		
	}
	
	public static function getIntroduzioneAnno (AnnoBudget $anno) {
		$introduzione_anno = false;
		foreach(RiesameDirezioneIntroduzione::getAll() as $introduzione) {
			if ($introduzione->anno_introduzione <= $anno->descrizione && ($introduzione->anno_termine == null || $introduzione->anno_termine >= $anno->descrizione)) {
				return $introduzione;                
			}
		}
		return $introduzione_anno;
	}
}