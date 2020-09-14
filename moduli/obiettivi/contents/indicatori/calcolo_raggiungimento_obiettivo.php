<?php
$raggiungimento = null;
$errori = false;
$messaggio = null;

//viene verificato che i parametri siano coerenti e recuperato l'obiettivo
if (isset($_REQUEST["raggiungimenti_indicatori"])) {
    //costruzione dell'array per il calcolo
    //array(array("obiettivo_indicatore" => ObiettivoIndicatore , "raggiungimento" => raggiungimento));
    $raggiungimenti_indicatori = array();
    $id_obiettivo = null;
    foreach ($_REQUEST["raggiungimenti_indicatori"] as $raggiungimento_indicatori) {
        $obiettivo_indicatore = new IndicatoriObiettivoIndicatore($raggiungimento_indicatori["id_obiettivo_indicatore"]); 
        if ($id_obiettivo !== null && $id_obiettivo !== $obiettivo_indicatore->id_obiettivo){
            $messaggio = "Errore nel passaggio dei parametri: id_obiettivo non coerenti.";            
        }
        $raggiungimenti_indicatori[] = array("obiettivo_indicatore" => $obiettivo_indicatore,
                                                "raggiungimento" => $raggiungimento_indicatori["valore"],);
    }
    $obiettivo = new ObiettiviObiettivo($obiettivo_indicatore->id_obiettivo);        
}
else{
    $errori = true;
    $messaggio = "Errore nel passaggio dei parametri: raggiungimento indicatori.";
}
//se l'id dell'indicatore è corretto viene effettuato il (tentativo di) calcolo del risultato
if ( $errori !== true) {
    $response = $obiettivo->calcoloRaggiungimentoObiettivo($raggiungimenti_indicatori);
}   
else {
    $response = array(    
                    "risultato" => $raggiungimento,    
                    "esito" => $esito,
                    "messaggio" => $messaggio,
                );
}
        
die(json_encode($response));