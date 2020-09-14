<?php
/**
* @package Forms PHP Framework
* @category Field Class
* @desc ffField_html.php - Forms Framework Interface Field, html version
* @author Samuele Diella <samuele.diella@gmail.com>
* @copyright Copyright &copy; 2004-2017, Samuele Diella
* @license http://opensource.org/licenses/gpl-3.0.html
* @link http://www.formsphpframework.com
* @version beta 2
* @since beta 2
*/

class ffField_html extends ffField_base
{
	var $framework_css					= array(
											"container" => array(
												//"class" => null
												"row"	=> true
												//, "col" => null
											)/* se definiti a null fa fallire il merge con il record
											, "label" => array(
												"class" => null
												, "col" => null
											)
											, "control" => array(
												"class" => null
												, "col" => null
												
											)
											, "fixed_pre_content" => false // false OR array(xs,sm,md,lg)
											, "fixed_post_content" => false // false OR array(xs,sm,md,lg)
											*/
                                            , "fixed_pre_content" => true // false OR array(xs,sm,md,lg)
                                            , "fixed_post_content" => true // false OR array(xs,sm,md,lg)

    );
	var $url = null;
	var $url_ajax = false;
	var $url_parsed = null;
	var $label_encode_entities = true;

	var $buttons_options = array(
		"file" => array(
			"edit" => array(
				"class" => "crop"
				, "label" => ""
			)
			, "delete" => array(
				"class" => "cancel"
				, "label" => ""
			)
		)
	);

	//----------------------
	//  Widget Settings

	// Active Combo EX Stuffs
	/**
	 * Il padre se presente
	 * @var String
	 */
	var $actex_father					= null;
	/**
	 * Il od I figli
	 * @var Mixed può essere stringa o array
	 */
	var $actex_child					= null;
	
	var $actex_reset_childs				= false;
	/**
	 * Se il contenuto dev'essere recuperato con richieste asincrone
	 * @var Boolean
	 */
	var $actex_update_from_db			= true;
	/**
	 * Il percorso di un servizio alternativo a quello di default
	 * @var String
	 */
	var $actex_service					= null;
	/**
	 * Il campo di relazione con il padre da usare per la [WHERE]
	 * @var Mixed
	 */
	var $actex_related_field			= "";
	var $actex_compare_field			= "";
	var $actex_having_field				= "";

	/**
	 * Il tipo di operazione usabile nella [WHERE]
	 * Possibili valori '=', 'IN', 'LIKE', '<>'
	 * @var Mixed
	 */
	var $actex_operation_field			= "=";

	/**
	 * Abilita o disabilita la visualizzazione dei Dialog collegati
	 * @var Boolean
	 */
	var $actex_dialog					= true; // disable everything
	/**
	 * Visualizza il pulsante di aggiunta al combo che apre il relativo dialog
	 * @var Boolean
	 */
	var $actex_dialog_show_add			= true; // without actex_dialog_url this is ignored

	/**
	 * Un elenco di parametri nel formato query_string da passare all'url di editing
	 * E' possibile usare i tag speciali [[ID_DOM]] per recuperare valori dal dom,
	 * così come i normali tag [ID] per recuperare valori dai fields del framework
	 * @var String
	 */
	var $actex_dialog_add_params		= "";

	/**
	 * l'url di apertura del Dialog
	 * @var String
	 */

