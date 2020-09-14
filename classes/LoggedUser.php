<?php
final class LoggedUser {
    public $matricola_utente_collegato;
    public $matricola_utente_selezionato;
	//gruppi utente
    public $user_groups = array();
    //deleghe accesso
    public $deleghe_accesso = array();
	//privilegi
	public $user_privileges = array();
	
    public static $groups = array	(
										1 => "Superamdin",
										2 => "Admin",
										3 => "Controllo di Gestione",
										4 => "Direzione",
										5 => "Responsabile cdr",
									);
	
    public static function Instance(){
        static $inst = null;
        if ($inst === null) {
            $inst = new LoggedUser();
        }
        return $inst;                
    }

    private function __construct(){
		$cm = cm::getInstance();
		//vengono recuperati i parametri globali della pagina
        $anno = $cm->oPage->globals["anno"]["value"];        
        
		//*************
		//gruppi utente
		//superadmin
		if (get_session("UserLevel") == 3){
			$this->user_groups[] = 1;		
		}
		
		//admin
		if (get_session("UserLevel") == 2){
			$this->user_groups[] = 2;	
		}
		
		//controllo di gestione
		/*if (){
			$this->user_groups[] = 3;
		}*/
		
		//direzione
		/*if (){
			$this->user_groups[] = 4;
		}*/
		
		//*************
		//privilegi core		
        foreach ($this->user_groups as $user_group){
			//visualizzazione di tutti i cdr
			if ($user_group == 1 || $user_group == 2 || $user_group == 3) {
				//visualizzazione di tutti i cdr (se nessun dipendente selezionato)
				if(!array_search("cdr_view_all", $this->user_privileges, true) && ($cm->oPage->globals["dipendente"]["value"] == null)){
					$this->user_privileges[] = "cdr_view_all";					
				}				
				
				if(!array_search("user_selection", $this->user_privileges, true)){
					$this->user_privileges[] = "user_selection";					
				}	
			
				if(!array_search("gestione_azienda", $this->user_privileges, true)){
					$this->user_privileges[] = "gestione_azienda";					
				}	
                
                if ($user_group == 1) {
                    if(!array_search("moduli_admin", $this->user_privileges, true)){
                        $this->user_privileges[] = "moduli_admin";					
                    }                    
                }
                
                if ($user_group ==1 || $user_group == 2) {
                    if(!array_search("deleghe_admin", $this->user_privileges, true)){
                        $this->user_privileges[] = "deleghe_admin";					
                    }
                    if(!array_search("anni_budget_admin", $this->user_privileges, true)){
                        $this->user_privileges[] = "anni_budget_admin";					
                    }
                }
			}	            
		}
		
        //recupero matricola dal sistema autenticazione
        if (USE_MS_ONLINE_LOGIN == true) {               
            $matricola_utente = get_session('ms_user_matricola');
        }        
        else if (LDAP_SERVER !== false) {
            if (defined("AD_USER_MATRICOLA_FIELD")) {
                $matricola_field = AD_USER_MATRICOLA_FIELD;           
                $ldap_info = mod_security_ldapGetUserInfo(LDAP_SERVER, get_session("UserID"), get_session("LdapPwd"), defined("LDAP_DC_STRING")?LDAP_DC_STRING:"", array($matricola_field));								
                $matricola_utente = $ldap_info[$matricola_field];      
                }
            else {
                ffErrorHandler::raise("Campo AD_USER_MATRICOLA_FIELD non definito.");
            }
        }
        else {
            $matricola_utente = mod_security_getUserInfo("matricola")->getValue();
        }
        $this->matricola_utente_selezionato = $this->matricola_utente_collegato = $matricola_utente;
        
        //vengono salvate nell'oggetto le eventuali deleghe dell'utente con eventuali moduli delegati
        //recupero di eventuali deleghe di accesso
        //vengono recuperate tutte le eventuali deleghe dell'utente ed i moduli per cui vale la delega
        $moduli_delegati = array();
        $deleghe = DelegaAccesso::getAll(array("matricola_delegato" => $this->matricola_utente_collegato));
        if (!empty($deleghe)) {
            if(!array_search("delega_accesso", $this->user_privileges, true)){
                $this->user_privileges[] = "delega_accesso";					
            }
            foreach ($deleghe as $delega) {                
                foreach ($delega->getModuliDelega() as $modulo_delega) {
                    $moduli_delegati[] = $modulo_delega;
                }            
                $this->deleghe_accesso[] = array("matricola_utente" => $delega->matricola_utente,
                                                "moduli_delega" => $moduli_delegati,
                                                );
            }
        }
        
		//matricola dell'utente loggato con forzatura nel caso l'utente sia admin e abbia selezionato un dipendente	
		//la matricola è già stata verificata in fase di recupero dei parametri in conf/common.php
		if ($cm->oPage->globals["dipendente"]["value"] != null && ($this->hasPrivilege("user_selection") || $this->hasPrivilege("delega_accesso"))){
            $dipendente_selezionato = $cm->oPage->globals["dipendente"]["value"];
            //nel caso in cui l'utente sia semplicemente delegato senza il privilegio di visualizzare tutte le utenze
            //viene verificato che il parametro selezionato sia uno degli utenti per cui ha la delega
            $privilegio_su_utente = true;            
            if ($this->hasPrivilege("user_selection") == false && $this->hasPrivilege("delega_accesso") == true){
                $privilegio_su_utente = false;
                foreach ($this->deleghe_accesso as $delega) {                    
                    if ($dipendente_selezionato->matricola == $delega["matricola_utente"]) {
                        $privilegio_su_utente = true;
                        break;
                    }
                }
            }
            if ($privilegio_su_utente == true){
                $this->matricola_utente_selezionato = $dipendente_selezionato->matricola;
            }
		}
		
		//responsabile cdr anno	
		try {
				$personale = Personale::factoryFromMatricola($this->matricola_utente_selezionato);
			} 				
			catch (Exception $ex) {
				ffErrorHandler::raise("Errore di autenticazione: impossibile trovare l'utente con matricola '" . $this->matricola_utente_selezionato . "' in anagrafica. Contattare l'assistenza.");
			}		
		if(count($personale->getCdrResponsabilitaAnno($anno))) {
			$this->user_groups[] = 5;	
		}		                
    }        
	
    //verifica di uno specifico privilegio per l'utente
	public function hasPrivilege($privilege) {	
		if(array_search($privilege, $this->user_privileges, true) !== false)
			return true;		
		else
			return false;
	}
    
    //viene verificato se è presente una delega dell'utente per il modulo
    public function hasDelegaModuloAnno(Modulo $modulo) {        
        $cm = cm::getInstance();
        $delega_modulo = false;
        if ($this->hasPrivilege("delega_accesso")){
            //viene verificato che per la delega dell'utente selezionato il modulo sia previsto       
            foreach ($this->deleghe_accesso as $delega) {  
                $dipendente_selezionato = $cm->oPage->globals["dipendente"]["value"];
                if ($dipendente_selezionato->matricola == $delega["matricola_utente"]) {
                    foreach($delega["moduli_delega"] as $modulo_delega){                           
                        if ($modulo->id == $modulo_delega->id){
                            $delega_modulo = true;
                            break;
                        }
                    }
                    break;
                }
            }                                    
        }
        return $delega_modulo;
    }
}