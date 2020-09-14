<?php
class ffDetails_html extends ffDetails_base
{
    var $framework_css = array(
			"component" => array(
				"inner_wrap" => false // null OR false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "outer_wrap" => false // false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
				, "grid" => false		//false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
				, "type" => null		//null OR '' OR "inline"
			)
			, "title" => array(
				"class" => null
				, "row" => false
			)
			, "actionsTop" => array(
				"class" => "actions"
				, "col" => array(
					"xs" => 6
					, "sm" => 6
					, "md" => 4
					, "lg" => 3
				)
				, "push" => array(
					"xs" => 6
					, "sm" => 6
					, "md" => 8
					, "lg" => 8
				)
				, "util" => "align-right"
			)
			, "group" => array(
				"class" => null
				, "col" => array(
					"xs" => 12
					, "sm" => 12
					, "md" => 12
					, "lg" => 6
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
			, "actionsBottom" => array(
				"class" => "actions"
				, "col" => array(
					"xs" => 6
					, "sm" => 6
					, "md" => 4
					, "lg" => 3
				)
				, "push" => array(
					"xs" => 6
					, "sm" => 6
					, "md" => 8
					, "lg" => 9
				)
				, "util" => "align-right"
			)
			, "error" => array(
				"class" => "error"
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
                                    "addrow" => array(
                                          "display" => true
                                        , "index"   => 0
                                        , "obj"     => null
                                        , "label"   => null 
                                        , "icon"    => null
                                        , "class"   => null
                                        , "aspect"  => "link"
                                    ),    
									"delete" => array(
										  "display" => true
										, "index" 	=> 0
										, "obj" 	=> null
                                        , "label"   => null 
                                        , "icon"    => null
										, "class" 	=> null
                                        , "aspect"  => "link"
									)
								);
	
	var $id_if = null;
	
	/**
	 * Il template di default
	 * @var String
	 */
    var $template_file         = "ffDetails.html";

	/**
	 * Abilita i tab
	 * @var Boolean
	 */	
	public $tab				= false;// false OR top OR left OR right
	/**
	 * le etichette delle schede
	 * @var Array
	 */
    public $tab_label			= null;
    
    /**
	 * Una collezione dei tabs presenti nella pagina
	 * @var Array
	 */
	var $tabs				    = array();
    var $display_group_title    = true;
	/**
	 * Il testo descrittivo da inserire nel template
	 * @var String
	 */
	public $description = null;

	/**
	 * Se dev'essere utilizzata la widget "disclosure panels"
	 * @var Boolean
	 */
	var $widget_discl_enable = false;
	/**
	 * Se il pannello dev'essere aperto di default
	 * @var Boolean
	 */
	var $widget_def_open = false;
	
	/**
	 * Forza il dettaglio ad agire usando le funzioni Ajax
	 * @var Boolean
	 */
	var $doAjax	= true;

	var $button_delete_key = "deleterow";
    
    var $fixed_pre_row = "";
    var $fixed_post_row = "";    
    
    var $js_deps = array(
		"ff.ffDetails" => null
	);
	
	function __construct(ffPage_base $page, $disk_path, $theme)
	{
		ffDetails_base::__construct($page, $disk_path, $theme);

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
	 * Visualizza il contenuto del dettaglio, riga per riga
	 */
	public function display_rows()
	{
		// First of all, set hidden fields to handle deleted keys
		if (is_array($this->deleted_keys) && count($this->deleted_keys))
		{
			for ($i = 0; $i < count($this->deleted_keys); $i++)
			{
				foreach ($this->deleted_keys[$i] as $key => $value)
				{
					$this->tpl[0]->set_var("hidden_name", "deleted_keys[$i][$key]");
					$this->tpl[0]->set_var("hidden_value", $value->getValue(null, FF_SYSTEM_LOCALE));
					$this->tpl[0]->parse("SectHidden", true);
				}
				reset($this->deleted_keys[$i]);
			}

			for ($i = 0; $i < count($this->deleted_values); $i++)
			{
				foreach ($this->deleted_values[$i] as $key => $value)
				{
					$this->tpl[0]->set_var("hidden_name", "deleted_values[$i][$key]");
					$this->tpl[0]->set_var("hidden_value", $value->getValue(null, FF_SYSTEM_LOCALE));
					$this->tpl[0]->parse("SectHidden", true);
				}
				reset($this->deleted_values[$i]);
			}
		}

		if (count($this->recordset))
		{
			$i = 0;
			
			/**
			* Tab init
			*/
			if($this->tab) {
				$this->button_delete_key = "deletetabrow";
				$this->parent[0]->widgetLoad("tabs", null, $this->main_record[0]->parent[0]);
				$this->tabs = array(
					"tab_mode" => $this->tab
					, "framework_css" => $this->framework_css["widget"]["tab"]
				);
			}

			$this->preProcessDetailButtons();

            $old_group = null;
			foreach ($this->recordset as $rst_key => $rst_val)
			{
				// EVENT HANDLER
				$res = $this->doEvent("on_before_process_row", array(&$this, &$rst_val, $i + 1)); 
				$rc = end($res);
				if (null !== $rc)
				{
					if ($rc === true) {
						$i++;
						continue;
					}
					if ($rc !== false)
					{
						$this->contain_error = true;
						$this->strError = $rc;
					}
				}

				$this->tpl[0]->set_var("row", $i);
				$this->tpl[0]->set_var("rrow", $i + 1);
				
				foreach ($this->fields_relationship as $key => $value)
				{
					$this->tpl[0]->set_var( $value . "_FATHER", $this->main_record[0]->key_fields[$value]->value->getValue());
				}
				reset ($this->fields_relationship);
				
				foreach ($this->key_fields as $key => $value)
				{
					$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $i . "][" . $key . "]");
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectHidden", true);
					$this->key_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];

					if (!isset($this->form_fields[$key]))
					{
						$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "]");
						$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
						$this->tpl[0]->parse("SectHidden", true);
						$this->key_fields[$key]->value = $this->recordset[$rst_key][$key];
					}

					$this->tpl[0]->set_var("Key_" . $key, $this->recordset[$rst_key][$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE));
					$this->tpl[0]->set_var("encoded_Key_" . $key, rawurlencode($this->recordset[$rst_key][$key]->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
					if (strlen($this->recordset[$rst_key][$key]->ori_value))
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
				reset($this->key_fields);

				foreach ($this->hidden_fields as $key => $value)
				{
					//if(!$value->store_in_db && (!$this->tab_label || (is_array($this->tab_label) && array_search($key, $this->tab_label) === false) || $this->tab_label != $key))
					//	continue;
										
					$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $i . "][" . $key . "]");
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectHidden", true);
					$this->hidden_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];

					$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "]");
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectHidden", true);
					$this->hidden_fields[$key]->value = $this->recordset[$rst_key][$key];
				}
				reset($this->hidden_fields);
				
				$group_title = "";
				foreach ($this->form_fields as $key => $value)
				{
					$this->form_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];
					$this->form_fields[$key]->value = $this->recordset[$rst_key][$key];
					
					if(!$this->tab_label && $this->form_fields[$key]->required && !$group_title) {
						$group_title = $this->form_fields[$key]->getValue();
					}
				}
				reset($this->form_fields);
				
				$res = $this->doEvent("on_after_process_row", array(&$this, $rst_key));
				
				if ($this->display_delete && $this->buttons_options["delete"]["display"])
				{
					$this->getDetailButton($this->button_delete_key)->variables[$this->main_record[0]->getIDIF() . "_detailaction"] = $this->getIDIF();
					$this->getDetailButton($this->button_delete_key)->variables[$this->getIDIF() . "_delete_row"] = $i;
				}

				$col = $this->tplDisplayFields($this->form_fields, $rst_key);
				
				$group_button = $this->processDetailButtons($i, false);
				
				if(is_array($this->tab_label) && count($this->tab_label)) {
					$group_key = array();
					foreach($this->tab_label AS $tab_label_value) {
						if($rst_val[$tab_label_value])
							$group_key[] = $rst_val[$tab_label_value]->getValue(
											(isset($this->form_fields[$tab_label_value])
												? $this->form_fields[$tab_label_value]->get_app_type()
												: $this->hidden_fields[$tab_label_value]->get_app_type()
											), 
											(isset($this->form_fields[$tab_label_value])
												? $this->form_fields[$tab_label_value]->get_locale()
												: $this->hidden_fields[$tab_label_value]->get_locale()
											)
										);
					}

					$group_key = implode(" - ", $group_key);
				} elseif(strlen($this->tab_label) && $rst_val[$this->tab_label]) {
					$group_key = $rst_val[$this->tab_label]->getValue(
									(isset($this->form_fields[$this->tab_label])
										? $this->form_fields[$this->tab_label]->get_app_type()
										: $this->hidden_fields[$this->tab_label]->get_app_type()
									), 
									(isset($this->form_fields[$this->tab_label])
										? $this->form_fields[$this->tab_label]->get_locale()
										: $this->hidden_fields[$this->tab_label]->get_locale()
									)
								);	
				}
							
				if(!$group_title)
					$group_title =  ($group_key || !$this->display_new ? $group_key : $i + 1);

				if($this->display_group_title &&  (!$this->tab || $this->tab === "left" || $this->tab === "right") && $group_title) {
					$this->tpl[0]->set_var("group_title", $group_title);
					$this->tpl[0]->set_var("group_buttons", $group_button);
					$this->tpl[0]->parse("SectFormTitle", false);
				}
                
				// EVENT HANDLER
				$res = $this->doEvent("on_before_parse_row", array(&$this, $rst_val));
               
				$rc = end($res);
				if($rc) 
				{
					if($rc === true) {
						$this->contain_error = true;
						$this->strError = $rc;
					}
				} else {
                    /**
                    * Tab manage rows
                    */
                    if($this->tab) {
                        if($old_group !== null && $old_group != $group_key) {
                            $this->tabs["contents"][$old_group]["data"] = $this->tpl[0]->rpparse("SectForm", false);
                                                
                            $this->tpl[0]->set_var("SectFormRow", "");
                            $this->tpl[0]->set_var("SectForm", "");
                        }                    
                    }                 

                    $this->tpl[0]->set_var("fixed_pre_row", (!$this->tab && $this->display_new
                        ? '<div ' . cm_getClassByDef($this->framework_css["group"], array("class" => "ffGrp"), true) . '>'
                        : ''
                    ) . $this->fixed_pre_row);
                    $this->tpl[0]->set_var("fixed_post_row", $this->fixed_post_row . (!$this->tab && $this->display_new
                        ? '</div>'
                        : ''
                    ));

					$this->tpl[0]->parse("SectFormRow", true);
					$this->tpl[0]->set_var("SectFormField", "");

                    /**
                    * Tab manage last row
                    */
                    if($this->tab) {
                        if(!isset($this->tabs["contents"][$group_key])) {
                            $this->tabs["contents"][$group_key]["title"] = ($group_key ? $group_title : ffTemplate::_get_word_by_code("ffDetail_general"));
                            $this->tabs["contents"][$group_key]["buttons"] = $group_button;
                            
                            $old_group = $group_key;    
                        }

                        if(count($this->recordset) == $rst_key + 1) {
                            $this->tabs["contents"][$group_key]["data"] = $this->tpl[0]->rpparse("SectForm", false);
                                                    
                            $this->tpl[0]->set_var("SectFormRow", "");
                            $this->tpl[0]->set_var("SectForm", "");
                        }                    
                    }                 
				}
				
				$i++;
			}
			reset($this->recordset);
		}

		$this->tpl[0]->set_var("rows", $this->rows);
		return;
	}
	
