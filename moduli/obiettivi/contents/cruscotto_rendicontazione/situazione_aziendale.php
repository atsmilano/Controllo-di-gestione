<?php
$user = LoggedUser::getInstance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("situazione_aziendale_obiettivi.html", "main");

$periodo = null;
if(isset($_REQUEST["periodo_select"])){    
    try {
        $periodo = new ObiettiviPeriodoRendicontazione($_REQUEST["periodo_select"]);
    } catch (Exception $ex) {

    }
}
if ($periodo == null){
    die("Errore nella selezione del periodo");
}

//utilizzo di query
//Preparazione dei dati
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

$db = ffDb_Sql::factory();
//estrazione di tutti gli obiettivi raggruppati per area
//variabili aziendali
$n_obiettivi_azienda = 0;
$n_azioni_azienda = 0;
$n_rendicontazioni_azienda = 0;
$n_obiettivi_non_raggiungibili_azienda = 0;
$cdr_obiettivi_aziendali = array();
$raggiungimento_nucleo_azienda = 0;

//predisposizione dei dati
foreach (AnagraficaCdrObiettivi::getCdrObiettiviAziendali($anno) as $anagrafica_cdr) {                  
    //variabili cdr
    $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
    $cdr_rendicontazione = array(       
                                "codice_cdr" => $anagrafica_cdr->codice,
                                "descrizione_cdr" => $tipo_cdr->abbreviazione . " " . $anagrafica_cdr->descrizione, 
                                "n_obiettivi_cdr" => 0,
                                "n_obiettivi_trasversali" => 0,
                                "n_azioni_cdr" => 0,
                                "n_rendicontazioni_cdr" => 0,
                                "raggiungimento_cdr" => 0,
                                "raggiungimento_nucleo" => 0,
                                );
    foreach ($anagrafica_cdr->getObiettiviCdrAnno($anno) as $obiettivo_cdr) {
        //viene estratta la rendicontazione dell'obiettivo_cdr padre per considerare anche le coreferenze
        $obiettivo_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale();
        $rendicontazione = null;
        if ($obiettivo_cdr->isCoreferenza()) {
            $rendicontazione = $obiettivo_cdr->getRendicontazionePeriodo($periodo);
            if ($rendicontazione !== null) {
                $rendicontazione->perc_nucleo = $rendicontazione->perc_raggiungimento;
                $rendicontazione->raggiungibile = true;
                $n_obiettivi_azienda++;
            }
        }
        if ($rendicontazione == null) {            
            $rendicontazione = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo);
        }
                
        $cdr_rendicontazione["n_obiettivi_cdr"]++; 
        
        if (!$obiettivo_cdr->isCoreferenza()) {            
            $n_obiettivi_azienda++;
        }
        else {
            $cdr_rendicontazione["n_obiettivi_trasversali"]++;
        }
        
        //conteggio delle azioni definite
        if (strlen($obiettivo_cdr_aziendale->azioni) > 0) {
            $cdr_rendicontazione["n_azioni_cdr"]++;
            if (!$obiettivo_cdr->isCoreferenza()) {
                $n_azioni_azienda++;
            }
        }   
            
        if ($rendicontazione !== null) {    
            if (!$obiettivo_cdr->isCoreferenza()) {
                $n_rendicontazioni_azienda++;
                $raggiungimento_nucleo_azienda += $rendicontazione->perc_nucleo;
                if ($rendicontazione->raggiungibile != true){
                    $n_obiettivi_non_raggiungibili_azienda++;
                }
            }
            $cdr_rendicontazione["n_rendicontazioni_cdr"]++;            
            $cdr_rendicontazione["raggiungimento_cdr"] += $rendicontazione->perc_raggiungimento;
            $cdr_rendicontazione["raggiungimento_nucleo"] += $rendicontazione->perc_nucleo;                        
        }
    }
    $cdr_obiettivi_aziendali[] = $cdr_rendicontazione; 
}

