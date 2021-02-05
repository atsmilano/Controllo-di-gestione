<?php
class ObiettiviObiettivoCdrPersonale extends Entity{
    protected static $tablename = "obiettivi_obiettivo_cdr_personale";
       
    //salvataggio dell'oggetto su db
    public function save() {
        $db = ffDB_Sql::factory();
        //update
        if ($this->id == null) {
            //TODO ripristino del record eliminato logicamente in caso di corrispondenza invece che inserimento di un nuovo record
            $sql = "INSERT INTO ".self::$tablename." 
                    (
                        ID_obiettivo_cdr,				
                        matricola_personale,
                        peso,						
                        data_accettazione,
                        data_ultima_modifica,
                        data_eliminazione
                    ) 
                    VALUES (
                        " . (strlen($this->id_obiettivo_cdr) ? $db->toSql($this->id_obiettivo_cdr) : "null") . ",						
                        " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",						
                        " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",	
                        " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . ",
                        " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                        " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                    );";
        } else {
            $sql = "UPDATE ".self::$tablename."
                SET					
                    ID_obiettivo_cdr = " . (strlen($this->id_obiettivo_cdr) ? $db->toSql($this->id_obiettivo_cdr) : "null") . ",						
                    matricola_personale = " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",						
                    peso = " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",	
                    data_accettazione =  " . (strlen($this->data_accettazione) ? $db->toSql($this->data_accettazione) : "null") . ",
                    data_ultima_modifica = " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                    data_eliminazione = " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                WHERE 
                    ".self::$tablename.".ID = " . $db->toSql($this->id) . "
                ";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
        }
    }

    //eliminazione logica
    public function logicalDelete($propagate = true) {       
        // Recupero le valutazioni associate
        if ($propagate) {
            $valutazioni_personale = ObiettiviValutazionePersonale::getAll(array("ID_obiettivo_cdr_personale" => $this->id));
        }
        
        $db = ffDB_Sql::factory();
        $sql = "
            UPDATE ".self::$tablename."
            SET data_eliminazione = " . $db->toSql(date("Y-m-d H:i:s")) . "
            WHERE ".self::$tablename.".ID = " . $db->toSql($this->id) . "
        ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare logicamente l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
        }
        else if ($propagate) {
            foreach($valutazioni_personale as $valutazione) {
                $valutazione->delete();
            }
        }        
        return true;
    }
}