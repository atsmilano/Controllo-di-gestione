<?php

class ffWidget_dragsort extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_dragsort";

	var $libraries		= array();
	
	var $widget_deps	= array();
    var $js_deps = array(
							  "ff.ffGrid.dragsort" 	=> null
						);
	var $css_deps = array(
						);
	// PRIVATE VARS

	var $source_path		= null;
	var $oPage				= null;
	var $style_path			= null;

	var $tpl 				= null;

	function __construct(ffPage_base $oPage = null, $source_path = null, $style_path = null)
	{
		$this->get_defaults();

		$this->oPage = array(&$oPage);

		if ($source_path !== null)
			$this->source_path = $source_path;
		elseif ($oPage !== null)
			$this->source_path = $oPage->getThemePath();

		$this->style_path = $style_path;
	}

	function prepare_template($id)
	{
		$this->tpl[$id] = ffTemplate::factory(__DIR__);
		$this->tpl[$id]->load_file($this->template_file, "main");

		$this->tpl[$id]->set_var("site_path", $this->oPage[0]->site_path);
		$this->tpl[$id]->set_var("theme", $this->oPage[0]->getTheme());

		$this->tpl[$id]->set_var("source_path", $this->source_path);

        if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());

		if (strlen($_SERVER["QUERY_STRING"]))
			$this->tpl[$id]->set_var("query_string", "?" . $_SERVER["QUERY_STRING"]);
	}

	function process(ffGrid_base $grid, $options, $key_field)
	{ 
		$tpl_id = $grid->getIDIF();
		if (!isset($this->tpl[$tpl_id]))
			$this->prepare_template($tpl_id);
			
		$grid->addEvent("on_before_parse_row", "ffWidget_dragsort::on_before_parse_row", ffEvent::PRIORITY_HIGH, 0, null, null, array(&$this, $options["resource_id"], $key_field));
		$grid->use_paging = false;
		$grid->use_order = false;
		$grid->framework_css["table"]["class"] .= " draggable";
		
		$this->tpl[$tpl_id]->set_var("component_id", $grid->getIDIF());
		$this->tpl[$tpl_id]->set_var("resource_id", $options["resource_id"]);
		$this->tpl[$tpl_id]->set_var("service_path", $options["service_path"]);
        $this->tpl[$tpl_id]->set_var("service_params", $options["service_params"]);
		
		$this->tpl[$tpl_id]->parse("SectIstance", true);
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
	
	static function on_before_parse_row(ffGrid_html $grid, $row, ffWidget_dragsort $plugin, $resource_id, $key_field)
	{ 
		$tpl_id = $grid->getIDIF();
		if (!isset($plugin->tpl[$tpl_id]))
			$plugin->prepare_template($tpl_id);

		$plugin->tpl[$tpl_id]->set_var("component_id", $grid->getIDIF());
		$plugin->tpl[$tpl_id]->set_var("key_value", $grid->key_fields[$key_field]->getValue());
		$plugin->tpl[$tpl_id]->parse("SectData", true);
		return null;
	}
}
