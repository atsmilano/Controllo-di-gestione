<?php
/**
 * Button
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Button
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffButton
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
	 * Istanzia un ffButton basandosi sui parametri in ingresso
	 *
	 * @param ffPage_base $page
	 * @param String $disk_path
	 * @param String $site_path
	 * @param String $page_path
	 * @param String $theme
	 * @param array() $variant
	 * @return ffButton_base
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
		$tmp = new $class_name($disk_path, $site_path, $page_path, $theme);
		
		$res = self::doEvent("on_factory_done", array($tmp));

		return $tmp;
	}
}



/**
 * ffButton è la classe adibita alla gestione di pulsanti
 * 
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffButton_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)
	var $framework_css					= array(
											"addon" => null // null OR prefix OR postfix
	);

	/**
	 * URL relativo al web del sito
	 * @var String
	 */
	var $site_path 				= "";

	/**
	 * URL relativo al disco del sito
	 * @var String
	 */
	var $disk_path 				= "";

	/**
	 * Cartella dove ï¿½ contenuta la pagina partendo dalla root del sito
	 * @var String
	 */
	var $page_path 				= "";

	/**
	 * Cartella del template; di default ï¿½ la cartella "theme"
	 * @var String
	 */
	var $template_dir			= NULL;

	/**
	 * File del template; di default ï¿½ il file "ffButton.html"
	 * @var String
	 */
	var $template_file 			= "";
	
	var $theme 					= NULL;
	var $properties				= array();

	/**
	 * ID di ffButton; deve essere univoco per ogni pulsante inserito nella pagina
	 * @var String
	 */
	var $id						= "";

	/**
	 * Label di ffButton
	 * @var String
	 */
	var $label					= "";
    var $icon                   = null;
	/**
	 * "Aspetto" di ffButton; può essere "button" (default) o link (con un'immagine opzionale)
	 * @var String
	 */
	var $aspect					= "button";
	var $class					= NULL;

	/**
	 * Azione eseguita sul click; può essere "none", "submit" o "gotourl"
	 * @var String
	 */
	var $action_type			= "submit";

	/**
	 * Metodo eseguito sul form; può essere NULL, "gotourl" e "submit"
	 * @var String
	 */
	var $form_method			= null;

	/**
	 * URL di $frm_action
	 * @var String
	 */
	var $form_action_url		= NULL;

	/**
	 * Azione restituita nel caso in cui l'action type sia "submit"
	 * @var String
	 */
	var $frmAction				= "";
	var $component_action		= NULL;

	var $jsaction				= "";

	/**
	 * E' l'URL al quale punta la variabile $gotourl;
	 * di default ï¿½ $site_path . $page_path . pagina
	 * @var String
	 */
	var $url					= "?[KEYS]&ret_url=[ENCODED_THIS_URL]";

	/**
	 * Parametro Target HTML per i pulsanti con azione "gotourl"
	 * @var String
	 */
	var $target					= "_self";

	/**
	 * Attributo da fornire a window.open() come parametro
	 * @var String
	 */
	var $attributes				= "";

	/**
	 * URL dell'immagine da visualizzare nel caso ffButton sia un "link"
	 * @var String
	 */
	var $image					= null;

	/**
	 * Rende visibile / invisibile il pulsante
	 * @var Boolean
	 */
	var $display				= true;
	var $display_label			= true;
	
	var $fixed_vars = array();

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	/**
	 * La classe contenente ffButton
	 * @var String
	 */
	var $parent					= NULL;

	/**
	 * La pagina contenente ffButton
	 * @var String
	 */
	var $parent_page			= NULL;

	/**
	 * Oggetto ffTemplate() interno
	 * @var ffTemplate()
	 */
	var $tpl					= NULL;
	
	var $variables				= array();

	var $processed_form_action_url 	= "";
	var $processed_form_action		= "";

	/**
	 * Questa proprietà di ffButton permette di aggiungere una widget.
	 * Le widget disponibili si trovano nella cartella /ffButton/widgets
	 * Per creare una nuova widgets leggere la sezione "widgets" sul manuale del framework
	 * Se $widget è settato, verrà utilizzato la pagina principale dell'interfaccia delle widgets per visualizzare i controlli anziché il process di ffPage
	 * @var String
	 */
	var $widget			= "";

	/**
	 * set di opzioni per l'istanza della widget sul campo specifico
	 * @var Array
	 */
	var $widget_options = array();

	/**
	 * eventuali widget di dipendeza da caricare per la corretta visualizzazione del campo
	 * @var Array
	 */
	var $widget_deps = array();
	
	/**
	 * utilizzato dai componenti, determina se il campo non dev'essere visualizzato nel normale
	 * flusso dei contenuti ma in una locazione specifica
	 * @var Boolean
	 */
	var $use_own_location = false;
	var $location_name = null;
	var $location_context = null;
	var $ajax = false;

	abstract public function getTemplateFile();
	abstract public function tplLoad();
	abstract public function tplParse($output_result);
	
	/**
	* costruttore
	* @param String il percorso su disco (FF_DISK_PATH)
	* @param String il percorso su web (FF_SITE_PATH)
	* @param String il percorso della pagina relativo a site_path
	* @param String il tema in uso
	* @return ffButton_base
	*/
	function __construct($disk_path, $site_path, $page_path, $theme)
	{
		$this->get_defaults("ffButton");
		$this->get_defaults();

		$this->disk_path = $disk_path;
		$this->site_path = $site_path;
		$this->page_path = $page_path;
		$this->theme = $theme;
	}

	/**
	* Elabora l'oggetto e restituisce il risultato
	* @param String l'indirizzo a cui deve puntare il pulsante (vedi url)
	* @param Boolean se dev'essere visualizzato o restituito il risultato (default a FALSE)
	* @param String l'id del pulsante (vedi id)
	* @return mixed
	*/
	function process($url = NULL, $output_result = FALSE, $id = null)
	{
		if ($url === NULL)
			$url = $this->url;
		
		$keysarray = array();
		$dataarray = array();
		$hiddenarray = null;
		$db = null;
		
		if ($this->parent !== NULL)
		{
			if ($this->parent_page === NULL)
			{
				$this->parent_page = array();
				$this->parent_page[0] = $this->parent[0]->parent[0];
			}

			$keysarray = $this->parent[0]->key_fields;
			switch (get_parent_class($this->parent[0]))
			{
				case "ffGrid_base":
					$dataarray = array_merge($this->prepareCompVars($this->parent[0]->id, $this->parent[0]->grid_fields), $dataarray);
					$hiddenarray = $this->prepareCompVars($this->parent[0]->id, $this->parent[0]->hidden_fields);
					$db = $this->parent[0]->db[0];
					break;
				case "ffRecord_base":
				case "ffDetails_base":
					$dataarray = array_merge($this->prepareCompVars($this->parent[0]->id, $this->parent[0]->form_fields), $dataarray);
					$db = $this->parent[0]->db[0];
					break;
			}
		}

		if ($this->parent_page !== null)
		{
			$ret_url = $this->parent_page[0]->ret_url;
			$params = $this->parent_page[0]->get_params();
			$globals = $this->parent_page[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST);
			if (is_array($this->parent_page[0]->keys))
				$keysarray = array_merge($this->parent_page[0]->keys, $keysarray);
		}
		else
		{
			$ret_url = $_SERVER['REQUEST_URI'];
			$params = NULL;
			$globals = NULL;
		}
		
		$url = ffProcessTags($url, $keysarray, $dataarray, "normal", $params, $ret_url, $globals, $hiddenarray, $db);
		if (strlen($this->form_action_url))
		{
			$this->processed_form_action_url = ffProcessTags($this->form_action_url, $keysarray, $dataarray, "normal", $params, $ret_url, $globals, $hiddenarray, $db);
		}
		if (strlen($this->frmAction))
		{
			$this->processed_form_action = ffProcessTags($this->frmAction, $keysarray, $dataarray, "normal", $params, $ret_url, $globals, $hiddenarray, $db);
		}
			
//				if (!$this->tpl)
		$this->tplLoad();

		if ($id !== null)
			$this->tpl[0]->set_var("id", $id);
			
		switch($this->aspect)
		{
			case "link":
				$this->process_link($url);
				break;
			case "button":
			default:
				$this->process_button($url);
		}
		
		if ($this->display)
			return $this->tplParse($output_result);
		else
			return "";
	}

	/**
	 * Restituisce il tema utilizzato da ffButton
	 * @return String Tema utilizzato da ffButton
	 */
	function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Restituisce la directory del template utilizzato da ffButton
	 * @return String Directory del template utilizzato da ffButton
	 */
	function getTemplateDir()
	{
		$res = $this->doEvent("getTemplateDir", array($this));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffButton";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}
	
	/**
	* restituisce le proprietà HTML dell'oggetto in una stringa ben formata
	* @param mixed il set di proprietà (Vedi properties)
	* @return String
	*/
	function getProperties($properties = null)
	{
		if ($properties === null)
			$properties = $this->properties;
		
		$buffer = "";
		if (is_array($properties) && count($properties))
		{
			foreach ($properties as $key => $value)
			{
				if ($key == "style")
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . "=\"";
					foreach ($properties[$key] as $subkey => $subvalue)
					{
						$buffer .= $subkey . ": " . $subvalue . ";";
					}
					reset($properties[$key]);
					$buffer .= "\"";
				}
				else
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . (strlen($value) ? "=\"" . $value . "\"" : "");
				}
			}
			reset($properties);
		}
		return $buffer;
	}
		
	function getImage($only_path = false)
	{
		if (!strlen($this->image))
			return "";
		
		if (strip_tags($this->image) != $this->image)
		{
			return $this->image;
		}
		else if (substr($this->image, 0, 1) == "/")
		{
			if ($only_path)
				return $this->image;
			else
				return '<img src="' . $this->image . '" title="' . $this->label . '" />';
		}
		elseif (isset($this->parent) && is_subclass_of($this->parent[0], "ffGrid_base"))
		{
			if ($only_path)
				return $this->site_path . '/themes/' . $this->getTheme() . '/images/FormsGrid/' . $this->image;
			else
				return '<img src="' . $this->site_path . '/themes/' . $this->getTheme() . '/images/FormsGrid/' . $this->image . '" title="' . $this->label . '" />';
		}
		else
		{
			if ($only_path)
				return $this->site_path . '/themes/' . $this->getTheme() . '/images/' . $this->image;
			else
				return '<img src="' . $this->site_path . '/themes/' . $this->getTheme() . '/images/' . $this->image . '" title="' . $this->label . '" />';
		}
	}

	/**
	* esegue il process di oggetti con aspect = "button"
	* @param String l'url del pusante se utilizzato (vedi url e frmAction_url)
	*/
	function process_button($url)
	{
		$img = $this->getImage();
		$this->tpl[0]->set_var("image", $img);
		if (strlen($img))
			$this->tpl[0]->parse("SectImage", false);
		else
			$this->tpl[0]->set_var("SectImage", "");

		switch($this->action_type)
		{
			case "none":
				$this->tpl[0]->set_var("type", "button");

				$this->tpl[0]->set_var("SectFormAction", "");
				$this->tpl[0]->set_var("SectGotourlSelf", "");
				$this->tpl[0]->set_var("SectGotourlOther", "");
				$this->tpl[0]->set_var("SectFormMethod", "");
				$this->tpl[0]->set_var("SectFormActionUrl", "");
				$this->setVariables();

				$this->tpl[0]->parse("SectSubmit", false);
				break;
				
			case "submit":
				$this->tpl[0]->set_var("type", "button");
				$component_action = $this->component_action;
				if ($this->component_action === NULL)
				{
					if ($this->parent !== NULL && strlen($this->parent[0]->id))
						$component_action = $this->parent[0]->id;
				}
				if (strlen($component_action))
					$component_action_ = $component_action . "_";

				if ($this->jsaction) 
				{
					$this->tpl[0]->set_var("jsaction", $this->process_tags($this->jsaction));
				} 
				elseif ($this->ajax)
				{
					if($this->ajax !== true)
					{
						$this->tpl[0]->set_var("jsaction", "ff.ffPage.dialog.doOpen('" . $this->ajax . "', '" . $url . "');");
					} 
					elseif($this->parent_page[0] !== null)
					{
						$this->parent_page[0]->tplAddJs("ff.ajax");
				
						if($this->parent_page[0]->getXHRCtx() !== false)
							$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxDoAction('" . $this->parent_page[0]->getXHRCtx() . "', '" . $this->processed_form_action . "', '" . $component_action_ . "')");
						else
							$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'component' : '" . $component_action . "', 'action' : '" . $component_action_ . $this->processed_form_action . "'})");
					} 
					else 
					{
						$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'component' : '" . $component_action . "', 'action' : '" . $component_action_ . $this->processed_form_action . "'})"); // TOCHECK: verificare l'IF if($this->parent_page[0] !== null)
					}
				} 
				else 
				{
					$this->tpl[0]->set_var("frmAction", $this->processed_form_action);

					if($component_action_)
						$this->tpl[0]->set_var("component_action", $component_action_);
					
					$this->tpl[0]->parse("SectFormAction", false);
					
					$this->tpl[0]->set_var("jsaction", "jQuery(this).closest('form').submit();");  
				}					
				
				if ($this->form_method === NULL)
					$this->tpl[0]->set_var("SectFormMethod", "");
				else
				{
					$this->tpl[0]->set_var("form_method", $this->form_method);
					$this->tpl[0]->parse("SectFormMethod", false);
				}

				if ($this->form_action_url === NULL)
					$this->tpl[0]->set_var("SectFormActionUrl", "");
				else
				{
					$this->tpl[0]->set_var("form_action_url", $this->processed_form_action_url);
					$this->tpl[0]->parse("SectFormActionUrl", false);
				}
				$this->setVariables();
				$this->tpl[0]->parse("SectSubmit", false);
				$this->tpl[0]->set_var("SectGotourlSelf", "");
				$this->tpl[0]->set_var("SectGotourlOther", "");
				break;
			case "gotourl":
				$this->tpl[0]->set_var("type", "button");
				if ($this->ajax)
				{	
					$this->tpl[0]->set_var("url", "javascript:void(0)");
					/*if($this->ajax !== true)
					{
						$this->tpl[0]->set_var("jsaction", "ff.ffPage.dialog.doOpen('" . $this->ajax . "', '" . $url . "');");
					} 
					else*/if($this->parent_page[0] !== null)
					{
						$this->parent_page[0]->tplAddJs("ff.ajax");
						if($this->frmAction)				
						{
							if($this->parent_page[0]->getXHRCtx() !== false)
								$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxDoAction('" . $this->parent_page[0]->getXHRCtx() . "', '" . $this->frmAction . "');");
							else
								$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "', 'action' : '" . $this->frmAction . "'});");
						} 
						else
						{
							if($this->parent_page[0]->getXHRCtx() !== false)
								$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxGoToUrl('" . $this->parent_page[0]->getXHRCtx() . "');");
							else
								$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "'});");
						}
					} 
					else 
					{
						$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "', 'action' : '" . $this->frmAction . "'});"); // TOCHECK: verificare l'IF if($this->parent_page[0] !== null)
					}		
					$this->tpl[0]->parse("SectSubmit", false);		
				} 
				else
				{			
					$this->tpl[0]->set_var("url", ffCommon_specialchars($url));
					$this->tpl[0]->set_var("SectSubmit", "");
				}
							
				if ($this->target == "_self") 
				{
					$this->tpl[0]->set_var("SectGotourlOther", "");
					$this->tpl[0]->parse("SectGotourlSelf", false);
				}
				else
				{
					$this->tpl[0]->set_var("target", $this->target);
					$this->tpl[0]->set_var("attributes", $this->attributes);
					$this->tpl[0]->set_var("title", $this->label);
					$this->tpl[0]->set_var("SectGotourlSelf", "");
					$this->tpl[0]->parse("SectGotourlOther", false);
				}
				break;
		}
	}
	
	/**
	* esegue il process di pulsanti con aspect = "link"
	* @param String l'url utilizzato (vedi url)
	*/
	function process_link($url)
	{
		$this->tpl[0]->set_var("target", $this->target);
		$this->tpl[0]->set_var("attributes", $this->attributes);
		//$this->tpl[0]->set_var("title", $this->label); // moved inside getImage
		
		$img = $this->getImage(true);
		$this->tpl[0]->set_var("path", $img);
		if (strlen($img))
			$this->tpl[0]->parse("SectImage", false);
		else
			$this->tpl[0]->set_var("SectImage", "");
		
		$this->tpl[0]->set_var("path", $img);
		
		if ($this->image)
			$this->tpl[0]->parse("SectImage", false);
		else
			$this->tpl[0]->set_var("SectImage", "");
		if ($this->form_action_url === NULL)
			$this->tpl[0]->set_var("SectFormActionUrl", "");
		else
		{
			$this->tpl[0]->set_var("form_action_url", $this->processed_form_action_url);
			$this->tpl[0]->parse("SectFormActionUrl", false);
		}
		
		switch($this->action_type)
		{
			case "none":
				$this->tpl[0]->set_var("url", "javascript:void(0)");
			
				$this->tpl[0]->set_var("SectFormAction", "");
				$this->tpl[0]->set_var("SectFormActionUrl", "");
				
				$this->setVariables();
				
				$this->tpl[0]->parse("SectSubmit", false);
				break;
				
			case "submit":
				$this->tpl[0]->set_var("url", "javascript:void(0)");

				$component_action = $this->component_action;
				if ($this->component_action === NULL)
				{
					if ($this->parent !== NULL && strlen($this->parent[0]->id))
						$component_action = $this->parent[0]->id;
				}
				if (strlen($component_action))
					$component_action_ = $component_action . "_";

				if ($this->jsaction) 
				{
					$this->tpl[0]->set_var("jsaction", $this->process_tags($this->jsaction));
				} 
				elseif ($this->ajax)
				{
					/*if($this->ajax !== true)
					{
						$this->tpl[0]->set_var("jsaction", "ff.ffPage.dialog.doOpen('" . $this->ajax . "', '" . $url . "');");
					} 
					else*/if($this->parent_page[0] !== null)
					{
						$this->parent_page[0]->tplAddJs("ff.ajax");
				
						if($this->parent_page[0]->getXHRCtx() !== false)
							$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxDoAction('" . $this->parent_page[0]->getXHRCtx() . "', '" . $this->processed_form_action . "', '" . $component_action_ . "')");
						else
							$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'component' : '" . $component_action . "', 'action' : '" . $component_action_ . $this->processed_form_action . "'})");
					} 
					else 
					{
						$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'component' : '" . $component_action . "', 'action' : '" . $component_action_ . $this->processed_form_action . "'})"); // TOCHECK: verificare l'IF if($this->parent_page[0] !== null)
					}
				} 
				else 
				{
					$this->tpl[0]->set_var("frmAction", $this->processed_form_action);

					if($component_action_)
						$this->tpl[0]->set_var("component_action", $component_action_);
					
					$this->tpl[0]->parse("SectFormAction", false);
					
					$this->tpl[0]->set_var("jsaction", "jQuery(this).closest('form').submit();");  
				}
				
				$this->setVariables();
				$this->tpl[0]->parse("SectSubmit", false);
				break;

			case "gotourl":
				if ($this->ajax)
				{	
					$this->tpl[0]->set_var("url", "javascript:void(0)");
					if($this->ajax !== true)
					{
						$this->tpl[0]->set_var("jsaction", "ff.ffPage.dialog.doOpen('" . $this->ajax . "', '" . $url . "');");
					} 
					elseif($this->parent_page[0] !== null)
					{
						$this->parent_page[0]->tplAddJs("ff.ajax");
						if($this->frmAction)				
						{
							if($this->parent_page[0]->getXHRCtx() !== false)
								$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxDoAction('" . $this->parent_page[0]->getXHRCtx() . "', '" . $this->frmAction . "');");
							else
								$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "', 'action' : '" . $this->frmAction . "'});");
						} 
						else
						{
							if($this->parent_page[0]->getXHRCtx() !== false)
								$this->tpl[0]->set_var("jsaction", "ff.ajax.ctxGoToUrl('" . $this->parent_page[0]->getXHRCtx() . "');");
							else
								$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "'});");
						}
					} 
					else 
					{
						$this->tpl[0]->set_var("jsaction", "ff.ajax.doRequest({'url' : '" . $url . "', 'action' : '" . $this->frmAction . "'});"); // TOCHECK: verificare l'IF if($this->parent_page[0] !== null)
					}		
					$this->tpl[0]->parse("SectSubmit", false);		
				} 
				else
				{			
					$this->tpl[0]->set_var("url", ffCommon_specialchars($url));
					$this->tpl[0]->set_var("SectSubmit", "");
				}
				break;
		}
	}
	
	/**
	* imposta nel template le variabili personalizzate attinte da variables
	*/
	function setVariables()
	{
		if (is_array($this->variables) && count($this->variables))
		{
			foreach ($this->variables as $key => $value)
			{
				$this->tpl[0]->set_var("varname", $key);
				$this->tpl[0]->set_var("varvalue", ffCommon_specialchars($value));
				$this->tpl[0]->parse("SectSetVariable", true);
			}
		}
		else
			$this->tpl[0]->set_var("SectSetVariable", "");
	}
	
	/**
	* prepara in un array le variabili ottenute con i fied dell'eventuale componente associato
	* @param String l'id del componente
	* @param mixed la collezione di campi in oggetto
	* @return mixed
	*/
	function prepareCompVars($id, $fields)
	{
		$res = array();
		
		foreach ($fields as $key => $value)
		{
			$res[$id . "_" . $key] = $value;
		}
		
		return $res;
	}
	
	/**
	* elabora i tag dell'url (utile a fini JS). Sono consentiti i seguenti tag:
	* [[frmAction]]
	* [[frmAction_url]]
	* [[XHR_CTX_ID]]
	* [[component_action]]
	* [[component]]
	*/
	function process_tags($string)
	{
		$string = str_replace("[[frmAction]]", $this->processed_form_action, $string);
		$string = str_replace("[[frmAction_url]]", $this->processed_form_action_url, $string);
		$string = str_replace("[[XHR_CTX_ID]]", $_REQUEST["XHR_CTX_ID"], $string);
		if ($this->component_action === NULL)
		{
			if ($this->parent !== NULL && strlen($this->parent[0]->id))
				$string = str_replace("[[component_action]]", $this->parent[0]->id . "_", $string);
		}
		else
			$string = str_replace("[[component_action]]", $this->component_action . "_", $string);
		
		if ($this->parent !== NULL && strlen($this->parent[0]->id))
		{
			$string = str_replace("[[component]]", $this->parent[0]->id, $string);
			if (is_subclass_of($this->parent[0], "ffGrid_base"))
				$string = ffProcessTags($string, $this->parent[0]->key_fields, $this->parent[0]->grid_fields, "normal", $this->parent_page[0]->get_params(), rawurlencode($_SERVER['REQUEST_URI']), $this->parent_page[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST));
		}
		return $string;
	}
}
