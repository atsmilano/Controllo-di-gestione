<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (jscalendar)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_kcuploadifive extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_kcuploadifive";

	var $widget_deps	= array();
	
	var $libraries		= array();
	
    var $js_deps = array(
							  "ff.ffField.kcuploadifive" => null
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
		global $plgCfg_kcuploadifive_UseOwnSession;
		//$Field->parent_page[0]->tplAddCss("jquery.uploadifive", "uploadifive.css", FF_SITE_PATH	 . "/themes/library/plugins/jquery.uploadifive");
		
		switch($Field->get_control_type())
		{
			case "picture":
			case "picture_no_link":
				//$this->process_picture($id, $value);
				//break;
			case "file_label":
			case "file":
				if($Field->file_show_filename)
                	$Field->file_show_filesize = true;
                	
				$Field->process_file($id, $value);
                if (count($Field->parent) && is_subclass_of($Field->parent[0], "ffDetails_base")) {
                    $suffix_start = "";
                    $suffix_target = "[name]";
                    $suffix_tmpname = "[tmpname]";
                    $suffix_delete = "[delete]";
                } else {
                    $suffix_start = "_file";
                    $suffix_target = "";
                    $suffix_tmpname = "_tmpname";
                    $suffix_delete = "_delete";
                }
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
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "kcuploadifive";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("id_kc", $id . $suffix_target);
        $this->tpl[$tpl_id]->set_var("suffix_start", $suffix_start);
        $this->tpl[$tpl_id]->set_var("suffix_target", $suffix_target);
        $this->tpl[$tpl_id]->set_var("suffix_tmpname", $suffix_tmpname);
        $this->tpl[$tpl_id]->set_var("suffix_delete", $suffix_delete);
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());
		$this->tpl[$tpl_id]->set_var("class", $this->class);
		$this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());
		
		$this->tpl[$tpl_id]->set_var("fixed_pre_content", $Field->fixed_pre_content);
		$this->tpl[$tpl_id]->set_var("fixed_post_content", $Field->fixed_post_content);

        $this->tpl[$tpl_id]->set_var("browse_class", cm_getClassByFrameworkCss("search", "icon", "lg"));

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/kcuploadifive");  
        
		if($Field->file_showfile_plugin) {
			$this->oPage[0]->tplAddJs("jquery." . $Field->file_showfile_plugin);

			$this->tpl[$tpl_id]->set_var("showfile_plugin", "'" . $Field->file_showfile_plugin . "'");
		} else {
			$this->tpl[$tpl_id]->set_var("showfile_plugin", "undefined");
		}

        $base_path = $Field->getFileBasePath();
        $storing_path = $Field->getFilePath();
		$folder = str_replace($base_path, "", $storing_path);
		
		if(!strlen($folder))
			$folder = "/";

		if(session_id() != '' && get_session("UserNID") != MOD_SEC_GUEST_USER_ID) { //if(session_status() == PHP_SESSION_NONE) {
			if ($plgCfg_kcuploadifive_UseOwnSession || $Field->actex_use_own_session) 
				session_start();
			$ff = get_session("ff");

	        $tmp = MD5($folder . "-" . $base_path . "-" . $Field->file_multi);
		}
		
        if($Field->extended_type == "File") {
        	//$this->tpl[$tpl_id]->set_var("base_url", $folder);
			if(session_id() != '' && get_session("UserNID") != MOD_SEC_GUEST_USER_ID) {//if(session_status() == PHP_SESSION_NONE) {
				$ff["uploadifive"][$tmp]["folder"] = $folder;
				$ff["uploadifive"][$tmp]["base_path"] = $base_path;
				
				$this->tpl[$tpl_id]->set_var("data_src", $tmp);
			}			

			$this->tpl[$tpl_id]->set_var("folder", $folder);
			$this->tpl[$tpl_id]->set_var("size_limit", $Field->file_max_size);
			
			$file_ext = "";
			if(is_array($Field->file_allowed_mime) && count($Field->file_allowed_mime)) 
			{
				foreach($Field->file_allowed_mime AS $file_allowed_mime_value) 
				{
					if(strlen($file_ext))
						$file_ext .= "|";
					if (strpos($file_allowed_mime_value, "/"))
						$file_ext .= ffMedia::getMimeTypeByExtension(substr($file_allowed_mime_value, strpos($file_allowed_mime_value, "/") + 1));
					else
						$file_ext .= ffMedia::getMimeTypeByExtension($file_allowed_mime_value);
				}
			}
			if(strlen($file_ext))
				$this->tpl[$tpl_id]->set_var("file_ext", "'" . $file_ext . "'");
			else
				$this->tpl[$tpl_id]->set_var("file_ext", "null");

			if(strlen($Field->file_normalize))
				$this->tpl[$tpl_id]->set_var("file_normalize", "true");
			else
				$this->tpl[$tpl_id]->set_var("file_normalize", "false");
			
			if($Field->file_widget_preview) {
				$this->tpl[$tpl_id]->set_var("preview_js", "true");
			} else {
				$this->tpl[$tpl_id]->set_var("preview_js", "false");
			}
            
            if($Field->file_writable) {
                $this->tpl[$tpl_id]->set_var("writable", "true");
            } else {
                $this->tpl[$tpl_id]->set_var("writable", "false");
			}
			
			$base_path = $Field->getFileBasePath();
        	$storing_path = $Field->getFilePath(false);
        	if($base_path && $storing_path) {
        		$base_url = str_replace($base_path, "", $storing_path);
        		if(!strlen($base_url))
        			$base_url = "/";
        			
        		$this->tpl[$tpl_id]->set_var("base_url_kc", $base_url);
			} else {
				$this->tpl[$tpl_id]->set_var("base_url_kc", "/");
			}
			$this->tpl[$tpl_id]->set_var("resource_type", basename($base_path));			

		} else {
			$this->tpl[$tpl_id]->set_var("preview_js", "false");
			$this->tpl[$tpl_id]->set_var("writable", "true");
			$this->tpl[$tpl_id]->set_var("size_limit", 0);
			$this->tpl[$tpl_id]->set_var("file_ext", "null");
		}
		$this->tpl[$tpl_id]->set_var("cancel_class", cm_getClassByFrameworkCss("cancel", "icon"));
        $this->tpl[$tpl_id]->set_var("aviary_class", cm_getClassByFrameworkCss("crop", "icon"));  
        $this->tpl[$tpl_id]->set_var("upload_class", cm_getClassByFrameworkCss("upload", "icon"));
        $this->tpl[$tpl_id]->set_var("upload_icon", cm_getClassByFrameworkCss("upload", "icon-tag", "lg"));
        
		if($Field->file_multi) {
			$this->tpl[$tpl_id]->set_var("multi", "true");
		} else {
			$this->tpl[$tpl_id]->set_var("multi", "false");
		}

        if($Field->file_show_edit) {
            $file_modify_path = ffMedia::MODIFY_PATH . "?key=" . $Field->file_modify_referer . "&path=";

            $this->tpl[$tpl_id]->set_var("showfile_path", "'" . $file_modify_path . "'");

            if($Field->file_modify_dialog) {
                if($Field->file_modify_dialog === true) {
                    $Field->file_modify_dialog = $Field->id . "_media";
                }

                $this->tpl[$tpl_id]->set_var("showfile_dialog", "'" . $Field->file_modify_dialog . "'");
            } else {
                $this->tpl[$tpl_id]->set_var("showfile_dialog", "undefined");
            }
        } else {
            $this->tpl[$tpl_id]->set_var("showfile_path", "undefined");
            $this->tpl[$tpl_id]->set_var("showfile_dialog", "undefined");
        }

        if($Field->file_sortable) {
            $this->oPage[0]->tplAddJs("jquery-ui");
            $this->tpl[$tpl_id]->set_var("showfile_sort", ($Field->file_sortable === true
                ? "true"
                : "'" . $Field->file_sortable . "'"
            ));
        } else {
            $this->tpl[$tpl_id]->set_var("showfile_sort", "undefined");
        }

        if(is_array($Field->file_thumb)) {
            $this->tpl[$tpl_id]->set_var("width", $Field->file_thumb["width"]);
            $this->tpl[$tpl_id]->set_var("height", $Field->file_thumb["height"]);
       } elseif(strlen($Field->file_thumb)) {
            $this->tpl[$tpl_id]->set_var("thumb_model", $Field->file_thumb);
        }

		if($Field->file_show_filename) {
			$this->tpl[$tpl_id]->set_var("show_file", "true");
		} else {
			$this->tpl[$tpl_id]->set_var("show_file", "false");
		}

        if($Field->file_full_path) {
            $this->tpl[$tpl_id]->set_var("full_path", "true");
        } else {
            $this->tpl[$tpl_id]->set_var("full_path", "false");
        }
		
        if ($Field->contain_error && $Field->error_preserve)
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->ori_value));
        else
            $this->tpl[$tpl_id]->set_var("value", ffCommon_specialchars($value->getValue($Field->get_app_type(), $Field->get_locale())));


        $this->tpl[$tpl_id]->set_var("aviary", "null");
        if ($Field->file_show_edit && is_array($Field->file_edit_params[$Field->file_edit_type])) {
            if(!$Field->file_edit_params[$Field->file_edit_type]["key"]) {
                $cache = ffCache::getInstance();
                $Field->file_edit_params[$Field->file_edit_type]["key"] = $cache->get($Field->file_edit_type . "/key");
            }
            if($Field->file_edit_params[$Field->file_edit_type]["key"]) {
                if(session_id() != '' && get_session("UserNID") != MOD_SEC_GUEST_USER_ID) {//if(session_status() == PHP_SESSION_NONE) {
                    $ff["aviary"][$tmp]["folder"] = $folder;
                    $ff["aviary"][$tmp]["base_path"] = $base_path;
                }

                $str_aviary = "'" . "img_hash" . "' : '" . $tmp . "'";
                foreach($Field->file_edit_params[$Field->file_edit_type] AS $params_key => $params_value) {
                    if(strlen($str_aviary ))
                        $str_aviary .= ", ";

                    $str_aviary .= "'" . "" . $params_key . "' : '" . $params_value . "'";
                }



                $this->tpl[$tpl_id]->set_var("aviary", "{" . $str_aviary . "}");
            }
        }
		
		if(session_id() != '' && get_session("UserNID") != MOD_SEC_GUEST_USER_ID)//if(session_status() == PHP_SESSION_NONE)
			set_session("ff", $ff);

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