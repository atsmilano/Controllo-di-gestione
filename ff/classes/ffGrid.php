<?php
/**
 * Data Grid
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Data Grid
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffGrid
{
	static protected $events = null;
	
	public function __construct()
	{
		ffErrorHandler::raise("Cannot istantiate " . __CLASS__ . " directly, use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}
	
	public function __clone()
	{
		ffErrorHandler::raise("Cannot clone " . __CLASS__ . ", use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}

	static public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null, $additional_data = null)
	{
		self::initEvents();
		self::$events->addEvent($event_name, $func_name, $priority, $index, $break_when, $break_value, $additional_data);
	}

	static public function doEvent($event_name, $event_params = array())
	{
		self::initEvents();
		return self::$events->doEvent($event_name, $event_params);
	}
	
	static private function initEvents()
	{
		if (self::$events === null)
			self::$events = new ffEvents();
	}

	/**
	 * Questa funzione crea un'istanza di ffGrid utilizzando i parametri in ingresso
	 *
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $theme
	 * @param array $variant
	 * @return ffGrid_base
	 */
	public static function factory(ffPage_base $page, $disk_path = null, $theme = null, array $variant = null)
	{
		if ($theme === null)
			$theme = $page->theme;
			
		if ($disk_path === null)
			$disk_path = $page->disk_path;
			
		$res = self::doEvent("on_factory", array($page, $disk_path, $theme, $variant));
		$last_res = end($res);

		if (is_null($last_res))
		{
			$base_path = $disk_path . "/themes/" . $theme;
			
			if (!isset($variant["name"]))
			{
				$registry = ffGlobals::getInstance("_registry_");
				if (!isset($registry->themes) || !isset($registry->themes[$theme]))
				{
					$registry->themes[$theme] = new SimpleXMLElement($base_path . "/theme_settings.xml", null, true);
				}
		
				$suffix = $registry->themes[$theme]->default_class_suffix;
				
				$class_name = __CLASS__ . "_" . $suffix;
			}
			else
				$class_name = $variant["name"];
				
			if (!isset($variant["path"]))
				$base_path .= "/ff/" . __CLASS__ . "/" . $class_name . "." . FF_PHP_EXT;
			else
				$base_path .= $variant["path"];
		}
		else
		{
			$base_path = $last_res["base_path"];
			$class_name = $last_res["class_name"];
		}
		
		require_once $base_path;
		$tmp = new $class_name($page, $disk_path, $theme);
		
		$res = self::doEvent("on_factory_done", array($tmp));

		return $tmp;
	}
}

