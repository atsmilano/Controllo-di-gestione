<?php

class CoanFpSecondo extends Entity {
    protected static $tablename = "coan_fp_secondo";
    
    public function delete($propaga = true) {
        if ($this->canDelete()) {
            if ($propaga) {
                foreach (CoanFpTerzo::getAll(["ID_fp_secondo" => $this->id]) as $item) {
                    if (!$item->delete()) {
                        return false;
                    }
                }
            }
        
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
        foreach (CoanFpTerzo::getAll(["ID_fp_secondo" => $this->id]) as $item) {
            if (!$item->canDelete()) {
                return false;
            }
        }
        
        return true;
    }
}