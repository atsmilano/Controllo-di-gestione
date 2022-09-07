<?php

namespace MappaturaCompetenze;

class Periodo extends \Entity
{
    protected static $tablename = "competenze_periodo"; 
    
    //restituisce tutti i profili definiti da un responsabile per il CdR per il periodo
    public function getProfiliResponsabileCdrPeriodo ($matricola_responsabile, CdrGestione $cdr) {
        $profili_responsabile_periodo = array();
        $filters = array(
            "matricola_responsabile" => $matricola_responsabile,
            "codice_cdr" => $cdr->codice,
        );
        foreach(\MappaturaCompetenze\Profilo::getAll($filters) as $profilo) {
            if (!\CoreHelper::verificaNonSovrapposizioneIntervalliAnno($profilo->data_inizio, $profilo->data_fine, $this->data_introduzione, $this->data_termine)) {
                $profili_responsabile_periodo[] = $profilo;
            }
        }
        return $profili_responsabile_periodo;
    }
}