	var $actex_dialog_url				= "";
	/**
	 * il titolo del dialog
	 * @var String
	 */
	var $actex_dialog_title				= "";
	/**
	 * Visualizza il pulsante di editing dell'elemento selezionato
	 * @var Boolean
	 */
	var $actex_dialog_show_edit			= true;
	/**
	 * l'url di editing per il dialgo. Se omesso è uguale a actex_dialog_url
	 * @var String
	 */
	var $actex_dialog_edit_url			= "";
	/**
	 * Il titolo del dialog in editing. Se omesso è uguale a actex_dialog_title
	 * @var String
	 */
	var $actex_dialog_edit_title		= "";
	/**
	 * Un elenco di parametri nel formato query_string da passare all'url di editing
	 * E' possibile usare i tag speciali [[ID_DOM]] per recuperare valori dal dom,
	 * così come i normali tag [ID] per recuperare valori dai fields del framework
	 * @var String
	 */
	var $actex_dialog_edit_params		= "";
	/**
	 * Visualizza il pulsante di eliminazione dell'elemento correntemente selezionato
	 * @var Boolean
	 */
	var $actex_dialog_show_delete		= true; // without actex_dialog_delete_url this is ignored
	/**
	 * L'url del dialog di eliminazione, se omesso è uguale a $actex_dialog_edit_url
	 * @var String
	 */
	var $actex_dialog_delete_url		= "";
	/**
	 * Il titolo del dialog di eliminazione, se omesso è uguale a $actex_dialog_edit_title
	 * @var String
	 */
	var $actex_dialog_delete_title		= "";
	/**
	 * Un elenco di parametri nel formato query_string da passare all'url di eliminazione
	 * E' possibile usare i tag speciali [[ID_DOM]] per recuperare valori dal dom,
	 * così come i normali tag [ID] per recuperare valori dai fields del framework
	 * Se omesso è uguale ad $actex_dialog_edit_params
	 * @var String
	 */
	var $actex_dialog_delete_params		= null;
	/**
	 * Il messaggio da visualizzare per l'eliminazione nel dialog
	 * @var String
	 */
	var $actex_dialog_delete_message	= "Confermi l'eliminazione del dato?<br /><span>Il dato verr&agrave; eliminato definitivamente, non potr&agrave; essere recuperato.</span>";
	/**
	 * L'ID del componente collegato per l'eliminazione
	 * @var String
	 */
	var $actex_dialog_delete_idcomp = null;
		
	/**
	 * Se l'oggetto DB dell'activecombo deve collegarsi direttamente al database principale (usando mod_security)
	 * @var Boolean default false
	 */
	var $actex_skip_empty 				= false;
	var $actex_use_main_db				= false;
    var $actex_add_plus     			= false;
    var $actex_on_change				= null;
    var $actex_on_update_bt 			= null;
    var $actex_on_refill 				= null;
    var $actex_hide_empty				= false;
    var $actex_group 					= null;
    var $actex_attr 					= null;
    var $actex_plugin 					= null;
    var $actex_multi             		= false;
    var $actex_multi_sort             	= true;    
	var $actex_use_own_session 			= false;
	var $actex_dialog_icon_add			= "add.png";
	var $actex_dialog_icon_edit			= "edit.png";
	var $actex_dialog_icon_delete		= "delete.png";
	var $actex_dialog_title_add			= "";
	var $actex_dialog_title_edit		= "";
	var $actex_dialog_title_delete		= "";
	var $actex_hide_result_on_query_empty = false;
	var $actex_preserve_field			= null;
	var $actex_cache					= true;
	var $actex_limit					= null;
	
	var $actex_autocomp					= false;
	var $actex_autocomp_ajax			= false;
	var $actex_autocomp_limit			= 100;	
	
	var $autocomplete_service			= null;
    var $autocomplete_disabled          = false;
    var $autocomplete_minLength         = 3;
    var $autocomplete_delay             = 300;
    var $autocomplete_multi             = false;
    var $autocomplete_cache             = true;
    var $autocomplete_readonly          = true;
    var $autocomplete_combo             = false;
    var $autocomplete_icon				= "";
    var $autocomplete_compare           = "";
    var $autocomplete_compare_having    = "";
    var $autocomplete_operation         = null; //"LIKE [%[VALUE]%]";
    var $autocomplete_strip_char        = "";
    var $autocomplete_label 			= "";
	var $autocomplete_use_own_session	= false;
	var $autocomplete_use_main_db		= false;
	var $autocomplete_hide_result_on_query_empty = false;
	var $autocomplete_res_limit 		= 100;
	var $autocomplete_image_field 		= "image";
	
    var $autocompletetoken_minLength 		= 3;
    var $autocompletetoken_delay 			= 300;
    var $autocompletetoken_compare 			= "";
    var $autocompletetoken_compare_having   = "";
    var $autocompletetoken_operation 		= null; //"LIKE [%[VALUE]%]";
    var $autocompletetoken_theme 			= "";
    var $autocompletetoken_not_found_label 	= "Not Found";
    var $autocompletetoken_init_label 		= "What you want to search?";
    var $autocompletetoken_searching_label 	= "Searching...";
    var $autocompletetoken_limit 			= "null";
    var $autocompletetoken_delimiter 		= ",";
    var $autocompletetoken_label 			= "";
    //var $autocompletetoken_service 			= null;
    var $autocompletetoken_combo            = false;
    var $autocompletetoken_concat_field		= array();
    var $autocompletetoken_concat_separator = " - ";
    var $autocompletetoken_res_limit 		= 100;

