<?php
class ffPageNavigator_html extends ffPageNavigator_base
{
	var $framework_css					= array(
											"component" => array(
												"class" => "pageNavigator" 
												, "pagination" => "align-center"
											)
											, "pagination" => array(
                                               "def" => array(
                                                   "class" => null
                                                    , "col" => array(
                                                            "xs" => 12
                                                            , "sm" => 10
                                                            , "md" => 12
                                                            , "lg" => 6
                                                    )
                                                )
                                                , "menu" => array(
                                                    "class" => "pages"
                                                    , "pagination" => "pages"
                                                )
											)
											, "choice" => array(
												"class" => null
												, "col" => array(
														"xs" => 0
														, "sm" => 0
														, "md" => 5
														, "lg" => 2
												)
											)
											, "totelem" => array(
												"class" => null
												, "col" => array(
														"xs" => 0
														, "sm" => 2
														, "md" => 3
														, "lg" => 2
												)											
											)
											, "perPage" => array(
												"class" => null
												, "col" => array(
														"xs" => 0
														, "sm" => 0
														, "md" => 4
														, "lg" => 2
												)
											)
	
	);
	
	var $id_if = null;
	var $prefix = null;
	/**
	 * Determina se le azioni devono essere eseguite con richieste Ajax
	 * @var Boolean
	 */
	var $doAjax = true;

	function getIDIF()
	{
		if ($this->id_if !== null)
			return $this->id_if;
		else
			return $this->id;
	}

	function getPrefix($tmp = null)
	{
		if($this->prefix === null) {
			if($tmp === null)
				$tmp = $this->getIDIF();

			if (strlen($tmp))
				return $tmp . "_";
		} else {
			return $this->prefix;
		}
	}

	var $url = null;
    var $callback = "null";
    var $callback_params = "{}";
    var $infinite = false;

	/**
	 * Carica il template nell'oggetto $tpl
	 */
	public function tplLoad()
	{
		$this->tpl[0] = ffTemplate::factory($this->getTemplateDir());
		$this->tpl[0]->load_file($this->template_file, "main");

        if($this->id !== null)
            $id = $this->id;
		elseif ($this->parent !== NULL && strlen($this->parent[0]->id))
            $id = $this->parent[0]->id;		

        $this->tpl[0]->set_var("XHRcomponent", $id);
        $this->tpl[0]->set_var("prefix", $this->getPrefix($id));

		$this->tpl[0]->set_var("site_path", $this->site_path);
		$this->tpl[0]->set_var("page_path", $this->page_path);
		$this->tpl[0]->set_var("theme", $this->getTheme());
		
		$this->tpl[0]->set_var("form_action", $this->form_action);
		$this->tpl[0]->set_var("form_name", $this->form_name);

		if (is_array($this->fixed_vars) && count($this->fixed_vars))
		{
			foreach ($this->fixed_vars as $key => $value)
			{
				$this->tpl[0]->set_var($key, $value);
			}
			reset($this->fixed_vars);
		}
		
		return $id;
	}

	/**
	 * Esegue il parsing del template
	 * @param Boolean $output_result se true visualizza a video il risultato del processing, se false restituisce il contenuto del processing
	 * @return Mixed può essere string o true, a seconda di output_result
	 */
	public function tplParse($output_result)
	{
		// determina se è stand-alone o attaccato ad una page/componente
		if ($this->parent === NULL)
		{
			$this->process_headers();
			$this->process_footers();
		}

		if ($output_result)
		{
			$this->tpl[0]->pparse("main", false);
			return true;
		}
		else
		{
			return $this->tpl[0]->rpparse("main", false);
		}
	}

	function process_headers($stand_alone = false)
	{
		if($this->oPage !== NULL)
			$this->oPage[0]->tplAddJs("ff.ffPageNavigator");

		if (!isset($this->tpl[0]))
			return;
 
		if ($this->parent === NULL)
			$this->tpl[0]->parse("SectHeaders", false);
		else
		{
			return $this->tpl[0]->rpparse("SectHeaders", false);
		}
	}

	function process_footers($stand_alone = false)
	{
		if (!isset($this->tpl[0]))
			return;

		if ($this->parent === NULL)
			$this->tpl[0]->parse("SectFooters", false);
		else
		{
			return $this->tpl[0]->rpparse("SectFooters", false);
		}
	}
	
