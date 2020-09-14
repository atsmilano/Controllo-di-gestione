<?php
/**
 * data validation
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * data validation
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator
{
	static protected $events = null;

	public function __construct()
	{
		ffErrorHandler::raise("Cannot istantiate " . __CLASS__ . " directly, use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}

	public function __clone()
	{
		ffErrorHandler::raise("Cannot clone " . __CLASS__ . ", use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}

	static public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null)
	{
		self::initEvents();
		self::$events->addEvent($event_name, $func_name, $priority, $index, $break_when, $break_value);
	}

	static public function doEvent($event_name, $event_params = array())
	{
		self::initEvents();
		return self::$events->doEvent($event_name, $event_params);
	}

	static private function initEvents()
	{
		if (self::$events === null)
			self::$events = new ffEvents();
	}

	/**
	 * This method istantiate a ff_something instance based on many params
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $site_path
	 * @param string $page_path
	 * @param string $theme
	 * @param mixed $variant
	 * @return ffValidator_base
	 */
	public static function getInstance($name)
	{
		$ret = null;

		$res = self::doEvent("on_getInstance", array($name));
		$last_res = end($res);


		if (is_null($last_res))
		{
			$base_path = dirname(__FILE__) . "/validators";
			$class_name = "ffValidator_" . $name;

			require_once $base_path . "/" . $name . "." . FF_PHP_EXT;
			eval("\$ret = " . $class_name . "::getInstance();");
		}
		else
		{
			$ret = $last_res;
		}

		$res = self::doEvent("on_getInstance", array($name, $ret));
		return $ret;
	}
}

/**
 * data validation
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffValidator_base extends ffCommon
{
	public $error;
	
	protected function __construct()
	{
		$this->get_defaults("ffValidator");
		$this->get_defaults();
	}

	protected function __clone()
	{
	}

	public function getType()
	{
		return substr(get_class($this), strpos(get_class($this), "_") + 1);
	}

	abstract public function checkValue(ffData $value, $label, $options);
	
	protected function get_error($type, $label)
	{
		$tmp = str_replace("[LABEL]", $label, $this->error[$type]);
		if (strpos($tmp, "_") === 0)
			$tmp = ffTemplate::_get_word_by_code($tmp);
		
		return $tmp;
	}
}
