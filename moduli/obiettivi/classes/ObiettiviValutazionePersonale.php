<?php
class ObiettiviValutazionePersonale extends Entity{
    protected static $tablename = "obiettivi_valutazione_personale";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"ID_periodo_rendicontazione", "direction"=>"ASC"),array("fieldname"=>"ID_obiettivo_cdr_personale", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    public static function factoryFromObiettivoCdrPersonalePeriodo(ObiettiviObiettivoCdrPersonale $obiettivo_cdr_personale, ObiettiviPeriodoRendicontazione $periodo) {
        $filters = array(
            "ID_periodo_rendicontazione" => $periodo->id,
            "ID_obiettivo_cdr_personale" => $obiettivo_cdr_personale->id,
        );
        $valutazione = ObiettiviValutazionePersonale::getAll($filters);
        //verrà selezionata al più una valutazione
        if (count($valutazione) > 0) {
            return $valutazione[0];
        } else {
            return null;
        }
    }

    //salvataggio su db
    public function save() {
        $db = ffDB_Sql::factory();
        if ($this->id == null) {
            $sql = "
                INSERT INTO ".self::$tablename." 
                (
                    id_periodo_rendicontazione,				
                    id_obiettivo_cdr_personale,
                    perc_raggiungimento,						
                    time_ultimo_aggiornamento
                ) 
                VALUES (
                    " . (strlen($this->id_periodo_rendicontazione) ? $db->toSql($this->id_periodo_rendicontazione) : "null") . ",
                    " . (strlen($this->id_obiettivo_cdr_personale) ? $db->toSql($this->id_obiettivo_cdr_personale) : "null") . ",
                    " . (strlen($this->perc_raggiungimento) ? $db->toSql($this->perc_raggiungimento) : "null") . ",
                    " . (strlen($this->time_ultimo_aggiornamento) ? $db->toSql($this->time_ultimo_aggiornamento) : "null") . "
                );
            ";
        } else {
            $sql = "
                UPDATE ".self::$tablename."
                SET					
                    id_periodo_rendicontazione = " . (strlen($this->id_periodo_rendicontazione) ? $db->toSql($this->id_periodo_rendicontazione) : "null") . ",				
                    id_obiettivo_cdr_personale = " . (strlen($this->id_obiettivo_cdr_personale) ? $db->toSql($this->id_obiettivo_cdr_personale) : "null") . ",
                    perc_raggiungimento = " . (strlen($this->perc_raggiungimento) ? $db->toSql($this->perc_raggiungimento) : "null") . ",						
                    time_ultimo_aggiornamento = " . (strlen($this->time_ultimo_aggiornamento) ? $db->toSql($this->time_ultimo_aggiornamento) : "null") . "
                WHERE 
                    ".self::$tablename.".ID = " . $db->toSql($this->id) . "
            ";
        }
        if (!$db->execute($sql))
            throw new Exception("Impossibile salvare l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
    }
    
    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM ".self::$tablename
            . " WHERE ID = ".$db->toSql($this->id);
        
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto "
                . static::class." con ID = " . $this->id . " nel DB");
        }
    }
}