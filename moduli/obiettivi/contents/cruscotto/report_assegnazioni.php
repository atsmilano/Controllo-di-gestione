<?php
$user = LoggedUser::getInstance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");
$tipo_piano = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date);

//il report visualizzerà dati differenti a seconda che l'utnte sia amministratore o responsabile CdR
//predisposizione dati per la grid	
//popolamento della grid tramite array		
//costruzione dell'array dei cdr da verificare
//admin visualizza tutti i cdr mentre responsabile cdr visualizza il proprio ramo gerarchico
$grid_fields = array("ID");
$cdr_piano = $piano_cdr->getCdr();
//vengono recuperati tutti i responsbili per i cdr da verificare e dell'anagrafica corrente per ottimizzare il numero di query
$responsabili_cdr_data = ResponsabileCdr::getResponsabiliCdrInData($dateTimeObject);
foreach ($cdr_piano as $key => $cdr) {
    $cdr_piano[$key]->responsabile = $cdr->getResponsabile($dateTimeObject);    
}
if ($user->hasPrivilege("cdr_view_all")) {
    $view_all = true;
    array_push($grid_fields, "padre_strategico");
    $cdr_to_check = $cdr_piano;                
}
else {
    $view_all = false; 
    $cdr_selezionato = new Cdr($cm->oPage->globals["cdr"]["value"]->id);
    $cdr_to_check = array();
    foreach ($cdr_selezionato->getGerarchia() as $cdr) {        
        $cdr["cdr"]->responsabile = $cdr["cdr"]->getResponsabile($dateTimeObject);
        $cdr_to_check[] = $cdr["cdr"];
    }     
    unset ($cdr);
}
array_push($grid_fields, "cdr_padre", "cdr");

//estrazione di tutti gli obiettivi dell'anno
//si opta per questa soluzione piuttosto che per il recupero degli obiettivi di ogni singolo cdr per ottimizzazione del numero di query
//soluzione db adottata esclusivamente per ottimizzazione risorse
$ob_obiettivi_cdr_anno = array();
$db = ffDb_Sql::factory();
$sql = "
    SELECT 
        obiettivi_obiettivo_cdr.*  
    FROM 
        obiettivi_obiettivo 
        INNER JOIN obiettivi_obiettivo_cdr
            ON obiettivi_obiettivo.ID = obiettivi_obiettivo_cdr.ID_obiettivo
    WHERE     
        (obiettivi_obiettivo.data_eliminazione is null || obiettivi_obiettivo.data_eliminazione = '0000-00-00')
        AND
        (obiettivi_obiettivo_cdr.data_eliminazione is null || obiettivi_obiettivo_cdr.data_eliminazione = '0000-00-00')
        AND
        obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id);
$db->query($sql);
if ($db->nextRecord()) {
    do {            
        $ob_cdr_obj = new ObiettiviObiettivoCdr();                

        $ob_cdr_obj->id = $db->getField("ID", "Number", true);
        $ob_cdr_obj->id_obiettivo = $db->getField("ID_obiettivo", "Number", true);
        $ob_cdr_obj->codice_cdr = $db->getField("codice_cdr", "Text", true);
        $ob_cdr_obj->codice_cdr_coreferenza = $db->getField("codice_cdr_coreferenza", "Text", true);
        if ($db->getField("ID_tipo_piano_cdr", "Number", true) == 0) {
            $ob_cdr_obj->id_tipo_piano_cdr = null;
        } else {
            $ob_cdr_obj->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
        }
        $ob_cdr_obj->peso = $db->getField("peso", "Text", true);
        $ob_cdr_obj->azioni = $db->getField("azioni", "Text", true);
        $ob_cdr_obj->id_parere_azioni = $db->getField("ID_parere_azioni", "Number", true);
        $ob_cdr_obj->note_azioni = $db->getField("note_azioni", "Text", true);
        //data_chiusura_modifiche
        $ob_cdr_obj->data_chiusura_modifiche = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_modifiche", "Date", true));
        $ob_cdr_obj->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));       

        $ob_obiettivi_cdr_anno[]= $ob_cdr_obj;        
    } while ($db->nextRecord());
}

//recupero di tutti gli obiettivi_cdr_personale dell'anno
//vengono recuperate solamente le matricole del personale che ha almeno un obiettivo assegnato
$ob_personale_associati = array();
$sql = "
    SELECT 
        DISTINCT obiettivi_obiettivo_cdr_personale.matricola_personale
    FROM obiettivi_obiettivo_cdr_personale
        INNER JOIN obiettivi_obiettivo_cdr 
            ON obiettivi_obiettivo_cdr_personale.ID_obiettivo_cdr = obiettivi_obiettivo_cdr.ID
        INNER JOIN obiettivi_obiettivo
            ON obiettivi_obiettivo_cdr.ID_obiettivo = obiettivi_obiettivo.ID
    WHERE 
        (obiettivi_obiettivo_cdr_personale.data_eliminazione is null || obiettivi_obiettivo_cdr_personale.data_eliminazione = '0000-00-00')
        AND
        obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id) . "
