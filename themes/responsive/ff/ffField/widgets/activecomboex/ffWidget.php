<?php
/**
 * @package theme_responsive
 * @subpackage widgets
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */

/**
 * @package theme_responsive
 * @subpackage widgets
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */
class ffWidget_activecomboex extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_activecomboex";

	var $widget_deps	= array(
		array(
			"name" => "dialog"
		)
	);
	
	var $libraries		= array();
	
    var $js_deps = array(
                              "ff.ffField.activecomboex"       => null
						);
    var $css_deps 		= array();

	var $disable_dialog = false;
	
	// PRIVATE VARS
	
	var $innerURL		= null;
	
	var $tpl 			= null;
	var $db				= null;

	var $display_debug	= false;

	var $oPage 			= null;
	var $source_path	= null;
	var $style_path 	= null;
	var $theme			= null;
	
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

		/*if ($this->innerURL === null)
			$this->tpl[$id]->set_var("innerURL", $this->source_path . "/ff/ffField/widgets/activecomboex/parsedata." . FF_PHP_EXT);
		else
			$this->tpl[$id]->set_var("innerURL", $this->innerURL);*/
	}

	function process($id, &$value, ffField_base &$Field)
	{
		global $plgCfg_ActiveComboEX_UseOwnSession;
        
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			$Field->parent[0]->processed_widgets[$prefix . $id] = "activecomboex";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		if (isset($Field->db[0]))
			$db =& $Field->db[0];
		else
			$db =& $this->db[0];

		//if($Field->parent_page[0]->jquery_ui_theme) {
			$Field->parent_page[0]->tplAddCss("jquery-ui.autocomplete");
		//}		
			
		$this->tpl[$tpl_id]->set_var("SectControl", "");
		$this->tpl[$tpl_id]->set_var("SectDataEl", "");

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("name", $Field->id);
		$this->tpl[$tpl_id]->set_var("class", $Field->get_control_class()); 
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		if($this->theme !== null) 
			$this->tpl[$tpl_id]->set_var("theme", $this->theme);
		else
			$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());

		
		if ($Field->actex_service === null)
		{
			$this->tpl[$tpl_id]->set_var("service", "null");
			if ($this->innerURL !== null) {
				$this->tpl[$tpl_id]->set_var("innerURL", $this->innerURL);
				$this->tpl[$tpl_id]->parse("SectInnerUrl", false);
			}
			/*			
			if ($this->innerURL === null)
				$this->tpl[$tpl_id]->set_var("innerURL", $this->source_path . "/ff/ffField/widgets/actex/parsedata." . FF_PHP_EXT);
			else
				$this->tpl[$tpl_id]->set_var("innerURL", $this->innerURL);
			*/
		}
		else
			$this->tpl[$tpl_id]->set_var("service", "'" . $Field->actex_service . "'");

		if ($Field->actex_cache)
			$this->tpl[$tpl_id]->set_var("use_cache", "true");
		else
			$this->tpl[$tpl_id]->set_var("use_cache", "false");

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path); 
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/activecomboex"); 

		$count_editable = 0;
		if ($Field->actex_update_from_db && $Field->actex_dialog && $Field->actex_dialog_show_add && !$this->disable_dialog && strlen($Field->actex_dialog_url))
		{
			if (strlen($Field->actex_dialog_title))
				$dialog_title = $Field->actex_dialog_title;
			elseif (strlen($Field->label))
				$dialog_title = $Field->label;
			else
				$dialog_title = $Field->id;

			$link = $Field->parent_page[0]->widgets["dialog"]->process(
					"actex_dlg_" . $prefix . $id
					, array(
							"title"			=> $dialog_title
							, "url"			=> $Field->actex_dialog_url
							/*, "name"		=> '<img alt="add" src="' . FF_SITE_PATH . '/themes/' . $Field->parent_page[0]->getTheme() . '/images/icons/' . $Field->actex_dialog_icon_add .'"' . (strlen($Field->actex_dialog_title_add)  ? ' title="' . $Field->actex_dialog_title_add . '"' : '') . ' />'*/
							//, "callback"	=> (count($Field->resources) ? "ff.ffField.activecomboex.dialog_success('" . $prefix . $id . "', '" . $Field->resources[0] . "')" : "")
							, "tpl_id"		=> $tpl_id
							, "addjs"		=> "javascript:ff.ffField.activecomboex.insertModeOn('" . $prefix . $id . "', '" . "actex_dlg_" . $prefix . $id . "');"
							, "class"		=> cm_getClassByFrameworkCss("addnew", "icon")
						)
					, $Field->parent_page[0]
				);
			$this->tpl[$tpl_id]->set_var("dialoglink", $link);
			$this->tpl[$tpl_id]->parse("SectDialog", false);
			
			$count_editable++;
		}
		else
		{
			$this->tpl[$tpl_id]->set_var("dialoglink", "");
			$this->tpl[$tpl_id]->set_var("SectDialog", "");
		}

		$edit_url = ($Field->actex_dialog_edit_url ? $Field->actex_dialog_edit_url : $Field->actex_dialog_url);
		$params = $Field->actex_dialog_edit_params;
		if (
				$Field->actex_update_from_db
				&& $Field->actex_dialog
				&& $Field->actex_dialog_show_edit
				&& !$this->disable_dialog
				&& strlen($edit_url)
				&& (
						(is_array($params) && count($params))
						|| (strpos($edit_url, "[[") !== false)
					)
			)
		{
			if (strlen($Field->actex_dialog_edit_title))
				$dialog_edit_title = $Field->actex_dialog_edit_title;
			elseif (strlen($Field->actex_dialog_title))
				$dialog_edit_title = $Field->actex_dialog_title;
			elseif (strlen($Field->label))
				$dialog_edit_title = $Field->label;
			else
				$dialog_edit_title = $Field->id;

			if (strpos($edit_url, "?") === false)
				$edit_url .= "?";
			elseif (substr($edit_url, -1) !== "&")
				$edit_url .= "&";
			if(is_array($params) && count($params)) 
			{
				foreach ($params as $param_key => $param_value)
				{
					if ($param_value === null)
						$edit_url .= $param_key . "=[[" . $prefix . $id . "]]&";
					else
						$edit_url .= $param_key . "=[[" . $param_value . "]]&";
				}
			}
			$this->tpl[$tpl_id]->set_var("dialogeditlink", $Field->parent_page[0]->widgets["dialog"]->process(
					"actex_dlg_edit_" . $prefix . $id
					, array(
							"title"			=> $dialog_title
							, "url"			=> $edit_url
							/*, "name"		=> '<img alt="edit" src="' . FF_SITE_PATH . '/themes/' . $Field->parent_page[0]->getTheme() . '/images/icons/' . $Field->actex_dialog_icon_edit .'" ' . (strlen($Field->actex_dialog_title_edit)  ? ' title="' . $Field->actex_dialog_title_edit . '"' : '') . ' />'*/
//							, "callback"	=> "ff.ffField.activecomboex.dialog_success('" . $prefix . $id . "', 'actex_dlg_edit_" . $Field->parent[0]->id . "_" . $Field->id . "')"
							, "tpl_id"		=> $tpl_id
							, "class"		=> cm_getClassByFrameworkCss("editrow", "icon")
						)
					, $Field->parent_page[0]
				));
			$this->tpl[$tpl_id]->parse("SectDialogEdit", false);
			
			$count_editable++;
		}
		else
		{
			$this->tpl[$tpl_id]->set_var("dialogeditlink", "");
			$this->tpl[$tpl_id]->set_var("SectDialogEdit", "");
		}

		$params = ($Field->actex_dialog_delete_params ? $Field->actex_dialog_delete_params : $Field->actex_dialog_edit_params);
		if (
				$Field->actex_update_from_db
				&& $Field->actex_dialog
				&& $Field->actex_dialog_show_delete
				&& !$this->disable_dialog
				&& strlen($Field->actex_dialog_delete_url)
				&& (
						(is_array($params) && count($params))
						|| (strpos($Field->actex_dialog_delete_url, "[[") !== false)
					)
			)
		{
			if (strlen($Field->actex_dialog_delete_title))
				$dialog_delete_title = $Field->actex_dialog_delete_title;
			elseif (strlen($Field->actex_dialog_title))
				$dialog_delete_title = $Field->actex_dialog_title;
			elseif (strlen($Field->label))
				$dialog_delete_title = $Field->label;
			else
				$dialog_delete_title = $Field->id;

			$delete_url = $Field->actex_dialog_delete_url;
			if (is_array($params) && count($params))
			{
				if (strpos($delete_url, "?") === false)
					$delete_url .= "?";
				elseif (substr($delete_url, -1) !== "&")
					$delete_url .= "&";
				foreach ($params as $param_key => $param_value)
				{
					if ($param_value === null)
						$delete_url .= $param_key . "=[[" . $prefix . $id . "]]&";
					else
						$delete_url .= $param_key . "=[[" . $param_value . "]]&";
				}
			}

			if(method_exists($Field->parent[0], "dialog")) {
				$dialog_delete = $Field->parent[0]->dialog(
													  true
													, "yesno"
													, $dialog_delete_title
													, $Field->actex_dialog_delete_message
													, "[CLOSEDIALOG]"
													, $delete_url
												);
			} else {
				$dialog_delete = ffDialog(true, "yesno", $dialog_delete_title, $Field->actex_dialog_delete_message, "[CLOSEDIALOG]", $delete_url, "/dialog");
			}
			$this->tpl[$tpl_id]->set_var("dialogdeletelink", $Field->parent_page[0]->widgets["dialog"]->process(
					"actex_dlg_delete_" . $prefix . $id
					, array(
							"title"			=> $dialog_delete_title
							, "url"			=> $dialog_delete
							/*, "name"		=> '<img alt="delete" src="' . FF_SITE_PATH . '/themes/' . $Field->parent_page[0]->getTheme() . '/images/icons/' . $Field->actex_dialog_icon_delete .'"' . (strlen($Field->actex_dialog_title_delete)  ? ' title="' . $Field->actex_dialog_title_delete . '"' : '') . ' />'*/
//							, "callback"	=> "ff.ffField.activecomboex.dialog_success('" . $prefix . $id . "', 'actex_dlg_delete_" . $Field->parent[0]->id . "_" . $Field->id . "')"
							, "tpl_id"		=> $tpl_id
							, "class"		=> cm_getClassByFrameworkCss("deleterow", "icon")
						)
					, $Field->parent_page[0]
				));
			$this->tpl[$tpl_id]->parse("SectDialogDelete", false);
			
			$count_editable++;
		}
		else
		{
			$this->tpl[$tpl_id]->set_var("dialogdeletelink", "");
			$this->tpl[$tpl_id]->set_var("SectDialogDelete", "");
		}

		if($count_editable) 
		{
			$this->tpl[$tpl_id]->set_var("data_class", "activecomboex editable" . (strlen($Field->data_class) ? " " : "") . $Field->data_class);
			//$this->tpl[$tpl_id]->set_var("editable", " editable");
			$this->tpl[$tpl_id]->parse("SectEditable", false);
		} 
		else 
		{
			$this->tpl[$tpl_id]->set_var("data_class", "activecomboex" . (strlen($Field->data_class) ? " " : "") . $Field->data_class);
			$this->tpl[$tpl_id]->set_var("SectEditable", "");
		}
		
		if (substr($id, -4) == "_src")
			$suffix = "_src";
		
		if ($Field->actex_father === null)
			$father = null;
		else if (ffIsset($Field->cont_array, $Field->actex_father))
			$father = $Field->cont_array[$Field->actex_father];
		else
			$father = $Field->actex_father[0];
		
		if ($Field->actex_child === null)
			$child = null;
		else if (is_array($Field->actex_child))
		{
			$i = -1;
			foreach ($Field->actex_child as $key => $element)
				{
					$i++;
					if (isset($Field->cont_array[$element]))
						$child[$i] = $Field->cont_array[$element];
					else
						$child[$i] = $Field->actex_child[$key];
				}
			reset($Field->actex_child);
		}
		else if (isset($Field->cont_array[$Field->actex_child]))
		{
			$child[0] = $Field->cont_array[$Field->actex_child];
		}
		else
			ffErrorHandler::raise("Cannot determine child in activecombo!", E_USER_ERROR, $this, get_defined_vars());
		
		if ($father === null)
			$this->tpl[$tpl_id]->set_var("father", "null");
		else
		{
			if ($Field->row === null)
				$this->tpl[$tpl_id]->set_var("father", "\"" . $prefix . $father->id . $suffix . "\"");
			else
				$this->tpl[$tpl_id]->set_var("father", "\"" . $prefix . "recordset[" . $Field->row . "][" . $father->id . "]\"");
		}
			
		$this->tpl[$tpl_id]->set_var("SectChild", "");
        if ($child !== null)
		{
			foreach ($child as $key => $element)
			{
				if ($Field->row === null)
					$this->tpl[$tpl_id]->set_var("child", ($key > 0 ? "," : "") . "\"" . $prefix . $child[$key]->id . $suffix . "\"");
				else
					$this->tpl[$tpl_id]->set_var("child", ($key > 0 ? "," : "") . "\"" . $prefix . "recordset[" . $Field->row . "][" . $child[$key]->id . "]\"");
				$this->tpl[$tpl_id]->set_var("n", $key);
				$this->tpl[$tpl_id]->parse("SectChild", true);
			}
			reset($child);
		}

		$property_set = $Field->properties;
		if(!is_array($Field->properties))
			$Field->properties = array();

		if (isset($property_set["onchange"]))
			ffErrorHandler::raise("DEPRECATED - use ->actex_on_change instead", E_USER_ERROR, $this, get_defined_vars());

		$this->tpl[$tpl_id]->set_var("properties", str_replace("'", "\'", $Field->getProperties($property_set)));
		$this->tpl[$tpl_id]->set_var("data_properties", str_replace("'", "\'", $Field->getProperties($Field->data_properties)));
		
		if ($Field->actex_on_change !== null)
		{
			$this->tpl[$tpl_id]->set_var("on_change", $Field->actex_on_change);
			$this->tpl[$tpl_id]->parse("SectEventChange", false);
		}
		else
			$this->tpl[$tpl_id]->set_var("SectEventChange", "");
		
		if ($Field->actex_on_update_bt !== null)
		{
			$this->tpl[$tpl_id]->set_var("on_update_bt", $Field->actex_on_update_bt);
			$this->tpl[$tpl_id]->parse("SectEventUpdateBt", false);
		}
		else
			$this->tpl[$tpl_id]->set_var("SectEventUpdateBt", "");

		if ($Field->actex_on_refill !== null)
		{
			$this->tpl[$tpl_id]->set_var("on_refill", $Field->actex_on_refill);
			$this->tpl[$tpl_id]->parse("SectEventRefill", false);
		}
		else
			$this->tpl[$tpl_id]->set_var("SectEventRefill", "");
			
		if ($Field->multi_limit_select)
			$this->tpl[$tpl_id]->set_var("limit_select", "true");
		else
			$this->tpl[$tpl_id]->set_var("limit_select", "false");

		if (strlen($Field->properties["disabled"]))
			$this->tpl[$tpl_id]->set_var("disabled", "true");
		else
			$this->tpl[$tpl_id]->set_var("disabled", "false");

        $this->tpl[$tpl_id]->set_var("separator", $Field->grouping_separator);

        // TODO: mettere un parametro nell'activecombo
		if($Field->control_type == "")
			$Field->control_type = "combo";

		$this->tpl[$tpl_id]->set_var("control_type", $Field->get_control_type());

        if ($Field->actex_add_plus)
            $this->tpl[$tpl_id]->set_var("add_plus", "true");
        else
            $this->tpl[$tpl_id]->set_var("add_plus", "false");

        if($Field->actex_hide_empty === true)
        	$this->tpl[$tpl_id]->set_var("hide_empty", "true");
        elseif(strlen($Field->actex_hide_empty))
            $this->tpl[$tpl_id]->set_var("hide_empty", "'" . $Field->actex_hide_empty . "'");
        else
            $this->tpl[$tpl_id]->set_var("hide_empty", "false");

        $this->tpl[$tpl_id]->set_var("icon_caret_down", cm_getClassByFrameworkCss("more", "icon"));    
        $this->tpl[$tpl_id]->set_var("icon_plus", cm_getClassByFrameworkCss("plus", "icon"));    
        $this->tpl[$tpl_id]->set_var("icon_minus", cm_getClassByFrameworkCss("minus", "icon"));        
        $this->tpl[$tpl_id]->set_var("icon_loader", cm_getClassByFrameworkCss("spinner", "icon-tag", "spin"));

        if(is_array($Field->actex_plugin) 
        	&& count($Field->actex_plugin)
        	&& strlen($Field->actex_plugin["name"])
        	&& strlen($Field->actex_plugin["path"])
        	&& strlen($Field->actex_plugin["js"])
        ) {

			$this->tpl[$tpl_id]->set_var("plugin_name", $Field->actex_plugin["name"]);	
			$this->tpl[$tpl_id]->set_var("plugin_path", $Field->actex_plugin["path"]);	
			$this->tpl[$tpl_id]->set_var("plugin_css", $Field->actex_plugin["css"]);	
			$this->tpl[$tpl_id]->set_var("plugin_js", $Field->actex_plugin["js"]);	
			if(is_array($Field->actex_plugin["params"]) && count($Field->actex_plugin["params"])) {
				$this->tpl[$tpl_id]->set_var("plugin_params", ffCommon_jsonenc($Field->actex_plugin["params"]));	
			} else {
				$this->tpl[$tpl_id]->set_var("plugin_params", "undefined");
			}
			$this->tpl[$tpl_id]->parse("SezPlugin", false);
        } else {
			$this->tpl[$tpl_id]->set_var("SezPlugin", "");
        }
            
        if($Field->autocomplete_label) {
        	$this->tpl[$tpl_id]->set_var("autocomplete_label", $Field->autocomplete_label);
        	$this->tpl[$tpl_id]->parse("SectControlLabel", false);
		} else {
        	$this->tpl[$tpl_id]->set_var("SectControlLabel", "");
		}

        
		if ($Field->multi_select_one && !$Field->multi_limit_select)
		{
			if ($Field->multi_select_one_val !== null && !$Field->multi_limit_select)
				$this->tpl[$tpl_id]->set_var("select_one_val", $Field->multi_select_one_val->getValue($Field->get_app_type(), $Field->get_locale()));
			else
				$this->tpl[$tpl_id]->set_var("select_one_val", "");
			$this->tpl[$tpl_id]->set_var("select_one_label", $Field->multi_select_one_label);
			$this->tpl[$tpl_id]->set_var("select_one", "true");
		}
		else
			$this->tpl[$tpl_id]->set_var("select_one", "false");
			
		if ($Field->multi_select_noone && 
							(!$Field->multi_limit_select || 
								($Field->multi_limit_select && $value->getValue($Field->get_app_type(), $Field->get_locale()) == $Field->multi_select_noone_val->getValue($Field->get_app_type(), $Field->get_locale()))
							)
			)
		{
			if ($Field->multi_select_noone_val !== null)
				$this->tpl[$tpl_id]->set_var("select_noone_val", $Field->multi_select_noone_val->getValue($Field->get_app_type(), $Field->get_locale()));
			else
				$this->tpl[$tpl_id]->set_var("select_noone_val", "");
			$this->tpl[$tpl_id]->set_var("select_noone_label", $Field->multi_select_noone_label);
			$this->tpl[$tpl_id]->set_var("select_noone", "true");
		}
		else
			$this->tpl[$tpl_id]->set_var("select_noone", "false");
			
		$this->tpl[$tpl_id]->set_var("SectData", "");
		$this->tpl[$tpl_id]->set_var("data_src", "");		
		$no_rec = true;
		
		if (strlen($tmp_sql = $Field->getSQL()) && $Field->actex_update_from_db)
		{
            if($Field->actex_service === null) 
            {
				$tmp = md5($tmp_sql);
                
			    if (!defined("FF_ACTEX_SESSION_STARTED") && ($plgCfg_ActiveComboEX_UseOwnSession || $Field->actex_use_own_session))
			    {
				    if (!isset($_COOKIE[session_name()]))
				    {
					    if (isset($_POST[session_name()]))
						    session_id($_POST[session_name()]);
					    elseif (isset($_GET[session_name()]))
						    session_id($_GET[session_name()]);
				    }
				    session_start();
				    if (!defined("FF_ACTEX_SESSION_STARTED"))
					    define("FF_ACTEX_SESSION_STARTED", true);
			    }

			    $ff = get_session("ff");
			    $ff["activecomboex"][$tmp]["sql"] 							= $tmp_sql;
			    $ff["activecomboex"][$tmp]["field"] 						= $Field->actex_related_field;
			    $ff["activecomboex"][$tmp]["operation"] 					= $Field->actex_operation_field;
			    $ff["activecomboex"][$tmp]["skip_empty"] 					= $Field->actex_skip_empty;
			    $ff["activecomboex"][$tmp]["group"] 						= $Field->actex_group;
			    $ff["activecomboex"][$tmp]["attr"] 							= $Field->actex_attr;
			    $ff["activecomboex"][$tmp]["main_db"] 						= $Field->actex_use_main_db;
			    $ff["activecomboex"][$tmp]["hide_result_on_query_empty"] 	= $Field->actex_hide_result_on_query_empty;
			    $ff["activecomboex"][$tmp]["preserve_field"] 				= $Field->actex_preserve_field;

			    //$ff["activecomboex"][$tmp]["preserve_having"] = $Field->actex_preserve_having;
			    set_session("ff", $ff);

    //			set_session("activecomboex_sql_" . $tmp, $tmp_sql);
    //			set_session("activecomboex_field_" . $tmp, $Field->actex_related_field);
    //			set_session("activecomboex_main_db_" . $tmp, $Field->actex_use_main_db);
			    
			    $this->tpl[$tpl_id]->set_var("data_src", $tmp);
			    $this->tpl[$tpl_id]->set_var("SectData", "");
            }
		}
		else if (strlen($tmp_sql))
		{
			$this->tpl[$tpl_id]->set_var("data_src", "");
			$db->query($tmp_sql);
			if ($db->nextRecord())
			{
				$n = -1;
				do
				{
					$n++;
					$this->tpl[$tpl_id]->set_var("n", $n);

					if ($n > 0)
						$this->tpl[$tpl_id]->set_var("data_comma", ",");
					else
						$this->tpl[$tpl_id]->set_var("data_comma", "");


					if ($father === null)
						$this->tpl[$tpl_id]->set_var("father_value", "null");
					else
					{
						$tmp = $db->getField($db->fields_names[0], $father->base_type);
						//$tmp = $db->getResult(null, 0, $father->base_type);
						$this->tpl[$tpl_id]->set_var("father_value", "\"" . str_replace('"', '\"', $tmp->getValue($father->get_app_type(), $father->get_locale())) . "\"");
					}
					$tmp = $db->getField($db->fields_names[1], $Field->base_type);
					//$tmp = $db->getResult(null, 1, $Field->base_type);
					$this->tpl[$tpl_id]->set_var("value", str_replace('"', '\"', $tmp->getValue($Field->get_app_type(), $Field->get_locale())));
					$tmp = $db->getField($db->fields_names[2], $Field->multi_base_type);
					//$tmp = $db->getResult(null, 2, $Field->multi_base_type);
					$this->tpl[$tpl_id]->set_var("desc", str_replace('"', '\"', $tmp->getValue($Field->multi_app_type, $Field->get_locale())));

					$this->tpl[$tpl_id]->parse("SectDataEl", true);
				}
				while ($db->nextRecord());
				$this->tpl[$tpl_id]->parse("SectData", false);
			}
			else
				$this->tpl[$tpl_id]->set_var("SectData", "");
		}
		else if (is_array($Field->multi_pairs) && count($Field->multi_pairs))
		{
			$n = -1;
			foreach($Field->multi_pairs as $key => $item)
			{
				$n++;
				$this->tpl[$tpl_id]->set_var("n", $n);

				if ($n > 0)
					$this->tpl[$tpl_id]->set_var("data_comma", ",");
				else
					$this->tpl[$tpl_id]->set_var("data_comma", "");
				
				if ($father === null) {
					$this->tpl[$tpl_id]->set_var("father_value", "null");
				} else {
					list($item_key, $father_id) = each($item); 					
					$this->tpl[$tpl_id]->set_var("father_value", "\"" . str_replace('"', '\"', $father_id->getValue($father->base_type, $father->get_locale())) . "\"");
				}

				list($item_key, $child_id) = each($item);
				list($item_key, $child_value) = each($item);

				$this->tpl[$tpl_id]->set_var("value", str_replace('"', '\"', $child_id->getValue($Field->get_app_type(), $Field->get_locale())));
				$this->tpl[$tpl_id]->set_var("desc", str_replace('"', '\"', $child_value->getValue($Field->multi_app_type, $Field->get_locale())));

				$this->tpl[$tpl_id]->parse("SectDataEl", true);
			}
			reset($Field->multi_pairs);
			$this->tpl[$tpl_id]->parse("SectData", false);
		}
		else
			$this->tpl[$tpl_id]->set_var("SectData", "");

		/*if ($Field->actex_father === null)
			$this->tpl[$tpl_id]->set_var("father_value", "null");
		else
		{
			if ($Field->row === null)
				$this->tpl[$tpl_id]->set_var("father_value", "\"" . $father->value->getValue($father->get_app_type(), $father->get_locale()) . "\"");
			else	
				$this->tpl[$tpl_id]->set_var("father_value", "\"" . $Field->parent[0]->recordset[$father->row][$father->id]->getValue($father->get_app_type(), $father->get_locale()) . "\"");
		}*/

		if (
				$value == null
				|| ($value->ori_value === "" && $Field->multi_select_one && $Field->multi_select_one_val === null)
			)
			$this->tpl[$tpl_id]->set_var("selected_value", "null");
		else
			$this->tpl[$tpl_id]->set_var("selected_value", "\"" . $value->getValue($Field->get_app_type(), $Field->get_locale()) . "\"");

		$this->tpl[$tpl_id]->parse("SectBinding", true);
		
        if ($father === null)
			$this->tpl[$tpl_id]->parse("SectBindingFoot", true);

		if ($this->display_debug)
			$this->tpl[$tpl_id]->parse("SectDebug", false);
		else
			$this->tpl[$tpl_id]->set_var("SectDebug", "");

 		return $Field->fixed_pre_content . $this->tpl[$tpl_id]->rpparse("SectControl", false) . $Field->fixed_post_content;
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
