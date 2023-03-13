<?php
use MappaturaCompetenze\Periodo;
use MappaturaCompetenze\MappaturaPeriodo;
use MappaturaCompetenze\Profilo;
use MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo;
use MappaturaCompetenze\CompetenzaTrasversale;
use MappaturaCompetenze\CompetenzaSpecifica;
use MappaturaCompetenze\ValutatoPeriodo;

$modulo = core\Modulo::getCurrentModule();

$mappature_periodo = array();
if (isset($_REQUEST["periodo_select"]) && isset($_REQUEST["valutato_select"])) {    
    try {
        $periodo = new Periodo($_REQUEST["periodo_select"]);
        $personale = Personale::factoryFromMatricola($_REQUEST["valutato_select"]);
        $mappature_periodo = MappaturaPeriodo::getAll(array("ID_periodo"=>$periodo->id
                                            , "matricola_personale"=>$personale->matricola));          
    } catch (Exception $ex) {
        die($ex->getMessage());
    }   
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}
if (!count($mappature_periodo)) {
    ffErrorHandler::raise("Nessuna mappatura per i parametri selezionati.");
}

//privilegi sulla mappatura
$user = LoggedUser::getInstance();

$show_mappatura = false;
$data_visualizzazione_valida = false;
$edit_note_data = false;

//ruoli nella mappatura
$ruoli = array (
    "amministratore" => false,
    "valutato" => true,
    "valutatore_responsabile" => false,
    "valutatore_collaboratore" => false,
    "valutatore_pari" => false,
);

if ($user->hasPrivilege("competenze_admin")) {
    $ruoli["amministratore"] = true;
    $show_mappatura = true;
    $data_visualizzazione_valida = true;
    $edit_note_data = true;
}
//se l'utente non è amministratore viene verificato che sia valutatore o valutato per garantire visibilità sulla mappatura
else {
    $edit_note_data = false;
    foreach ($mappature_periodo as $mappatura) {
        if ($user->matricola_utente_selezionato == $mappatura->matricola_personale) { 
            $ruoli["valutato"] = true;
            $show_mappatura = true;           
        }
        
        if ($user->matricola_utente_selezionato == $mappatura->matricola_valutatore){
            if ($mappatura->id_tipo_mappatura == 1) {
                $ruoli["valutatore_responsabile"] = true;
                $show_mappatura = true;
            }
        }     
        if ($user->matricola_utente_selezionato == $mappatura->matricola_valutatore){
            if ($mappatura->id_tipo_mappatura == 3) {
                $ruoli["valutatore_collaboratore"] = true;
                $show_mappatura = true;
            }
        } 
        if ($user->matricola_utente_selezionato == $mappatura->matricola_valutatore){
            if ($mappatura->id_tipo_mappatura == 4) {
                $ruoli["valutatore_pari"] = true;
                $show_mappatura = true;
            }
        }
    }
}
//se l'utente non è valutatore, valutato o admin verrà visualizzato errore
if ($show_mappatura == false) {
    ffErrorHandler::raise("L'utente non dispone dei privilegi per poter visualizzare la mappatura.");
}

$valutato_periodo = ValutatoPeriodo::getByFields(array("ID_periodo"=>$periodo->id , "matricola_valutato"=>$personale->matricola));
//visualizzazione delle informazioni (note e valutazioni differenti dalle proprie)
//permessa solo se data visualizzazione precedente a quella odierna
if ($data_visualizzazione_valida !== true
        && isset ($valutato_periodo) 
        &&  (($valutato_periodo->data_abilitazione_visualizzazione !== null)
            && (strtotime(date("Y-m-d"))-strtotime($valutato_periodo->data_abilitazione_visualizzazione)>=0)                
            )            
        ) {
    $data_visualizzazione_valida = true;
}         

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");    
$tpl->load_file("report_individuale.html", "main");

$tpl->set_var("module_img_path", $modulo->module_theme_full_path . "/images");
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

$tpl->set_var("id_periodo_select", $periodo->id);

