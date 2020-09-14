<?php
class InvestimentiCdrBilancioAnno {		
	public $id;
	public $codice_cdr;
	public $anno_inizio;
    public $anno_termine;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_cdr_bilancio_anno
					WHERE
						investimenti_cdr_bilancio_anno.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);
				$this->anno_inizio = $db->getField("anno_inizio", "Number", true);
                $this->anno_termine = $db->getField("anno_termine", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiCdrBilancioAnno con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$cdr_bilancio = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_cdr_bilancio_anno.*
				FROM
					investimenti_cdr_bilancio_anno
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $cdr_bilancio[] = new InvestimentiCdrBilancioAnno($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $cdr_bilancio;
	}
    
    //restituisce array con i cdr definiti come cdr bilancio per l'anno
    public static function getCdrBilancioAnno(AnnoBudget $anno){        
		$cdr_bilancio = array();
                                             
        $cdr_bilancio_anno = InvestimentiCdrBilancioAnno::getAll();        
        if (count ($cdr_bilancio_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($cdr_bilancio_anno as $cdr) {
                if ($cdr->anno_inizio <= $anno->descrizione && ($cdr->anno_termine == null || $cdr->anno_termine >= $anno->descrizione)) {
                    $cdr_bilancio[] = Cdr::factoryFromCodice($cdr->codice_cdr, $piano_cdr);
                }
            }
        }
		return $cdr_bilancio;
	}
}