<?php
class InvestimentiDirezioneRiferimentoAnno {		
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
						investimenti_direzione_riferimento_anno
					WHERE
						investimenti_direzione_riferimento_anno.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->anno_inizio = $db->getField("anno_inizio", "Number", true);
				$this->anno_termine = $db->getField("anno_termine", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiDirezioneRiferimentoAnno con ID = ".$id);
		}
	}
    
    public static function getAll($filters=array()){			
		$direzioni_riferimento = array();
        
		$db = ffDb_Sql::factory();
		$where = "WHERE 1=1 ";
		foreach ($filters as $field => $value){
			$where .= "AND ".$field."=".$db->toSql($value)." ";		
		}
        
		$sql = "
				SELECT 
					investimenti_direzione_riferimento_anno.*
				FROM
					investimenti_direzione_riferimento_anno
                    " . $where . "
				";
		$db->query($sql);
		if ($db->nextRecord()){
            do {
                $direzioni_riferimento[] = new InvestimentiDirezioneRiferimentoAnno($db->getField("ID", "Number", true));			
            } while ($db->nextRecord());
		}	
		return $direzioni_riferimento;
	}
    
    //restituisce array con i cdr definiti come direzioni di riferimento per l'anno
    public static function getDirezioneRiferimentoAnno(AnnoBudget $anno){        
		$direzioni_riferimento = array();
                                             
        $direzioni_riferimento_anno = InvestimentiDirezioneRiferimentoAnno::getAll();        
        if (count ($direzioni_riferimento_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($direzioni_riferimento_anno as $direzione_riferimento_anno) {
                if ($direzione_riferimento_anno->anno_inizio <= $anno->descrizione && ($direzione_riferimento_anno->anno_termine == null || $direzione_riferimento_anno->anno_termine >= $anno->descrizione)) {
                    $direzioni_riferimento[] = Cdr::factoryFromCodice($direzione_riferimento_anno->codice_cdr, $piano_cdr);
                }
            }
        }
		return $direzioni_riferimento;
	}
}