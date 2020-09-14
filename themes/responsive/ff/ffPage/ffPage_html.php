<?php
class ffPage_html extends ffPage_base 
{
	/**
	 * Il suffisso della dir del layer
	 * il file HTML si chiamerà layer_$layer.html
	 * posizionato sotto la dir del tema, sottodir "layers"
	 * @var String
	 */
	var $layer 					= "empty";
	var $layer_dialog 			= false;

	/**
	 * Permette di alterare la directory di default da dove
	 * caricare il layer
	 * @var String
	 */
	var $layer_dir				= null;

	/**
	 * Contiene tutte le sezioni a disposizione del layout
	 * @var Array
	 */
	var $sections 				= array();

	var $js_browser_detection	= true;
	
	/**
	 * Se deve essere utilizzato il framework Javascript
	 * @var Boolean
	 */
	var $use_own_js				= true;

	
	/**
	 * Javascript di default del framework
	 * @var Boolean
	 */
	var $default_js	= array(
			"ff.ffPage" => null
		);
		
	var $widget_tabs_placeholder = null;
	var $widget_tabs_context = null;
	
	// loaded with libs.json
    var $libraries = null;
	
	var $default_css	= array(
		);
	
	var $libraries_css = array(
		);
                                    
	var $css_browser_detection		= false;	

	/**
	 * Abilita o disabilita l'utilizzo del form
	 * il default (null) lo considera abilitato
	 * @var Boolean
	 */
	var $use_own_form			= null;

    /**
	 * L'id del form di default
	 * @var String
	 */
	var $form_id                = "frmMain";
	/**
	 * il nome del form di default
	 * @var String
	 */
    var $form_name              = "frmMain";

	/**
	 * il metodo del form di default
	 * blank sarà autoselezionato sulla base dei componenti presenti nella pagina
	 * altrinenti può essere impostato a GET o POST
	 * @var String
	 */
	var $form_method			= "";

	/**
	 * L'azione da impostare con il form. Corrisponde all'url di destinazione
	 * @var String
	 */
	var $form_action			= "";

	/**
	 * L'enctype del form, se blank viene selezionato automaticamente
	 * @var String
	 */
	var $form_enctype			= "";
	
	/**
	 * Il tema di jquery.ui
	 * @var String
	 */
	var $jquery_ui_theme 		= "base";
	
	/**
	 * Il nome del template da caricare per l'utilizzo con ffPage
	 * @var String
	 */
	public $template_file 			= "ffPage.html";

	/**
	 * Un Array di variabili fisse da utilizzare per essere
	 * inserite all'interno del template
	 * nella forma coppia chiave/valore
	 * @var Array
	 */
	public $fixed_vars 				= array();

	/**
	 * Se i css devono essere compattati in un unico file
	 * @var Boolean
	 */
	public $compact_css 		   = false;
	public $css_buffer 			   = array();
    public $override_css           = array();
	/**
	 * I css caricati nella pagina, identificati da un TAG
	 * @var Array
	 */
	public $page_css				= array();
	
	public $compact_js 			   = false;
	public $js_buffer 			   = array();
    public $override_js            = array(); 
    
	/**
	 * I Javascript caricati nella pagina, identificati da un TAG
	 * @var Array
	 */
    public $page_js                	= array();
    public $page_tags               = array();
    
	public $compress			   	= false;
	
	public $minify					= "strip"; // HTML post-process, can be: false, strip, strong_strip, minify
	public $page_defer				= array(); // array keys js and css of all compress resources (js and css)
	/**
	 * I Meta caricati nella pagina, identificati da un TAG
	 * @var Array
	 */
    public $page_meta              = array();

	/**
	 * I Meta caricati nella pagina, identificati da un TAG
	 * @var Array
	 */
    public $page_html_attr         = array();
    
	/**
	 * Una classe da impostare sul tag BODY
	 * @var String
	 */
    public $class_body             = null;
	public $properties_body        = null;

    /**
     * Abilita i tab
     * @var Boolean
     */
    public $tab				= true;// false OR top OR left OR right
	/**
	 * Il risultato JSON della pagina
	 * @var Array
	 */
	var $json_result = array();
	
	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	/**
	 * l'oggetto ffTemplate usato per il layer
	 * @var Array
	 */
	var $tpl_layer				= null;
	
	
	var $browser = null;
	
	var $canonical = null;

	var $js_counter = 0;
	var $js_loaded = array();
	var $css_counter = 0;
	var $css_loaded = array();
	var $above_the_fold = null;
		
	/**
	 * Il costruttore, istanzia un nuovo oggetto ffPage
	 * @param String $site_path
	 * @param String $disk_path
	 * @param String $page_path
	 * @param String $theme
	 */
	public function __construct($site_path, $disk_path, $page_path, $theme)
	{
		parent::__construct($site_path, $disk_path, $page_path, $theme);

		//if ($this->libraries === null)
		//	$this->libraries = ffTheme_restricted_get_libs($this, "theme/ff/ffPage");
		
		$glob_libs = ffGlobals::getInstance("__ffTheme_libs__");

		if (ffIsset($glob_libs->libs, "theme/ff/ffPage"))
			$this->libsExtend($glob_libs->libs["theme/ff/ffPage"]);

		foreach ($glob_libs->libs as $key => $value)
		{
			if ($key === "theme/ff/ffPage")
				continue;

			$this->libsExtend($value);
		}
		
		$registry = ffGlobals::getInstance("_registry_");

		if (isset($registry->themes[$this->theme]))
		{
			ffTheme_html_construct($this, $this->theme);
		}

		if ($this->theme !== cm_getMainTheme() && (!isset($registry->themes[$this->theme]) || !isset($registry->themes[$this->theme]->exclude_main_theme_defaults)))
		{
			ffTheme_html_construct($this, cm_getMainTheme());
		}
	}

	/**
	 * Recupera la directory in cui è contenuto il layer
	 * @param String $layer_file Permette di specificare un nome file aggiuntivo da passare all'evento getLayerDir
	 * @return String
	 */
	function getLayerDir($layer_file = null)
	{
		if ($this->layer_dir !== null)
			return $this->layer_dir;
		
		$res = $this->doEvent("getLayerDir", array(&$this, $layer_file));
		$last_res = end($res);
		if ($last_res !== null)
			return $last_res;
		else
			return $this->disk_path . "/themes/" . $this->getTheme() . "/layouts";
	}

	/**
	 * Recupera la directory da cui caricare tutti gli elementi di layout
	 * @param String $layout_file Permette di specificare un nome file aggiuntivo da passare all'evento getLayoutDir
	 * @return String
	 */
	function getLayoutDir($layout_file = null)
	{
		$res = $this->doEvent("getLayoutDir", array(&$this, $layout_file));
		$last_res = end($res);
		if ($last_res !== null)
			return $last_res;
		else
			return $this->disk_path . "/themes/" . $this->getTheme() . "/layouts";
	}

	public function libsExtend($addon)
	{
		cm_libsExtend($this->libraries, $addon);
	}

	public function resetCSS()
	{
		$this->page_css = array();
		$this->css_buffer = array();
		$this->css_counter = 0;
		$this->css_loaded = array();
	}
	
	public function tplAddMultiCss($elements, $priority = null)
	{
		if ($priority === null)
		{
			foreach ($elements as $css_queue_key => $css_queue)
			{
				foreach ($css_queue AS $css_key => $css_value)
				{
					$this->tplAddCss(
							$css_key
							, ffParamsMerge(array("priority" => $css_queue_key), $css_value)
						);
				}
			}
		}
		else
		{
			foreach ($elements AS $css_key => $css_value)
			{
				$this->tplAddCss(
						$css_key
						, ffParamsMerge(array("priority" => $priority), $css_value)
					);
			}
		}
	}

	/**
	 * Aggiunge un CSS alla pagina
	 * @param String $tag
	 * @param String $file
	 * @param String $path
	 * @param String $css_rel di default è "stylesheet"
	 * @param String $css_type di default è "text/css"
	 * @param Boolean $overwrite
	 * @param Boolean $async
	 * @param String $css_media
	 * @param Boolean $exclude_compact
	 * @param String $priority la coda di priorità di caricamento del CSS, di default è "top"
	 * @return Boolean se l'aggiunta ha avuto successo o meno
	 */
    public function tplAddCss($tag, $params = null)
    {
        $file = null;
        $path = null;
        $css_rel = "stylesheet";
        $css_type = "text/css";
        $overwrite = false;
        $async = null;
        $css_media = null;
        $exclude_compact = false;
        $priority = cm::LAYOUT_PRIORITY_DEFAULT;
        $embed = null;
        $index = 0;
        $version = null;

        if ($params !== null && is_array($params))
        {
            if (ffIsset($params, "file"))				$file = $params["file"];
            if (ffIsset($params, "path"))				$path = $params["path"];
            if (ffIsset($params, "overwrite"))			$overwrite = $params["overwrite"];
            if (ffIsset($params, "rel"))				$css_rel = $params["rel"];
            if (ffIsset($params, "css_rel"))			$css_rel = $params["css_rel"];
            if (ffIsset($params, "type"))				$css_type = $params["type"];
            if (ffIsset($params, "css_type"))			$css_type = $params["css_type"];
            if (ffIsset($params, "overwrite"))			$overwrite = $params["overwrite"];
            if (ffIsset($params, "async"))				$async = $params["async"];
            if (ffIsset($params, "css_media"))			$css_media = $params["css_media"];
            if (ffIsset($params, "exclude_compact"))	$exclude_compact = $params["exclude_compact"];
            if (ffIsset($params, "priority"))			$priority = $params["priority"];
            if (ffIsset($params, "embed"))				$embed = $params["embed"];
            if (ffIsset($params, "index"))				$index = $params["index"];
            if (ffIsset($params, "version"))			$version = $params["version"];
        }

		/*if ($file === "jquery.css")
			ffErrorHandler::raise("DEBUG CSS", E_USER_ERROR, $this, get_defined_vars());
		if ($file === "jquery-ui.css")
			ffErrorHandler::raise("DEBUG CSS", E_USER_ERROR, $this, get_defined_vars());
		*/	
		if(!$this->jquery_ui_theme && strpos($tag, "jquery-ui.") === 0) {
			return true;
		}
		
		if (!$overwrite && ffIsset($this->css_loaded, $tag))
			return true;
		
		$tmp_async = ($async !== null ? $async : (
							$this->isXHR() ? true : false
						)
				);
		
		// before, check for libraries
		$deps = array(
				"js" => array()
				, "css" => array()
			);
		
		// before, check for libraries
		$lib_parts = explode(".", $tag);
		$lib_parts_last = array_pop($lib_parts); // exclude last (this)
		$tmp_css_deps = array();
		$last_ref = $this->libraries;
		$is_css_defs = false;
		for ($i = 0; $i < count($lib_parts); $i++)
		{
			$name = $lib_parts[$i];		
			$tmp_found = ffIsset($last_ref, $name);
			if (!$tmp_found)
			{
				$ret = $this->doEvent("tplAddCss_not_found", array($this, $tag, $params));
				$rc = end($ret);
				if ($rc)
					return;
			}
			
			if ($tmp_found)
			{
				if ($i === 0) // primo livello, controllo di versione
				{
					if (!ffIsset($last_ref[$name], "default"))
						ffErrorHandler::raise ("Malformed Libraries Structure", E_USER_ERROR, null, get_defined_vars());
					
					$lib_version = (is_null($version) ? $last_ref[$name]["default"] : $version);
					if (!ffIsset($last_ref[$name], $lib_version))
						ffErrorHandler::raise ("Version not found", E_USER_ERROR, null, get_defined_vars());

					$last_ref = $last_ref[$name][$lib_version];
				}
				else
					$last_ref = $last_ref[$name];
				
				$tmp_css_deps[] = $name;
				if (!ffIsset($last_ref, "empty") || !$last_ref["empty"])
					$deps[($is_css_defs ? "css" : "js")][0] = implode(".", $tmp_css_deps);

				if (ffIsset($last_ref, "css_defs"))
				{
					$is_css_defs = true;
					$last_ref = $last_ref["css_defs"];
				}
				else
					break;
			}
			else
				break;
		}
		
		if ($i > 0 && $i < count($lib_parts))
		{
			$last_ref = $this->libraries;
			$i = 0;
		}
		
		if ($i > 0)
		{
			$tmp_found = ffIsset($last_ref, $lib_parts_last);

			if (!$tmp_found)
			{
				$ret = $this->doEvent("tplAddCss_not_found", array($this, $tag, $params));
				$rc = end($ret);
				if ($rc)
					return;
			}
				
			if (!$tmp_found)
			{
				$last_ref = $this->libraries[$lib_parts_last];
				$i = 0;
			}
			else
			{
				$found = true;
				$lib_data = $last_ref[$lib_parts_last];
			}
		}
		
		if ($i === 0 && ffIsset($last_ref, $tag))
		{
			if (!ffIsset($last_ref[$tag], "default"))
				ffErrorHandler::raise ("Malformed Libraries Structure", E_USER_ERROR, null, get_defined_vars());

			$lib_version = (is_null($version) ? $last_ref[$tag]["default"] : $version);
			if (!ffIsset($last_ref[$tag], $lib_version))
				ffErrorHandler::raise ("Version not found", E_USER_ERROR, null, get_defined_vars());

			$found = true;
			$lib_data = $last_ref[$tag][$lib_version];
		}

		// load base deps
		if (count($deps["js"]))
		{
			$ret = $this->tplAddJs($deps["js"][0]);
			/*foreach ($deps["js"] as $tmp)
			{
				$ret = $this->tplAddJs($tmp);
				//$deps["js"] = array_merge($deps["js"], $ret);
			}*/
		}
		if (count($deps["css"]))
		{
			$ret = $this->tplAddCss($deps["css"][0]);
			/*foreach ($deps["js"] as $tmp)
			{
				$ret = $this->tplAddJs($tmp);
				//$deps["js"] = array_merge($deps["js"], $ret);
			}*/
		}
		
		// got library! we can replace defaults
		if ($found)
		{
			if (ffIsset($lib_data, "js_deps"))
			{
				foreach ($lib_data["js_deps"] as $js_key => $js_value)
				{
					if ($js_key === "_//_")
						continue;
					
					if ($js_value !== false && !ffIsset($js_value, "embed"))
						$deps["js"][] = (strpos($js_key, ".") === 0 ? $tag : "") . $js_key;

					$ret = $this->tplAddJs(
							(strpos($js_key, ".") === 0 ? $tag : "") . $js_key
							, ffParamsMerge(array("async" => $tmp_async), ($js_value === false ? null : $js_value))
						);
					//$deps["js"] = array_merge($deps["js"], $ret);
				}
			}
			
			if (ffIsset($lib_data, "css_deps"))
			{
				foreach ($lib_data["css_deps"] as $css_key => $css_value)
				{
					$tmp_tag = (strpos($css_key, ".") === 0 ? $tag : "") . $css_key;
					if(!(!$this->jquery_ui_theme && strpos($tmp_tag, "jquery-ui.") === 0)) {
						$deps["css"][] = $tmp_tag;
						$ret = $this->tplAddCss(
								$tmp_tag
								, ffParamsMerge(array("async" => $tmp_async), $css_value)
							);
					}
				}
			}
			
			if (!ffIsset($lib_data, "empty") || !$lib_data["empty"])
			{
                if (!ffIsset($params, "file")) $file = (ffIsset($lib_data, "file") ? $lib_data["file"] : null);
                if (!ffIsset($params, "path")) $path = (ffIsset($lib_data, "path") ? $lib_data["path"] : null);
                if (!ffIsset($params, "overwrite")) $overwrite = (ffIsset($lib_data, "overwrite") ? $lib_data["overwrite"] : false);
                if (!ffIsset($params, "async")) $tmp_async = (ffIsset($lib_data, "async") ? $lib_data["async"] : $tmp_async);
                if (!ffIsset($params, "embed")) $embed = (ffIsset($lib_data, "embed") ? $lib_data["embed"] : null);
                if (!ffIsset($params, "exclude_compact")) $exclude_compact = (ffIsset($lib_data, "exclude_compact") ? $lib_data["exclude_compact"] : false);
                if (!ffIsset($params, "priority")) $priority = (ffIsset($lib_data, "priority") ? $lib_data["priority"] : cm::LAYOUT_PRIORITY_HIGH);
                if (!ffIsset($params, "index")) $index = (ffIsset($lib_data, "index") ? $lib_data["index"] : 0);
			}
		}
				
		$this->css_loaded[$tag] = true;

        if (
            (!ffIsset($lib_data, "empty") || !$lib_data["empty"])
            || $file !== null || $path !== null
        )
        {
			// found previous occourrence
			foreach ($this->page_css AS $css_queue_key => $css_queue)
			{
				foreach ($css_queue AS $css_key => $css_value)
				{
					if (
							$css_key == $tag
							/*|| (
								$css_value["path"] == $path
								&& $css_value["file"] == $file
								&& $css_value["path"] !== null
								&& $css_value["file"] !== null
							)*/
						)
					{
						$found_tag = $css_key;
						$found_queue = $css_queue_key;
						break;
					}
				}
			}
			
			if ($found_tag)
			{
				if ($overwrite)
					unset($this->page_css[$found_queue][$found_tag]);
				else
					return true;
					//ffErrorHandler::raise ("Duplicated Element", E_USER_ERROR, null, get_defined_vars());
			}
			
			if (!$found || $file !== null && $path !== null)
			{
				if ($priority === CM::LAYOUT_PRIORITY_TOPLEVEL && count($this->page_css[CM::LAYOUT_PRIORITY_TOPLEVEL]))
						ffErrorHandler::raise("TOPLEVEL CSS already exsts", E_USER_ERROR, null, get_defined_vars());
				
				if ($priority === CM::LAYOUT_PRIORITY_FINAL && count($this->page_css[CM::LAYOUT_PRIORITY_FINAL]))
						ffErrorHandler::raise("FINAL CSS already exsts", E_USER_ERROR, null, get_defined_vars());
				
				$this->css_counter++;
				//if ($file == "ff.js") ffErrorHandler::raise("asd", E_USER_ERROR, $this, get_defined_vars());
				
				$this->page_css[$priority][$tag] = array(
						"path" => $path
						, "file" => $file
						, "rel"  => $css_rel
						, "type" => $css_type
						, "async" => $tmp_async
						, "media" => $css_media
						, "exclude_compact" => $exclude_compact
						, "embed" => $embed
						, "index" => $index
						, "counter" => $this->css_counter * -1
						, "version" => $lib_version
					);
			}
		}
		
		if ($tag === "jquery-ui.core")
		{
			$this->tplAddCss(
				"jquery-ui.theme"
				, array(
					"file" => $this->jquery_ui_theme . "/theme.css"
					, "path" => "/themes/library/jquery-ui.themes"
					, "css_rel" => "stylesheet"
					, "css_type" => "text/css"
					, "async" => $tmp_async
					, "priority" => cm::LAYOUT_PRIORITY_HIGH
					, "index" => 199
				)
			);
		}
		
		if ($found)
		{
			if (ffIsset($lib_data, "js_loads"))
			{
				foreach ($lib_data["js_loads"] as $js_key => $js_value)
				{
					if ($js_key === "_//_")
						continue;
					
					$tmp_values = array(
						"async" => $tmp_async
					);
					$tmp_key = $js_key;
					
					if (strpos($js_key, ".") === 0)
					{
						$tmp_values["priority"] = $priority;
						$tmp_values["index"] = $index;
						$tmp_key = $tag . $js_key;
					}
					
					//$deps["js"][] = $js_key;
					$ret = $this->tplAddJs(
							$tmp_key
							, ffParamsMerge($tmp_values, $js_value)
						);
					//$deps["js"] = array_merge($deps["js"], $ret);
				}
			}

			if (ffIsset($lib_data, "css_loads"))
			{
				foreach ($lib_data["css_loads"] as $css_key => $css_value)
				{
					//$deps["css"][] = $css_key;
					//$this->page_js[$priority][$tag]["deps"]["css"][] = $css_key;
					$tmp_values = array(
						"async" => $tmp_async
					);
					$tmp_key = $css_key;
					
					if (strpos($css_key, ".") === 0)
					{
						$tmp_values["priority"] = $priority;
						$tmp_values["index"] = $index;
						$tmp_key = $tag . $css_key;
					}
					
					$ret = $this->tplAddCss(
							$tmp_key
							, ffParamsMerge($tmp_values, $css_value)
						);
				}
			}
		}
		
		return true;
	}
	
