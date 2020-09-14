<?php
class ProgettiLibreriaTipologiaMonitoraggio {
    public $id;
    public $descrizione_tipologia_monitoraggio;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDB_Sql::factory();
            $query = "
                SELECT *
                FROM progetti_libreria_tipologia_monitoraggio pltm 
                WHERE pltm.record_attivo = ".$db->toSql(1)."  
                    AND pltm.ID = ".$db->toSql($id);
            $db->query($query);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione_tipologia_monitoraggio = $db->getField("descrizione_tipologia_monitoraggio", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiLibreriaTipologiaMonitoraggio con ID = " . $id);
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
            FROM progetti_libreria_tipologia_monitoraggio pltm
            $where
            ORDER BY pltm.descrizione_tipologia_monitoraggio
        ";

        $db->query($query);
        if ($db->nextRecord()) {
            do {
                $libreria_tipologia_monitoraggio = new ProgettiLibreriaTipologiaMonitoraggio();

                $libreria_tipologia_monitoraggio->id = $db->getField("ID", "Number", true);
                $libreria_tipologia_monitoraggio->descrizione_tipologia_monitoraggio = $db->getField("descrizione_tipologia_monitoraggio", "Text", true);
                $libreria_tipologia_monitoraggio->extend = $db->getField("extend", "Text", true);
                $libreria_tipologia_monitoraggio->time_modifica = $db->getField("time_modifica", "Datetime", true);
                $libreria_tipologia_monitoraggio->record_attivo = $db->getField("record_attivo", "Number", true);

                $result_lists[] = $libreria_tipologia_monitoraggio;
            } while ($db->nextRecord());
        }
        return $result_lists;
    }
}