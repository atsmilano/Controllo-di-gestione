<?php
class Cdr extends Entity{
    public $id;
    public $id_anagrafica_cdr;
    public $id_piano_cdr;
    public $id_padre;
    //recuperati da anagrafica cdr    
    public $codice;
    public $descrizione;
    public $abbreviazione;
    public $id_tipo_cdr;
    public $useSql;

    public function __construct($id = null, $useSql = false) {
        if ($id !== null) {
            if (!$this->useSql) {
                $this->useSql = true;
                foreach (TableCdr::getInstance()->getPianiCdr() as $piani_cdr) {
                    if (array_key_exists($id, $piani_cdr['cdr'])) {
                        $this->useSql = false;
                        $this->id = $piani_cdr['cdr'][$id]->id;
                        $this->id_anagrafica_cdr = $piani_cdr['cdr'][$id]->id_anagrafica_cdr;
                        $this->id_piano_cdr = $piani_cdr['cdr'][$id]->id_piano_cdr;
                        $this->id_padre = $piani_cdr['cdr'][$id]->id_padre;
                        $this->codice = $piani_cdr['cdr'][$id]->codice;
                        $this->descrizione = $piani_cdr['cdr'][$id]->descrizione;
                        $this->abbreviazione = $piani_cdr['cdr'][$id]->abbreviazione;
                        $this->id_tipo_cdr = $piani_cdr['cdr'][$id]->id_tipo_cdr;

                        break;
                    }
                }
            }

            if ($this->useSql) {
                $db = ffDb_Sql::factory();

                $sql = "
                    SELECT cdr.*,
                        anagrafica_cdr.codice,
                        anagrafica_cdr.descrizione,
                        anagrafica_cdr.abbreviazione,
                        anagrafica_cdr.ID_tipo_cdr
                    FROM cdr
                        INNER JOIN anagrafica_cdr ON cdr.ID_anagrafica_cdr = anagrafica_cdr.ID
                    WHERE cdr.ID = " . $db->toSql($id)
                ;

                $db->query($sql);
                if ($db->nextRecord()) {
                    $this->id = $db->getField("ID", "Number", true);
                    $this->id_anagrafica_cdr = $db->getField("ID_anagrafica_cdr", "Number", true);

                    $this->id_piano_cdr = $db->getField("ID_piano_cdr", "Number", true);
                    $this->id_padre = $db->getField("ID_padre", "Number", true);
                    //$this->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                    //recuperati da anagrafica
                    $this->codice = $db->getField("codice", "Text", true);
                    $this->descrizione = $db->getField("descrizione", "Text", true);
                    $this->abbreviazione = $db->getField("abbreviazione", "Text", true);
                    $this->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Number", true);

                    $this->useSql = $useSql;
                } else {
                    throw new Exception("Impossibile creare l'oggetto Cdr con ID = " . $id);
                }
            }
        }
    }

    //viene istanziato l'oggetto cdr da codice e piano cdr di riferimento
    public static function factoryFromCodice($codice, PianoCdr $piano_cdr) {
        //lavaoriamo sul piano
        foreach (TableCdr::getInstance()->getPianiCdr() as $piani_cdr) {
            if ($piani_cdr['piano_cdr']->id == $piano_cdr->id) {
                foreach ($piani_cdr['cdr'] as $cdr_global) {
                    if ($cdr_global->codice == $codice && $cdr_global->id_piano_cdr == $piano_cdr->id) {
                        return $cdr_global;
                    }
                }
            }
        }

        $db = ffDb_Sql::factory();

        $sql = "
            SELECT cdr.*, 
                anagrafica_cdr.codice,
                anagrafica_cdr.descrizione,
                anagrafica_cdr.abbreviazione,
                anagrafica_cdr.ID_tipo_cdr
            FROM cdr
                INNER JOIN anagrafica_cdr ON cdr.ID_anagrafica_cdr = anagrafica_cdr.ID
            WHERE anagrafica_cdr.codice = " . $db->toSql($codice)
        ;

        $db->query($sql);

        if ($db->nextRecord()) {
            do {
                if ($db->getField("ID_piano_cdr", "Number", true) == $piano_cdr->id) {
                    $cdr = new Cdr();
                    $cdr->id = $db->getField("ID", "Number", true);
                    $cdr->id_anagrafica_cdr = $db->getField("ID_anagrafica_cdr", "Number", true);
                    $cdr->id_piano_cdr = $db->getField("ID_piano_cdr", "Number", true);
                    $cdr->id_padre = $db->getField("ID_padre", "Number", true);

                    //$cdr->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                    //recuperati da anagrafica
                    $cdr->codice = $db->getField("codice", "Text", true);
                    $cdr->descrizione = $db->getField("descrizione", "Text", true);
                    $cdr->abbreviazione = $db->getField("abbreviazione", "Text", true);
                    $cdr->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Number", true);

                    return $cdr;
                }
            } while ($db->nextRecord());
        }
        throw new Exception("Impossibile creare l'oggetto Cdr con codice = " . $codice . " per il piano ID = " . $piano_cdr->id);
    }