//ordinamento degli obiettivi aziendali
function raggCdrCmp ($cdr1, $cdr2) {
    if ($cdr1["n_rendicontazioni_cdr"]>0) {
        $cdr1_ragg_medio_nucleo = $cdr1["raggiungimento_nucleo"]/$cdr1["n_rendicontazioni_cdr"];
        $cdr1_ragg_medio_cdr = $cdr1["raggiungimento_cdr"]/$cdr1["n_rendicontazioni_cdr"];
    }
    else {
        $cdr1_ragg_medio_nucleo = 0;
        $cdr1_ragg_medio_cdr = 0;
    }
    if ($cdr2["n_rendicontazioni_cdr"]>0) {
        $cdr2_ragg_medio_nucleo = $cdr2["raggiungimento_nucleo"]/$cdr2["n_rendicontazioni_cdr"];
        $cdr2_ragg_medio_cdr = $cdr2["raggiungimento_cdr"]/$cdr2["n_rendicontazioni_cdr"];
    }
    else {
        $cdr2_ragg_medio_nucleo = 0;
        $cdr2_ragg_medio_cdr = 0;
    }    
    
    if ($cdr1_ragg_medio_nucleo == $cdr2_ragg_medio_nucleo) {
        if ($cdr1_ragg_medio_cdr == $cdr2_ragg_medio_cdr) {
            if ($cdr1["n_obiettivi_cdr"] == $cdr2["n_obiettivi_cdr"]) {
                if (strcmp($cdr1["descrizione_cdr"], $cdr2["descrizione_cdr"]) > 0){
                    return 1;
                }
            }
            else if ($cdr1["n_obiettivi_cdr"] > $cdr2["n_obiettivi_cdr"]) {
                return 1;
            }
        }
        else if ($cdr1_ragg_medio_cdr > $cdr2_ragg_medio_cdr) {
            return 1;
        }        
    }         
    else if ($cdr1_ragg_medio_nucleo > $cdr2_ragg_medio_nucleo){
        return 1;
    }
}			
usort($cdr_obiettivi_aziendali, "raggCdrCmp");
    
//visualizzazione dei dati nel template
//variabili per la visualizzazione
$label_cdr = false;
$value_raggiungimento_cdr = false;
$label_raggiungimento_cdr = false;
$value_raggiungimento_nucleo = false;
$label_raggiungimento_nucleo = false;
$label_completamento_rendicontazione = false;
$value_completamento_rendicontazione = false;
foreach($cdr_obiettivi_aziendali as $cdr) {                
    //descrizione cdr
    if ($label_cdr !== false)			
        $label_cdr .= ", ";
    $label_cdr .= "'" . addslashes(substr ($cdr["descrizione_cdr"], 0, OBIETTIVI_LABEL_GRAFICO_MAX_LEN)) ."<br>"
                        .addslashes(substr($cdr["descrizione_cdr"], OBIETTIVI_LABEL_GRAFICO_MAX_LEN, OBIETTIVI_LABEL_GRAFICO_MAX_LEN)) . "'"; 

    //raggiungimento cdr                
    //raggiungimento nucleo
    if ($cdr["n_rendicontazioni_cdr"] > 0) {
        $raggiungimento_medio_cdr = $cdr["raggiungimento_cdr"]/$cdr["n_rendicontazioni_cdr"];
        $raggiungimento_medio_nucleo = $cdr["raggiungimento_nucleo"]/$cdr["n_rendicontazioni_cdr"];
    }
    else {
        $raggiungimento_medio_cdr = 0;
        $raggiungimento_medio_nucleo = 0;
    }
    if ($value_raggiungimento_cdr !== false){			
        $value_raggiungimento_cdr .= ", ";	
        $label_raggiungimento_cdr .= ", ";
    }
    $value_raggiungimento_cdr .= number_format($raggiungimento_medio_cdr);
    $label_raggiungimento_cdr .= "'".number_format($raggiungimento_medio_cdr)."%'";
    if ($value_raggiungimento_nucleo !== false){			
        $value_raggiungimento_nucleo .= ", ";	
        $label_raggiungimento_nucleo .= ", ";
    }
    $value_raggiungimento_nucleo .= number_format($raggiungimento_medio_nucleo);
    $label_raggiungimento_nucleo .= "'".number_format($raggiungimento_medio_nucleo)."%'";

    //numero di rendicontazioni
    if ($value_completamento_rendicontazione !== false)
        $value_completamento_rendicontazione .= ", ";	        
    $completamento_rendicontazione = number_format($cdr["n_rendicontazioni_cdr"] / $cdr["n_obiettivi_cdr"] * 100);   
    $value_completamento_rendicontazione .= number_format($completamento_rendicontazione);
    if ($label_completamento_rendicontazione !== false)
        $label_completamento_rendicontazione .= ", ";
    $label_completamento_rendicontazione .= "'" . $cdr["n_rendicontazioni_cdr"]."/".$cdr["n_obiettivi_cdr"] ."(".$cdr["n_obiettivi_trasversali"].")-".$completamento_rendicontazione . "%'";
}       

