<?php
class ValutazioniAmbito extends Entity{
    protected static $tablename = "valutazioni_ambito";
    
    public static $metodi_valutazione = array(
        array(
            "ID" => 1,
            "nome_campo" => "manAdmin_",
            "descrizione" => "Ins. Manuale Admin",
        ),
        array(
            "ID" => 2,
            "nome_campo" => "item_",
            "descrizione" => "Items",
        ),
        array(
            "ID" => 3,
            "nome_campo" => "obiettivi_",
            "descrizione" => "Obiettivi",
        ),
    );

    public function getPesoAmbitoCategoriaAnno(ValutazioniCategoria $categoria, ValutazioniAnnoBudget $anno) {
        $db = ffDb_Sql::factory();

        $sql = "
            SELECT 
                valutazioni_ambito_categoria_anno.peso
            FROM
                valutazioni_ambito_categoria_anno
            WHERE
                valutazioni_ambito_categoria_anno.ID_ambito = " . $db->toSql($this->id) . " AND
                valutazioni_ambito_categoria_anno.ID_categoria = " . $db->toSql($categoria->id) . " AND
                valutazioni_ambito_categoria_anno.ID_anno_budget = " . $db->toSql($anno->id) . "				
        ";
        $db->query($sql);
        if ($db->nextRecord())
            return (int) $db->getField("peso", "Number", true);
        else
            return 0;
    }

    public function setPesoAnno(ValutazioniAnnoBudget $anno, ValutazioniCategoria $categoria, $peso, $metodo, $action) {
        $db = ffDb_Sql::factory();
        switch ($action) {
            case "insert":
                $sql = "
                    INSERT INTO valutazioni_ambito_categoria_anno(
                        ID_anno_budget, 
                        ID_ambito, 
                        ID_categoria, 
                        peso,
                        metodo_valutazione
                    )
                    VALUES (
                        " . $db->toSql($anno->id) . ", 
                        " . $db->toSql($this->id) . ", 
                        " . $db->toSql($categoria->id) . ", 
                        " . $db->toSql($peso) . ",
                        " . $db->toSql($metodo) . "
                    )
                ";
                break;
            case "update":
                $sql = "
                    UPDATE valutazioni_ambito_categoria_anno
                    SET peso = " . $db->toSql($peso) . ",
                        metodo_valutazione = " . $db->toSql($metodo) . "
                    WHERE 
                        ID_ambito = " . $db->toSql($this->id) . " AND
                        ID_categoria = " . $db->toSql($categoria->id) . " AND
                        ID_anno_budget = " . $db->toSql($anno->id) . "
                ";
                break;
            case "delete":
                $sql = "
                    DELETE FROM valutazioni_ambito_categoria_anno
                    WHERE 
                        ID_ambito = " . $db->toSql($this->id) . " AND
                        ID_categoria = " . $db->toSql($categoria->id) . " AND
                        ID_anno_budget = " . $db->toSql($anno->id) . "
                ";
                break;
            default:
                ffErrorHandler::raise("Action inesistente");
                break;
        }

        //$db->query($sql);
        if ($db->execute($sql)) {
            return true;
        }
        ffErrorHandler::raise("Impossibile eseguire operazioni su peso per categoria " . $categoria->abbreviazione . " associata alla sezione " . $this->codice);
    }

    //ritorna true se l'ambito è valutato per la categoria nell'anno
    public function isValutatoCategoriaAnno(ValutazioniCategoria $categoria, ValutazioniAnnoBudget $anno) {
        if ($this->getPesoAmbitoCategoriaAnno($categoria, $anno) > 0)
            return true;
        else
            return false;
    }

    //ritorna l'id del metodo di valutazione
    public function getMetodoValutazioneAmbitoCategoriaAnno(ValutazioniCategoria $categoria, ValutazioniAnnoBudget $anno) {
        $db = ffDb_Sql::factory();

        $sql = "
            SELECT 
                valutazioni_ambito_categoria_anno.metodo_valutazione
            FROM
                valutazioni_ambito_categoria_anno
            WHERE
                valutazioni_ambito_categoria_anno.ID_ambito = " . $db->toSql($this->id) . " AND
                valutazioni_ambito_categoria_anno.ID_categoria = " . $db->toSql($categoria->id) . " AND
                valutazioni_ambito_categoria_anno.ID_anno_budget = " . $db->toSql($anno->id)
        ;
        $db->query($sql);
        if ($db->nextRecord())
            return (int) $db->getField("metodo_valutazione", "Number", true);
        else
            return 0;
    }
    
    public function isAmbitoDaAggiornare(ValutazioniValutazionePeriodica $valutazione) {
        $db = ffDB_Sql::factory();
        
        $sql = "
            SELECT valutazioni_ambito_precalcolato.time_aggiornamento
            FROM valutazioni_ambito_precalcolato
            WHERE valutazioni_ambito_precalcolato.ID_ambito = ".$db->toSql($this->id)."
                AND valutazioni_ambito_precalcolato.ID_valutazione = ".$db->toSql($valutazione->id)
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

    public function canDelete() {
        return $this->checkOrDeleteRelations("canDelete");
    }

    public function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga) {
                if($this->checkOrDeleteRelations("delete")) {
                    $totali_ambito = ValutazioniTotaleAmbito::getAll(array("ID_ambito" => $this->id));
                    foreach($totali_ambito as $totale_ambito) {
                        if(!$totale_ambito->delete()) {
                            return false;
                        }
                    }
                } else {
                    return false;
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

    function checkOrDeleteRelations($function) {
        $conditions = array("ID_ambito" => $this->id);
        $ambito_categorie_anni = ValutazioniAmbitoCategoriaAnno::getAll($conditions);
        foreach($ambito_categorie_anni as $ambito_categoria_anno) {
            if(!$ambito_categoria_anno->$function(true)) {
                return false;
            }
        }

        $periodi_categorie_ambito = ValutazioniPeriodoCategoriaAmbito::getAll($conditions);
        foreach($periodi_categorie_ambito as $periodo_categoria_ambito) {
            if(!$periodo_categoria_ambito->$function()) {
                return false;
            }
        }

        $aree_item_ambito = ValutazioniAreaItem::getAll($conditions);
        foreach($aree_item_ambito as $area_item_ambito) {
            if(!$area_item_ambito->$function()) {
                return false;
            }
        }
        return true;
    }

    /*
     * L'inserimento di un'istanza ValutazioniPeriodoCategoriaAmbito è subordinato alla presenza
     * di una relativa istanza ValutazioniAmbitoCategoriaAnno.
     */
    public function hasRelationsAfterAnnoFine($anno_fine) {
        return ValutazioniAmbitoCategoriaAnno::relationsAfterAnnoFine("ID_ambito", $this->id, $anno_fine);
    }
}