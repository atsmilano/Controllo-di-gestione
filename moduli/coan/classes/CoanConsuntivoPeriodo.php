<?php
class CoanConsuntivoPeriodo extends Entity {
    protected static $tablename = "coan_consuntivo_periodo";
    
    public function save() {
        $db = ffDb_Sql::factory();
        
        $calling_class = static::class;
        if ($this->id == null) {
            // INSERT
            $sql = "INSERT INTO ".$calling_class::$tablename." (
                    ID_conto, 
                    ID_cdc_coan,
                    ID_periodo_coan, 
                    budget, 
                    consuntivo
                ) VALUES (
                    ".$db->toSql($this->id_conto).",
                    ".$db->toSql($this->id_cdc_coan).",
                    ".$db->toSql($this->id_periodo_coan).",
                    ".$db->toSql($this->budget).",
                    ".$db->toSql($this->consuntivo)."
                )
            ";
            if (!$db->execute($sql)) {
                throw new Exception("Impossibile salvare l'oggetto ".$calling_class." "
                    . "con ID = " . $this->id . " nel DB");
            }
        }
        else {
            // UPDATE
        }
    }
    
    public static function deleteDatiPeriodo($id_periodo_coan) {
        $db = ffDb_Sql::factory(); 
        
        $calling_class = static::class;        
        $sql = "DELETE FROM ".$calling_class::$tablename." 
                WHERE ID_periodo_coan = ".$db->toSql($id_periodo_coan); 
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare i dati del periodo ID = ".$id_periodo_coan." dalla tabella ".$calling_class);
        }        
    }
}