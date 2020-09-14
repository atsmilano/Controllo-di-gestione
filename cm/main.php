<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

if (isset($_REQUEST["__QUERY__"]))                  define("FF_URLPARAM_QUERY", $_REQUEST["__QUERY__"]);
if (isset($_REQUEST["__DEBUG__"]))                  define("FF_URLPARAM_DEBUG", $_REQUEST["__DEBUG__"]);
if (isset($_REQUEST["__CLEARCACHE__"]))             define("FF_URLPARAM_CLEARCACHE", $_REQUEST["__CLEARCACHE__"]);
if (isset($_REQUEST["__GENCACHE__"]))               define("FF_URLPARAM_GENCACHE", $_REQUEST["__GENCACHE__"]);
if (isset($_REQUEST["__NOCACHE__"]))                define("FF_URLPARAM_NOCACHE", $_REQUEST["__NOCACHE__"]);
if (isset($_REQUEST["__NOLAYOUT__"]))               define("FF_URLPARAM_NOLAYOUT", $_REQUEST["__NOLAYOUT__"]);
if (isset($_REQUEST["__SHOWCASCADELOADER__"]))      define("FF_URLPARAM_SHOWCASCADELOADER", $_REQUEST["__SHOWCASCADELOADER__"]);


if (!defined("CM_MAIN_INIT"))
{
	// DEBUG expressions
	if (isset($_REQUEST["__FORCE_XHR__"]))
		$_SERVER["HTTP_X_REQUESTED_WITH"] = "XMLHttpRequest";

	// load forms php framework
/*	if (defined("CM_ONLY_INIT") && !defined("FF_ONLY_INIT"))
		define("FF_ONLY_INIT" , true);
*/	require(__DIR__ . "/../ff/main.php");

	// load configs..

	// ..main
	require(__DIR__ . "/config." . FF_PHP_EXT);

	// ..check
	if (!is_dir(CM_MODULES_ROOT))
		ffErrorHandler::raise("CM: missing modules dir: " . CM_MODULES_ROOT, E_USER_ERROR, null, get_defined_vars());

	if (!is_dir(CM_CONTENT_ROOT))
		ffErrorHandler::raise("CM: missing content dir: " . CM_CONTENT_ROOT, E_USER_ERROR, null, get_defined_vars());

	// ..from conf
	if (@is_file(FF_DISK_PATH . "/conf/cm/config." . FF_PHP_EXT))
		require FF_DISK_PATH . "/conf/cm/config." . FF_PHP_EXT;
	require(__DIR__ . "/conf/config." . FF_PHP_EXT);

	// global config
	if (@is_file(FF_DISK_PATH . "/conf/config." . FF_PHP_EXT))
		require FF_DISK_PATH . "/conf/config." . FF_PHP_EXT;
	
	define("CM_MAIN_INIT", true);
	if (defined("CM_ONLY_INIT"))
		return;
}

// load classes
require_once (__DIR__ . "/autoload." . FF_PHP_EXT);

// load addons
require(__DIR__ . "/cm_cascadeloader." . FF_PHP_EXT);

// load commons..

// ..main
require(__DIR__ . "/common." . FF_PHP_EXT);

// ..from conf
if (@is_file(FF_DISK_PATH . "/conf/common." . FF_PHP_EXT))
	require FF_DISK_PATH . "/conf/common." . FF_PHP_EXT;

// run the Content Manager
$cm = cm::getInstance();
$ff->events->doEvent("cm::getInstance", array($cm));
$cm->process();

// done!