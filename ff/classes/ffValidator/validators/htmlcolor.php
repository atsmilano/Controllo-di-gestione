<?php
/**
 * validator: piva
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: piva
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_htmlcolor extends ffValidator_base
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
	 * @param ffData Valore inserito nel campo piva
	 * @param String label del campo
	 * @param <type> $options
	 * @return boolean Validità del valore inserito
	 */

	public function checkValue(ffData $value, $label, $options)
	{
		$htmlcolor = $value->getValue();

		//verifica formale dell'iban
        if(preg_match("/^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/", $htmlcolor) < 1)
            return "Il valore inserito nel campo \"$label\" non è un codice colore valido";

		return false;
	}
}