";
$db->query($sql);
if ($db->nextRecord()) {
    do {
        $ob_personale_associati[] = ["matricola" => $db->getField("matricola_personale", "Text", true)];     
    } while ($db->nextRecord());
}

//recupero degli obiettivi senza presa visione con conteggio
$ob_personale_senza_accettazione = array();
$sql = "
    SELECT 
        obiettivi_obiettivo_cdr_personale.matricola_personale,
        COUNT(obiettivi_obiettivo_cdr_personale.ID) as non_accettati
    FROM obiettivi_obiettivo_cdr_personale
        INNER JOIN obiettivi_obiettivo_cdr 
            ON obiettivi_obiettivo_cdr_personale.ID_obiettivo_cdr = obiettivi_obiettivo_cdr.ID
        INNER JOIN obiettivi_obiettivo
            ON obiettivi_obiettivo_cdr.ID_obiettivo = obiettivi_obiettivo.ID
    WHERE 
        (obiettivi_obiettivo_cdr_personale.data_eliminazione is null || obiettivi_obiettivo_cdr_personale.data_eliminazione = '0000-00-00')
        AND
        obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id) . "
        AND
        (obiettivi_obiettivo_cdr_personale.data_accettazione is null || obiettivi_obiettivo_cdr_personale.data_accettazione = '0000-00-00')
    GROUP BY
        obiettivi_obiettivo_cdr_personale.matricola_personale
";
$db->query($sql);
if ($db->nextRecord()) {
    do {
        $ob_personale_senza_accettazione[] = ["matricola" => $db->getField("matricola_personale", "Text", true),
                                                "n_non_accettati" => $db->getField("non_accettati", "Text", true)];    
    } while ($db->nextRecord());
}

//estrazione di tutti gli obiettivi individuali
//assegnazione individuale obiettivi (se modulo attivo)
/*
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $filters = array("ID_anno_budget" => $anno->id);
    $obiettivi_individuali_anno = ObiettiviIndividuali\AssegnazioneIndividuale::getAll($filters);    
}*/

//vengono filtrati tutti i cdr da verificare
$cdr_report_obiettivi = array();
$cdr_report_peso = array();
$cdr_report_chiusura = array();
$cdr_report_personale = array();
$cdr_report_personale_no_presa_visione = array();
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $report_personale_no_ob_ind = array();
    $report_personale_ob_ind_non_accettati = array();
}

//array per i filtri nella grid
$articolazioni_organizzative_obiettivi_filter = array();
$articolazioni_organizzative_peso_filter = array();
$cdr_padri_obiettivi_filter = array();
$cdr_padri_peso_filter = array();
$cdr_padri_personale_filter = array();
$cdr_filter = array();
$cdr_padri_personale_no_presa_visione_filter = array();
$cdr_no_presa_visione_filter = array();
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $articolazioni_organizzative_personale_no_ob_ind = array();
    $cdr_padri_personale_no_ob_ind_filter = array();
    $cdr_personale_no_ob_ind_filter = array();
}

function getCdrPadre($id_padre, $cdr_piano) {
    foreach ($cdr_piano as $cdr) {
        if ($cdr->id == $id_padre) {
            return $cdr;
        }
    }
}

//viene estratta l'anagrafe del personale per ottimizzazioni
$personale = array();
foreach (PersonaleObiettivi::getAll() as $dipendente) {
    $personale[$dipendente->matricola] = $dipendente;
}
//contatore per generare id univoci (non utilizzati)
$i=0;