	/**
	 * process è la funzione di elaborazione principale dell'oggetto
	 * @param Boolean $output_result se true visualizza a video il risultato del processing, se false restituisce il contenuto del processing
	 * @return Mixed può essere string o true, a seconda di output_result
	 */
	function process($output_result = FALSE)
	{
		$id_component = $this->tplLoad();
		$navigator_class["default"] = $id_component . "-pn";
        $pageparname = $this->getPrefix($id_component) . $this->page_parname;
		$current_class = cm_getClassByFrameworkCss("current", "pagination");
		$loader_class = cm_getClassByFrameworkCss("spinner", "icon", "spin");
		$totpage = ceil($this->num_rows / $this->records_per_page);
		
		$navigator_properties["page"] = 'data-page="' . $this->page . '"';
		
		if ($this->infinite) {
			if(is_bool($this->infinite))
				$this->infinite = "next";

			$this->with_totelem = false;
			$this->with_choice = false;
			$this->display_first = false;
			$this->display_prev = ($this->infinite === "prev" && $this->page > 1 ? true : false);
			$this->display_next = ($this->infinite === "next" && (!$totpage || $this->page != $totpage) ? true : false);
			$this->display_last = false;
			$this->with_frames = false;
			
			$this->tpl[0]->set_var("infinite", "true");
			$this->tpl[0]->set_var("SectNoInfinite", "");
		} else {
			if ($this->page > $totpage)
				$this->page = $totpage;

			$this->tpl[0]->set_var("records_per_page_parname", $this->records_per_page_parname);
			$this->tpl[0]->set_var("selected_records_per_page", $this->records_per_page);
			$this->tpl[0]->set_var("page_per_frame", $this->PagePerFrame);
			
			$navigator_properties["totrec"] = 'data-totrec="' . $this->num_rows . '"';

			$this->tpl[0]->set_var("infinite", "false");
			$this->tpl[0]->parse("SectNoInfinite", false);
		}

		if ($this->page < 1)
			$this->page = 1;

		if($this->infinite)
			$navigator_class["default"] .= "-" . $this->page . " " . $this->infinite;

		if ($this->doAjax)
			$this->tpl[0]->set_var("doAjax", "true");
		else
			$this->tpl[0]->set_var("doAjax", "false");		
			
        $this->tpl[0]->set_var("callback", $this->callback);
        $this->tpl[0]->set_var("callback_params", $this->callback_params);

		$this->tpl[0]->set_var("component_class", cm_getClassByDef($this->framework_css["component"], $navigator_class));
		$this->tpl[0]->set_var("component_properties", implode(" ", $navigator_properties));
		$this->tpl[0]->set_var("page_parname", $this->page_parname);
		$this->tpl[0]->set_var("current_class", $current_class);
		//$this->tpl[0]->set_var("loader_class", $loader_class);
		
		if(!$this->with_totelem) {
            if(is_array($this->framework_css["pagination"]["def"]["col"]) && count($this->framework_css["pagination"]["def"]["col"])) {
                foreach($this->framework_css["pagination"]["def"]["col"] AS $col_key => $col_value) {
                    $this->framework_css["pagination"]["def"]["col"][$col_key] = $this->framework_css["pagination"]["def"]["col"][$col_key] + $this->framework_css["totelem"]["col"][$col_key];
                    if($this->framework_css["pagination"]["def"]["col"][$col_key] > 12)
                        $this->framework_css["pagination"]["def"]["col"][$col_key] = 12;
                }
            }
            
			//$this->tpl[0]->set_var("SectTotElem", "");
		}
				
        if(!$totpage || !$this->with_choice) 
        {
            if(is_array($this->framework_css["pagination"]["def"]["col"]) && count($this->framework_css["pagination"]["def"]["col"])) {
                foreach($this->framework_css["pagination"]["def"]["col"] AS $col_key => $col_value) {
                    $this->framework_css["pagination"]["def"]["col"][$col_key] = $this->framework_css["pagination"]["def"]["col"][$col_key] + $this->framework_css["choice"]["col"][$col_key];
                    if($this->framework_css["pagination"]["def"]["col"][$col_key] > 12)
                        $this->framework_css["pagination"]["def"]["col"][$col_key] = 12;
                }
            }            
        }
        
		if($totpage > 1) 
		{
			if($this->with_choice)
			{
				$choice_class["default"] = "choice";
				$choice_class["pages"] = cm_getClassByFrameworkCss("pages", "pagination");
				$this->tpl[0]->set_var("choice_class", cm_getClassByDef($this->framework_css["choice"], $choice_class));
				$this->tpl[0]->set_var("choice_box_class", cm_getClassByFrameworkCss("group", "form"));

				$buffer_choice_label = '<span class="' . cm_getClassByFrameworkCss("control-prefix", "form") . '">' . ffTemplate::_get_word_by_code("go_to_page") . '</span>';
				$buffer_choice_tot_page = '<span class="' . cm_getClassByFrameworkCss("control-postfix", "form") . '">' . ffTemplate::_get_word_by_code("of") . ' <span class="totpage">' . $totpage . '</span></span>';
				
				$wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
				if($wrap_addon) {
					$buffer_choice_label = '<div class="' . cm_getClassByFrameworkCss(array(3), "col") . '">' . $buffer_choice_label . '</div>';
					$buffer_choice_tot_page = '<div class="' . cm_getClassByFrameworkCss(array(5), "col") . '">' . $buffer_choice_tot_page . '</div>';
					$this->tpl[0]->set_var("choice_input_box_class", cm_getClassByFrameworkCss(array(4), "col"));
					$this->tpl[0]->parse("SectChoiceInputStart", false);
					$this->tpl[0]->parse("SectChoiceInputEnd", false);
				}
				$this->tpl[0]->set_var("current_page", $this->page);
				$this->tpl[0]->set_var("choice_input_class", "currentpage " . cm_getClassByFrameworkCss("control", "form") . " " . cm_getClassByFrameworkCss("align-right", "util"));
				$this->tpl[0]->set_var("choice_label", $buffer_choice_label);
				$this->tpl[0]->set_var("choice_tot_page", $buffer_choice_tot_page);
				
				$this->tpl[0]->parse("SectChoice", false);
			}
			if($this->display_first)
	        {
				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($this->page <= 2)
					$containerClass[] = "hidden";
				
				if(count($containerClass)) 
					$this->tpl[0]->set_var("first_arrows_class", ' class="' . implode(" " , $containerClass) . '"');

				$this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, false, $this->url));
	            $this->tpl[0]->set_var("first_class", cm_getClassByFrameworkCss("first", "icon"));
	            //$this->tpl[0]->set_var("first_icon", cm_getClassByFrameworkCss("nav_first", "ico-link-tag"));
				$this->tpl[0]->parse("SectFirstButton", false);
			}

