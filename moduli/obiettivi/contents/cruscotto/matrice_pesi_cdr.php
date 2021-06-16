<?php

$user = LoggedUser::Instance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

$cdr = $cm->oPage->globals["cdr"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

$anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $date);
$peso_tot_obiettivi = $anagrafica_cdr->getPesoTotaleObiettivi($anno);

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("matrice_pesi_cdr.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);

//url della pagina di modifica di cdr_url (con i parametri globali)
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//viene visualizzato il link all'estrazione solamente se il cdr non Ã¨ quello radice (per evitare estrazione inutile e pesante)
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
        if ($obiettivo_cdr->isChiuso()) {
            $cod_ob .= $obiettivo->codice[$i] . "*";
        }
        $tpl->set_var("codice_obiettivo", $cod_ob);
        $tpl->set_var("desc_obiettivo", $obiettivo->titolo);
        $tpl->set_var("riga", $riga);
        $tpl->set_var("colonna", $colonna++);
        $tpl->parse("Obiettivi", true);

        $obiettivi_colspan++;
    }
    $riga++;
    //righe della tabella, cdr e per ognuno di essi associazione e peso agli obiettivi
    $cdr_figli = array($cdr);
    $cdr_figli = array_merge($cdr_figli, $cdr->getFigli());
    $obiettivi_modificabili = false;
    foreach ($cdr_figli as $cdr_figlio) {
        $tpl->set_var("codice_cdr", $cdr_figlio->codice);
        $tpl->set_var("desc_cdr", $cdr_figlio->descrizione);
        $tpl->set_var("riga", $riga++);
        $colonna = 1;
        $cdr_figlio_obiettivo = AnagraficaCdrObiettivi::factoryFromCodice($cdr_figlio->codice, $date);
        $obiettivi_cdr_anno_figlio = $cdr_figlio_obiettivo->getObiettiviCdrAnno($anno);
        $totale_obiettivi = 0;
        foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
            $tpl->set_var("colonna", $colonna++);
            $found = null;
            foreach ($obiettivi_cdr_anno_figlio as $key => $obiettivo_cdr_anno_figlio) {
                if ($obiettivo_cdr->id_obiettivo == $obiettivo_cdr_anno_figlio->id_obiettivo) {
                    //viene verificato che l'obiettivo sia stato assegnato dal cdr selezionato (o che sia di coreferenza e quindi assegnabile dal padre)
                    $obiettivo_cdr_padre = $obiettivo_cdr_anno_figlio->getObiettivoCdrPadre(true);
                    $is_obiettivo_cdr_figlio_coreferenza = $obiettivo_cdr_anno_figlio->isCoreferenza();

                    if ($obiettivo_cdr_padre == $obiettivo_cdr && $cdr->codice !== $cdr_figlio->codice) {
                        $assegnato_da_cdr = true;
                        $peso = $obiettivo_cdr_anno_figlio->peso;
                    } else {
                        $assegnato_da_cdr = false;
                        if ($cdr->codice !== $cdr_figlio->codice) {
                            $peso = null;
                        } else {
                            $peso = $obiettivo_cdr_anno_figlio->peso;
                        }
                    }
                    $found = array(
                        "id" => $obiettivo_cdr_anno_figlio->id,
                        "peso" => $peso,
                        "azioni" => $obiettivo_cdr_anno_figlio->azioni,
                        "chiuso" => $obiettivo_cdr_anno_figlio->isChiuso(),
                        "aziendale" => $obiettivo_cdr_anno_figlio->isObiettivoCdrAziendale(),
                        "coreferenza" => $is_obiettivo_cdr_figlio_coreferenza,
                        "codice_cdr_coreferenza" => $obiettivo_cdr_anno_figlio->codice_cdr_coreferenza,
                        "assegnato_da_cdr" => $assegnato_da_cdr,
                    );
                    $totale_obiettivi += $peso;
                    unset($obiettivi_cdr_anno_figlio[$key]);
                    break;
                }
            }
            //se ancora non è presente un'assegnazione
            if ($found == null) {
                if (($cdr_figlio->codice != $cdr->codice && !$obiettivo_cdr->isChiuso() && $user->hasPrivilege("resp_cdr_selezionato")) || ($cdr_figlio->codice == $cdr->codice && $user->hasPrivilege("obiettivi_aziendali_edit") && ($obiettivo_cdr->isObiettivoCdrAziendale() || $obiettivo_cdr->isCoreferenza()))
                ) {
                    $obiettivi_modificabili = true;
                    $tpl->set_var("modificabile_class", "modificabile");
                } else {
                    $tpl->set_var("modificabile_class", "non_modificabile");
                }
                $tpl->set_var("peso_obiettivo_cdr", "");
                $tpl->set_var("azioni_class", "");
            } else {
                $modificabile = false;
                $assegnabile = true;
                //se l'obiettivo non è assegnato dal o al cdr di riferimento                    
                if ($found["assegnato_da_cdr"] || $cdr->codice == $cdr_figlio->codice) {
                    //se l'obiettivo è aziendale modificabile dall'amministratore degli obiettivi aziendali
                    //oppure se risulta aperto e assegnato da l cdr selezionato risulterÃ  modificabile                        
                    //nel caso di cdr_figlio
                    if ($cdr_figlio->codice != $cdr->codice) {
                        if (!$obiettivo_cdr->isChiuso() && !$found["chiuso"]) {
                            if ($user->hasPrivilege("resp_cdr_selezionato")) {
                                $obiettivi_modificabili = true;
                                $modificabile = true;
                            }
                        }
                        $peso_obiettivo_cdr = $found["peso"];
                    }
                    //nel caso del cdr padre
                    else if ($cdr_figlio->codice == $cdr->codice) {
                        //se l'obiettivo è aziendale o di coreferenza diretta
                        if ($found["aziendale"] || ($found["coreferenza"] && $found["codice_cdr_coreferenza"] != null)) {
                            if ($user->hasPrivilege("obiettivi_aziendali_edit")) {
                                $obiettivi_modificabili = true;
                                $modificabile = true;
                            }
                        } else {
                            $modificabile = false;
                        }
                        $peso_obiettivo_cdr = $found["peso"];
                    }
                    //se nessuno dei due casi si verifica
                    else {
                        $assegnabile = false;
                        $peso_obiettivo_cdr = "NA";
                    }
                } else {
                    //il peso del cdr selezionabile è comunque sempre modificabile dall'admin
                    if ($cdr_figlio->codice !== $cdr->codice) {
                        $assegnabile = false;

                        $peso_obiettivo_cdr = "NA";
                    }
                }
                //modificabilità
                if ($modificabile == true) {
                    $tpl->set_var("modificabile_class", "modificabile");
                } else {
                    $tpl->set_var("modificabile_class", "non_modificabile");
                }

                //definizione delle proprietà della cella per visualizzazione
                if ($assegnabile == true) {
                    //Visualizzazione della definizione delle azioni
                    if (strlen($found["azioni"]) > 0) {
                        $tpl->set_var("azioni_class", "azioni_definite");
                    } else {
                        $tpl->set_var("azioni_class", "azioni_non_definite");
                    }
                } else {
                    $tpl->set_var("azioni_class", "obiettivo_non_assegnato_da_cdr");
                }
                $tpl->set_var("peso_obiettivo_cdr", $peso_obiettivo_cdr);
            }
            //vengono passati id_obiettivo e codice_cdr
            $tpl->set_var("id_obiettivo", $obiettivo_cdr->id_obiettivo);
            //vengono passati gli eventuali parametri della relazione obiettivo_cdr
            $tpl->set_var("id_obiettivo_cdr", $found["id"]);
            $tpl->parse("PesoObiettivoCdr", false);

            $tpl->parse("ObiettivoCdr", true);
        }
        $tpl->set_var("totale_obiettivi_cdr", $totale_obiettivi);
        $tpl->set_var("colonna", 0);
        $tpl->parse("Cdr", true);
        $tpl->set_var("ObiettivoCdr", false);
        $tpl->set_var("totale_obiettivi_cdr", false);
    }
    //se è definito almeno un obiettivo_cdr per l'anno (già verificato perchè ci sitrovi nel ramo) ed è definito almeno un peso e almeno un obiettivo risulta aperto
    if (count($cdr_figli) > 0 && $obiettivi_modificabili == true) {
        $tpl->parse("AzioniMatrice", true);
    } else if (count($cdr_figli) == 0) {
        $tpl->set_var("obiettivi_colspan", $obiettivi_colspan + 2);
        $tpl->parse("NoCdr", true);
    }
    $tpl->parse("MatricePesiCdr", true);
} else {
    $tpl->parse("NoObiettivi", true);
}

//***********************
//Adding contents to page
die($tpl->rpparse("main", true));
