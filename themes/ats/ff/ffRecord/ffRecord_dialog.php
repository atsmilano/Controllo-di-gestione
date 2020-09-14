<?php
class ffRecord_dialog extends ffRecord_base
{
	var $framework_css 			= array(
			"component" => array(
                "inner_wrap" => false // null OR false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "outer_wrap" => false // false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "grid" => false        //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
                , "type" => null        //null OR '' OR "inline"
			)
			, "title" => array(
				"class" => "dialogTitle"
				, "col" => false
			)
			, "actions" => array(
				"class" => "actions dialogActionsPanel"
				, "col" => false		
			)
			, "group" => array(
				"def" => null
				, "title" => array(
					"class" => "padding"
				)
			)
			, "record" => array(
				"row" => true
			)
			, "field" => array(
				"label" => array(
					"col" => null
				)
				, "control" => array(
					"col" => null
				)
			)
			, "field-inline" => array(
				"label" => array(
					"col" => array(
						"xs" => 0
						, "sm" => 0
						, "md" => 12
						, "lg" => 4
					)
				)
				, "control" => array(
					"col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 12
						, "lg" => 8
					)
				)
			)
			, "info" => array(
			    "class" => "info"
			    , "callout" => "info"
			)
			, "error" => array(
				"class" => "error dialogSubTitle"
				, "callout" => "danger"
			)
			, "widget" => array(
				"tab" => array(
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
				)			
			)	
	
	);	
	
	var $buttons_options		= array(
											"insert" => array(
														  "display" => true
														, "index" 	=> 1
														, "obj" 	=> null
														, "label" 	=> null
                                                        , "aspect"  => "link"
                                                        , "icon"	=> null
                                                        , "activebuttons" => true
											  				)
										  , "update" => array(
														  "display" => true
														, "index" 	=> 2
														, "obj" 	=> null
														, "label" 	=> null
                                                        , "aspect"  => "link"
                                                        , "icon"	=> null
                                                        , "activebuttons" => true
											  				)

										  , "delete" => array(
														  "display" => true
														, "index" 	=> 1
														, "obj" 	=> null
														, "label" 	=> null
                                                        , "aspect"  => "link"
                                                        , "icon"	=> null
                                                        , "activebuttons" => true
											  				)

										  , "cancel" => array(
														  "display" => true
														, "index" 	=> 0
														, "obj" 	=> null
														, "label" 	=> null
                                                        , "aspect"  => "link"
                                                        , "icon"	=> null 
											  				)
										  , "cursor_first" => array(
														  "display" => true  
														, "index" 	=> 4
														, "obj" 	=> null
														, "label" 	=> ""
								                        , "aspect"  => "button"
														, "newicon" => "NewIcon_firstl"
															)
										  , "cursor_prev" => array(
														  "display" => true  
														, "index" 	=> 3
														, "obj" 	=> null
														, "label" 	=> ""
								                        , "aspect"  => "button"
														, "newicon" => "NewIcon_left"
															)
										  , "cursor_next" => array(
														  "display" => true  
														, "index" 	=> 0
														, "obj" 	=> null
														, "label" 	=> ""
								                        , "aspect"  => "button"
														, "newicon" => "NewIcon_right"
															)
										  , "cursor_last" => array(
														  "display" => true  
														, "index" 	=> -1
														, "obj" 	=> null
														, "label" 	=> ""
								                        , "aspect"  => "button"
														, "newicon" => "NewIcon_lastr"
															)											  				
										);

	var $id_if					= null;

	var $cursor_dialog = false;
	
	public $template_file 	= "ffRecord_dialog.html";

	public $tpl				= null;					// Internal ffTemplate() object

	public $prefix			= null;
	
	/**
	 * Abilita i tab
	 * @var Boolean
	 */	
	public $tab				= false;// false OR top OR left OR right
    
    /**
	 * Una collezione dei tabs presenti nella pagina
	 * @var Array
	 */
	var $tabs				= array();
	
	public $description = null;

	var $widget_discl_enable = true;
	var $widget_def_open = true;
	
	var $widget_activebt_enable = false;
	
	var $displayed_fields		= 0;

