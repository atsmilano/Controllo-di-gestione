<?php
class ProgettiLibreriaTipoProgetto {
    public $id;
    public $codice_tipo_progetto;
    public $descrizione_tipo_progetto;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDB_Sql::factory();
            $query = "
                SELECT *
                FROM progetti_libreria_tipo_progetto pltp 
                WHERE pltp.record_attivo = ".$db->toSql(1)."  
                    AND pltp.ID = ".$db->toSql($id);
            $db->query($query);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->codice_tipo_progetto = $db->getField("codice_tipo_progetto", "Text", true);
                $this->descrizione_tipo_progetto = $db->getField("descrizione_tipo_progetto", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiLibreriaTipoProgetto con ID = " . $id);
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
            FROM progetti_libreria_tipo_progetto pltp
            $where
            ORDER BY pltp.codice_tipo_progetto
        ";
        $db->query($query);
        if ($db->nextRecord()) {
            do {
                $libreria_tipo_progetto = new ProgettiLibreriaTipoProgetto();

                $libreria_tipo_progetto->id = $db->getField("ID", "Number", true);
                $libreria_tipo_progetto->codice_tipo_progetto = $db->getField("codice_tipo_progetto", "Text", true);
                $libreria_tipo_progetto->descrizione_tipo_progetto = $db->getField("descrizione_tipo_progetto", "Text", true);
                $libreria_tipo_progetto->extend = $db->getField("extend", "Text", true);
                $libreria_tipo_progetto->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $libreria_tipo_progetto->record_attivo = $db->getField("record_attivo", "Number", true);

                $result_lists[] = $libreria_tipo_progetto;
            } while ($db->nextRecord());
        }
        return $result_lists;
    }
}