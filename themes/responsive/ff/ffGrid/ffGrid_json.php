<?php
class ffGrid_json extends ffGrid_base 
{   
	var $id_if					= null;
	
	var $cursor_dialog			= false;
    
    var $symbol_valuta          = "&euro; ";
	
    /**
     * Se visualizzare il campo di ricerca semplice
     * @var Boolean
     */
    var $display_search_simple     = true;
    /**
     * Le opzioni del campo di ricerca semplice
     * @var Array
     */
    var $search_simple_field_options    = array(
                                                "id" => "searchall"
                                                , "label"     => ""
                                                , "src_operation" => " [NAME] LIKE([VALUE]) "
                                                , "src_prefix"     => "%"
                                                , "src_postfix"     => "%"
                                                , "obj"     => null
                                                , "index"     => 0
                                            );
    var $reset_page_on_search = false;
	
    var $dialog_delete_url = null;
	
    /**
     * Se visualizzare l'url di editing sulla griglia
     * @var Boolean
     */
    var $display_edit_url        = true;            // display edit record url (on fields)
    var $display_edit_url_alt   = "";
    /**
     * Se visualizzare il pulsante di editing sulla griglia
     * @var Boolean
     */
    var $display_edit_bt        = false;        // display edit record button
    /**
     * Se visualizzare il pulsante di cancellazione sulla griglia
     * @var Boolean
     */
    var $display_delete_bt        = true;            // display delete record button. This cause use of dialog.
    /**
     * Se il pulsante di editing dev'essere visibile sulla riga processata attualmente
     * @var Boolean
     */
    var $visible_edit_bt        = true;            // display edit record button record per record
    /**
     * Se il pulsante di eliminazione dev'essere visibile sulla riga processata attualmente
     * @var Boolean
     */
    var $visible_delete_bt        = true;            // display delete record button record per record
	
    public $no_record_label = null;

    /**
     * Se la grid dev'essere full ajax
     * @var Boolean
     */
    var $full_ajax        = false;
    /**
     * Se dev'essere usata la funzionalità di editing ajax con dialog
     * @var Boolean
     */
    var $ajax_edit        = false;
    /**
     * Se dev'essere usata la funzionalità di cancellazione ajax con dialog
     * @var Boolean
     */
    var $ajax_delete    = false;
    /**
     * Se dev'essere usata la funzionalità di ricerca ajax con dialog
     * @var Boolean
     */
    var $ajax_search    = false;
    
    var $ajax_update_all = false;
	
