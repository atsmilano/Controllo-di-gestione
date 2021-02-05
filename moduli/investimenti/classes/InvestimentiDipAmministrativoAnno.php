<?php
class InvestimentiDipAmministrativoAnno extends Entity{		
	protected static $tablename = "investimenti_dipartimento_amministrativo_anno";			
    
    //restituisce array con i cdr definiti come dipartimento amministrativo per l'anno
    public static function getCdrDipartimentoAmministrativoAnno(AnnoBudget $anno){        
		$cdr_diparitmento_amministrativo = array();
                                             
        $cdr_diparitmento_amministrativo_anno = InvestimentiDipAmministrativoAnno::getAll();        
        if (count ($cdr_diparitmento_amministrativo_anno) > 0) {
            $cm = cm::getInstance();
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");        
            //recupero del del cdr       
            $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
            $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            foreach($cdr_diparitmento_amministrativo_anno as $cdr) {
                if ($cdr->anno_inizio <= $anno->descrizione && ($cdr->anno_termine == null || $cdr->anno_termine >= $anno->descrizione)) {
                    $cdr_diparitmento_amministrativo[] = Cdr::factoryFromCodice($cdr->codice_cdr, $piano_cdr);
                }
            }
        }
		return $cdr_diparitmento_amministrativo;
	}
}