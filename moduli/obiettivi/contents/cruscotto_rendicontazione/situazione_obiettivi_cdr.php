<?php

$user = LoggedUser::getInstance();

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

$cdr = $cm->oPage->globals["cdr"]["value"];
$anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $date);

if ($anagrafica_cdr == null) {
    ffErrorHandler::raise("Errore: non si dispone deiprivilegi per l'accesso alla pagina.");
}

$periodo = null;
if (isset($_REQUEST["periodo_select"])) {
    try {
        $periodo = new ObiettiviPeriodoRendicontazione($_REQUEST["periodo_select"]);
    } catch (Exception $ex) {
        
    }
}
if ($periodo == null) {
    die("Errore nella selezione del periodo");
}

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("matrice_rendicontazione_cdr.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);

//url della pagina di modifica di cdr_url (con i parametri globali)
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("id_periodo", $periodo->id);

//viene visualizzato il link all'estrazione solamente se il cdr non è quello radice (per evitare estrazione inutile e pesante)
if ($cdr->id_padre !== 0) {
    $tpl->parse("LinkEstrazione", false);
}

//intestazione della tabella, obiettivi del cdr
$colonna = 1;
$riga = 0;
$obiettivi_cdr_anno = $anagrafica_cdr->getObiettiviCdrAnno($anno);
if (count($obiettivi_cdr_anno) > 0) {
    $obiettivi_colspan = 0;
    foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
        $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
        $cod_ob = "";
        for ($i = 0; $i < strlen($obiettivo->codice); $i++) {
            $cod_ob .= $obiettivo->codice[$i] . "<br>";
        }
        if ($obiettivo_cdr->isCoreferenza()) {
            $cod_ob .= $obiettivo->codice[$i] . "T";
        }
        $tpl->set_var("codice_obiettivo", $cod_ob);
        $tpl->set_var("desc_obiettivo", $obiettivo->titolo);
        $tpl->set_var("riga", $riga);
        $tpl->set_var("colonna", $colonna++);
        $tpl->parse("Obiettivi", true);

        $obiettivi_colspan++;
    }
    $riga++;
    //righe della tabella, cdr e per ognuno di essi associazione e rendicontazione agli obiettivi
    $cdr_figli = array();
    $cdr_figli[] = $cdr;
    $cdr_figli = array_merge($cdr_figli, $cdr->getFigli());
    $obiettivi_modificabili = false;
    foreach ($cdr_figli as $cdr_figlio) {
        $tpl->set_var("codice_cdr", $cdr_figlio->codice);
        $tpl->set_var("desc_cdr", $cdr_figlio->descrizione);
        $tpl->set_var("riga", $riga++);
        $colonna = 1;
        $cdr_figlio_obiettivo = new Cdr($cdr_figlio->id);
        $anagrafica_cdr_figlio_obiettivo = AnagraficaCdrObiettivi::factoryFromCodice($cdr_figlio_obiettivo->codice, $date);
        $obiettivi_cdr_anno_figlio = $anagrafica_cdr_figlio_obiettivo->getObiettiviCdrAnno($anno);
        foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
            $tpl->set_var("colonna", $colonna++);
            $found = null;
            foreach ($obiettivi_cdr_anno_figlio as $key => $obiettivo_cdr_anno_figlio) {
                if ($obiettivo_cdr_anno_figlio->data_eliminazione == null) {
                    if ($obiettivo_cdr->id_obiettivo == $obiettivo_cdr_anno_figlio->id_obiettivo) {
                        $found = $obiettivo_cdr_anno_figlio->id;
                        if ($obiettivo_cdr_anno_figlio->isCoreferenza()) {
                            $obiettivo_cdr_aziendale = $obiettivo_cdr_anno_figlio->getObiettivoCdrAziendale();
                            $rendicontazione = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo);
                            $trasversale_desc = "*";
                        }
                        else {
                            $rendicontazione = $obiettivo_cdr_anno_figlio->getRendicontazionePeriodo($periodo);
                            $trasversale_desc = "";
                        }
                        unset($obiettivi_cdr_anno_figlio[$key]);
                        break;
                    }
                }
            }
            $tpl->set_var("id_obiettivo_cdr", $obiettivo_cdr->id);
            if ($found == null) {
                $tpl->parse("NoObiettivoCdr", false);
                $tpl->set_var("RendicontazioneObiettivoCdr", false);
                $tpl->set_var("azioni_class", false);
                $tpl->set_var("modificabile_class", false);
            }
            else {
                $tpl->set_var("id_rendicontazione", $rendicontazione->id);
                if ($rendicontazione !== null) {
                    $tpl->set_var("rendicontazione_obiettivo_cdr", (int)$rendicontazione->perc_raggiungimento . "%" . $trasversale_desc);
                    if ($rendicontazione->raggiungibile == true) {
                        $tpl->set_var("azioni_class", "azioni_definite");
                    }
                    else {
                        $tpl->set_var("azioni_class", "azioni_non_definite");
                    }
                }
                else {                    
                    $tpl->set_var("rendicontazione_obiettivo_cdr", "NR");
                    $tpl->set_var("azioni_class", "");
                }


                $tpl->parse("RendicontazioneObiettivoCdr", false);
                $tpl->set_var("NoObiettivoCdr", false);
            }
            $tpl->parse("ObiettivoCdr", true);
        }
        $tpl->set_var("colonna", 0);
        $tpl->parse("Cdr", true);
        $tpl->set_var("ObiettivoCdr", false);
    }
    //se è definito almeno un obiettivo_cdr per l'anno (già verificato perchè ci sitrovi nel ramo) ed è definito almeno un rendicontazione e almeno un obiettivo risulta aperto	
    if (count($cdr_figli) == 0) {
        $tpl->set_var("obiettivi_colspan", $obiettivi_colspan + 2);
        $tpl->parse("NoCdr", true);
    }
    $tpl->parse("MatricePesiCdr", true);
}
else {
    $tpl->parse("NoObiettivi", true);
}

//***********************
//Adding contents to page
die($tpl->rpparse("main", true));
