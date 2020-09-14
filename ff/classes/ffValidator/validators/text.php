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
class ffValidator_text extends ffValidator_base
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
	 * @param string label del campo
	 * @param <type> $options
	 * @return boolean validità del valore inserito
	 */

	public function checkValue(ffData $value, $label, $options)
	{
		//options
		//0 true se ammettere anche caratteri numerici (default esclusi)
		//1 lunghezza minima testo (default null)
		//2 lunghezza massima testo (default null)
		//viene salvato nella variabile text il valore immesso nel form
		$text = $value->getValue();
		//se non è stato inserito un valore il controll oviene bypassato
		if(!strlen($text))
			return false;

		//vengono predisposti i controlli sulla lunghezza mon e max del valore passato
		if (isset($options[1]))
			$min_lenght = intval($options[1]);
		else
			$min_lenght = null;
		if (isset($options[2]))	
			$max_lenght = intval($options[2]);
		else
			$max_lenght = null;

		//viene definita l'espressione regolare da utilizzare per il controllo
		$reg_exp = '';
		//viene valutato, tramite opzione passata al validatore, se è il caso di valutare la stringa come testo puro o come stringa alfanumerica
		if (isset($options[0]) && ($options[0] == true))
			$reg_exp .= "0-9";

		//controlli sulla lunghezza
		if (isset($min_lenght) && strlen($text)<$min_lenght)
			return "La lunghezza del testo inserito nel campo \"$label\" (".strlen($text).") dev'essere superiore ai $min_lenght caratteri";
		if (isset($max_lenght) && strlen($text)>$max_lenght)
			return "La lunghezza del testo inserito nel campo \"$label\" (".strlen($text).") dev'essere inferiore ai $max_lenght caratteri";
		//controllo formale
		if (preg_match("/^\s*[a-zA-Z$reg_exp\'àòùìèé,\s]+\s*$/", $text) < 1)
			return "Il testo inserito nel campo \"$label\" non è valido";
		
		return false;
	}
}