//il profilo è unico per ogni valutato, verrà recuperato una sola volta
$profilo = null;
$chart_data[0] = array("id_tipo_mappatura"=>0, "label"=>"Valori attesi", "data"=>array());
//intestazione elenco valori e recupero del profilo
foreach ($mappature_periodo as $mappatura) {           
    if ($mappatura->visualizzabileUtente($ruoli, $data_visualizzazione_valida) == true) {
        if (!isset($chart_data[$mappatura->id_tipo_mappatura])) {
            //viene valorizzato l'array dei dati per il grafico
            $chart_data[$mappatura->id_tipo_mappatura] = array("id_tipo_mappatura"=>$mappatura->id_tipo_mappatura, "label"=>MappaturaPeriodo::getTipoMappaturaFromId($mappatura->id_tipo_mappatura)["descrizione"], "data"=>array());
                                
            $tpl->set_var("descrizione_tipologia", MappaturaPeriodo::getTipoMappaturaFromId($mappatura->id_tipo_mappatura)["descrizione"]);
            $tpl->parse("SectValoriIntestazione", true);
            $tpl->parse("SectTipologia", true);    
            //viene recuperato il profilo per la prima mappatura (sempre uguale per ogni mappatura)
            if ($profilo == null) {
                try {
                    $profilo = new Profilo($mappatura->id_profilo);
                } catch (Exception $ex) {
                    throw new Exception("Errore nell'individuazione del profilo");
                }
            }   
        }  
    }
}
$tpl->parse("SectTipologie", false);

//tabella con le competenze previste per il profilo
$valori = $profilo->getValoriAssegnabili();

//costruzione array unico competenze
$competenze = array();
$competenze_trasversali_profilo = $profilo->getCompetenzeTrasversaliProfilo();
foreach ($competenze_trasversali_profilo as $competenza_trasversale_profilo) {
    $competenza_trasversale_profilo->tipo_competenza = "trasversale";
    $competenza_trasversale_profilo->id_competenza = $competenza_trasversale_profilo->id_competenza_trasversale;
    $competenze[] = $competenza_trasversale_profilo;
}
$competenze_specifiche_profilo = $profilo->getCompetenzeSpecificheProfilo();
foreach ($competenze_specifiche_profilo as $competenza_specifica_profilo) {
    $competenza_specifica_profilo->tipo_competenza = "specifica";   
    $competenza_specifica_profilo->id_competenza = $competenza_specifica_profilo->id_competenza_specifica;
    $competenze[] = $competenza_specifica_profilo;
}

foreach ($competenze as $key => $competenza_profilo) {
    if ($competenza_profilo->tipo_competenza == "trasversale" ) {
        $competenza = new CompetenzaTrasversale($competenza_profilo->id_competenza_trasversale);  
        $competenza->id_tipo_competenza = 1;        
        $competenza->classe_tipo_competenza = "trasversale";
    }
    else  {        
        $competenza = new CompetenzaSpecifica($competenza_profilo->id_competenza_specifica); 
        $competenza->id_tipo_competenza = 2;
        $competenza->classe_tipo_competenza = "specifica";
    }      
    $competenza->id_competenza = $competenza_profilo->id_competenza;
    $competenza->id_valore_atteso = $competenza_profilo->id_valore_atteso;
    $competenze[$key] = $competenza;
}

//ordinamento competenze (trasversali e specifiche ordinate per descrizione)
usort($competenze, "CompetenzaCmp");
//metodo per l'ordinamento delle competenze
function CompetenzaCmp($a, $b) {
    if ($a->id_tipo_competenza == $b->id_tipo_competenza) {
        return strcmp($a->nome, $b->nome);
    }
    return $a->id_tipo_competenza - $b->id_tipo_competenza;
}

