<?php
class ValutazioniSezione extends Entity{
    protected static $tablename = "valutazioni_sezione";

    //viene restituito un array con gli ambiti associati alla sezione
    public function getAmbitiAssociati() {
        $ambiti = array();
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					ID
				FROM
					valutazioni_ambito
				WHERE
					valutazioni_ambito.ID_sezione = " . $db->toSql($this->id)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ambiti[] = new ValutazioniAmbito($db->getField("ID", "Number", true));
            } while ($db->nextRecord());
        }
        return $ambiti;
    }

    //viene restiutito il peso della sezione in un anno di valutazione
    public function getPesoAnno(ValutazioniAnnoBudget $anno, ValutazioniCategoria $categoria) {
        $db = ffDb_Sql::factory();
        $sql = "
				SELECT 
					valutazioni_sezione_peso_anno.peso
				FROM
					valutazioni_sezione_peso_anno
				WHERE
					valutazioni_sezione_peso_anno.ID_sezione = " . $db->toSql($this->id) . "
					AND valutazioni_sezione_peso_anno.ID_anno_budget = " . $db->toSql($anno->id) . "
                    AND valutazioni_sezione_peso_anno.ID_categoria = " . $db->toSql($categoria->id)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("peso", "Number", true);
        }        
        throw new Exception("Peso sezione '" . $this->codice . "' non definito per l'anno " . $anno->descrizione);
    }  

    public function setPesoAnno(ValutazioniAnnoBudget $anno, ValutazioniCategoria $categoria, $peso, $action) {
        $db = ffDb_Sql::factory();

        switch($action) {
            case "insert":
                $sql = "
                    INSERT INTO valutazioni_sezione_peso_anno(
                        ID_anno_budget, 
                        ID_sezione, 
                        ID_categoria, 
                        peso
                    )
                    VALUES (
                        " . $db->toSql($anno->id) . ", 
                        " . $db->toSql($this->id) . ", 
                        " . $db->toSql($categoria->id) . ", 
                        " . $db->toSql($peso) . "
                    )
                ";
                break;
            case "update":
                $sql = "
                    UPDATE valutazioni_sezione_peso_anno
                    SET peso = " . $db->toSql($peso) . "
                    WHERE 
                        ID_sezione = " . $db->toSql($this->id) . " AND
                        ID_categoria = " . $db->toSql($categoria->id) . " AND
                        ID_anno_budget = " . $db->toSql($anno->id) . "
                ";
                break;
            case "delete":
                $sql = "
                    DELETE FROM valutazioni_sezione_peso_anno
                    WHERE 
                        ID_sezione = " . $db->toSql($this->id) . " AND
                        ID_categoria = " . $db->toSql($categoria->id) . " AND
                        ID_anno_budget = " . $db->toSql($anno->id) . "
                ";
                break;
            default:
                ffErrorHandler::raise("Action inesistente");
                break;
        }
        
        if ($db->execute($sql)) {
            return true;
        }
        ffErrorHandler::raise("Impossibile eseguire operazioni su peso per categoria " . $categoria->abbreviazione . " associata alla sezione " . $this->codice);
    }

    public function canDelete() {
        return $this->checkOrDeleteRelations("canDelete");
    }

    public function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga && !$this->checkOrDeleteRelations("delete")) {
                return false;
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

    private function checkOrDeleteRelations($function) {
        $ambiti = ValutazioniAmbito::getAll(array('ID_sezione' => $this->id));
        foreach($ambiti as $ambito) {
            if(!$ambito->$function()) {
                return false;
            }
        }

        $sezione_pesi_anni = ValutazioniSezionePesoAnno::getAll(array("ID_sezione" => $this->id));
        foreach($sezione_pesi_anni as $sezione_peso_anno) {
            if(!$sezione_peso_anno->$function()) {
                return false;
            }
        }
        return true;
    }

}