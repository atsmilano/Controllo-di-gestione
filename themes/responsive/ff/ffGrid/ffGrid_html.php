<?php
class ffGrid_html extends ffGrid_base 
{   
	var $framework_css = array(
			"component" => array(
                "inner_wrap" => false // null OR false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "outer_wrap" => false //false OR true OR array(xs, sm, md, lg) OR 'row-default' OR 'row' OR 'row-fluid'
                , "grid" => false        //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
                , "type" => "inline"		//null OR '' OR "inline"
			)
			, "title" => array(
					"class" => null
					, "col" => array(
								"xs" => 12
								, "sm" => 12
								, "md" => 12
								, "lg" => 12
							)
					)
			, "actionsTop" => array(
					"class" => "actions"
					, "col" => array(
							"xs" => 12
							, "sm" => 12
							, "md" => 7
							, "lg" => 7
					)
					, "util" => array(
						"right"
						, "align-right"
					)
			)
			, "description" => array(
					"class" => null
					, "callout" => "info"
					, "col" => array(
							"xs" => 12
							, "sm" => 12
							, "md" => 12
							, "lg" => 12
					)
			)
			, "search" => array(
				"class" => null
				, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 5
						, "lg" => 5						
				)
			)
			, "navigatorTop" => array(
				"class" => null
				, "col" => null
                , "util" => array(
                    "clear"
                )
			)
			, "table" => array(
				"class" => null
				, "col" => false
				, "table" => array("container", "oddeven", "responsive")
			)
			, "filters" => array(
				"class" => null
				, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 12
						, "lg" => 12
				)
			)
			, "navigatorBottom" => array(
				"class" => null
				, "col" => null
				, "util" => array(
					"clear"
				)
			)
			, "alphanum" => array(
				"class" => null
				, "col" => array(
					"xs" => 8
					, "sm" => 8
					, "md" => 6
					, "lg" => 5
				)
			)			
			, "actionsBottom" => array(
				"class" => null
				, "col" => array(
					"xs" => 12
					, "sm" => 12
					, "md" => 12
					, "lg" => 12
				)
				, "util" => array(
					"align-right"
				)
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
					"class" => null
					, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 6
						, "lg" => 6
					)
				)
				, "control" => array(
					"class" => null
					, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 6
						, "lg" => 6
					)
				)
			)
			, "error" => array(
				"class" => "error"
				, "callout" => "danger"
			)
			, "norecord" => array(
				"class" => "norecord"
				, "callout" => "info"
				, "col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 12
						, "lg" => 12
					)
			)
	);

	var $buttons_options		= array(
									"search" => array(
											  "display" => true
											, "index" 	=> 0
											, "obj" 	=> null
											, "label" 	=> null
											, "class"	=> null
											, "icon"	=> null
											, "aspect"	=> "link"
									)
									, "delete" => array(
											"class"		=> null
											, "label"	=> null
                                            , "aspect"    => "link" 
                                            , "disposition" => array("row" => 1, "col" => "default")
									)
									, "edit" => array(
											"class"		=> null
											, "label"	=> null
                                            , "icon"    => null
                                            , "aspect"  => "link"
                                            , "disposition" => array("row" => 1, "col" => "default")
									)
									, "addnew" => array(
											"class"		=> null
											, "label"	=> null 
											, "icon"	=> null
											, "aspect"	=> "link"
									)
									, "export" => array(
											"class"		=> null
											, "display" => false
											, "index" 	=> 0
											, "obj" 	=> null
											, "label" 	=> null
											, "icon"	=> null
											, "aspect"	=> "link"
									)
								);
	
	var $id_if					= null;
	
	var $grid_disposition_elem = array();
	var $component_properties = array();
    var $dialog_delete_url = null;
    var $dialog_action_button = false;

    var $cursor_dialog = false;

    /**
     * La label del tasto "turnon"
     * usato in presenza di checkbox
     * @var String
     */
    var $turn_on_label    = "ON";
    /**
     * La label del tasto "turnoff"
     * usato in presenza di checkbox
     * @var String
     */
    var $turn_off_label = "OFF";
    /**
     * Se la grid dev'essere full ajax
     * @var Boolean
     */
    var $full_ajax        = false;
    /**
     * Se dev'essere usata la funzionalità di aggiunta ajax con dialog
     * @var Boolean
     */
    var $ajax_addnew    = false;
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
    
    var $dialog_options = array(
        "addnew" => array(
        	"type" 			=> null
            , "width" 		=> ""
            , "height" 		=> ""
            , "title" 		=> ""
            , "class" 		=> null
        )
        , "edit" => array(
            "type" 			=> null
            , "width" 		=> ""
            , "height" 		=> ""
            , "title" 		=> ""
            , "class" 		=> null
        )    
        , "delete" => array(
            "type" 			=> null
            , "width" 		=> ""
            , "height" 		=> ""
            , "title" 		=> ""
            , "class" 		=> null
        )
    );
    
    /**
     * una classe da associare alla riga
     * @var String
     */
    var $row_class        = "";
    var $row_properties   = array();
    /**
     * Un elenco di classi da associare alle righe della grid, ciclate in sequenza
     * @var Array
     */
    var $switch_row_class =  array(
                                "display" => true
                                , "first" => "odd"
                                , "second" => "even"
                            );
	var $cel_class = "cel-[COUNT]";
    
    /**
     * La classe da assegnare alle colonne intermedie
     * @var String
     */
    var $column_class                = "";
    /**
     * La classe da assegnare alla prima colonna
     * @var String
     */
    var $column_class_first         = "";
    /**
     * La classe da assegnare all'ultima colonna
     * @var String
     */
    var $column_class_last            = "";
    /**
     * La classe da assegnare alle label di ordinamento
     * @var String
     */
    var $label_class                = "";
    /**
     * La classe da assegnare alle label di ordinamento quando selezionate
     * @var String
     */
    var $label_selected_class        = "";

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
     * Target dell'editing sulla griglia
     * @var Boolean
     */
    var $record_target = false;
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
    /**
     * Il simbolo usato per visualizzare i campi valuta
     * @var String
     */
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
                                                , "src_having" => true
                                            );
    var $reset_page_on_search = false;
    /**
     * Le dipendenze in widgets del componente
     * @var Array
     */
    var $widget_deps = array(
    		array("name" => "fullclick") 
        );
    /**
     * Se la ricerca avanzata dev'essere aperta di default
     * @var Boolean
     */
    var $open_adv_search = false;
    /**
     * Una descrizione aggiuntiva da assegnare al componente
     * @var String
     */
    public $description = null;
    
    public $no_record_label = null;
    /**
     * Se la widget "disclosure panels" dev'essere abilitata di default
     * @var Boolean
     */
    var $widget_discl_enable = false;
    /**
     * Se la widget "disclosure panels" dev'essere aperta di default
     * @var Boolean
     */
    var $widget_def_open = true;
    
    var $widget_activebt_enable = false;
    
    var $navigator_doAjax = true;
    var $navigator_display_selector = true;
    
	var $grid_disposition	= null;				// array of ffFields and fButtons to display in grid  by Row and Col
	var $grid_disposition_options = array(		// Wrap elem with same type in the same Col
		"button" => array(
			"wrap_col" => false
		)
		, "field" => array(
			"wrap_col" => false
		)
	);

    private $parsed_hidden_fields     = 0;
    /**
     * Variabile ad uso interno
     */
    private $parsed_fields         = 0;
    /**
     * Variabile ad uso interno
     */
    private $parsed_filters        = 0;

	var $js_deps = array(
		"ff.ffGrid" => null
	);
	
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
    
	/**
	 * Aggiunge un campo che verrà visualizzato
	 * @param ffField Il campo da aggiungere
	 * @param boolean
	 */
	function addContent($field, $toOrder = true, $col = null, $row = null, $params = null)
	{
		$field->framework_css = array_replace_recursive($this->framework_css["field" . ($this->framework_css["component"]["type"] ? "-" . $this->framework_css["component"]["type"] : "")], $field->framework_css);

		parent::addContent($field, $toOrder);
		
		$this->addGridDisposition($field->id, "field", $col, $row, $params);		
	} 	 

	/**
	 * Aggiunge un pulsante a ffGrid; il pulsante viene aggiunto ad ogni riga di ffGrid
	 * @param ffButton $button
	 */
	function addGridButton($button, $col = null, $row = null, $params = null)
	{
		parent::addGridButton($button);

		$this->addGridDisposition($button->id, "button", $col, $row, $params);
	}

    function addGridDisposition($ID_component, $component_type, $col = null, $row = null, $params = null) {
    	$default_type = array(
    		"button" => -1
    	);
    	
		if(!count($this->grid_disposition))
			$this->grid_disposition[] = array();

		if($row === null)
			$row = 0;
		elseif(is_numeric($row))
			$row = $row - 1;
		elseif($row == "last")
			$row = count($this->grid_disposition) - 1;
		
		if($row < 0)
			$row = 0;
				
		if($row - count($this->grid_disposition) > 1)
			$row = count($this->grid_disposition) + 1;
		
		if($col == "last" && !isset($this->grid_disposition_elem["data"][$row][count($this->grid_disposition[$row]) - 1][$component_type]))
			$col = null;
		if($col == "default")
			$col = (is_array($this->grid_disposition_elem["data"][$row][count($this->grid_disposition[$row]) - 1][$component_type]) && count($this->grid_disposition_elem["data"][$row][count($this->grid_disposition[$row]) - 1][$component_type]) == 1
				? "last"
				: null
			);

		if($col === null) {
			$col = count($this->grid_disposition[$row]);

			if($this->grid_disposition_options[$component_type]["wrap_col"]  
				&& isset($default_type[$component_type]) 
				&& $col > 0 
				&& count($this->grid_disposition_elem["data"][$row][$col + $default_type[$component_type]]) == 1 
				&& isset($this->grid_disposition_elem["data"][$row][$col + $default_type[$component_type]][$component_type])
			) {
				$col = $col + $default_type[$component_type];
			}
		} elseif(is_numeric($col)) {
			$col = $col - 1;
		} elseif($col == "last") {
			$col = count($this->grid_disposition[$row]) - 1;
		}
		
		if($col < 0)
			$col = count($this->grid_disposition[$row]);
			
		if($col - count($this->grid_disposition[$row]) > 1)
			$col = count($this->grid_disposition[$row]) + 1;

		$params["type"] = $component_type;

		$this->grid_disposition[$row][$col][$ID_component] = $params;    
		
		$this->grid_disposition_elem["data"][$row][$col][$component_type]++;
		$this->grid_disposition_elem[$component_type][$row]++;
		$this->grid_disposition_elem["count"][$row]++;
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
    
        $this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
        $this->tpl[0]->load_file($this->template_file, "main");
    
        $this->tpl[0]->set_var("site_path", $this->site_path);
        $this->tpl[0]->set_var("page_path", $this->page_path);
        $this->tpl[0]->set_var("theme", $this->getTheme());
        $this->tpl[0]->set_var("this_url", rawurlencode($this->parent[0]->getRequestUri()));
    
        $this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
        $this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);
        
        $this->tpl[0]->set_var("fixed_title_content", $this->fixed_title_content);
        $this->tpl[0]->set_var("fixed_heading_content", $this->fixed_heading_content);
        $this->tpl[0]->set_var("fixed_body_content", $this->fixed_body_content);
        
        $this->tpl[0]->set_var("SectHiddenField", "");
    
        $this->tpl[0]->set_var("component", $this->getPrefix());
        $this->tpl[0]->set_var("component_id", $this->getIDIF());
        
    	//$action_class["default"] = "actions";
        if(isset($_REQUEST["XHR_CTX_ID"])) 
        {
        	$title_class["default"] = "dialogTitle";
        	$action_class["dialog"] = "dialogActionsPanel";
            $action_class["align"] = cm_getClassByFrameworkCss("align-right", "util");
        	$action_top_class = $action_class;
            $action_top_class["top"] = "top";
    		if($this->dialog_action_button)
    			$action_top_class["force"] = "force";
            
			$this->tpl[0]->set_var("actions_top_class", implode(" " , array_filter($action_top_class)));
			$this->tpl[0]->set_var("actions_bottom_class", implode(" " , array_filter($action_class)));
		} 
		else 
		{
        	$title_class["default"] = "ffTitle";
            $action_class           = "";
        	$action_top_class       = "";

			$this->tpl[0]->set_var("actions_top_class", cm_getClassByDef($this->framework_css["actionsTop"], $action_top_class));
			$this->tpl[0]->set_var("actions_bottom_class", cm_getClassByDef($this->framework_css["actionsBottom"], $action_class));
		}
        if(strlen($this->title) || $this->widget_discl_enable) {
        	$this->tpl[0]->set_var("title_class", cm_getClassByDef($this->framework_css["title"], $title_class));
            $this->tpl[0]->set_var("title", $this->title);
            $this->tpl[0]->parse("SectTitle", false);
        } else {
            $this->tpl[0]->set_var("SectTitle", "");
        }

        if ($this->description !== null) {
			$this->tpl[0]->set_var("description_class", cm_getClassByDef($this->framework_css["description"]));        
            $this->tpl[0]->set_var("description", $this->description);
            $this->tpl[0]->parse("SectDescription", false);
        } else {
            $this->tpl[0]->set_var("SectDescription", "");
        }
    
        if ($this->parent !== NULL)
            $this->tpl[0]->set_var("ret_url", $this->parent[0]->ret_url);
    
    
        $this->setProperties();
    
        if (is_array($this->fixed_vars) && count($this->fixed_vars))
        {
            foreach ($this->fixed_vars as $key => $value)
            {
                $this->tpl[0]->set_var($key, $value);
            }
            reset($this->fixed_vars);
        }
    
        $res = $this->doEvent("on_load_template", array(&$this, $this->tpl[0]));
    }
    
    /**
     * Elabora il template
     * @param Boolean $output_result se dev'essere visualizzata immediatamente l'elaborazione
     * @return Mixed il risultato dell'operazione
     */
    public function tplParse($output_result)
    {
        $res = ffGrid::doEvent("on_tplParse", array($this, $this->tpl[0], $output_result));
        $res = $this->doEvent("on_tplParse", array($this, $this->tpl[0], $output_result));
    
        if ($this->parent[0]->getXHRComponent() == $this->id)
        {
            if($this->db[0]->query_id)
                $this->json_result["rows"] = $this->db[0]->numRows();
            else
                $this->json_result["rows"] = 0;
            
            $this->json_result["page"] = $this->page;
    
            if (isset($_REQUEST["XHR_SECTION"]))
            {
                if ($this->use_paging || $this->use_search || $this->use_alpha || $this->use_order || $this->parsed_hidden_fields || $this->use_fields_params)
                    $this->json_result["hidden"] = $this->tpl[0]->rpparse("SectHidden", false);
                else
                    $this->json_result["hidden"] = "";
                return $this->tpl[0]->rpparse("Sect" . $_REQUEST["XHR_SECTION"], false);
            }
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
    
        if (!$this->display_navigator || !$this->use_paging || ($this->navigator[0]->num_rows <= $this->navigator[0]->records_per_page && !($this->ajax_search || $this->full_ajax)))
            return $this->tpl[0]->rpparse("SectHeaders", false);
        else
            return $this->tpl[0]->rpparse("SectHeaders", false) . $this->navigator[0]->process_headers();
    }
    
    function process_footers()
    {
        if (!isset($this->tpl[0]))
            return;
            
        if (!$this->display_navigator || !$this->use_paging || ($this->navigator[0]->num_rows <= $this->navigator[0]->records_per_page && !($this->ajax_search || $this->full_ajax)))
            return $this->tpl[0]->rpparse("SectFooters", false);
        else
            return $this->tpl[0]->rpparse("SectFooters", false) . $this->navigator[0]->process_footers();
    }
    
    /**
	 * error template processing function
	 * called by process_interface()
	 */
	function displayError($sError = null)
	{
		if ($sError !== null)
			$this->strError = $sError;

		if (strlen($this->strError))
		{
			$this->tpl[0]->set_var("error_class", cm_getClassByDef($this->framework_css["error"]));		
			$this->tpl[0]->set_var("strError", $this->strError);
			$this->tpl[0]->parse("SectError", false);
		}
		else
			$this->tpl[0]->set_var("SectError", "");
	} 
    /**
     * Elabora i parametri di ricerca
     */
    function process_search_params()
    {
        parent::process_search_params();
        
        if($this->display_search_simple && strlen($this->search_fields[$this->search_simple_field_options["id"]]->getValue()) && $this->search_simple_field_options["src_having"]) 
        {
            $tmp_sqlHaving = "";
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
					if ($this->grid_fields[$key]->crypt)
					{
						$tmp_where_value = $this->search_fields[$this->search_simple_field_options["id"]]->value->getValue();
						
						if ($this->grid_fields[$key]->crypt_modsec)
						{
							$tmp_where_value = mod_sec_crypt_string($tmp_where_value);
						}
						
						if (is_array($this->grid_fields[$key]->src_fields) && count($this->grid_fields[$key]->src_fields))
							$tmp_sql .= " ( ";

						$tmp_sql = " " . $tblprefix . "`" . $this->grid_fields[$key]->get_data_source() . "` = UNHEX(" . $this->db[0]->toSql(bin2hex($tmp_where_value)) . ") ";
						
						if (is_array($this->grid_fields[$key]->src_fields) && count($this->grid_fields[$key]->src_fields))                        
						{
							foreach ($this->grid_fields[$key]->src_fields AS $addfld_key => $addfld_value)
							{                                                                                                                                                                                                                               
								$tmp_where_part = " " . $tblprefix . "`" . $this->grid_fields[$addfld_key]->get_data_source() . "` = UNHEX(" . $this->db[0]->toSql(bin2hex($tmp_where_value)) . ") ";
								$tmp_sql .= " OR " . $tmp_where_part . " ";                                                                                                                                                                                                                      
							}
							reset($this->grid_fields[$key]->src_fields);                                                                                                                                                                                                                                                                                                                                                                                        
							$tmp_sql .= " ) ";
						}
					}
					else
					{
						if($this->search_fields[$this->search_simple_field_options["id"]]->src_prefix || $this->search_fields[$this->search_simple_field_options["id"]]->src_postfix)
						{
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$this->search_simple_field_options["id"]]->value, $this->search_fields[$this->search_simple_field_options["id"]]->base_type, false);
							if ($this->grid_fields[$key]->crypt)
							{
								if ($this->grid_fields[$key]->crypt_modsec)
								{
									$tmp_where_value = mod_sec_crypt_string($tmp_where_value);
								}
							}
							$tmp_where_value = "'" . $this->search_fields[$this->search_simple_field_options["id"]]->src_prefix . $tmp_where_value . $this->search_fields[$this->search_simple_field_options["id"]]->src_postfix . "'";
						}
						else
						{
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$this->search_simple_field_options["id"]]->value);
						}
					
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
	 * alpha template processing function
	 * called by process_interface()
	 */
	function process_alpha()
	{
		if (!$this->use_alpha)
		{
			$this->tpl[0]->set_var("SectAlpha", "");
			return;
		}

		$this->tpl[0]->set_var("max_colspan", count($this->grid_disposition[0]));
		$this->tpl[0]->set_var("actual_alpha", ffCommon_specialchars($this->alpha));
		$this->tpl[0]->set_var("field", ffCommon_specialchars($this->search_fields[$this->alpha_field]->label));
		$this->tpl[0]->set_var("alpha_selected_" . $this->alpha, cm_getClassByFrameworkCss("current", "util"));
		
		if (isset($_REQUEST["XHR_DIALOG_ID"]))
			$this->tpl[0]->set_var("alpha_action", "jQuery('#" . $this->prefix . "alpha').val(jQuery(this).attr('rel')); ff.ffPage.dialog.doRequest('" . $_REQUEST["XHR_DIALOG_ID"] . "', {'action' : 'alpha', 'component' :'" . $this->id . "'})");
		else
			$this->tpl[0]->set_var("alpha_action", "jQuery('#" . $this->prefix . "alpha').val(jQuery(this).attr('rel'));  ff.ajax.doRequest({'action' : 'alpha', 'component' :'" . $this->id . "'})");
		
		$this->tpl[0]->parse("SectAlpha", false);
	}

	/**
	 * search template processing function
	 * called by process_interface()
	 */
    function process_search() 
    {
        if (!$this->use_search || ($this->parent[0]->getXHRComponent() == $this->id && $this->parent[0]->getXHRSection() == "GridData"))
            return;

        if($this->display_search)
        {
            if ($this->display_search_simple)
            {
	            $wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
                foreach ($this->search_fields AS $key => $value)
                {
                    if($this->open_adv_search === false && $this->search_fields[$key]->display && !$this->search_fields[$key]->multi_disp_as_filter)
                    {
                        //$this->tpl[0]->set_var("search_more_label", ffTemplate::_get_word_by_code("ffGrid_search_more"));
                        if($wrap_addon) {
	                        $this->tpl[0]->set_var("search_more_container_class_start", '<div class="' . cm_getClassByFrameworkCss(array(1), "col") . '">');
	                        $this->tpl[0]->set_var("search_more_container_class_end", '</div>');
						}
                        $this->tpl[0]->set_var("search_more_class", cm_getClassByFrameworkCss("more", "icon") . " " . cm_getClassByFrameworkCss("control-postfix", "form"));  
                        //$this->tpl[0]->set_var("search_more_icon", cm_getClassByFrameworkCss("more", "icon-link-tag"));  
                        $display_adv_search = $this->tpl[0]->parse("SectAdvSearch", false);
                        break;                                                                                                                                                                                                                                                                                                                                                                                                                                               
                    }
                }
                reset($this->search_fields);
    			
    			$this->search_fields[$this->search_simple_field_options["id"]]->properties["onkeydown"] = "javascript:ff.submitProcessKey(event, jQuery(this).nextAll('.search'));";
				$buffer = $this->search_fields[$this->search_simple_field_options["id"]]->process($this->search_simple_field_options["id"] . "_src");

				if($wrap_addon) {
					$arrSearchCol = array(10 - $display_adv_search);
					$buffer = '<div class="' . cm_getClassByFrameworkCss($arrSearchCol, "col") . '">' . $buffer . '</div>';
				}
                $this->tpl[0]->set_var("SearchAll", $buffer);
            }
    
            if(/*!$this->searched &&*/ $this->open_adv_search === false)
            	$this->tpl[0]->set_var("adv_class", "adv-search hidden");
        }

		$search_class["default"] = "search";
		$this->tpl[0]->set_var("search_class", cm_getClassByDef($this->framework_css["search"], $search_class));
		$this->tpl[0]->set_var("search_box_class", cm_getClassByFrameworkCss("group", "form"));
        
		//$this->tpl[0]->set_var("maxspan", ($this->search_cols * 2));
		//$this->tpl[0]->set_var("search_method", $this->search_method);
		//$this->tpl[0]->set_var("search_url", $this->search_url);

		//$col = 1; 
		//$last_span = 0;
		$wrap_count = 0;

		$this->parsed_fields = 0;
		$this->parsed_filters = 0;

		foreach ($this->search_fields as $key => $value)
		{
			if (!$this->search_fields[$key]->display)
				continue;

			$class = $this->search_fields[$key]->container_class;
			if(!strlen($class))
				$class = $this->column_class;
			
			if(strlen($class)) {
				$this->tpl[0]->set_var("container_class", " " . $class);
			} else {
				$this->tpl[0]->set_var("container_class", "");
			}
			if ($this->search_fields[$key]->multi_disp_as_filter)
			{
				$this->parsed_filters++;
				
				$this->tpl[0]->set_var("id", $this->search_fields[$key]->id . "_src");
				$this->tpl[0]->set_var("value", ffCommon_specialchars($this->search_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE)));
				$this->parse_hidden_field();
				
				$this->tpl[0]->set_var("SectFilterRow", "");
				$this->tpl[0]->set_var("SectFilterElement", "");
				$this->tpl[0]->set_var("SectSelFilterElement", "");
				$this->tpl[0]->set_var("SectNoElements", "");
				
				$this->tpl[0]->set_var("FilterElement_id", $this->search_fields[$key]->id . "_src");
				$this->tpl[0]->set_var("Filter_title", $this->search_fields[$key]->label);
				
				$this->search_fields[$key]->pre_process();
				
				$tmp_field_value = $this->search_fields[$key]->value->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale());
				
				if ($this->search_fields[$key]->multi_select_one && !$this->search_fields[$key]->multi_limit_select)
				{
					$this->tpl[0]->set_var("FilterElement_label", $this->search_fields[$key]->multi_select_one_label);
					
					if ($this->search_fields[$key]->multi_select_one_val !== null)
					{
						$tmp_value = $this->search_fields[$key]->multi_select_one_val->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale());
					}
					else
					{
						$tmp_value = "";
					}
					
					$this->tpl[0]->set_var("FilterElement_value", $tmp_value);

					if ($tmp_value == $tmp_field_value)
					{
						$this->tpl[0]->set_var("SectFilterElement", "");
						$this->tpl[0]->parse("SectSelFilterElement", false);
					}
					else
					{
						if (isset($_REQUEST["XHR_CTX_ID"]))
							$this->tpl[0]->set_var("filter_action", "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'filter', 'component' :'" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $tmp_value . "'})");
						else
							$this->tpl[0]->set_var("filter_action", "ff.ajax.doRequest({'action' : 'filter', 'component' : '" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $tmp_value . "'});");

						$this->tpl[0]->parse("SectFilterElement", false);
						$this->tpl[0]->set_var("SectSelFilterElement", "");
					}
					
					$this->tpl[0]->parse("SectFilterRow", true);
				}
	
				if ($this->search_fields[$key]->multi_select_noone &&
						(!$this->search_fields[$key]->multi_limit_select ||
							($this->search_fields[$key]->multi_limit_select && $this->search_fields[$key]->multi_select_noone_val->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale()) == $tmp_field_value)
						)
					)
				{
					$this->tpl[0]->set_var("FilterElement_label", $this->search_fields[$key]->multi_select_noone_label);
					
					if ($this->search_fields[$key]->multi_select_noone_val !== null)
						$tmp_value = $this->search_fields[$key]->multi_select_noone_val->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale());
					else
						$tmp_value = "";
					
					$this->tpl[0]->set_var("FilterElement_value", $tmp_value);

					if ($tmp_value == $tmp_field_value)
					{
						$this->tpl[0]->set_var("SectFilterElement", "");
						$this->tpl[0]->parse("SectSelFilterElement", false);
					}
					else
					{
						if (isset($_REQUEST["XHR_CTX_ID"]))
							$this->tpl[0]->set_var("filter_action", "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'filter', 'component' :'" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $tmp_value . "'})");
						else
							$this->tpl[0]->set_var("filter_action", "ff.ajax.doRequest({'action' : 'filter', 'component' : '" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $tmp_value . "'});");
						
						$this->tpl[0]->parse("SectFilterElement", false);
						$this->tpl[0]->set_var("SectSelFilterElement", "");
					}
					
					$this->tpl[0]->parse("SectFilterElement", false);
					$this->tpl[0]->parse("SectFilterRow", true);
				}
	
				
				if (is_array($this->search_fields[$key]->recordset) && count($this->search_fields[$key]->recordset))
				{
					foreach ($this->search_fields[$key]->recordset as $sub_key => $sub_item)
					{
						list($tmp, $item_key) = each($sub_item);
						list($tmp, $item_value) = each($sub_item);
						
						$this->tpl[0]->set_var("FilterElement_value", $item_key->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale()));
						$this->tpl[0]->set_var("FilterElement_label", $item_value->getValue($this->search_fields[$key]->get_multi_app_type(), $this->search_fields[$key]->get_locale()));
						
						if ($item_key->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale()) == $tmp_field_value)
						{
							$this->tpl[0]->set_var("SectFilterElement", "");
							$this->tpl[0]->parse("SectSelFilterElement", false);
						}
						else
						{
							if (isset($_REQUEST["XHR_CTX_ID"]))
								$this->tpl[0]->set_var("filter_action", "ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'filter', 'component' :'" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $item_key->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale()) . "'})");
							else
								$this->tpl[0]->set_var("filter_action", "ff.ajax.doRequest({'action' : 'filter', 'component' : '" . $this->getIDIF() . "', '" . $this->getPrefix() . $this->search_fields[$key]->id . "_src' : '" . $item_key->getValue($this->search_fields[$key]->get_app_type(), $this->search_fields[$key]->get_locale()) . "'});");
							
							$this->tpl[0]->parse("SectFilterElement", false);
							$this->tpl[0]->set_var("SectSelFilterElement", "");
						}
						
						$this->tpl[0]->parse("SectFilterRow", true);
					}
					reset($this->search_fields[$key]->recordset);
				}
				else
				{
					$this->tpl[0]->parse("SectNoElements", "");
				}
				
				$this->tpl[0]->parse("SectFilter", true);
			}
			elseif($this->display_search)
			{
				$buffer = "";
				$container_class = "";
				if ($this->search_fields[$key]->src_interval)
				{
					// display label from
					if($this->search_fields[$key]->interval_from_label)
						$buffer .= '<label for="' . $this->id . "_" . $this->search_fields[$key]->id . '_from_src">' . ffCommon_specialchars($this->search_fields[$key]->interval_from_label). '</label>'; 
					
					// display control from
					$buffer .= $this->search_fields[$key]->process($this->search_fields[$key]->id . "_from_src", $this->search_fields[$key]->interval_from_value);

					// display label to
					if($this->search_fields[$key]->interval_to_label)
						$buffer = '<label for="' . $this->id . "_" . $this->search_fields[$key]->id . '_to_src">' . ffCommon_specialchars($this->search_fields[$key]->interval_to_label). '</label>'; 
					
					// display control to
					$buffer .= $this->search_fields[$key]->process($this->search_fields[$key]->id . "_to_src", $this->search_fields[$key]->interval_to_value);
				}
				else
				{
					// display label
					if($this->search_fields[$key]->label)
						$buffer .= '<label for="' . $this->id . "_" . $this->search_fields[$key]->id . '_src">' . ffCommon_specialchars($this->search_fields[$key]->label). '</label>'; 
					
					// display control
					$buffer .= $this->search_fields[$key]->process($this->search_fields[$key]->id . "_src");
				}
				
				$this->parsed_fields++;
				$row = "row";
				if(is_array($this->search_fields[$key]->framework_css["container"]["col"]) 
				    && count($this->search_fields[$key]->framework_css["container"]["col"])
				) {
					$container_class = cm_getClassByFrameworkCss($this->search_fields[$key]->framework_css["container"]["col"], "col");
					$wrap_count = $wrap_count + $this->search_fields[$key]->framework_css["container"]["col"]["lg"];
				} elseif($this->search_fields[$key]->framework_css["container"]["row"]) {
					$row = "row-padding";
					//$container_class = cm_getClassByFrameworkCss("row-padding", "form");
					$wrap_count = 12;
				} else {
					$wrap_count = 12;
				}				

				if($container_class)
					$buffer = '<div class="' . $container_class . '">' . $buffer . "</div>";

				$this->tpl[0]->set_var("control", $buffer);
				$this->tpl[0]->parse("SectSearchCol", true);

				if($wrap_count >= 12) {
					$this->tpl[0]->set_var("adv_row_class", cm_getClassByFrameworkCss($row, "form"));
					$this->tpl[0]->parse("SectSearchRow", true);
					$this->tpl[0]->set_var("SectSearchCol", "");
					$wrap_count = 0;
				}
			}
		}
		reset($this->search_fields);
		
		if($this->display_search) 
		{
			//$this->tpl[0]->parse("SectSearchRow", true);
			
			$show_section = false;

			//$this->initControls();

			// PROCESS ALL BUTTONS
			$buffer = "";
			if (is_array($this->search_buttons) && count($this->search_buttons))
			{
				$show_section = true;
				foreach ($this->search_buttons as $key => $value)
					{
						$buffer = $this->search_buttons[$key]->process() . $buffer;
					}
				reset($this->search_buttons);
			}

			if ($show_section)
			{
				$wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
				if($wrap_addon) {
					$arrSearchCol = array(2);
				
					$buffer = '<div class="' . cm_getClassByFrameworkCss($arrSearchCol, "col") . '">' . $buffer . '</div>';
				}

				$this->tpl[0]->set_var("SearchButtons", $buffer);
				if(!$this->searched && $this->open_adv_search === false) {
					$this->search_buttons[0]->id = "searchadv";
					unset($this->search_buttons[0]->framework_css["addon"]);
					$this->tpl[0]->set_var("SearchButtonsAdv", '<div class="' . cm_getClassByFrameworkCss("align-right", "util"). '">' . $this->search_buttons[0]->process() . '</div>');
				}
			}

			if ($this->parsed_fields)
				$this->tpl[0]->parse("SectFields", false);
				
			if ($this->search_container === null)
				$this->tpl[0]->parse("SectSearch", false);
			else
			{
				$this->search_container_buffer = $this->tpl[0]->rpparse("SectSearch", false);
				$this->tpl[0]->set_var("SectSearch", "");
			}
		}

		if ($this->parsed_filters)
			$this->tpl[0]->parse("SectFilters", false);        
    }

    /**
     * Esegue il processing vero e proprio dei contenuti della grid
     */
    public function process_grid()
    {    
        if ($this->display_grid == "never" || ($this->display_grid == "search" && !strlen($this->searched)))
        {
            $this->tpl[0]->set_var("SectGridTable", "");
			$this->tpl[0]->parse("SectGrid", false);
            return;
        }
         
		// Manage various buttons
        /*if ($this->display_edit_bt)
        	$this->addGridDisposition("edit", "button", ($this->grid_disposition_elem["button"][$this->buttons_options["delete"]["disposition"]["row"] - 1] == $this->grid_disposition_elem["data"][$this->buttons_options["edit"]["disposition"]["row"] - 1][count($this->grid_disposition_elem["data"][$this->buttons_options["edit"]["disposition"]["row"] - 1]) - 1]["button"] 
        		? "last" 
        		: $this->buttons_options["delete"]["disposition"]["col"]), $this->buttons_options["edit"]["disposition"]["row"]
        	); 
        if ($this->display_delete_bt)
        	$this->addGridDisposition("delete", "button", ($this->grid_disposition_elem["button"][$this->buttons_options["delete"]["disposition"]["row"] - 1] == $this->grid_disposition_elem["data"][$this->buttons_options["delete"]["disposition"]["row"] - 1][count($this->grid_disposition_elem["data"][$this->buttons_options["delete"]["disposition"]["row"] - 1]) - 1]["button"] 
        		? "last" 
        		: $this->buttons_options["delete"]["disposition"]["col"]), $this->buttons_options["delete"]["disposition"]["row"]
        	);*/

        if ($this->display_edit_bt)
        	$this->addGridDisposition("edit", "button", $this->buttons_options["edit"]["disposition"]["col"], $this->buttons_options["edit"]["disposition"]["row"]);
        if ($this->display_delete_bt)
        	$this->addGridDisposition("delete", "button", $this->buttons_options["delete"]["disposition"]["col"], $this->buttons_options["delete"]["disposition"]["row"]);

        	
        	//echo $this->grid_disposition_elem["data"][$this->buttons_options["delete"]["disposition"]["row"] - 1][count($this->grid_disposition_elem["data"][$this->buttons_options["delete"]["disposition"]["row"] - 1]) - 1]["button"];
        parent::process_grid();

		//$table_class["default"] = "ffGrid";
        $this->tpl[0]->set_var("table_class", cm_getClassByDef($this->framework_css["table"]));

        if ($this->display_new) // done at this time due to maxspan
        {
            if (strlen($this->bt_insert_url))
            {
                $addnew_url_ajax = ffProcessTags($this->bt_insert_url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
                $addnew_url_noajax = $addnew_url_ajax;
            }
            else
            {
                if (strlen($this->record_insert_url))
                    $addnew_url_ajax = $this->record_insert_url;
                else
                    $addnew_url_ajax = $this->record_url;

                $addnew_url_ajax .= "?" . $this->parent[0]->get_keys($this->key_fields) .
                            $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) . $this->addit_insert_record_param;

                $addnew_url_ajax = ffProcessTags($addnew_url_ajax, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
				$addnew_url_noajax = $addnew_url_ajax; //. "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());

				/*if($this->full_ajax || $this->ajax_addnew)
                    $addnew_url = $addnew_url_ajax;
                else 
                    $addnew_url = $addnew_url_noajax;	*/			
            }
            
         
/*            $res = $this->doEvent("onUrlInsert", array(&$this, $temp_url));
            $rc = end($res);
                if ($rc !== null)
                    $temp_url = $rc;
*/
            if($this->buttons_options["addnew"]["label"] === null)
                $this->buttons_options["addnew"]["label"] = ffTemplate::_get_word_by_code("ffGrid_addnew");
            
            if($this->buttons_options["addnew"]["icon"] === null)
                $this->buttons_options["addnew"]["icon"] = cm_getClassByFrameworkCss("addnew", "icon-" . $this->buttons_options["addnew"]["aspect"] . "-tag");

            if($this->buttons_options["addnew"]["class"] === null)
                $this->buttons_options["addnew"]["class"] = cm_getClassByFrameworkCss("addnew", $this->buttons_options["addnew"]["aspect"]);        

            if ($this->full_ajax || $this->ajax_addnew)
            {
				$this->load_dialog($this->record_id, $this->dialog_options["addnew"]);
				$buffer = "<a " . (strlen($this->buttons_options["addnew"]["class"]) 
		                                                    ? "class=\"" . $this->buttons_options["addnew"]["class"] . "\" " 
		                                                    : ""
		                                                ) 
		                                                . "href=\"javascript:ff.ffPage.dialog.doOpen('" . $this->record_id . "', '" . ffCommon_specialchars($addnew_url_ajax) . "');\">" 
		                                                . $this->buttons_options["addnew"]["icon"] . $this->buttons_options["addnew"]["label"] 
		                                            . "</a>";				
				$this->tpl[0]->set_var("addnew_bt", $buffer);
				/*
                $this->parent[0]->widgetLoad("dialog");
                $this->tpl[0]->set_var("addnew_bt", $this->parent[0]->widgets["dialog"]->process(
                        $this->getIDIF()
                        , array(
                                "title"            	=> (strlen($this->dialog_options["addnew"]["title"])
                                                            ? $this->dialog_options["addnew"]["title"]
                                                            : ffTemplate::_get_word_by_code("ffGrid_addnew_title")
                                                        )
                                , "url"            	=> $temp_url
                                , "class"        	=> $this->buttons_options["addnew"]["class"]
                                , "name"        	=> $this->buttons_options["addnew"]["icon"] . $this->buttons_options["addnew"]["label"]
                                , "tpl_id"       	=> $this->getIDIF()
                                , "width"        	=> $this->dialog_options["addnew"]["width"]
                                , "height"        	=> $this->dialog_options["addnew"]["height"]
                            )
                        , $this->parent[0]
                    ));*/
                $this->tpl[0]->parse("SectAddNewBt", false);
                $this->tpl[0]->set_var("SectAddNewUrl", "");
            }
            else
            {
                $this->tpl[0]->set_var("addnew_url", "javascript:ff.ffPage.goToWithRetUrl('" . ffCommon_specialchars($addnew_url_noajax) . "');");
                $this->tpl[0]->set_var("addnew_label", $this->buttons_options["addnew"]["label"]);
                if(strlen($this->buttons_options["addnew"]["class"]))
                    $this->tpl[0]->set_var("addnew_class", ' class="' . $this->buttons_options["addnew"]["class"] . ' noajax"');
                $this->tpl[0]->set_var("addnew_icon", $this->buttons_options["addnew"]["icon"]);
                $this->tpl[0]->parse("SectAddNewUrl", false);
                $this->tpl[0]->set_var("SectAddNewBt", "");
            }
            $this->tpl[0]->parse("SectAddNew", false);
            $this->tpl[0]->set_var("SectNotAddNew", "");
        }
        else
        {
            $this->tpl[0]->set_var("SectAddNew", "");
            if ($this->display_not_add_new)
                $this->tpl[0]->parse("SectNotAddNew", false);
            else
                $this->tpl[0]->set_var("SectNotAddNew", "");
        }
        
        $col_count = count($this->grid_fields);
        
        if ($this->display_edit_bt)
            $col_count++;

        if ($this->display_delete_bt)
            $col_count++;

        if (is_array($this->grid_buttons) && count($this->grid_buttons))
            $col_count += count($this->grid_buttons);

        //$this->tpl[0]->set_var("maxspan", $totfields);

        $this->process_navigator(); // done at this time due to maxspan and $this->db[0]->numRows()

        if ($this->db[0]->query_id && $this->db[0]->nextRecord())
        {
        	/**
        	* Set last button Col
        	*/
			if(count($this->grid_disposition) > 1
                && count($this->grid_disposition_elem["data"][0][count($this->grid_disposition_elem["data"][0]) - 1]) == 1
                && array_key_exists("button", $this->grid_disposition_elem["data"][0][count($this->grid_disposition_elem["data"][0]) - 1])
            ) {
                $last_button = $this->grid_disposition[0][count($this->grid_disposition) - 1];
                unset($this->grid_disposition[0][count($this->grid_disposition) - 1]);
            }        
        
			if ($this->full_ajax || $this->ajax_edit || $this->ajax_delete) {
				if($this->visible_edit_bt || $this->display_edit_url)
					$this->load_dialog($this->record_id, $this->dialog_options["edit"]);

				if($this->visible_delete_bt)
					$this->load_dialog($this->record_id, $this->dialog_options["delete"]);
			}
            //$this->tpl[0]->set_var("SectNoRecords", "");
            if ($this->use_paging && !$this->pagination_save_memory_in_use)
                $this->db[0]->jumpToPage($this->page, $this->records_per_page);
            
            $arrGridData = array();
            $break_while = false;
            $recordset_count = 0;

            $rows = $this->db[0]->numRows();
            $srow = (($this->page - 1) * $this->records_per_page) + 1;
            do
            {
            	$arrGridData[] = $this->db[0]->record;
	            if ($this->use_paging && $recordset_count >= $this->records_per_page - 1)
	                $break_while = true;   

				$recordset_count++;
			} while($this->db[0]->nextRecord() && !$break_while);           
            
        	$res = $this->doEvent("on_loaded_data", array(&$this, &$arrGridData));
            
            foreach($arrGridData AS $rrow => $record)
            {
            	$this->db[0]->record = $record;
            	
                $this->tpl[0]->set_var("row", $rrow);

                $res = $this->doEvent("on_load_row", array(&$this, $this->db[0], $rrow));
                
                /* Step 1: retrieve values (done in 2 steps due to events needs) */

                $keys = "";
                $row_properties = array();
				
                //$unicKey = $i;
                if (count($this->key_fields))
                {
                    // find global recordset corrispondency (if one)
                    $aKeys = array();
                    foreach($this->key_fields as $key => $FormField)
                    {
                        $this->key_fields[$key]->value = $this->db[0]->getField($this->key_fields[$key]->get_data_source(), $this->key_fields[$key]->base_type);
                        $aKeys[$key] = $this->key_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE);
                        $keys .= "keys[" . $this->key_fields[$key]->id . "]=" . $this->key_fields[$key]->value->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE) . "&";
                        //$unicKey .= $this->key_fields[$key]->value->getValue($this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                        
                        $this->tpl[0]->set_var("Key_" . $key, $this->key_fields[$key]->getValue($this->key_fields[$key]->get_app_type(), $this->key_fields[$key]->get_locale()));
                        $this->tpl[0]->set_var("encoded_Key_" . $key, rawurlencode($this->key_fields[$key]->getValue($this->key_fields[$key]->get_app_type(), $this->key_fields[$key]->get_locale())));
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
                    reset($this->key_fields);

                    $recordset_key = array_search($aKeys, $this->recordset_keys, true);
                    // if not exists, add new
                    if ($recordset_key === false)
                    {
                        $recordset_key = count($this->recordset_keys);
                        $this->recordset_keys[$recordset_key] = $aKeys;
                    }
                    $this->displayed_keys[$recordset_key] = $aKeys;


                    // display hidden key fields to managed displayed values properly
                    if ($this->use_fields_params)
                    {
                        foreach($this->key_fields as $key => $FormField)
                        {
                            $this->tpl[0]->set_var("id", "displayed_keys[" . $recordset_key . "][" . $this->key_fields[$key]->id . "]");
                            $this->tpl[0]->set_var("value",   ffCommon_specialchars($this->key_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE)));
                            $this->parse_hidden_field();
                        }
                        reset($this->key_fields);
                    }
                }

                $keys .= $this->parent[0]->get_keys($this->key_fields);

                foreach ($this->grid_fields as $key => $FormField)
                {
                    if ($this->use_fields_params && $this->grid_fields[$key]->control_type != "" && isset($this->recordset_values[$recordset_key][$key]))
                    {
                        $this->grid_fields[$key]->value = new ffData($this->recordset_values[$recordset_key][$key], $this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                        if (isset($this->recordset_ori_values[$recordset_key][$key]))
                        {
                            $this->tpl[0]->set_var("id", "recordset_ori_values[" . $recordset_key . "][" . $this->grid_fields[$key]->id . "]");
                            $this->tpl[0]->set_var("value",   ffCommon_specialchars($this->recordset_ori_values[$recordset_key][$key]));
                            $this->parse_hidden_field();
                        }
                    }
                    else
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
                                if (isset($this->recordset_values[$recordset_key][$key]))
                                    $this->grid_fields[$key]->value = new ffData($this->recordset_values[$recordset_key][$key], $this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
                                else
                                    $this->grid_fields[$key]->value = $this->grid_fields[$key]->getDefault(array(&$this));
                        }
                        if ($this->use_fields_params && $this->grid_fields[$key]->control_type != "")
                        {
                            $this->tpl[0]->set_var("id", "recordset_ori_values[" . $recordset_key . "][" . $this->grid_fields[$key]->id . "]");
                            $this->tpl[0]->set_var("value", ffCommon_specialchars($this->grid_fields[$key]->value->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
                            $this->parse_hidden_field();
                        }
                    }
                }
                reset($this->grid_fields);

				parent::processRow();

                /* Step 2: display values */

                // EVENT HANDLER
                $res = $this->doEvent("on_before_parse_row", array(&$this, $rrow));
                $rc = end($res);

                if ($rc === null)
                {


					/**
	                * Set Modify Delete And Confirm Delete Url
	                */
	                if (strlen($this->bt_edit_url))
	                {
	                    $modify_url["default"] = $this->bt_edit_url;
		                $modify_url["default"] = ffProcessTags($modify_url["default"], $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
	                }
	                else
	                {
	                    $modify_url["ajax"] = $this->record_url . "?" . $keys .
	                                $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
	                                $this->addit_record_param;

		                $modify_url["ajax"] = ffProcessTags($modify_url["ajax"], $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);
	  					
	  					if($this->cursor_dialog) {
	  						$modify_url["ajax"] .= "cursor[id]=" . rawurlencode($this->getIDIF()) . "&"
													. "cursor[rrow]=" . rawurlencode($srow + $rrow) . "&"
													. "cursor[rows]=" . rawurlencode($rows) . "&";
	  					}                  

	                    $modify_url["noajax"] = $modify_url["ajax"]; // . "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
	                    
	                    if($this->full_ajax || $this->ajax_edit)
                    		$modify_url["default"] = $modify_url["ajax"];
	                    else 
                    		$modify_url["default"] = $modify_url["noajax"];
	                }

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
	                                    $this->record_id . "_frmAction=confirmdelete"
	                                    /*. ($this->full_ajax || $this->ajax_delete
                                    		? ""
                                    		: "&ret_url=" . rawurlencode($this->parent[0]->getRequestUri())
	                                    )*/;
	                
	                    $confirmurl = ffProcessTags($confirmurl, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);

	                    $delete_url = $this->dialog(
	                                      true
	                                    , "yesno"
	                                    , $this->parent[0]->title
	                                    , $this->label_delete
	                                    , null// ($this->full_ajax || $this->ajax_delete ? "[CLOSEDIALOG]" : "" /*$cancelurl*/)
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
	                        $confirmurl = $this->record_url . "?" . $keys .
	                                    $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
	                                    $this->addit_record_param .
	                                    $this->record_id . "_frmAction=confirmdelete"
	                                    /*. ($this->full_ajax || $this->ajax_delete
                                    		? ""
                                    		: "&ret_url=" . rawurlencode($this->parent[0]->getRequestUri())
	                                    )*/;
	                    }
	                    $confirmurl = ffProcessTags($confirmurl, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]);

	                    $delete_url = $this->dialog(
	                                                  true
	                                                , "yesno"
	                                                , $this->parent[0]->title
	                                                , $this->label_delete
	                                                , null //($this->full_ajax || $this->ajax_delete ? "[CLOSEDIALOG]" : "" /*$cancelurl*/)
	                                                , $confirmurl
	                                                );
	                }                 
                    $this->tpl[0]->set_var("keys", $keys); // Useful for Fixed Fields Templates

                   

                    if($this->display_edit_url) 
                    {
                    	$row_class["editable"] = "clickable";
                    	if($this->full_ajax || $this->ajax_edit) 
                    		$row_class["editable"] .= " ajax";
						if($this->record_target == true) 
                    		$row_properties["data-target"] = "_blank";

                    	$row_properties["data-url"] = ffCommon_specialchars($modify_url["default"]);
                    } else 
                    	$row_class["editable"] = "";

					/**
					* Ser Row Class And Properties                    	
					*/
                    if($this->switch_row_class["display"])
                    {
                        if($row_class["oddeven"] == $this->switch_row_class["first"])
                            $row_class["oddeven"] = $this->switch_row_class["second"];
                        else 
                            $row_class["oddeven"] = $this->switch_row_class["first"];
                    }

                    $row_class["record"] = "row-" . ($rrow + 1);
                         
                   
                    $row_class["custom"] = trim($this->row_class);
                    
                    /**
                    * Process Rows Cols Fields buttons
                    */
                   //print_r($this->grid_disposition_elem);
                   // print_r($this->grid_disposition);
                    if(is_array($this->grid_disposition) && count($this->grid_disposition)) 
                    {
                    	$buffer_col = "";
                    	if(count($this->grid_disposition) > 1) {
                    		$this->tpl[0]->set_var("row_init_start", '<td class="cel-1 mixed"><table><tbody>');
                    	}

                    	foreach($this->grid_disposition AS $row => $cols) 
                    	{
							if(count($this->grid_disposition) > 1) {
                    			$this->tpl[0]->set_var("row_start", '<tr' . (isset($this->grid_disposition_elem["rows"][$row])
                    				? " " . $this->getProperties($this->grid_disposition_elem["rows"][$row])
                    				: ''
                    			)
                    			. '>');
							}
                    		if(is_array($cols) && count($cols)) 
                    		{
                    			foreach($cols AS $col => $grid_contents)
                    			{
                                    $this->parse_col($grid_contents, $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col + 1, count($cols), $row + 1);
                    			}
                    		}

					        $this->tpl[0]->parse("SectRow", true);
					        $this->tpl[0]->set_var("SectCol", "");                  
						    $this->tpl[0]->set_var("row_start", "");
						    $this->tpl[0]->set_var("row_end",  "");

							if(count($this->grid_disposition) > 1) {
                    			$this->tpl[0]->set_var("row_end",  "</tr>");
							}
                    	}
						if(count($this->grid_disposition) > 1) {
						    if($last_button) {
							    $buffer_col = $this->parse_col($last_button, $recordset_key, $rrow, $modify_url, $keys, $delete_url, 2, 1, 1, true);
							    $this->tpl[0]->set_var("SectCol", "");
							}

							$this->tpl[0]->set_var("row_init_end", "</tbody></table></td>" . $buffer_col);
						}

                    }
                    else 
                    {
	                    $col = 1;  
	                    foreach ($this->grid_fields as $key => $FormField)
	                    {
	                        $this->parse_col($FormField, $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col, $col_count);
	                        
	                        $col++;
	                    }
	                    reset($this->grid_fields);

	                    if (is_array($this->grid_buttons) && count($this->grid_buttons))
	                    {
	                        foreach($this->grid_buttons as $key => $FormButton)
	                        {
		                        $this->parse_col($FormButton, $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col, $col_count);
		                        
		                        $col++;
	                        }
	                        reset($this->grid_buttons);
	                    }
				   		if($this->display_edit_bt)
				   		{
				   			$this->parse_col(($this->visible_edit_bt ? "edit": ""), $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col, $col_count);
				   			
				   			$col++;
						}						
				   		if($this->display_delete_bt)
				   		{
				   			$this->parse_col(($this->visible_delete_bt ? "delete": ""), $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col, $col_count);
				   			
				   			$col++;
						}

				        $this->tpl[0]->parse("SectRow", true);
				        $this->tpl[0]->set_var("SectCol", "");                  
					}

                    //$this->tpl[0]->set_var("RowNumber", $rrow);
                    //$this->tpl[0]->set_var("RecordNumber", ($rrow + ($this->page - 1 ) * $this->records_per_page));


                    $res = $this->doEvent("on_before_parse_record", array(&$this));


			        $row_properties = array_replace($row_properties, $this->row_properties);
			        $row_properties["class"] = implode(" " , array_filter($row_class));

			        $this->tpl[0]->set_var("row_properties", $this->getProperties($row_properties));
                    
                    $rc = end($res);
                    if ($rc === null)
                        $this->tpl[0]->parse("SectRecord", true);

			        $this->tpl[0]->set_var("SectRow", "");                  
                }

                // EVENT HANDLER
                $res = $this->doEvent("on_after_parse_row", array(&$this, $rrow));
                $rc = end($res);
                if($rc === true)
                	break;

            }
        }
        else
        {
            //$this->tpl[0]->set_var("SectActionButtonsHeader", "");
            $this->tpl[0]->set_var("SectRecord", "");
        }

		$this->process_labels($col_count);
        // store value of fields in hidden section, but only for not displayed records

        if ($this->use_fields_params && is_array($this->recordset_keys) && count($this->recordset_keys))
        {
            // cycle records
            foreach ($this->recordset_keys as $rst_key => $rst_value)
            {
                $aKeys = array();
                // cycle keys
                foreach($this->key_fields as $key => $value)
                {
                    $this->tpl[0]->set_var("id", "recordset_keys[" . $rst_key . "][" . $key . "]");
                    $this->tpl[0]->set_var("value", ffCommon_specialchars($this->recordset_keys[$rst_key][$key]));
                    $this->parse_hidden_field();
                    $aKeys[$key] = $this->recordset_keys[$rst_key][$key];
                }
                reset($this->key_fields);

                $displayed = array_search($aKeys, $this->displayed_keys, true);
                // if not displayed, store hidden
                if ($displayed === false)
                {
                    // cycle fields
                    foreach($this->grid_fields as $key => $value)
                    {
                        if ($this->grid_fields[$key]->control_type !== "")
                        {
                            $this->tpl[0]->set_var("id", "recordset_ori_values[" . $rst_key . "][" . $key . "]");
                            $this->tpl[0]->set_var("value", ffCommon_specialchars($this->recordset_ori_values[$rst_key][$key]));
                            $this->parse_hidden_field();

                            $this->tpl[0]->set_var("id", "recordset_values[" . $rst_key . "][" . $key . "]");
                            $this->tpl[0]->set_var("value", ffCommon_specialchars($this->recordset_values[$rst_key][$key]));
                            $this->parse_hidden_field();
                        }
                    }
                    reset($this->grid_fields);
                }
            }
            reset($this->recordset_keys);
        }

        $res = $this->doEvent("on_after_process_grid", array(&$this, $this->tpl[0]));

        $component_class["default"] = $this->class;
        if($this->framework_css["component"]["grid"]) {
            if(is_array($this->framework_css["component"]["grid"]))
                $component_class["grid"] = cm_getClassByFrameworkCss($this->framework_css["component"]["grid"], "col");
            else {
                $component_class["grid"] = cm_getClassByFrameworkCss("", $this->framework_css["component"]["grid"]);
            }
        }
        if ($this->db[0]->query_id && $this->db[0]->numRows())
        {
        	$this->component_properties["data-record"] = $this->record_id;

            $this->tpl[0]->parse("SectGridData", false);
			$this->tpl[0]->parse("SectGridTable", false);
            $this->tpl[0]->parse("SectGrid", false);
            $this->tpl[0]->set_var("SectNoRecords", "");
        } 
        else 
        {
        	$component_class["empty"] = "padding " . cm_getClassByFrameworkCss("clear", "util");
        	$this->tpl[0]->set_var("norecord_class", cm_getClassByDef($this->framework_css["norecord"]));
            $this->tpl[0]->set_var("SectGridData", "");
			$this->tpl[0]->set_var("SectGridTable", "");
            $this->tpl[0]->parse("SectGrid", false);
            //$this->tpl[0]->set_var("SectAlpha", "");
            //$this->tpl[0]->set_var("SectFilter", "");
            //$this->tpl[0]->set_var("SectSearch", "");
            if ($this->no_record_label !== null)
            {
                $this->tpl[0]->set_var("grid_no_record", $this->no_record_label);
            } 
            else 
            {
                $this->tpl[0]->set_var("grid_no_record", ffTemplate::_get_word_by_code("grid_no_record"));
            }
            
            $this->tpl[0]->parse("SectNoRecords", false);
        }
		
		if($this->tpl[0]->isset_var("component_class")) {
		    $this->tpl[0]->set_var("component_class", implode(" " , array_filter($component_class)));
		} else {
			$this->component_properties["class"] = implode(" ", array_filter($component_class));
		}
		
		$this->tpl[0]->set_var("component_properties", $this->getProperties($this->component_properties));

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

        if($this->use_paging || $this->use_search || $this->use_alpha || $this->use_order || $this->parsed_hidden_fields || $this->use_fields_params)
            $this->tpl[0]->parse("SectHidden", false);
        else
            $this->tpl[0]->set_var("SectHidden", "");
    }

    function parse_field($field, $recordset_key, $modify_url, $keys, $hide_container = true, $display_label = false)
    {
	    if (!is_subclass_of($field, "ffField_base"))
	        ffErrorHandler::raise("Wrong Grid Field: must be a ffField", E_USER_ERROR, $this, get_defined_vars());
    
	    if ($field->display === false)
	        return false;

	    /**
	    * Set Field Value
	    */
	    if ($field->control_type === "")
	    {
	        $field->pre_process(true);
	        $buffer_control_value = $field->fixed_pre_content . $field->getDisplayValue() . $field->fixed_post_content;
	    }
	    else
	    {
	        $buffer_control_value = $field->process(
	                    "recordset_values[$recordset_key][" . $field->id . "]");
	    }	        
	    
	    /**
	    * Set Field Symbol
	    */
		if(strlen($this->symbol_valuta) && $field->app_type == "Currency")
			$buffer_symbol = $this->symbol_valuta;
			

		/**
	    * Set Field Data Info
	    */
		if($field->data_info["field"] !== null)
	    {
	        $data_info = $this->db[0]->getField($field->data_info["field"], $field->data_info["base_type"], true);
	        if(strlen($data_info))
	        {
	            if($field->data_info["multilang"])
	                $container_properties["title"] = ffTemplate::_get_word_by_code(strip_tags($this->db[0]->getField($field->data_info["field"], $field->data_info["base_type"], true)));
	            else
	                $container_properties["title"] = strip_tags($this->db[0]->getField($field->data_info["field"], $field->data_info["base_type"], true));
	        }
	    }
	    
		/**
	    * Set Field Label
	    */
		if(/*!$hide_container &&*/ $display_label && $field->display_label && strlen($field->label))
		{
			if($field->encode_label) 	
				$buffer_label_value = ffCommon_specialchars($field->label);
			else
				$buffer_label_value = $field->label;

			/**
			* Label Class and Properties
			*/
			$label_properties	= array();

			if($field->control_type != "")
				$label_properties["label_for"] = $this->getIDIF() . "_" . $field->id;
			
			$label_class = array();
			if(is_array($field->framework_css))
			{
				$arrColumnLabel = $field->framework_css["label"]["col"];
				$arrColumnControl = $field->framework_css["control"]["col"];
				
				$label_class["label"] = cm_getClassByFrameworkCss("label", "form");
			}
			
			if(is_array($arrColumnLabel) && count($arrColumnLabel)
				&& is_array($arrColumnControl) && count($arrColumnControl)
			) {
				$label_class["align"] = cm_getClassByFrameworkCss(array("text-overflow", "text-nowrap"), "util");
				if($this->framework_css["component"]["type"] == "inline") {
					$label_align = "right";
					$control_align = "left";
				} else {
					$label_align = "left";
					$control_align = "right";
				}
				$label_prefix = '<div class="' . cm_getClassByFrameworkCss($arrColumnLabel, "col") . " " . cm_getClassByFrameworkCss("align-" . $label_align, "util") . '">';
				$label_postfix = '</div>';

				$control_prefix = '<div class="' . cm_getClassByFrameworkCss($arrColumnControl, "col") . " " . cm_getClassByFrameworkCss("text-nowrap", "util") . " " . cm_getClassByFrameworkCss("align-" . $control_align, "util") . '">';
				$control_postfix = '</div>';
				//$type_label = "-inline";

				$label_set = true;
			}
			
			$label_properties["class"] = implode(" ", array_filter($label_class));
			
			$buffer_label = '<label ' . $field->getProperties($label_properties) . ' title="' . $buffer_label_value . '">' . $buffer_label_value . '</label>';
			$buffer_label_container = $label_prefix . $buffer_label . $label_postfix;
			
			//$label_set = true;
		}	        
    
   		/**
	    * Set Field Control
	    */ 
	    $field_url = $this->parse_field_url($field, $modify_url, $keys);
		if($field_url)
		{
			$buffer_control = '<a href="' . $field_url . '">' . $buffer_symbol . $buffer_control_value . '</a>';
		} else {
			$buffer_control = $buffer_symbol . $buffer_control_value;
		}

		$buffer_control_container = $control_prefix . $buffer_control . $control_postfix;
		
		/**
		* Set Field Container
		*/
    	$container_properties =  $field->container_properties;

    	$container_class["base"] = "ffField";
	    $container_class["container"] = $field->container_class;
	    if($field->get_app_type() == "Text" && $field->extended_type != "String")
	        $container_class["type"] = strtolower($field->extended_type);
	    else 
	        $container_class["type"] = strtolower($field->get_app_type());

		$buffer = $buffer_label_container . $buffer_control_container;
		if($hide_container) {
			if($label_set) {
				$tmp_class["grid"] = cm_getClassByFrameworkCss("row", "form");
				
				$buffer =  '<div class="' . $tmp_class["grid"] . '">' . $buffer . '</div>';
			}
		} else {
			if($label_set && is_array($field->framework_css["container"]["col"]) && count($field->framework_css["container"]["col"])) {
				$tmp_class["grid"] = cm_getClassByFrameworkCss("row", "form");
				
				$buffer =  '<div class="' . $tmp_class["grid"] . '">' . $buffer . '</div>';
				
				if(is_array($field->framework_css["container"]["col"]) 
					&& count($field->framework_css["container"]["col"])
				) {
					$container_class["grid"] = cm_getClassByFrameworkCss($field->framework_css["container"]["col"], "col");
				} elseif($field->framework_css["container"]["row"]) {
					$container_class["grid"] = cm_getClassByFrameworkCss("row", "form");
				}
			} elseif($label_set) {
				/*$container_class["grid"] = cm_getClassByFrameworkCss("row", "form");	*/
			} else {
				if(is_array($field->framework_css["container"]["col"]) && count($field->framework_css["container"]["col"])) {
					$container_class["grid"] = cm_getClassByFrameworkCss($field->framework_css["container"]["col"], "col");
				} elseif($field->framework_css["container"]["row"]) {
					//$container_class["grid"] = cm_getClassByFrameworkCss("row", "form");
				}
			}		

			$tmp_properties = $container_properties;
			$tmp_properties["class"] = implode(" ", array_filter($container_class));

			$buffer = '<div ' . $field->getProperties($tmp_properties) . '>' . $buffer . '</div>';
		}
		
		/**
		* Parse Fixed Field Vars
		*/
		$this->tpl[0]->set_var("euro", $buffer_symbol);
		$this->tpl[0]->set_var($field->id . "_display_value", $buffer_control_value);
		$this->tpl[0]->set_var($field->id . "_value", $field->value->getValue());
		$this->tpl[0]->set_var("url_" . $field->id, ffCommon_url_rewrite($field->value->getValue()));
		$this->tpl[0]->set_var("url_" . $field->id . "_display_value", ffCommon_url_rewrite($buffer_control_value));
		$this->tpl[0]->set_var("ent_" . $field->id . "_display_value", ffCommon_specialchars($buffer_control_value));
		$this->tpl[0]->set_var("ent_" . $field->id . "_value", ffCommon_specialchars($field->value->getValue()));
		
		/**
		* Parse Field Sections
		*/
   		if ($this->tpl[0]->isset_var("Field_" . $field->id) === false && $this->tpl[0]->isset_var("EncodedField_" . $field->id) === false) 
   		{     
			$this->tpl[0]->set_var("Value", $buffer);		
		    $this->tpl[0]->parse("SectGridContent", true);

		    $this->tpl[0]->set_var("Fvalue_" . $field->id, $buffer_control_value);
		    $this->tpl[0]->set_var("EncodedFvalue_" . $field->id, rawurlencode(ffCommon_url_normalize($buffer_control_value)));
		} 
		else 
		{
	        $this->tpl[0]->set_var("Field_" . $field->id . "_url", ffCommon_specialchars($field_url));
	        $this->tpl[0]->set_var("Field_" . $field->id, $buffer_control_value);
		    $this->tpl[0]->set_var("EncodedField_" . $field->id, rawurlencode(ffCommon_url_normalize($buffer_control_value)));
		}
		
		if (strlen($this->grid_fields[$field->id]->value->ori_value))
		{
		    $this->tpl[0]->parse("SectSetField_" . $field->id, false);
		    $this->tpl[0]->set_var("SectNotSetField_" . $field->id, "");
		}
		else
		{
		    $this->tpl[0]->set_var("SectSetField_" . $field->id, "");
		    $this->tpl[0]->parse("SectNotSetField_" . $field->id, false);
		}

		if ($this->grid_fields[$field->id]->extended_type == "Selection")
		{
		    $this->tpl[0]->set_regexp_var("/SectSet_" . $field->id . "_.+/", "");
		    $this->tpl[0]->parse("SectSet_" . $field->id . "_" . $this->grid_fields[$field->id]->value->getValue($this->grid_fields[$field->id]->base_type, FF_SYSTEM_LOCALE), false);
		}
		
		$this->tpl[0]->parse("Sect_" . $field->id, false);
				
		return array(
			"symbol" => $buffer_symbol
			, "control" => $buffer_control
			, "control_value" => $buffer_control_value
			, "label" => $buffer_label
			, "label_value" => $buffer_label_value
			, "container" => $buffer
			, "container_class" => ($hide_container ? $container_class : array())
			, "container_properties" => ($hide_container ? $container_properties : array())
		);
    }

	/**
	* Set Field Url    
	*/    
    function parse_field_url($oField, $modify_url, $keys)
    {
		$field_url = null;
		if ($oField->url === null)
		{
		    $oField->url_ajax = false;
		} elseif($oField->url === true) {
			$oField->url_ajax = $this->full_ajax || $this->ajax_edit;
			if($this->display_edit_url_alt) {
				$field_url = $this->display_edit_url_alt . "?" . $keys .
								$this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) .
								$this->addit_record_param;

				//if(!$oField->url_ajax)
				//	$field_url .= "ret_url=" . rawurlencode($this->parent[0]->getRequestUri());
			} else {
				if($oField->url_ajax)
					$field_url = $modify_url["ajax"];
				else
					$field_url = $modify_url["noajax"];
			}

			if($oField->url_ajax) {
				$this->load_dialog($this->record_id, $this->dialog_options["edit"]);
				$field_url = "javascript:ff.ffPage.dialog.doOp0en('" . $this->record_id . "', '" . ffCommon_specialchars($field_url) . "');";
			} else {
				$field_url = ffCommon_specialchars($field_url);					    
			}
		} elseif(strlen($oField->url)) {
			if($oField->url_ajax) {
				$this->load_dialog($this->record_id, $this->dialog_options["edit"]);
				$field_url = "javascript:ff.ffPage.dialog.doOpen('" . $this->record_id . "', '" . ffCommon_specialchars(ffProcessTags($oField->url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0])) . "');";
			} else {
				$field_url = ffCommon_specialchars(ffProcessTags($oField->url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), $_SERVER['REQUEST_URI'], $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), null, $this->db[0]));
			}
		}
		$oField->url_parsed = $field_url;
		return $field_url;    
    }
    function parse_button($button, $recordset_key, $rrow, $url, $hide_container = true) 
    {
		/**
		* Set Button Class
		*/
    	$container_class["base"] = "ffButton";

	    /**
		* Parse Button Section
		*/
	    if(is_object($button) && is_subclass_of($button, "ffButton_base"))
	    {
			/**
			* Set Button Class And Properties
			*/
			$container_class["container"] = $button->container_class;
    		$container_properties =  $button->container_properties;

		    if(!is_array($button->class)) {
		    	/*if(!$hide_container)
		    		$container_class["custom"] = $button->class;

			    $button->class = array(
	                "value" => ($hide_container ? $button->class : implode(" ", array_filter($container_class)))
	                , "params" => array("strict" => true)
			    );*/
			    
			    $button->class = array(
	                "value" => $button->class
	                , "params" => array("strict" => true)
			    );
			}

		    $buffer = $button->process(null
		                                , false
		                                , $button->id . "_" . $rrow
		                            );

		} elseif($button == "edit" || $button == "delete") {
			if($this->buttons_options[$button]["icon"] === null)
			    $this->buttons_options[$button]["icon"] = cm_getClassByFrameworkCss($button . "row", "icon-" . $this->buttons_options[$button]["aspect"] . "-tag");
			 
			if($this->buttons_options[$button]["class"] === null) 
			    $this->buttons_options[$button]["class"] = $button . "row";

 			if ($this->full_ajax || ($button == "edit" && $this->ajax_edit) || ($button == "delete" && $this->ajax_delete))
		    {	                        
				$buffer = "<a " . (strlen($this->buttons_options[$button]["class"]) 
		                                                    ? "class=\"" . $this->buttons_options[$button]["class"] . "\" " 
		                                                    : ""
		                                                ) 
		                                                . "href=\"javascript:ff.ffPage.dialog.doOpen('" . $this->record_id . "', '" . rawurlencode($url) . "');\">" 
		                                                . $this->buttons_options[$button]["icon"] . $this->buttons_options[$button]["label"] 
		                                            . "</a>";
			}
			else
			{
				$buffer = "<a " . (strlen($this->buttons_options[$button]["class"]) 
		                                                    ? "class=\"" . $this->buttons_options[$button]["class"] . "\" " 
		                                                    : ""
		                                                )
                                                        . "href=\"javascript:ff.ffPage.goToWithRetUrl('" . rawurlencode($url) . "');\">"
		                                               // . "href=\"" . $url . "\">"
		                                                . $this->buttons_options[$button]["icon"] . $this->buttons_options[$button]["label"]
		                                            . "</a>";						
			}    
		}
		
		if($buffer) {
			$this->tpl[0]->set_var("Value", $buffer);		
			$this->tpl[0]->parse("SectGridContent", true);		
		}
				
		return array(
			"container" => $buffer
			, "container_class" => $container_class
			, "container_properties" => ($hide_container ? $container_properties : array())
		);
    }

    function parse_col($grid_contents, $recordset_key, $rrow, $modify_url, $keys, $delete_url, $col, $col_count, $row = 1, $output_buffer = false)
    {
    	//$this->tpl[0]->set_var("SectCol", "");
		/**
		* set Col Class
		*/
		$col_class = array();
		
		$col_class["container"] = $this->column_class;
		
		if ($col == 1 && $this->column_class_first)
		    $col_class["first"] = $this->column_class_first;
		elseif ($col == $col_count && $this->column_class_last)
		    $col_class["last"] = $this->column_class_last;
		
		$col_class["cell"] = str_replace(array(
											"[COUNT]"
											, "[ROW]"
											, "[COL]"
										)
										, array(
											($row > 1 ? $row . "-" : "") . $col
											, $row
											, $col
										)
										, $this->cel_class); 
		
		/**
		* Set Grid Content
		*/

		if(is_array($grid_contents)) 
		{
			if(is_array($grid_contents) && count($grid_contents))
			{
				foreach($grid_contents AS $key => $params) 
				{
					$res = null;

					if($params["type"] == "field")
						$res = $this->parse_field($this->grid_fields[$key], $recordset_key, $modify_url, $keys, ($this->grid_disposition_elem["data"][$row - 1 ][$col - 1]["field"] > 1 && count($this->grid_disposition_elem["data"]) == 1 ? false : true), ($row > 1 ? true : false/*!$this->grid_fields[$key]->allow_order*/));
					elseif($params["type"] == "button") {
						if(isset($this->grid_buttons[$key]))
							$res = $this->parse_button($this->grid_buttons[$key], $recordset_key, $rrow, $modify_url["default"], ($this->grid_disposition_elem["data"][$row - 1 ][$col - 1 ]["button"] > 1 && count($this->grid_disposition_elem["data"]) == 1 ? false : true));
						elseif($key == "edit" && $this->display_edit_bt)
							$res = $this->parse_button($key, $recordset_key, $rrow, $modify_url["default"], ($this->grid_disposition_elem["data"][$row - 1 ][$col - 1 ]["button"] > 1 && count($this->grid_disposition_elem["data"]) == 1 ? false : true));
						elseif($key == "delete" && $this->display_delete_bt)
							$res = $this->parse_button($key, $recordset_key, $rrow, $delete_url, ($this->grid_disposition_elem["data"][$row - 1 ][$col - 1 ]["button"] > 1 && count($this->grid_disposition_elem["data"]) == 1 ? false : true));

					}

					$col_class["cell"] = str_replace("[ID]", $key, $col_class["cell"]);
					if(is_array($res)) {
						$col_class = array_replace($col_class, $res["container_class"]);
						$col_properties = $res["container_properties"];					
					}
				}
			}
		} 
		elseif (is_object($grid_contents) && is_subclass_of($grid_contents, "ffField_base"))
		{
			$field = $this->parse_field($grid_contents, $recordset_key, $modify_url, $keys);

			$col_class = array_replace($col_class, $field["container_class"]);
			$col_properties = $field["container_properties"];
		}
		elseif (is_object($grid_contents) &&  is_subclass_of($grid_contents, "ffButton_base"))
		{
			$button = $this->parse_button($grid_contents, $recordset_key, $rrow, $modify_url["default"]);
			$col_class = array_replace($col_class, $button["container_class"]);
			$col_properties = $button["container_properties"];
		}
		elseif($grid_contents == "edit") {
			$button = $this->parse_button($grid_contents, $recordset_key, $rrow, $modify_url["default"]);

			$col_class = array_replace($col_class, $button["container_class"]);
			$col_properties = $button["container_properties"];
		}
		elseif($grid_contents == "delete") {
			$button = $this->parse_button($grid_contents, $recordset_key, $rrow, $delete_url);

			$col_class = array_replace($col_class, $button["container_class"]);
			$col_properties = $button["container_properties"];
		}

		if($col == 1 && count($this->grid_disposition_elem["data"]) > 1) {
			$colspan = max($this->grid_disposition_elem["count"]) - $col_count;
			if($colspan)
				$col_properties["colspan"] = $colspan + 1;
		}	
		/**
		* Parse Col Vars
		*/
		$this->tpl[0]->set_var("col", $col); // Useful for Fixed Fields Templates
		if($this->tpl[0]->isset_var("col_class")) {
			$this->tpl[0]->set_var("col_class", implode(" " , array_filter($col_class)));
		} else {
			$col_properties["class"] = implode(" " , array_filter($col_class));
		}

		$this->tpl[0]->set_var("col_properties", $this->getProperties($col_properties));
		$this->tpl[0]->parse("SectColStart", false);
		$this->tpl[0]->parse("SectColEnd", false);

		if($output_buffer) 
		{
			$buffer = $this->tpl[0]->rpparse("SectCol", false);
		}
		else
		{
			$this->tpl[0]->parse("SectCol", true);
		}

		$this->tpl[0]->set_var("SectGridContent", "");    
		$this->tpl[0]->set_var("SectColStart", "");
		$this->tpl[0]->set_var("SectColEnd", "");
		
		return $buffer;
    }

    function parse_field_label($field, $col_class, $hide_container = true)
    {
        $buffer_icon = "";
		/**
		*  Set Label
		*/
     	if($field->display_label) {
			if ($field->label_encode_entities)
			    $buffer_label_value = ffCommon_specialchars($field->label);
			else
			    $buffer_label_value = $field->label;
		}

		/**
		*  Set Order
		*/
 		if ($this->order_method != "none" && $field->allow_order)
		{
			if ($this->order == $field->id) {  
				if ($this->direction == "ASC")
				        $direction = "ASC";
				    else
				        $direction = "DESC";
			} else {
				$direction = $field->order_dir; 
			}
			
            if($field->display_label /*&& $this->order_method*/ && $buffer_label_value)
            {                 
                $buffer_label = "<a href=\"javascript:void(0);\""
                                . " onclick=\"ff.ffGrid.ajaxOrder(this, '" .
                                    $this->getIDIF() . "', '" .
                                    $field->id . "'"
                                . ($_REQUEST["XHR_CTX_ID"]
                                    ? ", '" . $_REQUEST["XHR_CTX_ID"] . "'"
                                    : ""
                                ) . ");\""
                                . " class=\"ff-sort"
                                . ($this->order_default == $field->id
                                    ? " " . cm_getClassByFrameworkCss("current", "util", $this->label_selected_class)
                                    : ""
                                ) . "\">"
                                    . ($this->order_method == "labels" || $this->order_method == "both"
                                        ? $buffer_label_value
                                        : ""
                                    )
                                    . ($this->order_method == "icons" || $this->order_method == "both"
                                        ? cm_getClassByFrameworkCss("sort" . ($this->order_default == $field->id ? "-" . strtolower($direction) : ""), "icon-link-tag")
                                        : ""
                                    )
                            . "</a>";
            }
            
          /*  
			if ($field->display_label && ($this->order_method == "labels" || $this->order_method == "both"))
			{	            
                $buffer_label = "<a href=\"javascript:void(0);\" " 
                			. "onclick=\"ff.ffGrid.ajaxOrder('" . 
                				$this->getIDIF() . "', '" . 
                				$field->id . "', undefined" 
                				. ($_REQUEST["XHR_CTX_ID"]
                					? ", '" . $_REQUEST["XHR_CTX_ID"] . "'"
                					: ""
                				) . ");\">" . $buffer_label_value . "</a>";
			} 
			
			if ($this->order_method == "icons" || $this->order_method == "both")
			{
                $rev_direction = (strtoupper($direction) == "ASC"
                                    ? "DESC"
                                    : "ASC"
                                );
                $buffer_icon = "<a href=\"javascript:void(0);\" " 
                				. "onclick=\"ff.ffGrid.ajaxOrder('" . 
                					$this->getIDIF() . "', '" . 
                					$field->id . "', '" . $rev_direction . "'" 
                				. ($_REQUEST["XHR_CTX_ID"]
                					? ", '" . $_REQUEST["XHR_CTX_ID"] . "'"
                					: ""
                				) . ");\""
                				. ($this->order_default == $field->id
                					? " class=\"" .cm_getClassByFrameworkCss("current", "util", $this->label_selected_class)  . "\""
                					: ""
                				) . ">" . 
                					cm_getClassByFrameworkCss("sort" . ($this->order_default == $field->id ? "-" . strtolower($direction) : ""), "icon-link-tag")
                			. "</a>";
			}*/				
		} elseif($hide_container || !$field->display_label)
			$buffer_label = $buffer_label_value;


		/**
		* Set Field Select All
		*/
        if ($field->get_control_type() == "checkbox" && $field->display_label && !isset($field->properties["disabled"])) {
	        $buffer_select = "<input type=\"checkbox\" onclick=\"this.value = ff.ffGrid.turnToggle('" . $this->getIDIF() . "', '" . $field->id . "', this.value);\" value=\"0\" />";
		}            
		
		
		/**
		* Set Field Container
		*/
		$container_class["base"] = "ffField";
	    if($field->get_app_type() == "Text" && $field->extended_type != "String")
	        $container_class["type"] = strtolower($field->extended_type);
	    else 
	        $container_class["type"] = strtolower($field->get_app_type());
		
		if ($this->order == $field->id)
            $container_class["current"] = cm_getClassByFrameworkCss("current", "util", $this->label_selected_class);

		$container_properties = $field->container_properties;
		$container_properties["width"] = $field->width;         

	    if($this->tpl[0]->isset_var("col_class")) {
	        $this->tpl[0]->set_var("col_class", implode(" " , array_filter($col_class)));
	    }

		$buffer = $buffer_icon . $buffer_label . $buffer_select;
		if(!$hide_container) {
			$tmp_properties = $container_properties;
			$tmp_properties["class"] = implode(" ", array_filter($container_class));

			$buffer = '<div ' . $field->getProperties($tmp_properties) . '>' . $buffer . '</div>';
		}
        
        if (!$this->use_fixed_fields) {
            $this->tpl[0]->set_var("Label", $buffer);
            $this->tpl[0]->parse("SectGridLabel", true);
		} else {
			$this->tpl[0]->set_var("Label", $buffer_icon . $buffer_label . $buffer_select);
            $this->tpl[0]->parse("SectGridLabel" . $field->id, false);
		}
		
		return array(
			"icon" => $buffer_icon
			, "select" => $buffer_select
			, "label" => $buffer_label
			, "label_value" => $buffer_label_value
			, "container" => $buffer
			, "container_class" => ($hide_container ? $container_class : array())
			, "container_properties" => ($hide_container ? $container_properties : array())
		);
    }
    
    function parse_col_label($grid_contents, $col, $col_count, $row = 1) 
    {
    	//$this->tpl[0]->set_var("SectCol", "");
		/**
		* set Col Class
		*/
		$col_class = array();
		
		$col_class["container"] = $this->label_class;
		
		if ($col == 1 && $this->column_class_first)
		    $col_class["first"] = $this->column_class_first;
		elseif ($col == $col_count && $this->column_class_last)
		    $col_class["last"] = $this->column_class_last;
		
		$col_class["cell"] = "cel" . ($row > 1 ? "-" . $row : "") . "-" . $col; 
		$col_class["wrap"] = cm_getClassByFrameworkCss("text-nowrap", "util");
		/**
		* Set Class And Properties by Grid Content Type
		*/

		if(is_array($grid_contents)) 
		{
			if(is_array($grid_contents) && count($grid_contents))
			{
				foreach($grid_contents AS $key => $params) 
				{
					$res = null;
					if($params["type"] == "field") {
						$res = $this->parse_field_label($this->grid_fields[$key], $col_class, ($this->grid_disposition_elem["data"][$row - 1][$col - 1]["field"] > 1 ? true : true));
					} elseif($params["type"] == "button") {
						$res["container_class"]["base"] = "ffButton";
						$res["container_properties"] = array();
					}
					
					if(is_array($res)) {
						$col_class = array_replace($col_class, $res["container_class"]);
						$col_properties = $res["container_properties"];
					}
				}
			}
		} 
		elseif (is_object($grid_contents) && is_subclass_of($grid_contents, "ffField_base"))
		{
			$field = $this->parse_field_label($grid_contents, $col_class);

			$col_class = array_replace($col_class, $field["container_class"]);
			$col_properties = $field["container_properties"];
		}
		elseif (is_object($grid_contents) &&  is_subclass_of($grid_contents, "ffButton_base"))
		{
			$col_class["base"] = "ffButton";
		}
		elseif($grid_contents == "edit") {
			$col_class["base"] = "ffButton";
			$col_class["default"] = "edit";
		}
		elseif($grid_contents == "delete") {
			$col_class["base"] = "ffButton";
			$col_class["default"] = "delete";
		}
		
		
		/**
		* Parse Col Vars
		*/
		$this->tpl[0]->set_var("col", $col); // Useful for Fixed Fields Templates
		if($this->tpl[0]->isset_var("col_class")) {
			$this->tpl[0]->set_var("col_class", implode(" " , array_filter($col_class)));
		} else {
			$col_properties["class"] = implode(" " , array_filter($col_class));
		}

		$this->tpl[0]->set_var("col_properties", $this->getProperties($col_properties));
		
		
		$this->tpl[0]->parse("SectColLabelStart", false);
		$this->tpl[0]->parse("SectColLabelEnd", false);
		$this->tpl[0]->parse("SectColLabel", true);
		$this->tpl[0]->set_var("SectGridLabel", "");    
    }    
    /**
     *  Elabora le label della griglia
     */
    function process_labels($col_count = 0)
    {
        if (!$this->display_labels)
        {
            $this->tpl[0]->set_var("SectLabels", "");
            return;
        }

   /*     if (isset($_REQUEST["XHR_CTX_ID"]))
            $this->tpl[0]->set_var("dialogid", "'" . $_REQUEST["XHR_CTX_ID"] . "'");
        else
            $this->tpl[0]->set_var("dialogid", "undefined");*/

       // $this->tpl[0]->set_var("turn_on_label", ffCommon_specialchars($this->turn_on_label));
       // $this->tpl[0]->set_var("turn_off_label", ffCommon_specialchars($this->turn_off_label));

       // $tot_labels = 0;
       // $tot_labels += (is_array($this->grid_buttons) ? count($this->grid_buttons) : 0);
       // $tot_labels += (is_array($this->grid_fields) ? count($this->grid_fields) : 0);

		if(is_array($this->grid_disposition) && count($this->grid_disposition)) 
        {
            foreach($this->grid_disposition AS $row => $cols) 
            {
                if(is_array($cols) && count($cols)) 
                {
                    foreach($cols AS $col => $grid_contents)
                    {
                        $this->parse_col_label($grid_contents, $col + 1, count($cols), $row + 1);
                    }
                }
            
            }
        }
        else        
        {
	        $col = 1;  
	        foreach ($this->grid_fields as $key => $FormField)
	        {
	            $this->parse_col_label($FormField, $col, $col_count);
	            
	            $col++;
	        }
	        reset($this->grid_fields);

	        if (is_array($this->grid_buttons) && count($this->grid_buttons))
	        {
	            foreach($this->grid_buttons as $key => $FormButton)
	            {
		            $this->parse_col_label($FormButton, $col, $col_count);
		            
		            $col++;
	            }
	            reset($this->grid_buttons);
	        }
			if($this->display_edit_bt)
			{
				$this->parse_col_label(($this->visible_edit_bt ? "edit": ""), $col, $col_count);
				
				$col++;
			}						
			if($this->display_delete_bt)
			{
				$this->parse_col_label(($this->visible_delete_bt ? "delete": ""), $col, $col_count);
				
				$col++;
			}						
        }

		$this->tpl[0]->parse("SectLabels", false);
    }

    /**
     * Elabora il page navigator
     */
    function process_navigator()
    {
        $this->navigator[0]->parent[0] = $this;

        $this->navigator[0]->page = $this->page;
        $this->navigator[0]->records_per_page = $this->records_per_page;
        $this->navigator[0]->nav_selector_elements = $this->nav_selector_elements;
        if ($this->db[0]->query_id)
            $this->navigator[0]->num_rows = $this->db[0]->numRows();

        if (!$this->display_navigator || !$this->use_paging || ($this->navigator[0]->num_rows <= $this->navigator[0]->records_per_page && !($this->ajax_search || $this->full_ajax)))
        {
            $this->tpl[0]->set_var("SectHiddenPageNavigator", "");
            $this->tpl[0]->set_var("SectPageNavigator", "");
            $this->tpl[0]->set_var("SectPaginatorTop", "");
            $this->tpl[0]->set_var("SectPaginatorBottom", "");
            return;
        }
        $this->navigator[0]->doAjax = $this->navigator_doAjax;
        $this->navigator[0]->nav_display_selector = $this->navigator_display_selector;
        $this->tpl[0]->set_var("page_parname", $this->navigator[0]->page_parname);
        $this->tpl[0]->set_var("records_per_page_parname", $this->navigator[0]->records_per_page_parname);
        $this->tpl[0]->set_var("page_per_frame_parname", $this->navigator[0]->page_per_frame_parname);

        $this->tpl[0]->set_var("page_per_frame", $this->navigator[0]->PagePerFrame);

        /*if($this->navigator[0]->num_rows >  $this->navigator[0]->records_per_page)
        {*/
        
        $this->tpl[0]->set_var("PageNavigator", $this->navigator[0]->process());
        $this->tpl[0]->parse("SectPageNavigator", false);
    
        if ($this->navigator_orientation == "both" || $this->navigator_orientation == "top")
        {
        	$this->tpl[0]->set_var("pagenavigator_top_class", cm_getClassByDef($this->framework_css["navigatorTop"]));
            $this->tpl[0]->parse("SectPaginatorTop", false);
		} else
            $this->tpl[0]->set_var("SectPaginatorTop", "");

        if ($this->navigator_orientation == "both" || $this->navigator_orientation == "bottom")
        {
        	$this->tpl[0]->set_var("pagenavigator_bottom_class", cm_getClassByDef($this->framework_css["navigatorBottom"]));
            $this->tpl[0]->parse("SectPaginatorBottom", false);
		} else
        	$this->tpl[0]->set_var("SectPaginatorBottom", "");
        //}
        $this->tpl[0]->parse("SectHiddenPageNavigator", false);
    }

	/**
	*  Load Widget Dialog for Edit and Delete
	*/
    function load_dialog($name = null, $params = null)
    {
		static $dialog_loaded = array();

		if($name === null)
			$name = $this->record_id;

		if(array_key_exists($this->getIDIF() . "_" . $name, $dialog_loaded))
			return;		

		if($params === null)
			$params = array(
				"title" 				=> ffTemplate::_get_word_by_code("ffGrid_dialog_title") . " " . $this->title
				, "class" 				=> null
				, "width" 				=> null
				, "height" 				=> null
				, "type" 				=> null
			);
		
		if(!count($dialog_loaded))
			$this->parent[0]->widgetLoad("dialog");

		$this->parent[0]->widgets["dialog"]->process(
			$name 
			, array(
		            "title"          	=> $params["title"]
					, "tpl_id"        	=> $this->getIDIF()
					, "width"        	=> $params["width"]
					, "height"        	=> $params["height"]
					, "dialogClass"     => $params["class"]
					, "type"			=> $params["type"]
				)
			, $this->parent[0]
		);
    }
    
    
    /**
     * Inizializza i controlli di default della griglia
     */
    function initControls()
    {
        // PREPARE DEFAULT BUTTONS
        if ($this->buttons_options["search"]["display"])
        {
            //if($this->buttons_options["search"]["label"] === null) //RICHIESTA DEL GIOVINE
             //   $this->buttons_options["search"]["label"] = ffTemplate::_get_word_by_code("ffGrid_search");

            if ($this->buttons_options["search"]["obj"] !== null)
            {
                $this->addSearchButton(   $this->buttons_options["search"]["obj"]
                                        , $this->buttons_options["search"]["index"]);
            }
            else
            {
                $tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
                $tmp->id            = "search";
                $tmp->label         = $this->buttons_options["search"]["label"];
                $tmp->class         = $this->buttons_options["search"]["class"];
                $tmp->icon          = $this->buttons_options["search"]["icon"];
                $tmp->aspect        = $this->buttons_options["search"]["aspect"];
                $tmp->action_type   = "submit";
                $tmp->frmAction     = "search";
                $tmp->framework_css["addon"] = "postfix";

                if (isset($_REQUEST["XHR_CTX_ID"]))
                {
                    $tmp->jsaction = ($this->reset_page_on_search ? " jQuery('#" . $this->getPrefix() . $this->navigator[0]->page_parname . "').val('1'); " : "") . ($this->open_adv_search === true ? "" : " ff.ffGrid.advsearchHide('" . $this->getIDIF() . "'); ") . " ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "',{'action'    : '" . $this->getIDIF() . "_search','component' : '" . $this->getIDIF() . "','section'    : 'GridData'});";
                } 
                elseif ($this->full_ajax || $this->ajax_search)
                {
                    $this->parent[0]->tplAddJs("ff.ajax");

                    $tmp->jsaction = ($this->reset_page_on_search ? " jQuery('#" . $this->getPrefix() . $this->navigator[0]->page_parname . "').val('1'); " : "")  . ($this->open_adv_search === true ? "" : " ff.ffGrid.advsearchHide('" . $this->getIDIF() . "'); ") . " ff.ajax.doRequest({'component' : '" . $this->getIDIF() . "','section' : 'GridData'});";
                }
                $this->addSearchButton(   $tmp
                                        , $this->buttons_options["search"]["index"]);
            }
        }

        if ($this->buttons_options["export"]["display"])
        {
            if($this->buttons_options["export"]["label"] === null)
                $this->buttons_options["export"]["label"] = ffTemplate::_get_word_by_code("ffGrid_export");

            if ($this->buttons_options["export"]["obj"] !== null)
            {
                $this->addActionButtonHeader($this->buttons_options["export"]["obj"]);
            }
            else
            {
                $tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
                $tmp->id            = "export";
                $tmp->label         = $this->buttons_options["export"]["label"];
                $tmp->class         = $this->buttons_options["export"]["class"];
                $tmp->icon          = $this->buttons_options["export"]["icon"];
                $tmp->aspect        = $this->buttons_options["export"]["aspect"];
                $tmp->action_type   = "submit";
                $tmp->frmAction     = "export";
                //$tmp->aspect         = "link";
                //$tmp->action_type     = "gotourl";
                //$tmp->form_action_url = $this->parent[0]->getRequestUri() . "&" . $this->getIDIF() . "_t=xls";
                //$tmp->jsaction = "ff.ajax.doRequest({'component' : '" . $this->getIDIF() . "', 'addFields' : '" . $this->getIDIF() . "t=xls'});";
                //$tmp->class         .= "noactivebuttons";
                //$tmp->url = $this->parent[0]->getRequestUri() . "&" . $this->getIDIF() . "_t=xls";
                $this->addActionButtonHeader($tmp);
            }
        }
    }
    
	public function structProcess($tpl)
    {
		$rc = 0;
		
        if ($this->ajax_update_all)
        {
			$rc++;
            $tpl->set_var("prop_name",    "update_all");
            $tpl->set_var("prop_value",    "true");
            $tpl->parse("SectFFObjProperty",    true);
        }
		
		if ($this->id_if !== null)
		{
			$rc++;
            $tpl->set_var("prop_name",    "factory_id");
            $tpl->set_var("prop_value",   '"' . $this->id . '"');
            $tpl->parse("SectFFObjProperty",    true);
		}
		
		return $rc;
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
	
	function setWidthDialog($width, $action = null) 
	{
		if(is_numeric($width))
			$prop = "width";
		else {
			$prop = "class";
			$width = cm_getClassByFrameworkCss("window-" . $width, "dialog");
		}
		if($action) {
			$this->dialog_options[$action][$prop] 	= $width;
		} else {
			$this->dialog_options["addnew"][$prop] 	= $width;
			$this->dialog_options["edit"][$prop] 	= $width;
			$this->dialog_options["delete"][$prop] 	= $width;
		}
	}
	function setTitleDialog($title, $action = null) 
	{
		if($action) {
			$this->dialog_options[$action]["title"] 	= $title;
		} else {
			$this->dialog_options["addnew"]["title"] 	= ffTemplate::_get_word_by_code("addnew") . ": " . $title;
			$this->dialog_options["edit"]["title"] 		= ffTemplate::_get_word_by_code("modify") . ": " . $title;
			$this->dialog_options["delete"]["title"] 	= ffTemplate::_get_word_by_code("delete") . ": " . $title;
		}
	}
	function setTitle($title, $class = null) 
	{
		$this->framework_css["title"]["class"] = $class;
		$this->title = $title;
	}
}
