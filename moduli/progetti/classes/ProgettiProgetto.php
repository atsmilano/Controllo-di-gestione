<?php
class ProgettiProgetto {
    public $id;
    public $numero_progetto;
    public $matricola_utente_creazione;
    public $codice_cdr_proponente;
    public $data_creazione;
    public $titolo_progetto;
    public $matricola_responsabile_progetto;
    public $id_tipo_progetto;
    public $id_file;
    public $finanziato;
    public $capofila;
    public $team_progetto;
    public $partner_esterni;
    public $descrizione_progetto;
    public $tema_progetto;
    public $modalita_progetto;
    public $target_progetto;
    public $setting;
    public $obiettivo_generale_progetto;
    public $analisi_contesto_progetto;
    public $id_territorio_applicazione;
    public $metodi_progetto;
    public $risultati_attesi_progetto;
    public $cambiamenti_altri_enti;
    public $frequenza_monitoraggio;
    public $metodo_monitoraggio;
    public $budget;
    public $risorse_gia_disponibili;
    public $descrizione_risorse_aggiuntive;
    public $importo_risorse_aggiuntive;
    public $ricadute_altri_cdr;
    public $costi_indotti;
    public $importo_totale_costi_indotti;
    public $materiali;
    public $importo_materiali;
    public $spazi;
    public $risorse_professionali_coinvolte;
    public $importo_risorse_professionali_coinvolte;
    public $altro;
    public $importo_alto;
    public $oracle_erp;
    public $data_inizio_progetto;
    public $data_fine_progetto;
    public $data_approvazione;
    public $matricola_responsabile_riferimento_approvazione;
    public $stato;
    public $note;
    public $numero_revisione;
    public $validazione_finale;
    public $note_validazione_finale;
    public $data_validazione_finale;
    public $extend;
    public $time_modifica;
    public $record_attivo;

