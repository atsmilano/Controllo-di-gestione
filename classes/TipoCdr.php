<?php
class TipoCdr extends Entity {		
    protected static $tablename = "tipo_cdr";
    
	//restituisce un array con tutti i tipi cdr che possono essere definiti come padre
    public function getPadri () {
        $tipi_cdr_padri = array();
        
		foreach(TipoCdrPadre::getAll() as $tipo_cdr_padre){
            $calling_class = static::class;
			//l'accoppiata tipo_cdr e tipo_cdr_padre è univoca, nel caso in cui il tipo_cdr sia quello dell'oggetto viene restituito il padre
			if ($tipo_cdr_padre->id_tipo_cdr == $this->id){
				try {
					$tipi_cdr_padri[] = new $calling_class($tipo_cdr_padre->id_tipo_cdr_padre);					
				} 				
				catch (Exception $ex) {
					ffErrorHandler::raise($ex->getMessage());
				}				                
			}
		}
		return $tipi_cdr_padri;
    } 		
	
	//restituisce un array con le relazione con TipoCdrPadre
	public function getPadriRelation (){
        $tipi_cdr_padri = array();
        
		foreach(TipoCdrPadre::getAll() as $tipo_cdr_padre){
			//l'accoppiata tipo_cdr e tipo_cdr_padre è univoca, nel caso in cui il tipo_cdr sia quello dell'oggetto viene restituito il padre
			if ($tipo_cdr_padre->id_tipo_cdr == $this->id){
				try {
					$tipi_cdr_padri[] = new TipoCdrPadre($tipo_cdr_padre->id);					
				} 				
				catch (Exception $ex) {
					ffErrorHandler::raise($ex->getMessage());
				}				                
			}
		}
		return $tipi_cdr_padri;
    }
	
	//restituisce un array di tutte le tipologie che possono essere figlie di quella sulla quale viene richiamato il metodo
	public function getFigli (){
        $tipi_cdr_figli = array();
        
		//vengono estratte tutte le relazioni
		foreach(TipoCdrPadre::getAll() as $tipo_cdr_padre){
			//se il tipo cdr risulta padre il figlio viene accodato all'array da restituire
			if ($this->id == $tipo_cdr_padre->id_tipo_cdr_padre){
				try {
                    $calling_class = static::class;
					$tipi_cdr_figli[] = new $calling_class($tipo_cdr_padre->id_tipo_cdr);					
				} 				
				catch (Exception $ex) {
					ffErrorHandler::raise($ex->getMessage());
				}					
			}		
		}
		return $tipi_cdr_figli;
    }
    
    //restituisce true se l'oggetto può essere eliminato (nessuna relazione vincolante)
    public function canDelete() {
        $anagrafiche_cdr_tipo = AnagraficaCdr::getAll(array("ID_tipo_cdr" => $this->id));
        return empty($anagrafiche_cdr_tipo);
    }
}