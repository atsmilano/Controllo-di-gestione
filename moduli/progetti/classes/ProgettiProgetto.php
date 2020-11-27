<?php
class ProgettiProgetto extends Entity {
    protected static $tablename = "progetti_progetto";

    public static function viewMenuByMatricola($user_matricola) {
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM progetti_progetto pp
            WHERE (
                    pp.matricola_utente_creazione = ".$db->toSql($user_matricola)." OR
                    pp.matricola_responsabile_progetto = ".$db->toSql($user_matricola)." OR
                    pp.matricola_responsabile_riferimento_approvazione = ".$db->toSql($user_matricola)."
                )
        ";
        $db->query($query);
        if ($db->nextRecord()) {
            $result = true;
        }
        return $result;
    }

    public function getDescrizioneStato() {
        switch ($this->stato) {
            case "0":
                return "In attesa di approvazione";
            case "1":
                return "Approvato";
            case "2":
                return "Non approvato";
            case "3":
                return "Concluso";
            case "4":
                return "In attesa di validazione finale";
        }
    }

    public function save() {
        $db = ffDB_Sql::factory();

        $query_update = "
            UPDATE progetti_progetto
            SET oracle_erp = ".$db->toSql($this->oracle_erp).",
                note = ".$db->toSql($this->note).",
                stato = ".$db->toSql($this->stato).",
                data_approvazione = ".$db->toSql($this->data_approvazione).",
                numero_revisione = ".$db->toSql($this->numero_revisione)."
            WHERE ID = ".$db->toSql($this->id);
        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile aggiornare l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }

    public function saveChiudiProgetto() {
        $db = ffDB_Sql::factory();
        $query_update = "
            UPDATE progetti_progetto
            SET stato = ".$db->toSql($this->stato)."
            WHERE ID = ".$db->toSql($this->id);

        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile chiudere/riapire l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }

    public function saveValidazioneFinale() {
        $db = ffDB_Sql::factory();

        $query_update = "
            UPDATE progetti_progetto
            SET stato = ".$db->toSql($this->stato).",
                validazione_finale = ".$db->toSql($this->validazione_finale).",
                note_validazione_finale = ".$db->toSql($this->note_validazione_finale).",
                data_validazione_finale = ".$db->toSql($this->data_validazione_finale)."
            WHERE ID = ".$db->toSql($this->id);

        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile aggiornare l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }
}
