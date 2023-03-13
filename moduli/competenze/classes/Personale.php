<?php

namespace MappaturaCompetenze;

class Personale extends \Personale
{
    public function isAmministratoreInData (\DateTime $date) {
        foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\Amministratore", $date, "data_introduzione", "data_termine") as $amministratori_in_data){
            if ($this->matricola == $amministratori_in_data->matricola_personale){
                return true;
            }
        }
        return false;
    }
    
    //mappature per la matricola
    private function getMappature($filters, Periodo $periodo=null, $id_tipo_mappatura=null) {        
        if ($id_tipo_mappatura !== null) {
            $filters["ID_tipo_mappatura"] = $id_tipo_mappatura;
        }
        if ($periodo !== null) {
            $filters["ID_periodo"] = $periodo->id;
        }
        return MappaturaPeriodo::getAll($filters);
    }
    
    public function getMappatureRuoloValutatore (Periodo $periodo=null, $id_tipo_mappatura=null) {        
        $filters = array(
                        "matricola_valutatore"=>$this->matricola,
                        );
        return $this->getMappature($filters, $periodo, $id_tipo_mappatura);       
    }       
    
    public function getMappatureRuoloValutato (Periodo $periodo=null, $id_tipo_mappatura=null) {
        $filters = array( 
                        "matricola_personale"=>$this->matricola,
                        );
        return $this->getMappature($filters, $periodo, $id_tipo_mappatura);
    }       
    
    public function hasMappatureRuoloValutatore (Periodo $periodo=null, $id_tipo_mappatura=null) {
        if (count($this->getMappatureRuoloValutatore($periodo, $id_tipo_mappatura)) > 0) {
            return true;
        }
        return false;        
    }
    
    public function hasMappatureRuoloValutato (Periodo $periodo=null, $id_tipo_mappatura=null) {
        if (count($this->getMappatureRuoloValutato($periodo, $id_tipo_mappatura)) > 0) {
            return true;
        }
        return false;        
    }
}