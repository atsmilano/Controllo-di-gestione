<?php
//librerie jquery per tooltip
CoreHelper::includeJqueryUi();
$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("report_situazione_attuale.html", "main");

$tpl->set_var("module_img_path", $modulo->module_theme_full_path . "/images");
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("ret_url", urlencode($_SERVER["REQUEST_URI"]));

$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$cdr_global = $cm->oPage->globals["cdr"]["value"];

//recupero di ruoli/qualifiche interne
$ruoli = array();
foreach (Ruolo::getAll() as $ruolo) {
    $ruoli[$ruolo->id] = $ruolo;
}
$qualifiche_interne = array();
foreach (QualificaInterna::getAll() as $qualifica_interna) {
    $qualifiche_interne[$qualifica_interna->id] = $qualifica_interna;
}
$cdr_report = array();

//recupero CdR ramo
$cdr_ramo = $cdr_global->getGerarchia();
//recupero global
if (isset($_REQUEST["ruolo_select"])) {
    $id_ruolo_selezionato = htmlspecialchars($_REQUEST["ruolo_select"]);
} else {
    $id_ruolo_selezionato = 0;
}

if (isset($_REQUEST["filter_qi_ids"]) && strlen($_REQUEST["filter_qi_ids"])) {
    $ids_qualifiche_selezionate = explode(",", htmlspecialchars($_REQUEST["filter_qi_ids"]));
} else {
    $ids_qualifiche_selezionate = null;
}
        
//******************************************************************************
//Filtro CdR
$report = array();

