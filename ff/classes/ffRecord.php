<?php 
/**
 * Visualizzazione di un singolo record.
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Visualizzazione di un singolo record.
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
if (!defined("FF_ENABLE_MEM_PAGE_CACHING"))         define("FF_ENABLE_MEM_PAGE_CACHING", false);

class ffRecord
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
	 * Questa funzione crea un'istanza di ffRecord basandosi sui parametri in ingresso
	 * 
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $theme
	 * @param array $variant
	 * @return ffRecord_base
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
 * Visualizzazione di un singolo record.
 * 
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffRecord_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)

	/**
	 * ID di ffRecord
	 * @var String
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
	 * percorso relativo al web di accesso alla pagina (dalla root del sito)
	 * @var String
	 */
	var $page_path 				= "";

	/**
	 * Classe HTML da inserire nel template
	 * @var String
	 */
	var $class 					= "ffRecord";
	
	/**
	 * Usato da ffPage, determina se il componente dev'essere posizionato in una locazione
	 * avente nome l'id del record stesso piuttosto che accodato nel contenuto
	 * @var Boolean
	 */
	var $use_own_location 		= false;

	/**
	 * Sovrascrive l'id quando use_own_location è uguale a true
	 * @var String
	 */
	var $location_name 			= null;

	/**
	 * Se TRUE forza le posizioni dei campi anzichè posizionarli dinamicamente
	 * Quindi non vengono utilizzate le sezioni dinamiche per la loro creazione
	 * @var Boolean
	 */
	var $use_fixed_fields		= false;

	/**
	 * Il tema da utilizzare nei rendering
	 * @var String
	 */
	var $theme					= null;
	/**
	 * La directory da dove attingere il template
	 * @var String
	 */
	var $template_dir			= null;					// Where to locate the template. Default to theme dir

	/**
	 * Un eventuale contenuto HTML fisso da pre-porre all'elaborazione
	 * @var String
	 */
	var $fixed_pre_content		= "";
	/**
	 * Un eventuale contenuto HTML fisso da post-porre all'elaborazione
	 * @var String
	 */
	var $fixed_post_content		= "";
	
	/**
	 * Un array di variabili da impostare all'interno del template, organizzate in coppie chiave => valore
	 * @var Array 
	 */
	var $fixed_vars				= array();

	/**
	 * Usato da ffPage, elabora il componente ma non lo accoda ai buffer
	 * @var Boolean
	 */
	var $display 				= true;

	/**
	 * Il percorso da utilizzare per il "dialog", ossia per effettuare richieste all'utente
	 * se null vale FF_SITE_PATH . "/dialog"
	 * In ogni caso ffRecord aggiunge "/dialog" alla fine del percorso
	 * @var String
	 */
	var $dialog_path			= null;
	var $url_delete = null;

	/**
	 * Link alla pagina precedente;
	 * è il link al quale si è reindirizzati quando si ha
	 * la necessità di navigare "all'indietro".
	 * Di default è blank; in questo caso il valore verrà composto
	 * automaticamente quando si accede a questa pagina tramite un'altra ffPage
	 * @var String
	 */
	var $ret_url				= "";

	/**
	 * Titolo del record
	 * @var String
	 */
	var $title					= "";					

	/**
	 * Tabella del DB sulla quale ffRecord eseguirà le operazioni
	 * @var String
	 */
	var $src_table 				= "";

	/**
	 * Nasconde tutti i pulsanti e controlli vari
	 * @var Boolean
	 */
	var $hide_all_controls		= false;

	/**
	 * Contiene tutti i pulsanti associati a ffRecord;
	 * per ogni pulsante (insert, update, delete, cancel) è possibile specificare i valori di "display" (true o false)
	 * "index" (la posizione), "obj" e "label"
	 * @var array()
	 */
	var $buttons_options		= array(
											"insert" => array(
														  "display" => true
														, "index" 	=> 1
														, "obj" 	=> null
														, "label" 	=> "Inserisci"
                                                        , "aspect"  => "button"
											  				)
										  , "update" => array(
														  "display" => true
														, "index" 	=> 2
														, "obj" 	=> null
														, "label" 	=> "Aggiorna"
                                                        , "aspect"  => "button"
											  				)

										  , "delete" => array(
														  "display" => true
														, "index" 	=> 1
														, "obj" 	=> null
														, "label" 	=> "Elimina"
                                                        , "aspect"  => "button"
											  				)

										  , "cancel" => array(
														  "display" => true
														, "index" 	=> 0
														, "obj" 	=> null
														, "label" 	=> "Indietro"
                                                        , "aspect"  => "button"
											  				)
										);

	/**
	 * Visualizza un simbolo nel caso in cui il campo sia obbligatorio
	 * la modalità di visualizzazione del simbolo dipende dal renderer
	 * @var Boolean
	 */
	var $display_required		= true;

	/**
	 * Simbolo che viene utilizzato da $display_required
	 * @var String
	 */
	var $required_symbol		= "*";

	/**
	 * Visualizza una nota che spiega $display_required
	 * @var Boolean
	 */
	var $display_required_note	= false;					// display a footnote that explain the required symbol

	/**
	 * Determina se possono essere eseguite operazioni sul DB
	 * Se è true le azioni vengono comunque processate, ma nessuna modifica ha effetto sul database
	 * @var Boolean
	 */
	var $skip_action			= false;
	
	var $skip_insert_if_empty	= false;
	
	/**
	 * Determina se evitare completamente di eseguire gli eventi relativi alle azioni nel caso in cui
	 * sia presente un errore nel record
	 * @var Boolean
	 */
	var $skip_events_on_error	= true;

	/**
	 * Associa le azioni standard ad eventuali azioni custom dell'utente
	 * il formato è la classica coppia chiave valore, dove chiave è l'azione custom e valore è l'azione standard
	 * @var Array
	 */
	var $default_actions		= array();				/* ES.:
														 * array( 
														 * "mycustomaction" => "update"
														 *		)
														 */
	
	/**
	 * Utilizza il record non per l'editing ma per la visualizzazione dei dati
	 * Non verrando renderizzati ffField
	 * @var Boolean
	 */
	var $display_values			= false;

	/**
	 * Forza il record a ricaricare ad ogni azione i dati dal database, di fatto ignorando l'intervento dell'utente
	 * @var Boolean
	 */
	var $ever_reload_data		= false;

	/**
	 * Array di campi addizionali da impostare; il valore è un ffData
	 * @var array()
	 */
	var $additional_fields		= array();

	/**
	 * Array di campi chiave addizionali; il valore è un ffData
	 * @var array()
	 */
	var $additional_key_fields	= array();
	
	/**
	 * Stabilisce le dipendenze fra il record e le widgets
	 * Eventuali widgets impostate verranno caricate automaticamente all'istanziazione del componente
	 * il formato è un elenco di valori, dove valore è il nome della widget
	 * @var Array
	 */
	var $widget_deps 			= array();
	
	var $processed_widgets		= array();
	
	/**
	 * Abilita l'inserimento
	 * @var Boolean
	 */
	var $allow_insert			= true;

	/**
	 * Array contenente tutti i campi addizionali da aggiungere alle operazioni d'inserimento su DB
	 * @var array()
	 */
	var $insert_additional_fields = array();

	/**
	 * Abilita l'auto popolazione del record in fase d'inserimento
	 * Questo può avvenire con un SQL o con un array di valori
	 * @var Boolean
	 */
	var $auto_populate_insert	= false;

	/**
	 * La stringa SQL da utilizzare nell'auto_populate_insert
	 * @var String
	 */
	var $populate_insert_SQL 	= null;

	/**
	 * L'array di dati da usare nell'auto_populate_insert
	 * Il formato è un elenco di coppie chiave/valore, dove chiave è il nome del campo e valore è un ffData
	 * @var Array
	 */
	var $populate_insert_array	= null;

	/**
	 * Abilita l'update di ffRecord
	 * @var Boolean
	 */
	var $allow_update			= true;

	/**
	 * Array contenente tutti i campi addizionali da aggiungere alle operazioni d'aggiornamento su DB
	 * @var array()
	 */
	var $update_additional_fields = array();

	/**
	 * Abilita l'auto populate in edit
	 * Questo può avvenire con un SQL o con un array di valori
	 * @var Boolean
	 */
	var $auto_populate_edit		= false;

	/**
	 * La stringa SQL da utilizzare nell'auto_populate_edit
	 * @var String
	 */
	var $populate_edit_SQL 		= null;
	
	/**
	 * L'array di dati da usare nell'auto_populate_edit
	 * Il formato è un elenco di coppie chiave/valore, dove chiave è il nome del campo e valore è un ffData
	 * @var Array
	 */
	var $populate_edit_array	= null;

	/**
	 * Abilita il delete di ffRecord
	 * @var ffRecord
	 */
	var $allow_delete			= true;

	/**
	 * Controlla se ffRecord deve eseguire un delete specifico
	 * che dipende dal risultato di un array di query SQL salvate in un array.
	 * I valori che può assumere sono: null, "single", "multi", "error"
	 * Per il comportamento standard lasciare vuoto.
	 * Se $del_auto_action = single ffRecord selezionerà "update" se una select
	 * all'interno dell'array restituisce un record, altrimenti selezionerà "delete"
	 * Se $del_auto_action = multi, ffRecord selezionerà "multi_update" oppure "multi_delete"
	 * (stesse condizioni del caso precedente)
	 * $del_auto_action = error, ffRecord restituisce un errore ed annulla l'azione se una
	 * select all'interno dell'array restituisce un record
	 * Nelle query SQL bisogna specificare i valori degli ffField utilizzando un tag seguito da
	 * _VALUE (ad es. [NOMETAG_VALUE])
	 * ES: supponiamo di voler annullare l'azione di "delete" di un record in una tabella chiamata
	 * "users" se esistono record correlati in due tabelle chiamate "post" e "polls".
	 * La chiave primaria della tabella "users" è un campo singolo chiamato "ID" memorizzato nelle altre
	 * tabelle come "ID_user".
	 * Si può utilizzare $del_auto_action = error ed un array come il seguente per eseguire il controllo:
	 *  array(	"SELECT * FROM posts WHERE ID_user = [ID_VALUE]",
				"SELECT * FROM polls WHERE ID_user = [ID_VALUE]" );
	 * @var String
	 */

	var $del_auto_action		= "";

	/**
	 * Array contenente le query SQL per eseguire il controllo
	 * @var array()
	 */
	var $del_auto_SQL 			= array();				

	/**
	 * Controlla come funzione l'eliminazione dei record.
	 * I valori sono: "delete", "multi_delete", "update" e "multi_update"
	 * Se $del_action = "delete", il record viene fisicamente eliminato.
	 * E' l'azione più semplice e meno sicura.
	 * Se $del_action = "multi_delete" il record è fisicamente eliminato e viene
	 * eseguito un insieme aggiuntivo di query SQL prese dall'array $multi_delete.
	 * Questa è la scelta meno sicura.
	 * Nelle query SQL bisogna specificare i valori degli ffField utilizzando un tag seguito da
	 * _VALUE (ad es. [NOMETAG_VALUE])
	 * ES: supponiamo di voler annullare l'azione di "delete" di un record in una tabella chiamata
	 * "users" se esistono record correlati in due tabelle chiamate "post" e "polls".
	 * La chiave primaria della tabella "users" è un campo singolo chiamato "ID" memorizzato nelle altre
	 * tabelle come "ID_user".
	 * Si può utilizzare $del_auto_action = error ed un array come il seguente per eseguire il controllo:
	 *  array(	"SELECT * FROM posts WHERE ID_user = [ID_VALUE]",
				"SELECT * FROM polls WHERE ID_user = [ID_VALUE]" );
	 * Se si sceglie "update", viene settato un campo del record anzichè rimuovere fisicamente l'intero record.
	 * E' la scelta più sicura ed è raccomandata per la gran parte delle situazioni.
	 * In questo caso si ha una stringa con il nome dei campi e i valori separati da virgola.
	 * Se per esempio si vogliono settare due campi "deleted" e "deleted_time" con il valore "1" ed
	 * il timestamp corrente:
	 * "deleted = 1, deleted_time = [DATETIME]"
	 * multi_update opera nello stesso modo di multi_delete.
	 * array(	"UPDATE posts SET deleted = '1', deleted_time = [DATETIME]
						WHERE ID_user = [ID_VALUE]",
				"UPDATE polls SET deleted = '1', deleted_time = [DATETIME]
						WHERE ID_user = [ID_VALUE]" );
	 * NB: se si utilizza "multi_update" viene eseguita un update classico sul record corrente
	 * in base a $del_update; bisogna quindi specificarli entrambi.
	 * @var String
	 */
	
	var $del_action				= "delete";				
	var $del_multi_delete 		= array();
	var $del_update				= "";
	var $del_multi_update 		= array();
	
	var $label_error_required 	= "Il campo [LABEL] è obbligatorio";
	var $label_error_range_min	= "Il campo [LABEL] è troppo piccolo";
	var $label_error_range_max	= "Il campo [LABEL] è troppo grande";
	var $label_error_nomatch 	= "Il campo [LABEL] non corrisponde";
	var $label_delete_record	= "Confermi l'eliminazione del dato?<br /><span>Il dato verr&agrave; eliminato definitivamente, non potr&agrave; essere recuperato.</span>";

	var $error_on_norecord		= true;
	var $error_on_norecord_title		= "Nessun Record Trovato";
	var $error_on_norecord_message		= "Il record selezionato non è stato trovato";
	// -----------------------------------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode with a nice flare effect! :-)

	var $parent					= null;

	/**
	 * Contiene gli ffField da gestire
	 * @var array()
	 */
	var $contents				= array();

	/**
	 * Contiene gli ffField da gestire
	 * @var array()
	 */
	var $form_fields			= array();				

	/**
	 * Contiene i campi chiave di ffRecord
	 * @var array()
	 */
	var $key_fields				= array();

	var $hidden_fields			= array();
	
	/**
	 * Array di chiavi utilizzato per determinare il record
	 * recuperato utilizzando i parametri
	 * @var array()
	 */
	
	var $keys					= array();				
	/**
	 * Array di ffButton usati per gestire le azioni su ffRecord
	 * @var array()
	 */
	var $action_buttons			= array();				

	/**
	 * Varibiali per la navigazione / ricerca / ordinamento
	 * utilizzate precedentemente
	 * @var String
	 */
	var $transit_params			= "";					

	/**
	 * Azione eseguita su ffRecord
	 * @var String
	 */
	var $frmAction				= "";					

	/**
	 * Descrizione dell'errore
	 * @var String
	 */
	var $strError				= "";					

	/**
	 * WHERE della query SQL per caricare il record da DB; � basata sull'array key[]
	 * @var String
	 */
	var $sWhere					= "";					
	var $sAddWhere				= "";

	/**
	 * URL completo della pagina ffRecord
	 * @var String
	 */
	var $url					= "";

    var $use_cache              = FF_ENABLE_MEM_PAGE_CACHING;

    var $record_exist 			= false;
	var $first_access 			= false;

	var $detail					= null;

	var $db						= null;					// Internal DB_Sql() Object

	var $groups					= null;
	var $tplSection				= array();
	
	var $contain_error 			= false;

	var $ajax 					= false;

	public $params				= "";
	
	var $cache_get_resources	= array();
	var $cache_clear_resources	= array();

	var $resources = array();
	var $resources_set = array();
	var $resources_get = array();

	var $libraries	= array();
	var $js_deps	= array();
	var $css_deps	= array();
	
	abstract protected 	function 	tplLoad				();
	abstract public 	function 	tplParse			($output_result);
	abstract public 	function 	tplDisplay 			();
	abstract protected 	function 	tplDisplayContents	();
	abstract protected 	function 	tplDisplayControls	();
	abstract public 	function 	tplDisplayError		($sError = null);

	var $json_result = array();
	
	// ---------------------------------------------------------------
	//  PUBLIC FUNCS

	/**
	 * costruttore
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $theme
	 * @return ffRecord_base
	 */
	function __construct(ffPage_base $page, $disk_path, $theme)
	{
		$this->get_defaults("ffRecord");
		$this->get_defaults();

		$this->site_path = $page->site_path;
		$this->page_path = $page->page_path;
		$this->disk_path = $disk_path;
		$this->theme = $theme;

		if ($this->db === null)
			$this->db[0] = ffDB_Sql::factory();

		if($this->use_cache)
			$this->addEvent("on_done_action", "ffRecord_reset_cache", ffEvent::PRIORITY_HIGH);
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
		if ($id === null)
			$id = uniqid(time(), true);
		
		if ($content !== null)
		{
			if (is_object($content)	&& is_subclass_of($content, "ffField_base"))
			{
				$id = $content->id;
				$content->parent = array(&$this);
				$content->cont_array =& $this->form_fields;

				$this->form_fields[$id] = $content;
				$this->form_fields[$id]->group = $group;
			}
			
			if (is_object($content)	&& is_subclass_of($content, "ffDetails_base"))
			{
				$content->main_record = array(&$this);
				$this->detail[$content->id] = $content;
			}

			if (is_object($content)	&& is_subclass_of($content, "ffGrid_base"))
			{
				$id = $content->id;
			}

			if ($group === null)
			{
				$this->contents[(string)$id]["data"] = $content;
				$this->contents[(string)$id]["group"] = $group;
			}
			else
			{
				$this->groups[$group]["contents"][(string)$id]["data"] = $content;
			}
		}
		elseif ($group === true)
		{
			$this->contents[(string)$id]["data"] = $content;
			$this->contents[(string)$id]["group"] = $group;
		}
		else
			ffErrorHandler::raise("Unhandled Content", E_USER_ERROR, $this, get_defined_vars());
	}

	/**
	 * Aggiunge il campo chiave a ffRecord
	 * @param ffField Il Field che farà da key field all'interno di ffRecord
	 * @return ffField L'id di ffField
	 */
	function addKeyField($field)
	{
		if (!is_subclass_of($field, "ffField_base"))
            ffErrorHandler::raise("Wrong call to addKeyField: object must be a ffField"
                            , E_USER_ERROR, $this, get_defined_vars());
                                
		$field->parent = array(&$this);
		$field->cont_array =& $this->key_fields;
		$this->key_fields[$field->id] = $field;
		return $field->id;
	}

	function addHiddenField($name, $value = null)
	{
		if ($value === null)
			$value = new ffData();

		$this->hidden_fields[$name] = $value;
	}

	/**
	* prepara l'oggetto all'elaborazione
	* elabora i parametri immessi dall'utente attraverso l'oggetto pagina e carica il template
	*/
	function pre_process()
	{
		// Load Template and initialize it
		$this->tplLoad();

		// First of all, process all page's params
		$this->process_params();
	}

	/**
	* elabora l'oggetto, senza restituire il risultato dell'elaborazione
	*/
	function process()
	{
		$this->retrieve_fields();
		if ($this->detail !== null)
		{
			foreach ($this->detail as $key => $value)
			{
				$this->detail[$key]->pre_process();
				$this->detail[$key]->retrieve_fields();
			}
			reset($this->detail);
		}

		// check fields values
		$this->check_fields();
		if ($this->detail !== null)
		{
			foreach ($this->detail as $key => $value)
			{
				$this->detail[$key]->check_fields();
			}
			reset($this->detail);
		}

		// manage actions. This may cause a redirect, so the end is never reached
		$this->process_action();
	}

	/**
	* elabora l'interfaccia dell'oggeto
	* @param bool se il risultato dev'essere visualizzato o meno, di default a false
	*/
	function process_interface($output_result = false)
	{
		 $res = ffRecord::doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		 $res = $this->doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		// -- INTERFACE STEP
		// do init stuffs
		$this->initControls();

		// display fields, if process_action() don't cause a redirection
		$this->tplDisplay();

		if ($output_result !== null)
			return $this->tplParse($output_result);
	}

	/**
	* recupera la directory del template
	* @return string
	*/
	function getTemplateDir()
	{
		$res = $this->doEvent("getTemplateDir", array($this));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffRecord";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}
	
	/**
	* recupera il tema in utilizzo
	* @return string
	*/
	function getTheme()
	{
		return $this->theme;
	}

	/**
	* elabora i parametri relativi all'oggetto principale ed ai componenti collegati
	*/
	function process_params()
	{
		$this->frmAction = $this->parent[0]->retrieve_param($this->id, "frmAction");

		if ($this->detail !== null)
		{
			foreach ($this->detail as $key => $value)
			{
				$this->detail[$key]->frmAction = $this->frmAction;
			}
			reset($this->detail);
		}

		// process keys
		if (is_array($this->key_fields) && count($this->key_fields))
		{
			$this->sWhere = "";
			foreach ($this->key_fields as $key => $value)
			{
				// NB: hidden/key fields ever store values in SYSTEM_LOCALE
				$this->key_fields[$key]->setValue(	$this->parent[0]->retrieve_key($key),
													$this->key_fields[$key]->base_type,
													FF_SYSTEM_LOCALE);

				// get original value of control associated with the key, if exist
				if (isset($this->form_fields[$key]))
					$this->form_fields[$key]->value_ori->setValue(	$this->parent[0]->keys[$key],
																	$this->key_fields[$key]->base_type,
																	FF_SYSTEM_LOCALE);

				// if a key value is given, build sWhere
				if ($this->key_fields[$key]->value->getValue() !== null)
				{
					$tmp = $this->key_fields[$key]->getValue();
					// Build WHERE For RECORD operation
					if (strlen($this->sWhere))
						$this->sWhere .= " AND ";
					$this->sWhere .= " " . $this->key_fields[$key]->get_data_source() . " = " . $this->db[0]->toSql($this->key_fields[$key]->value, $this->key_fields[$key]->base_type);
				}
			}
			reset ($this->key_fields);
		}
			
		if (is_array($this->additional_key_fields) && count($this->additional_key_fields))
		{
			$this->sAddWhere = "";
			foreach ($this->additional_key_fields as $key => $value)
			{
				$this->sAddWhere .= " AND `" . $key . "` = " . $this->db[0]->toSql($value);
			}
			reset ($this->additional_key_fields);
		}
	}

	/**
	* recupera i dati del record, a seconda della modalità di accesso
	* vedi first_access, record_exist ed ever_reload_data
	*/
	function retrieve_fields()
	{
		if (strlen($this->sWhere))
			$this->record_exist = true;

		if (!strlen($this->parent[0]->frmAction) || $this->frmAction == "confirmdelete")
			$this->first_access = true;

		foreach ($this->form_fields as $key => $value)
		{
			$this->form_fields[$key]->widget_init();
		}
		reset($this->form_fields);

		// EVENT HANDLER
		$res = $this->doEvent("on_load_data", array($this));
		$rc = end($res);
		if ($rc !== null)
			return;

		// Retrieve the fields values from the proper place.
		if ($this->record_exist && ($this->first_access || $this->ever_reload_data) && $this->src_table)
		{ // retrieve fields from DB
			if ($this->auto_populate_edit)
			{
				$tmp_sql = ffProcessTags($this->populate_edit_SQL, $this->key_fields, $this->form_fields, "sql", "", "", "", "", $this->db[0]);
				$this->db[0]->query($tmp_sql);
				
				if ($this->db[0]->nextRecord())
				{
					foreach ($this->form_fields as $key => $value)
					{
						switch ($this->form_fields[$key]->data_type)
						{
							case "db":
								if ($this->form_fields[$key]->extended_type != "Password")
								{
									if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
									{
										foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
										{
											$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
											if ($this->db[0]->isSetField($element))
												$this->form_fields[$key]->multi_values[$subkey] = ($this->form_fields[$key]->crypt ? ff_getDecryptedField($this->db[0], $element, $subvalue["type"]) : $this->db[0]->getField($element, $subvalue["type"]));
											elseif (isset($subvalue["default"]))
												$this->form_fields[$key]->multi_values[$subkey] = $subvalue["default"];
											else
												$this->form_fields[$key]->multi_values[$subkey] = new ffData("", $subvalue["type"]);
										}
										reset ($this->form_fields[$key]->multi_fields);
										$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
									}
									else
									{
										if ($this->db[0]->isSetField($this->form_fields[$key]->get_data_source()))
										{
											$tmp = ($this->form_fields[$key]->crypt ? 
															ff_getDecryptedField($this->db[0], $this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type)
															: $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type));
											//$this->form_fields[$key]->value = $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type);
											$this->form_fields[$key]->setValue($tmp->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale()));
										}
										else
											$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
									}
										
									$res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->form_fields[$key]));
									$rc = end($res);
									if ($rc !== null)
										$this->form_fields[$key]->value = $rc;
								}
								break;

							case "callback":
								$this->form_fields[$key]->value = call_user_func($this->form_fields[$key]->get_data_source(), $this->form_fields, $key, $this->first_access, $this);
								break;

							case "":
								$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
								break;
						}
							
						if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
						{
							$this->form_fields[$key]->multi_values_ori = $this->form_fields[$key]->multi_values;
							$this->form_fields[$key]->value_ori = $this->form_fields[$key]->value;
						}
						elseif (!isset($this->key_fields[$key]) && $this->form_fields[$key]->value_ori->getValue() === null) // ori values of key fields are retrieved before
							$this->form_fields[$key]->value_ori = clone $this->form_fields[$key]->value;
					}
					reset($this->form_fields);
				}
				else
				{
					if ($this->error_on_norecord)
						ffErrorHandler::raise("NO RECORD FOUND", E_USER_ERROR, $this, get_defined_vars());
					else
					{
						if (isset($_REQUEST["XHR_CTX_ID"]))
							$this->dialog(false, "okonly", $this->error_on_norecord_title, $this->error_on_norecord_message, "", "[CLOSEDIALOG]");
						else
							$this->dialog(false, "okonly", $this->error_on_norecord_title, $this->error_on_norecord_message, "", $this->parent[0]->ret_url);
					}
				}
			}
			else
			{
				$sql = "SELECT * FROM `" . $this->src_table . "` WHERE " . $this->sWhere . $this->sAddWhere;

				$this->db[0]->query($sql);

				if ($this->db[0]->nextRecord())
				{
					foreach ($this->form_fields as $key => $FormField)
					{
						if (!($this->form_fields[$key]->extended_type == "Password" || (strlen($this->form_fields[$key]->compare) && !strlen($this->form_fields[$key]->data_source)))) // Password must not stored on the form
						{
							// value retrivied in db format (the default)
							switch ($this->form_fields[$key]->data_type)
							{

								case "db":
									if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
									{
										foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
										{
											$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
											$this->form_fields[$key]->multi_values[$subkey] = ($this->form_fields[$key]->crypt ? ff_getDecryptedField($this->db[0], $element, $subvalue["type"]) : $this->db[0]->getField($element, $subvalue["type"]));
										}
										reset ($this->form_fields[$key]->multi_fields);
										$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
									}
									else
									{
										//$this->form_fields[$key]->value = $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type);
										$tmp = ($this->form_fields[$key]->crypt ? 
														ff_getDecryptedField($this->db[0], $this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type)
														: $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type));
										
										//$this->form_fields[$key]->value = new ffData($tmp->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale()), $this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale());
										$this->form_fields[$key]->setValue($tmp->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale()));
										//if ($this->form_fields[$key]->extended_type == "Selection")
										//	$this->form_fields[$key]->pre_process(true, $this->form_fields[$key]->value);
									}

									$res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, &$this->form_fields[$key]));
									$rc = end($res);
									if ($rc !== null)
										$this->form_fields[$key]->value = $rc;
									break;

								case "callback":
									$this->form_fields[$key]->value = call_user_func($this->form_fields[$key]->get_data_source(), $this->form_fields, $key, $this->first_access, $this);
									break;

								case "":
									$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
									break;
							}
							
							if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
							{
								$this->form_fields[$key]->multi_values_ori = $this->form_fields[$key]->multi_values;
								$this->form_fields[$key]->value_ori = $this->form_fields[$key]->value;
							}
							elseif (!isset($this->key_fields[$key]) && $this->form_fields[$key]->value_ori->getValue() === null) // ori values of key fields are retrieved before
								$this->form_fields[$key]->value_ori = clone $this->form_fields[$key]->value;
						}
					}
					reset($this->form_fields);
				}
				else
				{
					if (isset($_REQUEST["XHR_CTX_ID"]))
						$this->dialog(false, "okonly", $this->error_on_norecord_title, $this->error_on_norecord_message, "", "[CLOSEDIALOG]");
					else
						$this->dialog(false, "okonly", $this->error_on_norecord_title, $this->error_on_norecord_message, "", $this->parent[0]->ret_url);
				}
			}
			
			/*foreach ($this->form_fields as $key => $ff)
			{
				if ($ff->crypt)
				{
					if (MOD_SEC_CRYPT && $ff->crypt_modsec)
					{
						$value = $ff->value->getValue(null, FF_SYSTEM_LOCALE);
						$value = mod_sec_decrypt_string($value);

						$this->form_fields[$key]->value->setValue($value, null, FF_SYSTEM_LOCALE);
						$this->form_fields[$key]->value_ori->setValue($value, null, FF_SYSTEM_LOCALE);
					}
				}
			}*/
		}
		else if ((!$this->record_exist || !$this->src_table) && $this->first_access)
		{ // set proper default values
			if ($this->auto_populate_insert)
			{
				if (is_array($this->populate_insert_array) && count($this->populate_insert_array))
				{
					foreach ($this->key_fields as $key => $value)
					{
						$this->key_fields[$key]->value = $this->key_fields[$key]->getDefault(array(&$this));
					}
					reset($this->key_fields);

					foreach ($this->form_fields as $subkey => $subvalue)
					{
						if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
						{
							foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
							{
								if (isset($this->populate_insert_array[$key][$subkey]))
									$this->form_fields[$key]->multi_values[$subkey] = $this->populate_insert_array[$key][$subkey];
								elseif (isset($subvalue["default"]))
									$this->form_fields[$key]->multi_values = $subvalue["default"];
								else
									$this->form_fields[$key]->multi_values = new ffData("", $subvalue["type"]);
							}
							reset ($this->form_fields[$key]->multi_fields);
							$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
						}
						else
						{
							if (isset($this->populate_insert_array[$key]))
								$this->form_fields[$key]->value = $this->populate_insert_array[$key];
							else
								$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
						}
					}
					reset($this->form_fields);

				}
				elseif (strlen($this->populate_insert_SQL))
				{
					$this->db[0]->query($this->populate_insert_SQL);
					if ($this->db[0]->nextRecord())
					{
						foreach ($this->key_fields as $key => $value)
						{
							$this->key_fields[$key]->value = $this->key_fields[$key]->getDefault(array(&$this));
						}
						reset($this->key_fields);

						foreach ($this->form_fields as $key => $value)
						{
							if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
							{
								foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
								{
									$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
									if ($this->db[0]->isSetField($element))
										$this->form_fields[$key]->multi_values[$subkey] = ($this->form_fields[$key]->crypt ? ff_getDecryptedField($this->db[0], $element, $subvalue["type"]) : $this->db[0]->getField($element, $subvalue["type"]));
									elseif (isset($subvalue["default"]))
										$this->form_fields[$key]->multi_values = $subvalue["default"];
									else
										$this->form_fields[$key]->multi_values = new ffData("", $subvalue["type"]);
								}
								reset($this->form_fields[$key]->multi_fields);
								$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
							}
							elseif ($this->db[0]->isSetField($this->form_fields[$key]->get_data_source()))
							{
								$tmp = ($this->form_fields[$key]->crypt ? 
												ff_getDecryptedField($this->db[0], $this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type)
												: $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type));
								$this->form_fields[$key]->setValue($tmp->getValue($this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale()));

								$res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->form_fields[$key]));
								$rc = end($res);
								if ($rc !== null)
									$this->form_fields[$key]->value = $rc;
							}
							else
								$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
						}
						reset($this->form_fields);
					}
				}
				
				/*foreach ($this->form_fields as $key => $ff)
				{
					if ($ff->crypt)
					{
						if (MOD_SEC_CRYPT && $ff->crypt_modsec)
						{
							$value = $ff->value->getValue(null, FF_SYSTEM_LOCALE);
							$value = mod_sec_decrypt_string($value);

							$this->form_fields[$key]->value->setValue($value, null, FF_SYSTEM_LOCALE);
							$this->form_fields[$key]->value_ori->setValue($value, null, FF_SYSTEM_LOCALE);
						}
					}
				}*/
			}
			else
			{
				foreach ($this->form_fields as $key => $FormField)
				{
					if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
					{
						foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
						{
							if (isset($subvalue["default"]))
								$this->form_fields[$key]->multi_values[$subkey] = $subvalue["default"];
							else
								$this->form_fields[$key]->multi_values[$subkey] = new ffData(null, $subvalue["type"]);
							$this->form_fields[$key]->multi_values_ori[$subkey] = new ffData(null, $subvalue["type"]);
						}
						reset($this->form_fields[$key]->multi_fields);
						$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
						$this->form_fields[$key]->value_ori = $this->form_fields[$key]->multi_values_ori;
					}
					else
						$this->form_fields[$key]->value = $this->form_fields[$key]->getDefault(array(&$this));
				}
				reset($this->form_fields);
			}
		}
		else
		{ // retrieve fields from Form
			foreach ($this->form_fields as $key => $FormField)
			{
				if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				{
					$element_data_ori = $this->parent[0]->retrieve_param($this->id, $key . "_ori");
					$element_data = $this->parent[0]->retrieve_param($this->id, $key);
					foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
					{
						$element = $key . "[" . $subkey . "]";
						if (isset($subvalue["locale"]))
							$locale = $subvalue["locale"];
						else
							$locale = FF_SYSTEM_LOCALE;
						$this->form_fields[$key]->multi_values_ori[$subkey] = new ffData($element_data_ori[$subkey], $subvalue["type"], $locale);
						$this->form_fields[$key]->multi_values[$subkey] = new ffData($element_data[$subkey], $subvalue["type"], $locale);
					}
					reset ($this->form_fields[$key]->multi_fields);
					$this->form_fields[$key]->value_ori = $this->form_fields[$key]->multi_values_ori;
					$this->form_fields[$key]->value = $this->form_fields[$key]->multi_values;
					continue;
				}

				$this->form_fields[$key]->value = new ffData($this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id), $this->form_fields[$key]->get_app_type(), FF_LOCALE);
				//$this->form_fields[$key]->setValue($this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id), null, FF_LOCALE);
				switch ($this->form_fields[$key]->data_type)
				{
					case "callback":
						$this->form_fields[$key]->value = call_user_func($this->form_fields[$key]->get_data_source(), $this->form_fields, $key, $this->first_access, $this);
						break;
				}

				if (!isset($this->key_fields[$key])) // ori values of key fields are retrieved before
					$this->form_fields[$key]->value_ori->setValue(
																	  $this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id . "_ori")
																	, $this->form_fields[$key]->base_type
																	, FF_SYSTEM_LOCALE);
				switch($this->form_fields[$key]->extended_type)
				{
					case "Boolean":
						if ($this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) !== $this->form_fields[$key]->checked_value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) && $this->form_fields[$key]->unchecked_value !== null)
							$this->form_fields[$key]->value = $this->form_fields[$key]->unchecked_value;
						break;
						
					case "File":
						$file_array 								= $this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id . "_file");
						$this->form_fields[$key]->file_tmpname 		= $this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id . "_tmpname");
						if ($this->parent[0]->retrieve_param($this->id, $this->form_fields[$key]->id . "_delete") == "delete")
						{
							if (strlen($this->form_fields[$key]->file_tmpname))
								@unlink($this->form_fields[$key]->getFileFullPath($this->form_fields[$key]->file_tmpname));
							
							$this->form_fields[$key]->setValue("");
							$this->form_fields[$key]->file_tmpname = "";
						}
						elseif (
								$this->parent[0]->isset_param($this->id, $this->form_fields[$key]->id . "_file")
								&& is_array($file_array) && isset($file_array["tmp_name"]) && strlen($file_array["tmp_name"])
							)
						{
							/*if (
									is_array($this->form_fields[$key]->file_allowed_mime) && count($this->form_fields[$key]->file_allowed_mime)
									&& array_search(mime_content_type($file_array["name"]), $this->form_fields[$key]->file_allowed_mime) === false
								)
								{
									$this->strError .= "<br />Tipo di file errato per il campo " . $this->form_fields[$key]->label;
								}
							else*/
							if (!$this->form_fields[$key]->file_max_size ||
									($this->form_fields[$key]->file_max_size && $file_array["size"] <= $this->form_fields[$key]->file_max_size)
								)
							{
								if($this->form_fields[$key]->file_normalize) { 
									$file_ext = pathinfo($file_array['name'], PATHINFO_EXTENSION); 
									$file_basename = $file_array['name'];
									if($file_ext)
									    $file_array['name'] = ffCommon_url_rewrite(substr($file_basename, 0, strrpos($file_basename, "." . $file_ext))) . "." . $file_ext;
									else
									    $file_array['name'] = ffCommon_url_rewrite($file_basename) . "." . $file_ext;
								}

								if (strlen($this->form_fields[$key]->file_tmpname))
									@unlink($this->form_fields[$key]->getFileFullPath($this->form_fields[$key]->file_tmpname));

								if ($this->form_fields[$key]->file_avoid_temporary)
									$tmp_name = $file_array['name'];
								else
									$tmp_name = "tmp_" . date("YmdHms") . "_" . uniqid(rand(), true) . "_" . $file_array['name'];

								if ($this->form_fields[$key]->file_make_temp_dir)
									@mkdir($this->form_fields[$key]->getFilePath(), $this->form_fields[$key]->file_chmod, true);

								$tmp_filename = $this->form_fields[$key]->getFileFullPath($tmp_name);
								move_uploaded_file($file_array['tmp_name'], $tmp_filename);
								@chmod($tmp_filename, $this->form_fields[$key]->file_chmod);

								if($this->form_fields[$key]->file_full_path) {
                                    if (
                                        substr(strtolower($this->form_fields[$key]->value->getValue()), 0, 7) != "http://"
                                        && substr(strtolower($this->form_fields[$key]->value->getValue()), 0, 8) != "https://"
                                        && substr($this->form_fields[$key]->value->getValue(), 0, 2) != "//"
                                    ) {
										$tmp_filename = str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileFullPath($tmp_name));
									    if (file_exists($tmp_filename))
										    $this->form_fields[$key]->value->setValue($tmp_filename);
                                    }
								} else 
									$this->form_fields[$key]->value->setValue($file_array['name']);

								$this->form_fields[$key]->file_tmpname = $tmp_name;
							}
							else
							{
								$this->strError .= "<br />&Egrave; stato superato il limite di upload per il campo " . $this->form_fields[$key]->label;
							}
                        }
						break;

					default:
				}
			}
			reset($this->form_fields);
		}

		$res = $this->doEvent("on_loaded_data", array(&$this));
		
		return;
	}

	/**
	* aggiunge un pulsante d'azione all'oggetto record
	* @param ffButton_base
	* @param index di default a null, specifica l'ordinamento
	*/
	function addActionButton($button, $index = null)
	{
		if (!is_subclass_of($button, "ffButton_base"))
			ffErrorHandler::raise("Wrong call to addActionButton: object must be a ffButton"
			    		        , E_USER_ERROR, $this, get_defined_vars());

		$button->parent = array(&$this);
		if (!is_numeric($index))
		$index = null;

		$this->action_buttons[$button->id] = array(
			                          "index"   => $index
			                        , "obj"     => &$button
			                    );
	}
    
    /**
    * recupera un pulsante d'azione basandosi sull'id
    * @param string
    */
    function getActionButton($id)
    {
        if (!strlen($id))
            ffErrorHandler::raise("getActionButton require a valid id (str) as argument", E_USER_ERROR, $this, get_defined_vars());
            
        if (isset($this->action_buttons[$id]))
            return $this->action_buttons[$id]["obj"];
        else
            return null;
    }
	
	function addDefaultButton($type, $obj)
	{
		$this->addActionButton(	  $obj
								, $this->buttons_options[$type]["index"]);
	}
    
    /**
    * inizializza i pulsanti standard dell'oggetto
    * vedi hide_all_controls, buttons_options
    */
    function initControls()
    {
		$this->tplSection["buttons"]["display"] = false;

		if ($this->hide_all_controls)
			return;
			
		// PREPARE DEFAULT BUTTONS
		if ($this->buttons_options["cancel"]["display"])
		{
			if ($this->buttons_options["cancel"]["obj"] !== null)
			{
				$this->addActionButton(	  $this->buttons_options["cancel"]["obj"]
										, $this->buttons_options["cancel"]["index"]);
			}
			else
			{
				$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
				$tmp->id 			= "ActionButtonCancel";
				$tmp->label 		= $this->buttons_options["cancel"]["label"]; 
				$tmp->aspect 		= $this->buttons_options["cancel"]["aspect"];
				
				if ($this->buttons_options["cancel"]["jsaction"])
				{
					$tmp->action_type 	= "submit";
					$tmp->frmAction		= ($this->buttons_options["cancel"]["frmAction"] ? $this->buttons_options["cancel"]["frmAction"] : "cancel");
					$tmp->jsaction = $this->buttons_options["cancel"]["jsaction"];
				}
				else
				{
					$tmp->action_type 	= "gotourl";
					$tmp->url			= "[RET_URL]";
				}
				
				if (isset($this->buttons_options["cancel"]["class"]))
					$tmp->class			= $this->buttons_options["cancel"]["class"];
				
				$this->addDefaultButton("cancel", $tmp);
			}
		}

		if (!$this->record_exist)
		{
			if ($this->buttons_options["insert"]["display"] && $this->allow_insert)
			{
				if ($this->buttons_options["insert"]["obj"] !== null)
				{
					$this->addActionButton(	  $this->buttons_options["insert"]["obj"]
											, $this->buttons_options["insert"]["index"]);
				}
				else
				{
					$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
					$tmp->id 			= "ActionButtonInsert";
					$tmp->label 		= $this->buttons_options["insert"]["label"];
					$tmp->aspect 		= $this->buttons_options["insert"]["aspect"];
					$tmp->frmAction		= ($this->buttons_options["insert"]["frmAction"] ? $this->buttons_options["insert"]["frmAction"] : "insert");
					if ($this->buttons_options["insert"]["jsaction"])
					{
						$tmp->jsaction = $this->buttons_options["insert"]["jsaction"];
					}
					elseif($this->buttons_options["insert"]["url"])
					{
						$tmp->url = $this->buttons_options["insert"]["url"];
						$tmp->action_type 	= "gotourl";
					} 
					else 
					{
						$tmp->action_type 	= "submit";
						$tmp->ajax 			= $this->ajax;
					}
					
					if (isset($this->buttons_options["insert"]["class"]))
						$tmp->class			= $this->buttons_options["insert"]["class"];
					
					$this->addDefaultButton("insert", $tmp);
				}
			}
		}
		else
		{
			if ($this->buttons_options["delete"]["display"] && $this->allow_delete)
			{
				if ($this->buttons_options["delete"]["obj"] !== null)
				{
					$this->addActionButton(	  $this->buttons_options["delete"]["obj"]
											, $this->buttons_options["delete"]["index"]);
				}
				else
				{
					$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
					$tmp->id 			= "ActionButtonDelete";
					$tmp->label 		=  $this->buttons_options["delete"]["label"];
					$tmp->aspect 		=  $this->buttons_options["delete"]["aspect"]; 
					$tmp->frmAction		= ($this->buttons_options["delete"]["frmAction"] ? $this->buttons_options["delete"]["frmAction"] : "delete");
					if ($this->buttons_options["delete"]["jsaction"])
					{
						$tmp->jsaction = $this->buttons_options["delete"]["jsaction"];
					}
					elseif($this->buttons_options["delete"]["url"])
					{
						$tmp->url = $this->buttons_options["delete"]["url"];
						$tmp->action_type 	= "gotourl";
					}
					else
					{
						$tmp->action_type 	= "submit";
						$tmp->ajax 			= $this->ajax;
					}
					
					if (isset($this->buttons_options["delete"]["class"]))
						$tmp->class			= $this->buttons_options["delete"]["class"];
					
					$this->addDefaultButton("delete", $tmp);
				}
			}

			if ($this->buttons_options["update"]["display"] && $this->allow_update)
			{
				if ($this->buttons_options["update"]["obj"] !== null)
				{
					$this->addActionButton(	  $this->buttons_options["update"]["obj"]
											, $this->buttons_options["update"]["index"]);
				}
				else
				{
					$tmp = ffButton::factory(null, $this->disk_path, $this->site_path, $this->page_path, $this->getTheme());
					$tmp->id 			= "ActionButtonUpdate";
					$tmp->label 		= $this->buttons_options["update"]["label"];
					$tmp->aspect 		= $this->buttons_options["update"]["aspect"];
					$tmp->frmAction		= ($this->buttons_options["update"]["frmAction"] ? $this->buttons_options["update"]["frmAction"] : "update");
					if ($this->buttons_options["update"]["jsaction"])
					{
						$tmp->jsaction = $this->buttons_options["update"]["jsaction"];
					}
					elseif($this->buttons_options["update"]["url"])
					{
						$tmp->url = $this->buttons_options["update"]["url"];
						$tmp->action_type 	= "gotourl";
					} 
					else
					{
						$tmp->action_type 	= "submit";
						$tmp->ajax 			= $this->ajax;
					}
					
					if (isset($this->buttons_options["update"]["class"]))
						$tmp->class = $this->buttons_options["update"]["class"];
					else
					{
						if ($tmp->class !== null)
							$tmp->class .= ($this->cursor_dialog ? " noactivebuttons" : "");
						else
							$tmp->class = ($this->cursor_dialog ? " noactivebuttons" : null);
					}
					
					$this->addDefaultButton("update", $tmp);
				}
			}
		}

        if (is_array($this->action_buttons) && count($this->action_buttons))
			$this->tplSection["buttons"]["display"] = true;
	}
    
    /**
    * controlla che i dati inseriti nei campi siano validi
    * vedi contain_error e strError
    */
	function check_fields()
	{
		//if ($this->frmAction == "insert" || $this->frmAction == "update")
		if (strlen($this->frmAction) && $this->frmAction != "delete" && $this->frmAction != "confirmdelete" && strpos($this->frmAction, "detail_") !== 0)
		{
			$res = $this->doEvent("on_check_before", array(&$this, $this->frmAction));
			$rc = end($res);
			if ($rc !== null && $rc !== false && strlen($rc))
			{
				$this->contain_error = true;
				$this->strError = $rc;
				return;
			}

			$need_key_check = false;
			$tmp_where = "";

			foreach ($this->form_fields as $key => $FormField)
			{
				$res = $this->doEvent("on_check_fields", array(&$this, &$FormField, $this->frmAction));
				$rc = end($res);
				if ($rc !== null && $rc !== false && strlen($rc))
				{
					$this->form_fields[$key]->contain_error = true;
					$this->contain_error = true;
					$this->strError = $rc;
				}

				if ($rc === null)
				{
					if (isset($this->key_fields[$key]) && $this->form_fields[$key]->getValue() != $this->key_fields[$key]->getValue())
					{
						$need_key_check = true;
						if (strlen($tmp_where))
							$tmp_where .= " AND ";
						$tmp_where .= " " . $key . " = " . $this->db[0]->toSql($this->form_fields[$key]->value, $this->form_fields[$key]->base_type);
					}
					
					// required or not
					if ($this->form_fields[$key]->required)
					{
						switch ($this->form_fields[$key]->extended_type)
						{
							case "Boolean":
								if ($this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) === $this->form_fields[$key]->unchecked_value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE))
									$this->contain_error = true;
								break;
							
							default:
								if (
									!strlen($this->form_fields[$key]->value->ori_value) &&
									!($this->form_fields[$key]->extended_type == "Password" && $this->record_exist)
								)
									$this->contain_error = true;
						}
						
						if ($this->contain_error)
						{
							$this->form_fields[$key]->contain_error = true;
							$this->strError = str_replace("[LABEL]", ($this->form_fields[$key]->label ? $this->form_fields[$key]->label : $this->form_fields[$key]->placeholder), $this->label_error_required);
							break;
						}
					}

					// range
					switch ($this->form_fields[$key]->base_type)
					{
						case "Number":
							if (
								$this->form_fields[$key]->min_val !== null && $this->form_fields[$key]->value->ori_value < $this->form_fields[$key]->min_val
							)
							{
								$this->contain_error = true;
								$this->strError = $this->label_error_range_min;
							}
							else if (
								$this->form_fields[$key]->max_val !== null && $this->form_fields[$key]->value->ori_value > $this->form_fields[$key]->max_val
							)
							{
								$this->contain_error = true;
								$this->strError = $this->label_error_range_max;
							}
							
							if ($this->contain_error)
							{
								$this->form_fields[$key]->contain_error = true;
								$this->strError = str_replace("[LABEL]", ($this->form_fields[$key]->label ? $this->form_fields[$key]->label : $this->form_fields[$key]->placeholder), $this->strError);
								break;
							}
							break;

						default:
					}

					// corrispondency
					if (
							strlen($this->form_fields[$key]->compare) 
							&& $this->form_fields[$this->form_fields[$key]->compare]->value->ori_value != $this->form_fields[$this->form_fields[$key]->compare]->value_ori->ori_value
							&& $this->form_fields[$key]->getValue() !== $this->form_fields[$this->form_fields[$key]->compare]->getValue()
						)
					{
						$this->form_fields[$key]->contain_error = true;
						$this->contain_error = true;
						$this->strError = str_replace("[LABEL]", ($this->form_fields[$this->form_fields[$key]->compare]->label ? $this->form_fields[$this->form_fields[$key]->compare]->label : $this->form_fields[$this->form_fields[$key]->compare]->placeholder), $this->label_error_nomatch);
						break;
					}
					
					// format
					if ($this->form_fields[$key]->enable_check_format && ((!is_array($this->form_fields[$key]->value->ori_value) && strlen($this->form_fields[$key]->value->ori_value)) || $this->form_fields[$key]->required == true) && ($tmp = $this->form_fields[$key]->check_format()))
					{
						$this->form_fields[$key]->contain_error = true;
						$this->contain_error = true;
						$this->strError = $tmp;
						break;
					}
				}
				
				if ($this->contain_error)
					break;

			}
			reset($this->form_fields);

			if ($need_key_check && !$this->contain_error)
			{
				// keys unicity
				$db = ffDB_Sql::factory();
				$db->query("SELECT * FROM `" . $this->src_table . "` WHERE " . $tmp_where);
				if ($db->nextRecord())
				{
					$this->contain_error = true;
					$this->strError = "I campi chiave del record sono duplicati";
				}
			}

			$res = $this->doEvent("on_check_after", array(&$this, $this->frmAction));
			$rc = end($res);
			if ($rc !== null && $rc !== false && strlen($rc))
			{
				$this->contain_error = true;
				$this->strError = $rc;
				return;
			}
		}
	}

	/**
	* esegue il processing dell'azione. Questa funzione viene eseguita in ogni caso, anche se un azione non è presente.
	* vedi skip_events_on_error e frmAction
	*/
	function process_action()
	{
		if (!strlen($this->strError) || !$this->skip_events_on_error)
		{
			$res = $this->doEvent("on_do_action", array(&$this, $this->frmAction));
			if ($rc = end($res))
				return;
		}

		// verifica degli errori sui dettagli. Se uno solo ne contiene, stoppa il processing
		if ((isset($this->default_actions[$this->frmAction]) ? $this->default_actions[$this->frmAction] : $this->frmAction) != "cancel")
		{
			if (strlen($this->strError))
				return;

			if ($this->detail !== null)
			{
				foreach ($this->detail as $key => $value)
				{
					if (strlen($this->detail[$key]->strError))
						return;
				}
				reset($this->detail);
			}
		}

		switch (isset($this->default_actions[$this->frmAction]) ? $this->default_actions[$this->frmAction] : $this->frmAction)
		{
			case "cancel":
				$this->redirect($this->parent[0]->ret_url);

			case "detail_addrows":
				$detailaction = $this->parent[0]->retrieve_param($this->id, "detailaction");
				if (strlen($detailaction) && isset($this->detail[$detailaction]))
					$this->detail[$detailaction]->process_action();
				elseif (strlen($detailaction) && isset($this->detail[0]) && count($this->detail) == 1)
					$this->detail[0]->process_action();
				else
					ffErrorHandler::raise("Action on unknown detail", E_USER_ERROR, $this, get_defined_vars());
				break;

			case "detail_delete":
				$detailaction = $this->parent[0]->retrieve_param($this->id, "detailaction");
				if (strlen($detailaction) && isset($this->detail[$detailaction]))
					$rc = $this->detail[$detailaction]->process_action();
				elseif (strlen($detailaction) && isset($this->detail[0]) && count($this->detail) == 1)
					$rc = $this->detail[0]->process_action();
				else
					ffErrorHandler::raise("Action on unknown detail", E_USER_ERROR, $this, get_defined_vars());
				break;

			case "insert":
				if (!$this->allow_insert)
					$this->redirect($this->parent[0]->ret_url);

                //INSERT DB
                if($this->src_table) {
                    $fields = ""; $values = "";
                    foreach ($this->form_fields as $key => $FormField)
                    {
                        if ($this->form_fields[$key]->store_in_db == true && !strlen($this->form_fields[$key]->compare))
                        {
                            if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
                            {
                                foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
                                {
                                    $element = $this->form_fields[$key]->get_data_source(false) . "_" . $subkey;

                                    if (strlen($fields))
                                        $fields .= ", ";
                                    $fields .= "`" . $element . "`";

                                    if (strlen($values))
                                        $values .= ", ";

                                    $tmpval = $this->form_fields[$key]->multi_values[$subkey];
                                    $values .= $this->db[0]->toSql($tmpval, $this->form_fields[$key]->base_type);
                                }
                                reset ($this->form_fields[$key]->multi_fields);
                            }
                            else
                            {
                                $processed_sql_value = false;

                                if (strlen($fields))
                                    $fields .= ", ";
                                $fields .= "`" . $this->form_fields[$key]->get_data_source(false) . "`";

                                if (strlen($values))
                                    $values .= ", ";

                                $tmp_type = $this->form_fields[$key]->base_type;

                                if ($this->form_fields[$key]->crypt_method !== null)
                                {
                                    switch ($this->form_fields[$key]->crypt_method)
                                    {
                                        case "MD5":
                                            $tmpval = new ffData(md5($this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
                                            $tmp_type = "Text";
                                            break;
                                        case "mysql_password":
                                            $tmpval = "PASSWORD(" . $this->db[0]->toSql($this->form_fields[$key]->value, $this->form_fields[$key]->base_type) . ")";
                                            $processed_sql_value = true;
                                            break;
                                        case "mysql_oldpassword":
                                            $tmpval = new ffData($this->db[0]->mysqlOldPassword($this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
                                            $tmp_type = "Text";
                                            break;
                                        default:
                                            ffErrorHandler::raise("Crypt method not supported!", E_USER_ERROR, $this, get_defined_vars());
                                    }
                                }
                                else if ($this->form_fields[$key]->crypt)
                                {
                                    if (MOD_SEC_CRYPT && $this->form_fields[$key]->crypt_modsec)
                                    {
                                        $tmpval = $this->form_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE);
                                        $tmpval = mod_sec_crypt_string($tmpval);
                                        $tmpval = "UNHEX(" . $this->db[0]->toSql(bin2hex($tmpval)) . ")";
                                        $processed_sql_value = true;
                                    }
                                }
                                else
                                    $tmpval = $this->form_fields[$key]->value;

                                 $res = $this->form_fields[$key]->doEvent("on_store_in_db", array(&$this, &$this->form_fields[$key]));
                                 $rc = end($res);
                                 if ($rc !== null)
                                 {
                                     $tmpval = $rc;
                                     $processed_sql_value = false;
                                     $tmp_type = $this->form_fields[$key]->base_type;
                                 }

                                $values .= ($processed_sql_value ? $tmpval : $this->db[0]->toSql($tmpval, $tmp_type));
                            }
                        }
                    }
                    reset($this->form_fields);

                    if (is_array($this->additional_fields) && count($this->additional_fields))
                    {
                        foreach ($this->additional_fields as $key => $value)
                        {
                            if (strlen($fields))
                                $fields .= ", ";
                            $fields .= "`" . $key . "`";

                            if (strlen($values))
                                $values .= ", ";
                            $values .= $this->db[0]->toSql($value);
                        }
                        reset($this->additional_fields);
                    }

                    if (is_array($this->insert_additional_fields) && count($this->insert_additional_fields))
                    {
                        foreach ($this->insert_additional_fields as $key => $value)
                        {
                            if (strlen($fields))
                                $fields .= ", ";
                            $fields .= "`" . $key . "`";

                            if (strlen($values))
                                $values .= ", ";
                            $values .= $this->db[0]->toSql($value);
                        }
                        reset($this->insert_additional_fields);
                    }

                    if (!$this->skip_insert_if_empty || ($this->skip_insert_if_empty && strlen($fields)))
                    {
                        $sSQL = "INSERT INTO `" . $this->src_table . "` ( " . $fields . " ) VALUES ( " . $values . " ) ";
                        if (!$this->skip_action)
                        {
                            $this->db[0]->execute($sSQL);

                            foreach ($this->key_fields as $key => $value)
                            {
                                if ($this->key_fields[$key]->auto_key)
                                {
                                    $this->key_fields[$key]->value = $this->db[0]->getInsertID();
                                    $tmp_Where = "`" . $this->key_fields[$key]->get_data_source(false) . "` = " . $this->db[0]->toSql($this->key_fields[$key]->value, $this->key_fields[$key]->base_type);
                                    break;
                                }
                            }
                            reset($this->key_fields);
                        }
                    }
                }
				// MANAGE FILES
				ffCommon_manage_files($this, $tmp_Where);

				$rc = false;

				$res = $this->doEvent("on_done_record_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc |= true;

				if ($this->detail !== null)
				{
					foreach ($this->detail as $key => $value)
					{
						$rc |= $this->detail[$key]->process_action();
					}
					reset($this->detail);

					if($rc)
						return;
				}

				$res = $this->doEvent("on_done_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc |= true;

				if (!$rc)
					$this->redirect($this->parent[0]->ret_url);
				else
					break;

			case "update":
				if (!$this->allow_update)
					$this->redirect($this->parent[0]->ret_url);

				// MANAGE FILES
				ffCommon_manage_files($this);

				if($this->src_table) {
                    $fields = "";
                    foreach ($this->form_fields as $key => $FormField)
                    {
                        $processed_sql_value = false;

                        if ($this->form_fields[$key]->store_in_db == true && !strlen($this->form_fields[$key]->compare) &&
                                !($this->form_fields[$key]->extended_type == "Password" && !strlen($this->form_fields[$key]->getValue()))
                            )
                        {
                            if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
                            {
                                foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
                                {
                                    if ($this->form_fields[$key]->multi_values[$subkey]->ori_value != $this->form_fields[$key]->multi_values_ori[$subkey]->ori_value)
                                    {
                                        $element = $this->form_fields[$key]->get_data_source(false) . "_" . $subkey;

                                        if (strlen($fields))
                                            $fields .= ", ";

                                        $tmpval = $this->form_fields[$key]->multi_values[$subkey];

                                        $fields .= "`" . $this->src_table . "`.`" . $element . "`"
                                                    . " = "
                                                    . $this->db[0]->toSql($tmpval);
                                    }
                                }
                                reset ($this->form_fields[$key]->multi_fields);
                            }
                            elseif ($this->form_fields[$key]->value_ori->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) != $this->form_fields[$key]->value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE))
                            {
                                if (strlen($fields))
                                    $fields .= ", ";

                                $tmp_type = $this->form_fields[$key]->base_type;

                                if ($this->form_fields[$key]->crypt_method !== null)
                                {
                                    switch ($this->form_fields[$key]->crypt_method)
                                    {
                                        case "MD5":
                                            $tmpval = new ffData(md5($this->form_fields[$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
                                            $tmp_type = "Text";
                                            break;
                                        case "mysql_password":
                                            $tmpval = "PASSWORD(" . $this->db[0]->toSql($this->form_fields[$key]->value, $this->form_fields[$key]->base_type) . ")";
                                            $processed_sql_value = true;
                                            break;
                                        case "mysql_oldpassword":
                                            $tmpval = new ffData($this->db[0]->mysqlOldPassword($this->form_fields[$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
                                            $tmp_type = "Text";
                                            break;
                                        default:
                                            die("Crypt method not supported!");
                                    }
                                }
                                else if ($this->form_fields[$key]->crypt)
                                {
                                    if (MOD_SEC_CRYPT && $this->form_fields[$key]->crypt_modsec)
                                    {
                                        $tmpval = $this->form_fields[$key]->value->getValue(null, FF_SYSTEM_LOCALE);
                                        $tmpval = mod_sec_crypt_string($tmpval);
                                        $tmpval = "UNHEX(" . $this->db[0]->toSql(bin2hex($tmpval)) . ")";
                                        $processed_sql_value = true;
                                        $tmp_type = "Text";
                                    }
                                }
                                else
                                    $tmpval = $this->form_fields[$key]->value;

                                 $res = $this->form_fields[$key]->doEvent("on_store_in_db", array(&$this, &$this->form_fields[$key]));
                                 $rc = end($res);
                                 if ($rc !== null)
                                 {
                                     $tmpval = $rc;
                                     $processed_sql_value = false;
                                     $tmp_type = $this->form_fields[$key]->base_type;
                                 }

                                $fields .= "`" . $this->src_table . "`.`" . $this->form_fields[$key]->get_data_source(false) . "`"
                                            . " = "
                                            . ($processed_sql_value ?
                                                    $tmpval
                                                    : $this->db[0]->toSql($tmpval, $tmp_type)
                                                );
                            }
                        }
                    }
                    reset($this->form_fields);

                    //UPDATE DB
                    if (is_array($this->additional_fields) && count($this->additional_fields))
                    {
                        foreach ($this->additional_fields as $key => $value)
                        {
                            if (strlen($fields))
                                $fields .= ", ";
                            $fields .= "`" . $key . "` = " . $this->db[0]->toSql($value);
                        }
                        reset($this->additional_fields);
                    }

                    if (is_array($this->update_additional_fields) && count($this->update_additional_fields))
                    {
                        foreach ($this->update_additional_fields as $key => $value)
                        {
                            if (strlen($fields))
                                $fields .= ", ";
                            $fields .= "`" . $key . "` = " . $this->db[0]->toSql($value);
                        }
                        reset($this->update_additional_fields);
                    }

                    if (strlen($fields))
                    {
                        $sSQL = "UPDATE `" . $this->src_table . "` SET " . $fields . " WHERE " . $this->sWhere . $this->sAddWhere;
                        if (!$this->skip_action)
                            $this->db[0]->execute($sSQL);
                    }
                }
				$rc = false;
				
				$res = $this->doEvent("on_done_record_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc |= true;

				if ($this->detail !== null)
				{
					foreach ($this->detail as $key => $value)
					{
						$rc |= $this->detail[$key]->process_action();
					}
					reset($this->detail);

					if($rc)
						return;
				}

				$res = $this->doEvent("on_done_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc = true;

				if (!$rc)
					$this->redirect($this->parent[0]->ret_url);
				else
					break;

			case "delete":
				if ($this->url_delete !== null)
				{
					$tmp_url = ffProcessTags($this->url_delete, $this->key_fields, $this->form_fields, "normal", $this->parent[0]->get_params(), rawurlencode($_SERVER['REQUEST_URI']), $this->parent[0]->get_globals(GET_GLOBALS_EXCLUDE_LIST), $this->db[0]);
					$this->redirect($tmp_url);
				}
				/*if (!$this->skip_action)
				{*/
					$confirmurl = $_SERVER["REQUEST_URI"];
					if (strpos($confirmurl, "?") === false)
						$confirmurl .= "?";
					else
						$confirmurl .= "&";
					$confirmurl .= $this->getPrefix() . "frmAction=confirmdelete";
					if ($_REQUEST["cancelurl"])
						$cancelurl = $_REQUEST["cancelurl"];
					else
						$cancelurl = $_SERVER["REQUEST_URI"];
					$this->dialog(false, "yesno", $this->parent[0]->title, $this->label_delete_record, $cancelurl, $confirmurl);
					exit;
				/*}
				else
					break;*/
				/*header("Location: " . $this->dialog_path  . "/dialog/?"
					. "confirmurl=" . urlencode($confirmurl) . "&"
					. "cancelurl=" . urlencode($cancelurl) . "&"
					. "type=yesno&"
					. "message=" . urlencode("Confermi l'eliminazione del dato?") . "&"
					);
				exit;*/

			case "confirmdelete":
				if (!$this->allow_delete)
					$this->redirect($this->parent[0]->ret_url);

				$rc = false;
				if ($this->detail !== null)
				{
					foreach ($this->detail as $key => $value)
					{
						$rc |= $this->detail[$key]->process_action();
					}
					reset($this->detail);
				}

				if ($rc)
					return;

                if($this->src_table) {
                    $tmp_action = $this->del_action;
                    if (strlen($this->del_auto_action))
                    {
                        $bResult = false;
                        $tmp_db = ffDB_Sql::factory();
                        // first of all, do all the sql for subsequent checks
                        foreach ($this->del_auto_SQL as $key => $value)
                        {
                            $tmp_db->query($this->process_SQL($value));
                            if ($tmp_db->nextRecord())
                                $bResult = true;
                        }
                        reset($this->del_auto_SQL);

                        switch ($this->del_auto_action)
                        {
                            case "single":
                                if ($bResult)
                                    $tmp_action = "update";
                                else
                                    $tmp_action = "delete";
                                break;

                            case "multi":
                                if ($bResult)
                                    $tmp_action = "multi_update";
                                else
                                    $tmp_action = "multi_delete";
                                break;

                            case "error":
                                if ($bResult)
                                {
                                    $this->frmAction = "";
                                    $this->strError = "Esistono record correlati, impossibile procedere all'eliminazione.";
                                    return;
                                }
                        }
                    }

                    switch ($tmp_action)
                    {
                        case "delete":
                            if (!$this->skip_action)
                                $this->delete_files();
                            $sSQL = "DELETE FROM `" . $this->src_table . "` WHERE " . $this->sWhere  . $this->sAddWhere;
                            if (!$this->skip_action)
                                $this->db[0]->execute($sSQL);
                            break;

                        case "multi_delete":
                            if (!$this->skip_action)
                                $this->delete_files();
                            $sSQL = "DELETE FROM `" . $this->src_table . "` WHERE " . $this->sWhere . $this->sAddWhere;
                            if (!$this->skip_action)
                                $this->db[0]->execute($sSQL);

                            foreach ($this->del_multi_delete as $key => $value)
                            {
                                if (!$this->skip_action)
                                    $this->db[0]->execute($this->process_SQL($value));
                            }
                            reset($this->del_multi_delete);
                            break;

                        case "update":
                            $sSQL = "UPDATE `" . $this->src_table . "` SET " . $this->process_SQL($this->del_update) . " WHERE " . $this->sWhere . $this->sAddWhere;
                            if (!$this->skip_action)
                                $this->db[0]->execute($sSQL);
                            break;

                        case "multi_update":
                            $sSQL = "UPDATE `" . $this->src_table . "` SET " . $this->process_SQL($this->del_update) . " WHERE " . $this->sWhere . $this->sAddWhere;
                            if (!$this->skip_action)
                                $this->db[0]->execute($sSQL);

                            foreach ($this->del_multi_update as $key => $value)
                            {
                                if (!$this->skip_action)
                                    $this->db[0]->execute($this->process_SQL($value));
                            }
                            reset($this->del_multi_update);
                            break;
                    }
                }

				$res = $this->doEvent("on_done_record_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc |= true;

				$res = $this->doEvent("on_done_action", array($this, $this->frmAction));
				if (array_search(true, $res))
					$rc = true;

				if (!$rc)
					$this->redirect($this->parent[0]->ret_url);
				else
					break;

			default:
				if (strlen($this->frmAction))
				{
					$rc = false;
					if ($this->detail !== null)
					{
						foreach ($this->detail as $key => $value)
						{
							$rc |= $this->detail[$key]->process_action();
						}
						reset($this->detail);
					}

					if (!$rc)
					{
						$res = $this->doEvent("on_done_record_action", array($this, $this->frmAction));
						if (array_search(true, $res))
							$rc |= true;

						$res = $this->doEvent("on_done_action", array($this, $this->frmAction));
						if (array_search(true, $res))
							$rc = true;
					}
					else
						break;
				}
		}
	}

	/**
	* esegue il processing di un istruzione SQL sostituendo i tag relativi con i campi appropriati
	* i tag possibili sono:
	* [DATE]
	* [FIELDID_VALUE]
	*/
	function process_SQL($sSQL)
	{
		$sSQL = str_replace(	"[DATE]",
								$this->db[0]->toSql(new ffData(date("d/m/Y"), "Date", "ITA")),
								$sSQL
							);
		$sSQL = str_replace(	"[DATETIME]",
								$this->db[0]->toSql(new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA")),
								$sSQL
							);
		$sSQL = str_replace(	"[USERNID]",
								$this->db[0]->toSql(new ffData(get_session("UserNID"), "Number", "ISO9075")),
								$sSQL
							);
		foreach ($this->key_fields as $key => $FormField)
		{
			$sSQL = str_replace(	"[" . $key . "_VALUE]",
									$this->db[0]->toSql($this->key_fields[$key]->value, $this->key_fields[$key]->base_type),
									$sSQL
								);
		}
		reset($this->key_fields);
		foreach ($this->form_fields as $key => $FormField)
		{
			$sSQL = str_replace(	"[" . $key . "_VALUE]",
									$this->db[0]->toSql($this->form_fields[$key]->value, $this->form_fields[$key]->base_type),
									$sSQL
								);
		}
		reset($this->form_fields);
		return $sSQL;
	}
	
	/**
	* elimina tutti i file associati ai campi di tipo "File"
	*/
	function delete_files()
	{
		foreach ($this->form_fields as $key => $FormField)
		{
			if ($this->form_fields[$key]->extended_type == "File" && strlen($this->form_fields[$key]->value_ori->getValue()))
			{
				//$storing_path = ffProcessTags($this->form_fields[$key]->file_storing_path, $this->key_fields, $this->form_fields);
				@unlink($this->form_fields[$key]->getFileFullPath($this->form_fields[$key]->value_ori->getValue(), false));
			}
		}
		reset($this->form_fields);
	}
	
	/**
	 * effettua un redirect ad un indirizzo specifico
	 * @param string l'url
	 * @return String
	 */
	function redirect($url, $response = null)
	{
		return ffRedirect($url, null, null, ($response === null ? $this->json_result : $response));
	}

	/**
	* visualizza un dialog
	* @param bool se dev'essere restituito l'url invece di visualizzarlo
	* @param string il tipo (yesno, okonly)
	* @param string il titolo
	* @param string il messaggio
	* @param string l'url associato al pulsante no
	* @param string l'url associato al pulsante yes
	*/
	function dialog($returnurl, $type, $title, $message, $cancelurl, $confirmurl)
	{
		if ($this->dialog_path === null)
			$dialog_path = $this->parent[0]->getThemePath() . "/ff/dialog";
		else
			$dialog_path = $this->parent[0]->site_path . $this->dialog_path;

		$res = $this->doEvent("onDialog", array($this, true, $type, $title, $message, $cancelurl, $confirmurl, $dialog_path));
		$ret = end($res);
		if ($ret === null)
			$ret = ffDialog(true, $type, $title, $message, $cancelurl, $confirmurl, $dialog_path);

		if ($returnurl)
			return $ret;
		else
			$this->redirect($ret);
	}
}

function ffRecord_reset_cache($oRecord, $frmAction)
{
	if (strlen($frmAction) && count($oRecord->cache_clear_resources))
	{
		$cache = ffCache::getInstance();
        foreach ($oRecord->cache_clear_resources as $cache_clear_resource) {
            $cache->clear($cache_clear_resource);
		}
/*
		call_user_func_array(
				array($cache, "clear")
				, $oRecord->cache_clear_resources
			);
*/
	}
}
