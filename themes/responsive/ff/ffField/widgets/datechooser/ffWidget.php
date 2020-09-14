<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (datechooser)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_datechooser extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_datechooser";

	var $widget_deps	= array();

	var $libraries		= array();
	
    var $js_deps = array(
							"ff.ffField.datechooser" 	=> null
						);
	
    var $css_deps 		= array(
    					);

	// PRIVATE VARS
	
	var $tpl 			= null;
	var $db				= null;

	var $oPage = null;
	var $source_path	= null;
	var $style_path 	= null;
	
	var $framework_css		= array(
									"container" => array(
										"class" => null
										, "row"	=> true
										, "col" => null
									)
									, "day" => array(
										"class" => null
										, "col" => array(4)
									)
									, "month" => array(
										"class" => null
										, "col" => array(4)
									)
									, "year" => array(
										"class" => null
										, "col" => array(4)
									)	
								);
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

        if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());

	}
	
	function process($id, &$value, ffField_base &$Field)
	{

		// THE REAL STUFF
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "datechooser";
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

		if(is_array($Field->framework_css["widget"]["datechooser"])) {
			$this->framework_css = array_replace_recursive($this->framework_css, $Field->framework_css["widget"]["datechooser"]);
		}
		if($this->framework_css["container"]["class"] === null)
			$this->framework_css["container"]["class"] = $this->class;

		if($this->framework_css["container"]["row"])
			$this->tpl[$tpl_id]->set_var("container_class", cm_getClassByFrameworkCss("row", "form", $this->framework_css["container"]["class"]));
		elseif($this->framework_css["container"]["col"])
			$this->tpl[$tpl_id]->set_var("container_class", cm_getClassByFrameworkCss($this->framework_css["container"]["col"], "col", array("class" => $this->framework_css["container"]["class"])));
		elseif($this->framework_css["container"]["class"])
			$this->tpl[$tpl_id]->set_var("container_class", $this->framework_css["container"]["class"]);

		if($this->framework_css["day"]["col"])
			$this->tpl[$tpl_id]->set_var("day_class", cm_getClassByFrameworkCss($this->framework_css["day"]["col"], "col",  array("class" => $this->framework_css["day"]["class"])));
		elseif($this->framework_css["day"]["class"])
			$this->tpl[$tpl_id]->set_var("day_class", $this->framework_css["day"]["class"]);

		if($this->framework_css["month"]["col"])
			$this->tpl[$tpl_id]->set_var("month_class", cm_getClassByFrameworkCss($this->framework_css["month"]["col"], "col",  array("class" => $this->framework_css["month"]["class"])));
		elseif($this->framework_css["month"]["class"])
			$this->tpl[$tpl_id]->set_var("month_class", $this->framework_css["month"]["class"]);

		if($this->framework_css["year"]["col"])
			$this->tpl[$tpl_id]->set_var("year_class", cm_getClassByFrameworkCss($this->framework_css["year"]["col"], "col",  array("class" => $this->framework_css["year"]["class"])));
		elseif($this->framework_css["year"]["class"])
			$this->tpl[$tpl_id]->set_var("year_class", $this->framework_css["year"]["class"]);
				
        $year = 0;
		$month = 0;
		$day = 0;

		$timeparts = explode("-", $Field->getValue("Date", FF_SYSTEM_LOCALE));
		if (count($timeparts) > 0)
		{
			$year = intval($timeparts[0]);
			$month = intval($timeparts[1]);
			$day = intval($timeparts[2]); // non gestito
		}

		$this->tpl[$tpl_id]->set_var("sel_year", $year);
		$this->tpl[$tpl_id]->set_var("sel_month", $month);
		$this->tpl[$tpl_id]->set_var("sel_day", $day);
		
		if(is_array($Field->datechooser_type_date))
			$this->tpl[$tpl_id]->set_var("type_date", json_encode($Field->datechooser_type_date));
		else
			$this->tpl[$tpl_id]->set_var("type_date", "'" . $Field->datechooser_type_date . "'");
 
		if ($Field->contain_error && $Field->error_preserve)
			$this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->ori_value));
		else
			$this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue($Field->get_app_type(), $Field->get_locale())));

		$this->tpl[$tpl_id]->parse("SectBinding", true);
		return $this->tpl[$tpl_id]->rpparse("SectControl", false);
	}
	
	function get_component_headers($id)
	{
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
