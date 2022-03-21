<?php
class ObiettiviTipo extends Entity{
    protected static $tablename = "obiettivi_tipo";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $tipi_obiettivo_anno = array();
        foreach (ObiettiviTipo::getAll() as $tipo_obiettivo) {
            if ($tipo_obiettivo->anno_introduzione <= $anno->descrizione && ($tipo_obiettivo->anno_termine == 0 || $tipo_obiettivo->anno_termine >= $anno->descrizione)) {
                $tipi_obiettivo_anno[] = $tipo_obiettivo;
            }
        }
        return $tipi_obiettivo_anno;
    }

    public function canDelete() {
        $tipo = ObiettiviObiettivo::getAll(array("ID_tipo" => $this->id));
        return empty($tipo);
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM ".self::$tablename."
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ".static::class
                . "con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
}