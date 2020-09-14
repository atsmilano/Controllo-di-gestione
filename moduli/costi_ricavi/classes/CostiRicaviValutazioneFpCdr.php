<?php
class CostiRicaviValutazioneFpCdr {	
	public $id;
	public $id_periodo;
	public $id_fp;
	public $codice_cdr;
	public $campo_1;
	public $campo_2;
	public $campo_3;
		
	public function __construct($id = null)
    {				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						costi_ricavi_valutazione_fp_cdr.*
					FROM
						costi_ricavi_valutazione_fp_cdr
					WHERE
						costi_ricavi_valutazione_fp_cdr.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->id_periodo = $db->getField("ID_periodo", "Number", true);
				$this->id_fp = $db->getField("ID_fp", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Number", true);
				$this->campo_1 = $db->getField("campo_1", "Number", true);
				$this->campo_2 = $db->getField("campo_2", "Number", true);
				$this->campo_3 = $db->getField("campo_3", "Number", true);							
			}	
			else
				throw new Exception("Impossibile creare l'oggetto CostiRicaviValutazioneFpCdr con ID = ".$id);
		}
    }
	
	public static function factoryFromPeriodoFpCdr(CostiRicaviPeriodo $periodo, CostiRicaviFp $fp, Cdr $cdr){
		$filters = array (
							"ID_periodo" => $periodo->id,
							"ID_fp" => $fp->id,
							"codice_cdr" => $cdr->codice,			
							);
		$valutazioni_periodo_fp_cdr = CostiRicaviValutazioneFpCdr::getAll($filters);
		//getAll() restituirà al più un elemento
		if (count($valutazioni_periodo_fp_cdr)>0) {
			return $valutazioni_periodo_fp_cdr[0];
		}
		else {
			return null;
		}
	}
	
	public static function getAll ($filters=array()) {
		$valutazioni_fp_cdr = array();
        		
		$db = ffDB_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}			       	
        $sql = "SELECT costi_ricavi_valutazione_fp_cdr.*
                FROM costi_ricavi_valutazione_fp_cdr                
				" . $where;
        $db->query($sql);
        if ($db->nextRecord())
        {            
            do
            {		
				$valutazione_fp_cdr = new CostiRicaviValutazioneFpCdr();
				$valutazione_fp_cdr->id = $db->getField("ID", "Number", true);
				$valutazione_fp_cdr->id_periodo = $db->getField("ID_periodo", "Number", true);
				$valutazione_fp_cdr->id_fp = $db->getField("ID_fp", "Number", true);
				$valutazione_fp_cdr->codice_cdr = $db->getField("codice_cdr", "Number", true);
				$valutazione_fp_cdr->campo_1 = $db->getField("campo_1", "Text", true);
				$valutazione_fp_cdr->campo_2 = $db->getField("campo_2", "Text", true);
				$valutazione_fp_cdr->campo_3 = $db->getField("campo_3", "Text", true);
					
				$valutazioni_fp_cdr[] = $valutazione_fp_cdr;		                
            }while ($db->nextRecord());           
        }
        return $valutazioni_fp_cdr;		
	}
}