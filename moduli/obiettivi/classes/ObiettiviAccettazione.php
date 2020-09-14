<?php
class ObiettiviAccettazione {	
	public $id;
	public $matricola_personale;
	public $id_anno_budget;	
	public $note_dipendente;
	public $data_accettazione_dipendente;
	
	public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						obiettivi_accettazione.*
					FROM
						obiettivi_accettazione
					WHERE
						obiettivi_accettazione.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->matricola_personale = $db->getField("matricola_personale", "Text", true);
				$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);				
				$this->note_dipendente = $db->getField("note_dipendente", "Text", true);
                $this->data_accettazione_dipendente = CoreHelper::getDateValueFromDB($db->getField("data_accettazione_dipendente", "Date", true));							
			}	
			else
				throw new Exception("Impossibile creare l'oggetto ObiettivoAccettazione con ID = ".$id);
		}				
    }
	
	public static function factoryFromDipendenteAnno(Personale $personale, AnnoBudget $anno){
		$db = ffDb_Sql::factory();

		$sql = "
				SELECT 
					obiettivi_accettazione.ID
				FROM
					obiettivi_accettazione
				WHERE
					obiettivi_accettazione.matricola_personale = " . $db->toSql($personale->matricola) . "
					AND obiettivi_accettazione.ID_anno_budget = " . $db->toSql($anno->id) . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
			return new ObiettiviAccettazione($db->getField("ID", "Number", true));
		}
		throw new Exception("Impossibile creare l'oggetto ObiettivoAccettazione con matricola = ".$personale->matricola." e anno=".$anno->descrizione);
	}
}