<?php
class StrategiaCdrProgrammazioneStrategica {	
	public $id;
    public $codice_cdr;
    public $anno_inizio;
	public $anno_fine;
	
	public function __construct($id=null) {			
		if($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						strategia_cdr_programmazione_strategica.*
					FROM
						strategia_cdr_programmazione_strategica
					WHERE
						strategia_cdr_programmazione_strategica.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);				
				$this->anno_inizio = $db->getField("anno_inizio", "Text", true);
				if ((int)$db->getField("anno_fine", "Text", true) !== 0) {
					$this->anno_fine = $db->getField("anno_fine", "Text", true);
				}
				else {
					$this->anno_fine = null;
				}            
			}	
			else
				throw new Exception("Impossibile creare l'oggetto StrategiaCdrProgrammazioneStrategica con ID = ".$id);			
		}
    }
	
	public static function getAll () {
		$cdr_programmazione_strategica = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT strategia_cdr_programmazione_strategica.*
                FROM strategia_cdr_programmazione_strategica";
        $db->query($sql);
        if ($db->nextRecord()) {            
            do {	
				$cdr = new StrategiaCdrProgrammazioneStrategica();				
				$cdr->id = $db->getField("ID", "Number", true);
				$cdr->codice_cdr = $db->getField("codice_cdr", "Text", true);				
				$cdr->anno_inizio = $db->getField("anno_inizio", "Text", true);
				if ((int)$db->getField("anno_fine", "Text", true) !== 0) {
					$cdr->anno_fine = $db->getField("anno_fine", "Text", true);
				}
				else {
					$cdr->anno_fine = null;
				} 
				$cdr_programmazione_strategica[] = $cdr;				                
            }while ($db->nextRecord());           
        }
        return $cdr_programmazione_strategica;		
	}
	
	public static function getCdrProgrammazioneStrategicaAnno (AnnoBudget $anno) {
		$cdr_anno = array();
		foreach(StrategiaCdrProgrammazioneStrategica::getAll() as $cdr) {
			if ($cdr->anno_inizio <= $anno->descrizione && ($cdr->anno_fine == null || $cdr->anno_fine >= $anno->descrizione)) {
				$cdr_anno[] = $cdr->codice_cdr;
			}
		}
		return $cdr_anno;
	}
}