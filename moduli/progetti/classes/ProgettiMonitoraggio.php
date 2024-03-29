<?php
class ProgettiMonitoraggio extends Entity {
    protected static $tablename = "progetti_monitoraggio";

    public static function getNextNumeroMonitoraggio($id_progetto) {
        $db = ffDB_Sql::factory();
        $query = "
            SELECT MAX(pm.numero_monitoraggio) AS 'current_value'
            FROM ".self::$tablename." pm
            WHERE pm.ID_progetto = ".$db->toSql($id_progetto)."
        ";

        $db->query($query);
        if ($db->nextRecord()) {
            return ($db->getField("current_value", "Number", true) + 1);
        }
        return 1;
    }

    public static function isUnicoMonitoraggioFinale($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT ".self::$tablename.".*
            FROM ".self::$tablename."
            " . $where . "
            ORDER BY ".self::$tablename.".ID
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            return true;
        }
        return false;
    }

    public static function lastInsertedID($filters = array()) {
        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ". $field ." = ". $db->toSql($value);
        }
        $sql = "
            SELECT MAX(ID) AS 'last_id'
            FROM ".self::$tablename."
            " . $where . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("last_id", "Number", true);
        }
        throw new Exception("Impossibile recuperare l'ultimo ID inserito per " . static::class);
    }
}