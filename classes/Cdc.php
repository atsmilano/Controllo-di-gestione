<?php

class Cdc {
    public $id;
    public $id_anagrafica_cdc;
    public $id_cdr;
    //recuperati da anagrafica cdc
    public $codice;
    public $descrizione;
    public $abbreviazione;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
					SELECT 
						cdc.ID,
                        cdc.ID_anagrafica_cdc,
                        anagrafica_cdc.codice,
                        anagrafica_cdc.descrizione,
                        anagrafica_cdc.abbreviazione,
                        cdc.ID_cdr
					FROM
						cdc
                        INNER JOIN anagrafica_cdc
                            ON cdc.ID_anagrafica_cdc = anagrafica_cdc.ID
					WHERE
						cdc.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_anagrafica_cdc = $db->getField("ID_anagrafica_cdc", "Number", true);
                $this->id_cdr = $db->getField("ID_cdr", "Number", true);
                //recuperati da anagrafica cdc
                $this->codice = $db->getField("codice", "Text", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->abbreviazione = $db->getField("abbreviazione", "Text", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto Cdc con ID = " . $id);
            }
        }
    }

    public static function factoryFromCodice($codice, PianoCdr $piano_cdr) {
        $db = ffDb_Sql::factory();

        $sql = "
                SELECT 
                    cdc.ID,
                    cdc.ID_anagrafica_cdc,
                    anagrafica_cdc.codice,
                    anagrafica_cdc.descrizione,
                    anagrafica_cdc.abbreviazione,
                    cdc.ID_cdr
                FROM
                    cdc
                    INNER JOIN anagrafica_cdc
                        ON cdc.ID_anagrafica_cdc = anagrafica_cdc.ID
                WHERE
                    anagrafica_cdc.codice = " . $db->toSql($codice)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdc = new Cdc($db->getField("ID", "Number", true));
                $cdc->id = $db->getField("ID", "Number", true);
                $cdc->id_anagrafica_cdc = $db->getField("ID_anagrafica_cdc", "Number", true);
                $cdc->id_cdr = $db->getField("ID_cdr", "Number", true);
                //recuperati da anagrafica cdc
                $cdc->codice = $db->getField("codice", "Text", true);
                $cdc->descrizione = $db->getField("descrizione", "Text", true);
                $cdc->abbreviazione = $db->getField("abbreviazione", "Text", true);

                $cdr = new Cdr($cdc->id_cdr);
                if ($cdr->id_piano_cdr == $piano_cdr->id) {                    
                    return $cdc;
                }
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto Cdc con codice = " . $codice . " per il piano ID = " . $piano_cdr->id);
    }

    //eliminazione
    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM cdc WHERE cdc.ID = " . $db->toSql($this->id);
        if (!$db->execute($sql))
            throw new Exception("Impossibile eliminare l'oggetto Cdc con ID = " . $id . " nel DB");
    }

    //estrazione di tutti i cdc
    //il parametro Cdr viene utilizzato per velocizzare le richieste in caso di estrazioni di cdc di un solo cdr
    public static function getAll($filters = array()) {
        $all_cdc = array();

        $db = ffDb_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "
                SELECT 
                    cdc.ID,
                    cdc.ID_anagrafica_cdc,
                    anagrafica_cdc.codice,
                    anagrafica_cdc.descrizione,
                    anagrafica_cdc.abbreviazione,
                    cdc.ID_cdr
                FROM
                    cdc
                    INNER JOIN anagrafica_cdc
                        ON cdc.ID_anagrafica_cdc = anagrafica_cdc.ID
				" . $where . "
				ORDER BY anagrafica_cdc.descrizione
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdc = new Cdc($db->getField("ID", "Number", true));
                $cdc->id = $db->getField("ID", "Number", true);
                $cdc->id_anagrafica_cdc = $db->getField("ID_anagrafica_cdc", "Number", true);
                $cdc->id_cdr = $db->getField("ID_cdr", "Number", true);
                //recuperati da anagrafica cdc
                $cdc->codice = $db->getField("codice", "Text", true);
                $cdc->descrizione = $db->getField("descrizione", "Text", true);
                $cdc->abbreviazione = $db->getField("abbreviazione", "Text", true);

                $all_cdc[] = $cdc;
            } while ($db->nextRecord());
        }
        return $all_cdc;
    }

    //relazioni	
    public function getPersonaleCdcInData($date = null) {
        $cdc_personale = array();
        $filters = array(
            "codice_cdc" => $this->codice,
        );
        foreach (CdcPersonale::getAll($filters) as $cdc_pers) {
            $attivo = false;
            if ($date !== null) {
                if (
                    (
                    strtotime($cdc_pers->data_inizio) <= strtotime($date)) &&
                    (
                    strtotime($cdc_pers->data_fine) >= strtotime($date) ||
                    $cdc_pers->data_fine == null
                    )
                ) {
                    $attivo = true;
                }
            } else {
                $attivo = true;
            }

            if ($attivo == true) {
                $cdc_personale[] = $cdc_pers;
            }
        }
        return $cdc_personale;
    }

    public function save() {
        $db = ffDB_Sql::factory();
        if ($this->id != null) {
            $sql = "
                    UPDATE 
                        cdc
                    SET							
                        ID_anagrafica_cdc = " . (strlen($this->id_anagrafica_cdc) ? $db->toSql($this->id_anagrafica_cdc) : "null") . ",
                        ID_cdr = " . (strlen($this->id_cdr) ? $db->toSql($this->id_cdr) : "null") . ",
                    WHERE 
                        cdc.ID = " . $db->toSql($this->id) . "
                ";
        } else {
            $sql = "
                    INSERT INTO
                        cdc
                        (ID_anagrafica_cdc, ID_cdr)
                    VALUES
                        (" . (strlen($this->id_anagrafica_cdc) ? $db->toSql($this->id_anagrafica_cdc) : "null") . ",
                         " . (strlen($this->id_cdr) ? $db->toSql($this->id_cdr) : "null") . ")";
        }

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto Cdc nel DB");
        }
    }
}