foreach ($cdr_to_check as $cdr) {
    //vengono verificati i cdr per l'inserimento nei report
    //vengono considerati solamente i cdr con almeno un dipendente assegnato oppure di responsabilità diretta    
    //recupero del personale cdt        
    $to_check = false;
    $personale_cdr = $cdr->getPersonaleCdcAfferentiInData($dateTimeObject);   
    if (count($personale_cdr)) {
        $to_check = true;               
        //dal personale vengono eliminati i responsabili e rieffettuato il controllo
        foreach ($personale_cdr as $key => $dipendente_cdr) {
            $is_responsabile = false;
            foreach($responsabili_cdr_data as $responsabile_cdr){
                if ($responsabile_cdr->matricola_responsabile == $dipendente_cdr->matricola_personale) {
                    $is_responsabile = true;
                    break;
                }
            }            
            if ($is_responsabile){
                unset($personale_cdr[$key]);
            }                        
        }                        
    }
    else {
        //viene verificato che il cdr sia di responsabilità diretta
        $resp_diretta = false;
        foreach($responsabili_cdr_data as $responsabile_cdr){
            if ($responsabile_cdr->codice_cdr == $cdr->codice) {
                $resp_diretta = true;
                break;
            }
        }
        if ($resp_diretta == true) {
            $to_check = true;
        }        
    }
    //se il cdr rispetta i criteri di inclusione nel report vengono effettuate le verifiche
    if ($to_check == true) {
        $add_to_report_obiettivi = false;
        $add_to_report_peso = false;
        $add_to_report_chiusura = false;
        $add_to_report_personale = false;
        $add_to_report_accettazione = false;
        $add_to_report_ob_ind = false;        
        $anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $dateTimeObject);               
      
        //viene verificato che il cdr non abbia obiettivi assegnati
        $obiettivi_assegnati_cdr = array();
        foreach($ob_obiettivi_cdr_anno as $obiettivo_cdr) {
            if ($obiettivo_cdr->codice_cdr == $cdr->codice
                && ($obiettivo_cdr->id_tipo_piano_cdr == 0 || $obiettivo_cdr->id_tipo_piano_cdr == $tipo_piano->id)
                ) {
                $obiettivi_assegnati_cdr[] = $obiettivo_cdr;
            }
        }
        if (!count($obiettivi_assegnati_cdr)) {            
            $add_to_report_obiettivi = true;
        }
        //altrimenti viene verificato che i pesi siano assegnati (somma dei pesi > 0)
        //viene inoltre verificato che tutti gli obiettivi del cdr siano chiusi
        else {            
            if($anagrafica_cdr->getPesoTotaleObiettivi($anno, null, $obiettivi_assegnati_cdr) == 0) {
                $add_to_report_peso = true;                
            }
            $obiettivi_non_chiusi = array();
            foreach ($obiettivi_assegnati_cdr as $obiettivo_assegnato_cdr) {
                if ($obiettivo_assegnato_cdr->data_chiusura_modifiche == null) {
                    $add_to_report_chiusura = true;
                    if ( !array_key_exists($cdr->id, $obiettivi_non_chiusi)) {
                        $obiettivi_non_chiusi[$cdr->id]["n_ob_senza_chiusura"]=0;
                        $obiettivi_non_chiusi[$cdr->id]["n_tot_ob"]=count($obiettivi_assegnati_cdr);
                    }
                    $obiettivi_non_chiusi[$cdr->id]["n_ob_senza_chiusura"]++;                    
                }
            }

            //assegnazioni al personale          
            if (count($personale_cdr)){               
                $personale_senza_obiettivi = array();
                $personale_senza_obiettivi_individuali = array();
                $personale_obiettivi_senza_presa_visione = array(); 
                $responsabili_cdr_figli = [];
                if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {                    
                    $obiettivi_individuali_anno = ObiettiviIndividuali\AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInData($cdr, $dateTimeObject);                                      
                    foreach($obiettivi_individuali_anno["resp-cdr-figli"] as $ob_ind) {   
                        $ob_ind["personale_visualizzato"]["personale"]->matricola_personale = $ob_ind["personale_visualizzato"]["personale"]->matricola;                     
                        $responsabili_cdr_figli[] = $ob_ind["personale_visualizzato"]["personale"];
                    }                
                }
                foreach(array_merge($personale_cdr, $responsabili_cdr_figli) as $personale_cdc) {
                    $dipendente = $personale[$personale_cdc->matricola_personale];
                    $no_ob_ind = false;
                    $add_to_report_presa_visione = false;
                    $add_to_report_presa_visione_individuali = false;
                    $n_ob_ind_non_assegnati_desc = $n_ob_ind_accettati_desc = "";                                         
                    //vengono verificati i dipendenti senza assegnazione (perfomrance o individuale)
                    if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
                        $n_obiettivi = 0;
                        $n_obiettivi_accettati = 0;       
                        $assegnabile = false;
                        foreach ($obiettivi_individuali_anno as $tipo_obiettivo_individuale => $obiettivi_cdr_anno) {                            
                            foreach ($obiettivi_cdr_anno as $ob_ind_key => $obiettivo_individuale_anno) {                              
                                //obiettivi assegnati al dipendente per il cdr                                                                                                   
                                if ($obiettivo_individuale_anno["personale_visualizzato"]["personale"]->matricola == $dipendente->matricola) {
                                    if ($obiettivo_individuale_anno["personale_visualizzato"]["assegnabile"]) {
                                        $assegnabile = true;
                                        $n_obiettivi = count($obiettivo_individuale_anno["assegnazioni"]) ;                                                                      
                                        //obiettivi accettati         
                                        foreach ($obiettivo_individuale_anno["assegnazioni"] as $assegnazione_individuale) {                                            
                                            if ($assegnazione_individuale["assegnazione_individuale"]->datetime_accettazione != null) {                                                                    
                                                $n_obiettivi_accettati++; 
                                            }
                                        }
                                        $tipo_obiettivo = $tipo_obiettivo_individuale;
                                        // se l'obiettivo è già stato trovato viene eliminato dall'array per velocizzare le ricerche future                                    
                                        unset($obiettivi_individuali_anno[$tipo_obiettivo_individuale][$ob_ind_key]);
                                        break;
                                    }
                                    else {
                                        unset($obiettivi_individuali_anno[$tipo_obiettivo_individuale][$ob_ind_key]);
                                        break;
                                    }
                                }                                
                            }
                        }
                        $n_ob_ind_non_assegnati_desc = $n_obiettivi."/".OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR;                        
                        $n_ob_ind_accettati_desc = $n_obiettivi_accettati."/".OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR;                        
                        if ($assegnabile) {                            
                            if ($n_obiettivi < OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR) {                                                                            
                                $no_ob_ind = true;                                                                                
                            }                           
                            else if ($n_obiettivi_accettati < OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR) {                                                        
                                $add_to_report_presa_visione_individuali = true;                               
                            }
                        }
                    }

                    if (array_search($dipendente->matricola, array_column($ob_personale_associati, "matricola")) === false || $no_ob_ind) {                                               
                        $record["dipendente"] = $dipendente->cognome
                                                        ." "
                                                        .$dipendente->nome
                                                        ." (matr. "
                                                        .$dipendente->matricola
                                                        .")";       
                                                        
                        //i responsabili dei cdr figli non vengono considerati per gli obiettivi di performance
                        //sono i dipendenti in cui non è settato l'attributo codice_cdc (per via del recupero dei dati differente)                                                        
                        if (property_exists($personale_cdc, "codice_cdc")) {
                            if (array_search($dipendente->matricola, array_column($ob_personale_associati, "matricola")) === false) {
                                $add_to_report_personale = true;
                                $personale_senza_obiettivi[] = $record;
                            }
                        }
                        
                        if ($no_ob_ind == true) {  
                            $add_to_report_ob_ind = true;        
                            $record["n_ob_ind_non_assegnati"] = $n_ob_ind_non_assegnati_desc;  
                            $record["tipo_assegnazione"] = $tipo_obiettivo;                           
                            $personale_senza_obiettivi_individuali[] = $record;                                             
                        }                      
                    }  

                    //i responsabili dei cdr figli non vengono considerati per gli obiettivi di performance
                    //sono i dipendenti in cui non è settato l'attributo codice_cdc (per via del recupero dei dati differente)                                                        
                    //if (property_exists($personale_cdc, "codice_cdc")) {
                        if (array_search($dipendente->matricola, array_column($ob_personale_senza_accettazione, "matricola"))) {                            
                            $add_to_report_presa_visione = true;
                        }
                    //}

                    //vengono verificati i dipendenti che non hanno preso visione degli obiettivi assegnati                
                    if($add_to_report_presa_visione || $add_to_report_presa_visione_individuali) {                          
                        $add_to_report_accettazione = true;                                       
                        $record["dipendente"] = $dipendente->cognome." ".$dipendente->nome." (matr. ".$dipendente->matricola.")";
                        if ( $j = array_search($dipendente->matricola, array_column($ob_personale_senza_accettazione, "matricola"))) {                            
                            $record["n_perf_non_accettati"] = $ob_personale_senza_accettazione[$j]["n_non_accettati"];                                                      
                        }  
                        else {
                            $record["n_perf_non_accettati"] = "Nessuno";
                        }                        
                        $record["n_ind_accettati"] = $n_ob_ind_accettati_desc;                        
                        $personale_obiettivi_senza_presa_visione[] = $record;                                                                                                                               
                    }                       
                }
            }
        } 

        if ($add_to_report_obiettivi || $add_to_report_peso || $add_to_report_chiusura || $add_to_report_personale || $add_to_report_ob_ind || $add_to_report_accettazione) {
            $record_to_add = array($i);            
            if ($view_all) {
                //viene recuperato il padre strategico                
                $cdr_padre_strategico = $cdr->cloneAttributesToNewObject("CdrStrategia")->getPadreStrategico($anno);
                $responsabile_cdr_padre_strategico = $cdr_padre_strategico->getResponsabile($dateTimeObject);                
                $articolazione_organizzativa_to_add = $cdr_padre_strategico->codice
                                    ." - "
                                    .$cdr_padre_strategico->descrizione
                                    ." (".$responsabile_cdr_padre_strategico->cognome
                                    ." "
                                    .$responsabile_cdr_padre_strategico->nome
                                    ." matr."
                                    .$responsabile_cdr_padre_strategico->matricola_responsabile
                                    .")";
                $record_to_add[] = $articolazione_organizzativa_to_add;                 
            }
            if ($cdr->id_padre !== 0) {
                $cdr_padre = getCdrPadre($cdr->id_padre, $cdr_piano);                
            }
            else {
                $cdr_padre = new Cdr();
                $cdr_padre->codice = "";
                $cdr_padre->descrizione = "Nessuno";
            }   
            if ($cdr_padre->descrizione !== "Nessuno") {
                $cdr_padre_to_add = $cdr_padre->codice
                                    ." - "
                                    .$cdr_padre->descrizione
                                    ." (".$cdr_padre->responsabile->cognome
                                    ." "
                                    .$cdr_padre->responsabile->nome
                                    ." matr. "
                                    .$cdr_padre->responsabile->matricola_responsabile
                                    .")";      
            }   
            else {
                $cdr_padre_to_add = $cdr_padre->descrizione;
            }                      
            $cdr_to_add =  $cdr->codice
                            ." - "
                            .$cdr->descrizione
                            . " (".$cdr->responsabile->cognome
                            ." "
                            .$cdr->responsabile->nome
                            ." matr. "
                            .$cdr->responsabile->matricola_responsabile
                            .")";   
            
            $record_to_add[] = $cdr_padre_to_add;
            $record_to_add[] = $cdr_to_add;
                                                
            if ($add_to_report_obiettivi){
                $cdr_report_obiettivi[] = $record_to_add;                
                if ($view_all) {
                    $articolazioni_organizzative_obiettivi_filter[] = $articolazione_organizzativa_to_add;
                }                
                $cdr_padri_obiettivi_filter[] = $cdr_padre_to_add;                
            }

            if($add_to_report_peso){
                $cdr_report_peso[] = $record_to_add;
                if ($view_all) {
                    $articolazioni_organizzative_peso_filter[] = $articolazione_organizzativa_to_add;
                }
                $cdr_padri_peso_filter[] = $cdr_padre_to_add;
            }
            
            if($add_to_report_chiusura) {
                $record_obiettivi_chiusura_to_add = $record_to_add;
                $record_obiettivi_chiusura_to_add[] = $obiettivi_non_chiusi[$cdr->id]["n_ob_senza_chiusura"]."/".$obiettivi_non_chiusi[$cdr->id]["n_tot_ob"];
                
                $cdr_report_chiusura[] = $record_obiettivi_chiusura_to_add;

                $articolazioni_organizzative_obiettivi_chiusura_filter[] = $articolazione_organizzativa_to_add;
                $cdr_obiettivi_chiusura_filter[] = $cdr_padre_to_add;
                $cdr_obiettivi_chiusura[] = $cdr_to_add;
            }

            if ($add_to_report_personale) {                
                foreach ($personale_senza_obiettivi as $dipendente_senza_ob) {
                    $record_personale_to_add = $record_to_add;
                    $record_personale_to_add[] = $dipendente_senza_ob["dipendente"];
                    $cdr_report_personale[] = $record_personale_to_add;
                    unset($record_personale_to_add);
                }    
                $articolazioni_organizzative_assegnazione_chiusura_filter[] = $articolazione_organizzativa_to_add;
                $cdr_padri_personale_filter[] = $cdr_padre_to_add;
                $cdr_filter[] = $cdr_to_add;
            }    

            if ($add_to_report_ob_ind) {                
                foreach ($personale_senza_obiettivi_individuali as $dipendente_senza_ob_ind) {
                    $record_personale_to_add = $record_to_add;
                    $record_personale_to_add[] = $dipendente_senza_ob_ind["dipendente"];
                    $record_personale_to_add[] = $dipendente_senza_ob_ind["tipo_assegnazione"];
                    $record_personale_to_add[] = $dipendente_senza_ob_ind["n_ob_ind_non_assegnati"];
                    $cdr_report_personale_individuali[] = $record_personale_to_add;
                    unset($record_personale_to_add);
                }
                $articolazioni_organizzative_obiettivi_assegnazione_individuale_filter[] = $articolazione_organizzativa_to_add;
                $cdr_padri_personale_senza_obiettivi_individuali_filter[] = $cdr_padre_to_add;
                $cdr_individuali_filter[] = $cdr_to_add;
            }

            if ($add_to_report_accettazione){                              
                foreach ($personale_obiettivi_senza_presa_visione as $dipendente_no_presa_visione) {
                    $record_personale_no_presa_visione_to_add = $record_to_add;
                    $record_personale_no_presa_visione_to_add[] = $dipendente_no_presa_visione["dipendente"];
                    $record_personale_no_presa_visione_to_add[] = $dipendente_no_presa_visione["n_perf_non_accettati"];
                    if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
                        $record_personale_no_presa_visione_to_add[] = $dipendente_no_presa_visione["n_ind_accettati"];
                    }
                    $cdr_report_personale_no_presa_visione[] = $record_personale_no_presa_visione_to_add;
                    unset($record_personale_no_presa_visione_to_add);
                }    
                $articolazioni_organizzative_personale_no_presa_visione_filter[] = $articolazione_organizzativa_to_add;
                $cdr_padri_personale_no_presa_visione_filter[] = $cdr_padre_to_add;
                $cdr_no_presa_visione_filter[] = $cdr_to_add;                               
            }
            unset($articolazione_organizzativa_to_add);            
            unset($cdr_padre_to_add);
            unset($cdr_to_add);
        }      
    }
    $i++;
}
unset ($cdr);
unset ($cdr_to_check);

