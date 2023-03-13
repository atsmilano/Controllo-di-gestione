<?php
$descrizione_cdr = "";
if ($cm->oPage->globals["cdr"]["value"] != null) {
    $descrizione_cdr = "'".$cm->oPage->globals["cdr"]["value"]->getDescrizioneEstesa()."'";
}

$html = "
    <div id='premessa_metodologica'>
        <h2>Premessa metodologica</h2>
        <h3>Profilo (valore atteso)</h3>
        <p>Il &quot;Profilo&quot; è costituito dall’insieme di competenze trasversali e di competenze tecniche 
        specifiche.<br>
        In particolare, il Direttore del Cdr ".$descrizione_cdr." ha selezionato da 9 a 11 competenze trasversali, 
        che ritiene particolarmente importanti per lo svolgimento della funzione in questione, 
        all’interno della mappa delle 25 competenze proposte dal progetto, e ha poi indicato da 1 a 3 
        competenze specifiche.<br>
        Dopo aver individuato la rosa delle 12 competenze, il Direttore ha attribuito il valore 
        auspicato a ciascuna di esse. Per questo motivo, i punteggi assegnati sono sempre alti, 
        generalmente fra 4 = &quot;avanzato&quot; e 5 = &quot;eccellente&quot;.
        </p>
        <h3>Auto-valutazione</h3>
        <p>L’ &quot;Autovalutazione&quot; è espressione della valutazione individuale 
        (da parte del singolo professionista) del proprio livello di competenza in relazione alle 
        conoscenze/capacità indicate dal Direttore del Cdr, come centrali per il ruolo indagato, e ai rispettivi valori attesi.<br>
        Il confronto restituisce quindi la percezione, 
        da parte del professionista, della distanza fra il proprio livello di competenza e quello auspicato per quella posizione.
        </p>
        <h3>Mappatura del Responsabile</h3>
        <p>La &quot;Mappatura del Responsabile&quot; è espressione della valutazione,
        da parte del Direttore del Cdr ".$descrizione_cdr.", 
        del livello di competenza posseduto dal professionista in relazione alle conoscenze/capacità auspicate. 
        Fornisce, cioè, una lettura del bisogno formativo, formulata dal suo Direttore.</p>
    </div>
";

if (isset($report_pdf) && $report_pdf == true) {
    $html_report .= $html;
}
else {
    $cm->oPage->addContent($html);
}