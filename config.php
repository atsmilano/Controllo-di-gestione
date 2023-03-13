<?php
// *****************
//  GLOBAL SETTINGS
// *****************
//ogni enviroment definito avrà un file di configurazione corrispondente in enviroments
$enviroments = array(
                    "FF_ENV_DEVELOPMENT" => "localhost",
                    "FF_ENV_TEST" => "test.domain.it",
                    );

if(php_sapi_name() == "cli") {
    //in caso di esecuzione da riga di comando viene richiesto il nome dell'enviroment come parametro
    define("FF_ENV", getopt(null, ["env:"])["env"]);
    $_SERVER['SERVER_NAME'] = $enviroments[FF_ENV];
}
else {
    define("FF_ENV", array_search($_SERVER["HTTP_HOST"], $enviroments));    
}
require_once(__DIR__.DIRECTORY_SEPARATOR."conf".DIRECTORY_SEPARATOR."enviroments".DIRECTORY_SEPARATOR.FF_ENV.".php");

//parametri per la gestione dei loghi di default
//se i parametri non sono definiti si utilizzano i loghi di default
if (!defined("LOGO_LOGIN_FILENAME"))define("LOGO_LOGIN_FILENAME", "login.png");
if (!defined("LOGO_RESTRICTED_FILENAME"))define("LOGO_RESTRICTED_FILENAME", "restricted.png");
if (!defined("LOGO_NOBRAND_FILENAME"))define("LOGO_NOBRAND_FILENAME", "nobrand.png");
if (!defined("LOGO_QUALITA_FILENAME"))define("LOGO_QUALITA_FILENAME", "qualita.png");
if (!defined("LOGO_QUALITA_STAMPA_FILENAME"))define("LOGO_QUALITA_STAMPA_FILENAME", "logo_qualita_stampa.png");

//gestione visualizzazione profilo utente
if (!defined("USER_VIEW_PROFILE"))define("USER_VIEW_PROFILE", false);

// activecomboex
$plgCfg_ActiveComboEX_UseOwnSession = false;	/* set to true to bypass session check.
													NB: ActiveComboEX require a session. If you disable session
														check, ActiveComboEX do a session_start() by itself. */

/* DEFAULT FORMS SETTINGS
	this is a default array used by Forms classes to set user defined global default settings.
	the format is:
		$ff_global_setting[class_name][parameter_name] = value;
 */

// ****************
//  ERROR HANDLING
// ****************

// used to bypass certain ini settings
ini_set("display_errors", true);

/* used to define errors handled by PHP. 
   NB:
   This will be bit-masquered with FF_ERRORS_HANDLED by the framework.
 */
error_reporting((E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED) | E_STRICT);

/* used to define maximum recursion when digging into arrays/objects. NULL mean no limit. */
define("FF_ERRORS_MAXRECURSION", NULL);

// ***************
//  FILE HANDLING
// ***************

// disable file umasking
@umask(0);

// **********************
//  INTERNATIONALIZATION
// **********************

// default data type conversion
define("FF_LOCALE", "ITA");
define("FF_SYSTEM_LOCALE", "ISO9075"); /* this is the locale setting used to convert system data, like url parameters.
											 this not affect the user directly. */
											 
date_default_timezone_set("Europe/Rome");

define("FF_DEFAULT_CHARSET", "UTF-8");

// **********************
//  FEATURES
// **********************

define("FF_ENABLE_MEM_TPL_CACHING", false); // Template Caching: SPIEGARE DI CHE SI TRATTA
define("FF_ENABLE_MEM_PAGE_CACHING", false); // Page Caching: SPIEGARE DI CHE SI TRATTA
define("FF_DB_INTERFACE", "mysqli");
define("FF_ORM_ENABLE", false);

//define("COMPOSER_PATH", "/vendor"); //enable if you use composer

//ALLEGATI
//recupero della dimensione massima degli allegati da php ini
$ini_max_file_size = ini_get('upload_max_filesize');
$value = substr($ini_max_file_size,0,strlen($ini_max_file_size)-1);
switch(strtolower($ini_max_file_size[strlen($ini_max_file_size)-1])) {     
    case 'g':
    case 'gb':
        $value *= 1024;   
    case 'm':
    case 'mb':
        $value *= 1024;        
    case 'k':
    case 'kb':
        $value *= 1024;
}

//Gestione allegati
define("MAX_CONTENT_LENGHT", $value); // Espressa in byte 10.485.760Byte = 10Mbyte
unset($value);
unset($ini_max_file_size);
//mimetype permessi dalla gestione degli allegati
//http://www.iana.org/assignments/media-types/media-types.xhtml
const ALLOWED_MIMETYPE = array(
    'application/vnd.openxmlformats-officedocument.wordprocessingml.documentmimetype', //docx
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', //docx
    'application/vnd.openxmlformats-officedocument.wordprocessingml.template', //dotx
    'application/vnd.ms-word.document.macroEnabled.12', //docm
    'application/vnd.ms-word.template.macroEnabled.12', //dotm
    'application/vnd.ms-excel', //xla|xls|xlt|xlw
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',//xlsx
    'application/vnd.openxmlformats-officedocument.spreadsheetml.template',//xltx
    'application/vnd.ms-excel.sheet.macroEnabled.12',
    'application/vnd.ms-excel.template.macroEnabled.12',
    'application/vnd.ms-excel.addin.macroEnabled.12',
    'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.openxmlformats-officedocument.presentationml.template',
    'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
    'application/vnd.ms-powerpoint.addin.macroEnabled.12',
    'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
    'application/vnd.ms-powerpoint.template.macroEnabled.12',
    'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
    'application/vnd.ms-access',
    'application/vnd.ms-excel',
    'application/msword',
    'application/pdf', //PDF
    'text/csv', //CSV
    'image/jpeg', //JPEG/JPG
    'image/png', //PNG
    'image/gif', //GIF
    'image/pjpeg', //PJPEG
    'image/tiff', //TIFF
    'image/svg+xml', //SVG (Vettoriale)
    'image/bmp', //Bitmap
    'image/webp', //Webp
    'application/vnd.oasis.opendocument.text' // ODT
);

//Parametro per gestione della versione mpdf
define ("CURRENT_USE_MPDF_VERSION", "6.1.0");