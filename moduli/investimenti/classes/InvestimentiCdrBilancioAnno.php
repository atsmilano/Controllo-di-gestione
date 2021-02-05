<?php
class InvestimentiCdrBilancioAnno extends Entity{		
	protected static $tablename = "investimenti_cdr_bilancio_anno";	
    
    //restituisce array con i cdr definiti come cdr bilancio per l'anno
    public static function getCdrBilancioAnno(AnnoBudget $anno){        
		$cdr_bilancio = array();
                                             
        $cdr_bilancio_anno = InvestimentiCdrBilancioAnno::getAll();        
        if (count ($cdr_bilancio_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($cdr_bilancio_anno as $cdr) {
                if ($cdr->anno_inizio <= $anno->descrizione && ($cdr->anno_termine == null || $cdr->anno_termine >= $anno->descrizione)) {
                    $cdr_bilancio[] = Cdr::factoryFromCodice($cdr->codice_cdr, $piano_cdr);
                }
            }
        }
		return $cdr_bilancio;
	}
}