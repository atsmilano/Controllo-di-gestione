<?php
class ProgettiTipoProgetto extends Entity {
    protected static $tablename = "progetti_tipo_progetto";
    
    public function canDelete() {
        return empty(ProgettiProgetto::getAll(["ID_tipo_progetto" => $this->id]));
    }
}