	public function resetJS()
	{
		$this->page_js = array();
		$this->js_buffer = array();
		$this->js_counter = 0;
		$this->js_loaded = array();
	}
	
	public function tplAddMultiJS($elements, $priority = null)
	{
		if ($priority === null)
		{
			if (!is_array($elements))
				ffErrorHandler::raise ("Wrong elements", E_USER_ERROR, $this, get_defined_vars());
			
			foreach ($elements as $js_queue_key => $js_queue)
			{
				foreach ($js_queue AS $js_key => $js_value)
				{
					if ($js_key === "_//_")
						continue;
					
					$this->tplAddJs(
							$js_key
							, ffParamsMerge(array("priority" => $js_queue_key), $js_value)
						);
				}
			}
		}
		else
		{
			if (!is_array($elements))
				ffErrorHandler::raise ("Wrong elements", E_USER_ERROR, $this, get_defined_vars());
			foreach ($elements AS $js_key => $js_value)
			{
				if ($js_key === "_//_")
					continue;
				
				$this->tplAddJs(
						$js_key
						, ffParamsMerge(array("priority" => $priority), $js_value)
					);
			}
		}
	}
	
    /**
	 * Aggiunge un Javascript alla pagina
	 * @param String $tag un nome univoco
	 * @param String $file il nome del file
	 * @param String $path il percorso del file
	 * @param Boolean $overwrite se deve sovrascrivere se già presente
	 * @param Boolean $async se deve essere caricato in modo asincrono, tramite il framework JS
	 * @param Boolean $embed specifica l'attributo EMBED
	 * @return Boolean l'esito dell'operazione
	 */
    public function tplAddJs($tag, $file = null, $path = null, $overwrite = false, $async = null, $embed = null, $exclude_compact = false, $priority = cm::LAYOUT_PRIORITY_DEFAULT, $index = 0, $version = null)
    {
		/*$globals = ffGlobals::getInstance();
		if ($globals->test)
			ffErrorHandler::raise ("ASD", E_USER_ERROR, $this, get_defined_vars ());*/

		if ($file !== null && is_array($file))
		{
			$params = $file;
			$file = null;
			if (ffIsset($params, "file"))				$file = $params["file"];
			if (ffIsset($params, "path"))				$path = $params["path"];
			if (ffIsset($params, "overwrite"))			$overwrite = $params["overwrite"];
			if (ffIsset($params, "async"))				$async = $params["async"];
			if (ffIsset($params, "embed"))				$embed = $params["embed"];
			if (ffIsset($params, "exclude_compact"))	$exclude_compact = $params["exclude_compact"];
			if (ffIsset($params, "priority"))			$priority = $params["priority"];
			if (ffIsset($params, "index"))				$index = $params["index"];
			if (ffIsset($params, "version"))			$version = $params["version"];
		}
			
		if (!$overwrite && ffIsset($this->js_loaded, $tag))
			return $this->js_loaded[$tag];

		$this->js_loaded[$tag] = array(); // avoid infinite recursion
		
		$tmp_async = ($async !== null ? $async : (
							$this->isXHR() ? true : false
						)
				);

		// before, check for libraries
		$deps = array(
				"js" => array()
				, "css" => array()
			);

		$lib_parts = explode(".", $tag);
		$lib_parts_last = array_pop($lib_parts); // exclude last (this)
		$tmp_js_deps = array();
		$last_ref = $this->libraries;
		for ($i = 0; $i < count($lib_parts); $i++)
		{
			$name = $lib_parts[$i];
			$tmp_found = ffIsset($last_ref, $name);
			
			if (!$tmp_found)
			{
				$ret = $this->doEvent("tplAddJs_not_found", array($this, $tag, $params));
				$rc = end($ret);
				if ($rc)
					return;				
			}
			
			if ($tmp_found)
			{
				if ($i === 0) // primo livello, controllo di versione
				{
					if (!ffIsset($last_ref[$name], "default"))
						ffErrorHandler::raise ("Malformed Libraries Structure", E_USER_ERROR, null, get_defined_vars());
					
					$lib_version = (is_null($version) ? $last_ref[$name]["default"] : $version);
					if (!ffIsset($last_ref[$name], $lib_version))
						ffErrorHandler::raise ("Version not found", E_USER_ERROR, null, get_defined_vars());

//					$lib_data = $lib_value[$lib_version];
					$last_ref = $last_ref[$name][$lib_version];
				}
				else
					$last_ref = $last_ref[$name];
				
				$tmp_js_deps[] = $name;
				//$deps["js"][] = implode(".", $tmp_js_deps);
				if (!ffIsset($last_ref, "empty") || !$last_ref["empty"])
					$deps["js"][0] = implode(".", $tmp_js_deps);

				if (ffIsset($last_ref, "js_defs"))
					$last_ref = $last_ref["js_defs"];
				else
					break;
			}
			else
				break;
		}
		
		if (!$tmp_found)
		{
			$last_ref = $this->libraries;
			$i = 0;
		}
		
		if ($embed === null && $file === null && $path === null)
		{
			if ($tmp_found)
			{
				$tmp_found = ffIsset($last_ref, $lib_parts_last);
				if (!$tmp_found)
				{
					$ret = $this->doEvent("tplAddJs_not_found", array($this, $tag, $params));
					$rc = end($ret);
					if ($rc)
						return;
				}
					
				if (!$tmp_found)
				{
					$last_ref = $this->libraries[$lib_parts_last];
					$i = 0;
				}
				else
				{
					$found = true;
					$lib_data = $last_ref[$lib_parts_last];
				}
			}
		}
				
		if ($i === 0 && !$tmp_found && ffIsset($last_ref, $tag))
		{
			if (!ffIsset($last_ref[$tag], "default"))
				ffErrorHandler::raise ("Malformed Libraries Structure", E_USER_ERROR, null, get_defined_vars());

			$lib_version = (is_null($version) ? $last_ref[$tag]["default"] : $version);
			if (!ffIsset($last_ref[$tag], $lib_version))
				ffErrorHandler::raise ("Version not found", E_USER_ERROR, null, get_defined_vars());

			$found = true;
			$lib_data = $last_ref[$tag][$lib_version];
		}

		// eliminate parents
		/*if (count($deps["js"]))
			$deps["js"] = array(end($deps["js"]));*/
		
		// load base deps
		if (count($deps["js"]))
		{
			$ret = $this->tplAddJs($deps["js"][0]);
			/*foreach ($deps["js"] as $tmp)
			{
				$ret = $this->tplAddJs($tmp);
				//$deps["js"] = array_merge($deps["js"], $ret);
			}*/
		}
		
		// got library! we can load deps and replace defaults
		if ($found)
		{
			if (ffIsset($lib_data, "js_deps"))
			{
				foreach ($lib_data["js_deps"] as $js_key => $js_value)
				{
					if ($js_key === "_//_")
						continue;
					
					if ($js_value !== false && !ffIsset($js_value, "embed"))
						$deps["js"][] = (strpos($js_key, ".") === 0 ? $tag : "") . $js_key;
						
					$ret = $this->tplAddJs(
							(strpos($js_key, ".") === 0 ? $tag : "") . $js_key
							, ffParamsMerge(array("async" => $tmp_async), ($js_value === false ? null : $js_value))
						);
					//$deps["js"] = array_merge($deps["js"], $ret);
				}
			}
			
			if (ffIsset($lib_data, "css_deps"))
			{
				foreach ($lib_data["css_deps"] as $css_key => $css_value)
				{
					$tmp_tag = (strpos($css_key, ".") === 0 ? $tag : "") . $css_key;
					if(!(!$this->jquery_ui_theme && strpos($tmp_tag, "jquery-ui.") === 0)) {
						$deps["css"][] = $tmp_tag;
						$ret = $this->tplAddCss(
								$tmp_tag
								, ffParamsMerge(array("async" => $tmp_async), $css_value)
							);
					}
				}
			}
			
			if (!ffIsset($lib_data, "empty") || !$lib_data["empty"])
			{
				$file = (ffIsset($lib_data, "file") ? $lib_data["file"] : null);
				$path = (ffIsset($lib_data, "path") ? $lib_data["path"] : null);
				//$overwrite = (ffIsset($lib_data, "overwrite") ? $lib_data["overwrite"] : false);
				$tmp_async = (ffIsset($lib_data, "async") ? $lib_data["async"] : $tmp_async);
				$embed = (ffIsset($lib_data, "embed") ? $lib_data["embed"] : null);
				$exclude_compact = (ffIsset($lib_data, "exclude_compact") ? $lib_data["exclude_compact"] : false);
				$priority = (ffIsset($lib_data, "priority") ? $lib_data["priority"] : cm::LAYOUT_PRIORITY_HIGH);
				$index = (ffIsset($lib_data, "index") ? $lib_data["index"] : 0);
			}
		}
		
		$deps["js"] = array_unique($deps["js"]);
		$this->js_loaded[$tag] = $deps;
		
		if (($found && (!ffIsset($lib_data, "empty") || !$lib_data["empty"])) || (!$found && ($embed !== null || $file !== null || $path !== null)))
		{
			foreach ($this->page_js AS $js_queue_key => $js_queue)
			{
				foreach ($js_queue AS $js_key => $js_value)
				{
					if (
							$js_key == $tag
						)
					{
						$found_tag = $js_key;
						$found_queue = $js_queue_key;
						break;
					}
				}
			}

			if ($found_tag)
			{
				if ($overwrite)
					unset($this->page_js[$found_queue][$found_tag]);
				else
					return $deps;
			}

			if ($priority === CM::LAYOUT_PRIORITY_TOPLEVEL && count($this->page_js[CM::LAYOUT_PRIORITY_TOPLEVEL]))
					ffErrorHandler::raise("TOPLEVEL JS already exsts", E_USER_ERROR, null, get_defined_vars());

			if ($priority === CM::LAYOUT_PRIORITY_FINAL && count($this->page_js[CM::LAYOUT_PRIORITY_FINAL]))
					ffErrorHandler::raise("FINAL JS already exsts", E_USER_ERROR, null, get_defined_vars());

			$this->js_counter++;

			$this->page_js[$priority][$tag] = array(
					"path" => $path
					, "file" => $file
					, "async" => $tmp_async
					, "embed" => $embed
					, "exclude_compact" => $exclude_compact
					, "index" => $index
					, "counter" => $this->js_counter * -1
					, "version" => $lib_version
					, "deps" => $deps
				);
		}
		
		if ($found)
		{
			if (ffIsset($lib_data, "js_loads"))
			{
				foreach ($lib_data["js_loads"] as $js_key => $js_value)
				{
					if ($js_key === "_//_")
						continue;
					
					$tmp_values = array(
						"async" => $tmp_async
					);
					$tmp_key = $js_key;
					
					if (strpos($js_key, ".") === 0)
					{
						$tmp_values["priority"] = $priority;
						$tmp_values["index"] = $index;
						$tmp_key = $tag . $js_key;
					}
					
					$ret = $this->tplAddJs(
							$tmp_key
							, ffParamsMerge($tmp_values, $js_value)
						);
					//$deps["js"] = array_merge($deps["js"], $ret);
				}
			}

			if (ffIsset($lib_data, "css_loads"))
			{
				foreach ($lib_data["css_loads"] as $css_key => $css_value)
				{
					//$deps["css"][] = $css_key;
					//$this->page_js[$priority][$tag]["deps"]["css"][] = $css_key;
					$tmp_values = array(
						"async" => $tmp_async
					);
					$tmp_key = $css_key;
					
					if (strpos($css_key, ".") === 0)
					{
						$tmp_values["priority"] = $priority;
						$tmp_values["index"] = $index;
						$tmp_key = $tag . $css_key;
					}
					
					$ret = $this->tplAddCss(
							$tmp_key
							, ffParamsMerge($tmp_values, $css_value)
						);
				}
			}
		}
			
		return $deps;
    }

