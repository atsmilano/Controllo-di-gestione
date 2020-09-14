<?php
/**
 * Record Details Editor
 * 
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * Record Details Editor
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffDetails
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
	 * This method istantiate a ff_something instance based on many params
	 *
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $theme
	 * @param array $variant
	 * @return ffDetails_base
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
 * ffRecord è la classe che gestisce l'editing e la
 * visualizzazione di un singolo record.
 *
 * @package FormsFramework
 * @subpackage components
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffDetails_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)

	/**
	 * ID dell'oggetto; deve essere univoco per ogni FormPage
	 * @var number
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

	/**
	 * Classe del Componente;
	 * di default è "ffDetails"
	 * @var String
	 */
	var $class 					= "ffDetails";

	var $theme 					= null;
	/**
	 * Cartella del template; di default è la cartella "theme"
	 * @var String
	 */
	var $template_dir			= null;

	/**
	 * File del template; di default è il file "ffDetails.html"
	 * @var String
	 */
	var $template_file 			= "ffDetails.html";

	/**
	 * Inibisce la visualizzazione del componente in modo automatico con ffPage
	 * di fatto abilitando questo flag s'impedisce il normale processing dell'interfaccia del componente
	 * @var Boolean
	 */
	var $display				= true;

	/**
	 * Determina se ffPage deve utilizzare l'id del componente per posizionarlo nel layout
	 * @var Boolean
	 */
	var $use_own_location 		= false;

	/**
	 * il nome della locazione da utilizzare al posto dell'id
	 * vedi use_own_location
	 */
	var $location_name			= null;

	/**
	 * se abilitato, forza l'utilizzo di variabili fisse invece di quelle dinamiche nel template
	 * dovrebbe essere obsoleto
	 */
	var $use_fixed_fields		= false;
	
	/**
	 * il percorso utilizzato per effettuare i redirect
	 * dovrebbe essere obsoleto come tutte le variabili successive
	 */
	var $redirect_path			= null;
	var $redirect_fullscreen	= false;
	var $redirect_theme			= null;
	var $redirect_layer			= null;
	var $redirect_topbar		= null;
	var $redirect_navbar		= null;

	/**
	 * il percorso che forms userà per visualizzare il dialog
	 * di default il dialog viene visualizzato in questo percorso più "/dialog"
	 */
	var $dialog_path			= null;
	
	/**
	 * le caratteristiche di visualizzazione del dialog
	 * dovrebbero essere tutte obsolete, come per il redirect
	 */
	var $dialog_fullscreen		= false;
	var $dialog_theme			= null;
	var $dialog_layer			= null;
	var $dialog_topbar			= null;
	var $dialog_navbar			= null;

	/**
	 * Titolo del componente
	 * @var String
	 */
	var $title					= "";


	/**
	 * Tabella sorgente per i record del componente
	 * @var String
	 */
	var $src_table 				= "";

	/**
	 * Campo che viene utilizzato per l'ordinamento;
	 * la variabile va OBBLIGATORIAMENTE impostata.
	 * @var String
	 */
	var $order_default			= "";

	/**
	 * Variabile per determinare l'ordinamento dei record
	 * I possibili valori sono ASC e DESC
	 * @var String
	 */
	var $order_default_dir		= "ASC";

	/**
	 * Array che stabilisce la relazione tra ffDetails e ffRecord
	 * @var array()
	 */
	var $fields_relationship	= array();

	/**
	 * Salta le azioni sul database
	 * @var Boolean
	 */
	var $skip_action			= false;
	
	/**
	 * Visualizza il pulsante "Aggiungi"
	 * @var Boolean
	 */
	var $display_new			= true;
	var $display_rowstoadd		= true;
	var $rowstoadd_field_default = null;
    var $display_new_location   = "Header"; //Header Footer or Both

	/**
	 * Visualizza il pulsante "Elimina"
	 * @var Boolean
	 */
	var $display_delete			= true;
	
	/**
	 * le opzioni dei pulsanti di default
	 * i pulsanti del dettaglio sono orizzontali, per ogni record
	 * @var Mixed
	 */
	var $buttons_options		= array(
									"delete" => array(
										  "display" => true
										, "index" 	=> 0
										, "obj" 	=> null
										, "image" 	=> ""
										, "class" 	=> "ico-delete"
									)
								);
	
	var $properties = array();

	/**
	 * se dev'essere utilizzato l'auto populate su insert
	 * @var Boolean
	 */
	var $auto_populate_insert			= false;
	
	/**
	 * l'istruzione SQL da utilizzare per il populate
	 * ha la precedenza su populate_insert_array
	 * @var String
	 */
	var $populate_insert_SQL 			= null;
	var $populate_insert_DS 			= null;
	
	/**
	 * in assenza di populate_insert_SQL, un array di valori da utilizzare per popolare il dettaglio su inserimento
	 * @var Mixed
	 */
	var $populate_insert_array			= null;

	/**
	 * se dev'essere utilizzato l'auto populate su editing
	 * @var Boolean
	 */
	var $auto_populate_edit				= false;

	/**
	 * l'istruzione SQL da utilizzare per il populate su editing
	 * ha la precedenza su populate_edit_array
	 * @var String
	 */
	var $populate_edit_SQL 				= null;
	var $populate_edit_DS 				= null;
	
	/**
	 * in assenza di populate_edit_SQL, un array di valori da utilizzare per popolare il dettaglio su modifica
	 * @var Mixed
	 */
	var $populate_edit_array			= null;

	/**
	 * su inserimento, il numero di righe iniziali con cui deve presentarsi il dettaglio
	 */
	var $starting_rows		 			= 0;

	/**
	 * Numero minimo di righe, in caso non vengano raggiunte da errore
	 * @var Int
	 */
	var $min_rows		 				= 0;

	/**
	 * Numero massimo di righe, impedisce l'inserimento di righe aggiuntive
	 * @var Int
	 */
	var $max_rows		 				= 0;
	
	/**
	 * se deve forzare un minimo di righe per consentire le operazioni
	 * ossia se devono essere aggiunte in caso manchino
	 * @var Boolean
	 */
	var $force_min_rows					= false;

	/**
	 * se le label devono essere visualizzate con metodo orizzontale, ossia una sola volta
	 * (verticale significa ad ogni record)
	 * @var Boolean
	 */
	var $horizontal_labels				= false;

	/**
	 * Campo chiave addizionali
	 * @var array()
	 */
	var $additional_key_fields			= array();

	/**
	 * le dipendenze da soddisfare in quanto a widgets per poter eseguire il componente (vengono precaricate in modo automatico da ffPage)
	 * @var array
	 */
	var $widget_deps 					= array();

	var $processed_widgets = array();
	
	/**
	 * Insert array di campi aggiuntivi con un valore prefissato
	 * @var array
	 */
	var $insert_additional_fields		= array();


	/**
	 * Update di array di campi aggiuntivi con un valore prefissato
	 * @var array
	 */
	var $update_additional_fields		= array();

	var $ever_reload_data = false;

	/**
	 * Controlla se ffDetails deve eseguire un delete specifico
	 * che dipende dal risultato di un array di query SQL salvate in un array.
	 * I valori che può assumere sono: null, "single", "multi", "error"
	 * Per il comportamento standard lasciare vuoto.
	 * Se $del_auto_action = single ffDetails selezionerà "update" se una select
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
	 * Se si sceglie "update", viene settato un campo del record anziché rimuovere fisicamente l'intero record.
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
	
	/**
	 * l'insieme di istruzioni SQL da eseguire nel caso del_action sia "multi_delete"
	 * @var array
	 */
	var $del_multi_delete 		= array();
	
	/**
	 * l'istruzione SQL da eseguire nel caso del_action sia "update"
	 * @var String
	 */
	var $del_update				= "";
	
	/**
	 * le istruzioni SQL da eseguire nel caso del_action sia "multi_update"
	 */
	var $del_multi_update 		= array();

	/**
	 * se la pressione del tasto cancellazione deve eliminare immediatamente il record
	 * E' molto pericoloso, da usare con cautela!
	 * @var Boolean
	 */
	var $delete_istant			= false;

	/**
	 * un eventuale contenuto statico da preporre al componente
	 * @var String
	 */
	var $fixed_pre_content		= "";
	
	/**
	 * un eventuale contenuto statico da accodare al componente
	 * @var String
	 */
	var $fixed_post_content		= "";

    var $fixed_title_content 			= "";
    var $fixed_heading_content 			= "";
	// -----------------------------------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode with a nice flare effect! :-)
	/**
	 * l'oggetto ffPage collegato
	 * @var Array
	 */
	var $parent 				= null;

	/**
	 * l'oggetto ffRecord collegato
	 * @var Array
	 */
	var $main_record			= null;

	/**
	 * il recordset in modifica, sotto forma di ffFields
	 * @var Array
	 */
	var $recordset				= array();

	/**
	 * il recordset originale, sotto forma di ffFields
	 * @var Array
	 */
	var $recordset_ori			= array();
	
	/**
	 * il recordset dei files
	 * probabilmente obsoleto
	 * @var Array
	 */
	var $recordset_files 		= array();

	/**
	 * il recordset contenente le chiavi dei record eliminati
	 * @var Array
	 */
	var $deleted_keys			= array();

	/**
	 * il recordset contenente i valori dei record eliminati
	 * @var Array
	 */
	var $deleted_values			= array();

	/**
	 * Contiene gli ffFields da gestire
	 * @var array()
	 */
	var $form_fields			= array();

	/**
	 * ffField da gestire come campi nascosti
	 * @var array()
	 */
	var $hidden_fields			= array();

	/**
	 * ffField da gestire come campi chiave
	 * @var array()
	 */
	var $key_fields				= array();
	
	/**
	 * contiene tutti i pulsanti aggiunti al dettaglio, la visualizzazione è per ogni record
	 * @var Array
	 */
	var $detail_buttons			= array();

	/**
	 * Array di chiavi utilizzate per determinare
	 * il record recuperato tramite i parametri
	 * @var array()
	 */
	var $keys					= array();				

	/**
	 * il numero di colonne del dettaglio
	 * utile a fini di templating
	 * @var int
	 */
	var $cols					= 0;
	
	/**
	 * il numero di record del dettaglio
	 * @var int
	 */
	var $rows					= 0;

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
	
	/**
	 * Se l'oggetto contiene errori, usato dagli altri componenti solitamente
	 * @var Boolean
	 */
	var $contain_error 			= false;

	/**
	 * WHERE SQL per recupeare il record
	 * @var String
	 */
	var $sWhere					= "";	

	/**
	 * WHERE addizionale per recuperare il record
	 * @var String
	 */
	var $sAddWhere				= "";

	/**
	 * Oggetto interno di tipo ffDB_Sql()
	 * @var ffDB_Sql() 
	 */
	var $db						= null;	
	
	/**
	 * Oggetto interno di tipo ffTemplate()
	 * @var ffTemplate()
	 */
	var $tpl					= null;					// Internal ffTemplate() object

	/**
	 * parametri dell'oggetto in formato GET
	 * @var String
	 */
	public $params				= "";

	/**
	 * il risultato dell'elaborazione dell'oggetto sotto forma di json_result
	 * viene utilizzato quando la richiesta è un XHR
	 * @var Array
	 */
	var $json_result = array();
	
	/**
	 * elenco delle risorse implicate ai fini delle richieste XHR, per l'aggiornamento dell'interfaccia
	 * @var Array
	 */
	var $resources = array();
	
	/**
	 * elenco delle risorse in scrittura (cioè quelle che il componente modifica) ai fini della cache
	 * probabilmente obsoleto
	 * @var Array
	 */
	var $resources_set = array();
	
	/**
	 * elenco delle risorse in lettura ai fini della cache
	 * probabilmente obsoleto
	 * @var Array
	 */
	var $resources_get = array();

	/**
	 * elenco delle risorse in lettura ai fini della cache
	 * @var Array
	 */

	var $cache_get_resources = array();
	/**
	 * elenco delle risorse in scrittura (cioè quelle che il componente modifica) ai fini della cache
	 * @var Array
	 */
	var $cache_clear_resources = array();

	var $libraries	= array();
	var $js_deps	= array();
	var $css_deps	= array();
	
	abstract protected function tplLoad();
	abstract public function tplParse($output_result);
	abstract public function display_rows();

	// ---------------------------------------------------------------
	//  PUBLIC FUNCS

	//  CONSTRUCTOR
	function __construct(ffPage_base $page, $disk_path, $theme)
	{
		$this->get_defaults("ffDetails");
		$this->get_defaults();

		$this->site_path = $page->site_path;
		$this->page_path = $page->page_path;
		$this->disk_path = $disk_path;
		$this->theme = $theme;

		if ($this->db === null)
			$this->db[0] = ffDB_Sql::factory();
	}

	/**
	 * Aggiunge un campo di tipo ffField a ffDetail
	 * @param ffField Il campo da aggiungere
	 * @return L'id del campo
	 */
	function addContent($field)
	{
		if (!is_subclass_of($field, "ffField_base"))
			ffErrorHandler::raise("Wrong call to addContent: object must be a ffField"
							, E_USER_ERROR, $this, get_defined_vars());

		$field->parent = array(&$this);
		$field->cont_array =& $this->form_fields;
		$this->form_fields[$field->id] = $field;
		return $field->id;
	}

	/**
	 * Aggiunge un campo hidden di tipo ffField a ffDetail
	 * @param ffField Il campo da aggiungere
	 * @return L'id del campo
	 */
	function addHiddenField($field)
	{
		if (!is_subclass_of($field, "ffField_base"))
			ffErrorHandler::raise("Wrong call to addHiddenField: object must be a ffField"
							, E_USER_ERROR, $this, get_defined_vars());

		$field->parent = array(&$this);
		$field->cont_array =& $this->hidden_fields;
		$this->hidden_fields[$field->id] = $field;
		return $field->id;
	}

	/**
	 * Aggiunge un campo chiave di tipo ffField a ffDetail
	 * @param ffField Il campo da aggiungere
	 * @return L'id del campo
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

	/**
	 * Aggiunge un pulsante al dettaglio
	 * @param ffButton $button
	 * @param int $index l'ordinamento in cui si vuole posizionare il pulsante
	 */
	function addContentButton($button, $index = null)
	{
		if (!is_subclass_of($button, "ffButton_base"))
			ffErrorHandler::raise("Wrong call to addContentButton: object must be a ffButton"
							, E_USER_ERROR, $this, get_defined_vars());

		if (!is_numeric($index))
			$index = null;

		$button->parent = array(&$this);
		$this->detail_buttons[$button->id] = array(
													  "obj" => &$button
													, "index" => $index
												);
	}

	/**
	 * recupera un puntatore al pulsante usandone l'id
	 * @param string $id
	 * @return ffButton
	 */
	function getDetailButton($id)
	{
		if (!strlen($id))
			ffErrorHandler::raise("getDetailButton require a valid id (str) as argument", E_USER_ERROR, $this, get_defined_vars());

		if (isset($this->detail_buttons[$id]))
			return $this->detail_buttons[$id]["obj"];
		else
			return null;
	}
	
	/**
	 * preparazione al processing
	 * normalmente invocata da ffPage
	 */
	function pre_process()
	{
		// Load Template and initialize it
		$this->tplLoad();

		// First of all, process all page's params
		$this->process_params();
	}

	/**
	 * elaborazione dell'interfaccia
	 * normalmente invocata da ffPage
	 * vedi ffPage->components_buffer
	 * @param Boolean @output_result se deve visualizzare (true) o restituire (false, default)
	 * @return String
	 */
	function process_interface($output_result = false)
	{
		 $res = ffDetails::doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		 $res = $this->doEvent("on_before_process_interface", array(&$this));
		 $rc = end($res);
		 if($rc !== null)
		 	return;

		$this->tplDisplay();
		if ($output_result === null)
			return null;

		return $this->tplParse($output_result);
	}
	
	/**
	 * funzione di processing principale, fantasma
	 * ffDetails non ha una vera funzione di processing, il processing è a carico di ffRecord
	 */
	function process()
	{
	}

	/**
	 * elabora le sezioni principali del template
	 */
	public function tplDisplay()
	{
		$this->display_rows();

		// display error
		$this->displayError();
	}

	/**
	 * Funzione che restituisce il template specificato
	 * @return il file del template
	 */
	function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Funzione che restituisce la directory del template
	 * @return il file del template
	 */
	function getTemplateDir()
	{
		$res = $this->doEvent("getTemplateDir", array($this));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffDetails";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}

	/**
	 * elabora i parametri generici utilizzati da ffDetails
	 */
	function process_params()
	{
		$this->rows = $this->parent[0]->retrieve_param($this->id, "rows");

		return;
	}

	/**
	 * recupera i dati dalla sorgente appropriata
	 */
	function retrieve_fields()
	{
		if ($this->main_record[0]->record_exist)
		{
			foreach ($this->fields_relationship as $key => $value)
			{
				if (strlen($this->sWhere))
					$this->sWhere .= " AND ";

				$tmp_father = null;
				if (isset($this->main_record[0]->key_fields[$value]))
					$tmp_father = $this->main_record[0]->key_fields[$value];
				else
					ffErrorHandler::raise("Relationship broken", E_USER_ERROR, $this, get_defined_vars());

				$this->sWhere .= " " . $this->src_table . "." . $key . " = " .
												$this->db[0]->toSql(
														$tmp_father->value,
														$tmp_father->base_type
													) . " ";
			}
			reset($this->fields_relationship);

			if (is_array($this->additional_key_fields) && count($this->additional_key_fields))
			{
				$this->sAddWhere = "";
				foreach ($this->additional_key_fields as $key => $value)
				{
						$this->sAddWhere .= " AND " . $key . " = " . $this->db[0]->toSql($value);
				}
				reset ($this->additional_key_fields);
			}
		}

		//TODO: da rimuovere
		foreach ($this->form_fields as $key => $value)
		{
			if ($this->form_fields[$key]->multi_preserve_field === null)
				$this->form_fields[$key]->pre_process();
		}
		reset($this->form_fields);

		// EVENT HANDLER
		$res = $this->doEvent("on_load_data", array($this));
		$rc = end($res);
		if ($rc !== null)
			return;

		// Retrieve the fields values from the proper place.
		if (($this->main_record[0]->record_exist && $this->main_record[0]->first_access) || $this->ever_reload_data)
		{ // retrieve fields from DB
			if ($this->auto_populate_edit)
			{
				if (is_array($this->populate_edit_array) && count($this->populate_edit_array))
				{
					$i = 0;
					foreach ($this->populate_edit_array as $key => $value)
					{
						foreach ($this->key_fields as $subkey => $subvalue)
						{
							if (isset($value[$subkey]))
								$this->recordset[$i][$subkey] = $value[$subkey];
							else
								$this->recordset[$i][$subkey] = $this->key_fields[$subkey]->getDefault(array(&$this));
						}
						reset($this->key_fields);

						foreach ($this->hidden_fields as $subkey => $subvalue)
						{
							if (isset($value[$subkey]))
								$this->recordset[$i][$subkey] = $value[$subkey];
							else
								$this->recordset[$i][$subkey] = $this->hidden_fields[$subkey]->getDefault(array(&$this));
						}
						reset($this->hidden_fields);

						foreach ($this->form_fields as $subkey => $subvalue)
						{
							if (is_array($this->form_fields[$subkey]->multi_fields) && count($this->form_fields[$subkey]->multi_fields))
							{
								foreach ($this->form_fields[$subkey]->multi_fields as $mul_subkey => $mul_subvalue)
								{
									if (isset($this->populate_edit_array[$subkey][$mul_subkey]))
										$this->recordset[$i][$subkey][$mul_subkey] = $this->populate_edit_array[$subkey][$mul_subkey];
									elseif (isset($mul_subvalue["default"]))
										$this->recordset[$i][$subkey][$mul_subkey] = $mul_subvalue["default"];
									else
										$this->recordset[$i][$subkey][$mul_subkey] = new ffData("", $mul_subvalue["type"]);
								}
								reset ($this->form_fields[$subkey]->multi_fields);
							}
							else
							{
								if (isset($value[$subkey]))
									$this->recordset[$i][$subkey] = $value[$subkey];
								else
									$this->recordset[$i][$subkey] = $this->form_fields[$subkey]->getDefault(array(&$this));
							}
						}
						reset($this->form_fields);

						$res = $this->doEvent("on_loaded_row", array(&$this, "insert", $i));

						$i++;
					}
					reset($this->populate_edit_array);
					$this->recordset_ori = $this->recordset;
					$this->rows = $i;
//										ffErrorHandler::raise("asd", E_USER_ERROR, $this, get_defined_vars());
				}
				else
				{
					$tmp_SQL = $this->getSqlPopulateEdit();

					// pre-process for father values
					foreach ($this->main_record[0]->key_fields as $key => $value)
					{
                        $key_value = ($this->main_record[0]->src_table
                            ? $this->main_record[0]->key_fields[$key]->value
                            : $this->main_record[0]->key_fields[$key]->default_value
                        );

						$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($key_value, $this->main_record[0]->key_fields[$key]->base_type), $tmp_SQL);
						$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($key_value, $this->main_record[0]->key_fields[$key]->base_type, false), $tmp_SQL);
					}
					reset($this->main_record[0]->key_fields);

					foreach ($this->main_record[0]->form_fields as $key => $value)
					{
						if (is_array($this->main_record[0]->form_fields[$key]->multi_fields) && count($this->main_record[0]->form_fields[$key]->multi_fields))
						{
							if (is_array($this->main_record[0]->form_fields[$key]->multi_values) && count($this->main_record[0]->form_fields[$key]->multi_values))
							{
								foreach ($this->main_record[0]->form_fields[$key]->multi_fields as $subkey => $subvalue)
								{
									$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($this->main_record[0]->form_fields[$key]->multi_values[$subkey], $subvalue["type"]), $tmp_SQL);
									$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($this->main_record[0]->form_fields[$key]->multi_values[$subkey], $subvalue["type"], false), $tmp_SQL);
								}
								reset($this->main_record[0]->form_fields[$key]->multi_fields);
							}
						}
						else
						{
                            $form_value = ($this->main_record[0]->src_table
                                ? $this->main_record[0]->form_fields[$key]->value
                                : $this->main_record[0]->form_fields[$key]->default_value
                            );

							$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($form_value, $this->main_record[0]->form_fields[$key]->base_type), $tmp_SQL);
							$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($form_value, $this->main_record[0]->form_fields[$key]->base_type, false), $tmp_SQL);
						}
					}
					reset($this->main_record[0]->form_fields);

	/*					foreach ($this->fields_relationship as $key => $value)
					{
						$tmp_SQL = str_replace("[" . $value . "_FATHER]", $this->db[0]->toSql($this->main_record[0]->key_fields[$value]->value, $this->main_record[0]->key_fields[$value]->base_type), $tmp_SQL);
					}
					reset($this->fields_relationship);*/

					$this->db[0]->query($tmp_SQL);
					if ($this->db[0]->nextRecord())
					{
						$i = 0;
						do
						{
							foreach ($this->key_fields as $key => $value)
							{
								$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->key_fields[$key]->get_data_source(), $this->key_fields[$key]->base_type);
							}
							reset($this->key_fields);

							foreach ($this->form_fields as $key => $value)
							{
								switch ($this->form_fields[$key]->data_type)
								{
									case "db":
										if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
										{
											foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
											{
												$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
												if ($this->db[0]->isSetField($element))
													$this->recordset_ori[$i][$key][$subkey] = $this->db[0]->getField($element, $subvalue["type"]);
												elseif (isset($subvalue["default"]))
													$this->recordset_ori[$i][$key][$subkey] = clone $subvalue["default"];
												else
													$this->recordset_ori[$i][$key][$subkey] = new ffData("", $subvalue["type"]);
											}
											reset ($this->form_fields[$key]->multi_fields);
										}
										else
										{
											if ($this->db[0]->isSetField($this->form_fields[$key]->get_data_source()))
												$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type);
											else
												$this->recordset_ori[$i][$key] = $this->form_fields[$key]->getDefault(array(&$this));

                                            $res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->recordset_ori[$i][$key]));
                                            $rc = end($res);
                                            if ($rc !== null)
                                                $this->recordset_ori[$i][$key] = $rc;
										}
										break;

									case "callback":
										$this->recordset_ori[$i][$key] = call_user_func($this->form_fields[$key]->get_data_source(), $this->form_fields, $key);
										break;

									case "":
										$this->recordset_ori[$i][$key] = $this->form_fields[$key]->getDefault(array(&$this));
										break;
								}
							}
							reset($this->form_fields);

							foreach ($this->hidden_fields as $key => $value)
							{
								switch ($this->hidden_fields[$key]->data_type)
								{
									case "db":
										if ($this->db[0]->isSetField($this->hidden_fields[$key]->get_data_source()))
											$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->hidden_fields[$key]->get_data_source(), $this->hidden_fields[$key]->base_type);
										else {
                                            $this->recordset_ori[$i][$key] = $this->hidden_fields[$key]->getDefault(array(&$this));
                                        }
										break;

									case "callback":
										$this->recordset_ori[$i][$key] = call_user_func($this->hidden_fields[$key]->get_data_source(), $this->hidden_fields, $key);
										break;

									case "":
										$this->recordset_ori[$i][$key] = $this->hidden_fields[$key]->getDefault(array(&$this));
										break;
								}
							}
							reset($this->hidden_fields);
							$this->recordset[$i] = $this->recordset_ori[$i];

							$res = $this->doEvent("on_loaded_row", array(&$this, "populate_edit", $i));

							$i++;
						}
						while($this->db[0]->nextRecord());
						$this->rows = $i;
					}
				}
			}
			else
			{
				$sSQL = "SELECT
								*
							FROM
								" . $this->src_table . "
							WHERE
								" . $this->sWhere . "
								" . $this->sAddWhere . "
							ORDER BY
								" . $this->getOrderSQL();
				$this->db[0]->query($sSQL);

				if ($this->db[0]->nextRecord())
				{
					$i = 0;
					do
					{
						foreach ($this->key_fields as $key => $value)
						{
							$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->key_fields[$key]->get_data_source(), $this->key_fields[$key]->base_type);
							$this->recordset[$i][$key] = $this->recordset_ori[$i][$key];
						}
						reset($this->key_fields);

						foreach ($this->form_fields as $key => $value)
						{
							if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
							{
								foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
								{
									$this->recordset_ori[$i][$key][$subkey] = $this->db[0]->getField($key . "_" . $subkey, $subvalue["type"]);
									$this->recordset[$i][$key][$subkey] = clone $this->recordset_ori[$i][$key][$subkey];
								}
								reset($this->form_fields[$key]->multi_fields);
							}
							else
							{
								switch ($this->form_fields[$key]->data_type)
								{
									case "db":
										if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
										{
											foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
											{
												$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
												$this->recordset_ori[$i][$key][$subkey] = $this->db[0]->getField($element, $subvalue["type"]);
											}
											reset ($this->form_fields[$key]->multi_fields);
										}
										else
										{
											$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type);
											if ($this->form_fields[$key]->crypt)
											{
												if (MOD_SEC_CRYPT && $this->form_fields[$key]->crypt_modsec)
												{
													$value = $this->recordset_ori[$i][$key]->getValue(null, FF_SYSTEM_LOCALE);
													$value = mod_sec_decrypt_string($value);

													$this->recordset_ori[$i][$key]->setValue($value, null, FF_SYSTEM_LOCALE);
												}
											}

                                            $res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->recordset_ori[$i][$key]));
                                            $rc = end($res);
                                            if ($rc !== null)
                                                $this->recordset_ori[$i][$key] = $rc;
										}
										break;

									case "callback":
										$this->recordset_ori[$i][$key] = call_user_func($this->form_fields[$key]->get_data_source(), $this->form_fields, $key);
										break;

									case "":
										$this->recordset_ori[$i][$key] = $this->form_fields[$key]->getDefault(array(&$this));
										break;
								}
								$this->recordset[$i][$key] = $this->recordset_ori[$i][$key];
							}
						}
						reset($this->form_fields);

						foreach ($this->hidden_fields as $key => $value)
						{
							switch ($this->hidden_fields[$key]->data_type)
							{
								case "db":
									$this->recordset_ori[$i][$key] = $this->db[0]->getField($this->hidden_fields[$key]->get_data_source(), $this->hidden_fields[$key]->base_type);
									if ($this->hidden_fields[$key]->crypt)
									{
										if (MOD_SEC_CRYPT && $this->hidden_fields[$key]->crypt_modsec)
										{
											$value = $this->recordset_ori[$i][$key]->getValue(null, FF_SYSTEM_LOCALE);
											$value = mod_sec_decrypt_string($value);

											$this->recordset_ori[$i][$key]->setValue($value, null, FF_SYSTEM_LOCALE);
										}
									}

									$res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->recordset_ori[$i][$key]));
									$rc = end($res);
									if ($rc !== null)
										$this->recordset_ori[$i][$key] = $rc;
									break;

								case "callback":
									$this->recordset_ori[$i][$key] = call_user_func($this->hidden_fields[$key]->get_data_source(), $this->hidden_fields, $key);
									break;

								case "":
									$this->recordset_ori[$i][$key] = $this->hidden_fields[$key]->getDefault(array(&$this));
									break;
							}

							$this->recordset[$i][$key] = $this->recordset_ori[$i][$key];
						}
						reset($this->hidden_fields);

						$res = $this->doEvent("on_loaded_row", array(&$this, "edit", $i));

						$i++;
					}
					while($this->db[0]->nextRecord());
					$this->rows = $i;
				}
			}
		}
		else if (!$this->main_record[0]->record_exist && $this->main_record[0]->first_access)
		{
			if ($this->auto_populate_insert)
			{
				if (is_array($this->populate_insert_array) && count($this->populate_insert_array))
				{
					$i = 0;
					foreach ($this->populate_insert_array as $key => $value)
					{
						foreach ($this->key_fields as $subkey => $subvalue)
						{
							$this->recordset[$i][$subkey] = $this->key_fields[$subkey]->getDefault(array(&$this));
						}
						reset($this->key_fields);

						foreach ($this->hidden_fields as $subkey => $subvalue)
						{
							if (isset($value[$subkey]))
								$this->recordset[$i][$subkey] = $value[$subkey];
							else
								$this->recordset[$i][$subkey] = $this->hidden_fields[$subkey]->getDefault(array(&$this));
						}
						reset($this->hidden_fields);

						foreach ($this->form_fields as $subkey => $subvalue)
						{
							if (is_array($this->form_fields[$subkey]->multi_fields) && count($this->form_fields[$subkey]->multi_fields))
							{
								foreach ($this->form_fields[$subkey]->multi_fields as $mul_subkey => $mul_subvalue)
								{
									if (isset($this->populate_insert_array[$subkey][$mul_subkey]))
										$this->recordset[$i][$subkey][$mul_subkey] = $this->populate_insert_array[$subkey][$mul_subkey];
									elseif (isset($mul_subvalue["default"]))
										$this->recordset[$i][$subkey][$mul_subkey] = $mul_subvalue["default"];
									else
										$this->recordset[$i][$subkey][$mul_subkey] = new ffData("", $mul_subvalue["type"]);
								}
								reset ($this->form_fields[$subkey]->multi_fields);
							}
							else
							{
								if (isset($value[$subkey]))
									$this->recordset[$i][$subkey] = $value[$subkey];
								else
									$this->recordset[$i][$subkey] = $this->form_fields[$subkey]->getDefault(array(&$this));
							}
						}
						reset($this->form_fields);

						$res = $this->doEvent("on_loaded_row", array(&$this, "insert", $i));

						$i++;
					}
					reset($this->populate_insert_array);
					$this->recordset_ori = $this->recordset;
					$this->rows = $i;
//										ffErrorHandler::raise("asd", E_USER_ERROR, $this, get_defined_vars());
				}
				else
				{
					$tmp_SQL = $this->getSqlPopulateInsert();

					// mod by Alex. Check if really needed
					// pre-process for father values
					foreach ($this->main_record[0]->key_fields as $key => $value)
					{
                        $key_value = ($this->main_record[0]->src_table
                            ? $this->main_record[0]->key_fields[$key]->value
                            : $this->main_record[0]->key_fields[$key]->default_value
                        );

						$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($key_value, $this->main_record[0]->key_fields[$key]->base_type), $tmp_SQL);
						$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($key_value, $this->main_record[0]->key_fields[$key]->base_type, false), $tmp_SQL);
					}
					reset($this->main_record[0]->key_fields);

					foreach ($this->main_record[0]->form_fields as $key => $value)
					{
						if (is_array($this->main_record[0]->form_fields[$key]->multi_fields) && count($this->main_record[0]->form_fields[$key]->multi_fields))
						{
							if (is_array($this->main_record[0]->form_fields[$key]->multi_values) && count($this->main_record[0]->form_fields[$key]->multi_values))
							{
								foreach ($this->main_record[0]->form_fields[$key]->multi_fields as $subkey => $subvalue)
								{
									$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($this->main_record[0]->form_fields[$key]->multi_values[$subkey], $subvalue["type"]), $tmp_SQL);
									$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($this->main_record[0]->form_fields[$key]->multi_values[$subkey], $subvalue["type"], false), $tmp_SQL);
								}
								reset($this->main_record[0]->form_fields[$key]->multi_fields);
							}
						}
						else
						{
                            $form_value = ($this->main_record[0]->src_table
                                ? $this->main_record[0]->form_fields[$key]->value
                                : $this->main_record[0]->form_fields[$key]->default_value
                            );

							$tmp_SQL = str_replace("[" . $key . "_FATHER]", $this->db[0]->toSql($form_value, $this->main_record[0]->form_fields[$key]->base_type), $tmp_SQL);
							$tmp_SQL = str_replace("[UNQUOTED_" . $key . "_FATHER]", $this->db[0]->toSql($form_value, $this->main_record[0]->form_fields[$key]->base_type, false), $tmp_SQL);
						}
					}
					reset($this->main_record[0]->form_fields);
					// end mod by Alex
					
					$this->db[0]->query($tmp_SQL);
					if ($this->db[0]->nextRecord())
					{
						$i = 0;
						do
						{
							foreach ($this->key_fields as $key => $value)
							{
								$this->recordset[$i][$key] = $this->key_fields[$key]->getDefault(array(&$this));
							}
							reset($this->key_fields);

							foreach ($this->hidden_fields as $key => $value)
							{
								if ($this->db[0]->isSetField($this->hidden_fields[$key]->get_data_source()))
									$this->recordset[$i][$key] = $this->db[0]->getField($this->hidden_fields[$key]->get_data_source(), $this->hidden_fields[$key]->base_type);
								else
									$this->recordset[$i][$key] = $this->hidden_fields[$key]->getDefault(array(&$this));
							}
							reset($this->hidden_fields);

							foreach ($this->form_fields as $key => $value)
							{
								if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
								{
									foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
									{
										$element = $this->form_fields[$key]->get_data_source() . "_" . $subkey;
										if ($this->db[0]->isSetField($element))
											$this->recordset[$i][$key][$subkey] = $this->db[0]->getField($element, $subvalue["type"]);
										elseif (isset($subvalue["default"]))
											$this->recordset[$i][$key][$subkey] = $subvalue["default"];
										else
											$this->recordset[$i][$key][$subkey] = new ffData("", $subvalue["type"]);
									}
									reset($this->form_fields[$key]->multi_fields);
								}
								elseif ($this->db[0]->isSetField($this->form_fields[$key]->get_data_source()))
								{
									$this->recordset[$i][$key] = $this->db[0]->getField($this->form_fields[$key]->get_data_source(), $this->form_fields[$key]->base_type);
									
									$res = $this->form_fields[$key]->doEvent("on_get_from_db", array(&$this, $this->form_fields[$key]));
									$rc = end($res);
									if ($rc !== null)
										$this->recordset[$i][$key] = $rc;
								}
								else
									$this->recordset[$i][$key] = $this->form_fields[$key]->getDefault(array(&$this));
							}
							reset($this->form_fields);

							$res = $this->doEvent("on_loaded_row", array(&$this, "populate_insert", $i));

							$i++;
						}
						while($this->db[0]->nextRecord());
						$this->recordset_ori = $this->recordset;
						$this->rows = $i;
					}
				}
			}
			elseif ($this->starting_rows)
			{
				$this->addRows($this->starting_rows);
			}
		}
		else
		{ // retrieve fields from Form
			$this->recordset_ori = $this->parent[0]->retrieve_param($this->id, "recordset_ori");
			$this->recordset = $this->parent[0]->retrieve_param($this->id, "recordset");

			if (!is_array($this->recordset_ori))
				$this->recordset_ori = array();

			if (!is_array($this->recordset))
				$this->recordset = array();

			ksort($this->recordset_ori);
			ksort($this->recordset);

			foreach ($this->recordset as $i => $value)
			{
				foreach ($this->key_fields as $key => $value)
				{
					$this->recordset_ori[$i][$key] = new ffData($this->recordset_ori[$i][$key], $this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					$this->recordset[$i][$key] = new ffData($this->recordset[$i][$key], $this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
				}
				reset($this->key_fields);

				foreach ($this->hidden_fields as $key => $value)
				{
					$this->recordset_ori[$i][$key] = new ffData($this->recordset_ori[$i][$key], $this->hidden_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					$this->recordset[$i][$key] = new ffData($this->recordset[$i][$key], $this->hidden_fields[$key]->base_type, FF_SYSTEM_LOCALE);
				}
				reset($this->hidden_fields);

				foreach ($this->form_fields as $key => $value)
				{
					if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
					{
						foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
						{
							$this->recordset_ori[$i][$key][$subkey] = new ffData($this->recordset_ori[$i][$key][$subkey], $subvalue["type"], FF_SYSTEM_LOCALE);
							$this->recordset[$i][$key][$subkey] = new ffData($this->recordset[$i][$key][$subkey], $subvalue["type"], FF_SYSTEM_LOCALE);
						}
						reset($this->form_fields[$key]->multi_fields);
						continue;
					}

					$this->recordset_ori[$i][$key] = new ffData($this->recordset_ori[$i][$key], $this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);

					switch($this->form_fields[$key]->extended_type)
					{
						case "Boolean":
							$this->recordset[$i][$key] = new ffData($this->recordset[$i][$key], $this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale());

							if (!$this->form_fields[$key]->bool_preserve_value && $this->recordset[$i][$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) !== $this->form_fields[$key]->checked_value->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE) && $this->form_fields[$key]->unchecked_value !== null)
								$this->recordset[$i][$key] = $this->form_fields[$key]->unchecked_value;
							break;

						case "File":
							$this->recordset_files[$i][$key] = $this->recordset[$i][$key];
							$this->recordset[$i][$key] = new ffData($this->recordset_files[$i][$key]["name"]);

							if(is_array($this->recordset_files[$i][$key]) && strlen($this->recordset_files[$i][$key]["delete"]))
							{
								if (strlen($this->recordset_files[$i][$key]["tmpname"]))
									@unlink($this->form_fields[$key]->getFileFullPath($this->recordset_files[$i][$key]["tmpname"], true));

								$this->recordset[$i][$key]->setValue("");
								$this->recordset_files[$i][$key] = array();
							}
							elseif (is_array($this->recordset_files[$i][$key]) && strlen($this->recordset_files[$i][$key]["file"]["tmp_name"]))
							{
								if (!$this->form_fields[$key]->file_max_size ||
										($this->form_fields[$key]->file_max_size && $this->recordset_files[$i][$key]["file"]["size"] <= $this->form_fields[$key]->file_max_size)
									)
								{
									if($this->form_fields[$key]->file_normalize) { 
									    $file_ext = pathinfo($this->recordset_files[$i][$key]["file"]["name"], PATHINFO_EXTENSION); 
									    $file_basename = $this->recordset_files[$i][$key]["file"]["name"];
									    if($file_ext)
									        $this->recordset_files[$i][$key]["file"]["name"] = ffCommon_url_rewrite(substr($file_basename, 0, strrpos($file_basename, "." . $file_ext))) . "." . $file_ext;
									    else
									        $this->recordset_files[$i][$key]["file"]["name"] = ffCommon_url_rewrite($file_basename) . "." . $file_ext;
									}

									if (strlen($this->recordset_files[$i][$key]["tmpname"]))
										@unlink($this->form_fields[$key]->getFileFullPath($this->recordset_files[$i][$key]["tmpname"], true));

									$tmp_name = "tmp_" . date("YmdHms") . "_" . uniqid(rand(), true) . "_" . $this->recordset_files[$i][$key]["file"]["name"];

									if ($this->form_fields[$key]->file_make_temp_dir)
										@mkdir($this->form_fields[$key]->getFileFullPath($this->form_fields[$key]->file_chmod, true), true);

									move_uploaded_file($this->recordset_files[$i][$key]["file"]["tmp_name"], $this->form_fields[$key]->getFileFullPath($tmp_name, true));
									@chmod($this->form_fields[$key]->getFileFullPath($tmp_name, true), 0777);

									if($this->form_fields[$key]->file_full_path) {
                                        if (
                                            substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 7) != "http://"
                                            && substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 8) != "https://"
                                            && substr($this->recordset[$i][$key]->getValue(), 0, 2) != "//"
                                        ) {
										    if(file_exists($this->form_fields[$key]->getFileFullPath($tmp_name, true)))	
											    $this->form_fields[$key]->value->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileFullPath($tmp_name, true)));

										    //if(!file_exists($this->form_fields[$key]->getFileBasePath() . $this->recordset_files[$i][$key]["file"]["name"]))
											    //$this->recordset[$i][$key]->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileTempPath(false)) . "/" . $this->recordset_files[$i][$key]["file"]["name"]);
                                        }
									} else 
										$this->recordset[$i][$key]->setValue($this->recordset_files[$i][$key]["file"]["name"]);
										
									$this->recordset_files[$i][$key]["tmpname"] = $tmp_name;
									unset($this->recordset_files[$i][$key]["file"]);
								}
								else
								{
									$this->strError .= "<br />&Egrave; stato superato il limite di upload per il campo " . $this->form_fields[$key]->label;
								}
							} elseif(is_array($this->recordset_files[$i][$key]) && strlen($this->recordset_files[$i][$key]["file"]["name"])) {
								if($this->form_fields[$key]->file_full_path) {
                                    if (
                                        substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 7) != "http://"
                                        && substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 8) != "https://"
                                        && substr($this->recordset[$i][$key]->getValue(), 0, 2) != "//"
                                    ) {
									    if(!file_exists($this->form_fields[$key]->getFileBasePath() . $this->recordset_files[$i][$key]["file"]["name"]))
										    $this->recordset[$i][$key]->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileFullPath($this->recordset_files[$i][$key]["file"]["name"], false)));
                                    }
								} else {
									$this->recordset[$i][$key]->setValue($this->recordset_files[$i][$key]["file"]["name"]);
								}
							} elseif(strlen($this->recordset[$i][$key]->getValue()) && is_array($this->recordset_files[$i][$key]) && strlen($this->recordset_files[$i][$key]["tmpname"])) {
								if($this->form_fields[$key]->file_full_path) {
                                    if (
                                        substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 7) != "http://"
                                        && substr(strtolower($this->recordset[$i][$key]->getValue()), 0, 8) != "https://"
                                        && substr($this->recordset[$i][$key]->getValue(), 0, 2) != "//"
                                    ) {
									    //if($this->form_fields[$key]->getFileBasePath() === null)
										//    $this->form_fields[$key]->getFileBasePath() = FF_DISK_UPDIR;

									    //if(!file_exists($this->form_fields[$key]->getFileBasePath() . $this->recordset[$i][$key]->getValue()))
										    //$this->recordset[$i][$key]->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileTempPath(false)) . "/" . basename($this->recordset[$i][$key]->getValue()));
                                            //$this->recordset[$i][$key]->setValue("");
                                    }
								}
							}
							break;

						default:
							$this->recordset[$i][$key] = new ffData($this->recordset[$i][$key], $this->form_fields[$key]->get_app_type(), $this->form_fields[$key]->get_locale());
					}

				}
				reset($this->form_fields);

				$res = $this->doEvent("on_loaded_row", array(&$this, "form", $i));
			}
			reset($this->recordset);
			//ffErrorHandler::raise("u", E_USER_ERROR, $this, get_defined_vars());
			// after, retrieve keys of delete records
			$tmp_deleted_keys = $this->parent[0]->retrieve_param($this->id, "deleted_keys");
			$tmp_deleted_values = $this->parent[0]->retrieve_param($this->id, "deleted_values");

			// build array of ffData using deleted_keys
			if (is_array($tmp_deleted_keys) && count($tmp_deleted_keys))
			{
				for ($i = 0; $i < count($tmp_deleted_keys); $i++)
				{
					//$this->tpl[0]->set_var("row", $i);
					foreach ($tmp_deleted_keys[$i] as $key => $value)
					{
						$this->deleted_keys[$i][$key] = new ffData($value, $this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					}
					reset($tmp_deleted_keys[$i]);
				}

				for ($i = 0; $i < count($tmp_deleted_values); $i++)
				{
					foreach ($tmp_deleted_values[$i] as $key => $value)
					{
						$this->deleted_values[$i][$key] = new ffData($value, $this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					}
					reset($tmp_deleted_values[$i]);
				}
			}
		}

		if ($this->force_min_rows && count($this->recordset) < $this->min_rows)
		{
			$this->addRows($this->min_rows - count($this->recordset));
		}

		// EVENT HANDLER
		$rc = $this->doEvent("on_loaded_data", array($this));

		return;
	}

	/**
	 * verifica la validità dei campi
	 * non restituisce un valore perchè valorizza variabili interne
	 * vedi strError, contain_error, etc
	 */
	function check_fields()
	{
		if ($this->frmAction == "insert" || $this->frmAction == "update")
		{
			$need_key_check = false;
			$tmp_where = "";

			if ($this->min_rows > 0 && count($this->recordset) < $this->min_rows)
			{
				$this->contain_error = true;
				if ($this->min_rows == 1)
					$this->strError = "E' richiesta almeno " . $this->min_rows . " riga";
				else
					$this->strError = "Sono richieste almeno " . $this->min_rows . " righe";
			}
			elseif ($this->max_rows > 0 && count($this->recordset) > $this->max_rows)
			{
				$this->contain_error = true;
				if ($this->max_rows == 1)
					$this->strError = "Non inserire pi&ugrave; di " . $this->max_rows . " riga";
				else
					$this->strError = "Non inserire pi&ugrave; di " . $this->max_rows . " righe";
			}
			elseif (count($this->recordset))
			{
				for ($i = 0; $i < count($this->recordset); $i++)
				{
					foreach ($this->form_fields as $key => $FormField)
					{
                        $res = $this->doEvent("on_check_fields", array(&$this, $this->recordset[$i], &$FormField, $this->frmAction));
						$rc = end($res);
						if ($rc !== null && $rc !== false && strlen($rc))
						{
							$this->form_fields[$key]->contain_error = true;
							$this->contain_error = true;
							$this->strError = $rc;
						}
						
						if (isset($this->key_fields[$key]) && $this->recordset[$i][$key]->getValue() != $this->recordset_ori[$i][$key]->getValue() && $this->frmAction != "update")
						{
							$need_key_check = true;
							if (strlen($tmp_where))
								$tmp_where .= " AND ";
							$tmp_where .= " " . $key . " = " . $this->db[0]->toSql($this->recordset[$i][$key], $this->form_fields[$key]->base_type);
						}
						// required or not
						if ($this->form_fields[$key]->display && $this->form_fields[$key]->required && !strlen($this->recordset[$i][$key]->ori_value))
						{
							$this->form_fields[$key]->contain_error = true;
							$this->contain_error = true;
							
							if(($this instanceof ffDetails_tabs) && (is_array($this->tab_label) || strlen($this->tab_label))) {
								if(is_array($this->tab_label) && count($this->tab_label)) {
									$add_label = "";
									foreach($this->tab_label AS $tab_label_value) {
										if(isset($this->recordset[$i][$tab_label_value]))
											$add_label .= $this->recordset[$i][$tab_label_value]->getValue() . " ";
									}
									if(strlen($add_label))
										$add_label = " (" . trim($add_label) . ") ";
								} elseif(isset($this->recordset[$i][$this->tab_label])) {
									$add_label = " (" . $this->recordset[$i][$this->tab_label]->getValue() . ") ";
								} else {
									$add_label = "";
								}
							} else {
								$add_label = "";
							}
							$this->strError = "Il campo \"" . ffCommon_specialchars($this->form_fields[$key]->label) . $add_label . "\" &egrave; obbligatorio";
							break;
						}
						// corrispondency
						elseif ($this->form_fields[$key]->display && strlen($this->form_fields[$key]->compare) && $this->recordset[$i][$key]->getValue() !== $this->recordset[$i][$this->form_fields[$key]->compare]->getValue())
						{
							$this->form_fields[$key]->contain_error = true;
							$this->contain_error = true;
							$this->strError = "Il campo \"" . ffCommon_specialchars($this->form_fields[$this->form_fields[$key]->compare]->label) . "\" non corrisponde";
						}
						elseif ($this->form_fields[$key]->display && $this->form_fields[$key]->enable_check_format && ($tmp = $this->form_fields[$key]->check_format($this->recordset[$i][$key])))
						{
							$this->form_fields[$key]->contain_error = true;
							$this->contain_error = true;
							$this->strError = $tmp;
							break;
						}

						if ($this->contain_error)
							break;

					}
					reset($this->form_fields);

					if ($need_key_check && !$this->contain_error)
					{
						// keys unicity
						$db = ffDB_Sql::factory();
						$db[0]->query("SELECT * FROM " . $this->src_table . " WHERE " . $tmp_where);
						if ($db[0]->nextRecord())
						{
							$this->contain_error = true;
							$this->strError = "I campi chiave del record sono duplicati";
						}
					}
				}
			}
		}
	}
	
	function getOrderSQL()
	{
		$tmp_odf = $this->getOrderDefault();
		$tmp_odd = $this->getOrderDefaultDir();
		$tmp_sql = "";
		
		$tmp = null;
		if (isset($this->key_fields[$tmp_odf]))
			$tmp = $this->key_fields[$tmp_odf];
		else if (isset($this->hidden_fields[$tmp_odf]))
			$tmp = $this->hidden_fields[$tmp_odf];
		else if (isset($this->form_fields[$tmp_odf]))
			$tmp = $this->form_fields[$tmp_odf];			
		
		if ($tmp !== null && strlen($tmp->order_SQL))
		{
			// build order SQL
			$tmp_sql = " " . str_replace("[ORDER_DIR]", $this->direction, $tmp->order_SQL); //. " " .  $this->direction . " ";
		}
		else
		{
			$tmp_orderfield = ($tmp !== null ? $tmp->get_order_field() : $tmp_odf);
			if (strpos($tmp_orderfield, "`") !== 0)
				$tmp_orderfield = "`" . $tmp_orderfield . "`";
			
			$tmp_sql = $tmp_orderfield . " " . $tmp_odd;
		}

		return $tmp_sql;
	}
	
	function getOrderDefault()
	{
		return $this->order_default;
	}

	function getOrderDefaultDir()
	{
		return $this->order_default_dir;
	}

	/**
	 * elabora la sezione relativa alla visualizzazione dell'errore nel template
	 * da richiamare ogniqualvolta si aggiorna l'errore
	 */
	function displayError($sError = null)
	{
		if ($sError !== null)
			$this->strError = $sError;

		if (strlen($this->strError))
		{
			$this->tpl[0]->set_var("strError", $this->strError);
			$this->tpl[0]->parse("SectError", false);
		}
		else
			$this->tpl[0]->set_var("SectError", "");

		return $sError;
	}

	/**
	 * aggiunge record vuoti al recordset
	 * @param int @rowstoadd
	 */
	function addRows($rowstoadd)
	{
		for($i = 0; $i < $rowstoadd; $i++)
		{
			$next_element = count($this->recordset);
			$this->recordset[$next_element] = array();
			// create empty keys to preserve class functionality
			foreach ($this->key_fields as $key => $FormField)
			{
				$this->recordset[$next_element][$key] = $this->key_fields[$key]->getDefault(array(&$this));
				$this->recordset_ori[$next_element][$key] = new ffData(null, $this->key_fields[$key]->base_type, FF_SYSTEM_LOCALE);
			}
			reset($this->key_fields);

			foreach ($this->hidden_fields as $key => $FormField)
			{
				$this->recordset[$next_element][$key] = $this->hidden_fields[$key]->getDefault(array(&$this));
				// create a empty object to preserve class functionality
				$this->recordset_ori[$next_element][$key] = new ffData(null, $this->hidden_fields[$key]->base_type, FF_SYSTEM_LOCALE);
			}
			reset($this->hidden_fields);

			foreach ($this->form_fields as $key => $FormField)
			{
				if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				{
					foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
					{
						// create a empty object to preserve class functionality
						$this->recordset_ori[$next_element][$key][$subkey] = new ffData(null, $subvalue["type"], FF_SYSTEM_LOCALE);
						$this->recordset[$next_element][$key][$subkey] = clone $this->recordset_ori[$next_element][$key][$subkey];
					}
					reset($this->form_fields[$key]->multi_fields);
				}
				else
				{
					// create a empty object to preserve class functionality
					$rowstoadd_default = null;
					if($this->rowstoadd_field_default)
					{
						$rowstoadd_header_default = $this->parent[0]->retrieve_param($this->id, "rowstoadd_header_default_" . $key);
						$rowstoadd_footer_default = $this->parent[0]->retrieve_param($this->id, "rowstoadd_footer_default_" . $key);
						if(strlen($rowstoadd_header_default))
							$rowstoadd_default = $rowstoadd_header_default;
						else
							$rowstoadd_default = $rowstoadd_footer_default;
						$rowstoadd_default = new ffData($rowstoadd_default, $this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					}

					$this->recordset_ori[$next_element][$key] = new ffData(null, $this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);
					$this->recordset[$next_element][$key] = ($rowstoadd_default ? $rowstoadd_default : $this->form_fields[$key]->getDefault(array(&$this)));
				}
			}
			reset($this->form_fields);
		}
	}

	/**
	 * esegue il processing dell'azione (frmAction)
	 * dipende dall'elaborazione dell'azione del record, lo eredita dal record principale in alcuni casi
	 * questo significa che l'azione "confirmdelete" del record viene estesa anche al dettaglio, ad esempio
	 * il dettaglio ha anche azioni specifiche, come "detail_delete"
	 * @return Boolean se è false va tutto bene, se è true il record deve annullare l'azione principale
	 */
	function process_action()
	{
		$res = $this->doEvent("on_do_action", array(&$this, $this->frmAction));
		$rc = end($res);
		if (null !== $rc)
		{
			if ($rc === true)
				return true;
			else
				return false;
		}
		
		if ($this->skip_action) // per fare lo skip del solo SQL, è sufficiente lo skip_action del record
			return false;
			
		$rc = false; /* if something on the page set this to true, then the parent record
						must stop processing and show the error */

		switch (isset($this->main_record[0]->default_actions[$this->frmAction]) ? $this->main_record[0]->default_actions[$this->frmAction] : $this->frmAction)
		{
			case "detail_addrows":
				$rowstoadd = intval($this->parent[0]->retrieve_param($this->id, "rowstoadd"));
				if ($rowstoadd > 0 && $rowstoadd < 20)
				{
					$this->addRows($rowstoadd);
				}
				else
					$this->strError = "Digitare un valore compreso fra 0 e 20";
				$this->rows = count($this->recordset);
				
				$res = $this->doEvent("on_done_action", array(&$this, $this->frmAction));
				break;

			case "detail_delete":
				$delete_row = $this->parent[0]->retrieve_param($this->id, "delete_row");

				// FIRST OF ALL, DELETE TEMPORARY FILES (if presents)
				foreach ($this->form_fields as $key => $value)
				{
					if ($this->form_fields[$key]->extended_type == "File")
					{
						if (strlen($this->recordset_files[$delete_row][$key]["tmpname"]))
						{
							ffErrorHandler::raise("DEBUG - non dovresti vederlo", E_USER_ERROR, $this, get_defined_vars());
							@unlink($this->form_fields[$key]->getFileFullPath($this->recordset_files[$delete_row][$key]["tmpname"], true));
						}
					}
				}
				reset($this->form_fields);

				$next_element = count($this->deleted_keys);

				foreach ($this->key_fields as $key => $value)
				{
					if (strlen($this->recordset_ori[$delete_row][$key]->getValue()))
						$this->deleted_keys[$next_element][$key] = $this->recordset_ori[$delete_row][$key];
				}
				reset($this->key_fields);

				if (isset($this->deleted_keys[$next_element]))
				{
					foreach ($this->form_fields as $key => $value)
					{
						$this->deleted_values[$next_element][$key] = $this->recordset_ori[$delete_row][$key];
					}
					reset($this->form_fields);
					foreach ($this->hidden_fields as $key => $value)
					{
						$this->deleted_values[$next_element][$key] = $this->recordset_ori[$delete_row][$key];
					}
					reset($this->form_fields);
				}

				for ($i = $delete_row; $i < (count($this->recordset)-1); $i++)
					$this->recordset[$i] = $this->recordset[$i + 1];

				for ($i = $delete_row; $i < (count($this->recordset_ori)-1); $i++)
					$this->recordset_ori[$i] = $this->recordset_ori[$i + 1];

				array_pop($this->recordset);
				array_pop($this->recordset_ori);
				$this->rows = count($this->recordset);

				if ($this->delete_istant)
				{
					for ($i = 0; $i < count($this->deleted_keys); $i++)
					{
						$sSQL = "";
						foreach ($this->key_fields as $key => $value)
						{
							if (strlen($this->deleted_keys[$i][$key]->getValue()))
							{
								if (strlen($sSQL))
									$sSQL .= " AND ";
								$sSQL .= " " . $this->key_fields[$key]->get_data_source() . " = " . $this->db[0]->toSql($this->deleted_keys[$i][$key], $this->key_fields[$key]->base_type);
							}
						}
						reset($this->key_fields);
						if (strlen($sSQL))
						{
							$rc |= $this->delete_record($sSQL, $i, $this->deleted_keys, $this->deleted_values);
						}
					}
				}
				break;

			case "confirmdelete":
				if (count($this->recordset))
				{
					for ($i = 0; $i < count($this->recordset_ori); $i++)
					{
						$sSQL = "";
						foreach ($this->key_fields as $key => $value)
						{
							if (strlen($this->recordset_ori[$i][$key]->getValue()))
							{
								if (strlen($sSQL))
									$sSQL .= " AND ";
								$sSQL .= " " . $this->key_fields[$key]->get_data_source() . " = " . $this->db[0]->toSql($this->recordset_ori[$i][$key], $this->key_fields[$key]->base_type);
							}
						}
						reset($this->key_fields);
						if (strlen($sSQL))
						{
							$rc |= $this->delete_record($sSQL . " AND " . $this->sWhere . $this->sAddWhere, $i, $this->recordset_ori, $this->recordset_ori);
						}
					}
				}

				if ($rc)
					return true;

				$res = $this->doEvent("on_done_action", array(&$this, $this->frmAction));
				if (end($res) === true)
					return true;
				break;

			case "insert":
				if (count($this->recordset))
				{
					$tmp = $this->recordset;
					foreach ($tmp as $i => $value)
					{
						$rc |= $this->insert_record($sSQL, $i);
					}
				}

				if ($rc)
					return true;

				$res = $this->doEvent("on_done_action", array(&$this, $this->frmAction));
				if (end($res) === true)
					return true;
				break;

			case "update":
				// first, delete deleted records
				if (count($this->deleted_keys))
				{
					for ($i = 0; $i < count($this->deleted_keys); $i++)
					{
						$sSQL = "";
						foreach ($this->key_fields as $key => $value)
						{
							if (strlen($this->deleted_keys[$i][$key]->getValue()))
							{
								if (strlen($sSQL))
									$sSQL .= " AND ";
								$sSQL .= " " . $this->key_fields[$key]->get_data_source() . " = " . $this->db[0]->toSql($this->deleted_keys[$i][$key], $this->key_fields[$key]->base_type);
							}
						}
						reset($this->key_fields);
						if (strlen($sSQL))
						{
							$rc |= $this->delete_record($sSQL . (strlen($this->sWhere) || strlen($this->sAddWhere) ? " AND " : "") . $this->sWhere . $this->sAddWhere , $i, $this->deleted_keys, $this->deleted_values);
						}
					}
				}

				if ($rc)
					return true;

				if (count($this->recordset))
				{
					$tmp = $this->recordset;
					foreach ($tmp as $i => $value)
					{
						$sSQL = "";
						foreach ($this->key_fields as $key => $value)
						{
							if (strlen($this->recordset_ori[$i][$key]->getValue()))
							{
								if (strlen($sSQL))
									$sSQL .= " AND ";
								$sSQL .= " " . $this->key_fields[$key]->get_data_source() . " = " . $this->db[0]->toSql($this->recordset_ori[$i][$key], $this->key_fields[$key]->base_type);
							}
						}
						reset($this->key_fields);

						if (strlen($sSQL))
						{ // update existing records
							$rc |= $this->update_record($sSQL . (strlen($this->sWhere) || strlen($this->sAddWhere) ? " AND " : "") . $this->sWhere . $this->sAddWhere, $i);
						}
						else
						{ // insert new records
							$rc |= $this->insert_record($sSQL . (strlen($this->sWhere) || strlen($this->sAddWhere) ? " AND " : "") . $this->sWhere . $this->sAddWhere, $i);
						}
					}
				}

				if ($rc)
					return true;

				$res = $this->doEvent("on_done_action", array(&$this, $this->frmAction));
				if (end($res) === true)
					return true;
				break;
		}

		if ($rc || strlen($this->strError))
			return true;
		else
			return false;
	}

	/**
	 * esegue l'insert di un record specifico
	 * @param String $rec_SQL ignorato
	 * @param int $row il numero relativo di record
	 * @return Boolean vedi process_action
	 */
	function insert_record($rec_SQL, $row)
	{
		$res = $this->doEvent("on_do_record_action", array(&$this, "insert", $this->recordset[$row], $this->recordset_ori[$row]));
		$rc = end($res);
		if (null !== $rc)
		{
			if ($rc === true)
				return true;
			else
				return false;
		}

		$fields = ""; $values = "";
		foreach ($this->key_fields as $key => $FormField)
		{
			$this->key_fields[$key]->value = $this->recordset[$row][$key];
			$this->key_fields[$key]->value_ori = $this->recordset_ori[$row][$key];
			if (!$this->key_fields[$key]->auto_key)
			{
				if (strlen($fields))
					$fields .= ", ";
				$fields .= $this->key_fields[$key]->get_data_source();

				if (strlen($values))
					$values .= ", ";
				$values .= $this->db[0]->toSql($this->recordset[$row][$key],
											$this->key_fields[$key]->base_type
											);
			}
		}
		reset($this->key_fields);

		foreach ($this->hidden_fields as $key => $FormField)
		{
			$this->hidden_fields[$key]->value = $this->recordset[$row][$key];
			$this->hidden_fields[$key]->value_ori = $this->recordset_ori[$row][$key];
			if ($this->hidden_fields[$key]->store_in_db == true && !strlen($this->hidden_fields[$key]->compare))
			{
				if (strlen($fields))
					$fields .= ", ";
				$fields .= "`" . $this->hidden_fields[$key]->get_data_source() . "`";

				if (strlen($values))
					$values .= ", ";
				$values .= $this->db[0]->toSql(	$this->recordset[$row][$key],
												$this->hidden_fields[$key]->base_type
											);
			}
		}
		reset($this->hidden_fields);

		foreach ($this->form_fields as $key => $FormField)
		{
			$this->form_fields[$key]->value = $this->recordset[$row][$key];
			$this->form_fields[$key]->value_ori = $this->recordset_ori[$row][$key];

			if ($this->form_fields[$key]->extended_type == "File")
			{
				$this->form_fields[$key]->file_tmpname = $this->recordset_files[$row][$key]["tmpname"];
                if($this->form_fields[$key]->file_full_path) {
                    if (
                        substr(strtolower($this->recordset[$row][$key]->getValue()), 0, 7) != "http://"
                        && substr(strtolower($this->recordset[$row][$key]->getValue()), 0, 8) != "https://"
                        && substr($this->recordset[$row][$key]->getValue(), 0, 2) != "//"
                    ) {
                        //if(is_file($this->form_fields[$key]->getFileTempPath(false) . "/" . basename($this->form_fields[$key]->value->getValue()))) {
                        if($this->form_fields[$key]->file_tmpname)
                        	$this->recordset[$row][$key]->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileFullPath(basename($this->form_fields[$key]->value->getValue()), false)));
                    }
                    $this->form_fields[$key]->value = $this->recordset[$row][$key];
                }
			}

			if ($this->form_fields[$key]->store_in_db == true && !strlen($this->form_fields[$key]->compare))
			{
				if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				{
					foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
					{
						if (strlen($fields))
							$fields .= ", ";
						$fields .= "`" . $key . "_" . $subkey . "`";

						if (strlen($values))
							$values .= ", ";
						$values .= $this->db[0]->toSql(	$this->recordset[$row][$key][$subkey],
															$subvalue["type"]
														 );  
					}
					reset($this->form_fields[$key]->multi_fields);
				}
				else
				{
                    $processed_sql_value = false;
                    
					if (strlen($fields))
						$fields .= ", ";
					$fields .= "`" . $this->form_fields[$key]->get_data_source() . "`";

					if (strlen($values))
						$values .= ", ";
					
					$tmpval = $this->recordset[$row][$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);
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
					elseif ($this->form_fields[$key]->crypt)
					{
						if (MOD_SEC_CRYPT && $this->form_fields[$key]->crypt_modsec)
						{
							$tmpval = mod_sec_crypt_string($tmpval);
							$tmpval = "UNHEX(" . $this->db[0]->toSql(bin2hex($tmpval)) . ")";
                            $processed_sql_value = true;
							$tmp_type = "Text";
						}
					}
					
                    $res = $this->form_fields[$key]->doEvent("on_store_in_db", array(&$this, &$this->recordset[$row][$key]));
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

		if (is_array($this->fields_relationship) && count($this->fields_relationship))
		{
			foreach ($this->fields_relationship as $key => $value)
			{
				if (strlen($fields))
					$fields .= ", ";
				$fields .= "`" . $key . "`";

				if (strlen($values))
					$values .= ", ";
				$values .= $this->db[0]->toSql(
                                ($this->main_record[0]->src_table
                                    ? $this->main_record[0]->key_fields[$value]->value
                                    : $this->main_record[0]->key_fields[$value]->default_value
                                ),
									$this->main_record[0]->key_fields[$value]->base_type
								) . " ";
			}
			reset($this->fields_relationship);
		}

		if (is_array($this->insert_additional_fields) && count($this->insert_additional_fields))
		{
			foreach ($this->insert_additional_fields as $key => $value)
			{
				if (strlen($fields))
					$fields .= ", ";
				if (strpos($key, "`") === false)
					$fields .= "`" . $key . "`";
				else
					$fields .= $key;

				if (strlen($values))
					$values .= ", ";
				$values .= $this->db[0]->toSql($value);
			}
			reset($this->insert_additional_fields);
		}

		$rc = $this->doEvent("on_before_record_insert", array($this, $row, &$fields, &$values));

		$sSQL = "INSERT INTO " . $this->src_table . " ( " . $fields . " ) VALUES ( " . $values . " ) ";
		if (!$this->main_record[0]->skip_action)
			$this->db[0]->execute($sSQL);

		foreach ($this->key_fields as $key => $FormField)
		{
			if ($this->key_fields[$key]->auto_key)
			{
				$this->recordset[$row][$key] = $this->db[0]->getInsertID();
				$this->key_fields[$key]->value = $this->recordset[$row][$key];
				break;
			}

		}
		reset($this->key_fields);

		$res = $this->doEvent("on_done_record_action", array(&$this, "insert", $this->recordset[$row], $this->recordset_ori[$row]));
		$rc = end($res);
		if (true === $rc)
			return true;

		// MANAGE FILES
		if (!$this->main_record[0]->skip_action)
		{
			ffCommon_manage_files($this);
		}
		
		return false;
	}

	/**
	 * esegue l'update di un record specifico
	 * @param String $rec_SQL la clausola WHERE che permette in un SQL d'indentificare quel record specifico
	 * @param int $row il numero relativo di record
	 * @return Boolean vedi process_action
	 */
	function update_record($rec_SQL, $row)
	{
		$res = $this->doEvent("on_do_record_action", array(&$this, "update", $this->recordset[$row], $this->recordset_ori[$row]));
		$rc = end($res);
		if (null !== $rc)
		{
			if ($rc === true)
				return true;
			else
				return false;
		}

		$fields = "";
		$user_data_changed = false;

		foreach ($this->key_fields as $key => $FormField)
		{
			$this->key_fields[$key]->value = $this->recordset[$row][$key];
			$this->key_fields[$key]->value_ori = $this->recordset_ori[$row][$key];
			if (!$this->key_fields[$key]->auto_key)
			{
				if (strlen($fields))
					$fields .= ", ";
				$fields .= $this->key_fields[$key]->get_data_source()
							. " = "
							. $this->db[0]->toSql(	$this->recordset[$row][$key],
													$this->key_fields[$key]->base_type
												);
			}
		}
		reset($this->key_fields);

		foreach ($this->hidden_fields as $key => $FormField)
		{
			$this->hidden_fields[$key]->value = $this->recordset[$row][$key];
			$this->hidden_fields[$key]->value_ori = $this->recordset_ori[$row][$key];
			if ($this->hidden_fields[$key]->store_in_db === true &&
					!strlen($this->hidden_fields[$key]->compare) &&
					!($this->hidden_fields[$key]->extended_type == "Password" &&
					!strlen($this->recordset[$row][$key]->getValue())) &&
					$this->recordset_ori[$row][$key]->getValue() != $this->recordset[$row][$key]->getValue()
				)
			{
				if (strlen($fields))
					$fields .= ", ";
				$fields .= $this->hidden_fields[$key]->get_data_source()
							. " = "
							. $this->db[0]->toSql(	$this->recordset[$row][$key],
													$this->hidden_fields[$key]->base_type
												);
			}
			elseif ($this->hidden_fields[$key]->store_in_db === false && $this->recordset_ori[$row][$key]->getValue() != $this->recordset[$row][$key]->getValue())
				$user_data_changed |= true;

		}
		reset($this->hidden_fields);

		foreach ($this->form_fields as $key => $FormField)
		{
			$this->form_fields[$key]->value = $this->recordset[$row][$key];
			$this->form_fields[$key]->value_ori = $this->recordset_ori[$row][$key];
            
            if ($this->form_fields[$key]->extended_type == "File")
            {
                $this->form_fields[$key]->file_tmpname = $this->recordset_files[$row][$key]["tmpname"];
                if($this->form_fields[$key]->file_full_path) {
                    if (
                        substr(strtolower($this->recordset[$row][$key]->getValue()), 0, 7) != "http://"
                        && substr(strtolower($this->recordset[$row][$key]->getValue()), 0, 8) != "https://"
                        && substr($this->recordset[$row][$key]->getValue(), 0, 2) != "//"
                    ) {
                        //if(is_file($this->form_fields[$key]->getFileTempPath(false) . "/" . basename($this->form_fields[$key]->value->getValue()))) {
                        if($this->form_fields[$key]->file_tmpname)
	                        $this->recordset[$row][$key]->setValue(str_replace($this->form_fields[$key]->getFileBasePath(), "", $this->form_fields[$key]->getFileFullPath(basename($this->form_fields[$key]->value->getValue()), false)));

                        $this->form_fields[$key]->value = $this->recordset[$row][$key];
                    }
                }
            }

			if ($this->form_fields[$key]->store_in_db === true &&
					!strlen($this->form_fields[$key]->compare) &&
					!($this->form_fields[$key]->extended_type == "Password" && !strlen($this->recordset[$row][$key]->getValue()))
				)
			{
				if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				{
					foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
					{
						if ($this->recordset_ori[$row][$key][$subkey]->getValue() != $this->recordset[$row][$key][$subkey]->getValue())
						{
							if (strlen($fields))
								$fields .= ", ";
							
							$fields .= "`" . $key . "_" . $subkey . "` = "
									. $this->db[0]->toSql(	$this->recordset[$row][$key][$subkey],
															$subvalue["type"]
														 );  
						}
					}
					reset($this->form_fields[$key]->multi_fields);
				}
				elseif ($this->recordset_ori[$row][$key]->getValue() != $this->recordset[$row][$key]->getValue())
				{
					if (strlen($fields))
						$fields .= ", ";
                    
					$tmpval = $this->recordset[$row][$key]->getValue($this->form_fields[$key]->base_type, FF_SYSTEM_LOCALE);
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
					elseif ($this->form_fields[$key]->crypt)
					{
						if (MOD_SEC_CRYPT && $this->form_fields[$key]->crypt_modsec)
						{
							$tmpval = mod_sec_crypt_string($tmpval);
							$tmpval = "UNHEX(" . $this->db[0]->toSql(bin2hex($tmpval)) . ")";
							$processed_sql_value = true;
							$tmp_type = "Text";
						}
					}
							
                    $res = $this->form_fields[$key]->doEvent("on_store_in_db", array(&$this, &$this->recordset[$row][$key]));
                    $rc = end($res);
                    if ($rc !== null)
					{
                        $tmpval = $rc;
						$processed_sql_value = false;
						$tmp_type = $this->form_fields[$key]->base_type;
					}
                    
					$fields .= "`" . $this->form_fields[$key]->get_data_source()
								. "` = "
								. ($processed_sql_value ? $tmpval : $this->db[0]->toSql($tmpval, $tmp_type));
				}
			}
			elseif ($this->form_fields[$key]->store_in_db === false)
			{
				if (is_array($this->form_fields[$key]->multi_fields) && count($this->form_fields[$key]->multi_fields))
				{
					foreach ($this->form_fields[$key]->multi_fields as $subkey => $subvalue)
					{
						if ($this->recordset_ori[$row][$key][$subkey]->getValue() != $this->recordset[$row][$key][$subkey]->getValue())
							$user_data_changed |= true;
					}
					reset($this->form_fields[$key]->multi_fields);
				}
				elseif ($this->recordset_ori[$row][$key]->getValue() != $this->recordset[$row][$key]->getValue())
					$user_data_changed |= true;
			}
		}
		reset($this->form_fields);

		if (is_array($this->update_additional_fields) && count($this->update_additional_fields))
		{
			foreach ($this->update_additional_fields as $key => $value)
			{
				if (strlen($fields))
					$fields .= ", ";
				
				if (strpos($key, "`") === false)
					$fields .= "`" . $key . "`";
				else
					$fields .= $key;
				
				$fields .= " = " . $this->db[0]->toSql($value);
			}
			reset($this->update_additional_fields);
		}

		$rc = $this->doEvent("on_before_record_update", array($this, $row, &$fields));
		if (strlen($fields))
		{
			$sSQL = "UPDATE " . $this->src_table . " SET " . $fields . " WHERE " . $rec_SQL;
			if (!$this->main_record[0]->skip_action)
				$this->db[0]->execute($sSQL);
		}

		if (strlen($fields) || $user_data_changed)
		{
			$res = $this->doEvent("on_done_record_action", array(&$this, "update", $this->recordset[$row], $this->recordset_ori[$row]));
			$rc = end($res);
			if (true === $rc)
				return true;
		}

		// MANAGE FILES
		if (!$this->main_record[0]->skip_action)
		{
			ffCommon_manage_files($this);
		}
		
		return false;
	}

	/**
	 * elimina un record specifico
	 * @param String $rec_SQL la clausola WHERE che permette in un SQL d'indentificare quel record specifico
	 * @param int $row il numero relativo di record
	 * @param Array $keys_array i campi chiave del record
	 * @param Array $values_array i dati del record
	 * @return Boolean vedi process_action
	 */
	function delete_record($rec_SQL, $row, $keys_array, $values_array)
	{
		$res = $this->doEvent("on_do_record_action", array(&$this, "delete", $keys_array[$row], $values_array[$row]));
		$rc = end($res);
		if (null !== $rc)
		{
			if ($rc === true)
				return true;
			else
				return false;
		}

		$tmp_action = $this->del_action;
		if (strlen($this->del_auto_action))
		{
			$bResult = false;
			$tmp_db = ffDB_Sql::factory();
			// first of all, do all the sql for subsequent checks
			foreach ($this->del_auto_SQL as $key => $value)
			{
				$tmp_db[0]->query($this->process_delete_SQL($value, $row, $keys_array));
				if ($tmp_db[0]->nextRecord())
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
						$this->strError = "Esistono record correlati, impossibile procedere all'eliminazione di alcuni record.";
						return true;
					}
			}
		}


		if (!$this->main_record[0]->skip_action)
		{
			foreach ($this->form_fields as $key => $FormField)
			{
				switch($this->form_fields[$key]->extended_type)
				{
					case "File":
						$storing_path = ffProcessTags($this->form_fields[$key]->file_storing_path, $keys_array[$row], array_merge($values_array[$row]));
						foreach ($this->fields_relationship as $subkey => $subvalue)
						{
							$storing_path = str_replace("[" . $subvalue . "_FATHER]"
                                , ($this->main_record[0]->src_table
                                    ? $this->main_record[0]->key_fields[$subvalue]->value->getValue(null, FF_SYSTEM_LOCALE)
                                    : $this->main_record[0]->key_fields[$subvalue]->default_value->getValue(null, FF_SYSTEM_LOCALE)
                                )
                                , $storing_path
                            );
						}
						reset($this->fields_relationship);

						if (strlen($values_array[$row][$key]->getValue()))
							@unlink($storing_path . "/" . $values_array[$row][$key]->getValue());
						break;

					default:
				}
			}
			reset($this->form_fields);
		}
		
		switch ($tmp_action)
		{
			case "delete":
				$sSQL = "DELETE FROM " . $this->src_table . " WHERE " . $rec_SQL;
				if (!$this->main_record[0]->skip_action)
					$this->db[0]->execute($sSQL);
				break;

			case "multi_delete":
				$sSQL = "DELETE FROM " . $this->src_table . " WHERE " . $rec_SQL;
				if (!$this->main_record[0]->skip_action)
					$this->db[0]->execute($sSQL);

				foreach ($this->del_multi_delete as $key => $value)
				{
					if (!$this->main_record[0]->skip_action)
						$this->db[0]->execute($this->process_delete_SQL($value, $row, $keys_array));
				}
				reset($this->del_multi_delete);
				break;

			case "update":
				$sSQL = "UPDATE " . $this->src_table . " SET " . $this->process_delete_SQL($this->del_update, $row, $keys_array) . " WHERE " . $rec_SQL;
				if (!$this->main_record[0]->skip_action)
					$this->db[0]->execute($sSQL);
				break;

			case "multi_update":
				$sSQL = "UPDATE " . $this->src_table . " SET " . $this->process_delete_SQL($this->del_update, $row, $keys_array) . " WHERE " . $rec_SQL;
				if (!$this->main_record[0]->skip_action)
					$this->db[0]->execute($sSQL);

				foreach ($this->del_multi_update as $key => $value)
				{
					if (!$this->main_record[0]->skip_action)
						$this->db[0]->execute($this->process_delete_SQL($value, $row, $keys_array));
				}
				reset($this->del_multi_update);
				break;
		}

		$res = $this->doEvent("on_done_record_action", array(&$this, "delete", $keys_array[$row], $values_array[$row]));
		$rc = end($res);
		if (true === $rc)
			return true;

		return false;
	}

	/**
	 * elabora i tag di un istruzione DELETE
	 * sono permessi:
	 * 	[DATE]
	 * 	[key_VALUE]
	 * @param String l'SQL da modificare
	 * @param int $row il numero relativo di riga
	 * @param Array $keys_array l'insieme dei campi chiave del record
	 * @return String l'istruzione SQL modificata
	 */
	function process_delete_SQL($sSQL, $row, $keys_array)
	{
		$sSQL = str_replace(	"[DATE]"
								, $this->db[0]->toSql(new ffData(date("%d/%m/%Y"), "Date", "ITA"))
								, $sSQL
							);
		foreach ($this->key_fields as $key => $FormField)
		{
			$sSQL = str_replace(	"[" . $key . "_VALUE]"
									, $this->db[0]->toSql($keys_array[$row][$key], $this->key_fields[$key]->base_type )
									, $sSQL
								);
		}
		reset($this->key_fields);
		return $sSQL;
	}

	/**
	 * esegue un redirect
	 * @param String $url l'indirizzo a cui andare
	 */
	function redirect($url)
	{
		ffRedirect($url);
	}

	/**
	 * visualizza un dialog
	 * @param Boolean $returnurl se deve restituire l'url invece di eseguirlo
	 * @param String $type il tipo di dialog, può essere "yesno" "okonly"
	 * @param String $title il titolo della pagina del dialog
	 * @param String $message il messaggio da visualizzare
	 * @param String $cancelurl l'url di negazione
	 * @param String $confirmurl l'url di conferma
	 * @return String vedi $returnurl
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

	function addDefaultButton($type, $obj)
	{
		$this->addContentButton($obj
								, $this->buttons_options[$type]["index"]);
	}
    
	/**
	 * prepara i pulsanti standard del dettaglio
	 */
	function preProcessDetailButtons()
	{
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
				$tmp->id 			= "deleterow";
				$tmp->frmAction		= "detail_delete";
                $tmp->label         = $this->buttons_options["delete"]["label"];
                $tmp->class         = $this->buttons_options["delete"]["class"];
                $tmp->aspect        = $this->buttons_options["delete"]["aspect"];
				$tmp->action_type 	= "submit";
				$tmp->component_action = $this->main_record[0]->id;
				$this->addDefaultButton("delete", $tmp);
			}
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
	
	function getSqlPopulateEdit()
	{
		if ($this->populate_edit_DS !== null)
		{
			if (is_string($this->populate_edit_DS))
				return ffDBSource::getSource($this->populate_edit_DS)->getSql($this);
			else
				return $this->populate_edit_DS->getSql($this);
		}
		else
			return $this->populate_edit_SQL;
	}
	
	function getSqlPopulateInsert()
	{
		if ($this->populate_insert_DS !== null)
		{
			if (is_string($this->populate_insert_DS))
				return ffDBSource::getSource($this->populate_insert_DS)->getSql($this);
			else
				return $this->populate_insert_DS->getSql($this);
		}
		else
			return $this->populate_insert_SQL;
	}
}