/**
 * ffGrid è la classe che gestisce la visualizzazione di liste di dati.
 * Per quanto permetta di includere pulsanti d'azione e veri e propri controlli,
 * di proprio non esegue operazioni di nessun tipo sui dati.
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffGrid_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)

	/**
	 * ID dell'oggetto; deve essere univoco per ogni ffPage
	 * @var Number
	 */
	var $id						= "";

	/**
	 * URL relativo al web del sito
	 * @var String 
	 */
	var $site_path 				= "";					
	
	/**
	 * URL relativo al disco del sito
	 * @var String 
	 */
	var $disk_path 				= "";					

	/**
	 * Cartella dove è contenuta la pagina partendo dalla root del sito
	 * @var String
	 */
	var $page_path 				= "";					
	var $class	 				= "ffGrid";					//

	/**
	 * Cartella del template; di default è la cartella "theme"
	 * @var String
	 */
	var $template_dir			= null;

	var $max_rows				= null;

	/**
	 * File del template; di default è il file "ffGrid.html"
	 * @var String
	 */
	var $template_file 			= "ffGrid.html";
	
	var $fixed_pre_content 		= "";
	var $fixed_post_content 	= "";
	
	var $fixed_title_content 	= "";
	var $fixed_heading_content 	= "";
	var $fixed_body_content 	= "";
	
	var $theme					= null;

	/**
	 * Default settato a null; equivale a FF_SITE_PATH
	 * @var String
	 */
	var $redirect_path			= null;					

	var $display				= true;					// force ffPage to hide the whole component

	var $redirect_fullscreen	= false;
	var $redirect_theme			= null;
	var $redirect_layer			= null;
	var $redirect_topbar		= null;
	var $redirect_navbar		= null;

	var $dialog_path			= null;					/* default to null, means FF_SITE_PATH
															NB:
															Forms add "/dialog" to this value, so if you copying redirect
															files you are forced to enclose in a so-called directory. */
	var $properties 			= array(
											  "Frame" 			=> array(
														 "class" 				=> "ffGrid"
													   , "width"				=> null
																)
											, "Grid_Frame"	 	=> array(
														 "class" 				=> "DataGrid"
																)
											, "Grid_Table"	 	=> array(
														 "class" 				=> "frame"
													   , "width"				=> null
																)
										);

	var $widget_deps			= null;

	var $processed_widgets = array();
	
	/**
	 * Link alla pagina precedente; 
	 * @var String
	 */
	var $ret_url				= "";					

	var $use_fixed_fields		= false;				/* If enabled, force to use normal vars & sections on fixed
															fields Templates instead on generating them.
															Sections will have same fields names with "SectSet" prefix for
															fields with values, "SectNotSet" for empty fields. */

	var $fixed_vars = array();

	var $force_no_field_params		= false;
	var $include_all_records		= false;
	var $use_own_location		= false;
	var $location_name			= null;

	/**
	 * Titolo della grid
	 * @var String
	 */
	var $title					= "";					

	/**
	 * Select SQL per la Grid; ricorsarsi di mettere aggiungere i tag [WHERE] e [ORDER]
	 * Se vengono aggiunte delle condizioni di WHERE e ORDER proprie bisogna inoltre aggiungere anche i tag
	 * [AND] o [OR] nella clausola WHERE e il tag [COLON] nella clausola ORDER.
	 * Esempi:
	 * "SELECT * FROM users	 WHERE status = '1' [AND] [WHERE] ORDER BY level [COLON] [ORDER]"
	 * "SELECT * FROM users [WHERE] ORDER BY level [COLON] [ORDER]"
	 * "SELECT * FROM users	[WHERE]	[ORDER]	LIMIT 1000"
	 * 
	 * @var String
	 */
	var $source_SQL 			= "";
	var $source_DS				= null;
	
	var $sql_check = true;

	/**
	 * Abilita l'ordinamento all'interno della Grid
	 * @var Boolean
	 */
	var $use_order 				= true;

	/**
	 * Abilita la ricerca all'interno della grid
	 * @var String
	 */
	var $use_search				= true;

	/**
	 * Visualizza il BOX per la ricerca
	 * @var Boolean
	 */
	var $display_search		 	= true; 
	
	
	var $search_container		= null;
	var $search_container_buffer = "";
	
	var $search_method			= "get"; 				/* use 'post' only if you have a textarea in search box
															or if you have a lot of fields to search on */
	var $search_cols			= 2;					// how many field/label pairs per rows
	var $search_url				= "";					// leave blank for script page

	/**
	 * le opzioni dei pulsanti di default
	 * @var Mixed
	 */
	var $buttons_options		= array(
									"search" => array(
											  "display" => true
											, "index" 	=> 0
											, "obj" 	=> null
											, "label" 	=> ""
									)
									, "delete" => array(
											"class"		=> "ico-delete"
											, "label"		=> "&nbsp;" 
									)
									, "edit" => array(
											"class"		=> "ico-edit"
											, "label"		=> "&nbsp;" 
									)
									, "addnew" => array(
											"class"		=> "ico-add"
											, "label"		=> "" 
									)
									, "export" => array(
											"class"		=> "ico-export"
											, "display" => false
											, "index" 	=> 0
											, "obj" 	=> null
											, "label" 	=> ""
									)
								);


	/**
	 * Visualizza il pulsante "Aggiungi"
	 * @var Boolean
	 */
	var $display_new			= true;					
	var $display_not_add_new	= true;

	var $display_actions		= true;

	var $display_grid			= "always";				// always: display even without search | search: display only when a search is done | never: mah.. :)

	/**
	 * ID del recordo associato alla grid
	 * @var String
	 */
	var $record_id				= "";					// the record object name

	/**
	 * url del record associato alla grid
	 * @var String
	 */
	var $record_url				= "";
	var $record_url_delete		= null;

	/**
	 * Link al record per l'inserimento dei dati (pagina di tipo ffRecord).
	 * Se non impostato verrà utilizzato $record_url
	 * @var String
	 */
	var $record_insert_url		= "";

	var $record_delete_url		= "";
	
	var $bt_edit_url 			= "";
	var $bt_delete_url 			= "";
	var $bt_insert_url 			= "";
	
    var $addit_record_param     = "";
    var $addit_insert_record_param     = "";

	/**
	 * Determinerà se i record verranno suddivisi su più pagine.
	 * @var Boolean
	 */
	var $use_paging			= true;

	/**
	 * Numero di record per ogni pagina
	 * @var Number
	 */
	var $default_records_per_page	= 25;
	var $default_page	= 1;
	var $default_nav_selector_elements = array(10, 25, 50);
	
	var $pagination_save_memory = true;
	var $pagination_save_memory_in_use = false;
	protected $rrow = false;
	
	/**
	 * Visualizza il page navigator
	 * @var boolean
	 */
	var $display_navigator		= true;					
	var $navigator				= null;					/* a ffPageNavigator object. Read ffPageNavigator
															docs for personalization. If you don't instantiate it
															explicity, ffGrid do it for you with standard
															vals */
	var $navigator_orientation	= "both";				// may be: both, top or bottom. Use you immagination..

	var $display_labels			= true;					/* display column's labels. If you don't display them,
															you will not able to interactively order */
	var $order_method			= "both";				/* 	none: 	normal useless poor labels =D
															labels: order by clicking on labels
															icons:	order by clicking on a little pair of icon
															both:	use both methods (more cool! :-) */

	/**
	 * Campo che viene utilizzato per l'ordinamento;
	 * la variabile va OBBLIGATORIAMENTE impostata.
	 * @var String
	 */
	var $order_default			= "";					

	var $use_alpha				= false;				/* determine if records will be filtered using alphabetical
															index. */

	var $alpha_field			= null;					/* the id of the field to filter by.
															required if alpha indexing is used */

	var $alpha_default			= "";					/* the letter to filter by default. setting this to empty string
															cause to display all records. */


	// LABELS

	/**
	 * Label che viene visualizzata per la conferma dell'eliminazione del record
	 * @var String
	 */
	var $label_delete			= "Confermi l&apos;eliminazione del dato?<br /><span>Il dato verr&agrave; eliminato definitivamente, non potr&agrave; essere recuperato.</span>";

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)

	var $parent					= null;					// The containing class

	/**
	 * Campi nascosti associati a ffGrid
	 * @var array()
	 */
	var $hidden_fields			= array();				// array of hidden ffFields

	/**
	 * Array che contiene i campi di ricerca della grid
	 * @var array()
	 */
	var $search_fields			= array();				// array of ffFields to search for

	/**
	 * Array che contiene i campi della grid
	 * @var array()
	 */
	var $grid_fields			= array();				// array of ffFields to display in grid

	/**
	 * Array contenente i campi chiave della grid
	 * @var array()
	 */
	var $key_fields				= array();				/* array of key fields. to use in conjuction with
															edit or/and delete buttons */

	var $recordset_keys			= array();
	var $recordset_values		= array();
	var $displayed_keys			= array();
	
	var $recordset_ori_values 	= array();

	/**
	 * Array contenente i pulsanti associati ad ogni recordo della grid
	 * @var array()
	 */
	var $grid_buttons			= array();				
	
	/**
	 * Array di pulsanti associati alla grid.
	 * @var array() 
	 */
	var $action_buttons			= array();
    var $action_buttons_header  = array();
	/**
	 * Array di pulsanti visualizzati sotto il box della ricerca
	 * @var array()
	 */
	var $search_buttons			= array();				

	var $fields_values			= array();

	var $navigator_params		= "";					// navigation vars in url form
	var $order_params			= "";					// orders vars in url form
	var $search_params			= "";					// search vars in url form
	var $hidden_params			= "";					// hidden vars in url form

	var $params					= "";					// a collection of all of the above

	var $page					= "";					/* current page displayed (view page_navigator in main Form file)
														   automatically set to 1 by the code if not set */
	var $frame					= "";					// as above
	var $records_per_page		= "";					// as above
	var $nav_selector_elements  = "";

	/**
	 * Azione eseguita sul form
	 * @var String
	 */
	var $frmAction				= "";

	/**
	 * Descrizione dell'errore
	 * @var String
	 */
	var $strError				= "";					

	var $alpha					= "";					// the selected letter to filter by

	var $searched				= false;					// hold if a search is started

	var $order				    = "";					// the selected field
	var $direction				= "";					// the selected direction

	/**
	 * Ricerca nella sintassi SQL
	 * @var String
	 */
	var $sqlWhere				= "";

	/**
	 * Ordinamento nella sintassi SQL
	 * @var String
	 */
	var $sqlOrder				= "";

	/**
	 * HAVING nella sintassi SQL
	 * @var String
	 */
	var $sqlHaving				= "";

	/**
	 * SQL processata da ffGrid
	 * @var String
	 */
	var $processed_SQL			= null;
	var $SQL_passthrough		= false;

	/**
	 * L'uRL completo della pagina
	 * @var String
	 */
	var $url					= "";					
	var $full_record_url		= "";					// full parameterized record url without keys

	var $use_fields_params		= false;

	var $db						= null;					// Internal DB_Sql() Object
	var $tpl					= null;					// Internal ffTemplate() object
	
	private $parsed_fields			= 0;
	private $parsed_filters			= 0;
	private $parsed_hidden_fields 	= 0;

	var $cache_get_resources	= array();
	var $cache_clear_resources	= array();

	var $json_result = array();

	//var $additional_vars		= array();

	var $resources = array();
	var $resources_set = array();
	var $resources_get = array();

	var $libraries	= array();
	var $js_deps	= array();
	var $css_deps	= array();
	
