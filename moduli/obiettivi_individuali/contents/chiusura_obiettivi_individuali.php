<?php
use ObiettiviIndividuali\AssegnazioneIndividuale;
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

if ($user->hasPrivilege("resp_cdr_selezionato"))  {
    $obiettivi_assegnati = AssegnazioneIndividuale::getAssegnazioniCdr ($cdr_selezionato, $data_riferimento, [$user->matricola_utente_selezionato]);        
    $n_obiettivi_assegnabili = AssegnazioneIndividuale::nAssegnazioniAssegnabiliCdr ($cdr_selezionato, $data_riferimento, [$user->matricola_utente_selezionato]);                
    if(count($obiettivi_assegnati) !== $n_obiettivi_assegnabili) {                                                                       
        die(json_encode(array("messaggio" => "Non è possibile procedere con la chiusura poichè non è stata completata l'assegnazione per tutto il personale afferente.", "esito" => "error")));
    }      
    
    foreach (AssegnazioneIndividuale::getAssegnazioniChiudibiliCdr ($cdr_selezionato, $data_riferimento, [$user->matricola_utente_selezionato]) as $assegnazione) {
        $assegnazione->datetime_chiusura = date("Y-m-d H:i:s");
        $assegnazione->save(array("datetime_chiusura"));
    }
    die(json_encode(array("messaggio" => "Chiusura obiettivi individuali effettuata con successo.", "esito" => "success")));
}
die(json_encode(array("messaggio" => "L'utente non ha i privilegi per effettuare la chiusura degli obiettivi individuali.", "esito" => "error")));