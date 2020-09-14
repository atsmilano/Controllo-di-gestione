<?php
class ValutazioniCdrCruscotto {	
	public $id;
    public $codice_cdr;
    public $anno_inizio;
	public $anno_fine;
	
	public function __construct($id=null) {			
		if($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						valutazioni_cdr_cruscotto.*
					FROM
						valutazioni_cdr_cruscotto
					WHERE
						valutazioni_cdr_cruscotto.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->codice_cdr = $db->getField("codice_cdr", "Text", true);				
				$this->anno_inizio = $db->getField("anno_inizio", "Text", true);
				if ((int)$db->getField("anno_fine", "Text", true) !== 0) {
					$this->anno_fine = $db->getField("anno_fine", "Text", true);
				}
				else {
					$this->anno_fine = null;
				}            
			}	
			else
				throw new Exception("Impossibile creare l'oggetto ValutazioniCdrCruscotto con ID = ".$id);			
		}
    }
	
	public static function getAll () {
		$valutazioni_cdr_cruscotto = array();
        
        $db = ffDB_Sql::factory();	
        $sql = "SELECT valutazioni_cdr_cruscotto.*
                FROM valutazioni_cdr_cruscotto";
        $db->query($sql);
        if ($db->nextRecord()) {            
            do {	
				$cdr_cruscotto = new ValutazioniCdrCruscotto();				
				$cdr_cruscotto->id = $db->getField("ID", "Number", true);
				$cdr_cruscotto->codice_cdr = $db->getField("codice_cdr", "Text", true);				
				$cdr_cruscotto->anno_inizio = $db->getField("anno_inizio", "Text", true);
				if ((int)$db->getField("anno_fine", "Text", true) !== 0) {
					$cdr_cruscotto->anno_fine = $db->getField("anno_fine", "Text", true);
				}
				else {
					$cdr_cruscotto->anno_fine = null;
				} 
				$valutazioni_cdr_cruscotto[] = $cdr_cruscotto;				                
            }while ($db->nextRecord());           
        }
        return $valutazioni_cdr_cruscotto;		
	}
	
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