<?php
$cm->oPage->tplAddJs("jquery.min.js", "jquery.min.js", FF_THEME_DIR . "/library/jqplot/1.0.9");
$cm->oPage->tplAddJs("jquery.jqplot.min.js", "jquery.jqplot.min.js", FF_THEME_DIR . "/library/jqplot/1.0.9");

$cm->oPage->tplAddJs("jqplot.meterGaugeRenderer.js", "jqplot.meterGaugeRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.barRenderer.js", "jqplot.barRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");

$cm->oPage->tplAddJs("jqplot.canvasTextRenderer.js", "jqplot.canvasTextRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.categoryAxisRenderer.js", "jqplot.categoryAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.dateAxisRenderer.js", "jqplot.dateAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.pointLabels.js", "jqplot.pointLabels.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");

$cm->oPage->tplAddCss("jquery.jqplot.css", array("file" => "jquery.jqplot.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));
$cm->oPage->tplAddCss("jquery.jqplot.min.css", array("file" => "jquery.jqplot.min.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));

$user = LoggedUser::getInstance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

$date = $cm->oPage->globals["data_riferimento"]["value"];

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("bersaglio.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);

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

//Preparazione dei dati
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("id_periodo", $periodo->id);

//******************************************************************************
//costruzione dei filtri sul bersaglio
//variabile per verifica che sia stato selezionato almeno un filtro valido
$no_selection = true;
//***
//Cdr
$filter_cdr = null;
if (isset($_REQUEST["filter_cdr"])) {
    $cdr_selezionato = $_REQUEST["filter_cdr"];
}
else {
    $cdr_selezionato = null;
}
$cdr_obiettivi_aziendali = AnagraficaCdrObiettivi::getCdrObiettiviAziendali($anno);
function raggCdrCmp ($cdr1, $cdr2) {
    if ($cdr1->id_tipo_cdr == $cdr2->id_tipo_cdr) {
        if (strcmp($cdr1->descrizione, $cdr2->descrizione) > 0){
            return 1;
        } 
    }
    else {
        $cdr1_tipo_cdr = new TipoCdr($cdr1->id_tipo_cdr);
        $cdr2_tipo_cdr = new TipoCdr($cdr2->id_tipo_cdr);
        if (strcmp($cdr1_tipo_cdr->descrizione, $cdr2_tipo_cdr->descrizione) > 0) {
            return 1;
        }
    }               
}		
usort($cdr_obiettivi_aziendali, "raggCdrCmp");
$tpl->set_var("filter_cdr_codice", 0);
$tpl->set_var("filter_cdr_descrizione", "Tutti i CDR");
$tpl->parse("SectOptionCdr", true);
foreach ($cdr_obiettivi_aziendali as $cdr_obiettivo_anno) {
    $tpl->set_var("filter_cdr_codice", $cdr_obiettivo_anno->codice);
	$tpl->set_var("filter_cdr_descrizione", $cdr_obiettivo_anno->codice." - ".$cdr_obiettivo_anno->descrizione);
    
    if ($cdr_obiettivo_anno->codice == $cdr_selezionato) {
        $tpl->set_var("filter_cdr_selected", "selected='selected'");
        $filter_cdr = $cdr_obiettivo_anno->codice;
        $no_selection = false;
    }
    else {
        $tpl->set_var("filter_cdr_selected", "");
    }
    $tpl->parse("SectOptionCdr", true);
}

//*******
//Origine
$filter_origine = null;
if (isset($_REQUEST["filter_origine"])) {
    $origine_selezionata = $_REQUEST["filter_origine"];
}
else {
    $origine_selezionata = null;
}
$tpl->set_var("origine_id", 0);
$tpl->set_var("origine_descrizione", "Tutte le origini");
$tpl->parse("SectOptionOrigini", true);
foreach (ObiettiviOrigine::getAttiviAnno($anno) as $origine_anno) {
    $tpl->set_var("origine_id", $origine_anno->id);
	$tpl->set_var("origine_descrizione", $origine_anno->descrizione);
    
    if ($origine_anno->id == $origine_selezionata) {
        $tpl->set_var("origine_selected", "selected='selected'");
        $filter_origine = $origine_anno;
        $no_selection = false;
    }
    else {
        $tpl->set_var("origine_selected", "");
    }
    $tpl->parse("SectOptionOrigini", true);
}

//**************
//Area obiettivo
$filter_area_obiettivo = null;
if (isset($_REQUEST["filter_area_obiettivo"])) {
    $area_obiettivo_selezionata = $_REQUEST["filter_area_obiettivo"];
}
else {
    $area_obiettivo_selezionata = null;
}
$tpl->set_var("area_obiettivo_id", 0);
$tpl->set_var("area_obiettivo_descrizione", "Tutte le aree");
$tpl->parse("SectOptionAreeObiettivo", true);
foreach (ObiettiviArea::getAttiviAnno($anno) as $area_obiettivo_anno) {
    $tpl->set_var("area_obiettivo_id", $area_obiettivo_anno->id);
	$tpl->set_var("area_obiettivo_descrizione", $area_obiettivo_anno->descrizione);
    
    if ($area_obiettivo_anno->id == $area_obiettivo_selezionata) {
        $tpl->set_var("area_obiettivo_selected", "selected='selected'");
        $filter_area_obiettivo = $area_obiettivo_anno;
        $no_selection = false;
    }
    else {
        $tpl->set_var("area_obiettivo_selected", "");
    }
    $tpl->parse("SectOptionAreeObiettivo", true);
}

//**************
//Area risultato
$filter_area_risultato = null;
if (isset($_REQUEST["filter_area_risultato"])) {
    $area_risultato_selezionata = $_REQUEST["filter_area_risultato"];
}
else {
    $area_risultato_selezionata = null;
}
$tpl->set_var("area_risultato_id", 0);
$tpl->set_var("area_risultato_descrizione", "Tutte le aree");
$tpl->parse("SectOptionAreeRisultato", true);
foreach (ObiettiviAreaRisultato::getAttiviAnno($anno) as $area_risultato_anno) {
    $tpl->set_var("area_risultato_id", $area_risultato_anno->id);
	$tpl->set_var("area_risultato_descrizione", $area_risultato_anno->descrizione);
    
    if ($area_risultato_anno->id == $area_risultato_selezionata) {
        $tpl->set_var("area_risultato_selected", "selected='selected'");
        $filter_area_risultato = $area_risultato_anno;
        $no_selection = false;
    }
    else {
        $tpl->set_var("area_risultato_selected", "");
    }
    $tpl->parse("SectOptionAreeRisultato", true);
}

//**************
//Tipo obiettivo
$filter_tipo_obiettivo = null;
if (isset($_REQUEST["filter_tipo_obiettivo"])) {
    $tipo_obiettivo_selezionato = $_REQUEST["filter_tipo_obiettivo"];
}
else {
    $tipo_obiettivo_selezionato = null;
}
$tpl->set_var("tipo_obiettivo_id", 0);
$tpl->set_var("tipo_obiettivo_descrizione", "Tutti i tipi");
$tpl->parse("SectOptionTipiObiettivo", true);
foreach (ObiettiviTipo::getAttiviAnno($anno) as $tipo_obiettivo_anno) {
    $tpl->set_var("tipo_obiettivo_id", $tipo_obiettivo_anno->id);
	$tpl->set_var("tipo_obiettivo_descrizione", $tipo_obiettivo_anno->descrizione);
    
    if ($tipo_obiettivo_anno->id == $tipo_obiettivo_selezionato) {
        $tpl->set_var("tipo_obiettivo_selected", "selected='selected'");
        $filter_tipo_obiettivo = $tipo_obiettivo_anno;
        $no_selection = false;
    }
    else {
        $tpl->set_var("tipo_obiettivo_selected", "");
    }
    $tpl->parse("SectOptionTipiObiettivo", true);
}

//*****
//%Ragg		
$filter_fascia = null;
$fasce_perc_raggiungimento = array(
                            array("id"=>1, "descrizione"=>"0%", "min_val"=>0, "max_val"=>0),
                            array("id"=>2, "descrizione"=>"1%-49%", "min_val"=>1, "max_val"=>49),
                            array("id"=>3, "descrizione"=>"50%-79%", "min_val"=>50, "max_val"=>79),
                            array("id"=>4, "descrizione"=>"80%-89%", "min_val"=>80, "max_val"=>89),
                            array("id"=>5, "descrizione"=>"90%-100%", "min_val"=>90, "max_val"=>100),
                            );
if (isset($_REQUEST["filter_fasce"])) {
    $fascia_selezionata = $_REQUEST["filter_fasce"];
}
else {
    $fascia_selezionata = null;
}
$tpl->set_var("fascia_id", 0);
$tpl->set_var("fascia_descrizione", "Tutte le fasce");
$tpl->parse("SectOptionFascia", true);
foreach ($fasce_perc_raggiungimento as $fascia) {
    $tpl->set_var("fascia_id", $fascia["id"]);
	$tpl->set_var("fascia_descrizione", $fascia["descrizione"]);
    
    if ($fascia["id"] == $fascia_selezionata) {
        $tpl->set_var("fascia_selected", "selected='selected'");
        $filter_fascia = $fascia;
        $no_selection = false;
    }
    else {
        $tpl->set_var("fascia_selected", "");
    }
    $tpl->parse("SectOptionFascia", true);
}

//**********************
//Raggiungibile al 31/12
if ($periodo->hide_raggiungibile != 1) {
    if (isset($_REQUEST["filter_non_raggiungibile"]) && $_REQUEST["filter_non_raggiungibile"] == "true") {
        $tpl->set_var("filter_non_raggiungibile_checked", "checked='checked'");	
        $no_selection = false;
        $filter_non_raggiungibile = true;
    }
    else {
        $filter_non_raggiungibile = false;
    }    
    $tpl->parse("SectRaggiungibileSelect", true);
}
//******************************************************************************

if ($no_selection == true) {
    $tpl->parse("NoSelection", true);
}
else {
    /*Visualizzazione grafico*/
    /*----------------------------------------------------------------------------*/
    /*parte dello stile viene gestito dinamicamente in questa sezione per gestire variazioni di dimensioni in maniera parametrizzata*/
    //creazione del bersaglio e delle valutazioni
    //viene impostato il diametro dell'immagine bersaglio, e l'offset per centrare il bersaglio  
    define("TARGET_DIAMETER", 450);
    define("SHOT_DIAMETER", TARGET_DIAMETER/40);
    
    $tpl->set_var("target_diameter", TARGET_DIAMETER);
    $tpl->set_var("shot_diameter", SHOT_DIAMETER);
    //l'offset viene calcolato per immagini delle valutazioni grandi 16 px!!!
    $offset = ((TARGET_DIAMETER/2)-(SHOT_DIAMETER/2));
    
    $rendicontazioni_anno = ObiettiviRendicontazione::getAll(array("ID_periodo_rendicontazione" => $periodo->id));            
    function rendicontazioniCmp ($rend1, $rend2) {
        if ($rend1->perc_nucleo < $rend2->perc_nucleo) {
            return 1;
        }             
    }		
    usort($rendicontazioni_anno, "rendicontazioniCmp");
    
    //estrazione degli obiettivi del filtro impostati per gestione rendicontazioni su obiettivi di coreferenza
    if ($filter_cdr !== null) {
        $anagrafica_filter_cdr = AnagraficaCdrObiettivi::factoryFromCodice($filter_cdr, $date);
        $obiettivi_visibili_cdr_filter = $anagrafica_filter_cdr->getObiettiviCdrAnno($anno);
    }
    
    foreach($rendicontazioni_anno AS $rendicontazione) {        
        $obiettivo_cdr = new ObiettiviObiettivoCdr($rendicontazione->id_obiettivo_cdr);        
        if ($obiettivo_cdr->data_eliminazione == null && $obiettivo_cdr->isObiettivoCdrAziendale()) {   
            $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
            if ($obiettivo->data_eliminazione == null) {
                $trasversale_desc = "";
                //**********************************
                //visualizzazione colpi su bersaglio
                //verifica sulla visibilità della rendicontazione in base ai filtri
                $show_obiettivo = true;
                //filtri
                //per il filtro sul cdr vengono considerati anche gli obiettivi trasversali
                if ($filter_cdr !== null){                    
                    //se la rendicontazione è stata inserita per un cdr diverso da quello selezionato viene verificato che quello 
                    //impostato nel filtro sia di coreferenza e nel caso verificata lacoreferenza per la visualizzazione della rendicontazione
                    if( $filter_cdr !== $obiettivo_cdr->codice_cdr) {
                        $found = false;
                        foreach ($obiettivi_visibili_cdr_filter as $obiettivo_cdr_filter) {
                            //se l'obiettivo è di coreferenza vie erecuperato l'obiettivo_aziendale e visualizzata la rendicontazione nel caso sia relativa a quello
                            if ($obiettivo_cdr_filter->isCoreferenza()) {   
                                $obiettivo_cdr_coreferenza = $obiettivo_cdr_filter->getObiettivoCdrAziendale();
                                if ($obiettivo_cdr_coreferenza->id == $obiettivo_cdr->id) {                                    
                                    $rendicontazione_cdr_selezionato = ObiettiviRendicontazione::factoryFromObiettivoCdrPeriodo($obiettivo_cdr_filter, $periodo);
                                    if ($rendicontazione_cdr_selezionato !== null) {
                                       $rendicontazione = $rendicontazione_cdr_selezionato;
                                       $rendicontazione->perc_nucleo = $rendicontazione->perc_raggiungimento;
                                       $rendicontazione->raggiungibile = true;
                                    } 
                                    $trasversale_desc = " (trasversale al cdr selezionato)";
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        if ($found == false) {
                            $show_obiettivo = false;
                        }                                                                        
                    }                    
                }  
                if ($show_obiettivo == true && $filter_origine !== null && $filter_origine->id !== $obiettivo->id_origine) {
                    $show_obiettivo = false;
                }
                if ($show_obiettivo == true && $filter_area_obiettivo !== null && $filter_area_obiettivo->id !== $obiettivo->id_area) {
                    $show_obiettivo = false;
                }
                if ($show_obiettivo == true && $filter_area_risultato !== null && $filter_area_risultato->id !== $obiettivo->id_area_risultato) {
                    $show_obiettivo = false;
                }
                if ($show_obiettivo == true && $filter_tipo_obiettivo !== null && $filter_tipo_obiettivo->id !== $obiettivo->id_tipo) {
                    $show_obiettivo = false;
                }
                if ($show_obiettivo == true 
                    && $filter_fascia !== null 
                    && !(
                            $filter_fascia["min_val"] <= $rendicontazione->perc_nucleo &&
                            $rendicontazione->perc_nucleo <= $filter_fascia["max_val"]
                        )
                    ) {
                    $show_obiettivo = false;
                }           
                if ($show_obiettivo == true && ($periodo->hide_raggiungibile != true && $filter_non_raggiungibile == true && $rendicontazione->raggiungibile == true)) {                    
                    $show_obiettivo = false;
                }
                //visualizzazione colpi ed elenco obiettivi
                if ($show_obiettivo == true) {                                      
                    //visualizzazione del colpo sul bersaglio
                    //calcolo della posizione della valutazione
                    //calcolo della distanza dal centro
                    $radius = (TARGET_DIAMETER/2) - ((TARGET_DIAMETER/2) * $rendicontazione->perc_nucleo * 0.01);		
                    //distribuisco in maniera casuale
                    $degrees = rand(0,360);
                    //calcolo della posizione sugli assi tramite trigonometria
                    $pos_x = $radius*cos($degrees);
                    $pos_y = $radius*sin($degrees);
                    //visualizzazione nel target
                    if($rendicontazione->perc_raggiungimento == $rendicontazione->perc_nucleo)
                        $shot_img = "circle.png";				
                    else
                        $shot_img = "shot.png";		

                    $tpl->set_var("id", (string)($rendicontazione->id));
                    $tpl->set_var("codice_obiettivo", $obiettivo->codice);
                    $tpl->set_var("codice_cdr", $obiettivo_cdr->codice_cdr.$trasversale_desc);
                    $tpl->set_var("shot_image", $shot_img);

                    $tpl->set_var("y_offset", ($offset + $pos_y));
                    $tpl->set_var("x_offset", ($offset + $pos_x));

                    $tpl->parse("SectShot", true);
                    
                    //*************************
                    //visualizzazione in elenco
                    $tpl->set_var("cdr_codice", $obiettivo_cdr->codice_cdr.$trasversale_desc);
                    $tpl->set_var("descrizione_obiettivo", CoreHelper::cutText($obiettivo->titolo, 48));
                    $tpl->set_var("raggiungimento_referente", (int)$rendicontazione->perc_raggiungimento);
                    $tpl->set_var("raggiungimento_nucleo", (int)$rendicontazione->perc_nucleo);
                    
                    $tpl->parse("SectObiettivo", true);
                }
            }
        }
    }  
    $tpl->parse("Bersaglio", true);
}
die($tpl->rpparse("main", true));