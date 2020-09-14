<?php
/**
 * framework main inclusion file
 * 
 * @package FormsFramework
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

// init framework..
//if (!defined("FF_MAIN_INIT"))
//{
if(version_compare(phpversion(), "5.4", "<"))
    die("Forms PHP Framework, Critical Error: PHP Version >= 5.4 required, " . phpversion() . " detected.");

// set default extension for php files
define("FF_PHP_EXT", "php");

// some preprocessing for CPU saving
define("__FF_DIR__", dirname(__DIR__));

if(!defined("__TOP_DIR__")) {
    define("__TOP_DIR__", (getenv("FF_TOP_DIR") && getenv("FF_TOP_DIR") != "1"
        ? getenv("FF_TOP_DIR")
        : __FF_DIR__
    ));
}

/*
define("__PRJ_DIR__", (strpos($_SERVER["REQUEST_URI"], "/domains/") === false
    ? __TOP_DIR__
    : $_SERVER["DOCUMENT_ROOT"] . preg_replace('/'. preg_quote($_SERVER["PATH_INFO"], '/') . '$/', '', $_SERVER["REQUEST_URI"])
));*/
define("__PRJ_DIR__", (getenv("FF_PROJECT_DIR") && getenv("FF_PROJECT_DIR") != "1"
    ? getenv("FF_PROJECT_DIR")
    //ADDED-ATS sostituito $_SERVER["REDIRECT_FF_PROJECT_DIR"] con isset($_SERVER["REDIRECT_FF_PROJECT_DIR"] alla riga successiva
    : (isset($_SERVER["REDIRECT_FF_PROJECT_DIR"])
        ? $_SERVER["REDIRECT_FF_PROJECT_DIR"]
        : __TOP_DIR__
    )
));

// add ff'dirs to include path
set_include_path(
    __TOP_DIR__ . PATH_SEPARATOR .
    __TOP_DIR__ . "/library" . PATH_SEPARATOR .
    get_include_path()
);

// load config...

// ..base (all others depends on this one)
if (@is_file(__PRJ_DIR__ . "/config." . FF_PHP_EXT))
    require_once __PRJ_DIR__ . "/config." . FF_PHP_EXT;
else if (@is_file(__FF_DIR__ . "/ff/config." . FF_PHP_EXT))
    require __FF_DIR__ . "/ff/config." . FF_PHP_EXT;
else
    die("FORMS FRAMEWORK: config." . FF_PHP_EXT . " file not found. Place it under sources or root directory.");

// ..check config
if(!defined("FF_SYSTEM_LOCALE"))                    define("FF_SYSTEM_LOCALE", "ISO9075"); /* Default Locale */
if(!defined("FF_DEFAULT_CHARSET"))                  define("FF_DEFAULT_CHARSET", "UTF-8");  /* Charset Default */

// manage charsets
if (FF_DEFAULT_CHARSET == "UTF-8")
{
    mb_regex_encoding("UTF-8");
    mb_internal_encoding("UTF-8");
}

if (!defined("FF_UPDIR"))                           define("FF_UPDIR", "/uploads");
if (!defined("FF_SITE_UPDIR"))                      define("FF_SITE_UPDIR", FF_SITE_PATH . FF_UPDIR);
if (!defined("FF_DISK_UPDIR"))                      define("FF_DISK_UPDIR", FF_DISK_PATH . FF_UPDIR);

// Theme Management
// define base theme(s) location
if (!defined("FF_THEME_DIR"))			              define ("FF_THEME_DIR", 	"/themes");
if (!defined("FF_THEME_DISK_PATH")) 	              define ("FF_THEME_DISK_PATH", 	__TOP_DIR__ . FF_THEME_DIR);
if (!defined("FF_THEME_SITE_PATH")) 	              define ("FF_THEME_SITE_PATH", 	FF_SITE_PATH . FF_THEME_DIR);

if (!defined("FF_MAIN_THEME"))                      define("FF_MAIN_THEME", "responsive");

// Framework Classes
require_once (__DIR__ . "/autoload." . FF_PHP_EXT);

//rewrite pathinfo
ffRewritePathInfo();

//define("FF_MAIN_INIT", true);
if (defined("FF_ONLY_INIT"))
    return;
//}

if (!defined("FF_URLREWRITE_REMOVEHYPENS"))         define("FF_URLREWRITE_REMOVEHYPENS", true);
if (!defined("FF_ORM_ENABLE"))                      define("FF_ORM_ENABLE", true);

if (!defined("FF_PREFIX"))                          define("FF_PREFIX", "ff_");
if (!defined("FF_SUPPORT_PREFIX"))                  define("FF_SUPPORT_PREFIX", "support_");

// Common Functions
require(__FF_DIR__ . "/ff/common." . FF_PHP_EXT);

// Load commons..
// ...base
if (@is_file(__PRJ_DIR__ . "/common." . FF_PHP_EXT))
	require __PRJ_DIR__ . "/common." . FF_PHP_EXT;
//elseif (is_file(ffCommon_dirname($_SERVER['SCRIPT_FILENAME']) . "/common." . FF_PHP_EXT))
//	require (ffCommon_dirname($_SERVER['SCRIPT_FILENAME']) . "/common." . FF_PHP_EXT);

// ..theme
//ffCommon_theme_init(); // load commons

$ff = ffGlobals::getInstance("ff");
$ff->events = new ffEvents();
$ff->showfiles_events = new ffEvents();
