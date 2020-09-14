<?php
/**
 * validator: fiscal code
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: fiscal code
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_cf extends ffValidator_base
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

    public function checkValue(ffData $value, $label, $options)
    {
        $cf = $value->getValue();

        //controllo sulla lunghezza
        if(!strlen($cf) || strlen($cf) != 16)
            return "La lunghezza del codice fiscale inserito nel campo \"$label\" non è corretta: il codice fiscale dovrebbe essere lungo esattamente 16 caratteri.";

        //controllo sul formato
        $cf = strtoupper($cf);
        if(!preg_match('/^[A-Za-z]{6}[0-9]{2}[A-Za-z]{1}[0-9]{2}[A-Za-z]{1}[0-9]{3}[A-Za-z]{1}$/', $cf))
            return "Il formato del valore inserito per il campo \"$label\" non risulta corretto.";

        //controllo sull'ultima lettera
        $s = 0;
        for($i = 1; $i <= 13; $i += 2)
        {
            $c = $cf[$i];
            if('0' <= $c && $c <= '9')
                $s += ord($c) - ord('0');
            else
                $s += ord($c) - ord('A');
        }
        for($i = 0; $i <= 14; $i += 2)
        {
            $c = $cf[$i];
            switch( $c )
            {
                case '0':  $s += 1;  break;
                case '1':  $s += 0;  break;
                case '2':  $s += 5;  break;
                case '3':  $s += 7;  break;
                case '4':  $s += 9;  break;
                case '5':  $s += 13;  break;
                case '6':  $s += 15;  break;
                case '7':  $s += 17;  break;
                case '8':  $s += 19;  break;
                case '9':  $s += 21;  break;
                case 'A':  $s += 1;  break;
                case 'B':  $s += 0;  break;
                case 'C':  $s += 5;  break;
                case 'D':  $s += 7;  break;
                case 'E':  $s += 9;  break;
                case 'F':  $s += 13;  break;
                case 'G':  $s += 15;  break;
                case 'H':  $s += 17;  break;
                case 'I':  $s += 19;  break;
                case 'J':  $s += 21;  break;
                case 'K':  $s += 2;  break;
                case 'L':  $s += 4;  break;
                case 'M':  $s += 18;  break;
                case 'N':  $s += 20;  break;
                case 'O':  $s += 11;  break;
                case 'P':  $s += 3;  break;
                case 'Q':  $s += 6;  break;
                case 'R':  $s += 8;  break;
                case 'S':  $s += 12;  break;
                case 'T':  $s += 14;  break;
                case 'U':  $s += 16;  break;
                case 'V':  $s += 10;  break;
                case 'W':  $s += 22;  break;
                case 'X':  $s += 25;  break;
                case 'Y':  $s += 24;  break;
                case 'Z':  $s += 23;  break;
            }
        }

        if(chr($s%26 + ord('A')) != $cf[15])
            return "Il codice fiscale inserito nel campo \"$label\" non &egrave; corretto: il codice di controllo non corrisponde.";

        return false;
    }
}
