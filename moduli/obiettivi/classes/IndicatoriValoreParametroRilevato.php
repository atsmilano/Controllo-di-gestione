<?php
class IndicatoriValoreParametroRilevato extends Entity{		
    protected static $tablename = "indicatori_valore_parametro_rilevato";
    
    public function save() {
        $db = ffDb_Sql::factory();
                
        if ($this->id == null) {
            // INSERT
            $sql = "INSERT INTO ".self::$tablename." (
                    ID_parametro, 
                    ID_periodo_rendicontazione,
                    ID_periodo_cruscotto, 
                    codice_cdr, 
                    valore,
                    modificabile, 
                    data_riferimento, 
                    data_importazione
                ) VALUES (
                    ".$db->toSql($this->id_parametro).",
                    ".$db->toSql($this->id_periodo_rendicontazione).",
                    ".$db->toSql($this->id_periodo_cruscotto).",
                    ".$db->toSql($this->codice_cdr).",
                    ".$db->toSql($this->valore).",
                    ".$db->toSql($this->modificabile).",
                    ".$db->toSql($this->data_riferimento).",
                    ".$db->toSql($this->data_importazione)."
                )
            ";
            
            if (!$db->execute($sql)) {
                throw new Exception("Impossibile salvare l'oggetto ".static::class." "
                    . "con ID = " . $this->id . " nel DB");
            }
        }
        else {
            // UPDATE
        }
    }
}