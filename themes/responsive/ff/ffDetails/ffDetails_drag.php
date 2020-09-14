<?php
function ffDetails_drag_update_order($detail, $row, $fields)
{
	$tmp = $detail->recordset;
	$i = -1;
	foreach ($tmp as $key => $value)
	{
		$i++;
		if ($key == $row)
			break;
	}
	
	if (strlen($fields))
		$fields .= ", ";
	$fields .= "`" . $detail->drag_order_field . "` = " . $detail->db[0]->toSql($i);
}

function ffDetails_drag_insert_order($detail, $row, $fields, $values)
{
	if (!strlen($fields))
		return;
		
	$tmp = $detail->recordset;
	$i = -1;
	foreach ($tmp as $key => $value)
	{
		$i++;
		if ($key == $row)
			break;
	}
	
	if (strlen($fields))
		$fields .= ", ";
	$fields .= "`" . $detail->drag_order_field . "`";
	
	if (strlen($values))
		$values .= ", ";
	$values .= $detail->db[0]->toSql($i);
	
	return;
}

function ffDetails_drag_on_loaded_data($detail)
{
	$recordset_avl_ori = $detail->parent[0]->retrieve_param($detail->id, "recordset_avl_ori");
	if (!is_array($recordset_avl_ori))
		$recordset_avl_ori = array();
	
	foreach ($recordset_avl_ori as $key => $value)
	{
		$found = false;
		$slice = array();
		
		$tmp = $detail->key_fields;
		foreach ($tmp as $subkey => $subval)
		{
			if (strlen($value[$subkey]))
			{
				$slice[$subkey] = new ffData($value[$subkey], $subval->base_type, FF_SYSTEM_LOCALE);
			}
		}
		
		if (count($slice))
			$detail->deleted_keys[] = $slice;
	}
}

