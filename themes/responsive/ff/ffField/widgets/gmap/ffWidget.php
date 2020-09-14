<?php
class ffWidget_gmap
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_gmap";

	var $widget_deps	= array();
    var $js_deps = array();
    var $css_deps 		= array();

	// PRIVATE VARS
	
	var $tpl 			= null;
	var $db				= null;

	var $oPage = null;
	var $source_path	= null;
	var $style_path = null;
	
	var $framework_css		= array();
	
	function __construct(ffPage_base $oPage = null, $source_path = null, $style_path = null)
	{
		//$this->get_defaults();

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

		if ($style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());

	}
	
	function process($id, &$value, ffField_base &$Field)
	{
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

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());
		$this->tpl[$tpl_id]->set_var("class", $this->class);
		$this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());

		$this->tpl[$tpl_id]->set_var("container_class", cm_getClassByFrameworkCss("group", "form"));

		$wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
		if($wrap_addon) {
			$this->tpl[$tpl_id]->set_var("wrap_start", '<div class="' . cm_getClassByFrameworkCss(array(10), "col") . '">');
			$this->tpl[$tpl_id]->set_var("wrap_middle", '</div><div class="' . cm_getClassByFrameworkCss(array(2), "col") . '">');
			$this->tpl[$tpl_id]->set_var("wrap_end", '</div>');
		}
		$this->tpl[$tpl_id]->set_var("search_class", cm_getClassByFrameworkCss("control", "form"));
		$this->tpl[$tpl_id]->set_var("search_bt_class", cm_getClassByFrameworkCss("search", "link") . " " . cm_getClassByFrameworkCss("control-postfix", "form") . " " . cm_getClassByFrameworkCss("search", "icon"));

		$this->tpl[$tpl_id]->set_var("map_class", cm_getClassByFrameworkCss(array(12), "col"));

		
        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/gmap"); 

		$this->tpl[$tpl_id]->set_var("key", $Field->gmap_key);
		
		$this->tpl[$tpl_id]->set_var("region", (strlen($Field->gmap_region) ? $Field->gmap_region : ""));

		//$this->oPage[0]->tplAddJs("google.maps", "maps?file=api&v=2.x&sensor=false&key=" . $Field->gmap_key . "&language=" . strtolower(substr(FF_LOCALE, 0, -1)), "http://maps.google.com", false, $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest", null, true);
		

		if ($Field->gmap_draggable)
			$this->tpl[$tpl_id]->set_var("draggable", "true");
		else
			$this->tpl[$tpl_id]->set_var("draggable", "false");
         
				$know_point = true;
		//ffErrorHandler::raise("asdf", E_USER_ERROR, null,get_defined_vars());
		if(is_array($value))
		{
			if (!strlen($value["lat"]->getValue()))
			{
				$know_point = false;
			}
			else
				$this->tpl[$tpl_id]->set_var("start_lat", $value["lat"]->getValue());

			if (!strlen($value["lng"]->getValue()))
			{
				$know_point = false;
			}
			else
				$this->tpl[$tpl_id]->set_var("start_lng", $value["lng"]->getValue());

			
			if (strlen($value["title"]->getValue()))
			{
				$this->tpl[$tpl_id]->set_var("title", $value["title"]->getValue());
				$this->tpl[$tpl_id]->set_var("force_search", "true");
			}
			else
			{
				$this->tpl[$tpl_id]->set_var("title", "");
				$this->tpl[$tpl_id]->set_var("force_search", "false");
			}
			
			if (strlen($value["zoom"]->getValue()))
			{
				$this->tpl[$tpl_id]->set_var("start_zoom", $value["zoom"]->getValue());
			}
		} 
		
		
		if(!$know_point && is_array($Field->default_value)) {
			$know_point = true;
			if (!strlen($Field->default_value["lat"]->getValue()))
			{
				$know_point = false;
			}
			else 
			{
				$this->tpl[$tpl_id]->set_var("start_lat", $Field->default_value["lat"]->getValue());
			}
			if (!strlen($Field->default_value["lng"]->getValue()))
			{
				$know_point = false;
			}
			else 
			{
				$this->tpl[$tpl_id]->set_var("start_lng", $Field->default_value["lng"]->getValue());
			}
			if (strlen($Field->default_value["title"]->getValue()))
			{
				$this->tpl[$tpl_id]->set_var("title", $Field->default_value["title"]->getValue());
				$this->tpl[$tpl_id]->set_var("force_search", "true");
			}
			if (strlen($Field->default_value["zoom"]->getValue()))
			{
				$this->tpl[$tpl_id]->set_var("start_zoom", $Field->default_value["zoom"]->getValue());		
			}
		
		}
		
		if(!$know_point) {
			$this->tpl[$tpl_id]->set_var("start_lat", $Field->gmap_start_lat);
			$this->tpl[$tpl_id]->set_var("start_lng", $Field->gmap_start_lng);
			$this->tpl[$tpl_id]->set_var("start_zoom", $Field->gmap_start_zoom);
		}
		
		if(strlen($Field->gmap_update_class)) {
			$this->tpl[$tpl_id]->set_var("style_search", 'style="display:none;"');
			$this->tpl[$tpl_id]->set_var("style_search_bt", 'style="display:none;"');
		} else {
			$this->tpl[$tpl_id]->set_var("style_search", '');
			$this->tpl[$tpl_id]->set_var("style_search_bt", '');
		}
        $this->tpl[$tpl_id]->set_var("update_class", $Field->gmap_update_class);
        $this->tpl[$tpl_id]->set_var("update_class_prefix", $Field->gmap_update_class_prefix);
		
		

		$this->tpl[$tpl_id]->set_var("set_marker", "false");
		if ($know_point)
			$this->tpl[$tpl_id]->set_var("set_marker", "true");

		$this->tpl[$tpl_id]->parse("SectBinding", true);

		return $this->tpl[$tpl_id]->rpparse("SectControl", false);
	}

	function get_component_headers($id)
	{
		if ($this->oPage !== NULL) { //code for ff.js
            $this->oPage[0]->tplAddJs("ff.ffField", "ffField.js", FF_THEME_DIR . "/library/ff");
			$this->oPage[0]->tplAddJs("ff.ffField.gmap", "gmap.js", FF_THEME_DIR . "/responsive/ff/ffField/widgets/gmap");
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
            $this->oPage[0]->tplAddJs("ff.ffField", "ffField.js", FF_THEME_DIR . "/library/ff");
			$this->oPage[0]->tplAddJs("ff.ffField.gmap", "gmap.js", FF_THEME_DIR . "/responsive/ff/ffField/widgets/gmap");
			
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
	
	function init($Field)
	{
		if (!is_array($Field[0]->multi_fields) || !count($Field[0]->multi_fields))
			$Field[0]->multi_fields = array(
					"lat" 		=> array("type" => "Text")
					, "lng" 	=> array("type" => "Text")
					, "title" 	=> array("type" => "Text")
					, "zoom" 	=> array("type" => "Text")
				);
	}
}
