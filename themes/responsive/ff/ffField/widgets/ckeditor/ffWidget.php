<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (tiny_mce)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_ckeditor extends ffCommon
{

	
	
    // ---------------------------------------------------------------
    //  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_ckeditor";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps = array(
                              "ff.ffField.ckeditor"       => null
						);
    var $css_deps 		= array(
    					);

	// PRIVATE VARS
	
	var $tpl 			= null;
	var $db				= null;

	var $oPage = null;
	var $source_path	= null;
	var $style_path 	= null;
	var $ckeditor_toolbar = array(	
				"default" => "
				[
					['Source','-','Bold','Italic','Underline','-','Find','Replace','-','Cut','Copy','Paste','PasteFromWord'],
				    ['Maximize', 'ShowBlocks'],['Link','Unlink','Anchor'],['Format', 'TextColor','Table'],['NumberedList','BulletedList']
				]", //This is the default toolbar definition used by the editor. It contains all editor features. 
				"administrators" =>
				"[
				    ['Source'],
				    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
				    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
				    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
				    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
				    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				    ['Link','Unlink'],
				    ['Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak'],
				    ['Styles','Format','Font','FontSize'],
				    ['TextColor','BGColor'],
				    ['Maximize', 'ShowBlocks']
				]",  //This is the default toolbar definition used by the editor. It contains all editor features. 
				"dataentry" =>
				"[
				    ['Source','-','Templates'],
				    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
				    ['Undo','Redo'],
				    ['Bold','Italic','Underline','Strike'],
				    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
				    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				    ['Link','Unlink'],
				    ['Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak'],
				    ['Styles','Format','Font','FontSize'],
				    ['TextColor','BGColor'],
				    ['Maximize', 'ShowBlocks']
				]",  //This is the default toolbar definition used by the editor. It contains all editor features. 
				"user" =>
				"[
				    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
				    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
				    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				    ['Link','Unlink'],
				    ['Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak'],
				    ['Styles','Format','Font','FontSize'],
				    ['TextColor','BGColor']
				]",  //This is the default toolbar definition used by the editor. It contains all editor features. 
				"htmledit" =>
				"[
				    ['Source','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
				    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
				    ['NumberedList','BulletedList'],
				    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				    ['FontSize','TextColor','BGColor']
				]" //This is the default toolbar definition used by the editor. It contains all editor features.
			);

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

	}
	
	function process($id, &$value, ffField_base &$Field)
	{
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";

			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			$Field->parent[0]->processed_widgets[$prefix . $id] = "ckeditor";
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

		$this->tpl[$tpl_id]->set_var("fixed_pre_content", $Field->fixed_pre_content);
		$this->tpl[$tpl_id]->set_var("fixed_post_content", $Field->fixed_post_content);

		
		if($_REQUEST["XHR_CTX_TYPE"]) {
			$this->tpl[$tpl_id]->set_var("dialog", "'" . $_REQUEST["XHR_CTX_TYPE"] . "'");
		} else {
			$this->tpl[$tpl_id]->set_var("dialog", "false");
		}

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/ckeditor"); 
        
        if ($Field->contain_error && $Field->error_preserve)
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->ori_value));
        else
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue($Field->get_app_type(), $Field->get_locale())));
        $this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());

        if(class_exists("Auth") && $Field->ckeditor_group_by_auth) {
            $user = Auth::get("user");
            $ckeditor_group = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($user->acl_primary));
        }
        if(!$ckeditor_group) {
        	$ckeditor_group = $Field->ckeditor_group;
        }
// var_dump($this->ckeditor_toolbar, $ckeditor_group);
// exit;
        if(isset($this->ckeditor_toolbar[$ckeditor_group])) {
//         die("ok");
			$this->tpl[$tpl_id]->set_var("widget_toolbar", $this->ckeditor_toolbar[$ckeditor_group]);
		} else {
			$this->tpl[$tpl_id]->set_var("widget_toolbar", $this->ckeditor_toolbar["default"]);
		}
        $this->tpl[$tpl_id]->set_var("widget_class", $this->class);
        $this->tpl[$tpl_id]->set_var("widget_theme", $Field->ckeditor_theme);
        $this->tpl[$tpl_id]->set_var("widget_skin", $Field->ckeditor_skin);
        $this->tpl[$tpl_id]->set_var("widget_lang", strtolower(substr(FF_LOCALE, 0, -1)));
        
        $this->tpl[$tpl_id]->set_var("widget_custom_config", json_encode($Field->ckeditor_custom_config));
        
        
        if($Field->ckeditor_br_mode) {
        	$this->tpl[$tpl_id]->set_var("widget_brmode", "true");	
		} else {
			$this->tpl[$tpl_id]->set_var("widget_brmode", "false");	
		}
        
        $this->tpl[$tpl_id]->parse("SectBinding", true);

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
		//ffErrorHandler::raise("sad", E_USER_ERROR, null, get_defined_vars());
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
