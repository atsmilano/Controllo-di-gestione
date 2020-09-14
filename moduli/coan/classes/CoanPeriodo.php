<?php
class CoanPeriodo {		
	public $id;
	public $descrizione;		
	public $id_anno_budget;
    public $data_apertura;
	public $data_inizio;
	public $data_fine;	
	
	public function __construct($id) {				
		$db = ffDb_Sql::factory();
		
		$sql = "
				SELECT 
					*
				FROM
					coan_periodo
				WHERE
					coan_periodo.ID = " . $db->toSql($id) 
				;
		$db->query($sql);
		if ($db->nextRecord()){
			$this->id = $db->getField("ID", "Number", true);
			$this->descrizione = $db->getField("descrizione", "Text", true);			 
			$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);			
			$this->data_inizio = $db->getField("data_inizio", "Date", true);
            $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
			$this->data_apertura = $db->getField("data_apertura", "Date", true);			
		}	
		else {
			throw new Exception("Impossibile creare l'oggetto ValutazioniPeriodo con ID = ".$id);
        }
	}
	
	public static function getAll($filters = array()) {			
		$periodi = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					coan_periodo.*
				FROM
					coan_periodo
					INNER JOIN anno_budget ON coan_periodo.ID_anno_budget = anno_budget.ID
                    " . $where . "
				ORDER BY
					anno_budget.descrizione DESC,
					coan_periodo.ordinamento_anno DESC
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $periodi[] = new CoanPeriodo($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $periodi;
	}	
}