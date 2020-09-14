<?php
  function mod_restricted_get_framework_css() {
  	$framework_css = array(
  		"layer" => array(
  			"component" => array(
  				"class" => null
  			)
  			, "nav" => array(
  				"left" => array(
  					"class" => null
  					, "topbar" => "nav-brand"
  				)
  				, "form" => array(
  					"class" => null
  					, "topbar" => "nav-form"
  				)
  				, "right" => array(
  					"class" => "nav-right"
  					, "util" => "align-right"
  				)
			)  		
  			, "action" => array(
  				"toggle" => array(
  					"class" => null
  					, "topbar" => "hamburger"
  				)
  			)
  		)
        , "variant" => array(
            "-inverse" => array(
                "sidemenu"
                , "rightmenu"
                , "header__top"
                , "header__bar"
                , "pagecontent"
                , "footer"
            )
            , "-fixed" => array(
                "sidemenu"
                , "rightcol"
                , "header__top"
                , "header__bar"
                , "footer"
            )
            , "-noicon" => array(
                "sidemenu"
                , "header__top"
                , "header__bar"
                , "pagecontent"
                , "footer"
            )
            , "-closed" => array(
                "sidemenu"
                , "rightcol"
            )
            , "-rightview" => array(
                "rightcol"
            )
            , "-notab" => array(
                "header__bar"
            )
            , "-floating" => array(
                "sidemenu"
                , "rightcol"
                , "button"
            )
            , "-sortable" => array(

            )
            , "-draggable" => array(

            )
            , "-dragging" => array(

            )
            , "-dragover" => array(

            )
            , "-active" => array(

            )
            , "-pad1" => array(

            )
            , "-pad2" => array(

            )
            , "-pad3" => array(

            )
            , "-pad4" => array(

            )
            , "-pad5" => array(

            )
            , "-pad6" => array(

            )
            , "-pad7" => array(

            )
            , "-pad8" => array(

            )
            , "-pad9" => array(

            )


        )
		, "menu" => array(
			"topbar" => cm_getClassByFrameworkCss("topbar", "bar")
			, "navbar" => cm_getClassByFrameworkCss("navbar", "bar")
            //todo: da inserire la sidenav
		)
        , "list" => array(
            "container" => cm_getClassByFrameworkCss("group", "list")
            , "horizontal" => cm_getClassByFrameworkCss("group-horizontal", "list")
            , "item" => cm_getClassByFrameworkCss("item", "list")
        )
		, "dropdown" => array(
			"container" => array(
				"class" => null
				, "panel" => "container"
				, "collapse" => "pane"
			)
			, "header" => array(
				"panel" => "heading"
				, "util" => "clear"
			)
			, "body" => array(
				"def" => array(
					"panel" => "body"
				)
				, "img" => array(
					"col" => array(
						"xs" => 0
						, "sm" => 4
						, "md" => 4
						, "lg" => 4
					)
				)
				, "desc" => array(
					"col" => array(
						"xs" => 12
						, "sm" => 8
						, "md" => 8
						, "lg" => 8
					)
				)
				, "links" => array(
					"class" => "panel-link"
					, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 12
						, "lg" => 12
					)
					, "util" => "align-right"
				)
			)
			, "footer" => array(
				"panel" => "footer"
				, "util" => "clear"
			)
			, "actions" => array(
                "header" => array(
                    "button" => array("value" => "default", "params" => array("size" => "small"))
                )
			    , "body" => array(
                    "button" => array("value" => "link")
                )

			    , "footer" => array(
                    "button" => array("value" => "default", "params" => array("size" => "small"))
                )

                //todo da togliere e verificare
				, "profile" => array(
					"button" => array("value" => "primary", "params" => array("size" => "small"))
					, "icon" => "pencil"
				)
				, "users" => array(
					"button" => array("value" => "default", "params" => array("size" => "small"))
					, "icon" => "users"
				)
				, "domains" => array(
					"button" => array("value" => "default", "params" => array("size" => "small"))
					, "icon" => "database"
				)
				, "profiling" => array(
					"button" => array("value" => "default", "params" => array("size" => "small"))
					, "icon" => "users"
					, "util" => "right"	
				)
                , "settings" => array(
                    "button" => array("value" => "default", "params" => array("size" => "small"))
                    , "icon" => "cogs"
                    , "util" => "right"
                )
				, "logout" => array(
					"button" => array("value" => "default", "params" => array("size" => "small"))
					, "icon" => "power-off"
					, "util" => "right"
				)
			)
		)
		, "description" => cm_getClassByFrameworkCss("text-muted", "util")
		, "image" => array(
			"util" => array("corner-circle", "corner-thumbnail")
		)
		, "collapse" => array(
			"action" => cm_getClassByFrameworkCss("link", "data", "collapse")
			, "pane" => cm_getClassByFrameworkCss("pane", "collapse")
			, "current" => cm_getClassByFrameworkCss("current", "collapse")
			, "menu" => cm_getClassByFrameworkCss("menu", "collapse")
		)
		, "current" => cm_getClassByFrameworkCss("current", "util")
		, "icons" => array(
			"caret-collapsed" => "menu-caret " . cm_getClassByFrameworkCss("chevron-right", "icon")
			, "caret" => "menu-caret " . cm_getClassByFrameworkCss("chevron-right", "icon", array("rotate-90"))
			, "settings" => cm_getClassByFrameworkCss("cog", "icon")
		)
		, "logo" => array(
			"class" => "brand-logo"
		)
	);
	
  	return $framework_css;
  
  }
  
 // if (isset($cm->modules["restricted"]["events"])) 
  	$cm->modules["restricted"]["events"]->addEvent("on_layout_process", "mod_restricted_cm_on_layout_process");

  	
