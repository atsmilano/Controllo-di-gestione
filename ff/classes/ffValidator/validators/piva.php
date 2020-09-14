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
class ffValidator_piva extends ffValidator_base
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
		$piva = $value->getValue();
		

        if($piva == '')  
            return "Il campo \"$label\" è vuoto";
        if(strlen($piva) != 11)
            return "Il campo \"$label\" deve contenere 11 caratteri";
        if($piva == '00000000000')  
            return "Il campo \"$label\" non è valido";
        if(preg_match("/^[0-9]+$/", $piva) < 1)
            return "Il valore inserito nel campo \"$label\" non è un numero";
        
        $s = 0;
        for($i = 0; $i <= 9; $i += 2)
            $s += ord($piva[$i]) - ord('0');
        
        for($i = 1; $i <= 9; $i += 2) {
            $c = 2*(ord($piva[$i]) - ord('0'));
            if($c > 9)  
                $c = $c - 9;
            
            $s += $c;
        }
        if(( 10 - $s%10 )%10 != ord($piva[10]) - ord('0'))
            return "Il campo \"$label\" non corrisponde ad un codice di partita iva";

		return false;
	}
}
