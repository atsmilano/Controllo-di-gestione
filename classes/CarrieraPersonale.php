<?php
class CarrieraPersonale extends Entity{
    protected static $tablename = "carriera";

    //restituisce array con tutte le informazioni di carriera
    public static function getAll($where=array(), $order=array(array("fieldname"=>"matricola_personale", "direction"=>"ASC"),array("fieldname"=>"data_inizio", "direction"=>"DESC"))) {                
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
                    ID_tipo_contratto = " . (strlen($this->id_tipo_contratto) ? $db->toSql($this->id_tipo_contratto) : "null") . ",
                    ID_qualifica_interna = " . (strlen($this->id_qualifica_interna) ? $db->toSql($this->id_qualifica_interna) : "null") . ",
                    ID_rapporto_lavoro = " . (strlen($this->id_rapporto_lavoro) ? $db->toSql($this->id_rapporto_lavoro) : "null") . ",
                    perc_rapporto_lavoro = " . (strlen($this->perc_rapporto_lavoro) ? $db->toSql($this->perc_rapporto_lavoro) : "null") . ",
                    posizione_organizzativa = " . (strlen($this->posizione_organizzativa) ? $db->toSql($this->posizione_organizzativa) : "null") . ",
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