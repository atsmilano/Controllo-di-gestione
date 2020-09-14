<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (slider)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_slider extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_slider";
	
	var $widget_deps	= array();
	
	var $libraries = array();
	
    var $js_deps = array(
							"jquery-ui" 		=> null
						);
    var $css_deps 		= array();
    					
	// PRIVATE VARS
	
	var $oPage			= null;
	var $source_path	= null;
	var $style_path		= null;

	var $tpl			= null;

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

		$this->tpl[$id]->set_var("source_path", $this->source_path);

        if ($this->style_path !== null)
			$this->tpl[$id]->set_var("style_path", $this->style_path);
		elseif ($this->oPage !== null)
			$this->tpl[$id]->set_var("style_path", $this->oPage[0]->getThemePath());
	}

	function process($id, &$value, ffField_html &$Field)
	{
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "slider";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $Field->parent_page[0]->theme);

		//if($Field->parent_page[0]->jquery_ui_theme) {
			$this->oPage[0]->tplAddCss("jquery-ui.slider");
		//}
		
        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/slider"); 
        
		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("min", $Field->min_val);
		$this->tpl[$tpl_id]->set_var("max", $Field->max_val);
		$this->tpl[$tpl_id]->set_var("step", $Field->step);

		if(count($Field->desc_label) > 0)
		{			
			foreach ($Field->desc_label as $elem)
			{
				$this->tpl[$tpl_id]->set_var("element", $elem);
				$this->tpl[$tpl_id]->parse("SectElement", true);
				$this->tpl[$tpl_id]->parse("SectDefault", false);
			}
			$this->tpl[$tpl_id]->parse("SectDesc", false);
			$this->tpl[$tpl_id]->parse("SectText", false);
		}
		
		if ($Field->contain_error && $Field->error_preserve) {
			$this->tpl[$tpl_id]->set_var("value", $value->ori_value);
			$ori_value = new ffData($value->ori_value, $Field->base_type);
			$this->tpl[$tpl_id]->set_var("value_format", $ori_value->getValue($Field->get_app_type(), FF_LOCALE));
		} else {
			$this->tpl[$tpl_id]->set_var("value", (intval($value->getValue()) > 0 ? $value->getValue($Field->get_app_type(), FF_SYSTEM_LOCALE) : $Field->min_val));
			$this->tpl[$tpl_id]->set_var("value_format", (intval($value->getValue()) > 0 ? $value->getValue($Field->get_app_type(), FF_LOCALE) : $Field->min_val));
		}
		$this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
		
		if (strlen($Field->class))
			$this->tpl[$tpl_id]->set_var("class", $Field->class);
		else
			$this->tpl[$tpl_id]->set_var("class", $this->class);		
			
		$this->tpl[$tpl_id]->parse("SectBinding", true);
		
		$this->tpl[$tpl_id]->set_var("fixed_pre_content", $Field->fixed_pre_content);
		$this->tpl[$tpl_id]->set_var("fixed_post_content", $Field->fixed_post_content);
		return $this->tpl[$tpl_id]->rpparse("SectControl", FALSE);
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
