<?php

class ffWidget_disclosures extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_disclosures";

	var $libraries		= array();
	
	var $widget_deps	= array();
	
    var $js_deps		= array(
							  "ff.ffPage.disclosures" 	=> null
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

		if ($source_path !== null)
			$this->source_path = $source_path;
		elseif ($oPage !== null)
			$this->source_path = $oPage->getThemePath();

		$this->style_path = $style_path;

		ffGrid::addEvent("on_tplParse", "ffWidget_disclosures::processButton", null, 0, null, null, array($this));
		ffRecord::addEvent("on_tplParse", "ffWidget_disclosures::processButton", null, 0, null, null, array($this));
		ffDetails::addEvent("on_tplParse", "ffWidget_disclosures::processButton", null, 0, null, null, array($this));
	}

	function prepare_template($id)
	{
		$this->tpl[$id] = ffTemplate::factory(__DIR__);
		$this->tpl[$id]->load_file($this->template_file, "main");

		$this->tpl[$id]->set_var("site_path", $this->oPage[0]->site_path);

		$this->tpl[$id]->set_var("source_path", $this->source_path);

        if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());
	}

	function process($id, &$data, ffPage_base &$oPage)
	{
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

	static function processButton($obj, $tpl, $widget)
	{
		//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
		if ($obj->widget_discl_enable)
		{
			if (!isset($widget->tpl[$obj->id]))
				$widget->prepare_template($obj->id);
			$tpl->parse("SectDisclosures", false);

			$widget->tpl[$obj->id]->set_var("element", $obj->id . "_discl");
			$widget->tpl[$obj->id]->set_var("section", $obj->id . "_discl_sect");
			if ($obj->widget_def_open)
				$widget->tpl[$obj->id]->set_var("state", "opened");
			else
				$widget->tpl[$obj->id]->set_var("state", "closed");

			$widget->tpl[$obj->id]->set_var("component_id", $obj->id);
			$tpl->set_var("discl_bt", $widget->tpl[$obj->id]->rpparse("SectIstance", false));
            //$tpl->set_var("content_wrap_start", '<div id="' . $obj->id . '_discl_sect">');
            //$tpl->set_var("content_wrap_end", '</div>');

			$widget->tpl[$obj->id]->parse("SectInitElement", true);

			if (is_subclass_of($obj, "ffGrid_base"))
				$tpl->parse("SectTitle", false);
			elseif (is_subclass_of($obj, "ffRecord_base"))
			{
				$tpl->parse("SectTitle", false);
			}
		}
		else
			$tpl->set_var("SectDisclosures", "");

	}
}