class ffDetails_drag extends ffDetails_base
{
    var $framework_css = array(
			"component" => array(
				"inner_wrap" => false // null OR false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "outer_wrap" => false // false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
				, "grid" => false		//false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
				, "type" => null		//null OR '' OR "inline"
			)
			, "actions" => array(
				"class" => "actions"
				, "row" => true
				, "util" => "right"
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
				"class" => "error"
				, "callout" => "danger"
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
	
	var $id_if					= null;

	/**
	 * L'eventuale tab in cui è inserito il dettaglio
	 * @var String
	 */
	public $tab			= null;

	/**
	 * Il testo descrittivo da inserire nel template
	 * @var String
	 */
	public $description = null;

	/**
	 * Se dev'essere utilizzata la widget "disclosure panels"
	 * @var Boolean
	 */
	var $widget_discl_enable	= false;
	/**
	 * Se il pannello dev'essere aperto di default
	 * @var Boolean
	 */
	var $widget_def_open		= false;

	//var $doAjax	= true;

	/**
	 * Il template di default
	 * @var String
	 */
	var $template_file			= "ffDetails_drag.html";

	/**
	 * la collezione di record disponibili per le operazioni di drag
	 * @var Array
	 */
	var $drag_records_avaiable	= null;

	/**
	 * il campo utilizzato (su DB) per l'ordinamento
	 * @var String
	 */
	var $drag_order_field		= "";

	var $js_deps = array(
		"ff.ffDetails" => null
	);	
	
	/**
	 * Sovrascrive il costruttore di default aggiungendo gli eventi necessario al sorting
	 * @param ffPage_base $page L'oggetto pagina collegato
	 * @param String $disk_path il percorso assoluto su disco
	 * @param String $theme il tema in utilizzo
	 */
	function __construct(ffPage_base $page, $disk_path, $theme)
	{
		ffDetails_base::__construct($page, $disk_path, $theme);
		
		if (FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID)
			$this->id_if = uniqid();
		
		$this->addEvent("on_before_record_update", "ffDetails_drag_update_order", ffEvent::PRIORITY_HIGH);
		$this->addEvent("on_before_record_insert", "ffDetails_drag_insert_order", ffEvent::PRIORITY_HIGH);
		$this->addEvent("on_loaded_data", "ffDetails_drag_on_loaded_data", ffEvent::PRIORITY_HIGH);

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

		$totfields = count($this->form_fields);
		if ($this->display_delete)
			$totfields++;

		$this->tpl[0]->set_var("maxspan", $totfields);

		//$this->preProcessDetailButtons();
		
		//------------------------------------------
		// Manage Avaiable Records
		//------------------------------------------

		// retrieve records
		
		$recordset_avaiable = array();
		
		if (is_string($this->drag_records_avaiable))
		{
			$this->db[0]->query($this->drag_records_avaiable);
			if ($this->db[0]->nextRecord())
			{
				do
				{
					$each_tmp = $this->form_fields;
					$slice = array();
					foreach ($each_tmp as $key => $value)
					{
						$slice[$key] = $this->db[0]->getField($key, $this->form_fields[$key]->base_type);
					}
					
					if (ffCommon_partial_in_array($slice, $this->recordset) === false)
						$recordset_avaiable[] = $slice;

				} while ($this->db[0]->nextRecord());
			}
		}
		
		foreach ($recordset_avaiable as $key => $value)
		{
			$this->tpl[0]->set_var("SectFormField", "");
			$this->tpl[0]->set_var("SectFieldHidden", "");
			foreach ($value as $subkey => $subvalue)
			{
				//ffErrorHandler::raise("", E_USER_ERROR, $this, get_defined_vars());
				if($this->form_fields[$subkey]->encode_label)
					$this->tpl[0]->set_var("label", ffCommon_specialchars($this->form_fields[$subkey]->label));
				else
					$this->tpl[0]->set_var("label", $this->form_fields[$subkey]->label);

				$this->tpl[0]->set_var("control", $this->form_fields[$subkey]->process("recordset_avl[" . $key . "][" . $subkey . "]", $subvalue));

				$this->tpl[0]->set_var("hidden_name", "recordset_avl_ori[" . $key . "][" . $subkey . "]");
				$this->tpl[0]->set_var("hidden_value", "");
				$this->tpl[0]->parse("SectFieldHidden", true);

				$this->tpl[0]->parse("SectFormField", true);
			}
			$this->tpl[0]->parse("SectAvaiableRow", true);
		}
		reset($recordset_avaiable);
		
		// pre-order detail buttons
	    $tmp_detail_buttons = $this->detail_buttons;
	    if (is_array($tmp_detail_buttons) && count($tmp_detail_buttons))
	    {
	        $rc = usort($tmp_detail_buttons, "ffCommon_IndexOrder");
	        if (!$rc)
	            ffErrorHandler::raise("UNABLE TO ORDER DETAIL BUTTONS", E_USER_ERROR, $this, get_defined_vars());
	    }

		// display data, if present
		if (count($this->recordset))
		{
			$i = count($recordset_avaiable) - 1;
			$display_label = true;
			foreach ($this->recordset as $rst_key => $rst_val)
			{
				$this->tpl[0]->set_var("SectSelFormField", "");
				$this->tpl[0]->set_var("SectSelFieldHidden", "");

				// EVENT HANDLER
				$res = $this->doEvent("on_before_process_row", array(&$this, &$rst_val, $i + 1)); 
				$rc = end($res);
				if (null !== $rc)
				{
					if ($rc === true)
						continue;
					if ($rc !== false)
					{
						$this->contain_error = true;
						$this->strError = $rc;
					}
				}

				$i++;
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
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectSelFieldHidden", true);
					$this->key_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];

					if (!isset($this->form_fields[$key]))
					{
						$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "]");
						$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
						$this->tpl[0]->parse("SectSelFieldHidden", true);
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
					$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $i . "][" . $key . "]");
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectSelFieldHidden", true);
					$this->hidden_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];

					$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "]");
					$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
					$this->tpl[0]->parse("SectSelFieldHidden", true);
					$this->hidden_fields[$key]->value = $this->recordset[$rst_key][$key];
				}
				reset($this->hidden_fields);

