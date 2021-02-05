<?php
class InvestimentiCategoriaUocCompetenteAnno extends Entity{		
	protected static $tablename = "investimenti_categoria_uoc_competente_anno";		
    
    //restituisce l'oggetto InvestimentiCategoriaUoccompetente di una categoria passata come parametro
    public static function getCategoriaUocCompetentiAnno(AnnoBudget $anno, $id_categoria=null){        
		$categoria_uoc_competenti = null;               
        foreach(InvestimentiCategoriaUocCompetenteAnno::getAll() as $categoria_uoc_competente_anno) {
            if ($categoria_uoc_competente_anno->anno_inizio <= $anno->descrizione && ($categoria_uoc_competente_anno->anno_termine == null || $categoria_uoc_competente_anno->anno_termine >= $anno->descrizione)) {
                if ($id_categoria == null || $categoria_uoc_competente_anno->id_categoria == $id_categoria) {
                    $categorie_uoc_competenti[] = $categoria_uoc_competente_anno;
                }
            }
        }        
		return $categorie_uoc_competenti;
	}
    
    //restituisce array con i cdr definiti come uoc competenti per l'anno
    public static function getUocCompetentiAnno(AnnoBudget $anno){        
		$uoc_competenti = array();
                                             
        $uoc_competenti_anno = InvestimentiCategoriaUocCompetenteAnno::getAll();        
        if (count ($uoc_competenti_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($uoc_competenti_anno as $uoc_competente_anno) {
                if ($uoc_competente_anno->anno_inizio <= $anno->descrizione && ($uoc_competente_anno->anno_termine == null || $uoc_competente_anno->anno_termine >= $anno->descrizione)) {
                    $uoc_competenti[] = Cdr::factoryFromCodice($uoc_competente_anno->codice_cdr, $piano_cdr);
                }
            }
        }
		return $uoc_competenti;
	}
}