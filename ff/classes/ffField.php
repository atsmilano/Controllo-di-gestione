<?php
/** 
 * Data Field
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffField
{
	static protected $events = null;

	/**
	 * Funzione per creare un'instanza di ffField.
	 * Per creare una nuova istanza di ffField utilizzare "::factory()"
	 *
	 */
	public function __construct()
	{
		ffErrorHandler::raise("Cannot istantiate " . __CLASS__ . " directly, use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}
	
	public function __clone()
	{
		ffErrorHandler::raise("Cannot clone " . __CLASS__ . ", use ::factory instead", E_USER_ERROR, $this, get_defined_vars());
	}

	/**
	 * Aggiunge un evento a ffField
	 * @param String Nome dell'evento associato al Field
	 * @param String Nome della funzione
	 * @param <type> $priority
	 * @param <type> $index
	 * @param <type> $break_when
	 * @param <type> $break_value 
	 */
	static public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null)
	{
		self::initEvents();
		self::$events->addEvent($event_name, $func_name, $priority, $index, $break_when, $break_value);
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
	 * @param ffPage_base $page
	 * @param string $disk_path
	 * @param string $site_path
	 * @param string $page_path
	 * @param string $theme
	 * @param array $variant
	 * @return ffField_base
	 */
	public static function factory(ffPage_base $page = null, $disk_path = null, $site_path = null, $page_path = null, $theme = null, array $variant = null)
	{
		if ($page === null && ($disk_path === null || $site_path === null))
			ffErrorHandler::raise("page or fixed path_vars required", E_USER_ERROR, null, get_defined_vars());
		
		if ($theme === null)
		{
			if ($page !== null)
				$theme = $page->theme;
			else
				$theme = FF_DEFAULT_THEME;
		}
			
		if ($disk_path === null)
		{
			if ($page !== null)
				$disk_path = $page->disk_path;
		}
			
		if ($site_path === null)
		{
			if ($page !== null)
				$site_path = $page->site_path;
		}
			
		if ($page_path === null)
		{
			if ($page !== null)
				$page_path = $page->page_path;
		}
			
		
		$res = self::doEvent("on_factory", array($page, $disk_path, $site_path, $page_path, $theme, $variant));
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
		$tmp = new $class_name($disk_path, $site_path, $page_path, $theme);
		
		$res = self::doEvent("on_factory_done", array($tmp));
		
		return $tmp;
	}
}

/**
 * ffField è la classe adibita alla gestione dei controlli
 * d'interfaccia con l'utente
 *
 * @package FormsFramework
 * @subpackage interface
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffField_base extends ffCommon
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)
	// ----------------------------------

	/**
	 * ID di ffField; univoco per ogni ffField all'interno di ffPage
	 * @var String
	 */
	var $id				= "";		

	//----------------------------
	//  Data settings

	var $locale				= null;		/* the region code used to translate values for output pourpose.
											if null, this assume FF_LOCALE value. */

	/**
	 * Contiene il valore attuale di ffField
	 * @var ffData
	 */
	var $value				= null;		
	var $display_value		= null;		

	var $data_type			= "db";	/* The source of data displayed & managed. This and data_source is ignored by
										some superclasses functions, like the search form in ffGrid, wich are
										implicit managed by core.

										May be blank for disabled (managed by the programmer / buttons, etc) or:

										db 			: a db field, retrieved within the superclass
										callback	: the results of a callback function
									*/

    /**
	 * E' la sorgente dati di ffField; se il valore di $data_type è "db" e $data_source è "",
	 * ffField assumerà il valore di $id
	 * Se il valore è "callback"
	 * @var String
	 */
	var $data_source		= "";	

	/**
	 * Formato utilizzato per salvare i valori.
	 * I formati disponibili sono: Text, Number, DateTime, Date, Time, Binary
	 * E' una campo obbligatorio quando si lavora con un DB.
	 * @var String
	 */
	var $base_type		= "Text"; 	

	/**
	 * Tipo usato per memorizzare i valori e gestire operazioni ad alto livello come la visualizzazione.
	 * Se è "null" il campo verrà trattato in base al valore di "base_type".
	 * I valori che può assumere app_type sono Text, Number, Date, Time, Binary (per salvare file)
	 * @var String
	 */
	var $app_type		= null; 	

	/**
	 * E' un estensione di "base_type"; è utilizzato per modificare
	 * il tipo di controllo da applicare;
	 * i formati possibili sono: String, Text, Password, Integer, Currency, 
	 * Float, Date, Year, Month, Day, Time, Hours, Minutes, Seconds,
	 * Boolean, Flags, Selection, Email, HTML, File.
	 * NB: se come extended_type si imposta "File" e come base_type "Text",
	 * il framework salverï¿½ solo il percorso del file.
	 * Se invece imposti "Binary" come base_type il framework salverà
	 * l'intero contenuto del file.
	 *
	 * @var String
	 */
	var $extended_type	= "String";	

	/**
	 * Quando settato a true, se il valore da memorizzare nel DB è "null"
	 * viene automaticamente convertito a 0 per i Field con $base_type "Numeric",
	 * mentre a null per i Field testuali.
	 * @var Boolean
	 */
	var $db_transform_null		= true;

	/**
	 * Utilizzata per il campo chiave;
	 * Quando settata a "true" vengono ignorati i campi chiave nascosti
	 * in insert / update (comportamento di default, come i campi auto_increment)
	 * Se settato a "false" verranno gestiti i campi chiave nascosti.
	 * N.B. I campi chiave nascosti non sono settati dall'utente;
	 * sono settati dal programmatore tramite un evento.
	 * @var Boolean
	 */
	var $auto_key				= true; 

	var $multi_fields = null;

	//----------------------------
	//  Control settings


	/**
	 * Label del campo
	 * @var String
	 */
	var $label			= "";		
	
	var $placeholder = false;
	/**
	 * Tipo di controllo che verrà utilizzato per il campo. Di default "";
	 * I valori possibili sono: label, input, textarea, checkbox, radio, combo, list, file e picture
	 * Se al Field è associata una widget, $control_type verrà sovrascritto.
	 * @var String
	 */
	var $control_type	= "";		

	/**
	 * Utilizzato per nascondere un controllo; il controllo non è visualizzato ma è gestito.
	 * @var Boolean
	 */
	var $display = true;	

	/**
	 * Esegue l'encoding delle entità HTML. Se si intende gestire
	 * l'encoding bisogna settarlo a false.
	 * @var Boolean
	 */
	var $encode_entities = true;	

	/**
	 * contenuto fisso da pre-porre al risultato dell'elaborazione del template
	 * @var String
	 */
	var $fixed_pre_content = "";

	/**
	 * contenuto fisso da post-porre al risultato dell'elaborazione del template
	 * @var String
	 */
	var $fixed_post_content = "";

	/**
	 * utilizzata dai componenti, determina la larghezza del contenitore che conterrà il field
	 * @var String
	 */
	var $width 				= "";

	//----------------------------
	//  Specific Control Settings

	//  multi selection (combo, lists, groups)

	/**
	 * Visualizza, all'interno del combo, "Selezionare un elemento.."
	 * @var boolean
	 */
	var	$multi_select_one		= true;

	/**
	 * Valore per la riga "Selezionare un elemento"; è un dato di tipo ffData
	 * @var ffData
	 */
	var	$multi_select_one_val	= null;		

	/**
	 * Label che viene visualizzata quando $multi_select_one è settato a true
	 * @var String
	 */
	var	$multi_select_one_label	= "Selezionare Un Elemento..";	

	/**
	 * Visualizza all'interno del combo la riga "Nessuno"
	 * @var Boolean
	 */
	var	$multi_select_noone		= false;

	/**
	 * Valore per la riga "Nessun elemento"; è un dato di tipo ffData
	 * @var String
	 */
	var	$multi_select_noone_val	= null;							

	/**
	 * Label che viene visualizzata se $multi_select_noone è settata a true.
	 * @var String
	 */
	var	$multi_select_noone_label = "Nessuno";

	/**
	 * in un campo a selezione multipla, visualizza solo il/i valori correntemente selezionati
	 * @var Boolean
	 */
	var $multi_limit_select		= false;

	/**
	 * Base Type dei i valori visualizzati
	 * @var String
	 */
	var $multi_base_type 		= "Text";

	/**
	 * App Type dei valori visualizzati
	 * @var String
	 */
	var $multi_app_type 		= null;

	/**
	 * utilizzato da ffGrid, determina se il campo a selezione multipla dev'essere utilizzato per
	 * la visualizzazione di un filtro invece che per una normale ricerca
	 * (funziona solo se aggiunto con addSearchField()
	 * @var Boolean
	 */
	var $multi_disp_as_filter 	= false;

	/**
	 * Permette d'impostare una funzione che viene eseguita una volta per ogni elemento di un campo
	 * a selezione multipla
	 * @var function
	 */
	var $multi_filter_func		= null;

	/**
	 * Il valore assunto dalla checkbok quando selezionata
	 * @var ffData
	 */
	var	$checked_value			= null;							
	
	/**
	 * Il valore assunto dalla checkbok quando non selezionata
	 * @var ffData
	 */
	var	$unchecked_value		= null;

	var $bool_preserve_value	= false;
	
	/**
	 * Per i field con tipo esteso "Selection" con control_type "checkbox", permette
	 * d'impostare una funzione per il raggruppamento dei valori
	 * I valori che è possibile selezionare dipendono dal "base_type"
	 * Al momento è supportato solo "concat" per il base_type "Text"
	 * @var String
	 */
	var $grouping_action		= "concat";						/* Grouping apply only on extended type "Selection" and control type "checkbox".
																	With this parameter u can choose the grouping action depending on base_type.
																		base_type = Text
																			concat: concatenate strings, using a separation string
																*/

	// radio
	/**
	 * Per i control_type "radio", visualizza la label per ogni elemento
	 * @var Boolean
	 */
	var $radio_display_label	= true;
	/**
	 * Per i control_type "radio", abilita l'"a capo"
	 * @var Boolean
	 */
	var $radio_hyphen			= true;
	
	var $crypt = false;
	var $crypt_key = null;
	var $crypt_modsec = false;
	var $crypt_concat = false;

	var $multi_crypt = false;
	var $multi_crypt_key = null;
	var $multi_crypt_modsec = false;
	var $multi_crypt_concat = false;

	/**
	 *
	 * @var Metodo di criptazione.
	 * I valori possibili sono: null, "MD5", "mysql_password", "mysql_oldpassword"
	 */
	var	$crypt_method			= null;				
													
	//----------------------------
	//  Specific Data Type settings

	// File

	var $file_mime 		= "";						// the mime type of an embedded file
	var $file_name 		= "";						// the name of an embedded file
	var $file_tmpname   = "";
	
	/**
	 * Cartella in cui il file ï¿½ salvato
	 * @var String
	 */
	var $file_storing_path 	= "";	// the dir within the file is stored

	/**
	 * Cartella in cui il file ï¿½ salvato temporaneamente dopo l'upload
	 * @var String
	 */
	var $file_temp_path 	= "";	

	/**
	 * Nome di ffField utilizzato per salvare il mime-type di un file embedded
	 * @var String
	 */
	var $file_mime_field	= "";

	/**
	 * Nome di un ffField utilizzato per memorizzare il nome di un file embedded
	 * @var String
	 */
	var $file_name_field 	= "";		

	/**
	 * Dimensione massima, in byte, consentita per l'upload
	 * @var Int
	 */
	var $file_max_size		= 1800000;

	/**
	 * Un elenco dei tipi mime accettati dal campo uploads
	 * @var Array
	 */
	var $file_allowed_mime	= array();

	/**
	 * Abilita la creazione della cartella per il file in upload
	 * @var Boolean
	 */
	var $file_make_dir		= true;

	/**
	 * Abilita la creazione della cartella
	 * temporanea per il file in upload
	 * @var Boolean
	 */
	var $file_make_temp_dir	= true;

	/**
	 * Imposta i permessi sul file
	 * @var Int
	 */
	var $file_chmod			= 0777;

	/**
	 * Abilità la possibilità di scrivere nel DB i percorsi relativi delle immagini; di default viene scritto solo il nome del file.
	 * @var Boolean
	 */
	var $file_full_path		= false;

	/**
	 * Abilita la conversione dei nomi delle immagini normalizzandole
	 * @var Boolean
	 */
	var $file_normalize		= false;

	/**
	 * Percorso assoluto della cartella degli uploads (ad esempio: /var/www/website/uploads); se omesso verrà generato nel seguente modo: FF_DISK_UPDIR
	 * @var Boolean
	 */
	var $file_base_path		= null;		


	/**
	 * Rende modificabile il percorso relativo dell'immagine da interfaccia
	 * @var boolean
	 */
	var $file_writable		= false;	

	/**
	 * Rende visibile il controllo per uplodare l'immagine
	 * @var boolean
	 */
	var $file_show_control	= true;

	/**
	 * URL per la visualizzazione del file salvato
	 * @var String
	 */
	var $file_saved_view_url				= "";

	/**
	 * URL per la preview del file salvato
	 * @var String
	 */
	var $file_saved_preview_url				= "";

	/**
	 * eventuale query string da accodare all'url di visualizzazione (GLOBALE)
	 * @var String
	 */
	var $file_query_string					= "";
	
	var $file_modify_dialog					= true;
	var $file_modify_referer                = "";

	/**
	 * eventuale query string da accodare all'url di visualizzazione
	 * @var String
	 */
	var $file_saved_view_query_string		= "";
	/**
	 * eventuale query string da accodare all'url di preview
	 * @var String
	 */
	var $file_saved_preview_query_string	= "";

	/**
	 * URL temporaneo per la visualizzazione del file
	 * @var String
	 */
	var $file_temp_view_url					= "";
	/**
	 * URL temporaneo per la visualizzazione dell'anteprima dei file
	 * @var String
	 */
	var $file_temp_preview_url				= "";

	/**
	 * eventuale query string da accodare all'url di visualizzazione dei file temporanei
	 * @var String
	 */
	var $file_temp_view_query_string		= "";
	/**
	 * eventuale query string da accodare all'url di visualizzazione della preview dei file temporanei
	 * @var String
	 */
	var $file_temp_preview_query_string		= "";

	/**
	 * visualizza il pulsante di eliminazione di un file
	 * @var Boolean
	 */
	var $file_show_delete	= true;

	/**
	 * visualizza il pulsante di modifica di un file se disponibile il servizio
	 * @var Boolean
	 */
	var $file_show_edit	= false;
	/**
	 * Utilizza il servizio Aviary per l'editinig inline dell'immagine
	 * @var Boolean
	 */
	var $file_edit_type		= "Aviary";
	var $file_edit_params	= array("Aviary" => array(
														"key" => ""
														, "tools" => "all"
														, "theme" => "light"  //light, dark
														, "version" => 3
														, "post_url" => ""
	));

	/**
	 * visualizza l'anteprima di un file
	 * @var Boolean
	 */
	var $file_show_preview	= true;

	/**
	 * visualizza il nome dei file caricati
	 * @var Boolean
	 */
	var $file_show_filename	= false;

	/**
	 * visualizza il link
	 * @var Boolean
	 */
	var $file_show_link	= false;

	/**
	 * visualizza la dimensione dei file caricati
	 * @var Boolean
	 */
    var $file_show_filesize = false;

	/**
	 * verifica che un file esista veramente per la visualizzazione dell'anteprima e della cancellazione
	 * @var Boolean
	 */
    var $file_check_exist	= false;
	/**
	 * Disabilita il salvataggio temporaneo del file
	 * Di default settato a false
	 * @var Boolean
	 */
	var $file_avoid_temporary = false;
	
	var $file_name_override = null;

	/**
	 * Abilita il salvataggio di file multipli
	 * Di default settato a false
	 * @var Boolean
	 */
    var $file_multi = false;
	/**
	 * Separatore per i file multipli
	 * Di default settato a ,
	 * @var Char
	 */
    var $file_separator = ",";

    var $file_thumb = array("width" => 100
    						, "height" => 100
    					);
    	
	//  multi selection (combo, lists, groups)

	var $field_params_ignore_change = false;
	
	/**
	 * SELECT SQL per valorizzare il combo
	 * @var String
	 */
	var $source_SQL = "";

	/**
	 * DATA SOURCE per valorizzare il combo
	 * può essere un nome od un oggetto
	 * @var Mixed
	 */
	var $source_DS 	= null;

	/**
	 * Proprietà per valorizzare il combo; è un array di array; ogni array interno contiene due elementi di tipo ffData, il primo è il valore salvato nel DB ed il secondo quello visualizzato all'interno del combo.
	 *		array(
	 *				 array("1", "element one")
	 *				,array("2", "element two")
	 *				,array("2", "another element two)
	 *			);
	 * @var array()
	 */
	var $multi_pairs	= null;	
	
	var $multi_preserve_field = null;
	var $multi_preserve_having = false;

	//  Date and Time
	/**
	 * Formato della stringa applicato a $base_type; questo formato è applicato a livello di visualizzazione;
	 * a livello di ricerca o ordinamento verrà utilizzato il dato non formattato
	 * @var String
	 */
	var $format_string	= null;

	//----------------------
	//  Widget Settings

	/**
	 * Questa proprietà di ffField permette di aggiungere una widget.
	 * Le widget disponibili si trovano nella cartella /ffField/widgets
	 * Per creare una nuova widgets leggere la sezione "widgets" sul manuale del framework
	 * Se $widget è settato, verrà utilizzato la pagina principale dell'interfaccia delle widgets per visualizzare i controlli anziché il process di ffPage
	 * @var String
	 */
	var $widget			= "";

	/**
	 * set di opzioni per l'istanza della widget sul campo specifico
	 * @var Array
	 */
	var $widget_options = array();

	/**
	 * eventuali widget di dipendeza da caricare per la corretta visualizzazione del campo
	 * @var Array
	 */
	var $widget_deps = array();

	//----------------------
	//  Template & Visualization Stuffs

	/**
	 * URL relativo al web del sito
	 * @var String 
	 */
	var $site_path 		= "";

	/**
	 * URL relativo al disco del sito
	 * @var String
	 */
	var $disk_path 		= "";

	/**
	 * Cartella dove è contenuta la pagina partendo dalla root del sito
	 * @var String
	 */
	var $page_path 		= "";

	/**
	 * Cartella del template; di default è la cartella "theme"
	 * @var String
	 */
	var $template_dir	= null;

	/**
	 * File del template
	 * @var String
	 */
	var $template_file	= "";		

	/**
	 * utilizzato dai componenti, determina se il campo non dev'essere visualizzato nel normale
	 * flusso dei contenuti ma in una locazione specifica
	 * @var Boolean
	 */
	var $use_own_location = false;
	var $location_name = null;
	var $location_context = null;

	/**
	 * determina se l'id del campo generato dev'essere comprensivo o meno dell'id del componente associato
	 * @var Boolean
	 */
	var $omit_parent_id = false;

	/**
	 * Classe assegnata al Controllo
	 * @var String
	 */
	var $class			= "";

	/**
	 * Classe assegnata al container del Controllo
	 * @var String
	 */
	var $container_class			= "";

	/**
	 * Utilizzato dai componenti, determina quante colonne deve occupare il campo in una visualizzazione tabellare
	 * @var Number
	 */
	var $span			= 1;

	/**
	 * Un array di proprietà aggiuntive da settare sul campo. Nell'HTML corrisponde agli attributi
	 * è presente una chiave speciale "style" che consente di impostare sotto-set di proprietà. Esempio:
	 * ->properties["alt"] = "test";
	 * ->properties["style"]["height"] = "100px";
	 * @var Array
	 */
	var $properties					= array();

	/**
	 * come sopra, ma utilizzato dai componenti per il contenitore del field (solitamente la colonna)
	 * @var Array
	 */
	var $container_properties		= array();

	/**
	 * Come sopra, ma per l'oggetto label
	 * @var array() 
	 */
	var $label_properties 			= array();

	/**
	 * Il tema da utilizzare per il field specifico. Solitamente ereditato dall'oggetto padre
	 * @var String
	 */
	var $theme			= null;

	//----------------------------
	//  Check settings

	/**
	 * utilizzato dai componenti, determina se devono essere effettuati i controlli di validità sul field
	 * @var Boolean
	 */
	var $need_check		= true;

	/**
	 * Determina se il campo è obbligatorio o no
	 * @var Boolean
	 */
	var $required		= false;	

	/**
	 * Massimo valore che può assumere un ffField (istanza di ffData)
	 * @var int
	 */
	var $max_val		= null;

	/**
	 * Minimo valore che può assumere un Field (istanza di ffData)
	 * @var int
	 */
	var $min_val		= null;		// as above, but for minimum

	var $min_year		= "";
	var $max_year		= "";

	/**
	 * Regular Expression per un check ulteriore
	 * @var String
	 */
	var $regexp			= "";

	/**
	 * ID di ffField con il quale eseguire il check;
	 * se settato, determina se un ffField è utilizzato per
	 * controllare il valore di un altro ffField
	 * anziché memorizzare / gestire i normali valori
	 * @var String
	 */
	var $compare		= null;
	
	/**
	 * Permette di aggiungere un validator a ffField;
	 * i validator disponibili sono: cf, email, number, piva e url
	 * @var String
	 * @deprecated 
	 */
	var $validators		= array();

	/**
	 * utilizzato dai componenti, determina se in presenza di un errore il valore originale del field dev'essere preservato
	 * @var Boolean
	 */
	var $error_preserve	= true;		// preserve original value on error

	//----------------------
	//  Superclasses settings

	/* NB: all of those values are directly managed by superclasses. This mean that do not involves in processing of a single
		ffField object */

	/**
	 * Valore di default che deve assumere il Field
	 * @var ffData
	 */
	var $default_value		= null;

	/**
	 * Permette d'impostare una funzione per il recupero del valore di default
	 * il prototipo è function(pComponent, sFieldName)
	 * La funzione deve restituire un ffData
	 * @var function
	 */
	var $default_callback	= null;

	/**
	 * Nei control_type "radio", determina che valore di default dev'essere selezionato
	 * @var ffData
	 */
	var $default_selected	= null;

	/**
	 * utilizzato dai componenti, determina se dev'essere verificata la correttezza formale del field
	 * laddove possibile
	 * @var Boolean
	 */
	var $enable_check_format = true;

	//----------------------
	//  ffField superclass settings

	/**
	 * usato dai componenti, la tabella cui fa riferimento il field
	 * molto utile soprattutto nelle ricerche di ffGrid
	 * @var String
	 */
	var $src_table		= "";

	/**
	 * Usato da ffGrid, un elenco di campi addizionali in cui effettuare ricerca
	 * utilizzabile solo sui campi aggiunti con addSearchField()
	 * @var Array
	 */
	var $src_fields		= null;	
	
	var $skip_search = false;

	/**
	 * usato da ffGrid, se il campo dev'essere cercato in HAVING piuttosto che in WHERE
	 * @var Boolean
	 */
	var $src_having		= false;


	/**
	 * Tipo di operazione per la ricerca sul DB.
	 * Di default è settata a [NAME] = [VALUE]
	 * Il framework automaticamente sostituisce il tag [NAME] con il nome del campo del DB e [VALUE] con il valore appropriato.
	 * Se invece viene specificata una funzione, si deve utilizzare il tag [VALUE] per specificare il valore.
	 * Per esempio:
	 * [NAME] LIKE([VALUE])
	 *
	 * Non sono necessari apici (singoli / doppi) in quanto il framework li aggiunge automaticamente
	 *
	 * E' possibile utilizzare anche il tag [NAME]
	 * [NAME] IS null.
	 *
	 * Quanto si utilizzano invece intervalli di ricerca, bisogna specificare il campo usando solo il tag [NAME].
	 * DATE([NAME])
	 * @var String
	 */
	var $src_operation 	= "[NAME] = [VALUE]";		

	/**
	 * Utilizzato per aggiugnere una string / carattere all'inizio del valore del campo.
	 * Utile quando si utilizzano funzioni come "LIKE"
	 * @var String
	 */
	var $src_prefix 	= "";

	/**
	 * Come $src_prefix, ma il carattere / stringa viene aggiunto alla fine
	 * @var String 
	 */
	var $src_postfix 	= "";

	/**
	 * Se impostato a true, determina se il campo è utilizzato per l'ordinamento
	 * @var String
	 */
	var $allow_order	= false;					

	/**
	 *  Variabile per determinare l'ordinamento dei record
	 * I possibili valori sono ASC e DESC
	 * @var String 
	 */
	var $order_dir		= "ASC";					

	/**
	 * Usato da ffGrid, il campo da utilizzare per effettuare l'ordinamento
	 * qualora dovesse essere diverso dal campo di default
	 * @var String
	 */
	var $order_field	= null;

	/**
	 * Fa in modo che il controllo venga suddiviso in due campi "Da" "A".
	 * Utile quando si vogliono fare ricerche in un intervallo di tempo o in altri tipi di intervalli.
	 * I due controlli generati condividono il type e le caratteristiche del controllo principale.
	 * @var boolean
	 */
	var $src_interval	= false;		

	/**
	 * Label del campo "DA" (da utilizzare se $src_interval = true)
	 * @var String
	 */
	var $interval_from_label 	= "";	
	
	/**
	 * Label del campo "A" (da utilizzare se $src_interval = true)
	 * @var String 
	 */
	var $interval_to_label 		= "";

	/**
	 * Rappresenta il valore del campo "DA" dell'intervallo
	 * @var ffData
	 */
	var $interval_from_value 	= null;	

	/**
	 * Rappresenta il valore del campo "A" dell'intervallo.
	 * @var ffData
	 */
	var $interval_to_value 		= null;	

	//----------------------
	//  ffRecord & ffDetails superclass settings

	/**
	 * Abilita il salvataggio del Field nel DB; di default è settato a true.
	 * @var Boolean
	 */
	var $store_in_db			= true;

	/**
	 * usato da ffRecord, ignora il campo nelle istruzioni SQL quando vuoto
	 * @var Boolean
	 */
	var $skip_if_empty			= false;

	/**
	 * usato dai componenti, il nome del gruppo cui appartiene il campo
	 * (utile solo a fini di visualizzazione)
	 * @var String
	 */
	var $group					= "";

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may explode! :-)
	// ---------------------------------------------------------------

	/**
	 * La classe che contiene il Field
	 * @var array() 
	 */
	var $parent			= null;						

	/**
	 * Pagina contenente il Field
	 * @var array()
	 */
	var $parent_page	= null;

	/**
	 * Se contiene un errore
	 * @var Boolean
	 */
	var $contain_error	= false;					// hold if field contain an error

	/**
	 * Se dev'essere preservato il valore originale non formattato su input dell'utente
	 * @var Boolean
	 */
	var $preserve_ori_value		= false;

	var $tpl			= null;
	var $db				= null;

	var $cont_array		= null;						// the containing array
	var $row			= null;						// used by multiple rows component

	var $recordset		= null;						// data for multi-element fields (Selection)
	var $value_ori		= null;						// value before editing
	var $multi_values	= null;
	var $multi_values_ori = null;

	var $pre_processed	= false;					// store if pre_processed
	var $widget_init	= false;

	var $is_global		= false;					// true if the field is a key and is global for a FormPage

	var $recordset_grouping_values = null;

	public $order_SQL 	= "";

	var $resources = array();
	var $resources_set = array();
	var $resources_get = array();

	abstract public function tplLoad($control_type);
	abstract public function tplParse($output_result);
	abstract public function getTemplateFile($control_type);

	// ---------------------------------------------------------------
	//  PUBLIC FUNCS
	// ---------------------------------------------------------------

	//  CONSTRUCTOR
	function __construct($disk_path, $site_path, $page_path, $theme)
	{
		$this->get_defaults("ffField");
		$this->get_defaults();

		$this->disk_path = $disk_path;
		$this->site_path = $site_path;
		$this->page_path = $page_path;
		$this->theme = $theme;

		if ($this->value === null)
			$this->value = new ffData();
		if ($this->value_ori === null)
			$this->value_ori = new ffData();
		if ($this->interval_from_value === null)
			$this->interval_from_value = new ffData();
		if ($this->interval_to_value === null)
			$this->interval_to_value = new ffData();
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
	
	function getKeysArray()
	{
		if ($this->parent !== null)
			return $this->parent[0]->key_fields;
		else
			return null;
	}

	function getDataArray()
	{
		if ($this->parent === null)
			return null;
		
		if (is_subclass_of($this->parent[0], "ffGrid_base"))
			return $this->parent[0]->grid_fields;
		elseif (is_subclass_of($this->parent[0], "ffRecord_base"))
			return $this->parent[0]->form_fields;
		elseif (is_subclass_of($this->parent[0], "ffDetails_base"))
			return array_merge($this->parent[0]->form_fields, $this->parent[0]->hidden_fields);
		else
			return null;
	}
	
	function getParentPage()
	{
		if ($this->parent_page !== null)
			return $this->parent_page[0];
		else if ($this->parent !== null)
		{
			$this->parent_page = array($this->parent[0]->parent[0]);
			return $this->parent_page[0];
		}
		else
			ffErrorHandler::raise ("Field not added to a ffPage instance", E_USER_ERROR, $this, get_defined_vars());
	}

	function pre_process($reset = false, $value = null)
	{
		if ($this->pre_processed && !$reset)
			return;
		
		// sostituire con funzione
		if ($this->parent !== null && $this->parent_page === null)
			$this->parent_page = array(&$this->parent[0]->parent[0]);

		if ($value === null)
			$value = $this->value;

		if($reset)
			$this->widget_init = false;
		
		$this->widget_init();

		switch ($this->extended_type)
		{
			case "Selection":
				if ($this->recordset !== null && !$reset)
					return;
				
				if ($this->multi_pairs !== null)
					$this->recordset = $this->multi_pairs;
				else if (strlen($tmp_SQL = $this->getSQL()))
				{
					if ($this->multi_base_type === null)
						die("I don't know how to handle multi displayed values");

					$this->recordset = array();

					if ($this->db === null)
						$this->db[0] = ffDB_Sql::factory();

					$tmp_SQL = ffProcessTags($tmp_SQL, $this->getKeysArray(), $this->getDataArray(), "sql");
					
					if ($this->multi_preserve_field !== null && $value !== null)
					{
						$tmp = " ";
						if (strpos($this->multi_preserve_field, ".") === false && strpos($this->multi_preserve_field, "`") === false)
							$tmp .= "`" . $this->multi_preserve_field . "`";
						else
							$tmp .= $this->multi_preserve_field;
						$tmp .= " = " . $this->db[0]->toSql($value) . " ";
						
						if ($this->multi_preserve_having)
						{
							if (strpos($tmp_SQL, "[HAVING]") === false && strpos($tmp_SQL, "[HAVING_OR]") === false)
								ffErrorHandler::raise ("multi_preserve_field with multi_preserve_having require [HAVING] and [HAVING_OR] tag into SQL", E_USER_ERROR, $this, get_defined_vars ());
							else
								$tmp_SQL = str_replace("[HAVING_OR]", "OR", $tmp_SQL);
							$tmp_SQL = str_replace("[HAVING]", $tmp, $tmp_SQL);
						}
						else
						{
							if (strpos($tmp_SQL, "[WHERE]") === false && strpos($tmp_SQL, "[OR]") === false)
								ffErrorHandler::raise ("multi_preserve_field require [WHERE] and [OR] tag into SQL", E_USER_ERROR, $this, get_defined_vars ());
							else
								$tmp_SQL = str_replace("[OR]", "OR", $tmp_SQL);
							$tmp_SQL = str_replace("[WHERE]", $tmp, $tmp_SQL);
						}
					}

					// avoid bad or unused tags into sql
					$tmp_SQL = str_replace("[AND]", "", $tmp_SQL);
					$tmp_SQL = str_replace("[OR]", "", $tmp_SQL);
					$tmp_SQL = str_replace("[WHERE]", "", $tmp_SQL);
					
					$tmp_SQL = str_replace("[HAVING_AND]", "", $tmp_SQL);
					$tmp_SQL = str_replace("[HAVING_OR]", "", $tmp_SQL);
					$tmp_SQL = str_replace("[HAVING]", "", $tmp_SQL);
					
					if(preg_match("/(\[COLON\])/", $tmp_SQL))
						$tmp_SQL = str_replace("[ORDER]", " ORDER BY ", $tmp_SQL); 
					else
						$tmp_SQL = str_replace("[ORDER]", "", $tmp_SQL);
						 
					$tmp_SQL = str_replace("[COLON]", "", $tmp_SQL);
					$tmp_SQL = str_replace("[LIMIT]", "", $tmp_SQL);

					$this->db[0]->query($tmp_SQL);

					if ($this->db[0]->nextRecord())
					{
						do
						{
							$pair = null;
							if ($this->multi_filter_func !== null)
								$pair = call_user_func_array($this->multi_filter_func, array($this->db[0]));

							if ($pair === null)
							{
								$dbvalue = $this->db[0]->getField($this->db[0]->fields_names[0], $this->base_type);
								$dblabel = $this->db[0]->getField($this->db[0]->fields_names[1], $this->multi_base_type);
								//$dbvalue = $this->db[0]->getResult(null, 0, $this->base_type);
								//$dblabel = $this->db[0]->getResult(null, 1, $this->multi_base_type);
							}
							else
							{
								$dbvalue = $pair[0];
								$dblabel = $pair[1];
							}

							if ($this->multi_crypt)
							{
								if (MOD_SEC_CRYPT && $this->multi_crypt_modsec)
								{
									if ($this->multi_crypt_concat)
										$dblabel->setValue(mod_sec_decrypt_concat($dblabel->getValue()));
									else
										$dblabel->setValue(mod_sec_decrypt_string($dblabel->getValue()));
								}
							}
							$this->recordset[] = array($dbvalue, $dblabel);
						} while ($this->db[0]->nextRecord());
					}
				}
				else
					ffErrorHandler::raise("Selection without anything to select", E_USER_ERROR, $this, get_defined_vars());
				break;
			default:
				if ($reset)
					$this->recordset = null;
		}
		$this->pre_processed = true;
	}

	function widget_init()
	{
		if ($this->widget_init || !strlen($this->widget))
			return;
		
		$pp = $this->getParentPage();
		
		// invoke method from ffPage
		$pp->widgetLoad($this->widget, $pp->getThemeDir() . "/ff/ffField/widgets", $this);
		if (method_exists($pp->widgets[$this->widget], "init"))
			$pp->widgets[$this->widget]->init(array(&$this));

		$this->widget_init = true;
	}

	function widget_process($id = null, $value = null)
	{
		$this->widget_init();

		if ($id === null)
			$id = $this->id;

		if ($value === null)
			$value = $this->value;

		return $this->parent_page[0]->widgets[$this->widget]->process($id, $value, $this);
	}

	function process($id = null, $value = null, $output_result = false, $control_type = null)
	{
		$this->pre_process(false, $value);

		if ($id === null)
			$id = $this->id;

		if ($value === null)
			$value = $this->value;

		if ($control_type === null)
			$control_type = $this->get_control_type();
		
		$this->tplLoad($control_type);

		if (strlen($this->widget))
		{
			if (($buffer = $this->widget_process($id, $value)) !== null)
			{
				if ($output_result)
				{
					echo $buffer;
					return true;
				}
				else
					return $buffer;
			}
		}

		switch(strtolower($control_type))
		{
			case "radio":
				$this->process_radio($id, $value);
				break;

			case "combo":
				$this->process_combo($id, $value);
				break;

			default:
			case "email":
			case "label":
				$this->process_label($id, $value);
				break;
			case "textarea":
				$this->process_textarea($id, $value);
				break;
			case "html":
			case "date":
			case "time":
			case "input":
			case "password":
				$this->process_input($id, $value);
				break;

			case "checkbox":
				$this->process_checkbox($id, $value);
				break;

			case "picture":
			case "picture_no_link":
				//$this->process_picture($id, $value);
				//break;
			case "file_label":
			case "file":
				$this->process_file($id, $value);
				break;
		}

		return $this->tplParse($output_result);
	}

	function getTemplateDir($control_type)
	{
		$res = $this->doEvent("getTemplateDir", array($this, $control_type));
		$last_res = end($res);
		if ($last_res === null)
		{
			if ($this->template_dir === null)
				return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffField";
			else
				return $this->template_dir;
		}
		else
		{
			return $last_res;
		}
	}

	function getTheme()
	{
		return $this->theme;
	}
	
	function getFileBasePath()
	{
		if ($this->file_base_path === null)
			return FF_DISK_UPDIR;
		else
			return $this->file_base_path;
	}

	function getFilePath($temporary = true, $clean_path = true)
	{
		$storing_path = ffProcessTags($this->file_storing_path, $this->getKeysArray(), $this->getDataArray(), "normal"); 
		$temp_path = ffProcessTags($this->file_temp_path, $this->getKeysArray(), $this->getDataArray(), "normal"); 
		if (count($this->parent) && is_subclass_of($this->parent[0], "ffDetails_base"))
		{
			foreach ($this->parent[0]->fields_relationship as $el_key => $el_value)
			{
				$storing_path = str_replace("[" . $el_value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$el_value]->base_type, FF_SYSTEM_LOCALE), $storing_path);
				$temp_path = str_replace("[" . $el_value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$el_value]->base_type, FF_SYSTEM_LOCALE), $temp_path);
			}
			reset ($this->parent[0]->fields_relationship);

			foreach ($this->parent[0]->main_record[0]->form_fields as $el_key => $el_value)
			{
				if ($this->parent[0]->main_record[0]->form_fields[$el_key]->multi_fields === null)
				{
					$storing_path = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $storing_path);
					$temp_path = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $temp_path);
				}
			}
			reset ($this->parent[0]->main_record[0]->form_fields);
		}

		if ($clean_path)
		{
			if (strpos($storing_path, "[_FILENAME_]") !== false)
				$storing_path = substr($storing_path, 0, strrpos($storing_path, "/"));

			if (strpos($temp_path, "[_FILENAME_]") !== false)
				$temp_path = substr($temp_path, 0, strrpos($temp_path, "/"));
		}

		if ($temporary && strlen($temp_path))
			$res = $temp_path;
		else
			$res = $storing_path;
		
        // by Alex
		if($clean_path && $this->file_normalize)
			return $this->fileNormalize($res);
		else
			return $res;
	}
	
	function getFileFullPath($file_name, $temporary = true, $file_path = null)
	{
		if ($file_path === null)
			$file_path = $this->getFilePath($temporary, false);
		
		if (strpos($file_path, "[_FILENAME_]") !== false)
			$file_path = str_replace("[_FILENAME_]", $file_name, $file_path);
		else
			$file_path = rtrim($file_path, "/") . "/" . $file_name;
		
        // by Alex
		if($this->file_normalize)
			return $this->fileNormalize($file_path);
		else
			return $file_path;
	}
	
    // by Alex
	function fileNormalize($path)
	{
		$arrRes = pathinfo(str_replace($this->getFileBasePath(), "", $path));

		if(is_array($arrRes["dirname"]) && count($arrRes["dirname"])) {
			$path = "";
			foreach($arrRes["dirname"] AS $arrRes_key => $arrRes_value) {
				if (strpos($arrRes_value, "[") === 0)
					$path .= "/" . $arrRes_value;
				else if(strlen($arrRes_value))
					$path .= "/" . ffCommon_url_rewrite($arrRes_value);
			}
			$path = $this->getFileBasePath() . $path . ffCommon_url_rewrite($arrRes["basename"]) . (isset($arrRes["extension"]) ? "." . $arrRes["extension"] : "");
		}
		
		return $path;
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
						$subvalue = ffProcessTags($subvalue, $this->getKeysArray(), $this->getDataArray());
						$buffer .= $subkey . ": " . $subvalue . ";";
					}
					reset($property_set[$key]);
					$buffer .= "\"";
				}
				else
				{
					$value = ffProcessTags($value, $this->getKeysArray(), $this->getDataArray());
					
					if (strlen($buffer))
						$buffer .= " ";
					$buffer .= $key . (strlen($value) ? "=\"" . $value . "\"" : "");
				}
			}
			reset($property_set);
		}
		return $buffer;
	}

	function get_control_type()
	{
		if (strlen($this->control_type))
			return $this->control_type;
		
		switch($this->extended_type)
		{
			case "Boolean":
				return "checkbox";

			case "Date":
			case "DateTime":
			case "Time":
			case "String":
			case "EMail":
				return "input";

			case "Password":
				return "password";

			case "HTML":
			case "Text":
				return "textarea";

			case "Selection":
				return "combo";

			case "File":
				if ($this->base_type == "Text")
					return "file";
				else if ($this->base_type == "Binary")
					return "embedfile";
				else
					die ("Error selecting File base_type");
		}
	}

	function getValue($data_type = null, $locale = null)
	{
		if ($data_type === null)
			$data_type = $this->get_app_type();

		if ($locale === null)
			$locale = $this->get_locale();

		return $this->get_encoded($this->value->getValue($data_type, $locale));
	}

	function getDisplayValue($data_type = null, $locale = null, $value = null, $display_value = null)
	{
		if ($locale === null)
			$locale = $this->get_locale();

		if ($value === null)
			$value = $this->value;
		
		if ($display_value === null)
		{
			$display_value = $this->display_value;
			if ($this->display_value === null)
				$display_value = $value;
		}

		$this->pre_process(false, $value);

		$tmp_value = null;

		if (is_array($this->recordset) && $this->extended_type == "Selection")
		{
			if ($data_type === null)
				$data_type = $this->get_multi_app_type();

			$t = $this->base_type;
			$a = $value->getValue(null, FF_SYSTEM_LOCALE);
			//$b = $this->multi_select_one_val->getValue(null, FF_SYSTEM_LOCALE);

			if ($this->multi_select_one &&
					(
							($this->multi_select_one_val === null && ($value->ori_value === null || $value->ori_value === "" || ($this->base_type == "Number" && ($value->ori_value === "0" || $value->ori_value === 0))))
						 || ($this->multi_select_one_val !== null && $value->getValue(null, FF_SYSTEM_LOCALE) === $this->multi_select_one_val->getValue(null, FF_SYSTEM_LOCALE))
					)
			   )
				$tmp_value = $this->multi_select_one_label;
			else if ($this->multi_select_noone &&
					(
						($this->multi_select_noone_val !== null && $value->getValue(null, FF_SYSTEM_LOCALE) === $this->multi_select_noone_val->getValue(null, FF_SYSTEM_LOCALE))
					)
				)
				$tmp_value = $this->multi_select_one_label;
			else
			{
				foreach ($this->recordset as $key => $item)
				{
					list($tmp, $item_key) = each($item);
					list($tmp, $item_value) = each($item);

					if ($item_key->getValue($this->get_app_type(), FF_SYSTEM_LOCALE) === $value->getValue($this->get_app_type(), FF_SYSTEM_LOCALE))
					{
						$tmp_value = $this->get_encoded($item_value->getValue($data_type, $locale));
						break;
					}
				}
				reset($this->recordset);
			}

			if ($tmp_value === null)
			{
				if ($this->multi_select_one)
					$tmp_value = $this->multi_select_one_label;
				else if (count($this->recordset))
				{
					$tmp_key = array_keys($this->recordset);
					if ($this->widget == "activecomboex" && $this->actex_update_from_db === false)
						$tmp_value = $this->get_encoded($this->recordset[$tmp_key[0]][2]->getValue($data_type, $locale));
					else
						$tmp_value = $this->get_encoded($this->recordset[$tmp_key[0]][1]->getValue($data_type, $locale));
				}
			}
			return $tmp_value;
		}
		else
		{
			if ($data_type === null)
				$data_type = $this->get_app_type();

			switch ($this->extended_type)
			{
				case "File";
					$res = $this->doEvent("on_display_value_get_file", array($this, $value));
					$rc = end($res);
					if ($rc !== null)
						return $rc;

					if (strlen($this->file_tmpname))
					{
						$filename = str_replace($this->getFileBasePath(), "", $this->file_temp_path) . "/" . $this->file_tmpname;

						$view_url				= ($this->file_temp_view_url ? $this->file_temp_view_url : $this->file_saved_view_url);
						$view_query_string		= ($this->file_temp_view_query_string ? $this->file_temp_view_query_string : 
								($this->file_saved_view_query_string ? $this->file_saved_view_query_string : $this->file_query_string)
							);

						$preview_url			= ($this->file_temp_preview_url ? $this->file_temp_preview_url : $this->file_saved_preview_url);
						$preview_query_string	= ($this->file_temp_preview_query_string ? $this->file_temp_preview_query_string : 
								($this->file_saved_preview_query_string ? $this->file_saved_preview_query_string : $this->file_query_string)
							);

/*						$view_url				= $this->file_temp_view_url;
						$view_query_string		= $this->file_temp_view_query_string;

						$preview_url			= $this->file_temp_preview_url;
						$preview_query_string	= $this->file_temp_preview_query_string;
						*/
					}
					else
					{
						$filename = $value->getValue();

						$view_url				= $this->file_saved_view_url;
						$view_query_string		= ($this->file_saved_view_query_string ? $this->file_saved_view_query_string : $this->file_query_string);

						$preview_url			= $this->file_saved_preview_url;
						$preview_query_string	= ($this->file_saved_preview_query_string ? $this->file_saved_preview_query_string : $this->file_query_string);
					}

					if ($filename == "")
						return;

					$view_url = ffProcessTags($view_url, $this->getKeysArray(), $this->getDataArray(), "normal");
					$view_url = str_replace("[_FILENAME_]", $filename, $view_url);
					$view_query_string = ffProcessTags($view_query_string, $this->getKeysArray(), $this->getDataArray(), "normal");

					$preview_url = ffProcessTags($preview_url, $this->getKeysArray(), $this->getDataArray(), "normal");
					$preview_url = str_replace("[_FILENAME_]", $filename, $preview_url);
					$preview_query_string = ffProcessTags($preview_query_string, $this->getKeysArray(), $this->getDataArray(), "normal");
					
					if (strlen($view_query_string) && strpos($view_query_string, "?") !== 0)
						$view_query_string = "?" . $view_query_string;
					if (strlen($preview_query_string) && strpos($preview_query_string, "?") !== 0)
						$preview_query_string = "?" . $preview_query_string;
					
					if (count($this->parent) && is_subclass_of($this->parent[0], "ffDetails_base"))
					{
						foreach ($this->parent[0]->fields_relationship as $key => $value)
						{
							$view_url = str_replace("[" . $value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$value]->base_type, FF_SYSTEM_LOCALE), $view_url);
							$preview_url = str_replace("[" . $value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$value]->base_type, FF_SYSTEM_LOCALE), $preview_url);
						}
						reset ($this->parent[0]->fields_relationship);
						foreach ($this->parent[0]->main_record[0]->form_fields as $el_key => $el_value)
						{
							if ($this->parent[0]->main_record[0]->form_fields[$el_key]->multi_fields === null)
							{
								$view_url = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $view_url);
								$preview_url = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $preview_url);
							}
						}
						reset ($this->parent[0]->main_record[0]->form_fields);
					}

					return $preview_url . $preview_query_string;

				default:
					return  $this->get_encoded($display_value->getValue($data_type, $locale));
			}
		}
	}

    function get_literal_size($lngSize) 
    {
        if ($lngSize >= 1000000000000)
            $strFormatSize = number_format(($lngSize / 1000000000000), 2, ".",",") . "TB";
        elseif ($lngSize >= 1000000000)
            $strFormatSize = number_format(($lngSize / 1000000000), 2, ".",",") . "GB";
        elseif ($lngSize >= 1000000)
            $strFormatSize = number_format(($lngSize / 1000000), 2, ".",",") . "MB";
        elseif ($lngSize >= 1000)
            $strFormatSize = number_format(($lngSize / 1000), 2, ".",",") . "KB";
        else
            $strFormatSize = number_format(($lngSize), 2, ".",",") . "B";
        
        return $strFormatSize;
    }
    
	function getDefault($component)
	{
		$ret_val = null;
		if ($this->default_callback !== null)
			$ret_val = call_user_func($this->default_callback, $component, $this->id);
		else if ($this->default_value !== null)
		{
			if (!is_object($this->default_value) || get_class($this->default_value) != "ffData")
				ffErrorHandler::raise("default_value must be a ffData instance", E_USER_ERROR, $this, get_defined_vars());
			$ret_val = $this->default_value;
		}
		else
		{
			switch ($this->extended_type)
			{
				case "Boolean":
					$ret_val = $this->unchecked_value;
					break;

				case "Selection":
					$this->pre_process();

					if ($this->multi_select_one)
						$ret_val = $this->multi_select_one_val;
					else if ($this->multi_select_noone)
						$ret_val = $this->multi_select_noone_val;
					else if (is_array($this->recordset) && count($this->recordset))
					{
						$tmp_key = array_keys($this->recordset);
						if ($this->widget == "activecomboex" && $this->actex_update_from_db === false)
							$ret_val = $this->recordset[$tmp_key[0]][1];
						else
							$ret_val = $this->recordset[$tmp_key[0]][0];
					}
					break;
			}
		}

		if ($ret_val === null)
			return new ffData();
		else
			return $ret_val;
	}

	/**
	 * Imposta il valore $value, di tipo $data_type nel formato $locale
	 * @param ffData $value Valore del Field
	 * @param String $data_type Tipo di dato per il Field
	 * @param String $locale Locale impostato nell'applicazione
	 */
	function setValue($value, $data_type = null, $locale = null)
	{
		if ($data_type === null)
			$data_type = $this->get_app_type();

		if ($locale === null)
			$locale = $this->get_locale();

		$this->value->setValue($value, $data_type, $locale);
	}

	function get_control_name()
	{
		return $this->id;
	}

	/**
	 * Restituisce $data_source del field
	 * @return String
	 */
	function get_data_source($enable_data_source = true)
	{
		if($this->data_type == "callback" && !$enable_data_source) {
			return $this->id;
		} else {
			if ($this->data_source == "")
				return $this->id;
			else
				return $this->data_source;
		}
	}

	/**
	 * Restituisce il formato Locale impostato
	 * @return String
	 */
	function get_locale()
	{
		if ($this->locale === null)
			return FF_LOCALE;
		else
			return $this->locale;
	}

	/**
	 * Restituisce $app_type di quel Field
	 * @return String
	 */
	function get_app_type()
	{
		if ($this->app_type === null)
			return $this->base_type;
		else
			return $this->app_type;
	}

	function get_multi_app_type()
	{
		if ($this->multi_app_type === null)
			return $this->multi_base_type;
		else
			return $this->multi_app_type;
	}

	
	function get_encoded($value)
	{
		if ($this->encode_entities)
			return nl2br(ffCommon_specialchars($value));
		else
			return $value;
	}

	function process_label($id, &$value)
	{
		$this->tpl[0]->set_var("id", $id);
		if($this->encode_entities)
			$this->tpl[0]->set_var("value", ffCommon_specialchars($value->getValue($this->get_app_type(), $this->get_locale())));
		else
			$this->tpl[0]->set_var("value", $value->getValue($this->get_app_type(), $this->get_locale()));

		$this->tpl[0]->set_var("label", $this->getDisplayValue(null, null, $value));

		if ($this->parent !== null && is_subclass_of($this->parent[0], "ffGrid_base") && !$this->parent[0]->force_no_field_params)
			$this->tpl[0]->parse("SectFieldValue", false);
		else
			$this->tpl[0]->set_var("SectFieldValue", "");
	}

	function process_input($id, &$value)
	{
		$this->tpl[0]->set_var("id", $id);

		if (is_array($this->recordset) && count($this->recordset))
		{
			foreach ($this->recordset as $key => $item)
			{
				list($tmp, $item_key) = each($item);
				list($tmp, $item_value) = each($item);

				if ($item_key->getValue($this->get_app_type(), $this->get_locale()) == $value->getValue($this->get_app_type(), $this->get_locale()))
					$this->tpl[0]->set_var("value", ffCommon_specialchars($item_value->getValue($this->get_multi_app_type(), $this->get_locale())));
			}
			reset($this->recordset);
		}
		else
		{
			if (($this->contain_error && $this->error_preserve) || $this->preserve_ori_value)
				$this->tpl[0]->set_var("value", ffCommon_specialchars($value->ori_value));
			else
				$this->tpl[0]->set_var("value", ffCommon_specialchars($value->getValue($this->get_app_type(), $this->get_locale())));
		}
	}

	function process_textarea($id, &$value)
	{
		$this->process_input($id, $value);

		if(!$this->properties || !$this->properties["readonly"])
			$this->tpl[0]->parse("SectAutoGrow", false);
	}
	
	/**
	 * Esegue il process del Field nel caso si tratti di un checkbox
	 * @param String $id ID del Field
	 * @param ffData $value Valore del Field
	 */
	function process_checkbox($id, &$value)
	{
		if (!is_object($this->checked_value) || get_class($this->checked_value) != "ffData"
			|| !is_object($this->unchecked_value) || get_class($this->unchecked_value) != "ffData")
			ffErrorHandler::raise("checked_value and unchecked_value must be defined for checkboxes.", E_USER_ERROR, $this, get_defined_vars());

		$this->tpl[0]->set_var("id", ffCommon_specialchars($id));
		$this->tpl[0]->set_var("value", ffCommon_specialchars($this->checked_value->getValue($this->get_app_type(), $this->get_locale())));

		if ($value->getValue($this->get_app_type(), $this->get_locale()) == $this->checked_value->getValue($this->get_app_type(), $this->get_locale()))
			$this->tpl[0]->set_var("Checked", "checked=\"checked\"");
		else
			$this->tpl[0]->set_var("Checked", "");
	}

	/**
	 * Esegue il process del Field nel caso si tratti di un combo
	 * @param String $id ID del Field
	 * @param ffData $value Valore del Field
	 */
	function process_combo($id, &$value)
	{
		$this->tpl[0]->set_var("id", ffCommon_specialchars($id));

		if ($this->multi_select_one && !$this->multi_limit_select)
		{
			if ($this->multi_select_one_val !== null)
				$this->tpl[0]->set_var("select_one_value", $this->multi_select_one_val->getValue($this->get_app_type(), $this->get_locale()));
			else
				$this->tpl[0]->set_var("select_one_value", "");
			$this->tpl[0]->set_var("select_one_label", $this->multi_select_one_label);
			$this->tpl[0]->parse("SectSelectOne", false);
		}
		else
			$this->tpl[0]->set_var("SectSelectOne", "");

		if ($this->multi_select_noone &&
				(!$this->multi_limit_select ||
					($this->multi_limit_select && $this->multi_select_noone_val->getValue($this->get_app_type(), $this->get_locale()) == $value->getValue($this->get_app_type(), $this->get_locale()))
				)
			)
		{
			if ($this->multi_select_noone_val === null)
				ffErrorHandler::raise("u must enter a select noone value", E_USER_ERROR, $this, get_defined_vars());

			$this->tpl[0]->set_var("select_noone_value", $this->multi_select_noone_val->getValue($this->get_app_type(), $this->get_locale()));
			$this->tpl[0]->set_var("select_noone_label", $this->multi_select_noone_label);
			if ($value->ori_value !== "" && $this->multi_select_noone_val->getValue($this->get_app_type(), $this->get_locale()) === $value->getValue($this->get_app_type(), $this->get_locale()))
				$this->tpl[0]->set_var("Selected", "selected=\"selected\"");
			else
				$this->tpl[0]->set_var("Selected", "");
			$this->tpl[0]->parse("SectSelectNoOne", false);
		}
		else
			$this->tpl[0]->set_var("SectSelectNoOne", "");

		if (is_array($this->recordset) && count($this->recordset))
		{
			foreach ($this->recordset as $key => $item)
			{
				list($tmp, $item_key) = each($item);
				list($tmp, $item_value) = each($item);

				$this->tpl[0]->set_var("Value", ffCommon_specialchars($item_key->getValue($this->get_app_type(), $this->get_locale())));
				$this->tpl[0]->set_var("Label", ffCommon_specialchars($item_value->getValue($this->get_multi_app_type(), $this->get_locale())));
//                                if ($this->id == "attivo")
//                                    ffErrorHandler::raise("DEBUG IN CORSO", E_USER_ERROR, $this, get_defined_vars());
				if ($value->ori_value !== "" && $item_key->getValue($this->get_app_type(), $this->get_locale()) === $value->getValue($this->get_app_type(), $this->get_locale()))
				{
					$this->tpl[0]->set_var("Selected", "selected=\"selected\"");
					$this->tpl[0]->parse("SectRow", true);
				}
				else
				{
					$this->tpl[0]->set_var("Selected", "");
					if (!$this->multi_limit_select)
						$this->tpl[0]->parse("SectRow", true);
				}
			}
			reset($this->recordset);
		}
		else
		{
			$this->tpl[0]->set_var("SectRow", "");
		}
	}

	function process_file($id, &$value)
	{
	    static $loaded_dialog_fields = null;

		$this->tpl[0]->set_var("id", ffCommon_specialchars($id));

		$suffix_file 		= "file";
		$suffix_name 		= "name";
		$suffix_tmpname 	= "tmpname";
		$suffix_delete 		= "delete";

		if (substr($id, -1) == "]")
		{
			$suffix_file 		= ""; //"[" . $suffix_file . "]";
			$suffix_name 		= "[" . $suffix_name . "]";
			$suffix_tmpname 	= "[" . $suffix_tmpname . "]";
			$suffix_delete 		= "[" . $suffix_delete . "]";
		}
		else
		{
			$suffix_file 		= "_" . $suffix_file;
			$suffix_name 		= ""; //"_" . $suffix_name;
			$suffix_tmpname 	= "_" . $suffix_tmpname;
			$suffix_delete 		= "_" . $suffix_delete;
		}

		$file_thumb = (is_array($this->file_thumb)
            ? "/" . implode("x", $this->file_thumb)
            : (strlen($this->file_thumb)
                ? "/" . $this->file_thumb
                : ""
            )
        );

		$this->tpl[0]->set_var("theme", $this->parent_page[0]->theme);
		$this->tpl[0]->set_var("suffix_file", $suffix_file);
		$this->tpl[0]->set_var("suffix_name", $suffix_name);
		$this->tpl[0]->set_var("suffix_tmpname", $suffix_tmpname); 
		$this->tpl[0]->set_var("suffix_delete", $suffix_delete);

		$this->tpl[0]->set_var("filename", ffCommon_specialchars($value->getValue($this->get_app_type(), $this->get_locale())));
		$this->tpl[0]->set_var("filename_normalized", preg_replace('/[^a-zA-Z0-9]/', '', basename($value->getValue($this->get_app_type(), $this->get_locale()))));
		$this->tpl[0]->set_var("tmpname", ffCommon_specialchars($this->file_tmpname));
		$this->tpl[0]->set_var("encoded_filename", urlencode($value->getValue($this->get_app_type(), $this->get_locale())));

        if($this->file_show_edit) {
            $file_modify_path = ffMedia::MODIFY_PATH . "?key=" . $this->file_modify_referer . "&path=";

            if($this->file_modify_dialog === true) {
                $this->file_modify_dialog = $this->id . "_media";

                if(!$loaded_dialog_fields[$this->file_modify_dialog]) {
                    $params = array(
                        "title" 				=> ffTemplate::_get_word_by_code("ffField_modify") . " " . $this->label
                        , "class" 				=> null
                        , "width" 				=> null
                        , "height" 				=> null
                        , "type" 				=> null
                    );


                    $this->parent_page[0]->widgetLoad("dialog");
                    $this->parent_page[0]->widgets["dialog"]->process(
                        $this->file_modify_dialog
                        , array(
                            "title"          	=> $params["title"]
                            , "tpl_id"        	=> null
                            , "width"        	=> $params["width"]
                            , "height"        	=> $params["height"]
                            , "dialogClass"     => $params["class"]
                            , "type"			=> $params["type"]
                        )
                        , $this->parent_page[0]
                    );
                }
            }
        }


		if (strlen($this->file_tmpname))
		{
			$filename = $this->file_tmpname;
            if(substr($filename, 0,1) == "/")
                $this->file_full_path = true;

			if ($this->file_full_path)
			{
				$tmp_path = str_replace($this->getFileBasePath(), "", $this->file_temp_path);
				$filename = $tmp_path . "/" . basename($filename);
				if (
					substr(strtolower($filename), 0, 7) == "http://"
					|| substr(strtolower($filename), 0, 8) == "https://"
                    || substr($filename, 0, 2) == "//"
				) {
					$this->file_check_exist = false;
					$this->file_show_filesize = false;
					$is_local = false;
				} else {
					$is_local = true;
				}
			}
			else
				$is_local = true;

            $view_url               = ($this->file_temp_view_url 
            	? $this->file_temp_view_url 
            	: ($is_local 
            		? CM_SHOWFILES
            		: ""
            	) . $filename
            );
            $view_query_string      = ($this->file_temp_view_query_string ? $this->file_temp_view_query_string : 
					($this->file_saved_view_query_string ? $this->file_saved_view_query_string : $this->file_query_string)
				);

            $preview_url = ($this->file_temp_preview_url
                ? $this->file_temp_preview_url
                : ($is_local
                	? str_replace($this->getFileBasePath(), CM_SHOWFILES . $file_thumb, $this->file_temp_path)
                	: $filename
                ) 
            );
            $preview_query_string   = ($this->file_temp_preview_query_string ? $this->file_temp_preview_query_string : 
					($this->file_saved_preview_query_string ? $this->file_saved_preview_query_string : $this->file_query_string)
				); 
			
//SBAGLIATO DA SISTEMARE

			$is_tmpfile = true;
            $mode = "normal"; 
		}
		else
		{
			$storing_path = null;
			$filename = $value->getValue();
			if($this->file_multi) {
				$arrFilename = explode($this->file_separator, $filename);
				if(count($arrFilename))
					$filename = $arrFilename[0];
			}

            if(substr($filename, 0,1) == "/")
                $this->file_full_path = true;


			if ($this->file_full_path)
			{
				if (
					substr(strtolower($filename), 0, 7) == "http://"
					|| substr(strtolower($filename), 0, 8) == "https://"
                    || substr($filename, 0, 2) == "//"
				) {
					$this->file_check_exist = false;
					$this->file_show_filesize = false;
					$is_local = false;
				} else {
					if(strlen($filename))
						$storing_path = $this->getFileBasePath() . ffCommon_dirname($filename);

					$is_local = true;
				}
			}
			else
				$is_local = true;
            
            if(!$storing_path)
            	$storing_path = $this->file_storing_path;

            $view_url = ($this->file_saved_view_url
                ? $this->file_saved_view_url
                : ($is_local
                	? str_replace($this->getFileBasePath(), CM_SHOWFILES, $storing_path)
                	: $storing_path
                ) . "/[_FILENAME_]"
            );
			$view_query_string		= ($this->file_saved_view_query_string ? $this->file_saved_view_query_string : $this->file_query_string);
            
            $preview_url = ($this->file_saved_preview_url 
                ? $this->file_saved_preview_url
                : ($is_local
                	? str_replace($this->getFileBasePath(), CM_SHOWFILES . $file_thumb, $storing_path)
                	: $storing_path
                ) . "/[_FILENAME_]"
            );
			$preview_query_string	= ($this->file_saved_preview_query_string ? $this->file_saved_preview_query_string : $this->file_query_string);
            
            
            $is_tmpfile = false;
            $mode = "ori"; 
		}

        if($is_local)
		{
			$view_url = ffProcessTags($view_url, $this->getKeysArray(), $this->getDataArray(), $mode);
		   // $view_url = str_replace("[_FILENAME_]", ($this->file_full_path ? ltrim($filename, "/") : $filename), $view_url);

		    //$view_url = ffProcessTags($view_url, $this->getKeysArray(), $this->getDataArray(), $mode);
            //$view_url = str_replace("//", "/0/", $view_url); //procedura per fixare il bug nel ffprocesstag che con un valore di tipo numerico ritorna "" al posto di 0
		    $view_query_string = ffProcessTags($view_query_string, $this->getKeysArray(), $this->getDataArray(), $mode);
		    
		    $preview_url = ffProcessTags($preview_url, $this->getKeysArray(), $this->getDataArray(), $mode);
		   // $preview_url = str_replace("[_FILENAME_]", ($this->file_full_path ? ltrim($filename, "/") : $filename), $preview_url);
		    //$preview_url = ffProcessTags($preview_url, $this->getKeysArray(), $this->getDataArray(), $mode);
            //$preview_url = str_replace("//", "/0/", $preview_url);  //procedura per fixare il bug nel ffprocesstag che con un valore di tipo numerico ritorna "" al posto di 0
            $preview_query_string = ffProcessTags($preview_query_string, $this->getKeysArray(), $this->getDataArray(), $mode);
		}
		else 
		{
            $view_url = $filename;
            $preview_url = $filename;
        }

		if (strlen($view_query_string) && strpos($view_query_string, "?") !== 0)
			$view_query_string = "?" . $view_query_string;
		if (strlen($preview_query_string) && strpos($preview_query_string, "?") !== 0)
			$preview_query_string = "?" . $preview_query_string;

	/*	if (strlen($this->file_tmpname))
		{
			$this->file_temp_view_url_tmp = $view_url;
			$this->file_temp_view_query_string_tmp = $view_query_string;

			$this->file_temp_preview_url_tmp = $preview_url;
			$this->file_temp_preview_query_string_tmp = $preview_query_string;
		} 
		else 
		{
			$this->file_saved_view_url_tmp	= $view_url;
			$this->file_saved_view_query_string_tmp = $view_query_string;

			$this->file_saved_preview_url_tmp = $preview_url;
			$this->file_saved_preview_query_string_tmp = $preview_query_string;
		}*/
		if (count($this->parent) && is_subclass_of($this->parent[0], "ffDetails_base"))
		{
			foreach ($this->parent[0]->fields_relationship as $el_key => $el_value)
			{
				$view_url = str_replace("[" . $el_value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$el_value]->base_type, FF_SYSTEM_LOCALE), $view_url);
				$preview_url = str_replace("[" . $el_value . "_FATHER]", $this->parent[0]->main_record[0]->key_fields[$el_value]->value->getValue($this->parent[0]->main_record[0]->key_fields[$el_value]->base_type, FF_SYSTEM_LOCALE), $preview_url);
			}
			reset ($this->parent[0]->fields_relationship);

			foreach ($this->parent[0]->main_record[0]->form_fields as $el_key => $el_value)
			{
				if ($this->parent[0]->main_record[0]->form_fields[$el_key]->multi_fields === null)
				{
					$view_url = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $view_url);
					$preview_url = str_replace("[" . $el_key . "_FATHER]", $this->parent[0]->main_record[0]->form_fields[$el_key]->value->getValue($this->parent[0]->main_record[0]->form_fields[$el_key]->base_type, FF_SYSTEM_LOCALE), $preview_url);
				}
			}
			reset ($this->parent[0]->main_record[0]->form_fields);
		}
		
		if (strlen($value->getValue()))
		{
			$arrFileValue = explode($this->file_separator, $value->getValue());
			if($this->file_show_preview && is_array($arrFileValue) && count($arrFileValue))	{
				$preview_class = "uploaded-preview";

				foreach($arrFileValue AS $file_key => $file_value) {
					if(!$is_tmpfile && $this->file_full_path) {
						$file_full_path = $this->getFileBasePath() . "/" . ltrim($file_value, "/");
						$real_file_value = basename($file_value);
					} else {
						if($this->file_storing_path === null) {
							$file_full_path = $this->getFileBasePath() . "/" . ltrim($file_value, "/");
							$real_file_value = ltrim($file_value, "/");
						} else {
							$file_full_path = $this->getFileFullPath(basename($file_value), $is_tmpfile);
							$real_file_value = basename($file_value);
						}
					}
					$this->tpl[0]->set_var("show_name", ffCommon_specialchars(basename($file_value)));
					$this->tpl[0]->set_var("filevalue", ffCommon_specialchars(basename($file_value)));
					$this->tpl[0]->set_var("filevalue_normalized", preg_replace('/[^a-zA-Z0-9]/', '', basename($file_value)));
					$this->tpl[0]->set_var("filename_full", ffCommon_specialchars($file_value));
					if($this->file_full_path)
						$filename_base = preg_replace('/[^a-zA-Z0-9]/', '', basename($file_value));
					else
						$filename_base = preg_replace('/[^a-zA-Z0-9]/', '', $file_value);

					if($this->widget) {
						$this->tpl[0]->set_var("filename_base", $filename_base);
						$this->tpl[0]->set_var("widget_name", $this->widget);
						$this->tpl[0]->set_var("SectBaseDel", "");
						$this->tpl[0]->parse("SectWidgetDel", false);
					} else {
						$this->tpl[0]->parse("SectBaseDel", false);
						$this->tpl[0]->set_var("SectWidgetDel", "");
					}
						
					$check_file = true;
					
					if($is_local && $this->file_check_exist && !@is_file($file_full_path)) 
						$check_file = false;
					
					if ($this->file_show_delete) {
						$this->tpl[0]->parse("ShowDelete", false);
					} else {
						$this->tpl[0]->set_var("ShowDelete", "");
					}

					if($check_file) {
						$processed_view_url = ffCommon_specialchars(str_replace("[_FILENAME_]", $real_file_value, $view_url));
						if($file_modify_path) {
							$file_path = substr($file_full_path, strlen($this->getFileBasePath()));
							if($this->file_modify_dialog) {
 								$this->tpl[0]->set_var("ajax_view_url", " onclick=\"ff.ffPage.dialog.doOpen('" . $this->file_modify_dialog ."', '" . ffCommon_specialchars(FF_SITE_PATH . $file_modify_path . $file_path) . "');\"");
                                $this->tpl[0]->set_var("view_url", "javascript:void(0);");
							} else {
                                $this->tpl[0]->set_var("ajax_view_url", "");
								$this->tpl[0]->set_var("view_url", ffCommon_specialchars($file_modify_path . $file_path));
							}
						} else {
                            $this->tpl[0]->set_var("ajax_view_url", "");
							$this->tpl[0]->set_var("view_url", $processed_view_url);
						}
						$this->tpl[0]->set_var("preview_url", ffCommon_specialchars(str_replace("[_FILENAME_]", $real_file_value, $preview_url)));

						/*
						if($is_tmpfile)
							$this->tpl[0]->set_var("show_file", ffCommon_dirname(str_replace(ffCommon_dirname($file_value), "", $preview_url)));
						else
							$this->tpl[0]->set_var("show_file", ffCommon_dirname(str_replace(ffCommon_dirname($file_value), "", $preview_url)));
						*/
						$this->tpl[0]->set_var("view_query_string", $view_query_string);
						$this->tpl[0]->set_var("preview_query_string", $preview_query_string);
					
						$this->tpl[0]->set_var("ShowFilesize", "");
						if($this->file_show_filesize) {
							$file_size = @filesize($check_file);
							if($file_size) {
								$this->tpl[0]->set_var("show_size", $this->get_literal_size($file_size));
								$this->tpl[0]->parse("ShowFilesize", false);
							}
						}

						$this->tpl[0]->set_var("SectWritable", "");
						if($this->file_writable) 
							$this->tpl[0]->parse("SectWritable", false);
						
						if ($this->file_show_edit && is_array($this->file_edit_params[$this->file_edit_type])) {
                            if(!$this->file_edit_params[$this->file_edit_type]["key"]) {
                                $cache = ffCache::getInstance();
                                $this->file_edit_params[$this->file_edit_type]["key"] = $cache->get($this->file_edit_type . "/key");
                            }
							if($this->file_edit_params[$this->file_edit_type]["key"]) {
                                $tmp = md5($id . "-" . $file_full_path);
                                $ff = get_session("ff");
                                $ff["aviary"][$tmp]["folder"] = str_replace($this->getFileBasePath(), "", ffCommon_dirname($file_full_path));
                                $ff["aviary"][$tmp]["base_path"] = $this->getFileBasePath();
                                set_session("ff", $ff);


                                foreach($this->file_edit_params[$this->file_edit_type] AS $params_key => $params_value) {
                                    $this->tpl[0]->set_var(strtolower($this->file_edit_type) . "_" . $params_key, $params_value);
                                }

                                $this->tpl[0]->set_var(strtolower($this->file_edit_type) . "_url", $file_value);
                                $this->tpl[0]->set_var(strtolower($this->file_edit_type) . "_hash_img", $tmp);
                                $this->tpl[0]->parse("SezEdit" . $this->file_edit_type, false);
							}
						}

                        $this->tpl[0]->set_var("showfile_plugin", ($this->file_showfile_plugin
                                                                    ? $this->file_showfile_plugin
                                                                    : "origin-file"
                                                                ));


						$this->tpl[0]->set_var("filename_base", $filename_base);
						$this->tpl[0]->parse("ShowPreviewImg", false);
						$this->tpl[0]->set_var("ShowPreviewNoImg", "");
					} else {
						$this->tpl[0]->set_var("filename_base", $filename_base . " noimg");
						$this->tpl[0]->set_var("ShowPreviewImg", "");
						$this->tpl[0]->parse("ShowPreviewNoImg", false);
					}					
					$this->tpl[0]->parse("ShowPreview", true);
				}

				if ($this->file_show_link) {
					$this->tpl[0]->parse("ShowLink", false);
				}
			
				$this->tpl[0]->set_var("preview_class", $preview_class);
				$this->tpl[0]->parse("SectAddon", false);
			} else {
				$this->tpl[0]->set_var("SectAddon", "");
			}
			//$this->file_show_filename
		}
		else
		{
			$this->tpl[0]->set_var("SectAddon", "");
		}
        
        if($this->file_show_control ) {
			$this->tpl[0]->parse("SectControl", false);
		} else {
			$this->tpl[0]->set_var("SectControl", "");
		}
  
	}

	function process_picture($id, &$value)
	{
		$this->tpl[0]->set_var("id", ffCommon_specialchars($id));

		$suffix_file 		= "file";
		$suffix_name 		= "name";
		$suffix_tmpname 	= "tmpname";
		$suffix_delete 		= "delete";

		if (substr($id, -1) == "]")
		{
			$suffix_file 		= ""; //"[" . $suffix_file . "]";
			$suffix_name 		= "[" . $suffix_name . "]";
			$suffix_tmpname 	= "[" . $suffix_tmpname . "]";
			$suffix_delete 		= "[" . $suffix_delete . "]";
		}
		else
		{
			$suffix_file 		= "_" . $suffix_file;
			$suffix_name 		= ""; //"_" . $suffix_name;
			$suffix_tmpname 	= "_" . $suffix_tmpname;
			$suffix_delete 		= "_" . $suffix_delete;
		}

		$this->tpl[0]->set_var("suffix_file", $suffix_file);
		$this->tpl[0]->set_var("suffix_name", $suffix_name);
		$this->tpl[0]->set_var("suffix_tmpname", $suffix_tmpname);
		$this->tpl[0]->set_var("suffix_delete", $suffix_delete);

		$this->tpl[0]->set_var("filename", $value->getValue($this->get_app_type(), $this->get_locale()));
		$this->tpl[0]->set_var("tmpname", $this->file_tmpname);
		$this->tpl[0]->set_var("encoded_filename", urlencode($value->getValue($this->get_app_type(), $this->get_locale())));

		$query_string = ffProcessTags($this->file_query_string, $this->getKeysArray(), $this->getDataArray(), "normal");
		$this->tpl[0]->set_var("query_string", $query_string);

		if (strlen($this->file_view_query_string))
		{
			$view_query_string = ffProcessTags($this->file_view_query_string, $this->getKeysArray(), $this->getDataArray(), "normal");
			$this->tpl[0]->set_var("view_query_string", $view_query_string);
		}
		else
		{
			$this->tpl[0]->set_var("view_query_string", $query_string);
		}

		$this->tpl[0]->set_var("show_path", $this->file_show_path);
		if (strlen($this->file_tmpname))
		{
			$this->tpl[0]->set_var("show_name", $this->file_tmpname);
			$this->tpl[0]->set_var("encoded_show_name", urlencode($this->file_tmpname));
			$this->tpl[0]->set_var("show_temp", 1);
		}
		else
		{
			$this->tpl[0]->set_var("show_name", $value->getValue($this->get_app_type(), $this->get_locale()));
			$this->tpl[0]->set_var("encoded_show_name", urlencode($value->getValue($this->get_app_type(), $this->get_locale())));
			$this->tpl[0]->set_var("show_temp", 0);
		}
	}

	/**
	 * Esegue il process del Field nel caso si tratti di un radio
	 * @param String $id ID del campo
 	 * @param ffData $value Valore del campo
	 */
	function process_radio($id, &$value)
	{
		$this->tpl[0]->set_var("id", ffCommon_specialchars($id));

		if (is_array($this->recordset) && count($this->recordset))
		{
			foreach ($this->recordset as $key => $item)
			{
				list($tmp, $item_key) = each($item);
				list($tmp, $item_value) = each($item);

				$this->tpl[0]->set_var("Value", ffCommon_specialchars($item_key->getValue($this->get_app_type(), $this->get_locale())));
				if($this->encode_entities)
					$this->tpl[0]->set_var("Label", ffCommon_specialchars($item_value->getValue($this->get_multi_app_type(), $this->get_locale())));
				else
					$this->tpl[0]->set_var("Label", $item_value->getValue($this->get_multi_app_type(), $this->get_locale()));

				if ($item_key->getValue($this->base_type, FF_SYSTEM_LOCALE) == $value->getValue($this->base_type, FF_SYSTEM_LOCALE) || $this->default_selected == ($key + 1))
					$this->tpl[0]->set_var("Checked", "checked");
				else
					$this->tpl[0]->set_var("Checked", "");

				$this->tpl[0]->set_var("properties", $this->getProperties());

				if ($this->radio_display_label)
				{
					$this->tpl[0]->parse("LabelPre", false);
					$this->tpl[0]->parse("LabelPost", false);
				}
				else
				{
					$this->tpl[0]->set_var("LabelPre", "");
					$this->tpl[0]->set_var("LabelPost", "");
				}

				if ($this->radio_hyphen)
				{
					$this->tpl[0]->parse("HyphenPre", false);
					$this->tpl[0]->parse("HyphenPost", false);
				}
				else
				{
					$this->tpl[0]->set_var("HyphenPre", "");
					$this->tpl[0]->set_var("HyphenPost", "");
				}
                $this->tpl[0]->set_var("count", "_" . $key);
				$this->tpl[0]->parse("SectRow", true);
			}
		}
		else
		{
			$this->tpl[0]->set_var("SectRow", "");
		}
	}

	function get_order_field()
	{
		if ($this->order_field === null)
		{
			if (strlen($this->src_table))
				$tblprefix = "`" . $this->src_table . "`.";
			else
				$tblprefix = "";

			return $tblprefix . "`" . $this->get_data_source() . "`";
		}
		else
		{
			return $this->order_field;
		}
	}

	function check_format($value = null, $type = null, $label = null)
	{
		if ($value === null)
			$value = $this->value;
		if ($type === null)
			$type = $this->get_app_type();
		if ($label === null)
			$label = ($this->label ? $this->label : $this->placeholder);

		if (strlen($value->ori_value))
		{
			if (!$value->checkValue(null, $type, $this->get_locale()))
				return "E' stato assegnato al campo " . $label . " un valore non valido."; 
		}

		if (count($this->validators))
		{
			foreach ($this->validators as $key => $validator)
			{
				if ($ret = $validator["obj"]->checkValue($value, $label, $validator["options"]))
					return $ret;
			}
		}

		return false;
	}

	/**
	 * Aggiunge un validator (sono all'interno della cartella ffValidator)
	 * Restituisce il Field con il validatore
	 * @param string $instance Istanza di un validatore
	 * @param array $options Eventuali opzioni del validatore
	 * @return ffField_base
	 */
	function addValidator($instance, $options = null)
	{
		if (is_string($instance))
			$instance = ffValidator::getInstance($instance);
			
		if (!is_object($instance) || !is_subclass_of($instance, "ffValidator_base"))
			ffErrorHandler::raise("ffValidator_base istance expected", E_USER_ERROR, $this, get_defined_vars());

		$type = $instance->getType();

		if (!isset($this->validators[$type]))
		{
			$this->validators[$type]["obj"] = $instance;
			$this->validators[$type]["options"] = $options;
		}

		return $this;
	}
	
	function getFileName()
	{
		if ($this->file_name_override)
			return $this->file_name_override;
		else
			return $this->value_ori->getValue();
	}
}
