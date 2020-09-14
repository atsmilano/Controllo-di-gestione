<?php
function mod_restricted_on_tpl_layer_loaded($page, $tpl)
{
    $cm = cm::getInstance();
  //AVATAR USERNAME
    $tpl->set_var("CM_LOCAL_APP_NAME", ffCommon_specialchars(cm_getAppName()));
	if (MOD_RES_DEVELOPER)
		$tpl->parse("SectFooter", false);
}	

function mod_restricted_cm_on_load_topbar($page, $tpl, $location, $attr)
{
	$cm = cm::getInstance();
	$globals_mod = ffGlobals::getInstance("__mod_restricted__");
    $is_default = ($attr["default"] == "true" ? true : false);

    if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
    {
        $cache_key = "/" . $location . (MOD_RES_MEM_CACHING_BYPATH
                ? $cm->path_info
                : "default"
            );

        $res = $cm->cache->get($cache_key, "/cm/mod/restricted/template/topbar");
    }
    if ($res)
    {
        $globals_mod->access    = $res["access"];
        $tpl->ParsedBlocks      = $res["ParsedBlock"];
    }
    else
    {
        $count = 0;
		$toskip = explode(",", MOD_SEC_PROFILING_SKIPSYSTEM);
		foreach ($cm->modules["restricted"]["menu"] as $key => $value)
		{
			$profile_check = MOD_SEC_PROFILING && (MOD_SEC_PROFILING_SKIPSYSTEM !== "*") && !in_array($key, $toskip) && !$value["profiling_skip"];
			
			if (
					!mod_restricted_checkacl_bylevel($value["acl"]) 
					|| ($profile_check && !mod_sec_checkprofile_bypath($value["path"]))
					|| ($is_default && isset($value["location"]) && $value["location"] != $location)
					|| (!$is_default && (!isset($value["location"]) || $value["location"] != $location))
				)
				continue;

			$globals_mod->access |= true;

			// codice ridondante, da eliminare
			if(!isset($value["visible"]))
				$visible = true;
			else
				$visible = $value["visible"];
			
			if(!$value["hide"] && $visible)
			{
				$tpl->set_var("top_name", $key);
				
				$description = "";
				if ($value["description"])
	            {
	                if(strpos($value["description"], "_") === 0)
                		$description = ffTemplate::_get_word_by_code(substr($value["description"], 1));
	                else
                		$description = $value["description"];

					$description =  '<p>' . $description . '</p>';	                 
				} 				
				$tpl->set_var("description", $description);
					
				$add_class = "";

				
				$globals = "";
				$params = "";
				
				if ($value["globals_exclude"])
				{
					$globals =  $cm->oPage->get_globals($value["globals_exclude"]);
					$params = ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals($value["globals_exclude"]));
				}
				else
				{
					$globals = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
					$params = ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
				}
								
				$tpl->set_var("class", MOD_RES_TOPBAR_CLASS_PREFIX . $key . $add_class);
				if ($value["jsaction"])
					$tpl->set_var("path", $value["jsaction"]);
				else
					$tpl->set_var("path", $cm->oPage->site_path . $value["path"] . "?" . $globals . $params);
					
				if(strpos($value["label"], "_") === 0) {
					$tpl->set_var("label", ffTemplate::_get_word_by_code("mt_" . substr($value["label"], 1)));
				} else {
					$tpl->set_var("label", $value["label"]);
				}
				
				//$tpl->set_var("label", ffCommon_specialchars($value["label"], ENT_QUOTES, "UTF-8"));
				if ($value["globals_exclude"])
					$tpl->set_var("params", ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals($value["globals_exclude"])));
				else
					$tpl->set_var("params", ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)));

                if ($value["selected"])
				{
                    $tpl->set_var("selected", "class=\"selected\"");
                    $tpl->set_var("class_selected", "selected");
				}
                else
				{
                    $tpl->set_var("selected", "");
                    $tpl->set_var("class_selected", "");
				}
                    
				if ($value["is_heading"])
				{
					$tpl->parse("SectHeading", false);
					$tpl->set_var("SectLink", "");
				}
				else
				{
					$tpl->parse("SectLink", false);
					$tpl->set_var("SectHeading", "");
				}
				
				if ($count)
					$tpl->parse("SectSeparator", false);

				$tpl->parse("SectElement", true);
                $count++;
			}
		}
		reset($cm->modules["restricted"]["menu"]);

        if($count) {
            $tpl->parse("SectMenu", false);
        } else {
            $tpl->set_var("SectMenu", "");
        }

        if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
        {
            $cache_key = "/" . $location . (MOD_RES_MEM_CACHING_BYPATH
                    ? $cm->path_info
                    : "default"
                );

            /** @var reference $access */
            $tmp = array(
                "ParsedBlock" => $tpl->ParsedBlocks
            , "access" => $globals_mod->access
            );

            $res = $cm->cache->set($cache_key, $tmp, "/cm/mod/restricted/template/topbar");
        }
	}
}

