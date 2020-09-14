<?php
class InvestimentiCdrAbilitatoAnno {		
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
						investimenti_cdr_abilitato_anno
					WHERE
						investimenti_cdr_abilitato_anno.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);
				$this->anno_inizio = $db->getField("anno_inizio", "Number", true);
                $this->anno_termine = $db->getField("anno_termine", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiCdrAbilitatoAnno con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$cdr_abilitato = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_cdr_abilitato_anno.*
				FROM
					investimenti_cdr_abilitato_anno
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $cdr_abilitato[] = new InvestimentiCdrAbilitatoAnno($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $cdr_abilitato;
	}
    
    //restituisce array con i cdr definiti come cdr abilitati per l'anno
    public static function getCdrAbilitatiAnno(AnnoBudget $anno){        
		$cdr_abilitati = array();
                                             
        $cdr_abilitati_anno = InvestimentiCdrAbilitatoAnno::getAll();        
        if (count ($cdr_abilitati_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($cdr_abilitati_anno as $cdr_abilitato_anno) {
                if ($cdr_abilitato_anno->anno_inizio <= $anno->descrizione && ($cdr_abilitato_anno->anno_termine == null || $cdr_abilitato_anno->anno_termine >= $anno->descrizione)) {
                    //try catch per evitare blocco su codice cdr errato
                    try {
                        $cdr_abilitati[] = Cdr::factoryFromCodice($cdr_abilitato_anno->codice_cdr, $piano_cdr);
                    } catch (Exception $exc) {
                        
                    }                   
                }
            }
        }
		return $cdr_abilitati;
	}
}