//gestione filtro
//verifica ridondante (in gerarchia è presente anche il cdr selezionato) inserita per robustezza
$id_ruoli_filtro = array();
$id_qualifiche_interne_filtro = array();
if (count($cdr_ramo) > 0) {        
    //generazione del filtro sul cdr    
    $cdr_selezionato = null;
    if (isset($_REQUEST["cdr_ru_select"])) {
        $id_cdr_selezionato = htmlspecialchars($_REQUEST["cdr_ru_select"]);
    } else {
        $id_cdr_selezionato = 0;
    }
    foreach ($cdr_ramo as $cdr_figlio_ramo) {
        //gestione filtro            
        if ($cdr_figlio_ramo["cdr"]->id == $id_cdr_selezionato) {
            $tpl->set_var("cdr_ru_selected", "selected='selected'");
            $cdr_selezionato = $cdr_figlio_ramo["cdr"];
        } else {
            $tpl->set_var("cdr_ru_selected", "");            
        }
        $tpl->set_var("cdr_ru_id", $cdr_figlio_ramo["cdr"]->id);
        $indent = "";
        for ($i = 0; $i < $cdr_figlio_ramo["livello"]; $i++) {
            $indent .= "----";
        }
        $tipo_cdr = new TipoCdr($cdr_figlio_ramo["cdr"]->id_tipo_cdr);
        $tpl->set_var("cdr_ru_indent", $indent);
        $tpl->set_var("cdr_ru_descrizione", $cdr_figlio_ramo["cdr"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_figlio_ramo["cdr"]->descrizione);
        $tpl->parse("SectOptionCdrRU", true);
    }
    if ($cdr_selezionato == null) {
        $cdr_selezionato = $cdr_global;
    }    
    unset ($id_cdr_selezionato);
    
    if (isset($_REQUEST["filter_cdr_ramo"]) && $_REQUEST["filter_cdr_ramo"] == "true") {
        $cdr_ramo_select = true;
        $tpl->set_var("filter_cdr_ramo_checked", "checked");
    } else {
        $cdr_ramo_select = false;
    }

    $cdr_ramo = $cdr_selezionato->getGerarchia();
    foreach ($cdr_ramo as $cdr_figlio_ramo) {    
        $cdr_report[$cdr_figlio_ramo["cdr"]->id] = $cdr_figlio_ramo;                              
        
        //viene sempre creato un elemento per il cdr per gestire i filtri in caso il cdr non corrisponda ai criteri di ricerca ma i figli si o non abbia personale afferente, mantenendo l'ordinamento gerarchico
        //a fine popolamento del report non verranno visualizzati i cdr che non abbiano neppure un dato corrispondente ai criteri di ricerca
        if (!array_key_exists($cdr_figlio_ramo["cdr"]->id, $report)) {
            //inizializzazione array
            $report[$cdr_figlio_ramo["cdr"]->id]["cdr_figli_in_ramo"] = false;
            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"] = array();
            $report[$cdr_figlio_ramo["cdr"]->id]["tot_teste"] = 0;
            $report[$cdr_figlio_ramo["cdr"]->id]["tot_fte"] = 0;
            $report[$cdr_figlio_ramo["cdr"]->id]["tot_teste_ramo"] = 0;
            $report[$cdr_figlio_ramo["cdr"]->id]["tot_fte_ramo"] = 0;
        }
                   
        //recupero del personale per ogni cdr della gerarchia
        //array cdr-ruolo-qualifica-personale, struttura del report espoandibile
        foreach ($cdr_figlio_ramo["cdr"]->getPersonaleCdcAfferentiInData($data_riferimento) as $cdc_personale) {
            try {                
                $dipendente = Personale::factoryFromMatricola($cdc_personale->matricola_personale);
                $carriera = $dipendente->getCarriera($data_riferimento->format("Y-m-d"));
                if (array_key_exists($carriera->id_qualifica_interna, $qualifiche_interne)) {
                    $qualifica_interna = $qualifiche_interne[$carriera->id_qualifica_interna];                    
                    if (!array_key_exists($qualifica_interna->id_ruolo, $ruoli)) {
                        throw Exception("Ruolo non esistente.");
                    }
                    $cdr = $cdr_figlio_ramo["cdr"];
                    //gestione univocità valori filtri
                    if ($cdr_ramo_select == false || ($cdr_ramo_select == true && $cdr->id == $cdr_selezionato->id)) {                        
                        if (!array_key_exists($qualifica_interna->id_ruolo, $id_ruoli_filtro)) {                        
                            $id_ruoli_filtro[$qualifica_interna->id_ruolo] = $qualifica_interna->id_ruolo;
                        } 
                        if ($id_ruolo_selezionato == 0 || ($id_ruolo_selezionato !== 0 && $id_ruolo_selezionato == $qualifica_interna->id_ruolo)) {
                            if (!array_key_exists($qualifica_interna->id, $qualifiche_interne_filtro)) {                        
                                $qualifiche_interne_filtro[$qualifica_interna->id] = $qualifica_interna;
                            }
                        }                                                    
                    }
    
                    //inizializzazioni totali
                    if ($id_ruolo_selezionato == 0 || ($id_ruolo_selezionato !== 0 && $id_ruolo_selezionato == $qualifica_interna->id_ruolo)) {
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_fte"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste_ramo"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_fte_ramo"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_teste"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_fte"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_teste_ramo"] = 0;}
                        if (!isset($report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"])){$report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_fte_ramo"] = 0;}
                        if (                                                                           
                            ($qualifiche_interne_filtro == null || $qualifiche_interne_filtro !== null && ($ids_qualifiche_selezionate == null || in_array($qualifica_interna->id, $ids_qualifiche_selezionate, false))) 
                            ){
                            
                            $perc_rapporto_lavoro = $carriera->perc_rapporto_lavoro==null?100:$carriera->perc_rapporto_lavoro;  
                            $fte = ($perc_rapporto_lavoro/100)*($cdc_personale->percentuale/100);
                            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["dipendenti"][] = array("dipendente"=>$dipendente,
                                                                                                                                            "perc_rapporto_lavoro"=>$carriera->perc_rapporto_lavoro,
                                                                                                                                            "cdc_personale"=>$cdc_personale,   
                                                                                                                                            "fte"=>$fte,
                                                                                                                                            );                                              
                                                     

                            $report[$cdr_figlio_ramo["cdr"]->id]["tot_teste"] ++; 
                            $report[$cdr_figlio_ramo["cdr"]->id]["tot_fte"] += $fte;   

                            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste"] ++;
                            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_fte"] += $fte;

                            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_teste"] ++;
                            $report[$cdr_figlio_ramo["cdr"]->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_fte"] += $fte;

                            if ($cdr_ramo_select == false) {                            
                                if ($cdr->id !== $cdr_selezionato->id){
                                    do { 
                                        $cdr = new Cdr($cdr->id_padre);                                   
                                        $report[$cdr->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["dipendenti_ramo"][] = array("dipendente"=>$dipendente,
                                                                                                                                               "perc_rapporto_lavoro"=>$carriera->perc_rapporto_lavoro,
                                                                                                                                                "cdc_personale"=>$cdc_personale,
                                                                                                                                                "fte"=>$fte,
                                                                                                                                                );
                                        $report[$cdr->id]["cdr_figli_in_ramo"] = true;                                    

                                        $report[$cdr->id]["tot_teste_ramo"] ++; 
                                        $report[$cdr->id]["tot_fte_ramo"] += $fte;   

                                        $report[$cdr->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_teste_ramo"] ++;
                                        $report[$cdr->id]["ruoli"][$qualifica_interna->id_ruolo]["tot_fte_ramo"] += $fte;

                                        $report[$cdr->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_teste_ramo"] ++;
                                        $report[$cdr->id]["ruoli"][$qualifica_interna->id_ruolo]["qualifiche"][$qualifica_interna->id]["tot_fte_ramo"] += $fte;
                                    } while ($cdr->id !== $cdr_selezionato->id);
                                }   
                            }
                            else {

                            }
                        }
                    }
                }
                else {
                    throw new Exception("Qualifica interna non esistente.");
                }
            } catch (Exception $ex) {
                //anomalie non riportate nel report
            }
        }               
    }
} else {
    ffErrorHandler::raise("Nessun CDR disponibile");
}

//gestione visualizzazione filtri ruolo e qualifica interna
if (count($id_ruoli_filtro) > 0) {
    //generazione del filtro su distretto
    $id_ruolo_filtro = 0;    
    foreach ($id_ruoli_filtro as $id_ruolo) {
        $ruolo_filtro = $ruoli[$id_ruolo];

        if ($ruolo_filtro->id == $id_ruolo_selezionato) {
            $tpl->set_var("ruolo_selected", "selected='selected'");
         
        } else {
            $tpl->set_var("ruolo_selected", "");
        }
        $tpl->set_var("ruolo_select_id", $ruolo_filtro->id);
        $tpl->set_var("ruolo_select_descrizione", $ruolo_filtro->descrizione);
        $tpl->parse("SectOptionRuolo", true);
    }
}

if ($qualifiche_interne_filtro !== null) {
    foreach ($qualifiche_interne_filtro as $qualifica) {         
        if ($ids_qualifiche_selezionate !== null && in_array($qualifica->id, $ids_qualifiche_selezionate, false)) {
            $tpl->set_var("filter_qi_checked", "checked");         
        } else {
            $tpl->set_var("filter_qi_checked", "");
        }
        $tpl->set_var("qi_checkbox_id", $qualifica->id);
        $tpl->set_var("qi_checkbox_descrizione", $qualifica->descrizione);
        $tpl->parse("SectOptionQI", true);
    }       
}

//visualizzazione e conteggi     
//ciclo CdR
foreach ($report as $id_cdr => $cdr_personale) {
    $cdr = $cdr_report[$id_cdr];
               
    if ($cdr_personale["cdr_figli_in_ramo"] == true || $cdr_personale["tot_teste"] > 0) {     
        //indentazione cdr rispetto alla gerarchia        
        $indent = "";
        for($i=0; $i<$cdr["livello"]+1; $i++) {
            $indent .= "<span class='gerarchia_indent'>&nbsp;</span>";
        }
        $tpl->set_var("indent", $indent);    
        $tpl->set_var("livello_cdr", $cdr["livello"]);
        $tpl->set_var("id_cdr_padre", $cdr["cdr"]->id_padre);
        $tpl->set_var("id_cdr", $cdr["cdr"]->id);
        //Ciclo ruoli
        foreach ($cdr_personale["ruoli"] as $id_ruolo => $ruolo) {             
            //Ciclo qualifiche interne
            $tpl->set_var("id_ruolo", $ruoli[$id_ruolo]->id);   
            if ($cdr_ramo_select == false) {
                uasort($ruolo["qualifiche"], 'ordinaQIperFTERamo');
            }
            else {
                uasort($ruolo["qualifiche"], 'ordinaQIperFTE');
            }                                        
            foreach ($ruolo["qualifiche"] as $id_qualifica_interna => $qualifica_interna) {
                //Ciclo dipendente
                $tpl->set_var("id_qualifica_interna", $qualifiche_interne[$id_qualifica_interna]->id);            
                foreach ($qualifica_interna["dipendenti"] as $dipendente) {
                    $perc_rapporto_lavoro = $dipendente["perc_rapporto_lavoro"]==null?100:$dipendente["perc_rapporto_lavoro"];
                    $perc_cdc = $dipendente["cdc_personale"]->percentuale;                    

                    $tpl->set_var("dipendente", $dipendente["dipendente"]->cognome." ".$dipendente["dipendente"]->nome." (".$dipendente["dipendente"]->matricola.")");
                    $tpl->set_var("perc_rapporto_lavoro", $perc_rapporto_lavoro);
                    $tpl->set_var("cdc", $dipendente["cdc_personale"]->codice_cdc . " (".$perc_cdc."%)");
                    $tpl->set_var("fte", $dipendente["fte"]!==(int)$dipendente["fte"]?number_format($dipendente["fte"], 2):$dipendente["fte"]);
                    $tpl->set_var("fte_ramo", $dipendente["fte"]!==(int)$dipendente["fte"]?number_format($dipendente["fte"], 2):$dipendente["fte"]);

                    $tpl->set_var("dipendente_diretto_class", "dipendente_diretto");

                    if ($cdr_ramo_select == false) {
                        $tpl->parse("SectTotDipendenteRamo", false);
                    }
                    $tpl->parse("SectReportDipendenteRow", true);
                }    
                foreach ($qualifica_interna["dipendenti_ramo"] as $dipendente) {
                    $perc_rapporto_lavoro = $dipendente["perc_rapporto_lavoro"]==null?100:$dipendente["perc_rapporto_lavoro"];
                    $perc_cdc = $dipendente["cdc_personale"]->percentuale;                    

                    $tpl->set_var("dipendente", $dipendente["dipendente"]->cognome." ".$dipendente["dipendente"]->nome." (".$dipendente["dipendente"]->matricola.")");
                    $tpl->set_var("perc_rapporto_lavoro", $perc_rapporto_lavoro);
                    $tpl->set_var("cdc", $dipendente["cdc_personale"]->codice_cdc . " (".$perc_cdc."%)");
                    $tpl->set_var("fte", null);
                    $tpl->set_var("fte_ramo", $dipendente["fte"]!==(int)$dipendente["fte"]?number_format($dipendente["fte"], 2):$dipendente["fte"]);
                    
                    $tpl->set_var("dipendente_diretto_class", "");

                    if ($cdr_ramo_select == false) {
                        $tpl->parse("SectTotDipendenteRamo", false);
                    }
                    $tpl->parse("SectReportDipendenteRow", true);
                }
                $tpl->set_var("qualifica_interna", $qualifiche_interne[$id_qualifica_interna]->descrizione);            
                $tpl->set_var("teste_qi", $qualifica_interna["tot_teste"]);
                $tpl->set_var("fte_qi", $qualifica_interna["tot_fte"]!==(int)$qualifica_interna["tot_fte"]?number_format($qualifica_interna["tot_fte"], 2):$qualifica_interna["tot_fte"]);
                if ($cdr_ramo_select == false) {
                    $fte_qi_ramo = $qualifica_interna["tot_fte"]+$qualifica_interna["tot_fte_ramo"];
                    $tpl->set_var("teste_qi_ramo", $qualifica_interna["tot_teste"]+$qualifica_interna["tot_teste_ramo"]);
                    $tpl->set_var("fte_qi_ramo", $fte_qi_ramo!==(int)$fte_qi_ramo?number_format($fte_qi_ramo, 2):$fte_qi_ramo);
                    $tpl->parse("SectTotQIRamo", false);
                }
                $tpl->parse("SectReportQIRow", true);
                if ($cdr_ramo_select == false) {
                    $tpl->set_var("SectTotDipendenteRamo", null);
                }
                $tpl->set_var("SectReportDipendenteRow", null);
            }         
            $tpl->set_var("ruolo", $ruoli[$id_ruolo]->descrizione);
            $tpl->set_var("teste_ruolo", $ruolo["tot_teste"]);
            $tpl->set_var("fte_ruolo", $ruolo["tot_fte"]!==(int)$ruolo["tot_fte"]?number_format($ruolo["tot_fte"], 2):$ruolo["tot_fte"]);
            if ($cdr_ramo_select == false) {
                $fte_ruolo_ramo = $ruolo["tot_fte"]+$ruolo["tot_fte_ramo"];
                $tpl->set_var("teste_ruolo_ramo", $ruolo["tot_teste"]+$ruolo["tot_teste_ramo"]);
                $tpl->set_var("fte_ruolo_ramo", $fte_ruolo_ramo!==(int)$fte_ruolo_ramo?number_format($fte_ruolo_ramo, 2):$fte_ruolo_ramo);      
                $tpl->parse("SectTotRuoloRamo", false);
            }
            $tpl->parse("SectReportRuoloRow", true); 
            if ($cdr_ramo_select == false) {
                $tpl->set_var("SectTotQIRamo", null);
            }
            $tpl->set_var("SectReportQIRow", null);
        }     
        $tipo_cdr = new TipoCdr($cdr["cdr"]->id_tipo_cdr);
        $tpl->set_var("cdr", $cdr["cdr"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr["cdr"]->descrizione);
        $tpl->set_var("teste_cdr", $cdr_personale["tot_teste"]);
        $tpl->set_var("fte_cdr", $cdr_personale["tot_fte"]!==(int)$cdr_personale["tot_fte"]?number_format($cdr_personale["tot_fte"], 2):$cdr_personale["tot_fte"]);
        if ($cdr_ramo_select == false) {
            $fte_cdr_ramo = $cdr_personale["tot_fte"]+$cdr_personale["tot_fte_ramo"];
            $tpl->set_var("teste_cdr_ramo", $cdr_personale["tot_teste"]+$cdr_personale["tot_teste_ramo"]);
            $tpl->set_var("fte_cdr_ramo", $fte_cdr_ramo!==(int)$fte_cdr_ramo?number_format($fte_cdr_ramo, 2):$fte_cdr_ramo);
            $tpl->parse("SectTotCdrRamo", false);
        }
        //verifica che il cdr abbia figli
        if ($cdr_personale["cdr_figli_in_ramo"] == true) {
            $tpl->parse("SectFigli", false);
        }
        else {
            $tpl->parse("SectNoFigli", false);
        }
        $tpl->parse("SectReportCdrRow", true);
        $tpl->set_var("SectCdrIndent", null);
        $tpl->set_var("SectFigli", null);
        $tpl->set_var("SectNoFigli", null);
        if ($cdr_ramo_select == false) {
            $tpl->set_var("SectTotRuoloRamo", null);
        }
        $tpl->set_var("SectReportRuoloRow", null);
    }
}
if ($cdr_ramo_select == false) {
    $tpl->parse("SectThRamo", false);
}
$tpl->parse("SectReportRuAttuale", false);

$cm->oPage->addContent($tpl);

function ordinaQIperFTE($a, $b) {
    return $b['tot_fte'] - $a['tot_fte'];
}

function ordinaQIperFTERamo($a, $b) {
    return $b['tot_fte_ramo'] - $a['tot_fte_ramo'];
}