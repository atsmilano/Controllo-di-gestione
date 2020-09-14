<?php

class ffWidget_activebuttons extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_activebuttons";

	var $widget_deps	= array();

	var $libraries		= array();
	
    var $js_deps = array(
							  "ff.ffPage.activebuttons" => null
						);
    var $css_deps 		= array();

	// PRIVATE VARS
	var $oPage			= null;
	var $source_path	= null;
	var $style_path		= null;

	var $tpl 			= null;

	var $processed_id	= array();

	function __construct(ffPage_base $oPage = null, $source_path = null, $style_path = null)
	{
		$this->get_defaults();

		$this->oPage = array(&$oPage);
		
		$this->tpl[0] = ffTemplate::factory(__DIR__);
		$this->tpl[0]->load_file($this->template_file, "main");

		$this->tpl[0]->set_var("site_path", FF_SITE_PATH);

		if ($source_path !== null)
			$this->source_path = $source_path;
		elseif ($oPage !== null)
			$this->source_path = $oPage->getThemePath();

		$this->tpl[0]->set_var("source_path", $this->source_path);

        if ($this->style_path !== null)
			$this->tpl[0]->set_var("style_path", $style_path);
		elseif ($oPage !== null)
			$this->tpl[0]->set_var("style_path", $oPage->getThemePath());
	}

	function process($id, &$data, ffPage_base &$oPage)
	{
	}

	function get_component_headers($id)
	{
		return "";
	}

	function get_component_footers($id)
	{
		return "";
	}

	function process_headers()
	{
		return $this->tpl[0]->rpparse("SectHeaders", false);
	}
	
	function process_footers()
	{
		$this->tpl[0]->set_var("spinner_class", cm_getClassByFrameworkCss("spinner", "icon"));
		
		return $this->tpl[0]->rpparse("SectFooters", false);
	}
}
