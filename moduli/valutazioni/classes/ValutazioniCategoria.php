<?php
class ValutazioniCategoria {
    public $id;
    public $abbreviazione;
    public $descrizione;
    public $dirigenza;
    public $formula_appartenenza_personale;
    public $anno_inizio;
    public $anno_fine;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT *
                FROM valutazioni_categoria
                WHERE valutazioni_categoria.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->id_anno_budget_inizio = $db->getField("ID_anno_budget_inizio", "Number", true);
                $this->dirigenza = CoreHelper::getBooleanValueFromDB($db->getField("dirigenza", "Number", true));
                $this->formula_appartenenza_personale = $db->getField("formula_appartenenza_personale", "Text", true);
                $this->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if ($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null) {
                    $this->anno_fine = null;
                } else {
                    $this->anno_fine = $db->getField("anno_fine", "Number", true);
                }
            } else
                throw new Exception("Impossibile creare l'oggetto ValutazioniCategoria con ID = " . $id);
        }
    }

    public static function getAll($filters = array()) {
        $categorie = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }

        $sql = "
            SELECT valutazioni_categoria.*
            FROM valutazioni_categoria
            " . $where . "
            ORDER BY valutazioni_categoria.descrizione DESC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $categoria = new ValutazioniCategoria();

                $categoria->id = $db->getField("ID", "Number", true);
                $categoria->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $categoria->descrizione = $db->getField("descrizione", "Text", true);
                $categoria->dirigenza = CoreHelper::getBooleanValueFromDB($db->getField("dirigenza", "Number", true));
                $categoria->formula_appartenenza_personale = $db->getField("formula_appartenenza_personale", "Text", true);
                $categoria->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if ($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null) {
                    $categoria->anno_fine = null;
                } else {
                    $categoria->anno_fine = $db->getField("anno_fine", "Number", true);
                }
                $categorie[] = $categoria;
            } while ($db->nextRecord());
        }
        return $categorie;
    }

    public function getValutazioniCategoria() {
        $schede_valutazione = array();

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT 
                valutazioni_valutazione_periodica.*,
                valutazioni_categoria.abbreviazione,
                valutazioni_categoria.descrizione,
                valutazioni_categoria.dirigenza,
                valutazioni_categoria.anno_inizio,
                valutazioni_categoria.anno_fine
            FROM valutazioni_valutazione_periodica
                LEFT JOIN personale ON valutazioni_valutazione_periodica.matricola_valutato = personale.matricola
                INNER JOIN valutazioni_categoria ON valutazioni_categoria.ID = valutazioni_valutazione_periodica.ID_categoria	
            WHERE
                valutazioni_valutazione_periodica.ID_categoria = " . $db->toSql($this->id) . "
                AND valutazioni_valutazione_periodica.matricola_valutatore <> valutazioni_valutazione_periodica.matricola_valutato
            ORDER BY personale.cognome, personale.nome
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $scheda_valutazione = new ValutazioniValutazionePeriodica();

                $scheda_valutazione->id = $db->getField("ID", "Number", true);
                $scheda_valutazione->matricola_valutatore = $db->getField("matricola_valutatore", "Text", true);
                $scheda_valutazione->matricola_valutato = $db->getField("matricola_valutato", "Text", true);
                $scheda_valutazione->data_chiusura_autovalutazione = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_autovalutazione", "Date", true));
                $scheda_valutazione->note_valutatore = $db->getField("note_valutatore", "Text", true);
                $scheda_valutazione->data_ultimo_colloquio = CoreHelper::getDateValueFromDB($db->getField("data_ultimo_colloquio", "Date", true));
                $scheda_valutazione->data_firma_valutatore = CoreHelper::getDateValueFromDB($db->getField("data_firma_valutatore", "Date", true));
                $scheda_valutazione->note_valutato = $db->getField("note_valutato", "Text", true);
                $scheda_valutazione->data_firma_valutato = CoreHelper::getDateValueFromDB($db->getField("data_firma_valutato", "Date", true));
                $scheda_valutazione->id_periodo = $db->getField("ID_periodo", "Number", true);
                //$scheda_valutazione->categoria = $this;
                $scheda_valutazione->id_categoria = $db->getField("ID_categoria", "Number", true);

                $schede_valutazione[] = $scheda_valutazione;
            } while ($db->nextRecord());
        }
        return $schede_valutazione;
    }

    // genrazione combo box delle categorie
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
                DELETE FROM valutazioni_categoria
                WHERE valutazioni_categoria.ID = ".$db->toSql($this->id)."
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniCategoria con ID='" . $this->id . "' dal DB");
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