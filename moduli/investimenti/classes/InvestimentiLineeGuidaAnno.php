<?php
class InvestimentiLineeGuidaAnno extends Entity{		
	protected static $tablename = "investimenti_linee_guida_anno";	
    
    //viene istanziato l'oggetto in base all'id anno passato (nel db è presente al più una linea guida per anno)
    //viene restituito false in caso non sia presente una linea guida per l'anno
    public static function factoryFromAnno(AnnoBudget $anno){
		$db = ffDb_Sql::factory();

        $sql = "
                SELECT 
                    *
                FROM
                    ".self::$tablename."
                WHERE
                    ".self::$tablename.".ID_anno_budget = " . $db->toSql($anno->id) 
                ;
		$db->query($sql);		
		if ($db->nextRecord()){	
			return new InvestimentiLineeGuidaAnno($db->getField("ID", "Number", true));            
		}
		return false;
	}
}