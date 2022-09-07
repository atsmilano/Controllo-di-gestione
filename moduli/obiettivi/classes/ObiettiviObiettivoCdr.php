<?php
class ObiettiviObiettivoCdr extends Entity{
    protected static $tablename = "obiettivi_obiettivo_cdr";

    public function __construct($id = null) {
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                SELECT obiettivi_obiettivo_cdr.*
                FROM obiettivi_obiettivo_cdr
                WHERE obiettivi_obiettivo_cdr.ID = " . $db->toSql($id)
            ;
            $db->query($sql);
            if ($db->nextRecord()) {
                $this->id = $db->getField("ID", "Number", true);
                $this->id_obiettivo = $db->getField("ID_obiettivo", "Number", true);
                $this->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $this->codice_cdr_coreferenza = $db->getField("codice_cdr_coreferenza", "Text", true);
                if ($db->getField("ID_tipo_piano_cdr", "Number", true) == 0) {
                    $this->id_tipo_piano_cdr = null;
                } else {
                    $this->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
                }
                $this->peso = $db->getField("peso", "Text", true);
                $this->azioni = $db->getField("azioni", "Text", true);
                $this->id_parere_azioni = $db->getField("ID_parere_azioni", "Number", true);
                $this->note_azioni = $db->getField("note_azioni", "Text", true);
                //data_chiusura_modifiche
                $this->data_chiusura_modifiche = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_modifiche", "Date", true));
                $this->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));
                $this->data_eliminazione = CoreHelper::getDateValueFromDB($db->getField("data_eliminazione", "Date", true));
            } else
                throw new Exception("Impossibile creare l'oggetto ObiettivoCdr con ID = " . $id);
        }
    }
    
    public static function getAll($filters = array()) {
        $obiettivi_cdr = array();

        $db = ffDB_Sql::factory();
        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value)
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";

        $sql = "SELECT obiettivi_obiettivo_cdr.*
                FROM obiettivi_obiettivo_cdr
				" . $where;
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $obiettivo_cdr = new ObiettiviObiettivoCdr();
                $obiettivo_cdr->id = $db->getField("ID", "Number", true);
                $obiettivo_cdr->id_obiettivo = $db->getField("ID_obiettivo", "Number", true);
                $obiettivo_cdr->codice_cdr = $db->getField("codice_cdr", "Text", true);
                $obiettivo_cdr->codice_cdr_coreferenza = $db->getField("codice_cdr_coreferenza", "Text", true);
                $obiettivo_cdr->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
                $obiettivo_cdr->peso = $db->getField("peso", "Text", true);
                $obiettivo_cdr->azioni = $db->getField("azioni", "Text", true);
                $obiettivo_cdr->id_parere_azioni = $db->getField("ID_parere_azioni", "Number", true);
                $obiettivo_cdr->note_azioni = $db->getField("note_azioni", "Text", true);
                //data_chiusura_modifiche
                $obiettivo_cdr->data_chiusura_modifiche = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_modifiche", "Date", true));
                $obiettivo_cdr->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));
                $obiettivo_cdr->data_eliminazione = CoreHelper::getDateValueFromDB($db->getField("data_eliminazione", "Date", true));
                $obiettivi_cdr[] = $obiettivo_cdr;
            } while ($db->nextRecord());
        }
        return $obiettivi_cdr;
    }    
    
    //restituisce l'obiettivo_cdr (se non eliminato logicamente) assegnato a ID_obiettivo e codice_cdr
    public static function factoryFromObiettivoCdr(ObiettiviObiettivo $obiettivo, Cdr $cdr) {
        if ($obiettivo->data_eliminazione !== null) {
            return null;
        }
        $filters = array(
            "ID_obiettivo" => $obiettivo->id,
            "codice_cdr" => $cdr->codice,
        );
        foreach (ObiettiviObiettivoCdr::getAll($filters) as $obiettivo_cdr) {
            if ($obiettivo_cdr->data_eliminazione == null) {
                return $obiettivo_cdr;
            }
        }
        return null;
    }

    //salvataggio su db
    //aggiornamento
    public function save() {
        $db = ffDB_Sql::factory();
        //update
        if ($this->id == null) {
            //TODO ripristino del record eliminato logicamente in caso di corrispondenza invece che inserimento di un nuovo record
            $sql = "INSERT INTO ".self::$tablename." 
                    (
                        ID_obiettivo,				
                        ID_tipo_piano_cdr,
                        codice_cdr,
                        codice_cdr_coreferenza,
                        peso,
                        azioni,
                        ID_parere_azioni,
                        note_azioni,
                        data_chiusura_modifiche,
                        data_ultima_modifica,
                        data_eliminazione
                    ) 
                    VALUES (
                        " . (strlen($this->id_obiettivo) ? $db->toSql($this->id_obiettivo) : "null") . ",
                        " . (strlen($this->id_tipo_piano_cdr) ? $db->toSql($this->id_tipo_piano_cdr) : "null") . ",
                        " . (strlen($this->codice_cdr) ? $db->toSql($this->codice_cdr) : "null") . ",
                        " . (strlen($this->codice_cdr_coreferenza) ? $db->toSql($this->codice_cdr_coreferenza) : "null") . ",
                        " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",
                        " . (strlen($this->azioni) ? $db->toSql($this->azioni) : "null") . ",
                        " . (strlen($this->id_parere_azioni) ? $db->toSql($this->id_parere_azioni) : "null") . ",
                        " . (strlen($this->note_azioni) ? $db->toSql($this->note_azioni) : "null") . ",
                        " . (strlen($this->data_chiusura_modifiche) ? $db->toSql($this->data_chiusura_modifiche) : "null") . ",
                        " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                        " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                    );";
        } else {
            $sql = "UPDATE ".self::$tablename."
                    SET					
                        ID_obiettivo = " . (strlen($this->id_obiettivo) ? $db->toSql($this->id_obiettivo) : "null") . ",						
                        ID_tipo_piano_cdr = " . (strlen($this->id_tipo_piano_cdr) ? $db->toSql($this->id_tipo_piano_cdr) : "null") . ",
                        codice_cdr = " . (strlen($this->codice_cdr) ? $db->toSql($this->codice_cdr) : "null") . ",	
                        codice_cdr_coreferenza = " . (strlen($this->codice_cdr_coreferenza) ? $db->toSql($this->codice_cdr_coreferenza) : "null") . ",	
                        peso = " . (strlen($this->peso) ? $db->toSql($this->peso) : "null") . ",
                        azioni = " . (strlen($this->azioni) ? $db->toSql($this->azioni) : "null") . ",
                        ID_parere_azioni = " . (strlen($this->id_parere_azioni) ? $db->toSql($this->id_parere_azioni) : "null") . ",
                        note_azioni = " . (strlen($this->note_azioni) ? $db->toSql($this->note_azioni) : "null") . ",
                        data_chiusura_modifiche = " . (strlen($this->data_chiusura_modifiche) ? $db->toSql($this->data_chiusura_modifiche) : "null") . ",	
                        data_ultima_modifica = " . (strlen($this->data_ultima_modifica) ? $db->toSql($this->data_ultima_modifica) : "null") . ",
                        data_eliminazione = " . (strlen($this->data_eliminazione) ? $db->toSql($this->data_eliminazione) : "null") . "
                    WHERE 
                        ".self::$tablename.".ID = " . $db->toSql($this->id) . "
                    ";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile salvare l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
        }
    }

    //eliminazione logica
    //la propagazione avvien solo sulle entità collegate ma NON su obiettivi_cdr assegnati,
    //utilizzare $obiettivo_cdr->getDipendenze($date) ed eliminare logicamente
    public function logicalDelete($propagate = true) {
        //vengono estratti gli obiettivi cdr personale associati prima che l'obiettivo venga eliminato
        // Vengono estratte tutte le rendicontazioni collegate all'obiettivo cdr
        if ($propagate == true) {
            $obiettivi_cdr_personale_associati = $this->getObiettivoCdrPersonaleAssociati();
            $rendicontazioni = ObiettiviRendicontazione::getAll(array("ID_obiettivo_cdr" => $this->id));
        }
        $db = ffDB_Sql::factory();
        $sql = "
            UPDATE ".self::$tablename."
                SET data_eliminazione = " . $db->toSql(date("Y-m-d H:i:s")) . "
            WHERE ".self::$tablename.".ID = " . $db->toSql($this->id) . "
        ";

        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare logicamente l'oggetto ".static::class." con ID = " . $this->id . " nel DB");
        } else if ($propagate == true) {
            foreach ($obiettivi_cdr_personale_associati as $obiettivo_cdr_personale) {
                $obiettivo_cdr_personale->logicalDelete();
            }
            foreach ($rendicontazioni as $rendicontazione) {
                $rendicontazione->delete();
            }
        }
        
        return true;
    }

    //restituisce true se l'obiettivo risulta chiuso
    public function isChiuso() {
        if ($this->data_chiusura_modifiche == null || strtotime(date("Y-m-d")) < strtotime($this->data_chiusura_modifiche)) {
            return false;
        }
        return true;
    }

    //restituisce true se l'assegnazione è aziendale e l'assegnazione al cdr non è coreferenza
    //skip coreferenza impone che non venga valutato se l'obiettivo cdr sia di coreferenza ma    
    //viene considerato sempre e comunque aziendale se id_tipo piano = 0
    public function isObiettivoCdrAziendale($skip_coreferenza = false) {
        if ($this->id_tipo_piano_cdr == null) {
            if ($skip_coreferenza == true || strlen($this->codice_cdr_coreferenza) == 0) {
                return true;
            }   
        }
        return false;        
    }
    
    //restituisce true se l'obiettivo è trasversale e il CdR risulta referente
    public function isReferenteObiettivoTrasversale() {        
        if ($this->isObiettivoCdrAziendale() && count($this->getObiettiviCdrCoreferentiAssociati())){                    
            return true;
        } else {
            return false;
        }                
    }

    //restituisce true se l'assegnazione all'obiettivo è una coreferenza
    public function isCoreferenza() {
        //viene verificato che l'assegnazione sia una coreferenza
        if (strlen($this->codice_cdr_coreferenza) > 0) {
            return true;
        } else if ($this->id_tipo_piano_cdr == 0) {
            return false;
        }
        //altrimenti viene verificato se l'assegnazione sul ramo gerarchico non sia una coreferenza
        else {
            $obiettivo = new ObiettiviObiettivo($this->id_obiettivo);
            $anno = new AnnoBudget($obiettivo->id_anno_budget);
            $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
            $date = $data_riferimento->format("Y-m-d");
            $tipo_piano = Cdr::getTipoPianoPriorita($this->codice_cdr, $date);
            $piano_cdr = Pianocdr::getAttivoInData($tipo_piano, $date);
            $cdr = Cdr::factoryFromCodice($this->codice_cdr, $piano_cdr);
            //i cdr sono ordinati per livello gerarchico inverso partendo dal cdr che chiama il metodo
            foreach ($cdr->getPadriRamo() as $cdr_padre_ramo) {
                //la selezione restituisce al più un elemento
                $obiettivo_cdr_padre = ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_padre_ramo);               
                if ($obiettivo_cdr_padre !== null) {
                    if (!strlen($obiettivo_cdr_padre->codice_cdr_coreferenza) > 0 && $obiettivo_cdr_padre->id_tipo_piano_cdr == 0) {
                        return false;
                    }
                    if(strlen($obiettivo_cdr_padre->codice_cdr_coreferenza) > 0 && $obiettivo_cdr_padre->codice_cdr_coreferenza !== $this->codice_cdr) {
                        return true;
                    }
                }                                                     
            }
        }
        return false;
    }

    //****************
    //entità collegate
    public function getObiettivoCdrPersonaleAssociati($matricola = null) {
        $ob_cdr_personale_associati = array();
        $filters = array("ID_obiettivo_cdr" => $this->id);
        if ($matricola !== null) {
            $filters["matricola_personale"] = $matricola;
        }
        foreach (ObiettiviObiettivoCdrPersonale::getAll($filters) AS $obiettivo_cdr_associato) {
            if ($obiettivo_cdr_associato->data_eliminazione == null) {
                $ob_cdr_personale_associati[] = $obiettivo_cdr_associato;
            }
        }
        return $ob_cdr_personale_associati;
    }

    //funzione che restituisce la rendicontazione del periodo per l'obiettivo_cdr, restituisce null se non presente
    public function getRendicontazionePeriodo(ObiettiviPeriodoRendicontazione $periodo) {
        $filters = array(
            "ID_obiettivo_cdr" => $this->id,
            "ID_periodo_rendicontazione" => $periodo->id,
        );
        $rendicontazione = ObiettiviRendicontazione::getAll($filters);
        if (count($rendicontazione) > 0) {
            return $rendicontazione[0];
        } else {
            return null;
        }
    }

    //restituisce l'obiettivo_cdr di riferimento
    //nel caso in cui sia valorizato check_solo_padre a true in caso di coreferenza
    //non si risale il ramo gerarchico in cerca dell'obiettivo_cdr origine
    //ma viene restituito l'obiettivo_Cdr di assegnazione (e non di origine)
    //viene inoltre imposto che non venga valutato se l'obiettivo cdr sia di coreferenza ma
    //viene considerato sempre e comunque aziendale se id_tipo piano = 0
    public function getObiettivoCdrPadre($check_solo_padre=false) {
        $cdr_padre_obiettivo = null;        
        if ($this->isObiettivoCdrAziendale($check_solo_padre)) {
            return null;
        }
        $obiettivo = new ObiettiviObiettivo($this->id_obiettivo);
        $anno = new AnnoBudget($obiettivo->id_anno_budget);
        $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
        $date = $data_riferimento->format("Y-m-d");
        if ($this->id_tipo_piano_cdr != null) {
            $tipo_piano = new TipoPianoCdr($this->id_tipo_piano_cdr);
        } else {
            $tipo_piano = Cdr::getTipoPianoPriorita($this->codice_cdr, $date);
        }
        $piano_cdr = Pianocdr::getAttivoInData($tipo_piano, $date);
        //altrimenti il cdr è il padre gerarchico sul tipo di piano selezionato
        try {
            $cdr = Cdr::factoryFromCodice($this->codice_cdr, $piano_cdr);
            //se l'assegnazione è una coreferenza il padre è il cdr di coreferenza specificato oppure il cdr di corefernza specificato su unoi dei padri
            if ($this->isCoreferenza()) {
                if (strlen($this->codice_cdr_coreferenza) > 0) {
                    $cdr_padre_obiettivo = Cdr::factoryFromCodice($this->codice_cdr_coreferenza, $piano_cdr);
                } 
                else if ($check_solo_padre == true) {
                    $cdr_padre_obiettivo = new Cdr($cdr->id_padre);                    
                }
                else {
                    $cdr = Cdr::factoryFromCodice($this->codice_cdr, $piano_cdr);
                    foreach ($cdr->getPadriRamo() as $cdr_padre_ramo) {
                        //la selezione restituisce al più un elemento
                        $obiettivo_cdr_padre = ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_padre_ramo);
                        if ($obiettivo_cdr_padre !== null && strlen($obiettivo_cdr_padre->codice_cdr_coreferenza) > 0) {
                            $cdr_padre_obiettivo = Cdr::factoryFromCodice($obiettivo_cdr_padre->codice_cdr_coreferenza, $piano_cdr);
                            break;
                        }                        
                    }
                }
            } else {
                $cdr_padre_obiettivo = new Cdr($cdr->id_padre);
            }
        } catch (Exception $ex) {
            ffErrorHandler::raise("Errore: assegnazione senza obiettivo_cdr padre valido.");
        }
        if ($cdr_padre_obiettivo !== null) {
            return ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_padre_obiettivo);
        } else {
            return null;
        }
    }

    //recupera l'obiettivo_cdr padre assegnato dalla direzione    
    public function getObiettivoCdrAziendale() {
        //se l'obiettivo è stato assegnato dalla direzione viene restituito
        if ($this->isObiettivoCdrAziendale()) {
            return $this;
        }
        //se l'obiettivo corrente non è aziendale si scala sul padre (obiettivo-cdr) fincheè non si trova uin obiettivo definito come aziendale
        $obiettivo_cdr_padre = $this->getObiettivoCdrPadre();
        if ($obiettivo_cdr_padre !== null) {
            do {
                if ($obiettivo_cdr_padre->isObiettivoCdrAziendale()) {
                    return $obiettivo_cdr_padre;
                }
                $obiettivo_cdr_padre = $obiettivo_cdr_padre->getObiettivoCdrPadre();
            } while ($obiettivo_cdr_padre !== null);
        }
        ffErrorHandler::raise("Errore: non esiste un'assegnazione aziendale dell'obiettivo nei cdr padri del ramo gerarchico.");
    }

    //coreferenti associati all'obiettivo-cdr
    public function getObiettiviCdrCoreferentiAssociati() {
        $ob_coreferenti_associati = array();
        $filters = array("ID_obiettivo" => $this->id_obiettivo,
            "codice_cdr_coreferenza" => $this->codice_cdr,
        );
        foreach (ObiettiviObiettivoCdr::getAll($filters) AS $obiettivo_coreferente) {
            if ($obiettivo_coreferente->data_eliminazione == null) {
                $ob_coreferenti_associati[] = $obiettivo_coreferente;
            }
        }
        return $ob_coreferenti_associati;
    }

    //recupera un array con tutte gli obiettivi cdr assegnati o dipendenti da quello selezionato
    public function getDipendenze(Datetime $date, $dipendenze = array()) {
        $obiettivo = new ObiettiviObiettivo($this->id_obiettivo);
        //vengono recuperati tutti gli eventuali obiettivi di coreferenza a quello selezionato e accodati per la dipendenza gerarchica
        $obiettivo_cdr_referente_coreferenti = array();
        $coreferenti_associati = $this->getObiettiviCdrCoreferentiAssociati();
        $dipendenze = array_merge($dipendenze, $coreferenti_associati);
        $obiettivo_cdr_referente_coreferenti = array_merge($obiettivo_cdr_referente_coreferenti, $coreferenti_associati);
        $obiettivo_cdr_referente_coreferenti[] = $this;

        //per ognuno degli obiettivi_cdr individuati viene recuperata la gerarchia per recuperare tutte le assegnazioni      
        foreach ($obiettivo_cdr_referente_coreferenti as $obiettivo_cdr) {
            //recupero della gerarchia del cdr
            if ($obiettivo_cdr->id_tipo_piano_cdr != 0) {
                $tipo_piano_cdr = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
            }
            //se l'obiettivo è aziendale viene considerato come tipologia il piano di priorità massima in cui il codice_cdr è presente
            else {
                $tipo_piano_cdr = Cdr::getTipoPianoPriorita($obiettivo_cdr->codice_cdr, $date->format("Y-m-d"));
            }
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date->format("Y-m-d"));
            $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
            foreach ($cdr->getFigli() as $cdr_figlio) {
                $obiettivo_cdr_figlio = ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_figlio);
                if ($obiettivo_cdr_figlio !== null && $obiettivo_cdr_figlio->id_tipo_piano_cdr != 0) {
                    $dipendenze[] = $obiettivo_cdr_figlio;
                    $dipendenze = $obiettivo_cdr_figlio->getDipendenze($date, $dipendenze);
                }
            }
        }
        return $dipendenze;
    }

    //valori target indicatore associati all'obiettivo-cdr
    public function getValoriTargetIndicatoreAssociati(IndicatoriIndicatore $indicatore) {
        $valori_target_indicatore_associati = array();
        $filters = array("ID_obiettivo_cdr" => $this->id, "ID_indicatore" => $indicatore->id, "codice_cdr" => $this->codice_cdr);
        foreach (IndicatoriValoreTargetObiettivoCdr::getAll($filters) AS $valore_target_indicatore_obiettivo_cdr) {
            $valori_target_indicatore_associati[] = $valore_target_indicatore_obiettivo_cdr;
        }
        return $valori_target_indicatore_associati;
    }

    //metodo per la visualizzazione delle informazioni del padre dell'obiettivo_cdr (padre dell'obiettivo) in html alla data selezionata
    public function showHtmlInfoPadre(DateTime $date) {
        $html = "";
        //visualizzazione dettagli per i cdr padri sul ramo gerarchico                
        //se ID_tipo_piano è nullo significa che l'obiettivo è stato assegnato dalla direzione e quindi non ci sono azioni definite        
        if ($this->id_tipo_piano_cdr != 0 || !$this->isCoreferenza()) {
            $tipo_piano_cdr = new TipoPianoCdr($this->id_tipo_piano_cdr);
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date->format("Y-m-d"));
            $cdr = Cdr::factoryFromCodice($this->codice_cdr, $piano_cdr);
            if ($cdr !== null) {
                $obiettivo_cdr_padre = $this->getObiettivoCdrPadre();
                $obiettivo = new ObiettiviObiettivo($obiettivo_cdr_padre->id_obiettivo);
                $anno = new AnnoBudget($obiettivo->id_anno_budget);
                $cdr_padre = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_padre->codice_cdr, $date);
                $tot_pesi_obiettivi_cdr_padre = $cdr_padre->getPesoTotaleObiettivi($anno);
                $peso_perc = CoreHelper::percentuale($obiettivo_cdr_padre->peso, $tot_pesi_obiettivi_cdr_padre);
                $html .= "  <div class='form-group clearfix padding'>
                                <label>Azioni CDR '" . $cdr_padre->codice . " - " . $cdr_padre->descrizione . "' (" . $tipo_piano_cdr->descrizione . ")"
                    . "(peso obiettivo = " . $obiettivo_cdr_padre->peso . "/" . $tot_pesi_obiettivi_cdr_padre . " => " . number_format($peso_perc, 2) . "%)</label>
                                <span class='form-control readonly'>" . $obiettivo_cdr_padre->azioni . "</span>
                            </div>";
            }
        }
        return $html;
    }

    //metodo per la visualizzazione delle informazioni dell'obiettivo_cdr in html alla data selezionata
    public function showHtmlInfo(DateTime $date) {
        $html = "<div class='form-group clearfix padding'>
                    <label>Piano organizzativo assegnazione</label>";

        if ($this->id_tipo_piano_cdr !== null) {
            $tipo_piano_cdr = new TipoPianoCdr($this->id_tipo_piano_cdr);
            $html .= "<span class='form-control readonly'>" . $tipo_piano_cdr->descrizione . "</span>";
        } else {
            $html .= "<span class='form-control readonly'>Assegnazione aziendale</span>";
        }

        $anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($this->codice_cdr, $date);
        $obiettivo = new ObiettiviObiettivo($this->id_obiettivo);
        $anno = new AnnoBudget($obiettivo->id_anno_budget);

        $tot_peso_obiettivi = $anagrafica_cdr->getPesoTotaleObiettivi($anno);
        $html .= "  </div>";

        if (!$this->isCoreferenza()) {
            $azioni = $this->azioni;
            $desc_cdr_padre = "";
        } else {
            $obiettivo_cdr_padre = $this->getObiettivoCdrAziendale();
            $azioni = $obiettivo_cdr_padre->azioni;
            if ($obiettivo_cdr_padre->isObiettivoCdrAziendale()) {
                $tipo_piano = TipoPianoCdr::getPrioritaMassima();
            } else {
                $tipo_piano = new TipoPianoCdr($this->id_tipo_piano_cdr);
            }
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
            $cdr_padre_obiettivo = Cdr::factoryFromCodice($obiettivo_cdr_padre->codice_cdr, $piano_cdr);
            $desc_cdr_padre = ": '" . $cdr_padre_obiettivo->codice . " - " . $cdr_padre_obiettivo->descrizione . " (referente obiettivo trasversale)'";
        }
        $html .= "
                    <div class='form-group clearfix padding'>
                        <label>Azioni CDR" . $desc_cdr_padre . "</label>
                        <span class='form-control readonly'>" . $azioni . "</span>
                    </div>
                    <div class='form-group clearfix padding'>
                        <label>Peso obiettivo / peso totale obiettivi CDR</label>
                        <span class='form-control readonly'>" . $this->peso . "/"
            . $tot_peso_obiettivi . " (" . number_format(CoreHelper::percentuale($this->peso, $tot_peso_obiettivi), 2) . "%)</span>
                    </div>
                    <div class='form-group clearfix padding'>
                        <label>Dipendenti associati all&acute;obiettivo</label>
                        ";

        $obiettivi_cdr_personale_associati = $this->getObiettivoCdrPersonaleAssociati();
        if (count($obiettivi_cdr_personale_associati) > 0) {
            $html .= "<span class='form-control readonly'><ul>";
            foreach ($obiettivi_cdr_personale_associati as $obiettivo_cdr_personale_associato) {
                $dipendente = PersonaleObiettivi::factoryFromMatricola($obiettivo_cdr_personale_associato->matricola_personale);
                $tot_obiettivi = $dipendente->getPesoTotaleObiettivi($anno);
                $html .= "<li>" . $dipendente->nome . " " . $dipendente->cognome . " (" . $dipendente->matricola .
                    ") - peso: " . $obiettivo_cdr_personale_associato->peso . "/" . $tot_obiettivi . " (" .
                    number_format(CoreHelper::percentuale($obiettivo_cdr_personale_associato->peso, $tot_obiettivi),2) . "%)</li>";
            }
            $html .= "</ul></span>";
        } else {
            $html .= "<span class='form-control readonly'>Nessun dipendente associato</span>";
        }
        $html .= "</div>";

        return $html;
    }

    public function riaperturaObiettivoCdr() {
        $this->data_chiusura_modifiche = null;
        $this->save();
    }
}