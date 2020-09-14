<?php
class CoanCdc{	
    public $id;
    public $codice;
	public $descrizione;			
    public $id_cdc_standard_regionale;
	public $codice_cdr;
	public $id_distretto;
    
	public function __construct($id) {				
		$db = ffDb_Sql::factory();
		
		$sql = "
				SELECT 
					coan_cdc.*
				FROM
					coan_cdc
				WHERE
					coan_periodo.ID = " . $db->toSql($id) 
				;
		$db->query($sql);
		if ($db->nextRecord()){
			$this->id = $db->getField("ID", "Number", true);
            $this->codice = $db->getField("codice", "Text", true);
			$this->descrizione = $db->getField("descrizione", "Text", true);			 
			$this->id_cdc_standard_regionale = $db->getField("ID_cdc_standard_regionale", "Number", true);			
            $this->codice_cdr = $db->getField("codice_cdr", "Text", true);	
            $this->id_distretto = $db->getField("ID_distretto", "Number", true);
		}	
		else
			throw new Exception("Impossibile creare l'oggetto CoanCdc con ID = ".$id);
	}
    
    public static function getAll($filters=array()){			
		$cdc = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					coan_cdc.*
				FROM
					coan_cdc
                    " . $where . "
				ORDER BY
					coan_cdc.descrizione ASC
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $cdc[] = new CoanCdc($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $cdc;
	}
    
    public static function isCdrAssociatoAnno(AnnoBudget $anno, $cdr){		
		$db = ffDb_Sql::factory();		        
		$sql = "
				SELECT DISTINCT
					coan_cdc.codice_cdr
				FROM
					coan_cdc
                    INNER JOIN coan_consuntivo_periodo ON  coan_cdc.ID = coan_consuntivo_periodo.ID_cdc_coan
                    INNER JOIN coan_periodo ON coan_consuntivo_periodo.ID_periodo_coan = coan_periodo.ID
                WHERE
                    coan_periodo.ID_anno_budget = " . $db->toSql($anno->id) . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {    
                if ($cdr->codice == $db->getField("codice_cdr", "Text", true)){
                    return true;                    
                }                                
            } while ($db->nextRecord());
		}	
		return false;    
    }
}