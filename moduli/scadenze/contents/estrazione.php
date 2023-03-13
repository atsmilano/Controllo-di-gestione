<?php
use scadenze\AbilitazioneCdr;
use scadenze\Personale;
use scadenze\Tipologia;

$user = LoggedUser::getInstance();
if (!($user->hasPrivilege("scadenze_admin") || $user->hasPrivilege("scadenze_referente_cdr"))) {           
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter visualizzare le scadenze");
}

$date = new DateTime();

$intestazione = array(
    "ID",
    "Stato",
    "Data Scadenza",
    "Cdr",
    "Tipologia",
    "Protocollo",
    "Oggetto",
    "Note",
    "Data evasione",
    "Giorni Promemoria Scadenza",
    "Mail di promemoria inviata",
    "Data inserimento",
    "Matricola inserimento",
    "Data ultima modifica",
    "Matricola ultima modifica",    
);
$fogli["Scadenze"] = array($intestazione);

$abilitazioni_attive_in_data = \CoreHelper::getObjectsInData ("scadenze\AbilitazioneCdr", $date, "data_riferimento_inizio", "data_riferimento_fine");

$personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);
foreach ($personale->getScadenzeCompetenzaInData($date) as $scadenza) {  
    $data_inserimento = new DateTime($scadenza->data_inserimento);
    $abilitazione_cdr = new AbilitazioneCdr($scadenza->id_abilitazione_cdr);
    $tipologia = new Tipologia($scadenza->id_tipologia);
    $found = false;
    foreach ($abilitazioni_attive_in_data as $abilitazione_attiva) {
        if ($abilitazione_attiva->codice_cdr == $abilitazione_cdr->codice_cdr) {
            $found = true;
            break;
        }
    } 
    if ($found == true) {
        $anagrafica_cdr = \AnagraficaCdr::factoryFromCodice($abilitazione_cdr->codice_cdr, $data_inserimento);    
        if ($anagrafica_cdr !== null) {
            $descrizione_cdr = $anagrafica_cdr->getDescrizioneEstesa();            
        }
        else {        
            $descrizione_cdr = "Codice " . $abilitazione_cdr->codice_cdr . " senza corrispondenza al " . $data_inserimento->format("d/m/Y");
        }
    }   
    else {                
        $descrizione_cdr = "Codice " . $abilitazione_cdr->codice_cdr . " non abilitato dal " . \CoreHelper::formatUiDate($abilitazione_cdr->data_riferimento_fine);
    }   
    
    //costruzione multipairs stati
    $stato_scadenza = $scadenza->getStato();   
    
    $record = array(
        $scadenza->id,
        $scadenza->getStato()["descrizione"],
        $scadenza->data_scadenza,
        $descrizione_cdr,
        $tipologia->descrizione,
        $scadenza->protocollo,
        $scadenza->oggetto,
        $scadenza->note,
        $scadenza->data_evasione,
        $scadenza->giorni_promemoria_scadenza,
        $scadenza->mail_promemoria_inviata == 1?"Si":"No",
        $scadenza->datetime_inserimento,
        $scadenza->matricola_inserimento,
        $scadenza->datetime_ultima_modifica,
        $scadenza->matricola_ultima_modifica,        
    );
    $fogli["Scadenze"][] = $record;          
}
//estrazione in excel
CoreHelper::simpleExcelWriter("Scadenze" . date("Ymd"), $fogli);