<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (autocompletex)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_autocompletex extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_autocompletex";

	var $widget_deps	= array(
			array(
				"name" => "dialog"
				)
		);
    var $js_deps = array(
							  "jquery"						=> null
							, "jquery.ui"					=> null
/*							, "ff.ffField.autocompletex"	=> array(
									"file" => "autocompletex.js"
									, "path" => "/themes/responsive/ff/ffField/widgets/autocompletex" 
								)*/
						);
    var $css_deps 		= array(/*
                              "jquery.ui.core"        => array(
                                      "file" => "jquery.ui.core.css"
                                    , "path" => null
                                    , "rel" => "jquery.ui"
                                ), 
                              "jquery.ui.theme"        => array(
                                      "file" => "jquery.ui.theme.css"
                                    , "path" => null
                                    , "rel" => "jquery.ui"
                                ), 
                              "jquery.ui.autocomplete"        => array(
                                      "file" => "jquery.ui.autocomplete.css"
                                    , "path" => null
                                    , "rel" => "jquery.ui"
                                )*/
    					);

	var $disable_dialog = false;
	
	// PRIVATE VARS
	
	var $innerURL		= null;
	
	var $tpl 			= null;
	var $db				= null;

	var $display_debug	= false;

	var $oPage 			= null;
	var $source_path	= null;
	var $style_path 	= null;
	var $theme			= null;
	
	function __construct(ffPage_base $oPage = null, $source_path = null, $style_path = null)
	{
		$this->get_defaults();

		$this->oPage = array(&$oPage);
		
		if ($source_path !== null)
			$this->source_path = $source_path;
		elseif ($oPage !== null)
			$this->source_path = $oPage->getThemePath();

		$this->style_path = $style_path;
		
		$this->db[0] = ffDB_Sql::factory();
	}

	function prepare_template($id)
	{
		$this->tpl[$id] = ffTemplate::factory(__DIR__);
		$this->tpl[$id]->load_file($this->template_file, "main");

		$this->tpl[$id]->set_var("source_path", $this->source_path);

		if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());
