<?php
class RURichiesta extends Entity
{
    protected static $tablename = "ru_richiesta";
    protected static $stati_richiesta = array(
        //anomalia
        array("ID" => 0,
            "descrizione" => "Anomalia",
            "esito" => "ko",
        ),
        //fase richiesta (modificare controlli in dettaglio_richiesta in caso di modifica)                                             
        array("ID" => 1,
            "descrizione" => "Richiesta in fase di compilazione",
            "esito" => "",
        ),
        //fase approvazione CdR intermedi                      
        array("ID" => 2,
            "descrizione" => "Richiesta in fase di approvazione dal CdR padre",
            "esito" => "",
        ),
        //fase approvazione CdR strategico                      
        array("ID" => 3,
            "descrizione" => "Richiesta in fase di approvazione dal CdR di programmazione Strategica",
            "esito" => "",
        ),
        //fase approvazione Direzione riferimento                                                
        array("ID" => 4,
            "descrizione" => "In attesa di approvazione dalla Direzione di riferimento",
            "esito" => "",
        ),
        array("ID" => 5,
            "descrizione" => "In attesa di parere dalla Direzione Generale",
            "esito" => "",
        ),
        array("ID" => 6,
            "descrizione" => "In istruttoria da parte della UO competente",
            "esito" => "",
        ),
        array("ID" => 7,
            "descrizione" => "In fase di monitoraggio",
            "esito" => "",
        ),
        array("ID" => 8,
            "descrizione" => "Acquisita",
            "esito" => "ok",
        ),
        array("ID" => 9,
            "descrizione" => "Non approvato dai CdR padre",
            "esito" => "ko",
        ),
        array("ID" => 10,
            "descrizione" => "Non approvato dal CdR strategico",
            "esito" => "ko",
        ),
        array("ID" => 11,
            "descrizione" => "Non approvato dalla Direzione di riferimento",
            "esito" => "ko",
        ),
        array("ID" => 12,
            "descrizione" => "Non approvato dalla Direzione Generale",
            "esito" => "ko",
        ),
        array("ID" => 13,
            "descrizione" => "Non approvato dalla UO competente",
            "esito" => "ko",
        ),
    );

    //restituisce l'array degli stati di avanzamento
    public static function getStatiAvanzamento()
    {
        $classname = static::class;
        return $classname::$stati_richiesta;
    }

