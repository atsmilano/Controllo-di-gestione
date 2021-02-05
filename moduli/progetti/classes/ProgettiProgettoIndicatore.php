<?php
class ProgettiProgettoIndicatore extends Entity {
    protected static $tablename = "progetti_progetto_indicatore";

    public static function factoryIndicatoriNonConsuntivatiByProgettoMonitoraggio($id_progetto, $id_monitoraggio) {
        $results_list = array();

        $db = ffDB_Sql::factory();

        $query = "
            SELECT ppi.*
            FROM ".self::$tablename." ppi
                INNER JOIN progetti_monitoraggio pm ON (
                    ppi.ID_progetto = pm.ID_progetto AND
                    pm.ID = ". $db->toSql($id_monitoraggio) ."
                )
                LEFT JOIN progetti_monitoraggio_indicatore pmi ON (
                    pm.ID = pmi.ID_monitoraggio AND
                    ppi.ID = pmi.ID_indicatore
                )
            WHERE ppi.id_progetto = ". $db->toSql($id_progetto) ."
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

                $results_list[] = $progetto_indicatore;
            } while($db->nextRecord());
        }

        return $results_list;
    }

    public static function getNumeroTotaleIndicatori($id_progetto) {
        $db = ffDB_Sql::factory();

        $query = "
            SELECT COUNT(ppi.ID) AS 'totale'
            FROM ".self::$tablename." ppi
            WHERE ppi.ID_progetto = ".$db->toSql($id_progetto)."
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
            FROM ".self::$tablename." ppi
                INNER JOIN progetti_monitoraggio pm ON (
                    ppi.ID_progetto = pm.ID_progetto AND
                    pm.ID = ". $db->toSql($id_monitoraggio) ."
                )
                LEFT JOIN progetti_monitoraggio_indicatore pmi ON (
                    pm.ID = pmi.ID_monitoraggio AND
                    ppi.ID = pmi.ID_indicatore
                )
            WHERE ppi.id_progetto = ". $db->toSql($id_progetto) ."
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