//generazione filtri ricerca
if ($view_all) {    
    $articolazioni_organizzative_obiettivi_multipairs = array();
    foreach(array_unique($articolazioni_organizzative_obiettivi_filter) as $art_org) {
        $articolazioni_organizzative_obiettivi_multipairs[] = array(
                                                new ffData($art_org, "Text"),
                                                new ffData($art_org, "Text"),
                                                );
    }
    unset($art_org);
    unset($articolazioni_organizzative_obiettivi_filter);
    
    $articolazioni_organizzative_peso_multipairs = array();
    foreach(array_unique($articolazioni_organizzative_peso_filter) as $art_org) {
        $articolazioni_organizzative_peso_multipairs[] = array(
                                                new ffData($art_org, "Text"),
                                                new ffData($art_org, "Text"),
                                                );
    }
    unset($art_org);
    unset($articolazioni_organizzative_peso_filter);
}

$cdr_padri_obiettivi_multipairs = array();
foreach(array_unique($cdr_padri_obiettivi_filter) as $padri) {    
    $cdr_padri_obiettivi_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_obiettivi_filter);

$cdr_padri_peso_multipairs = array();
foreach(array_unique($cdr_padri_peso_filter) as $padri) {    
    $cdr_padri_peso_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_peso_filter);

$cdr_padri_chiusura_multipairs = array();
foreach(array_unique($cdr_obiettivi_chiusura_filter) as $padri) {
    $cdr_padri_chiusura_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_obiettivi_chiusura_filter);

