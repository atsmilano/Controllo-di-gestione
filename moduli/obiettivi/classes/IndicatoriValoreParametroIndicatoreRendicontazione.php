<?php
class IndicatoriValoreParametroIndicatoreRendicontazione extends Entity {
    protected static $tablename = "indicatori_valore_parametro_indicatore_rendicontazione";

    //salvataggio su db
    //aggiornamento
    public function save() {
        $db = ffDB_Sql::factory();
        //update
        if ($this->id == null) {
            //TODO ripristino del record eliminato logicamente in caso di corrispondenza invece che inserimento di un nuovo record
            $sql = "INSERT INTO indicatori_valore_parametro_indicatore_rendicontazione 
                    (
                        ID_rendicontazione,				
                        ID_parametro_indicatore,
                        valore
                    ) 
                    VALUES (
                        " . (strlen($this->id_rendicontazione) ? $db->toSql($this->id_rendicontazione) : "null") . ",
                        " . (strlen($this->id_parametro_indicatore) ? $db->toSql($this->id_parametro_indicatore) : "null") . ",
                        " . (strlen($this->valore) ? $db->toSql($this->valore) : "null") . "
                    );";
        } else {
            $sql = "UPDATE indicatori_valore_parametro_indicatore_rendicontazione
                    SET					
                        ID_rendicontazione = " . (strlen($this->id_rendicontazione) ? $db->toSql($this->id_rendicontazione) : "null") . ",						
                        ID_parametro_indicatore = " . (strlen($this->id_parametro_indicatore) ? $db->toSql($this->id_parametro_indicatore) : "null") . ",
                        valore = " . (strlen($this->valore) ? $db->toSql($this->valore) : "null") . "                       
                    WHERE 
                        indicatori_valore_parametro_indicatore_rendicontazione.ID = " . $db->toSql($this->id) . "
                    ";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto IndicatoriValoreParametroIndicatoreRendicontazione con ID = " . $this->id . " nel DB");
        }
    }
    
    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM indicatori_valore_parametro_indicatore_rendicontazione
            WHERE ID = ".$db->toSql($this->id);
            
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto "
                . "IndicatoriValoreParametroIndicatoreRendicontazione con ID = " . $this->id . " nel DB");
        }        
        return true;
    }
}