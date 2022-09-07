<?php

namespace MappaturaCompetenze;

class Profilo extends \Entity
{
    protected static $tablename = "competenze_profilo";
   
    //restituisce tutte le competenze trasversali utilizzabili con le date di validità del profilo 
    public function getCompetenzeTrasversaliAssegnabili() {
        $competenze_trasversali = array();
        foreach (\MappaturaCompetenze\CompetenzaTrasversale::getAll() as $competenza_trasversale) {
            if (!\CoreHelper::verificaNonSovrapposizioneIntervalliAnno($competenza_trasversale->data_introduzione, $competenza_trasversale->data_termine, $this->data_introduzione, $this->data_termine)) {
                $competenze_trasversali[] = $competenza_trasversale;
            }
        }
        return $competenze_trasversali;        
    }
    
    public function getCompetenzeTrasversaliProfilo(){
        $filters = array(
            "ID_profilo" => $this->id,
        );
        return \MappaturaCompetenze\ProfiloCompetenzaTrasversale::getAll($filters);
    }
    
    //restituisce tutte le competenze specifiche utilizzabili con le date di validità del profilo 
    public function getCompetenzeSpecificheAssegnabili() {
        $competenze_specifiche = array();
        foreach (\MappaturaCompetenze\CompetenzaSpecifica::getAll() as $competenza_specifica) {
            if ((!\CoreHelper::verificaNonSovrapposizioneIntervalliAnno($competenza_specifica->data_introduzione, $competenza_specifica->data_termine, $this->data_introduzione, $this->data_termine))
                &&
                ($competenza_specifica->codice_cdr == $this->codice_cdr && $competenza_specifica->matricola_responsabile == $this->matricola_responsabile)
                ) {
                $competenze_specifiche[] = $competenza_specifica;
            }
        }
        return $competenze_specifiche;        
    }
    
    public function getCompetenzeSpecificheProfilo(){
        $filters = array(
            "ID_profilo" => $this->id,
        );
        return \MappaturaCompetenze\ProfiloCompetenzaSpecifica::getAll($filters);
    }
    
    //restituisce tutti valori attesi utilizzabili con le date di validità del profilo 
    public function getValoriAssegnabili() {
        $valori_attesi = array();
        foreach (\MappaturaCompetenze\Valore::getAll() as $valore_atteso) {
            if (!\CoreHelper::verificaNonSovrapposizioneIntervalliAnno($valore_atteso->data_introduzione, $valore_atteso->data_termine, $this->data_introduzione, $this->data_termine)) {
                $valori_attesi[] = $valore_atteso;
            }
        }
        return $valori_attesi;        
    }
}
