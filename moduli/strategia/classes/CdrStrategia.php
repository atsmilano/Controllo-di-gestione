<?php
class CdrStrategia extends Cdr {
    public function getPadreStrategico(AnnoBudget $anno) {
        $cdr = null;
                
        if ($this->id_padre == 0){
            return $this;
        } else {
            $cdr = new CdrStrategia($this->id_padre, $this->useSql);
        }
       
        //si itera di livello in livello fino al raggiungimento di un cdr di programmazione strategica		
        //verifica di sicurezza sull'elemento radice. Casistica teoricamente impossibile introdotta per robustezza
        $programmazione_strategica = false;
        foreach (StrategiaCdrProgrammazioneStrategica::getCdrProgrammazioneStrategicaAnno($anno) as $cdr_programmazione_strategica) {
            if ($cdr->codice == $cdr_programmazione_strategica) {     
                $programmazione_strategica = true;
                break;
            }
        }
     
        if ($programmazione_strategica == true) {
            return $cdr;			
        } else {
            return $cdr->getPadreStrategico($anno);
        }	
        
    }
}