/*	var $buttons_options		= array(
			"insert" => array(
						  "display" => true
						, "index" 	=> 1
						, "obj" 	=> null
						, "label" 	=> null
                        , "aspect"  => "button"
							)
		  , "update" => array(
						  "display" => true
						, "index" 	=> 2
						, "obj" 	=> null
						, "label" 	=> null
                        , "aspect"  => "button"
							)

		  , "delete" => array(
						  "display" => true
						, "index" 	=> 1
						, "obj" 	=> null
						, "label" 	=> null
                        , "aspect"  => "button"
							)

		  , "cancel" => array(
						  "display" => true  
						, "index" 	=> 0
						, "obj" 	=> null
						, "label" 	=> null
                        , "aspect"  => "button"
							)
		);*/

	var $disable_mod_notifier_on_error = false;
	var $auto_wrap 				= true;
	var $js_deps = array(
		"ff.ffRecord" => null
	);

	var $properties = null;
	
	
	function __construct(ffPage_base $page, $disk_path, $theme)
    {
        parent::__construct($page, $disk_path, $theme);
		
		if (FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID)
			$this->id_if = uniqid();
	}
		
	function getIDIF()
	{
		if ($this->id_if !== null)
			return $this->id_if;
		else
			return $this->id;
	}

	function getPrefix()
	{
		$tmp = $this->getIDIF();
		if (strlen($tmp))
			return $tmp . "_";
	}
	
	/**
	 * aggiunge un contenuto all'oggetto record
	 * supporta ffField_base, ffDetails_base, ffGrid_base
	 * può essere utilizzata anche per definire gruppi
	 * @param mixed $content
	 * @param mixed $group può essere o il nome di un gruppo o true nel caso si voglia definire un gruppo
	 * @param string $id l'id del contenuto, anche nel caso di un gruppo
	 */
	public function addContent($content, $group = null, $id = null)
	{
		if ($content !== null)
		{
			if (is_object($content)	&& is_subclass_of($content, "ffField_base"))
			{
				$content->framework_css = array_replace_recursive($this->framework_css["field" . ($this->framework_css["component"]["type"] ? "-" : "") . $this->framework_css["component"]["type"]], $content->framework_css);
			}
		}
		
		parent::addContent($content, $group, $id);
	}
	public function addTab($name)
	{
		$this->tab = true;
		if (!isset($this->tabs["contents"][$name]))
			$this->tabs["contents"][$name] = array();
	}

	public function setTabTitle($name, $title)
	{
		$this->tabs["contents"][$name]["title"] = $title;
	}

	public function tplDisplay()
	{
		$this->tplDisplayContents();

		// display selected record controls (delete, insert..)
		$this->tplDisplayControls();

		// display error
		$this->tplDisplayError();
	}

	protected function tplLoad()
	{
		if ($this->tpl === null)
		{
			$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
			$this->tpl[0]->load_file($this->template_file, "main");
		}

		$this->tpl[0]->set_var("component", $this->getPrefix());
		$this->tpl[0]->set_var("component_id", $this->getIDIF());

		$this->tpl[0]->set_var("site_path", $this->site_path);
		$this->tpl[0]->set_var("page_path", $this->page_path);
		$this->tpl[0]->set_var("theme", $this->getTheme());

		$this->tpl[0]->set_var("ret_url", ffCommon_specialchars($this->parent[0]->ret_url));

		$this->tplSetFixedVars();

		// EVENT HANDLER
		$res = $this->doEvent("on_process_template", array(&$this, $this->tpl[0]));
	}

	public function tplSetFixedVars()
	{
	    if(!$this->title)
	        $this->title = $this->parent[0]->title;
		if (is_array($this->fixed_vars) && count($this->fixed_vars))
		{
			foreach ($this->fixed_vars as $key => $value)
			{
				if (is_object($value) && get_class($value) == "ffData")
				{
					$this->tpl[0]->set_var($key, $value->getValue());
					if (strlen($value->ori_value))
						$this->tpl[0]->parse("SectSet_" . $key, false);
					else
						$this->tpl[0]->set_var("SectSet_" . $key, "");
				}
				else
				{
					$this->tpl[0]->set_var($key, $value);
					if (strlen($value))
						$this->tpl[0]->parse("SectSet_" . $key, false);
					else
						$this->tpl[0]->set_var("SectSet_" . $key, "");
				}
			}
			reset($this->fixed_vars);
		}
	}

	public function tplParse($output_result)
	{
		$res = ffRecord::doEvent("on_tplParse", array(&$this, $this->tpl[0]));
		$res = $this->doEvent("on_tpl_parse", array(&$this, $this->tpl[0]));

		$output_buffer = "";

		$this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
		$this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);

		$this->tplSetFixedVars();

		if(strlen($this->title)) {
			$this->tpl[0]->set_var("record_title", $this->title);
			$this->tpl[0]->set_var("title_class", cm_getClassByDef($this->framework_css["title"]));
			$this->tpl[0]->parse("SectTitle", false);
		}
		if ($this->description !== null) {
			$this->tpl[0]->set_var("info_class", cm_getClassByDef($this->framework_css["info"]));
			$this->tpl[0]->set_var("record_description", $this->description);
			$this->tpl[0]->parse("SectDescription", false);
		}

		if(!$this->parent[0]->use_own_form && !$this->ajax) {
			$this->tpl[0]->set_var("record_tag", "form");
			$this->tpl[0]->set_var("component", "");
			$this->tpl[0]->set_var("id", "frmAction");
			$this->tpl[0]->set_var("value", "");
			$this->tpl[0]->parse("SectHiddenField", true);
			$this->properties["method"] = "post";
		} else
			$this->tpl[0]->set_var("record_tag", "div");

		$component_class["default"] = $this->class;
		if($this->framework_css["component"]["grid"]) {
            if(is_array($this->framework_css["component"]["grid"]))
                $component_class["grid"] = cm_getClassByFrameworkCss($this->framework_css["component"]["grid"], "col");
            else {
                $component_class["grid"] = cm_getClassByFrameworkCss("", $this->framework_css["component"]["grid"]);
            }
        }
		$component_class["form"] = cm_getClassByFrameworkCss("component" . ($this->framework_css["component"]["type"] ? "-" : "") . $this->framework_css["component"]["type"], "form");
        
		$this->properties["id"] = $this->getIDIF();
		$this->properties["class"] = implode(" ", array_filter($component_class));
		
		$this->tpl[0]->set_var("component_properties", $this->getProperties());

		if(is_array($this->framework_css["component"]["col"]) && $this->framework_css["component"]["inner_wrap"] === null)
			$this->framework_css["component"]["inner_wrap"] = "row";

        if($this->framework_css["component"]["inner_wrap"]) 
        {
            if(is_array($this->framework_css["component"]["inner_wrap"])) {
                $this->tpl[0]->set_var("inner_wrap_start", '<div class="' . cm_getClassByFrameworkCss($this->framework_css["component"]["inner_wrap"], "col", "innerWrap") . '">');
            } elseif(is_bool($this->framework_css["component"]["inner_wrap"])) {
                $this->tpl[0]->set_var("inner_wrap_start", '<div class="innerWrap">');
            } else {
                $this->tpl[0]->set_var("inner_wrap_start", '<div class="' . cm_getClassByFrameworkCss("", $this->framework_css["component"]["inner_wrap"], "innerWrap") . '">');
            }
            $this->tpl[0]->set_var("inner_wrap_end", '</div>');
        }       
           
        if($this->framework_css["component"]["outer_wrap"]) 
        {
            if(is_array($this->framework_css["component"]["outer_wrap"])) {
                $this->tpl[0]->set_var("outer_wrap_start", '<div class="' . cm_getClassByFrameworkCss($this->framework_css["component"]["outer_wrap"], "col", $this->getIDIF() . "Wrap outerWrap"). '">');
            } elseif(is_bool($this->framework_css["component"]["outer_wrap"])) {
                $this->tpl[0]->set_var("outer_wrap_start", '<div class="' . $this->getIDIF() . 'Wrap outerWrap">');
            } else {
                $this->tpl[0]->set_var("outer_wrap_start", '<div class="' . cm_getClassByFrameworkCss("", $this->framework_css["component"]["outer_wrap"], $this->getIDIF() . "Wrap outerWrap") . '">');
            }
            $this->tpl[0]->set_var("outer_wrap_end", '</div>');                
        }

		if ($this->tab && count($this->tabs))
		{                
			$this->tpl[0]->set_var("form_tabs", $this->parent[0]->widgets["tabs"]->process($this->getIDIF(), $this->tabs, $this->parent[0], $this->id));
		}
		elseif ($this->displayed_fields)
		{
			$this->tpl[0]->parse("SectGroup", false);
		}

		$output_buffer = $this->tpl[0]->rpparse("main", false);

