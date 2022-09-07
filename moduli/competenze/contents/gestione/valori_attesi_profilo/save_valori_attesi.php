<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");

//viene verificata la possibilità dell'utente di accedere alla pagina
if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
    die(json_encode(array('messaggio' => "Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze dei profili per il CdR.", 'esito' => "error")));		
}
if (isset($_REQUEST["keys[ID_profilo]"])) {
    try {
        $profilo = new MappaturaCompetenze\Profilo($_REQUEST["keys[ID_profilo]"]);
    } catch (Exception $ex) {
        die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: profilo.", 'esito' => "error")));
    }
    $found = false;
    foreach ($cdr->getProfiliResponsabile($user->matricola_utente_selezionato) as $profilo_cdr) {
        if ($profilo->id == $profilo_cdr->id){
            $found = true;
            break;
        }            
    }
    if ($found == false) {        
        die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: profilo non previsto per il CdR e responsabile.", 'esito' => "error")));        
    }                    
}
else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: profilo.", 'esito' => "error")));
}

//per ogni variazione dei valori attesi, se l'utente ha i privilegi per effettuarla viene salvato un record su db
if (isset($_GET["ct"]) && isset($_GET["cs"])){
    //recupero dei valori attesi ammessi
    $valori_attesi_ammessi = $profilo->getValoriAssegnabili();
    foreach ($_GET["ct"] as $id_competenza_trasversale) {
        //selezione competenza trasversale per il profilo
        $competenza_trasversale_profilo = \MappaturaCompetenze\ProfiloCompetenzaTrasversale::getByFields(array("ID_profilo"=>$profilo->id, "ID_competenza_trasversale"=>$id_competenza_trasversale["idc"]));
        
        if ($competenza_trasversale_profilo == null) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: competenza trasversale / profilo.", 'esito' => "error")));
        }  
        $found = false;
        foreach ($valori_attesi_ammessi as $valore_atteso_ammesso) {
            if ($valore_atteso_ammesso->id == $id_competenza_trasversale["idva"]) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: valore atteso.", 'esito' => "error")));
        }
        //verifica eventuale variazione della competenza trasversale
        if ($competenza_trasversale_profilo->id_valore_atteso !== $id_competenza_trasversale["idva"]) {
            $competenza_trasversale_profilo->id_valore_atteso = $id_competenza_trasversale["idva"];
            $competenza_trasversale_profilo->save(array("ID_valore_atteso"));
        }
    }
    foreach ($_GET["cs"] as $id_competenza_specifica) {
        //selezione competenza specifica per il profilo
        $competenza_specifica_profilo = \MappaturaCompetenze\ProfiloCompetenzaSpecifica::getByFields(array("ID_profilo"=>$profilo->id, "ID_competenza_specifica"=>$id_competenza_specifica["idc"]));
        if ($competenza_specifica_profilo == null) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: competenza specifica / profilo.", 'esito' => "error")));
        }           
        $found = false;
        foreach ($valori_attesi_ammessi as $valore_atteso_ammesso) {
            if ($valore_atteso_ammesso->id == $id_competenza_trasversale["idva"]) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: valore atteso.", 'esito' => "error")));
        }
        //verifica eventuale variazione della competenza specifica
        if ($competenza_specifica_profilo->id_valore_atteso !== $id_competenza_specifica["idva"]) {
            $competenza_specifica_profilo->id_valore_atteso = $id_competenza_specifica["idva"];
            $competenza_specifica_profilo->save(array("ID_valore_atteso"));
        }
    }
}
else {
	die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione.", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Valori attesi aggiornati con successo.", 'esito' => "success")));