<?php
use \ObiettiviIndividuali\AssegnazioneIndividuale;

$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("obiettivi_individuali_admin")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli obiettivi aziendali.");
}

$anno = new AnnoBudget($_GET["anno"]);
$cdr = $cm->oPage->globals["cdr"]["value"];
$date = CoreHelper::getDataRiferimentoBudget($anno);

$xls_file = "obiettivi_individuali-".date("Ymd");
$nome_foglio_lavoro = "Obiettivi Individuali";

//inizializzazione matrice e intestazioni	
$matrice_dati = array(
    array(
        "ID",
        "Anno Budget", 
        "Dipendente",         
        "Responsabile Assegnazione",
        "codice cdr",
        "descrizione",
        "ID obiettivo",
        "Data Ora Inserimento",
        "Matricola inserimento",
        "Data Ora Chiusura",
        "Data Ora Accettazione",
        "Rendicontazione",
        "Data Ora Chiusura Rendicontazione",
        "Note Responsabile",
        "Data Ora Chiusura ",
    )
);

//recupero personale
$personale = Personale::getAll();

foreach (AssegnazioneIndividuale::getAll(array("ID_anno_budget" => $anno->id)) as $assegnazione) {
    $record = array();

    $record[] = $assegnazione->id;
    $record[] = $anno->descrizione;
    $dipendente = $personale[array_search($assegnazione->matricola_personale, array_column($personale, 'matricola'))];    
    $record[] = $dipendente->cognome." ".$dipendente->nome." (matr. ".$dipendente->matricola.")";
    
    $dipendente = $personale[array_search($assegnazione->matricola_responsabile_assegnazione, array_column($personale, 'matricola'))];
    $record[] = $dipendente->cognome." ".$dipendente->nome." (matr. ".$dipendente->matricola.")";
    
    $anagrafica_cdr = AnagraficaCdr::factoryFromCodice($assegnazione->codice_cdr, $date);
    $record[] = $anagrafica_cdr->getDescrizioneEstesa();

    $record[] = $assegnazione->descrizione;

    if ((int)$assegnazione->id_obiettivo != 0) {
        $obiettivo = new ObiettiviObiettivo($assegnazione->id_obiettivo);
        $record[] = $obiettivo->codice." - ".$obiettivo->titolo;
    }
    else {
        $record[] = "Nessuno";
    }

    $record[] = $assegnazione->datetime_inserimento;
    $record[] = $assegnazione->matricola_inserimento;
    $record[] = $assegnazione->datetime_chiusura;
    $record[] = $assegnazione->datetime_accettazione;
    $record[] = $assegnazione->rendicontazione;
    $record[] = $assegnazione->datetime_chiusura_rendicontazione;
    $record[] = $assegnazione->note_responsabile;
    $record[] = $assegnazione->datetime_chiusura_modifiche;

    $matrice_dati[] = $record;
}

CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));