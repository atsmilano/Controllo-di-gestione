<?php
class CostiRicaviImportoPeriodo {	
	public $id;
	public $id_periodo;
	public $id_conto;
	public $campo_1;
	public $campo_2;
	public $campo_3;
	public $campo_4;
		
	public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						costi_ricavi_importo_periodo.*
					FROM
						costi_ricavi_importo_periodo
					WHERE
						costi_ricavi_importo_periodo.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->id_periodo = $db->getField("ID_periodo", "Number", true);
				$this->id_conto = $db->getField("ID_conto", "Number", true);
				$this->campo_1 = $db->getField("campo_1", "Number", true);
				$this->campo_2 = $db->getField("campo_2", "Number", true);
				$this->campo_3 = $db->getField("campo_3", "Number", true);							
				$this->campo_4 = $db->getField("campo_4", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto CostiRicaviImportoPeriodo con ID = ".$id);
		}
    }
	
	public static function factoryFromPeriodoConto(CostiRicaviPeriodo $periodo, CostiRicaviConto $conto){
		$filters = array (
							"ID_periodo" => $periodo->id,
							"ID_conto" => $conto->id,		
							);
		$importi_periodo = CostiRicaviImportoPeriodo::getAll($filters);
		//getAll() restituirà al più un elemento
		if (count($importi_periodo)>0) {
			return $importi_periodo[0];
		}
		else {
			return null;
		}
	}
	
	public static function getAll ($filters=array()) {
		$importi_periodo = array();
        		
		$db = ffDB_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}			       	
        $sql = "SELECT costi_ricavi_importo_periodo.*
                FROM costi_ricavi_importo_periodo                
				" . $where;
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {		
				$importo_periodo = new CostiRicaviImportoPeriodo();
				$importo_periodo->id = $db->getField("ID", "Number", true);
				$importo_periodo->id_periodo = $db->getField("ID_periodo", "Number", true);
				$importo_periodo->id_conto = $db->getField("ID_conto", "Number", true);
				$importo_periodo->campo_1 = $db->getField("campo_1", "Number", true);
				$importo_periodo->campo_2 = $db->getField("campo_2", "Number", true);
				$importo_periodo->campo_3 = $db->getField("campo_3", "Number", true);							
				$importo_periodo->campo_4 = $db->getField("campo_4", "Number", true);
					
				$importi_periodo[] = $importo_periodo;		                
            }while ($db->nextRecord());           
        }
        return $importi_periodo;		
	}
}