				foreach ($this->form_fields as $key => $value)
				{
					$this->form_fields[$key]->value_ori = $this->recordset_ori[$rst_key][$key];
					$this->form_fields[$key]->value = $this->recordset[$rst_key][$key];
				}
				reset($this->form_fields);
				
				$res = $this->doEvent("on_after_process_row", array(&$this, $rst_key));
				
				$col = 0;
				$this->tpl[0]->set_var("SectCol", "");
				
				if ($this->display_delete && $this->buttons_options["delete"]["display"])
				{
					$this->getDetailButton("deleterow")->variables[$this->main_record[0]->getIDIF() . "_detailaction"] = $this->getIDIF();
					$this->getDetailButton("deleterow")->variables[$this->getIDIF() . "_delete_row"] = $i;
				}

				//$this->processDetailButtons($col, $display_label);
				$col++;
				
				foreach ($this->form_fields as $key => $value)
				{
					$multi_field = false;
					//$this->processDetailButtons($col, $display_label);

					// store hidden original value
					if (!isset($this->key_fields[$key]) && $this->form_fields[$key]->data_type == "db") /*&& isset($this->recordset_ori[$rst_key][$key])*/
					{
						if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
						{
							$multi_field = true;
							foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
							{
								$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $i . "][" . $key . "][" . $subkey . "]");
								$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key][$subkey]->getValue(null, FF_SYSTEM_LOCALE)));
								$this->tpl[0]->parse("SectSelFieldHidden", true);

								$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "][" . $subkey . "]");
								$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key][$subkey]->getValue(null, FF_SYSTEM_LOCALE)));
								$this->tpl[0]->parse("SectSelFieldHidden", true);

								$this->form_fields[$key]->multi_values[$subkey] = $this->recordset[$rst_key][$key][$subkey];
								$this->form_fields[$key]->multi_values_ori[$subkey] = $this->recordset_ori[$rst_key][$key][$subkey];
							}
							reset($this->form_fields[$key]->multi_fields);
						}
						else
						{
							$this->tpl[0]->set_var("hidden_name", "recordset_ori[" . $i . "][" . $key . "]");
							$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset_ori[$rst_key][$key]->getValue(null, FF_SYSTEM_LOCALE)));
							$this->tpl[0]->parse("SectSelFieldHidden", true);
						}
					}

					// EVENT HANDLER
					$res = $this->doEvent("on_before_process_field", array(&$this, $rst_val, &$this->form_fields[$key]));

					// if control is a Label, store hidden value