$cdr_multipairs = array();
foreach(array_unique(array_unique($cdr_filter)) as $cdr_search) {
    $cdr_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}

$articolazioni_organizzative_chiusura_multipairs = array();
foreach(array_unique($articolazioni_organizzative_obiettivi_chiusura_filter) as $cdr_search) {
    $articolazioni_organizzative_chiusura_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}
unset($cdr_search);
unset($articolazioni_organizzative_obiettivi_chiusura_filter);

$articolazioni_organizzative_assegnazione_multipairs = array();
foreach(array_unique($articolazioni_organizzative_assegnazione_chiusura_filter) as $cdr_search) {
    $articolazioni_organizzative_assegnazione_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}
unset($cdr_search);
unset($articolazioni_organizzative_obiettivi_assegnazione_filter);

if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $articolazioni_organizzative_assegnazione_individuale_multipairs = array();
    foreach(array_unique($articolazioni_organizzative_obiettivi_assegnazione_individuale_filter) as $cdr_search) {
        $articolazioni_organizzative_assegnazione_individuale_multipairs[] = array(
                                                new ffData($cdr_search, "Text"),
                                                new ffData($cdr_search, "Text"),
                                                );
    }
    unset($cdr_search);
    unset($articolazioni_organizzative_obiettivi_assegnazione_individuale_filter);

    $cdr_padri_personale_individuale_multipairs = array();
    foreach(array_unique($cdr_padri_personale_senza_obiettivi_individuali_filter) as $padri) {
        $cdr_padri_personale_individuale_multipairs[] = array(
                                                new ffData($padri, "Text"),
                                                new ffData($padri, "Text"),
                                                );
    }
    unset($padri);
    unset($cdr_padri_personale_senza_obiettivi_individuali_filter);
    
    $cdr_no_assegnazione_individuale_multipairs = array();
    foreach(array_unique(array_unique($cdr_individuali_filter)) as $cdr_search) {
        $cdr_no_assegnazione_individuale_multipairs[] = array(
                                                new ffData($cdr_search, "Text"),
                                                new ffData($cdr_search, "Text"),
                                                );
    }
    unset($cdr_search);
    unset($cdr_individuali_filter);
}

