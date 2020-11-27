<?php
class CoanPeriodo extends Entity {
    protected static $tablename = "coan_periodo";
    
    public function canDelete() {
        return empty(CoanConsuntivoPeriodo::getAll(["ID_periodo_coan" => $this->id]));
    }
}