function mod_restricted_cm_on_layout_process()
{
	$cm = cm::getInstance();

	//if (isset($cm->oPage->sections["favorite"]))
	//	$cm->oPage->sections["favorite"]["events"]->addEvent("on_process", "mod_restricted_cm_on_load_favorite");

	//if (isset($cm->oPage->sections["breadcrumb"]))
	//	$cm->oPage->sections["breadcrumb"]["events"]->addEvent("on_process", "mod_restricted_cm_on_load_breadcrumb");

	$cm->oPage->widgetLoad("dialog");
    $cm->oPage->widgets["dialog"]->process(
         "dialogResponsive"
         , array(
            "tpl_id" => null
            //"name" => "myTitle"
            , "url" => ""
            , "title" => ""
            , "callback" => ""
            , "class" => ""
            , "params" => array(
            )
            , "resizable" => true
            , "position" => "center"
            , "draggable" => true
            , "doredirects" => false
            , "responsive" => true
            , "unic" => true
            , "dialogClass" => "modal-lg"
        )
        , $cm->oPage
    );
}
  

function on_load_section_breadcrumb($page, $tpl, $attr)
{
	$cm = cm::getInstance();
    $attr["layout_default"] = "breadcrumb";

	if($page->sections["brand"]) {
		if(!$page->tpl_layer[0]->isset_var("brand")) {
            $framework_css = mod_restricted_get_framework_css();

            $page->sections["brand"]["tpl"]->set_var("SectLogo", "");

            $ID_domain = mod_security_get_domain();

            $page->sections["brand"]["tpl"]->set_var("host_name", ($ID_domain 
                ? get_session("Domain")
                : CM_LOCAL_APP_NAME
            ));
            $page->sections["brand"]["tpl"]->set_var("more_icon", '<i class="' . $framework_css["icons"]["caret"] . '"></i>');
			$home = $page->sections["brand"]["tpl"]->rpparse("SectBrandName", false);
			$tpl->set_var("fixed_post_content", $page->sections["brand"]["tpl"]->rpparse("SectBrandInfo", false));
		}
	} else {
		$home = "Home";
	}
	
	$arrPath = explode("/", trim(ffCommon_dirname($cm->path_info), "/"));
	if(is_array($arrPath) && count($arrPath)) {
        $actual_path = "";
		foreach($arrPath AS $key => $path_name) {
			if($actual_path) {
				$tpl->set_var("crumb_label", ($cm->modules["restricted"]["menu_bypath"][$actual_path]["label"]
						? $cm->modules["restricted"]["menu_bypath"][$actual_path]["label"]
						: ucfirst($path_name)
					)
				);		

			} elseif($home) {
				$tpl->set_var("crumb_label", $home);
			} else {
				$actual_path .= "/" . $path_name;
				continue;
			}
			
			$actual_path .= "/" . $path_name;
			
			$tpl->set_var("crumb_url", FF_SITE_PATH . $actual_path);
			$tpl->parse("SectCrumb", true);
		}
	}
	
	$tpl->set_var("page-title", ($page->title == cm_getAppName()
		? ucwords(str_replace("-", " ", basename($cm->path_info)))
		: str_replace(" - " . cm_getAppName(), "", $page->title)
	));
	
}

function on_load_section_admin($page, $tpl, $attr) {
    $cm = cm::getInstance();
    $attr["layout_default"] = "admin";
    $attr["label"] = false;
    $attr["description_skip"] = true;
    $attr["readonly_skip"] = true;

    $res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["sections"]["admin"], $attr);
    if($res_navbar["count"])
    {
        $tpl->parse("SectMenu", false);
    }
}

