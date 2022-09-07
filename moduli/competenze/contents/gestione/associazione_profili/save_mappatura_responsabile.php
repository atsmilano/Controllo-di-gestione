<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dell'associazione dei profili per il CdR.");	
}    
    
//verifica e recupero del periodo
if (isset($_REQUEST["id_periodo"])) {
    try {
        $periodo = new MappaturaCompetenze\Periodo($_REQUEST["id_periodo"]);       
    } catch (Exception $ex) {
        die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: periodo.", 'esito' => "error")));
    }  
}
else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: periodo.", 'esito' => "error")));
}

//recupero assegnazioni da matrice
if (isset($_GET["pp"]) ){
    $assegnazione_profili_personale = $_GET["pp"];
}
else {
    $assegnazione_profili_personale = array();
}

//recupero valori ammessi
$profili_cdr_responsabile = $cdr->getProfiliResponsabile($user->matricola_utente_selezionato);
$personale_afferente_in_data = array();
$date_time_fine_periodo = new DateTime(date($periodo->data_riferimento_fine));
foreach ($cdr->getGerarchiaRamoCdrGestioneData(new DateTime(date($date_time_fine_periodo))) as $cdr_gestione) {
    foreach ($cdr_gestione->getPersonaleCdcAfferentiInData($date_time_fine_periodo) as $cdc_personale) {
        $personale_afferente_in_data[] = \Personale::factoryFromMatricola($cdc_personale->matricola_personale);
    }    
}

//per ogni assegnazione viene creato/modificato il record nella tabella
$profili_matricole = array();
foreach ($assegnazione_profili_personale as $mappatura) {               
    $profilo = null;
    $personale = null;

    //verifica profilo con profili cdr responsabile
    $found = false;    
    foreach ($profili_cdr_responsabile as $profilo_cdr) {
        if ($mappatura["idpr"] == $profilo_cdr->id){
            $found = true;
            $profilo = $profilo_cdr;
            break;
        }            
    }
    if ($found == false) {        
        die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: profilo non previsto per il CdR e responsabile.", 'esito' => "error")));        
    }

    //verifica personale con personale afferente
    $found = false;        
    foreach ($personale_afferente_in_data as $personale_afferente) {
        if ($mappatura["idpe"] == $personale_afferente->id){
            $found = true;
            $personale = $personale_afferente;
            break;
        }            
    }
    if ($found == false) {        
        die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: personale.", 'esito' => "error")));        
    }
    //se l'associazione esiste già viene mantenuta altrimenti viene creata
    $assegnazione_profilo_personale = \MappaturaCompetenze\MappaturaPeriodo::getByFields(array("ID_periodo"=>$periodo->id, "ID_tipo_mappatura"=>1, "ID_profilo"=>$profilo->id, "matricola_personale"=>$personale->matricola,));
    if ($assegnazione_profilo_personale == null) {
        $assegnazione_profilo_personale = new \MappaturaCompetenze\MappaturaPeriodo();        
        $assegnazione_profilo_personale->id_periodo = $periodo->id;
        $assegnazione_profilo_personale->id_tipo_mappatura = 1;
        $assegnazione_profilo_personale->id_profilo = $profilo->id;
        $assegnazione_profilo_personale->matricola_valutatore = $user->matricola_utente_selezionato;
        $assegnazione_profilo_personale->matricola_personale = $personale->matricola;       
        $assegnazione_profilo_personale->save(array("ID_periodo","ID_tipo_mappatura","ID_profilo","matricola_valutatore","matricola_personale"));        
    }    
    $profili_matricole[] = array("id_profilo"=>$profilo->id, "matricola_personale"=>$personale->matricola);
}

//vengono eliminate tutte le asssociazioni non mantenute per profilo e personale afferenti al cdr per il responsabile 
foreach ($personale_afferente_in_data as $personale_afferente) {    
    foreach ($profili_cdr_responsabile as $profilo_cdr) {
        $found = false;
        $assegnazione_profilo_personale = \MappaturaCompetenze\MappaturaPeriodo::getByFields(array("ID_periodo"=>$periodo->id, "ID_tipo_mappatura"=>1, "ID_profilo"=>$profilo_cdr->id, "matricola_personale"=>$personale_afferente->matricola,));    
        //se esiste una relazione viene verificato se mantenerla (se selezionata dall'utente)
        if ($assegnazione_profilo_personale !== null) {
            foreach ($profili_matricole as $mappatura) {
                if ($mappatura["id_profilo"] == $profilo_cdr->id && $mappatura["matricola_personale"] == $personale_afferente->matricola){                    
                    $found = true;                    
                    break;
                }
            }  
            //in caso sia stata trovata un'associazione viene considerato il dipendente successivo (una solo associazione per periodo)
            if ($found == false) {
                $assegnazione_profilo_personale->delete();
            }
        }                
    }
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Associazioni profilo-presonale aggiornate con successo.", 'esito' => "success")));