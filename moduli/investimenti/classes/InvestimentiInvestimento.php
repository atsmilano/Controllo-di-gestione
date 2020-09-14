<?php
class InvestimentiInvestimento {
    public $id;
    public $codice_cdr_creazione;
    public $id_anno_budget;
    public $data_creazione;
    public $richiesta_codice_cdc;
    public $richiesta_nuova;
    public $richiesta_matricola_bene_da_sostituire;
    public $richiesta_id_categoria;
    public $richiesta_descrizione_bene;
    public $richiesta_quantita;
    public $richiesta_motivo;
    public $richiesta_motivazioni_supporto;
    public $richiesta_eventuali_costi_aggiuntivi;
    public $richiesta_costo_stimato;
    public $richiesta_id_priorita;
    public $richiesta_tempi;
    public $richiesta_ubicazione_bene;
    public $richiesta_data_chiusura;
    public $approvazione_id_parere_direzione_riferimento;
    public $approvazione_note_parere_direzione_riferimento;
    public $approvazione_id_priorita_direzione_riferimento;
    public $approvazione_id_tempi_stimati_direzione_riferimento;
    public $approvazione_data;
    public $approvazione_data_scarto_direzione_riferimento;
    public $istruttoria_id_categoria_uoc_competente_anno;
    public $istruttoria_data_avvio;
    public $istruttoria_costo_presunto;
    public $istruttoria_modalita_acquisizione;
    public $istruttoria_id_tempi_stimati_uoc_competente;
    public $istruttoria_anno_soddisfacimento;
    public $istruttoria_id_fonte_finanziamento_proposta;
    public $istruttoria_id_categoria_registro_cespiti_proposta;
    public $istruttoria_non_coerente_piano_investimenti;
    public $istruttoria_data_chiusura_uoc_competente;
    public $istruttoria_data_scarto_uoc_competente;
    public $verifica_copertura_id_registro_cespiti;
    public $verifica_copertura_id_fonte_finanziamento;
    public $verifica_copertura_data_fine;
    public $proposta_piano_investimenti_data;
    public $dg_id_parere;
    public $dg_id_priorita;
    public $dg_id_tempi;
    public $dg_data_validazione_piano_investimenti;
    public $dg_data_scarto_piano_investimenti;
    public $monitoraggio_importo_definitivo;
    public $monitoraggio_data;
    public $monitoraggio_provvedimento;
    public $monitoraggio_fatture;
    public $monitoraggio_fornitore;
    public $monitoraggio_id_fonte_finanziamento;
    public $monitoraggio_note;
    public $monitoraggio_data_chiusura;
    
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
	
