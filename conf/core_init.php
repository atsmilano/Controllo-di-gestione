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
define(MODULES_CRON_DIR, "cron");
define(MODULES_ICONHIDE, "ICONHIDE");