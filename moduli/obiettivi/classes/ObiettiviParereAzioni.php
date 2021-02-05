<?php
class ObiettiviParereAzioni extends Entity{
    protected static $tablename = "obiettivi_parere_azioni";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }    

    public static function getAttiveAnno(AnnoBudget $anno) {
        $pareri_azioni = array();
        foreach (ObiettiviParereAzioni::getAll() as $parere_azioni) {
            if ($parere_azioni->anno_introduzione <= $anno->descrizione && ($parere_azioni->anno_termine == null || $parere_azioni->anno_termine >= $anno->descrizione)) {
                $pareri_azioni[] = $parere_azioni;
            }
        }
        return $pareri_azioni;
    }

    public function canDelete() {
        $parere_azioni = ObiettiviObiettivoCdr::getAll(array("ID_parere_azioni" => $this->id));

        return empty($parere_azioni);
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