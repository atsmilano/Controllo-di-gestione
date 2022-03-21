<?php
class ObiettiviArea extends Entity{
    protected static $tablename = "obiettivi_area";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $aree_obiettivo_anno = array();
        foreach (ObiettiviArea::getAll() as $area_obiettivo) {
            if ($area_obiettivo->anno_introduzione <= $anno->descrizione && ($area_obiettivo->anno_termine == 0 || $area_obiettivo->anno_termine >= $anno->descrizione)) {
                $aree_obiettivo_anno[] = $area_obiettivo;
            }
        }
        return $aree_obiettivo_anno;
    }

    public function canDelete() {
        $area = ObiettiviObiettivo::getAll(array("ID_area" => $this->id));

        return empty($area);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM ".self::$tablename."
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto " . static::class
                . "con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }
}