function on_load_section_rightcol($page, $tpl, $attr) {
    $cm = cm::getInstance();
    $attr["layout_default"] = "admin";

    $res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["sections"]["rightcol"], $attr);
    if($res_navbar["count"])
    {
        $tpl->parse("SectMenu", false);
    }
}
function on_load_section_favorite($page, $tpl, $attr)
{
	$cm = cm::getInstance();
    $attr["layout_default"] = "favorite";

	if(isset($cm->modules["restricted"]["sections"]["favorite"]) && is_array($cm->modules["restricted"]["sections"]["favorite"]["elements"]))
	{
		foreach ($cm->modules["restricted"]["sections"]["favorite"]["elements"] as $key => $value)
		{
			if($value["path"] != "/" && strpos($cm->path_info, $value["path"]) === 0)
				continue;

            if (mod_restricted_check_no_permission($value)) {
                continue;
            }

			if(strpos($value["label"], "_") === 0) {
                $value["label"] = ffTemplate::_get_word_by_code(substr($value["label"], 1));
			}
			if($value["icon"]) {
                $tpl->set_var("icon", cm_getClassByFrameworkCss($value["icon"], "icon-tag", "lg"));
            }

            if($attr["label"] === false) {
                $item_properties["title"] = 'title="' . $value["label"] . '"';
                if($value["icon"]) {
                    $tpl->set_var("label", "");
                } else {
                    $tpl->set_var("label", '<span>' . ucfirst(substr($value["label"], 0, 1)) . '</span>');
                }
            } else {
                if(strpos($value["label"], "_") === 0) {
                    $tpl->set_var("label", '<span>' . ffTemplate::_get_word_by_code(substr($value["label"], 1)) . '</span>');
                } else {
                    $tpl->set_var("label", '<span>' . $value["label"] . '</span>');
                }
            }

            if($item_properties) {
                $item_properties = implode(" ", $item_properties);
            }

            $tpl->set_var("item_properties", $item_properties);
            $tpl->set_var("path", $cm->oPage->site_path . $value["path"]);
			$tpl->parse("SectFavorite", true);
		}
	}
}

  
function mod_restricted_on_tpl_layer_loaded($page, $tpl)
{
    $cm = cm::getInstance();

  	$framework_css = mod_restricted_get_framework_css();

  	$tpl->set_var("toggle_class", cm_getClassByDef($framework_css["layer"]["action"]["toggle"]));
  	$tpl->set_var("toggle_properties", $framework_css["collapse"]["action"]);
  	$tpl->set_var("nav_left_class", cm_getClassByDef($framework_css["layer"]["nav"]["left"]));
  	$tpl->set_var("nav_right_class", cm_getClassByDef($framework_css["layer"]["nav"]["right"]));

  	$tpl->set_var("page-title", ($page->title == cm_getAppName()
		? ucwords(str_replace("-", " ", basename($cm->path_info)))
		: str_replace(" - " . cm_getAppName(), "", $page->title)
	));
    $tpl->set_var("CM_LOCAL_APP_NAME", ffCommon_specialchars(cm_getAppName()));
	if (MOD_RES_DEVELOPER)
		$tpl->parse("SectFooter", false);
}	