    /**
	 * Aggiunge un META-TAG alla pagina
	 * @param String $name il nome del meta
	 * @param String $content il contenuto
	 * @param Boolean $overwrite se deve sovrascrivere se già presente, di default false
	 * @param String $type l'attribugo type del meta, di default "name"
	 * @return Boolean l'esito dell'azione
	 */
	public function tplAddMeta($name, $content, $overwrite = false, $type = "name")
    {
		if (!isset($this->page_meta[$name]) || $overwrite)
		{
			$this->page_meta[$name] = array(
										"name" => $name
										, "content" => $content
										, "type" => $type
									);
			return true;
		}
		else
			return false;
    }

	public function tplAddTag($type, $params = array())
	{
		switch($type) 
		{
			case "canonical":
				$params["rel"] = "canonical";
				$tag = "link";
				break;
			case "next":
				$params["rel"] = "next";
				$tag = "link";
				break;
			case "prev":
				$params["rel"] = "prev";
				$tag = "link";
				break;
			case "alternate":
				$params["rel"] = "alternate";
				$tag = "link";
				break;
			case "favicon":
			case "icon":
				$params["rel"] = "icon";
				$tag = "link";
				break;
			default:
				$tag = $type;
		}			

		$this->page_tags[$tag][] = $params;
		return true;
	}
	    
    /**
	 * Aggiunge un Attributo al TAG html
	 * @param String $name il nome dell'attributo
	 * @param String $content il contenuto
	 * @param Boolean $overwrite se deve sovrascrivere se già presente, di default false
	 * @param String $type l'attribugo type del meta, di default "name"
	 * @return Boolean l'esito dell'azione
	 */
	public function tplAddHtmlAttr($content, $overwrite = false, $type = "xmlns")
    {
		if (!isset($this->page_html_attr[$content]) || $overwrite)
		{
			$this->page_html_attr[$content] = array(
										"content" => $content
										, "type" => $type
									);
			return true;
		}
		else
			return false;
    }
    
	public function tplLoad($tpl = null)
	{
		if ($this->template_loaded)
			return;
		
		$this->template_loaded = true;
		if ($tpl === null)
		{
			if ($this->getXHRCtx() && $this->template_file === "ffPage.html")
			{
				$tmp = $this->getTemplateDir("ffPage_dialog.html");
				if ($tmp !== null)
				{
					$this->tpl[0] = ffTemplate::factory($tmp);
					$this->tpl[0]->load_file("ffPage_dialog.html", "main");
				}
				else
				{
					$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
					$this->tpl[0]->load_file($this->template_file, "main");
				}
			}
			else 
			{
				$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
				$this->tpl[0]->load_file($this->template_file, "main");
			}
		}
		else
			$this->tpl[0] = $tpl;

		$this->tpl[0]->compress = $this->compress;
		$this->tpl[0]->minify = $this->minify;

		// ff.js
		$this->doEvent("on_tpl_load", array($this, $this->tpl));		

		$this->tplProcessVars($this->tpl);

		$this->tplParseHidden();

		if (is_array($this->globals) && count($this->globals))
		{
			foreach ($this->globals as $key => $value)
			{
				if (!$value["display_hidden"])
					continue;

				$this->tpl[0]->set_var("varname", $key);
				$this->tpl[0]->set_var("varvalue", $this->retrieve_global($key));
				$this->tpl[0]->parse("SectFormHidden", true);
				$this->tpl[0]->parse("SectHiddenFields", false);
			}
			reset($this->globals);
		}

		$this->doEvent("on_tpl_loaded", array(&$this, $this->tpl));
	}
	
	function tplLoadLayer($tpl = null)
	{
		// LAYER SECTION
		if ($this->template_layer_loaded)
			return;
		
		$this->template_layer_loaded = true;
		if (strlen($this->layer) && (!$this->isXHR() || $this->layer_dialog))
		{
			$this->tpl_layer[0] = ffTemplate::factory($this->getLayerDir("layer_" . $this->layer  . ".html"));
			if ($tpl === null)
				$this->tpl_layer[0]->load_file("layer_" . $this->layer  . ".html", "main");
			else
				$this->tpl_layer[0] = $tpl;

			//$this->tpl_layer[0]->strip_extra_newlines = $this->strip_extra_newlines;

			$res = $this->doEvent("on_tpl_layer_load", array(&$this, $this->tpl_layer[0]));

			$this->tplProcessVars($this->tpl_layer);
			$this->tplSetGlobals($this->tpl_layer);

			$res = $this->doEvent("on_tpl_layer_loaded", array(&$this, $this->tpl_layer[0]));

			// SECTIONS
			if (!$this->isXHR())
			{
				if (property_exists("ffPage_html", "navbar") && strlen($this->navbar))
				{
					ffErrorHandler::raise("Obsolete use of ->navbar, use ->sections[\"navbar\"] instead", E_USER_ERROR, $this, get_defined_vars());
				}

				if (property_exists("ffPage_html", "topbar") && strlen($this->topbar))
				{
					ffErrorHandler::raise("Obsolete use of ->topbar, use ->sections[\"topbar\"] instead", E_USER_ERROR, $this, get_defined_vars());
				}

				if (is_array($this->sections) && count($this->sections))
				{
					foreach ($this->sections as $key => $value)
					{
                        if ($value["is_php"])
                        {
                            if (strlen($value["name"]))
                            {
                                ob_start();
                                require($value["name"]);
                                $this->tpl[0]->set_var($key, ob_get_contents());
                                $this->tpl_layer[0]->set_var($key, ob_get_contents());
                                ob_end_clean();
                            }
                        }
                        else
                        {
                            if ($value["dir"] === null)
                                $this->sections[$key]["tpl"] = ffTemplate::factory($this->getLayoutDir($key . ".html"));
                            else
                                $this->sections[$key]["tpl"] = ffTemplate::factory($value["dir"]);

                            $this->sections[$key]["tpl"]->load_file($key . ".html", "main");

                            //$this->sections[$key]["tpl"]->strip_extra_newlines = $this->strip_extra_newlines;

                            $this->tplProcessVars(array(&$this->sections[$key]["tpl"]));
                            $this->tplSetGlobals(array(&$this->sections[$key]["tpl"]));

                            if ($this->sections[$key]["events"] === null) {
                                $this->sections[$key]["events"] = new ffEvents();
                            }

                            $this->sections[$key]["events"]->doEvent("on_load_template", array(&$this, &$this->sections[$key]["tpl"], $value["attributes"]));
                        }

					}
					reset($this->sections);
				}
			}
		}
	}

	public function tplParseHidden()
	{
		$this->tpl[0]->set_var("SectFormHidden", "");
		if (is_array($this->hidden_fields) && count($this->hidden_fields))
		{
			foreach ($this->hidden_fields as $key => $value)
			{
				$this->tpl[0]->set_var("varname", $key);
				if (is_object($value["field"]))
				{
					switch (get_class($value["field"]))
					{
						case "ffField":
						case "ffData":
							$this->tpl[0]->set_var("varvalue", $value["field"]->getValue($value["type"], $value["locale"]));
							break;
							
						default:
							$this->tpl[0]->set_var("varvalue", $value["field"]);
					}
				}
				else
					$this->tpl[0]->set_var("varvalue", $value["field"]);
				$this->tpl[0]->parse("SectFormHidden", true);
				$this->tpl[0]->parse("SectHiddenFields", false);
			}
			reset($this->hidden_fields);
		}
	}

	/**
	 * Elabora i componenti di transizione, cioè quei componenti segnalati ma non presenti nella pagina
	 */
	protected function tplProcessBounceComponents()
	{
		$varcount = FALSE;
		if (is_array($this->bounce_components) && count($this->bounce_components))
		{
			foreach ($this->bounce_components as $key => $value)
			{
				if (isset($this->params[$key]) && is_array($this->params[$key]) && count($this->params[$key]))
				{
					$varcount = true;
					foreach ($this->params[$key] as $subkey => $subvalue)
					{
						if (is_array($subvalue))
						{
							$this->tplProcessBounceArray($subvalue, $key, $subkey);
						}
						elseif ($subkey !== "frmAction")
						{
							$this->tpl[0]->set_var("varname", $key . "_" . $subkey);
							$this->tpl[0]->set_var("varvalue", $subvalue);
							$this->tpl[0]->parse("SectFormHidden", true);
						}
					}
					reset($this->params[$key]);
				}
			}
			reset($this->bounce_components);
		}
		return;
	}
		
