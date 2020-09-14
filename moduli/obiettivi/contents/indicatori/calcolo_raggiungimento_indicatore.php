<?php
$raggiungimento = null;
$errori = false;
$messaggio = null;

//recupero dell'indicatore e del risultato da utilizzare
if (isset($_REQUEST["id_obiettivo_indicatore"])) {
    try {
        $obiettivo_indicatore = new IndicatoriObiettivoIndicatore($_REQUEST["id_obiettivo_indicatore"]);
        $indicatore = new IndicatoriIndicatore($obiettivo_indicatore->id_indicatore);
    } catch (Exception $ex) {
        $errori = true;
        $messaggio = "Errore nel passaggio dei parametri: id_obiettivo_indicatore.";
    }
}
else{
    $errori = true;
    $messaggio = "Errore nel passaggio dei parametri: id_obiettivo_indicatore.";
}

if (isset($_REQUEST["cdr_associato"])) {
    try {
        $cdr = new Cdr($_REQUEST["cdr_associato"]);
    } catch (Exception $ex) {
        $cdr = null;
    }
}
else{
    $cdr = null;
}

//recupero del valore target
$valore_target = $obiettivo_indicatore->getValoreTarget($cdr);
//se l'id dell'indicatore è corretto viene effettuato il (tentativo di) calcolo del raggiungimento
if ( $errori !== true) {
    $response = $indicatore->calcoloRaggiungimentoIndicatore($_REQUEST["risultato"], $valore_target);
}   
else {
    $response = array(    
                    "risultato" => $raggiungimento,    
                    "esito" => $esito,
                    "messaggio" => $messaggio,
                );
} 
        
die(json_encode($response));