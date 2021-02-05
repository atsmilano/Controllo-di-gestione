<?php
class InvestimentiCdrAbilitatoAnno extends Entity{		
	protected static $tablename = "investimenti_cdr_abilitato_anno";
    
    //restituisce array con i cdr definiti come cdr abilitati per l'anno
    public static function getCdrAbilitatiAnno(AnnoBudget $anno){        
		$cdr_abilitati = array();
                                             
        $cdr_abilitati_anno = InvestimentiCdrAbilitatoAnno::getAll();        
        if (count ($cdr_abilitati_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($cdr_abilitati_anno as $cdr_abilitato_anno) {
                if ($cdr_abilitato_anno->anno_inizio <= $anno->descrizione && ($cdr_abilitato_anno->anno_termine == null || $cdr_abilitato_anno->anno_termine >= $anno->descrizione)) {
                    //try catch per evitare blocco su codice cdr errato
                    try {
                        $cdr_abilitati[] = Cdr::factoryFromCodice($cdr_abilitato_anno->codice_cdr, $piano_cdr);
                    } catch (Exception $exc) {
                        
                    }                   
                }
            }
        }
		return $cdr_abilitati;
	}
}