	public function __construct($id=null){				
		if ($id !== null){
			$db = ffDb_Sql::factory();

			$sql = "
					SELECT 
						*
					FROM
						investimenti_investimento                     
					WHERE
						investimenti_investimento.ID = " . $db->toSql($id) 
					;
			$db->query($sql);
			if ($db->nextRecord()){
                $this->id = $db->getField("ID", "Number", true);
                $this->codice_cdr_creazione = $db->getField("codice_cdr_creazione", "Text", true);
                $this->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $this->data_creazione = CoreHelper::getDateValueFromDB($db->getField("data_creazione", "Date", true));
                $this->richiesta_codice_cdc = $db->getField("richiesta_codice_cdc", "Text", true);
                $this->richiesta_nuova = CoreHelper::getBooleanValueFromDB($db->getField("richiesta_nuova", "Number", true));
                $this->richiesta_matricola_bene_da_sostituire = $db->getField("richiesta_matricola_bene_da_sostituire", "Text", true);
                $this->richiesta_id_categoria = $db->getField("richiesta_ID_categoria", "Number", true);
                $this->richiesta_descrizione_bene = $db->getField("richiesta_descrizione_bene", "Text", true);
                $this->richiesta_quantita = $db->getField("richiesta_quantita", "Number", true);
                $this->richiesta_motivo = $db->getField("richiesta_motivo", "Text", true);
                $this->richiesta_motivazioni_supporto = $db->getField("richiesta_motivazioni_supporto", "Text", true);
                $this->richiesta_eventuali_costi_aggiuntivi = $db->getField("richiesta_eventuali_costi_aggiuntivi", "Text", true);
                $this->richiesta_costo_stimato = $db->getField("richiesta_costo_stimato", "Number", true);
                $this->richiesta_id_priorita = $db->getField("richiesta_ID_priorita", "Number", true);
                $this->richiesta_tempi = $db->getField("richiesta_tempi", "Text", true);
                $this->richiesta_ubicazione_bene = $db->getField("richiesta_ubicazione_bene", "Text", true);
                $this->richiesta_data_chiusura = CoreHelper::getDateValueFromDB($db->getField("richiesta_data_chiusura", "Date", true));
                $this->approvazione_id_parere_direzione_riferimento = $db->getField("approvazione_ID_parere_direzione_riferimento", "Number", true);
                $this->approvazione_note_parere_direzione_riferimento = $db->getField("approvazione_note_parere_direzione_riferimento", "Text", true);
                $this->approvazione_id_priorita_direzione_riferimento = $db->getField("approvazione_ID_priorita_direzione_riferimento", "Number", true);
                $this->approvazione_id_tempi_stimati_direzione_riferimento = $db->getField("approvazione_ID_tempi_stimati_direzione_riferimento", "Number", true);
                $this->approvazione_data = CoreHelper::getDateValueFromDB($db->getField("approvazione_data", "Date", true));
                $this->approvazione_data_scarto_direzione_riferimento = CoreHelper::getDateValueFromDB($db->getField("approvazione_data_scarto_direzione_riferimento", "Date", true));                
                $this->istruttoria_id_categoria_uoc_competente_anno = $db->getField("istruttoria_ID_categoria_uoc_competente_anno", "Number", true);
                $this->istruttoria_data_avvio = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_avvio", "Date", true));
                $this->istruttoria_costo_presunto = $db->getField("istruttoria_costo_presunto", "Number", true);
                $this->istruttoria_modalita_acquisizione = $db->getField("istruttoria_modalita_acquisizione", "Text", true);
                $this->istruttoria_id_tempi_stimati_uoc_competente = $db->getField("istruttoria_ID_tempi_stimati_uoc_competente", "Number", true);
                $this->istruttoria_anno_soddisfacimento = $db->getField("istruttoria_anno_soddisfacimento", "Text", true);
                $this->istruttoria_id_fonte_finanziamento_proposta = $db->getField("istruttoria_ID_fonte_finanziamento_proposta", "Text", true);
                $this->istruttoria_id_categoria_registro_cespiti_proposta = $db->getField("istruttoria_ID_categoria_registro_cespiti_proposta", "Text", true);
                $this->istruttoria_non_coerente_piano_investimenti = CoreHelper::getBooleanValueFromDB($db->getField("istruttoria_non_coerente_piano_investimenti", "Number", true));
                $this->istruttoria_data_chiusura_uoc_competente = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_chiusura_uoc_competente", "Date", true));
                $this->istruttoria_data_scarto_uoc_competente = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_scarto_uoc_competente", "Date", true));  
                $this->verifica_copertura_id_registro_cespiti = $db->getField("verifica_copertura_ID_registro_cespiti", "Number", true);                                
                $this->verifica_copertura_id_fonte_finanziamento = $db->getField("verifica_copertura_ID_fonte_finanziamento", "Number", true);
                $this->verifica_copertura_data_fine = CoreHelper::getDateValueFromDB($db->getField("verifica_copertura_data_fine", "Date", true));
                $this->proposta_piano_investimenti_data = CoreHelper::getDateValueFromDB(CoreHelper::getDateValueFromDB($db->getField("proposta_piano_investimenti_data", "Date", true)));
                $this->dg_id_parere = $db->getField("dg_ID_parere", "Number", true);
                $this->dg_id_priorita = $db->getField("dg_ID_priorita", "Number", true);
                $this->dg_id_tempi = $db->getField("dg_ID_tempi", "Number", true);
                $this->dg_data_validazione_piano_investimenti = CoreHelper::getDateValueFromDB($db->getField("dg_data_validazione_piano_investimenti", "Date", true));
                $this->dg_data_scarto_piano_investimenti = CoreHelper::getDateValueFromDB($db->getField("dg_data_scarto_piano_investimenti", "Date", true));                
                $this->monitoraggio_importo_definitivo = $db->getField("monitoraggio_importo_definitivo", "Number", true);
                $this->monitoraggio_data = CoreHelper::getDateValueFromDB($db->getField("monitoraggio_data", "Date", true));
                $this->monitoraggio_provvedimento = $db->getField("monitoraggio_provvedimento", "Text", true);
                $this->monitoraggio_fatture = $db->getField("monitoraggio_fatture", "Text", true);
                $this->monitoraggio_fornitore = $db->getField("monitoraggio_fornitore", "Text", true);
                $this->monitoraggio_id_fonte_finanziamento = $db->getField("monitoraggio_ID_fonte_finanziamento", "Number", true);
                $this->monitoraggio_note = $db->getField("monitoraggio_note", "Text", true);
                $this->monitoraggio_data_chiusura = CoreHelper::getDateValueFromDB($db->getField("monitoraggio_data_chiusura", "Date", true));  
			}	
			else
				throw new Exception("Impossibile creare l'oggetto InvestimentiInvestimento con ID = ".$id);
		}
	}
        