    var $datechooser_type_date				= "mixed";
    var $datepicker_lang					= NULL; // default to first two chars of FF_LOCALE
    var $datepicker_force_datetime			= false;
    var $datepicker_showbutton				= true;
	var $datepicker_weekselector			= false;

	// Slider
	/**
	 * Quante posizioni sono a disposizione dello slider
	 * @var Int
	 */
	var $step = "1";
	/**
	 * L'elenco di label per ogni step dello slider
	 * @var Array
	 */
	var $desc_label = array();
    /**
	 * Se devono essere visualizzate le label relative ad ogni posizione
	 * @var Boolean
	 */
    var $display_label = true;
    var $encode_label = true;
    
    var $data_info = array("field" => null
    						, "base_type", "Text"
    						, "multilang", false
    					);	

	// gmap stuffs
	/**
	 * la chiave di google maps
	 * @var String
	 */
	var $gmap_key = null;
	/**
	 * Se il pinpoint dev'essere Spostabile
	 * @var Boolean
	 */
	var $gmap_draggable = true;
	/**
	 * Il livello di zoom iniziale
	 * @var Int
	 */
	var $gmap_start_zoom = 6;
	/**
	 * La latitudine di default
	 * @var Int
	 */
	var $gmap_start_lat = 45;
	/**
	 * La longitudine di default
	 * @var Int
	 */
	var $gmap_start_lng = 9;
	
	var $gmap_force_search = false;
	var $gmap_update_class = "";
	var $gmap_update_class_prefix = "";
	
	var $gmap_region									= "";
	var $gmap3_marker_limit								= 1;
	var $gmap3_max_zoom									= 1;
	var $gmap3_min_zoom									= 18;
	var $gmap3_zoom_control								= true;
	var $gmap3_zoom_control_position					= "RIGHT_BOTTOM";
	var $gmap3_marker_icon								= "";// esempio "/themes/site/images/beachflag_with_shadow.png";
	var $gmap3_map_type_control							= false;
	var $gmap3_map_type_control_options					= "DROPDOWN_MENU";
	var $gmap3_map_type_control_enable_your_style		= true;
	var $gmap3_map_type_control_your_style_name			= "my_style";
	var $gmap3_zoom_control_style						= "SMALL";
	var $gmap3_pan_control								= false;
	var $gmap3_pan_control_position						= "BOTTOM_CENTER";
	var $gmap3_scale_control							= false;
	var $gmap3_scale_control_position					= "LEFT_CENTER";
	var $gmap3_streetview_control						= false;
	var $gmap3_streetview_control_position				= "RIGHT_CENTER";
	/**
	 * permette di customizzare graficamente le mappe. 
	 * Bisogna generare il JSON seguendo questo link:
	 * http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html
	 * @var JSON
	 */
    var $gmap3_personal_style							= false;
	var $gmap3_personal_style_text                 		= "";
	
	//tiny_mce    
    var $tiny_mce_group_by_auth = false;
    var $tinymce_group = "default";

	//ckeditor
	var $ckeditor_custom_config = array();		//Altera la configurazione di base
	var $ckeditor_theme = "default";	//Altera la struttura di ckeditor
	var $ckeditor_br_mode = false;		//Usa i br o le p per costruire la paragrafazione dei testi
	var $ckeditor_skin = "office2013";		//Altera l'aspetto grafico  di ckeditor
	var $ckeditor_group_by_auth = false;//Abilita l'assegnazione del gruppo per le toolbar di ckeditor basandosi 
										//sul nome del gruppo di appartenenza dell'utente definito in sessione.
	var $ckeditor_group = "default";	//Altera le toolbar all'interno di ckeditor.
										//I possibili valori sono:
										/*	default =
											[
												['Source','NewPage','Preview','-','Bold','Italic','Underline','-','Find','Replace','-','Cut','Copy','Paste','PasteFromWord'],
											    ['Maximize', 'ShowBlocks'],['Link','Unlink','Anchor'],['Format', 'TextColor'],['NumberedList','BulletedList']
											]; 
											administrators =
											[
											    ['Source','-','Save','NewPage','Preview','-','Templates'],
											    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
											    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
											    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
											    '/',
											    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
											    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
											    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
											    ['Link','Unlink','Anchor'],
											    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
											    '/',
											    ['Styles','Format','Font','FontSize'],
											    ['TextColor','BGColor'],
											    ['Maximize', 'ShowBlocks','-','About']
											]; 
											dataentry =
											[
											    ['Source'],
											    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
											    ['Undo','Redo'],
											    '/',
											    ['Bold','Italic','Underline','Strike'],
											    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
											    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
											    ['Link','Unlink','Anchor'],
											    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
											    '/',
											    ['Styles','Format','Font','FontSize'],
											    ['TextColor','BGColor'],
											    ['Maximize', 'ShowBlocks']
											]; 
											user =
											[
											    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
											    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
											    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
											    ['Link','Unlink','Anchor'],
											    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
											    '/',
											    ['Styles','Format','Font','FontSize'],
											    ['TextColor','BGColor'],
											]; 
										*/
	
