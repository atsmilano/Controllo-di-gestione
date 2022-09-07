<?php
class ProgettiMonitoraggioIndicatore extends Entity {
    protected static $tablename = "progetti_monitoraggio_indicatore";

    public static function factoryFromMonitoraggio($id_monitoraggio) {
        $results_list = array();
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM ".self::$tablename." pmi
            WHERE pmi.ID_monitoraggio = ".$db->toSql($id_monitoraggio)
        ;

        $db->query($query);
        if ($db->nextRecord()){
            do {
                $monitoraggio_indicatore = new ProgettiMonitoraggioIndicatore();

                $monitoraggio_indicatore->id = $db->getField("ID", "Number", true);
                $monitoraggio_indicatore->id_monitoraggio = $db->getField("ID_monitoraggio", "Number", true);
                $monitoraggio_indicatore->id_indicatore = $db->getField("ID_indicatore", "Number", true);
                $monitoraggio_indicatore->valore = $db->getField("valore", "Text", true);

                $results_list[] = $monitoraggio_indicatore;
            } while ($db->nextRecord());
        }
        return $results_list;
    }

    public static function factoryFromMonitoraggioIndicatore($id_monitoraggio, $id_indicatore) {
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM ".self::$tablename." pmi
            WHERE pmi.ID_monitoraggio = ".$db->toSql($id_monitoraggio)."
                AND pmi.ID_indicatore = ".$db->toSql($id_indicatore)
        ;

        $db->query($query);
        if ($db->nextRecord()){
            do {
                $monitoraggio_indicatore = new ProgettiMonitoraggioIndicatore();

                $monitoraggio_indicatore->id = $db->getField("ID", "Number", true);
                $monitoraggio_indicatore->id_monitoraggio = $db->getField("ID_monitoraggio", "Number", true);
                $monitoraggio_indicatore->id_indicatore = $db->getField("ID_indicatore", "Number", true);
                $monitoraggio_indicatore->valore = $db->getField("valore", "Text", true);

                $results_list = $monitoraggio_indicatore;
            } while ($db->nextRecord());
        }
        return $results_list;
    }

    public static function deleteRecord($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }

        $query_delete = "
            DELETE FROM ".self::$tablename."
            $where
        ";

        try {
            $db->execute($query_delete);
        }
        catch (Exception $ext) {
            throw $ext;
        }
        return true;
    }
}