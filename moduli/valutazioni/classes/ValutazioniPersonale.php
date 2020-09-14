<?php
class ValutazioniPersonale extends Personale{   
    public $periodo_riferimento = null;
    public $carriera = null;
    public $qualifica_interna = null;    
    public $cdr_afferenza = null;
    public $cdr_riferimento = null;
    public $valutatore_suggerito = null;
    public $categoria = null;
    public $anomalie = "";
        
    public function __construct($id = null, ValutazioniPeriodo $periodo, $ottimizza = true){        
        //selezione anno
        $anno = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        $data_fineObject = new DateTime($periodo->data_fine);
        $tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();      
        
        //viene utilizzata una query differente da quella di Personale per ottimizzare i tempi        
        if ($id !== null) {
            $db = ffDb_Sql::factory();

            $sql = "
                    SELECT 
                        personale.ID,
                        personale.matricola,
                        personale.cognome,
                        personale.nome,
                        carriera.ID AS ID_carriera,
                        carriera.matricola_personale,
                        carriera.ID_tipo_contratto,
                        carriera.ID_qualifica_interna,
                        carriera.ID_rapporto_lavoro,
                        carriera.perc_rapporto_lavoro,
                        carriera.posizione_organizzativa,
                        carriera.data_inizio,
                        carriera.data_fine,
                        qualifica_interna.ID AS ID_qualifica_interna,
                        qualifica_interna.codice,
                        qualifica_interna.descrizione,
                        qualifica_interna.dirigente,
                        qualifica_interna.ID_ruolo
                    FROM
                        personale
                        LEFT JOIN carriera ON personale.matricola = carriera.matricola_personale
                        LEFT JOIN qualifica_interna ON carriera.ID_qualifica_interna = qualifica_interna.ID
                    WHERE
                        personale.ID = " . $db->toSql($id) . "
                        ORDER BY carriera.data_inizio DESC"
                    ;
            $db->query($sql);
            if ($db->nextRecord()){
                $this->periodo_riferimento = $periodo;
                $first = true;
                $carriere = array();
                do {
                    if ($first == true) {
                        $this->id = $db->getField("ID", "Number", true);
                        $this->matricola = $db->getField("matricola", "Text", true);			
                        $this->cognome = $db->getField("cognome", "Text", true);			
                        $this->nome = $db->getField("nome", "Text", true);
                        
                        $first = false;                    
                    }
                    $carriera = new CarrieraPersonale();
                    $carriera->id = $db->getField("ID_carriera", "Number", true);
                    $carriera->matricola_personale = $db->getField("matricola_personale", "Text", true);
                    $carriera->id_tipo_contratto = $db->getField("ID_tipo_contratto", "Number", true);
                    $carriera->id_qualifica_interna = $db->getField("ID_qualifica_interna", "Number", true);
                    $carriera->id_rapporto_lavoro = $db->getField("ID_rapporto_lavoro", "Number", true);
                    $carriera->perc_rapporto_lavoro = $db->getField("perc_rapporto_lavoro", "Number", true);
                    $carriera->posizione_organizzativa = $db->getField("posizione_organizzativa", "Number", true);
                    $carriera->data_inizio = CoreHelper::getDateValueFromDB($db->getField("data_inizio", "Date", true));
                    $carriera->data_fine = CoreHelper::getDateValueFromDB($db->getField("data_fine", "Date", true));
                    
                    $qualifica_iterna = new QualificaInterna();
                    $qualifica_iterna->id = $db->getField("ID_qualifica_interna", "Number", true);
                    $qualifica_iterna->codice = $db->getField("codice", "Text", true);
                    $qualifica_iterna->descrizione = $db->getField("descrizione", "Text", true);
                    $qualifica_iterna->dirigente = CoreHelper::getBooleanValueFromDB($db->getField("dirigente", "Number", true));
                    $qualifica_iterna->id_ruolo = $db->getField("ID_ruolo", "Number", true);
                    
                    $carriere[] = array("carriera"=>$carriera,"qualifica_interna"=>$qualifica_iterna);
                    unset($carriera);
                    unset($qualifica_iterna);
                } while($db->nextRecord());
            }
            else {
                throw new Exception("Impossibile creare l'oggetto Personale con ID = ".$id);
            }            
        }
                
        //**********************************************************************
        //valorizzazione del cdr di riferimento e del valutatore suggerito
        //viene considerato il cdr con percentuale maggiore fra quelli del dipendente (si potrà sempre cambiare il valutatore)
        $this->cdr_afferenza = $this->getCdrAfferenzaInData($tipo_piano_cdr, $periodo->data_fine);            
        if (count ($this->cdr_afferenza) == 0) {
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo->data_fine);
            $ultimi_cdr_afferenza = $this->getCdrUltimaAfferenza($tipo_piano_cdr);
            if (count ($ultimi_cdr_afferenza) > 0) {
                foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                    $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                    if ($cdr_attuale->id == $cdr_aff["cdr"]->id) {
                        $this->cdr_afferenza[] = $cdr_aff;
                    }
                }
            }
        }
        //il primo cdc estratto sarà quello con percentuale maggiore (per l'ordinamento con cui vengono estratti i dati dal record)
        foreach ($this->cdr_afferenza as $afferenza_cdr){
            $this->cdr_riferimento = $afferenza_cdr["cdr"];
            break;
        }
        if (empty($this->cdr_afferenza)){
            $this->accodaAnomalia("Nessun CdR di afferenza");
        }
        else {                           
            $responsabile_cdr = $this->cdr_riferimento->getResponsabile($data_fineObject);
            if ($responsabile_cdr->matricola_responsabile == $this->matricola) {
                $this->valutatore_suggerito = $this->cdr_riferimento->getPrimoResponsabilePadre($data_fineObject);
            }                        
            else {
                $resp_cdr = $this->cdr_riferimento->getResponsabile($data_fineObject);            
                $this->valutatore_suggerito = $resp_cdr;
            }
            if ($this->valutatore_suggerito == null || $this->valutatore_suggerito->matricola_responsabile == ""){                
                $this->accodaAnomalia("Nessun valutatore suggerito");
            }
        }
         
        //**********************************************************************        
        //valorizzazione della categoria
        //recupero della carriera dipendente        
        foreach($carriere as $carriera) {
            //gli eventi di carriera sono ordinati per data inizio decrescente (dalla più recente)
            //la prima occorrenza con data inizio precedente alla data considerata sarà quella valida
            if (strtotime($carriera["carriera"]->data_inizio) < strtotime($periodo->data_fine)) {					
                $this->carriera = $carriera["carriera"];
                $this->qualifica_interna = $carriera["qualifica_interna"];
                break;
            }
        }                
              
        if ($this->carriera !== null){
            //$qualifica_interna = new QualificaInterna($carriera->id_qualifica_interna);                             
            if($this->qualifica_interna !== null) {
                //valorizzazione categoria (null se non trovata o se anomalie)
                foreach ($anno->getCategorieAnno() as $categoria) {            
                    //viene verificata la regola per la categoria                       
                    if ($categoria->verificaAppartenenzaPersonale ($this)) {                        
                        //viene verififcato che il dipendente corrisponda ad una ed una sola categoria
                        if ($this->categoria == null) {
                            $this->categoria = $categoria;
                        }
                        else {
                            $this->categoria = null;
                            $this->accodaAnomalia("Categoria multipla");
                            break;
                        }
                    }                    
                }
            }
        } 
        else {
            $this->accodaAnomalia("Nessuna carriera associata al dipendente");
        }
        if ($this->categoria == null){
            $this->accodaAnomalia("Nessuna categoria identificabile");
        }
    }
    
    //metodo per l'accodamento delle anomalie
    private function accodaAnomalia($anomalia){
        strlen($this->anomalie)?$this->anomalie.="\n":$this->anomalie;
        $this->anomalie .= $anomalia;
    }  
}