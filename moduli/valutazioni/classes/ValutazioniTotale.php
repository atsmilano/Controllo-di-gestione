<?php
class ValutazioniTotale extends Entity{		
	protected static $tablename = "valutazioni_totale";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"ordine_visualizzazione", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
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
                DELETE FROM ".self::$tablename."
                WHERE ".self::$tablename.".ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ".static::class." con ID='" . $this->id . "' dal DB");
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