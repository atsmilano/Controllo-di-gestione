<?php

class ffWidget_tabs extends ffCommon
{

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $template_file 	 = "ffWidget.html";

	var $class			= "ffWidget_tabs";

	var $widget_deps	= array();
	
    var $libraries		= array();
	
    var $js_deps = array(
							"ff.ffPage.tabs"	=> null
						);
	
    var $css_deps 		= array(
    					);
    					
	// PRIVATE VARS
	var $oPage			= null;
	var $source_path	= null;
	var $style_path		= null;

	var $tpl 			= null;

	var $processed_id	= array();
	var $tab_mode 		= "top"; //top OR left OR right
 	var $framework_css = array(
		"menu" => array(
			"class" => null
			//, "tab" => null //menu OR menu-vertical OR menu-vertical-right
			, "wrap_menu" => null	// null OR array(xs, sm, md, lg)
			, "wrap_pane" => null	// null OR array(xs, sm, md, lg)
		)
		, "menu-item" => array(
			"class" => null
			, "tab" => "menu-item"
		)
		, "pane" => array(
			"class" => null
			, "tab" => "pane"
		)
		, "pane-item" => array(
			"class" => null
			, "tab" => "pane-item-effect" // pane-item-effect OR pane-item
		)
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

	function process($id, &$data, ffPage_base &$oPage, $component = null)
	{
		if ($component !== null)
		{
			$tpl_id = $oPage->components[$component]->getIDIF();
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
//			$oPage->components[$component]->processed_widgets[$id] = "tabs";
		}
		else
		{
			$tpl_id = "main";
			if (!isset($this->tpl[$tpl_id]))
				$this->prepare_template($tpl_id);
		}

		if(isset($data["tab_mode"]))
			$this->tab_mode = $data["tab_mode"];

		$framework_css = cm_getFrameworkCss();
		if(is_array($data["framework_css"]))
			$this->framework_css = array_replace_recursive($this->framework_css, $data["framework_css"]);

		$this->tpl[$tpl_id]->set_var("site_path", $oPage->site_path);
		$this->tpl[$tpl_id]->set_var("theme", $oPage->getTheme());

		$this->tpl[$tpl_id]->set_var("component_id", $id);
		$this->tpl[$tpl_id]->set_var("tab_id", ($_REQUEST["XHR_CTX_ID"] ? $_REQUEST["XHR_CTX_ID"] . "-" : "") . $id);
		$this->tpl[$tpl_id]->set_var("framework_css_name", $framework_css["name"]);
//		if($id == "MainRecord")
//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
		if(!$framework_css["name"]) {
			$this->oPage[0]->tplAddJs("jquery-ui");
			$this->oPage[0]->tplAddJs("ff.history");
			if($oPage->jquery_ui_theme) {
				$this->oPage[0]->tplAddCss("jquery-ui.tabs");
			}		
		}
		
		$this->tpl[$tpl_id]->set_var("SectHeaderTabRow", "");
		$this->tpl[$tpl_id]->set_var("SectHeaderRowBottom", "");
		$this->tpl[$tpl_id]->set_var("SectBodyRow", "");	
		
		/**
		* Tab init
		*/
		if($this->tab_mode) {
            $wrap_tab_need = false;
            $default_wrap_menu = array(
                "xs" => 4
                , "sm" => 3
                , "md" => 3
                , "lg" => 2
            );
            $default_wrap_pane = array(
                "xs" => 8
                , "sm" => 9
                , "md" => 9
                , "lg" => 10
            );

			if($this->tab_mode === true)
				$this->tab_mode = "default";

			switch($this->tab_mode) {
				case "right":
					$wrap_tab_need = cm_getClassByFrameworkCss("menu-vertical-wrap", "tab");
					$this->framework_css["menu"]["tab"] = "menu-vertical-right";
					$tab_position = "Bottom";
					break;
				case "left":
					$wrap_tab_need = cm_getClassByFrameworkCss("menu-vertical-wrap", "tab");
					$this->framework_css["menu"]["tab"] = "menu-vertical";
					break;
				default:
					$floating_tabs = true;
					if($_REQUEST["XHR_CTX_TYPE"] == "dialog") {
						$this->framework_css["menu"]["class"] = "ffTab";
					}
				case "top":
					$this->framework_css["menu"]["tab"] = "menu";
			}

			$first_menu_current = cm_getClassByFrameworkCss("menu-current", "tab");
			if(strpos($this->framework_css["pane-item"]["tab"], "effect") === false) {
				$first_pane_current = cm_getClassByFrameworkCss("pane-current", "tab");
			} else {
				$first_pane_current = cm_getClassByFrameworkCss("pane-current-effect", "tab");
			}
		}
        //ffErrorHandler::raise("AD", E_USER_WARNING  , $oPage->components[$component], get_defined_vars());

        //ffErrorHandler::raise("ASD", E_USER_WARNING, $data, get_defined_vars());
		$i = 0;
		if(is_array($data["contents"]) && count($data["contents"]))
		{
            foreach ($data["contents"] as $subkey => $subvalue)
            {
                $data = array();
                $enable_menu = false;
                $output = "";
                $title = "";
                if ($subvalue["data"] === null)
                    continue;

                if(isset($subvalue["title"]))
                {
                    $title = $subvalue["title"];
                } else {
                    if (
                        $subvalue["data"] instanceof ffGrid_base
                        || $subvalue["data"] instanceof ffRecord_base
                        || $subvalue["data"] instanceof ffDetails_base
                    )
                        $title = $subvalue["data"]->title;
                    elseif (
                        $subvalue["data"] instanceof ffField_base
                    )
                        $title = $subvalue["data"]->label;
                }

                if(is_array($subvalue["menu"]) && count($subvalue["menu"]))
                {
                    $default_menu = $this->framework_css["menu"];
                    foreach($subvalue["menu"] AS $mode => $items)
                    {
                        if(count($items) <= 1)
                            break;

                        if($oPage->components[$component] instanceof ffRecord_base && $oPage->components[$component]->tabs == $mode)
                            break;

                        if($mode == "right") {
                            $default_menu["tab"] = "menu-vertical-right";
                        } else {
                            $default_menu["tab"] = "menu-vertical";
                        }


                        $wrap_menu_start    =  '<div class="' . cm_getClassByFrameworkCss($default_wrap_menu, "col") . '">'
                            . '<ul ' . cm_getClassByDef($default_menu, null, true) . cm_getClassByFrameworkCss("menu", "data", "tab") . '>';
                        $wrap_menu_end      = '</ul></div>';

                        $menu = $wrap_menu_start;
                        $menu_current = true;
                        foreach ($items AS $key => $item)
                        {
                            $menu_current = ($menu_current === true
                                ? array("current" => cm_getClassByFrameworkCss("menu-current", "tab"))
                                : null
                            );
                            $menu .= '<li ' . cm_getClassByDef($this->framework_css["menu-item"], $menu_current, true) . '><a href="#tabmenu-' . $key . '" ' . cm_getClassByFrameworkCss("menu-link", "data", "tab") . ' data-link="'  . ffCommon_url_rewrite($item) . '">' . $item . '</a></li>';
                        }

                        if($mode == "right") {
                            $menu_right = $menu . $wrap_menu_end;
                        } else {
                            $menu_left = $menu . $wrap_menu_end;
                        }
                    }
                    if($menu_left || $menu_right) {
                        $enable_menu = true;
                    }
                }

                if(is_array($subvalue["data"]))
                    $data = $subvalue["data"];
                else
                    $data[] = $subvalue["data"];

                $pane_current = true;
                foreach($data AS $key => $content)
                {
                    $wrap_pane_item_start = "";
                    $wrap_pane_item_end = "";
                    if($enable_menu)
                    {
                        $pane_current = ($pane_current === true
                            ? array("current" => cm_getClassByFrameworkCss("pane-current-effect", "tab"))
                            : null
                        );

                        $wrap_pane_item_start = '<div id="tabmenu-' . $key . '" ' . cm_getClassByDef($this->framework_css["pane-item"], $pane_current, true) . cm_getClassByFrameworkCss("pane-item", "data", "tab") . '>';
                        $wrap_pane_item_end = '</div>';
                    }

                    $ret = $oPage->getContentData($content);
                    if (is_array($ret))
                    {
                        $oPage->output_buffer["headers"] .= $ret["headers"];
                        $oPage->output_buffer["footers"] .= $ret["footers"];
                        $output .= $wrap_pane_item_start . $ret["html"] . $wrap_pane_item_end;
                    }
                    else
                    {
                        $output .= $wrap_pane_item_start . $ret . $wrap_pane_item_end;
                    }
                }

                if($enable_menu)
                {
                    $wrap_pane_start    = '<div class="' . cm_getClassByFrameworkCss($default_wrap_pane, "col") . '">'
                        . '<div ' . cm_getClassByDef($this->framework_css["pane"], null, true) . cm_getClassByFrameworkCss("pane", "data", "tab") . '>';
                    $wrap_pane_end      = '</div></div>';

                    $output = $menu_left . $wrap_pane_start . $output . $wrap_pane_end . $menu_right;
                }


                if(!$title)
                    $title = $subkey;

                $buttons = $subvalue["buttons"];

                $this->tpl[$tpl_id]->set_var("rrow", $i);
                $this->tpl[$tpl_id]->set_var("tab_label", $title);
                $this->tpl[$tpl_id]->set_var("tab_buttons", $buttons);
                $this->tpl[$tpl_id]->set_var("content", $output);

                /**
                * Tab manage rows
                */
                if($this->tab_mode)
                {
                    $this->tpl[$tpl_id]->set_var("tab_pane_properties", cm_getClassByDef($this->framework_css["pane"], null, true) . cm_getClassByFrameworkCss("pane", "data", "tab"));
                    $this->tpl[$tpl_id]->set_var("tab_pane_item_properties", cm_getClassByDef($this->framework_css["pane-item"], array("tab-label" => ffCommon_url_rewrite($title), "current" => $first_pane_current), true) . cm_getClassByFrameworkCss("pane-item", "data", "tab"));

                    $this->tpl[$tpl_id]->set_var("tab_menu_properties", cm_getClassByDef($this->framework_css["menu"], null, true) . cm_getClassByFrameworkCss("menu", "data", "tab"));
                    $this->tpl[$tpl_id]->set_var("tab_menu_item_properties", cm_getClassByDef($this->framework_css["menu-item"], array("current" => $first_menu_current), true));
                    $this->tpl[$tpl_id]->set_var("tab_menu_link_properties", cm_getClassByFrameworkCss("menu-link", "data", "tab") . ' data-link="' . ffCommon_url_rewrite($title) . '"');
                }

                $first_menu_current = "";
                $first_pane_current = "";

                $this->tpl[$tpl_id]->parse("SectHeaderRow" . $tab_position, true);
                $this->tpl[$tpl_id]->parse("SectBodyRow", true);

                $i++;
            }
        }
		/**
		* Tab container
		*/
		if($this->tab_mode && !($oPage->isXHR() && !isset($_REQUEST["XHR_CTX_TYPE"]) && $this->tab_mode == "top")) {
			if(!$this->framework_css["menu"]["wrap_menu"] && $wrap_tab_need)
				$this->framework_css["menu"]["wrap_menu"] = $default_wrap_menu;

			if(!$this->framework_css["menu"]["wrap_pane"] && $wrap_tab_need)
				$this->framework_css["menu"]["wrap_pane"] = $default_wrap_pane;
			
			if($this->framework_css["menu"]["wrap_menu"]) {
				$this->tpl[$tpl_id]->set_var("tab_menu_wrap_start", '<div class="' . cm_getClassByFrameworkCss($this->framework_css["menu"]["wrap_menu"], "col") . '">');
				$this->tpl[$tpl_id]->set_var("tab_menu_wrap_end", '</div>');
			}
			if($this->framework_css["menu"]["wrap_pane"]) {
				$this->tpl[$tpl_id]->set_var("tab_pane_wrap_start", '<div class="' . cm_getClassByFrameworkCss($this->framework_css["menu"]["wrap_pane"], "col") . '">');
				$this->tpl[$tpl_id]->set_var("tab_pane_wrap_end", '</div>');
			}

	        $this->tpl[$tpl_id]->parse("SectHeaderTab" . $tab_position, false);
		}		

		$this->tpl[$tpl_id]->parse("SectBinding", true);
		if(!$oPage->isXHR() && $floating_tabs) {
			if(!$oPage->widget_tabs_placeholder && $oPage->tpl_layer[0]->isset_var("tab-header"))
				$oPage->widget_tabs_placeholder = "tab-header";
			
			if ($oPage->widget_tabs_placeholder || $oPage->widget_tabs_context)
			{
				if ($oPage->widget_tabs_context)
				{
					$oPage->widget_tabs_context[0]->set_var( ($oPage->widget_tabs_placeholder ? $oPage->widget_tabs_placeholder : "tab-header"), $this->tpl[$tpl_id]->getBlockContent("SectHeaderTab" . $tab_position, false) );
				}
				else
				{
			        $oPage->tpl_layer[0]->set_var($oPage->widget_tabs_placeholder, $this->tpl[$tpl_id]->getBlockContent("SectHeaderTab" . $tab_position, false));
				}
			
		        $this->tpl[$tpl_id]->set_var("SectHeaderTab" . $tab_position, "");
			}
		}
		
		return $this->tpl[$tpl_id]->rpparse("SectInstance", true);
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
