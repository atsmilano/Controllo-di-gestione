<?php
class ObiettiviOrigine extends Entity{
    protected static $tablename = "obiettivi_origine";    

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    } 
    
    public static function getAttiviAnno(AnnoBudget $anno) {
        $origini_obiettivo_anno = array();
        foreach (ObiettiviOrigine::getAll() as $origine_obiettivo) {
            if ($origine_obiettivo->anno_introduzione <= $anno->descrizione && ($origine_obiettivo->anno_termine == null || $origine_obiettivo->anno_termine >= $anno->descrizione)) {
                $origini_obiettivo_anno[] = $origine_obiettivo;
            }
        }
        return $origini_obiettivo_anno;
    }

    public function canDelete() {
        $origine = ObiettiviObiettivo::getAll(array("ID_origine" => $this->id));

        return empty($origine);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM ".self::$tablename."
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto " .static::class
                . "con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
}