<?php
class CostiRicaviPeriodo {	
	public $id;
	public $id_anno_budget;
	public $data_riferimento_inizio;
	public $data_riferimento_fine;
	public $data_scadenza;
	public $ordinamento_anno;
    public $descrizione;
	public $id_tipo_periodo;
	
	public static $tipo_periodo = array	(
											1 => "Apertura",
											2 => "Intermedio",
											3 => "Chiusura",
										);
	
	public function __construct($id = null)    {				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						costi_ricavi_periodo.*
					FROM
						costi_ricavi_periodo
					WHERE
						costi_ricavi_periodo.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->data_riferimento_inizio = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_inizio", "Date", true));
                $this->data_riferimento_fine = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_fine", "Date", true));
                $this->data_scadenza = CoreHelper::getDateValueFromDB($db->getField("data_scadenza", "Date", true));				
				$this->ordinamento_anno = $db->getField("ordinamento_anno", "Number", true);				
				$this->descrizione = $db->getField("descrizione", "Text", true);							
				$this->id_tipo_periodo = $db->getField("ID_tipo_periodo", "Number", true);								
			}	
			else
				throw new Exception("Impossibile creare l'oggetto CostiRicaviPeriodo con ID = ".$id);
		}
    }
	
	public static function getAll ($filters=array()) {
		$periodi = array();
        		
		$db = ffDB_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}			       	
        $sql = "SELECT costi_ricavi_periodo.*
                FROM costi_ricavi_periodo                
				" . $where . "
				ORDER BY costi_ricavi_periodo.ordinamento_anno ASC";
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {		
				$periodo = new CostiRicaviPeriodo();
				$periodo->id = $db->getField("ID", "Number", true);
				$periodo->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
				$periodo->data_riferimento_inizio = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_inizio", "Date", true));
                $periodo->data_riferimento_fine = CoreHelper::getDateValueFromDB($db->getField("data_riferimento_fine", "Date", true));
                $periodo->data_scadenza = CoreHelper::getDateValueFromDB($db->getField("data_scadenza", "Date", true));
				$periodo->ordinamento_anno = $db->getField("ordinamento_anno", "Number", true);				
				$periodo->descrizione = $db->getField("descrizione", "Text", true);							
				$periodo->id_tipo_periodo = $db->getField("ID_tipo_periodo", "Number", true);
					
				$periodi[] = $periodo;		                
            }while ($db->nextRecord());           
        }
        return $periodi;		
	}
	
	//restituisce l'ultimo periodo di rendicontazione definito nell'anno (null se nessuno definito)
	public static function getUltimoDefinitoAnno (AnnoBudget $anno) {		
		//viene considerato l'ultimo periodo attivo nell'anno per la data selezionata			
		$periodi = CostiRicaviPeriodo::getAll(array("ID_anno_budget" => $anno->id));
		if (count($periodi)>0)
			return end($periodi);
		else
			return null;
	}
}