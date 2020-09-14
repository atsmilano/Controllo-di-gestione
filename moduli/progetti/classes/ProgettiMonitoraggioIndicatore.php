<?php
class ProgettiMonitoraggioIndicatore {
    public $id;
    public $id_monitoraggio;
    public $id_indicatore;
    public $valore;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {
        $db = ffDb_Sql::factory();

        $query = "
            SELECT *
            FROM progetti_monitoraggio_indicatore pmi
            WHERE pmi.ID = ".$db->toSql($id);     
        $db->query($query);
       
        if ($db->nextRecord()){

            $this->id = $db->getField("ID", "Number", true);
            $this->id_monitoraggio = $db->getField("ID_monitoraggio", "Number", true);
            $this->id_indicatore = $db->getField("ID_indicatore", "Number", true);
            $this->valore = $db->getField("valore", "Text", true);
            $this->extend = $db->getField("extend", "Text", true);
            $this->time_modifica = $db->getField("time_modifica", "Date", true);
            $this->record_attivo = $db->getField("record_attivo", "Number", true);
        }
    }

    public static function factoryFromMonitoraggio($id_monitoraggio) {
        $results_list = array();
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM progetti_monitoraggio_indicatore pmi
            WHERE pmi.record_attivo = ".$db->toSql(1)."
                AND pmi.ID_monitoraggio = ".$db->toSql($id_monitoraggio)
        ;

        $db->query($query);
        if ($db->nextRecord()){
            do {
                $monitoraggio_indicatore = new ProgettiMonitoraggioIndicatore();

                $monitoraggio_indicatore->id = $db->getField("ID", "Number", true);
                $monitoraggio_indicatore->id_monitoraggio = $db->getField("ID_monitoraggio", "Number", true);
                $monitoraggio_indicatore->id_indicatore = $db->getField("ID_indicatore", "Number", true);
                $monitoraggio_indicatore->valore = $db->getField("valore", "Text", true);
                $monitoraggio_indicatore->extend = $db->getField("extend", "Text", true);
                $monitoraggio_indicatore->time_modifica = $db->getField("time_modifica", "DateTime", true);
                $monitoraggio_indicatore->record_attivo = $db->getField("record_attivo", "Number", true);

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
            WHERE pmi.record_attivo = ".$db->toSql(1)."
                AND pmi.ID_monitoraggio = ".$db->toSql($id_monitoraggio)."
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
                $monitoraggio_indicatore->extend = $db->getField("extend", "Text", true);
                $monitoraggio_indicatore->time_modifica = $db->getField("time_modifica", "DateTime", true);
                $monitoraggio_indicatore->record_attivo = $db->getField("record_attivo", "Number", true);

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
                .$db->toSql($value["valore"]).","
                .$db->toSql($value["time_modifica"]).","
                .$db->toSql($value["record_attivo"])
                ."), ";
        }

        $insert_values = substr($insert_values, 0, strrpos($insert_values, ", "));
        $query_insert = "
            INSERT INTO progetti_monitoraggio_indicatore (ID_monitoraggio, ID_indicatore, valore, time_modifica, record_attivo)
              VALUES $insert_values
        ";
        try {
            $result = $db->execute($query_insert);
        }
        catch (Exception $ext) {
            throw $ext;
        }

        if (!$result) {
            throw new Exception("Impossibile inserire l'oggetto ProgettiMonitoraggioIndicatore nel DB. Di seguito i valori: ".json_encode($values));
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

    public static function update($id_monitoraggio, $values = array()) {
        $db = ffDB_Sql::factory();
        $query_update = "
            UPDATE progetti_monitoraggio_indicatore
                SET time_modifica = ".$db->toSql(new ffData(date("Y-m-d H:i:s"), "Datetime")).",
                    record_attivo = ".$db->toSql(new ffData(0, "Number"))."
              WHERE ID_monitoraggio = ".$db->toSql($id_monitoraggio);

        try {            
            $result = $db->execute($query_update);
            ProgettiMonitoraggioIndicatore::save($values);
        }
        catch (Exception $ext) {
            throw $ext;
        }
        if (!$result) {
            throw new Exception("Impossibile aggiornare l'oggetto ProgettiMonitoraggioIndicatore nel DB. Di seguito i valori: ".json_encode($values));
        }
        return $result;
    }
}