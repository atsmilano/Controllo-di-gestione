<?php
/**
 * Data Handling
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Questa classe immagazzina i dati utilizzati dagli oggetti del framework in un formato indipendente dalla localizzazione.
 * Memorizza inoltre informazioni dettagliate sulle parti che compongono il dato specifico e permette di effettuare conversioni da un locale all'altro
 * ed anche fra alcuni tipi di dati
 * 
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffData extends ffClassChecks
{
	/**
	 * il valore originale del dato, memorizzato non modificato
	 * @var mixed
	 */
	var $ori_value 	= null;
	/**
	 * Il tipo del dato memorizzato al momento della creazione dell'oggetto.
	 * può essere: Text, Number, Date, Time, DateTime, Timestamp, Currency
	 * non tutti i tipi di dato sono permessi per tutti i locale
	 * @var string
	 */
	var $data_type 	= "Text";
	/**
	 * Il locale del dato memorizzato al momento della creazione dell'oggetto
	 * può essere uno qualsiasi dei tipi indicati nella sottodir "locale"
	 * Esistono due costanti predefinite normalmente associate al locale:
	 *  - FF_SYSTEM_LOCALE : il tipo usato dal sistema (sempre ISO9075)
	 *  - FF_LOCALE : il tipo usato per visualizzare i dati all'utente
	 * @var string
	 */
	var $locale 	= FF_SYSTEM_LOCALE;	/* The default locale setting. 
	
												NB.: DON'T ALTER THIS!!!!
												This will be altered on single instances, but is NOT safe to alter the default
												due to superclasses automation.
												If you want to alter the default locale of system objects, alter the settings
												in configuration file. */
	/**
	 * Se dev'essere applicata una trasformazione in modo che non venga mai restituito null come valore.
	 * Se "true", per dati di tipo testuale verrà restituita stringa nulla, per dati di tipo numerico verrà restituito 0
	 * @var string
	 */
	var $transform_null 		= false;

	/**
	 * @todo
	 * @var string
	 */
	var $format_string			= null;
	
	/**
	 * Il valore testuale del dato
	 * @var string
	 */
	var $value_text				= null;
	/**
	 * la parte intera di un valore numerico
	 * @var int
	 */
	var $value_numeric_integer	= null;
	/**
	 * la parte decimale di un valore numerico
	 * @var int
	 */
	var $value_numeric_decimal	= null;
	/**
	 * il segno di un valore numerico, true per negativo, false per positivo
	 * @var boolean
	 */
	var $value_sign				= false;
	/**
	 * La parte "giorno" di una data
	 * @var int
	 */
	var $value_date_day			= null;
	/**
	 * La parte "mese" di una data
	 * @var int
	 */
	var $value_date_month		= null;
	/**
	 * La parte "anno" di una data
	 * @var int
	 */
	var $value_date_year		= null;
	/**
	 * La parte "ora" di un orario
	 * @var int
	 */
	var $value_date_hours		= null;
	/**
	 * La parte "minuti" di un orario
	 * @var int
	 */
	var $value_date_minutes		= null;
	/**
	 * La parte "secondi" di un orario
	 * @var int
	 */
	var $value_date_seconds		= null;
	/**
	 * Se una data è precedente o successiva a mezzogiorno: true se precedente, false se successiva
	 * @var bool
	 */
	var $value_date_meridiem	= false; /* true = ante, false = post */

	/**
	 * @deprecated
	 * Se un tipo currency deve mostrare la parte decimale
	 * @var bool
	 */
	var $format_currency_showdecimals = true;
    
	/**
	 * crea un oggetto ffData
	 * 
	 * @param mixed $value il valore originale del dato
	 * @param string $data_type il tipo del dato
	 * @param string $locale la localizzazione del dato originale
	 * @return ffData
	 */
	function __construct($value = null, $data_type = null, $locale = null)
	{
		// embedded types
		if (is_object($value))
		{
			if (get_class($value) == "DateTime")
			{
				if ($data_type === null)
					$data_type = "DateTime";
				elseif ($data_type !== "DateTime" && $data_type !== "Date")
					ffErrorHandler::raise("DateTime object with " . $data_type . " type", E_USER_ERROR, $this, get_defined_vars());

				if ($data_type == "Date")
					$value = $value->format("Y-m-d");
				else
					$value = $value->format("Y-m-d H:i:s");

				$locale = "ISO9075";
			}
		}
		
		if ($data_type !== null)
			$this->data_type = $data_type;
			
		if ($locale !== null)
			$this->locale = $locale;
			
		if ($value !== null)
			$this->setValue($value, $data_type, $locale);
	}
		
	/**
	 * set all the proper value fields in one shot.
	 * 
	 * @param mixed $value il valore da impostare nell'oggetto preesistente
	 * @param string $data_type il tipo del dato da memorizzare (sovrascriverà quello attuale). Se omesso viene considerato il tipo attuale.
	 * @param string $locale il locale del dato da impostare. se omesso viene utilizzato quello attuale.
	 */
	function setValue($value, $data_type = null, $locale = null)
	{
		$this->ori_value = $value;
		
		// setting content of the object will not reset the locale
		if ($locale === null)
			$locale = $this->locale;
			
		if (!$locale)
			ffErrorHandler::raise("You must specify a locale settings", E_USER_ERROR, $this, get_defined_vars());
			
		// alter the content of the object will reset the data_type
		if ($data_type === null)
			$data_type = $this->data_type;
		else
			$this->data_type = $data_type;
			
		if ($data_type == "Text")
		{
			$this->value_text = $value;
			return;
		}

		require_once(__DIR__ . "/locale/FormsLocale_" . $locale . "." . FF_PHP_EXT);

		$funcname = "FormsLocale_" . $locale . "_Set" . $data_type;
		/*if(!function_exists($funcname))
			ffErrorHandler::raise("Function not exist " . $funcname, E_USER_ERROR, $this, get_defined_vars());*/
		
		$funcname(array(&$this), $value);
	}
		
	function getValue($data_type = null, $locale = null)
	{
		if ($this->ori_value === null/* || $this->ori_value === ""*/)
			return null;
	
		if ($locale === null)
			$locale = $this->locale;
			
		if (!$locale)
			ffErrorHandler::raise("You must specify a locale settings", E_USER_ERROR, $this, get_defined_vars());
			
		// it's possible to use data type different from the one stored (es.: DateTime -> Date or Time)
		if ($data_type === null)
			$data_type = $this->data_type;
		
		if ($data_type == "Text")
		{
			return $this->value_text . "";
		}
			
		if ($data_type == "Currency" && $locale == "ISO9075")
			ffErrorHandler::raise("ffData cowardly refuse to manage currency on ISO9075", E_USER_ERROR, $this, get_defined_vars());
			
        require_once(__DIR__ . "/locale/FormsLocale_" . $locale . "." . FF_PHP_EXT);

		$funcname = "FormsLocale_" . $locale . "_Get" . $data_type;
		/*if(!function_exists($funcname))
			ffErrorHandler::raise("Function not exist " . $funcname, E_USER_ERROR, $this, get_defined_vars());*/
		
		return $funcname(array(&$this));
	}
        
	function getDateTime()
	{
		if ($this->data_type === "Date")
			return new DateTime(
					sprintf("%'04u-%'02u-%'02uT00:00:00", $this->value_date_year, $this->value_date_month, $this->value_date_day)
				);
		else if ($this->data_type === "DateTime")
			return new DateTime(
					sprintf("%'04u-%'02u-%'02uT%'02u:%'02u:%'02u", $this->value_date_year, $this->value_date_month, $this->value_date_day, $this->value_date_hours, $this->value_date_minutes, $this->value_date_seconds)
				);
		else
			ffErrorHandler::raise("tried to recover DateTime on " . $this->data_type . " type", E_USER_ERROR, $this, get_defined_vars());
	}
        
	static function getEmpty($data_type, $locale)
	{
		if (!$data_type)
			ffErrorHandler::raise("You must specify a data type", E_USER_ERROR, null, get_defined_vars());
			
		if (!$locale)
			ffErrorHandler::raise("You must specify a locale settings", E_USER_ERROR, null, get_defined_vars());
			
		if ($data_type == "Currency" && $locale == "ISO9075")
			ffErrorHandler::raise("ffData cowardly refuse to manage currency on ISO9075", E_USER_ERROR, null, get_defined_vars());
			
		require_once(__DIR__ . "/locale/FormsLocale_" . $locale . "." . FF_PHP_EXT);

		$funcname = "FormsLocale_" . $locale . "_GetEmpty" . $data_type;
		
		return $funcname();
	}
        
    function checkValue($raw_value = null, $data_type = null, $locale = null)
    {
        if ($raw_value === null)
            $raw_value = $this->ori_value;
            
        if ($raw_value === null/* || $this->ori_value === ""*/)
            return null;
    
        if ($locale === null)
            $locale = $this->locale;
            
		if (!$locale)
			ffErrorHandler::raise("You must specify a locale settings", E_USER_ERROR, $this, get_defined_vars());
            
        // it's possible to use data type different from the one stored (es.: DateTime -> Date or Time)
        if ($data_type === null)
            $data_type = $this->data_type;
		
		if ($data_type == "Text")
			return true;
            
        if ($data_type == "Currency" && $locale == "ISO9075")
			ffErrorHandler::raise("ffData cowardly refuse to manage currency on ISO9075", E_USER_ERROR, $this, get_defined_vars());
            
        require_once(__DIR__ . "/locale/FormsLocale_" . $locale . "." . FF_PHP_EXT);

		$funcname = "FormsLocale_" . $locale . "_Check" . $data_type;
		/*if(!function_exists($funcname))
			ffErrorHandler::raise("Function not exist " . $funcname, E_USER_ERROR, $this, get_defined_vars());*/
		
		return $funcname($raw_value);
    }
        
           		
	function format_value($format_string = null, $data_type = null, $locale = null)
	{
		if ($locale === null)
			$locale = $this->locale;
			
		if (!$locale)
			die("You must specify a locale settings");
			
		// it's possible to use data type different from the one stored (es.: DateTime -> Date or Time)
		if ($data_type === null)
			$data_type = $this->data_type;
		
		if ($data_type == "Text")
			return $this->ori_value;

		require_once(__DIR__ . "/locale/FormsLocale_" . $locale . "." . FF_PHP_EXT);

		if ($format_string === null)
		{
			if ($this->format_string !== null)	
				$format_string = $this->format_string;
			else
			{
				$format_string = ${"FormsLocale_" . $locale . "_format"}[$data_type];
			}
		}
			
		switch ($data_type)
		{
			case "Date":
			case "Time":
			case "DateTime":
				$timestamp = mktime(
						$this->value_date_hours,
						$this->value_date_minutes,
						$this->value_date_seconds,
						$this->value_date_month,
						$this->value_date_day,
						$this->value_date_year
					);
					
				return date($format_string, $timestamp);
			
			case "Currency":
			case "Number":
				break;

			default: // Text
				ffErrorHandler::raise("Unhandled data_type", E_USER_ERROR, $this, get_defined_vars());
		}
	}
		
	/*function __toString()
	{
		return $this->getValue();
	}*/
}
