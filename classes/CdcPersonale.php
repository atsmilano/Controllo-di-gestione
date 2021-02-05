<?php
class CdcPersonale extends Entity{
    protected static $tablename = "cdc_personale";
    
    //estrazione di tutte le affernze   
    public static function getAll($where=array(), $order=array(
                                                    array("fieldname"=>"matricola_personale", "direction"=>"ASC"),
                                                    array("fieldname"=>"percentuale", "direction"=>"DESC"),
                                                    array("fieldname"=>"codice_cdc", "direction"=>"ASC"))
                                                    ) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
    
    public function update() {
        $db = ffDB_Sql::factory();
        $sql = "
                UPDATE 
                    ".self::$tablename."
                SET							
                    matricola_personale = " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",
                    codice_cdc = " . (strlen($this->codice_cdc) ? $db->toSql($this->codice_cdc) : "null") . ",
                    percentuale = " . (strlen($this->percentuale) ? $db->toSql($this->percentuale) : "null") . ",
                    data_inizio = " . (strlen($this->data_inizio) ? $db->toSql($this->data_inizio) : "null") . ",
                    data_fine = " . (strlen($this->data_fine) ? $db->toSql($this->data_fine) : "null") . "
                WHERE 
                    ".self::$tablename.".ID = " . $db->toSql($this->id) . "
            ";
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto con id = " . $this->id . " ".static::class." nel DB");
        }

    }

    public function delete() {
        $db = ffDb_Sql::factory();
        $query = "
            DELETE FROM ".self::$tablename."
            WHERE ID = ".$db->toSql($this->id)."
        ";
        try {
            $db->query($query);
        }
        catch (Exception $e) {
            throw new Exception("Impossibile eliminare l'oggetto ".static::class." con ID = " . $this->id);
        }
    }
}
