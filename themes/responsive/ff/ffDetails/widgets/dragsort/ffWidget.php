<?php

class ffWidget_dragsort extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_dragsort";

	var $widget_deps	= array();
	
    var $libraries		= array();
	
    var $js_deps = array(
							  "ff.ffDetails.dragsort" 			=> null
						);
	var $css_deps = array();
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

	function process(ffDetails_base $details, $options, $key_field)
	{ 
		$tpl_id = $details->id;
		if (!isset($this->tpl[$tpl_id]))
			$this->prepare_template($tpl_id);
			
		$details->addEvent("on_before_parse_row", "ffWidget_dragsort::on_before_parse_row", ffEvent::PRIORITY_HIGH, 0, null, null, array(&$this, $options["resource_id"], $key_field));

		$this->tpl[$tpl_id]->set_var("component_id", $details->id);
		$this->tpl[$tpl_id]->set_var("resource_id", $options["resource_id"]);
		$this->tpl[$tpl_id]->set_var("service_path", $options["service_path"]);
		
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

	function prepare_details_template($id)
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
	static function on_before_parse_row(ffDetails_horiz $details, $row, ffWidget_dragsort $plugin, $resource_id, $key_field)
	{ 
		$tpl_id = $details->id;
		if (!isset($plugin->tpl[$tpl_id]))
			$plugin->prepare_details_template($tpl_id);

		$plugin->tpl[$tpl_id]->set_var("component_id", $details->id);
		$plugin->tpl[$tpl_id]->set_var("key_value", $details->key_fields[$key_field]->getValue());
		$plugin->tpl[$tpl_id]->parse("SectData", true);
		return null;
	}
}
