<?php
class CarrieraPersonale {
    public $id;
    public $matricola_personale;
    public $id_tipo_contratto;
    public $id_qualifica_interna;
    public $id_rapporto_lavoro;
    public $perc_rapporto_lavoro;
    public $posizione_organizzativa;
    public $data_inizio;
    public $data_fine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT carriera.*
                FROM carriera
                WHERE carriera.ID = " . $db->toSql($id) . "
                ORDER BY carriera.data_inizio DESC"
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $this->id_tipo_contratto = $db->getField("ID_tipo_contratto", "Number", true);
                $this->id_qualifica_interna = $db->getField("ID_qualifica_interna", "Number", true);
                $this->id_rapporto_lavoro = $db->getField("ID_rapporto_lavoro", "Number", true);
                $this->perc_rapporto_lavoro = $db->getField("perc_rapporto_lavoro", "Number", true);
                $this->posizione_organizzativa = $db->getField("posizione_organizzativa", "Number", true);
                $this->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto Carriera con ID = " . $id);
        }
    }

    //restituisce array con tutte le informazioni di carriera
    public static function getAll($filters = array()) {
        $carriera = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "SELECT carriera.*
                FROM carriera
				" . $where . "
                ORDER BY carriera.matricola_personale, carriera.data_inizio DESC";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $evento_carriera = new CarrieraPersonale();
                $evento_carriera->id = $db->getField("ID", "Number", true);
                $evento_carriera->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $evento_carriera->id_tipo_contratto = $db->getField("ID_tipo_contratto", "Number", true);
                $evento_carriera->id_qualifica_interna = $db->getField("ID_qualifica_interna", "Number", true);
                $evento_carriera->id_rapporto_lavoro = $db->getField("ID_rapporto_lavoro", "Number", true);
                $evento_carriera->perc_rapporto_lavoro = $db->getField("perc_rapporto_lavoro", "Number", true);
                $evento_carriera->posizione_organizzativa = $db->getField("posizione_organizzativa", "Number", true);
                $evento_carriera->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $evento_carriera->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                $carriera[] = $evento_carriera;
            } while ($db->nextRecord());
        }
        return $carriera;
    }

    public function update() {
        $db = ffDB_Sql::factory();
        $sql = "
                UPDATE 
                    carriera
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
                    carriera.ID = " . $db->toSql($this->id) . "
            ";
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto con id = " . $this->id . "Carriera nel DB");
        }

    }

    
    public function delete() {
        $db = ffDb_Sql::factory();
        $query = "
            DELETE FROM carriera
            WHERE ID = ".$db->toSql($this->id)."
        ";
        try {
            $db->query($query);
        }
        catch (Exception $e) {
            throw new Exception("Impossibile eliminare l'oggetto Carriera con ID = " . $this->id);
        }
    }
}