<?php
/**
 * validator: number
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: number
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_number extends ffValidator_base
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
        //options
        //0 true
        //1 valore minimo ammesso(default null)
        //2 valore massimo ammesso(default null)
        //3 true=val minimo compreso nel controllo(di default false)
        //4 true=val massimo compreso nel controllo(di default false)
        //5 ammesso il valore 0
        //controllo formale
        if(preg_match('/^[0-9]*$/', $number) < 1)
            return "Il valore inserito nel campo \"$label\" non è un numero intero";

        //vengono predisposti i controlli sul valore min e max ammessi
        if (isset($options[1]))
            $min = $options[1];
        else
            $min = null;
        if (isset($options[2]))
            $max = $options[2];
        else
            $max = null;
        if ((isset($options[3]) && $options[3]!==true) || !isset($options[3]))
            $options[3] = false;
        if ((isset($options[4]) && $options[4]!==true) || !isset($options[4]))
            $options[4] = false;
        //ADDED-ATS parametro per la validità del valore 0
        if ((isset($options[5]) && $options[5]!==true) || !isset($options[5]))
            $options[5] = false;            

        //controlli sulla lunghezza
		if ($options[5] !== false && $number == 0) {
			return false;
		}
		if ($options[3] !== false) {
			if (isset($min) && $number<$min)
				return "Il numero inserito per il campo \"$label\" dev'essere superiore a $min";
		}
		else {
			if (isset($min) && $number<=$min)
				return "Il numero inserito per il campo \"$label\" dev'essere superiore o uguale a $min";
		}
        
		if ($options[4] !== false) {
			if (isset($max) && $number>$max)
				return "Il numero inserito per il campo \"$label\" dev'essere inferiore a $max";
		}
		else {
			if (isset($max) && $number>=$max)
				return "Il numero inserito per il campo \"$label\" dev'essere inferiore o uguale a $max";
		}
    }
}
