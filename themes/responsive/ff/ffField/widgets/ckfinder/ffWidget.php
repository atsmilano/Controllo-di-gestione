<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (jscalendar)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_ckfinder extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_ckfinder";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps = array(
                              "ff.ffField.ckfinder"       => null
						);
    var $css_deps 		= array(
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
		$Field->file_writable = true;
		$Field->file_show_control = false;
		switch($Field->get_control_type())
		{
			case "picture":
			case "picture_no_link":
				//$this->process_picture($id, $value);
				//break;
			case "file_label":
			case "file":
				$Field->process_file($id, $value);
				$suffix_name = "[name]";
				break;
			default:
				$Field->process_label($id, $value);
		}

		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "ckfinder";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		$this->tpl[$tpl_id]->set_var("id", $id . $suffix_name);
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());
		//$this->tpl[$tpl_id]->set_var("class", $this->class);
		//$this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
        $this->tpl[$tpl_id]->set_var("browse_class", cm_getClassByFrameworkCss("search", "icon", "lg"));

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/ckfinder"); 

        if($Field->extended_type == "File") {
        	$base_path = $Field->getFileBasePath();
        	$storing_path = $Field->getFilePath(false);
        	
        	if($base_path && $storing_path) {
        		$this->tpl[$tpl_id]->set_var("base_url", str_replace($base_path, "", str_replace("/[_FILENAME_]", "", $storing_path)));
			} else {
				$this->tpl[$tpl_id]->set_var("base_url", "/");
			}
			
			if(strlen($Field->file_tmpname)) {
				$view_url				= $Field->file_temp_view_url;
				$view_query_string		= $Field->file_temp_view_query_string;

				$preview_url			= $Field->file_temp_preview_url;
				$preview_query_string	= $Field->file_temp_preview_query_string;
			} else {
				$view_url				= $Field->file_saved_view_url;
				$view_query_string		= $Field->file_saved_view_query_string;

				$preview_url			= $Field->file_saved_preview_url;
				$preview_query_string	= $Field->file_saved_preview_query_string;
			}
			
			$filename = $Field->getValue();
			
			$view_url = ffProcessTags($view_url, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			$view_url = str_replace("[_FILENAME_]", $filename, $view_url);
			$view_url = ffProcessTags($view_url, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			$view_query_string = ffProcessTags($view_query_string, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			
			$preview_url = ffProcessTags($preview_url, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			$preview_url = str_replace("[_FILENAME_]", $filename, $preview_url);
			$preview_url = ffProcessTags($preview_url, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			$preview_query_string = ffProcessTags($preview_query_string, $Field->getKeysArray(), $Field->getDataArray(), "normal");
			
			if($Field->file_show_preview  && is_file($Field->getFileBasePath() . $Field->getValue())) {
				$this->tpl[$tpl_id]->set_var("show_file", str_replace($Field->getValue(), "", $preview_url));
				$this->tpl[$tpl_id]->set_var("preview_js", "true");
				$this->tpl[$tpl_id]->set_var("SectPreview", "");
			} elseif($Field->file_widget_preview) {
				$this->tpl[$tpl_id]->set_var("show_file", str_replace($Field->getValue(), "", $preview_url));
				if($Field->getValue()) {
					$this->tpl[$tpl_id]->set_var("view_url", str_replace($Field->getValue(), "", $view_url));
					$this->tpl[$tpl_id]->set_var("view_query_string", $view_query_string);

					$this->tpl[$tpl_id]->set_var("preview_url", str_replace($Field->getValue(), "", $preview_url));
					$this->tpl[$tpl_id]->set_var("preview_query_string", $preview_query_string);
				} else {
					$this->tpl[$tpl_id]->set_var("view_url", "#");
					$this->tpl[$tpl_id]->set_var("view_query_string", "");

					$this->tpl[$tpl_id]->set_var("preview_url", "#");
					$this->tpl[$tpl_id]->set_var("preview_query_string", "");
				}
				$this->tpl[$tpl_id]->set_var("preview_js", "true");
				$this->tpl[$tpl_id]->parse("SectPreview", false);
			} else {
				$this->tpl[$tpl_id]->set_var("preview_js", "false");
				$this->tpl[$tpl_id]->set_var("SectPreview", "");
			}						
		} else {
			if($Field->ckfinder_base_path === null)
				$Field->ckfinder_base_path = FF_DISK_UPDIR;
			
			$base_path = $Field->ckfinder_base_path;

			$Field->ckfinder_storing_path = ffProcessTags($Field->ckfinder_storing_path, $Field->getKeysArray(), $Field->getDataArray(), "normal"); 
			$Field->ckfinder_show_file = ffProcessTags($Field->ckfinder_show_file, $Field->getKeysArray(), $Field->getDataArray(), "normal"); 

			if (count($Field->parent) && is_subclass_of($Field->parent[0], "ffDetails_base"))
			{
				foreach ($Field->parent[0]->fields_relationship as $el_key => $el_value)
				{
					$Field->ckfinder_storing_path = str_replace("[" . $el_value . "_FATHER]", $Field->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue(), $Field->ckfinder_storing_path);
					$Field->ckfinder_show_file = str_replace("[" . $el_value . "_FATHER]", $Field->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue(), $Field->ckfinder_show_file);
				}
				reset ($Field->parent[0]->fields_relationship);

				foreach ($Field->parent[0]->main_record[0]->form_fields as $el_key => $el_value)
				{
					$Field->ckfinder_storing_path = str_replace("[" . $el_key . "_FATHER]", $Field->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue(), $Field->ckfinder_storing_path);
					$Field->ckfinder_show_file = str_replace("[" . $el_key . "_FATHER]", $Field->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue(), $Field->ckfinder_show_file);
				}
				reset ($Field->parent[0]->main_record[0]->form_fields);
			}

			if($base_path && $Field->ckfinder_storing_path) {
				$this->tpl[$tpl_id]->set_var("base_url", str_replace($base_path, "", $Field->ckfinder_storing_path));
			} else {
				$this->tpl[$tpl_id]->set_var("base_url", "/");
			}
			if($Field->file_widget_preview && $Field->ckfinder_show_file) {
				$this->tpl[$tpl_id]->set_var("show_file", $Field->ckfinder_show_file);
				if($Field->getValue()) {
					$this->tpl[$tpl_id]->set_var("view_url", ffCommon_dirname($Field->ckfinder_show_file) . $Field->getValue());
					$this->tpl[$tpl_id]->set_var("view_query_string", "");

					$this->tpl[$tpl_id]->set_var("preview_url", $Field->ckfinder_show_file . $Field->getValue());
					$this->tpl[$tpl_id]->set_var("preview_query_string", "");
				} else {
					$this->tpl[$tpl_id]->set_var("view_url", "#");
					$this->tpl[$tpl_id]->set_var("view_query_string", "");

					$this->tpl[$tpl_id]->set_var("preview_url", "#");
					$this->tpl[$tpl_id]->set_var("preview_query_string", "");
				}
				$this->tpl[$tpl_id]->set_var("preview_js", "true");
				$this->tpl[$tpl_id]->parse("SectPreview", false);
			} else {
				$this->tpl[$tpl_id]->set_var("preview_js", "false");
				$this->tpl[$tpl_id]->set_var("SectPreview", "");
			}						
		}

	    $this->tpl[$tpl_id]->set_var("resource_type", basename($base_path));
	    $this->tpl[$tpl_id]->set_var("site_base_url", str_replace(FF_DISK_PATH, "", $base_path));

        if ($Field->contain_error && $Field->error_preserve)
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->ori_value));
        else
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue($Field->get_app_type(), $Field->get_locale())));
        
        //$this->tpl[0]->set_var("properties", $Field->getProperties());

        $this->tpl[$tpl_id]->parse("SectBinding", true);

        //$Field->tpl[0]->set_var("id", $id);
        $this->tpl[$tpl_id]->set_var("control", $Field->tpl[0]->rpparse("main", false));
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