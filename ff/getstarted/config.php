<?php
/**
 * framework config example file
 *
 * @package FormsFramework
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2010, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */

// *****************
//  GLOBAL SETTINGS
// *****************

define("FF_ENV_DEVELOPMENT", "localhost");
define("FF_ENV_STAGING", "");
define("FF_ENV_PRODUCTION", "");

switch (true)
{
	case ($_SERVER["HTTP_HOST"] == FF_ENV_DEVELOPMENT):
		// PATH SETTINGS
		define("FF_DISK_PATH", "/home/htdocs");
		define("FF_SITE_PATH", "");

		// DEFAULT DB CONNECTION FOR ffDb_Sql
		define("FF_DATABASE_HOST", "localhost");
		define("FF_DATABASE_NAME", "");
		define("FF_DATABASE_USER", "");
		define("FF_DATABASE_PASSWORD", "");
		
		define("FF_ENV", FF_ENV_DEVELOPMENT);
		break;
		
	case ($_SERVER["HTTP_HOST"] == FF_ENV_STAGING):
		// PATH SETTINGS
		/**#@+
		 * @ignore
		 */
		define("FF_DISK_PATH", "");
		define("FF_SITE_PATH", "");

		// DEFAULT DB CONNECTION 
		define("FF_DATABASE_HOST", "localhost");
		define("FF_DATABASE_NAME", "");
		define("FF_DATABASE_USER", "");
		define("FF_DATABASE_PASSWORD", "");

		define("FF_ENV", FF_ENV_STAGING);
		break;
		
	case (substr($_SERVER["HTTP_HOST"], (strlen(FF_ENV_PRODUCTION) * -1)) == FF_ENV_PRODUCTION):
		define("FF_DISK_PATH", $disk_path);
		define("FF_SITE_PATH", $site_path);

		// DEFAULT DB CONNECTION 
		define("FF_DATABASE_HOST", "localhost");
		define("FF_DATABASE_NAME", "");
		define("FF_DATABASE_USER", "");
		define("FF_DATABASE_PASSWORD", "");

		define("FF_ENV", FF_ENV_PRODUCTION);
		break;
}

// unique application id
define("APPID", "691C9185-C34B-494Or4Z3-9450-FE374g3r");

// session name
session_name("PHPSESSFF");



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
define("FF_ORM_ENABLE", true);

//define("COMPOSER_PATH", "/vendor"); //enable if you use composer