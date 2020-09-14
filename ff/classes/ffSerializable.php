<?php
/**
 * framework serializable cloning object
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * framework serializable cloning object
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffSerializable
{
	/**
	 * Il contenuto testuale convertito dell'oggetto
	 * @var String
	 */
	var $__tostring = "";
	var $__attributes = array(); // simpleXML compatibility

	/**
	 * Crea una copia dell'oggetto esaminando proprietà, metodi ed attributi
	 * @param Object $object l'oggetto o la risorsa
	 */
	function  __construct($object = null)
	{
		if ($object === null)
			return;
		
		if (!is_object($object))
			ffErrorHandler::raise ("Can only serialize objects", E_USER_ERROR, $this, get_defined_vars ());

		$obj_vars = get_object_vars($object);
		if (is_array($obj_vars) && count($obj_vars))
		{
			foreach ($obj_vars as $key => $var)
			{
				if ($key == "comment") // simpleXML compatibility
					continue;

				if ($key != "@attributes") // simpleXML compatibility
					$this->$key = $this->__process_var($var);
			}
		}

		if (isset($obj_vars["@attributes"])) // simpleXML compatibility
		{
			$this->__attributes = $obj_vars["@attributes"];
			foreach ($obj_vars["@attributes"] as $key => $var)
			{
				$this->$key = $var;
			}
		}

		$tmp = (string)$object;
		if (strlen($tmp))
			$this->__tostring = $tmp;
	}

	/**
	 * Recupera la rappresentazione testuale dell'oggetto
	 * @return String la rappresentazione testuale dell'oggetto
	 */
	function __toString()
	{
		return $this->__tostring;
	}

	/**
	 * Elabora un elemento od un set di elementi creandone copie a cascata con ffSerializable, quando necessario
	 * Utilizzato internamente
	 * @param Mixed $var l'elemento o il set di elementi
	 * @return Mixed Il clone risultante
	 */
	private function __process_var($var)
	{
		if (is_array($var))
		{
			$tmp = array();
			foreach ($var as $key => $value)
			{
				$tmp[$key] = $this->__process_var($value);
			}
		}
		elseif (is_object($var))
		{
			$tmp = new ffSerializable($var);
		}
		else
			$tmp = $var;
		
		return $tmp;
	}
}
