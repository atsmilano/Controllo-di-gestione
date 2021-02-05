<?php
class StrategiaDescrizioneIntroduttiva extends Entity {
    protected static $tablename = "strategia_descrizione_introduttiva";
    
    public static function factoryFromAnnoBudget(AnnoBudget $anno) {
        $descrizione = new StrategiaDescrizioneIntroduttiva();
        
        $db = ffDb_Sql::factory();

        $sql = "
            SELECT *
            FROM ".self::$tablename."
            WHERE anno_introduzione <= " . $db->toSql($anno->descrizione) . "
            ORDER BY anno_introduzione DESC
            LIMIT 1
        ";

        $db->query($sql);
        if ($db->nextRecord()) {
            $descrizione->id = $db->getField("ID", "Number", true);
            $descrizione->descrizione = $db->getField("descrizione", "Text", true);
            $descrizione->anno_introduzione = $db->getField("anno_introduzione", "Number", true);
            
            return $descrizione;
        } else {
            throw new Exception("Nessuna descrizione introduttiva di strategia definita per l'anno " . $anno->descrizione);
        }
    }
}