	/**
	 * Visualizza il campo del Dettaglio
	 */
	protected function tplDisplayFields($contents, $rst_key, $primary_field = null) 
	{
		$vars_to_reset = array();
		$i = 0;
		$wrap_count = 0;
		$count_contents = 0;
        $is_wrapped = false;

        if(is_array($contents) && count($contents)) 
        {
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
			    if (is_string($subvalue) || get_class($subvalue) == "ffTemplate" || is_subclass_of($subvalue, "ffDetails_base") || is_subclass_of($subvalue, "ffGrid_base") || is_subclass_of($subvalue, "ffRecord_base"))
			    {
				    $i++;

				    if (is_string($subvalue))
					    $this->tpl[0]->set_var("content", $subvalue);
				    else if (get_class($subvalue) == "ffTemplate")
					    $this->tpl[0]->set_var("content", $subvalue->rpparse("main", false));
				    else
					    $this->tpl[0]->set_var("content", "{{" . $subvalue->id . "}}");
				    
				    if($this->framework_css["record"]["row"] && (!$wrap_count || $wrap_count >= 12)) 
				    {//con le grid e troppo piccola la visualizzazione
				    	$container_class["wrap"] = "form-wrap";
				    	$container_class["row"] = cm_getClassByFrameworkCss("row", "form");
					}
				    $this->tpl[0]->set_var("container_class", implode(" ", array_filter($container_class)));
				    //$this->displayed_fields++;
			    }
			    elseif (is_subclass_of($subvalue, "ffField_base"))
			    {
				    if (
						    (/*!$this->use_fixed_fields && $this->display_values && */$this->form_fields[$key]->skip_if_empty && !strlen($this->form_fields[$key]->value->ori_value))
					    )
					    continue;

				    if (!isset($this->key_fields[$subvalue->id])) // displayed key fields are threated previously
				    {
					    if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
						{
							$multi_field = true;
							foreach ($this->form_fields[$key]->multi_fields as $mul_subkey => $mul_subvalue)
							{
								$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $rst_key . "][" . $key . "][" . $mul_subkey . "]");
								$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key][$mul_subkey]->getValue(null, FF_SYSTEM_LOCALE)));
								$this->tpl[0]->parse("SectHidden", true);

								$this->tpl[0]->set_var("hidden_name", "recordset[" . $rst_key . "][" . $key . "][" . $mul_subkey . "]");
								$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key][$mul_subkey]->getValue(null, FF_SYSTEM_LOCALE)));
								$this->tpl[0]->parse("SectHidden", true);

								$this->form_fields[$key]->multi_values[$mul_subkey] = $this->recordset[$rst_key][$key][$mul_subkey];
								$this->form_fields[$key]->multi_values_ori[$mul_subkey] = $this->recordset_ori[$rst_key][$key][$mul_subkey];
							}
							reset($this->form_fields[$key]->multi_fields);
						}
						else
						{
							$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $rst_key . "][" . $key . "]");
							$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
							$this->tpl[0]->parse("SectHidden", true);
						}
				    }
				    
				    $container_class = array();
				    $i++;

					$tmp_source_SQL = "";
					if(strlen($this->form_fields[$key]->source_SQL)) {
						$tmp_source_SQL = $this->form_fields[$key]->source_SQL;
						$this->form_fields[$key]->source_SQL = ffProcessTags(
							$this->form_fields[$key]->source_SQL
							, $this->key_fields
							, $this->form_fields
							, "normal"
							, $this->main_record[0]->parent[0]->get_params()
							, urlencode($_SERVER['REQUEST_URI'])
							, $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST)
							, $this->hidden_fields
						);
					}					
				    // EVENT HANDLER
					$res = $this->doEvent("on_before_process_field", array(&$this, $this->recordset[$rst_key], &$this->form_fields[$key]));

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
					    $container_class[] = $this->form_fields[$key]->container_class;
					else
						$container_class[] = $this->form_fields[$key]->class;
						
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
						    $this->tpl[0]->set_var("description", $this->form_fields[$key]->description);
						    $this->tpl[0]->parse("SectDescriptionLabel", false);
	                        
	                        $label_set = true;
					    }	
					    else
					    {
						    $this->tpl[0]->set_var("description", "");
						    $this->tpl[0]->set_var("SectDescriptionLabel", "");
					    }

	                    if($this->form_fields[$key]->label_properties)
	                    {
						    $this->tpl[0]->set_var("label_properties", " " . $this->form_fields[$key]->getProperties($this->form_fields[$key]->label_properties));
	                        $label_set = true;
	                    }

					    if ($this->main_record[0]->display_required && $this->form_fields[$key]->required) {
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
						    $this->tpl[0]->set_var("label_for", $this->getIDIF() . "_recordset[" . $rst_key . "][" . $key . "]");
						    
							$arrColumnLabel = $this->form_fields[$key]->framework_css["label"]["col"];
							$arrColumnControl = $this->form_fields[$key]->framework_css["control"]["col"];
							$type_label = "";
							if($primary_field == $key)
								$type_label = "inline";
							
							if(!strlen($control_var))
							{
								if(is_array($arrColumnLabel) && count($arrColumnLabel)
									&& is_array($arrColumnControl) && count($arrColumnControl)
								) {
									$this->tpl[0]->set_var("label_prefix", '<div class="' . cm_getClassByFrameworkCss($arrColumnLabel, "col") . " " . cm_getClassByFrameworkCss("align-right", "util") . '">');
									$this->tpl[0]->set_var("label_postfix", '</div>');
								
									$control_prefix = '<div class="' . cm_getClassByFrameworkCss($arrColumnControl, "col") . '">';
									$control_postfix = '</div>';
									$type_label = "inline";
								}
							}
							
							if($this->framework_css["component"]["type"] === null && $type_label)
								$this->framework_css["component"]["type"] = $type_label;
							
							$label_class = cm_getClassByFrameworkCss("label" . $this->framework_css["component"]["type"], "form");
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

					$container_inner_start = '';
					$container_inner_end = '';
					if(count($container_class)) {
						if($this->form_fields[$key]->framework_css["fixed_pre_content"] || $this->form_fields[$key]->framework_css["fixed_post_content"]) {
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
					
					// CONTROL/VALUE SECTION
				    /**
				    * Control Class
				    */
				    $rc = false;
				    if(is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				    {
						$rc |= $this->tpl[0]->set_var($key . "_value", $this->recordset[$rst_key][$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE));
						$rc |= $this->tpl[0]->set_var($key . "_display_value", $this->form_fields[$key]->getDisplayValue(
								null, null, $this->recordset[$rst_key][$key]
							));
				    }
				    if ($this->tpl[0]->isset_var($key . "_field"))
						$rc |= $this->tpl[0]->set_var($key . "_field", $this->form_fields[$key]->process(
																								  "recordset[" . $rst_key . "][" . $key . "]"
																								, $this->recordset[$rst_key][$key]
																							));


				    if (!$rc)
				    {
					    if (!$this->main_record[0]->display_values || strlen($this->form_fields[$key]->control_type))
					    {
                            if ($this->tpl[0]->isset_var("content" . $control_var)) {
                                $processed_field = $this->form_fields[$key]->process(
                                                                                  "recordset[" . $rst_key . "][" . $key . "]"
                                                                                , $this->recordset[$rst_key][$key]
                                                                            );

                                if($control_prefix && ($this->form_fields[$key]->framework_css["fixed_pre_content"] || $this->form_fields[$key]->framework_css["fixed_post_content"])) {
                                    $control_prefix = $control_prefix . '<div class="' . cm_getClassByFrameworkCss("group", "form") . '">';
                                    $control_postfix = '</div>' . $control_postfix;
                                }                                
                                $this->tpl[0]->set_var("content" . $control_var, $control_prefix . $processed_field . $control_postfix);
                            }
    				    }
					    else
						    $this->tpl[0]->set_var("content" . $control_var, $this->form_fields[$key]->getDisplayValue(
								null, null, $this->recordset[$rst_key][$key]
							));
				    }

					if($label_set)
						$this->tpl[0]->parse("SectGroupLabel", false);
					else
	                	$this->tpl[0]->set_var("SectGroupLabel", "");

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
				    if (strlen($this->recordset[$rst_key][$key]->ori_value))
					{
						$rc |= $this->tpl[0]->parse("SectSet_$key", false);
						$rc |= $this->tpl[0]->set_var("SectNotSet_$key", "");
					}
					else
					{
						$rc |= $this->tpl[0]->set_var("SectSet_$key", "");
						$rc |= $this->tpl[0]->parse("SectNotSet_$key", false);
					}

					if ($this->form_fields[$key]->extended_type == "Selection")
					{
						$rc |= $this->tpl[0]->set_regexp_var("/SectSet_" . $key . "_.+/", "");
						$rc |= $this->tpl[0]->parse("SectSet_" . $key . "_" . $this->recordset[$rst_key][$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE), false);
					}

					$rc |= $this->tpl[0]->parse("Sect_$key", false);

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
                                
				if($primary_field == $key && is_subclass_of($subvalue, "ffField_base")) {
					$this->tpl[0]->set_var("GroupTitle", $this->tpl[0]->ProceedTpl($this->tpl[0]->DBlocks["SectFormField"]));
				} else {
					if(($wrap_count >= 12 || $count_contents == count($contents)) && $is_wrapped) {
						$this->tpl[0]->parse("SectWrapEnd", false);
						$wrap_count = 0;
						$is_wrapped = false;
					}			    

				    $rc = $this->tpl[0]->parse("SectFormField", true);
				    if (!$rc) $this->tpl[0]->parse("SectFormField", true);
				    
				    $this->tpl[0]->set_var("SectWrapStart", "");
				    $this->tpl[0]->set_var("SectWrapEnd", "");
				}

				if(strlen($tmp_source_SQL))
					 $this->form_fields[$key]->source_SQL = $tmp_source_SQL;				
		    }
		    reset($contents);
        }
        
		return $i;
	}
	/**
	 * La funzione che carica il template ed imposta le variabili di default
	 */
	protected function tplLoad()
	{
		if ($this->tpl === null)
		{
			$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
			$this->tpl[0]->load_file($this->template_file, "main");
		}

		$this->tpl[0]->set_var("component_id", $this->getIDIF());
		$this->tpl[0]->set_var("main_record_component", $this->main_record[0]->getPrefix());

		$this->tpl[0]->set_var("site_path", $this->site_path);
		$this->tpl[0]->set_var("page_path", $this->page_path);
		$this->tpl[0]->set_var("theme", $this->getTheme());

		$this->tpl[0]->set_var("XHR_CTX_ID", $_REQUEST["XHR_CTX_ID"]);
		$this->tpl[0]->set_var("requested_url", ffCommon_specialchars($_SERVER["REQUEST_URI"]));

		if ($this->description !== null)
			$this->tpl[0]->set_var("description", $this->description);

		if ($this->doAjax)
		{
			if (isset($_REQUEST["XHR_CTX_ID"])) {
				$this->tpl[0]->set_var("submit_action", "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : '" . $this->main_record[0]->getPrefix() . "detail_addrows', 'component' :'" . $this->getIDIF() . "', 'detailaction' : '" . $this->main_record[0]->getPrefix() . "'})");
			} else {
				$this->main_record[0]->parent[0]->tplAddJs("ff.ajax");		
            
            	$detail_params = ", 'addFields' : [{'name': '" . $this->main_record[0]->getPrefix() . "detailaction', 'value': '" . $this->getIDIF() . "'}]";
				$this->tpl[0]->set_var("submit_action", "ff.ajax.doRequest({'action' : '" . $this->main_record[0]->getPrefix() . "detail_addrows', 'component' : '" . $this->getIDIF() . "'" . $detail_params . "});");
			}
		}
		else
			$this->tpl[0]->set_var("submit_action", "document.getElementById('" . $this->main_record[0]->getPrefix() . "detailaction').value='" . $this->getIDIF() . "'; document.getElementById('frmAction').value = '" . $this->main_record[0]->getPrefix() . "detail_addrows'; jQuery(this).closest('form').submit();");

		if ($this->display_new === true) {
            if($this->buttons_options["addrow"]["label"] === null)
                $this->buttons_options["addrow"]["label"] = ffTemplate::_get_word_by_code("ffDetail_addrow");
            
            if($this->buttons_options["addrow"]["icon"] === null)
                $this->buttons_options["addrow"]["icon"] = cm_getClassByFrameworkCss("addrow", "icon-" . $this->buttons_options["addrow"]["aspect"] . "-tag");

            if($this->buttons_options["addrow"]["class"] === null)
                $this->buttons_options["addrow"]["class"] = cm_getClassByFrameworkCss("addrow", $this->buttons_options["addrow"]["aspect"]);        

				
            if($this->display_rowstoadd) {
				$this->buttons_options["addrow"]["class"] .= " " . cm_getClassByFrameworkCss("control-postfix", "form");
                if($this->display_new_location == "Header" || $this->display_new_location == "Both") {
					$this->framework_css["actionsTop"]["form"] = "group";
					$this->tpl[0]->set_var("rows_to_add_class", cm_getClassByFrameworkCss("control", "form"));
                    $this->tpl[0]->parse("SectNewHeaderQta", false);
				}
                if($this->display_new_location == "Footer" || $this->display_new_location == "Both") {
					$this->framework_css["actionsBottom"]["form"] = "group";
					$this->tpl[0]->set_var("rows_to_add_class", cm_getClassByFrameworkCss("control", "form"));
                    $this->tpl[0]->parse("SectNewFooterQta", false);
				}
            } else {
                $this->tpl[0]->set_var("hidden_name", "rowstoadd");
                $this->tpl[0]->set_var("hidden_value", "1");
                $this->tpl[0]->parse("SectHidden", true);
                $this->tpl[0]->set_var("SectNewHeaderQta", "");
                $this->tpl[0]->set_var("SectNewFooterQta", "");
            }
			
            $this->tpl[0]->set_var("addrow_label", $this->buttons_options["addrow"]["label"]);
            $this->tpl[0]->set_var("addrow_class", $this->buttons_options["addrow"]["class"]);  
            $this->tpl[0]->set_var("addrow_icon", $this->buttons_options["addrow"]["icon"]);  
			
			$this->tpl[0]->set_var("actions_top_class", cm_getClassByDef($this->framework_css["actionsTop"]));
			$this->tpl[0]->set_var("actions_bottom_class", cm_getClassByDef($this->framework_css["actionsBottom"]));
		
            if($this->display_new_location == "Header" || $this->display_new_location == "Both")
                $this->tpl[0]->parse("SectNewHeader", false);
            if($this->display_new_location == "Footer" || $this->display_new_location == "Both")
                $this->tpl[0]->parse("SectNewFooter", false);
        } else {
            $this->tpl[0]->set_var("SectNewHeader", "");
            $this->tpl[0]->set_var("SectNewFooter", "");
        }
		
		if(!count($this->main_record[0]->tabs) && (strlen($this->title) || $this->widget_discl_enable)) {
			$this->tpl[0]->set_var("title", ffCommon_specialchars($this->title));
			$this->tpl[0]->set_var("title_class", cm_getClassByDef($this->framework_css["title"]));
			$this->tpl[0]->parse("SectTitle", false);
		}

	}

	/**
	 * Restituisce l'output eseguendo il processing finale
	 * @param Boolean $output_result Se il contenuto dev'essere restituito o "visualizzato"
	 * @return Boolean Il risultato del processing od un flag per confermare l'avvenuta esecuzione
	 */
	public function tplParse($output_result)
	{
		$res = ffDetails::doEvent("on_tplParse", array($this, $this->tpl[0]));
		$res = $this->doEvent("on_tpl_parse", array(&$this, $this->tpl[0]));

		$this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
		$this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);

		$component_class["default"] = $this->class;
        if($this->framework_css["component"]["grid"]) {
            if(is_array($this->framework_css["component"]["grid"]))
                $component_class["grid"] = cm_getClassByFrameworkCss($this->framework_css["component"]["grid"], "col");
            else {
                $component_class["grid"] = cm_getClassByFrameworkCss("", $this->framework_css["component"]["grid"]);
            }
        }
        $component_class["form"] = cm_getClassByFrameworkCss("component" . $this->framework_css["component"]["type"], "form");

        $this->tpl[0]->set_var("component_class", implode(" ", array_filter($component_class)));

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
        
        $this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
        $this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);
        
        $this->tpl[0]->set_var("fixed_title_content", $this->fixed_title_content);
        $this->tpl[0]->set_var("fixed_heading_content", $this->fixed_heading_content);

		if ($this->tab && count($this->tabs))
		{
			$this->tpl[0]->set_var("form_tabs", $this->main_record[0]->parent[0]->widgets["tabs"]->process($this->getIDIF(), $this->tabs, $this->main_record[0]->parent[0], $this->id));
		}
		else
		{
			$this->tpl[0]->parse("SectForm", false);
		}		

		if ($output_result === true)
		{
			$this->tpl[0]->pparse("main", false);
			return true;
		}
		elseif ($output_result === false)
		{
			return $this->tpl[0]->rpparse("main", false);
		}
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
	/**
	 * Accoda al template i pulsanti basandosi sul numero di colonna
	 * @param Int $col il numero di colonna
	 * @param Boolean $display_label se deve visualizzare l'etichetta
	 * @param Boolean $remaining se deve processare tutti i pulsanti che rimangono (da li in poi)
	 * @param Int $row il numero della riga
	 */
	function processDetailButtons($rst_key, $output_result = true)
	{
		if (is_array($this->detail_buttons) && count($this->detail_buttons))
		{
            $output_buffer = "";
			foreach ($this->detail_buttons as $key => $value)
			{
				if ($key == $this->button_delete_key)
				{
					if (isset($_REQUEST["XHR_CTX_ID"])) {
						$this->detail_buttons[$key]["obj"]->jsaction = "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : '" . $this->main_record[0]->getPrefix() . "detail_delete', 'component' : '" . $this->getIDIF() . "', 'detailaction' : '" . $this->main_record[0]->getPrefix() . "', 'action_param' : " . $rst_key . "});";
					} else {
						$this->main_record[0]->parent[0]->tplAddJs("ff.ajax");

						$detail_params = ", 'addFields' : [{'name': '" . $this->main_record[0]->getPrefix() . "detailaction', 'value': '" . $this->getIDIF() . "'}, {'name': '" . $this->getIDIF() . "_delete_row', 'value': '" . $rst_key . "'}]";
						$this->detail_buttons[$key]["obj"]->jsaction = "ff.ajax.doRequest({'action' : '" . $this->main_record[0]->getPrefix() . "detail_delete', 'component' : '" . $this->getIDIF() . "'" . $detail_params . "});";
					}
				}

				$output_buffer .= $this->detail_buttons[$key]["obj"]->process(
							ffProcessTags(
											$this->detail_buttons[$key]["obj"]->url
											, $this->key_fields
											, $this->form_fields
											, "normal"
											, $this->main_record[0]->parent[0]->get_params()
											, urlencode($_SERVER['REQUEST_URI'])
											, $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST)
										)
							, false
							, $key . "_" . $rst_key
				);
			}
		}
		
		if($output_result) {
			$this->tpl[0]->set_var("group_buttons", $output_buffer);
			return true;
		} else {
			return $output_buffer;
		}
	}
	
	/**
	 * elabora la sezione relativa alla visualizzazione dell'errore nel template
	 * da richiamare ogniqualvolta si aggiorna l'errore
	 */
	function displayError($sError = null)
	{
		if ($sError !== null)
			$this->strError = $sError;

		$this->doEvent("on_error", array($this));

		$this->tpl[0]->set_var("SectError", "");
		if (strlen($this->strError))
		{
			$this->tpl[0]->set_var("error_class", cm_getClassByDef($this->framework_css["error"]));
			$this->tpl[0]->set_var("strError", $this->strError);
			$this->tpl[0]->parse("SectError", false);
		}

		return $sError;
	}
	/**
	 * Elabora l'azione. L'azione viene ereditata dall'oggetto record padre
	 * @return Mixed Il risultato del processing
	 */
	function process_action()
	{
		if ($this->frmAction == "detail_addrows")
			$this->widget_def_open = true;

		return parent::process_action();
	}

	/**
	 * Prepara i pulsanti standard del dettaglio
	 * al momento l'unico pulsante di default ? quello di cancellazione
	 */
	function preProcessDetailButtons()
	{
		if (!$this->doAjax)
			return parent::preProcessDetailButtons();

		// PREPARE DEFAULT BUTTONS
		if ($this->display_delete && $this->buttons_options["delete"]["display"])
		{
			if ($this->buttons_options["delete"]["obj"] !== null)
			{
				$this->addContentButton($this->buttons_options["delete"]["obj"]
										, $this->buttons_options["delete"]["index"]);
			}
			else
			{
				$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
				$tmp->id 			= $this->button_delete_key;
				$tmp->frmAction		= "detail_delete";
                $tmp->label         = $this->buttons_options["delete"]["label"];
                $tmp->icon          = $this->buttons_options["delete"]["icon"];
                $tmp->class         = $this->buttons_options["delete"]["class"];
                $tmp->aspect        = "span"; //$this->buttons_options["delete"]["aspect"];
                $tmp->image 		= false;
				$tmp->action_type 	= "submit";
				$tmp->component_action = $this->main_record[0]->getIDIF();
				$this->addContentButton($tmp
										, $this->buttons_options["delete"]["index"]);
			}
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
		$obj->icon          = $this->buttons_options[$type]["icon"];
		$obj->activebuttons	= $this->buttons_options[$type]["activebuttons"]; 
		
		parent::addDefaultButton($type, $obj);
	}
}