$n_mappature = array();
foreach ($competenze as $competenza) {
    $tpl->set_var("classe_tipo_competenza", $competenza->classe_tipo_competenza);
    $tpl->set_var("id_competenza", "competenza_".$competenza_tipo_competenza."_".$competenza->id);
    $tpl->set_var("nome_competenza", $competenza->nome);        
    $tpl->parse("SectLabelsCompetenze", true);
    //valori attesi   
    $tpl->set_var("valore_atteso", $valori[array_search($competenza->id_valore_atteso, array_column($valori, 'id'))]->valore);     
    
    //costruzione di un id univoco per tipo_competenza e competenza
    $id_competenza_tipo = $competenza->id_tipo_competenza."_".$competenza->id;
    $chart_data[0]["data"][$id_competenza_tipo] = $valori[array_search($competenza->id_valore_atteso, array_column($valori, 'id'))]->valore;
                            
    //valori mappatura
    foreach ($mappature_periodo as $mappatura) {    
        if ($mappatura->visualizzabileUtente($ruoli, $data_visualizzazione_valida) == true) {
            $filters = array(                       
                            "ID_mappatura_periodo" => $mappatura->id,
                            "ID_tipo_competenza" => $competenza->id_tipo_competenza,
                            "ID_competenza" => $competenza->id,            
                            );        
            $mappatura_competenza = ProfiloMappaturaCompetenzaPeriodo::getByFields($filters);        
            $valore_mappatura_visualizzato = 0;
            if ($mappatura_competenza !== null) {            
                $valore_mappatura_visualizzato = $valori[array_search($mappatura_competenza->id_valore, array_column($valori, 'id'))]->valore;            
            }
            if (!isset($chart_data[$mappatura->id_tipo_mappatura]["data"][$id_competenza_tipo])) {
                $chart_data[$mappatura->id_tipo_mappatura]["data"][$id_competenza_tipo] = $valore_mappatura_visualizzato;                                                                       
                $n_mappature[$mappatura->id_tipo_mappatura] = 1;
            }
            else {
                $chart_data[$mappatura->id_tipo_mappatura]["data"][$id_competenza_tipo] += $valore_mappatura_visualizzato;                                       
                $n_mappature[$mappatura->id_tipo_mappatura]++;
            }               
        }
    }
    //calcolo medie e visualizzazione valori in tabella
    foreach ($chart_data as $key=>$data) { 
        if ($key !== 0) {
            //calcolo medie
            $media = $data["data"][$id_competenza_tipo]/$n_mappature[$key];
            $chart_data[$key]["data"][$id_competenza_tipo] = $media;
            $tpl->set_var("valore_tipologia_competenza", $media);
            $tpl->parse("SectValoreTipologia", true);
        }
    }
    $tpl->parse("SectValoriTipologie", true);    
    $tpl->set_var("SectValoreTipologia", false);           
}
$tpl->parse("SectValori", false);
$tpl->set_var("SectValoriTipologie", false);

//creazione legenda valori
foreach ($profilo->getValoriAssegnabili() as $valore) {
    $tpl->set_var("valore", $valore->valore);
    $tpl->set_var("descrizione_valore", $valore->descrizione);
    $tpl->parse("SectVoceLegenda", true);
}
//i valori vengono recuperati in ordine crescente, l'ultimo sarà quindi quello più alto
$tpl->set_var("valore_massimo", $valore->valore);
$tpl->parse("SectLegenda", false);

//creazione del codice per la visualizzazione del grafico
foreach ($chart_data as $id_tipo_mappatura=>$mappatura) {
    //creazione legenda per tipo di mappatura   
    if ($mappatura["id_tipo_mappatura"] === 0) {
        $tpl->set_var("label_tipo_mappatura", "Valori attesi");
        $tpl->set_var("color", "0, 82, 204");
    }
    else {
        $tpl->set_var("label_tipo_mappatura", MappaturaPeriodo::getTipoMappaturaFromId($mappatura["id_tipo_mappatura"])["descrizione"]);
        $tpl->set_var("color",  MappaturaPeriodo::getTipoMappaturaFromId($mappatura["id_tipo_mappatura"])["chart_color"]);
    }        
    
    //popolamento del grafico con i dati per ogni mappatura
    //competenze trasversali
    foreach ($competenze as $competenza) {
        $id_competenza_tipo = $competenza->id_tipo_competenza."_".$competenza->id;
        $tpl->set_var("valore_tipologia_competenza", $chart_data[$id_tipo_mappatura]["data"][$id_competenza_tipo]);                    
        $tpl->parse("SectDataCompetenze", true);
    }
    $tpl->parse("SectDatasetTipologie", true);
    $tpl->set_var("SectDataCompetenze",false);
}

//aggiunta dei campi note e data abilitazione
$tpl->set_var("matricola_valutato", $personale->matricola);
$tpl->set_var("note_valutato", $valutato_periodo->note);

if ($edit_note_data) {    
    $tpl->set_var("data_abilitazione_visualizzazione", CoreHelper::formatUiDate($valutato_periodo->data_abilitazione_visualizzazione));
    if (!isset($report_pdf) || $report_pdf !== true) {
        $tpl->parse("SectNoteTextArea", true);
        $tpl->parse("SectAdminActions", true);
    }
    else {
        $tpl->parse("SectNoteP", true);
    }
    $tpl->parse("SectValutatoPeriodo", true);
    $tpl->parse("SectSectValutatoPeriodoScript", true);    
}
else {
    $tpl->set_var("disabled", "disabled");
    if ($data_visualizzazione_valida) {
        if (!isset($report_pdf) || $report_pdf !== true) {
            $tpl->parse("SectNoteTextArea", true);
        }
        else {
            $tpl->parse("SectNoteP", true);
        }
        $tpl->parse("SectValutatoPeriodo", true);
    }    
}

if (isset($report_pdf) && $report_pdf == true) {
    $html_report .= $tpl->rpparse("main", true);
}
else {
    $tpl->parse("SectChartCanvas", true);
    die($tpl->rpparse("main", true));
}