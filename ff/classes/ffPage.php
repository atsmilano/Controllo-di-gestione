<?php
/**
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * ffPage ?? la classe che funge da contenitore e riferimento per tutti gli altri oggetti del framework,
 * dai componenti agli elementi base.
 * E' indispensabile per realizzare una pagina che utilizzi gli automatismi del framework, per quanto sia possibile
 * utlizzare piccoli pezzetti del framework senza il suo intervento.
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

if (!defined("FF_ENABLE_MEM_PAGE_CACHING"))         define("FF_ENABLE_MEM_PAGE_CACHING", false);

class ffPage
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
     * Questo metodo crea un'istanza di ffPage basandosi sui parametri che riceve
     *
     * @param string $site_path
     * @param string $disk_path
     * @param string $page_path
     * @param string $theme
     * @param array $variant
     * @return ffPage_base
     */
    public static function factory($disk_path, $site_path, $page_path, $theme = null, $variant = null)
    {
        if ($theme === null)
            $theme = FF_DEFAULT_THEME;

        $res = self::doEvent("on_factory", array($disk_path, $site_path, $page_path, $theme, $variant));
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
 * ffPage_base ?? la classe che funge da contenitore e riferimento per tutti gli altri oggetti del framework,
 * dai componenti agli elementi base.
 * E' indispensabile per realizzare una pagina che utilizzi gli automatismi del framework, per quanto sia possibile
 * utlizzare piccoli pezzetti del framework senza il suo intervento.
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
abstract class ffPage_base extends ffCommon
{
    // ----------------------------------
    //  PUBLIC VARS (used for settings)

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
     * Cartella dove ?? contenuta la pagina partendo dalla root del sito
     * @var String
     */
    var $page_path 				= "";

    /**
     * Cartella del template; di default ?? la cartella "theme"
     * @var String
     */
    var $template_dir			= null;

    /**
     * Titolo della pagina
     * @var String
     */
    var $title					= "";

    /**
     * E' il link alla pagina precedente
     * Se non specificato (default) viene auto-generato quando si accede a questa ffPage da un'altra ffPage
     * @var String
     */
    var $ret_url				= null;

    /**
     * Tema utilizzato da ffPage; di default ?? settato a "default"
     * @var String
     */
    var $theme 					= "default";

    /**
     * Permette di alterare il percorso utilizzato nel form
     * @var String
     */
    var $script_name			= null;
    /**
     * Permette di alterare la query string
     * @var String
     */
    var $script_params			= null;

    /**
     * Contenuto fisso da prependere al {content}
     * @var String
     */
    var $fixed_pre_content		= "";

    /**
     * Contenuto fisso da appendere al {content}
     * @var String
     */
    var $fixed_post_content		= "";

    /**
     * hash d'indetificazione della richiesta (ad uso della cache)
     * @var String
     */
    var $request_key			= null;

    // ---------------------------------------------------------------
    //  PRIVATE VARS (used by code, don't touch or may be explode! :-)

    /**
     * i contenuti della pagina in ordine d'inserimento
     * @var array()
     */
    var $contents				= array();
    /**
     * i gruppi in cui ? suddivisa la pagina
     * @var array()
     */
    var $groups					= array();

    /**
     * i campi aggiunti alla pagina tramite addContent (ffField)
     * @var array()
     */
    var $fields					= array();
    /**
     * i pulsanti aggiunti alla pagina tramite addContent (ffButton)
     * @var array()
     */
    var $buttons					= array();
    /**
     * i componenti aggiunti alla pagina tramite addContent o addComponent (ffGrid, ffRecord, ffDetails)
     * @var array()
     */
    var $components				= array();
    /**
     * oggetti che non ricadono nelle altre categorie
     * @var array()
     */
    var $objects					= array();
    /**
     * Variabile che contiene i bounce_component; i bounce_component sono quei componenti
     * che devono essere "rimbalzati" da una ffpage all'altra
     * (ad esempio nel caso di una procedura a step)
     * @var array()
     */
    var $bounce_components		= array();

    /**
     * Array contenente tutti i parametri che vengono passati tra tutte le pagine
     * @var array()
     */
    var $global_params			= array();

    /**
     * Array bidimensionale contenente tutti i parametri processati dalla pagina
     * @var array()
     */
    var $params					= array();

    /**
     * Buffer di output del processing
     * @var String
     */
    var $output_buffer			= "";
    var $components_buffer		= array();
    var $objects_buffer		= array();

    /**
     * il template principale della pagina
     * @var Array ffTemplate
     */
    var $tpl					= null;

    /**
     * Contiene le widget associate alla pagina
     * @var array()
     */
    var $widgets				= array();

    /**
     * Chiavi della pagina (se esistono)
     * @var array()
     */
    var $keys					= array();

    var $frmAction				= "";

    var $globals				= array();

    /**
     * Array contenente i campi nascosti di ffPage
     * @var array()
     */
    var $hidden_fields			= array();

    /**
     * Se il template principale ?? stato caricato o meno
     * @var boolean
     */
    var $template_loaded        = false;
    var $template_layer_loaded  = false;

    /**
     * Se i parametri di request sono stati processati o meno
     * @var boolean
     */
    var $params_processed		= false;

    /**
     * L'istanza all'oggetto cache
     * @var ffMemCache
     */
    var $cache					= null;
    var $use_cache              = FF_ENABLE_MEM_PAGE_CACHING;

    var $force_no_xhr			= false;

    var $struct_properties		= array();

    // ---------------------------------------------------------------
    //  ABSTRACT FUNCS (depends on theme)
    // ---------------------------------------------------------------

    abstract public function tplLoad();
    abstract protected function tplProcessBounceComponents();
    abstract protected function tplProcessBounceArray($array, $prefix, $arrayname, $arraykeys = "");
    abstract protected function tplProcessVars($tpl);
    abstract protected function tplProcess();
    abstract protected function tplParse($output_result);

    // ---------------------------------------------------------------
    //  PUBLIC FUNCS
    // ---------------------------------------------------------------

    /**
     * il costruttore di ffPage richiede, come ogni altro elemento del framework, il passaggio delle variabili
     * che determinano i percorsi utilizzati negli automatismi.
     * Oltre a impostare i corretti valori di default, il costruttore non esegue altre operazioni degne di nota.
     *
     * @param string $site_path il percorso assoluto su web del sito
     * @param string $disk_path il percorso assoluto su disco
     * @param string $page_path il percorso relativo al sito della pagina
     */
    public function __construct($disk_path, $site_path, $page_path, $theme)
    {
        $this->get_defaults("ffPage");
        $this->get_defaults();

        $this->site_path = $site_path;
        $this->disk_path = $disk_path;
        $this->page_path = $page_path;
        $this->theme = $theme;

        if ($this->use_cache)
        {
            $this->cache = ffCache::getInstance();
            $this->request_key = sha1($_SERVER["REQUEST_URI"] . "_" . serialize($_REQUEST));
        }
    }

    /**
     * Aggiunge un contenuto alla pagina, facendo si che sia processato.
     * 		Ogni contenuto deve avere un id univoco (componente->id) o ffPage sovrascriver?? l'istanza.
     * 		E' bene ricordare che il contenuto viene memorizzato tramite un riferimento.
     * 		Questo significa che modifiche successive alla variabile contenente l'oggetto saranno applicate
     * 		anche all'istanza presente nella collezione.
     *		se content = null, $id = string e group = true aggiunge un gruppo.
     * @param mixed $content una variabile contenente un oggetto di FF od un contenuto testuale qualsiasi
     * @param string $id
     * @param string $group
     */
    public function addContent($content, $group = null, $id = null, $options = array())
    {
        if ($content !== null)
        {
            if (
                is_object($content)
                && (
                    is_subclass_of($content, "ffGrid_base")
                    || is_subclass_of($content, "ffRecord_base")
                    || is_subclass_of($content, "ffDetails_base")
                    || is_subclass_of($content, "ffField_base")
                    || is_subclass_of($content, "ffButton_base")
                    || is_subclass_of($content, "ffPageNavigator_base")
                )
            )
            {
                if ($id === null)
                    $id = $content->id;

                if (is_subclass_of($content, "ffPageNavigator_base"))
                {
                    $content->parent = array(&$this);
                    $this->objects[$content->id] = $content;
                    return;
                }
                elseif (is_subclass_of($content, "ffField_base"))
                {
                    $content->parent_page = array(&$this);
                    $this->fields[$content->id] = $content;
                    $content->cont_array =& $this->fields;
                }
                elseif (is_subclass_of($content, "ffButton_base"))
                {
                    $content->parent_page = array(&$this);
                    $this->buttons[$content->id] = $content;
                }
                else
                {
                    $content->parent = array(&$this);
                    $this->components[$content->id] = $content;
                    $this->params[$content->id] = array();
                }

                if (is_array($content->widget_deps) && count($content->widget_deps))
                {
                    foreach ($content->widget_deps as $key => $value)
                    {
                        if ($value["frompage"])
                            $ref = $this;
                        else
                            $ref = $content;
                        $this->widgetLoad($value["name"], $value["path"], $ref);
                    }
                    reset($content->widget_deps);
                }

                if ($content->use_own_location || !$content->display)
                    return;
            } elseif(is_string($content)) {
                if(strpos($content, "/") === 0 && is_file($content)) {
                    $content = file_get_contents($content);
                }

                $content = ffTranslator::process($content);

                if(is_array($options["vars"]) && count($options["vars"])) {
                    $content = str_replace(array_keys($options["vars"]), array_values($options["vars"]), $content);
                }
            }

            if ($id === null)
                $id = uniqid(time(), true);

            if ($group === null)
            {
                $this->contents[(string)$id]["data"] = $content;
                $this->contents[(string)$id]["group"] = null;
            }
            else
            {
                $this->groups[$group]["contents"][(string)$id]["data"][] = $content;
                if (isset($options["title"]))
                    $this->groups[$group]["contents"][(string)$id]["title"] = $options["title"];
            }

        }
        elseif ($group === true)
        {
            if ($id === null)
                $id = (string)uniqid(time(), true);

            $this->contents[$id]["data"] = $content;
            $this->contents[$id]["group"] = true;
            if (isset($options["title"]))
                $this->groups[$id]["title"] = $options["title"];
            //if (isset($options["cols"]))
            //    $this->groups[$id]["cols"] = $options["cols"];
            if (isset($options["tab_mode"]))
                $this->groups[$id]["tab_mode"] = $options["tab_mode"];
            if (isset($options["vars"]))
                $this->groups[$id]["vars"] = $options["vars"];
        }
        else
            ffErrorHandler::raise("Unhandled Content", E_USER_ERROR, $this, get_defined_vars());


        //ffErrorHandler::raise("Unhandled Content", E_USER_WARNING, $this, get_defined_vars());
    }

    /**
     * DEPRECATA!!! USARE addContent()
     *		Aggiunge un componente alla pagina, facendo si che sia processato.
     * 		Ogni componente deve avere un id univoco (componente->id) o ffPage sovrascriver?? l'istanza.
     * 		E' bene ricordare che il componente viene memorizzato tramite un riferimento.
     * 		Questo significa che modifiche successive alla variabile contenente l'oggetto saranno applicate
     * 		anche all'istanza presente nella collezione.
     * @param pComponent $component una variabile oggetto contenente un componente di Forms
     * 		(ffGrid, ffRecord, ffDetails, ffCalendar)
     */
    function addComponent($component)
    {
        if (
            !is_subclass_of($component, "ffGrid_base")
            && !is_subclass_of($component, "ffRecord_base")
            && !is_subclass_of($component, "ffDetails_base")
        )
            ffErrorHandler::raise("Wrong call to addComponent: object must be a Forms Component", E_USER_ERROR, $this, get_defined_vars());

        $this->addContent($component);
    }

    /**
     * aggiunge alla pagina un riferimento a componenti non presenti nella pagina medesima, ma i cui parametri
     * 		vengono passati e vanno gestiti, continuando per cos?? dire a rimbalzare fra le pagine.
     * 		E' bene ricordare che gli id dei componenti in questione devono essere differenti da quelli dei componenti
     * 		presenti nella pagina medesima, a meno che non si desideri che i parametri dei due componenti si sovrappongano.
     * @param string $sName l'id del componente da rimbalzare
     */
    function addBounceComponent($sName)
    {
        $this->bounce_components[$sName] = $sName;
        $this->params[$sName] = array();
    }

    /**
     * aggiunge alla pagina un campo nascosto. Funziona solo se ?? abilitato il form della pagina (abilitato per default).
     *
     * @param string $sName il nome che dovr?? assumere il campo nascosto
     * @param object $oField una variabile contenente l'oggetto da utilizzare per estrarre il valore del campo.
     * 							Pu?? essere un ffData o un ffField. Nel secondo caso verr?? usato il membro "value".
     */
    function addHiddenField($sName, $oField = null, $sType = "Text", $sLocale = FF_SYSTEM_LOCALE)
    {
        if (is_array($oField))
            $oField = $oField[0];

        $this->hidden_fields[$sName] = array("field" => $oField, "type" => $sType, "locale" => $sLocale);
    }

    /**
     * recupera il risultato dell'elaborazione di un contenuto
     * @param mixed $content il contenuto, pu?? essere un oggetto od un contenuto testuale
     * @return mixed
     */
    public function getContentData($content)
    {
        if (
            is_object($content)
            && (
                is_subclass_of($content, "ffGrid_base")
                || is_subclass_of($content, "ffRecord_base")
                || is_subclass_of($content, "ffDetails_base")
            )
            && $content->display !== false
        )
        {
            if ($content->display !== false)
                return $this->components_buffer[$content->id];
            else
                return "";
        }
        elseif (
            is_object($content)
            && (
                is_subclass_of($content, "ffField_base")
                || is_subclass_of($content, "ffButton_base")
            )
        )
        {
            return $content->process();
        }
        elseif (
            is_object($content)
            && get_class($content) == "ffData"
        )
        {
            return $content->getValue(null, FF_LOCALE);
        }
        elseif (
            is_object($content)
            && get_class($content) == "ffTemplate"
        )
        {
            return $content->rpparse("main", false);
        }
        elseif (is_string($content))
        {
            return $content;
        }
        else
        {
            ffErrorHandler::raise("Unhandled Content", E_USER_ERROR, $this, get_defined_vars());
        }
    }

    /**
     * getter per l'url utilizzato
     * @return String
     */
    function get_script_name()
    {
        if ($this->script_name !== null)
            return $this->script_name;
        else
        {
            if (isset($_SERVER["ORIG_PATH_INFO"]))
                return $_SERVER["ORIG_PATH_INFO"];
            else
                return $_SERVER["SCRIPT_NAME"];
        }
    }

    /**
     * getter per i parametri dell'url
     * @return String
     */
    function get_script_params()
    {
        if ($this->script_params !== null)
            return $this->script_params;
        else
            return $_SERVER["QUERY_STRING"];
    }

    // -----------------------
    //  Template Funcs

    /**
     * getter per il tema
     * @return String
     */
    function getTheme()
    {
        return $this->theme;
    }

    /**
     * Questa funzione restituisce la directory dove sono contenuti i temi del sito web.
     *
     * @return La directory dei temi.
     */
    function getThemeDir()
    {
        $res = $this->doEvent("getThemeDir", array(&$this));
        $last_res = end($res);
        if ($last_res === null)
        {
            return $this->disk_path . "/themes/" . $this->getTheme();
        }
        else
        {
            return $last_res;
        }
    }

    /**
     * Questa funzione restituisce il percorso del tema.
     * @return Il percorso del tema
     *
     */
    function getThemePath($include_site_path = true)
    {
        $res = $this->doEvent("getThemePath", array(&$this));
        $last_res = end($res);
        if ($last_res === null)
        {
            if($include_site_path)
                return $this->site_path . "/themes/" . $this->getTheme();
            else
                return "/themes/" . $this->getTheme();
        }
        else
        {
            return $last_res;
        }
    }

    /**
     * Restituisce la directory del template utilizzato
     * @return String La directory del template
     */
    function getTemplateDir($filename = null)
    {
        if ($this->template_dir !== null)
            return $this->template_dir;

        $res = $this->doEvent("getTemplateDir", array(&$this, ($filename === null ? $this->template_file : $filename)));
        $last_res = end($res);
        if ($last_res !== null)
            return $last_res;
        else if ($filename === null)
            return $this->disk_path . "/themes/" . $this->getTheme() . "/ff/ffPage";
        else
            return null;
    }

    /**
     * elabora i parametri di request della pagina
     */
    function process_params()
    {
        // retrieve returning url
        if (!strlen($this->ret_url))
        {
            $this->ret_url = $_REQUEST["ret_url"];
        }

        // retrieve page unique keys.
        $this->keys = $_REQUEST["keys"];

        $this->frmAction = $_REQUEST["frmAction"];
        if (strlen($this->frmAction))
        {
            $prefix = substr($this->frmAction, 0, strpos($this->frmAction, "_"));
            if (strlen($prefix) && isset($this->params[$prefix]))
            {
                $suffix = substr($this->frmAction, strpos($this->frmAction, "_") + 1);
                $this->params[$prefix]["frmAction"] = $suffix;
            }
        }

        // process normal submitted vars
        foreach ($_REQUEST as $key => $value)
        {
            $prefix = substr($key, 0, strpos($key, "_"));
            if (strlen($prefix) && isset($this->params[$prefix]))
            {
                $suffix = substr($key, strpos($key, "_") + 1);
                $this->params[$prefix][$suffix] = $value;
            }
            else
            {
                $this->global_params[$key] = $value;
            }
        }
        reset($_REQUEST);

        // After normal vars, process files array
        if (is_array($_FILES))
        {
            foreach ($_FILES as $key => $value)
            {
                $prefix = substr($key, 0, strpos($key, "_"));
                if (strlen($prefix) && isset($this->params[$prefix]))
                {
                    $suffix = substr($key, strpos($key, "_") + 1);
                    if (is_array($value["name"]))
                    {
                        $this->filesRecordsetNormalize($value, $this->params[$prefix][$suffix]);
                    }
                    else
                        $this->params[$prefix][$suffix] = $value;
                }
                else
                {
                    $this->global_params[$key] = $value;
                }
            }
            reset($_FILES);
        }

        $this->params_processed = true;
        $this->doEvent("on_params_process", array(&$this));
    }

    /**
     * Normalizza l'array file unendolo con i parametri di request in modo che possa essere utilizzato con ffPage
     * @param array() $value la porzione d'array di Files
     * @param String $param il nome del parametro da utilizzare
     */
    protected function filesRecordsetNormalize($value, &$param)
    {
        $ret = array();

        if (!is_array($param))
            $param = array();

        foreach ($value as $files_key => $files_value)
        {
            foreach ($files_value as $rst_key => $rst_field)
            {
                foreach ($rst_field as $rst_field_id => $rst_field_value)
                {
                    $param[$rst_key][$rst_field_id]["file"][$files_key] = $rst_field_value;
                }
            }
        }
    }

    /**
     * verifica che esista un determinato campo chiave
     * @param String $key_name
     * @return boolean
     */
    function isset_key($key_name)
    {
        if (isset($this->keys[$key_name]) && strlen($this->keys[$key_name]))
            return true;
        else
            return false;
    }

    /**
     * restituisce il valore di un determinato campo chiave
     * @param String $key_name
     * @return String
     */
    function retrieve_key($key_name)
    {
        if ($this->isset_key($key_name))
            return $this->keys[$key_name];
        else
            return null;
    }

    /**
     * Verifica che sia stato richiesto un parametro
     * @param String $object_name
     * @param String $param_name
     * @param boolean $bGlobal
     * @return boolean
     */
    function isset_param($object_name, $param_name, $bGlobal = false)
    {
        if ($bGlobal || $object_name === null)
            return @isset($this->global_params[$param_name]);
        else
            return @isset($this->params[$object_name][$param_name]);
    }

    /**
     * recupera un parametro
     * @param String $object_name
     * @param String $param_name
     * @param boolean $bGlobal
     * @return String
     */
    function retrieve_param($object_name, $param_name = null, $bGlobal = false)
    {
        if ($bGlobal || $object_name === null)
        {
            if ($this->isset_param($object_name, $param_name, $bGlobal))
                return $this->global_params[$param_name];
            else
                return null;
        }
        else
        {
            if ($param_name === null)
                return @$this->params[$object_name];

            if ($this->isset_param($object_name, $param_name, $bGlobal))
                return @$this->params[$object_name][$param_name];
            else
                return null;
        }
    }

    /**
     * recupera i parametri dell'url con cui ?? stata chiamata la pagina sotto forma di query string
     * @param array() $exclude l'elenco dei parametri da escludere
     * @return String
     */
    function getUrlParams($exclude = array())
    {
        $params = explode("&", $this->get_script_params());
        $ret = "";
        foreach ($params as $key => $value)
        {
            $parts = explode("=", $value);
            if (array_search($parts[0], $exclude) === false)
                $ret .= $parts[0] . "=" . rawurlencode($parts[1]) . "&";
        }
        return $ret;
    }

    /**
     * recupera i parametri con cui ?? stata chiamata la pagina sotto forma di query string
     * @param String $object_name restringe la ricerca al singolo componente
     * @param String $object_params aggiunge alcuni parametri
     * @param boolean $bEncodeEntities restituisce l'url con l'ecoding dei caratteri speciali
     * @return String
     */
    function get_params($object_name = "", $object_params = "", $bEncodeEntities = true, $refresh_keys = false)
    {
        $ret = "";

        // this is do this way to avoid a loop
        $components_keys = array_keys($this->components);

        // is long written this way, but is much safe for errors
        if (strlen($object_name))
        {
            if (strlen($object_params))
                $ret .= $object_params;

            foreach ($components_keys as $key => $item)
            {
                if ($item != $object_name)
                {
                    $ret .= "&" . $item->params;
                }
            }
            reset($components_keys);
        }
        else
        {
            foreach ($components_keys as $key => $item)
            {
                if (strlen($ret))
                    $ret .= "&";
                $ret .= $this->components[$item]->params;
            }
            reset($components_keys);
        }

        $keys = $this->keys;
        if ($refresh_keys)
        {
            foreach ($components_keys as $key)
            {
                $subkeys = array_keys($this->components[$key]->key_fields);
                foreach ($subkeys as $subkey)
                {
                    $keys[$subkey] = $this->components[$key]->key_fields[$subkey]->getValue(null, FF_SYSTEM_LOCALE);
                }
            }
            reset($components_keys);
        }
        if (is_array($keys) && count($keys))
        {
            foreach ($keys as $key => $value)
            {
                $ret .= "&keys[$key]=" . urlencode($value);
            }
            reset($keys);
        }

        $ret .= "&ret_url=" . urlencode($this->ret_url);
        if ($bEncodeEntities)
            return ffCommon_specialchars($ret);
        else
            return $ret;
    }

    function getRequestUri($refresh_keys = false)
    {
        $ru_query_p = array();
        $url = $_SERVER["REQUEST_URI"];

        $tmp = explode("?", $url);
        $path = $tmp[0];
        $ru_query_s = $tmp[1];
        if (strlen($ru_query_s))
            $ru_query_p = explode("&", $ru_query_s);

        $query_s = $this->get_params("", "", false, $refresh_keys) . "&" . $this->get_globals(GET_GLOBALS_EXCLUDE_LIST);
        $query_p = explode("&", $query_s);

        foreach ($ru_query_p as $key => $value)
        {
            if (strlen($value))
            {
                $tmp = explode("=", $value);
                if (strlen($tmp[0]))
                    $ru_query_p[$tmp[0]] = $tmp[1];
            }
            unset($ru_query_p[$key]);
        }
        reset($ru_query_p);

        foreach ($query_p as $key => $value)
        {
            if (strlen($value))
            {
                $tmp = explode("=", $value);
                if (strlen($tmp[0]))
                    $query_p[$tmp[0]] = $tmp[1];
            }
            unset($query_p[$key]);
        }
        reset($query_p);

        $combined_p = array_merge($ru_query_p, $query_p);

        $final_qs = "";
        foreach ($combined_p as $key => $value)
        {
            $final_qs .= $key . "=" . $value . "&";
        }
        reset($combined_p);

        return $path . "?" . $final_qs;
    }

    /**
     * recupera le chiavi con cui ?? stata richiesta la pagina sotto forma di url
     * @param array() $keys
     * @return String
     */
    function get_keys(&$keys = null)
    {
        $ret = "";
        if (is_array($this->keys) && count($this->keys))
        {
            foreach ($this->keys as $key => $value)
            {
                if (!isset($keys[$key]))
                {
                    $ret .= "keys[$key]=$value&";
                }
            }
            reset($this->keys);
        }
        return $ret;
    }

    /**
     * carica una widget
     * @param String $name il nome della widget
     * @param String $path il percorso della widget,
     * @param <type> $ref parametro di riferimento per l'evento di caricamento
     */
    function widgetLoad($name, $path = null, &$ref = null)
    {
        if (isset($this->widgets[$name]))
            return false;

        $source_path 	= null;

        $res = $this->doEvent("on_widget_load", array(&$this, $name, $path, $ref));
        $last_res = end($res);

        if (is_null($last_res))
        {
            $realpath = realpath($path . "/" . $name);
        }
        else
        {
            $realpath 		= $last_res["realpath"];
            $source_path	= $last_res["source_path"];
        }

       /* if ( (substr($realpath, 0, strlen(FF_DISK_PATH)) != FF_DISK_PATH) && (substr($realpath, 0, strlen(__TOP_DIR__)) != __TOP_DIR__))
            ffErrorHandler::raise("ffPage: widget path must be within FF_DISK_PATH", E_USER_ERROR, $this, get_defined_vars());
        else*/if (!is_dir($realpath) || !is_file($realpath . "/ffWidget." . FF_PHP_EXT))
            ffErrorHandler::raise("ffPage: widget not found", E_USER_ERROR, $this, get_defined_vars());

        require_once($realpath . "/ffWidget." . FF_PHP_EXT);

        $widget_name = "ffWidget_" . $name;
        $temp = new $widget_name($this, $source_path);

        $this->widgets[$name] = $temp;
        if (is_array($this->widgets[$name]->widget_deps) && count($this->widgets[$name]->widget_deps))
        {
            foreach ($this->widgets[$name]->widget_deps as $key => $value)
            {
                $this->widgetLoad($value["name"], $value["path"], $value["ref"]);
            }
            reset($this->widgets[$name]->widget_deps);
        }

        return true;
    }

    /**
     * elabora le widget globali caricate
     */
    function widgetsProcess()
    {
        if (is_array($this->widgets) && count($this->widgets))
        {
            foreach ($this->widgets as $key => $value)
            {
                $this->tpl[0]->set_var("WidgetsContent", $this->widgets[$key]->process_headers());
                $this->tpl[0]->parse("SectWidgetsHeaders", true);
                $this->tpl[0]->set_var("WidgetsContent", $this->widgets[$key]->process_footers());
                $this->tpl[0]->parse("SectWidgetsFooters", true);
            }
            reset ($this->widgets);
        }
        else
        {
            $this->tpl[0]->set_var("SectWidgetsHeaders", "");
            $this->tpl[0]->set_var("SectWidgetsFooters", "");
        }
    }

    /**
     * elabora le widget per il componente specifico
     * @param String $id
     * @return String
     */
    function componentWidgetsProcess($id)
    {
        $ret = array(
            "headers" => ""
        , "footers" => ""
        );

        if (is_array($this->widgets) && count($this->widgets))
        {
            foreach ($this->widgets as $key => $item)
            {
                $ret["headers"] .= $this->widgets[$key]->get_component_headers($id);
                $ret["footers"] .= $this->widgets[$key]->get_component_footers($id);
            }
            reset($this->widgets);
        }

        return $ret;
    }

    /**
     * aggiunge una variabile all'elenco dei globali utilizzati dalla pagina
     * @param String $name
     * @param mixed $value
     * @param boolean $display_hidden se deve essere aggiunto ai campi hidden
     */
    function register_globals($name, $value = null, $display_hidden = true)
    {
        $this->globals[$name]["value"] 			= $value;
        $this->globals[$name]["display_hidden"] = $display_hidden;
    }

    /**
     * Recupera il valore di un parametro globale
     * @param String $name
     * @return mixed
     */
    function retrieve_global($name)
    {
        if (!isset($this->globals[$name]))
            ffErrorHandler::raise("Global variable not declared", E_USER_ERROR, $this, get_defined_vars());

        if ($this->globals[$name]["value"] === null)
            return $this->retrieve_param(null, $name);
        else
            return $this->globals[$name]["value"];
    }

    /**
     * recupera tutti i parametri globali sotto forma di query string
     * @return String
     */
    function get_globals($excludelist = null)
    {
        if ($excludelist !== null)
        {
            $excludearray = explode(",", $excludelist);
        }

        $params = "";
        if (is_array($this->globals) && count($this->globals))
        {
            foreach ($this->globals as $key => $value)
            {
                if (is_array($excludearray) && array_search($key, $excludearray) !== false)
                    continue;

                $keyval = $this->retrieve_global($key);
                //**************************************************************
                //ADDED-ATS 10/12/2018 a seguito dell'utilizzo dei parametri globali anche per il salvataggio di oggetti
                //viene adattato il metodo per gestirli. In caso il parametro sia un oggetto viene usato il suo id come parametro
                /*if ($keyval !== null)
                    $params .= $key . "=" . urlencode($keyval) . "&";*/
                if ($keyval !== null) {
                    if (is_object($keyval) && isset($keyval->id)){
                        $params .= $key . "=" . urlencode($keyval->id) . "&";
                    }
                    else {
                        $params .= $key . "=" . urlencode($keyval) . "&";
                    }
                }
                //**************************************************************
            }
            reset($this->globals);
        }
        return $params;
    }

    function struct_process()
    {
        $rc = 0;
        $rc += $this->struct_process_fields($this->fields);

        foreach ($this->struct_properties as $key => $value)
        {
            $this->tpl[0]->set_var("prop_name",		$key);
            if (is_string($value))
                $this->tpl[0]->set_var("prop_value",	'"' . $value . '"');
            else
                $this->tpl[0]->set_var("prop_value",	$value);
            $this->tpl[0]->parse("SectFFProperty",	true);
        }

        foreach ($this->components as $key => $value)
        {
            if ($this->getXHRComponent() && $this->getXHRComponent() !== $value->getIDIF())
                continue;

            $this->tpl[0]->set_var("SectFFObjResource", "");
            $this->tpl[0]->set_var("SectFFObjProperty", "");
            $this->tpl[0]->set_var("SectFFObjFld", "");

            $rc++;
            $this->tpl[0]->set_var("objid", $value->getIDIF());
            $this->tpl[0]->set_var("type", substr(get_parent_class($value), 0, strpos(get_parent_class($value), "_")));

            if ($this->isXHR())
            {
                $this->tpl[0]->set_var("prop_name",		"url");
                if (isset($_REQUEST["__FORCE_XHR__"]))
                    $this->tpl[0]->set_var("prop_value",	'"http' . ($_SERVER["HTTPS"] ? "s" : "") . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '"');
                else
                    $this->tpl[0]->set_var("prop_value",	'"' . $_SERVER["REQUEST_URI"] . '"');
                $this->tpl[0]->parse("SectFFObjProperty",	true);
            }

            if (is_array($this->components[$key]->resources) && count($this->components[$key]->resources))
            {
                $i = 0;
                foreach ($this->components[$key]->resources as $subkey => $subvalue)
                {
                    $i++;
                    if ($i > 1)
                        $this->tpl[0]->set_var("resource", ', "' . $subvalue . '"');
                    else
                        $this->tpl[0]->set_var("resource", '"' . $subvalue . '"');
                    $this->tpl[0]->parse("SectFFObjResource", true);
                }
                reset($this->components[$key]->resources);
            }

            if (is_array($this->components[$key]->processed_widgets) && count($this->components[$key]->processed_widgets))
            {
                $this->tpl[0]->set_var("prop_name",	"widgets");
                $widgets = "[";
                $i = 0;
                foreach ($this->components[$key]->processed_widgets as $subkey => $subvalue)
                {
                    $i++;
                    if ($i > 1)
                        $widgets .= ", ";
                    $widgets .= "{'id' : '" . $subkey . "', 'type' : '" . $subvalue . "'}";
                }
                reset($this->components[$key]->processed_widgets);
                $widgets .= "]";
                $this->tpl[0]->set_var("prop_value", $widgets);
                $this->tpl[0]->parse("SectFFObjProperty",	true);
            }

            switch (get_parent_class($value))
            {
                case "ffGrid_base":
                    $rc += $this->struct_process_fields($value->key_fields, $value->getIDIF(), "", "key");
                    $rc += $this->struct_process_fields($value->grid_fields, $value->getIDIF(), "", "grid");
                    $rc += $this->struct_process_fields($value->search_fields, $value->getIDIF(), "_src", "search");
                    break;

                case "ffRecord_base":
                    $rc += $this->struct_process_fields($value->key_fields, $value->getIDIF(), "", "key");
                    $rc += $this->struct_process_fields($value->form_fields, $value->getIDIF(), "", "form");
                    break;

                case "ffDetails_base":
                    $rc += $this->struct_process_fields($value->key_fields, $value->getIDIF(), "", "key");
                    $rc += $this->struct_process_fields($value->form_fields, $value->getIDIF(), "", "form");
                    $this->tpl[0]->set_var("prop_name",	"main_record");
                    $this->tpl[0]->set_var("prop_value", '"' . $value->main_record[0]->getIDIF() . '"');
                    $this->tpl[0]->parse("SectFFObjProperty",	true);
                    break;
            }

            $rc += $value->structProcess($this->tpl[0]);

            $this->tpl[0]->parse("SectFFObj", true);
        }
        reset($this->components);

        if ($rc)
        {
            $this->tpl[0]->parse("SectFFStruct", false);
            return true;
        }
        else
            return false;
    }

    /**
     * Elabora i fields aggiunti ad una pagina all'interno della struttura javascript degli oggetti
     */
    function struct_process_fields($fields, $component = null, $suffix = "", $type = "")
    {
        if (is_array($fields) && count($fields))
        {
            foreach ($fields as $key => $field)
            {
                $this->tpl[0]->set_var("fieldid", $key . $suffix);

                //if ($component !== null && $field->parent[0]->processed_widgets[$component . "_" . $key . $suffix] == $field->widget)
                $this->tpl[0]->set_var("widget", $field->widget);
                /*else
                    $this->tpl[0]->set_var("widget", "");*/

                $this->tpl[0]->set_var("fldtype", $type);

                $this->tpl[0]->set_var("SectFFFldResource", "");
                if (is_array($field->resources) && count($field->resources))
                {
                    $i = 0;
                    foreach ($field->resources as $subkey => $subvalue)
                    {
                        $i++;
                        if ($i > 1)
                            $this->tpl[0]->set_var("resource", ', "' . $subvalue . '"');
                        else
                            $this->tpl[0]->set_var("resource", '"' . $subvalue . '"');
                        $this->tpl[0]->parse("SectFFFldResource", true);
                    }
                    reset($field->resources);
                }

                if ($component === null)
                    $this->tpl[0]->parse("SectFFFld", true);
                else
                    $this->tpl[0]->parse("SectFFObjFld", true);
            }
            return count($fields);
        }
    }
}
