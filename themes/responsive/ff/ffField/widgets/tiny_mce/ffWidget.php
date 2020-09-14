<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (tiny_mce)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_tiny_mce extends ffCommon
{

    // ---------------------------------------------------------------
    //  PRIVATE VARS (used by code, don't touch or may be explode! :-)

    var $template_file  = "ffWidget.html";
    
    var $class          = "ffWidget_tinymce";
    
    var $widget_deps    = array();

	var $libraries		= array();
	
    var $js_deps        = array(
    						"ff.ffField.tiny_mce"       => null
    					);
    var $css_deps       = array();

    // PRIVATE VARS
    
    var $tpl             = null;
    var $db              = null;

    var $oPage = null;
    var $source_path    = null;
    var $style_path     = null;
    var $tinymce_toolbar = array(    
                "default" => array(
                        "plugins" => ""
                        , "buttons1" => "bold,italic,underline"
                        , "buttons2" => ""
                        , "buttons3" => ""
                        , "buttons4" => ""
                        , "buttons5" => ""
                        , "buttons6" => ""
                    )
                , //This is the default toolbar definition used by the editor. It contains all editor features. 
                "administrators" => array(
                        "plugins" => "safari,spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak,imagemanager,filemanager"
                        , "buttons1" => "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect"
                        , "buttons2" => "fontselect,fontsizeselect,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist"
                        , "buttons3" => "undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor"
                        , "buttons4" => "tablecontrols,|,outdent,indent,blockquote"
                        , "buttons5" => "hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen"
                        , "buttons6" => "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage"
                    )
                ,  //This is the default toolbar definition used by the editor. It contains all editor features. 
                "dataentry" => array(
                        "plugins" => "safari,spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak,imagemanager,filemanager"
                        , "buttons1" => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect"
                        , "buttons2" => "fontselect,fontsizeselect,|,bullist,numlist,|,forecolor,backcolor"
                        , "buttons3" => "cut,copy,paste,pastetext,|,undo,redo,|,link,unlink,image,|,insertdate,inserttime,preview"
                        , "buttons4" => "outdent,indent,|,sub,sup,|,charmap,emotions,media,|,spellchecker,|,pagebreak,|,insertfile,insertimage"
                        , "buttons5" => ""
                        , "buttons6" => ""
                    )
                ,  //This is the default toolbar definition used by the editor. It contains all editor features. 
                "user" => array(
                        "plugins" => "safari,spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak"
                        , "buttons1" => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,forecolor,backcolor,|,link,unlink"
                        , "buttons2" => ""
                        , "buttons3" => ""
                        , "buttons4" => ""
                        , "buttons5" => ""
                        , "buttons6" => ""
                    )
                ,  //This is the default toolbar definition used by the editor. It contains all editor features. 
                "htmledit" => array(
                        "plugins" => "safari,spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak,imagemanager,filemanager"
                        , "buttons1" => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,forecolor,backcolor"
                        , "buttons2" => ""
                        , "buttons3" => ""
                        , "buttons4" => ""
                        , "buttons5" => ""
                        , "buttons6" => ""
                    )
                     //This is the default toolbar definition used by the editor. It contains all editor features.
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
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			$Field->parent[0]->processed_widgets[$prefix . $id] = "tiny_mce";
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
        $this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
        
		$this->tpl[$tpl_id]->set_var("fixed_pre_content", $Field->fixed_pre_content);
		$this->tpl[$tpl_id]->set_var("fixed_post_content", $Field->fixed_post_content);

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/tiny_mce"); 
        
        if ($Field->contain_error && $Field->error_preserve)
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->ori_value));
        else
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue($Field->get_app_type(), $Field->get_locale())));
        $this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());


        if(class_exists("Auth") && $Field->tiny_mce_group_by_auth) {
            $user = Auth::get("user");
            $tinymce_group = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($user->acl_primary));
        }
        if(!$tinymce_group) {
            $tinymce_group = $Field->tinymce_group;
        }

        if(strlen($tinymce_group))
            $class_groups = "_" . $tinymce_group;
        else
            $class_groups = "";

        $this->tpl[$tpl_id]->set_var("class", $this->class . $class_groups);
        $this->tpl[$tpl_id]->set_var("widget_class", $this->class . $class_groups);
        $this->tpl[$tpl_id]->set_var("widget_lang", strtolower(substr(FF_LOCALE, 0, -1)));
        
        if(isset($this->tinymce_toolbar[$tinymce_group])) {
            $this->tpl[$tpl_id]->set_var("plugins", $this->tinymce_toolbar[$tinymce_group]["plugins"]);
            $this->tpl[$tpl_id]->set_var("buttons1", $this->tinymce_toolbar[$tinymce_group]["buttons1"]);
            $this->tpl[$tpl_id]->set_var("buttons2", $this->tinymce_toolbar[$tinymce_group]["buttons2"]);
            $this->tpl[$tpl_id]->set_var("buttons3", $this->tinymce_toolbar[$tinymce_group]["buttons3"]);
            $this->tpl[$tpl_id]->set_var("buttons4", $this->tinymce_toolbar[$tinymce_group]["buttons4"]);
            $this->tpl[$tpl_id]->set_var("buttons5", $this->tinymce_toolbar[$tinymce_group]["buttons5"]);
            $this->tpl[$tpl_id]->set_var("buttons6", $this->tinymce_toolbar[$tinymce_group]["buttons6"]);
        } else {
            $this->tpl[$tpl_id]->set_var("plugins", $this->tinymce_toolbar["default"]["plugins"]);
            $this->tpl[$tpl_id]->set_var("buttons1", $this->tinymce_toolbar["default"]["buttons1"]);
            $this->tpl[$tpl_id]->set_var("buttons2", $this->tinymce_toolbar["default"]["buttons2"]);
            $this->tpl[$tpl_id]->set_var("buttons3", $this->tinymce_toolbar["default"]["buttons3"]);
            $this->tpl[$tpl_id]->set_var("buttons4", $this->tinymce_toolbar["default"]["buttons4"]);
            $this->tpl[$tpl_id]->set_var("buttons5", $this->tinymce_toolbar["default"]["buttons5"]);
            $this->tpl[$tpl_id]->set_var("buttons6", $this->tinymce_toolbar["default"]["buttons6"]);
        }
        
        $this->tpl[$tpl_id]->set_var("SectBinding", "");

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