    //restituisce lo stato d'avanzamento della richiesta
    public function getIdStatoAvanzamento()
    {
        //la variabile stato viene inizializzta con stato = anomalia
        $stato = 0;
        //stati di chiusura
        if ($this->data_rifiuto_uo_competente !== null) {
            $stato = 13;
        } else if ($this->data_rifiuto_dg !== null) {
            $stato = 12;
        } else if ($this->data_rifiuto_direzione_riferimento !== null) {
            $stato = 11;
        } else if ($this->data_rifiuto_cdr_strategico !== null) {
            $stato = 10;
        } else if ($this->data_rifiuto_cdr_padre !== null) {
            $stato = 9;
        } else if ($this->data_conferma_acquisizione !== null) {
            $stato = 8;
        }
        //stati intermedi
        else if ($this->data_approvazione_uo_competente !== null) {
            $stato = 7;
        } else if ($this->data_approvazione_dg !== null) {
            $stato = 6;
        } else if ($this->data_approvazione_direzione_riferimento !== null) {
            $stato = 5;
        }
        //le seguenti condizioni nidificate sono previste per contemplare la possibilità che fra il cdr richiedente e la direzione generale
        //non ci siano livelli intermedi o che il cdr stesso abbia un ruolo nel processo di approvazione bypassando i ruoli inferiori
        else if ($this->data_chiusura !== null) {
            $cm = cm::getInstance();
            $anno = new AnnoBudget($this->id_anno_budget);
            $cdr_richiesta = Cdr::factoryFromCodice($this->codice_cdr_creazione, PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d")))->cloneAttributesToNewObject("CdrRU");
            if ($cdr_richiesta->isDirezioneRiferimentoAnno($anno)) {
                $stato = 5;
            } else {
                $cdr_direzione_riferimento = $cdr_richiesta->getCdrDirezioneRiferimento($anno);
                //non esiste direzione riferimento
                if ($cdr_direzione_riferimento == null) {
                    if ($this->data_approvazione_cdr_strategico !== null || $cdr_richiesta->isProgrammazioneStrategicaAnno($anno)) {
                        $stato = 5;
                    } else {
                        //approvazione padre strategico                        
                        $cdr_padre_strategico = $cdr_richiesta->getCdrPadreProgrammazioneStrategica($anno);
                        if ($cdr_padre_strategico == null) {
                            if ($this->data_approvazione_cdr_padre !== null) {
                                $stato = 5;
                            } else {
                                $cdr_padre_abilitato = $cdr_richiesta->getCdrPadreAbilitato($anno);
                                if ($cdr_padre_abilitato == null) {
                                    $stato = 5;
                                }
                            }
                        } else {
                            //approvazione cdr_padre
                            if ($this->data_approvazione_cdr_padre !== null) {
                                $stato = 3;
                            } else {
                                $cdr_padre_abilitato = $cdr_richiesta->getCdrPadreAbilitato($anno);
                                if ($cdr_padre_abilitato == null) {
                                    $stato = 3;
                                }
                            }
                        }
                    }
                } else {
                    if ($this->data_approvazione_cdr_strategico !== null || $cdr_richiesta->isProgrammazioneStrategicaAnno($anno)) {
                        $stato = 4;
                    } else {
                        //approvazione padre strategico                        
                        $cdr_padre_strategico = $cdr_richiesta->getCdrPadreProgrammazioneStrategica($anno);
                        if ($cdr_padre_strategico == null) {
                            if ($this->data_approvazione_cdr_padre !== null) {
                                $stato = 4;
                            } else {
                                $cdr_padre_abilitato = $cdr_richiesta->getCdrPadreAbilitato($anno);
                                if ($cdr_padre_abilitato == null) {
                                    $stato = 4;
                                }
                            }
                        } else {
                            //approvazione cdr_padre
                            if ($this->data_approvazione_cdr_padre !== null) {
                                $stato = 3;
                            } else {
                                $cdr_padre_abilitato = $cdr_richiesta->getCdrPadreAbilitato($anno);
                                if ($cdr_padre_abilitato == null) {
                                    $stato = 3;
                                }
                            }
                        }
                    }
                }
            }
        }
        //se ancora non è stato definito uno stato si procede con le verifiche
        if ($stato == 0) {
            if ($this->data_chiusura !== null) {
                $stato = 2;
            } else {
                $stato = 1;
            }
        }
        return $stato;
    }
    
    //restituisce true se la richiesta risulta di competenza dell'utente (privilegi per avanzamento) coerentemente con quanto definito nel dettaglio della richiesta 
    public function isApprovazioneCompetenza(CdrRU $cdr, AnnoBudget $anno, Datetime $data_riferimento, PianoCdr $piano_cdr) {
        $user = LoggedUser::getInstance();
               
        $richiesta = $this;   
        $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr)->cloneAttributesToNewObject("CdrRU");   
        $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();
        //richiesta modificabile solamente dal responsabile del cdr selezionato, se abilitato
        if ($id_stato_avanzamento == 1) {
            if ($user->hasPrivilege("ru_richiesta_edit") && $user->hasPrivilege("resp_cdr_selezionato")){
                //verifica che la modifica sta avvenendo da parte del cdr creatore della richiesta
                if ($richiesta->codice_cdr_creazione !== $cdr->codice) {
                    return false;
                }   
                return true;
            }
        }
        else if ($id_stato_avanzamento > 1) {
            $cdr_padre_abilitato = $cdr_creazione->getCdrPadreAbilitato($anno);
            if ($cdr_padre_abilitato !== null) {
                $responsabile_cdr_padre_abilitato = $cdr_padre_abilitato->getResponsabile($data_riferimento);
            }
        }
        if ($id_stato_avanzamento == 2) {
            if ($user->hasPrivilege("ru_richiesta_edit") && $responsabile_cdr_padre_abilitato->matricola_responsabile == $user->matricola_utente_selezionato){
                return true;
            }            
        }
        else if ($id_stato_avanzamento > 2 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 9)) {
            $cdr_padre_strategico = $cdr_creazione->getCdrPadreProgrammazioneStrategica($anno);
            if ($cdr_padre_strategico !== null) {
                $responsabile_cdr_padre_strategico = $cdr_padre_strategico->getResponsabile($data_riferimento);
            }        
        }
        if ($id_stato_avanzamento == 3) {        
            if ($user->hasPrivilege("ru_programmazione_strategica_edit") && $responsabile_cdr_padre_strategico->matricola_responsabile == $user->matricola_utente_selezionato){            
                return true;
            }
        }
        else if ($id_stato_avanzamento > 3 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 10)) {
            $cdr_direzione_riferimento = $cdr_creazione->getCdrDirezioneRiferimento($anno);
            if ($cdr_direzione_riferimento !== null) {
                $responsabile_direzione_riferimento = $cdr_direzione_riferimento->getResponsabile($data_riferimento);
            }        
        }
        if ($id_stato_avanzamento == 4) {        
            if ($user->hasPrivilege("ru_direzione_riferimento_edit") && $responsabile_direzione_riferimento->matricola_responsabile == $user->matricola_utente_selezionato) {
                return true;
            }
        }
        if ($id_stato_avanzamento == 5  && $user->hasPrivilege("ru_dg_edit")) {
            return true;
        }
        if ($id_stato_avanzamento == 6  && $user->hasPrivilege("ru_uo_competente_edit")) {
            return true;
        }
        if ($id_stato_avanzamento == 7  && $user->hasPrivilege("ru_uo_competente_edit")) {
            return true;
        }
        
        return false;
    }

    //inserimento o update su db
    public function save()
    {
        $db = ffDB_Sql::factory();
        //insert
        if ($this->id !== null) {
            $sql = "
            UPDATE " . self::$tablename . "
            SET        
                ID=" . $db->toSql($this->id) . ",
                ID_anno_budget=" . $db->toSql($this->id_anno_budget) . ",
                matricola_creazione=" . $db->toSql($this->matricola_creazione) . ",
                codice_cdr_creazione=" . $db->toSql($this->codice_cdr_creazione) . ",
                data_creazione=" . $db->toSql($this->data_creazione) . ",
                codice_cdc=" . $db->toSql($this->codice_cdc) . ",
                ID_tipo_richiesta=" . $db->toSql($this->id_tipo_richiesta) . ",
                informazioni_aggiuntive_tipologia=" . $db->toSql($this->informazioni_aggiuntive_tipologia) . ",
                ID_qualifica_interna=" . $db->toSql($this->id_qualifica_interna) . ",
                qta=" . $db->toSql($this->qta) . ",
                motivazioni=" . $db->toSql($this->motivazioni) . ",
                data_chiusura=" . $db->toSql($this->data_chiusura) . ",
                ID_parere_cdr_padre=" . $db->toSql($this->id_parere_cdr_padre) . ",
                note_parere_cdr_padre=" . $db->toSql($this->note_parere_cdr_padre) . ",
                ID_priorita_cdr_padre=" . $db->toSql($this->id_priorita_cdr_padre) . ",
                ID_tempi_cdr_padre=" . $db->toSql($this->id_tempi_cdr_padre) . ",
                data_approvazione_cdr_padre=" . $db->toSql($this->data_approvazione_cdr_padre) . ",
                data_rifiuto_cdr_padre=" . $db->toSql($this->data_rifiuto_cdr_padre) . ",
                ID_parere_cdr_strategico=" . $db->toSql($this->id_parere_cdr_strategico) . ",
                note_parere_cdr_strategico=" . $db->toSql($this->note_parere_cdr_strategico) . ",
                ID_priorita_cdr_strategico=" . $db->toSql($this->id_priorita_cdr_strategico) . ",
                ID_tempi_cdr_strategico=" . $db->toSql($this->id_tempi_cdr_strategico) . ",
                data_approvazione_cdr_strategico=" . $db->toSql($this->data_approvazione_cdr_strategico) . ",
                data_rifiuto_cdr_strategico=" . $db->toSql($this->data_rifiuto_cdr_strategico) . ",
                ID_parere_direzione_riferimento=" . $db->toSql($this->id_parere_direzione_riferimento) . ",
                note_parere_direzione_riferimento=" . $db->toSql($this->note_parere_direzione_riferimento) . ",
                ID_priorita_direzione_riferimento=" . $db->toSql($this->id_priorita_direzione_riferimento) . ",
                ID_tempi_direzione_riferimento=" . $db->toSql($this->id_tempi_direzione_riferimento) . ",
                data_approvazione_direzione_riferimento=" . $db->toSql($this->data_approvazione_direzione_riferimento) . ",
                data_rifiuto_direzione_riferimento=" . $db->toSql($this->data_rifiuto_direzione_riferimento) . ",
                ID_parere_dg=" . $db->toSql($this->id_parere_dg) . ",
                note_parere_dg=" . $db->toSql($this->note_parere_dg) . ",
                ID_priorita_dg=" . $db->toSql($this->id_priorita_dg) . ",
                ID_tempi_dg=" . $db->toSql($this->id_tempi_dg) . ",
                data_approvazione_dg=" . $db->toSql($this->data_approvazione_dg) . ",
                data_rifiuto_dg=" . $db->toSql($this->data_rifiuto_dg) . ",
                costo_presunto=" . $db->toSql($this->costo_presunto) . ",
                modalita_acquisizione=" . $db->toSql($this->modalita_acquisizione) . ",
                ID_tempi_uo_competente=" . $db->toSql($this->id_tempi_uo_competente) . ",
                anno_soddisfacimento_richiesta=" . $db->toSql($this->anno_soddisfacimento_richiesta) . ",
                fonte_finanziamento_proposta=" . $db->toSql($this->fonte_finanziamento_proposta) . ",
                incoerenza_piano_fabbisogni=" . $db->toSql($this->incoerenza_piano_fabbisogni) . ",
                data_approvazione_uo_competente=" . $db->toSql($this->data_approvazione_uo_competente) . ",
                data_rifiuto_uo_competente=" . $db->toSql($this->data_rifiuto_uo_competente) . ",
                importo_definitivo=" . $db->toSql($this->importo_definitivo) . ",
                data_acquisizione=" . $db->toSql($this->data_acquisizione) . ",
                provvedimento=" . $db->toSql($this->provvedimento) . ",
                fonte_finanziamento=" . $db->toSql($this->fonte_finanziamento) . ",
                note_monitoraggio=" . $db->toSql($this->note_monitoraggio) . ",
                data_conferma_acquisizione=" . $db->toSql($this->data_conferma_acquisizione) . "
            WHERE
                ID = " . $db->toSql($this->id);
        } else {
                $sql = "
            INSERT INTO ".self::$tablename."            
            (
                ID_anno_budget,
                matricola_creazione,
                codice_cdr_creazione,
                data_creazione,
                codice_cdc,
                ID_tipo_richiesta,
                informazioni_aggiuntive_tipologia,
                ID_qualifica_interna,
                qta,
                motivazioni,
                data_chiusura,
                ID_parere_cdr_padre,
                note_parere_cdr_padre,
                ID_priorita_cdr_padre,
                ID_tempi_cdr_padre,
                data_approvazione_cdr_padre,
                data_rifiuto_cdr_padre,
                ID_parere_cdr_strategico,
                note_parere_cdr_strategico,
                ID_priorita_cdr_strategico,
                ID_tempi_cdr_strategico,
                data_approvazione_cdr_strategico,
                data_rifiuto_cdr_strategico,
                ID_parere_direzione_riferimento,
                note_parere_direzione_riferimento,
                ID_priorita_direzione_riferimento,
                ID_tempi_direzione_riferimento,
                data_approvazione_direzione_riferimento,
                data_rifiuto_direzione_riferimento,
                ID_parere_dg,
                note_parere_dg,
                ID_priorita_dg,
                ID_tempi_dg,
                data_approvazione_dg,
                data_rifiuto_dg,
                costo_presunto,
                modalita_acquisizione,
                ID_tempi_uo_competente,
                anno_soddisfacimento_richiesta,
                fonte_finanziamento_proposta,
                incoerenza_piano_fabbisogni,
                data_approvazione_uo_competente,
                data_rifiuto_uo_competente,
                importo_definitivo,
                data_acquisizione,
                provvedimento,
                fonte_finanziamento,
                note_monitoraggio,
                data_conferma_acquisizione
            )
            VALUES
            (
                ".$this->id_anno_budget = null?"NULL":$db->toSql($this->id_anno_budget).",
                ".$this->matricola_creazione = null?"NULL":$db->toSql($this->matricola_creazione).",
                ".$this->codice_cdr_creazione = null?"NULL":$db->toSql($this->codice_cdr_creazione).",
                ".$this->data_creazione = null?"NULL":$db->toSql($this->data_creazione).",
                ".$this->codice_cdc = null?"NULL":$db->toSql($this->codice_cdc).",
                ".$this->id_tipo_richiesta = null?"NULL":$db->toSql($this->id_tipo_richiesta).",
                ".$this->informazioni_aggiuntive_tipologia = null?"NULL":$db->toSql($this->informazioni_aggiuntive_tipologia).",
                ".$this->id_qualifica_interna = null?"NULL":$db->toSql($this->id_qualifica_interna).",
                ".$this->qta = null?"NULL":$db->toSql($this->qta).",
                ".$this->motivazioni = null?"NULL":$db->toSql($this->motivazioni).",
                ".$this->data_chiusura = null?"NULL":$db->toSql($this->data_chiusura).",
                ".$this->id_parere_cdr_padre = null?"NULL":$db->toSql($this->id_parere_cdr_padre).",
                ".$this->note_parere_cdr_padre = null?"NULL":$db->toSql($this->note_parere_cdr_padre).",
                ".$this->id_priorita_cdr_padre = null?"NULL":$db->toSql($this->id_priorita_cdr_padre).",
                ".$this->id_tempi_cdr_padre = null?"NULL":$db->toSql($this->id_tempi_cdr_padre).",
                ".$this->data_approvazione_cdr_padre = null?"NULL":$db->toSql($this->data_approvazione_cdr_padre).",
                ".$this->data_rifiuto_cdr_padre = null?"NULL":$db->toSql($this->data_rifiuto_cdr_padre).",
                ".$this->id_parere_cdr_strategico = null?"NULL":$db->toSql($this->id_parere_cdr_strategico).",
                ".$this->note_parere_cdr_strategico = null?"NULL":$db->toSql($this->note_parere_cdr_strategico).",
                ".$this->id_priorita_cdr_strategico = null?"NULL":$db->toSql($this->id_priorita_cdr_strategico).",
                ".$this->id_tempi_cdr_strategico = null?"NULL":$db->toSql($this->id_tempi_cdr_strategico).",
                ".$this->data_approvazione_cdr_strategico = null?"NULL":$db->toSql($this->data_approvazione_cdr_strategico).",
                ".$this->data_rifiuto_cdr_strategico = null?"NULL":$db->toSql($this->data_rifiuto_cdr_strategico).",
                ".$this->id_parere_direzione_riferimento = null?"NULL":$db->toSql($this->id_parere_direzione_riferimento).",
                ".$this->note_parere_direzione_riferimento = null?"NULL":$db->toSql($this->note_parere_direzione_riferimento).",
                ".$this->id_priorita_direzione_riferimento = null?"NULL":$db->toSql($this->id_priorita_direzione_riferimento).",
                ".$this->id_tempi_direzione_riferimento = null?"NULL":$db->toSql($this->id_tempi_direzione_riferimento).",
                ".$this->data_approvazione_direzione_riferimento = null?"NULL":$db->toSql($this->data_approvazione_direzione_riferimento).",
                ".$this->data_rifiuto_direzione_riferimento = null?"NULL":$db->toSql($this->data_rifiuto_direzione_riferimento).",
                ".$this->id_parere_dg = null?"NULL":$db->toSql($this->id_parere_dg).",
                ".$this->note_parere_dg = null?"NULL":$db->toSql($this->note_parere_dg).",
                ".$this->id_priorita_dg = null?"NULL":$db->toSql($this->id_priorita_dg).",
                ".$this->id_tempi_dg = null?"NULL":$db->toSql($this->id_tempi_dg).",
                ".$this->data_approvazione_dg = null?"NULL":$db->toSql($this->data_approvazione_dg).",
                ".$this->data_rifiuto_dg = null?"NULL":$db->toSql($this->data_rifiuto_dg).",
                ".$this->costo_presunto = null?"NULL":$db->toSql($this->costo_presunto).",
                ".$this->modalita_acquisizione = null?"NULL":$db->toSql($this->modalita_acquisizione).",
                ".$this->id_tempi_uo_competente = null?"NULL":$db->toSql($this->id_tempi_uo_competente).",
                ".$this->anno_soddisfacimento_richiesta = null?"NULL":$db->toSql($this->anno_soddisfacimento_richiesta).",
                ".$this->fonte_finanziamento_proposta = null?"NULL":$db->toSql($this->fonte_finanziamento_proposta).",
                ".$this->incoerenza_piano_fabbisogni = null?"NULL":$db->toSql($this->incoerenza_piano_fabbisogni).",
                ".$this->data_approvazione_uo_competente = null?"NULL":$db->toSql($this->data_approvazione_uo_competente).",
                ".$this->data_rifiuto_uo_competente = null?"NULL":$db->toSql($this->data_rifiuto_uo_competente).",
                ".$this->importo_definitivo = null?"NULL":$db->toSql($this->importo_definitivo).",
                ".$this->data_acquisizione = null?"NULL":$db->toSql($this->data_acquisizione).",
                ".$this->provvedimento = null?"NULL":$db->toSql($this->provvedimento).",
                ".$this->fonte_finanziamento = null?"NULL":$db->toSql($this->fonte_finanziamento).",
                ".$this->note_monitoraggio = null?"NULL":$db->toSql($this->note_monitoraggio).",
                ".$this->data_conferma_acquisizione = null?"NULL":$db->toSql($this->data_conferma_acquisizione)."
                )";
        }
        if (!$db->execute($sql)) {
            throw new Exception("Impossibile aggiornare l'oggetto " . static::class . " con ID='" . $this->id . "' nel DB");
        }
    }

    //restituisce un oggetto Grid delle richieste
    //il recordset deve rispettare il tracciato specificato nell'array grid_fields
    public static function getGridRichieste($grid_id, $grid_title, $grid_recordset, $include_fullclick=null) {   
        $cm = cm::getInstance();
        $module = Modulo::getCurrentModule();
        $grid_fields = array(
            "ID", 
            "cdr_creazione", 
            "ruolo",
            "qualifica",
            "qta",
            "tipologia",
            "stato_avanzamento",
            "info_accettazione",
        );
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = $grid_id;
        $oGrid->title = $grid_title;
        $oGrid->resources[] = "richiesta";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
            $grid_fields, 
            $grid_recordset, 
            "ru_richiesta"
        );
        $oGrid->order_default = "cdr_creazione";
        $oGrid->record_id = "richiesta-modify";
        $oGrid->record_url = FF_SITE_PATH . "/area_riservata" . $module->site_path . "/dettaglio_richiesta";
        $oGrid->order_method = "labels";
        $oGrid->full_ajax = false;
        if ($include_fullclick !== null) {
            $oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
        }      

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr_creazione";
        $oField->base_type = "Text";
        $oField->label = "CdR Creazione";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ruolo";
        $oField->base_type = "Text";
        $oField->label = "Ruolo";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "qualifica";
        $oField->base_type = "Text";
        $oField->label = "Qualifica";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "qta";
        $oField->base_type = "Text";
        $oField->label = "Qta";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "tipologia";
        $oField->base_type = "Text";
        $oField->label = "Tipo";
        $oGrid->addContent($oField);

        //tipo del campo esteso a selection per poter definirne l'ordinamento
        $classname = static::class;
        foreach ($classname::getStatiAvanzamento() as $stato_avanzamento){
            $stati_avanzamento_multipairs[] = array (
                                                new ffData($stato_avanzamento["ID"], "Number"),
                                                new ffData($stato_avanzamento["descrizione"], "Text"),
                                            );
        }
        $oField = ffField::factory($cm->oPage);
        $oField->id = "stato_avanzamento";
        $oField->base_type = "Number";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $stati_avanzamento_multipairs;
        $oField->label = "Stato avanzamento";
        $oGrid->addContent($oField);
        
        return $oGrid;
    }
    
    //restituisce l'ultima accettazione disponibile in base allo stato della richiesta
    //se viene passato un cdr viene restituita l'ultima accettazione effettuata da un cdr diverso da quello passato
    function getUltimaAccettazioneData(DateTime $data_riferimento, CdrRU $cdr = null) {
        $anno = new AnnoBudget($this->id_anno_budget);
        $piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
        $cdr_creazione = Cdr::factoryFromCodice($this->codice_cdr_creazione, $piano_cdr)->cloneAttributesToNewObject("CdrRU");
        $accettazione = null;
        $id_stato_avanzamento = $this->getIdStatoAvanzamento();         
        //se la richiesta è stata approvata dal cdr di programmazione strategica viene verificata l'eventuale accettazione
        if ($id_stato_avanzamento > 3) {
            $cdr_accettazione = $cdr_creazione->getCdrPadreProgrammazioneStrategica($anno);
            if ($cdr == null || $cdr_accettazione->codice !== $cdr->codice) {
                $filters = array (
                    "ID_anno_budget" => $this->id_anno_budget,
                    "codice_cdr" => $cdr_accettazione->codice,
                );
                $accettazione = RUAccettazione::GetAll($filters);
                if (empty($accettazione)){
                    $accettazione = null;
                }
            }            
        }
        //se non è presente un'accettazione da parte del cdr di programmazione strategica        
        //viene verificato che ci sia un'accettazione dal padre del cdr padre
        if ($accettazione == null && $id_stato_avanzamento > 2) {
            $cdr_accettazione = $cdr_creazione->getCdrPadreAbilitato($anno);
            if ($cdr == null || $cdr_accettazione->codice !== $cdr->codice) {
                $filters = array (
                    "ID_anno_budget" => $this->id_anno_budget,
                    "codice_cdr" => $cdr_accettazione->codice,
                );
                $accettazione = RUAccettazione::GetAll($filters);
                if (empty($accettazione)){
                    $accettazione = null;
                }
            }
        }
        //se non presente accettazione cdr padre viene verificata accettazione cdr creazione ( se richiesta chiusa
        if ($accettazione == null && $id_stato_avanzamento > 1){
            if ($cdr == null || $cdr_creazione->codice !== $cdr->codice) {
                $filters = array (
                    "ID_anno_budget" => $this->id_anno_budget,
                    "codice_cdr" => $cdr_creazione->codice,
                );
                $accettazione = RUAccettazione::GetAll($filters);             
                if (empty($accettazione)){
                    $accettazione = null;
                }
            }
        }        
        if (!$accettazione == null){
            $accettazione = $accettazione[0];
        }
        return $accettazione;
    }
}