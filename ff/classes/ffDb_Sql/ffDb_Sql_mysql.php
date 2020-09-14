<?php
/**
 * SQL database access: mysql version
 * 
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * ffDB_Sql è la classe preposta alla gestione della connessione con database di tipo SQL
 * 
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffDB_Sql
{
	var $locale = "ISO9075";

	// PARAMETRI DI CONNESSIONE
	var $database = null;
	var $user     = null;
	var $password = null;
	var $host     = null;

	var $charset			= "utf8"; //"utf8";
	var $charset_names		= "utf8"; //"utf8";
	var $charset_collation	= "utf8_unicode_ci"; //"utf8_unicode_ci";

	// PARAMETRI DI DEBUG
	var $halt_on_connect_error		= true;		## Setting to true will cause a HALT message on connection error
	var $debug						= false;	## Set to true for debugging messages. It also turn on error reporting
	var $on_error					= "halt";	## "halt" (halt with message), "report" (ignore error, but spit a warning), "ignore" (ignore errors quietly)
	var $HTML_reporting				= true;		## Display Messages in HTML Format

	// PARAMETRI SPECIFICI DI MYSQL
	var $persistent					= true;	## Setting to true will cause use of mysql_pconnect instead of mysql_connect
	
	var $transform_null				= true;

	// -------------------
	//  VARIABILI PRIVATE

	// VARIABILI DI GESTIONE DEI RISULTATI
	var $row	  = -1;
	var $record   = false;

	/* public: current error number and error text */
	var $errno    = 0;
	var $error    = "";

	var $link_id  = false;
	var $query_id = false;

	var $fields			= null;
	var $fields_names	= null;

	private $num_rows = null;
	private $useFormsFramework		= false;
	public 	$events 				= null;
	static protected $_events		= null;

	static $_profile = false;
	static $_objProfile = array();
	
	// COMMON CHECKS
 	public function __set ($name, $value)
 	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("property \"$name\" not found on class " . __CLASS__, E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("property \"$name\" not found on class " . __CLASS__);
	}

	public function __get ($name)
	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("property \"$name\" not found on class " . __CLASS__, E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("property \"$name\" not found on class " . __CLASS__);
	}

	public function __isset ($name)
	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("property \"$name\" not found on class " . __CLASS__, E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("property \"$name\" not found on class " . __CLASS__);
	}

	public function __unset ($name)
	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("property \"$name\" not found on class " . __CLASS__, E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("property \"$name\" not found on class " . __CLASS__);
	}

	public function __call ($name, $arguments)
	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("function \"$name\" not found on class " . __CLASS__, E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("function \"$name\" not found on class " . __CLASS__);
	}

	/*static public function __callStatic ($name, $arguments)
	{
 		if ($this->useFormsFramework)
 			ffErrorHandler::raise("function \"$name\" not found on class " . get_class($this), E_USER_ERROR, $this, get_defined_vars());
 		else
 			die("function \"$name\" not found on class " . get_class($this));
	}*/

	// STATIC EVENTS MANAGEMENT
	static public function addEvent($event_name, $func_name, $priority = null, $index = 0, $break_when = null, $break_value = null, $additional_data = null)
	{
		if (!class_exists("ffCommon", false))
			die(__CLASS__ . ": " . __FUNCTION__ . " method require Forms Framework");

		self::initEvents();
		self::$_events->addEvent($event_name, $func_name, $priority, $index, $break_when, $break_value, $additional_data);
	}

	static private function doEvent($event_name, $event_params = array())
	{
		self::initEvents();
		return self::$_events->doEvent($event_name, $event_params);
	}

	static private function initEvents()
	{
		if (self::$_events === null)
			self::$_events = new ffEvents();
	}

	/**
	 * This method istantiate a ffDb_Sql instance. When using this
	 * function, the resulting object will deeply use Forms Framework.
	 *
	 * @param string $templates_root
	 * @return ffDB_Sql
	 */
	public static function factory()
	{
		if (!class_exists("ffCommon", false))
			die(__CLASS__ . ": " . __FUNCTION__ . " method require Forms Framework");

		$res = self::doEvent("on_factory", array());

		$tmp = new ffDB_Sql();
		$tmp->useFormsFramework = true;
		$tmp->events = new ffEvents();

		$res = self::doEvent("on_factory_done", array($tmp));

		return $tmp;
	}

	// CONSTRUCTOR
	function __construct()
	{
	}

	// -------------------------------------------------
	//  FUNZIONI GENERICHE PER LA GESTIONE DELLA CLASSE
	
	// LIBERA LA CONNESSIONE E LA QUERY
	function cleanup()
	{
		$this->freeResult();
		if (is_resource($this->link_id) && !$this->persistent)
			@mysql_close($this->link_id);
		$this->link_id = false;
	}

	// LIBERA LA RISORSA DELLA QUERY SENZA CHIUDERE LA CONNESSIONE
	function freeResult()
	{
		if (is_resource($this->query_id))
			@mysql_free_result($this->query_id);
		$this->query_id = false;
		$this->row		= -1;
		$this->record 	= false;
		$this->num_rows = null;
		$this->fields	= null;
		$this->fields_names	= null;
	}

	// -----------------------------------------------
	//  FUNZIONI PER LA GESTIONE DELLA CONNESSIONE/DB
	
	// GESTIONE DELLA CONNESSIONE

	/**
	 * Gestisce la connessione al DB
	 * @param String Nome del DB a cui connettersi
	 * @param String Host su cui risiede il DB
	 * @param String username
	 * @param String password
	 * @return String
	 */
	function connect($Database = null, $Host = null, $User = null, $Password = null)
	{
		// CHIUDE LA CONNESSIONE PRECEDENTE NEL CASO DI RIUTILIZZO DELL'OGGETTO
		$this->cleanup();

		// SOVRASCRIVE I VALORI DI DEFAULT, SE NECESSARIO
		if ($Database !== null)
			$this->database = $Database;
		else if ($this->database === null)
			$this->database = FF_DATABASE_NAME;

		if ($Host !== null)
			$this->host = $Host;
		else if ($this->host === null)
			$this->host = FF_DATABASE_HOST;

		if ($User !== null)
			$this->user = $User;
		else if ($this->user === null)
			$this->user = FF_DATABASE_USER;

		if ($Password !== null)
			$this->password = $Password;
		else if ($this->password === null)
			$this->password = FF_DATABASE_PASSWORD;

		if ($this->persistent)
			$this->link_id = @mysql_pconnect($this->host, $this->user, $this->password, 2);
		else
			$this->link_id = @mysql_connect($this->host, $this->user, $this->password, 2);

		if ($this->link_id === false || $this->link_id === null || $this->checkError())
		{
			if ($this->halt_on_connect_error)
				$this->errorHandler("Connection failed to database " . $this->database);
			$this->cleanup();
			return false;
		}

		if ($this->charset_names !== null && $this->charset_collation !== null)
			@mysql_query("SET NAMES '" . $this->charset_names . "' COLLATE '" . $this->charset_collation . "'", $this->link_id);

		if ($this->charset !== null)
			mysql_set_charset($this->charset, $this->link_id);

		if ($this->selectDb())
			return $this->link_id;
		else
			return false;
	}
	
	/**
	 * Seleziona il DB su cui effettuare le operazioni
	 * @param String Nome del DB
	 * @return Boolean
	 */

	function selectDb($Database = "")
	{
		if ($Database == "")
			$Database = $this->database;

		if (!@mysql_select_db($Database, $this->link_id) || $this->checkError())
		{
			$this->errorHandler("Cannot use database " . $this->database);
			$this->cleanup();
			return false;
		}

		return true;
	}

	// -------------------------------------------
	//  FUNZIONI PER LA GESTIONE DELLE OPERAZIONI
	
	/**
	 * Esegue una query senza restituire un recordset
	 * @param String La query da eseguire
	 * @return boolean 
	 */

	function execute($Query_String)
	{
		if ($Query_String == "")
			$this->errorHandler("Execute invoked With blank Query String");

		if (!$this->link_id)
		{
			if (!$this->connect())
				return false;
		}
		else
		{
			if (!$this->selectDb())
				return false;
		}

		$this->freeResult();
		
		$this->debugMessage("Execute = " . $Query_String);
		
		$this->query_id = @mysql_query($Query_String, $this->link_id);
		if ($this->checkError())
		{
			$this->errorHandler("Invalid SQL: " . $Query_String);
			return false;
		}

		return true;
	}

	/**
	 * Esegue una query 
	 * @param String La query da eseguire
	 * @return L'id della query eseguita 
	 */

	function query($Query_String)
	{
		if ($Query_String == "")
			$this->errorHandler("Query invoked With blank Query String");

		if (!$this->link_id)
		{
			if (!$this->connect())
				return false;
		}
		else
		{
			if (!$this->selectDb())
				return false;
		}

		$this->freeResult();

		$this->debugMessage("query = " . $Query_String);

		$this->query_id = @mysql_query($Query_String, $this->link_id);
		if (!$this->query_id || $this->checkError())
		{
			$this->errorHandler("Invalid SQL: " . $Query_String);
			return false;
		}
		else
		{
			$this->fields = array();
			$this->fields_names = array();
			if (is_resource($this->query_id))
			{
				while($tmp = mysql_fetch_field($this->query_id))
				{
					$this->fields[$tmp->name] = $tmp;
					$this->fields_names[] = $tmp->name;
				}
				mysql_field_seek($this->query_id, 0);
			}
			
			if (strpos($Query_String, "SQL_CALC_FOUND_ROWS"))
			{
				if (ini_get("mysql.trace_mode"))
					ffErrorHandler::raise("Disable mysql.trace_mode In order for SQL_CALC_FOUND_ROWS to work", E_USER_ERROR, $this, get_defined_vars ());
				
				$tmp = @mysql_query("SELECT FOUND_ROWS()", $this->link_id);
				if (!$tmp || $this->checkError())
				{
					$this->errorHandler("Unable to retrieve num_rows");
					return $this->query_id;
				}
				$tmp_data = @mysql_fetch_array($tmp, MYSQLI_NUM);
				if ($tmp_data === false)
				{
					$this->errorHandler("Unable to retrieve num_rows");
					return $this->query_id;
				}
				$this->num_rows = $tmp_data[0];
				
				@mysql_free_result($tmp);
				$tmp = false;
				unset($tmp_data);
			}
		}

		return $this->query_id;
	}

	/* function lookup($tabella, $chiave, $valorechiave = null, $defaultvalue = null, $nomecampo = null, $tiporestituito = "Text", $bReturnPlain = false)

		recupera un valore sulla base del match di una o più chiavi in una tabella.
		i valori possono indifferentemente essere specificati sotto forma di ffData o plain values

		chiave può essere:
			$chiave = array(
								"nomecampo" => "valore"
								[, ...]
							);
		oppure
			$chiave = "nomecampo"
			$value = "valore"


		nomecampo può essere:
			$nomecampo = "nomecampo"

		oppure:
			array(
					"nomecampo" => "tipodato"
				)

		il valore restituito rispetterà il formato di "nomecampo".
		nel caso in cui "nomecampo" sia un array, $tiporestituito verrà ignorato.

		NB.: Si ricorda che, se non si utilizza Forms Framework, i tipi accettati sono solo "Number" e "Text"
	*/
	function lookup($tabella, $chiave = null, $valorechiave = null, $defaultvalue = null, $nomecampo = null, $tiporestituito = null, $bReturnPlain = false)
	{
		if (!$this->link_id)
		{
			if (!$this->connect())
				return false;
		}
		else
		{
			if (!$this->selectDb())
				return false;
		}

		if ($tiporestituito === null)
			$tiporestituito = "Text";

		if (strpos(strtolower(trim($tabella)), "select") !== 0)
		{
			$listacampi = "";
			
			if(is_array($nomecampo))
			{
				$valori = array();
				if (!count($nomecampo))
					$this->errorHandler("lookup: Nuessun campo specificato da recuperare", E_USER_ERROR, $this, get_defined_vars());

				foreach ($nomecampo as $key => $value)
				{
					if (strlen($listacampi))
						$listacampi .= ", ";
					$listacampi .= "`" . $key . "`";
				}
				reset($nomecampo);
			}
			elseif ($nomecampo !== null)
			{
				$listacampi = "`" . $nomecampo . "`";
			}
			else
				$listacampi = "*";
			
			$sSql = "SELECT " . $listacampi . " FROM " . $tabella . " WHERE 1 ";
		}
		else
			$sSql = $tabella;
		if(is_array($chiave))
		{
			if (!count($chiave))
				$this->errorHandler("lookup: Nuessuna chiave specificata per il lookup");

			foreach ($chiave as $key => $value)
			{
				if (is_object($value) && get_class($value) != "ffData")
						$this->errorHandler("lookup: Il valore delle chiavi dev'essere di tipo ffData od un plain value", E_USER_ERROR, $this, get_defined_vars());
						
				$sSql .= " AND `" . $key . "` = " . $this->toSql($value);
			}
			reset($chiave);
		}
		elseif ($chiave != null)
		{
			if (is_object($valorechiave) && get_class($valorechiave) != "ffData")
				$this->errorHandler("lookup: Il valore della chiave dev'essere un oggetto ffData od un plain value", E_USER_ERROR, $this, get_defined_vars());

			$sSql .= " AND `" . $chiave . "` = " . $this->toSql($valorechiave);
		}

		$this->query($sSql);
		if ($this->nextRecord())
		{

			if(is_array($nomecampo))
			{
				$valori = array();
				if (!count($nomecampo))
					$this->errorHandler("lookup: Nuessun campo specificato da recuperare", E_USER_ERROR, $this, get_defined_vars());

				foreach ($nomecampo as $key => $value)
				{
					$valori[$key] = $this->getField($key, $value, $bReturnPlain);
				}
				reset($nomecampo);

				return $valori;
			}
			elseif ($nomecampo !== null)
			{
				return $this->getField($nomecampo, $tiporestituito, $bReturnPlain);
			}
			else
			{
				return $this->getField($this->fields_names[0], $tiporestituito, $bReturnPlain);
			}

		}
		else
		{
			if ($defaultvalue === null)
				return false;
			else
				return $defaultvalue;
		}
	}
	
	/**
	 * Sposta il puntatore al DB al record successivo (va chiamato almeno una volta)
	 * @return boolean
	 */

	function nextRecord()
	{
		if (!$this->query_id)
		{
			$this->errorHandler("nextRecord called with no query pending");
			return false;
		}

		// fetch assoc bug workaround...
		if ($this->row == ($this->numRows() - 1))
			return false;

		$this->record = @mysql_fetch_assoc($this->query_id);
		/*if ($this->checkError())
		{
			$this->errorHandler("Invalid SQL: " . $Query_String);
			return false;
		}*/

		if ($this->record !== false && $this->record !== null)
		{
			$this->row += 1;
			return true;
		}
		else
		{
			return false;
		}
	}

	// SI POSIZIONA AD UN RECORD SPECIFICO
	function seek($pos = 0)
	{
		if (!$this->query_id)
		{
			$this->errorHandler("Seek called with no query pending");
			return false;
		}

		if (!@mysql_data_seek($this->query_id, $pos)/* || $this->checkError()*/)
		{
			$this->errorHandler("seek($pos) failed, result has  " . $this->numRows() . " rows");
			@mysql_data_seek($this->query_id, 0);
			$this->row = -1;
			return false;
		}
		else
		{
			$this->record 	= @mysql_fetch_assoc($this->query_id);
			$this->row 		= $pos;
			return true;
		}
	}

	// SI POSIZIONA AL PRIMO RECORD DI UNA PAGINA IDEALE
	function jumpToPage($page, $RecPerPage)
	{
		$totpage = ceil($this->numRows() / $RecPerPage);
		if ($page > $totpage)
			$page = $totpage;

		if ($page > 1)
			if ($this->seek(($page - 1) * $RecPerPage))
				return $page;

		return false;
	}

	// -------------------------
	//  WRAPPER PER L'API MySQL
	
	function affectedRows()
	{
		if (!$this->link_id)
		{
			$this->errorHandler("affectedRows() called with no DB connection");
			return false;
		}

		return @mysql_affected_rows($this->link_id);
	}

	/**
	 * Conta il numero di righe
	 * @return Il numero di righe
	 */

	function numRows()
	{
		if (!$this->query_id)
		{
			$this->errorHandler("numRows() called with no query pending");
			return false;
		}
		
		if ($this->num_rows === null)
			$this->num_rows = @mysql_num_rows($this->query_id);

		return $this->num_rows;
	}

	/**
	 * Conta il numero di campi
	 * @return Il numero di campi
	 */

	function numFields()
	{
		if (!$this->query_id)
		{
			$this->errorHandler("numFields() called with no query pending");
			return false;
		}

		return @mysql_num_fields($this->query_id);
	}

	function isSetField($Name)
	{
		if (!$this->query_id)
		{
			$this->errorHandler("isSetField() called with no query pending");
			return false;
		}

		if(isset($this->fields[$Name]))
			return true;
		else
			return false;
	}

	/* ----------------------------------------
	    FUNZIONI PER LA GESTIONE DEI RISULTATI

	    Ogni volta che verrà restituito un valore da una query il tipo di valore dipenderà
	    dal settaggio globale della classe "useFormsFramework".

	    Nel caso sia abilitato, verrà restituito un oggetto di tipo ffData, nel caso
	    sia disabilitato verrà restituito un plain value.
	    E' possibile forzare la restituzione di un plain value usando il parametro $bReturnPlain.

	    Nel caso in cui non si utilizzi Forms Framework, i data_type accettati saranno solo
	    "Text" (il default) e "Number".
	*/
	
	function getInsertID($bReturnPlain = false)
	{
		if (!$this->link_id)
		{
			$this->errorHandler("insert_id() called with no DB connection");
			return false;
		}
		if (!$this->useFormsFramework || $bReturnPlain)
			return @mysql_insert_id($this->link_id);
		else
			return new ffData(@mysql_insert_id($this->link_id), "Number", $this->locale);
	}

	/**
	 *
	 * @param String Nome del campo
	 * @param String Tipo di dato inserito
	 * @param <type> $bReturnPlain
	 * @return ffData Dato recuperato dal DB 
	 */
	function getField($Name, $data_type = "Text", $bReturnPlain = false, $return_error = true)
	{
		if (!$this->query_id)
		{
			$this->errorHandler("f() called with no query pending");
			return false;
		}

		if(isset($this->fields[$Name]))
			$tmp = $this->record[$Name];
		else {
            if($return_error)
			    $tmp = "NO_FIELD [" . $Name . "]";
            else
                $tmp = null;
        }
		if (!$this->useFormsFramework || $bReturnPlain)
		{
			switch ($data_type)
			{
				case "Number":
					if (strpos($tmp, ".") !== false || strpos($tmp, ",") !== false)
						return (double)$tmp;
					else
						return (int)$tmp;
				default:
					return $tmp;
			}
		}
		else
			return new ffData($tmp, $data_type, $this->locale);
	}

	// PERMETTE DI RECUPERARE IL VALORE DI UN CAMPO SPECIFICO DI UNA RIGA SPECIFICA. NB: Name può essere anche un indice numerico
	function getResult($row = null, $Name, $data_type = "Text", $bReturnPlain = false)
	{
		if (!$this->query_id)
		{
			$this->errorHandler("result() called with no query pending");
			return false;
		}

		if ($row === null)
			$row = $this->row;

		if (!$this->useFormsFramework || $bReturnPlain)
			return @mysql_result($this->query_id, $row, $Name);
		else
			return new ffData(@mysql_result($this->query_id, $row, $Name), $data_type, $this->locale);
	}

	// ----------------------------------------
	//  FUNZIONI PER LA FORMATTAZIONE DEI DATI
	
	function toSql($cDataValue, $data_type = null, $enclose_field = true, $transform_null = null)
	{
		if (!$this->link_id)
			$this->connect();

		if (is_array($cDataValue))
		{
			$this->errorHandler("toSql: Wrong parameter, array not managed.");
		}
		elseif (!is_object($cDataValue))
		{
			$value = mysql_real_escape_string($cDataValue, $this->link_id);
		}
		else if (get_class($cDataValue) == "ffData")
		{
			
			if ($data_type === null)
				$data_type = $cDataValue->data_type;

			$value = mysql_real_escape_string($cDataValue->getValue($data_type, $this->locale), $this->link_id);
		}
		else if (get_class($cDataValue) == "DateTime")
		{
			switch ($data_type)
			{
				case "Date":
					$tmp = new ffData($cDataValue, "Date");
					$value = mysql_real_escape_string($tmp->getValue($data_type, $this->locale), $this->link_id);
					break;
				
				case "DateTime":
				default:
					$data_type = "DateTime";
					$tmp = new ffData($cDataValue, "DateTime");
					$value = mysql_real_escape_string($tmp->getValue($data_type, $this->locale), $this->link_id);
			}
		}
		else
			$this->errorHandler("toSql: Wrong parameter, unmanaged datatype");
			
		if ($transform_null === null)
			$transform_null = $this->transform_null;

		switch ($data_type)
		{
			case "Number":
			case "ExtNumber":
				if (!strlen($value))
				{
					if ($transform_null)
						return 0;
					else
						return "null";
				}
				return $value;

			default:
				if (!strlen($value) && !$transform_null)
					return "null";
				
				if (!strlen($value) && ($data_type == "Date" || $data_type == "DateTime"))
					$value = ffData::getEmpty($data_type, $this->locale);

				if ($enclose_field)
					return "'" . $value . "'";
				else
					return $value;
		}
	}

	function mysqlPassword($passStr)
	{
		$dbtemp = ffDB_Sql::factory();
		$dbtemp->connect($this->database, $this->host, $this->user, $this->password);
		$dbtemp->query("SELECT PASSWORD('" . $passStr . "') AS password");
		$dbtemp->nextRecord();
		return $dbtemp->getField("password", "Text", true);
	}

	function mysqlOldPassword($passStr)
	{
		/*$dbtemp = new ffDb_sql;
		$dbtemp->connect($this->database, $this->host, $this->user, $this->password);
		$dbtemp->query("SELECT OLD_PASSWORD('" . $passStr . "') AS password");
		$dbtemp->nextRecord();
		return $dbtemp->getField("password", "Text", true);*/
		
		$nr = 0x50305735;
		$nr2 = 0x12345671;
		$add = 7;
		$charArr = preg_split("//", $passStr);

		foreach ($charArr as $char)
		{
			if (($char == '') || ($char == ' ') || ($char == '\t')) continue;
			$charVal = ord($char);
			$nr ^= ((($nr & 63) + $add) * $charVal) + ($nr << 8);
			$nr2 += ($nr2 << 8) ^ $nr;
			$add += $charVal;
		}

		return sprintf("%08x%08x", ($nr & 0x7fffffff), ($nr2 & 0x7fffffff));
	}

	// ----------------------------------------
	//  GESTIONE ERRORI
	
	function debugMessage($msg)
	{
		if ($this->debug)
		{
			if ($this->HTML_reporting)
			{
				$tmp = "ffDb_sql - Debug: $msg<br />";
			}
			else
			{
				$tmp = "ffDb_sql - Debug: $msg\n";
			}
		}
		if(ffDB_Sql::$_profile) {
			ffDB_Sql::$_objProfile["total"]++;
			ffDB_Sql::$_objProfile[substr($msg, strpos($msg, "=") + 1, 60)][] = $msg;
		}
	}

	function checkError()
	{
		$this->error = @mysql_error($this->link_id);
		$this->errno = @mysql_errno($this->link_id);
		if ($this->errno)
			return true;
		else
			return false;
	}
	
	function errorHandler($msg)
	{
		$this->checkError(); // this is needed due to params order

		if ($this->on_error == "ignore" && !$this->debug)
			return;

		if ($this->HTML_reporting)
		{
			$tmp = "ffDb_sql - Error: $msg";

			if ($this->errno)
			{
				$tmp .= "<br />MySQL - Error #" . $this->errno . ": " . $this->error;
			}

			if (!$this->useFormsFramework)
			{
				print $tmp;
				if ($this->on_error == "halt")
					die("<br />ffDb_sql - Error: Script Halted.<br />");
			}
			else
			{
				if ($this->on_error == "halt")
					$err_code = E_USER_ERROR;
				else
					$err_code = E_USER_WARNING;

				ffErrorHandler::raise($tmp, $err_code, $this, get_defined_vars());
			}
		}
		else
		{
			$tmp = "ffDb_sql - Error: $msg";

			if ($this->errno)
			{
				$tmp .= "\nMySQL - Error #" . $this->errno . ": " . $this->error;
			}


			print $tmp;
			if ($this->on_error == "halt")
				die("ffDb_sql - Error: Script Halted.\n");
		}
		return;
	}
}