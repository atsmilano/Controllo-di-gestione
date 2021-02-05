<?php
class ValutazioniCdrCruscotto extends Entity{	
	protected static $tablename = "valutazioni_cdr_cruscotto";			
	
	public static function getCodiciCdrCruscottoAnno (AnnoBudget $anno) {
		$cdr_cruscotto_anno = array();
		foreach(ValutazioniCdrCruscotto::getAll() as $cdr_cruscotto) {
			if ($cdr_cruscotto->anno_inizio <= $anno->descrizione && ($cdr_cruscotto->anno_fine == null || $cdr_cruscotto->anno_fine >= $anno->descrizione)) {
				$cdr_cruscotto_anno[] = $cdr_cruscotto->codice_cdr;
			}
		}
		return $cdr_cruscotto_anno;
	}
}