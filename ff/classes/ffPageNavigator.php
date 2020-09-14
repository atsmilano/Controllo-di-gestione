<?php
/**
 * Interface Page Navigator
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Interface Page Navigator
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffPageNavigator
{
	static protected $events = null;
	
	public function __construct()
	{
		ffErrorHandler::raise("Cannot istantiate " . __CLASS__ . " directly, use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}
	
	public function __clone()
	{
		ffErrorHandler::raise("Cannot clone " . __CLASS__ . ", use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}
	
	static public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null)
	{
		self::initEvents();
		self::$events->addEvent($event_name, $func_name, $priority, $index, $break_when, $break_value);
	}

	static public function doEvent($event_name, $event_params = array())
	{
		self::initEvents();
		return self::$events->doEvent($event_name, $event_params);
	}
	
	static private function initEvents()
	{
		if (self::$events === null)
			self::$events = new ffEvents();
	}

	/**
	 * Questo metodo crea un'istanza di ffPageNavigator utilizzando i parametri in ingresso
	 *
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $site_path
	 * @param string $page_path
	 * @param string $theme
	 * @param array $variant
	 * @return ffPageNavigator_base
	 */
	public static function factory(ffPage_base $page = null, $disk_path = null, $site_path = null, $page_path = null, $theme = null, array $variant = null)
	{
		if ($page === null && ($disk_path === null || $site_path === null))
			ffErrorHandler::raise("page or fixed path_vars required", E_USER_ERROR, null, get_defined_vars());
		
		if ($theme === null)
		{
			if ($page !== null)
				$theme = $page->theme;
			else
				$theme = FF_DEFAULT_THEME;
		}
			
		if ($disk_path === null)
		{
			if ($page !== null)
				$disk_path = $page->disk_path;
		}
			
		if ($site_path === null)
		{
			if ($page !== null)
				$site_path = $page->site_path;
		}
			
		if ($page_path === null)
		{
			if ($page !== null)
				$page_path = $page->page_path;
		}
		
		$res = self::doEvent("on_factory", array($page, $disk_path, $site_path, $page_path, $theme, $variant));
		$last_res = end($res);

		if (is_null($last_res))
		{
			$base_path = $disk_path . "/themes/" . $theme;
			
			if (!isset($variant["name"]))
			{
				$registry = ffGlobals::getInstance("_registry_");
				if (!isset($registry->themes) || !isset($registry->themes[$theme]))
				{
					$registry->themes[$theme] = new SimpleXMLElement($base_path . "/theme_settings.xml", null, true);
				}
		
				$suffix = $registry->themes[$theme]->default_class_suffix;
				
				$class_name = __CLASS__ . "_" . $suffix;
			}
			else
				$class_name = $variant["name"];
				
			if (!isset($variant["path"]))
				$base_path .= "/ff/" . __CLASS__ . "/" . $class_name . "." . FF_PHP_EXT;
			else
				$base_path .= $variant["path"];
		}
		else
		{
			$base_path = $last_res["base_path"];
			$class_name = $last_res["class_name"];
		}
		
		require_once $base_path;
		$tmp = new $class_name($disk_path, $site_path, $page_path, $theme, $page);

		$res = self::doEvent("on_factory_done", array($tmp));

		return $tmp;
	}
}

