<?php
//vengono estratte tutte le scadenze non evase
$invio_scadenza = [];
//le scadenze da inviare vengono aggregate per cdr abilitati e tipologia
foreach(\scadenze\Scadenza::getAll([], [["fieldname"=>"data_scadenza", "direction"=>"ASC"]]) as $scadenza) {
    $stato = $scadenza->getStato();    
    switch ($stato['id']) {
        //scadenze settimanali (inviate il Lunedì)
        case 1:
        case 2:
            //le scadenze settimanali vengono inviate esclusivamente nella giornata di Lunedì
            if(date('N') === '1') { 
                $invio_scadenza[$scadenza->id_abilitazione_cdr][2][] = $scadenza;
            }            
        break;
        //scadenze in scadenza (inviate tutti i giorni se in scadenza)
        //vengono selezionate solo le mail per le quali non è ancora stata inviata una notifica
        case 3:
            if ($scadenza->mail_promemoria_inviata != true) {            
                $invio_scadenza[$scadenza->id_abilitazione_cdr][3][] = $scadenza;
            }
        break;
    }                              
}

if (count($invio_scadenza)) {          
    //recupero tipologie
    $tipologie = [];
    foreach (scadenze\Tipologia::getAll() as $tipologia) {
        $tipologie[$tipologia->id] = $tipologia->descrizione;
    }
  
    //costruzione delle mail per i cdr abilitati ed invio
    foreach ($invio_scadenza as $scadenze_cdr) {
        //recupero del cdr
        $abilitazione_cdr = new scadenze\AbilitazioneCdr($scadenza->id_abilitazione_cdr);
        $anagrafica_cdr = AnagraficaCdr::factoryFromCodice($abilitazione_cdr->codice_cdr, new DateTime());        
        if ($anagrafica_cdr !== null) {        
            $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
            $anagrafica_desc = $anagrafica_cdr->codice." - ".$tipo_cdr->abbreviazione." ".$anagrafica_cdr->descrizione;
        }
        else {
            $anagrafica_desc = $abilitazione_cdr->codice_cdr . (" (inattivo dal ".date("d/m/Y").")");
        }        
        
        $mail = new MailGraph(SCADENZE_MAIL_INVIO);
        $mail->contentType = "HTML";
        $mail->subject = html_entity_decode("Promemoria attività in scadenza - '".$anagrafica_desc."'");
        $text_html = "<p>Con la presente si segnalano le prossime scadenze caricate a sistema:</p>";         
                
        if (array_key_exists(2, $scadenze_cdr)) {             
            $text_html .= "
                <table style='width:100%;border:solid 1px black; text-align:center'>    
                    <caption>Scadenze imminenti</caption>
                    <thead>
                        <tr>
                            <td style='border:solid 1px black;'>Termine</td>
                            <td style='border:solid 1px black;'>N. Protocollo</td>
                            <td style='border:solid 1px black;'>Tipo scadenza/richiesta</td>
                            <td style='border:solid 1px black;'>Obiettivo di Scadenza</td>
                        </tr>
                    </thead>
                    <tbody>                
                ";
            foreach($scadenze_cdr[2] as $scadenza) {                
                $text_html .= "            
                <tr style='border:solid 1px black;'>
                    <td>".CoreHelper::formatUiDate($scadenza->data_scadenza)."</td>
                    <td>".$scadenza->protocollo."</td>
                    <td>".$tipologie[$scadenza->id_tipologia]."</td>
                    <td>".$scadenza->oggetto."</td>
                </tr>               
                ";
            }            
        }                        
        if (array_key_exists(3, $scadenze_cdr)) {            
            $text_html .= "
                <table style='width:100%;border:solid 1px black; text-align:center'>    
                    <caption>Altre attività in scadenza</caption>
                    <thead>
                        <tr>
                            <td style='border:solid 1px black;'>Termine</td>
                            <td style='border:solid 1px black;'>N. Protocollo</td>
                            <td style='border:solid 1px black;'>Tipo scadenza/richiesta</td>
                            <td style='border:solid 1px black;'>Obiettivo di Scadenza</td>
                        </tr>
                    </thead>
                    <tbody>                
                ";
            foreach($scadenze_cdr[3] as $scadenza) {                
                $text_html .= "            
                <tr style='border:solid 1px black;'>
                    <td>".CoreHelper::formatUiDate($scadenza->data_scadenza)."</td>
                    <td>".$scadenza->protocollo."</td>
                    <td>".$tipologie[$scadenza->id_tipologia]."</td>
                    <td>".$scadenza->oggetto."</td>
                </tr>               
                ";
            } 
        }
        $text_html .= "
                </tbody>
            </table>
            <br>"; 
        $text_html .= "<p>Qualora si ravvisassero errori di attribuzione o di identificazione del termine dell'adempimento, lacune o inserimenti impropri si chiede la cortesia di segnalarlo.
            Si ringrazia per l'attenzione e la collaborazione.</p>
            <p>Cordiali saluti</p><br><br>";

        $mail->message = html_entity_decode($text_html);
        foreach($abilitazione_cdr->getContattiMail() as $recipient) {
            $mail->toRecipients[] = $recipient->mail;
        }
        
        //se no sono ritornati errori
        if (strlen ($mail->send()) == 0) {
            if (array_key_exists(3, $scadenze_cdr)) {
                foreach($scadenze_cdr[3] as $scadenza) {
                    $scadenza->mail_promemoria_inviata = true;
                    $scadenza->save(["mail_promemoria_inviata"]);
                }
            }
        }        
        unset($mail);     
    }
}