/*					if ($this->form_fields[$key]->control_type == "label")
					{
						$this->tpl[0]->set_var("hidden_name", "recordset[" . $i . "][" . $key . "]");
						$this->tpl[0]->set_var("hidden_value", ffCommon_specialchars($this->recordset[$rst_key][$key]->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale())));
						$this->tpl[0]->parse("SectSelFieldHidden", true);
					}
*/

					$this->form_fields[$key]->row = $i;
					$this->tpl[0]->set_var("width", $this->form_fields[$key]->width);
					if ($this->form_fields[$key]->extended_type == "File")
					{
						$this->form_fields[$key]->file_tmpname = $this->recordset_files[$rst_key][$key]["tmpname"];
					}

           			if (!$this->use_fixed_fields)
						$tmp_loc = "control";
					else
						$tmp_loc = $key;

					$class = $this->form_fields[$key]->container_class;

					if ($this->form_fields[$key]->required) {
						$class = $class . (strlen($class) ? " " : "") . "required";
					}
					$class = $class . (strlen($class) ? " " : "") . $this->form_fields[$key]->get_control_class(null, null, array("framework_css" => false, "control_type" => false));
					if(strlen($class)) {
						$this->tpl[0]->set_var("container_class", " " . $class);
					} else {
						$this->tpl[0]->set_var("container_class", "");
					}
					
					$this->tpl[0]->set_var("container_properties", $this->form_fields[$key]->getProperties($this->form_fields[$key]->container_properties));

					if (!$this->use_fixed_fields) {
                        if($this->form_fields[$key]->display)
                        {
                            $this->tpl[0]->set_var($tmp_loc, $this->form_fields[$key]->process(
                                                                                                      "recordset[" . $i . "][" . $key . "]"
                                                                                                    , $this->recordset[$rst_key][$key]
                                                                                                )
                                                );
                        }
                        else 
                            $this->tpl[0]->set_var("control", "");
                    }
					else
					{
						$this->tpl[0]->set_var($tmp_loc . "_value",
														$this->form_fields[$key]->get_encoded($this->recordset[$rst_key][$key]->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale()))
											);
						$this->tpl[0]->set_var($tmp_loc . "_field", $this->form_fields[$key]->process(
																											  "recordset[" . $i . "][" . $key . "]"
																											, $this->recordset[$rst_key][$key]
																										)
											);
					}

					$this->tpl[0]->parse("SectSelFormField", true);
					
					$col++;
				}
				reset($this->form_fields);
				//$this->processDetailButtons($col, $display_label, true);
				
				// EVENT HANDLER
				$res = $this->doEvent("on_before_parse_row", array(&$this, $rst_val));
				$rc = end($res);
				if (null !== $rc)
				{
					if ($rc === false)
						$this->tpl[0]->parse("SectSelectedRow", true);
					else if ($rc !== true)
					{
						$this->contain_error = true;
						$this->strError = $rc;
					}
				}
				else
					$this->tpl[0]->parse("SectSelectedRow", true);
				
				$display_label = false;
			}
			reset($this->recordset);
		}
		else
		{
			$this->tpl[0]->set_var("SectFormRow", "");
		}

		if ($this->display_delete)
		{
			$this->cols++;
			$this->tpl[0]->parse("SectDeleteLabel", false);
		}
		else
			$this->tpl[0]->set_var("SectDeleteLabel", "");
			
		$this->tpl[0]->set_var("rows", $this->rows);
		$this->tpl[0]->set_var("maxspan", $this->cols);
		return;
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
        
		$this->tpl[0]->set_var("SectHiddden", "");

        $this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
        $this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);
        
        $this->tpl[0]->set_var("fixed_title_content", $this->fixed_title_content);
        $this->tpl[0]->set_var("fixed_heading_content", $this->fixed_heading_content);

		$this->tpl[0]->set_var("requested_url", ffCommon_specialchars($_SERVER["REQUEST_URI"]));

		$this->tpl[0]->set_var("title", ffCommon_specialchars($this->title));

		if ($this->description !== null)
			$this->tpl[0]->set_var("description", $this->description);

		if ($this->tab)
		{
			$this->tpl[0]->set_var("tab_id", $this->main_record[0]->getIDIF());
			$this->tpl[0]->set_var("tab_number", key($this->main_record[0]->tabs[$this->tab]) + 1);
			$this->tpl[0]->parse("SectTabUrl", false);
		}
		else
		{
			$this->tpl[0]->set_var("SectTabUrl", "");
		}

        if ($this->display_new === true) {
            $this->tpl[0]->parse("SectTitle", false);
        } else {
            /*
            $this->tpl[0]->set_var("SectNewHeader", "");
            $this->tpl[0]->set_var("SectNewFooter", "");
            */
            if(strlen($this->title) || $this->widget_discl_enable)
                $this->tpl[0]->parse("SectTitle", false);
            else
                $this->tpl[0]->set_var("SectTitle", "");
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
	
	public function structProcess($tpl)
	{
		if ($this->id_if !== null)
		{
            $tpl->set_var("prop_name",    "factory_id");
            $tpl->set_var("prop_value",   '"' . $this->id . '"');
            $tpl->parse("SectFFObjProperty",    true);
		}
	}
}
