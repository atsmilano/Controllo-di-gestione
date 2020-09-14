<?php
class CostiRicaviFp {	
	public $id;
	public $codice;
    public $descrizione;
	
	public function __construct($id = null){				
		if ($id !== null) {
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						costi_ricavi_fp.*
					FROM
						costi_ricavi_fp
					WHERE
						costi_ricavi_fp.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){			
				$this->id = $db->getField("ID", "Number", true);
				$this->codice = $db->getField("codice", "Text", true);
				$this->descrizione = $db->getField("descrizione", "Text", true);
			}	
			else
				throw new Exception("Impossibile creare l'oggetto CostiRicaviFp con ID = ".$id);
		}
    }	
	
	//vengono recuperati tutti i conti associati al fattore produttivo, eventualmente filtrati per singolo cdr
	public function getContiAssociatiAnno(AnnoBudget $anno, Cdr $cdr=null) {
		$filters = array(
						"ID_anno_budget" => $anno->id,
						"Id_fp" => $this->id,
						);
		if ($cdr !== null){
			$filters["codice_cdr"] = $cdr->codice;
		}		
		$conti_associati = CostiRicaviConto::getAll($filters);
		if (count ($conti_associati)>0){
			return $conti_associati;
		}
		else {
			return null;
		}
	}
	
	public static function getFpAnno (AnnoBudget $anno) {
		$fp_anno = array();
		//la relazione è su cdr-conto, vengono quindi estratti tutti i conti del cdr ed estratti univocamente gli fp
		$conti_anno = CostiRicaviConto::getAll(array("ID_anno_budget" => $anno->id));	
		
		if (count($conti_anno)>0) {
			$fp_anno = array();			
			foreach ($conti_anno as $conto_anno){
				$found = false;
				foreach($fp_anno as $fp) {
					if($fp->id == $conto_anno->id_fp) {
						$found = true;
						break;
					}
				}
				if ($found == false){
					$fp_anno[] = new CostiRicaviFp($conto_anno->id_fp);
				}
			}
		}
		
		return $fp_anno ;				
	}
}