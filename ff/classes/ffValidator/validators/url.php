<?php
/**
 * validator: url
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: url
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_url extends ffValidator_base
{
	static $_singleton = null;

	static function getInstance()
	{
		if (self::$_singleton === null)
			self::$_singleton = new self;

		return self::$_singleton;
	}

	/**
	 * Questa funzione controlla la validitï¿½ di un URL tramite l'utilizzo di una regular expression
	 *
	 * @param ffData URL inserito
	 * @param String Label del campo
	 * @param <type> $options
	 * @return boolean validità dell'url inserito
	 */

	public function checkValue(ffData $value, $label, $options)
	{
		$url = $value->getValue();
		if(!strlen($url))
			return false;
		
        if(strpos($url, "http") !== 0 && strpos($url, "www") !== false)
            $url = "http://" . $url;
            
		if (preg_match('/^(https?:\/\/)(([\d\w]|%[a-fA-F\d]{2,2})+(:([\d\w]|%[a-fA-F\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([\-+_~.\d\w]|%[a-fA-F\d]{2,2})*)*(\?(&?([\-+_~.\d\w]|%[a-fA-F\d]{2,2})=?)*)?(#([\-+_~.\d\w]|%[a-fA-F\d]{2,2})*)?$/', $url) < 1)
		{
			return "L'url inserito nel campo \"$label\" non è valido";
		}

		return false;
	}
}
