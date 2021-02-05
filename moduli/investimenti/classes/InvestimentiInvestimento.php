<?php
class InvestimentiInvestimento extends Entity{
    protected static $tablename = "investimenti_investimento";    
    
    public static $stati_investimento = array	(
                                                //anomalia
                                                array(  "ID" => 0,
														"descrizione" => "Anomalia",
                                                        "esito" => "ko",
													),
                                                //fase richiesta
                                                //NB ID usato per verifiche in richieste.php e dettaglio_richiesta, in caso di variazione propagare modifica
                                                array(  "ID" => 1,
														"descrizione" => "Richiesta in fase di compilazione",
                                                        "esito" => "",
													),
                                                //fase approvazione
                                                //NB ID usato per verifica in approvazione
												array(  "ID" => 2,
														"descrizione" => "Richiesta in fase di approvazione dalla direzione di riferimento",
                                                        "esito" => "",
													),
                                                //fase istruttoria                                                
												array(  "ID" => 3,
														"descrizione" => "In attesa di avvio istruttoria",
                                                        "esito" => "",
													),                                                
												array(  "ID" => 4,
														"descrizione" => "Istruttoria UOC competente",
                                                        "esito" => "",
													),
                                                /*
                                                //stato eliminato
                                                array(  "ID" => 5,
														"descrizione" => "In attesa di avvio verifica copertura",
                                                        "esito" => "",
													),                                                
                                                */
                                                array(  "ID" => 6,
														"descrizione" => "In fase di verifica copertura",
                                                        "esito" => "",
													),
                                                array(  "ID" => 7,
														"descrizione" => "In attesa di proposta piano investimenti",
                                                        "esito" => "",
													),
                                                array(  "ID" => 8,
														"descrizione" => "In attesa di parere della Direzione Generale",
                                                        "esito" => "",
													),
                                                array(  "ID" => 9,
														"descrizione" => "In fase di monitoraggio",
                                                        "esito" => "",
													),
                                                array(  "ID" => 10,
														"descrizione" => "Chiuso",
                                                        "esito" => "ok",
													),
                                                array(  "ID" => 11,
														"descrizione" => "Non approvato dalla direzione di riferimento",
                                                        "esito" => "ko",
													),
                                                array(  "ID" => 12,
														"descrizione" => "Non approvato dalla direzione generale",
                                                        "esito" => "ko",
													),
                                                array(  "ID" => 13,
														"descrizione" => "Non approvato dalla UOC Competente",
                                                        "esito" => "ko",
													),
												);
	