    public static function getAll($filters = array()) {
        $investimenti = array();

        $db = ffDB_Sql::factory();

        $where = "WHERE 1=1 ";
        foreach($filters as $field => $value){
            $where .= " AND ".$field."=".$db->toSql($value);
        }
        $sql = "
                SELECT investimenti_investimento.*
                FROM investimenti_investimento
                " . $where . "
                ORDER BY ID"
                ;
        $db->query($sql);
        if ($db->nextRecord()){
            do{
                $investimento = new InvestimentiInvestimento();
                $investimento->id = $db->getField("ID", "Number", true);
                $investimento->codice_cdr_creazione = $db->getField("codice_cdr_creazione", "Text", true);
                $investimento->id_anno_budget = $db->getField("ID_anno_budget", "Number", true);
                $investimento->data_creazione = CoreHelper::getDateValueFromDB($db->getField("data_creazione", "Date", true));
                $investimento->richiesta_codice_cdc = $db->getField("richiesta_codice_cdc", "Text", true);
                $investimento->richiesta_nuova = CoreHelper::getBooleanValueFromDB($db->getField("richiesta_nuova", "Number", true));
                $investimento->richiesta_matricola_bene_da_sostituire = $db->getField("richiesta_matricola_bene_da_sostituire", "Text", true);
                $investimento->richiesta_id_categoria = $db->getField("richiesta_ID_categoria", "Number", true);
                $investimento->richiesta_descrizione_bene = $db->getField("richiesta_descrizione_bene", "Text", true);
                $investimento->richiesta_quantita = $db->getField("richiesta_quantita", "Number", true);
                $investimento->richiesta_motivo = $db->getField("richiesta_motivo", "Text", true);
                $investimento->richiesta_motivazioni_supporto = $db->getField("richiesta_motivazioni_supporto", "Text", true);
                $investimento->richiesta_eventuali_costi_aggiuntivi = $db->getField("richiesta_eventuali_costi_aggiuntivi", "Text", true);
                $investimento->richiesta_costo_stimato = $db->getField("richiesta_costo_stimato", "Number", true);
                $investimento->richiesta_id_priorita = $db->getField("richiesta_ID_priorita", "Number", true);
                $investimento->richiesta_tempi = $db->getField("richiesta_tempi", "Text", true);
                $investimento->richiesta_ubicazione_bene = $db->getField("richiesta_ubicazione_bene", "Text", true);
                $investimento->richiesta_data_chiusura = CoreHelper::getDateValueFromDB($db->getField("richiesta_data_chiusura", "Date", true));
                $investimento->approvazione_id_parere_direzione_riferimento = $db->getField("approvazione_ID_parere_direzione_riferimento", "Number", true);
                $investimento->approvazione_note_parere_direzione_riferimento = $db->getField("approvazione_note_parere_direzione_riferimento", "Text", true);
                $investimento->approvazione_id_priorita_direzione_riferimento = $db->getField("approvazione_ID_priorita_direzione_riferimento", "Number", true);
                $investimento->approvazione_id_tempi_stimati_direzione_riferimento = $db->getField("approvazione_ID_tempi_stimati_direzione_riferimento", "Number", true);
                $investimento->approvazione_data = CoreHelper::getDateValueFromDB($db->getField("approvazione_data", "Date", true));
                $investimento->approvazione_data_scarto_direzione_riferimento = CoreHelper::getDateValueFromDB($db->getField("approvazione_data_scarto_direzione_riferimento", "Date", true));                
                $investimento->istruttoria_id_categoria_uoc_competente_anno = $db->getField("istruttoria_ID_categoria_uoc_competente_anno", "Number", true);
                $investimento->istruttoria_data_avvio = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_avvio", "Date", true));
                $investimento->istruttoria_costo_presunto = $db->getField("istruttoria_costo_presunto", "Number", true);
                $investimento->istruttoria_modalita_acquisizione = $db->getField("istruttoria_modalita_acquisizione", "Text", true);
                $investimento->istruttoria_id_tempi_stimati_uoc_competente = $db->getField("istruttoria_ID_tempi_stimati_uoc_competente", "Text", true);
                $investimento->istruttoria_anno_soddisfacimento = $db->getField("istruttoria_anno_soddisfacimento", "Text", true);
                $investimento->istruttoria_id_fonte_finanziamento_proposta = $db->getField("istruttoria_ID_fonte_finanziamento_proposta", "Text", true);
                $investimento->istruttoria_id_categoria_registro_cespiti_proposta = $db->getField("istruttoria_ID_categoria_registro_cespiti_proposta", "Text", true);
                $investimento->istruttoria_non_coerente_piano_investimenti = CoreHelper::getBooleanValueFromDB($db->getField("istruttoria_non_coerente_piano_investimenti", "Number", true));
                $investimento->istruttoria_data_chiusura_uoc_competente = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_chiusura_uoc_competente", "Date", true));
                $investimento->istruttoria_data_scarto_uoc_competente = CoreHelper::getDateValueFromDB($db->getField("istruttoria_data_scarto_uoc_competente", "Date", true));                                
                $investimento->verifica_copertura_id_registro_cespiti = $db->getField("verifica_copertura_ID_registro_cespiti", "Number", true);                                
                $investimento->verifica_copertura_id_fonte_finanziamento = $db->getField("verifica_copertura_ID_fonte_finanziamento", "Number", true);
                $investimento->verifica_copertura_data_fine = CoreHelper::getDateValueFromDB($db->getField("verifica_copertura_data_fine", "Date", true));
                $investimento->proposta_piano_investimenti_data = CoreHelper::getDateValueFromDB($db->getField("proposta_piano_investimenti_data", "Date", true));
                $investimento->dg_id_parere = $db->getField("dg_ID_parere", "Number", true);
                $investimento->dg_id_priorita = $db->getField("dg_ID_priorita", "Number", true);
                $investimento->dg_id_tempi = $db->getField("dg_ID_tempi", "Number", true);
                $investimento->dg_data_validazione_piano_investimenti = CoreHelper::getDateValueFromDB($db->getField("dg_data_validazione_piano_investimenti", "Date", true));
                $investimento->dg_data_scarto_piano_investimenti = CoreHelper::getDateValueFromDB($db->getField("dg_data_scarto_piano_investimenti", "Date", true));
                $investimento->monitoraggio_importo_definitivo = $db->getField("monitoraggio_importo_definitivo", "Number", true);
                $investimento->monitoraggio_data = CoreHelper::getDateValueFromDB($db->getField("monitoraggio_data", "Date", true));
                $investimento->monitoraggio_provvedimento = $db->getField("monitoraggio_provvedimento", "Text", true);
                $investimento->monitoraggio_fatture = $db->getField("monitoraggio_fatture", "Text", true);
                $investimento->monitoraggio_fornitore = $db->getField("monitoraggio_fornitore", "Text", true);
                $investimento->monitoraggio_id_fonte_finanziamento = $db->getField("monitoraggio_ID_fonte_finanziamento", "Number", true);
                $investimento->monitoraggio_note = $db->getField("monitoraggio_note", "Text", true);
                $investimento->monitoraggio_data_chiusura = CoreHelper::getDateValueFromDB($db->getField("monitoraggio_data_chiusura", "Date", true));

                $investimenti[] = $investimento;
            }while($db->nextRecord());
        }
        return $investimenti;
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
				UPDATE investimenti_investimento
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
				INSERT INTO investimenti_investimento
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
            throw new Exception("Impossibile aggiornare l'oggetto InvestimentiInvestimento con ID='".$this->id."' nel DB");
	}
}