    //estrazione di tutti i cdr	
    public static function getAll($filters = array(), $useSql = true) {
        $all_cdr = array();
        $db = ffDb_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach ($filters as $field => $value) {
            $where .= "AND " . $field . "=" . $db->toSql($value) . " ";
        }

        $sql = "
            SELECT cdr.*,
                anagrafica_cdr.codice,
                anagrafica_cdr.descrizione,
                anagrafica_cdr.abbreviazione,
                anagrafica_cdr.ID_tipo_cdr
            FROM cdr
                INNER JOIN anagrafica_cdr ON cdr.ID_anagrafica_cdr = anagrafica_cdr.ID
            " . $where . "
        ";

        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdr = new Cdr();
                $cdr->useSql = $useSql;
                $cdr->id = $db->getField("ID", "Number", true);
                $cdr->id_anagrafica_cdr = $db->getField("ID_anagrafica_cdr", "Number", true);
                $cdr->id_piano_cdr = $db->getField("ID_piano_cdr", "Number", true);
                $cdr->id_padre = $db->getField("ID_padre", "Number", true);
                //$cdr->matricola_responsabile = $db->getField("matricola_responsabile", "Text", true);
                //recuperati da anagrafica
                $cdr->codice = $db->getField("codice", "Text", true);
                $cdr->descrizione = $db->getField("descrizione", "Text", true);
                $cdr->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $cdr->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Number", true);

                $all_cdr[] = $cdr;
            } while ($db->nextRecord());
        }

        return $all_cdr;
    }

    //estrazione di tutti gli oggetti cdr con codice dei piani cdr attivi in una data specifica   
    //$date è una stringa
    public static function getCdrPianiFromCodice($codice_cdr, $date) {        
        $cdr_piani = array();
        foreach (TipoPianoCdr::getAll() as $tipo_piano) {            
            try {
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date);
                $cdr_piano = Cdr::factoryFromCodice($codice_cdr, $piano_cdr);
                $cdr_piani[] = array(
                            "tipo_piano_cdr" => $tipo_piano,
                            "cdr" => $cdr_piano,
                        );
            } catch (Exception $ex) {
                
            }
        }
        return $cdr_piani;        
    }

    //salvataggio su db
    //aggiornamento
    public function save() {
        $db = ffDB_Sql::factory();
        if ($this->id != null) {
            $sql = "
                    UPDATE 
                        cdr
                    SET							
                        ID_anagrafica_cdr = " . (strlen($this->id_anagrafica_cdr) ? $db->toSql($this->id_anagrafica_cdr) : "null") . ",
                        ID_piano_cdr = " . (strlen($this->id_piano_cdr) ? $db->toSql($this->id_piano_cdr) : "null") . ",
                        ID_padre = " . (strlen($this->id_padre) ? $db->toSql($this->id_padre) : "null") . "
                    WHERE 
                        cdr.ID = " . $db->toSql($this->id) . "
                ";
            if (!$db->execute($sql)) {
                throw new Exception("Impossibile salvare l'oggetto con id = " . $this->id . "Cdr nel DB");
            }
        } else {
            $sql = "
                    INSERT INTO
                        cdr
                        (ID_anagrafica_cdr, ID_piano_cdr, ID_padre)
                    VALUES
                        (" . (strlen($this->id_anagrafica_cdr) ? $db->toSql($this->id_anagrafica_cdr) : "null") . ",
                         " . (strlen($this->id_piano_cdr) ? $db->toSql($this->id_piano_cdr) : "null") . ",
                         " . (strlen($this->id_padre) ? $db->toSql($this->id_padre) : "null") . ")";

            if (!$db->execute($sql)) {
                throw new Exception("Impossibile aggiungere CdR al PianoCdr con ID='".$this->id_piano_cdr."' nel DB");
            } else {
                return $db->getInsertID();
            }
        }
    }

    //restituisce il tipo di piano con priorità più alta al quale il cdr è assegnato in una data specifica
    public static function getTipoPianoPriorita($codice_cdr, $date) {
        $piano_priorita = 0;
        $tipo_piano_piano_cdr = null;
        foreach (Cdr::getCdrPianiFromCodice($codice_cdr, $date) as $cdr_piano) {
            if ($cdr_piano["tipo_piano_cdr"]->priorita > $piano_priorita) {
                $piano_priorita = $cdr_piano["tipo_piano_cdr"]->priorita;
                $tipo_piano_piano_cdr = $cdr_piano["tipo_piano_cdr"];
            }
        }
        return $tipo_piano_piano_cdr;
    }

    //restituisce un array con i figli di primo livello del cdr
    public function getFigli() {
        $cdr_figli = array();

        $db = ffDb_Sql::factory();
        if ($this->useSql) {
            foreach (Cdr::getAll(array("ID_padre"=>$this->id), $this->useSql) as $cdr_figlio) {
                    $cdr_figli[] = $cdr_figlio;
            }                        
        } else {
            foreach (TableCdr::getInstance()->getPianiCdr() as $piani_cdr) {
                foreach ($piani_cdr['cdr'] as $cdr_global) {
                    if ($cdr_global->id_padre == $this->id) {
                        $cdr_figli[] = $cdr_global;
                    }
                }
            }
        }

        return $cdr_figli;
    }
    
    //restituisce un array di oggetti Cdr che sono padri del cdr sullo stesso ramo , ordinati dal più basso al più alto livello  
    public function getPadriRamo() {
        $result = array();
        if ($this->useSql) {
            $cdr = new Cdr($this->id);            
            while ($cdr->id_padre !== 0) {
                $cdr = new Cdr($cdr->id_padre);
                $result[] = $cdr;
            }
        } else {
            foreach (TableCdr::getInstance()->getPianiCdr() as $piani_cdr) {
                if (isset($piani_cdr['cdr'][$this->id])) {
                    $id_padre = $this->id;
                    while ($piani_cdr['cdr'][$id_padre]->id_padre !== 0) {
                        $id_padre = $piani_cdr['cdr'][$id_padre]->id_padre;
                        $result[] = $piani_cdr['cdr'][$id_padre];
                    }
                }
            }
        }
        return $result;
    }

    //restituisce un numero intero che rappresenta il livello gerarchico del cdr (0 livello radice)
    public function getLivelloGerarchico() {
        $livello = 0;
        if ($this->id_padre !== 0) {
            $cdr_padre = new Cdr($this->id_padre);
            $livello++;
            while ($cdr_padre->id_padre !== 0) {
                $cdr_padre = new Cdr($cdr_padre->id_padre);
                $livello++;
            }
        }
        return $livello;
    }

    //restituisce un array multidimensionale con i cdr della gerarchia di cui il cdr corrente è padre ed i livello dei cdr su questa gerarchia
    //l'ultimo parametro è un array contenente i cdr che è possibile escludere dalla gerarchia restituita (con i relativi figli)
    //il codice del cdr sul quale viene chiamato il metodo non verrà escluso anche nel caso in cui compaia in elenco
    public function getGerarchia($cdr = null, $livello = 0, $gerarchia = null, $codici_cdr_figli_esclusi = array()) {
        if ($cdr == null) {
            $cdr = $this;
        }
        //variabili globali
        global $result;
        if ($gerarchia == null)
            $result = array();
        //viene valorizzato l'elemento attuale dell'array da restituire		
        $result[] = array("cdr" => $cdr, "livello" => $livello);

        //vengono recuperati i figli del cdr considerato e per ognuno recuperata la gerarchia				
        $livello++;
        foreach ($cdr->getFigli() as $figlio) {
            $escluso = false;
            foreach ($codici_cdr_figli_esclusi as $codice_cdr_escluso) {
                if ($codice_cdr_escluso == $figlio->codice) {
                    $escluso = true;
                    break;
                }
            }
            if ($escluso == false) {
                $this->getGerarchia($figlio, $livello, $result, $codici_cdr_figli_esclusi);
            }
        }
        return $result;
    }

    //restituisce tutti i rami gerarchici individuati tramite un array di cdr passato come parametro
    //vengono restituiti, per ogni codice cdr passato nell'array, tutti i cdr afferenti al ramo gerarchico di quel cdr
    //che non costituiscono a loro volta un elemento dell'array.
    //viene restituito un array con i cdr afferenti al ramo (array codice e livello rispetto al cdr considerato) compreso il cdr passato come elemento
    //
    //[elenco]array (
    //       [ramo]array(   
    //            [cdr]  array ("cdr"=> cdr, "livello" =>  livello),
    //                  )
    //      )
    public static function getRamiGerarchiciElencoCdr($elenco_codici = array(), PianoCdr $piano_cdr) {
        $rami_gerarchici = array();
        $useSql = true;
        //per ogni codice trovato viene restituita la gerarchia (con esclusione degli altri codici nell'array)
        //controllo se piano passato è in oggetto globale        
        foreach (TableCdr::getInstance()->getPianiCdr() as $piani_cdr) {
            if ($piani_cdr['piano_cdr']->id == $piano_cdr->id) {
                $useSql = false;
            }
        }

        foreach ($elenco_codici as $codice_cdr) {
            //viene usato il try per eveitare che un codice errato in tabella generi errore bloccante
            try {
                //viene istanziato il cdr dal codice e dal piano
                $cdr = Cdr::factoryFromCodice($codice_cdr, $piano_cdr, $useSql);
                //viene recuperata la gerarchia del cdr con esclusione degli altri codici dell'array
                //il codice elemento corrente dell'array non verrà escluso                      
                $rami_gerarchici[] = $cdr->getGerarchia($cdr, 0, null, $elenco_codici);
            } catch (Exception $ex) {
                
            }
        }
        return $rami_gerarchici;
    }

    //restituisce un array con i cdc appartenenti al piano
    //array vuoto se nessun cdc per il piano	
    public function getCdc() {
        return Cdc::getAll(array("ID_cdr" => $this->id));
    }

    //restituisce il responsabile del cdr
    public function getResponsabile(DateTime $data_riferimento) {
        //vengono ciclati tutti i cdr dall'attuale ai padri finchè non viene traovato un responsabile. Il controllo nel while serve ad evitare cicli infiniti
        //in caso non venga trovato nessun padre, eventualità che non dovrebbe verificarsi        
        $cdr = $this;
        while ($cdr !== null) {
            try {
                $responsabile_cdr = ResponsabileCdr::factoryFromCodiceCdr($cdr->codice, $data_riferimento);
                return $responsabile_cdr;                
            }
            //se non si riesce ad instanziare un dipendente con matricola personale si passa al padre
            catch (Exception $ex) {
                //se non si riesce ad istanziare il padre significa che si sta cercando di accedere al padre dell'elemento radice (eventualità teoricamente impossibile)
                try {
                    $cdr = new Cdr($cdr->id_padre);
                } catch (Exception $ex) {
                    $cdr = null;
                }
            }
        }
    }

    //Restituisce true o false dipendentemente dal fatto che la matricola passata abbia visibilità o meno sul cdr    
    public function getIfResponsabileRamo(Personale $personale, DateTime $date) {
        //viene aggiunto il privilegio all'utente nel caso sia un responsabile di un cdr superiore sul ramo gerarchico
        foreach ($this->getPadriRamo() as $padre_ramo) {
            $resp_padre_ramo = $padre_ramo->getResponsabile($date);            
            if ($resp_padre_ramo->matricola_responsabile == $personale->matricola) {
                return true;
            }
        }
        return false;
    }

    //privilegi sul cdr di un determinato dipendente in una determinata data
    public function getPrivileges(Personale $personale, DateTime $date) {
        $privileges = array();
        $resp_cdr = $this->getResponsabile($date);
        //viene verificato che il cdr sia di responsabilità dell'utente (nel caso l'utente non abbia privilegi di visualizzazione su tutto)			
        //viene aggiunto il privilegio all'utente nel caso sia responsabile del cdr selezionato
        //in ogni caso se l'utente è responsabile del cdr vengono forniti i privilegi			
        if ($resp_cdr->matricola_responsabile == $personale->matricola) {
            $privileges[] = "resp_cdr_selezionato";
        }
        //se non ci si trova al cdr radice del piano	
        if ($this->id_padre!==0) {					
            //viene aggiunto il privilegio all'utente nel caso sia responsabile del padre del cdr selezionato
            $cdr_padre = new Cdr($this->id_padre);
            $resp_cdr_padre = $cdr_padre->getResponsabile($date);
            //viene nel caso attribuito anche il privilegio del responsabile ramo gerarchico per evitare ulteriori controlli
            if ($resp_cdr_padre->matricola_responsabile == $personale->matricola) {
                $privileges[] = "resp_padre_cdr_selezionato";
                $privileges[] = "resp_padre_ramo_cdr_selezionato";
            }
            //se non è padre viene verificato che sia responsabilità sul ramo gerarchico              
            else if($this->getIfResponsabileRamo($personale, $date)){
                $privileges[] = "resp_padre_ramo_cdr_selezionato";
            }				
        }
        //se il cdr non ha padre significa che ci si trova al cdr radice
        //vengono forniti così tutti i iprivilegi di padre e di ramo gerarchico in caso l'utente sia responsabile
        else {
            foreach($privileges as $privilege) {            
                if ($privilege == "resp_cdr_selezionato") {
                    $privileges[] = "resp_padre_cdr_selezionato";
                    $privileges[] = "resp_padre_ramo_cdr_selezionato";
                    break;
                }
            }
        }
        return $privileges;
    }    
    
    //restituisce un array con i dipendenti del cdr alla data
    public function getPersonaleCdcAfferentiInData(DateTime $date) {
        $personale_cdc_afferenti = array();
        foreach ($this->getCdc() as $cdc) {
            foreach ($cdc->getPersonaleCdcInData($date) as $cdc_personale) {
                $personale_cdc_afferenti[] = $cdc_personale;
            }
        }
        return $personale_cdc_afferenti;
    }

    //resituisce il primo responsabile su cdr padre differente dal responsabile del cdr
    //null se non si riesce ad istanziare il responsabile superiore (es cdr radice)
    //$primo_cdr_padre determina se forzare la restituzione del responsabile del primo cdr padre
    public function getPrimoResponsabilePadre(DateTime $date, $primo_cdr_padre = false) {
        $cdr = $this;
        $return = null;
        //se non ci si trova all'elemento radice
        if ($cdr->id_padre !== 0) {
            $cdr_padre = new Cdr($cdr->id_padre, $this->useSql);
            $responsabile_cdr = $cdr->getResponsabile($date);
            $responsabile_cdr_padre = $cdr_padre->getResponsabile($date);
            if ($primo_cdr_padre == true) {
                return $responsabile_cdr_padre;
            }
            //viene considerato come responsabile il primo resopnsabile di cdr padre non identico al responsabile del cdr figlio
            while ((($responsabile_cdr->matricola_responsabile == $responsabile_cdr_padre->matricola_responsabile) || $responsabile_cdr_padre->matricola_responsabile == null) && $cdr->id_padre !== 0) {
                $cdr = $cdr_padre;
                $cdr_padre = new Cdr($cdr->id_padre, $this->useSql);
                $responsabile_cdr_padre = $cdr_padre->getResponsabile($date);
            }
            //viene selezionato il responsabile del cdr padre solamente nel caso in cui sia stato trovato con l'ultima iterazione
            //nel caso in cui non si riesca ad istanziare l'oggetto Personale tramite la matricola del responsabile viene restituito null
            $return = $responsabile_cdr_padre;
        }
        return $return;
    }

    public function delete() {
        $db = ffDB_Sql::factory();
        $sql = "DELETE FROM cdr WHERE cdr.ID = " . $db->toSql($this->id);
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile eliminare l'oggetto Cdr con ID = " . $id . " nel DB");
        }
    }
}
