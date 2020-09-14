<?php
/*
    CLASSE IN BETA NON USARE
*/
class ffGrid_xls extends ffGrid_base 
{
	var $framework_css = array(); 

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
    /**
     * una classe da associare alla riga
     * @var String
     */
    var $row_class        = "";
    
    var $pagination_save_memory = false;

    /**
     * Un elenco di classi da associare alle righe della grid, ciclate in sequenza
     * @var Array
     */
    var $switch_row_class =  array(
                                "display" => false
                                , "first" => "odd"
                                , "second" => "even"
                            );

	var $id_if					= null;                            
	
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
     * La classe da assegnare alla prima label di ordinamento quando selezionata
     * @var String
     */
    var $label_selected_class_first    = "";
    /**
     * La classe da assegnare all'ultima label di ordinamento quando selezionata
     * @var String
     */
    var $label_selected_class_last    = "";

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
                                            );
    var $open_adv_search = false;    
    var $csv_start_row		        	= 2000; 
    var $csv_field_sep		        	= ";"; /*AVEVO DEI PROBLEMI DI FORMATTAZIONE CON "\t"*/
    var $csv_field_caps		        	= '"';
    var $csv_row_sep		        	= "\n";
    
	var $raw_values						= false;
	
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

    private $parsed_fields         = 0;
    
    private $objPHPExcel = null;
    private $objWriter = null;
    
    /**
     * Il costruttore dell'oggetto
     * da non richiamare direttamente
     * @param ffPage_base $page
     * @param String $disk_path
     * @param String $theme 
     */
    function __construct(ffPage_base $page, $disk_path, $theme)
    {
        set_time_limit(0);

        parent::__construct($page, $disk_path, $theme);

        if ($this->display_search_simple)
        {
            if ($this->search_simple_field_options["obj"] !== null)
            {
                $this->addSearchField($this->search_simple_field_options["obj"]);
            }
            else
            {
                $tmp = ffField::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
                $tmp->id                = $this->search_simple_field_options["id"];
                $tmp->label                = $this->search_simple_field_options["label"];
                $tmp->src_operation        = $this->search_simple_field_options["src_operation"];
                $tmp->src_prefix        = $this->search_simple_field_options["src_prefix"];
                $tmp->src_postfix        = $this->search_simple_field_options["src_postfix"];
                $tmp->data_type            = "";
                $tmp->display    = false;
                $this->addSearchField($tmp);
            }
        }
        
		if(!class_exists("PHPExcel") && file_exists(FF_DISK_PATH . "/library/PHPexcel/class.PHPexcel.php"))
            require_once(FF_DISK_PATH . "/library/PHPexcel/class.PHPexcel.php");
		
		$this->objPHPExcel = new PHPExcel();
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
        $this->use_paging = false;
        //$this->use_search = false;
        //$this->use_alpha = false;
        $this->display_actions = false;
        
        //$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
        //$this->tpl[0]->load_file($this->template_file, "main");
//        $this->tpl[0]->set_var("fixed_pre_content", $this->fixed_pre_content);
//        $this->tpl[0]->set_var("fixed_post_content", $this->fixed_post_content);
//        
//        $this->tpl[0]->set_var("fixed_title_content", $this->fixed_title_content);
//        $this->tpl[0]->set_var("fixed_heading_content", $this->fixed_heading_content);
        
//        $this->tpl[0]->set_var("SectHiddenField", "");

        if(strlen($this->title)) {
        	$this->objPHPExcel->getActiveSheet()->setTitle(ffCommon_specialchars(substr($this->title, 0, 30)));
		}

        if (is_array($this->fixed_vars) && count($this->fixed_vars))
        {
            foreach ($this->fixed_vars as $key => $value)
            {
                $this->tpl[0]->set_var($key, $value);
            }
            reset($this->fixed_vars);
        }

        $res = $this->doEvent("on_load_template", array(&$this, $this->objPHPExcel));
    }
	function process_headers()
	{
		return;
	}

	function process_footers()
	{
		return;
	}
    /**
     * Elabora il template
     * @param Boolean $output_result se dev'essere visualizzata immediatamente l'elaborazione
     * @return Mixed il risultato dell'operazione
     */
    public function tplParse($output_result)
    {
        $res = ffGrid::doEvent("on_tplParse", array($this, $this->objPHPExcel), $output_result);
        $res = $this->doEvent("on_tplParse", array($this, $this->objPHPExcel), $output_result);
        
        $this->objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        if ($this->parent[0]->getXHRComponent() == $this->id)
        {
            if (isset($_REQUEST["XHR_SECTION"])) {
                header('Content-Type: application/vnd.ms-excel');
                header('Cache-Control: max-age=0');

                $this->objWriter->save('php://output');
                exit;
            }
        }
        
        if ($output_result === false)
        {
            if(strlen($this->title)) {
                $filename = ffCommon_url_rewrite($this->title);
            } else {
                $filename = ffCommon_url_rewrite($this->id);
            }
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
            header('Cache-Control: max-age=0');

            $this->objWriter->save('php://output');
            exit;
        }
        elseif ($output_result === true)
        {
            ob_start();
            $this->objWriter->save('php://output');
            return ob_get_clean();            
        }
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
                if ($this->grid_fields[$key]->data_type == "")
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
     * Elabora la sezione di ricerca
     */
    function process_search()
    {
        //parent::process_search();
    }
    
    /**
     * Esegue il processing vero e proprio dei contenuti della grid
     */
    public function process_grid()
    {
        if ($this->display_grid == "never" || ($this->display_grid == "search" && !strlen($this->searched)))
        {
            return;
        }
        parent::process_grid();

        // Manage various buttons
        $totfields = count($this->grid_fields);

        if ($this->db[0]->query_id && $this->db[0]->nextRecord())
        {
        	
			if($this->db[0]->numRows() > $this->csv_start_row) 
			{
				//$handle = tmpfile(); 
				$export = "";

				if(is_array($this->db[0]->fields) && count($this->db[0]->fields)) {
					foreach($this->db[0]->fields AS $key => $value) {
						if(strlen($key)) {
							if(strlen($export))
								$export .= ";";/*AVEVO DEI PROBLEMI DI FORMATTAZIONE CON "\t"*/
							
							$export .= $this->csv_field_caps . $key . $this->csv_field_caps;	
						}
					}
				}

				do
				{
					$sub_export = "";

					if(is_array($this->db[0]->fields) && count($this->db[0]->fields)) {
						foreach($this->db[0]->fields AS $key => $value) {
							if(strlen($key)) {
								if(strlen($sub_export))
									$sub_export .= $this->csv_field_sep;
								
								$value = $this->db[0]->getField($key, "Text", true);
								
								if(strlen($this->csv_field_caps)) {
									$value = str_replace($this->csv_field_caps, "", $value);	
								} else {
									$value = str_replace($this->csv_field_sep, "", $value);
								}
								
								$value = str_replace($this->csv_row_sep, "", $value);
								$value = str_replace("\r", "", $value);

								$value = $this->csv_field_caps . $value . $this->csv_field_caps;	
								$sub_export .= $value;
							}
						}
					}
					
					$export .= $sub_export . $this->csv_row_sep;

				} while ($this->db[0]->nextRecord());

				//fwrite($handle, $export);
				//rewind($handle);

	            if(strlen($this->title)) {
	                $filename = ffCommon_url_rewrite($this->title);
	            } else {
	                $filename = ffCommon_url_rewrite($this->id);
	            }

	            header('Content-Type: text/csv');
	            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
	            header('Cache-Control: max-age=0');

				echo $export;

				//fpassthru($handle);
				exit;
			}
        	
            $i = 1;
            if($this->display_labels)
                $i++;

            do
            {
                $res = $this->doEvent("on_load_row", array(&$this, $this->db[0], $i));
                
                /* Step 1: retrieve values (done in 2 steps due to events needs) */
                if (count($this->key_fields))
                {
                    // find global recordset corrispondency (if one)
                    $aKeys = array();
                    foreach($this->key_fields as $key => $FormField)
                    {
                        $this->key_fields[$key]->value = $this->db[0]->getField($this->key_fields[$key]->get_data_source(), $this->key_fields[$key]->base_type);
                        $aKeys[$key] = $this->key_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE);
                    }
                    reset($this->key_fields);

                    $recordset_key = array_search($aKeys, $this->recordset_keys);
                    // if not exists, add new
                    if ($recordset_key === false)
                    {
                        $recordset_key = count($this->recordset_keys);
                        $this->recordset_keys[$recordset_key] = $aKeys;
                    }
                    $this->displayed_keys[$recordset_key] = $aKeys;
                }

                foreach ($this->grid_fields as $key => $FormField)
                {
                    if ($this->use_fields_params && $this->grid_fields[$key]->control_type != "" && isset($this->recordset_values[$recordset_key][$key]))
                    {
                        $this->grid_fields[$key]->value = new ffData($this->recordset_values[$recordset_key][$key], $this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
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
                    }
                }
                reset($this->grid_fields);

				parent::processRow();
				
                /* Step 2: display values */

                // EVENT HANDLER
                $res = $this->doEvent("on_before_parse_row", array(&$this, $i));
                $rc = end($res);

                if ($rc === null)
                {
                    $col = 1;
                    
                    foreach ($this->grid_fields as $key => $FormField)
                    {
                        $buffer = "";
                        if (
                                $this->grid_fields[$key]->display === false
                                /*|| (
                                    $this->use_fixed_fields == true
                                    && $this->grid_fields[$key]->skip_if_empty
                                    && $this->grid_fields[$key]->value->ori_value == ""
                                )*/
                            )
                            continue;

                        if (!is_subclass_of($this->grid_fields[$key], "ffField_base"))
                            ffErrorHandler::raise("Wrong Grid Field: must be a ffField", E_USER_ERROR, $this, get_defined_vars());

                        //---------------------------------------------------------------------
                        //---------------------------------------------------------------------
                        
                        //---------------------------------------------------------------------
                        //---------------------------------------------------------------------
                        
						if ($this->raw_values)
						{
							$this->grid_fields[$key]->pre_process(true);
							switch($this->grid_fields[$key]->base_type) {
								case "Number":
									$buffer = $this->grid_fields[$key]->getValue("Number", FF_SYSTEM_LOCALE);
									$this->objPHPExcel->setActiveSheetIndex(0)->getCell(ffCommon_colNumber2Letter($col) . $i)->setValueExplicit($buffer, PHPExcel_Cell_DataType::TYPE_NUMERIC);
									if ($this->grid_fields[$key]->app_type === "Currency")
										$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SYMBOL);
									else
										$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
									break;
								case "Date":
									$tmp = $this->grid_fields[$key]->value;
									$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . $i, PHPExcel_Shared_Date::FormattedPHPToExcel($tmp->value_date_year, $tmp->value_date_month, $tmp->value_date_day));
									$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
									break;
								case "DateTime":
									$tmp = $this->grid_fields[$key]->value;
									$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . $i, PHPExcel_Shared_Date::FormattedPHPToExcel($tmp->value_date_year, $tmp->value_date_month, $tmp->value_date_day, $tmp->value_date_hours, $tmp->value_date_minutes, $tmp->value_date_seconds));
									$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
									break;
								default:
									$buffer = $this->grid_fields[$key]->fixed_pre_content . $this->grid_fields[$key]->getDisplayValue() . $this->grid_fields[$key]->fixed_post_content;
									$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . $i, $this->normalizeTag($buffer));
									$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getAlignment()->setWrapText(true);
							}
						}
						else
						{
							if(strlen($this->symbol_valuta) && $this->grid_fields[$key]->app_type == "Currency")
								$symbol_valuta = " " . $this->symbol_valuta;
							else 
								$symbol_valuta = "";

							$this->grid_fields[$key]->pre_process(true);
							$buffer = $this->normalizeTag(
									$this->grid_fields[$key]->fixed_pre_content 
									. $symbol_valuta 
									. $this->grid_fields[$key]->getDisplayValue() 
									. $this->grid_fields[$key]->fixed_post_content
								);

							$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . $i, $buffer);
							$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getAlignment()->setWrapText(true);
						}
						
						$this->objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$this->objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(ffCommon_colNumber2Letter($col))->setAutoSize(true);
                        
                        //$this->objPHPExcel->setActiveSheetIndex(0)->getRowDimension($i)->setRowHeight(-1);
                        //ffErrorHandler::raise("DEBUG", E_USER_ERROR, $this, get_defined_vars());
                        $col++;
                    }
                    reset($this->grid_fields);

                    $i++;

                    $res = $this->doEvent("on_before_parse_record", array(&$this));
                    $rc = end($res);
                }
            } while ($this->db[0]->nextRecord());
        }

        $res = $this->doEvent("on_after_process_grid", array(&$this, $this->objPHPExcel));
    }

    function normalizeTag($buffer) {
        $res = html_entity_decode($buffer, ENT_COMPAT, "UTF-8");
        $res = str_replace("</div>", "</div><br />", $buffer);
        $res = str_replace("</span>", "</span> ", $res);
        $res = str_replace("</label>", "</label> ", $res);
        $res = str_replace("&#039;", "'", $res);
        $res = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $res);
		$res = html_entity_decode($res);
		
        $res = strip_tags($res);
		$res = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',$res);
        $res = trim($res);
		
        return $res;
    }
    /**
     *  Elabora le label della griglia
     */
    function process_labels()
    {
        if (!$this->display_labels)
        {
            return;
        }

        $tot_labels = 0;
        $tot_labels += (is_array($this->grid_buttons) ? count($this->grid_buttons) : 0);
        $tot_labels += (is_array($this->grid_fields) ? count($this->grid_fields) : 0);

        $selected_class = $this->label_selected_class;
        $selected_class_f = ($this->label_selected_class_first ? $this->label_selected_class_first : $selected_class);
        $selected_class_l = ($this->label_selected_class_last ? $this->label_selected_class_last : $selected_class);
 
        $col = 1;

        foreach ($this->grid_fields as $key => $FormField)
        {
            if ($this->grid_fields[$key]->display === false)
                continue;

            if($this->grid_fields[$key]->display_label) {
                $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . "1", $this->grid_fields[$key]->label);
            }

            $col++;
        }
        reset($this->grid_fields);
    }

    /**
     * Elabora il page navigator
     */
    function process_navigator()
    {
    }

    /**
     * Inizializza i controlli di default della griglia
     */
    function initControls()
    {
    }
	
	public function structProcess($tpl)
	{
	}
	
	function setWidthComponent($resolution_large_to_small) 
	{
	}
	
	function setWidthDialog($width, $action = null) 
	{
	}
	function setTitleDialog($title, $action = null) 
	{
	}
	function setTitle($title, $class = null) 
	{
	}
}
