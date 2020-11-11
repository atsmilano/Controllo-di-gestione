<?php
class ProgettiMonitoraggioIndicatore extends Entity {
    protected static $tablename = "progetti_monitoraggio_indicatore";

    public static function factoryFromMonitoraggio($id_monitoraggio) {
        $results_list = array();
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM progetti_monitoraggio_indicatore pmi
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
            FROM progetti_monitoraggio_indicatore pmi
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

    public static function save($values = array()) {
        $db = ffDB_Sql::factory();

        $insert_values = "";
        foreach($values as $value){
            $insert_values .= "("
                .$db->toSql($value["ID_monitoraggio"]).","
                .$db->toSql($value["ID_indicatore"]).","
                .$db->toSql($value["valore"])
            ."), ";
        }

        $insert_values = substr($insert_values, 0, strrpos($insert_values, ", "));
        $query_insert = "
            INSERT INTO progetti_monitoraggio_indicatore (ID_monitoraggio, ID_indicatore, valore)
            VALUES $insert_values
        ";
        try {
            $result = $db->execute($query_insert);
        }
        catch (Exception $ext) {
            throw $ext;
        }

        if (!$result) {
            throw new Exception("Impossibile inserire l'oggetto ProgettiMonitoraggioIndicatore nel DB. "
                . "Di seguito i valori: ".json_encode($values));
        }
        return $result;
    }

    public static function deleteRecord($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }

        $query_delete = "
            DELETE FROM progetti_monitoraggio_indicatore
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