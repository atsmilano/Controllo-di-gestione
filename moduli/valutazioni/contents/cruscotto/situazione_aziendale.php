<?php
$user = LoggedUser::Instance();
//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("situazione_aziendale.html", "main");

$periodo = null;
if(isset($_REQUEST["periodo_select"])){    
    try {
        $periodo = new ValutazioniPeriodo($_REQUEST["periodo_select"]);
    } catch (Exception $ex) {

    }
}
if ($periodo == null){
    die("Errore nella selezione del periodo");
}

//Preparazione dei dati
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $periodo->data_fine;
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);

//estrazione delle valutazioni periodiche
$valutazioni_per_report = array();
foreach ($periodo->getValutazioniAttivePeriodo() as $valutazione) {    
    $valutato = Personale::factoryFromMatricola($valutazione->matricola_valutato); 
    
    $cdr_afferenza = $valutato->getCdrAfferenzaInData($cm->oPage->globals["tipo_piano_cdr"]["value"], $date);        
    if (count ($cdr_afferenza) == 0) {
        $ultimi_cdr_afferenza = $valutato->getCdrUltimaAfferenza($tipo_piano_cdr);
        if (count ($ultimi_cdr_afferenza) > 0) {
            foreach ($ultimi_cdr_afferenza as $cdr_aff) {
                try {
                    $cdr_attuale = Cdr::factoryFromCodice($cdr_aff["cdr"]->codice, $piano_cdr);
                    if ($cdr_attuale->id == $cdr_aff["cdr"]->id) {
                        $cdr_afferenza[] = $cdr_aff;
                    }
                } catch (Exception $ex) {

                }
            }
        }
    }
    
    $valutazioni_per_report[] = array(
                                    "valutazione" => $valutazione,
                                    "valutato" => $valutato,
                                    "cdr_afferenza" => $cdr_afferenza,
                                    );   
}
$valutazioni_periodo = $valutazioni_per_report;
        
//vengono estratti i rami gerarchici dei cdr che verranno visualizzati nel cruscotto
$rami_gerarchici = Cdr::getRamiGerarchiciElencoCdr(ValutazioniCdrCruscotto::getCodiciCdrCruscottoAnno($anno), $piano_cdr);
//viene costruito l'array per il report (n_valutazioni_ramo, n_completate_valutatore, n_completate_valutato)
$report = array();
//conteggio valutazioni su rami gerarchici
foreach ($rami_gerarchici as $ramo_gerarchico) {
    //array per il salvataggio dei dati del cdr (ramo) corrente per il report
    //il cdr padre del piano Ã¨ il primo del ramo gerarchico
    $dati_cdr_report = array(
                            "cdr" => $ramo_gerarchico[0]["cdr"],
                            "n_valutazioni" => 0,
                            "n_valutazioni_completate_valutatore" => 0,
                            "n_valutazioni_completate_valutato" => 0,
                            "n_valutazioni_dirigenza" => 0,
                            "n_valutazioni_dirigenza_completate_valutatore" => 0,
                            "n_valutazioni_dirigenza_completate_valutato" => 0,
                            "n_valutazioni_comparto" => 0,
                            "n_valutazioni_comparto_completate_valutatore" => 0,
                            "n_valutazioni_comparto_completate_valutato" => 0,
                            );          
    //vengono conteggiate tutte le valutazioni per il ramo gerarchico
    foreach ($ramo_gerarchico as $cdr_ramo) {     
        //se viene trovata una corrispondenza fra cdr del valutato e il rampo viene conteggiata la valutazione ed eliminato
        //il cdr di afferenza (e nel caso non ce ne siano piÃ¹ anche il valutato) per evitare iterazioni successive inutili                
        foreach ($valutazioni_per_report as $valutazione_key => $valutazione) {
            //per ogni cdr d'afferenza del dipendente
            foreach ($valutazione["cdr_afferenza"] as $cdr_afferenza_key => $cdr_afferenza) {                  
                if ($cdr_ramo["cdr"]->id == $cdr_afferenza["cdr"]->id) {
                    //conteggio complessivo
                    $dati_cdr_report["n_valutazioni"]++;  
                    if ($valutazione["valutazione"]->data_firma_valutatore !== null) {                       
                        $dati_cdr_report["n_valutazioni_completate_valutatore"]++;
                    }
                    if ($valutazione["valutazione"]->data_firma_valutato !== null) {
                        $dati_cdr_report["n_valutazioni_completate_valutato"]++;
                    }
                    //conteggio dirigenza
                    if ($valutazione["valutazione"]->categoria->dirigenza == true) {
                        $dati_cdr_report["n_valutazioni_dirigenza"]++;
                        if ($valutazione["valutazione"]->data_firma_valutatore !== null) {                       
                        $dati_cdr_report["n_valutazioni_dirigenza_completate_valutatore"]++;
                        }
                        if ($valutazione["valutazione"]->data_firma_valutato !== null) {
                            $dati_cdr_report["n_valutazioni_dirigenza_completate_valutato"]++;
                        }
                    }
                    //conteggio compparto
                    else {
                        $dati_cdr_report["n_valutazioni_comparto"]++;
                        if ($valutazione["valutazione"]->data_firma_valutatore !== null) {                       
                        $dati_cdr_report["n_valutazioni_comparto_completate_valutatore"]++;
                        }
                        if ($valutazione["valutazione"]->data_firma_valutato !== null) {
                            $dati_cdr_report["n_valutazioni_comparto_completate_valutato"]++;
                        }
                    }                                                                        
                    unset($valutazioni_per_report[$valutazione_key]["cdr_afferenza"][$cdr_afferenza_key]);                    
                    if (count($valutazioni_per_report[$valutazione_key]["cdr_afferenza"]) == 0){
                        unset($valutazioni_per_report[$valutazione_key]);
                    }
                }
            }
        }   
    }
    $report[] = $dati_cdr_report;
}
//************************************************************
//variabili per la visualizzazione del completamento aziendale
$n_valutazioni_tot = 0;
$n_completate_tot = 0;
$n_valutazioni_dirigenza = 0;
$n_completate_dirigenza = 0;
$n_valutazioni_comparto = 0;
$n_completate_comparto = 0;
//conteggi totali aziendali
foreach ($valutazioni_periodo as $valutazione) {
    //conteggio complessivo      
    $n_valutazioni_tot++;    
    if ($valutazione["valutazione"]->data_firma_valutato !== null) {        
        $n_completate_tot++;
    }
    //conteggio dirigenza
    if ($valutazione["valutazione"]->categoria->dirigenza == true) {        
        $n_valutazioni_dirigenza++;        
        if ($valutazione["valutazione"]->data_firma_valutato !== null) {            
            $n_completate_dirigenza++;
        }
    }
    //conteggio compparto
    else {        
        $n_valutazioni_comparto++;        
        if ($valutazione["valutazione"]->data_firma_valutato !== null) {            
            $n_completate_comparto++;
        }
    }                                                                            
}

