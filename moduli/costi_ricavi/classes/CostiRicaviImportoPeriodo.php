<?php
class CostiRicaviImportoPeriodo extends Entity {
    protected static $tablename = "costi_ricavi_importo_periodo";

    public static function factoryFromPeriodoConto(CostiRicaviPeriodo $periodo, CostiRicaviConto $conto) {
        $filters = array(
            "ID_periodo" => $periodo->id,
            "ID_conto" => $conto->id,
        );
        $importi_periodo = CostiRicaviImportoPeriodo::getAll($filters);
        //getAll() restituirà al più un elemento
        if (count($importi_periodo) > 0) {
            return $importi_periodo[0];
        } else {
            return null;
        }
    }
    
    public function save() {
        $db = ffDb_Sql::factory();
        
        $calling_class = static::class;
        if ($this->id == null) {
            // INSERT
            $sql = "INSERT INTO ".$calling_class::$tablename." (
                    ID_periodo,
                    ID_conto, 
                    campo_1,
                    campo_2,
                    campo_3,
                    campo_4
                ) VALUES (
                    ".$db->toSql($this->id_periodo).",
                    ".$db->toSql($this->id_conto).",                                        
                    ".$db->toSql($this->campo_1).",
                    ".$db->toSql($this->campo_2).",
                    ".$db->toSql($this->campo_3).",
                    ".$db->toSql($this->campo_4)."
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
    
    public static function deleteDatiPeriodo($id_periodo) {
        $db = ffDb_Sql::factory(); 
        
        $calling_class = static::class;        
        $sql = "DELETE FROM ".$calling_class::$tablename." 
                WHERE ID_periodo = ".$db->toSql($id_periodo); 
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare i dati del periodo ID = ".$id_periodo_coan." dalla tabella ".$calling_class);
        }        
    }
}