// ---------------------------------------------------------------
	//  ABSTRACT FUNCS (depends on theme)
	// ---------------------------------------------------------------

	abstract protected function tplLoad();
	abstract public function tplParse($output_result);
	abstract public function structProcess($tpl);
	
	// ---------------------------------------------------------------
	//  PUBLIC FUNCS
	// ---------------------------------------------------------------

	/**
	 * constructor
	 * @param ffPage_base $page l'istanza di page associata
	 * @param String $disk_path il percorso su disco del tema
	 * @param String $theme il tema impiegato
	 */
	function __construct(ffPage_base $page, $disk_path, $theme)
	{
		$this->get_defaults("ffGrid");
		$this->get_defaults();

		$this->site_path = $page->site_path;
		$this->page_path = $page->page_path;
		$this->disk_path = $disk_path;
		$this->theme = $theme;

		if ($this->db === null)
			$this->db[0] = ffDB_Sql::factory();

		if ($this->use_paging && $this->navigator === null)
			$this->navigator[0] = ffPageNavigator::factory($page, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
	}
	
	// -----------------------
	//  Internal Array funcs

	/**
	 * Aggiunge un campo hidden a ffGrid
	 * @param ffField Campo da aggiungere
	 * @param String $value
	 */
	function addHiddenField($field, $value = null)
	{
        if ($value !== null && !get_class($value) == "ffData")
            ffErrorHandler::raise("Wrong call to addHiddenField: value must be a ffData"
								, E_USER_ERROR, $this, get_defined_vars());
	
		$this->hidden_fields[$field] = $value;
	}

	/**
	 * Aggiunge un campo di ricerca a ffGrid
	 * @param ffField Il campo di ricerca
	 */
	function addSearchField(ffField_base $field)
	{
        if (!is_subclass_of($field, "ffField_base"))
            ffErrorHandler::raise("Wrong call to addSearchField: object must be a ffField"
								, E_USER_ERROR, $this, get_defined_vars());
	
		$field->cont_array =& $this->search_fields;
		$field->parent = array(&$this);
		$this->search_fields[$field->id] = $field;
	}

	/**
	 * Aggiunge un campo che verrà visualizzato
	 * @param ffField Il campo da aggiungere
	 * @param boolean
	 */
	function addContent($field, $toOrder = true)
	{
        if (!is_subclass_of($field, "ffField_base"))
            ffErrorHandler::raise("Wrong call to addContent: object must be a ffField"
                            , E_USER_ERROR, $this, get_defined_vars());

		$field->parent = array(&$this);
		$field->cont_array =& $this->grid_fields;
		$this->grid_fields[$field->id] = $field;
		
		if ($field->crypt && $field->order_field === null)
			$toOrder = false;
		
		if ($toOrder && $field->data_type !== "" && $this->use_order)
			$this->grid_fields[$field->id]->allow_order = true;
	}

	/**
	 * Aggiunge un campo chiave a ffGrid
	 * @param ffField Il campo da aggiungere
	 * @param boolean
	 */
	function addKeyField($field, $bGlobal = false)
	{
        if (!is_subclass_of($field, "ffField_base"))
            ffErrorHandler::raise("Wrong call to addKeyField: object must be a ffField"
                            , E_USER_ERROR, $this, get_defined_vars());

		$field->parent = array(&$this);
		$field->cont_array =& $this->key_fields;
		$this->key_fields[$field->id] = $field;
		$this->key_fields[$field->id]->is_global = $bGlobal;
	}

	/**
	 * Aggiunge un pulsante a ffGrid; il pulsante viene aggiunto ad ogni riga di ffGrid
	 * @param ffButton $button
	 */
	function addGridButton($button)
	{
	    if (!is_subclass_of($button, "ffButton_base"))
	            ffErrorHandler::raise("Wrong call to addGridButton: object must be a ffButton"
	                            , E_USER_ERROR, $this, get_defined_vars());

		$button->parent = array(&$this);
		$this->grid_buttons[$button->id] = $button;
	}

	/**
	 * Aggiunge un pulsante a ffGrid; in questo caso il pulsante viene aggiungo dopo ffGrid e non ad ogni riga.
	 * @param ffButton $button
	 */
	function addActionButton($button)
	{
	    if (!is_subclass_of($button, "ffButton_base"))
            ffErrorHandler::raise("Wrong call to addActionButton: object must be a ffButton"
                            , E_USER_ERROR, $this, get_defined_vars());

		$button->parent = array(&$this);
		$this->action_buttons[$button->id] = $button;
	}

    /**
     * Aggiunge un pulsante a ffGrid; in questo caso il pulsante viene aggiunto accanto al bottone aggiungi nuovo e non ad ogni riga.
     * @param ffButton $button
     */
    function addActionButtonHeader($button)
    {
        if (!is_subclass_of($button, "ffButton_base"))
            ffErrorHandler::raise("Wrong call to addActionButton: object must be a ffButton"
                            , E_USER_ERROR, $this, get_defined_vars());

        $button->parent = array(&$this);
        $this->action_buttons_header[$button->id] = $button;
    }
    
	/**
	 * Aggiunge un pulsante di ricerca
	 * @param ffButton_base $button
	 * @param int $index l'ordine in cui comparirà il pulsante
	 */
	function addSearchButton($button, $index = null)
	{
	    if (!is_subclass_of($button, "ffButton_base"))
            ffErrorHandler::raise("Wrong call to addSearchButton: object must be a ffButton"
                            , E_USER_ERROR, $this, get_defined_vars());

		$button->parent = array(&$this);
		if ($index === null)
			$this->search_buttons[] = $button;
		else
			array_splice(	$this->search_buttons,
							$index,
							0,
							array(&$button)
						);
	}

	function getSQL()
	{
		if ($this->source_DS !== null)
		{
			if (is_string($this->source_DS))
				return ffDBSource::getSource($this->source_DS)->getSql($this);
			else
				return $this->source_DS->getSql($this);
		}
		else
			return $this->source_SQL;
	}
	
	/**
	 * process preparation' function
	 * usually called by ffPage
	 */
	function pre_process()
	{
		// Load Template and initialize it
		$this->tplLoad();
			
		// First of all, process all page's params
		$this->process_params();
		
		// Finally, prepare SQL
		$sSQL = $this->getSQL();

        if(strlen($sSQL))
        {
			if (!$this->SQL_passthrough)
			{
				$bFindWhereTag = preg_match("/\[WHERE\]/", $sSQL);
				$bFindWhereOptions = preg_match("/(\[AND\]|\[OR\])/", $sSQL);
				$bFindOrderTag = preg_match("/\[ORDER\]/", $sSQL);
				$bFindOrderOptions = preg_match("/\[COLON\]/", $sSQL);
				$bFindHavingTag = preg_match("/\[HAVING\]/", $sSQL);
				$bFindHavingOptions = preg_match("/(\[HAVING_AND\]|\[HAVING_OR\])/", $sSQL);

				// Do some (pathetic) SQL syntax check
				if ($this->sql_check)
				{
					if (!$bFindWhereTag)
						ffErrorHandler::raise("SQL Statement without '[WHERE]' Tag in the SQL!", E_USER_ERROR, $this, get_defined_vars());
					else if (!$bFindOrderTag)
						ffErrorHandler::raise("SQL Statement without '[ORDER]' Tag in the SQL", E_USER_ERROR, $this, get_defined_vars());
					else if (!$bFindHavingTag && strlen($this->sqlHaving))
						ffErrorHandler::raise("SQL statement with 'having' fields and without a '[HAVING]' tag", E_USER_ERROR, $this, get_defined_vars());
				}

				if (strlen($this->sqlWhere))
				{
					if ($bFindWhereOptions)
						$tmp = "";
					else
						$tmp = " WHERE ";
					$sSQL = str_replace("[WHERE]", $tmp . $this->sqlWhere, $sSQL);
					$sSQL = str_replace("[AND]", "AND", $sSQL);
					$sSQL = str_replace("[OR]", "OR", $sSQL);
				}
				else
				{
					// remove tags, if exist
					$sSQL = str_replace("[WHERE]", "", $sSQL);
					$sSQL = str_replace("[AND]", "", $sSQL);
					$sSQL = str_replace("[OR]", "", $sSQL);
				}

				if (strlen($this->sqlHaving))
				{
					if ($bFindHavingOptions)
						$tmp = "";
					else
						$tmp = " HAVING ";
					$sSQL = str_replace("[HAVING]", $tmp . "(" . $this->sqlHaving . ")", $sSQL);
					$sSQL = str_replace("[HAVING_AND]", "AND", $sSQL);
					$sSQL = str_replace("[HAVING_OR]", "OR", $sSQL);
				}
				else
				{
					// remove tags, if exist
					$sSQL = str_replace("[HAVING]", "", $sSQL);
					$sSQL = str_replace("[HAVING_AND]", "", $sSQL);
					$sSQL = str_replace("[HAVING_OR]", "", $sSQL);
				}

				if (strlen($this->sqlOrder))
				{
					if ($bFindOrderOptions)
						$tmp = "";
					else
						$tmp = " ORDER BY ";
					$sSQL = str_replace("[ORDER]", $tmp . $this->sqlOrder, $sSQL);
					$sSQL = str_replace("[COLON]", ",", $sSQL);
				}
				else
				{
					// remove tags, if exist
					$sSQL = str_replace("[ORDER]", "", $sSQL);
					$sSQL = str_replace("[COLON]", "", $sSQL);
				}
				//---------------------------------------------
			}
			
		    $this->processed_SQL = $sSQL;
        }
	}

	/**
	 * process function
	 * usually called by ffPage
	 */
	function process()
	{
		// manage actions. This may cause a redirect, so the end is never reached
		$this->process_action();
	}

	/**
	 * interface' processing function
	 * @param boolean $output_result
	 * @return mixed
	 */
	function process_interface($output_result = false)
	{
		 $res = ffGrid::doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		 $res = $this->doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		// display error
		if($this->tpl !== null)
		{
			$this->displayError();

			// process all section
	        $this->initControls();

			$this->process_action_buttons();
	        $this->process_action_buttons_header();
	        
			$this->process_search();
			$this->process_alpha();
			$this->process_hidden();
		}
		
		$this->process_grid();		// process navigator and labels (with order) from inside

		if ($output_result !== null)
			return $this->tplParse($output_result);
	}
	
	function getProperties($property_set)
	{
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
				else
				{
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . (strlen($value) ? "=\"" . $value . "\"" : "");
				}
			}
			reset($property_set);
		}
		return $buffer;
	}

	/**
	 * Restituisce il tema utilizzato da ffGrid
	 * @return String Tema utilizzato da ffGrid 
	 */
	function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Restituisce il percorso del template utilizzato da ffGrid
	 * @return String path del template utilizzato da ffGrid
	 */
	function getTemplateDir()
	{
		$res = $this->doEvent("getTemplateDir", array($this));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffGrid";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}
	
	/**
	 * params processing function
	 * usually called by ffPage
	 */
	function process_params()
	{
		if ($this->parent[0]->isset_param($this->id, "rrow"))
		{
			$this->rrow = intval($this->parent[0]->retrieve_param($this->id, "rrow"));
			if ($this->rrow < 0)
				$this->rrow = 0;
		}
		
		$this->process_alpha_params();
		$this->process_search_params();
		$this->process_order_params();
		$this->process_navigator_params();
		if (!$this->force_no_field_params)
			$this->process_fields_params();
		$this->process_hidden_params();

		$this->params = $this->order_params;
		if ($this->use_search)
			$this->params .= "&" . $this->search_params;
		if ($this->use_paging)
			$this->params .= "&" . $this->navigator_params;
		$this->params .= $this->hidden_params;
	}

	/**
	 * field's params processing
	 * called by process_params()
	 */
	function process_fields_params()
	{
		$this->frmAction = $this->parent[0]->retrieve_param($this->id, "frmAction");

		if (!$this->force_no_field_params)
		{
			foreach ($this->grid_fields as $key => $value)
			{
				if(strlen($this->grid_fields[$key]->control_type))
					$this->use_fields_params = true;
			}
			reset($this->grid_fields);
		}

		// retrieve global recordset (handle all values)
		$this->recordset_keys = $this->parent[0]->retrieve_param($this->id, "recordset_keys");
		$this->recordset_values = $this->parent[0]->retrieve_param($this->id, "recordset_values");
		$this->recordset_ori_values = $this->parent[0]->retrieve_param($this->id, "recordset_ori_values");

		if (!is_array($this->recordset_keys))
			$this->recordset_keys = array();

		if (!is_array($this->recordset_values))
			$this->recordset_values = array();

		if (!is_array($this->recordset_ori_values))
			$this->recordset_ori_values = array();

//				ffErrorHandler::raise("DEBUG IN CORSO", E_USER_ERROR, $this, get_defined_vars());

		ksort($this->recordset_keys, SORT_NUMERIC);
		ksort($this->recordset_values, SORT_NUMERIC);
		ksort($this->recordset_ori_values, SORT_NUMERIC);

		// retrieve last displayed recordset and exclude from global recordset if needed
		// use of displayed recordset is required to use proper locale/app_type
		$this->displayed_keys = $this->parent[0]->retrieve_param($this->id, "displayed_keys");

		if (is_array($this->displayed_keys) && count($this->displayed_keys))
		{
			ksort($this->displayed_keys, SORT_NUMERIC);
			foreach ($this->displayed_keys as $rst_key => $rst_value)
			{
				// determine if record is changed and store values
				$include_record = false;
				foreach ($this->grid_fields as $key => $FormField)
				{
					if ($this->grid_fields[$key]->control_type != "" && !$this->grid_fields[$key]->field_params_ignore_change)
					{
						if (isset($this->recordset_values[$rst_key][$key]))
							$tmp = new ffData($this->recordset_values[$rst_key][$key], $this->grid_fields[$key]->get_app_type(), FF_LOCALE);
						else
							$tmp = $this->grid_fields[$key]->getDefault(array(&$this));
						$tmp2 = new ffData($this->recordset_ori_values[$rst_key][$key], $this->grid_fields[$key]->get_app_type(), FF_LOCALE);

						if ($tmp->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE) !== $tmp2->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE))
						{
							$include_record = true;
							$this->recordset_values[$rst_key][$key] = $tmp->getValue($this->grid_fields[$key]->base_type, FF_SYSTEM_LOCALE);
						}
					}
				}
				reset($this->grid_fields);

				if (!$include_record && !$this->include_all_records)
				{
					unset($this->recordset_keys[$rst_key]);
					unset($this->recordset_values[$rst_key]);
					unset($this->recordset_ori_values[$rst_key]);
				}
			}
			reset($this->displayed_keys);

			ksort($this->recordset_keys, SORT_NUMERIC);
			ksort($this->recordset_values, SORT_NUMERIC);
			ksort($this->recordset_ori_values, SORT_NUMERIC);

			// recalc ids (array_merge weird behavoir)
			$this->recordset_keys = array_merge($this->recordset_keys, array());
			$this->recordset_values = array_merge($this->recordset_values, array());
			$this->recordset_ori_values = array_merge($this->recordset_ori_values, array());
		}

		// reset displayed keys to handle new values
		$this->displayed_keys = array();
	}

	/**
	 * hidden params processing function
	 * called by process_params()
	 */
	function process_hidden_params()
	{
		foreach ($this->hidden_fields as $key => $value)
		{
			if ($this->hidden_fields[$key] === null)
				$this->hidden_fields[$key] = new ffData($this->parent[0]->retrieve_param($this->id, $key));
			$this->hidden_params = $this->getPrefix() . $key . "=" . $this->hidden_fields[$key]->getValue(null, FF_SYSTEM_LOCALE) . "&";
		}
		reset($this->hidden_fields);
	}

	/**
	 * navigator params processing function
	 * called by process_params()
	 */
	function process_navigator_params()
	{
		if (!$this->use_paging)
			return;
		
		$this->page = intval($this->parent[0]->retrieve_param($this->id, $this->navigator[0]->page_parname));
		$this->records_per_page = intval($this->parent[0]->retrieve_param($this->id, $this->navigator[0]->records_per_page_parname));

		if (!$this->records_per_page)
			$this->records_per_page = $this->default_records_per_page;
		if (!$this->page)
			$this->page = $this->default_page;
		if (!$this->nav_selector_elements)
			$this->nav_selector_elements = $this->default_nav_selector_elements;

		if ($this->tpl !== null)
		{
			$this->tpl[0]->set_var("page", $this->page);
			$this->tpl[0]->set_var("records_per_page", $this->records_per_page);
		}

		$this->navigator_params = $this->getPrefix() . "records_per_page=" . $this->records_per_page . "&" . $this->getPrefix() . "page=" . $this->page;
	}

	/**
	 * alpha params processing function
	 * called by process_params()
	 */
	function process_alpha_params()
	{
		if (!$this->use_alpha)
			return;

		if ($this->parent[0]->isset_param($this->id, "alpha"))
			$this->alpha = $this->parent[0]->retrieve_param($this->id, "alpha");
		else
			$this->alpha = $this->alpha_default;

		$this->tpl[0]->set_var("selected_alpha", $this->alpha);				
		if (strlen($this->search_params))
			$this->search_params .= "&";
		$this->search_params .= $this->getPrefix() . "alpha=" . urlencode($this->alpha);

		if (strlen($this->alpha))
		{
			$tmp_alpha_cont = null;
			if (isset($this->search_fields[$this->alpha_field]))
				$tmp_alpha_cont = $this->search_fields;
			else if(isset($this->grid_fields[$this->alpha_field]))
				$tmp_alpha_cont = $this->grid_fields;
			
			if ($tmp_alpha_cont === null)
				ffErrorHandler::raise("Cannot find alpha field into fields", E_USER_ERROR, $this, get_defined_vars());

			$tmp_field = "";
			
			if (strlen($tmp_alpha_cont[$this->alpha_field]->src_table))
				$tmp_field .= "`" . $tmp_alpha_cont[$this->alpha_field]->src_table . "`.";

			$tmp_field .=  "`" . $tmp_alpha_cont[$this->alpha_field]->get_data_source(false) . "`";

			switch ($this->alpha)
			{
				case "cipher":
					//$tmp_sql .= " SUBSTRING( " . $tmp_field . ", 1, 1 ) BETWEEN '0' AND '9' ";
					$tmp_sql .= $tmp_field . " REGEXP '^[0-9]' ";
					break;
				default:
					$tmp_sql .= $tmp_field . " LIKE '" . $this->db[0]->toSql($this->alpha, "Text", false) . "%' ";
			}

			if ($tmp_alpha_cont[$this->alpha_field]->src_having)
			{
				if (strlen($this->sqlHaving))
					$this->sqlHaving .= " AND ";

				$this->sqlHaving .= $tmp_sql;

			}
			else
			{
				if (strlen($this->sqlWhere))
					$this->sqlWhere .= " AND ";

				$this->sqlWhere .= $tmp_sql;
			}
		}
	}

	/**
	 * search params processing function
	 * called by process_params()
	 */
	function process_search_params()
	{
		if (!$this->use_search)
			return;

		if (strlen($this->parent[0]->retrieve_param($this->id, "searched")))
			$this->searched = true;

		foreach ($this->search_fields as $key => $FormField)
		{
			if ($this->search_fields[$key]->skip_search)
				continue;
			
			if (strlen($this->search_fields[$key]->src_table))
				$tblprefix = "`" . $this->search_fields[$key]->src_table . "`.";
			else
				$tblprefix = "";

			if ($this->search_fields[$key]->src_interval)
			{
				// parse from field
				$this->search_fields[$key]->interval_from_value->setValue(	$this->parent[0]->retrieve_param($this->id, $this->search_fields[$key]->id . "_from_src"),
																			$this->search_fields[$key]->get_app_type(),
																			$this->search_fields[$key]->get_locale());

				if (strlen($this->search_fields[$key]->interval_from_value->ori_value))
					$this->searched = true;

				// parse to field
				$this->search_fields[$key]->interval_to_value->setValue(
																			$this->parent[0]->retrieve_param($this->id, $this->search_fields[$key]->id . "_to_src")
																			, $this->search_fields[$key]->get_app_type()
																			, $this->search_fields[$key]->get_locale()
																		);

				if (strlen($this->search_fields[$key]->interval_to_value->ori_value))
					$this->searched = true;
			}
			else
			{
				if ($this->parent[0]->isset_param($this->id, $this->search_fields[$key]->id . "_src"))
				{
					$this->search_fields[$key]->setValue(
															$this->parent[0]->retrieve_param($this->id, $this->search_fields[$key]->id . "_src")
															, $this->search_fields[$key]->get_app_type()
															, $this->search_fields[$key]->get_locale()
														);
				}
				else
				{
					switch ($this->search_fields[$key]->extended_type)
					{
						case "Boolean":
							$this->search_fields[$key]->value = $this->search_fields[$key]->unchecked_value;
							break;

						default:
							$this->search_fields[$key]->value = $this->search_fields[$key]->getDefault(array(&$this));
					}
				}

				if (strlen($this->search_fields[$key]->value->ori_value))
					$this->searched = true;
			}

			$tmp_sql = "";
			if ($this->search_fields[$key]->src_interval && $this->search_fields[$key]->data_type != "")
			{
				// check validity
				$bFindNameTag = preg_match("/\[NAME\]/", $this->search_fields[$key]->src_operation);
				$bFindValueTag = preg_match("/\[VALUE\]/", $this->search_fields[$key]->src_operation);

				if ($bFindValueTag)
					die("You must enter a valid src_operation to use interval search");

				if (strlen($this->search_fields[$key]->interval_from_value->ori_value) || strlen($this->search_fields[$key]->interval_to_value->ori_value))
				{
        			if (strlen($this->search_params))
        				$this->search_params .= "&";

					$tmp_sql = " (";

					if (strlen( $this->search_fields[$key]->interval_from_value->ori_value ))
					{
                		$this->search_params .= $this->getPrefix() . $this->search_fields[$key]->id . "_from_src=" . $this->search_fields[$key]->interval_from_value->ori_value;
                		$tmp_where_value = "";
						if ($this->search_fields[$key]->src_prefix || $this->search_fields[$key]->src_postfix)
						{
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->interval_from_value, $this->search_fields[$key]->base_type, false);
							$tmp_where_value = "'" . $this->search_fields[$key]->src_prefix . $tmp_where_value . $this->search_fields[$key]->src_postfix . "'";
						}
						else
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->interval_from_value, $this->search_fields[$key]->base_type);
						$tmp_sql .= " " . str_replace("[NAME]", $tblprefix . "`" . $this->search_fields[$key]->get_data_source() . "`", $this->search_fields[$key]->src_operation) . " >= " . $tmp_where_value;
					}

					if (strlen( $this->search_fields[$key]->interval_to_value->ori_value ))
					{
						if (strlen( $this->search_fields[$key]->interval_from_value->ori_value ))
						{
							$tmp_sql .= " AND ";
							$this->search_params .= "&";
						}
                		$this->search_params .= $this->getPrefix() . $this->search_fields[$key]->id . "_to_src=" . $this->search_fields[$key]->interval_to_value->ori_value;
						$tmp_where_value = "";
						if ($this->search_fields[$key]->src_prefix || $this->search_fields[$key]->src_postfix)
						{
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->interval_to_value, $this->search_fields[$key]->base_type, false);
							$tmp_where_value = "'" . $this->search_fields[$key]->src_prefix . $tmp_where_value . $this->search_fields[$key]->src_postfix . "'";
						}
						else
							$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->interval_to_value, $this->search_fields[$key]->base_type);
						$tmp_sql .= " " . str_replace("[NAME]", $tblprefix . "`" . $this->search_fields[$key]->get_data_source() . "`", $this->search_fields[$key]->src_operation) . " <= " . $tmp_where_value;
					}

					$tmp_sql .= ") ";
				}
			}
			elseif ($this->search_fields[$key]->data_type != "")
			{
				if (strlen($this->search_fields[$key]->value->ori_value))
				{
    				if (strlen($this->search_params))
        				$this->search_params .= "&";

        			$this->search_params .= $this->getPrefix() . $this->search_fields[$key]->id . "_src=" . $this->search_fields[$key]->getValue();

    				$tmp_where_value = "";

					if ($this->search_fields[$key]->src_prefix || $this->search_fields[$key]->src_postfix)
					{
						$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->value, $this->search_fields[$key]->base_type, false);
						$tmp_where_value = "'" . $this->search_fields[$key]->src_prefix . $tmp_where_value . $this->search_fields[$key]->src_postfix . "'";
					}
					else
						$tmp_where_value = $this->db[0]->toSql($this->search_fields[$key]->value);

					if (is_array($this->search_fields[$key]->src_fields) && count($this->search_fields[$key]->src_fields))
						$tmp_sql .= " ( ";

					$tmp_where_part = $this->search_fields[$key]->src_operation;
					$tmp_where_part = str_replace("[NAME]", $tblprefix . "`" . $this->search_fields[$key]->get_data_source() . "`", $tmp_where_part);
					$tmp_where_part = str_replace("[VALUE]", $tmp_where_value, $tmp_where_part);
					$tmp_sql .= " " . $tmp_where_part . " ";

					if (is_array($this->search_fields[$key]->src_fields) && count($this->search_fields[$key]->src_fields))
					 {
						foreach ($this->search_fields[$key]->src_fields as $addfld_key => $addfld_value)
						{
							$tmp_where_part = $this->search_fields[$key]->src_operation;
							$tmp_where_part = str_replace("[NAME]", $addfld_value, $tmp_where_part);
							$tmp_where_part = str_replace("[VALUE]", $tmp_where_value, $tmp_where_part);
							$tmp_sql .= " OR " . $tmp_where_part . " ";
						}
						reset($this->search_fields[$key]->src_fields);
						$tmp_sql .= " ) ";
					 }
				}
			}
			else
			{
				if (strlen($this->search_params))
					$this->search_params .= "&";
				$this->search_params .= $this->getPrefix() . $this->search_fields[$key]->id . "_src=" . $this->search_fields[$key]->getValue();
			}

			if (strlen($tmp_sql))
			{
				if ($this->search_fields[$key]->src_having)
				{
					if (strlen($this->sqlHaving))
						$this->sqlHaving .= " AND ";

					$this->sqlHaving .= $tmp_sql;

				}
				else
				{
					if (strlen($this->sqlWhere))
						$this->sqlWhere .= " AND ";

					$this->sqlWhere .= $tmp_sql;
				}
			}
		}
		reset($this->search_fields);
		// after retrieving values, build sql where based on user function, is specified

		$res = $this->doEvent("extended_search", array(&$this, $this->sqlWhere, $this->sqlHaving));
		$rc = end($res);
		if ($rc !== null)
		{
            if (is_array($rc))
            {
				$this->sqlWhere = $rc[0];
				$this->sqlHaving = $rc[1];
			}
			else
			{
				$this->sqlWhere = $rc;
			}
		}
	}

	/**
	 * order params processing function
	 * called by process_params()
	 */
	function process_order_params()
	{
		if (!strlen($this->order_default) && !$this->SQL_passthrough)
			die("You MUST set an order by default!");

		$this->order = $this->parent[0]->retrieve_param($this->id, "order");
		$this->direction = $this->parent[0]->retrieve_param($this->id, "direction");

		if (!strlen($this->order)) {
			$is_default = true;
			$this->order = $this->order_default;
		}
		
		if (!$this->SQL_passthrough)
		{
			$tmp = null;
			if (isset($this->key_fields[$this->order]))
				$tmp = $this->key_fields[$this->order];
			else if (isset($this->grid_fields[$this->order]))
				$tmp = $this->grid_fields[$this->order];

			if ($tmp === null)
				ffErrorHandler::raise("Cannot determine order field!", E_USER_ERROR, $this, get_defined_vars());

			if (!strlen($this->direction))
				$this->direction = $tmp->order_dir;

			// build order SQL
			if (strlen($tmp->order_SQL))
			{
				if($is_default) 
				{
					$this->sqlOrder = " " . str_replace("[ORDER_DIR]", $this->direction, $tmp->order_SQL); //. " " .  $this->direction . " ";
				} 
				else 
				{
					if($this->order != $this->order_default) {
						$this->sqlOrder = " " . $tmp->get_order_field() . " " .  $this->direction . " ";
					} else {
						$this->sqlOrder = " " . str_replace("[ORDER_DIR]", $this->direction, $tmp->order_SQL); //. " " .  $this->direction . " ";
					}
				}
			}
			else
			{
				$this->sqlOrder = " " . $tmp->get_order_field() . " " .  $this->direction . " ";				
			}
		}
		
		if($this->tpl !== null) {
			$this->tpl[0]->set_var("actual_order", $this->order);
			$this->tpl[0]->set_var("actual_direction", $this->direction);

			$this->order_params = $this->getPrefix() . "order=" . $this->order . "&" . $this->getPrefix() . "direction=" . $this->direction;

			if($this->use_order) {
				$this->tpl[0]->parse("SectHiddenOrder", false);
			} else {
				$this->tpl[0]->set_var("SectHiddenOrder", "");			
			}
		}
	}

	function parse_hidden_field()
	{
		$this->tpl[0]->parse("SectHiddenField", true);
		$this->parsed_hidden_fields++;
	}
	/**
	 * hidden params template processing function
	 * called by process_interface()
	 */
	function process_hidden()
	{
		foreach ($this->hidden_fields as $key => $value)
		{
			$this->tpl[0]->set_var("id", $key);
			$this->tpl[0]->set_var("value", ffCommon_specialchars($value->getValue(null, FF_SYSTEM_LOCALE)));
			$this->parse_hidden_field();
		}
		reset($this->hidden_fields);
	}

	/**
	 * grid template preparation processing function
	 * called by process_interface()
	 */
	function process_grid()
	{
		$res = $this->doEvent("on_before_process_grid", array(&$this, $this->tpl[0]));

		//$this->process_labels(); 	// also do order, because order is embedded with labels

		if($this->tpl !== null)
		{
			if ($this->display_new) // done at this time due to maxspan
			{
				if (strlen($this->bt_insert_url))
				{
					$temp_url = ffProcessTags($this->bt_insert_url, $this->key_fields, $this->grid_fields, "normal", $this->parent[0]->get_params(), rawurlencode($_SERVER['REQUEST_URI']), $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST));
				}
				else
				{
					if (strlen($this->record_insert_url))
						$temp_url = $this->record_insert_url;
					else
						$temp_url = $this->record_url;
					$temp_url .= "?" . $this->parent[0]->get_keys($this->key_fields) .
								$this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST) . $this->addit_insert_record_param .
								"ret_url=" . rawurlencode($_SERVER['REQUEST_URI']);
				}
				$this->tpl[0]->set_var("addnew_url", ffCommon_specialchars($temp_url));
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
		}

        if (strlen($this->processed_SQL)) 
        {
			do
			{
				$sSQL = $this->processed_SQL;

				if (!$this->SQL_passthrough)
				{
					if (
						$this->pagination_save_memory 
						&& strpos($sSQL, "LIMIT") === false 
						&& strpos($sSQL, "SQL_CALC_FOUND_ROWS") === false 
						&& strpos($sSQL, "SELECT") === 0
					)
					{
						if ($this->rrow !== false)
						{
							$sSQL = "SELECT SQL_CALC_FOUND_ROWS" . substr($sSQL, 6);
							$sSQL .= " LIMIT " . intval($this->rrow) . ", 1";
							$this->pagination_save_memory_in_use = true;
						}
						elseif ($this->use_paging)
						{
							$sSQL = "SELECT SQL_CALC_FOUND_ROWS" . substr($sSQL, 6);
							$sSQL .= " LIMIT " . intval($this->records_per_page * ($this->page - 1)) . ", " . intval($this->records_per_page);
							$this->pagination_save_memory_in_use = true;
						}
					}
				}			

				$this->db[0]->query($sSQL);

				// fix if out of bounds
				if ($num_rows = $this->db[0]->numRows())
				{
					$wanted_record = ($this->rrow ? $this->rrow : 
							($this->use_paging ? $this->records_per_page * ($this->page - 1) : 0)
						);
					if ($num_rows <= $wanted_record)
					{
						if ($this->use_paging)
							$this->page = floor($num_rows / $this->records_per_page) + 1;
						if ($this->rrow !== false)
							$this->rrow = $num_rows - 1;
					}
					else
						break;
				}
				else
					break;
			} while (true);
		}
		
		$res = $this->doEvent("on_process_grid", array(&$this, $this->tpl[0]));
	}
	
	function processRow()
	{
		foreach ($this->grid_fields as $key => $ff)
		{
			if ($ff->crypt)
			{
				if (MOD_SEC_CRYPT && $ff->crypt_modsec)
				{
					$value = $ff->value->getValue(null, FF_SYSTEM_LOCALE);
					
					if ($ff->crypt_concat)
						$value = mod_sec_decrypt_concat($value);
					else
						$value = mod_sec_decrypt_string($value);
					
					$this->grid_fields[$key]->value->setValue($value, null, FF_SYSTEM_LOCALE);
					$this->grid_fields[$key]->value_ori->setValue($value, null, FF_SYSTEM_LOCALE);
				}
			}
		}
		reset($this->grid_fields);
	}

	/**
	 * action buttons processing function
	 * called by process_interface()
	 */
	function process_action_buttons()
	{
		if (!$this->display_actions)
		{
			$this->tpl[0]->set_var("SectActionButtons", "");
			return;
		}

		$count = 0;
		if (is_array($this->action_buttons) && count($this->action_buttons))
		{
			foreach ($this->action_buttons as $key => $FormButton)
			{
				if (!isset($this->buttons_options[$key]["display"]) || $this->buttons_options[$key]["display"] !== false)
				{
					$this->tpl[0]->set_var("ActionButton", $this->action_buttons[$key]->process());
					$this->tpl[0]->parse("SectAction", true);
					$count++;
				}
			}
			reset($this->action_buttons);
			$this->tpl[0]->parse("SectActionButtons", false);
		}
		
		if ($count)
			$this->tpl[0]->parse("SectActionButtons", false);
		else
		{
			$this->tpl[0]->set_var("SectAction", "");
			$this->tpl[0]->set_var("SectActionButtons", "");
		}
	}
    /**
     * action buttons processing function
     * called by process_interface()
     */
    function process_action_buttons_header()
    {
        if (!$this->display_actions)
        {
            $this->tpl[0]->set_var("SectActionButtonsHeader", "");
            return;
        }

		$count = 0;
        if (is_array($this->action_buttons_header) && count($this->action_buttons_header))
        {
            foreach ($this->action_buttons_header as $key => $FormButton)
            {
				if (!isset($this->buttons_options[$key]["display"]) || $this->buttons_options[$key]["display"] !== false)
				{
					$this->tpl[0]->set_var("ActionButtonHeader", $this->action_buttons_header[$key]->process());
					$this->tpl[0]->parse("SectActionHeader", true);
					$count++;
				}
            }
            reset($this->action_buttons_header);
        }
		
		if ($count)
			$this->tpl[0]->parse("SectActionButtonsHeader", false);
        else
        {
            $this->tpl[0]->set_var("SectActionHeader", "");
            $this->tpl[0]->set_var("SectActionButtonsHeader", "");
        }
    }

	/**
	 * action processing function
	 * called by process()
	 */
	function process_action()
	{
		if (strlen($this->frmAction))
		{
			
			$res = $this->doEvent("on_do_action", array(&$this, $this->frmAction));
			if ($rc = end($res))
				return;
		}
	}

	/**
	 * template set properties
	 * called by process_interface()
	 */
	function setProperties()
	{
		if (is_array($this->properties) && count($this->properties))
		{
			foreach ($this->properties as $key => $value)
			{
				if (is_array($this->properties[$key]) && count($this->properties[$key]))
				{
					do
					{
						$subkey = key($this->properties[$key]);
						if ($this->properties[$key][$subkey] !== null)
						{
							$this->tpl[0]->set_var($key . "_" . $subkey, $this->properties[$key][$subkey]);
							$this->tpl[0]->parse("Sect" . $key . "_" . $subkey, false);
						}
						else
						{
							$this->tpl[0]->set_var("Sect" . $key . "_" . $subkey, "");
						}
					}
					while(next($this->properties[$key]) !== false);
					reset($this->properties[$key]);
				}
			}
			reset($this->properties);
		}
	}

	/**
	 * Esegue il redirect all'url specificato
	 * @param String $url
	 */
	function redirect($url, $response = null)
	{
		return ffRedirect($url, null, null, ($response === null ? $this->json_result : $response));
	}

	/**
	 *
	 * @param boolean $returnurl se dev'essere restituito l'url o effettuato il redirect immediato
	 * @param String $type il tipo di dialog (okonly, yesno..)
	 * @param String $title il titolo del dialog
	 * @param String $message il messaggio visualizzato
	 * @param String $cancelurl l'url di negazione/ritorno
	 * @param String $confirmurl l'url di conferma/proseguimento
	 * @return String
	 */
	function dialog($returnurl = false, $type, $title, $message, $cancelurl, $confirmurl)
	{
		if ($this->dialog_path === null)
			$dialog_path = $this->parent[0]->getThemePath() . "/ff/dialog";
		else
			$dialog_path = $this->parent[0]->site_path . $this->dialog_path;
			
		$message = ffProcessTags($message, $this->key_fields, $this->grid_fields, "normal");

		$res = $this->doEvent("onDialog", array($this, $returnurl, $type, $title, $message, $cancelurl, $confirmurl, $dialog_path));
		$ret = end($res);
		if ($ret === null)
			$ret = ffDialog($returnurl, $type, $title, $message, $cancelurl, $confirmurl, $dialog_path);

		if ($returnurl)
			return $ret;
		else
			$this->redirect($ret);
	}

    /**
	 * inizializza i pulsanti di default
	 */
	function initControls()
    {
		// PREPARE DEFAULT BUTTONS
		if ($this->buttons_options["search"]["display"])
		{
			if ($this->buttons_options["search"]["obj"] !== null)
			{
				$this->addSearchButton(	  $this->buttons_options["search"]["obj"]
										, $this->buttons_options["search"]["index"]);
			}
			else
			{
				$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
				$tmp->id 			= "searched";
				$tmp->label 		= $this->buttons_options["search"]["label"];
				$tmp->aspect 		= "link";
				$tmp->action_type 	= "submit";
				$tmp->frmAction		= "search";
				$this->addSearchButton(	  $tmp
										, $this->buttons_options["search"]["index"]);
			}
		}
		if ($this->buttons_options["export"]["display"])
		{
			if(!strlen($this->buttons_options["export"]["label"]))
				$this->buttons_options["export"]["label"] = ffTemplate::_get_word_by_code("ffGrid_export");

			if ($this->buttons_options["export"]["obj"] !== null)
			{
				$this->addActionButtonHeader($this->buttons_options["export"]["obj"]);
			}
			else
			{
				$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->theme);
				$tmp->id 			= "export";
				$tmp->label 		= $this->buttons_options["export"]["label"];
				$tmp->aspect 		= "link";
				$tmp->action_type 	= "submit";
				$tmp->frmAction		= "export";
				//$tmp->aspect 		= "link";
				//$tmp->action_type 	= "gotourl";
				if  (strlen($tmp->class)) $tmp->class .= " ";
				$tmp->class .= "noactivebuttons";
				//$tmp->form_action_url = $this->parent[0]->getRequestUri() . "&" . $this->getIDIF() . "_t=xls";
				//$tmp->jsaction = "ff.ajax.doRequest({'component' : '" . $this->getIDIF() . "', 'addFields' : '" . $this->getIDIF() . "t=xls'});";
				//$tmp->class 		.= "noactivebuttons";
				//$tmp->url = $this->parent[0]->getRequestUri() . "&" . $this->getIDIF() . "_t=xls";
				$this->addActionButtonHeader($tmp);
			}
		}
	}
}
