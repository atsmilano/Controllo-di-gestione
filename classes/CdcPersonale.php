<?php
class CdcPersonale {
    public $id;
    public $matricola_personale;
    public $codice_cdc;
    public $percentuale;
    public $data_inizio;
    public $data_fine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
		SELECT cdc_personale.*
                FROM cdc_personale
                WHERE cdc_personale.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $this->codice_cdc = $db->getField("codice_cdc", "Text", true);
                $this->percentuale = $db->getField("percentuale", "Number", true);
                $this->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto CdcPersonale con ID = " . $id);
        }
    }

    //estrazione di tutte le affernze    
    public static function getAll($filters = array()) {
        $cdc_personale = array();

        $db = ffDb_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "
                SELECT cdc_personale.*
                FROM cdc_personale
				" . $where . "
				ORDER BY cdc_personale.matricola_personale, cdc_personale.percentuale DESC, cdc_personale.codice_cdc
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdc_pers = new CdcPersonale();
                $cdc_pers->id = $db->getField("ID", "Number", true);
                $cdc_pers->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $cdc_pers->codice_cdc = $db->getField("codice_cdc", "Text", true);
                $cdc_pers->percentuale = $db->getField("percentuale", "Number", true);
                $cdc_pers->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                $cdc_pers->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));

                $cdc_personale[] = $cdc_pers;
            } while ($db->nextRecord());
        }
        return $cdc_personale;
    }

    public function update() {
        $db = ffDB_Sql::factory();
        $sql = "
                UPDATE 
                    cdc_personale
                SET							
                    matricola_personale = " . (strlen($this->matricola_personale) ? $db->toSql($this->matricola_personale) : "null") . ",
                    codice_cdc = " . (strlen($this->codice_cdc) ? $db->toSql($this->codice_cdc) : "null") . ",
                    percentuale = " . (strlen($this->percentuale) ? $db->toSql($this->percentuale) : "null") . ",
                    data_inizio = " . (strlen($this->data_inizio) ? $db->toSql($this->data_inizio) : "null") . ",
                    data_fine = " . (strlen($this->data_fine) ? $db->toSql($this->data_fine) : "null") . "
                WHERE 
                    cdc_personale.ID = " . $db->toSql($this->id) . "
            ";
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto con id = " . $this->id . "CdcPersonale nel DB");
        }

    }

    public function delete() {
        $db = ffDb_Sql::factory();
        $query = "
            DELETE FROM cdc_personale
            WHERE ID = ".$db->toSql($this->id)."
        ";
        try {
            $db->query($query);
        }
        catch (Exception $e) {
            throw new Exception("Impossibile eliminare l'oggetto CdcPersonale con ID = " . $this->id);
        }
    }
}
