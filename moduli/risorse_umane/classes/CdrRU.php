<?php

class CdrRU extends Cdr
{
    public function isCdrAbilitatoAnno (AnnoBudget $anno) {        
        $cdr_abilitati = RUCdrAbilitato::getAll(array("codice_cdr" => $this->codice));
        foreach($cdr_abilitati as $cdr_abilitato) {
            if (CoreHelper::annoInIntervallo($anno->descrizione, $cdr_abilitato->anno_inizio, $cdr_abilitato->anno_termine)){
                return true;                
            }
        }
        return false; 
    }
    
    public function getCdrPadreAbilitato (AnnoBudget $anno){
        foreach($this->getPadriRamo() as $cdr_padre_ramo) {
            $cdr_padre_ramo = $cdr_padre_ramo->cloneAttributesToNewObject("CdrRU");
            if ($cdr_padre_ramo->isDgAnno($anno) || $cdr_padre_ramo->isDirezioneRiferimentoAnno($anno) || $cdr_padre_ramo->isProgrammazioneStrategicaAnno($anno)) {
                break;
            }
            if ($cdr_padre_ramo->isCdrAbilitatoAnno($anno)) {
                return $cdr_padre_ramo;
            }
        }
        return null;
    }
    
    public function isProgrammazioneStrategicaAnno(AnnoBudget $anno)
    {
        $classname = static::class;
        foreach($classname::getCdrProgrammazioneStrategicaAnno($anno) as $cdr_p_s) {
            if ($cdr_p_s->codice_cdr == $this->codice){
                return true;                
            }
        }
        return false; 
    }
    
    public static function getCdrProgrammazioneStrategicaAnno(AnnoBudget $anno) {
        $cdr_programmazione_strategica = array();
        foreach (RUCdrAbilitato::getAll(array("programmazione_strategica" => true)) as $cdr_p_s) {
            if (CoreHelper::annoInIntervallo($anno->descrizione, $cdr_p_s->anno_inizio, $cdr_p_s->anno_termine)){
                $cdr_programmazione_strategica[] = $cdr_p_s;
            }
        }        
        return $cdr_programmazione_strategica;
    }
    
    public function getCdrPadreProgrammazioneStrategica(AnnoBudget $anno) {        
        foreach($this->getPadriRamo() as $cdr_padre_ramo) {
            $cdr_padre_ramo = $cdr_padre_ramo->cloneAttributesToNewObject("CdrRU");
            if ($cdr_padre_ramo->isDgAnno($anno) || $cdr_padre_ramo->isDirezioneRiferimentoAnno($anno)) {                  
                break;
            }
            if ($cdr_padre_ramo->isProgrammazioneStrategicaAnno($anno)) {
                return $cdr_padre_ramo;
            }
        }
        return null;
    }       
    
    public function isDirezioneRiferimentoAnno(AnnoBudget $anno)
    {
        $classname = static::class;
        foreach($classname::getCdrDirezioneRiferimentoAnno($anno) as $cdr_direzione_riferimento) {
            if ($cdr_direzione_riferimento->codice_cdr == $this->codice){
                return true;                
            }
        }
        return false; 
    }
             
    public function getCdrDirezioneRiferimento (AnnoBudget $anno){        
        foreach($this->getPadriRamo() as $cdr_padre_ramo) {
            $cdr_padre_ramo = $cdr_padre_ramo->cloneAttributesToNewObject("CdrRU");            
            if ($cdr_padre_ramo->isDgAnno($anno)) {
                break;
            }
            if ($cdr_padre_ramo->isDirezioneRiferimentoAnno($anno)) {
                return $cdr_padre_ramo;
            }
        }
        return null;
    }          
    
    public static function getCdrDirezioneRiferimentoAnno(AnnoBudget $anno)
    { 
        $cdr_direzione_riferimento = array();
        foreach(RUDirezioneRiferimento::getAll() as $direzione_riferimento) {            
            if (CoreHelper::annoInIntervallo($anno->descrizione, $direzione_riferimento->anno_inizio, $direzione_riferimento->anno_termine)){
                $cdr_direzione_riferimento[] = $direzione_riferimento;             
            }
        }
        return $cdr_direzione_riferimento;
    }
    