	// ckfinder
    var $file_widget_preview = true;		//Abilita la preview dell'immagine
    									// (se ckfinder_show_file non e valorizzato la preview sara disabilitata)
    var $ckfinder_show_file = "";		// percorso assoluto della chiamata a show_files per la preview del thumb
    									// (omettendo la porzione descritta nel db)
    var $ckfinder_base_path = null;		// percorso assoluto della cartella degli uploads es: /var/www/miosito/uploads
										// se omesso verra generato come segue: FF_DISK_UPDIR
    var $ckfinder_storing_path = "";	// percorso assoluto di dove verra salvato il file

    
    var $editarea_syntax 		= "";
    var $editarea_writable 		= true;
    
    
	//uploadify
	var $uploadify_use_own_session = false;



	//uploadifive
    var $file_showfile_plugin = "fancybox";
    var $file_sortable = false;
   
    									// (se ckfinder_show_file non e valorizzato la preview sara disabilitata)
    
    var $placeholder = false;

	//slug
	/**
	 * Il nome del campo slug associato 
	 * @var String
	 */
	var $slug_title_field = null;
    var $imagepicker_title_field = null;

	// List Splitter Stuffs
	var $size = 7;
	var $grouping_separator	= ",";							// the string used to separate groups of strings

	/**
	 * Una descrizione aggiuntiva da associare al field
	 * @var String
	 */
	var $description = null;

	/**
	 * Se il campo dev'essere visualizzato dal programmatore invece che dal componente
	 * @var Boolean
	 */
	var $manual_display = false;

	/**
	 * proprietà da associare all'elemento che fa il wrap dei dati
	 * @var Array
	 */
	var $data_properties		= array();
	var $data_class = "";

	var $fixed_vars = array();
	var $container_vars = array();
    
    var $widget_path = "";
    
