<?php
class ValutazioniCategoria extends Entity{
    protected static $tablename = "valutazioni_categoria";

    public static function getAll($where=array(), $order=array(array("fieldname"=>"descrizione", "direction"=>"DESC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }

    // generazione combo box delle categorie
    public static function getQualifiche($categorie = array()) {
        if(empty($categorie)) {
            $categorie = self::getAll();
        }

        $id_dirigenze = array();
        $id_comparti = array();
        foreach($categorie as $valutazione_categoria) {
            if($valutazione_categoria->dirigenza) {
                $id_dirigenze[] = $valutazione_categoria->id;
            } else {
                $id_comparti[] = $valutazione_categoria->id;
            }
        }

        $categorie_select = array(
            array(
                "id_categorie" => implode("_", $id_dirigenze),
                "descrizione" => "Dirigenza"
            ),
            array(
                "id_categorie" => implode("_", $id_comparti),
                "descrizione" => "Comparto"
            )
        );
        return $categorie_select;
    }
    
    public function canDelete() {
        $valutazioniPeriodicheCategoria =
            ValutazioniValutazionePeriodica::getSchedeCategoriaPeriodo($this->id);

        if(count($valutazioniPeriodicheCategoria) > 0) {
            return false;
        }

        return $this->checkOrDeleteRelations("canDelete");
    }

    public function delete($propaga = true) {
        if($this->canDelete()) {
            if($propaga) {
                if($this->checkOrDeleteRelations("delete")) {
                    //Se posso eliminare la categoria, posso eliminare anche il totale categoria
                    $totali_categoria = ValutazioniTotaleCategoria::getAll(array("ID_categoria" => $this->id));
                    foreach($totali_categoria as $totale_categoria) {
                        if(!$totale_categoria->delete()) {
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

    private function checkOrDeleteRelations($function) {
        $conditions = array("ID_categoria" => $this->id);
        $sezioni_categoria_anni = ValutazioniSezionePesoAnno::getAll($conditions);
        foreach($sezioni_categoria_anni as $sezione_categoria_anno) {
            if(!$sezione_categoria_anno->$function()) {
                return false;
            }
        }

        $ambiti_categoria_anni = ValutazioniAmbitoCategoriaAnno::getAll($conditions);
        $ignoreAssociationPeriodiAnno = true;
        foreach($ambiti_categoria_anni as $ambito_categoria_anno) {
            if(!$ambito_categoria_anno->$function($ignoreAssociationPeriodiAnno)) {
                return false;
            }
        }

        $periodi_categoria = ValutazioniPeriodoCategoria::getAll($conditions);
        foreach($periodi_categoria as $periodo_categoria) {
            if(!$periodo_categoria->$function()) {
                return false;
            }
        }

        $items_categoria = ValutazioniItemCategoria::getAll($conditions);
        foreach($items_categoria as $item_categoria) {
            if(!$item_categoria->$function()) {
                return false;
            }
        }

        foreach($this->getRegole() as $regola_categoria) {
            if(!$regola_categoria->$function()) {
                return false;
            }
        }

        return true;
    }

    //recupero delle regole associate alla categoria
    public function getRegole() {
        return ValutazioniRegolaCategoria::getAll(array("ID_categoria"=>$this->id));
    }
    
    //viene verificato se il personale è incluso nella categoria
    public function verificaAppartenenzaPersonale (ValutazioniPersonale $personale) {
        if (!strlen($this->formula_appartenenza_personale)) {
            return false;
        }
        $formula_verifica_categoria = $this->formula_appartenenza_personale;
        //sostituzione dei parametri della formula   
        $regole = array_reverse($this->getRegole());
        foreach($regole as $key => $regola_categoria) {            
            //viene recuperata la formula per la categoria                
            //vengono sostituite le regole in ordine inverso per utilizzare parametri con codice incrementali > 9 senza errori
            $n_incrementale_regola = count($regole)-$key;
            $regola_formula = VALUTAZIONI_IDENTIFICATORE_PARAMETRO_FORMULA.$n_incrementale_regola;             
            $risultato_verifica_regola = (int)$regola_categoria->verificaRegola($personale);            
            $formula_verifica_categoria = str_replace($regola_formula, $risultato_verifica_regola, $formula_verifica_categoria);
        }          
        $evaluator = new Evaluator($formula_verifica_categoria);        
        $risultato = $evaluator->evaluate();
        //verifica sulla formula e sul risultato, se sono presenti identificatori di parametri significa che non tutte le regole necessario al calcolo esistono                             
        if (stripos($formula_verifica_categoria, VALUTAZIONI_IDENTIFICATORE_PARAMETRO_FORMULA) !== false || is_nan($risultato) || is_infinite($risultato)) {
            return null;
        }  
        return $risultato;                
    }

    /*
     * L'inserimento di un'istanza ValutazioniPeriodoCategoriaAmbito è subordinato alla presenza
     * di una relativa istanza ValutazioniAmbitoCategoriaAnno.
     */
    public function hasRelationsAfterAnnoFine($anno_fine) {
        return
            ValutazioniAmbitoCategoriaAnno::relationsAfterAnnoFine("ID_categoria", $this->id, $anno_fine)
                ||
            ValutazioniSezionePesoAnno::relationsAfterAnnoFine("ID_categoria", $this->id, $anno_fine);
    }
}