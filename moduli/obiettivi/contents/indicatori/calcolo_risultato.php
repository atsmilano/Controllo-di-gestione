<?php
$risultato = null;
$errori = false;
$messaggio = null;

//recupero dell'indicatore e dei parametri
if (isset($_REQUEST["id_indicatore"])) {
    try {
        $indicatore = new IndicatoriIndicatore($_REQUEST["id_indicatore"]);
    } catch (Exception $ex) {
        $errori = true;
        $messaggio = "Errore nel passaggio dei parametri: ID_indicatore.";
    }
}
elseif (isset($_REQUEST["id_obiettivo_indicatore"])){
    try {
        $obiettivo_indicatore = new IndicatoriObiettivoIndicatore($_REQUEST["id_obiettivo_indicatore"]);
        $indicatore = new IndicatoriIndicatore($obiettivo_indicatore->id_indicatore);
    } catch (Exception $ex) {
        $errori = true;
        $messaggio = "Errore nel passaggio dei parametri: ID_indicatore.";
    }    
}
else{
    $errori = true;
    $messaggio = "Errore nel passaggio dei parametri: ID_indicatore.";
}

//se l'id dell'indicatore è corretto viene effettuato il (tentativo di) calcolo del risultato
if ( $errori !== true) {
    $response = $indicatore->calcoloRisultatoIndicatore($_REQUEST["parametri"]);
}   
else {
    $response = array(    
                    "risultato" => $risultato,    
                    "esito" => $esito,
                    "messaggio" => $messaggio,
                );
} 

die(json_encode($response));