$articolazioni_organizzative_no_presa_visione_multipairs = array();
foreach(array_unique($articolazioni_organizzative_personale_no_presa_visione_filter) as $cdr_search) {
    $articolazioni_organizzative_no_presa_visione_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}
unset($cdr_search);
unset($articolazioni_organizzative_personale_no_presa_visione_filter);

$cdr_padri_personale_multipairs = array();
foreach(array_unique($cdr_padri_personale_no_presa_visione_filter) as $padri) {
    $cdr_padri_personale_no_presa_visione_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_personale_no_presa_visione_filter);
  
$cdr_no_presa_visione_multipairs = array();
foreach(array_unique(array_unique($cdr_no_presa_visione_filter)) as $cdr_search) {
    $cdr_no_presa_visione_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}
unset($cdr_search);
unset($cdr_no_presa_visione_filter);

//grid report obiettivi*********************************************************
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "assegnazioni";
$oGrid->title = "CdR senza obiettivi assegnati";
$oGrid->resources[] = "cdr"; 
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $cdr_report_obiettivi, "obiettivi_obiettivo_cdr");
$oGrid->order_default =$view_all?"padre_strategico":"cdr_padre";    
//visualizzazione della grid dei cdr associati all'obiettivo       
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;

//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

if(count($cdr_report_obiettivi)) {
    //filters
    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico_search";
        $oField->data_source = "padre_strategico";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $articolazioni_organizzative_obiettivi_multipairs;
        $oField->label = "Articolazione organizzativa";
        $oGrid->addSearchField($oField);
    }
        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre_search";
    $oField->data_source = "cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_padri_obiettivi_multipairs;
    $oField->label = "CdR padre";
    $oGrid->addSearchField($oField);
}