/*
		if ($this->innerURL === null)
			$this->tpl[$id]->set_var("innerURL", $this->source_path . "/ff/ffField/widgets/autocompletex/parsedata." . FF_PHP_EXT);
		else
			$this->tpl[$id]->set_var("innerURL", $this->innerURL);*/
		
	}

	function process($id, &$value, ffField_base &$Field)
	{
		global $plgCfg_autocomplete_UseOwnSession;
        
		if ($Field->parent !== null && strlen($Field->parent[0]->id))
		{
			$tpl_id = $Field->parent[0]->id;
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $Field->parent[0]->id . "_");
			$prefix = $Field->parent[0]->id . "_";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		if (isset($Field->db[0]))
			$db =& $Field->db[0];
		else
			$db =& $this->db[0];

		$this->tpl[$tpl_id]->set_var("SectControl", "");

		if(strpos($id, "[") === false) {
			$this->tpl[$tpl_id]->set_var("id_encoded", $id);
		} else {
			$this->tpl[$tpl_id]->set_var("id_encoded", str_replace("[", '\\\\[', str_replace("]", '\\\\]', $id)));
		}

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("class", " " . $this->class . (strlen($Field->class) ? " " . $Field->class : ""));
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
        $this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
		if($this->theme !== null) 
			$this->tpl[$tpl_id]->set_var("theme", $this->theme);
		else
			$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/autocompletex");

		if ($Field->actex_service === null)
		{
			$this->tpl[$tpl_id]->set_var("service", "null");
			if (!$this->innerURL === null) {
				$this->tpl[$tpl_id]->set_var("innerURL", $this->innerURL);
				$this->tpl[$tpl_id]->parse("SectInnerUrl", false);
			}
			/*			
			if ($this->innerURL === null)
				$this->tpl[$tpl_id]->set_var("innerURL", $this->source_path . "/ff/ffField/widgets/autocompletex/parsedata." . FF_PHP_EXT);
			else
				$this->tpl[$tpl_id]->set_var("innerURL", $this->innerURL);
			*/
		}
		else
			$this->tpl[$tpl_id]->set_var("service", "'" . $Field->actex_service . "'");


        $this->tpl[$tpl_id]->set_var("icon_caret_down", cm_getClassByFrameworkCss("more", "icon"));    
        $this->tpl[$tpl_id]->set_var("icon_plus", cm_getClassByFrameworkCss("plus", "icon"));    
        $this->tpl[$tpl_id]->set_var("icon_minus", cm_getClassByFrameworkCss("minus", "icon")); 
        $this->tpl[$tpl_id]->set_var("icon_loader", cm_getClassByFrameworkCss("spinner", "icon-tag", "spin"));       
		
			
/* Remove jquery ui css
    	$css_deps 		= array(
              "jquery.ui.core"        => array(
                      "file" => "jquery.ui.core.css"
                    , "path" => null
                    , "rel" => "jquery.ui"
                ), 
              "jquery.ui.theme"        => array(
                      "file" => "jquery.ui.theme.css"
                    , "path" => null
                    , "rel" => "jquery.ui"
                ), 
              "jquery.ui.autocomplete"        => array(
                      "file" => "jquery.ui.autocomplete.css"
                    , "path" => null
                    , "rel" => "jquery.ui"
                )
    	);

		if(is_array($css_deps) && count($css_deps)) {
			foreach($css_deps AS $css_key => $css_value) {
				$rc = $Field->parent_page[0]->widgetResolveCss($css_key, $css_value, $Field->parent_page[0]);

				$this->tpl[$tpl_id]->set_var(preg_replace('/[^0-9a-zA-Z]+/', "", $css_key), $rc["path"] . "/" . $rc["file"]);
				$Field->parent_page[0]->tplAddCss(preg_replace('/[^0-9a-zA-Z]+/', "", $css_key), $rc["file"], $rc["path"], "stylesheet", "text/css", false, false, null, false, "bottom");
			}
		}
*/
        if($Field->autocomplete_combo) {
        	$this->tpl[$tpl_id]->parse("SectCombo", false);
		} else {
			$this->tpl[$tpl_id]->set_var("SectCombo", "");
		}
		
        if($Field->autocomplete_disabled)
            $this->tpl[$tpl_id]->set_var("disabled", "true");
        else
            $this->tpl[$tpl_id]->set_var("disabled", "false");

        if($Field->autocomplete_readonly) {
			if ($value == null || !($value->getValue($Field->get_app_type(), $Field->get_locale()))) {
				$this->tpl[$tpl_id]->set_var("selected_value", "");
				$this->tpl[$tpl_id]->set_var("selected_id", "");
			} else {
                if($Field->autocomplete_multi) {
                    $arrValue = explode(",", $value->getValue($Field->get_app_type(), $Field->get_locale()));
                    if(is_array($arrValue) && count($arrValue)) {
                        foreach($arrValue AS $value_part) {
                            if(strlen($strValue))
                                $strValue .= ",";

                            $strValue .= $Field->getDisplayValue($Field->get_app_type(), $Field->get_locale(), new ffData($value_part, $Field->get_app_type(), $Field->get_locale()));
                        }
                    }
                }
                if(!strlen($strValue))
                    $strValue = $Field->getDisplayValue();
				$this->tpl[$tpl_id]->set_var("selected_value", $strValue);
				$this->tpl[$tpl_id]->set_var("selected_id", $value->getValue($Field->get_app_type(), $Field->get_locale()));
			}
            $this->tpl[$tpl_id]->set_var("readonly", "true");
            $this->tpl[$tpl_id]->set_var("prefix", "autocompletex_");
            $this->tpl[$tpl_id]->parse("SectReadOnly", false);
        } else {
			if ($value == null || !$value->getValue($Field->get_app_type(), $Field->get_locale()))
				$this->tpl[$tpl_id]->set_var("selected_value", "");
			else
				$this->tpl[$tpl_id]->set_var("selected_value", $value->getValue($Field->get_app_type(), $Field->get_locale()));

			$this->tpl[$tpl_id]->set_var("prefix", "");
            $this->tpl[$tpl_id]->set_var("readonly", "false");
            $this->tpl[$tpl_id]->set_var("SectReadOnly", "");
        }
        
        $this->tpl[$tpl_id]->set_var("minLength", $Field->autocomplete_minLength);
        $this->tpl[$tpl_id]->set_var("delay", $Field->autocomplete_delay);
        
        if($Field->autocomplete_multi)
            $this->tpl[$tpl_id]->set_var("multi", "true");
        else
            $this->tpl[$tpl_id]->set_var("multi", "false");

        if($Field->autocomplete_cache)
            $this->tpl[$tpl_id]->set_var("cache", "true");
        else
            $this->tpl[$tpl_id]->set_var("cache", "false");

        if($Field->autocomplete_combo)
            $this->tpl[$tpl_id]->set_var("combo", "true");
        else
            $this->tpl[$tpl_id]->set_var("combo", "false");

        $this->tpl[$tpl_id]->set_var("strip_char", urlencode($Field->autocomplete_strip_char));
        
		$this->tpl[$tpl_id]->set_var("data_src", "");

		if (strlen($Field->source_SQL))
		{
			if($father === null)
				$tmp = md5($Field->source_SQL);
			else
				$tmp = md5($Field->source_SQL . "-" . $father->getValue());

			if (!defined("FF_AUTOCOMPLETE_SESSION_STARTED") && ($plgCfg_autocomplete_UseOwnSession || $Field->autocomplete_use_own_session))
			{
				if (!isset($_COOKIE[session_name()]))
				{
					if (isset($_POST[session_name()]))
						session_id($_POST[session_name()]);
					elseif (isset($_GET[session_name()]))
						session_id($_GET[session_name()]);
				}
				session_start();
				if (!defined("FF_AUTOCOMPLETE_SESSION_STARTED"))
					define("FF_AUTOCOMPLETE_SESSION_STARTED", true);
			}

			$ff = get_session("ff");
			$ff["autocomplete"][$tmp]["sql"]						= $Field->source_SQL;
			$ff["autocomplete"][$tmp]["main_db"]					= $Field->autocomplete_use_main_db;
			$ff["autocomplete"][$tmp]["hide_result_on_query_empty"] = $Field->autocomplete_hide_result_on_query_empty;

			$ff["autocomplete"][$tmp]["compare"]		= $Field->autocomplete_compare;
			$ff["autocomplete"][$tmp]["compare_having"] = $Field->autocomplete_compare_having;
			$ff["autocomplete"][$tmp]["operation"]		= $Field->autocomplete_operation;

			set_session("ff", $ff);

			$this->tpl[$tpl_id]->set_var("data_src", $tmp);
		}

		$this->tpl[$tpl_id]->parse("SectBinding", true);

		if ($this->display_debug)
			$this->tpl[$tpl_id]->parse("SectDebug", false);
		else
			$this->tpl[$tpl_id]->set_var("SectDebug", "");

        if($Field->autocomplete_multi) {
            $this->tpl[$tpl_id]->parse("SectControlMulti", false);
            $this->tpl[$tpl_id]->set_var("SectControlMono", "");
        } else {
            $this->tpl[$tpl_id]->set_var("SectControlMulti", "");
            $this->tpl[$tpl_id]->parse("SectControlMono", false);
        }
        return $Field->fixed_pre_content . $this->tpl[$tpl_id]->rpparse("SectControl", false) . $Field->fixed_post_content;	

/*        if(array_key_exists("autocomplete", $Field->parent_page[0]->widgets)) {
        	return $Field->fixed_pre_content . $this->tpl[$tpl_id]->rpparse("SectControl", false) . $Field->fixed_post_content;	
		} else {
			return $this->get_component_headers($tpl_id) 
					. $Field->fixed_pre_content 
					. $this->tpl[$tpl_id]->rpparse("SectControl", false) 
					. $Field->fixed_post_content 
					. $this->get_component_footers($tpl_id);
		}*/
        
 		
	}

	function get_component_headers($id)
	{
		if ($this->oPage !== NULL) { //code for ff.js
			//$this->oPage[0]->tplAddJs("jquery.blockui", "jquery.blockui.js", FF_THEME_DIR . "/library/plugins/jquery.blockui");
			$this->oPage[0]->tplAddJs("ff.ajax", "ajax.js", FF_THEME_DIR . "/library/ff");
            $this->oPage[0]->tplAddJs("ff.ffField", "ffField.js", FF_THEME_DIR . "/library/ff");
			$this->oPage[0]->tplAddJs("ff.ffField.autocompletex", "autocompletex.js", FF_THEME_DIR . "/responsive/ff/ffField/widgets/autocompletex");
		}

		if (!isset($this->tpl[$id]))
			return;

		return $this->tpl[$id]->rpparse("SectHeaders", false);
	}
	
	function get_component_footers($id)
	{
		if (!isset($this->tpl[$id]))
			return;

		return $this->tpl[$id]->rpparse("SectFooters", false);
	}

	function process_headers()
	{
		if ($this->oPage !== NULL) { //code for ff.js
			//$this->oPage[0]->tplAddJs("jquery.blockui", "jquery.blockui.js", FF_THEME_DIR . "/library/plugins/jquery.blockui");
			$this->oPage[0]->tplAddJs("ff.ajax", "ajax.js", FF_THEME_DIR . "/library/ff");
            $this->oPage[0]->tplAddJs("ff.ffField", "ffField.js", FF_THEME_DIR . "/library/ff");
			$this->oPage[0]->tplAddJs("ff.ffField.autocompletex", "autocompletex.js", FF_THEME_DIR . "/responsive/ff/ffField/widgets/autocompletex");
			
			//return;
		}
		
		if (!isset($this->tpl["main"]))
			return;

		return $this->tpl["main"]->rpparse("SectHeaders", false);
	}
	
	function process_footers()
	{
		if (!isset($this->tpl["main"]))
			return;

		return $this->tpl["main"]->rpparse("SectFooters", false);
	}
}
