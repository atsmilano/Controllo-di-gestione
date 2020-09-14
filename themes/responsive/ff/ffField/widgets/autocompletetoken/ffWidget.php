<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN DEFINITION (activecomboex)
//			   by Samuele Diella
// ----------------------------------------

class ffWidget_autocompletetoken extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";
	
	var $class			= "ffWidget_autocompletetoken";

	var $widget_deps	= array();
    
	var $libraries		= array();
            
    var $js_deps = array(
                              "jquery.tokeninput"       => null
						);
    var $css_deps 		= array(
    					);

	var $disable_dialog = false;
	
	// PRIVATE VARS
	
	var $innerURL		= null;
	
	var $tpl 			= null;
	var $db				= null;

	var $display_debug	= false;

	var $oPage 			= null;
	var $source_path	= null;
	var $style_path 	= null;
	
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
			$this->tpl[$id]->set_var("innerURL", $this->source_path . "/ff/ffField/widgets/autocompletetoken/parsedata." . FF_PHP_EXT . "?");
		else
			$this->tpl[$id]->set_var("innerURL", (strpos($this->innerURL, "?") === false ? $this->innerURL : substr($this->innerURL, 0, strpos($this->innerURL, "?"))));*/
			
	}

	function process($id, &$value, ffField_base &$Field)
	{
		global $plgCfg_autocompletetoken_UseOwnSession;
        
		if ($Field->parent !== null && strlen($Field->parent[0]->getIDIF()))
		{
			$tpl_id = $Field->parent[0]->getIDIF();
			$prefix = $tpl_id . "_";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
			$this->tpl[$tpl_id]->set_var("component", $tpl_id);
			$this->tpl[$tpl_id]->set_var("container", $prefix);
			//$Field->parent[0]->processed_widgets[$prefix . $id] = "autocompletetoken";
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

		$this->tpl[$tpl_id]->set_var("SectControl", "");

		if(strpos($id, "[") === false) {
			$this->tpl[$tpl_id]->set_var("id_encoded", $id);
		} else {
			$this->tpl[$tpl_id]->set_var("id_encoded", str_replace("[", '\\\\[', str_replace("]", '\\\\]', $id)));
		}

		$this->tpl[$tpl_id]->set_var("id", $id);
		$this->tpl[$tpl_id]->set_var("class", $Field->get_control_class()); 
		$this->tpl[$tpl_id]->set_var("site_path", $Field->parent_page[0]->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $Field->getTheme());
        $this->tpl[$tpl_id]->set_var("properties", $Field->getProperties());

        if(strlen($Field->widget_path))
            $this->tpl[$tpl_id]->set_var("widget_path", $Field->widget_path);
        else 
            $this->tpl[$tpl_id]->set_var("widget_path", "/themes/responsive/ff/ffField/widgets/autocompletetoken"); 

        if(strlen($Field->autocompletetoken_theme)) {
        	$css_file = "token-input-" . $Field->autocompletetoken_theme . ".css";
		} else {
			$css_file = "token-input.css";
		}
		
		$this->oPage[0]->tplAddCss("jquery-ui.tokeninput-" . $Field->autocompletetoken_theme, array(
			"path" => "/themes/library/plugins/jquery.tokeninput"
			, "file" => $css_file
			, "index" => 200
		));
        
        $arrValue = array();
        if ($value == null || !$value->getValue($Field->get_app_type(), $Field->get_locale())) {
        	$this->tpl[$tpl_id]->set_var("arr_values", "[]");
            //$this->tpl[$tpl_id]->set_var("selected_value", "");
            //$this->tpl[$tpl_id]->set_var("selected_id", "");
        } else {
        	if($Field->autocompletetoken_limit == "1") {
        		$arrValue[0]["id"] = $value->getValue($Field->get_app_type(), $Field->get_locale());
        		$arrValue[0]["name"] = $Field->getDisplayValue();
			} else {
        		$tmp_values = explode($Field->autocompletetoken_delimiter, $value->getValue($Field->get_app_type(), $Field->get_locale()));
        		if(is_array($tmp_values) && count($tmp_values)) {
        			$count_item = 0;
        			foreach($tmp_values AS $data_value) {
        				$arrValue[$count_item]["id"] = $data_value;
                        $arrValue[$count_item]["name"] = $Field->getDisplayValue($Field->get_app_type(), $Field->get_locale(), new ffData($data_value, $Field->get_app_type(), $Field->get_locale()));
                        
                        /*$key_value = explode("@", $data_value);
        				
        				$arrValue[$count_item]["id"] = $key_value[0];
        				if(isset($key_value[1]))
        					$arrValue[$count_item]["name"] = $key_value[1];
        				else
        					$arrValue[$count_item]["name"] = $key_value[0];
						 */
						$count_item++;
					}
				}
			}

        	if(count($arrValue))
        		$this->tpl[$tpl_id]->set_var("arr_values", json_encode($arrValue));
        	else
        		$this->tpl[$tpl_id]->set_var("arr_values", "[]");
//            $this->tpl[$tpl_id]->set_var("selected_value", $Field->getDisplayValue());
//            $this->tpl[$tpl_id]->set_var("selected_id", $value->getValue($Field->get_app_type(), $Field->get_locale()));
        }

        $this->tpl[$tpl_id]->set_var("delay", $Field->autocompletetoken_delay);
        $this->tpl[$tpl_id]->set_var("autocomplete_theme", $Field->autocompletetoken_theme);
        $this->tpl[$tpl_id]->set_var("not_found_label", $Field->autocompletetoken_not_found_label);
        $this->tpl[$tpl_id]->set_var("init_label", $Field->autocompletetoken_init_label);
        $this->tpl[$tpl_id]->set_var("searching_label", $Field->autocompletetoken_searching_label);
		$this->tpl[$tpl_id]->set_var("delimiter", $Field->autocompletetoken_delimiter);
        $this->tpl[$tpl_id]->set_var("limit", $Field->autocompletetoken_limit);

        if($Field->autocompletetoken_combo) {
            $this->tpl[$tpl_id]->set_var("combo_class", "autocompletetoken-combo " . cm_getClassByFrameworkCss("caret-down", "icon"));
        	$this->tpl[$tpl_id]->parse("SectCombo", false);
		} else {
            $this->tpl[$tpl_id]->set_var("minLength", $Field->autocompletetoken_minLength);
			$this->tpl[$tpl_id]->set_var("SectCombo", "");
		}
		
		if ($Field->actex_service === null)
		{
			if ($this->innerURL === null) {
				$this->tpl[$tpl_id]->set_var("service", FF_SITE_PATH . "/atparsedata");
			} else {
				$this->tpl[$tpl_id]->set_var("service", $this->innerURL);
			}
		}
		else
			$this->tpl[$tpl_id]->set_var("service", $Field->actex_service);


                
        if($Field->autocompletetoken_label) {
        	$this->tpl[$tpl_id]->set_var("autocompletetoken_label", $Field->autocompletetoken_label);
        	$this->tpl[$tpl_id]->parse("SectControlLabel", false);
		} else {
        	$this->tpl[$tpl_id]->set_var("SectControlLabel", "");
		}
        
		$this->tpl[$tpl_id]->set_var("SectData", "");
		//$this->tpl[$tpl_id]->set_var("data_src", "");
		
		if (strlen($Field->source_SQL))
		{
            if($Field->actex_service === null)
            {
			    if($father === null) {
				    $data_src = md5($Field->source_SQL);
			    } else {
				    $data_src = md5($Field->source_SQL . "-" . $father->getValue());
			    }
			    
			    if (!defined("FF_AUTOCOMPLETE_TOKEN_SESSION_STARTED") && ($plgCfg_autocompletetoken_UseOwnSession || $Field->actex_use_own_session))
			    {
				    if (!isset($_COOKIE[session_name()]))
				    {
					    if (isset($_POST[session_name()]))
						    session_id($_POST[session_name()]);
					    elseif (isset($_GET[session_name()]))
						    session_id($_GET[session_name()]);
				    }
				    session_start();
				    if (!defined("FF_AUTOCOMPLETE_TOKEN_SESSION_STARTED"))
					    define("FF_AUTOCOMPLETE_TOKEN_SESSION_STARTED", true);
			    }

			    $ff = get_session("ff");
			    $ff["autocompletetoken"][$data_src]["sql"] 							= $Field->source_SQL;
                $ff["autocompletetoken"][$data_src]["attr"] 							= $Field->actex_attr;
			    $ff["autocompletetoken"][$data_src]["main_db"] 						= $Field->actex_use_main_db;
			    $ff["autocompletetoken"][$data_src]["hide_result_on_query_empty"] 	= $Field->actex_hide_result_on_query_empty;
			    $ff["autocompletetoken"][$data_src]["limit"] 						= $Field->autocompletetoken_res_limit;
			    
			    
			    $ff["autocompletetoken"][$data_src]["compare"]						= $Field->autocompletetoken_compare;
			    $ff["autocompletetoken"][$data_src]["compare_having"] 				= $Field->autocompletetoken_compare_having;
			    $ff["autocompletetoken"][$data_src]["operation"]						= $Field->autocompletetoken_operation;
			    $ff["autocompletetoken"][$data_src]["concat_field"]					= $Field->autocompletetoken_concat_field;
			    $ff["autocompletetoken"][$data_src]["concat_separator"]				= $Field->autocompletetoken_concat_separator;

			    set_session("ff", $ff);

    //			set_session("autocompletetoken_sql_" . $tmp, $Field->source_SQL);
    //			set_session("autocompletetoken_main_db_" . $tmp, $Field->actex_use_main_db);
			    //$this->tpl[$tpl_id]->set_var("data_src", $tmp);
			    $this->tpl[$tpl_id]->set_var("SectData", "");
            }
		}
		else if (is_array($Field->multi_pairs) && count($Field->multi_pairs))
		{
			$n = -1;
			foreach($Field->multi_pairs as $key => $item)
			{
				if(!(count($item) > 0)) 
                    continue;

                $n++;

				if ($n > 0)
					$this->tpl[$tpl_id]->set_var("data_comma", ",");
				else
					$this->tpl[$tpl_id]->set_var("data_comma", "");
				
                if(count($item) == 1) {
                    list($item_key, $elem_id) = each($item);
                    $this->tpl[$tpl_id]->set_var("value", str_replace('"', '\"', $elem_id->getValue($Field->get_app_type(), $Field->get_locale())));
                    $this->tpl[$tpl_id]->set_var("label", str_replace('"', '\"', $elem_id->getValue($Field->get_app_type(), $Field->get_locale())));
                    $this->tpl[$tpl_id]->set_var("cat", "");
                } else {
                    list($item_key, $elem_id) = each($item);
                    $this->tpl[$tpl_id]->set_var("value", str_replace('"', '\"', $elem_id->getValue($Field->get_app_type(), $Field->get_locale())));
                    
				    if(count($item) >= 2) {
                        list($item_key, $elem_value) = each($item);
                        $this->tpl[$tpl_id]->set_var("label", str_replace('"', '\"', $elem_value->getValue($Field->get_app_type(), $Field->get_locale())));
                    }
                    if(count($item) >= 3) {
                    list($item_key, $elem_cat) = each($item);
                        $this->tpl[$tpl_id]->set_var("cat", str_replace('"', '\"', $elem_cat->getValue($Field->multi_app_type, $Field->get_locale())));
                    } else {
                        $this->tpl[$tpl_id]->set_var("cat", "");
                    }
                }

				$this->tpl[$tpl_id]->parse("SectData", true);
			}
			reset($Field->multi_pairs);
		}
		else
			$this->tpl[$tpl_id]->set_var("SectData", "");

		$srv_data[] = "type=autocomplete";
		if($data_src)
			$srv_data[] = "data_src=" . $data_src;
/*		if($Field->autocompletetoken_compare)
			$srv_data[] = "compare=" . $Field->autocompletetoken_compare;
		if($Field->autocompletetoken_compare_having)
			$srv_data[] = "compareh=" . $Field->autocompletetoken_compare_having;
		if($Field->autocompletetoken_operation)
			$srv_data[] = "operation=" . $Field->autocompletetoken_operation;
*/			
		if(is_array($srv_data) && count($srv_data))
			$this->tpl[$tpl_id]->set_var("srv_data", "?" . implode("&", $srv_data));
		
		$this->tpl[$tpl_id]->set_var("SectAttrResult", "");
		$this->tpl[$tpl_id]->set_var("SectAttrToken", "");

		if(is_array($Field->actex_attr) && count($Field->actex_attr)) {
			foreach($Field->actex_attr AS $attr_key => $attr_value) {
				if($attr_key == "image")
					continue;

				$this->tpl[$tpl_id]->set_var("attr_name", $attr_key);
				$this->tpl[$tpl_id]->parse("SectAttrResult", true);
				$this->tpl[$tpl_id]->parse("SectAttrToken", true);
			}			
			$this->tpl[$tpl_id]->parse("SectAttr", false);
		} else {
			$this->tpl[$tpl_id]->set_var("SectAttr", "");
		}
			
		$this->tpl[$tpl_id]->parse("SectBinding", true);

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
