<?php
ini_set("max_execution_time", VALUTAZIONI_MAX_EXECUTION_TIME);
if(isset($_GET["periodo"])) {
    try {
        $periodo_valutazione = new ValutazioniPeriodo($_GET["periodo"]);
    } catch (Exception $e) {
        ffErrorHandler::raise($e->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: periodo.");
}

//recupero parametri da payloaod json
$request_body = file_get_contents('php://input');
$data = json_decode($request_body);

$schede_aperte = "";
$anomalie = "";
//per ogni matricola per la quale aprire la valutazione viene valutata l'eventuale esistenza di una valutazione già esistente e vengono verificate eventali anomalie
$schede_da_aprire = json_decode($data->matricola_list, true);
if (!count($schede_da_aprire)) {
    die(json_encode("Nessuna scheda da aprire."));
}

foreach($schede_da_aprire as $matricola) {
    $personale = Personale::factoryFromMatricola($matricola);
    $personale = new ValutazioniPersonale($personale->id, $periodo_valutazione);
    //se non è già presente una valutazione per il periodo per il valutato viene creata
    if (!count($periodo_valutazione->getValutazioniAttivePeriodo($matricola))) {
        $valutazione = new ValutazioniValutazionePeriodica();
        if (!strlen($personale->anomalie)) {
            //viene verificato che non ci sia già una valutazione presente
            if (!count($periodo_valutazione->getValutazioniAttivePeriodo($personale->matricola))) {
                $valutazione->matricola_valutatore = $personale->valutatore_suggerito->matricola_responsabile;
                $valutazione->matricola_valutato = $personale->matricola;
                $valutazione->id_periodo = $periodo_valutazione->id;
                $valutazione->id_categoria = $personale->categoria->id;
                $valutazione->save();
                //se è prevista l'autovalutazione nel periodo per la categoria viene creata (valutazione con valutatore = valutato)                
                if($periodo_valutazione->getAutovalutazioneAttivaPeriodo($personale->categoria)){
                    $valutazione->matricola_valutatore = $valutazione->matricola_valutato;
                    $valutazione->save();
                }
                $schede_aperte .= "<li>".$personale->cognome." ".$personale->nome." (".$personale->matricola.")</li>";
            }
        }
        else {
            $anomalie .= "<li>".$personale->cognome." ".$personale->nome." (".$personale->matricola."): ".$personale->anomalie."</li>";
        }
    }
    else {
        $anomalie .= "<li>".$personale->cognome." ".$personale->nome." (".$personale->matricola."): ".$personale->anomalie." già aperta</li>";
    }
}
$return_message = "";
if (strlen($schede_aperte)){
    $return_message .= "Schede aperte correttamente:<ul>".$schede_aperte."</ul>";
}
if (strlen($anomalie)){
    $return_message .= "Anomalie:<ul>".$anomalie."</ul>";
}
die(json_encode($return_message));