if (count($report) > 0) {
    //ordinamento degli obiettivi aziendali
    function completamentoValutazioniCdrCmp ($report1, $report2) {
        if ($report1["n_valutazioni"]>0) {
            $report1_completamento_tot = $report1["n_valutazioni_completate_valutato"]/$report1["n_valutazioni"];
            $report1_completamento_dirigenza = $report1["n_valutazioni_dirigenza_completate_valutato"]/$report1["n_valutazioni_dirigenza"];
            $report1_completamento_comparto = $report1["n_valutazioni_comparto_completate_valutato"]/$report1["n_valutazioni_comparto"];        
        }
        else {
            $report1_completamento_tot = 0;
            $report1_completamento_comparto = 0;
            $report1_completamento_dirigenza = 0;
        }

        if ($report2["n_valutazioni"]>0) {
            $report2_completamento_tot = $report2["n_valutazioni_completate_valutato"]/$report2["n_valutazioni"];
            $report2_completamento_dirigenza = $report2["n_valutazioni_dirigenza_completate_valutato"]/$report2["n_valutazioni_dirigenza"];
            $report2_completamento_comparto = $report2["n_valutazioni_comparto_completate_valutato"]/$report2["n_valutazioni_comparto"];        
        }
        else {
            $report2_completamento_tot = 0;
            $report2_completamento_comparto = 0;
            $report2_completamento_dirigenza = 0;
        }
        if ($report1_completamento_tot == $report2_completamento_tot) {
            if ($report1_completamento_comparto == $report2_completamento_comparto) {
                if ($report1_completamento_dirigenza == $report2_completamento_dirigenza) {
                    if ($report1["n_valutazioni"] == $report2["n_valutazioni"]) {
                        if (strcmp($report1["cdr"]->descrizione, $report2["cdr"]->descrizione) > 0){
                            return 1;
                        }
                    }
                    else if ($report1["n_valutazioni"] > $report1["n_valutazioni"]) {
                        return 1;
                    }
                }
                else if ($report1_completamento_dirigenza > $report2_completamento_dirigenza) {
                    return 1;
                }                    
            }
            else if ($report1_completamento_comparto > $report2_completamento_comparto) {
                return 1;
            }        
        }         
        else if ($report1_completamento_tot > $report2_completamento_tot){        
            return 1;
        }
    }			
    usort($report, "completamentoValutazioniCdrCmp");

    // visualizzazione dei dati nel template
    //variabili per la visualizzazione del completamento cdr
    $label_cdr = false;
    $value_dirigenza_valutatore = false;
    $label_dirigenza_valutatore = false;
    $value_dirigenza_valutato = false;
    $label_dirigenza_valutato = false;
    $value_comparto_valutatore = false;
    $label_comparto_valutatore = false;
    $value_comparto_valutato = false;
    $label_comparto_valutato = false;
    $value_tot_valutatore = false;
    $label_tot_valutatore = false;
    $value_tot_valutato = false;
    $label_tot_valutato = false;
    
    foreach ($report as $record) {
        //descrizione cdr
        if ($label_cdr !== false)			
            $label_cdr .= ", ";
        $descrizione_cdr = $record["cdr"]->descrizione;
        $label_cdr .= "'" . substr (addslashes($descrizione_cdr), 0, VALUTAZIONI_LABEL_GRAFICO_MAX_LEN) ."<br>"
                            .substr(addslashes($descrizione_cdr), VALUTAZIONI_LABEL_GRAFICO_MAX_LEN, VALUTAZIONI_LABEL_GRAFICO_MAX_LEN) . "'"; 

        //valorizzazione del grafico
        //calcoli
        $perc_totale_valutatore = 0;
        $perc_totale_valutato = 0;
        $perc_dirigenza_valutatore = 0;
        $perc_dirigenza_valutato = 0;
        $perc_comparto_valutatore = 0;
        $perc_comparto_valutato = 0;
        if ($record["n_valutazioni"] > 0) {
            $perc_totale_valutatore = $record["n_valutazioni_completate_valutatore"]/$record["n_valutazioni"]*100;
            $perc_totale_valutato = $record["n_valutazioni_completate_valutato"]/$record["n_valutazioni"]*100;
            if ($record["n_valutazioni_dirigenza"]) {            
                $perc_dirigenza_valutatore = $record["n_valutazioni_dirigenza_completate_valutatore"]/$record["n_valutazioni_dirigenza"]*100;
                $perc_dirigenza_valutato = $record["n_valutazioni_dirigenza_completate_valutato"]/$record["n_valutazioni_dirigenza"]*100;
            }
            else {
                $perc_dirigenza_valutatore = $perc_dirigenza_valutato = 100;
            }
            if ($record["n_valutazioni_comparto"]) {                                
                $perc_comparto_valutatore = $record["n_valutazioni_comparto_completate_valutatore"]/$record["n_valutazioni_comparto"]*100;
                $perc_comparto_valutato = $record["n_valutazioni_comparto_completate_valutato"]/$record["n_valutazioni_comparto"]*100;
            }   
            else {
                $perc_comparto_valutatore = $perc_comparto_valutato = 100;
            }
        }    
        else {
            $perc_totale_valutatore = $perc_totale_valutato = 100;
        }
        
        //accodamento dei valori totale
        if ($value_tot_valutatore !== false){			
            $value_tot_valutatore .= ", ";	
            $label_tot_valutatore .= ", ";
        }
        if ($value_tot_valutato !== false){
            $value_tot_valutato .= ", ";	
            $label_tot_valutato .= ", ";
        }
        $value_tot_valutatore .= number_format($perc_totale_valutatore);
        $label_tot_valutatore .= "'".$record["n_valutazioni_completate_valutatore"]."/".$record["n_valutazioni"]." ".number_format($perc_totale_valutatore)."%'";
        $value_tot_valutato .= number_format($perc_totale_valutato);
        $label_tot_valutato .= "'".$record["n_valutazioni_completate_valutato"]."/".$record["n_valutazioni"]." ".number_format($perc_totale_valutato)."%'";

        //accodamento dei valori dirigenza    
        if ($value_dirigenza_valutatore !== false){			
            $value_dirigenza_valutatore .= ", ";	
            $label_dirigenza_valutatore .= ", ";
        }
        if ($value_dirigenza_valutato !== false){
            $value_dirigenza_valutato .= ", ";	
            $label_dirigenza_valutato .= ", ";
        }
        $value_dirigenza_valutatore .= number_format($perc_dirigenza_valutatore);
        $label_dirigenza_valutatore .= "'".$record["n_valutazioni_dirigenza_completate_valutatore"]."/".$record["n_valutazioni_dirigenza"]." ".number_format($perc_dirigenza_valutatore)."%'";
        $value_dirigenza_valutato .= number_format($perc_dirigenza_valutato);
        $label_dirigenza_valutato .= "'".$record["n_valutazioni_dirigenza_completate_valutato"]."/".$record["n_valutazioni_dirigenza"]." ".number_format($perc_dirigenza_valutato)."%'";

        //accodamento dei valori comparto
        if ($value_comparto_valutatore !== false){			
            $value_comparto_valutatore .= ", ";	
            $label_comparto_valutatore .= ", ";
        }
        if ($value_comparto_valutato !== false){
            $value_comparto_valutato .= ", ";	
            $label_comparto_valutato .= ", ";
        }
        $value_comparto_valutatore .= number_format($perc_comparto_valutatore);
        $label_comparto_valutatore .= "'".$record["n_valutazioni_comparto_completate_valutatore"]."/".$record["n_valutazioni_comparto"]." ".number_format($perc_comparto_valutatore)."%'";
        $value_comparto_valutato .= number_format($perc_comparto_valutato);
        $label_comparto_valutato .= "'".$record["n_valutazioni_comparto_completate_valutato"]."/".$record["n_valutazioni_comparto"]." ".number_format($perc_comparto_valutato)."%'";

    }

    //valorizzazione delle variabili nel template
    //altezza del grafico aree (200 per legenda e titolo + 50 per area)
    $tpl->set_var("grafico_completamento_cdr_height", 200+(80*count($report)));
    $tpl->set_var("cdr_label", $label_cdr);

    $tpl->set_var("perc_dirigenza_valutatore", $value_dirigenza_valutatore);
    $tpl->set_var("point_label_dirigenza_valutatore", $label_dirigenza_valutatore);
    $tpl->set_var("perc_dirigenza_valutato", $value_dirigenza_valutato);
    $tpl->set_var("point_label_dirigenza_valutato", $label_dirigenza_valutato);

    $tpl->set_var("perc_comparto_valutatore", $value_comparto_valutatore);
    $tpl->set_var("point_label_comparto_valutatore", $label_comparto_valutatore);
    $tpl->set_var("perc_comparto_valutato", $value_comparto_valutato);
    $tpl->set_var("point_label_comparto_valutato", $label_comparto_valutato);        

    $tpl->set_var("perc_tot_valutatore", $value_tot_valutatore); 
    $tpl->set_var("point_label_tot_valutatore", $label_tot_valutatore);
    $tpl->set_var("perc_tot_valutato", $value_tot_valutato);    
    $tpl->set_var("point_label_tot_valutato", $label_tot_valutato);

    //******************************
    //REPORT completamento aziendale totale
    $tpl->set_var("tipo_report", "totale");
    $tpl->set_var("n_completate", number_format($n_completate_tot, 0, ",", "."));
    $tpl->set_var("n_valutazioni", number_format($n_valutazioni_tot, 0, ",", "."));
    $perc_completate = $n_completate_tot/$n_valutazioni_tot*100;    
    $tpl->set_var("perc_completamento", number_format($perc_completate));    
    $tpl->parse("SectCompletamentoValutazioni", true);
    //REPORT completamento aziendale comparto
    $tpl->set_var("tipo_report", "comparto");
    $tpl->set_var("n_completate", number_format($n_completate_comparto, 0, ",", "."));
    $tpl->set_var("n_valutazioni", number_format($n_valutazioni_comparto, 0, ",", "."));
    $perc_completate = $n_completate_comparto/$n_valutazioni_comparto*100;
    $tpl->set_var("perc_completamento", number_format($perc_completate));    
    $tpl->parse("SectCompletamentoValutazioni", true);
    //REPORT completamento aziendale dirigenza
    $tpl->set_var("tipo_report", "dirigenza");
    $tpl->set_var("n_completate", number_format($n_completate_dirigenza, 0, ",", "."));
    $tpl->set_var("n_valutazioni", number_format($n_valutazioni_dirigenza, 0, ",", "."));
    $perc_completate = $n_completate_dirigenza/$n_valutazioni_dirigenza*100;
    $tpl->set_var("perc_completamento", number_format($perc_completate));    
    $tpl->parse("SectCompletamentoValutazioni", true);    
    
    $tpl->parse("SectSituazioneAziendaleValutazioni", true);
}
else {
    $tpl->parse("SectNessunaValutazione", true);
}
//***********************
//Adding contents to page
die($tpl->rpparse("main", true));