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
 * validator: datetime
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_datetime extends ffValidator_base
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
		$plain = $value->getValue("DateTime", FF_SYSTEM_LOCALE);
		if(!strlen($plain))
			return false;

		if (!isValidDateTimeString($plain, "YY-MM-DD HH:ii:ss", $str_timezone))
			return "Il valore inserito nel campo \"$label\" non è valido";

		return false;
	}
}

function isValidDateTimeString($str_dt, $str_dateformat/*, $str_timezone*/)
{
	$date = DateTime::createFromFormat($str_dateformat, $str_dt/*, new DateTimeZone($str_timezone)*/);
	return $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
}