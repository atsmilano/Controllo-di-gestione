<?php
class ObiettiviCampoRevisione extends Entity{		
    protected static $tablename = "obiettivi_campo_revisione";
    
    //vengono restituite tutte le scelte per il campo istanziato
    public function getScelte() {        
        return ObiettiviSceltaCampoRevisione::getAll(array("ID_campo_revisione"=>$this->id));
    } 
    
    //ritorna true se l'istanza può essere eliminata
    public function canDelete() {
        $can_delete = true;
        /*
        $can_delete = empty(ObiettiviPeriodoRendicontazione::getAll(array("ID_campo_revisione" => $this->id)));
        //la seguente verifica è ridondante. Se il campo non è utilizzato in nessun periodo non ci saranno rendicontazioni con ID_scelta collegata al campo.
        //viene comunque mantenuto il controllo per robustezza
        if ($can_delete == true) {
            foreach ($this->getScelte() as $scelta) {
                if($scelta->canDelete() == false){
                    $can_delete = false;
                    break;
                }
            }
        }*/
        return $can_delete;        
    }

    public function delete($propagate = true) {
        if ($this->canDelete()) {
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM obiettivi_campo_revisione
                WHERE ID = " . $db->toSql($this->id);

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ObiettiviArea "
                . "con ID='" . $this->id . "' dal DB");
            }            
            else {
                if ($propagate == true) {
                    foreach ($this->getScelte() as $scelta) {
                        $scelta->delete();
                    }
                }
            }
            return true;
        }
        return false;
    }
}