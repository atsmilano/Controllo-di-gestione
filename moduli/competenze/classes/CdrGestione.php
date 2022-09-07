<?php

namespace MappaturaCompetenze;

class CdrGestione extends \Cdr
{
    public function isCdrGestioneInData (\DateTime $date) {
        foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\CdrGestioneAbilitato", $date, "data_introduzione", "data_termine") as $cdr_gestione_in_data){            
            if ($this->codice == $cdr_gestione_in_data->codice_cdr){
                return true;                
            }   
        }               
        return false; 
    }
    
    //restituisce tutti i profili definiti da un responsabile per il CdR
    public function getProfiliResponsabile ($matricola_responsabile) {
        $filters = array(
            "matricola_responsabile" => $matricola_responsabile,
            "codice_cdr" => $this->codice,
        );
        return \MappaturaCompetenze\Profilo::getAll($filters);
    }
    
    //restituisce tutte le competenze specifiche definite ddal responsabile del cdr    
    public function getCompetenzeSpecificheResponsabileInData ($matricola_responsabile, \DateTime $date) {
        $filters = array(
            "matricola_responsabile" => $matricola_responsabile,
            "codice_cdr" => $this->codice,
        );
        return \CoreHelper::getObjectsInData (__NAMESPACE__."\CompetenzaSpecifica", $date, "data_introduzione", "data_termine", $filters);
    }
    
    //restituisce i cdr di competenza dell'anno
    public function getGerarchiaRamoCdrGestioneData(\DateTime $date) {        
        $cdr_gestione_ramo = array();
        $cdr_abilitati_in_data = array();            
        foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\CdrGestioneAbilitato", $date, "data_introduzione", "data_termine") as $cdr_gestione_in_data){            
            $cdr_abilitati_in_data[] = $cdr_gestione_in_data;
        }
        foreach($this->getGerarchia(null, 0, null, $cdr_abilitati_in_data) as $cdr_abilitato) { 
            //se il cdr è abilitato e non è un Cdr di gestione
            $cdr_gestione_ramo[] = $cdr_abilitato["cdr"]->cloneAttributesToNewObject("CdrRU");            
        }
        return $cdr_gestione_ramo;        
    }
}
