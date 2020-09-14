<?php
class CostiRicaviConto {	
	public $id;
	public $codice;
    public $descrizione;
	public $id_fp;
    public $codice_cdr;
	public $evidenza;
	public $id_anno_budget;
	
	public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						costi_ricavi_conto.*
					FROM
						costi_ricavi_conto
					WHERE
						costi_ricavi_conto.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->codice = $db->getField("codice", "Text", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
				$this->id_fp = $db->getField("ID_fp", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->evidenza = CoreHelper::getBooleanValueFromDB($db->getField("evidenza", "Number", true));				
				$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
			}	
			else {
				throw new Exception("Impossibile creare l'oggetto CostiRicaviConto con ID = ".$id);
            }
		}
    }	
	
	public static function getAll ($filters=array()) {
		$conti = array();
        		
		$db = ffDB_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}			       	
        $sql = "SELECT costi_ricavi_conto.*
                FROM costi_ricavi_conto                
				" . $where . "
				ORDER BY costi_ricavi_conto.codice_cdr ASC";
        $db->query($sql);
        if ($db->nextRecord()){            
            do{		
				$conto = new CostiRicaviConto();
				$conto->id = $db->getField("ID", "Number", true);
				$conto->codice = $db->getField("codice", "Text", true);
				$conto->descrizione = $db->getField("descrizione", "Text", true);
				$conto->id_fp = $db->getField("ID_fp", "Number", true);
				$conto->codice_cdr = $db->getField("codice_cdr", "Text", true);
				$conto->evidenza = CoreHelper::getBooleanValueFromDB($db->getField("evidenza", "Number", true));			
				$conto->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
					
				$conti[] = $conto;		                
            }while ($db->nextRecord());           
        }
        return $conti;		
	}
	
	public function getImportiPeriodo(CostiRicaviPeriodo $periodo){
		$importo_periodo = CostiRicaviImportoPeriodo::factoryFromPeriodoConto($periodo, $this);
		if ($importo_periodo == null){
			return 0;		
		}
		else {
			return $importo_periodo;
		}
	}
}