			if($this->display_prev) 
	        {
				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($this->page == 1)
					$containerClass[] = "hidden";
				
				if(count($containerClass)) 
					$this->tpl[0]->set_var("prev_arrows_class", ' class="' . implode(" " , $containerClass) . '"');
				
				$this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, ($this->page == 1 ? $totpage : ($this->page) == 2 ? false :  $this->page - 1), $this->url));

				if($this->infinite)
					$this->tpl[0]->set_var("prev_class", $loader_class . " prev");
				else
		            $this->tpl[0]->set_var("prev_class", cm_getClassByFrameworkCss("prev", "icon"));
	            //$this->tpl[0]->set_var("prev_icon", cm_getClassByFrameworkCss("nav_prev", "ico-link-tag"));
				$this->tpl[0]->parse("SectPrevButton", false);
			}
			
			if($this->display_next) 
	        {
				$page_inject = "pinject";
				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($this->page == $totpage)
					$containerClass[] = "hidden";

				if($this->infinite) {
					$this->tpl[0]->set_var("next_class", $loader_class . " next");
				} else {
					$containerClass[] = $page_inject;

            		$this->tpl[0]->set_var("next_class", cm_getClassByFrameworkCss("next", "icon"));
				}
				if(count($containerClass))
					$this->tpl[0]->set_var("next_arrows_class", ' class="' . implode(" " , $containerClass) . '"');

				$this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, ($this->page == $totpage ? false : $this->page + 1), $this->url));
				
	            //$this->tpl[0]->set_var("next_icon", cm_getClassByFrameworkCss("play", "ico-link-tag"));
				$this->tpl[0]->parse("SectNextButton", false);
			}

			if($this->display_last)
	        {
				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($totpage - $this->page <= 2)
					$containerClass[] = "hidden";

				if(!$page_inject) {
        			$page_inject = "pinject";			
					$containerClass[] = $page_inject;
				}
				
				if(count($containerClass)) 
					$this->tpl[0]->set_var("last_arrows_class", ' class="' . implode(" " , $containerClass) . '"');
        		
				$this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, $totpage, $this->url));
	            $this->tpl[0]->set_var("last_class", cm_getClassByFrameworkCss("last", "icon"));
	            //$this->tpl[0]->set_var("last_icon", cm_getClassByFrameworkCss("nav_last", "ico-link-tag"));
				$this->tpl[0]->parse("SectLastButton", false);
			} 
			
			if ($this->with_frames)
			{
				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($this->page == 1)
					$containerClass[] = "hidden";
				
				if(count($containerClass)) 
					$this->tpl[0]->set_var("first_frame_arrows_class", ' class="' . implode(" " , $containerClass) . '"');

		        $this->tpl[0]->set_var("prevframe_class", cm_getClassByFrameworkCss("prev-frame", "icon"));
				$this->tpl[0]->parse("SectPrevFrameButton", false);

				$containerClass = array();
				//$containerClass[] = $arrows_class;
				if($totpage == $this->page)
					$containerClass[] = "hidden";

				if(!$page_inject) {
        			$page_inject = "pinject";			
					$containerClass[] = $page_inject;
				}			
				
				if(count($containerClass)) 
					$this->tpl[0]->set_var("last_frame_arrows_class", ' class="' . implode(" " , $containerClass) . '"');
	            
	            $this->tpl[0]->set_var("nextframe_class", cm_getClassByFrameworkCss("next-frame", "icon"));
				$this->tpl[0]->parse("SectNextFrameButton", false);
			}

				
			if(!$this->infinite)
			{
				if($totpage > 1)
					$this->process_selector($current_class); // do at last so variables have the correct values

		        if ($totpage > $this->PagePerFrame) {
		            $start_page = $this->page - floor($this->PagePerFrame / 2);
		            if ($start_page < 1)
		                $start_page = 1;

		            $end_page = $start_page + $this->PagePerFrame - 1;
		            if ($end_page > $totpage)
		                $end_page = $totpage;

		            $start_page = $end_page - $this->PagePerFrame + 1;
		        } else {
		            $start_page = 1;
		            $end_page = $totpage;
		        }

		        if($end_page > $start_page) {
                    $lastNum = ceil(($end_page - $start_page) / $this->PagePerFrame);

					$start = ($start_page - $lastNum > 0
						? $start_page - $lastNum
						: $start_page
					);
					$end = ($this->page < $totpage - $lastNum
						? $end_page - $lastNum
						: $end_page
					);

					$step = floor($start_page / $lastNum) - 1;
					if($start != $start_page) {
						for($i = 1; $i <= $lastNum; $i++)
						{
							$value = $step * $i;
							if($totpage >= $value) {
								if($value == $this->page) {
				                    $this->tpl[0]->set_var("page_class", ' class="' . $current_class . '"');
				                } else {
				                    $this->tpl[0]->set_var("page_class", "");
				                }
			                    $this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, $value, $this->url));
				                $this->tpl[0]->set_var("num_page", $value);
				                $this->tpl[0]->parse("SectPageButton", true);
			                }
						}
					}

					
		            for ($i = $start; $i <= $end; $i++) {
		                if($i == $this->page) {
		                    $this->tpl[0]->set_var("page_class", ' class="' . $current_class . '"');
		                } else {
		                    $this->tpl[0]->set_var("page_class", "");
		                }

						$this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, ($i > 1 ? $i : false), $this->url));
		                $this->tpl[0]->set_var("num_page", $i);
		                $this->tpl[0]->parse("SectPageButton", true);
		            }
		            if($end != $end_page) 
		            {
						$step = floor(($totpage - ($end_page - $lastNum)) / $lastNum);
						for($i = 1; $i <= $lastNum; $i++) 
						{	
							$value = ($end_page - $lastNum) + ($i * $step); 
							if($totpage >= $value) {
								if($value == $this->page) {
				                    $this->tpl[0]->set_var("page_class", ' class="' . $current_class . '"');
				                } else {
				                    $this->tpl[0]->set_var("page_class", "");
				                }					
			                    $this->tpl[0]->set_var("url", ffUpdateQueryString($pageparname, $value, $this->url));
				                $this->tpl[0]->set_var("num_page", $value);
				                $this->tpl[0]->parse("SectPageButton", true);
			                }			
						}
					}		            
		            
		        }
			}

			$this->tpl[0]->set_var("pagination_class", cm_getClassByDef($this->framework_css["pagination"]["def"]));			
            $this->tpl[0]->set_var("pagination_menu_class", cm_getClassByDef($this->framework_css["pagination"]["menu"]));
            $this->tpl[0]->parse("SectNav", false);
		} else {
            if(is_array($this->framework_css["totelem"]["col"]) && count($this->framework_css["totelem"]["col"])) {
			    $this->framework_css["totelem"] = array(
				    "col" => array(
					    "xs" => 12
					    , "sm" => 12
					    , "md" => 12
					    , "lg" => 12
				    )
				    , "util" => "align-right"
			    );		
            }
		}	
		
		if($this->with_totelem) {
        	$totelem_class["default"] = "totelem";
			$totelem_class["pages"] = cm_getClassByFrameworkCss("pages", "pagination");
        	$this->tpl[0]->set_var("totelem_class", cm_getClassByDef($this->framework_css["totelem"], $totelem_class));
			$this->tpl[0]->set_var("totelem", $this->num_rows);
			$this->tpl[0]->parse("SectTotElem", false);
		}		
			


		return $this->tplParse($output_result);
	}

	/**
	 * Elabora la parte di selezione degli elementi totali e dell'input di selezione della pagina
	 */
	function process_selector($current_class)
	{
		if ($this->nav_display_selector && count($this->nav_selector_elements))
		{
            $current_selector_isset = false;
			foreach ($this->nav_selector_elements as $key => $value)
			{
                if($this->num_rows >= $value) {
                    if(!$current_selector_isset && $value >= $this->records_per_page) {
                        $this->tpl[0]->set_var("rec_per_page_class", ' class="' . $current_class . '"');
                        $current_selector_isset = true;
                    } else {
                        $this->tpl[0]->set_var("rec_per_page_class", "");
                    }                
				    $this->tpl[0]->set_var("records_per_page", $value);
                   // $this->tpl[0]->set_var("records_per_page_class", "rec-x-page-" . $value);
				    $this->tpl[0]->parse("SectSelectorPage", true);
                }
			}
			reset($this->nav_selector_elements);
		}
		  
        if($this->nav_selector_elements_all) {
            if(!$current_selector_isset && $this->records_per_page >= $this->num_rows) 
                $this->tpl[0]->set_var("rec_per_page_class", ' class="' . $current_class . '"');
			else
				$this->tpl[0]->set_var("rec_per_page_class", "");

            $this->tpl[0]->set_var("totelem", $this->num_rows);
            //$this->tpl[0]->set_var("records_per_page_all_class", "rec-x-page-all");
            $this->tpl[0]->parse("SectSelectorPageAll", false);
        }

        if(($this->nav_display_selector && count($this->nav_selector_elements)) || $this->nav_selector_elements_all) {
        	$perpage_class["default"] = "perPage";
        	$perpage_class["pages"] = cm_getClassByFrameworkCss("pages", "pagination");

        	$this->tpl[0]->set_var("perpage_class", cm_getClassByDef($this->framework_css["perPage"], $perpage_class));
			$this->tpl[0]->parse("SectSelector", false);
		} else {
            if(is_array($this->framework_css["pagination"]["def"]["col"]) && count($this->framework_css["pagination"]["def"]["col"])) {
                foreach($this->framework_css["pagination"]["def"]["col"] AS $col_key => $col_value) {
                    $this->framework_css["pagination"]["def"]["col"][$col_key] = $this->framework_css["pagination"]["def"]["col"][$col_key] + $this->framework_css["perPage"]["col"][$col_key];
                    if($this->framework_css["pagination"]["def"]["col"][$col_key] > 12)
                        $this->framework_css["pagination"]["def"]["col"][$col_key] = 12;
                }
            }
            
			$this->tpl[0]->set_var("SectSelector", "");
		}
		
	}
	

}