<?php
class ObiettiviSceltaCampoRevisione extends Entity{		
    protected static $tablename = "obiettivi_scelta_campo_revisione";   
    
    //ritorna true se l'istanza può essere eliminata
    public function canDelete() {
        $can_delete = true;
        /*
        $can_delete = empty(ObiettiviRendicontazione::getAll(array("ID_scelta_campo_revisione" => $this->id)));
        */
        return $can_delete;
    }

    public function delete() {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_scelta_campo_revisione
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto obiettivi_scelta_campo_revisione "
                . "con ID='" . $this->id . "' dal DB");
            }            
            
            return true;
        }
        return false;
    }
}