    public function __construct($id = null) {

        if ($id != null) {
            $db = ffDb_Sql::factory();

            $sql = "
            SELECT * 
            FROM progetti_progetto pp 
            WHERE pp.ID = " . $db->toSql($id);
            $db->query($sql);           
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->numero_progetto = $this->id;
                $this->matricola_utente_creazione = $db->getField("matricola_utente_creazione", "Text", true);
                $this->codice_cdr_proponente = $db->getField("codice_cdr_proponente", "Text", true);
                $this->data_creazione = $db->getField("data_creazione", "Date", true);
                $this->titolo_progetto = $db->getField("titolo_progetto", "Text", true);
                $this->matricola_responsabile_progetto = $db->getField("matricola_responsabile_progetto", "Text", true);
                $this->id_tipo_progetto = $db->getField("ID_tipo_progetto", "Number", true);
                $this->id_file = $db->getField("ID_file", "Number", true);
                $this->finanziato = CoreHelper::getBooleanValueFromDB($db->getField("finanziato", "Text", true));
                $this->capofila = $db->getField("capofila", "Text", true);
                $this->team_progetto = $db->getField("team_progetto", "Text", true);
                $this->partner_esterni = $db->getField("partner_esterni", "Text", true);
                $this->descrizione_progetto = $db->getField("descrizione_progetto", "Text", true);
                $this->tema_progetto = $db->getField("tema_progetto", "Text", true);
                $this->modalita_progetto = $db->getField("modalita_progetto", "Text", true);
                $this->target_progetto = $db->getField("target_progetto", "Text", true);
                $this->setting = $db->getField("setting", "Text", true);
                $this->obiettivo_generale_progetto = $db->getField("obiettivo_generale_progetto", "Text", true);
                $this->analisi_contesto_progetto = $db->getField("analisi_contesto_progetto", "Text", true);
                $this->id_territorio_applicazione = $db->getField("ID_territorio_applicazione", "Number", true);
                $this->metodi_progetto = $db->getField("metodi_progetto", "Text", true);
                $this->risultati_attesi_progetto = $db->getField("risultati_attesi_progetto", "Text", true);
                $this->cambiamenti_altri_enti = $db->getField("cambiamenti_altri_enti", "Text", true);
                $this->frequenza_monitoraggio = $db->getField("frequenza_monitoraggio", "Text", true);
                $this->metodo_monitoraggio = $db->getField("metodo_monitoraggio", "Text", true);
                $this->budget = $db->getField("budget", "Number", true);
                $this->risorse_gia_disponibili = CoreHelper::getBooleanValueFromDB($db->getField("risorse_gia_disponibili", "Text", true));
                $this->descrizione_risorse_aggiuntive = $db->getField("descrizione_risorse_aggiuntive", "Text", true);
                $this->importo_risorse_aggiuntive = $db->getField("importo_risorse_aggiuntive", "Number", true);
                $this->ricadute_altri_cdr = $db->getField("ricadute_altri_cdr", "Text", true);
                $this->costi_indotti = $db->getField("costi_indotti", "Text", true);
                $this->importo_totale_costi_indotti = $db->getField("importo_totale_costi_indotti", "Number", true);
                $this->materiali = $db->getField("materiali", "Text", true);
                $this->importo_materiali = $db->getField("importo_materiali", "Number", true);
                $this->spazi = $db->getField("spazi", "Text", true);
                $this->risorse_professionali_coinvolte = $db->getField("risorse_professionali_coinvolte", "Text", true);
                $this->importo_risorse_professionali_coinvolte = $db->getField("importo_risorse_professionali_coinvolte", "Number", true);
                $this->altro = $db->getField("altro", "Text", true);
                $this->importo_alto = $db->getField("importo_alto", "Number", true);
                $this->oracle_erp = $db->getField("oracle_erp", "Text", true);
                $this->data_inizio_progetto = CoreHelper::getDateValueFromDB($db->getField("data_inizio_progetto", "Date", true));
                $this->data_fine_progetto = CoreHelper::getDateValueFromDB($db->getField("data_fine_progetto", "Date", true));
                $this->data_approvazione = CoreHelper::getDateValueFromDB($db->getField("data_approvazione", "Date", true));
                $this->matricola_responsabile_riferimento_approvazione = $db->getField("matricola_responsabile_riferimento_approvazione", "Text", true);
                $this->stato = $db->getField("stato", "Text", true);
                $this->note = $db->getField("note", "Text", true);
                $this->numero_revisione = $db->getField("numero_revisione", "Number", true);
                $this->validazione_finale = CoreHelper::getBooleanValueFromDB($db->getField("validazione_finale", "Text", true));
                $this->note_validazione_finale = $db->getField("note_validazione_finale", "Text", true);
                $this->data_validazione_finale = CoreHelper::getDateValueFromDB($db->getField("data_validazione_finale", "Date", true));
                $this->extend = $db->getField("extend", "Text", true);                
                $this->time_modifica = $db->getField("time_modifica", "Date", true);
                $this->record_attivo = $db->getField("record_attivo", "Number", true);
            } else {
                throw new Exception("Impossibile creare l'oggetto ProgettiProgetto con ID = " . $id);
            }
        }
    }
    
    public static function getAll($filters = array()) {
        $progetti = array();
        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= "AND ".$field."=".$db->toSql($value);
        }
        $sql = "
                SELECT progetti_progetto.*
                FROM progetti_progetto
                " . $where . "
                ORDER BY ID"
                ;
        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $progetto = new ProgettiProgetto();
                $progetto->id = $db->getField("ID", "Number", true);
                $progetto->numero_progetto = $progetto->id;
                $progetto->matricola_utente_creazione = $db->getField("matricola_utente_creazione", "Text", true);
                $progetto->codice_cdr_proponente = $db->getField("codice_cdr_proponente", "Text", true);
                $progetto->data_creazione = $db->getField("data_creazione", "Date", true);
                $progetto->titolo_progetto = $db->getField("titolo_progetto", "Text", true);
                $progetto->matricola_responsabile_progetto = $db->getField("matricola_responsabile_progetto", "Text", true);
                $progetto->id_tipo_progetto = $db->getField("ID_tipo_progetto", "Number", true);
                $progetto->id_file = $db->getField("ID_file", "Number", true);
                $progetto->finanziato = CoreHelper::getBooleanValueFromDB($db->getField("finanziato", "Text", true));
                $progetto->capofila = $db->getField("capofila", "Text", true);
                $progetto->team_progetto = $db->getField("team_progetto", "Text", true);
                $progetto->partner_esterni = $db->getField("partner_esterni", "Text", true);
                $progetto->descrizione_progetto = $db->getField("descrizione_progetto", "Text", true);
                $progetto->tema_progetto = $db->getField("tema_progetto", "Text", true);
                $progetto->modalita_progetto = $db->getField("modalita_progetto", "Text", true);
                $progetto->target_progetto = $db->getField("target_progetto", "Text", true);
                $progetto->setting = $db->getField("setting", "Text", true);
                $progetto->obiettivo_generale_progetto = $db->getField("obiettivo_generale_progetto", "Text", true);
                $progetto->analisi_contesto_progetto = $db->getField("analisi_contesto_progetto", "Text", true);
                $progetto->id_territorio_applicazione = $db->getField("ID_territorio_applicazione", "Number", true);
                $progetto->metodi_progetto = $db->getField("metodi_progetto", "Text", true);
                $progetto->risultati_attesi_progetto = $db->getField("risultati_attesi_progetto", "Text", true);
                $progetto->cambiamenti_altri_enti = $db->getField("cambiamenti_altri_enti", "Text", true);
                $progetto->frequenza_monitoraggio = $db->getField("frequenza_monitoraggio", "Text", true);
                $progetto->metodo_monitoraggio = $db->getField("metodo_monitoraggio", "Text", true);
                $progetto->budget = $db->getField("budget", "Number", true);
                $progetto->risorse_gia_disponibili = CoreHelper::getBooleanValueFromDB($db->getField("risorse_gia_disponibili", "Text", true));
                $progetto->descrizione_risorse_aggiuntive = $db->getField("descrizione_risorse_aggiuntive", "Text", true);
                $progetto->importo_risorse_aggiuntive = $db->getField("importo_risorse_aggiuntive", "Number", true);
                $progetto->ricadute_altri_cdr = $db->getField("ricadute_altri_cdr", "Text", true);
                $progetto->costi_indotti = $db->getField("costi_indotti", "Text", true);
                $progetto->importo_totale_costi_indotti = $db->getField("importo_totale_costi_indotti", "Number", true);
                $progetto->materiali = $db->getField("materiali", "Text", true);
                $progetto->importo_materiali = $db->getField("importo_materiali", "Number", true);
                $progetto->spazi = $db->getField("spazi", "Text", true);
                $progetto->risorse_professionali_coinvolte = $db->getField("risorse_professionali_coinvolte", "Text", true);
                $progetto->importo_risorse_professionali_coinvolte = $db->getField("importo_risorse_professionali_coinvolte", "Number", true);
                $progetto->altro = $db->getField("altro", "Text", true);
                $progetto->importo_alto = $db->getField("importo_alto", "Number", true);
                $progetto->oracle_erp = $db->getField("oracle_erp", "Text", true);
                $progetto->data_inizio_progetto = CoreHelper::getDateValueFromDB($db->getField("data_inizio_progetto", "Date", true));
                $progetto->data_fine_progetto = CoreHelper::getDateValueFromDB($db->getField("data_fine_progetto", "Date", true));
                $progetto->data_approvazione = CoreHelper::getDateValueFromDB($db->getField("data_approvazione", "Date", true));
                $progetto->matricola_responsabile_riferimento_approvazione = $db->getField("matricola_responsabile_riferimento_approvazione", "Text", true);
                $progetto->stato = $db->getField("stato", "Text", true);
                $progetto->note = $db->getField("note", "Text", true);
                $progetto->numero_revisione = $db->getField("numero_revisione", "Number", true);
                $progetto->validazione_finale = CoreHelper::getBooleanValueFromDB($db->getField("validazione_finale", "Text", true));
                $progetto->note_validazione_finale = $db->getField("note_validazione_finale", "Text", true);
                $progetto->data_validazione_finale = CoreHelper::getDateValueFromDB($db->getField("data_validazione_finale", "Date", true));
                $progetto->extend = $db->getField("extend", "Text", true);                
                $progetto->time_modifica = $db->getField("time_modifica", "Date", true);
                $progetto->record_attivo = $db->getField("record_attivo", "Number", true);

                $progetti[] = $progetto;
            }while($db->nextRecord());
        }
        return $progetti;
    }

    public static function viewMenuByMatricola($user_matricola) {
        $db = ffDb_Sql::factory();
        $query = "
            SELECT *
            FROM progetti_progetto pp
            WHERE pp.record_attivo = ".$db->toSql(1)."
                AND (
                    pp.matricola_utente_creazione = ".$db->toSql($user_matricola)." OR
                    pp.matricola_responsabile_progetto = ".$db->toSql($user_matricola)." OR
                    pp.matricola_responsabile_riferimento_approvazione = ".$db->toSql($user_matricola)."
                )
        ";
        $db->query($query);
        if ($db->nextRecord()) {
            $result = true;
        }
        return $result;
    }

    public function getDescrizioneStato() {
        switch ($this->stato) {
            case "0":
                return "In attesa di approvazione";
            case "1":
                return "Approvato";
            case "2":
                return "Non approvato";
            case "3":
                return "Concluso";
            case "4":
                return "In attesa di validazione finale";
        }
    }

    public function save() {
        $db = ffDB_Sql::factory();

        $query_update = "
            UPDATE progetti_progetto
            SET oracle_erp = ".$db->toSql($this->oracle_erp).",
                note = ".$db->toSql($this->note).",
                stato = ".$db->toSql($this->stato).",
                data_approvazione = ".$db->toSql($this->data_approvazione).",
                numero_revisione = ".$db->toSql($this->numero_revisione).",
                time_modifica = ".$db->toSql($this->time_modifica).",
                record_attivo = ".$db->toSql($this->record_attivo)."
            WHERE ID = ".$db->toSql($this->id);
        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile aggiornare l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }

    public function saveChiudiProgetto() {
        $db = ffDB_Sql::factory();
        $query_update = "
            UPDATE progetti_progetto
            SET stato = ".$db->toSql($this->stato).",
                time_modifica = ".$db->toSql($this->time_modifica).",
                record_attivo = ".$db->toSql($this->record_attivo)."
            WHERE ID = ".$db->toSql($this->id);

        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile chiudere/riapire l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }

    public function saveValidazioneFinale() {
        $db = ffDB_Sql::factory();

        $query_update = "
            UPDATE progetti_progetto
            SET stato = ".$db->toSql($this->stato).",
                validazione_finale = ".$db->toSql($this->validazione_finale).",
                note_validazione_finale = ".$db->toSql($this->note_validazione_finale).",
                data_validazione_finale = ".$db->toSql($this->data_validazione_finale).",
                time_modifica = ".$db->toSql($this->time_modifica).",
                record_attivo = ".$db->toSql($this->record_attivo)."
            WHERE ID = ".$db->toSql($this->id);

        $result = $db->execute($query_update);
        if (!$result) {
            throw new Exception("Impossibile aggiornare l'oggetto ProgettiProgetto con ID='" . $this->id . "' nel DB");
        }
        return $result;
    }
}