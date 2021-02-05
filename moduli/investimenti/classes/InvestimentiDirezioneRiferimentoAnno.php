<?php
class InvestimentiDirezioneRiferimentoAnno extends Entity{		
	protected static $tablename = "investimenti_direzione_riferimento_anno";	
    
    //restituisce array con i cdr definiti come direzioni di riferimento per l'anno
    public static function getDirezioneRiferimentoAnno(AnnoBudget $anno){        
		$direzioni_riferimento = array();
                                             
        $direzioni_riferimento_anno = InvestimentiDirezioneRiferimentoAnno::getAll();        
        if (count ($direzioni_riferimento_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($direzioni_riferimento_anno as $direzione_riferimento_anno) {
                if ($direzione_riferimento_anno->anno_inizio <= $anno->descrizione && ($direzione_riferimento_anno->anno_termine == null || $direzione_riferimento_anno->anno_termine >= $anno->descrizione)) {
                    $direzioni_riferimento[] = Cdr::factoryFromCodice($direzione_riferimento_anno->codice_cdr, $piano_cdr);
                }
            }
        }
		return $direzioni_riferimento;
	}
}