	/**
     * Il costruttore dell'oggetto
     * da non richiamare direttamente
     * @param ffPage_base $page
     * @param String $disk_path
     * @param String $theme 
     */
    function __construct(ffPage_base $page, $disk_path, $theme)
    {
        parent::__construct($page, $disk_path, $theme);

		if (FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID)
			$this->id_if = uniqid();
		
        if ($this->display_search_simple)
        {
            if ($this->search_simple_field_options["obj"] !== null)
            {
                $this->addSearchField($this->search_simple_field_options["obj"]);
            }
            else
            {
                $tmp = ffField::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
                $tmp->id			= $this->search_simple_field_options["id"];
                $tmp->label			= $this->search_simple_field_options["label"];
                $tmp->src_operation = $this->search_simple_field_options["src_operation"];
                $tmp->src_prefix	= $this->search_simple_field_options["src_prefix"];
                $tmp->src_postfix	= $this->search_simple_field_options["src_postfix"];
                $tmp->data_type		= "";
                $tmp->display		= false;
                $this->addSearchField($tmp);
            }
        }
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
     * Carica il template della griglia
     */
    protected function tplLoad()
    {
		$this->json_result["data"] = null;
    }
    
	function process_interface($output_result = false)
	{
		$this->process_grid();
	}
	
    /**
     * Elabora il template
     * @param Boolean $output_result se dev'essere visualizzata immediatamente l'elaborazione
     * @return Mixed il risultato dell'operazione
     */
    public function tplParse($output_result)
    {
        if ($output_result === true)
        {
            cm::jsonParse($this->json_result);
            return true;
        }
        elseif ($output_result === false)
        {
            return $this->json_result;
        }
    }
    
    function process_headers()
    {
    }
    
    function process_footers()
    {
    }
	
	function process_labels()
	{
	}
	
    function process_search()
    {
    }
	
    /**
     * Elabora i parametri di ricerca
     */
    function process_search_params()
    {
        parent::process_search_params();
        
        if($this->display_search_simple && strlen($this->search_fields[$this->search_simple_field_options["id"]]->getValue())) 
        {
            foreach ($this->grid_fields AS $key => $value) 
            {
                if ($this->grid_fields[$key]->data_type == "" || $this->grid_fields[$key]->skip_search)
                    continue;
    
                $tmp_sql = "";
    
                $tmp_where_value = "";
                if(strlen($this->grid_fields[$key]->src_table))
                    $tblprefix = "`" . $this->grid_fields[$key]->src_table . "`.";
                else
                    $tblprefix = "";
                    
                if($this->grid_fields[$key]->extended_type == "Selection" && $this->grid_fields[$key]->source_SQL) 
                {
                    $this->grid_fields[$key]->pre_process();
                    
                    if(is_array($this->grid_fields[$key]->recordset) && count($this->grid_fields[$key]->recordset))
                    {
                        foreach ($this->grid_fields[$key]->recordset AS $source_SQL_key => $source_SQL_value)
                        {
                            if(strpos($source_SQL_value[1]->getValue(), $this->search_fields[$this->search_simple_field_options["id"]]->getValue()) !== false)                
                            {
                                if(strlen($tmp_where_value))
                                    $tmp_where_value .= ", ";
                                    
                                $tmp_where_value .= $this->db[0]->toSql($source_SQL_value[0]);
                            }
                        }
                    }
                    if(strlen($tmp_where_value))
                        $tmp_sql = $tblprefix . "`" . $this->grid_fields[$key]->get_data_source() . "` IN (" . $tmp_where_value . ")";
                } 
                elseif($this->grid_fields[$key]->extended_type == "Selection" && $this->grid_fields[$key]->multi_pairs) 
                {
                    if(is_array($this->grid_fields[$key]->multi_pairs) && count($this->grid_fields[$key]->multi_pairs))
                    {
                        foreach ($this->grid_fields[$key]->multi_pairs AS $multi_pairs_key => $multi_pairs_value)
                        {
                            if(stripos($multi_pairs_value[1]->getValue(null, FF_LOCALE), $this->search_fields[$this->search_simple_field_options["id"]]->getValue()) !== false)
                            {
                                if(strlen($tmp_where_value))
                                    $tmp_where_value .= ", ";
                                    
                                $tmp_where_value .= $this->db[0]->toSql($multi_pairs_value[0]);
                            }
                        }
                    }
                    if(strlen($tmp_where_value))
                        $tmp_sql = $tblprefix . "`" . $this->grid_fields[$key]->get_data_source() . "` IN (" . $tmp_where_value . ")";
                } 
                else 
                {
                    $tmp_where_value = new ffData($this->search_fields[$this->search_simple_field_options["id"]]->getValue(), $this->grid_fields[$key]->base_type, FF_LOCALE);
                    $tmp_where_value = $tmp_where_value->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                    
                    if($this->search_fields[$this->search_simple_field_options["id"]]->src_prefix || $this->search_fields[$this->search_simple_field_options["id"]]->src_postfix)
                    {
                        $tmp_where_value = $this->db[0]->toSql($this->search_fields[$this->search_simple_field_options["id"]]->value, $this->search_fields[$this->search_simple_field_options["id"]]->base_type, false);
                        $tmp_where_value = "'" . $this->search_fields[$this->search_simple_field_options["id"]]->src_prefix . $tmp_where_value . $this->search_fields[$this->search_simple_field_options["id"]]->src_postfix . "'";
                    }
                    else
                        $tmp_where_value = $this->db[0]->toSql($this->search_fields[$this->search_simple_field_options["id"]]->value);
                
                    if (is_array($this->grid_fields[$key]->src_fields) && count($this->grid_fields[$key]->src_fields))
                        $tmp_sql .= " ( ";
    
                    $tmp_where_part = $this->search_fields[$this->search_simple_field_options["id"]]->src_operation;
                    $tmp_where_part = str_replace("[NAME]", $tblprefix . "`" . $this->grid_fields[$key]->get_data_source() . "`", $tmp_where_part);
                    $tmp_where_part = str_replace("[VALUE]", $tmp_where_value, $tmp_where_part);
                    $tmp_sql .= " " . $tmp_where_part . " ";
    
                    if (is_array($this->grid_fields[$key]->src_fields) && count($this->grid_fields[$key]->src_fields))                        
                    {
                        foreach ($this->grid_fields[$key]->src_fields AS $addfld_key => $addfld_value)
                        {                                                                                                                                                                                                                               
                            $tmp_where_part = $this->search_fields[$this->search_simple_field_options["id"]]->src_operation;
                            $tmp_where_part = str_replace("[NAME]", $this->grid_fields[$addfld_key], $tmp_where_part);
                            $tmp_where_part = str_replace("[VALUE]", $tmp_where_value, $tmp_where_part);
                            $tmp_sql .= " OR " . $tmp_where_part . " ";                                                                                                                                                                                                                      
                        }
                        reset($this->grid_fields[$key]->src_fields);                                                                                                                                                                                                                                                                                                                                                                                        
                        $tmp_sql .= " ) ";
                    }
                }                
    
                //if($this->grid_fields[$key]->src_having)
                //{
                    if (strlen($tmp_sqlHaving) && strlen($tmp_sql))
                        $tmp_sqlHaving .= " OR ";
    
                    $tmp_sqlHaving .=  $tmp_sql;
                /*} else {      
                    if (strlen($tmp_sqlWhere) && strlen($tmp_sql))
                        $tmp_sqlWhere .= " OR ";
                                                                                                                                    
                    $tmp_sqlWhere .=  $tmp_sql;
                }*/                                                                                         
            }
            reset ($this->grid_fields);
            if(strlen($tmp_sqlHaving)) 
                $this->sqlHaving = " ( " . $tmp_sqlHaving . " ) ";
        
            /*if(strlen($tmp_sqlWhere))
                $this->sqlWhere = " ( " . $tmp_sqlWhere . " ) ";*/                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
        }
    }
    
    /**
     * Esegue il processing vero e proprio dei contenuti della grid
     */
    public function process_grid()
    {
        if ($this->display_grid == "never" || ($this->display_grid == "search" && !strlen($this->searched)))
            return;
         
        parent::process_grid();
         
		if($this->db[0]->query_id)
			$this->json_result["rows"] = $this->db[0]->numRows();
		else
			$this->json_result["rows"] = 0;

		if ($this->use_paging)
            $this->json_result["page"] = $this->page;
		
        if ($this->db[0]->query_id && $this->db[0]->nextRecord())
        {
			$rows = $this->db[0]->numRows();
			$rrow = 0;
			
            if (!$this->pagination_save_memory_in_use)
			{
				if ($this->rrow !== false)
				{
					$this->db[0]->seek($this->rrow);
				}
				elseif ($this->use_paging)
				{
					$this->db[0]->jumpToPage($this->page, $this->records_per_page);
				}
			}
			
            if ($this->rrow !== false)
				$rrow = $this->rrow;
            else if ($this->use_paging)
				$rrow = $this->records_per_page * ($this->page - 1);
			
            $i = 0;
            $break_while = false;
            do
            {
				$this->json_result["data"][$i] = array();
				$this->json_result["data"][$i]["rrow"] = $rrow + $i;
				$this->json_result["data"][$i]["url"] = array();
					
				
                $res = $this->doEvent("on_load_row", array(&$this, $this->db[0], $i));
                
                /* Step 1: retrieve values (done in 2 steps due to events needs) */

                $keys = "";
				
				$dlg_edit = false;
				$dlg_delete = false;
				
                if (count($this->key_fields))
                {
                    // find global recordset corrispondency (if one)
                    foreach($this->key_fields as $key => $FormField)
                    {
                        $this->key_fields[$key]->value = $this->db[0]->getField($this->key_fields[$key]->get_data_source(), $this->key_fields[$key]->base_type);
                        $keys .= "keys[" . $this->key_fields[$key]->id . "]=" . $this->key_fields[$key]->value->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE) . "&";
						
						$this->json_result["data"][$i]["keys"][$key] = $this->key_fields[$key]->value->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                    }
                    reset($this->key_fields);
                }

                $keys .= $this->parent[0]->get_keys($this->key_fields);

                foreach ($this->grid_fields as $key => $FormField)
                {
					switch ($this->grid_fields[$key]->data_type)
					{
						case "db":
							$this->grid_fields[$key]->value = $this->db[0]->getField($this->grid_fields[$key]->get_data_source(), $this->grid_fields[$key]->base_type);
							break;

						case "callback":
							$this->grid_fields[$key]->value = call_user_func($this->grid_fields[$key]->get_data_source(), $this->grid_fields, $key);
							break;

						default:
							$this->grid_fields[$key]->value = $this->grid_fields[$key]->getDefault(array(&$this));
					}
					
					$this->json_result["data"][$i]["grid"][$key]["base"] = $this->grid_fields[$key]->value->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                }
                reset($this->grid_fields);

                /* Step 2: display values */

                // EVENT HANDLER
                $res = $this->doEvent("on_before_parse_row", array(&$this, $i));
                $rc = end($res);

                if ($rc === null)
                {
                    $col = 0;
                    
                    if (strlen($this->bt_edit_url))
                    {
                        $modify_url = $this->bt_edit_url;
                    }
                    else
                    {
                        $modify_url = $this->record_url . "?" . $keys .
                                    $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
                                    $this->addit_record_param .
									($this->cursor_dialog ? 
										"cursor[id]=" . rawurlencode($this->getIDIF()) . "&" .
										"cursor[rrow]=" . rawurlencode($rrow + $i) . "&" .
										"cursor[rows]=" . rawurlencode($rows) . "&"
										: "") .
                                    "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
                    }
                    $modify_url = ffProcessTags($modify_url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);

                    $cancelurl = $this->parent[0]->getRequestUri();
                    if (strlen($this->dialog_delete_url))
                    {
                        $delete_url = ffProcessTags($this->dialog_delete_url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
                    }
                    else if (strlen($this->record_delete_url))
                    {
                        $confirmurl = $this->record_delete_url . "?" . $keys .
                                        $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
                                        $this->addit_record_param .
                                        $this->record_id . "_frmAction=confirmdelete&" .
                                        "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
                        $confirmurl = ffProcessTags($confirmurl, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);

                        $delete_url = $this->dialog(
                                          true
                                        , "yesno"
                                        , $this->parent[0]->title
                                        , $this->label_delete
                                        , ($this->full_ajax || $this->ajax_delete ? "[CLOSEDIALOG]" : $cancelurl)
                                        , $confirmurl
                                        );
                    }
                    else
                    {
                        if (strlen($this->bt_delete_url))
                        {
                            $confirmurl = $this->bt_delete_url;
                        }
                        else
                        {
                            $confirmurl = ($this->record_url_delete ? $this->record_url_delete : $this->record_url) . "?" . $keys .
                                        $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
                                        $this->addit_record_param .
                                        $this->record_id . "_frmAction=confirmdelete&" .
                                        "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
                        }
                        $confirmurl = ffProcessTags($confirmurl, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);

                        $delete_url = $this->dialog(
                                                      true
                                                    , "yesno"
                                                    , $this->parent[0]->title
                                                    , $this->label_delete
                                                    , ($this->full_ajax || $this->ajax_delete ? "[CLOSEDIALOG]" : $cancelurl)
                                                    , $confirmurl
                                                    );
                    }
					
                    if ($this->display_edit_url || ($this->visible_edit_bt && $this->display_edit_bt))
                    {
						$this->json_result["data"][$i]["url"]["modify"] = $modify_url;
					}

                    if ($this->visible_delete_bt && $this->display_delete_bt)
                    {
						$this->json_result["data"][$i]["url"]["delete"] = $delete_url;
					}

                    foreach ($this->grid_fields as $key => $FormField)
                    {
                        $buffer = "";
                        if (
                                $this->grid_fields[$key]->display === false
                            )
                            continue;

                        if (!is_subclass_of($this->grid_fields[$key], "ffField_base"))
                            ffErrorHandler::raise("Wrong Grid Field: must be a ffField", E_USER_ERROR, $this, get_defined_vars());

                        //---------------------------------------------------------------------
                        //---------------------------------------------------------------------

                        if(strlen($symbol_valuta) && $this->grid_fields[$key]->app_type == "Currency")
                        {
							$buffer .= $symbol_valuta;
                        }

                        if ($this->grid_fields[$key]->control_type === "")
                        {
                            $this->grid_fields[$key]->pre_process(true);
                            $buffer .= $this->grid_fields[$key]->fixed_pre_content . $this->grid_fields[$key]->getDisplayValue() . $this->grid_fields[$key]->fixed_post_content;
                        }
                        else
                        {
                            $buffer .= $this->grid_fields[$key]->process(
                                        "recordset_values[$recordset_key][" . $this->grid_fields[$key]->id . "]");
                        }

						$this->json_result["data"][$i]["grid"][$key]["display"] = $buffer;
						$this->json_result["data"][$i]["grid"][$key]["app"] = $this->grid_fields[$key]->value->getValue();
                        
						if ($this->grid_fields[$key]->url === null)
						{
						}
						else
						{
							$this->json_result["data"][$i]["grid"][$key]["url"] = ffProcessTags($this->grid_fields[$key]->url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
						}

						if ($this->grid_fields[$key]->url === "" || $this->grid_fields[$key]->control_type != "" || !$this->display_edit_url)
						{
						}
						else
						{
							if($this->display_edit_url_alt) {
								$this->json_result["data"][$i]["grid"][$key]["url"] = $this->display_edit_url_alt . "?" . $keys .
											$this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
											$this->addit_record_param .
											"ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
							}
						}
                        
						if (!($this->grid_fields[$key]->url === "" || $this->grid_fields[$key]->control_type != "" || !$this->display_edit_url))
						{
							$tmp_url = null;
							if ($this->grid_fields[$key]->url === null)
							{
								if($this->display_edit_url_alt) {
									$tmp_url = $this->display_edit_url_alt . "?" . $keys .
												$this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
												$this->addit_record_param .
												"ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
								}
							}
							else
							{
								$tmp_url = ffProcessTags($this->grid_fields[$key]->url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
							}

							$res = ffGrid::doEvent("onProcessBtEdit", array(&$this, &$tmp_url, &$buffer));
							$rc = end($res);
							if (!$rc)
							{
								$res = $this->doEvent("onProcessBtEdit", array(&$this, &$tmp_url, &$buffer));
								$rc = end($res);
							}

							if (!$rc && $tmp_url)
							{
								$this->json_result["data"][$i]["url"] = $tmp_url;
							}
						}
                    }
                    reset($this->grid_fields);

                    $i++;
                    if (($this->use_paging && $i >= $this->records_per_page) || $this->rrow !== false)
                        $break_while = true;
                }

                // EVENT HANDLER
                $res = $this->doEvent("on_after_parse_row", array(&$this, $i));
                $rc = end($res);
                if($rc === true)
                	break;

            } while ($this->db[0]->nextRecord() && !$break_while);
        }
    }

    public function structProcess($tpl)
    {
    }
}