    public function isDgAnno(AnnoBudget $anno)
    {
        $classname = static::class;       
        foreach($classname::getCdrDgAnno($anno) as $cdr_direzione_riferimento) {
            if ($cdr_direzione_riferimento->codice_cdr == $this->codice){
                return true;                
            }
        }
        return false; 
    }
    
    public function getCdrDg (AnnoBudget $anno){
        foreach($this->getPadriRamo() as $cdr_padre_ramo) {
            if ($cdr_padre_ramo->isDgAnno($anno)) {
                return $cdr_padre_ramo;
            }
        }
        return null;       
    }
    
    public static function getCdrDgAnno(AnnoBudget $anno)
    { 
        $cdr_dg = array();
        foreach(RUDg::getAll() as $dg) {
            if (CoreHelper::annoInIntervallo($anno->descrizione, $dg->anno_inizio, $dg->anno_termine)){
                $cdr_dg[] = $dg;                
            }
        }
        return $cdr_dg;
    }

    public function isUOCompetenteAnno(AnnoBudget $anno)
    {
        $classname = static::class;
        foreach($classname::getUOCompetentiAnno($anno) as $uoc_competente) {
            if ($uoc_competente->codice_cdr == $this->codice){
                return true;                
            }
        }
        return false; 
    }

    public static function getUOCompetentiAnno(AnnoBudget $anno)
    {
        $uo_competenti_anno = array();
        foreach(RUUOCompetente::getAll() as $uo_competente) {
            if (CoreHelper::annoInIntervallo($anno->descrizione, $uo_competente->anno_inizio, $uo_competente->anno_termine)){
                $uo_competenti_anno[] = $uo_competente;                
            }
        }
        return $uo_competenti_anno;
    }

    public function getRichiesteAnno(AnnoBudget $anno)
    {
        return RURichiesta::getAll(array("codice_cdr_creazione" => $this->codice, "ID_anno_budget" => $anno->id));
    }

    //restituisce i cdr di competenza dell'anno
    public function getGerarchiaCompetenzaRamoCdrAnno(AnnoBudget $anno) {
        $classname = static::class;
        $cdr_competenza_ramo = array();        
        $cdr_programmazione_strategica = $classname::getCdRProgrammazioneStrategicaAnno($anno);
        $cdr_direzioni_riferimento = $classname::getCdrDirezioneRiferimentoAnno($anno);
        $cdr_figli_ramo = array();
        foreach(array_merge($cdr_programmazione_strategica, $cdr_direzioni_riferimento) as $cdr) {
            $cdr_figli_ramo[] = $cdr->codice_cdr;
        }
        foreach($this->getGerarchia(null, 0, null, $cdr_figli_ramo) as $cdr_figlio) {
            $cdr_richieste["livello"] = $cdr_figlio["livello"];
            $cdr_richieste["cdr"] = $cdr_figlio["cdr"]->cloneAttributesToNewObject("CdrRU");
            //se il cdr è abilitato e non è un Cdr di programmazione strategica
            $cdr_competenza_ramo[] = $cdr_richieste;            
        }
        return $cdr_competenza_ramo;        
    }   
    
    //restituisce le richieste di tutti i cdr figli del ramo gerarchico ad esclusione di quelli che sono programmazione strategica o direzioni riferimento e figli   
    public function getRichiesteCompetenzaRamoCdrAnno(AnnoBudget $anno) {
        $richieste_ramo = array();                
        foreach($this->getGerarchiaCompetenzaRamoCdrAnno($anno) as $cdr_figlio) {
            $richieste_cdr = $cdr_figlio["cdr"]->getRichiesteAnno($anno);
            if (count($richieste_cdr)) {
                $richieste_ramo = array_merge($richieste_ramo, $richieste_cdr);
            }                        
        }
        return $richieste_ramo;        
    }    
    
    //restituisce l'eventuale accettazione delle richieste per l'anno
    //l'accettazione è univoca per cdr-anno
    public function getAccettazioneAnno(AnnoBudget $anno) {
        $filters = array (
            "ID_anno_budget" => $anno->id,
            "codice_cdr" => $this->codice,
        );
        $accettazione_cdr_anno = RUAccettazione::GetAll($filters);
        if (empty($accettazione_cdr_anno)){
            return null;
        }
        else {
            return $accettazione_cdr_anno[0];
        }
    }      
}
