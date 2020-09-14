<?php
if (isset($_REQUEST["ID_periodo"])) {
    try {
        $start = new DateTime();
        $periodo = new ValutazioniPeriodo($_REQUEST["ID_periodo"]);
        $valutazioni_attive = $periodo->getValutazioniAttivePeriodo();
        
        if (empty($valutazioni_attive)) {
            die("Non ci sono valutazioni attive");
        } else {
            foreach ($valutazioni_attive as $valutazione) {
                echo("valutazione ID=".$valutazione->id." - matricola valutato '".$valutazione->matricola_valutato."': ");
                echo($valutazione->saveTotaliPrecalcolati(true)."</br>");
            }            
            $end = new DateTime();
            $interval = $end->diff($start);
            die("Procedura dio aggiornamento dei totali precalcolati conclusa. Durata [h:min:sec]: ".($interval->h.":".$interval->i.":".$interval->s));
        }
    } catch (Exception $e) {
        die("Impossibile creare il periodo con ID '" . $_REQUEST["ID_periodo"] . "'. Errore: " . $e->getMessage());
    }
} else {
    die("Non &egrave; stato specificato alcun periodo. L'operazione sar&agrave; interrotta");
}