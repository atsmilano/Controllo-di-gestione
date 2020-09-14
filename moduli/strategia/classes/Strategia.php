<?php
class Strategia {	
	public $id;
    public $descrizione;
    public $id_prospettiva;
    public $id_anno_budget;
	public $codice_cdr;
	public $data_ultima_modifica;
	
	public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						strategia_strategia.*
					FROM
						strategia_strategia
					WHERE
						strategia_strategia.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);			
				$this->id_prospettiva = $db->getField("ID_prospettiva", "Number", true);			
				$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));							
			}	
			else
				throw new Exception("Impossibile creare l'oggetto Strategia con ID = ".$id);
		}
    }
	
	public static function getAll ($filters=array()) {
		$strategie = array();
		
		$db = ffDB_Sql::factory();	
        $where = "WHERE 1=1 ";
		foreach ($filters as $field => $value)
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
				       
        $sql = "SELECT strategia_strategia.*
                FROM strategia_strategia
				" . $where;
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {		
				$strategia = new Strategia();
				$strategia->id = $db->getField("ID", "Number", true);
				$strategia->descrizione = $db->getField("descrizione", "Text", true);			
				$strategia->id_prospettiva = $db->getField("ID_prospettiva", "Number", true);			
				$strategia->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
				$strategia->codice_cdr = $db->getField("codice_cdr", "Text", true);
				$strategia->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));	
				$strategie[] = $strategia;		                
            }while ($db->nextRecord());           
        }
        return $strategie;		
	}	
}