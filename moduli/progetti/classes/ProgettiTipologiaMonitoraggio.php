<?php
class ProgettiTipologiaMonitoraggio extends Entity {
    protected static $tablename = "progetti_tipologia_monitoraggio";
    
    public function canDelete() {
        return empty(ProgettiMonitoraggio::getAll(["ID_tipologia_monitoraggio" => $this->id]));
    }
}