//valorizzazione delle variabili nel template
//altezza del grafico aree (200 per legenda e titolo + 50 per area)
$tpl->set_var("grafico_medio_aree_height", 200+(50*count($cdr_obiettivi_aziendali)));
$tpl->set_var("cdr_label", $label_cdr);
$tpl->set_var("perc_ragg_cdr", $value_raggiungimento_cdr);
$tpl->set_var("point_label_ragg_cdr", $label_raggiungimento_cdr);
$tpl->set_var("perc_ragg_nucleo", $value_raggiungimento_nucleo);
$tpl->set_var("point_label_ragg_nucleo", $label_raggiungimento_nucleo);        
$tpl->set_var("completamento_rendicontazione", $value_completamento_rendicontazione);    
$tpl->set_var("point_label_n_ob", $label_completamento_rendicontazione);
$label_completamento_rendicontazione = false;

//avanzamento azioni e rendicontazioni	
$tpl->set_var("n_obiettivi", $n_obiettivi_azienda);
$tpl->set_var("avanzamento_azioni", floor(number_format(($n_azioni_azienda/$n_obiettivi_azienda),2)*100));
$tpl->set_var("n_azioni_definite", $n_azioni_azienda);
$tpl->set_var("n_rendicontazioni", $n_rendicontazioni_azienda);
$tpl->set_var("avanzamento_rendicontazioni", floor(number_format(($n_rendicontazioni_azienda/$n_obiettivi_azienda),2)*100));

//media aziendale	
if ($n_rendicontazioni_azienda>0)
    $tpl->set_var("media_aziendale_cdr", number_format($raggiungimento_nucleo_azienda/$n_rendicontazioni_azienda, 2));
else
    $tpl->set_var("media_aziendale_cdr", 0);
if ($periodo->hide_raggiungibile != 1) {
    if ($n_obiettivi_non_raggiungibili_azienda > 0){	
        $tpl->set_var("n_non_raggiungibili", $n_obiettivi_non_raggiungibili_azienda . " obiettivi ritenuti non raggiungibili al 31/12 su un totale di " .
                        $n_rendicontazioni_azienda . " rendicontati nel periodo (" . floor(number_format(($n_obiettivi_non_raggiungibili_azienda/$n_rendicontazioni_azienda),2)*100) . "%)");	
    }
    else {
        $tpl->set_var("n_non_raggiungibili", "Tutti gli obiettivi rendicontati (" . $n_rendicontazioni_azienda . ") sono ritenuti raggiungibili al 31/12");		
    }
}
$tpl->parse("SectRendicontazioni", true);

//***********************
//Adding contents to page
die($tpl->rpparse("main", true));