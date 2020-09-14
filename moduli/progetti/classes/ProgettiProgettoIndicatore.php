<?php
class ProgettiProgettoIndicatore {
    public $id;
    public $id_progetto;
    public $descrizione;
    public $valore_atteso;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        if ($id != null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT *
                FROM progetti_progetto_indicatore ppi
                WHERE ppi.ID = ".$db->toSql($id)."
            ";
            
            $db->query($sql);

            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_progetto = $db->getField("ID_progetto", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->valore_atteso = $db->getField("valore_atteso", "Text", true);
                $this->extend = $db->getField("extend", "Text", true);
                $this->time_modifica = $db->getField("time_modifica", "Date", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            }
            else {
                throw new Exception("Impossibile creare l'oggetto ProgettiProgettoIndicatore con ID = " . $id);
            }
        }
    }

    public static function getAll($filters = array()) {
        $results_list = array();

        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT progetti_progetto_indicatore.*
            FROM progetti_progetto_indicatore
            " . $where . "
            ORDER BY progetti_progetto_indicatore.ID
        ";

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                $progetto_indicatore = new ProgettiProgettoIndicatore();

                $progetto_indicatore->id = $db->getField("ID", "Number", true);
                $progetto_indicatore->id_progetto = $db->getField("ID_progetto", "Number", true);
                $progetto_indicatore->descrizione = $db->getField("descrizione", "Text", true);
                $progetto_indicatore->valore_atteso = $db->getField("valore_atteso", "Text", true);
                $progetto_indicatore->extend = $db->getField("extend", "Text", true);
                $progetto_indicatore->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto_indicatore->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $progetto_indicatore;
            } while($db->nextRecord());
        }

        return $results_list;
    }

    public static function factoryIndicatoriNonConsuntivatiByProgettoMonitoraggio($id_progetto, $id_monitoraggio) {
        $results_list = array();

        $db = ffDB_Sql::factory();

        $query = "
            SELECT ppi.*
            FROM progetti_progetto_indicatore ppi
                INNER JOIN progetti_monitoraggio pm ON (
                    ppi.ID_progetto = pm.ID_progetto AND
                    pm.ID = ". $db->toSql($id_monitoraggio) ." AND
                    pm.record_attivo = ". $db->toSql(1) ."
                )
                LEFT JOIN progetti_monitoraggio_indicatore pmi ON (
                    pm.ID = pmi.ID_monitoraggio AND
                    ppi.ID = pmi.ID_indicatore AND
                    pmi.record_attivo = ". $db->toSql(1) ."
                )
            WHERE ppi.record_attivo = 1
                AND ppi.id_progetto = ". $db->toSql($id_progetto) ."
                AND pmi.ID IS NULL
            ORDER BY ppi.ID, pm.ID
        ";

        $db->query($query);

        if ($db->nextRecord()) {
            do {
                $progetto_indicatore = new ProgettiProgettoIndicatore();

                $progetto_indicatore->id = $db->getField("ID", "Number", true);
                $progetto_indicatore->id_progetto = $db->getField("ID_progetto", "Number", true);
                $progetto_indicatore->descrizione = $db->getField("descrizione", "Text", true);
                $progetto_indicatore->valore_atteso = $db->getField("valore_atteso", "Text", true);
                $progetto_indicatore->extend = $db->getField("extend", "Text", true);
                $progetto_indicatore->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto_indicatore->record_attivo = $db->getField("record_attivo", "Number", true);

                $results_list[] = $progetto_indicatore;
            } while($db->nextRecord());
        }

        return $results_list;
    }

    public static function getNumeroTotaleIndicatori($id_progetto) {
        $db = ffDB_Sql::factory();

        $query = "
        	SELECT COUNT(ppi.ID) AS 'totale'
            FROM progetti_progetto_indicatore ppi
            WHERE ppi.record_attivo = 1
                AND ppi.ID_progetto = ".$db->toSql($id_progetto)."
            GROUP BY ppi.ID_progetto
        ";

        $db->query($query);

        $numero_totale_indicatori = 0;

        if ($db->nextRecord()) {

            $numero_totale_indicatori = $db->getField("totale", "Number", true);
        }

        return $numero_totale_indicatori;
    }

    public static function getNumeroTotaleIndicatoriNonConsuntivati($id_progetto, $id_monitoraggio) {
        $db = ffDB_Sql::factory();

        $query = "
            SELECT COUNT(ppi.ID) AS 'totale'
            FROM progetti_progetto_indicatore ppi
                INNER JOIN progetti_monitoraggio pm ON (
                    ppi.ID_progetto = pm.ID_progetto AND
                    pm.ID = ". $db->toSql($id_monitoraggio) ." AND
                    pm.record_attivo = ". $db->toSql(1) ."
                )
                LEFT JOIN progetti_monitoraggio_indicatore pmi ON (
                    pm.ID = pmi.ID_monitoraggio AND
                    ppi.ID = pmi.ID_indicatore AND
                    pmi.record_attivo = ". $db->toSql(1) ."
                )
            WHERE ppi.record_attivo = 1
                AND ppi.id_progetto = ". $db->toSql($id_progetto) ."
                AND pmi.ID IS NULL
            GROUP BY ppi.ID_progetto
        ";
        $db->query($query);
        $numero_totale_indicatori_non_consuntivati = 0;
        if ($db->nextRecord()) {
            $numero_totale_indicatori_non_consuntivati = $db->getField("totale", "Number", true);
        }
        return $numero_totale_indicatori_non_consuntivati;
    }
}