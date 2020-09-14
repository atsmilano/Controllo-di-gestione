<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vBeta
//		      PLUGIN DEFINITION (listgroup)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_listgroup extends ffCommon
{
	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_listgroup";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps	= array(
                              "ff.ffField.listgroup"       => null
		);
    var $css_deps 	= array(
		);

	// PRIVATE VARS
	
	var $tpl 			= null;
	var $db				= null;

	var $oPage = null;
	var $source_path	= null;
	var $style_path = null;
	
	
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
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "listgroup";
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
		//$this->tpl[$tpl_id]->set_var("class", ($this->class ? (" " . $this->class) : ""));
		//$this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
        $this->tpl[$tpl_id]->set_var("add_class", cm_getClassByFrameworkCss("plus", "icon"));
        $this->tpl[$tpl_id]->set_var("remove_class", cm_getClassByFrameworkCss("minus", "icon"));

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/listgroup"); 

		$this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue()));			
		$this->tpl[$tpl_id]->set_var("separator", $Field->grouping_separator);
        
        $selected_values = explode($Field->grouping_separator, $value->getValue());
        if (count($selected_values))
        {
            $this->tpl[$tpl_id]->set_var("SectRow", "");
            foreach ($selected_values as $tmp_key => $tmp_value)
            {
                if(strlen($tmp_value)) {
                    $this->tpl[$tpl_id]->set_var("listgroup_value", $tmp_value);
                    $this->tpl[$tpl_id]->parse("SectRow", TRUE);
                }
            }
            reset($selected_values);
            
            $this->tpl[$tpl_id]->parse("SectBinding", TRUE);
            return $this->tpl[$tpl_id]->rpparse("SectControl", FALSE);
        }
        else
        {
            return "No data to select";
        }
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
