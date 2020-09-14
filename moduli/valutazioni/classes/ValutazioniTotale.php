<?php
class ValutazioniTotale {		
	public $id;
	public $descrizione;
	public $anno_inizio;
	public $anno_fine;
	public $ordine_visualizzazione;

	public function __construct($id=null) {
	    if($id!=null) {
            $db = ffDb_Sql::factory();

            $sql = "
                    SELECT 
                        *
                    FROM
                        valutazioni_totale
                    WHERE
                        valutazioni_totale.ID = " . $db->toSql($id)
                    ;
            $db->query($sql);
            if ($db->nextRecord())
            {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->anno_inizio = $db->getField("anno_inizio", "Text", true);
                if($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null){
                    $this->anno_fine = null;
                }
                else{
                    $this->anno_fine = $db->getField("anno_fine", "Number", true);
                }
            }
            else
                throw new Exception("Impossibile creare l'oggetto ValutazioniTotale con ID = ". $id);
	    }
	}		
		
	//restituisce array con tutti gli ambiti utilizzati per il calcolo di un totale
	public function getAmbitiTotale (){		
		$ambiti = array();
		$db = ffDb_Sql::factory();
		$sql = "SELECT
					valutazioni_ambito.ID
				FROM valutazioni_ambito
					INNER JOIN valutazioni_totale_ambito ON valutazioni_ambito.ID = valutazioni_totale_ambito.ID_ambito
                    INNER JOIN valutazioni_sezione ON valutazioni_ambito.ID_sezione = valutazioni_sezione.ID
				WHERE 
					valutazioni_totale_ambito.ID_totale = " . $db->toSql($this->id) . "
                    ORDER BY valutazioni_sezione.codice, valutazioni_ambito.codice
				";
		$db->query($sql);
		if ($db->nextRecord()){			
			do{		
				$ambiti[$db->getField("ID", "Number", true)] = new ValutazioniAmbito($db->getField("ID", "Number", true));
			}while ($db->nextRecord());			
		}
		return $ambiti;
	}
		
	//restituisce array con tutte le categorie associate al totale
	public function getCategorieTotale (){
		$categorie = array();
		$db = ffDb_Sql::factory();
		$sql = "SELECT
					valutazioni_categoria.ID
				FROM valutazioni_categoria
					INNER JOIN valutazioni_totale_categoria ON valutazioni_categoria.ID = valutazioni_totale_categoria.ID_categoria
				WHERE 
					valutazioni_totale_categoria.ID_totale = " . $db->toSql($this->id) . "
				";
		$db->query($sql);
		if ($db->nextRecord()){			
			do{		
				$categorie[$db->getField("ID", "Number", true)] = new ValutazioniCategoria($db->getField("ID", "Number", true));
			}while ($db->nextRecord());			
		}
		return $categorie;
	}

    public function getAll($filters = array()) {
        $totali = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value)." ";
        }

        $sql = "SELECT valutazioni_totale.*
                FROM valutazioni_totale
				" . $where ."
				ORDER BY valutazioni_totale.ordine_visualizzazione
		";

        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $totale = new ValutazioniTotale();
                $totale->id = $db->getField("ID", "Number", true);
                $totale->descrizione = $db->getField("descrizione", "Text", true);
                $totale->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null){
                    $totale->anno_fine = null;
                }
                else{
                    $totale->anno_fine = $db->getField("anno_fine", "Number", true);
                }
                $totali[] = $totale;
            }while ($db->nextRecord());
        }
        return $totali;
    }

    public function canDelete() {
        $totale_categorie = ValutazioniTotaleCategoria::getAll(array("ID_totale" => $this->id));
        foreach($totale_categorie as $totale_categoria) {
            //Se entità associata è false
            if(!$totale_categoria->canDelete()) {
                return false;
            }
        }

        $totale_ambiti = ValutazioniTotaleAmbito::getAll(array("ID_totale" => $this->id));
        foreach($totale_ambiti as $totale_ambito) {
            if(!$totale_ambito->canDelete()) {
                return false;
            }
        }

        return true;
    }

    public function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga) {
                $totale_categorie = ValutazioniTotaleCategoria::getAll(array("ID_totale" => $this->id));
                foreach($totale_categorie as $totale_categoria) {
                    if(!$totale_categoria->delete()) {
                        return false;
                    }
                }

                $totale_ambiti = ValutazioniTotaleAmbito::getAll(array("ID_totale" => $this->id));
                foreach($totale_ambiti as $totale_ambito) {
                    if(!$totale_ambito->delete()) {
                        return false;
                    }
                }
            }

            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_totale
                WHERE valutazioni_totale.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniTotale con ID='" . $this->id . "' dal DB");
            }

            return true;
        }

        return false;
    }
    
    public function isTotaleDaAggiornare(ValutazioniValutazionePeriodica $valutazione) {
        $db = ffDB_Sql::factory();
        
        $sql = "
            SELECT valutazioni_totale_precalcolato.time_aggiornamento
            FROM valutazioni_totale_precalcolato
            WHERE valutazioni_totale_precalcolato.ID_totale = ".$db->toSql($this->id)."
                AND valutazioni_totale_precalcolato.ID_valutazione = ".$db->toSql($valutazione->id)
        ;
                
        $db->query($sql);
        if ($db->nextRecord()) {
            // Check data
            $obj_time_aggiornamento = DateTime::createFromFormat("Y-m-d H:i:s", CoreHelper::getDateValueFromDB($db->getField("time_aggiornamento", "Date", true)), new DateTimeZone("Europe/Rome"));
            //se la data è null
            if ($obj_time_aggiornamento == false){
                return true;
            }            
            $obj_now = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s"), new DateTimeZone("Europe/Rome"));
            
            if ($obj_now >= $obj_time_aggiornamento->modify(VALUTAZIONI_DIFF_ORA_RICALCOLO)) {
                return true;
            }
            else {
                return false;
            }
        }        
        return true;
    }
}