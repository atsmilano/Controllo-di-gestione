<?php
$html = "
    <div id='introduzione'>
        <h2>CRUSCOTTO DI CONTROLLO E GESTIONE DEI PESI</h2>
        <p>Il cruscotto consente di monitorare l&acute;assegnazione degli obiettivi e la gestione (assegnazione /variazione) dei pesi:</p>
        <ol>
            <li>
                <b>ASSEGNAZIONE OBIETTIVI AL CDR</b><br>
                <i>Le informazioni consentono al Responsabile di Struttura, per il proprio ambito gestionale di:</i>
                <ol type='a'>
                        <li>visualizzare gli obiettivi e il peso assegnato alle strutture afferenti;</li>
                        <li>verificare l'inserimento delle azioni attuative da parte delle strutture afferenti;</li>
                        <li>effettuare la variazione/inserimento dei pesi;</li>
                        <li>estrarre gli obiettivi, i pesi e le azioni inserite per tutti i CdR di afferenza.</li>
                </ol>					
            </li>				
            <li>
                <b>ASSEGNAZIONE DEGLI OBIETTIVI AL PERSONALE</b><br>
                <i>Le informazioni consentono al Responsabile di Struttura, ognuno per il proprio ambito gestionale di:</i>
                <ol type='a'>
                        <li>visualizzare l&acute;attribuzione degli obiettivi al personale ( dirigenza e comparto) e il relativo peso;</li>
                        <li>effettuare la variazione/inserimento dei pesi (modifica pesi).</li>
                </ol>
            </li>	
            <li>
                <b>OBIETTIVI INDIVIDUALI</b><br>
                <i>Le informazioni consentono al Responsabile di Struttura, ognuno per il proprio ambito gestionale di:</i>
                <ol type='a'>
                        <li>Assegnare gli obiettivi individuali al personale (dirigenza, comparto e Responsabili di Strutture afferenti) coerentemente con il regolamento sulla valutazione vigente;</li>
                        <li>Verificare l’avvenuta presa visione da parte del singolo dipendente degli obiettivi individuali assegnati;</li>
                        <li>c) Verificare la rendicontazione effettuata dal dipendente in relazione a ciascun obiettivo individuale assegnato.</li>
                </ol>
            </li>
            <li>
                <b>REPORT ASSEGNAZIONE CDR</b><br>
                <i>Le informazioni consentono al Responsabile di Struttura, ognuno per il proprio ambito gestionale, di verificare che:</i>
                <ol type='a'>
                        <li>ciascun CdR di afferenza (con personale assegnato) abbia ricevuto gli obiettivi di competenza, con una pesatura >0, e che abbia provveduto alla chiusura della scheda Obiettivi CdR;</li>
                        <li>ciascun dipendente abbia ricevuto almeno un obiettivo di performance organizzativa dal proprio superiore gerarchico ed abbia provveduto alla relativa presa visione.</li>
                </ol>
            </li>
        </ol>
    </div>
";
$cm->oPage->addContent($html);