/**
 * ffPageNavigator è la classe adibita alla gestione
 * dell'elemento d'interfaccia adibito alla navigazione fra pagine.
 * Viene comunemente usato da ffGrid
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffPageNavigator_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)

	/**
	 * ID dell'oggetto; deve essere univoco per ogni ffPage
	 * @var Number
	 */
	var $id 					= null;

	/**
	 * URL relativo al web del sito
	 * @var String
	 */
	var $site_path 				= "";
	
	/**
	 * URL relativo al disco del sito
	 * @var String
	 */
	var $disk_path 				= "";					// site's disk-relative URL

	/**
	 * Directory dove è contenuta la pagina partendo dalla root del sito
	 * @var String
	 */
	var $page_path 				= "";					// page's directory from site root

	/**
	 * Directory del template; di default è la Directory "theme"
	 * @var String
	 */
	var $template_dir			= null;					// Where to locate the template. Default to theme dir

	/**
	 * File del template; di default è il file "ffPageNavigator.html"
	 * @var String
	 */
	var $template_file 			= "ffPageNavigator.html"; 		/* the template file to use with navigator.
																		normally set to Navigator_default.html
																		NB: the CSS must be bundled with the main CSS,
																		because the navigator is a part
																		of the page, not a page itself. */

	/**
	 * Il tema correntemente in uso
	 * @var String
	 */
	var $theme					= null;

	/**
	 * Le variabili fisse da inserire nel template, personalizzate dall'utente
	 * @var mixed
	 */
	var $fixed_vars				= array();

	/**
	 * Visualizza il pulsante "pagina precedente"
	 * @var Boolean
	 */
	var $display_prev 			= true;					

	/**
	 * Visualizza il pulsante "pagina successiva"
	 * @var Boolean
	 */
	var $display_next			= true;					

	/**
	 * Visualizza il pulsante "prima pagina"
	 * @var Boolean
	 */
	var $display_first			= true; 				

	/**
	 * Visualizza il pulsante "ultima pagina"
	 * @var Boolean
	 */
	var $display_last			= true;

	/**
	 * Abilita / Disabilita l'utilizzo dei frames;
	 * I frame suddividono le pagine in gruppi, servono ad evitare di avere un menù di navigazione troppo largo
	 * @var Boolean
	 */
	var $with_frames			= true;

	/**
	 * Abilita / Disabilita l'utilizzo dei pulsanti per andare al frame precedente / successivo;
	 * @var Boolean
	 */
	var $show_frame_button		= true;
	
	/**
	 * Abilita la visualizzazione dell'input per la selezione di una pagina specifica
	 * @var Boolean
	 */
	var $with_choice 			= false;

	/**
	 * Abilita la visualizzazione del numero totale di record
	 * @var Boolean
	 */
	var $with_totelem 			= false;

	/**
	 * Abilita la possibilità di selezionare il numero di record per pagina
	 * @var Boolean
	 */
	var $nav_display_selector	= true;					

	/**
	 * Elementi che appaiono all'interno del combo per selezionare il numero di record per pagina
	 * @var array()
	 */
	var $nav_selector_elements	= array(10, 25, 50);

	/**
	 * Abilita la visualizzazione del pulsante "tutti i record"
	 * @var Boolean
	 */
    var $nav_selector_elements_all = false;

	/**
	 * Nome del form utilizzato per la navigazione. Di default è "frmMain"
	 * @var String
	 */
	var $form_name 				= "frmMain";

	/**
	 * Nome dell'input utilizzato per impostare l'azione di navigazione. Di default è "frmAction"
	 * @var String
	 */
	var $form_action 			= "frmAction";

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	/**
	 * il componente padre del navigator, se esiste (solitamente una ffGrid)
	 * @var mixed
	 */
	var $parent					= null;

	/**
	 * l'oggetto pagina a cui appartiene il navigator
	 * @var mixed
	 */
	var $oPage					= null;

	/**
	 * Pagina attuale
	 * @var Number
	 */
	var $page					= 1;

	/**
	 * Numero di records
	 * @var Number
	 */
	var $num_rows				= 0;					

	/**
	 * Numero di record per pagina; utilizzato per calcolare il numero totale di pagine
	 * @var Number
	 */
	var $records_per_page		= 25;

	/**
	 * Il numero di pagine da includere in un singolo frame. Determina di fatto
	 * la larghezza che occuperà il navigator nell'interfaccia (quando i frame
	 * sono attivi, scelta aupsicabile in ogni caso)
	 * @var Number
	 */
	var $PagePerFrame			= 5;					/* 

	/* NB: changing parameters names make Forms integration "difficult" ;-)
			change this only if you are using non Forms pages */

	/**
	 * Nome del parametro "pagina"
	 * @var String
	 */
	var $page_parname			= "page";

	/**
	 * Nome del parametro "records_per_page"
	 * @var String
	 */
	var $records_per_page_parname = "records_per_page";

	/**
	 * Nome del parametro "page_per_frame"
	 * @var String
	 */
	var $page_per_frame_parname = "page_per_frame";

	/**
	 * L'oggetto ffTemplate utilizzato
	 * @var Mixed
	 */
	var $tpl					= null;

	abstract public function tplLoad();
	abstract public function tplParse($output_result);

	// ---------------------------------------------------------------
	//  PUBLIC FUNCS

	//  CONSTRUCTOR
	function __construct($disk_path, $site_path, $page_path, $theme, $page = null)
	{
		$this->get_defaults("ffPageNavigator");
		$this->get_defaults();

		$this->disk_path = $disk_path;
		$this->site_path = $site_path;
		$this->page_path = $page_path;
		$this->theme = $theme;
        
        if(is_subclass_of($page, "ffPage_base")) {
            $this->oPage[0] = $page;
        }                   
	}

	/**
	 * Restituisce il tema in uso
	 * @return theme Il tema in uso
	 */
	function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Restituisce la directory dei template
	 * @return Directory dei template
	 */
	function getTemplateDir()
	{
		$res = $this->doEvent("getTemplateDir", array($this));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffPageNavigator";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}
}