	/**
	 * recupera il file del template
	 * @param String $control_type il tipo di controllo di cui recuperare il template
	 * @return String
	 */
	public function getTemplateFile($control_type)
	{
		if (strlen($this->template_file))
			return $this->template_file;
		else
			return "ffControl_" . $control_type . ".html";
	}
	function get_control_class($control_type = null, $addit_class = null, $params = false)
	{
		$arrClass = array();

		if(!isset($params) || !isset($params["framework_css"]) || $params["framework_css"]) {
			if(strlen($this->framework_css["control"]["class"]))
				$arrClass[] = $this->framework_css["control"]["class"];
			elseif($this->framework_css["control"]["class"] === null)
				$arrClass[] = cm_getClassByFrameworkCss("control", "form", array("exclude" => $control_type));
		}		                                                                          

		if (strlen($this->class))
			$arrClass[] = $this->class;
		else if(strlen($this->widget))
			$arrClass[] = $this->widget;
		elseif(!isset($params) || !isset($params["control_type"]) || $params["control_type"]) {
			if ($control_type === null)
				$control_type = $this->get_control_type();
			
			switch($control_type)
			{
				case "label":
					$arrClass[] = "readonly";
					break;
				case "checkbox":
					$arrClass[] = "check";
					break;
				case "picture":
				case "picture_no_link":
					$arrClass[] = "picture";
				case "input":
					if($this->app_type == "Currency")
						$arrClass[] = cm_getClassByFrameworkCss("align-right", "util");
					elseif($this->base_type == "Number")
						$arrClass[] = cm_getClassByFrameworkCss("align-center", "util");
				default:
					$arrClass[] = $control_type;
			}
		}
		return implode(" " , array_filter($arrClass));
	}	
	/**
	 * Esegue il parsing del template
	 * @param Boolean $output_result se true visualizza a video il risultato del processing, se false restituisce il contenuto del processing
	 * @return Mixed può essere string o true, a seconda di output_result
	 */
	public function tplParse($output_result)
	{
		$this->tpl[0]->set_var("properties", $this->getProperties());

		$fixed_pre_content = $this->fixed_pre_content;
		$fixed_post_content = $this->fixed_post_content;
		$buffer = $this->tpl[0]->rpparse("main", false);
		
		$wrap_addon = null;
		if(($fixed_pre_content && $this->framework_css["fixed_pre_content"])
            || ($fixed_post_content && $this->framework_css["fixed_post_content"]))
		{
			$wrap_addon = cm_getClassByFrameworkCss("wrap-addon", "form");
			$arrFieldCol = ($wrap_addon
							? array(12,12,12,12)
							: null
						);
			if($fixed_pre_content && $this->framework_css["fixed_pre_content"]) {
				$prefix_class = cm_getClassByFrameworkCss("control-prefix", "form");
				if(strlen($prefix_class))
					$fixed_pre_content = '<span class="' . $prefix_class . '">' . $fixed_pre_content . '</span>';
				
				if($wrap_addon && strlen($fixed_pre_content)) {
					$i = 0;
					$arrAddonCol = (array_key_exists("fixed_pre_content", $this->framework_css)
									? (is_array($this->framework_css["fixed_pre_content"])
										? $this->framework_css["fixed_pre_content"]
										: (is_bool($this->framework_css["fixed_pre_content"])
											? array(3,3,3,3)
											: array_fill(0, 4, $this->framework_css["fixed_pre_content"])
										)
									)
									: array(3,3,3,3)	
								);
					if(count($arrAddonCol) < 4)
						$arrAddonCol = array_merge($arrAddonCol, array_fill(count($arrAddonCol), 4 - count($arrAddonCol), $arrAddonCol[count($arrAddonCol) - 1]));

					foreach($arrAddonCol AS $addon_col_value) {
						$arrFieldCol[$i] = $arrFieldCol[$i] - $addon_col_value;
						$i++;
					}				
				
					$fixed_pre_content = '<div class="' . cm_getClassByFrameworkCss($arrAddonCol, "col") . '">' . $fixed_pre_content . '</div>';
				}
			}

			if($fixed_post_content && $this->framework_css["fixed_post_content"]) {
				$postfix_class = cm_getClassByFrameworkCss("control-postfix", "form");
				if(strlen($postfix_class))
					$fixed_post_content = '<span class="' . $postfix_class . '">' . $fixed_post_content . '</span>';

				if($wrap_addon && strlen($fixed_post_content)) {
					$i = 0;
					$arrAddonCol = (array_key_exists("fixed_post_content", $this->framework_css)
									? (is_array($this->framework_css["fixed_post_content"])
										? $this->framework_css["fixed_post_content"]
										: (is_bool($this->framework_css["fixed_post_content"])
											? array(3,3,3,3)
											: array_fill(0, 4, $this->framework_css["fixed_post_content"])
										)
									)
									: array(3,3,3,3)	
								);
					if(count($arrAddonCol) < 4)
						$arrAddonCol = array_merge($arrAddonCol, array_fill(count($arrAddonCol), 4 - count($arrAddonCol), $arrAddonCol[count($arrAddonCol) - 1]));
						
					foreach($arrAddonCol AS $addon_col_value) {
						$arrFieldCol[$i] = $arrFieldCol[$i] - $addon_col_value;
						$i++;
					}				

					$fixed_post_content = '<div class="' . cm_getClassByFrameworkCss($arrAddonCol, "col") . '">' . $fixed_post_content . '</div>';
				}
					
			}
			
			if(is_array($arrFieldCol))
				$buffer = '<div class="' . cm_getClassByFrameworkCss($arrFieldCol, "col") . '">' . $buffer . '</div>';
		}
		$buffer = $fixed_pre_content . $buffer . $fixed_post_content;

		if($wrap_addon !== null && !$wrap_addon)
			$buffer = '<div class="' . cm_getClassByFrameworkCss("group", "form") . '">' . $buffer . '</div>';

		if ($this->parent_page !== NULL) //code for ff.js
			$this->parent_page[0]->tplAddJs("ff.ffField");

		if ($output_result)
		{
			echo $buffer;
			return true;
		}
		else
		{
			return $buffer;
		}
	}

