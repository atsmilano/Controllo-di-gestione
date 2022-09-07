<?php

//******************************************************************************************************************************************
//inclusione di tutte le classi definite nella directory specifica
//******************************************************************************************************************************************
//viene caricata come prima classe la classe Entity per gestire le eventuali estensioni
require(FF_DISK_PATH . "/classes/Entity.php");
require(FF_DISK_PATH . "/classes/Singleton.php");
foreach (glob(FF_DISK_PATH . "/classes/*.php") as $filename) {
    if ($filename !== FF_DISK_PATH . "/classes/Entity.php" && $filename !== FF_DISK_PATH . "/classes/Singleton.php") {
        require($filename);
    }
}

//parametro per l'esclusione dei globals da escludere nella costruzione della query string dell'url nel metodo get_globals
//stringa con il nome dei parametri separati da virgola "parametro1,parametro2,parametro3"
define(GET_GLOBALS_EXCLUDE_LIST, "data_riferimento,cdr_visibili,modules");
//gestione moduli su disco
//ATTENZIONE: in caso di modifica dei parametri variare anche la regola nell'htaccess
define(MODULES_DIR, "moduli");
define(MODULES_CLASSES_DIR, "classes");
define(MODULES_CONFIG_FILE, "mod_config.xml");
define(MODULES_COMMON_FILE, "common.php");
define(MODULES_DISK_PATH, FF_DISK_PATH . DIRECTORY_SEPARATOR . MODULES_DIR);
define(MODULES_SITE_PATH, FF_SITE_PATH . "/area_riservata");
define(MODULES_THEME_DIR, "theme");
define(MODULES_CSS_DIR, MODULES_THEME_DIR . DIRECTORY_SEPARATOR . "css");
define(MODULES_CSS_PATH, MODULES_THEME_DIR . "/css");
define(MODULES_ICONHIDE, "ICONHIDE");

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
    $cm->oPage->register_globals("modules", Modulo::getActiveModulesFromDisk($anno_selezionato), false);
    
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
