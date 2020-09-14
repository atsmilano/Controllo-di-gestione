<?php
class ProgettiLibreriaTerritorioApplicazione {
    public $id;
    public $descrizione_territorio_applicazione;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDB_Sql::factory();
            $query = "
                SELECT *
                FROM progetti_libreria_territorio_applicazione plta 
                WHERE plta.record_attivo = ".$db->toSql(1)."  
                    AND plta.ID = ".$db->toSql($id);
            $db->query($query);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione_territorio_applicazione = $db->getField("descrizione_territorio_applicazione", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiLibreriaTerritorioApplicazione con ID = " . $id);
            }
        }
    }

    public static function getAll($filters = array()) {
        $result_lists = array();
        $db = ffDB_Sql::factory();
        $where = "WHERE 1 = 1 ";
        foreach ($filters as $db_field => $value) {
            $where .= "AND " . $db_field . " = " . $db->toSql($value);
        }

        $query = "
            SELECT *
            FROM progetti_libreria_territorio_applicazione plta
            $where
            ORDER BY plta.descrizione_territorio_applicazione
        ";
        $db->query($query);
        if ($db->nextRecord()) {
            do {
                $libreria_territorio_applicazione = new ProgettiLibreriaTerritorioApplicazione();

                $libreria_territorio_applicazione->id = $db->getField("ID", "Number", true);
                $libreria_territorio_applicazione->descrizione_territorio_applicazione = $db->getField("descrizione_territorio_applicazione", "Text", true);
                $libreria_territorio_applicazione->extend = $db->getField("extend", "Text", true);
                $libreria_territorio_applicazione->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $libreria_territorio_applicazione->record_attivo = $db->getField("record_attivo", "Number", true);

                $result_lists[] = $libreria_territorio_applicazione;
            } while ($db->nextRecord());
        }
        return $result_lists;
    }
}