function mod_restricted_cm_on_load_navbar($page, $tpl, $location, $attr)
{      
	$cm = cm::getInstance();
    $globals_mod = ffGlobals::getInstance("__mod_restricted__");
    $is_default = ($attr["default"] == "true" ? true : false);

	if ($cm->modules["restricted"]["sel_topbar"] === null)
		return;

	// --- codice di ALEX
	if(strlen($cm->modules["restricted"]["sel_topbar"]["label"]))
	{
		if(strpos($cm->modules["restricted"]["sel_topbar"]["label"], "_") === 0)
		{
			$tpl->set_var("navbar_label", ffTemplate::_get_word_by_code("mt_" . substr($cm->modules["restricted"]["sel_topbar"]["label"], 1)));
		} 
		else 
		{
			$tpl->set_var("navbar_label", $cm->modules["restricted"]["sel_topbar"]["label"]);
		}
		$tpl->parse("SectNavbarTitle", false);
	} 
	else 
	{
		$tpl->set_var("SectNavbarTitle", "");
	}
	
	$tpl->set_var("navbar_class", $cm->modules["restricted"]["sel_topbar"]["name"]);
	// ---
		
	if (!is_array($cm->modules["restricted"]["sel_topbar"]["elements"]) || !count($cm->modules["restricted"]["sel_topbar"]["elements"]))
		return;

    if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
    {
        $cache_key = "/" . $location . (MOD_RES_MEM_CACHING_BYPATH
                ? $cm->path_info
                : "default"
            );

        $res = $cm->cache->get($cache_key, "/cm/mod/restricted/template/navbar");
    }
    if ($res)
    {
        $globals_mod->access    = $res["access"];
        $tpl->ParsedBlocks      = $res["ParsedBlock"];
    }
    else
    {
		// modifica di ALEX
		$res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["sel_topbar"], "", $location, $is_default);

        $globals_mod->access |= true;

		if($res_navbar["count"])
            $tpl->parse("SectMenu", false);
		else 
            $tpl->set_var("SectMenu", "");

        if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
        {
            $cache_key = "/" . $location . (MOD_RES_MEM_CACHING_BYPATH
                    ? $cm->path_info
                    : "default"
                );

            /** @var reference $access */
            $tmp = array(
                "ParsedBlock" => $tpl->ParsedBlocks
            , "access" => $globals_mod->access
            );

            $res = $cm->cache->set($cache_key, $tmp, "/cm/mod/restricted/template/navbar");
        }
	}
}

function mod_restricted_process_navbar(&$tpl, $sel_topbar, $prefix = "", $location = "navbar", $default = true)
{
    $cm = cm::getInstance();
    $count = 0;
    $is_opened = false;
    
    if(strlen($prefix))
        $tpl->set_var("Sect" . $prefix . "Element", "");

	$tpl->set_var("navbar_class", MOD_RES_NAVBAR_CLASS_PREFIX . $cm->modules["restricted"]["sel_topbar"]["name"]);
    
    if(is_array($sel_topbar) && array_key_exists("elements", $sel_topbar) && count($sel_topbar["elements"]))
	{
        foreach ($sel_topbar["elements"] as $key => $value)
        {
            if (
                    !mod_restricted_checkacl_bylevel($value["acl"]) 
					|| (
							!$value["profiling_skip"]
							&& !mod_sec_checkprofile_bypath($value["path"])
						)
                    || (isset($value["hide"]) && $value["hide"])
                    || ($default && isset($value["location"]) && $value["location"] != $location)
                    || (!$default && (!isset($value["location"]) || $value["location"] != $location))
                )
                continue;
            
            $tpl->set_var("Sect" . $prefix . "Link", "");
            $tpl->set_var("Sect" . $prefix . "Heading", "");

            $description = "";
			if ($value["description"])
            {
                if(strpos($value["description"], "_") === 0)
                	$description = ffTemplate::_get_word_by_code(substr($value["description"], 1));
                else
                	$description = $value["description"];

                 $description = '<p>' . $description . '</p>';
			} 
            $tpl->set_var("description", $description);
            
            if(strpos($value["label"], "_") === 0)
                $tpl->set_var("label", ffTemplate::_get_word_by_code("mn_" . substr($value["label"], 1)));
            else
                $tpl->set_var("label", $value["label"]);
            
            $globals = "";
            $params = "";

            if ($value["globals_exclude"])
            {
                    $globals =  $cm->oPage->get_globals($value["globals_exclude"]);
                    $params = ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals($value["globals_exclude"]));
            }
            else
            {
                    $globals = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
                    $params = ffProcessTags($value["params"], null, null, "normal", $cm->oPage->get_params(), $cm->oPage->ret_url, $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
            }

            $tpl->set_var("globals", $globals);
            $tpl->set_var("params", $params);

            if ($value["description"])
            {
                    if(strpos($value["description"], "_") === 0)
                            $tpl->set_var("description", ffTemplate::_get_word_by_code(substr($value["description"], 1)));
                    else
                            $tpl->set_var("description", $value["description"]);
                    $tpl->parse("SectDescription", false);
            }
            else
                    $tpl->set_var("SectDescription", "");
            
            if($value["class"])
                $tpl->set_var("class", $value["class"]);
            else 
                $tpl->set_var("class", "");
            
            if ($value["is_heading"])
                $tpl->parse("Sect" . $prefix . "Heading", false);
            else
            {
                $tpl->set_var("name", $key);
				if ($value["jsaction"])
					$tpl->set_var("path", $value["jsaction"]);
				else
					$tpl->set_var("path", $cm->oPage->site_path . $value["path"] . "?" . $globals . $params);

                if ($value["selected"])
				{
                    $tpl->set_var("selected", "class=\"selected\"");
                    $tpl->set_var("class_selected", "selected");
                    
                    $is_opened = true;
				}
                else
				{
                    $tpl->set_var("selected", "");
                    $tpl->set_var("class_selected", "");
				}

                $tpl->parse("Sect" . $prefix . "Link", false);
            }
            $tpl->parse("Sect" . $prefix . "Element", true);
            $count++;
        }
    }
    reset($sel_topbar);

    return array("count" => $count, "opened" => $is_opened);
}