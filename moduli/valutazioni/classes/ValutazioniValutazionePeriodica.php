<?php
class ValutazioniValutazionePeriodica {
    public $id;
    public $matricola_valutatore;
    public $matricola_valutato;
    public $data_chiusura_autovalutazione;
    public $note_valutatore;
    public $data_ultimo_colloquio;
    public $data_firma_valutatore;
    public $note_valutato;
    public $data_firma_valutato;
    public $id_periodo;
    public $periodo;
    public $id_categoria;
    public $categoria;
    public static $stati_valutazione = array(
        array(
            "ID" => 0,
            "descrizione" => "Anomalia",
        ),
        array(
            "ID" => 1,
            "descrizione" => "Non ancora compilabile",
        ),
        array(
            "ID" => 3,
            "descrizione" => "Attesa compilazione del valutatore",
        ),
        array(
            "ID" => 4,
            "descrizione" => "Attesa presa visione del valutato",
        ),
        array(
            "ID" => 5,
            "descrizione" => "Completa",
        ),
    );

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    valutazioni_valutazione_periodica.ID,
                    valutazioni_valutazione_periodica.matricola_valutatore,
                    valutazioni_valutazione_periodica.matricola_valutato,
                    valutazioni_valutazione_periodica.data_chiusura_autovalutazione,
                    valutazioni_valutazione_periodica.note_valutatore,
                    valutazioni_valutazione_periodica.data_ultimo_colloquio,
                    valutazioni_valutazione_periodica.data_firma_valutatore,
                    valutazioni_valutazione_periodica.note_valutato,
                    valutazioni_valutazione_periodica.data_firma_valutato,
                    valutazioni_valutazione_periodica.ID_periodo,
                    valutazioni_valutazione_periodica.ID_categoria,
                    valutazioni_periodo.descrizione,
                    valutazioni_periodo.ID_anno_budget,
                    valutazioni_periodo.inibizione_visualizzazione_totali,
                    valutazioni_periodo.inibizione_visualizzazione_ambiti_totali,
                    valutazioni_periodo.inibizione_visualizzazione_data_colloquio,
                    valutazioni_periodo.data_inizio,
                    valutazioni_periodo.data_fine,
                    valutazioni_periodo.data_apertura_compilazione,
                    valutazioni_periodo.data_chiusura_autovalutazione AS data_chiusura_autovalutazione_periodo,
                    valutazioni_periodo.data_chiusura_valutatore,
                    valutazioni_periodo.data_chiusura_valutato,
                    valutazioni_categoria.abbreviazione,
                    valutazioni_categoria.descrizione,
                    valutazioni_categoria.dirigenza,
                    valutazioni_categoria.anno_inizio,
                    valutazioni_categoria.anno_fine
                FROM valutazioni_valutazione_periodica
                    INNER JOIN valutazioni_periodo ON valutazioni_valutazione_periodica.ID_periodo = valutazioni_periodo.ID
                    INNER JOIN valutazioni_categoria ON valutazioni_valutazione_periodica.ID_categoria = valutazioni_categoria.ID
                WHERE
                    valutazioni_valutazione_periodica.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->matricola_valutatore = $db->getField("matricola_valutatore", "Text", true);
                $this->matricola_valutato = $db->getField("matricola_valutato", "Text", true);
                $this->data_chiusura_autovalutazione = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_autovalutazione", "Date", true));
                $this->note_valutatore = $db->getField("note_valutatore", "Text", true);
                $this->data_ultimo_colloquio = CoreHelper::getDateValueFromDB($db->getField("data_ultimo_colloquio", "Date", true));
                $this->data_firma_valutatore = CoreHelper::getDateValueFromDB($db->getField("data_firma_valutatore", "Date", true));
                $this->note_valutato = $db->getField("note_valutato", "Text", true);
                $this->data_firma_valutato = CoreHelper::getDateValueFromDB($db->getField("data_firma_valutato", "Date", true));
                $this->id_periodo = $db->getField("ID_periodo", "Number", true);
                //recupero periodo
                $periodo = new ValutazioniPeriodo();
                $periodo->id = $this->id_periodo;
                $periodo->descrizione = $db->getField("descrizione", "Text", true);
                $periodo->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $periodo->inibizione_visualizzazione_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_totali", "Text", true));
                $periodo->inibizione_visualizzazione_ambiti_totali = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_ambiti_totali", "Text", true));
                $periodo->inibizione_visualizzazione_data_colloquio = CoreHelper::getBooleanValueFromDB($db->getField("inibizione_visualizzazione_data_colloquio", "Text", true));                
                $periodo->data_inizio = $db->getField("data_inizio", "Date", true);
                $periodo->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                $periodo->data_apertura_compilazione = CoreHelper::getDateValueFromDB($db->getField("data_apertura_compilazione", "Date", true));
                $periodo->data_chiusura_autovalutazione = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_autovalutazione_periodo", "Date", true));
                $periodo->data_chiusura_valutatore = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutatore", "Date", true));
                $periodo->data_chiusura_valutato = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_valutato", "Date", true));
                $this->periodo = $periodo;
                $this->id_categoria = $db->getField("ID_categoria", "Number", true);
                //recupero categoria
                $categoria = new ValutazioniCategoria();
                $categoria->id = $this->id_categoria;
                $categoria->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $categoria->descrizione = $db->getField("descrizione", "Text", true);
                $categoria->dirigenza = CoreHelper::getBooleanValueFromDB($db->getField("dirigenza", "Number", true));
                $categoria->anno_inizio = $db->getField("anno_inizio", "Number", true);
                if ($db->getField("anno_fine", "Number", true) == 0 || $db->getField("anno_fine", "Number", true) == null) {
                    $categoria->anno_fine = null;
                } else {
                    $categoria->anno_fine = $db->getField("anno_fine", "Number", true);
                }
                $this->categoria = $categoria;
            } else
                throw new Exception("Impossibile creare l'oggetto ValutazioniValutazionePeriodica con ID = " . $id);
        }
    }

    //creazione su db
    public function save() {
        $db = ffDB_Sql::factory();
        //insert
        if ($this->id == null) {
            $sql = "
                INSERT INTO valutazioni_valutazione_periodica ( 
                    matricola_valutatore,
                    matricola_valutato,
                    data_chiusura_autovalutazione,
                    note_valutatore,
                    data_ultimo_colloquio,
                    data_firma_valutatore,
                    note_valutato,
                    data_firma_valutato,
                    ID_periodo,
                    ID_categoria
                )
                VALUES ("
                . $db->toSql($this->matricola_valutatore)
                . "," . $db->toSql($this->matricola_valutato)
                . "," . ($this->data_chiusura_autovalutazione == null ? "NULL" : $db->toSql($this->data_chiusura_autovalutazione))
                . "," . ($this->note_valutatore == null ? "NULL" : $db->toSql($this->note_valutatore))
                . "," . ($this->data_ultimo_colloquio == null ? "NULL" : $db->toSql($this->data_ultimo_colloquio))
                . "," . ($this->data_firma_valutatore == null ? "NULL" : $db->toSql($this->data_firma_valutatore))
                . "," . ($this->note_valutato == null ? "NULL" : $db->toSql($this->note_valutato))
                . "," . ($this->data_firma_valutato == null ? "NULL" : $db->toSql($this->data_firma_valutato))
                . "," . $db->toSql($this->id_periodo)
                . "," . $db->toSql($this->id_categoria)
                . ");";
            if (!$db->execute($sql))
                throw new Exception("Impossibile creare l'oggetto ValutazioniValutazionePeriodica nel DB");
        }
        //update
        else {
            $sql = "
                UPDATE valutazioni_valutazione_periodica
                SET
                    matricola_valutatore=" . $db->toSql($this->matricola_valutatore) . ",
                    matricola_valutato=" . $db->toSql($this->matricola_valutato) . ",
                    data_chiusura_autovalutazione=" . $db->toSql($this->data_chiusura_autovalutazione) . ",
                    note_valutatore=" . $db->toSql($this->note_valutatore) . ",
                    data_ultimo_colloquio=" . $db->toSql($this->data_ultimo_colloquio) . ",
                    data_firma_valutatore=" . $db->toSql($this->data_firma_valutatore) . ",
                    note_valutato=" . $db->toSql($this->note_valutato) . ",
                    data_firma_valutato=" . $db->toSql($this->data_firma_valutato) . ",
                    ID_periodo=" . $db->toSql($this->id_periodo) . ",
                    ID_categoria=" . $db->toSql($this->id_categoria) . "
                WHERE
                    ID = " . $db->toSql($this->id) . "
            ";
            if (!$db->execute($sql))
                throw new Exception("Impossibile aggiornare l'oggetto ValutazioniValutazionePeriodica con ID='" . $this->id . "' nel DB");
        }
    }

    //funzione che restituisce lo stato d'avanzamento della valutazione in base alla valorizzazione dei campi
    public function getIdStatoAvanzamento() {
        $db = ffDb_Sql::factory();

        //la variabile stato viene inizializzta con stato = anomalia
        $stato = 0;
        //autovalutazione		
        $periodo = $this->periodo;
        $id_periodo_categoria = $periodo->getIdCategoriaPeriodo($this->categoria);
        if ($id_periodo_categoria !== false) {
            //firma valutatore
            if ($this->data_firma_valutatore !== null && $this->data_firma_valutatore !== '0000-00-00')
                $firma_valutatore = true;
            else
                $firma_valutatore = false;
            //firma valutato
            if ($this->data_firma_valutato !== null && $this->data_firma_valutato !== '0000-00-00')
                $firma_valutato = true;
            else
                $firma_valutato = false;

            //verifica dello stato avanzamento ed eventuali anomalie
            if ($firma_valutato && $firma_valutatore)
                $stato = 5;
            elseif ($firma_valutatore)
                $stato = 4;
            //prima condizione non dovrebbe mai verificarsi (data_apertura_compilazione obbligatoria)
            elseif ($periodo->data_apertura_compilazione !== null && (strtotime(date("Y-m-d")) >= strtotime($periodo->data_apertura_compilazione)))
            //$stato = 2;						
                $stato = 3;
            else
                $stato = 1;
        }
        return $stato;
    }

    //restituisce true se la valutazione è autovalutazione
    public function isAutovalutazione() {
        if ($this->matricola_valutatore == $this->matricola_valutato)
            return true;
        else
            return false;
    }

    //restituisce l'autovalutazione collegata alla valutazione nel caso sia presente
    //nel caso la valutazione sia autovalutazione viene ritornato l'id stesso
    public function getAutovalutazioneCollegata() {
        if ($this->isAutovalutazione())
            return $this->id;
        else {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    *
                FROM
                    valutazioni_valutazione_periodica
                WHERE
                    valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id_periodo) . "
                    AND valutazioni_valutazione_periodica.matricola_valutato = " . $db->toSql($this->matricola_valutato) . "
                    AND valutazioni_valutazione_periodica.matricola_valutatore = valutazioni_valutazione_periodica.matricola_valutato
            ";
            $db->query($sql);
            if ($db->nextRecord())
                return new ValutazioniValutazionePeriodica($db->getField("ID", "Number", true));
            else
                return false;
        }
    }

    //restituisce la valutazione collegata all'autovalutazione
    //nel caso la valutazione non sia autovalutazione viene ritornato false
    public function getValutazioneCollegata() {
        if (!$this->isAutovalutazione()) {
            return false;
        } else {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT 
                    *
                FROM
                    valutazioni_valutazione_periodica
                WHERE
                    valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($this->id_periodo) . "
                    AND valutazioni_valutazione_periodica.matricola_valutato = " . $db->toSql($this->matricola_valutato) . "
                    AND valutazioni_valutazione_periodica.matricola_valutatore <> valutazioni_valutazione_periodica.matricola_valutato
            ";
            $db->query($sql);
            if ($db->nextRecord())
                return new ValutazioniValutazionePeriodica($db->getField("ID", "Number", true));
            else
                return false;
        }
    }

    //viene restituito un array con i privilegi dell'utente sulla scheda di valutazione in base allo stato in cui questa si trova
    public function getPrivilegiPersonale($matricola) {
        //viene inizializzato l'array con tutti i privilegi a false
        $privilegi = array(
            "view_autovalutazione" => false,
            "edit_autovalutazione" => false,
            "view_valutazione" => false,
            "edit_valutatore" => false,
            "edit_valutato" => false,
        );

        $stato_avanzamento = $this->getIdStatoAvanzamento();

        //autovalutazione
        if ($this->isAutovalutazione()) {
            $periodo = $this->periodo;
            //viene verificato che almeno per un ambito sia attiva l'autovalutazione per capire se l'autovalutazione è attiva per il periodo
            if ($periodo->getAutovalutazioneAttivaPeriodo($this->categoria))
                $autovalutazioni_attive = true;
            else
                $autovalutazioni_attive = false;

            if ($autovalutazioni_attive == false || ($autovalutazioni_attive == true && ($this->data_chiusura_autovalutazione !== null && $this->data_chiusura_autovalutazione !== '0000-00-00')))
                $autovalutazione_completata = true;
            else
                $autovalutazione_completata = false;

            //il valutato potrà sempre visualizzare la scheda di autovalutazione e modificarla solo in fase di attesa di compilazione						
            if ($this->matricola_valutatore == $matricola) {
                $privilegi["view_autovalutazione"] = true;
                //1 Attesa compilazione autovalutazione
                //se la valutazione è un'autovalutazione e l'utente è il valutatore e l'autovalutazione non è chiusa
                if ($this->isAutovalutazione() && $stato_avanzamento > 1 && $autovalutazione_completata == false) {
                    $privilegi["edit_autovalutazione"] = true;
                }
            }
            //in tutti gli altri casi viene recuperato il valutatore tramite la valutazione collegata che potrà sempre visualizzare l'autovalutazione
            //nel caso in cui sia stata compilata
            else {
                $val_collegata = $this->getValutazioneCollegata();
                if ($stato_avanzamento > 1 && $val_collegata->matricola_valutatore == $matricola && $autovalutazione_completata == true) {
                    $privilegi["view_autovalutazione"] = true;
                }
            }
        }
        //nel caso in cui la valutazione non sia autovalutazione
        else {
            if ($this->matricola_valutatore == $matricola) {
                //il valutatore potrà sempre vedere la valutazione (lo stato 1 viene utilizzato solo per l'autovalutazione)
                if ($stato_avanzamento >= 3) {
                    $privilegi["view_valutazione"] = true;
                    //potrà inoltre modificarla nel caso ci si trovi nel caso dell'attesa della compilazione da parte del valutatore
                    if ($stato_avanzamento == 3) {
                        $privilegi["edit_valutatore"] = true;
                    }
                }
            } else if ($this->matricola_valutato == $matricola) {
                //il valutato potrà sempre vedere la valutazione una volta approvata dal valutatore
                if ($stato_avanzamento >= 4) {
                    $privilegi["view_valutazione"] = true;
                    //potrà inoltre modificarla nel caso ci si trovi nel caso dell'attesa della compilazione da parte del valutato
                    if ($stato_avanzamento == 4) {
                        $privilegi["edit_valutato"] = true;
                    }
                }
            }
        }

        return $privilegi;
    }

    //viene restituito un array con gli eventuali items della valutazione considerata per l'ambito
    //se passata area item viene filtrato per questa
    public function getItemsCategoriaAmbitoValutazione(ValutazioniAmbito $ambito, ValutazioniAreaItem $area = null) {
        $items = array();
        $periodo = $this->periodo;
        $anno_valutazione = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        $categoria = $this->categoria;
        //vengono visualizzati solamente gli item attivi per la categoria della scheda di valutazione per l'ambito considerato
        foreach ($anno_valutazione->getItemsAnno($ambito, $categoria, $area) as $item_anno) {
            $items[] = $item_anno;
        }
        return $items;
    }

    //viene recuperato il punteggio di un ambito di una determinata valutazione
    public function getPunteggioAmbito($ambito) {
        $db = ffDb_Sql::factory();
        //viene verificato che esista già una valutazione per l'ambito
        $sql = "
            SELECT valutazioni_valutazione_ambito.punteggio
            FROM valutazioni_valutazione_ambito
            WHERE valutazioni_valutazione_ambito.ID_valutazione_periodica = " . $db->toSql($this->id) . "
                AND valutazioni_valutazione_ambito.ID_ambito = " . $db->toSql($ambito->id)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            return $db->getField("punteggio", "Number", true);
        } else {
            return false;
        }
    }

    //salvataggio della valutazione dell'ambito. Ritorna true o false a seconda che il salvataggio sia andato a buon fine o meno
    public function salvaPunteggioAmbito(ValutazioniAmbito $ambito, $punteggio) {
        $user = LoggedUser::getInstance();
        $privilegi_utente = $this->getPrivilegiPersonale($user->matricola_utente_selezionato);
        if (
            $user->hasPrivilege("valutazioni_admin") == true ||
            ($this->isAutovalutazione() && $privilegi_utente["edit_autovalutazione"] == true) ||
            (!$this->isAutovalutazione() && $privilegi_utente["edit_valutatore"] == true)
        ) {
            $db = ffDb_Sql::factory();
            //viene verificato che esista già una valutazione per l'ambito
            $sql = "
                SELECT valutazioni_valutazione_ambito.ID
		FROM valutazioni_valutazione_ambito
		WHERE valutazioni_valutazione_ambito.ID_valutazione_periodica = " . $db->toSql($this->id) . "
                    AND valutazioni_valutazione_ambito.ID_ambito = " . $db->toSql($ambito->id)
            ;
            $db->query($sql);
            //se la valutazione esiste già viene aggiornata
            if ($db->nextRecord()) {
                $sql = "
                    UPDATE valutazioni_valutazione_ambito 
                    SET 
                        punteggio = " . $punteggio . ",
			time_ultima_modifica = NOW()
                    WHERE 
                        valutazioni_valutazione_ambito.ID_valutazione_periodica = " . $db->toSql($this->id) . "
			AND valutazioni_valutazione_ambito.ID_ambito = " . $db->toSql($ambito->id)
                ;
                return $db->execute($sql);
            }
            //altrimenti viene inserita una nuova valutazione
            else {
                $sql = "
                    INSERT INTO valutazioni_valutazione_ambito (ID_valutazione_periodica, ID_ambito, punteggio, time_ultima_modifica)
                    VALUES 
                        (" . $db->toSql($this->id) . "," . $db->toSql($ambito->id) . "," . $db->toSql($punteggio) . ",NOW())						
                ";
                return $db->execute($sql);
            }
        } else
            return false;
    }

    //viene recuperato il punteggio di un item di una determinata valutazione
    public function getPunteggioItem(ValutazioniItem $item) {
        $db = ffDb_Sql::factory();
        //viene verificato che esista già una valutazione per l'ambito
        $sql = "
            SELECT valutazioni_valutazione_item.punteggio
            FROM valutazioni_valutazione_item
            WHERE valutazioni_valutazione_item.ID_valutazione_periodica = " . $db->toSql($this->id) . "
                AND valutazioni_valutazione_item.ID_item = " . $db->toSql($item->id)
        ;
        $db->query($sql);
        if ($db->nextRecord())
            return $db->getField("punteggio", "Number", true);
        else
            return false;
    }

    //salvataggio della valutazione dell'item. Ritorna true o false a seconda che il salvataggio sia andato a buon fine o meno
    public function salvaPunteggioItem(ValutazioniItem $item, $punteggio) {
        $user = LoggedUser::getInstance();
        $privilegi_utente = $this->getPrivilegiPersonale($user->matricola_utente_selezionato);
        if (
            $user->hasPrivilege("valutazioni_admin") == true ||
            ($this->isAutovalutazione() && $privilegi_utente["edit_autovalutazione"] == true) ||
            (!$this->isAutovalutazione() && $privilegi_utente["edit_valutatore"] == true)
        ) {
            $db = ffDb_Sql::factory();
            //viene verificato che esista già una valutazione per l'ambito
            $sql = "
                SELECT valutazioni_valutazione_item.ID
		FROM valutazioni_valutazione_item
		WHERE valutazioni_valutazione_item.ID_valutazione_periodica = " . $db->toSql($this->id) . "
                    AND valutazioni_valutazione_item.ID_item = " . $db->toSql($item->id)
            ;
            $db->query($sql);
            //se la valutazione esiste già viene aggiornata
            if ($db->nextRecord()) {
                $sql = "
                    UPDATE 
                        valutazioni_valutazione_item 
                    SET
                        punteggio = " . $punteggio . ",
			time_ultima_modifica = NOW()
                    WHERE 
                        valutazioni_valutazione_item.ID_valutazione_periodica = " . $db->toSql($this->id) . "
			AND valutazioni_valutazione_item.ID_item = " . $db->toSql($item->id)
                ;
                return $db->execute($sql);
            }
            //altrimenti viene inserita una nuova valutazione
            else {
                $sql = "
                    INSERT INTO valutazioni_valutazione_item (ID_valutazione_periodica, ID_item, punteggio, time_ultima_modifica)
                    VALUES 
                        (" . $db->toSql($this->id) . "," . $db->toSql($item->id) . "," . $db->toSql($punteggio) . ",NOW())						
						";
                return $db->execute($sql);
            }
        } else
            return false;
    }

    //viene verificato se l'ambito è valutato per la valutazione corrente
    public function isAmbitoValutato(ValutazioniAmbito $ambito) {
        $periodo = $this->periodo;
        if ($this->isAutovalutazione()) {
            //l'ambito deve essere attivo per l'autovalutazione nel caso in cui la valutazione periodica sia autovalutazione
            if (!$periodo->getAutovalutazioneAttivaCategoriaAmbito($this->id_categoria, $ambito->id))
                return false;
        }
        //verifica che l'ambito sia valutato nell'anno considerato (controllo di coerenza, in teoria non dovrebbe mai verificarsi il caso)
        if ($ambito->isValutatoCategoriaAnno($this->categoria, new ValutazioniAnnoBudget($periodo->id_anno_budget)) == false)
            return false;

        //verifica che l'ambito sia valutato nel periodo considerato per la categoria considerata
        $categoria = new ValutazioniCategoria($this->id_categoria);
        $found = false;
        foreach ($periodo->getAmbitiCategoriaPeriodo($categoria) as $periodo_ambito_categoria) {
            if ($periodo_ambito_categoria->id == $ambito->id) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            return false;
        }

        return true;
    }

    //viene restituito il totale della valutazione di un'area item
    public function getPunteggiAreaItem(ValutazioniAreaItem $area_item) {
        $ambito = new ValutazioniAmbito($area_item->id_ambito);
        if (!$this->isAmbitoValutato($ambito))
            return false;

        $peso_items = 0;
        $punteggio_items = 0;
        //calcolo della somma dei punteggi degli items per l'area selezionata							
        $items_valutazione = $this->getItemsCategoriaAmbitoValutazione($ambito, $area_item);

        foreach ($items_valutazione as $item_valutazione) {
            $peso_items += $item_valutazione->peso;
            $punteggio_items += $this->getPunteggioItem($item_valutazione) / $item_valutazione->getPunteggioMassimo() * $item_valutazione->peso;
        }

        return array(
            "peso" => $peso_items,
            "punteggio" => $punteggio_items
        );
    }

    //viene restituito il totale della valutazione periodica per l'ambito considerato. 
    //FALSE se ambito non valutato per il periodo
    public function getTotaleRaggiungimentoAmbito(ValutazioniAmbito $ambito) {
        $periodo = $this->periodo;

        if (!$this->isAmbitoValutato($ambito))
            return false;

        //una volta effettuati i controlli di coerenza viene eseguito il caloclo vero e proprio
        //in base al metodo di valutazione dell'ambito viene calcolato il totale in maniera differente
        $categoria = $this->categoria;
        $anno_val = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        $peso_ambito = $ambito->getPesoAmbitoCategoriaAnno($categoria, $anno_val);
        $metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_val);

        if ($metodo_valutazione == 1) {
            //il raggiungimento con metodo 1 è sempre espresso in %
            return $this->getPunteggioAmbito($ambito) * $peso_ambito / 100;
        } else if ($metodo_valutazione == 2) {
            $raggiungimento_items = 0;
            //calcolo della somma dei punteggi degli items									
            $items_valutazione = $this->getItemsCategoriaAmbitoValutazione($ambito);

            $peso_tot_aree = 0;
            //vengono recuperate le aree di valutazione dagli item valutati per il calcolo del totale su cui ridimensionare i punteggi
            $aree_item = array();

            foreach ($items_valutazione as $item_valutazione) {
                $found = false;
                foreach ($aree_item as $area_item) {
                    if ($item_valutazione->id_area_item == $area_item["area"]->id) {
                        $found = true;
                        break;
                    }
                }
                if ($found == false) {
                    $area = new ValutazioniAreaItem($item_valutazione->id_area_item);
                    $raggiungimento = $this->getPunteggiAreaItem($area);
                    $aree_item[] = array(
                        "area" => $area,
                        "punteggio" => $raggiungimento["punteggio"],
                        "peso" => $raggiungimento["peso"],
                    );
                    $peso_tot_aree += $raggiungimento["peso"];
                }
            }
            foreach ($aree_item as $area_item) {
                //raggiungimento su area (ragg_area) * peso dell'area (tot area/tot aree)						
                $raggiungimento_items += ($area_item["punteggio"] / $area_item["peso"]) *
                    ($area_item["peso"] / $peso_tot_aree);
            }
            return ($peso_ambito * $raggiungimento_items);
        } else
            return false;
    }

    //viene restituito il totale della valutazione periodica per la sezione considerata. 
    //FALSE se sezione non valutata per il periodo
    public function getTotaleRaggiungimentoSezione(ValutazioniSezione $sezione) {
        $periodo = $this->periodo;
        $ambiti = $sezione->getAmbitiAssociati();
        $raggiungimento_sezione = 0;
        $totale_ambiti = 0;
        foreach ($ambiti as $ambito) {
            if ($this->isAmbitoValutato($ambito)) {
                //una volta effettuati i controlli di coerenza viene eseguito il caloclo vero e proprio
                //in base al metodo di valutazione dell'ambito viene calcolato il totale in maniera differente
                $categoria = $this->categoria;
                $anno_val = new ValutazioniAnnoBudget($periodo->id_anno_budget);
                $peso_ambito = $ambito->getPesoAmbitoCategoriaAnno($categoria, $anno_val);
                $metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_val);
                if ($metodo_valutazione == 1) {
                    //il raggiungimento con metodo 1 è sempre espresso in %					
                    $raggiungimento_sezione += $this->getPunteggioAmbito($ambito) * $peso_ambito / 100;
                } else if ($metodo_valutazione == 2) {
                    $raggiungimento_items = 0;
                    //calcolo della somma dei punteggi degli items									
                    $items_valutazione = $this->getItemsCategoriaAmbitoValutazione($ambito);
                    $peso_tot_aree = 0;
                    //vengono recuperate le aree di valutazione dagli item valutati per il calcolo del totale su cui ridimensionare i punteggi
                    $aree_item = array();
                    foreach ($items_valutazione as $item_valutazione) {
                        $found = false;
                        foreach ($aree_item as $area_item) {
                            if ($item_valutazione->id_area_item == $area_item["area"]->id) {
                                $found = true;
                                break;
                            }
                        }
                        if ($found == false) {
                            $area = new ValutazioniAreaItem($item_valutazione->id_area_item);
                            $raggiungimento = $this->getPunteggiAreaItem($area);
                            $aree_item[] = array(
                                "area" => $area,
                                "punteggio" => $raggiungimento["punteggio"],
                                "peso" => $raggiungimento["peso"],
                            );
                            $peso_tot_aree += $raggiungimento["peso"];
                        }
                    }
                    foreach ($aree_item as $area_item) {
                        //raggiungimento su area (ragg_area) * peso dell'area (tot area/tot aree)						
                        $raggiungimento_items += ($area_item["punteggio"] / $area_item["peso"]) *
                            ($area_item["peso"] / $peso_tot_aree);
                    }
                    $raggiungimento_sezione += $peso_ambito * $raggiungimento_items;
                }
                $totale_ambiti += $peso_ambito;
            }
        }
        $anno = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        return $raggiungimento_sezione * $sezione->getPesoAnno($anno, $categoria) / $totale_ambiti;
    }

    //array con tutti i totali previsti per la valutazione
    public function getTotaliValutazione(ValutazioniPeriodo $periodo = null, ValutazioniAnnoBudget $anno = null, ValutazioniCategoria $categoria = null) {
        if ($periodo == null) {
            $periodo = $this->periodo;
        }
        if ($anno == null) {
            $anno = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        }
        //vengono recuperati tutti i totali previsti per l'anno		
        //se è passata una categoria vengono già filtrati i totali
        $totali_categoria = array();
        $totali_anno = $anno->getTotaliAnno($categoria);
        if ($categoria !== null) {
            $totali_categoria = $totali_anno;
        } else {
            //vengono considerati solamente i totali previsti per la categoria			
            foreach ($totali_anno as $totale_anno) {
                $categorie_totali = $totale_anno->getCategorieTotale();
                foreach ($categorie_totali as $categoria_totale) {
                    if ($categoria_totale->id == $this->id_categoria) {
                        $totali_categoria[] = $totale_anno;
                        break;
                    }
                }
            }
        }
        return $totali_categoria;
    }

    //array con tutti i totali calcolati e le descrizioni per la valutazione
    public function getTotaliCalcolati() {
        $totali = array();
        $periodo_valutazione = new ValutazioniPeriodo($this->id_periodo);
        $anno_valutazione = new ValutazioniAnnoBudget($periodo_valutazione->id_anno_budget);
        $categoria = $this->categoria;
        foreach ($this->getTotaliValutazione($periodo_valutazione, $anno_valutazione, $categoria) as $totale_val) {
            //viene visualizzato il totale solamente nel caso in cui sia attivo almeno un ambito							
            $totale_sezioni = array();
            //per ogni ambito viene aggiornato il totale della relativa sezione (per poter pesare i risultati)
            $ambiti_valutati = false;            
            foreach ($totale_val->getAmbitiTotale() as $ambito_totale) {
                //viene verificato che il totale abbia almeno un ambito valutato nel periodo
                if ($this->isAmbitoValutato($ambito_totale)){
                    $ambiti_valutati = true;
                
                    $found = false;
                    for ($i = 0; $i < count($totale_sezioni); $i++) {
                        if ($ambito_totale->id_sezione == $totale_sezioni[$i]["id_sezione"]) {
                            $found = true;
                            $totale_sezioni[$i]["raggiungimento"] += $this->getTotaleRaggiungimentoAmbito($ambito_totale);
                            $totale_sezioni[$i]["peso"] += $ambito_totale->getPesoAmbitoCategoriaAnno($categoria, $anno_valutazione);
                            break;
                        }
                    }
                    if ($found == false) {
                        $totale_sezioni[] = array(
                            "id_sezione" => $ambito_totale->id_sezione,
                            "raggiungimento" => $this->getTotaleRaggiungimentoAmbito($ambito_totale),
                            "peso" => $ambito_totale->getPesoAmbitoCategoriaAnno($categoria, $anno_valutazione),
                        );
                    }
                }
            }
            //calcolo del totale pesato per sezione
            if ($ambiti_valutati == true) {
                $totale_raggiungimento = 0;
                foreach ($totale_sezioni as $totale_sezione) {
                    $sezione = new ValutazioniSezione($totale_sezione["id_sezione"]);
                    if ($totale_sezione["peso"] == 0) {
                        $totale_raggiungimento = 0;
                    } else {
                        $totale_raggiungimento += $totale_sezione["raggiungimento"] * $sezione->getPesoAnno($anno_valutazione, $categoria) / $totale_sezione["peso"];
                    }
                }
                $totali[] = array(
                    "totale_obj" => $totale_val,
                    "totale_calcolo" => round($totale_raggiungimento, 2),
                );
            }
        }
        return $totali;
    }

    //array con tutti i totali precalcolati (tabella db) e le descrizioni per la valutazione
    public function getTotaliPreCalcolati() {
        $totali = array();
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT 
                    *
            FROM
                valutazioni_valutazione_periodica
                INNER JOIN valutazioni_totale_precalcolato ON valutazioni_valutazione_periodica.ID = valutazioni_totale_precalcolato.ID_valutazione
            WHERE
                valutazioni_valutazione_periodica.ID = " . $db->toSql($this->id)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $totali[] = array(
                    "totale_obj" => new ValutazioniTotale($db->getField("ID_totale", "Number", true)),
                    "totale_calcolo" => $db->getField("valore", "Number", true),
                );
            } while ($db->nextRecord());
        }
        return $totali;
    }
    
    
    public function saveTotaliPrecalcolati($checkDaAggiornare = false) {
        $db = ffDb_Sql::factory();   
        $ret_message = "";
        //se è necessario verificare la possibiltà di aggiornamento significa che deve essere salvato datetime
        if ($checkDaAggiornare == true){
            $date_time = "NOW()";
        }
        else {
            $date_time = "null";
        }
        foreach ($this->getTotaliCalcolati() as $totale) {            
            $sql = "
                SELECT * 
                FROM valutazioni_totale_precalcolato
                WHERE
                    valutazioni_totale_precalcolato.ID_valutazione = " . $db->toSql($this->id) . "
                    AND valutazioni_totale_precalcolato.ID_totale = " . $db->toSql($totale["totale_obj"]->id) . "
            ";

            //se il totale è stato già salvato viene aggiornato altrimenti viene inserito in tabella
            $db->query($sql);                                                                                                                                
            if ($db->nextRecord()) {
                $to_update = true;
                if ($checkDaAggiornare) {
                    // Check data
                    $obj_time_aggiornamento = DateTime::createFromFormat("Y-m-d H:i:s", CoreHelper::getDateValueFromDB($db->getField("time_aggiornamento", "Date", true)), new DateTimeZone("Europe/Rome"));                              
                    $obj_now = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s"), new DateTimeZone("Europe/Rome"));

                    if ($obj_time_aggiornamento !== false && !($obj_now >= $obj_time_aggiornamento->modify(VALUTAZIONI_DIFF_ORA_RICALCOLO))) {
                        $to_update = false;
                        $ret_message .= "->" . $totale["totale_obj"]->descrizione . " NON aggiornato";
                    }
                } 
                if ($to_update == true){
                    $sql = "
                    UPDATE valutazioni_totale_precalcolato 
                    SET 
                        ID_totale = " . $db->toSql($totale["totale_obj"]->id) . ", 
                        ID_valutazione = " . $db->toSql($this->id) . ", 
                        valore = " . $db->toSql($totale["totale_calcolo"]) . ",
                        time_aggiornamento = ".$date_time."
                    WHERE
                        valutazioni_totale_precalcolato.ID = " . $db->getField("ID", "Number", true);
                    $db->execute($sql);
                    $ret_message .= "->" . $totale["totale_obj"]->descrizione . " aggiornato";
                }                                                        
            } 
            else {
                $sql = "
                    INSERT INTO valutazioni_totale_precalcolato (
                        ID_totale, ID_valutazione, 
                        valore, time_aggiornamento
                    ) VALUES (
                        " . $db->toSql($totale["totale_obj"]->id) . "," . $db->toSql($this->id) . ", " 
                        . $db->toSql($totale["totale_calcolo"]) .",". $date_time."
                    );
                ";
                $db->execute($sql);
                $ret_message .= "->" . $totale["totale_obj"]->descrizione . " inserito";
            }
        }
        return $ret_message;
    }

    public static function getTotaliPrecalcolatiCategoriaAnno(ValutazioniAnnoBudget $anno, $id_categorie, $id_totale = null) {
        $totali_precalcolati = array();

        $db = ffDB_Sql::factory();
        $where = " 1=0 ";
        foreach($id_categorie as $id_categoria) {
            $where .=  " OR valutazioni_categoria.id = " . $db->toSql($id_categoria);
        }

        $filter_totale = '';
        if($id_totale) {
            $filter_totale = ' AND valutazioni_totale_precalcolato.ID_totale = '. $db->toSql($id_totale);
        }

        $where = "(" . $where . ")" . $filter_totale;

        $sql = "SELECT valutazioni_totale_precalcolato.*
                FROM valutazioni_totale_precalcolato
				INNER JOIN
		          valutazioni_valutazione_periodica ON (valutazioni_valutazione_periodica.ID = valutazioni_totale_precalcolato.ID_valutazione)
		        INNER JOIN
		          valutazioni_periodo ON (
		            valutazioni_periodo.ID = valutazioni_valutazione_periodica.ID_periodo
		            AND valutazioni_periodo.ID_anno_budget = ".$db->toSql($anno->id)."
		            AND MONTH(valutazioni_periodo.data_fine) = '12'
		            AND DAY(valutazioni_periodo.data_fine) = '31'
		          )
		        INNER JOIN
		          valutazioni_categoria ON (valutazioni_categoria.ID = valutazioni_valutazione_periodica.ID_categoria)
		        WHERE 
		        " . $where;

        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $totale_precalcolato = new stdClass();
                $totale_precalcolato->id = $db->getField("ID", "Number", true);
                $totale_precalcolato->id_totale = $db->getField("ID_totale", "Number", true);
                $totale_precalcolato->id_valutazione = $db->getField("ID_valutazione", "Number", true);
                $totale_precalcolato->valore = $db->getField("valore", "Number", true);
                $totale_precalcolato->totale_aggiornamento = CoreHelper::getDateValueFromDB($db->getField("time_aggiornamento", "Date", true));
                $totali_precalcolati[] = $totale_precalcolato;
            }while ($db->nextRecord());
        }
        return $totali_precalcolati;
    }
    
    // Salvataggio ambiti precalcolati
    public function saveAmbitoPrecalcolato(ValutazioniAmbito $ambito, $checkDaAggiornare = false) {
        
        $totale_ambito = round($this->getTotaleRaggiungimentoAmbito($ambito), 2);
        
        $db = ffDb_Sql::factory();
        $sql = "
            SELECT * 
            FROM valutazioni_ambito_precalcolato
            WHERE
                valutazioni_ambito_precalcolato.ID_valutazione = " . $db->toSql($this->id) . " AND
                valutazioni_ambito_precalcolato.ID_ambito = " . $db->toSql($ambito->id) . "
        ";

        if ($checkDaAggiornare == true){
            $date_time = "NOW()";
        }
        else {
            $date_time = "null";
        }

        // Se l'ambito è già stato salvato: UPDATE
        // viceversa: INSERT
        $db->query($sql);
        if ($db->nextRecord()) {
            $sql = "
                UPDATE valutazioni_ambito_precalcolato 
                SET ID_ambito = " . $db->toSql($ambito->id) . ", 
                    ID_valutazione = " . $db->toSql($this->id) . ", 
                    valore = " . $db->toSql($totale_ambito) . ", 
                    time_aggiornamento = ".$date_time."
                WHERE
                    valutazioni_ambito_precalcolato.ID = " . $db->getField("ID", "Number", true) . ";
            ";
        } else {
            $sql = "
                INSERT INTO valutazioni_ambito_precalcolato (
                    ID_ambito, ID_valutazione, 
                    valore, time_aggiornamento
                )
                VALUES (
                    " . $db->toSql($ambito->id) . "," . $db->toSql($this->id) . ", 
                    " . $db->toSql($totale_ambito) . "," . $date_time."
                );
            ";
        }

        $db->execute($sql);
    }
    
    public static function getAmbitiPrecalcolatiPunteggiValutazione($id_anno, $id_periodo, $ids_categoria, $id_sezione = -1) {        
        $db = ffDb_Sql::factory();
        $condizione_periodo = "valutazioni_valutazione_periodica.ID_periodo = ".$db->toSql($id_periodo);
        
        $condizione_sezione = "";
        if ($id_sezione != -1) {
            $condizione_sezione = " AND valutazioni_ambito.ID_sezione = ".$db->toSql($id_sezione);
        }
        
        $condizione_categoria = " AND (";
        foreach($ids_categoria as $id_categoria) {
            if (strlen($condizione_categoria) > 6) {
                $condizione_categoria .= " OR ";
            }
            
            $condizione_categoria .= "{tabella}.ID_categoria = ".$db->toSql($id_categoria);
        }
        $condizione_categoria .= ")";
        
        $sql = "
            SELECT valutazioni_ambito_precalcolato.ID,
                valutazioni_valutazione_periodica.ID AS 'ID_valutazione',
                valutazioni_ambito_precalcolato.ID_ambito,
                valutazioni_ambito.ID_sezione, 
                valutazioni_ambito_categoria_anno.ID_categoria,
                valutazioni_ambito_categoria_anno.peso, valutazioni_ambito_categoria_anno.metodo_valutazione,
                valutazioni_ambito_precalcolato.valore,
                valutazioni_valutazione_periodica.matricola_valutato
            FROM valutazioni_valutazione_periodica
                INNER JOIN valutazioni_ambito_precalcolato ON (
                    valutazioni_valutazione_periodica.ID = valutazioni_ambito_precalcolato.ID_valutazione
                )
                INNER JOIN valutazioni_ambito valutazioni_ambito ON (
                    valutazioni_ambito_precalcolato.ID_ambito = valutazioni_ambito.ID
                    $condizione_sezione
                ) 
                INNER JOIN valutazioni_ambito_categoria_anno ON ( 
                        valutazioni_ambito.ID = valutazioni_ambito_categoria_anno.ID_ambito AND 
                        valutazioni_ambito_categoria_anno.ID_anno_budget = ".$db->toSql($id_anno)."
                        ". str_replace("{tabella}", "valutazioni_ambito_categoria_anno", $condizione_categoria) ."
                )
            WHERE $condizione_periodo
                ". str_replace("{tabella}", "valutazioni_valutazione_periodica", $condizione_categoria) ."
            ORDER BY valutazioni_valutazione_periodica.ID, valutazioni_ambito_precalcolato.ID_ambito, 
                valutazioni_ambito.ID_sezione
        ";
        
        $ambiti_precalcolati = array();
        
        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $ambito_precalcolato = new stdClass();
                $ambito_precalcolato->id = $db->getField("ID", "Number", true);
                $ambito_precalcolato->id_valutazione = $db->getField("ID_valutazione", "Number", true);
                $ambito_precalcolato->matricola_valutato = $db->getField("matricola_valutato", "Text", true);
                $ambito_precalcolato->id_ambito = $db->getField("ID_ambito", "Number", true);
                $ambito_precalcolato->id_sezione = $db->getField("ID_sezione", "Number", true);
                $ambito_precalcolato->id_categoria = $db->getField("ID_categoria", "Number", true);
                $ambito_precalcolato->peso = $db->getField("peso", "Number", true);
                $ambito_precalcolato->metodo_valutazione = $db->getField("metodo_valutazione", "Number", true);                
                $ambito_precalcolato->valore = $db->getField("valore", "Number", true);
                $ambiti_precalcolati[] = $ambito_precalcolato;
            }while ($db->nextRecord());
        }
        
        return $ambiti_precalcolati;
    }

    public static function getSchedeCategoriaPeriodo($id_categoria, $id_periodo = null) {
        $schede_valutazione = array();

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT 
                valutazioni_valutazione_periodica.*
            FROM valutazioni_valutazione_periodica
            WHERE
                valutazioni_valutazione_periodica.ID_categoria = " . $db->toSql($id_categoria);

        if(isset($id_periodo)) {
            $sql .= " AND valutazioni_valutazione_periodica.ID_periodo = " . $db->toSql($id_periodo);
        }

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
                $scheda_valutazione->id_categoria = $db->getField("ID_categoria", "Number", true);

                $schede_valutazione[] = $scheda_valutazione;
            } while ($db->nextRecord());
        }
        return $schede_valutazione;
    }

    public function delete() {
        $db = ffDB_Sql::factory();

        $query_delete = "
            DELETE FROM valutazioni_valutazione_periodica
            WHERE valutazioni_valutazione_periodica.ID = " . $db->toSql($this->id) . "
        ";

        try {
            $db->execute($query_delete);
        }
        catch (Exception $ext) {
            return false;
        }

        return true;
    }
    
    //generazione del pdf per la stampa delle schede.
    //viene restituito il codice html per l'estrazione della scheda
    public function generazioneHtmlStampa() {        
        $module = Modulo::getCurrentModule();
        //viene caricato il template specifico per la pagina                
        $tpl = ffTemplate::factory($module->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
        $tpl->load_file("stampa_valutazione.html", "main");
        $tpl->set_var("logo_stampa_filename", mod_security_get_logo());                

        //******
        //titolo
        $periodo = new ValutazioniPeriodo($this->id_periodo);
        $anno_valutazione = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        
        $tpl->set_var("anno", $anno_valutazione->descrizione);

        if ($this->isAutovalutazione())
            $tpl->set_var ("titolo", "Autovalutazione - " . $periodo->descrizione);
        else
            $tpl->set_var ("titolo", "Valutazione - " . $periodo->descrizione);
      
        //************
        //intestazione
        
        $tpl->set_var ("date_riferimento_periodo", CoreHelper::formatUiDate($periodo->data_inizio) . " - " . CoreHelper::formatUiDate($periodo->data_fine));
              
        if (!$this->isAutovalutazione()){
            $valutatore = Personale::factoryFromMatricola($this->matricola_valutatore);
            $tpl->set_var("valutatore", $valutatore->cognome." ".$valutatore->nome." (matr. ".$valutatore->matricola.")");
        }

        $valutato = Personale::factoryFromMatricola($this->matricola_valutato);
        $tpl->set_var("valutato", $valutato->cognome." ".$valutato->nome." (matr. ".$valutato->matricola.")");

        //cdr afferenza
        $cdr_commento = "";
        $tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();
        $cdr_afferenza = $valutato->getCdrAfferenzaInData($tipo_piano_cdr, $periodo->data_fine);      
        if (count ($cdr_afferenza) == 0) {
            $cdr_commento = " (ultima afferenza - dipendente cessato nell'anno)";
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo->data_fine);
            $ultimi_cdr_afferenza = $valutato->getCdrUltimaAfferenza($tipo_piano_cdr);
            if (count ($ultimi_cdr_afferenza) > 0) {
                foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                    try {
                        $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                        if ($cdr_attuale->codice == $cdr_aff["cdr"]->codice) {
                            $cdr_afferenza[] = $cdr_aff;
                        }
                    } catch (Exception $ex) {

                    }
                }
            }
        }

        foreach ($cdr_afferenza as $cdr_aff) {  
            $tipo_cdr = new TipoCdr($cdr_aff["cdr"]->id_tipo_cdr);                        
            $tpl->set_var("cdr", $cdr_aff["cdr"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_aff["cdr"]->descrizione . $cdr_commento);
            $tpl->set_var("perc_testa", $cdr_aff["peso_cdr"]);
            $tpl->parse("SectCdrAssociati", true);                
        }
        
        $categoria = $this->categoria;
        $tpl->set_var("tipologia_scheda", $categoria->descrizione);

        //*********************RIEPILOGO OBIETTIVI**************************
        if ($periodo->visualizzazione_obiettivi != false) {           
            //estrazione degli eventuali obiettivi associati al dipendente
            $personale_obiettivi = $valutato->cloneAttributesToNewObject("PersonaleObiettivi");
            $obiettivi_indiviuduali = $personale_obiettivi->getObiettiviCdrPersonaleAnno($anno_valutazione);            
            if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
                $obiettivi_cdr_responsabilita = $personale_obiettivi->getObiettiviCdrReponsabilitaData($anno_valutazione, new DateTime(date($periodo->data_fine)), TipoPianoCdr::getPrioritaMassima());
            }
            else {
                $obiettivi_cdr_responsabilita = $personale_obiettivi->getObiettiviReponsabilitaData($anno_valutazione, new DateTime(date($periodo->data_fine)), TipoPianoCdr::getPrioritaMassima());
            }
            $no_obiettivi = true;
            if (count($obiettivi_indiviuduali)) {     
                $no_obiettivi = false;
                $tot_obiettivi_personale = $personale_obiettivi->getPesoTotaleObiettivi($anno_valutazione);
                foreach ($obiettivi_indiviuduali as $obiettivo_individuale) {
                    if ($obiettivo_individuale->data_eliminazione == null) {
                        $obiettivo_cdr = new ObiettiviObiettivoCdr($obiettivo_individuale->id_obiettivo_cdr);
                        $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);                
                        if ($obiettivo_cdr->id_tipo_piano == 0) {
                            $tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();
                        }
                        else {
                            $tipo_piano_cdr = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano);
                        }
                        try {
                            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo->data_fine);
                            $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
                            $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
                            $cdr_desc = $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione;
                        } catch (Exception $ex) {
                            $cdr_desc = "Cdr cessato";
                        }
                        if ($tot_obiettivi_personale == 0) {
                            $peso_perc = 0;
                        } else {
                            $peso_perc = 100 / $tot_obiettivi_personale * $obiettivo_individuale->peso;
                        }

                        $tpl->set_var("obiettivo", $obiettivo->codice." - ".$obiettivo->titolo);
                        $tpl->set_var("cdr_obiettivo", $cdr_desc);
                        $tpl->set_var("peso", number_format($peso_perc, 2)."%");                    

                        $tpl->parse("SectObiettivoIndividuale", true);
                    }
                }
                $tpl->parse("SectObiettiviIndividualiAnno", false);
            }
            if (count($obiettivi_cdr_responsabilita)) {
                $no_obiettivi = false;
                foreach($obiettivi_cdr_responsabilita as $obiettivo_cdr_responsabilita) {                    
                    if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
                        $obiettivo = $obiettivo_cdr_responsabilita["obiettivo"];
                        $tipo_cdr = new TipoCdr($obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->id_tipo_cdr);
                        $cdr_desc = $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->descrizione;
                        $peso_tot_obiettivi_cdr = $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->getPesoTotaleObiettivi($anno_valutazione);
                        if ($obiettivo_cdr_responsabilita["obiettivo_cdr"]->isReferenteObiettivoTrasversale()){
                            $coreferente = " (referente)";
                        }
                        else if ($obiettivo_cdr_responsabilita["obiettivo_cdr"]->isCoreferenza()) {
                            $coreferente = " (trasversale)";
                        } else {
                            $coreferente = "";
                        }
                        if ($peso_tot_obiettivi_cdr == 0) {
                            $peso = 0;
                        } else {
                            $peso = 100 / $peso_tot_obiettivi_cdr * $obiettivo_cdr_responsabilita["obiettivo_cdr"]->peso;
                        }
                        $tpl->set_var("obiettivo", $obiettivo->codice." - ".$obiettivo->titolo);
                        $tpl->set_var("cdr_obiettivo", $cdr_desc);
                        $tpl->set_var("peso", number_format($peso, 2)."%");                    
                        $tpl->parse("SectObiettivoCdrResponsabile", true);
                    }
                    else {
                        $tpl->set_var("obiettivo", $obiettivo_cdr_responsabilita->codice." - ".$obiettivo_cdr_responsabilita->titolo);                             
                        $tpl->parse("SectObiettivoResponsabile", true);
                    }                                        
                }        
                if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
                    $tpl->parse("SectObiettiviCdrResponsabileAnno", false);
                }
                else {
                    $tpl->parse("SectObiettiviResponsabileAnno", false);
                }
            }
            if ($no_obiettivi == true) {
                $tpl->parse("SectNoObiettiviAnno", false);
            }
            $tpl->parse("SectObiettivi", false);
        }      
        
        //****************
        //sezioni e ambiti
        $sezione_prec = -1;
        foreach($periodo->getAmbitiCategoriaPeriodo($categoria) as $ambito){
            //in base al metodo di valutazione vengono visualizzati campi differenti ed in base ai privilegi viene data la possibilità o meno di compilare l'ambito
            $view_ambito = false;
            //se la valutazione è un'autovalutazione verranno visualizzati solamente gli ambiti con autovalutazione attiva
            //e verrà permessa la modifica solo agli utenti con privilegio edit_autovalutazione attivo
            if ($this->isAutovalutazione()){	
                //l'ambito deve essere attivo per l'autovalutazione
                if ($periodo->getAutovalutazioneAttivaCategoriaAmbito($categoria, $ambito))
                    $view_ambito = true;						
            }	
            //se la valutazione non è autovalutazione verranno visualizzati tutti gli ambiti attivi per categoria e periodo
            //e verrà permessa la modifica dell'ambito solamente al valutatore
            else{
                $view_ambito = true;
            }

            //verifica che l'ambito sia valutato nell'anno considerato (controllo di coerenza, in teoria non dovrebbe mai verificarsi il caso)
            if ($ambito->isValutatoCategoriaAnno($categoria, $anno_valutazione)==false){
                $view_ambito = false;		
            }	

            if ($view_ambito == true){   
                $visualizzazione_punteggio_categoria_ambito = $periodo->getVisualizzazionePunteggiAttivaCategoriaAmbito($categoria, $ambito);
                if ($sezione_prec !== $ambito->id_sezione) {            
                    $sezione = new ValutazioniSezione($ambito->id_sezione);
                    $descrizione_sezione = $sezione->codice.". ".$sezione->descrizione;
                    if ($periodo->getVisualizzazionePunteggiAttivaCategoriaSezione($categoria, $sezione)) {
                        $descrizione_sezione .= " (".round($this->getTotaleRaggiungimentoSezione($sezione),2)."/".$sezione->getPesoAnno($anno_valutazione, $categoria).")";                                 
                    }
                    $tpl->set_var("sezione", $descrizione_sezione);
                    $sezione_prec = $ambito->id_sezione;

                }
                else {
                    $tpl->set_var("sezione", false);
                }

                if ($this->isAutovalutazione()){
                    $nome_ambito = $sezione->codice.".".$ambito->codice. ". ".$ambito->descrizione;
                }
                else {
                    $tot_ambito = $ambito->getPesoAmbitoCategoriaAnno($categoria, $anno_valutazione);
                    $nome_ambito = $sezione->codice.".".$ambito->codice. ". ".$ambito->descrizione;
                    if ($visualizzazione_punteggio_categoria_ambito == true) {
                        $nome_ambito .= " (".round($this->getTotaleRaggiungimentoAmbito($ambito),2)." / ".$tot_ambito.")";
                    }			
                }	
                $tpl->set_var("ambito", $nome_ambito);

                //in base al metodo di valutazione dell'ambito viene visualizzato un campo / dei campi differenti
                $metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_valutazione);

                //estrazione campi metodo valutazione
                $found = false;
                for($i=0; $i<count(ValutazioniAmbito::$metodi_valutazione); $i++){
                    if (ValutazioniAmbito::$metodi_valutazione[$i]["ID"] == $metodo_valutazione){				
                        $found = $i;
                        $i = count(ValutazioniAmbito::$metodi_valutazione);
                    }
                }
                if ($found === false)
                    ffErrorHandler::raise("Configurazione errata metodi valutazione");
                else			
                    $nome_campo = ValutazioniAmbito::$metodi_valutazione[$found]["nome_campo"];

                //metodo valutazione: Ins. backoffice
                //il valore è sempre un raggiungimento percentuale
                if ($metodo_valutazione == 1){
                    //$tpl->set_var("nome_ambito", $nome_ambito);
                    $tpl->set_var("punteggio_ambito", $this->getPunteggioAmbito($ambito));
                    $tpl->parse("SectMetodo1", false);
                    $tpl->set_var("SectMetodo2", false);
                }	
                //metodo valutazione: Items
                //neppure gli admin possono modificare il metodo di valutazione
                else if ($metodo_valutazione == 2){
                    //vengono considerati solo gli item dell'ambito corrente
                    $items_valutazione = $this->getItemsCategoriaAmbitoValutazione($ambito);
                    if(count($items_valutazione) > 0){						
                        foreach($items_valutazione as $item_valutazione){			
                            //visualizzazione dell'area
                            if ($i==0 || ($area_prec !== $item_valutazione->id_area_item)){					
                                $area_item = new ValutazioniAreaItem($item_valutazione->id_area_item);
                                $area_prec = $item_valutazione->id_area_item;
                                //echo("<br>area_item ".$area_item->id."-area_prec ".$area_prec."-ambito ".$ambito->id);
                                if ($this->isAutovalutazione()){
                                    $tpl->set_var("area_item", $area_item->descrizione);
                                }
                                else {
                                    $punteggio_area_item = $this->getPunteggiAreaItem($area_item);	
                                    $descrizione_area_item = $area_item->descrizione;							
                                    if ($visualizzazione_punteggio_categoria_ambito == true) {
                                        $descrizione_area_item .= " (" . $punteggio_area_item["punteggio"] . " / " . $punteggio_area_item["peso"] . ")";
                                    }                           
                                    $tpl->set_var("area_item", $descrizione_area_item);
                                }	
                                $tpl->parse("SectTitoloAreaItem", false);                        
                            }
                            else {
                                $tpl->set_var("SectTitoloAreaItem", false);
                            }

                            $raggiunto_item = $this->getPunteggioItem($item_valutazione);                    
                            if ($this->isAutovalutazione()){
                                $item_desc = $item_valutazione->nome." - ".$item_valutazione->descrizione;
                            }
                            else {
                                $item_desc =  $item_valutazione->nome." - ".$item_valutazione->descrizione;
                                if ($visualizzazione_punteggio_categoria_ambito == true) {
                                    $item_desc .=  " (".$raggiunto_item / $item_valutazione->getPunteggioMassimo() * $item_valutazione->peso."/".$item_valutazione->peso.")";                          
                                }                       
                            }	                    

                            $desc_punteggio = null;
                            foreach($item_valutazione->getPunteggi() as $punteggio){
                                if ($raggiunto_item == $punteggio->punteggio) {                            
                                    if ($visualizzazione_punteggio_categoria_ambito == true){
                                        $desc_punteggio = (float)$punteggio->punteggio." - ".$punteggio->descrizione;
                                    }
                                    else {
                                        $desc_punteggio = $punteggio->descrizione;
                                    }                           
                                    break;	
                                }                        					
                            }
                            if ($desc_punteggio == null){
                                $desc_punteggio  = "Non valutato.";
                            }
                            $tpl->set_var("item", $item_desc);
                            $tpl->set_var("punteggio", $desc_punteggio);

                            $tpl->parse("SectItem", true);
                            $tpl->parse("SectAreaItem", true);
                            $tpl->set_var("SectItem", false);

                        }
                    }
                    $tpl->parse("SectMetodo2", false);
                    $tpl->set_var("SectMetodo1", false);
                }
                else {
                    //se nessun metodo specificato ($metodo = 0 casistica che non dovrebbe presentarsi)		
                    ffErrorHandler::raise("Errore di configurazione ambito " . $nome_ambito);
                }      
                $tpl->parse("SectAmbito", true);
                $tpl->set_var("SectAreaItem", false);
            }
        }

        if ($this->isAutovalutazione()){	
            $tpl->set_var("titolo_note_valutato", "auto");
        }
        else {
            if (strlen($this->note_valutatore)>0){
                $note_valutatore = $this->note_valutatore;
            }
            else {
                $note_valutatore = "Nessuna";
            }
            $tpl->set_var("note_valutatore", $note_valutatore);
            if ($this->data_firma_valutatore !== null){
                $data_firma_valutatore = CoreHelper::formatUiDate($this->data_firma_valutatore, "Y-m-d H:i:s", "d/m/Y - H:i");
            }
            else {
                $data_firma_valutatore = "Valutazione non firmata";
            }
            $tpl->set_var("data_firma_valutatore", $data_firma_valutatore);

            $tpl->parse("SectValutatore", false);
            $tpl->set_var("titolo_note_valutato", "");
            if (strlen($this->note_valutato)>0){
                $note_valutato = $this->note_valutato;
            }
            else {
                $note_valutato = "Nessuna";
            }
            if ($this->data_firma_valutato !== null){
                $data_firma_valutato = CoreHelper::formatUiDate($this->data_firma_valutato, "Y-m-d H:i:s", "d/m/Y - H:i");
            }
            else {
                $data_firma_valutato = "Valutazione non firmata";
            }
            $tpl->set_var("data_firma_valutato", $data_firma_valutato);
            $tpl->set_var("note_valutato", $note_valutato);    
            $tpl->parse("SectValutato", false);
        }

        //TOTALI VALUTAZIONE*********************************
        if (!$this->isAutovalutazione()) {  
            $view_totali = false;
            foreach($this->getTotaliPreCalcolati() as $totale_valutazione){
                $ambiti_totale_attivi = "";
                $ambiti_totale = $totale_valutazione["totale_obj"]->getAmbitiTotale();        
                foreach ($ambiti_totale as $ambito_totale) {
                    if ($periodo->getVisualizzazionePunteggiAttivaCategoriaAmbito($categoria, $ambito_totale) == true) {                
                        if ($ambito_totale->isValutatoCategoriaAnno($categoria, $anno_valutazione)) {
                            if ($this->isAmbitoValutato($ambito_totale))
                                $nv = "";
                            else
                                $nv = "(nv)";

                            if (strlen($ambiti_totale_attivi) > 0)
                                $plus = " - ";
                            else
                                $plus = "";
                            $sezione = new ValutazioniSezione($ambito_totale->id_sezione);
                            $ambiti_totale_attivi .= $plus . $sezione->codice . "." . $ambito_totale->codice . "." . $nv;
                        }
                    }
                }    
                if ($periodo->inibizione_visualizzazione_totali == false) {
                    if ($periodo->inibizione_visualizzazione_ambiti_totali == true || strlen($ambiti_totale_attivi)==0) {
                        $ambiti_totale_attivi = "";
                    }
                    else {
                        $ambiti_totale_attivi = " (".$ambiti_totale_attivi.")";
                    }  
                    $tpl->set_var("totale_desc", $totale_valutazione["totale_obj"]->descrizione.$ambiti_totale_attivi);
                    $tpl->set_var("totale_punteggio", $totale_valutazione["totale_calcolo"]);
                    $view_totali = true;
                    $tpl->parse("SectTotale", true);            
                }                               		                      
            }
            if ($view_totali == true) {
                $tpl->parse("SectTotali", true);
            }
            else {
                    $tpl->set_var("SectTotale", "");
            } 
        }            
        return $tpl->rpparse("main", false);
    }
}