<?php

$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_operatore_formazione") && !$user->hasPrivilege("fabbisogno_admin")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere all'estrazione delle schede.");
}

$anno = $cm->oPage->globals["anno"]["value"];

//in caso ci siano fabbisogni previsti per l'anno selezionato
$fabbisogni_da_estrarre = array();
$fabbisogni_anno = FabbisognoFormazione\Richiesta::getAll(array("ID_anno_budget" => $anno->id));
if (isset($_REQUEST["ids"])) {
    $ids = explode(",", $_REQUEST["ids"]);
    foreach ($ids as $id) {
        foreach ($fabbisogni_anno as $fabbisogno) {
            if ($fabbisogno->id == $id) {
                $fabbisogni_da_estrarre[] = $fabbisogno;
                break;
            }
        }
    }
}

if (count($fabbisogni_da_estrarre)) {
    @date_default_timezone_set("GMT");

    header('Content-type: text/xml');
    header('Content-Disposition: attachment; filename="dataset.xml"');

    $xml = new XMLWriter();
    // Output directly to the user
    $xml->openURI('php://output');
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(8);
    $xml->startElement("dataroot");
    foreach ($fabbisogni_da_estrarre as $evento) {
        $obiettivo = new \FabbisognoFormazione\ObiettivoRiferimento($evento->id_obiettivo_riferimento);
        $xml->startElement("evento_2019");
        $xml->writeAttribute('ID_EVENTO', '0');
        $xml->writeAttribute('DS_TITOLO', $evento->titolo);
        $xml->writeAttribute('DS_EVENTO', $evento->descrizione);
        $xml->writeAttribute('ID_TIPO_OBIETTIVO', $obiettivo->id_tabella_riferimento_tipi_obiettivi);
        //OPZ $xml->writeAttribute('ID_TIPO_TEMATICA', );
        $xml->writeAttribute('DS_NOTE_OBIETTIVO', $evento->obiettivi_formativi);
        //OPZ $xml->writeAttribute('ID_TIPI_OBIETTIVI_FORMATIVI', );
        $xml->writeAttribute('ID_TIPO_ISCRIZIONE', 1);
        $xml->writeAttribute('TEL_SEGRETERIA', $evento->telefono_segreteria_organizzativa);
        $xml->writeAttribute('EMAIL_SEGRETERIA', $evento->mail_segreteria_organizzativa);
        $xml->writeAttribute('ID_TIPO_METODO_DIDAT', 1);
        //OPZ $xml->writeAttribute('METOD_INTERATTIVE', );
        //OPZ $xml->writeAttribute('FL_PROGRAMMATO', );
        //OPZ $xml->writeAttribute('ID_TIPO_VALUT_GRADIMENTO', );           
        if ($evento->id_modalita_valutazione_apprendimento == 2) {
            $xml->writeAttribute('VAL_APP_SCELTA_MULTIPLA', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_SCELTA_MULTIPLA', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 4) {
            $xml->writeAttribute('VAL_APP_COLLOQUIO', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_COLLOQUIO', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 5) {
            $xml->writeAttribute('VAL_APP_ELAB_RELAZ_INTERM', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_ELAB_RELAZ_INTERM', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 6) {
            $xml->writeAttribute('VAL_APP_REDAZ_DOC_CONC', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_REDAZ_DOC_CONC', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 7) {
            $xml->writeAttribute('VAL_APP_VAL_RESP_SCIENT', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_VAL_RESP_SCIENT', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 8) {
            $xml->writeAttribute('VAL_APP_VAL_DOC_TUTOR', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_VAL_DOC_TUTOR', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 10) {
            $xml->writeAttribute('VAL_APP_NON_PREVISTA', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_NON_PREVISTA', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 9) {
            $xml->writeAttribute('VAL_APP_PROVA_PRATICA', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_PROVA_PRATICA', 'N');
        }
        if ($evento->id_modalita_valutazione_apprendimento == 3) {
            $xml->writeAttribute('VAL_APP_RISP_APERTA', 'Y');
        }
        else {
            $xml->writeAttribute('VAL_APP_RISP_APERTA', 'N');
        }
        $xml->writeAttribute('NUM_EDIZIONI_PREVISIONALI', $evento->n_edizioni);
        $xml->writeAttribute('BUDGET_PREVISIONALE', $evento->costi_edizione);

        //destinatari
        $xml->startElement("listaDestinatari_2019");
        $xml->writeAttribute('NR_TOT_DESTINATARI', $evento->n_posti);
        $xml->startElement("destinatario_2019");
        $xml->writeAttribute('ID_DESTINATARIO', '0');
        try {
            $destinatari = new \FabbisognoFormazione\Destinatari($evento->id_destinatari);
        } catch (Exception $ex) {
            $destinatari = new \FabbisognoFormazione\Destinatari();
            $destinatari->id_tabella_riferimento_destinatari_validi = 0;
        }
        $xml->writeAttribute('ID_TIPO_DESTINATARIO', $destinatari->id_tabella_riferimento_destinatari_validi);
        $xml->endElement();
        $xml->endElement();
        $xml->startElement("listaAttivita_2019");
        $xml->startElement("attivita_2019");
        $xml->writeAttribute('ID_ATTIVITA', '0');
        try {
            $attivita = new \FabbisognoFormazione\Tipologia($evento->id_tipologia);
            if ($attivita->id_tabella_riferimento_attivita == 1) {
                $xml->startElement("elearning");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 3) {
                $xml->startElement("fadstrumenti");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 4) {
                $xml->startElement("fadsincrona");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 5) {
                $xml->startElement("addestramento");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 6) {
                $xml->startElement("ricerca");
                $xml->startElement("semestre_2019");
                $xml->writeAttribute('NUM_ORE_SEMESTRE', $evento->n_ore);
                $xml->endElement();
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 8) {
                $xml->startElement("gruppi");
                $xml->startElement("ore_incontro");
                $xml->writeAttribute('NUM_ORE_INCONTRO', $evento->n_ore);
                $xml->endElement();
                $xml->startElement("ore_incontro");
                $xml->writeAttribute('NUM_ORE_INCONTRO', 1);
                $xml->endElement();
                $xml->startElement("ore_incontro");
                $xml->writeAttribute('NUM_ORE_INCONTRO', 1);
                $xml->endElement();
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 9) {
                $xml->startElement("congresso");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 10) {
                $xml->startElement("formazione101");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 11) {
                $xml->startElement("formazione");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
            else if ($attivita->id_tabella_riferimento_attivita == 12) {
                $xml->startElement("videoconferenza");
                $xml->writeAttribute('NUM_ORE_TOTALE', $evento->n_ore);
                $xml->endElement();
            }
        } catch (Exception $ex) {
            
        }
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
    }
    $xml->endElement();
    $xml->endDocument();
    $xml->flush();
    die();
}
else {
    ffErrorHandler::raise("Errore: nessun fabbisogno da estrarre.");
}