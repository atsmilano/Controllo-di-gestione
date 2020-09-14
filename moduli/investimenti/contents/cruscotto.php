<?php
$user = LoggedUser::Instance();

if (!$user->hasPrivilege("investimenti_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

CoreHelper::includeJqueryUi();
$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("cruscotto.html", "main");

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("ret_url", urlencode($_SERVER["REQUEST_URI"]));

//recupero globals e info cdr
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_richieste = array();
$date = $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d");        
//recupero del del cdc       
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);

/* ---------------------------------------------------------------------------- */
//generazione del filtro sui cdr
if (isset($_REQUEST["cdr_select"])) {
    $id_cdr_selezionato = $_REQUEST["cdr_select"];
} else {
    $id_cdr_selezionato = 0;
}
//generazione del filtro su uoc competente
if (isset($_REQUEST["uoc_competente_select"])) {
    $id_uoc_competente_selezionata = $_REQUEST["uoc_competente_select"];
} else {
    $id_uoc_competente_selezionata = 0;
}
$cdr_richieste = CdrInvestimenti::getCdrConRichiesteAnno($anno);
if (count($cdr_richieste)){
    foreach ($cdr_richieste as $cdr_richiesta) {
        if ($cdr_richiesta->id == $id_cdr_selezionato) {
            $tpl->set_var("cdr_selected", "selected='selected'");
        } else
            $tpl->set_var("cdr_selected", "");
        $tipo_cdr_richiesta = new TipoCdr ($cdr_richiesta->id_tipo_cdr);
        $tpl->set_var("cdr_id", $cdr_richiesta->id);
        $tpl->set_var("cdr_descrizione", $cdr_richiesta->codice . " - " . $tipo_cdr_richiesta->abbreviazione . " " . $cdr_richiesta->descrizione);

        $tpl->parse("SectOptionCdr", true);
    }
    $tpl->parse("SectSelezioneCdr", true);
    /* ---------------------------------------------------------------------------- */
    
    //selezione uoc competente
    foreach (InvestimentiCategoriaUocCompetenteAnno::getUocCompetentiAnno($anno) as $uoc_competente) {
        if ($uoc_competente->id == $id_uoc_competente_selezionata) {
            $uoc_competente_selezionata = $uoc_competente;
            $tpl->set_var("uoc_competente_selected", "selected='selected'");
        } else
            $tpl->set_var("uoc_competente_selected", "");
        $tipo_uoc_competente = new TipoCdr ($uoc_competente->id_tipo_cdr);
        $tpl->set_var("uoc_competente_id", $uoc_competente->id);
        $tpl->set_var("uoc_competente_descrizione", $uoc_competente->codice . " - " . $tipo_uoc_competente->abbreviazione . " " . $uoc_competente->descrizione);
        $tpl->parse("SectOptionUocCompetente", true);
    }
    $tpl->parse("SectSelezioneUocCompetente", true);

    //******************************************************************************
    //recupero dati

    //inizializzazione variabili per visualizzazione
    //stati investimento, viene aggiunta una chiave per il conteggio degli investimenti in quello stato
    $stati_investimento = InvestimentiInvestimento::$stati_investimento;
    foreach($stati_investimento as $key => $value){
        $stati_investimento[$key]["conteggio"] = 0;
    }

    //costruzione degli array per i report degli importi
    $fonti_finanziamento = InvestimentiFonteFinanziamento::getAll(array("ID_anno_budget" => $anno->id));
    $conti_categoria_registro_cespiti = InvestimentiCategoriaRegistroCespiti::getAll(array("ID_anno_budget" => $anno->id));
    //istruttoria
    $report_uoc_competente = array();
    //verifica copertura
    $report_uoc_bilancio = array();
    //piano investimenti
    $report_piano_investimenti = array();
    foreach ($conti_categoria_registro_cespiti as $conto_categoria_registro_cespiti){
        $report = array(
                        "conto_categoria_registro_cespiti" => $conto_categoria_registro_cespiti,
                        "importo_fonte_finanziamento" => array(),                    
                        );
        foreach ($fonti_finanziamento as $fonte_finanziamento){
            $report["importo_fonte_finanziamento"][] = array(
                                                        "fonte_finanziamento" => $fonte_finanziamento,
                                                        "importo" => 0,
                                                        );        
        }
        $report_uoc_competente[] = $report_uoc_bilancio[] = $report_piano_investimenti[] = $report;
    }   

    //ogni investimento popola uno dei report
    $filters = array("ID_anno_budget" => $anno->id);
               
    foreach (InvestimentiInvestimento::getAll($filters) as $investimento) {     
        $id_stato_avanzamento = null;
        //gestione filtri
        $view_investimento = false;     
        if ($id_cdr_selezionato != 0) {
            $cdc_richiesta = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
            $cdr_richiesta = new Cdr ($cdc_richiesta->id_cdr);
            if ($cdr_richiesta->id == $id_cdr_selezionato) {
                $view_investimento = true;
            }
        }
        else {
            $view_investimento = true;
        }
        if ($view_investimento == true) {
            if ($id_uoc_competente_selezionata != 0) {
                $view_investimento = false;
                $id_stato_avanzamento = $investimento->getIdStatoAvanzamento();
                //il filtro sulla uoc competente viene applicato solo negli stati in cui è già stata definita
                if ($id_stato_avanzamento >= 4 && $id_stato_avanzamento <= 10) {
                    //non dovrebbero mai esserci investimenti in questo stato senza uoc definita ma per sicurezza in caso di anomalia viene utilizzato il try
                    try{
                        $categoria_uoc_competente = new InvestimentiCategoriaUocCompetenteAnno($investimento->istruttoria_id_categoria_uoc_competente_anno);           
                    } catch (Exception $ex) {

                    }
                    if ($categoria_uoc_competente->codice_cdr == $uoc_competente_selezionata->codice) {
                        $view_investimento = true;
                    }
                }
            }
            else {
                $view_investimento = true;
            }
        }
        //predisposizione report
        if ($view_investimento == true) {   
            if ($id_stato_avanzamento == null) {
                $id_stato_avanzamento = $investimento->getIdStatoAvanzamento(); 
            }
            $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column(InvestimentiInvestimento::$stati_investimento, 'ID'));        
            $stati_investimento[$index_stato_avanzamento]["conteggio"]++;    
            //vengono sommati gli importi per i record (con esito ok)   
            //viene considerato, per le richieste non ancora in piano d'investimento, l'importo previsto da uoc competente            
            //i valori degli importi sono incrementali per i report
            if ($id_stato_avanzamento <= 10) {               
                //in base allo stato d'avanzamento vengono considerate fonti di finanziamento e cespiti diferente (proposte da uoc competente / confermate da uoc bilancio)
                //monitoraggio chiuso, report piano investimenti                
                if ($id_stato_avanzamento == 10) {    
                    $importo = $investimento->istruttoria_costo_presunto;
                    $id_fonte_finanziamento = $investimento->istruttoria_id_fonte_finanziamento_proposta;
                    $id_conto_categoria_registro_cespiti = $investimento->istruttoria_id_categoria_registro_cespiti_proposta;                  
                    $report_uoc_competente = aggiornaReport($report_uoc_competente, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    $id_fonte_finanziamento = $investimento->verifica_copertura_id_fonte_finanziamento;
                    $id_conto_categoria_registro_cespiti = $investimento->verifica_copertura_id_registro_cespiti;
                    $report_uoc_bilancio = aggiornaReport($report_uoc_bilancio, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    $importo = $investimento->monitoraggio_importo_definitivo;                    
                    $id_fonte_finanziamento = $investimento->verifica_copertura_id_fonte_finanziamento;
                    $id_conto_categoria_registro_cespiti = $investimento->verifica_copertura_id_registro_cespiti;
                    $report_piano_investimenti = aggiornaReport($report_piano_investimenti, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                }   
                //istruttoria o precedente
                else if ($id_stato_avanzamento < 6) {                    
                    $importo = $investimento->istruttoria_costo_presunto;
                    $id_fonte_finanziamento = $investimento->istruttoria_id_fonte_finanziamento_proposta;
                    $id_conto_categoria_registro_cespiti = $investimento->istruttoria_id_categoria_registro_cespiti_proposta;
                    $report_uoc_competente = aggiornaReport($report_uoc_competente, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    $report_uoc_bilancio = aggiornaReport($report_uoc_bilancio, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    $report_piano_investimenti = aggiornaReport($report_piano_investimenti, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                }                  
                //verifica copertura
                else{
                    $importo = $investimento->istruttoria_costo_presunto;
                    $id_fonte_finanziamento = $investimento->istruttoria_id_fonte_finanziamento_proposta;
                    $id_conto_categoria_registro_cespiti = $investimento->istruttoria_id_categoria_registro_cespiti_proposta;                  
                    $report_uoc_competente = aggiornaReport($report_uoc_competente, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    $id_fonte_finanziamento = $investimento->verifica_copertura_id_fonte_finanziamento;
                    $id_conto_categoria_registro_cespiti = $investimento->verifica_copertura_id_registro_cespiti;
                    $report_uoc_bilancio = aggiornaReport($report_uoc_bilancio, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    if ($id_stato_avanzamento !== 7) {
                        $report_piano_investimenti = aggiornaReport($report_piano_investimenti, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo);
                    }
                }                                                      
            }         
        }
    }        

    //******************************************************************************
    //visualizzazione dei report tramite template

    //stati avanzamento
    foreach($stati_investimento as $stato_investimento) {     
        if ($stato_investimento["esito"] !== "ko") {
            $tpl->set_var("stato_avanzamento", $stato_investimento["descrizione"]);
            $tpl->set_var("n_richieste", $stato_investimento["conteggio"]);
            $tpl->parse("SectStatoAvanzamento", true);
        }
    }
  
    //array dei report
    $report_importi = array(
                    0 => array (
                                "titolo_report" => "Istruttoria",
                                "report" => $report_uoc_competente,
                                "report_div_id" => "istruttoria",
                                ),
                    1 => array (
                                "titolo_report" => "Verifica Copertura",
                                "report" => $report_uoc_bilancio,
                                "report_div_id" => "verifica_copertura",
                                ),
                    2 => array (
                                "titolo_report" => "Piano Investimenti",
                                "report" => $report_piano_investimenti,
                                "report_div_id" => "piano_investimenti",
                                ),
                    );
    
    //visualizzazioni report importi
    foreach ($report_importi as $report) {
        //array per totali e budget fonti finanziamento
        $budget_fonti_finanziamento = array();
        foreach ($fonti_finanziamento as $fonte_finanziamento){
            $budget_fonti_finanziamento[] = array(
                                                    "budget_anno" => $fonte_finanziamento->budget_anno,
                                                    "totale_fonte_finanziamento" => 0
                                                    );
        }
        
        $intestazione_visualizzata = false;
        $tpl->set_var("titolo_report", $report["titolo_report"]);
        $tpl->set_var("report_div_id", $report["report_div_id"]);              
        foreach ($report["report"] as $record_report) {    
            $tpl->set_var("conto_categoria_registro_cespiti", $record_report["conto_categoria_registro_cespiti"]->descrizione);
            $totale_conto_categoria_registro_cespiti = 0;
            $i=0;
            foreach ($record_report["importo_fonte_finanziamento"] as $importo_fonte_finanziamento) {
                if ($intestazione_visualizzata == false) {
                    $tpl->set_var("n_fonti_finanziamento", count($record_report["importo_fonte_finanziamento"]));
                    $tpl->set_var("fonte_finanziamento", $importo_fonte_finanziamento["fonte_finanziamento"]->descrizione);
                    $tpl->parse("SectFonteFinanziamentoIntestazione", true);           
                }
                $tpl->set_var("importo", number_format($importo_fonte_finanziamento["importo"], 2, ",", "."));
                $budget_fonti_finanziamento[$i++]["totale_fonte_finanziamento"] += $importo_fonte_finanziamento["importo"];        
                $totale_conto_categoria_registro_cespiti += $importo_fonte_finanziamento["importo"];
                $tpl->parse("SectImporti", true);
            }
            if ($totale_conto_categoria_registro_cespiti>0){
                $tpl->set_var("totale_conto_categoria_registro_cespiti", number_format($totale_conto_categoria_registro_cespiti, 2, ",", "."));
                $tpl->parse("SectContoCategoriaCespiti", true);
            }
            $tpl->set_var("SectImporti", false);
            $intestazione_visualizzata = true;    
        }
        //visualizzazione totali
        $totale_complessivo = 0;
        $totale_budget = 0;        
        foreach ($budget_fonti_finanziamento as $budget_fonte_finanziamento) { 
            $tpl->set_var("totale_fonte_finanziamento", number_format($budget_fonte_finanziamento["totale_fonte_finanziamento"], 2, ",", "."));
            $totale_complessivo += $budget_fonte_finanziamento["totale_fonte_finanziamento"];
            $tpl->parse("SectTotale", true);

            $tpl->set_var("budget_fonte_finanziamento", number_format($budget_fonte_finanziamento["budget_anno"], 2, ",", "."));
            $totale_budget += $budget_fonte_finanziamento["budget_anno"];
            $tpl->parse("SectBudget", true);

            if($budget_fonte_finanziamento["budget_anno"] != 0) {
                $erosione = 100/ $budget_fonte_finanziamento["budget_anno"] * $budget_fonte_finanziamento["totale_fonte_finanziamento"];
                $tpl->set_var("erosione_fonte_finanziamento", number_format($erosione, 2, ",", ".")."%");  
            }
            else {
                $tpl->set_var("erosione_fonte_finanziamento", "ND");  
            }                      
            $tpl->parse("SectErosione", true);
        }
        $tpl->set_var("totale_complessivo", number_format($totale_complessivo, 2, ",", "."));
        $tpl->set_var("totale_budget", number_format($totale_budget, 2, ",", "."));    
        if($totale_budget != 0) {
            $erosione = 100 / $totale_budget * $totale_complessivo;
        }
        else {
            $erosione = 0;
        }            
        $tpl->set_var("totale_erosione", number_format($erosione, 2, ",", "."));

        $tpl->parse("SectReport", true);
        $tpl->set_var("SectContoCategoriaCespiti", false);
        $tpl->set_var("SectFonteFinanziamentoIntestazione", false);    
        $tpl->set_var("SectTotale", false);
        $tpl->set_var("SectBudget", false);
        $tpl->set_var("SectErosione", false);
    }
    $tpl->parse("SectDati", false);
}
else {
    $tpl->parse("SectNoCdr", false);
}

$cm->oPage->addContent($tpl);

function aggiornaReport(array $report, $id_fonte_finanziamento, $id_conto_categoria_registro_cespiti, $importo){
    $found = false;
    foreach($report as $i => $record){
        foreach ($record["importo_fonte_finanziamento"] as $j => $fonte_finanziamento){
            if ($record["conto_categoria_registro_cespiti"]->id == $id_conto_categoria_registro_cespiti
                && $fonte_finanziamento["fonte_finanziamento"]->id == $id_fonte_finanziamento){
                $report[$i]["importo_fonte_finanziamento"][$j]["importo"] += $importo;                  
                $found = true;
                break;
            }            
        }
        if ($found == true){
            break;
        }
    }         
    return $report;
}