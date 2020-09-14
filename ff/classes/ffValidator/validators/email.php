<?php
/**
 * validator: email
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: email
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_email extends ffValidator_base
{
	static $_singleton = null;
	
	public $error = array(
			"invalid" => "L'indirizzo mail inserito nel campo [LABEL] non è valido"
		);

	static function getInstance()
	{
		if (self::$_singleton === null)
			self::$_singleton = new self;

		return self::$_singleton;
	}

	/**
	 *
	 * @param ffData valore immesso
	 * @param string label del campo
	 * @param <type> $options
	 * @return boolean validità del valore inserito
	 */

	public function checkValue(ffData $value, $label, $options)
	{
		$email = $value->getValue();
		if(!strlen($email))
			return false;
		
        $regex = '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';                              // Normal mode
        if (preg_match($regex, $email) < 1)
            return $this->get_error("invalid", $label);
            
		$parts = explode("@", $email);
		if (strlen($parts[0]) > 64 || strlen($parts[1]) > 255)
            return $this->get_error("invalid", $label);

		return false;
	}
}