function on_load_section_topbar($page, $tpl, $attr)
{
	$cm = cm::getInstance();
	$globals_mod = ffGlobals::getInstance("__mod_restricted__");
    $attr["location_default"] = "topbar";

	if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
	{
        $cache_key = "/" . $attr["location_default"] . (MOD_RES_MEM_CACHING_BYPATH
            ? $cm->path_info
            : "default"
        );

        $cache = $cm->cache->get($cache_key, "/cm/mod/restricted/template/" . $attr["location_default"]);
	}
	if ($cache)
	{
        $globals_mod->access    = $cache["access"];
		$tpl->ParsedBlocks      = $cache["ParsedBlock"];
	}
	else
	{
		$framework_css = mod_restricted_get_framework_css();

		$res = array(
		    "count" => 0
            , "count_icon" => 0
            , "count_position" => null
        );

		foreach ($cm->modules["restricted"]["menu"] as $key => $value)
		{
			if (mod_restricted_check_no_permission($value)) {
                continue;
            }
			if($attr["readonly_skip"] && $value["readonly"]) {
                continue;
            }
            if($value["hide"]) {
                continue;
            }
			$location = ($attr["default"] && !$value["location"]
                ? $attr["location_default"]
                : $value["location"]
            );

            if($location != $attr["location_default"]) {
                continue;
            }

			$globals_mod->access |= true;
//			ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());

            $item_tag = ($value["readonly"]
                ? ($value["readonly"] === true
                    ? "div"
                    : $value["readonly"]
                )
                : "a"
            );
            $item_class = array("key" => $key);
            $item_icon = null;
            $item_properties = null;
            $item_actions = null;
            $description = "";

            $tpl->set_var("name", $key);

            if ($value["description"] && !$attr["description_skip"])
            {
                if(strpos($value["description"], "_") === 0)
                    $description = ffTemplate::_get_word_by_code(substr($value["description"], 1));
                else
                    $description = $value["description"];

                $description =  '<p class="' . $framework_css["description"] . '">' . $description . '</p>';
            }

            $tpl->set_var("item_description", $description);

            if($value["actions"]) {
                if(is_array($value["actions"]) && count($value["actions"])) {
                    foreach($value["actions"] AS $action_data) {
                        $action_path = "";
                        $action_label = "";
                        $action_icon = $framework_css["icons"]["settings"];
                        if(is_array($action_data)) {
                            $action_path = $action_data["path"];
                            if($action_data["icon"])
                                $action_icon = cm_getClassByFrameworkCss($action_data["icon"], "icon") . ($action_data["class"] ? " " . $action_data["class"] : "");

                            $action_label = $action_data["label"];
                        } elseif($cm->modules["restricted"]["menu_bypath"][$action_data]) {
                            $action_path = $action_data;
                            if($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"])
                                $action_icon = cm_getClassByFrameworkCss($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"], "icon");

                            $action_label = $cm->modules["restricted"]["menu_bypath"][$action_data][0]["label"];
                        }

                        if(strpos($action_label, "_") === 0)
                            $action_label = ffTemplate::_get_word_by_code(substr($action_label, 1));

                        $action_path = str_replace(array("[rel]", "[key]"), array($value["rel"], $key), $action_path);
                        if($action_data["dialog"] !== false)
                            $action_path = 'javascript:ff.ffPage.dialog.doOpen(\'dialogResponsive\',\'' . $action_path . '\');';

                        $item_actions[] = '<a href="' . $action_path . '" class="' . $action_icon . '" title="' . $action_label . '"></a>';
                    }
                }
            }

            if($attr["submenu"]) {
                $child_class = null;
                $params = $attr;
                $params["prefix"] = "Child";

                $res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["menu"][$key], $params);
                if($res_navbar["count"])
                {
                    if($value["collapse"] !== false)
                    {
                        $aria = ' aria-expanded="false"';
                        if($res_navbar["is_opened"] || $value["collapse"]) {
                            $item_class["current"] = $framework_css["current"];
                            $aria = ' aria-expanded="true"';
                            //      $child_class["current"] = $framework_css["collapse"]["current"];
                        }
                        if(!$value["readonly"]) {
                            $item_properties["url"] = 'href="#topnav-' . $key . '"';
                            $item_properties["collapse"] = $framework_css["collapse"]["action"] . $aria;
                        }
                        $child_class["collapse"] = $framework_css["collapse"]["pane"];
                        //$item_actions["dropdown"] = '<a href="#topnav-' . $key . '" class="' . ($res_navbar["is_opened"] ? $framework_css["icons"]["caret"] : $framework_css["icons"]["caret-collapsed"] . " " . $framework_css["collapse"]["menu"]) . '" ' . $framework_css["collapse"]["action"] . '></a>';
                        $item_actions["dropdown"] = '<a href="#topnav-' . $key . '" class="' . $framework_css["icons"]["caret-collapsed"] . " " . $framework_css["collapse"]["menu"] . '" ' . '></a>';

                    }

                    $tpl->set_var("child_id", "topnav-" . $key);
                    if($child_class)
                        $tpl->set_var("child_class", implode(" ", $child_class));

                    $tpl->set_var("menu_class", $framework_css["menu"]["navbar"]);

                    if(is_array($res_navbar["count_position"]) && count($res_navbar["count_position"])) {
                        foreach($res_navbar["count_position"] AS $position_name => $position_count) {
                            $tpl->parse("SectChild" . $position_name, false);
                        }
                    }

                    $tpl->parse("SectChild", false);
                }
            }

            if(!$value["path"] && !$value["label"]) {
                continue;
            }

            if(!$item_properties["url"]) {
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

                if ($value["jsaction"]) {
                    $path = $value["jsaction"];
                } elseif($value["redir"]) {
                    $path = $cm->oPage->site_path . $value["redir"];
                } else {
                    $path = $cm->oPage->site_path . $value["path"] . ($globals . $params ? "?" . $globals . $params : "");
                }

                if($value["readonly"]) {
                    $item_properties["url"] = 'data-url="' . $path . '"';
                } else {
                    if($value["dialog"])
                        $item_properties["url"] = 'href="' . "javascript:ff.ffPage.dialog.doOpen('dialogResponsive','" . $path . "');"  . '"';
                    else
                        $item_properties["url"] = 'href="' . $path . '"';

                    if($value["rel"])
                        $item_properties["rel"] = 'rel="' . $value["rel"] . '"';
                }

            }

            if(($attr["icons"] === true || $attr["icons"] == "all" || $attr["icons"] == "mainmenu") && $value["icon"])
                $item_icon = cm_getClassByFrameworkCss($value["icon"], "icon-tag", "lg");

            if($attr["label"] === false) {
                $item_properties["title"] = 'title="' . $value["label"] . '"';
                if($item_icon) {
                    $tpl->set_var("label", "");
                } else {
                    $tpl->set_var("label", '<span>' . ucfirst(substr($value["label"], 0, 1)) . '</span>');
                }
            } else {
                if(strpos($value["label"], "_") === 0) {
                    $tpl->set_var("label", '<span>' . ffTemplate::_get_word_by_code(substr($value["label"], 1)) . '</span>');
                } else {
                    $tpl->set_var("label", '<span>' . $value["label"] . '</span>');
                }
            }

            if($value["position"]) {
                $item_class["grid"] = cm_getClassByDef($framework_css["dropdown"]["actions"][$value["position"]]);
            }
            if($value["class"]) {
                $item_class["custom"] = $value["class"];
            }
            if($item_class) {
                $item_properties["class"] = 'class="' . implode(" ", $item_class) . '"';
            }
            if($item_properties) {
                $item_properties = implode(" ", $item_properties);
            }
            if($value["badge"]) {
                $item_actions[] = '<span class="' . cm_getClassByFrameworkCss("default", "badge") . '">' . $value["badge"] . '</span>';
            }

            if($item_actions) {
                $item_actions = '<span class="nav-controls">' . implode(" ", $item_actions) . '</span>';
            }
            $tpl->set_var("actions", $item_actions);
            $tpl->set_var("item_properties", $item_properties);
            $tpl->set_var("item_icon", $item_icon);
            $tpl->set_var("item_tag", $item_tag);

            /*if($res["count"]) {
                $tpl->parse("SectSeparator", false);
            }*/

            $parse_key = "Sect" . $attr["prefix"] . "Element";
            if($value["position"]) {
                $position = ucfirst($value["position"]);
                $parse_key .= $position;
                $res["count_position"][$position]++;
            }
            $tpl->parse($parse_key, true);
            $tpl->set_var("SectChild", "");

            if($item_icon) {
                $res["count_icon"]++;
            }
            $res["count"]++;
        }

		//reset($cm->modules["restricted"]["menu"]);

        if($res["count"]) {
			$tpl->set_var("menu_class", $framework_css["menu"][$attr["location_default"]] . ($res["count_icon"] ? " -withicons" : ""));

            if(is_array($res["count_position"]) && count($res["count_position"])) {
                foreach($res["count_position"] AS $position_name => $position_count) {
                    $tpl->parse("Sect" . $position_name, false);
                }
            }
            $tpl->parse("SectMenu", false);
        }

		if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
		{
            $cache_key = "/" . $attr["location_default"] . (MOD_RES_MEM_CACHING_BYPATH
                ? $cm->path_info
                : "default"
            );

            /** @var reference $access */
            $tmp = array(
                "ParsedBlock" => $tpl->ParsedBlocks
                , "access" => $globals_mod->access
            );

            $res = $cm->cache->set($cache_key, $tmp, "/cm/mod/restricted/template/" . $attr["location_default"]);
		}
	}
}

function on_load_section_navbar($page, $tpl, $attr)
{
	$cm = cm::getInstance();
    $globals_mod = ffGlobals::getInstance("__mod_restricted__");
    $attr["layout_default"] = "navbar";
    if(!isset($attr["readonly_skip"])) {
        $attr["readonly_skip"] = true;
    }

    if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
    {
        $cache_key = "/navbar" . (MOD_RES_MEM_CACHING_BYPATH
                ? $cm->path_info
                : "default"
            );

        $cache = $cm->cache->get($cache_key, "/cm/mod/restricted/template/navbar");
    }
    if ($cache)
    {
        $globals_mod->access    = $cache["access"];
        $tpl->ParsedBlocks      = $cache["ParsedBlock"];
    }
    else
    {
        $framework_css = mod_restricted_get_framework_css();

        $tpl->set_var("navbar_class", $cm->modules["restricted"]["sel_topbar"]["name"]);

        $globals_mod->access |= true;

        $res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["sel_topbar"], $attr);
        if($res_navbar["count"])
        {

            $tpl->set_var("menu_class", $framework_css["menu"]["topbar"]);

            $tpl->parse("SectMenu", false);
        }

        if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
        {
            $cache_key = "/navbar" . (MOD_RES_MEM_CACHING_BYPATH
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

function on_load_section_sidebar($page, $tpl, $attr)
{
    $cm = cm::getInstance();
    $globals_mod = ffGlobals::getInstance("__mod_restricted__");
    $attr["location_default"] = "sidebar";

    if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
    {
        $cache_key = "/" . $attr["location_default"] . (MOD_RES_MEM_CACHING_BYPATH
                ? $cm->path_info
                : "default"
            );

        $cache = $cm->cache->get($cache_key, "/cm/mod/restricted/template/" . $attr["location_default"]);
    }
    if ($cache)
    {
        $globals_mod->access    = $cache["access"];
        $tpl->ParsedBlocks      = $cache["ParsedBlock"];
    }
    else
    {
        $framework_css = mod_restricted_get_framework_css();

        $res = array(
            "count" => 0
            , "count_icon" => 0
            , "count_position" => null
        );

        foreach ($cm->modules["restricted"]["menu"] as $key => $value)
        {
            if (mod_restricted_check_no_permission($value)) {
                continue;
            }
            if($attr["readonly_skip"] && $value["readonly"]) {
                continue;
            }
            if($value["hide"]) {
                continue;
            }
            $location = ($attr["default"] && !$value["location"]
                ? $attr["location_default"]
                : $value["location"]
            );

            if($location != $attr["location_default"]) {
                continue;
            }

            $globals_mod->access |= true;
//			ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());

            $item_tag = ($value["readonly"]
                ? ($value["readonly"] === true
                    ? "div"
                    : $value["readonly"]
                )
                : "a"
            );
            $item_class = array("key" => $key);
            $item_icon = null;
            $item_properties = null;
            $item_actions = null;
            $description = "";

            $tpl->set_var("name", $key);

            if ($value["description"] && !$attr["description_skip"])
            {
                if(strpos($value["description"], "_") === 0) {
                    $description = ffTemplate::_get_word_by_code(substr($value["description"], 1));
                } else {
                    $description = $value["description"];
                }
                $description =  '<p class="' . $framework_css["description"] . '">' . $description . '</p>';
            }

            $tpl->set_var("item_description", $description);

            if($value["actions"]) {
                if(is_array($value["actions"]) && count($value["actions"])) {
                    foreach($value["actions"] AS $action_data) {
                        $action_path = "";
                        $action_label = "";
                        $action_icon = $framework_css["icons"]["settings"];
                        if(is_array($action_data)) {
                            $action_path = $action_data["path"];
                            if($action_data["icon"]) {
                                $action_icon = cm_getClassByFrameworkCss($action_data["icon"], "icon") . ($action_data["class"] ? " " . $action_data["class"] : "");
                            }
                            $action_label = $action_data["label"];
                        } elseif($cm->modules["restricted"]["menu_bypath"][$action_data]) {
                            $action_path = $action_data;
                            if($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"]) {
                                $action_icon = cm_getClassByFrameworkCss($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"], "icon");
                            }
                            $action_label = $cm->modules["restricted"]["menu_bypath"][$action_data][0]["label"];
                        }

                        if(strpos($action_label, "_") === 0) {
                            $action_label = ffTemplate::_get_word_by_code(substr($action_label, 1));
                        }
                        $action_path = str_replace(array("[rel]", "[key]"), array($value["rel"], $key), $action_path);
                        if($action_data["dialog"] !== false) {
                            $action_path = 'javascript:ff.ffPage.dialog.doOpen(\'dialogResponsive\',\'' . $action_path . '\');';
                        }
                        $item_actions[] = '<a href="' . $action_path . '" class="' . $action_icon . '" title="' . $action_label . '"></a>';
                    }
                }
            }

            if($attr["submenu"] !== false) {
                $child_class = null;

                $params = $attr;
                $params["prefix"] = "Child";
                $res_navbar = mod_restricted_process_navbar($tpl, $cm->modules["restricted"]["menu"][$key], $params);
                if($res_navbar["count"])
                {
                    if($value["collapse"] !== false)
                    {
                        $aria = ' aria-expanded="false"';
                        if($res_navbar["is_opened"] || $value["collapse"]) {
                            $item_class["current"] = $framework_css["current"];
                            $child_class["current"] = $framework_css["collapse"]["current"];

                            $aria = ' aria-expanded="true"';
                        }
                        if(!$value["readonly"]) {
                            $item_properties["url"] = 'href="#sidenav-' . $key . '"';                                   
                            $item_properties["collapse"] = $framework_css["collapse"]["action"] . $aria;
                        }
                        $child_class["collapse"] = $framework_css["collapse"]["pane"];
                        $item_actions["dropdown"] = '<a href="#sidenav-' . $key . '" class="' . ($res_navbar["is_opened"] ? $framework_css["icons"]["caret"] : $framework_css["icons"]["caret-collapsed"] . " " . $framework_css["collapse"]["menu"]) . '" ' . '></a>';

                    }

                    $tpl->set_var("child_id", "sidenav-" . $key);
                    if($child_class) {
                        $tpl->set_var("child_class", implode(" ", $child_class));
                    }
                    $tpl->set_var("menu_class", $framework_css["menu"]["navbar"]);

                    $tpl->parse("SectChild", false);
                }
            }

            if(!$value["path"] && !$value["label"]) {
                continue;
            }

            if(!$item_properties["url"]) {
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

                if ($value["jsaction"]) {
                    $path = $value["jsaction"];
                } elseif($value["redir"]) {
                    $path = $cm->oPage->site_path . $value["redir"];
                } else {
                    $path = $cm->oPage->site_path . $value["path"] . ($globals . $params ? "?" . $globals . $params : "");
                }               
                if($value["readonly"]) {
                    $item_properties["url"] = 'data-url="' . $path . '"';
                } else {
                    if($value["dialog"]) {
                        $item_properties["url"] = 'href="' . "javascript:ff.ffPage.dialog.doOpen('dialogResponsive','" . $path . "');" . '"';
                    } else {
                        $item_properties["url"] = 'href="' . $path . '"';
                    }
                    if($value["rel"]) {
                        $item_properties["rel"] = 'rel="' . $value["rel"] . '"';
                    }
                }
            }
 
            if(($attr["icons"] === true || $attr["icons"] == "all" || $attr["icons"] == "mainmenu") && $value["icon"]) {
                $item_icon = cm_getClassByFrameworkCss($value["icon"], "icon-tag", "lg");
            }

            if($attr["label"] === false) {
                $item_properties["title"] = 'title="' . $value["label"] . '"';
                if($item_icon) {
                    $tpl->set_var("label", "");
                } else {
                    $tpl->set_var("label", '<span>' . ucfirst(substr($value["label"], 0, 1)) . '</span>');
                }
            } else {
                if(strpos($value["label"], "_") === 0) {
                    $tpl->set_var("label", '<span>' . ffTemplate::_get_word_by_code(substr($value["label"], 1)) . '</span>');
                } else {
                    $tpl->set_var("label", '<span>' . $value["label"] . '</span>');
                }
            }

            if($value["position"]) {
                $item_class["grid"] = cm_getClassByDef($framework_css["dropdown"]["actions"][$value["position"]]);
            }
            if($value["class"]) {
                $item_class["custom"] = $value["class"];
            }
            if($item_class) {
                $item_properties["class"] = 'class="' . implode(" ", $item_class) . '"';
            }                        
            if($item_properties) {
                $item_properties = implode(" ", $item_properties);
            }
            if($value["badge"]) {
                $item_actions[] = '<span class="' . cm_getClassByFrameworkCss("default", "badge") . '">' . $value["badge"] . '</span>';
            }

            if($item_actions)
                $item_actions = '<span class="nav-controls">' . implode(" " , $item_actions) . '</span>';
           
            $tpl->set_var("actions", $item_actions);
            //$item_properties .= ' data-toggle="collapse"';
            $tpl->set_var("item_properties", $item_properties);
            $tpl->set_var("item_icon", $item_icon);
            $tpl->set_var("item_tag", $item_tag);

            //if($res["count"])
            //    $tpl->parse("SectSeparator", false);

            $parse_key = "Sect" . $attr["prefix"] . "Element";
            if($value["position"]) {
                $position = ucfirst($value["position"]);
                $parse_key .= $position;
                $res["count_position"][$position]++;
            }
            $tpl->parse($parse_key, true);
            $tpl->set_var("SectChild", "");

            if($item_icon) {
                $res["count_icon"]++;
            }
            $res["count"]++;
        }
        reset($cm->modules["restricted"]["menu"]);

        if($res["count"]) {
            $tpl->set_var("menu_class", $framework_css["menu"]["topbar"] . ($res["count_icon"] ? " -withicons" : ""));

            if(is_array($res["count_position"]) && count($res["count_position"])) {
                foreach($res["count_position"] AS $position_name => $position_count) {
                    $tpl->parse("Sect" . $position_name, false);
                }
            }
            $tpl->parse("SectMenu", false);
        }

        if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
        {
            $cache_key = "/" . $attr["location_default"] . (MOD_RES_MEM_CACHING_BYPATH
                    ? $cm->path_info
                    : "default"
                );

            /** @var reference $access */
            $tmp = array(
                "ParsedBlock" => $tpl->ParsedBlocks
            , "access" => $globals_mod->access
            );

            $res = $cm->cache->set($cache_key, $tmp, "/cm/mod/restricted/template/" . $attr["location_default"]);
        }
    }

    return $res;
}

// funzione di ALEX
function mod_restricted_process_navbar(&$tpl, $sel_topbar, $attr = array())
{
    $cm = cm::getInstance();
    $res = array(
        "count" => 0
        , "is_opened" => false
        , "count_position" => null
    );

    $framework_css = mod_restricted_get_framework_css();
    if($attr["prefix"]) {
        $tpl->set_var("Sect" . $attr["prefix"] . "Element", "");
    }
	//$tpl->set_var("navbar_class", preg_replace("/[^[:alnum:]]+/", "", $sel_topbar["label"]));
    //var_dump(count($sel_topbar["elements"]));
//print_r(count($sel_topbar["elements"]));
    if(is_array($sel_topbar) && array_key_exists("elements", $sel_topbar) && count($sel_topbar["elements"]))
	{
       // echo "CCCC  ";

        foreach ($sel_topbar["elements"] as $key => $value)
        {
            if (mod_restricted_check_no_permission($value)) {
                continue;
            }
            if($attr["readonly_skip"] && $value["readonly"]) {
                continue;
            }
            if($value["hide"]) {
                continue;
            }
           // $tpl->set_var("Sect" . $attr["prefix"] . "Link", "");
           // $tpl->set_var("Sect" . $attr["prefix"] . "Heading", "");
			$item_tag = ($value["readonly"] 
				? ($value["readonly"] === true
					? "div"
					: $value["readonly"] 
				)
				: "a"
			);
			$item_class = null;
			$item_icon = null;
			$item_properties = null;
			$item_actions = null;
			$description = "";

            if ($value["description"] && !$attr["description_skip"])
            {
                if(strpos($value["description"], "_") === 0)
                	$description = ffTemplate::_get_word_by_code(substr($value["description"], 1));
                else
                	$description = $value["description"];

                 $description = '<p class="' . $framework_css["description"] . '">' . $description . '</p>';
			} 
			                    
            $tpl->set_var("description", $description);

 			if($value["actions"]) {
				if(is_array($value["actions"]) && count($value["actions"])) {
					foreach($value["actions"] AS $action_data) {
						$action_path = "";
						$action_label = "";
						$action_icon = $framework_css["icons"]["settings"];
                        $action_data_dialog = false;
						if(is_array($action_data)) {
							$action_path = $action_data["path"];
							if($action_data["icon"])
								$action_icon = cm_getClassByFrameworkCss($action_data["icon"], "icon") . ($action_data["class"] ? " " . $action_data["class"] : "");

							$action_label = $action_data["label"];
                            if($action_data["dialog"] !== false)
                                $action_data_dialog = true;
						} elseif($cm->modules["restricted"]["menu_bypath"][$action_data]) {
							$action_path = $action_data;
							if($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"])
								$action_icon = cm_getClassByFrameworkCss($cm->modules["restricted"]["menu_bypath"][$action_data][0]["icon"], "icon");
								
							$action_label = $cm->modules["restricted"]["menu_bypath"][$action_data][0]["label"];
						}

				        if(strpos($action_label, "_") === 0)
				            $action_label = ffTemplate::_get_word_by_code(substr($action_label, 1));
						
						$action_path = str_replace(array("[rel]", "[key]"), array($value["rel"], $key), $action_path);
                        if($action_data_dialog)
							$action_path = 'javascript:ff.ffPage.dialog.doOpen(\'dialogResponsive\',\'' . $action_path . '\');';
						
						$item_actions[] = '<a href="' . $action_path . '" class="' . $action_icon . '" title="' . $action_label . '"></a>';
					}
				}
			}

 
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

			if ($value["jsaction"])
				$path = $value["jsaction"];
            elseif($value["redir"])
                $path = $cm->oPage->site_path . $value["redir"];
			else
				$path = $cm->oPage->site_path . $value["path"] . ($globals . $params ? "?" . $globals . $params : "");

            if($value["readonly"]) {
                $item_properties["url"] = 'data-url="' . $path . '"';
            } else {
                if ($value["dialog"])
                    $item_properties["url"] = 'href="' . "javascript:ff.ffPage.dialog.doOpen('dialogResponsive','" . $path . "');" . '"';
                else
                    $item_properties["url"] = 'href="' . $path . '"';
            }
			if($value["rel"])
				$item_properties["rel"] = 'rel="' . $value["rel"] . '"';

			if(($attr["icons"] == "all" || $attr["icons"] == "submenu") && $value["icon"])
				$item_icon = cm_getClassByFrameworkCss($value["icon"], "icon-tag", "lg");

            if($attr["label"] === false) {
                $item_properties["title"] = 'title="' . $value["label"] . '"';
                if($item_icon) {
                    $tpl->set_var("label", "");
                } else {
                    $tpl->set_var("label", '<span>' . ucfirst(substr($value["label"], 0, 1)) . '</span>');
                }
            } else {
                if(strpos($value["label"], "_") === 0) {
                    $tpl->set_var("label", '<span>' . ffTemplate::_get_word_by_code(substr($value["label"], 1)) . '</span>');
                } else {
                    $tpl->set_var("label", '<span>' . $value["label"] . '</span>');
                }
            }

            if($value["class"]) {
                $item_class["custom"] = $value["class"];
            }
            if ($value["selected"])
			{
				$item_class["current"] = $framework_css["current"];
                $res["is_opened"] = true;
			}

			if($item_class) {
                $item_properties["class"] = 'class="' . implode(" ", $item_class) . '"';
            }
            if($item_properties) {
                $item_properties = implode(" ", $item_properties);
            }
            if($value["badge"]) {
                $item_actions[] = '<span class="' . cm_getClassByFrameworkCss("default", "badge") . '">' . $value["badge"] . '</span>';
            }

            if($item_actions) {
                $item_actions = '<span class="nav-controls">' . implode(" ", $item_actions) . '</span>';
            }
			$tpl->set_var("actions", $item_actions);
			$tpl->set_var("item_properties", $item_properties);
			$tpl->set_var("item_icon", $item_icon);
			$tpl->set_var("item_tag", $item_tag);
//echo "Sect" . $attr["prefix"] . "Element";

            $parse_key = "Sect" . $attr["prefix"] . "Element";
            if($value["position"]) {
                $position = ucfirst($value["position"]);
                $parse_key .= $position;
                $res["count_position"][$position]++;
            }
            $tpl->parse($parse_key, true);

            $res["count"]++;
        }
    }

    /*
    if($count) {
    	if($location == "navbar")
        	$tpl->set_var("menu_class", $framework_css["menu"]["topbar"]);
        else 
        	$tpl->set_var("menu_class", $framework_css["menu"]["navbar"]);
        $tpl->parse("SectMenu", false);
    }*/
    
   // reset($sel_topbar);

    return $res;
}