	/**
	 * carica l'oggetto template dentro $tpl
	 */
	public function tplLoad($control_type)
	{
		$this->tpl[0] = ffTemplate::factory($this->getTemplateDir($control_type));
		$this->tpl[0]->load_file($this->getTemplateFile($control_type), "main");
                              
		if ($this->parent !== null && strlen($this->parent[0]->getIDIF()))
		{
			if (!$this->omit_parent_id)
				$this->tpl[0]->set_var("container", $this->parent[0]->getPrefix());

		}

		if($this->parent_page !== NULL) {
			$this->tpl[0]->set_var("keys", $this->parent_page[0]->get_params("", "", false));
			$this->tpl[0]->set_var("query_string", $this->parent_page[0]->get_script_params());
		}

		$this->tpl[0]->set_var("site_path", ffCommon_specialchars($this->site_path));
		$this->tpl[0]->set_var("page_path", ffCommon_specialchars($this->page_path));

		//if (!strlen($this->widget))
			$this->tpl[0]->set_var("class", ffCommon_specialchars($this->get_control_class($control_type)));

        if (is_array($this->fixed_vars) && count($this->fixed_vars))
        {
            foreach ($this->fixed_vars as $key => $value)
            {
                $this->tpl[0]->set_var($key, $value);
            }
            reset($this->fixed_vars);
        }

		$res = $this->doEvent("on_tpl_load", array(&$this));
	}
	
	function getProperties($property_set = null)
	{
		if ($property_set === null)
		{
			$property_set = $this->properties;

			if ($this->placeholder === true)
				$property_set["placeholder"] = ffCommon_specialchars($this->label);
			else if($this->placeholder)
				$property_set["placeholder"] = $this->placeholder;
		}

		return parent::getProperties($property_set);
	}
	
	function process_file($id, &$value)
	{
		$this->tpl[0]->set_var("butt_del_class", cm_getClassByFrameworkCss($this->buttons_options["file"]["delete"]["class"], "icon"));
		$this->tpl[0]->set_var("butt_del_label", $this->buttons_options["file"]["delete"]["label"]);

		$this->tpl[0]->set_var("butt_edit_class", cm_getClassByFrameworkCss($this->buttons_options["file"]["edit"]["class"], "icon"));
		$this->tpl[0]->set_var("butt_edit_label", $this->buttons_options["file"]["edit"]["label"]);
		
		$this->tpl[0]->set_var("noimg_class", " " . cm_getClassByFrameworkCss("noimg", "icon"));
		
		parent::process_file($id, $value);
	}
	
	function setWidthComponent($resolution_large_to_small) 
	{
		if(is_array($resolution_large_to_small) || is_numeric($resolution_large_to_small)) 
			$this->framework_css["container"]["col"] = ffCommon_setClassByFrameworkCss($resolution_large_to_small);
		elseif(strlen($resolution_large_to_small))
			$this->framework_css["container"]["row"] = $resolution_large_to_small;
	}	

	function setWidthLabel($resolution_large_to_small, $reverse_control_class = true, $align = "right") 
	{
		$this->framework_css["label"]["col"] = ffCommon_setClassByFrameworkCss($resolution_large_to_small);
		if($align) {
			$this->framework_css["label"]["util"] = array(
				"align-" . $align
			);			
		}
		if($reverse_control_class && is_array($this->framework_css["label"]["col"])) {
			$this->framework_css["control"]["col"] = array(
				"xs" => ($this->framework_css["label"]["col"]["xs"] == 12 ? 12 : 12 - $this->framework_css["label"]["col"]["xs"])
				, "sm" => ($this->framework_css["label"]["col"]["sm"] == 12 ? 12 : 12 - $this->framework_css["label"]["col"]["sm"])
				, "md" => ($this->framework_css["label"]["col"]["md"] == 12 ? 12 : 12 - $this->framework_css["label"]["col"]["md"])
				, "lg" => ($this->framework_css["label"]["col"]["lg"] == 12 ? 12 : 12 - $this->framework_css["label"]["col"]["lg"])
			);
		}	
	}

	function setWidthControl($resolution_large_to_small) 
	{
		$this->framework_css["control"]["col"] = ffCommon_setClassByFrameworkCss($resolution_large_to_small);
	}	
	
	function setLabelProperties($properties) 
	{
		if(isset($properties["col"]))
			$properties["col"] = ffCommon_setClassByFrameworkCss($properties["col"]);

		$this->framework_css["label"] = array_replace($this->framework_css["label"], $properties);
	}	

	function setControlProperties($properties) 
	{
		if(isset($properties["col"]))
			$properties["col"] = ffCommon_setClassByFrameworkCss($properties["col"]);
			
		$this->framework_css["control"] = array_replace($this->framework_css["control"], $properties);
	}	
}
