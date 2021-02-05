<?php
/**
 * validator: password
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * validator: password
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffValidator_password extends ffValidator_base
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
		$password = $value->getValue();

		//verifica formale dell'password        
        //ADDED-ATS changed password policy*************************************
        /*old controls
        if(preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%\.]{8,30}$/', $password) < 1) 
            return "Il valore inserito nel campo \"$label\" non soddisfa i criteri minimi di sicurezza: La lunghezza deve essere compresa tra gli 8 e i 30 caratteri, e deve essere composta sia da lettere che da numeri.";
        */
        $errors = array();
        if (strlen($password) < 12 || strlen($password) > 30) {
            $errors[] = "lunghezza minuma di 12 caratteri e lunghezza massima di 30";
        }
        if (!preg_match("/\d/", $password)) {
            $errors[] = "contenere almeno una numero";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "contenere almeno una maiuscola";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "contenere almeno una minuscola";
        }
        if (!preg_match("/\W/", $password)) {
            $errors[] = "contenere almeno un carattere speciale";
        }
        if (preg_match("/\s/", $password)) {
            $errors[] = "non contenere spazi vuoti";
        }

        if ($errors) {
            return "La password non rispetta i seguenti criteri:<br>".implode("<br>",$errors);
        }
        //**********************************************************************
		return false;
	}
}
