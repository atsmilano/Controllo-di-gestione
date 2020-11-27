<?php

class CoanConto extends Entity {
    protected static $tablename = "coan_conto";
    
    public function delete() {
        if ($this->canDelete()) {
            $db = ffDB_Sql::factory();
            $sql = "DELETE FROM ".self::$tablename." WHERE ID = ".$db->toSql($this->id);
            if (!$db->execute($sql)) {
                throw new Exception(
                    "Impossibile eliminare l'oggetto ".static::class." con ID=" . $this->id . " dal DB"
                );
            }
            
            return true;
        }
        
        return false;
    }
    
    public function canDelete() {
        return empty(CoanConsuntivoPeriodo::getAll(["ID_conto" => $this->id]));
    }
}