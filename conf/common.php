<?php
require_once('core_init.php');

//vengono ordinati i moduli in base all'ordine definito
function moduleCmp($mod1, $mod2)
{
    return ($mod1->ordine_caricamento > $mod2->ordine_caricamento);
}

//generazione rgole di routing
$cm = cm::getInstance();
$cm->addEvent("on_before_routing", "routingRulesGen");

function routingRulesGen()
{
    $cm = cm::getInstance();
    //******************************************************************************************************************************************
    //gestione dei campi di selezione per parametri globali
    //******************************************************************************************************************************************
    //utente selezionato	
    //viene recuperata la matricola e verificato che sia valida altrimenti passato null	    
    $dipendente = null;
    if (isset($_REQUEST["dipendente"])) {
        try {
            $dipendente = new Personale($_REQUEST["dipendente"]);
        } catch (Exception $ex) {
            
        }
    }
    $cm->oPage->register_globals("dipendente", $dipendente, false);

    //valorizzazione del campo anno budget
    //viene recuperato l'eventuale parametro definito per l'anno
    if (isset($_REQUEST["anno"]))
        $id_anno = $_REQUEST["anno"];
    else
        $id_anno = 0;

    //se il parametro di selezione dell'anno risulta valido viene utilizzato
    $anno_selezionato = null;
    try {
        $anno_budget = new AnnoBudget($id_anno);
        if ($anno_budget->attivo == 1) {
            $anno_selezionato = $anno_budget;
        }
    }
    
    //altrimenti viene selezionato l'anno predefinito
    catch (Exception $ex) {
        $anno_selezionato = AnnoBudget::getPredefinito();
        //altrimenti viene selezionato l'ultimo anno attivo se presente o l'ultimo anno attivo definito
        if ($anno_selezionato == null) {                            
            $ultimo_anno_attivo = AnnoBudget::ultimoAttivoInData();
            if ($ultimo_anno_attivo !== null) {
                $anno_selezionato = $ultimo_anno_attivo;
            }
        }
    }
    if ($anno_selezionato == null) {
        $anno_selezionato = AnnoBudget::ultimoDefinito();
        if ($anno_selezionato == null) {
            ffErrorHandler::raise("Errore nella definizione degli anni di budget: nessun anno attivo");
        }
    }

    $cm->oPage->register_globals("anno", $anno_selezionato, false);
    $cm->oPage->register_globals("modules", \core\Modulo::getActiveModulesFromDisk($anno_selezionato), false);
    
    //******************************************************************************************************************************************
    //******************************************************************************************************************************************
    //generazione dellle regole di routing per ogni modulo e valorizzazione delle costanti dall'enviroment
    //******************************************************************************************************************************************		
    foreach ($cm->oPage->globals["modules"]["value"] as $module) {
        //regola routing
        $cm->router->addXMLRule("
                                                                        <rule id='" . $module->site_path . "'>                                                                                
                                                                                <priority>NORMAL</priority>
                                                                                <source>/area_riservata" . $module->site_path . "(.*)</source>
                                                                                <destination>
                                                                                        <url>/moduli" . $module->site_path . "/contents/$1</url>
                                                                                </destination>
                                                                                <reverse>/area_riservata" . $module->site_path . "</reverse>
                                                                                <index>100</index>
                                                                                <accept_path_info />
                                                                        </rule>									
                                                        ");
        //costanti
        foreach ($module->getEnvConstants() as $const =>$value){
            define($const, $value);            
        }
    }
}