$cm->oPage->addContent($oGrid);

//grid report peso**************************************************************
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "pesi";
$oGrid->title = "CdR con peso 0";
$oGrid->resources[] = "peso";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $cdr_report_peso, "obiettivi_obiettivo_cdr");
$view_all?$oGrid->order_default = "padre_strategico":$oGrid->order_default = "cdr_padre";      
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;
//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oGrid->addContent($oField);

if (count($cdr_report_peso)) {
    //filters
    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico_search";
        $oField->data_source = "padre_strategico";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $articolazioni_organizzative_peso_multipairs;
        $oField->label = "Articolazione organizzativa";
        $oGrid->addSearchField($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre_search";
    $oField->data_source = "cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_padri_peso_multipairs;
    $oField->label = "CdR padre";
    $oGrid->addSearchField($oField);
}

$cm->oPage->addContent($oGrid);

//grid report chiusura**************************************************************
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "chiusure";
$oGrid->title = "CdR con obiettivi aperti";
$oGrid->resources[] = "peso";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(array_merge($grid_fields,array("chiusure")), $cdr_report_chiusura, "obiettivi_obiettivo_cdr");
$view_all?$oGrid->order_default = "padre_strategico":$oGrid->order_default = "cdr_padre";      
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;
//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "chiusure";
$oField->base_type = "Text";
$oField->label = "Obiettivi aperti/tot";
$oGrid->addContent($oField);

if (count($cdr_report_chiusura)) {
    //filters
    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico_search";
        $oField->data_source = "padre_strategico";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $articolazioni_organizzative_chiusura_multipairs;
        $oField->label = "Articolazione organizzativa";
        $oGrid->addSearchField($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre_search";
    $oField->data_source = "cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_padri_chiusura_multipairs;
    $oField->label = "CdR padre";
    $oGrid->addSearchField($oField);
}

$cm->oPage->addContent($oGrid);

//grid report personale obiettivi performance non assegnati*********************************************************
array_push($grid_fields, "dipendente");

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "dipendenti";
$oGrid->title = "Personale senza obiettivi di performance assegnati";
$oGrid->resources[] = "personale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $cdr_report_personale, "obiettivi_obiettivo_cdr_personale");
$oGrid->order_default = "cdr";        
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->order_SQL = "";
if ($view_all) {
    $oField->order_SQL = "padre_strategico ASC, ";
}
$oField->order_SQL .= "cdr_padre ASC, cdr ASC, dipendente ASC";
$oField->label = "CdR";
$oGrid->addContent($oField);
    
$oField = ffField::factory($cm->oPage);
$oField->id = "dipendente";
$oField->base_type = "Text";
$oField->label = "Dipendente";
$oGrid->addContent($oField);

if (count($cdr_report_personale)) {
    //filters
    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico_search";
        $oField->data_source = "padre_strategico";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $articolazioni_organizzative_peso_multipairs;
        $oField->label = "Articolazione organizzativa";
        $oGrid->addSearchField($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre_search";
    $oField->data_source = "cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_padri_personale_multipairs;
    $oField->label = "CdR padre";
    $oGrid->addSearchField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_search";
    $oField->data_source = "cdr";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_multipairs;
    $oField->label = "CdR";
    $oGrid->addSearchField($oField);
}
$cm->oPage->addContent($oGrid);

//grid report personale obiettivi individuali non assegnati*********************************************************
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {  
    array_push($grid_fields, "tipo_assegnazione");  
    array_push($grid_fields, "n_ob_ind_non_assegnati");

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dipendenti-individuali";
    $oGrid->title = "Personale senza obiettivi individuali assegnati";
    $oGrid->resources[] = "personale";
    $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $cdr_report_personale_individuali, "obind_assegnazione_individuale");
    $oGrid->order_default = "cdr";        
    $oGrid->record_id = "";
    $oGrid->order_method = "labels";
    $oGrid->record_url = "";
    $oGrid->use_paging = false;
    $oGrid->full_ajax = true;
    //operazioni di inserimento ed eliminazione non permesse
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;
    $oGrid->display_edit_url = false;

    //******************************************************************************
    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico";
        $oField->base_type = "Text";
        $oField->label = "Articolazione organizzativa";
        $oGrid->addContent($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre";
    $oField->base_type = "Text";
    $oField->label = "CdR Padre";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr";
    $oField->base_type = "Text";
    $oField->order_SQL = "";
    if ($view_all) {
        $oField->order_SQL = "padre_strategico ASC, ";
    }
    $oField->order_SQL .= "cdr_padre ASC, cdr ASC, dipendente ASC";
    $oField->label = "CdR";
    $oGrid->addContent($oField);
        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dipendente";
    $oField->base_type = "Text";
    $oField->label = "Dipendente";
    $oGrid->addContent($oField);   
    /*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "tipo_assegnazione";
    $oField->base_type = "Text";
    $oField->label = "Tipo assegnazione";
    $oGrid->addContent($oField);*/
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "n_ob_ind_non_assegnati";
    $oField->base_type = "Text";
    $oField->label = "Assegnati / assegnabili";
    $oGrid->addContent($oField);

    if (count($cdr_report_personale_individuali)) {
        //filters
        if ($view_all) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "padre_strategico_individuali_search";
            $oField->data_source = "padre_strategico";
            $oField->base_type = "Text";
            $oField->extended_type = "Selection";
            $oField->multi_pairs = $articolazioni_organizzative_assegnazione_multipairs;
            $oField->label = "Articolazione organizzativa";
            $oGrid->addSearchField($oField);
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr_padre_individuali_search";
        $oField->data_source = "cdr_padre";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $cdr_padri_personale_individuale_multipairs;
        $oField->label = "CdR padre";
        $oGrid->addSearchField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr_individuali_search";
        $oField->data_source = "cdr";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $cdr_no_assegnazione_individuale_multipairs;
        $oField->label = "CdR";
        $oGrid->addSearchField($oField);
    }
    $cm->oPage->addContent($oGrid);
}

//grid report personale senza accettazione*************************************
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    array_pop($grid_fields);
    array_pop($grid_fields);
}
array_push($grid_fields, "n_perf_non_accettati");
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    array_push($grid_fields, "n_ind_accettati");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "dipendenti-presa-visione";
$oGrid->title = "Personale con obiettivi senza presa visione";
$oGrid->resources[] = "personale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $cdr_report_personale_no_presa_visione, "obiettivi_obiettivo_cdr_personale");
$oGrid->order_default = "cdr";        
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->order_SQL = "";
if ($view_all) {
    $oField->order_SQL = "padre_strategico ASC, ";
}
$oField->order_SQL .= "cdr_padre ASC, cdr ASC, dipendente ASC";
$oField->label = "CdR";
$oGrid->addContent($oField);
    
$oField = ffField::factory($cm->oPage);
$oField->id = "dipendente";
$oField->base_type = "Text";
$oField->label = "Dipendente";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "n_perf_non_accettati";
$oField->base_type = "Text";
$oField->label = "Obiettivi senza presa visione";
$oGrid->addContent($oField);

if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "n_ind_accettati";
    $oField->base_type = "Text";
    $oField->label = "Obiettivi individuali accettati";
    $oGrid->addContent($oField);
}

if (count($cdr_report_personale_no_presa_visione)) {
    //filters
    if ($view_all) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "padre_strategico_search";
        $oField->data_source = "padre_strategico";
        $oField->base_type = "Text";
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $articolazioni_organizzative_no_presa_visione_multipairs;
        $oField->label = "Articolazione organizzativa";
        $oGrid->addSearchField($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_padre_search";
    $oField->data_source = "cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_padri_personale_no_presa_visione_multipairs;
    $oField->label = "CdR padre";
    $oGrid->addSearchField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_search";
    $oField->data_source = "cdr";    
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_no_presa_visione_multipairs;
    $oField->label = "CdR";
    $oGrid->addSearchField($oField);
}
$cm->oPage->addContent($oGrid);