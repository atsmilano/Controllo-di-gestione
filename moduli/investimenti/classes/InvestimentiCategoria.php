<?php
class InvestimentiCategoria extends Entity{		
	protected static $tablename = "investimenti_categoria";
    
    //viene restituito il codice dell'uoc competente per la categoria per l'anno passato come parametro (una e una sola)
    public function getCodiceUocCompetenteAnno (AnnoBudget $anno){            
        foreach(InvestimentiCategoriaUocCompetenteAnno::getAll(array("ID_categoria"=>$this->id)) as $uoc_competente_anno) {
            if ($uoc_competente_anno->anno_introduzione <= $anno->descrizione && ($uoc_competente_anno->anno_termine == null || $uoc_competente_anno->anno_termine >= $anno->descrizione)) {
                return $uoc_competente_anno->codice_cdr;
            }            
        }
		return false;        
    }
}