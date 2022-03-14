<?php
class Personale extends Entity{		
    protected static $tablename = "personale";
    	
    public static function factoryFromMatricola($matricola) {
    $calling_class_name = static::class;
            $db = ffDb_Sql::factory();

    $sql = "
            SELECT 
                ".self::$tablename.".ID
            FROM
                ".self::$tablename."
            WHERE
                ".self::$tablename.".matricola = " . $db->toSql($matricola) 
            ;
            $db->query($sql);
    if ($db->nextRecord()){
                return new $calling_class_name($db->getField("ID", "Number", true));
            }
            throw new Exception("Impossibile creare l'oggetto ".$calling_class_name." con matricola = ".$matricola);
    }
	
    //restituisce array con tutti i dipendenti in anagrafica
    public static function getAll($where=array(), $order=array(array("fieldname"=>"cognome", "direction"=>"ASC"),array("fieldname"=>"nome", "direction"=>"ASC"))) {                
        //metodo classe entity
        return parent::getAll($where, $order);        
    }
	
    //restituisce un array con i cdc d'afferenza di un dipendente alla data per un tipo piano
    //viene restituito un array di array contententi PersonaleCdc e Cdc    
    public function getCdcAfferenzaInData(TipoPianoCdr $tipo_piano_cdr, $date){
            $afferenza = array();
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);	
            //vengono estratti tutti i cdc associati alla matricola personale
            $cdc_personale = CdcPersonale::getAll(array("matricola_personale" => $this->matricola));
            foreach($cdc_personale AS $cdc_dipendente){
                    //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
                    if (strtotime($cdc_dipendente->data_inizio) <= strtotime($date) && ($cdc_dipendente->data_fine == null || strtotime($cdc_dipendente->data_fine) >= strtotime($date))){
                            try {
                                    $cdc = Cdc::factoryFromCodice($cdc_dipendente->codice_cdc, $piano_cdr);
                                    $afferenza[] = array("cdc_personale" => $cdc_dipendente, 
                                                                            "cdc" => $cdc);
                            } 				
                            catch (Exception $ex) {
                                  
                            }
                    }
            }			
            return $afferenza;
    }	
    
    //restituisce un array con i cdr d'afferenza di un dipendente alla data per un tipo piano
    //viene restituito un array di array contententi cdr e peso totale su cdr
    //i risultati sono restituiti in ordine di livello gerarchico
    public function getCdrAfferenzaInData(TipoPianoCdr $tipo_piano_cdr, $date){
        $cdc_afferenza = array();
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);	
        //vengono estratti tutti i cdc associati alla matricola personale
        $cdc_personale = CdcPersonale::getAll(array("matricola_personale" => $this->matricola));
        foreach($cdc_personale AS $cdc_dipendente){
                //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
                if (strtotime($cdc_dipendente->data_inizio) <= strtotime($date) && ($cdc_dipendente->data_fine == null || strtotime($cdc_dipendente->data_fine) >= strtotime($date))){
                        try {
                                $cdc = Cdc::factoryFromCodice($cdc_dipendente->codice_cdc, $piano_cdr);
                                $cdc_afferenza[] = array("cdc_personale" => $cdc_dipendente, 
                                                                        "cdc" => $cdc);
                        } 				
                        catch (Exception $ex) {

                        }
                }
        }	
        $cdr_afferenza = array();
        foreach ($cdc_afferenza as $afferenza_cdc){
            $found = false;
            foreach($cdr_afferenza as $cdr_dipendente) {
                if ($cdr_dipendente->id == $afferenza_cdc["cdc"]->id_cdr) {
                    $afferenza_cdc["cdc_personale"]->percentuale += $afferenza_cdc["cdc_personale"]->percentuale;
                    $found = true;
                    break;
                }
            }
            if ($found == false){
                $cdr = new Cdr ($afferenza_cdc["cdc"]->id_cdr);                        
                $cdr_afferenza[] = array("cdr"=>$cdr, "peso_cdr"=>$afferenza_cdc["cdc_personale"]->percentuale);
            }
        }           
        return $cdr_afferenza;
    }
    
    //restituisce gli ultimi cdr delle posizioni chiuse (non attive) al quale il dipendente risulta afferente
    //viene restituito un array di array contententi cdr e peso totale su cdr
    //se passato l'anno come parametro restituisce l'ultimo cdr di afferenza nell'anno
    public function getCdrUltimaAfferenza(TipoPianoCdr $tipo_piano_cdr, AnnoBudget $anno = null){
            $cdr_afferenza = array();
            $cdc_personale = CdcPersonale::getAll(array("matricola_personale" => $this->matricola));
            $ultima_data = null;
            
            //viene recuperata la data di fine assegnazione più recente
            foreach($cdc_personale AS $cdc_dipendente){ 
                if ($cdc_dipendente->data_fine !== null) {                 
                    $data_obj = DateTime::createFromFormat("Y-m-d", $cdc_dipendente->data_fine);                
                    if ($anno == null || $anno->descrizione == $data_obj->format("Y")) {
                        if ($ultima_data == null || strtotime($cdc_dipendente->data_fine) > strtotime($ultima_data)) {
                            $ultima_data = $cdc_dipendente->data_fine;
                        }
                    } 
                }
            }
            //se è stata trovata almeno un'occorrenza
            if ($ultima_data !== null){   
                //vengono recuperati i cdc di assegnazione nell'ultima data 
                $cdc_afferenza = array();
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $ultima_data);
                foreach($cdc_personale AS $cdc_dipendente){
                    if (strtotime($cdc_dipendente->data_fine) == strtotime($ultima_data)) {
                        try {
                           $cdc = Cdc::factoryFromCodice($cdc_dipendente->codice_cdc, $piano_cdr);
                           $cdc_afferenza[] = array("cdc_personale" => $cdc_dipendente, 
                                                                    "cdc" => $cdc);
                        } 				
                        catch (Exception $ex) {
                                
                        }
                    }
                }                
                foreach ($cdc_afferenza as $afferenza_cdc){
                    $found = false;
                    foreach($cdr_afferenza as $cdr_dipendente) {                    
                        if ($cdr_dipendente->id == $afferenza_cdc["cdc"]->id_cdr) {
                            $afferenza_cdc["cdc_personale"]->percentuale += $afferenza_cdc["cdc_personale"]->percentuale;
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false){
                        $cdr = new Cdr ($afferenza_cdc["cdc"]->id_cdr);                        
                        $cdr_afferenza[] = array("cdr"=>$cdr, "peso_cdr"=>$afferenza_cdc["cdc_personale"]->percentuale, "livello"=>$cdr->getLivelloGerarchico());
                    }
                }
            }
            usort($cdr_afferenza, "cdrLevelCmp");
            return $cdr_afferenza;
    }
       
    //restituisce un array tutta la carriera del dipendente nel caso non vengano passate date
    //altirmenti viene resituito un oggetto carriera con la carriera attualizzata alla data considerata
    public function getCarriera($data=null){
            if ($data == null){
                    return CarrieraPersonale::getAll(array("matricola_personale"=>$this->matricola));		
            }
            else{			
                    foreach(CarrieraPersonale::getAll(array("matricola_personale"=>$this->matricola)) as $carriera) {
                            //gli eventi di carriera sono ordinati per data inizio decrescente (dalla più recente)
                            //la prima occorrenza con data inizio precedente alla data considerata sarà quella valida
                            if (strtotime($carriera->data_inizio) < strtotime($data)) {							
                                    return $carriera;
                            }
                    }
                    return false;
            }
    }		

    //viene restituito un array con i cdr di responsabilità diretta in un anno di budget (anche su piani cdr differenti)  
    public function getCodiciCdrResponsabilitaAnno(AnnoBudget $anno){        
        $elenco_cdr_resp = array();                
        foreach (ResponsabileCdr::getResponsabiliCdrAnno($anno) as $responsabile_anno) {
            if ($responsabile_anno->matricola_responsabile == $this->matricola) {
                $elenco_cdr_resp[] = $responsabile_anno->codice_cdr;
            }
        }                     
        return $elenco_cdr_resp;
    }

    //viene restituito un array con i cdr di responsabilità diretta in uno specifico piano dei cdr
    //resitutisce un array con oggetti Cdr cdr, boolean resp_diretta
    public function getCdrResponsabilitaPiano(PianoCdr $piano_cdr, DateTime $data_riferimento){
        $elenco_cdr_resp = array();
        foreach($piano_cdr->getCdr() as $cdr){            
            $responsabile = $cdr->getResponsabile($data_riferimento);
            if($responsabile->id_personale == $this->id){
                $elenco_cdr_resp[] = array("cdr" => $cdr);
            }
        }

        return $elenco_cdr_resp;
    }
    
    //viene restituito un array con tutti i cdr visibili in un determinato piano cdr
    function getCdrVisibiliPianoCdr(PianoCdr $piano_cdr, DateTime $date){
        $user = LoggedUser::getInstance();		
        $cdr_visibili = array();
        if (!($user->hasPrivilege("cdr_view_all") == true)){
            $cdr_responsabilità = $this->getCdrResponsabilitaPiano($piano_cdr, $date);
        }
        else {
            $cdr_responsabilità = array();
            foreach($piano_cdr->getCdr() as $cdr){                
                $responsabile = $cdr->getResponsabile($date);
                if($responsabile->id_personale == $this->id){			
                    $cdr_responsabilità[] = array("cdr" => $cdr);
                }
                else {
                    $cdr_responsabilità[] = array("cdr" => $cdr);
                }
            }						
        }
        foreach ($cdr_responsabilità AS $cdr){
            $cdr_visibili[] = array(
                "cdr" => $cdr["cdr"],
                "livello" => $cdr["cdr"]->getLivelloGerarchico(),
            );
        }								
        foreach ($cdr_visibili as $cdr){
            foreach ($cdr["cdr"]->getGerarchia() as $figlio_gerarchico){
                $found = false;
                foreach ($cdr_visibili as $cdr_visibile) {
                    if ($figlio_gerarchico["cdr"]->id == $cdr_visibile["cdr"]->id){
                        $found = true;
                        break;
                    }
                }
                if ($found == false){
                    $cdr_visibili[] = array(
                        "cdr" => $figlio_gerarchico["cdr"],
                        "livello" => $figlio_gerarchico["cdr"]->getLivelloGerarchico(),
                    );
                }
            }
        }			

        //viene effettuato un ordinamento per ordine gerarchico
        usort($cdr_visibili, "cdrLevelCmp");		
        return $cdr_visibili;
    }	

    //restituisce true se il dipendente risulta attivo in azienda nella data passata come parametro
    public function isAttivoInData ($date) {
        //vengono estratti tutte le afferenze del dipendente sui vari piani dei cdr per verificare che ce ne sia almeno una attiva
        $attivo = false;
        foreach (TipoPianoCdr::getAll() as $tipo_piano_cdr) {
            foreach ($this->getCdcAfferenzaInData($tipo_piano_cdr, $date) as $cdc_pers) {
                if (
                        (	
                                strtotime($cdc_pers["cdc_personale"]->data_inizio) <= strtotime($date)) &&
                                (	
                                        strtotime($cdc_pers["cdc_personale"]->data_fine) >= strtotime($date) ||
                                        $cdc_pers["cdc_personale"]->data_fine == null
                                )						
                        ){
                    $attivo = true;
                    break 2;
                }
            }
        }
        return $attivo;
    }
    
    public function isAttivoAnno (AnnoBudget $anno) {
        //vengono estratti tutte le afferenze del dipendente sui vari piani dei cdr per verificare che ce ne sia almeno una attiva
        $attivo = false;       
        foreach (CdcPersonale::getAll(array("matricola_personale" => $this->matricola)) as $cdc_dipendente){
            //se la data inizio è precedente alla data corretnte inclusa e la data fine è successiva alla data corrente inclusa)
            if (
                    strtotime($cdc_dipendente->data_inizio) <= strtotime($anno->descrizione."-12-31") && 
                    ($cdc_dipendente->data_fine == null || strtotime($cdc_dipendente->data_fine) >= strtotime($anno->descrizione."-01-01"))
                ){
                $attivo = true;
                break;
            }
        }                
        return $attivo;
    }
}

function cdrLevelCmp ($cdr1, $cdr2) {
	return ($cdr1["livello"] > $cdr2["livello"]);		
}
