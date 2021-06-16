<?php
class RUAccettazione extends Entity{		
    protected static $tablename = "ru_accettazione";	
    
    //salvataggio dell'oggetto su db
    public function save() {
        $db = ffDB_Sql::factory();
        //update
        if ($this->id == null) {            
            $sql = "INSERT INTO ".self::$tablename." 
                    (
                        codice_cdr,				
                        ID_anno_budget,
                        note,						
                        data_accettazione
                    ) 
                    VALUES (
                        " . (strlen($this->codice_cdr) ? $db->toSql($this->codice_cdr) : "null") . ",						
                        " . (strlen($this->id_anno_budget) ? $db->toSql($this->id_anno_budget) : "null") . ",						
                        " . (strlen($this->note) ? $db->toSql($this->note) : "null") . ",	
                        " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . "                        
                    );";
        } else {
            $sql = "UPDATE ".self::$tablename."
                SET					
                    codice_cdr = " . (strlen($this->codice_cdr) ? $db->toSql($this->codice_cdr) : "null") . ",						
                    ID_anno_budget = " . (strlen($this->id_anno_budget) ? $db->toSql($this->id_anno_budget) : "null") . ",						
                    note = " . (strlen($this->note) ? $db->toSql($this->note) : "null") . ",	
                    data_accettazione =  " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . "                  
                WHERE 
                    ".self::$tablename.".ID = " . $db->toSql($this->id) . "
                ";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
        }
    }
}