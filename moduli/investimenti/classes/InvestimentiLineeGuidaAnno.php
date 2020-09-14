<?php
class InvestimentiLineeGuidaAnno {		
	public $id;
	public $descrizione;
	public $id_anno_budget;
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_linee_guida_anno
					WHERE
						investimenti_linee_guida_anno.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
				$this->id = $db->getField("ID", "Number", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
				$this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiLineeGuidaAnno con ID = ".$id);
		}
	}
    
    //viene istanziato l'oggetto in base all'id anno passato (nel db è presente al più una linea guida per anno)
    //viene restituito false in caso non sia presente una linea guida per l'anno
    public static function factoryFromAnno(AnnoBudget $anno){
		$db = ffDb_Sql::factory();

        $sql = "
                SELECT 
                    *
                FROM
                    investimenti_linee_guida_anno
                WHERE
                    investimenti_linee_guida_anno.ID_anno_budget = " . $db->toSql($anno->id) 
                ;
		$db->query($sql);		
		if ($db->nextRecord()){	
			return new InvestimentiLineeGuidaAnno($db->getField("ID", "Number", true));            
		}
		return false;
	}
}