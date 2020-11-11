<?php

class ProgettiRisorseFinanziarieDisponibili extends Entity {
    protected static $tablename = "progetti_risorse_finanziarie_disponibili";    
    
    public function canDelete() {
        return empty(ProgettiProgetto::getAll(["ID_risorse_finanziarie_disponibili" => $this->id]));
    }
}