/*		if ($this->frmAction && !$this->strError)
			$this->parent[0]->contain_error = true;*/

		return $output_buffer;
	}
	
	function process_headers()
	{
		if (!isset($this->tpl[0]))
			return;

		return $this->tpl[0]->rpparse("SectHeaders", false);
	}

	function process_footers()
	{
		if (!isset($this->tpl[0]))
			return;

		return $this->tpl[0]->rpparse("SectFooters", false);
	}
	
	protected function tplDisplayContents()
	{
		foreach($this->key_fields as $key => $FormField)
		{
			if (!isset($this->form_fields[$key]) && strlen($this->key_fields[$key]->value->getValue()))
			{
				$this->tpl[0]->set_var("id", "keys[" . $key . "]");
				$this->tpl[0]->set_var("value", ffCommon_specialchars($this->key_fields[$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
				$this->tpl[0]->parse("SectHiddenField", true);

				$this->tpl[0]->set_var("Key_" . $key, $this->key_fields[$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE));
				$this->tpl[0]->set_var("encoded_Key_" . $key, rawurlencode($this->key_fields[$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
				if (strlen($this->key_fields[$key]->value->ori_value))
				{
					$this->tpl[0]->parse("SectSetKey" . $key, false);
					$this->tpl[0]->set_var("SectNotSetKey" . $key, "");
				}
				else
				{
					$this->tpl[0]->set_var("SectSetKey" . $key, "");
					$this->tpl[0]->parse("SectNotSetKey" . $key, false);
				}
			}
		}
		reset($this->key_fields);

		if (is_array($this->hidden_fields) && count($this->hidden_fields))
		{
			foreach($this->hidden_fields as $key => $value)
			{
				$this->tpl[0]->set_var("id", $key);
				$this->tpl[0]->set_var("value", ffCommon_specialchars($value->getValue(null, FF_SYSTEM_LOCALE)));
				$this->tpl[0]->parse("SectHiddenField", true);
			}
			reset($this->hidden_fields);
		}

		if ($this->display_required && $this->display_required_note)
		{
			$this->tpl[0]->set_var("required_symbol", $this->required_symbol);
			$this->tpl[0]->parse("SectRequiredNote", false);
		}
		else
			$this->tpl[0]->set_var("SectRequiredNote", "");

		if ($this->tab)
		{
			$this->parent[0]->widgetLoad("tabs", null, $this->parent[0]);
			$this->tabs = array(
				"tab_mode" => $this->tab
				, "framework_css" => $this->framework_css["widget"]["tab"]
			);
		}
		else
		{
			if(strlen($this->title))
				$this->tpl[0]->parse("SectTitle", false);
			else
				$this->tpl[0]->set_var("SectTitle", "");
		}

		$this->displayed_fields = 0;

		//$groups_tabs = array(); // conteggio gruppi per tab
		if(!is_array($this->groups))
			$this->groups = array();

		$this->groups = array_merge(array("_main_" => array(
											"class" => "default"
											, "title" => ""
											, "contents" => array()
											, "data" => null
										)
									), $this->groups);

		foreach ($this->contents as $key => $content)
		{
			if (!$content["group"]) {
				$this->groups["_main_"]["contents"][$key] = $content;
				continue;
			}	
			//if ($this->groups[$key]["tab"])
			//	$groups_tabs[$this->groups[$key]["tab"]]++;
		}
		reset($this->contents);

		if(!count($this->groups["_main_"]["contents"]))
			unset($this->groups["_main_"]);

		if (is_array($this->groups) && count($this->groups))
		{
            $last_tab_key = "";
			foreach($this->groups AS $group_key => $group_value) 
			{
				$i = $this->tplDisplayFields($this->groups[$group_key]["contents"], $group_key, $group_value["primary_field"]);
				if ($i > 0)
				{
					$res = $this->doEvent("on_before_parse_group", array(&$this, $group_key));
                    $rc = end($res);

					if (!$rc)
					{
				        $this->tpl[0]->set_var("group_pre_content", $this->groups[$group_key]["fixed_pre_content"]);
				        $this->tpl[0]->set_var("group_post_content", $this->groups[$group_key]["fixed_post_content"]);
					
						if($group_key == "_main_") 
						{
							$this->tpl[0]->set_var("SectGroupStart", "");
							$this->tpl[0]->set_var("SectGroupEnd", "");
						} 
						else 
						{
                            $group_class = array();
                            $group_class["default"] = cm_getClassByDef($this->framework_css["group"]["def"]);
                            $group_class["custom"] = (strlen($this->groups[$group_key]["class"]) ? $this->groups[$group_key]["class"] : $group_key);

                            if(is_array($group_value["tab"])) {
                                $tab_key = ($group_value["tab"]["title"]
                                    ? $group_value["tab"]["title"]
                                    : $group_key
                                );
                            } elseif(is_string($group_value["tab"])) {
                                $tab_key = (strlen($group_value["tab"])
                                    ? $group_value["tab"]
                                    : $group_key
                                );
                            } elseif(is_bool($group_value["tab"])) {
                                $tab_key = ($last_tab_key
                                    ? $last_tab_key
                                    : $group_key
                                );

                            } else {
                                $tab_key = $group_key;
                            }

                            $last_tab_key = $tab_key;

                            if($this->groups[$group_key]["hide_title"]
                                || (!strlen($this->groups[$group_key]["title"])
                                    && !strlen($this->groups[$group_key]["primary_field"])
                                )
                            ) {
                                $this->tpl[0]->set_var("SectGroupTitle", "");
                            } else {
                                $arrTitleProperties = array();
                                $arrTitleProperties["class"]["default"] = cm_getClassByDef($this->framework_css["group"]["title"]);
                                $this->tpl[0]->set_var("title_properties", "");

                                if($group_value["primary_field"]) {
                                    $arrTitleProperties["class"]["primary_field"] = "ffCheckDep";
                                    $arrTitleProperties["onclick"] = "javascript:ff.ffRecord.displayFieldSetElem(this, '" . $this->getIDIF() . "_" . $group_value["primary_field"] . "');";
                                }
                                /*if($this->groups[$group_key]["tab_dialog"] === true && $group_value["primary_field"]) {
                                    $group_title = '<span class="dialogSubTitleTab ' . ($this->groups[$group_key]["tab_dialog_selected"] ? "selected " : "") . 'dep-' . ffCommon_url_rewrite($group_key) . '">' . ($this->groups[$group_key]["title"] ? $this->groups[$group_key]["title"] : ffTemplate::_get_word_by_code($group_key)) . '</span>';

                                    $this->tpl[0]->set_var("GroupTitle", $group_title . $this->tpl[0]->getBlockContent("GroupTitle", false));
                                } else {*/
                                //$group_class["tab_dialog"] = "dlg-tab dlg-" . ffCommon_url_rewrite($group_key); //*
                                /*	if($this->groups[$group_key]["tab_dialog"] === true)
                                        $arrTitleProperties["class"]["tab_dialog"] = "dialogSubTitleTab " . ($this->groups[$group_key]["tab_dialog_selected"] ? "selected " : "") . "dep-" . ffCommon_url_rewrite($group_key);
                                    elseif(strlen($this->groups[$group_key]["tab_dialog"]))
                                        $group_class["tab_dialog"] = "dlg-tab dlg-" . ffCommon_url_rewrite($this->groups[$group_key]["tab_dialog"]);
                                */
                                if(!$group_value["primary_field"]) {
                                    //ADDED-ATS commento a && $tab_key != $group_key
                                    if($this->groups[$group_key]["title"] /*&& $tab_key != $group_key*/)
                                        $this->tpl[0]->set_var("GroupTitle", $this->groups[$group_key]["title"]);
                                    else
                                        $this->tpl[0]->set_var("GroupTitle", "");
                                }
                                //}


                                if(is_array($arrTitleProperties) && count($arrTitleProperties)) {
                                    $str_title_properties = "";
                                    foreach($arrTitleProperties AS $arrTitleProperties_key => $arrTitleProperties_value) {
                                        $str_title_properties .= " " . $arrTitleProperties_key . '="' . (is_array($arrTitleProperties_value) ? implode(" ", array_filter($arrTitleProperties_value)) : $arrTitleProperties_value) . '"';
                                    }
                                    $this->tpl[0]->set_var("title_properties", $str_title_properties);
                                }
                                if($this->tpl[0]->getBlockContent("GroupTitle", false))  //(!$this->tab || $this->tab === "left" || $this->tab === "right") &&
                                    $this->tpl[0]->parse("SectGroupTitle", false);
                                else
                                    $this->tpl[0]->set_var("SectGroupTitle", "");
                            }

                            $this->tpl[0]->set_var("group_class", implode(" ", array_filter($group_class)));

                            if(isset($this->groups[$group_key]["description"]) && strlen($this->groups[$group_key]["description"])) {
                                $this->tpl[0]->set_var("GroupDescription", $this->groups[$group_key]["description"]);
                                $this->tpl[0]->parse("SectGroupDescription", false);
                            } else {
                                $this->tpl[0]->set_var("SectGroupDescription", "");
                            }

                            $this->tpl[0]->parse("SectGroupStart", false);
                            $this->tpl[0]->parse("SectGroupEnd", false);
                        }

                        if ($this->tab)
                        {
                            if ($group_key == "_main_")
                                $this->groups["_main_"]["data"] .= $this->tpl[0]->rpparse("SectGroup", false);
                            else
                            {
                                if(!$this->tabs["contents"][$tab_key]["title"])
                                    $this->tabs["contents"][$tab_key]["title"] = (is_array($group_value["tab"]) && $group_value["tab"]["title"]
                                        ? $group_value["tab"]["title"]
                                        : ($group_value["title"]
                                            ? $group_value["title"]
                                            : $tab_key
                                        )
                                    );

                                if($group_value["current"])
                                    $this->tabs["current"] = $tab_key;

                                $this->tabs["contents"][$tab_key]["data"][$group_key] = $this->tpl[0]->rpparse("SectGroup", false);
                                if(is_array($group_value["tab"]) && is_array($group_value["tab"]["menu"]))
                                    $this->tabs["contents"][$tab_key]["menu"][$group_value["tab"]["menu"]["mode"]][$group_key] = $group_value["tab"]["menu"]["title"];

                            }
                            $this->tpl[0]->set_var("SectGroup", "");
                        }
                        else
                        {
                            if ($this->groups[$group_key]["hide_title"]/*&& $groups_tabs[$tab] == 1*/)
                                $this->tpl[0]->parse("SectGroupTitle", false);

                            $this->tpl[0]->parse("SectGroup", true);
                        }
					}
				}	
			}
		}

		if (isset($this->groups["_main_"]) && $this->groups["_main_"]["data"] !== null)
			$this->tpl[0]->set_var("form_default", $this->groups["_main_"]["data"]);
	}
	
	/**
	 * Visualizza il campo del record
	 */
	protected function tplDisplayFields($contents, $group_key, $primary_field = null) 
	{
		$this->tpl[0]->set_var("SectGroupRow", "");
		$this->tpl[0]->set_var("SectGroupRow_" . $group_key, "");
	
		$vars_to_reset = array();
		$i = 0;
		$wrap_count = 0;
		$count_contents = 0;

        if(is_array($contents) && count($contents)) 
        {
            $is_wrapped = false;
		    foreach ($contents as $key => $subvalue)
		    {
		    	$this->tpl[0]->set_var("SectGroupLabel", "");
				$this->tpl[0]->set_var("field_container_start", "");
				$this->tpl[0]->set_var("field_container_end", "");
				$this->tpl[0]->set_var("content_pre_label", "");
				$this->tpl[0]->set_var("content_in_label", "");
				$this->tpl[0]->set_var("content", "");
				
		    	$container_class = array();
		    	$container_properties = array();
		    	
		    	$count_contents++;
			    if (is_string($subvalue["data"]) || get_class($subvalue["data"]) == "ffTemplate" || is_subclass_of($subvalue["data"], "ffDetails_base") || is_subclass_of($subvalue["data"], "ffGrid_base") || is_subclass_of($subvalue["data"], "ffRecord_base"))
			    {
				    $i++;

				    if (is_string($subvalue["data"]))
					    $this->tpl[0]->set_var("content", $subvalue["data"]);
				    else if (get_class($subvalue["data"]) == "ffTemplate")
					    $this->tpl[0]->set_var("content", $subvalue["data"]->rpparse("main", false));
				    else
					    $this->tpl[0]->set_var("content", "{{" . $subvalue["data"]->id . "}}");
				    
 					if(($this->framework_css["record"]["row"] && (!$wrap_count || $wrap_count >= 12))
				    	&& (is_object($subvalue["data"]) 
				    		&& (is_subclass_of($subvalue["data"], "ffDetails_base") || is_subclass_of($subvalue["data"], "ffGrid_base") || is_subclass_of($subvalue["data"], "ffRecord_base")) 
				    		&& !$subvalue["data"]->framework_css["component"]["grid"]
				    	)
				    ) {//con le grid e troppo piccola la visualizzazione
				    	$container_class["wrap"] = "form-wrap";
				    	$container_class["row"] = cm_getClassByFrameworkCss("row", "form");
					}

				    //$this->displayed_fields++;
			    }
			    elseif (is_subclass_of($subvalue["data"], "ffField_base"))
			    {
				    if (
						    (/*!$this->use_fixed_fields && $this->display_values && */$this->form_fields[$key]->skip_if_empty && !strlen($this->form_fields[$key]->value->ori_value))
					    )
					    continue;

				    if (!isset($this->key_fields[$subvalue["data"]->id])) // displayed key fields are threated previously
				    {
					    if (is_array($this->form_fields[$subvalue["data"]->id]->multi_fields) && count($this->form_fields[$subvalue["data"]->id]->multi_fields))
					    {
						    foreach ($this->form_fields[$subvalue["data"]->id]->multi_fields as $mul_subkey => $mul_subvalue)
						    {
							    $this->tpl[0]->set_var("id", $subvalue["data"]->id . "[" . $mul_subkey . "]");
							    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$subvalue["data"]->id]->value[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)));
							    $this->tpl[0]->parse("SectHiddenField", true);
	                            if($this->record_exist) {
								    $this->tpl[0]->set_var("id", $subvalue["data"]->id . "_ori[" . $mul_subkey . "]");
								    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$subvalue["data"]->id]->value_ori[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)));
								    $this->tpl[0]->parse("SectHiddenField", true);
	                            }
						    }
						    reset($this->form_fields[$subvalue["data"]->id]->multi_fields);
					    }
					    else
					    {     
						    if($this->record_exist) {
	                            $this->tpl[0]->set_var("id", $subvalue["data"]->id . "_ori");
							    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$subvalue["data"]->id]->value_ori->getValue($this->form_fields[$subvalue["data"]->id]->base_type, FF_SYSTEM_LOCALE)));
							    $this->tpl[0]->parse("SectHiddenField", true);
	                        }
					    }
				    }
				    
				    $i++;
					
				    // EVENT HANDLER
				    $res = $this->doEvent("on_process_field", array(&$this, $key));

				    // container
				    foreach ($vars_to_reset as $field_key => $field_values)
				    {
					    $this->tpl[0]->set_var($field_key, "");
				    }
				    reset($vars_to_reset);
				    
				    $this->tpl[0]->set_var("container_properties", $this->form_fields[$key]->getProperties($this->form_fields[$key]->container_properties));

				    if (is_array($this->form_fields[$key]->container_vars) && count($this->form_fields[$key]->container_vars))
				    {
					    foreach ($this->form_fields[$key]->container_vars as $field_key => $field_value)
					    {
						    $this->tpl[0]->set_var($field_key, $field_value);
						    $vars_to_reset[$field_key] = true;
					    }
					    reset($this->form_fields[$key]->container_vars);
				    }
			    
				    
				    if(strlen($this->form_fields[$key]->container_class))
					    $container_class["custom"] = $this->form_fields[$key]->container_class;
					else
						$container_class["custom"] = $this->form_fields[$key]->class;
						
				    if($this->form_fields[$key]->placeholder === true) {
					    $this->form_fields[$key]->placeholder = ffCommon_specialchars($this->form_fields[$key]->label);
				    }				    	

				    $control_var = "";
					$control_prefix = "";
					$control_postfix = "";
					$is_combine_field = false;
				    					
				    // LABEL 
				    $label_set = false;
				    if($this->form_fields[$key]->display_label)
				    {
					    $this->tpl[0]->set_var("label_prefix", "");
					    $this->tpl[0]->set_var("label_postfix", "");
					    $required_symbol = "";

						if(($this->form_fields[$key]->get_control_type() == "checkbox" || $this->form_fields[$key]->get_control_type() == "radio") && $this->form_fields[$key]->widget == "") {
							$control_var = cm_getClassByFrameworkCss("control-check-position", "form");
							$is_combine_field = true;
						}
					    
					    if ($this->form_fields[$key]->description !== null) 
					    {       
						    $this->tpl[0]->set_var("fielDescription", $this->form_fields[$key]->description);
						    $this->tpl[0]->parse("SectDescriptionLabel", false);
	                        
	                        $label_set = true;
					    }	
					    else
					    {
						    $this->tpl[0]->set_var("fielDescription", "");
						    $this->tpl[0]->set_var("SectDescriptionLabel", "");
					    }
	                    
	                    if($this->form_fields[$key]->label_properties)
	                    {
						    $this->tpl[0]->set_var("label_properties", " " . $this->form_fields[$key]->getProperties($this->form_fields[$key]->label_properties));
	                        $label_set = true;
	                    }

					    if ($this->display_required && $this->form_fields[$key]->required) {
							$container_class["require"] = "required";
							$required_symbol = "*";
					    }

 						if(strlen($this->form_fields[$key]->label)) 
	                    {
						    if($primary_field == $key && !$control_var) {
						    	$prefix_label = "content_pre_";
						    	$label_set = false; 
						    } 
						    else
						    {
						    	$prefix_label = "";
						    	$label_set = true; 
							}
						    if($this->form_fields[$key]->encode_label) 	
							    $this->tpl[0]->set_var($prefix_label . "label", ffCommon_specialchars($this->form_fields[$key]->label) . $required_symbol);
						    else
							    $this->tpl[0]->set_var($prefix_label . "label",$this->form_fields[$key]->label . $required_symbol);
	                    }

	                    if($label_set) {
						    /**
						    * Label Class
						    */
						    $this->tpl[0]->set_var("label_for", $this->getIDIF() . "_" . $key);
						    
							$arrColumnLabel = $this->form_fields[$key]->framework_css["label"]["col"];
							$arrColumnControl = $this->form_fields[$key]->framework_css["control"]["col"];
							$type_label = "";
							//if($primary_field == $key)
							//	$type_label = "inline";

							if(!strlen($control_var))
							{
								if(is_array($arrColumnLabel) && count($arrColumnLabel)
									&& is_array($arrColumnControl) && count($arrColumnControl)
								) {
									$this->tpl[0]->set_var("label_prefix", '<div class="' . cm_getClassByDef($this->form_fields[$key]->framework_css["label"]) . '">');
									//$this->tpl[0]->set_var("label_prefix", '<div class="' . cm_getClassByFrameworkCss($arrColumnLabel, "col") . " " . cm_getClassByFrameworkCss("align-right", "util") . '">');
									$this->tpl[0]->set_var("label_postfix", '</div>');
								
									$control_prefix = '<div class="' . cm_getClassByDef($this->form_fields[$key]->framework_css["control"]) . '">';
									//$control_prefix = '<div class="' . cm_getClassByFrameworkCss($arrColumnControl, "col") . '">';
									$control_postfix = '</div>';
								  //  $type_label = "inline";
								}
							}
							
							//if($this->framework_css["component"]["type"] === null && $type_label)
							//	$this->framework_css["component"]["type"] = $type_label;

							$label_class = cm_getClassByFrameworkCss("label" . $type_label, "form");
							if($this->framework_css["component"]["type"] && $is_combine_field) {
								if($control_var == "_in_label")
									$label_class .= ($label_class ? " " : "") . cm_getClassByFrameworkCss($arrColumnLabel, "push") . " " . cm_getClassByFrameworkCss($arrColumnControl, "col");
								else
									$container_class["align"] = cm_getClassByFrameworkCss("align-right", "util");
							}
							
							if($label_class)
								$this->tpl[0]->set_var("label_class", ' class="' . $label_class . '"');
							else
								$this->tpl[0]->set_var("label_class", "");

					    } else {
					    	$control_var = "";
					    }
				    } 

				    /**
				    * Row Class
				    */
				    $container_class["default"] = $this->form_fields[$key]->get_control_class(null, null, array("framework_css" => false, "control_type" => false)); 
				    
					if($primary_field != $key) {
					    if(is_array($this->form_fields[$key]->framework_css["container"]["col"]) 
				    		&& count($this->form_fields[$key]->framework_css["container"]["col"])
					    ) {
							$container_class["grid"] = cm_getClassByFrameworkCss($this->form_fields[$key]->framework_css["container"]["col"], "col");
							if(!$is_wrapped) {
								$wrap_class = array("form-wrap");
								if($this->form_fields[$key]->framework_css["container"]["row"]) {
									$wrap_class[] = cm_getClassByFrameworkCss("row", "form");
								}
								$this->tpl[0]->set_var("wrap_class", implode(" ", array_filter($wrap_class)));
								$is_wrapped = $this->tpl[0]->parse("SectWrapStart", false);
							}

							$wrap_count = $wrap_count + $this->form_fields[$key]->framework_css["container"]["col"]["lg"];
						} elseif($this->form_fields[$key]->framework_css["container"]["row"]) {
							$container_class["row"] = cm_getClassByFrameworkCss("row-padding", "form");
							//$container_class["grid"] = cm_getClassByFrameworkCss(array(12), "col");

							/*if($is_wrapped) {
								$wrap_count = 12;
								$container_class["grid"] = cm_getClassByFrameworkCss(array($wrap_count), "col");
							} elseif($label_set) { 
								$container_class["row"] = cm_getClassByFrameworkCss("row", "form");
							}*/
						} else {
							/*if($is_wrapped) {
								$wrap_count = 12;
								$container_class["grid"] = cm_getClassByFrameworkCss(array($wrap_count), "col");
							}*/
						}
					}

				    // CONTROL/VALUE SECTION
				    /**
				    * Control Class
				    */
				    $rc = false;
                    if($this->tpl[0]->isset_var($key . ":value"))
                        $rc |= $this->tpl[0]->set_var($key . ":value", $this->form_fields[$key]->getValue());
                    if($this->tpl[0]->isset_var($key . ":displayvalue"))
                        $rc |= $this->tpl[0]->set_var($key . ":displayvalue", $this->form_fields[$key]->getDisplayValue());

                    if ($this->tpl[0]->isset_var($key)) {
                        if ($this->display_values)
                            $buffer_field = $this->form_fields[$key]->getDisplayValue();
                        else
                            $buffer_field = $this->form_fields[$key]->process();

                        if(is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
                        {
                            foreach ($this->form_fields[$key]->multi_fields as $mul_subkey => $mul_subvalue)
                            {
                                $buffer_field .= '<input type="hidden" id="' . $this->id . "_". $key . "[" . $mul_subkey . "]" . '" name="' . $this->id . "_". $key . "[" . $mul_subkey . "]" . '" value="' . ffCommon_specialchars($this->form_fields[$key]->value[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)) . '" />';
                                if($this->record_exist) 
                                    $buffer_field .= '<input type="hidden" id="' . $this->id . "_". $key . "_ori" . "[" . $mul_subkey . "]" . '" name="' . $this->id . "_". $key . "_ori" . "[" . $mul_subkey . "]" . '" value="' . ffCommon_specialchars($this->form_fields[$key]->value_ori[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)) . '" />';
                            }
                        } else {
                            $buffer_field .= '<input type="hidden" id="' . $this->id . "_". $key . "_ori" . '" name="' . $this->id . "_". $key . "_ori" . '" value="' . ffCommon_specialchars($this->form_fields[$key]->value_ori->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)) . '" />';
                        }

                        $rc |= $this->tpl[0]->set_var($key, $buffer_field);
                    }
                    
				    if (!$rc)
				    {
					    if (!$this->display_values || strlen($this->form_fields[$key]->control_type))
					    {
                            if ($this->tpl[0]->isset_var("content" . $control_var)) {
                                $processed_field = $this->form_fields[$key]->process();

                                if($control_prefix && ($this->form_fields[$key]->framework_css["fixed_pre_content"] || $this->form_fields[$key]->framework_css["fixed_post_content"])) {
                                    $control_prefix = $control_prefix . '<div class="' . cm_getClassByFrameworkCss("group", "form") . '">';
                                    $control_postfix = '</div>' . $control_postfix;
                                }                                
                                $this->tpl[0]->set_var("content" . $control_var, $control_prefix . $processed_field . $control_postfix);
                            }
					    }
					    else
						    $this->tpl[0]->set_var("content" . $control_var, $this->form_fields[$key]->getDisplayValue());
				    }

					if($label_set)
						$this->tpl[0]->parse("SectGroupLabel", false);

				    /*
				    $fieldset = false;

				    switch ($this->form_fields[$key]->extended_type)
				    {
					    case "Selection":
						    switch ($this->form_fields[$key]->get_control_type())
						    {
							    case "radio":
								    $fieldset = true;
								    break;
						    }
						    break;
				    }

				    if ($fieldset)
				    {
					    $this->tpl[0]->set_var("SectField", "");
					    $this->tpl[0]->parse("SectFieldSet", false);
				    }
				    else
				    {
					    $this->tpl[0]->parse("SectField", false);
					    $this->tpl[0]->set_var("SectFieldSet", "");
				    }*/

				    // Manage Fixed Sections
				    if(!is_array($this->form_fields[$key]->value->ori_value))
				    {
					    if (strlen($this->form_fields[$key]->value->ori_value))
					    {
						    $this->tpl[0]->parse("SectSet_$key", false);
						    $this->tpl[0]->set_var("SectNotSet_$key", "");
					    }
					    else
					    {
						    $this->tpl[0]->set_var("SectSet_$key", "");
						    $this->tpl[0]->parse("SectNotSet_$key", false);
					    }
				    }
				    if ($this->form_fields[$key]->extended_type == "Selection")
				    {
					    $this->tpl[0]->set_regexp_var("/SectSet_" . $key . "_.+/", "");
					    $this->tpl[0]->parse("SectSet_" . $key . "_" . $this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE), false);
				    }

				    $this->tpl[0]->parse("Sect_$key", false);

				    // "display" hidden original values
	    /*						if (!isset($this->key_fields[$key])) // displayed key fields are threated previously
				    {
					    if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
					    {
						    foreach ($this->form_fields[$key]->multi_fields as $mul_subkey => $mul_subvalue)
						    {
							    $this->tpl[0]->set_var("id", $key . "[" . $mul_subkey . "]");
							    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$key]->value[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)));
							    $this->tpl[0]->parse("SectHiddenField", true);

	                            if($this->record_exist) {
								    $this->tpl[0]->set_var("id", $key . "_ori[" . $mul_subkey . "]");
								    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$key]->value_ori[$mul_subkey]->getValue($mul_subvalue["type"], FF_SYSTEM_LOCALE)));
								    $this->tpl[0]->parse("SectHiddenField", true);
	                            }
						    }
						    reset($this->form_fields[$key]->multi_fields);
					    }
					    else
					    {       
	                        if($this->record_exist) {
							    $this->tpl[0]->set_var("id", $key . "_ori");
							    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->form_fields[$key]->value_ori->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
							    $this->tpl[0]->parse("SectHiddenField", true);
	                        }
					    }
				    }
	    */					
			    }

				$container_inner_start = '';
				$container_inner_end = '';
				if(count($container_class)) {
					if(!$control_prefix && ($this->form_fields[$key]->framework_css["fixed_pre_content"] || $this->form_fields[$key]->framework_css["fixed_post_content"])) {
						$wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
						if($wrap_addon) {
							if($container_class["grid"]) {
								$container_inner_start = '<div class="' . cm_getClassByFrameworkCss("group-padding", "form") . '">';
								$container_inner_end = '</div>';
							} else {
								$container_class["row"] = cm_getClassByFrameworkCss("group-padding", "form");
							}
						}
					}
									
				    $str_container_class = implode(" ", array_filter($container_class));
				    if($str_container_class)
				    	$container_properties["class"] = $str_container_class;
				}
				if(is_array($container_properties) && count($container_properties)) {
					$str_container_properties = "";
					foreach($container_properties AS $container_properties_key => $container_properties_value) {
						
						$str_container_properties .= " " . $container_properties_key . '="' . (is_array($container_properties_value) ? implode(" ", array_filter($container_properties_value)) : $container_properties_value) . '"';
					}
					$this->tpl[0]->set_var("container_properties", $str_container_properties);
					
					$this->tpl[0]->set_var("field_container_start", '<div' . $str_container_properties . '>' . $container_inner_start);
					$this->tpl[0]->set_var("field_container_end", $container_inner_end . '</div>');
				}	
							    
				if($primary_field == $key && is_subclass_of($subvalue["data"], "ffField_base")) {
					$this->tpl[0]->set_var("GroupTitle", $this->tpl[0]->ProceedTpl("SectGroupRow"));
				} else {
					if(($wrap_count >= 12 || $count_contents == count($contents)) && $is_wrapped) {
						$this->tpl[0]->parse("SectWrapEnd", false);
						$wrap_count = 0;
						$is_wrapped = false;
					}			    

				    $rc = $this->tpl[0]->parse("SectGroupRow_" . $group_key, true);
				    if (!$rc) $this->tpl[0]->parse("SectGroupRow", true);
				    
				    $this->tpl[0]->set_var("SectWrapStart", "");
				    $this->tpl[0]->set_var("SectWrapEnd", "");
				}
		    }
		    reset($contents);
        }
        
		return $i;
	}

	/**
	 * Visualizza i pulsanti del record
	 */
	protected function tplDisplayControls()
	{
		if ($this->hide_all_controls || !$this->tplSection["buttons"]["display"])
		{
			$this->tpl[0]->set_var("SectControls", "");
			return;
		}

		// EVENT HANDLER
		$res = $this->doEvent("on_before_process_buttons", array(&$this));

        $tmp_action_buttons = $this->action_buttons;
        $rc = usort($tmp_action_buttons, "ffCommon_IndexOrder");
        if (!$rc)
            ffErrorHandler::raise("UNABLE TO ORDER ACTION BUTTONS", E_USER_ERROR, $this, get_defined_vars());

		// PROCESS ALL BUTTONS
		$buffer = "";
		foreach ($tmp_action_buttons as $key => $value)
		{
			if ($this->tpl[0]->isset_var($tmp_action_buttons[$key]["obj"]->id))
				$this->tpl[0]->set_var($tmp_action_buttons[$key]["obj"]->id, $tmp_action_buttons[$key]["obj"]->process());
			else
				$buffer = $tmp_action_buttons[$key]["obj"]->process() . $buffer;
		}
		reset($tmp_action_buttons);

		$this->tpl[0]->set_var("actions_class", cm_getClassByDef($this->framework_css["actions"]));
		$this->tpl[0]->set_var("ActionButtons", $buffer);
		$this->tpl[0]->parse("SectControls", false);
	}

	public function tplDisplayError($sError = null)
	{
		if ($sError !== null)
			$this->strError = $sError;
		
		$this->doEvent("on_error", array($this));
		
		$this->tpl[0]->set_var("SectError", "");
		if (strlen($this->strError))
		{
			if ($this->tpl[0]->isset_var("error"))
			{
				$this->tpl[0]->set_var("error", $this->strError);
			}
			/*elseif (function_exists("mod_notifier_add_message_to_queue") && !MOD_NOTIFIER_DISABLE_AJAX && !$this->disable_mod_notifier_on_error)
			{
				mod_notifier_add_message_to_queue($this->strError, MOD_NOTIFIER_ERROR);
			}*/
			else
			{
				$this->tpl[0]->set_var("error_class", cm_getClassByDef($this->framework_css["error"]));
				$this->tpl[0]->set_var("strError", $this->strError);
				$this->tpl[0]->parse("SectError", false);
			}	
		}
	}

    function initControls()
    {
		if ($this->hide_all_controls)
			return;

		if ($this->cursor_dialog && $this->record_exist)
		{
			// -------------
			//  FIRST

			$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
			$tmp->id 			= "ActionButtonFirst";
			$tmp->label 		= $this->buttons_options["cursor_first"]["label"];
			$tmp->aspect 		= $this->buttons_options["cursor_first"]["aspect"];
			$tmp->action_type 	= "submit";
			$tmp->frmAction		= ($this->buttons_options["cursor_first"]["frmAction"] ? $this->buttons_options["cursor_first"]["frmAction"] : "first");

			if ($this->buttons_options["cursor_first"]["jsaction"])
				$tmp->jsaction = $this->buttons_options["cursor_first"]["jsaction"];
			else
				$tmp->jsaction = "javascript:ff.ffRecord.cursor.first('[[XHR_CTX_ID]]');";

			if (isset($this->buttons_options["cursor_first"]["class"]))
				$tmp->class			= $this->buttons_options["cursor_first"]["class"];
			else
				$tmp->class			= "noactivebuttons";

			if ($this->buttons_options["cursor_first"]["image"])
				$tmp->image = $this->buttons_options["cursor_first"]["image"];

			if ($this->buttons_options["cursor_first"]["newicon"])
				$tmp->newicon = $this->buttons_options["cursor_first"]["newicon"];

			$this->addActionButton(	  $tmp
									, $this->buttons_options["cursor_first"]["index"]);

			// -------------
			//  PREV

			$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
			$tmp->id 			= "ActionButtonPrev";
			$tmp->label 		= $this->buttons_options["cursor_prev"]["label"];
			$tmp->aspect 		= $this->buttons_options["cursor_prev"]["aspect"];
			$tmp->action_type 	= "submit";
			$tmp->frmAction		= ($this->buttons_options["cursor_prev"]["frmAction"] ? $this->buttons_options["cursor_prev"]["frmAction"] : "prev");

			if ($this->buttons_options["cursor_prev"]["jsaction"])
				$tmp->jsaction = $this->buttons_options["cursor_prev"]["jsaction"];
			else
				$tmp->jsaction = "javascript:ff.ffRecord.cursor.prev('[[XHR_CTX_ID]]');";

			if (isset($this->buttons_options["cursor_prev"]["class"]))
				$tmp->class			= $this->buttons_options["cursor_prev"]["class"];
			else
				$tmp->class			= "noactivebuttons";

			if ($this->buttons_options["cursor_prev"]["image"])
				$tmp->image = $this->buttons_options["cursor_prev"]["image"];

			if ($this->buttons_options["cursor_prev"]["newicon"])
				$tmp->newicon = $this->buttons_options["cursor_prev"]["newicon"];

			$this->addActionButton(	  $tmp
									, $this->buttons_options["cursor_prev"]["index"]);
		}			
			
		// PREPARE DEFAULT BUTTONS
		if ($this->buttons_options["cancel"]["display"])
		{
			if ($this->buttons_options["cancel"]["obj"] === null)
			{
				if($this->buttons_options["cancel"]["label"] === null)
					$this->buttons_options["cancel"]["label"] = ffTemplate::_get_word_by_code("ffRecord_close");
				
				$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
				$tmp->id 			= "ActionButtonCancel";
				$tmp->label 		= $this->buttons_options["cancel"]["label"];
				$tmp->aspect 		= $this->buttons_options["cancel"]["aspect"];
				$tmp->action_type 	= "submit";
				$tmp->frmAction		= "close";

                if (isset($this->buttons_options["cancel"]["class"]))
					$tmp->class	= $this->buttons_options["cancel"]["class"];
                $tmp->icon  = $this->buttons_options["cancel"]["icon"]; 


/*				$tmp->class	= cm_getClassByFrameworkCss("cancel", $this->buttons_options["cancel"]["aspect"]);
				if ($this->buttons_options["cancel"]["class"])
					$tmp->class	.= " " . $this->buttons_options["cancel"]["class"];
*/
/*                if (isset($this->buttons_options["cancel"]["class"]))
                    $tmp->class     = $this->buttons_options["cancel"]["class"];
*/
				$this->buttons_options["cancel"]["obj"] =& $tmp;
			}
		}

		parent::initControls();

		/*if (isset($this->action_buttons["ActionButtonCancel"]))
		{
			$tmp = $this->getActionButton("ActionButtonCancel");
			$tmp->class = $tmp->get_class() . " cancel";
		}

		if (isset($this->action_buttons["ActionButtonDelete"]))
		{
			$tmp = $this->getActionButton("ActionButtonDelete");
			$tmp->class = $tmp->get_class() . " delete";
		}

		if (isset($this->action_buttons["ActionButtonUpdate"]))
		{
			$tmp = $this->getActionButton("ActionButtonUpdate");
			$tmp->class = $tmp->get_class() . " update";
		}

		if (isset($this->action_buttons["ActionButtonInsert"]))
		{
			$tmp = $this->getActionButton("ActionButtonInsert");
			$tmp->class = $tmp->get_class() . " insert";
		}*/
		
		if ($this->cursor_dialog && $this->record_exist)
		{
			// -------------
			//  NEXT

			$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
			$tmp->id 			= "ActionButtonNext";
			$tmp->label 		= $this->buttons_options["cursor_next"]["label"];
			$tmp->aspect 		= $this->buttons_options["cursor_next"]["aspect"];
			$tmp->action_type 	= "submit";
			$tmp->frmAction		= ($this->buttons_options["cursor_next"]["frmAction"] ? $this->buttons_options["cursor_next"]["frmAction"] : "next");

			if ($this->buttons_options["cursor_next"]["jsaction"])
				$tmp->jsaction = $this->buttons_options["cursor_next"]["jsaction"];
			else
				$tmp->jsaction = "javascript:ff.ffRecord.cursor.next('[[XHR_CTX_ID]]');";

			if (isset($this->buttons_options["cursor_next"]["class"]))
				$tmp->class			= $this->buttons_options["cursor_next"]["class"];
			else
				$tmp->class			= "noactivebuttons";

			if ($this->buttons_options["cursor_next"]["image"])
				$tmp->image = $this->buttons_options["cursor_next"]["image"];

			if ($this->buttons_options["cursor_next"]["newicon"])
				$tmp->newicon = $this->buttons_options["cursor_next"]["newicon"];

			$this->addActionButton(	  $tmp
									, $this->buttons_options["cursor_next"]["index"]);

			// -------------
			//  LAST

			$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
			$tmp->id 			= "ActionButtonLast";
			$tmp->label 		= $this->buttons_options["cursor_last"]["label"];
			$tmp->aspect 		= $this->buttons_options["cursor_last"]["aspect"];
			$tmp->action_type 	= "submit";
			$tmp->frmAction		= ($this->buttons_options["cursor_last"]["frmAction"] ? $this->buttons_options["cursor_last"]["frmAction"] : "last");

			if ($this->buttons_options["cursor_last"]["jsaction"])
				$tmp->jsaction = $this->buttons_options["cursor_last"]["jsaction"];
			else
				$tmp->jsaction = "javascript:ff.ffRecord.cursor.last('[[XHR_CTX_ID]]');";

			if (isset($this->buttons_options["cursor_last"]["class"]))
				$tmp->class			= $this->buttons_options["cursor_last"]["class"];
			else
				$tmp->class			= "noactivebuttons";

			if ($this->buttons_options["cursor_last"]["image"])
				$tmp->image = $this->buttons_options["cursor_last"]["image"];

			if ($this->buttons_options["cursor_last"]["newicon"])
				$tmp->newicon = $this->buttons_options["cursor_last"]["newicon"];

			$this->addActionButton(	  $tmp
									, $this->buttons_options["cursor_last"]["index"]);
		}		
	}

	/*function redirect($url)
	{
		$this->json_result["url"] = $url;
		$this->json_result["close"] = false;
		die(ffCommon_jsonenc($this->json_result, true));
	}*/
	
	/**
	 * Esegue un reload dello stesso componente in base alle caratteristiche del record e della richiesta, se XHR o meno
	 * NB: se la richiesta non è XHR, viene effettuato un redirect completo
	 * @param String $url l'indirizzo di destinazione
	 * @return String
	 */
	function doReload($refresh_keys = true)
	{
		if ($this->parent[0]->isXHR())
		{
			$this->json_result["url"] = $this->parent[0]->getRequestUri($refresh_keys);
			$this->json_result["close"] = false;
			$this->json_result["component"] = $this->getIDIF();
			if ($this->parent[0]->getXHRSection())
				$this->json_result["section"] = $this->parent[0]->getXHRSection();
			$this->json_result["ctx"] = $this->parent[0]->getXHRCtx();
			cm::jsonParse($this->json_result, true);
			exit;
		}
		else
		{
			return parent::redirect($this->parent[0]->getRequestUri($refresh_keys));
		}
	}
	
	public function structProcess($tpl)
	{
		if ($this->id_if !== null)
		{
            $tpl->set_var("prop_name",    "factory_id");
            $tpl->set_var("prop_value",   '"' . $this->id . '"');
            $tpl->parse("SectFFObjProperty",    true);
		}
	}
	
	function getProperties($property_set = null)
	{
		if ($property_set === null)
			$property_set = $this->properties;

		$buffer = "";
		if (is_array($property_set) && count($property_set))
		{
			foreach ($property_set as $key => $value)
			{
				if ($key == "style")
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . "=\"";
					foreach ($property_set[$key] as $subkey => $subvalue)
					{
						$buffer .= $subkey . ": " . $subvalue . ";";
					}
					reset($property_set[$key]);
					$buffer .= "\"";
				}
				elseif(strlen($value))
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . "=\"" . $value . "\"";
				}
			}
			reset($property_set);
		}
		return $buffer;
	}
	
	function setWidthComponent($resolution_large_to_small) 
	{
		if(is_array($resolution_large_to_small) || is_numeric($resolution_large_to_small)) 
			$this->framework_css["component"]["grid"] = ffCommon_setClassByFrameworkCss($resolution_large_to_small);
		elseif(strlen($resolution_large_to_small))
			$this->framework_css["component"]["grid"] = $resolution_large_to_small;
		else
			$this->framework_css["component"]["grid"] = false;
	}
	
	function addDefaultButton($type, $obj)
	{
		if($obj->label === null)
			$obj->label = ffTemplate::_get_word_by_code("ffRecord_" . $type);

		$obj->label = $this->buttons_options[$type]["icon"] . $obj->label;
		
		$obj->activebuttons	= $this->buttons_options[$type]["activebuttons"];
		$obj->icon  = $this->buttons_options[$type]["icon"];
		
		if(strlen($obj->url) && strpos($obj->url, "javascript:") === 0)
		{
			$obj->url = "javascript:void(0);";
			$obj->properties["onclick"] = substr($obj->url, strlen("javascript:"));
		}
		
		parent::addDefaultButton($type, $obj);
	} 
	
	function setTitle($title, $class = null) 
	{
		$this->framework_css["title"]["class"] = "dialogTitle " . $class;
		$this->title = $title;
	}
}
