<?php
class ObiettiviAreaRisultato extends Entity{
    protected static $tablename = "obiettivi_area_risultato";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $aree_risultato_obiettivo_anno = array();
        foreach (ObiettiviAreaRisultato::getAll() as $area_risultato_obiettivo) {
            if ($area_risultato_obiettivo->anno_introduzione <= $anno->descrizione && ($area_risultato_obiettivo->anno_termine == 0 || $area_risultato_obiettivo->anno_termine >= $anno->descrizione)) {
                $aree_risultato_obiettivo_anno[] = $area_risultato_obiettivo;
            }
        }
        return $aree_risultato_obiettivo_anno;
    }

    public function canDelete() {
        $area_risultato = ObiettiviObiettivo::getAll(array("ID_area_risultato" => $this->id));

        return empty($area_risultato);
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