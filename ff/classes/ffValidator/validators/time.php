<?php
/**
 * validator: time
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: time
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_time extends ffValidator_base
{
	static $_singleton = null;

	static function getInstance()
	{
		if (self::$_singleton === null)
			self::$_singleton = new self;

		return self::$_singleton;
	}

	/**
	 *
	 * @param ffData valore inserito
	 * @param string label del campo
	 * @param <type> $options
	 * @return boolean validità del valore inserito
	 */

	public function checkValue(ffData $value, $label, $options)
	{
		$number = $value->getValue();
		if(!strlen($number))
			return false;

		if(preg_match('/^[0-23][:][0-59]$/', $number))
			return "Il valore inserito nel campo \"$label\" non è valido";

		return false;
	}
}