	/**
	 * Elabora gli array di transizione per i bounce componente, funzione ricorsiva di supporto
	 */
	protected function tplProcessBounceArray($array, $prefix, $arrayname, $arraykeys = "")
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$this->tplProcessBounceArray($value, $prefix, $arrayname, $arraykeys . "[" . $key . "]");
			}
			else
			{
				$this->tpl[0]->set_var("varname", $prefix . "_" . $arrayname . $arraykeys . "[" . $key . "]");
				$this->tpl[0]->set_var("varvalue", $value);
				$this->tpl[0]->parse("SectFormHidden", true);
			}
		}
		reset($array);
	}
		
	/**
	 * Processa le variabili standard in un template associato alla pagina
	 * @param Array $tpl l'oggetto template
	 */
	protected function tplProcessVars($tpl)
	{
		$framework_css = cm_getFrameworkCss();
		$font_icon = cm_getFontIcon();

        if(__TOP_DIR__ != __PRJ_DIR__)
            $tpl[0]->set_var("base_path", substr($this->site_path, 0, strpos($this->site_path, "/domains/")));
        else
            $tpl[0]->set_var("base_path", $this->site_path);

        $tpl[0]->set_var("site_path", $this->site_path);
		$tpl[0]->set_var("language", FF_LOCALE);
		$tpl[0]->set_var("locale", strtolower(substr(FF_LOCALE, 0, 2)));
		$tpl[0]->set_var("framework_css", $framework_css["name"]);
		$tpl[0]->set_var("font_icon", $font_icon["name"]);

		$tpl[0]->set_var("theme", $this->theme);
		
		if (strlen($this->jquery_ui_theme)) {
			$tpl[0]->set_var("theme_ui", $this->jquery_ui_theme);
		} else {
			$tpl[0]->set_var("theme_ui", "");
		}
		
		$tpl[0]->set_var("layer", $this->layer);
        $tpl[0]->set_var("lazy_img", (CM_CACHE_IMG_LAZY_LOAD ? "true" : "false"));
		$tpl[0]->set_var("showfiles", CM_SHOWFILES);

		if(class_exists("Auth")) {
            $user = Auth::get("user");
            $tpl[0]->set_var("group", $user->acl_primary);
            $tpl[0]->parse("SectGroup", false);
        }

		$tpl[0]->set_var("encoded_this_url", rawurlencode($_SERVER['REQUEST_URI']));

		foreach ($this->global_params as $key => $value)
		{
			if (is_array($value))
			{
				$tmp = "";
				foreach ($value as $subkey => $subvalue)
				{
					if (!is_array($subvalue))
				    {
				        $tpl[0]->set_var("[VAR_$key\[$subkey]]", $subvalue);
				        $tpl[0]->set_var("[VAR_URL_$key\[$subkey]]", urlencode($subvalue));
				        $tmp .= $key . "[" . $subkey . "]=" . urlencode($subvalue) . "&";
					}
				}
				reset($value);
				$tpl[0]->set_var("[VAR_URL_$key]", $tmp);
			}
			else
			{
				$tpl[0]->set_var("[VAR_$key]", $value);
				$tpl[0]->set_var("[VAR_URL_$key]", urlencode($value));
			}
		}
		reset($this->global_params);

		$tpl[0]->set_var("query_string", $this->get_script_params());
		
		if (is_array($this->keys) && count($this->keys))
		{
			foreach ($this->keys as $key => $value)
			{
				$tpl[0]->set_var("_key_name", $key);
				$tpl[0]->set_var("_key_value", $value);
				$tpl[0]->parse("SectFormKeys", true);
			}
			reset($this->keys);
		}
		else
			$tpl[0]->set_var("SectFormKeys", "");

		if (is_array($this->fixed_vars) && count($this->fixed_vars))
		{
			foreach ($this->fixed_vars as $key => $value)
			{
				$tpl[0]->set_var($key, $value);
				if (strlen($value))
					$tpl[0]->parse("SectFixed_" . $key, false);
			}
			reset($this->fixed_vars);
		}
	}

	protected function parse_tags()
	{
		if(is_array($this->page_tags) && count($this->page_tags)) 
		{
			foreach($this->page_tags AS $type => $tags)
			{
				$this->tpl[0]->set_var("tag_type", $type);
				foreach($tags AS $attr)
				{
					$tag_properties = "";
					foreach($attr AS $attr_name => $attr_value) 
					{
						$tag_properties .= ' ' . $attr_name . '="' . $attr_value . '"';
					}
					$this->tpl[0]->set_var("tag_properties", $tag_properties);
					$this->tpl[0]->parse("SectTags", true);
				}
			}		
		}
	}
	
	/**
	 * Elabora i template e restituisce il risultato
	 * il risultato dipende dal formato (XHR, normale, etc)
	 * @param Boolean $output_result Se dev'essere eseguito l'output immediatamente
	 * @return Mixed il risultato dell'operazione
	 */
	protected function tplParse($output_result)
	{
		$this->doEvent("on_tpl_parse", array(&$this, $this->tpl[0]));

		$this->tpl[0]->set_var("title", strip_tags($this->title));
		$this->tpl[0]->set_var("properties_body", $this->getProperties());

		if ($this->canonical)
			$this->tplAddTag("canonical", array(
				"href" => $this->canonical
			));

		if(is_file(ff_getThemeDir("/favicon.ico") . "/favicon.ico"))
            $this->tplAddTag("favicon", array(
                "href" => FF_SITE_PATH . "/favicon.ico"
            ));

		if ($this->use_own_js)
		{
			$this->tplAddMultiJS($this->default_js, cm::LAYOUT_PRIORITY_HIGH);
			$this->tplAddMultiCss($this->default_css, cm::LAYOUT_PRIORITY_HIGH);
			$this->tplAddJs("ff.init", array(
					"async" => false
					, "embed" => "{FFJSINIT}"
					, "priority" => cm::LAYOUT_PRIORITY_HIGH
					, "index" => -1000
				));
		}
		
		$this->parse_css();
		$this->parse_tags();
        $this->parse_js();
        $this->parse_meta();
        $this->parse_html_attr();

        if ($this->use_own_js)
		{
			$tmp = $this->tpl[0]->rpparse("SectFFJS", false);
			$this->tpl[0]->ParsedBlocks["SectJs"] = str_replace("{FFJSINIT}", $tmp, $this->tpl[0]->ParsedBlocks["SectJs"]);
			foreach ($this->js_buffer as $key => $value)
			{
				if (ffIsset($value, "content") && $value["content"] === "{FFJSINIT}")
				{
					$this->js_buffer[$key]["content"] = $tmp;
					break;
				}
			}
		}
		$this->tpl[0]->set_var("SectFFJS", "");	

		$this->doEvent("on_tpl_parsed_header", array($this, $this->tpl[0]));		
		if ($this->isXHR())
		{
			if ($this->getXHRFormat() === false)
			{
				$this->output_buffer["headers"] = $this->tpl[0]->rpparse("SectHeaders", false) . $this->output_buffer["headers"];
				$this->output_buffer["footers"] .= $this->tpl[0]->rpparse("SectFooters", false);

				if (!$this->getXHRComponent())
				{
					$this->tpl[0]->set_var("SectHeaders", "");
					$this->tpl[0]->set_var("SectFooters", "");
					$this->tpl[0]->set_var("content", $this->output_buffer["html"]);
					$this->output_buffer["html"] = $this->tpl[0]->rpparse("main", false);
				}

				$this->doEvent("on_tpl_parsed", array(&$this));
			}

			cm::jsonParse(array_merge($this->json_result, $this->output_buffer), $output_result);
		}
		else
		{
			$this->tpl[0]->set_var("content", $this->output_buffer["html"]);
			
			if (strlen($this->output_buffer["headers"])) 
			{
				$this->tpl[0]->set_var("WidgetsContent", $this->output_buffer["headers"]);
				$this->tpl[0]->parse("SectWidgetsHeaders", true);
			}
			
			if (strlen($this->output_buffer["footers"])) 
			{
				$this->tpl[0]->set_var("WidgetsContent", $this->output_buffer["footers"]);
				$this->tpl[0]->parse("SectWidgetsFooters", true);
			}

			//$debug = $this->tpl[0];
			//ffErrorHandler::raise("ASD", E_USER_ERROR, $this, get_defined_vars());

			$this->doEvent("on_tpl_parsed", array(&$this));

			if ($output_result)
			{
				$this->tpl[0]->pparse("main", false);
			} 
			else 
			{
				return $this->tpl[0]->rpparse("main", false);
			}
		}
		return true;
	}

	/**
	 * Imposta le variabili globali all'interno di un template
	 * @param Array $tpl l'oggetto template implicato
	 */
	function tplSetGlobals($tpl)
	{
		if (is_array($this->globals) && count($this->globals))
		{
			foreach ($this->globals as $key => $value)
			{
				$keyval = $this->retrieve_global($key);
				$tpl[0]->set_var($key, $keyval);
			}
			reset($this->globals);
			$tpl[0]->set_var("globals", $this->get_globals(GET_GLOBALS_EXCLUDE_LIST));
		}
	}


	private function tplProcessData($data)
    {
        /*if (
            is_object($data)
            && (
                is_subclass_of($data, "ffGrid_base")
                || is_subclass_of($data, "ffRecord_base")
                || is_subclass_of($data, "ffDetails_base")
            )
        )
        {
            if ($data->use_own_location)
                return;

            $tmp_found = false;
            foreach (cm::getInstance()->applets_components as $applet_id => $applet_comps)
            {
                if (ffIsset($applet_comps, $data->id))
                {
                    $tmp_found = true;
                    break;
                }
            }

            if ($tmp_found)
                return;
        }*/

        $tmp = $this->getContentData($data);

        if (is_array($tmp))
        {
            //if ($this->isXHR()) {
            $output                         = $tmp["html"];
            $this->output_buffer["headers"] .= $tmp["headers"];
            $this->output_buffer["footers"] .= $tmp["footers"];
            /*}
            else
                $this->output_buffer["html"] .= $tmp["headers"] . $tmp["html"] . $tmp["footers"];*/
        }
        else
            $output = $tmp;

        return $output;
    }

	/**
	 * Elabora il template valorizzandone tutte le parti
	 */
	protected function tplProcess()
	{
		if ($this->getXHRCtx())
			$this->tpl[0]->set_var("ctx", $this->getXHRCtx());

		if (!(strlen($this->getXHRComponent()) && strlen($this->getXHRSection()))) // TODO: selective regeneration
			$this->struct_process();

		$this->doEvent("on_tpl_process", array(&$this, $this->tpl[0]));
		if ($this->use_own_form !== false)
		{
            $this->tpl[0]->set_var("form_id", $this->form_id);
            $this->tpl[0]->set_var("form_name", $this->form_name);
			$this->tpl[0]->set_var("form_method", (strlen($this->form_method) ? strtolower($this->form_method) : "get"));
			if($this->form_enctype)
				$this->tpl[0]->set_var("form_enctype", ' enctype="' . $this->form_enctype . '"');
			if($this->form_action)
				$this->tpl[0]->set_var("form_action", ' action="' . $this->form_action . '"');

			$this->tpl[0]->set_var("script_name", $this->get_script_name() . "?" . $this->get_script_params());
			
			$this->tpl[0]->parse("SectFormHeader", false);
			$this->tpl[0]->parse("SectFormFooter", false);
		}
		else
		{
			$this->tpl[0]->set_var("SectFormHeader", "");
			$this->tpl[0]->set_var("SectFormFooter", "");
		}

		if ($this->getXHRComponent())
		{
			if (!isset($this->components_buffer[$this->getXHRComponent()])) {
				ffErrorHandler::raise("Component Not Found", E_USER_ERROR, $this, get_defined_vars());
			} else {
				if ($this->getXHRFormat() === false)
					$this->output_buffer = $this->components_buffer[$this->getXHRComponent()];
				foreach ($this->components[$this->getXHRComponent()]->json_result as $key => $value)
				{
					if ($key == "refresh")
						$this->json_result["refresh"]	|=	$value;
					elseif ($key == "close")
						$this->json_result["close"]		|=	$value;
					elseif ($key == "insert_id")
						$this->json_result["insert_id"]	=	$value;
					else
						$this->json_result[$key]		=	$value;
				}
			}
		}
		else
		{
			if (!is_array($this->output_buffer)) //TODO: Fixare veramente :)
			{
				$this->output_buffer = array("html" => $this->output_buffer, "headers" => "", "footers" => "");
			}

			foreach ($this->contents as $key => $content)
			{
				if ($this->getXHRFormat() === "json")
				{
					foreach ($this->components[$key]->json_result as $subkey => $subvalue)
					{
						if ($subkey == "refresh")
							$this->json_result["refresh"]	|=	$subvalue;
						elseif ($subkey == "close")
							$this->json_result["close"]		|=	$subvalue;
						elseif ($subkey == "insert_id")
							$this->json_result["insert_id"]	=	$subvalue;
						else
							$this->json_result[$key][$subkey]		=	$subvalue;
					}
					
					continue;
				}
				
				if ($content["group"] === true)
				{
					if($this->tab)
					{
                        if (!count($this->groups[$key]["contents"]))
                            continue;

                        $this->groups[$key]["tab_mode"] = $this->tab;
                        $this->output_buffer["html"] .= $this->widgets["tabs"]->process($key, $this->groups[$key], $this);
                    } else {
					    if(is_array($this->groups[$key]["contents"]) && count($this->groups[$key]["contents"]))
                        {
                            foreach($this->groups[$key]["contents"] AS $group)
                            {
                                $output = "";
                                if(is_array($group["data"]) && count($group["data"]))
                                {
                                    foreach($group["data"] AS $data)
                                    {
                                        $output .= $this->tplProcessData($data);
                                    }

                                }
                                if($output)
                                {
                                    $this->output_buffer["html"] .= '<h4>' . $group["title"] . '</h4>' . $output;
                                }

                            }
                        }
                    }
				}
				else
				{
                    $this->output_buffer["html"]	.= $this->tplProcessData($content["data"]);
				}
			}
			reset($this->contents);

			$rc = $this->doEvent("on_fixed_process_before", array(&$this));
			if ($this->getXHRFormat() === false)
				$this->output_buffer["html"] = $this->fixed_pre_content . $this->output_buffer["html"] . $this->fixed_post_content;

			if (strlen($this->layer) && (!$this->isXHR() || $this->layer_dialog))
			{
				$this->tpl_layer[0]->set_var("content", $this->output_buffer["html"]);
				$this->output_buffer["html"] = $this->tpl_layer[0]->rpparse("main", false);
			}
		}
	}

	/**
	 * In base al contenuto, recupera di dati d'elaborazione ad esso associati
	 * @param Mixed $content
	 * @return Mixed 
	 */
	public function getContentData($content)
	{
		if (
				is_object($content)
				&& (
					   is_subclass_of($content, "ffGrid_base")
					|| is_subclass_of($content, "ffRecord_base")
					|| is_subclass_of($content, "ffDetails_base")
				)
			)
		{
			if ($content->display !== false /*&& !$content->use_own_location*/)
				return $this->components_buffer[$content->id];
			else
				return "";
		}
		elseif (
					is_object($content)
					&& (
						is_subclass_of($content, "ffField_base")
						|| is_subclass_of($content, "ffButton_base")
					)
			)
		{
			return $content->process();
		}
		elseif (
					is_object($content)
					&& get_class($content) == "ffData"
			)
		{
			return $content->getValue(null, FF_LOCALE);
		}
		elseif (
					is_object($content)
					&& get_class($content) == "ffTemplate"
			)
		{
			//cm::getInstance()->parseApplets($content);

			foreach($this->components as $key => $item)
			{
				$rc = false;

				if ($this->components[$key]->display !== false)
				{
					if ($this->components[$key]->location_name === null)
						$rc = $content->set_var($key, $this->components_buffer[$key]["html"]);
					else
						$rc = $content->set_var($this->components[$key]->location_name, $this->components_buffer[$key]["html"]);
				}

				if ($rc)
					$this->components_buffer[$key]["html"] = "";
			}
			reset($this->components);
			
			return $content->rpparse("main", false);
		}
		elseif (is_string($content))
		{
			return $content;
		}
		else
			ffErrorHandler::raise("Unhandled Content", E_USER_ERROR, $this, get_defined_vars());
	}
	
	/**
	 * Aggiunge una sezione all'array sections
	 * @param String $sName Il nome della sezione
	 */
	function addSection($sName)
	{
		if (!isset($this->sections[$sName]))
		{
			$this->sections[$sName] = array(
											  "dir" => null
											, "name" => ""
											, "tpl" => null
											, "is_php" => false
											, "events" => new ffEvents()
										);
			//$this->sections[$sName]["events"]->addEvent("on_load_template", "cm::oPage_on_process_parts", ffEvent::PRIORITY_HIGH);
		}
	}

	/**
	 * Elabora il template del layout e delle sezioni
	 */
	protected function tplProcessLayout()
	{
		$this->doEvent("on_tpl_layer_process", array(&$this, $this->tpl_layer[0]));

		// process components buffer
		foreach($this->components as $key => $item)
		{
			$rc = false;
			
			if (/*$this->components[$key]->use_own_location &&*/ $this->components[$key]->display !== false)
			{
				if ($this->components[$key]->location_name === null)
					$rc = $this->tpl_layer[0]->set_var($key, $this->components_buffer[$key]["html"]);
				else
					$rc = $this->tpl_layer[0]->set_var($this->components[$key]->location_name, $this->components_buffer[$key]["html"]);
			}
			
			if ($rc)
				$this->components_buffer[$key]["html"] = "";
				
			if (get_class($this->components[$key]) == "ffGrid_html" && $this->components[$key]->search_container !== null)
			{
				$this->sections[$this->components[$key]->search_container]["content"] .= $this->components[$key]->search_container_buffer;
			}
		}
		reset($this->components);

		if (is_array($this->sections) && count($this->sections))
		{
			foreach ($this->sections as $key => $value)
			{
				if ($value["tpl"] === null)
					continue;

                if ($this->sections[$key]["events"] === null) {
                    $this->sections[$key]["events"] = new ffEvents();
                }
//ADDED-ATS togliere
if ($key == "brand"){
    $this->sections["brand"]["events"]->addEvent("on_process", "mod_security_cm_on_load_brand");
}
if ($key == "account"){
    $this->sections["account"]["events"]->addEvent("on_process", "mod_security_cm_on_load_account");
}

				$this->sections[$key]["events"]->doEvent("on_process", array(&$this, &$this->sections[$key]["tpl"], $value["attributes"]));
				
				// process components buffer
				foreach ($this->components as $subkey => $item)
				{
					$rc = false;

					if (/*$this->components[$subkey]->use_own_location && */$this->components[$subkey]->display !== false)
					{
						if ($this->components[$subkey]->location_name === null)
							$rc = $value["tpl"]->set_var($subkey, $this->components_buffer[$subkey]["html"]);
						else
							$rc = $value["tpl"]->set_var($this->components[$subkey]->location_name, $this->components_buffer[$subkey]["html"]);
					}

					if ($rc)
						$this->components_buffer[$subkey]["html"] = "";

				}
				reset($this->components);
				$value["tpl"]->set_var("content", $value["content"]);

				$this->tpl_layer[0]->set_var($key, $value["tpl"]->rpparse("main", false));
			}
			reset($this->sections);
		}
	}

   /**
	 * Elabora i CSS
	 * Da richiamare ad ogni aggiunta di CSS se si aggiungono CSS dinamicamente post-elaborazione
	 */
	public function parse_css()
    {
        $this->tpl[0]->set_var("SectCssEmbed", "");
        $this->tpl[0]->set_var("SectCssLink", "");
        $this->tpl[0]->set_var("SectCss", "");
		
		$this->css_buffer = array();
		
		cm::_layoutOrderElements($this->page_css);

		if ($this->browser === null)
			$this->browser = $this->getBrowser();

		//ffErrorHandler::raise("ASD", E_USER_ERROR, $this, get_defined_vars());
			
		foreach ($this->page_css as $css_queue_key => $css_queue)
		{
			foreach ($css_queue as $key => $value)
			{
				$this->tpl[0]->set_var("css_embed", "");
				$this->tpl[0]->set_var("css_type", "");
				$this->tpl[0]->set_var("inline", "");

				$tmp_path = null;
				$tmp_file = null;
				/* Deprecato ADD CSS Custom Browser
				$tmp_add_tag = null;
				$tmp_add_path = null;
				$tmp_add_file = null;
				*/
				$tmp_file_version = $value["version"];
				
				if ($value["embed"])
				{
					if (
							$this->compact_css 
							&& !$value["exclude_compact"]
						)
					{
						if ($value["media"] === null)
							$tmp_media = "default";
						else
							$tmp_media = $value["media"];

						$this->css_buffer[$tmp_media][]["content"] = $value["embed"];
					} 
					else
					{
						$link_properties = "";

						$this->tpl[0]->set_var("lib_tag", $key);
						$this->tpl[0]->set_var("lib_type", "css");
						$this->tpl[0]->set_var("lib_deps", "undefined");
						$this->tpl[0]->set_var("lib_media", (strlen($value["media"]) ? $value["media"] : "undefined"));
						$this->tpl[0]->parse("SectLib", true);
						$this->tpl[0]->parse("SectLibs", false);

						$this->tpl[0]->set_var("css_embed", $value["embed"]);
						
						$link_properties .= ' id="' . $key .'"';

						if($value["type"])
							$link_properties .= ' type="' . $value["type"] .'"';
							
						if ($value["exclude_compact"])
							$link_properties .= " inline";

						if(is_array($value["media"])) {
							foreach($value["media"] AS $media_key => $media_value) {
								$link_properties .= ' ' . $media_key . '="' . $media_value .'"';
							}
						} elseif ($value["media"] && $value["media"] !== "default") {
							$link_properties .= ' media="' . $value["media"] .'"';
						}
						$this->tpl[0]->set_var("link_properties", $link_properties);	
							
						$this->tpl[0]->parse("SectCssEmbed", true);
						$this->tpl[0]->parse("SectCss", true);
					}
					continue;
				} 

				if (isset($this->override_css[$key]))
				{
					$tmp_path = $this->override_css[$key]["path"];
					$tmp_file = $this->override_css[$key]["file"];
				} 
				else 
				{
					$res = $this->doEvent("on_css_parse", array($this, $key, $value["path"], $value["file"]));
					$rc = end($res);
					if ($rc !== null)
					{
						$value["path"] = $rc["path"];
						$value["file"] = $rc["file"];
					}

					$value["path"] = rtrim($value["path"], "/");

					$flag_path_file = (substr($value["path"], -4) === ".css");
					$flag_path_ext = (
										substr(strtolower($value["path"]), 0, 7) === "http://"
										|| substr(strtolower($value["path"]), 0, 8) === "https://"
										|| substr(strtolower($value["path"]), 0, 2) === "//"
									);
					$flag_path_abs = (
										$flag_path_ext || substr($value["path"], 0, 1) === "/"
									);
					$flag_file_ext = (
										substr(strtolower($value["file"]), 0, 7) === "http://"
										|| substr(strtolower($value["file"]), 0, 8) === "https://"
										|| substr(strtolower($value["file"]), 0, 2) === "//"
									);
                    $flag_file_abs = (
                            //ADDED-ATS                                    
                                    strlen($value["file"]) && ($flag_file_ext || (substr($value["file"], 0, 1)==":" && substr($value["file"], 0, 1) === ffCommon_start_abs_path()) || strpos(realpath($value["file"]), realpath(ff_getAbsDir($value["path"]))) === 0)
                                    //strlen($value["file"]) && ($flag_file_ext || substr($value["file"], 0, 1) === ffCommon_start_abs_path() || strpos(realpath($value["file"]), realpath(ff_getAbsDir($value["path"]))) === 0)
                                    );

					$variants = array();

					if ($flag_path_abs)
					{
						$tmp_path = (substr(strtolower($value["path"]), 0, 2) === "//"
										? "http" . ($_SERVER["HTTPS"] ? "s": "") . ":"
										: ""
									) . $value["path"];

						if ($flag_path_file)
						{
							if ($value["file"] === null)
							{
								if ($flag_path_ext)
									$tmp_file = $tmp_path;
								else
								{
                                    $tmp_file = ff_getAbsDir($tmp_path) . $tmp_path;
								}
							}
							elseif ($flag_file_abs)
							{
								if ($flag_file_ext)
									$tmp_file = $value["file"];
								else
								{ //con i link simbolici va in bomba todo: da verificare
                                    if (0 && strpos(realpath($value["file"]), realpath(ff_getAbsDir($tmp_path))) !== 0)
                                        $tmp_file = ff_getAbsDir($tmp_path) . $value["file"];
                                    else
                                        $tmp_file = $value["file"];
								}
							}
							else
								ffErrorHandler::raise ("Impossibile determinare il file fisico", E_USER_ERROR, null, get_defined_vars());
						}
						else
						{
							if ($flag_file_abs)
								ffErrorHandler::raise ("Impossibile determinare il percorso pubblico", E_USER_ERROR, null, get_defined_vars());
							elseif (strlen($value["file"]) || $value["file"] === null)
							{
								if ($flag_path_ext)
								{
									$tmp_path .= "/" . ($value["file"] ? $value["file"] : $key . ".css");
									$tmp_file = $tmp_path;
								}
								else
								{
									if ($tmp_file_version)
									{
 										$variants = array(
												"files" => array(
													ff_getAbsDir($tmp_path) . $tmp_path . "/" . ($value["file"] ? $value["file"] : $key . ".css")
													, ff_getAbsDir($tmp_path) . $tmp_path . "/" . $tmp_file_version . "/". ($value["file"] ? $value["file"] : $key . ".css")
												)
												, "paths" => array(
													$tmp_path . "/" . ($value["file"] ? $value["file"] : $key . ".css")
													, $tmp_path . "/" . $tmp_file_version . "/". ($value["file"] ? $value["file"] : $key . ".css")
												)
											);
									}
									else
									{
										$tmp_path .= "/" . ($value["file"] ? $value["file"] : $key . ".css");
                                        $tmp_file = ff_getAbsDir($tmp_path) . $tmp_path;
									}
								}
							}
							elseif (!strlen($value["file"] && $flag_path_ext))
							{
								$tmp_file = $tmp_path;
							}							
							else
								ffErrorHandler::raise ("Impossibile determinare il file fisico", E_USER_ERROR, null, get_defined_vars());
						}
					}
					elseif ($value["path"] === null || strlen($value["path"]))
					{
						if ($value["path"] === null)
							$tmp_path = "/themes/" . $this->theme . "/css";
						else
							$tmp_path = "/themes/" . $this->theme . "/css/" . $value["path"];

						if ($value["file"] === null)
						{
							$tmp_path .= "/" . $key . ".css";
							$tmp_file = ff_getThemeDir($this->theme) . $tmp_path;
						}
						elseif (strlen($value["file"]))
						{
							if ($flag_file_abs)
								ffErrorHandler::raise ("Impossibile determinare il percorso pubblico", E_USER_ERROR, null, get_defined_vars());
							else
							{
								$tmp_path .= "/" . $value["file"] . ".css";
								$tmp_file = ff_getThemeDir($this->theme) . $tmp_path;
							}

						}
					} 
				}

				if (count($variants))
				{
					$found = false;
					for ($i = 0; $i < count($variants["files"]); $i++)
					{
						$tmp_path = $variants["paths"][$i];
						$tmp_file = $variants["files"][$i];
						
						
						/*if (
								substr($tmp_path, -3) !== ".css"
								|| substr($tmp_file, -3) !== ".css"
							)
							ffErrorHandler::raise ("Eccezione non gestita", E_USER_ERROR, null, get_defined_vars());*/

						//ffErrorHandler::raise ("DEBUG", E_USER_ERROR, null, get_defined_vars());
						$flag_path_ext = (
											substr(strtolower($tmp_path), 0, 7) === "http://"
											|| substr(strtolower($tmp_path), 0, 8) === "https://"
											|| substr(strtolower($tmp_path), 0, 2) === "//"
										);
						$flag_file_ext = (
											substr(strtolower($tmp_file), 0, 7) === "http://"
											|| substr(strtolower($tmp_file), 0, 8) === "https://"
											|| substr(strtolower($tmp_file), 0, 2) === "//"
										);

						$tmp_path = str_replace("[VERSION]", $tmp_file_version, $tmp_path);
						$tmp_file = str_replace("[VERSION]", $tmp_file_version, $tmp_file);

						if (!$flag_file_ext && !$flag_path_ext && is_file($tmp_file))
						{
							$found = true;
							break;
						}
					}
					
					if (!$found)
						ffErrorHandler::raise ("DEBUG: File CSS non esistente", E_USER_ERROR, null, get_defined_vars());
				}
				else
				{
					/*if (
							substr($tmp_path, -3) !== ".css"
							|| substr($tmp_file, -3) !== ".css"
						)
						ffErrorHandler::raise ("Eccezione non gestita", E_USER_ERROR, null, get_defined_vars());*/

					//ffErrorHandler::raise ("DEBUG", E_USER_ERROR, null, get_defined_vars());
					$flag_path_ext = (
										substr(strtolower($tmp_path), 0, 7) === "http://"
										|| substr(strtolower($tmp_path), 0, 8) === "https://"
										|| substr(strtolower($tmp_path), 0, 2) === "//"
									);
					$flag_file_ext = (
										substr(strtolower($tmp_file), 0, 7) === "http://"
										|| substr(strtolower($tmp_file), 0, 8) === "https://"
										|| substr(strtolower($tmp_file), 0, 2) === "//"
									);

					$tmp_path = str_replace("[VERSION]", $tmp_file_version, $tmp_path);
					$tmp_file = str_replace("[VERSION]", $tmp_file_version, $tmp_file);

					if (!$flag_file_ext && !$flag_path_ext && !is_file($tmp_file))
						ffErrorHandler::raise ("DEBUG: File CSS non esistente", E_USER_ERROR, null, get_defined_vars());
				}
				/* Deprecato ADD CSS Custom Browser
				if ($this->css_browser_detection && !$flag_file_ext && !$flag_path_ext)
				{
					$check_file_dir = ffCommon_dirname($tmp_file);
					$check_file_name = basename($tmp_file);
					$check_path_dir = ffCommon_dirname($tmp_path);
					$check_path_name = basename($tmp_path);

					if (strlen($this->browser["name"] . $this->browser["majorver"]))
					{
						$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/replace_" . $check_file_name;
						if (is_file($tmp))
						{
							$tmp_add_file = $tmp;
							$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/replace_" . $check_path_name;
							$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"];
						}
						else
						{
							$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/" . $check_file_name;
							if (is_file($tmp))
							{
								$tmp_add_file = $tmp;
								$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/" . $check_path_name;
								$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"];
							}
						}
					}

					if (strlen($this->browser["platform"]))
					{
						$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/replace_" . $check_file_name;
						if (is_file($tmp))
						{
							$tmp_add_file = $tmp;
							$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/replace_" . $check_path_name;
							$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["platform"]) . $this->browser["majorver"];
						}
						else
						{
							$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/" . $check_file_name;
							if (is_file($tmp))
							{
								$tmp_add_file = $tmp;
								$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/" . $check_path_name;
								$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["platform"]);
							}
						}
					}
				}*/

				//ffErrorHandler::raise ("DEBUG", E_USER_ERROR, null, get_defined_vars());
				// common parts
				if ($value["async"])
				{
					$this->tpl[0]->set_var("lib_async", "true");
					$this->tpl[0]->set_var("css_async", "true");
				}
				else
				{
					$this->tpl[0]->set_var("lib_async", "false");
					$this->tpl[0]->set_var("css_async", "false");
				}

				$link_properties = "";
				
				// static libs in normal load (not XHR)
				$this->tpl[0]->set_var("lib_tag", $key);
				$this->tpl[0]->set_var("lib_type", "css");
				$this->tpl[0]->set_var("lib_deps", "undefined");
				$this->tpl[0]->set_var("lib_media", (strlen($value["media"]) ? $value["media"] : "undefined"));

				// load plugin in XHR loads
				$this->tpl[0]->set_var("css_tag", $key);
				
				if($value["rel"])
					$link_properties .= ' rel="' . $value["rel"] .'"';
				
				if($value["type"])
					$link_properties .= ' type="' . $value["type"] .'"';
				
				if ($flag_path_ext)
					$this->tpl[0]->set_var("css_path", $tmp_path);
				else 
				{
					$mtime = @filemtime(ff_getAbsDir($tmp_path));
					if (ff_getAbsDir($tmp_path, false))
					{
						$a = FF_DISK_PATH;
						$b = substr($a, 0, strlen(FF_SITE_PATH) * -1);
						$c = substr(__TOP_DIR__, strlen($b));
						$this->tpl[0]->set_var("css_path", $c . $tmp_path . ($mtime ? "?" . $mtime : ""));
					}
					else
						$this->tpl[0]->set_var("css_path", FF_SITE_PATH . $tmp_path . ($mtime ? "?" . $mtime : ""));
				}
				
				if (!$this->isXHR() && !$value["async"])
				{
					$this->tpl[0]->parse("SectLib", true);
					$this->tpl[0]->parse("SectLibs", false);
				}
				
				if (
						$this->compact_css 
						&& !$value["exclude_compact"]
						&& $value["rel"] == "stylesheet"
						&& $value["type"] == "text/css"
				)
				{
					if ($value["media"] === null)
						$tmp_media = "default";
					else
						$tmp_media = $value["media"];

					$this->css_buffer[$tmp_media][]["path"] = $tmp_file;
				}
				else
				{
					if ($this->isXHR())
					{
						$this->tpl[0]->set_var("link_properties", $link_properties);	
						$this->tpl[0]->parse("SectCss", true);
					}
					else
					{
						if ($value["async"])
						{
							$this->tpl[0]->parse("SectAsyncCssPlugin", true);
						}
						else
						{
							if ($value["exclude_compact"])
								$link_properties .= " inline";

							if(is_array($value["media"])) {
								foreach($value["media"] AS $media_key => $media_value) {
									$link_properties .= ' ' . $media_key . '="' . $media_value .'"';
								}
							} elseif ($value["media"] && $value["media"] !== "default") {
								$link_properties .= ' media="' . $value["media"] .'"';
							}
							$this->tpl[0]->set_var("link_properties", $link_properties);	
						
							$this->tpl[0]->set_var("SectCssEmbed", "");
							$this->tpl[0]->set_var("SectAsyncCssPlugin", "");
							$this->tpl[0]->parse("SectCssLink", false);
							$this->tpl[0]->parse("SectCss", true);
						}
					}
				}

				// --------------------------------------------------------------------------------------------------
				// add browser customization as additional CSS
				/* Deprecato ADD CSS Custom Browser
				if ($this->css_browser_detection && $tmp_add_tag !== null) 
				{
					if ($value["async"])
					{
						$this->tpl[0]->set_var("lib_async", "true");
						$this->tpl[0]->set_var("css_async", "true");
					}
					else
					{
						$this->tpl[0]->set_var("lib_async", "false");
						$this->tpl[0]->set_var("css_async", "false");
					}				
					
					// static libs in normal load (not XHR)
					$this->tpl[0]->set_var("lib_tag", $tmp_add_tag);
					$this->tpl[0]->set_var("lib_type", "css");
					$this->tpl[0]->set_var("lib_deps", "undefined");
					$this->tpl[0]->set_var("lib_media", (strlen($value["media"]) ? $value["media"] : "undefined"));
					
					// load plugin in XHR loads
					$this->tpl[0]->set_var("css_tag", $tmp_add_tag);
					
					if($value["rel"])
					$link_properties .= ' rel="' . $value["rel"] .'"';
				
					if($value["type"])
						$link_properties .= ' type="' . $value["type"] .'"';					
						
					$this->tpl[0]->set_var("css_path", FF_SITE_PATH . $tmp_add_path);
					
					if (!$this->isXHR() && !$value["async"])
					{
						$this->tpl[0]->parse("SectLib", true);
						$this->tpl[0]->parse("SectLibs", false);
					}					
					
					if (
							$this->compact_css 
							&& !$value["exclude_compact"]
							&& $value["rel"] == "stylesheet"
							&& $value["type"] == "text/css"
					)
					{
						if ($value["media"] === null)
							$tmp_media = "default";
						else
							$tmp_media = $value["media"];					
							
						$this->css_buffer[$tmp_media][]["path"] = $tmp_add_file;
					}
					else
					{
						if ($this->isXHR())
						{
							$this->tpl[0]->set_var("link_properties", $link_properties);	
							$this->tpl[0]->parse("SectCss", true);
						}
						else
						{
							if ($value["async"])
							{
								$this->tpl[0]->parse("SectAsyncCssPlugin", true);
							}
							else
							{
								if ($value["exclude_compact"])
									$link_properties .= " inline";

								if(is_array($value["media"])) {
									foreach($value["media"] AS $media_key => $media_value) {
										$link_properties .= ' ' . $media_key . '="' . $media_value .'"';
									}
								} elseif ($value["media"] && $value["media"] !== "default") {
									$link_properties .= ' media="' . $value["media"] .'"';
								}
								$this->tpl[0]->set_var("link_properties", $link_properties);	
								
								$this->tpl[0]->set_var("SectCssEmbed", "");
								$this->tpl[0]->set_var("SectAsyncCssPlugin", "");
								$this->tpl[0]->parse("SectCssLink", false);
								$this->tpl[0]->parse("SectCss", true);
							}
						}
					}
				}*/
			}
		}
		reset($this->page_css);
    }
    
   /**
	 * Elabora i Javascript
	 * Da richiamare ad ogni aggiunta di Javascript se si aggiungono Javascript dinamicamente post-elaborazione
	 */
    public function parse_js() 
    {
        $this->tpl[0]->set_var("SectJs", ""); 
        $this->tpl[0]->set_var("SectAsyncJsPlugin", "");

        $this->js_buffer = array();
        
		$this->parse_js_fix();
        
        cm::_layoutOrderElements($this->page_js);

		if ($this->browser === null)
			$this->browser = $this->getBrowser();        
        
		$ffjs_queue = null;
		$ffjs_index = null;

		$alldeps = array();
		foreach ($this->page_js as $js_queue_key => $js_queue)
		{
			foreach ($js_queue as $key => $value)
			{
				$alldeps = array_merge($alldeps, $value["deps"]["js"]);
			}
		}
		$alldeps = array_flip($alldeps);
		        
        foreach ($this->page_js as $js_queue_key => $js_queue)
		{
			foreach ($js_queue as $key => $value)
			{
				$this->tpl[0]->set_var("SectJSAsync", "");
				$this->tpl[0]->set_var("js_embed", "");

				$tmp_path = null;
				$tmp_file = null;
				/* Deprecato ADD JS Custom Browser
				$tmp_add_tag = null;
				$tmp_add_path = null;
				$tmp_add_file = null;
				*/
				$tmp_file_version = $value["version"];
				
				$static_init = true;
				
				if ($key === "ff.init")
				{
					$ffjs_queue = $js_queue_key;
					$ffjs_index = $value["index"];
				}
				else if ($ffjs_queue !== null && (
								$js_queue_key > $ffjs_queue
								|| ($js_queue_key === $ffjs_queue && $value["index"] < $ffjs_index)
						))
						$static_init = false;
				
				if ($value["embed"])
				{
					if (
							$this->compact_js 
							&& !$value["exclude_compact"]
						)
					{
						$this->js_buffer[]["content"] = $value["embed"];
					}
					else 
					{
						$this->tpl[0]->set_var("lib_tag", $key);
						$this->tpl[0]->set_var("lib_type", "js");
						$this->tpl[0]->set_var("lib_deps", $this->libDepsToString($value["deps"]));
						$this->tpl[0]->set_var("lib_media", "undefined");
						if ($value["exclude_compact"])
							$this->tpl[0]->parse("SectJSAsync", false);
						// when embed, we can't verify static libs
						//$this->tpl[0]->parse("SectLib", true);
						//$this->tpl[0]->parse("SectLibs", false);

						$this->tpl[0]->set_var("js_embed", $value["embed"]);
						$this->tpl[0]->parse("SectJsEmbed", false);
						$this->tpl[0]->set_var("SectJsSrc", "");
						$this->tpl[0]->parse("SectJs", true);
					}
					continue;
				}


				if (isset($this->override_js[$key]) && strlen($this->override_js[$key])) 
				{
					$tmp_path = $this->override_js[$key];
					$tmp_file = $tmp_path;
				} 
				else 
				{
					$res = $this->doEvent("on_js_parse", array($this, $key, $value["path"], $value["file"]));
					$rc = end($res);
					if ($rc !== null)
					{
						$value["path"] = $rc["path"];
						$value["file"] = $rc["file"];
					}

					$value["path"] = rtrim($value["path"], "/");

					$flag_path_file = (substr($value["path"], -3) === ".js");
					$flag_path_ext = (
										substr(strtolower($value["path"]), 0, 7) === "http://"
										|| substr(strtolower($value["path"]), 0, 8) === "https://"
										|| substr(strtolower($value["path"]), 0, 2) === "//"
									);
					$flag_path_abs = (
										$flag_path_ext || substr($value["path"], 0, 1) === "/"
									);
					$flag_file_ext = (
										substr(strtolower($value["file"]), 0, 7) === "http://"
										|| substr(strtolower($value["file"]), 0, 8) === "https://"
										|| substr(strtolower($value["file"]), 0, 2) === "//"
									);

                    $flag_file_abs = (
                            //ADDED-ATS                                    
                                    strlen($value["file"]) && ($flag_file_ext || (substr($value["file"], 0, 1)==":" && substr($value["file"], 0, 1) === ffCommon_start_abs_path()) || strpos(realpath($value["file"]), realpath(ff_getAbsDir($value["path"]))) === 0)
                                    //strlen($value["file"]) && ($flag_file_ext || substr($value["file"], 0, 1) === ffCommon_start_abs_path() || strpos(realpath($value["file"]), realpath(ff_getAbsDir($value["path"]))) === 0)
                                    );

                    $variants = array();

					if ($flag_path_abs)
					{
						$tmp_path = (substr(strtolower($value["path"]), 0, 2) === "//"
										? "http" . ($_SERVER["HTTPS"] ? "s": "") . ":"
										: ""
									) . $value["path"];

						if ($flag_path_file)
						{
							if ($value["file"] === null)
							{
								if ($flag_path_ext)
									$tmp_file = $tmp_path;
								else
								{
                                    $tmp_file = ff_getAbsDir($tmp_path) . $tmp_path;
								}
							}
							elseif ($flag_file_abs)
							{
								if ($flag_file_ext)
									$tmp_file = $value["file"];
								else
								{ //con i link simbolici va in bomba todo: da verificare
                                    if (0 && strpos(realpath($value["file"]), realpath(ff_getAbsDir($tmp_path))) !== 0)
                                        $tmp_file = ff_getAbsDir($tmp_path) . $value["file"];
                                    else
                                        $tmp_file = $value["file"];
								}
							}
							else
								ffErrorHandler::raise ("Impossibile determinare il file fisico", E_USER_ERROR, null, get_defined_vars());
						}
						else
						{
							if ($flag_file_abs)
								ffErrorHandler::raise ("Impossibile determinare il percorso pubblico", E_USER_ERROR, null, get_defined_vars());
							elseif (strlen($value["file"]) || $value["file"] === null)
							{
								if ($flag_path_ext)
								{
									$tmp_path .= "/" . ($value["file"] ? $value["file"] : $key . ".js");
									$tmp_file = $tmp_path;
								}
								else
								{
									if ($tmp_file_version)
									{
										$variants = array(
												"files" => array(
													ff_getAbsDir($tmp_path) . $tmp_path . "/" . ($value["file"] ? $value["file"] : $key . ".js")
													, ff_getAbsDir($tmp_path) . $tmp_path . "/" . $tmp_file_version . "/". ($value["file"] ? $value["file"] : $key . ".js")
												)
												, "paths" => array(
													$tmp_path . "/" . ($value["file"] ? $value["file"] : $key . ".js")
													, $tmp_path . "/" . $tmp_file_version . "/". ($value["file"] ? $value["file"] : $key . ".js")
												)
											);
									}
									else
									{
										$tmp_path .= "/" . ($value["file"] ? $value["file"] : $key . ".js");
										$tmp_file = ff_getAbsDir($tmp_path) . $tmp_path;
									}
								}
							}
							else
								ffErrorHandler::raise ("Impossibile determinare il file fisico", E_USER_ERROR, null, get_defined_vars());
						}
					}
					elseif ($value["path"] === null || strlen($value["path"]))
					{
						if ($value["path"] === null)
							$tmp_path = "/themes/" . $this->theme . "/js";
						else
							$tmp_path = "/themes/" . $this->theme . "/js/" . $value["path"];

						if ($value["file"] === null)
						{
							$tmp_path .= "/" . $key . ".js";
							$tmp_file = FF_DISK_PATH . $tmp_path;
						}
						elseif (strlen($value["file"]))
						{
							if ($flag_file_abs)
								ffErrorHandler::raise ("Impossibile determinare il percorso pubblico", E_USER_ERROR, null, get_defined_vars());
							else
							{
								$tmp_path .= "/" . $value["file"] . ".js";
								$tmp_file = FF_DISK_PATH . $tmp_path;
							}

						}
					} 
				}

				if (count($variants))
				{
					$found = false;
					for ($i = 0; $i < count($variants["files"]); $i++)
					{
						$tmp_path = $variants["paths"][$i];
						$tmp_file = $variants["files"][$i];
						
						
						/*if (
								substr($tmp_path, -3) !== ".js"
								|| substr($tmp_file, -3) !== ".js"
							)
							ffErrorHandler::raise ("Eccezione non gestita", E_USER_ERROR, null, get_defined_vars());*/

						//ffErrorHandler::raise ("DEBUG", E_USER_ERROR, null, get_defined_vars());
						$flag_path_ext = (
											substr(strtolower($tmp_path), 0, 7) === "http://"
											|| substr(strtolower($tmp_path), 0, 8) === "https://"
											|| substr(strtolower($tmp_path), 0, 2) === "//"
										);
						$flag_file_ext = (
											substr(strtolower($tmp_file), 0, 7) === "http://"
											|| substr(strtolower($tmp_file), 0, 8) === "https://"
											|| substr(strtolower($tmp_file), 0, 2) === "//"
										);

						$tmp_path = str_replace("[VERSION]", $tmp_file_version, $tmp_path);
						$tmp_file = str_replace("[VERSION]", $tmp_file_version, $tmp_file);

						if (!$flag_file_ext && !$flag_path_ext && is_file($tmp_file))
						{
							$found = true;
							break;
						}
					}
					
					if (!$found)
						ffErrorHandler::raise ("DEBUG: File JS non esistente", E_USER_ERROR, null, get_defined_vars());
				}
				else
				{
					/*if (
							substr($tmp_path, -3) !== ".js"
							|| substr($tmp_file, -3) !== ".js"
						)
						ffErrorHandler::raise ("Eccezione non gestita", E_USER_ERROR, null, get_defined_vars());*/

					//ffErrorHandler::raise ("DEBUG", E_USER_ERROR, null, get_defined_vars());
					$flag_path_ext = (
										substr(strtolower($tmp_path), 0, 7) === "http://"
										|| substr(strtolower($tmp_path), 0, 8) === "https://"
										|| substr(strtolower($tmp_path), 0, 2) === "//"
									);
					$flag_file_ext = (
										substr(strtolower($tmp_file), 0, 7) === "http://"
										|| substr(strtolower($tmp_file), 0, 8) === "https://"
										|| substr(strtolower($tmp_file), 0, 2) === "//"
									);

					$tmp_path = str_replace("[VERSION]", $tmp_file_version, $tmp_path);
					$tmp_file = str_replace("[VERSION]", $tmp_file_version, $tmp_file);

					if (!$flag_file_ext && !$flag_path_ext && !is_file($tmp_file))
						ffErrorHandler::raise ("DEBUG: File JS non esistente", E_USER_ERROR, null, get_defined_vars());
				}
				
				/* Deprecato ADD JS Custom Browser
				if ($this->js_browser_detection && !$flag_file_ext && !$flag_path_ext)
				{
					$check_file_dir = ffCommon_dirname($tmp_file);
					$check_file_name = basename($tmp_file);
					$check_path_dir = ffCommon_dirname($tmp_path);
					$check_path_name = basename($tmp_path);

					if (strlen($this->browser["name"] . $this->browser["majorver"]))
					{
						$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/replace_" . $check_file_name;
						if (is_file($tmp))
						{
							$tmp_add_file = $tmp;
							$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/replace_" . $check_path_name;
							$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"];
						}
						else
						{
							$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/" . $check_file_name;
							if (is_file($tmp))
							{
								$tmp_add_file = $tmp;
								$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"] . "/" . $check_path_name;
								$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["name"]) . $this->browser["majorver"];
							}
						}
					}

					if (strlen($this->browser["platform"]))
					{
						$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/replace_" . $check_file_name;
						if (is_file($tmp))
						{
							$tmp_add_file = $tmp;
							$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/replace_" . $check_path_name;
							$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["platform"]) . $this->browser["majorver"];
						}
						else
						{
							$tmp = $check_file_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/" . $check_file_name;
							if (is_file($tmp))
							{
								$tmp_add_file = $tmp;
								$tmp_add_path = $check_path_dir . "/" . ffCommon_url_rewrite($this->browser["platform"]) . "/" . $check_path_name;
								$tmp_add_tag = $key . "." . ffCommon_url_rewrite($this->browser["platform"]);
							}
						}
					}
				}*/
				
				$tmp_lib_deps = $this->libDepsToString($value["deps"]);
					
				if ($value["async"])
				{
					$this->tpl[0]->set_var("lib_async", "true");
					$this->tpl[0]->set_var("js_async", "true");
				}
				else
				{
					$this->tpl[0]->set_var("lib_async", "false");
					$this->tpl[0]->set_var("js_async", "false");
				}
				
				// static libs in normal load (not XHR)
				$this->tpl[0]->set_var("lib_tag", $key);
				$this->tpl[0]->set_var("lib_type", "js");
				$this->tpl[0]->set_var("lib_deps", $tmp_lib_deps);
				$this->tpl[0]->set_var("lib_media", "undefined");
				
				// load plugin in XHR loads
				$this->tpl[0]->set_var("js_tag", $key);
				$this->tpl[0]->set_var("js_deps", $tmp_lib_deps);
				if ($flag_path_ext)
					$this->tpl[0]->set_var("js_path", $tmp_path);
				else
				{
					$mtime = @filemtime(ff_getAbsDir($tmp_path));
					if (ff_getAbsDir($tmp_path, false))
					{
						$a = FF_DISK_PATH;
						$b = substr($a, 0, strlen(FF_SITE_PATH) * -1);
						$c = substr(__TOP_DIR__, strlen($b));
						$this->tpl[0]->set_var("js_path", $c . $tmp_path . ($mtime ? "?" . $mtime : ""));
					}
					else
						$this->tpl[0]->set_var("js_path", FF_SITE_PATH . $tmp_path . ($mtime ? "?" . $mtime : ""));
				}
				
				if (!$this->isXHR() && !$value["async"] && $static_init)
				{
					$this->tpl[0]->parse("SectLib", true);
					$this->tpl[0]->parse("SectLibs", false);
				}

				if (
					$this->compact_js
					&& !$value["exclude_compact"]
				) 
				{
					$this->js_buffer[]["path"] = $tmp_file;
					if (!$this->isXHR() && !$value["async"] && !$static_init/* && ffIsset($alldeps, $key)*/)
					{
						$this->js_buffer[]["content"] = "ff.libLoadStatic('js', '$key');"; //, $tmp_lib_deps
					}
				}
				else
				{
					if ($this->isXHR())
					{
						$this->tpl[0]->parse("SectJsSrc", false);
						$this->tpl[0]->set_var("SectJsEmbed", "");
						$this->tpl[0]->parse("SectJs", true);
					}
					else
					{
						if ($value["async"])
						{
							$this->tpl[0]->parse("SectAsyncJsPlugin", true);
						}
						else
						{
							$this->tpl[0]->parse("SectJsSrc", false);
							$this->tpl[0]->set_var("SectJsEmbed", "");
							$this->tpl[0]->parse("SectJs", true);
							if (!$static_init/* && ffIsset($alldeps, $key)*/)
							{
								$this->tpl[0]->set_var("js_embed", "ff.libLoadStatic('js', '$key');"); //, $tmp_lib_deps
								$this->tpl[0]->parse("SectJsEmbed", false);
								$this->tpl[0]->set_var("SectJsSrc", "");
								$this->tpl[0]->parse("SectJs", true);
							}
						}
					}
				}

				// --------------------------------------------------------------------------------------------------
				// add browser customization as additional JS
				/* Deprecato ADD JS Custom Browser
				if ($this->js_browser_detection && $tmp_add_tag !== null)
				{
					$tmp_lib_deps = $this->libDepsToString(array("js" => array($key)));
					
					// static libs in normal load (not XHR)
					$this->tpl[0]->set_var("lib_tag", $tmp_add_tag);
					$this->tpl[0]->set_var("lib_deps", $tmp_lib_deps);

					// load plugin in XHR loads
					$this->tpl[0]->set_var("js_tag", $tmp_add_tag);
					$this->tpl[0]->set_var("js_deps", $tmp_lib_deps);
					$this->tpl[0]->set_var("js_path", FF_SITE_PATH . $tmp_add_path);
					
					if (
							$this->compact_js 
							&& !$value["exclude_compact"]
						) 
					{
						$this->js_buffer[]["path"] = $tmp_add_file;
					} 
					else 
					{
						if ($this->isXHR())
						{
							$this->tpl[0]->parse("SectJsSrc", false);
							$this->tpl[0]->set_var("SectJsEmbed", "");
							$this->tpl[0]->parse("SectJs", true);
						}
						else
						{
							if ($value["async"])
							{
								$this->tpl[0]->parse("SectAsyncJsPlugin", true);
							}
							else
							{
								if ($static_init)
								{
									$this->tpl[0]->parse("SectLib", true);
									$this->tpl[0]->parse("SectLibs", false);
								}

								$this->tpl[0]->parse("SectJsSrc", false);
								$this->tpl[0]->set_var("SectJsEmbed", "");
								$this->tpl[0]->parse("SectJs", true);
							}
						}
					}
				}*/
			}
		}
		reset($this->page_js);
	}
	
	function libDepsToString($deps)
	{
		$sub = "";
		foreach ($deps as $key => $value)
		{
			$libs = "";
			foreach ($value as $lib)
			{
				if (strlen($libs)) $libs .= ", ";
				$libs .= "'" . $lib  . "'";
			}
			
			if (strlen($libs))
			{
				if (strlen($sub)) $sub .= ", ";
				$sub .= "{";
				$sub .= "'id' : '" . $key . "'";
				$sub .= ", 'value' : [" ;

				$sub .= $libs;
				$sub .= "]}";
			}
		}

		if (strlen($sub))
		{
			return "ff.hash([" . $sub . "])";
		}
		else
			return "undefined";
	}
	
	function parse_js_fix() 
	{
		if (!is_array($this->libraries) || !count($this->libraries))
			return;
		
		foreach ($this->page_js as $js_queue_key => $js_queue)
		{
			foreach ($js_queue as $key => $value)
			{
				if (ffIsset($this->libraries, $key) && ffIsset($this->libraries[$key], "version"))
				{
					$version_value = $this->libraries[$key]["version"];
					
					$tmp_file = FF_DISK_PATH . FF_THEME_DIR . "/library/" . $key . "/" . $key . ".fix." . $version_value . ".js";
					$tmp_path = FF_THEME_DIR . "/library/" . $key . "/" . $key . ".fix." . $version_value . ".js";
					if (is_file($tmp_file))
					{
						$tmp_js[$key . ".fix." . $version_value] = array(
							"path" => $tmp_path
							, "file" => $tmp_file
							, "async" => $js_queue[$key]["async"]
							, "embed" => $js_queue[$key]["embed"]
							, "exclude_compact" => $js_queue[$key]["exclude_compact"]
						);

						$this->tplAddMultiJS($tmp_js, $js_queue_key);
					}
				}
			}
		}
	}
	
   /**
	 * Elabora i Meta Tag
	 * Da richiamare ad ogni aggiunta di Meta se si aggiungono Meta dinamicamente post-elaborazione
	 */
    public function parse_meta() 
    {   
        if (is_array($this->page_meta) && count($this->page_meta))
        {
			$this->tpl[0]->set_var("tag_type", "meta");
            foreach ($this->page_meta as $key => $value)
            {
                $this->tpl[0]->set_var("tag_properties", " " . $value["type"] . '="' . $value["name"] . '" content="' . $value["content"] . '"');
                $this->tpl[0]->parse("SectTags", true);
            }
            reset($this->page_meta);
        }
    }

   /**
	 * Elabora gli attributi del tag html
	 * Da richiamare ad ogni aggiunta di Meta se si aggiungono Meta dinamicamente post-elaborazione
	 */
    public function parse_html_attr() 
    {   
        $this->tpl[0]->set_var("SectHtmlAttr", "");
        if (is_array($this->page_html_attr) && count($this->page_html_attr))
        {
            foreach ($this->page_html_attr as $key => $value)
            {
            	$this->tpl[0]->set_var("attr_type", $value["type"]);
                $this->tpl[0]->set_var("attr_content", $value["content"]);
                $this->tpl[0]->parse("SectHtmlAttr", true);
            }
            reset($this->page_html_attr);
        }
        else
            $this->tpl[0]->set_var("SectHtmlAttr", "");
    }
	/**
	 * Carica una widget
	 * @param String $name il nome della widget
	 * @param String $path il percorso della widget. Se omesso viene determinato in base all'oggetto di riferimento
	 * @param Object $ref l'oggetto di riferimento, di default la pagina stessa
	 */
	function widgetLoad($name, $path = null, &$ref = null)
	{
		if (!parent::widgetLoad($name, $path, $ref))
			return;
		
		if (count($this->widgets[$name]->libraries))
		{
			$this->libsExtend($this->widgets[$name]->libraries);
		}
		
		$this->tplAddMultiJS($this->widgets[$name]->js_deps, cm::LAYOUT_PRIORITY_HIGH);
		$this->tplAddMultiCss($this->widgets[$name]->css_deps, cm::LAYOUT_PRIORITY_HIGH);
	}
	
	/**
	 *
	 * @param Mixed $content il contenuto da aggiungere. Se null si sta aggiungendo un gruppo
	 * @param Mixed $group può essere una stringa se si aggiunge un contenuto ad un gruppo o "true" nel caso si aggiunga un gruppo
	 * @param String $id L'id del contenuto
	 * @param Array $options opzioni aggiuntive da passare relativamente ad un aggiunta
	 */
	public function addContent($content, $group = null, $id = null, $options = array())
	{
	    if(is_array($id) && !$options) {
	       $options = $id;
	       $id = null;
        }

        if($this->tab && $group === "tabs" && !$this->groups["tabs"]) {
            $this->widgetLoad("tabs");
            parent::addContent(null, true, "tabs");
        } elseif ($this->tab && $content === null && $group === true)
		{
            //$options["tab_mode"] = $this->tab;
            $this->widgetLoad("tabs");
        }
		parent::addContent($content, $group, $id, $options);

		if ($content !== null)	
		{
			if (
					is_object($content)
					&& (
						   is_subclass_of($content, "ffGrid_base")
						|| is_subclass_of($content, "ffRecord_base")
						|| is_subclass_of($content, "ffDetails_base")
					)
				)
			{
				if (count($content->libraries))
				{
					$this->libsExtend($content->libraries);
				}

				$this->tplAddMultiJS($content->js_deps, cm::LAYOUT_PRIORITY_HIGH);
				$this->tplAddMultiCss($content->css_deps, cm::LAYOUT_PRIORITY_HIGH);
			}
			else if (
					is_object($content)
					&& get_class($content) == "ffTemplate"
				)
			{
				//cm::getInstance()->preloadApplets($content);
			}
		}
	}
	
	function process_params()
	{
		if (FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID && count($this->components))
		{
			$tmp = $this->getXHRFFStruct();
			if ($tmp)
			{
				foreach ($this->components as $comp_key => $comp_obj)
				{
					foreach ($tmp as $struct_item)
					{
						if (
								isset($struct_item->factory_id)
								&& $comp_key === $struct_item->factory_id
								&& (
										(!$this->getXHRCtx() && (!isset($struct_item->ctx) || !strlen($struct_item->ctx)))
										|| ($this->getXHRCtx() !== false && $this->getXHRCtx() === $struct_item->ctx)
								)
							)
						{
							
							$this->components[$comp_key]->id_if = $struct_item->id;
							
							if (ffIsset($_REQUEST, "XHR_COMPONENT") && $_REQUEST["XHR_COMPONENT"] === $struct_item->id)
							{
								$_REQUEST["XHR_COMPONENT"] = $struct_item->factory_id;
								if (ffIsset($_GET, "XHR_COMPONENT"))
									$_GET["XHR_COMPONENT"] = $_REQUEST["XHR_COMPONENT"];
								if (ffIsset($_POST, "XHR_COMPONENT"))
									$_POST["XHR_COMPONENT"] = $_REQUEST["XHR_COMPONENT"];
							}
							
							if (ffIsset($_REQUEST, "frmAction") && strpos($_REQUEST["frmAction"], $struct_item->id . "_") === 0)
							{
								$_REQUEST["frmAction"] = $struct_item->factory_id . substr($_REQUEST["frmAction"], strpos($_REQUEST["frmAction"], "_"));
								if (ffIsset($_GET, "frmAction"))
									$_GET["frmAction"] = $_REQUEST["frmAction"];
								if (ffIsset($_POST, "frmAction"))
									$_POST["frmAction"] = $_REQUEST["frmAction"];
							}
							
							$tmp_req_key = array_keys($_REQUEST);
							foreach ($tmp_req_key as $key)
							{
								if (strpos($key, $struct_item->id . "_") === 0)
								{
									$newkey = $struct_item->factory_id . substr($key, strpos($key, "_"));
									
									$_REQUEST[$newkey] = $_REQUEST[$key];
									unset($_REQUEST[$key]);
									
									if (ffIsset($_GET, $key))
									{
										unset($_GET[$key]);
										$_GET[$newkey] = $_REQUEST[$newkey];
									}
									
									if (ffIsset($_POST, $key))
									{
										unset($_POST[$key]);
										$_POST[$newkey] = $_REQUEST[$newkey];
									}
								}
							}
							
						}
					}
				}
				
				foreach ($this->components as $comp_key => $comp_obj)
				{
					if (
							is_subclass_of($comp_obj, "ffRecord_base")
							&& ffIsset($_REQUEST, $comp_key . "_detailaction")
						)
					{
						foreach ($tmp as $struct_item)
						{
							if ($struct_item->type === "ffDetails" && isset($struct_item->factory_id))
							{
								if ($_REQUEST[$comp_key . "_detailaction"] === $struct_item->id)
									$_REQUEST[$comp_key . "_detailaction"] = $struct_item->factory_id;
								if (ffIsset($_GET, $comp_key . "_detailaction"))
									$_GET[$comp_key . "_detailaction"] = $struct_item->factory_id;
								if (ffIsset($_POST, $comp_key . "_detailaction"))
									$_POST[$comp_key . "_detailaction"] = $struct_item->factory_id;
							}
						}
					}						
				}
			}
		}
		
		parent::process_params();
	}

	/**
	 * Esegue il processing dell'oggetto.
	 * Difficilmente verrà chiamata due volte.
	 * @param Boolean $output_result se dev'essere emesso l'output immediatamente
	 * @return Mixed il risultato dell'elaborazione
	 */
	function process($output_result = true)
	{
		$this->output_buffer = array();

		if ($this->use_own_form !== false)
			$this->addHiddenField("frmAction", ffCommon_specialchars($_REQUEST["frmAction"]));

		if (!$this->params_processed)
			$this->process_params();

		$this->tplLoad();
		$this->tplLoadLayer();

		$this->doEvent("on_page_process", array(&$this));

		if (is_array($this->components) && count($this->components))
		{
			// First of all, do a preprocess to retrieve params
			foreach ($this->components as $key => $item)
			{
				$this->components[$key]->pre_process();

				if ($this->form_method == "" && is_subclass_of($this->components[$key], "ffRecord_base"))
					$this->form_method = "post";
			}
			reset($this->components);

			if ($this->form_method == "")
				$this->form_method = "get";

			if ($this->form_enctype == "")
			{
				if (strtolower($this->form_method) == "get")
					$this->form_enctype = "application/x-www-form-urlencoded";
				else if (strtolower($this->form_method) == "post")
					$this->form_enctype = "multipart/form-data";
			}

			// After params, process page contents (without parsing templates)
			$components_keys = array_keys($this->components);
			foreach ($components_keys as $key => $item)
			{
				if (is_array($this->components[$item]->widget_deps) && count($this->components[$item]->widget_deps))
				{
					foreach ($this->components[$item]->widget_deps as $subkey => $subvalue)
					{
						if ($subvalue["options"])
							call_user_func_array(array($this->widgets[$subvalue["name"]], "process"), $subvalue["options"]);
						else
							$this->widgets[$subvalue["name"]]->process($this->components[$item]);
					}
					reset($this->components[$item]->widget_deps);
				}
                if ($this->use_cache && isset($this->components[$item]->cache_get_resources) && count($this->components[$item]->cache_get_resources))
                    $this->components_buffer[$item] = $this->cache->get($this->request_key . "_" . $item, "/ff/req");

                if (!$this->components_buffer[$item])
                    $this->components[$item]->process();
			}
			reset($components_keys);
		}

		// After components, process fields
		$fields_keys = array_keys($this->fields);
		foreach ($fields_keys as $key => $item)
		{
			if (is_array($this->fields[$item]->widget_deps) && count($this->fields[$item]->widget_deps))
			{
				foreach ($this->fields[$item]->widget_deps as $subkey => $subvalue)
				{
					if ($subvalue["options"])
						call_user_func_array(array($this->widgets[$subvalue["name"]], "process"), $subvalue["options"]);
					else
						$this->widgets[$subvalue["name"]]->process($this->fields[$item]);
				}
				reset($this->fields[$item]->widget_deps);
			}
			//TOCHECK $this->fields[$item]->process();
		}
		reset($fields_keys);

		// process buttons
		$butt_keys = array_keys($this->buttons);
		foreach ($butt_keys as $key => $item)
		{
			if (is_array($this->buttons[$item]->widget_deps) && count($this->buttons[$item]->widget_deps))
			{
				foreach ($this->buttons[$item]->widget_deps as $subkey => $subvalue)
				{
					if ($subvalue["options"])
						call_user_func_array(array($this->widgets[$subvalue["name"]], "process"), $subvalue["options"]);
					else
						$this->widgets[$subvalue["name"]]->process($this->buttons[$item]);
				}
				reset($this->buttons[$item]->widget_deps);
			}
		}
		reset($butt_keys);

		$obj_keys = array_keys($this->objects);
		foreach ($obj_keys as $key => $item)
		{
			if ($this->objects[$item]->parent[0] === $this)
			{
				$this->objects_buffer[$item]["headers"] = $this->objects[$item]->process_headers();
				$this->objects_buffer[$item]["footers"] = $this->objects[$item]->process_footers();
			}
		}
		reset($obj_keys);

		if (is_array($this->components) && count($this->components))
		{
			// After processing, retrieve output
			foreach ($components_keys as $key => $item)
			{
				if ($this->components[$item]->display !== false)
				{
					if (!(
							!$this->getXHRComponent()
							|| $this->getXHRComponent() === $item
							|| (is_subclass_of($this->components[$item], "ffDetails_base") && $this->components[$item]->main_record[0]->id === $this->getXHRComponent())
					))
						continue;
					
					$tmp_id_if = $this->components[$item]->getIDIF();

					if ($this->components_buffer[$item] === null) // ignora nel caso in cui sia già stato preso da cache
					{
						$this->components_buffer[$item]["html"] = $this->components[$item]->process_interface();
						$this->components_buffer[$item]["headers"] = $this->components[$item]->process_headers();
						$this->components_buffer[$item]["footers"] = $this->components[$item]->process_footers();
						
						if (property_exists($this->components[$item], "widget_activebt_enable") && $this->components[$item]->widget_activebt_enable && !isset($this->widgets["activebuttons"]))
							$this->widgetLoad("activebuttons");
						
						if (property_exists($this->components[$item], "widget_discl_enable") && $this->components[$item]->widget_discl_enable && !isset($this->widgets["disclosures"]))
							$this->widgetLoad("disclosures");

						$ret = $this->componentWidgetsProcess($tmp_id_if);
						$this->components_buffer[$item]["headers"] .= $ret["headers"];
						$this->components_buffer[$item]["footers"] .= $ret["footers"];

						if ($this->use_cache && isset($this->components[$item]->cache_get_resources) && count($this->components[$item]->cache_get_resources))
						{
							call_user_func_array(array($this->cache, "set"),
									array_merge(
										array(
												$this->request_key . "_" . $item
												, null
												, $this->components_buffer[$item]
										),
										$this->components[$item]->cache_get_resources
									)
								);
						}
					}
				}
			}
			reset($components_keys);

			// After processing buffers, set inner components
			if ($this->getXHRFormat() === false)
			{
				$components_keys_copy = array_keys($this->components);
				do
				{
					$replaces = 0;
					foreach ($components_keys_copy as $key => $item)
					{
						foreach ($components_keys as $subkey => $subitem)
						{
							if (!(
									!$this->getXHRComponent()
									|| $this->getXHRComponent() === $subitem
									|| (is_subclass_of($this->components[$subitem], "ffDetails_base") && $this->components[$subitem]->main_record[0]->id === $this->getXHRComponent())
								))
									continue;

							$this->components_buffer[$item]["html"] = str_replace("{{" . $subitem . "}}", $this->components_buffer[$subitem]["html"], $this->components_buffer[$item]["html"], $count);
							$replaces += $count;
							if ($count)
							{
								$this->components_buffer[$item]["headers"] .= $this->components_buffer[$subitem]["headers"];
								$this->components_buffer[$item]["footers"] .= $this->components_buffer[$subitem]["footers"];
								$this->components_buffer[$subitem] = array();
							}
						}
						reset($components_keys);
					}
					reset($components_keys_copy);
				} while ($replaces > 0);
			}
		}

		// process buttons with different location
		$butt_keys = array_keys($this->buttons);
		foreach ($butt_keys as $key => $item)
		{
			if ($this->buttons[$item]->use_own_location)
			{
				if ($this->buttons[$item]->location_context !== null)
				{
					if (
						is_object($this->buttons[$item]->location_context)
						&& get_class($this->buttons[$item]->location_context) == "ffTemplate"
					)
						$this->buttons[$item]->location_context->set_var(($this->buttons[$item]->location_name !== null ? $this->buttons[$item]->location_name : $this->buttons[$item]->id), $this->buttons[$item]->process());
				}
			}
		}
		reset($butt_keys);
			
		// process fields with different location
		$fld_keys = array_keys($this->fields);
		foreach ($fld_keys as $key => $item)
		{
			if ($this->fields[$item]->use_own_location)
			{
				if ($this->fields[$item]->location_context !== null)
				{
					$tmp = $this->fields[$item]->location_context;
					if (
						is_object($this->fields[$item]->location_context)
						&& get_class($this->fields[$item]->location_context) == "ffTemplate"
					)
						$this->fields[$item]->location_context->set_var(($this->fields[$item]->location_name !== null ? $this->fields[$item]->location_name : $this->fields[$item]->id), $this->fields[$item]->process());
				}
			}
		}
		reset($fld_keys);

		$this->tplProcessBounceComponents();

		if (strlen($this->layer) && !$this->isXHR()) {
            $this->tplProcessLayout();
        }

		$this->tplProcess();

        $rc = $this->doEvent("on_page_processed", array(&$this));

        if (!($this->isXHR() && $this->getXHRComponent()))
			$this->widgetsProcess();

		return $this->tplParse($output_result);
	}
	
	function getBrowser()
	{
	    $u_agent = $_SERVER['HTTP_USER_AGENT'];
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";

	    //First get the platform?
	    if (preg_match('/linux/i', $u_agent)) {
	        $platform = 'linux';
	    }
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'mac';
	    }
	    elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'windows';
	    }
	   
	    // Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/iPad/i',$u_agent))
	    {
	        $bname = 'Ipad';
	        $ub = "Ipad";
	    }
		elseif (preg_match('/iPhone/i',$u_agent))
	    {
	        $bname = 'iPhone';
	        $ub = "iPhone";
	    }
		elseif (preg_match('/iPod/i',$u_agent))
	    {
	        $bname = 'Ipod';
	        $ub = "Ipod";
	    }
	    elseif (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	    {
	        $bname = 'Internet Explorer';
	        $ub = "MSIE";
	    }
	    elseif (preg_match('/Firefox/i',$u_agent))
	    {
	        $bname = 'Mozilla Firefox';
	        $ub = "Firefox";
	    }
	    elseif (preg_match('/Chrome/i',$u_agent))
	    {
	        $bname = 'Google Chrome';
	        $ub = "Chrome";
	    }
	    elseif (preg_match('/Safari/i',$u_agent))
	    {
	        $bname = 'Apple Safari';
	        $ub = "Safari";
	    }
	    elseif (preg_match('/Opera/i',$u_agent))
	    {
	        $bname = 'Opera';
	        $ub = "Opera";
	    }
	    elseif (preg_match('/Netscape/i',$u_agent))
	    {
	        $bname = 'Netscape';
	        $ub = "Netscape";
	    }

	    // finally get the correct version number
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?P<browser>' . join('|', $known) .
	    ')[/ ]+(?P<version>[0-9.|a-zA-Z.]*)#';
	    $rc = @preg_match_all($pattern, $u_agent, $matches);
	   	if ($rc === false) {
			$pattern = '#(?<browser>' . join('|', $known) .
		    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		    $rc = @preg_match_all($pattern, $u_agent, $matches);
		    
		   	if ($rc === false) {
			   	ffErrorHandler::raise("unable to check browser version", E_USER_ERROR, null, get_defined_vars());
		   	}
	   	}
	    // see how many we have
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version= $matches['version'][0];
	        }
	        else {
	            $version= $matches['version'][1];
	        }
	    }
	    else {
	        $version= $matches['version'][0];
	    }
	   
	    // check if we have a number
	    if ($version==null || $version=="") {$version="?";}
	   
	    return array(
	        'userAgent' 	=> $u_agent
	        , 'extendname'  => $bname
	        , 'name'		=> $ub
	        , 'majorver'   	=> (strpos($version, ".") === false ? $version : substr($version, 0, strpos($version, ".")))
	        , 'lowerver'   	=> (strpos($version, ".") === false ? $version : substr($version, strpos($version, ".") + 1))
	        , 'platform'  	=> $platform
	        , 'pattern'    	=> $pattern
	    );
	}
	
	/**
	 * rileva se la pagina è stata richiesta con una chiamata Ajax
	 * @return boolean
	 */
	function isXHR()
	{
		if (!$this->force_no_xhr && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
			return true;
		else
			return false;
	}

	/**
	 * rileva se è stata richiesta l'elaborazione di un singolo componente, se si restituisce il nome del componente
	 * @return mixed
	 */
	function getXHRComponent()
	{
		if (!isset($_REQUEST["XHR_COMPONENT"]))
			return false;
		else
			return $_REQUEST["XHR_COMPONENT"];
	}

	/**
	 * rileva se è stata richiesta l'elaborazione di una sezione di un singolo componente, se si restituisce il nome della sezione, se no false
	 * @return mixed
	 */
	function getXHRSection()
	{
		if (!isset($_REQUEST["XHR_SECTION"]))
			return false;
		else
			return $_REQUEST["XHR_SECTION"];
	}

	/**
	 * rileva se è stata richiesta l'elaborazione dell'intera pagina
	 * @return mixed
	 */
	function getXHRFull()
	{
		if (!isset($_REQUEST["XHR_GET_FULL"]))
			return false;
		else
			return true;
	}

	/**
	 * rileva se è stata richiesta l'elaborazione di un contesto
	 * @return mixed
	 */
	function getXHRCtx()
	{
		if (!isset($_REQUEST["XHR_CTX_ID"]))
			return false;
		else
			return $_REQUEST["XHR_CTX_ID"];
	}
	
	function getXHRFFStruct($reset = false)
	{
		if (!isset($_REQUEST["XHR_FFSTRUCT"]))
			return false;
		
		static $struct = null;
		if ($struct === null || $reset)
			$struct = json_decode($_REQUEST["XHR_FFSTRUCT"]);
		
		return $struct;
	}
	
	function getXHRFormat()
	{
		if (!isset($_REQUEST["XHR_FORMAT"]))
			return false;
		else
			return $_REQUEST["XHR_FORMAT"];
	}
	function getProperties()
	{
		$buffer = "";
		if($this->class_body) {
			if (is_array($this->class_body))
				$this->properties_body["class"] = implode(" ", array_filter($this->class_body));
			else
				$this->properties_body["class"] = $this->class_body;
		}

		if (is_array($this->properties_body) && count(properties_body))
		{
			foreach ($this->properties_body as $key => $value)
			{
				if ($key == "style")
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . "=\"";
					foreach ($this->properties_body[$key] as $subkey => $subvalue)
					{
						$buffer .= $subkey . ": " . $subvalue . ";";
					}
					reset($this->properties_body[$key]);
					$buffer .= "\"";
				}
				elseif(strlen($value))
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . "=\"" . $value . "\"";
				}
			}
		}
		if($buffer)
			return " " . $buffer;
	}
}
