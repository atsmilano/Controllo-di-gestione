<?php
use ObiettiviIndividuali\AssegnazioneIndividuale;
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

if ($user->hasPrivilege("resp_cdr_selezionato"))  {
    foreach (AssegnazioneIndividuale::getAssegnazioniRiapribiliCdr ($cdr_selezionato, $data_riferimento, [$user->matricola_utente_selezionato]) as $assegnazione) {
        $assegnazione->datetime_chiusura = null;
        $assegnazione->save(array("datetime_chiusura"));
    }
    die(json_encode(array("messaggio" => "Riapertura obiettivi individuali effettuata con successo.", "esito" => "success")));
}
die(json_encode(array("messaggio" => "L'utente non ha i privilegi per effettuare la riapertura degli obiettivi individuali.", "esito" => "error")));