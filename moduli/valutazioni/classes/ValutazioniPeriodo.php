<?php
class ValutazioniPeriodo {
    public $id;
    public $descrizione;
    public $id_anno_budget;
    public $inibizione_visualizzazione_totali;
    public $inibizione_visualizzazione_ambiti_totali;
    public $inibizione_visualizzazione_data_colloquio;
    public $data_inizio;
    public $data_fine;
    public $data_apertura_compilazione;
    public $data_chiusura_autovalutazione;
    public $data_chiusura_valutatore;
    public $data_chiusura_valutato;

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                    SELECT 
                        *
                    FROM
                        valutazioni_periodo
                    WHERE
                        valutazioni_periodo.ID = " . $db->toSql($id);
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->descrizione = $db->getField("descrizione", "Text", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->inibizione_visualizzazione_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_totali", "Text", true));
                $this->inibizione_visualizzazione_ambiti_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_ambiti_totali", "Text", true));
                $this->inibizione_visualizzazione_data_colloquio = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_data_colloquio", "Text", true));                
                $this->data_inizio = $db->getField("data_inizio", "Date", true);
                $this->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                $this->data_apertura_compilazione = CoreHelper::getDateValueFromDB($db->getField("data_apertura_compilazione", "Date", true));
                $this->data_chiusura_autovalutazione = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_autovalutazione", "Date", true));
                $this->data_chiusura_valutatore = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutatore", "Date", true));
                $this->data_chiusura_valutato = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutato", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto ValutazioniPeriodo con ID = " . $id);
        }
    }

    public static function getAll($filters = array()) {
        $periodi = array();

        $db = ffDb_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }

        $sql = "
            SELECT 
                valutazioni_periodo.*
            FROM
                valutazioni_periodo
                INNER JOIN anno_budget ON valutazioni_periodo.ID_anno_budget = anno_budget.ID
            " . $where . "
            ORDER BY
                anno_budget.descrizione DESC,
                valutazioni_periodo.data_fine DESC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $periodo = new ValutazioniPeriodo();

                $periodo->id = $db->getField("ID", "Number", true);
                $periodo->descrizione = $db->getField("descrizione", "Text", true);
                $periodo->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $periodo->inibizione_visualizzazione_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_totali", "Text", true));
                $periodo->inibizione_visualizzazione_ambiti_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_ambiti_totali", "Text", true));
                $periodo->inibizione_visualizzazione_data_colloquio = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_data_colloquio", "Text", true));                
                $periodo->data_inizio = $db->getField("data_inizio", "Date", true);
                $periodo->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                $periodo->data_apertura_compilazione = CoreHelper::getDateValueFromDB($db->getField("data_apertura_compilazione", "Date", true));
                $periodo->data_chiusura_autovalutazione = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_autovalutazione", "Date", true));
                $periodo->data_chiusura_valutatore = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutatore", "Date", true));
                $periodo->data_chiusura_valutato = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutato", "Date", true));

                $periodi[] = $periodo;
            } while ($db->nextRecord());
        }
        return $periodi;
    }

    //restituisce l'id periodo_categoria per il periodo corrente e una categoria passata come parametro
    public function getIdCategoriaPeriodo(ValutazioniCategoria $categoria) {
        $db = ffDb_Sql::factory();

        $sql = "
            SELECT ID
            FROM valutazioni_periodo_categoria
            WHERE 
                valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . " AND
		valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($categoria->id) . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("ID", "Number", true);
        } else
            return false;
    }

    //restituisce array con tutte le categorie valutate nel periodo
    public function getCategoriePeriodo() {
        $categorie = array();
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT ID_categoria
            FROM valutazioni_periodo_categoria
            WHERE 
            valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $categorie[] = new ValutazioniCategoria($db->getField("ID_categoria", "Number", true));
            } while ($db->nextRecord());
        }
        return $categorie;
    }

    //restituisce la categoria valutate nel periodo, dato ID
    public function getCategoriaPeriodo($id_periodo_categoria) {
        $categoria = array();
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT *
            FROM valutazioni_periodo_categoria
            WHERE 
            valutazioni_periodo_categoria.ID = " . $db->toSql($id_periodo_categoria) . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $categoria = new ValutazioniCategoria($db->getField("ID_categoria", "Number", true));
            } while ($db->nextRecord());
        }
        return $categoria;
    }

    //restituisce array con tutt gli ambiti valutate nel periodo
    public function getAmbitiCategoriaPeriodo(ValutazioniCategoria $categoria) {
        $ambiti = array();
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT 
                valutazioni_periodo_categoria_ambito.ID_ambito
            FROM 
                valutazioni_periodo_categoria_ambito			
                INNER JOIN valutazioni_periodo_categoria ON valutazioni_periodo_categoria_ambito.ID_periodo_categoria = valutazioni_periodo_categoria.ID
                INNER JOIN valutazioni_ambito ON valutazioni_periodo_categoria_ambito.ID_ambito = valutazioni_ambito.ID
                INNER JOIN valutazioni_sezione ON valutazioni_ambito.ID_sezione = valutazioni_sezione.ID
            WHERE 
                valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
                AND valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($categoria->id) . "
            ORDER BY
                valutazioni_sezione.codice ASC,
                valutazioni_ambito.codice ASC
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ambiti[] = new ValutazioniAmbito($db->getField("ID_ambito", "Number", true));
            } while ($db->nextRecord());
        }
        return $ambiti;
    }

    //restituisce true o false dipendentemente dal fatto che sia attiva l'autovalutazione o meno per il periodo
    //se passato l'id categoria viene verificata anche la categoria specifica
    public function getAutovalutazioneAttivaPeriodo(ValutazioniCategoria $categoria = null) {
        $db = ffDb_Sql::factory();
        if ($categoria == null) {
            $categoria_sql = "";
        } else {
            $categoria_sql = " AND valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($categoria->id);
        }
        $sql = "SELECT 
					valutazioni_periodo_categoria_ambito.ID
				FROM 
					valutazioni_periodo_categoria_ambito					
					INNER JOIN valutazioni_periodo_categoria ON valutazioni_periodo_categoria_ambito.ID_periodo_categoria = valutazioni_periodo_categoria.ID
				WHERE 
					valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
					" . $categoria_sql . "
					AND valutazioni_periodo_categoria_ambito.autovalutazione_attiva = 1
				";
        $db->query($sql);
        if ($db->nextRecord()){
            return true;       
        }
        return false;
    }

    //restituisce true o false dipendentemente dal fatto che sia attiva l'autovalutazione o meno
    public function getAutovalutazioneAttivaCategoriaAmbito($id_categoria, $id_ambito) {
        $db = ffDb_Sql::factory();
        $sql = "SELECT 
					valutazioni_periodo_categoria_ambito.autovalutazione_attiva
				FROM 
					valutazioni_periodo_categoria_ambito
					INNER JOIN valutazioni_periodo_categoria ON valutazioni_periodo_categoria_ambito.ID_periodo_categoria = valutazioni_periodo_categoria.ID
				WHERE 
					valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
					AND valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($id_categoria->id) . "
					AND valutazioni_periodo_categoria_ambito.ID_ambito = " . $db->toSql($id_ambito->id) . "
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            return CoreHelper::getBooleanValueFromDB($db->getField("autovalutazione_attiva", "Number", true));
        }
        return false;
    }

    //restituisce true o false dipendentemente dal fatto che sia attiva la visualizzazione dei punteggi o meno
    public function getVisualizzazionePunteggiAttivaCategoriaAmbito(ValutazioniCategoria $categoria, ValutazioniAmbito $ambito) {
        $db = ffDb_Sql::factory();
        $sql = "SELECT 
					valutazioni_periodo_categoria_ambito.inibizione_visualizzazione_punteggi
				FROM 
					valutazioni_periodo_categoria_ambito
					INNER JOIN valutazioni_periodo_categoria ON valutazioni_periodo_categoria_ambito.ID_periodo_categoria = valutazioni_periodo_categoria.ID
				WHERE 
					valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
					AND valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($categoria->id) . "
					AND valutazioni_periodo_categoria_ambito.ID_ambito = " . $db->toSql($ambito->id) . "
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            if ($db->getField("inibizione_visualizzazione_punteggi", "Number", true) != 1)
                return true;
        }
        return false;
    }

    //restituisce true o false dipendentemente dal fatto che sia attiva la visualizzazione dei punteggi per almeno un ambito della sezione o meno
    public function getVisualizzazionePunteggiAttivaCategoriaSezione(ValutazioniCategoria $categoria, ValutazioniSezione $sezione) {
        $db = ffDb_Sql::factory();
        $sql = "SELECT 
					valutazioni_periodo_categoria_ambito.inibizione_visualizzazione_punteggi
				FROM 
					valutazioni_periodo_categoria_ambito
					INNER JOIN valutazioni_periodo_categoria ON valutazioni_periodo_categoria_ambito.ID_periodo_categoria = valutazioni_periodo_categoria.ID
                    INNER JOIN valutazioni_ambito ON valutazioni_periodo_categoria_ambito.ID_ambito = valutazioni_ambito.ID
				WHERE 
					valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
					AND valutazioni_periodo_categoria.ID_categoria = " . $db->toSql($categoria->id) . "
					AND valutazioni_ambito.ID_sezione = " . $db->toSql($sezione->id) . "
				";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                if ($db->getField("inibizione_visualizzazione_punteggi", "Number", true) !== 1) {
                    return true;
                }
            } while ($db->nextRecord());
        }
        return false;
    }

    //viene restituito un array con tutte le schede valutazioni nel periodo passato come id
    //(vengono escluse le autovalutazioni)
    //se passata la matricola valutato vengono verificate se esiste una valutazione per il periodo per il valutato
    public function getValutazioniAttivePeriodo($matricola_valutato = null) {        
        $schede_valutazione = array();

        $db = ffDb_Sql::factory();

        if ($matricola_valutato !== null) {
            $where = " AND (matricola_valutato = " . $matricola_valutato . " AND matricola_valutato <> matricola_valutatore)";
        }
        else {
            $where = "";
        }
        $sql = "
            SELECT 
                valutazioni_valutazione_periodica.*,
                valutazioni_categoria.abbreviazione,
                valutazioni_categoria.descrizione,
                valutazioni_categoria.dirigenza,
                valutazioni_categoria.anno_inizio,
                valutazioni_categoria.anno_fine
            FROM
                valutazioni_valutazione_periodica
                LEFT JOIN personale ON (valutazioni_valutazione_periodica.matricola_valutato = personale.matricola)
                INNER JOIN valutazioni_categoria ON (valutazioni_valutazione_periodica.ID_categoria = valutazioni_categoria.ID)
            WHERE valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id) . "
                AND valutazioni_valutazione_periodica.matricola_valutatore <> valutazioni_valutazione_periodica.matricola_valutato
                " . $where . "
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
                $scheda_valutazione->periodo = $this;
                $scheda_valutazione->id_categoria = $db->getField("ID_categoria", "Number", true);
                //recupero categoria
                $categoria = new ValutazioniCategoria();
                $categoria->id = $scheda_valutazione->id_categoria;
                $categoria->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $categoria->descrizione = $db->getField("descrizione", "Text", true);
                $categoria->dirigenza = CoreHelper::getBooleanValueFromDB($db->getField("dirigenza", "Number", true));
                $categoria->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if ($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null) {
                    $categoria->anno_fine = null;
                } else {
                    $categoria->anno_fine = $db->getField("anno_fine", "Number", true);
                }
                $scheda_valutazione->categoria = $categoria;

                $schede_valutazione[] = $scheda_valutazione;
            } while ($db->nextRecord());
        }
        return $schede_valutazione;
    }

    //viene restituito un array con tutto il personale senza scheda valutazione nel periodo
    public function getNonValutatiPeriodo() {
        $personale_non_valutato = array();
        $anno = new ValutazioniAnnoBudget($this->id_anno_budget);

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT
                    personale.matricola,
                    personale.ID,
                    personale.cognome,
                    personale.nome
            FROM
                    personale
            LEFT JOIN 
                    (SELECT * FROM valutazioni_valutazione_periodica WHERE valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id) . ")
                            AS valutazioni_valutazione_periodica 
                    ON valutazioni_valutazione_periodica.matricola_valutato = personale.matricola
            WHERE
                    valutazioni_valutazione_periodica.ID is null
            ORDER BY
                    personale.cognome, personale.nome
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $personale = Personale::factoryFromMatricola($db->getField("matricola", "Text", true));
                if ($personale->isAttivoAnno($anno)) {
                    $personale_non_valutato[] = $personale;
                }
            } while ($db->nextRecord());
        }
        return $personale_non_valutato;
    }

    //viene restituito un array con tutte le schede valutazioni nel periodo per le quali un dipendente risulta valutatore
    public function getValutazioniValutatorePeriodo($matricola) {
        $schede_valutazione = array();

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT 
                    valutazioni_valutazione_periodica.ID
            FROM
                    valutazioni_valutazione_periodica
                    INNER JOIN personale ON valutazioni_valutazione_periodica.matricola_valutato = personale.matricola
            WHERE
                    valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id) . "
                    AND valutazioni_valutazione_periodica.matricola_valutatore = " . $db->toSql($matricola) . "
                    AND valutazioni_valutazione_periodica.matricola_valutatore <> valutazioni_valutazione_periodica.matricola_valutato                    
            ORDER BY
                    valutazioni_valutazione_periodica.ID_categoria DESC, personale.cognome, personale.nome
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do
                $schede_valutazione[] = $db->getField("ID", "Number", true);
            while ($db->nextRecord());
        }
        return $schede_valutazione;
    }

    //viene restituito un array con tutte le schede valutazioni nel periodo per le quali un dipendente risulta valutato
    public function getValutazioniValutatoPeriodo($matricola) {
        $schede_valutazione = array();

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT *
            FROM
                valutazioni_valutazione_periodica
            WHERE
                valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id) . "
                AND valutazioni_valutazione_periodica.matricola_valutato = " . $db->toSql($matricola) . "
                AND valutazioni_valutazione_periodica.matricola_valutatore <> valutazioni_valutazione_periodica.matricola_valutato
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do
                $schede_valutazione[] = $db->getField("ID", "Number", true);
            while ($db->nextRecord());
        }
        return $schede_valutazione;
    }

    public function canDelete() {
        //Controllo se l'entità è cancellabile
        $valutazioni = $this->getValutazioniAttivePeriodo();

        if (count($valutazioni) != 0) {
            return false;
        }

        // Controllo se eventuali entità periodo_categoria associate sono cancellabili
        $periodo_categorie = ValutazioniPeriodoCategoria::getAll(array("ID_periodo" => $this->id));

        foreach ($periodo_categorie as $periodo_categoria) {
            //Se entità associata è false
            if (!$periodo_categoria->canDelete()) {
                return false;
            }
        }
        return true;
    }
    
    public function delete($propaga = true) {
        //Controllo se l'istanza può essere cancellata
        if ($this->canDelete()) {
            //Se propagazione, cancello le istanze collegate (solo quelle di primo livello)
            if ($propaga) {
                $periodo_categorie = ValutazioniPeriodoCategoria::getAll(array("ID_periodo" => $this->id));
                foreach ($periodo_categorie as $periodo_categoria) {
                    if (!$periodo_categoria->delete()) {
                        return false;
                    }
                }
            }
            $db = ffDb_Sql::factory();
            $sql = "
                DELETE FROM valutazioni_periodo
                WHERE valutazioni_periodo.ID = " . $db->toSql($this->id) . "
            ";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile eliminare l'oggetto ValutazioniPeriodo con ID='" . $this->id . "' dal DB");
            }
            return true;
        }
        return false;
    }
    
    //restituisce array di obj ValutazioniTotale di una categoria per il periodo in esame
    public function getTotaliPeriodo(ValutazioniCategoria $categoria) {
        $totali = array();
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT DISTINCT valutazioni_totale.*
            FROM valutazioni_totale_categoria
                INNER JOIN valutazioni_periodo_categoria ON (
                    valutazioni_totale_categoria.ID_categoria = valutazioni_periodo_categoria.ID_categoria
                    AND valutazioni_periodo_categoria.ID_periodo = " . $db->toSql($this->id) . "
                )
                INNER JOIN valutazioni_totale ON (
                    valutazioni_totale_categoria.ID_totale = valutazioni_totale.ID
                )
            WHERE valutazioni_totale_categoria.ID_categoria = " . $db->toSql($categoria->id) . "
            ORDER BY valutazioni_totale_categoria.ID_totale
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
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
            } while ($db->nextRecord());
        }
        return $totali;
    }

    function existsValutazioniAttivePeriodoMatricola($matricola_personale) {
        return !empty($this->getValutazioniAttivePeriodo($matricola_personale));
    }
}