    public static function getAll($where=array(), $order=array(array("fieldname"=>"ID", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
    
    //funzione che restituisce lo stato d'avanzamento dell'investimento in base alla valorizzazione dei campi
	public function getIdStatoAvanzamento () {
		//la variabile stato viene inizializzta con stato = anomalia
		$stato = 0;
        //stati di chiusura (buon esito o meno
        if ($this->monitoraggio_data_chiusura !== null) {            
            $stato = 10;
        }    
        else if ($this->istruttoria_data_scarto_uoc_competente !== null) {
            $stato = 13;
        }
        else if ($this->dg_data_scarto_piano_investimenti !== null) {
            $stato = 12;
        }
        else if($this->approvazione_data_scarto_direzione_riferimento !== null){
            $stato = 11;
        }
        //stati intermedi
        else if($this->dg_data_validazione_piano_investimenti !== null) {
            $stato = 9;
        }
        else if($this->proposta_piano_investimenti_data !== null) {
            $stato = 8;
        }
        else if($this->verifica_copertura_data_fine !== null) {
            $stato = 7;
        }
        /*
        //stato (e campo) eliminato
        else if($this->verifica_copertura_data_avvio !== null) {
            $stato = 6;
        }         
        */       
        else if($this->istruttoria_data_chiusura_uoc_competente !== null) {
            //stato eliminato
            //$stato = 5;
            $stato = 6;
        }
        else if($this->istruttoria_data_avvio !== null) {
            $stato = 4;
        }
        else if($this->approvazione_data !== null) {
            $stato = 3;
        }
        else if($this->richiesta_data_chiusura !== null) {
            $stato = 2;
        }
        else {
            $stato = 1;
        }
                                        
		return $stato;
	}
    
    //inserimento o update su db
	public function save(){
		$db = ffDB_Sql::factory();
		//insert
		if ($this->id !== null){            
			$sql = "
				UPDATE ".self::$tablename."
				SET
                    codice_cdr_creazione=".$db->toSql($this->codice_cdr_creazione).",
                    ID_anno_budget=".$db->toSql($this->id_anno_budget).",
                    data_creazione=".$db->toSql($this->data_creazione).",
                    richiesta_codice_cdc=".$db->toSql($this->richiesta_codice_cdc).",
                    richiesta_nuova=".$db->toSql($this->richiesta_nuova).",
                    richiesta_matricola_bene_da_sostituire=".$db->toSql($this->richiesta_matricola_bene_da_sostituire).",
                    richiesta_ID_categoria=".$db->toSql($this->richiesta_id_categoria).",
                    richiesta_descrizione_bene=".$db->toSql($this->richiesta_descrizione_bene).",
                    richiesta_quantita=".$db->toSql($this->richiesta_quantita).",
                    richiesta_motivo=".$db->toSql($this->richiesta_motivo).",
                    richiesta_motivazioni_supporto=".$db->toSql($this->richiesta_motivazioni_supporto).",
                    richiesta_eventuali_costi_aggiuntivi=".$db->toSql($this->richiesta_eventuali_costi_aggiuntivi).",
                    richiesta_costo_stimato=".$db->toSql(str_replace(",",".",str_replace(".","",$this->richiesta_costo_stimato))).",
                    richiesta_ID_priorita=".$db->toSql($this->richiesta_id_priorita).",
                    richiesta_tempi=".$db->toSql($this->richiesta_tempi).",
                    richiesta_ubicazione_bene=".$db->toSql($this->richiesta_ubicazione_bene).",
                    richiesta_data_chiusura=".$db->toSql($this->richiesta_data_chiusura).",
                    approvazione_ID_parere_direzione_riferimento=".$db->toSql($this->approvazione_id_parere_direzione_riferimento).",
                    approvazione_note_parere_direzione_riferimento=".$db->toSql($this->approvazione_note_parere_direzione_riferimento).", 
                    approvazione_ID_priorita_direzione_riferimento=".$db->toSql($this->approvazione_id_priorita_direzione_riferimento).",
                    approvazione_ID_tempi_stimati_direzione_riferimento=".$db->toSql($this->approvazione_id_tempi_stimati_direzione_riferimento).",
                    approvazione_data=".$db->toSql($this->approvazione_data).",
                    approvazione_data_scarto_direzione_riferimento=".$db->toSql($this->approvazione_data_scarto_direzione_riferimento).",                
                    istruttoria_ID_categoria_uoc_competente_anno=".$db->toSql($this->istruttoria_id_categoria_uoc_competente_anno).",
                    istruttoria_data_avvio=".$db->toSql($this->istruttoria_data_avvio).",
                    istruttoria_costo_presunto=".$db->toSql(str_replace(",",".",str_replace(".","",$this->istruttoria_costo_presunto))).",
                    istruttoria_modalita_acquisizione=".$db->toSql($this->istruttoria_modalita_acquisizione).",
                    istruttoria_ID_tempi_stimati_uoc_competente=".$db->toSql($this->istruttoria_id_tempi_stimati_uoc_competente).",
                    istruttoria_anno_soddisfacimento=".$db->toSql($this->istruttoria_anno_soddisfacimento).",
                    istruttoria_ID_fonte_finanziamento_proposta=".$db->toSql($this->istruttoria_id_fonte_finanziamento_proposta).",
                    istruttoria_ID_categoria_registro_cespiti_proposta=".$db->toSql($this->istruttoria_id_categoria_registro_cespiti_proposta).",
                    istruttoria_non_coerente_piano_investimenti=".$db->toSql($this->istruttoria_non_coerente_piano_investimenti).",
                    istruttoria_data_chiusura_uoc_competente=".$db->toSql($this->istruttoria_data_chiusura_uoc_competente).",
                    istruttoria_data_scarto_uoc_competente=".$db->toSql($this->istruttoria_data_scarto_uoc_competente).",
                    verifica_copertura_ID_registro_cespiti=".$db->toSql($this->verifica_copertura_id_registro_cespiti).",                                        
                    verifica_copertura_ID_fonte_finanziamento=".$db->toSql($this->verifica_copertura_id_fonte_finanziamento).",
                    verifica_copertura_data_fine=".$db->toSql($this->verifica_copertura_data_fine).",
                    proposta_piano_investimenti_data=".$db->toSql($this->proposta_piano_investimenti_data).",
                    dg_ID_parere=".$db->toSql($this->dg_id_parere).",
                    dg_ID_priorita=".$db->toSql($this->dg_id_priorita).",
                    dg_ID_tempi=".$db->toSql($this->dg_id_tempi).",
                    dg_data_validazione_piano_investimenti=".$db->toSql($this->dg_data_validazione_piano_investimenti).",
                    dg_data_scarto_piano_investimenti=".$db->toSql($this->dg_data_scarto_piano_investimenti).",
                    monitoraggio_importo_definitivo=".$db->toSql(str_replace(",",".",str_replace(".","",$this->monitoraggio_importo_definitivo))).",
                    monitoraggio_data=".$db->toSql($this->monitoraggio_data).",
                    monitoraggio_provvedimento=".$db->toSql($this->monitoraggio_provvedimento).",
                    monitoraggio_fatture=".$db->toSql($this->monitoraggio_fatture).",
                    monitoraggio_fornitore=".$db->toSql($this->monitoraggio_fornitore).",
                    monitoraggio_ID_fonte_finanziamento=".$db->toSql($this->monitoraggio_id_fonte_finanziamento).",
                    monitoraggio_note=".$db->toSql($this->monitoraggio_note).",
                    monitoraggio_data_chiusura=".$db->toSql($this->monitoraggio_data_chiusura)."
				WHERE
					ID = ".$db->toSql($this->id)
				;			
		}	
        else {
            
            $sql = "
				INSERT INTO ".self::$tablename."
                    (
                    codice_cdr_creazione,
                    ID_anno_budget,
                    data_creazione,
                    richiesta_codice_cdc,
                    richiesta_nuova,
                    richiesta_matricola_bene_da_sostituire,
                    richiesta_ID_categoria,
                    richiesta_descrizione_bene,
                    richiesta_quantita,
                    richiesta_motivo,
                    richiesta_motivazioni_supporto,
                    richiesta_eventuali_costi_aggiuntivi,
                    richiesta_costo_stimato,
                    richiesta_ID_priorita,
                    richiesta_tempi,
                    richiesta_ubicazione_bene,
                    richiesta_data_chiusura,
                    approvazione_ID_parere_direzione_riferimento,
                    approvazione_note_parere_direzione_riferimento,
                    approvazione_ID_priorita_direzione_riferimento,
                    approvazione_ID_tempi_stimati_direzione_riferimento,
                    approvazione_data,
                    approvazione_data_scarto_direzione_riferimento,
                    istruttoria_ID_categoria_uoc_competente_anno,
                    istruttoria_data_avvio,
                    istruttoria_costo_presunto,
                    istruttoria_modalita_acquisizione,
                    istruttoria_ID_tempi_stimati_uoc_competente,
                    istruttoria_anno_soddisfacimento,
                    istruttoria_ID_fonte_finanziamento_proposta,
                    istruttoria_ID_categoria_registro_cespiti_proposta,
                    istruttoria_non_coerente_piano_investimenti,
                    istruttoria_data_chiusura_uoc_competente,
                    istruttoria_data_scarto_uoc_competente,
                    verifica_copertura_ID_registro_cespiti,       
                    verifica_copertura_ID_fonte_finanziamento,
                    verifica_copertura_data_fine,
                    proposta_piano_investimenti_data,
                    dg_ID_parere,
                    dg_ID_priorita,
                    dg_ID_tempi,
                    dg_data_validazione_piano_investimenti,
                    dg_data_scarto_piano_investimenti,
                    monitoraggio_importo_definitivo,
                    monitoraggio_data,
                    monitoraggio_provvedimento,
                    monitoraggio_fatture,
                    monitoraggio_fornitore,
                    monitoraggio_ID_fonte_finanziamento,
                    monitoraggio_note,
                    monitoraggio_data_chiusura
                    )
				VALUES
                    (
                    ".$this->codice_cdr_creazione=null?"NULL":$db->toSql($this->codice_cdr_creazione).",
                    ".$this->id_anno_budget=null?"NULL":$db->toSql($this->id_anno_budget).",
                    ".$this->data_creazione=null?"NULL":$db->toSql($this->data_creazione).",
                    ".$this->richiesta_codice_cdc=null?"NULL":$db->toSql($this->richiesta_codice_cdc).",
                    ".$this->richiesta_nuova=null?"NULL":$db->toSql($this->richiesta_nuova).",
                    ".$this->richiesta_matricola_bene_da_sostituire=null?"NULL":$db->toSql($this->richiesta_matricola_bene_da_sostituire).",
                    ".$this->richiesta_id_categoria=null?"NULL":$db->toSql($this->richiesta_id_categoria).",
                    ".$this->richiesta_descrizione_bene=null?"NULL":$db->toSql($this->richiesta_descrizione_bene).",
                    ".$this->richiesta_quantita=null?"NULL":$db->toSql($this->richiesta_quantita).",
                    ".$this->richiesta_motivo=null?"NULL":$db->toSql($this->richiesta_motivo).",
                    ".$this->richiesta_motivazioni_supporto=null?"NULL":$db->toSql($this->richiesta_motivazioni_supporto).",
                    ".$this->richiesta_eventuali_costi_aggiuntivi=null?"NULL":$db->toSql($this->richiesta_eventuali_costi_aggiuntivi).",
                    ".$this->richiesta_costo_stimato=null?"NULL":$db->toSql(str_replace(",",".",str_replace(".","",$this->richiesta_costo_stimato))).",
                    ".$this->richiesta_id_priorita=null?"NULL":$db->toSql($this->richiesta_id_priorita).",
                    ".$this->richiesta_tempi=null?"NULL":$db->toSql($this->richiesta_tempi).",
                    ".$this->richiesta_ubicazione_bene=null?"NULL":$db->toSql($this->richiesta_ubicazione_bene).",
                    ".$this->richiesta_data_chiusura=null?"NULL":$db->toSql($this->richiesta_data_chiusura).",
                    ".$this->approvazione_id_parere_direzione_riferimento=null?"NULL":$db->toSql($this->approvazione_id_parere_direzione_riferimento).",
                    ".$this->approvazione_note_parere_direzione_riferimento=null?"NULL":$db->toSql($this->approvazione_note_parere_direzione_riferimento).", 
                    ".$this->approvazione_id_priorita_direzione_riferimento=null?"NULL":$db->toSql($this->approvazione_id_priorita_direzione_riferimento).",
                    ".$this->approvazione_id_tempi_stimati_direzione_riferimento=null?"NULL":$db->toSql($this->approvazione_id_tempi_stimati_direzione_riferimento).",
                    ".$this->approvazione_data=null?"NULL":$db->toSql($this->approvazione_data).",
                    ".$this->approvazione_data_scarto_direzione_riferimento=null?"NULL":$db->toSql($this->approvazione_data_scarto_direzione_riferimento).",                
                    ".$this->istruttoria_id_categoria_uoc_competente_anno=null?"NULL":$db->toSql($this->istruttoria_id_categoria_uoc_competente_anno).",
                    ".$this->istruttoria_data_avvio=null?"NULL":$db->toSql($this->istruttoria_data_avvio).",
                    ".$this->istruttoria_costo_presunto=null?"NULL":$db->toSql(str_replace(",",".",str_replace(".","",$this->istruttoria_costo_presunto))).",
                    ".$this->istruttoria_modalita_acquisizione=null?"NULL":$db->toSql($this->istruttoria_modalita_acquisizione).",
                    ".$this->istruttoria_id_tempi_stimati_uoc_competente=null?"NULL":$db->toSql($this->istruttoria_id_tempi_stimati_uoc_competente).",
                    ".$this->istruttoria_anno_soddisfacimento=null?"NULL":$db->toSql($this->istruttoria_anno_soddisfacimento).",
                    ".$this->istruttoria_id_fonte_finanziamento_proposta=null?"NULL":$db->toSql($this->istruttoria_id_fonte_finanziamento_proposta).",
                    ".$this->istruttoria_id_categoria_registro_cespiti_proposta=null?"NULL":$db->toSql($this->istruttoria_id_categoria_registro_cespiti_proposta).",
                    ".$this->istruttoria_non_coerente_piano_investimenti=null?"NULL":$db->toSql($this->istruttoria_non_coerente_piano_investimenti).",
                    ".$this->istruttoria_data_chiusura_uoc_competente=null?"NULL":$db->toSql($this->istruttoria_data_chiusura_uoc_competente).",
                    ".$this->istruttoria_data_scarto_uoc_competente=null?"NULL":$db->toSql($this->istruttoria_data_scarto_uoc_competente).",                                        
                    ".$this->verifica_copertura_id_registro_cespiti=null?"NULL":$db->toSql($this->verifica_copertura_id_registro_cespiti).",                                        
                    ".$this->verifica_copertura_id_fonte_finanziamento=null?"NULL":$db->toSql($this->verifica_copertura_id_fonte_finanziamento).",
                    ".$this->verifica_copertura_data_fine=null?"NULL":$db->toSql($this->verifica_copertura_data_fine).",
                    ".$this->proposta_piano_investimenti_data=null?"NULL":$db->toSql($this->proposta_piano_investimenti_data).",
                    ".$this->dg_id_parere=null?"NULL":$db->toSql($this->dg_id_parere).",
                    ".$this->dg_id_priorita=null?"NULL":$db->toSql($this->dg_id_priorita).",
                    ".$this->dg_id_tempi=null?"NULL":$db->toSql($this->dg_id_tempi).",
                    ".$this->dg_data_validazione_piano_investimenti=null?"NULL":$db->toSql($this->dg_data_validazione_piano_investimenti).",
                    ".$this->dg_data_scarto_piano_investimenti=null?"NULL":$db->toSql($this->dg_data_scarto_piano_investimenti).",
                    ".$this->monitoraggio_importo_definitivo=null?"NULL":$db->toSql(str_replace(",",".",str_replace(".","",$this->monitoraggio_importo_definitivo))).",
                    ".$this->monitoraggio_data=null?"NULL":$db->toSql($this->monitoraggio_data).",
                    ".$this->monitoraggio_provvedimento=null?"NULL":$db->toSql($this->monitoraggio_provvedimento).",
                    ".$this->monitoraggio_fatture=null?"NULL":$db->toSql($this->monitoraggio_fatture).",
                    ".$this->monitoraggio_fornitore=null?"NULL":$db->toSql($this->monitoraggio_fornitore).",
                    ".$this->monitoraggio_id_fonte_finanziamento=null?"NULL":$db->toSql($this->monitoraggio_id_fonte_finanziamento).",
                    ".$this->monitoraggio_note=null?"NULL":$db->toSql($this->monitoraggio_note).",
                    ".$this->monitoraggio_data_chiusura=null?"NULL":$db->toSql($this->monitoraggio_data_chiusura)."
                    )";		            
        } 
        if (!$db->execute($sql))		
            throw new Exception("Impossibile aggiornare l'oggetto ".static::class." con ID='".$this->id."' nel DB");
	}
}
