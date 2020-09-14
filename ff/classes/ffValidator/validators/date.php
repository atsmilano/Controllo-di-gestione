<?php
/**
 * validator: fiscal code
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Matteo Bonvini <matteo.bonvini@gmail.com>
 * @copyright Copyright (c) 2004-2010, Matteo Bonvini
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: fiscal code
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Matteo Bonvini <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2010, Matteo Bonvini
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_date extends ffValidator_base
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
	 * @param ffData valore immesso
	 * @param String label del campo
	 * @param <type> $options
	 * @return boolean valodità del valore immesso
	 */

	public function checkValue(ffData $value, $label, $options = null)
	{
                //verifica sul tipo di ffData
                if ($value->data_type !== "Date")
                {
                    return "Il campo \"$label\" non è di tipo data.";
                }
                else
                {
                    //verifica sul valore immesso
                    if(!(checkdate($value->value_date_month,$value->value_date_day,$value->value_date_year)))
                    {
                        return "Errore di inserimento: il dato inserito nel campo \"$label\" non è una data.";
                    }
                }
                return false;
	}
}
