<?php
class ProgettiTerritorioApplicazione extends Entity {
    protected static $tablename = "progetti_territorio_applicazione";
    
    public function canDelete() {
        return empty(ProgettiProgetto::getAll(["ID_territorio_applicazione" => $this->id]));
    }
}