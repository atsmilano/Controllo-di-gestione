<?php
namespace core;

class Modulo
{
    public $id;
    public $dir_path;
    public $site_path;
    public $ordine_caricamento;
    public $anno_inizio;
    public $anno_fine;
    public $dipendenze_moduli = array();
    public $hide;
    public $module_theme_dir;
    public $module_theme_path;
    public $module_theme_full_path;
    public $module_css_path;
    public $module_css_dir;
    private $env_constants = array();       

    public function __construct($directory)
    {
        //viene recuperato l'xml da directory		
        $mod_config_file = MODULES_DISK_PATH . $directory . DIRECTORY_SEPARATOR . MODULES_CONFIG_FILE;
        if (file_exists($mod_config_file)) {
            $config_data = simplexml_load_file($mod_config_file);
            if ((int) $config_data->id > 0) {
                $this->id = (int) $config_data->id;
            } else {
                throw new Exception("Errore in '" . $mod_config_file . "': ID modulo errato.");
            }
            $this->dir_path = $directory;
            $this->site_path = str_replace(DIRECTORY_SEPARATOR, "/", $directory);
            $this->ordine_caricamento = (int) $config_data->ordine_caricamento;

            $this->module_theme_dir = MODULES_DISK_PATH . $this->dir_path . DIRECTORY_SEPARATOR . MODULES_THEME_DIR;
            $this->module_theme_path = $this->site_path . "/" . MODULES_THEME_DIR;
            $this->module_theme_full_path = FF_SITE_PATH . "/" . MODULES_DIR . $this->module_theme_path;
            $this->module_css_dir = MODULES_DISK_PATH . $this->dir_path . DIRECTORY_SEPARATOR . MODULES_CSS_DIR;
            $this->module_css_path = $this->site_path . "/" . MODULES_CSS_PATH;
          
            //se è presente una configurazione specifica per la distribuzione viene utilizzata quella altrimenti quella generica
            $env = FF_ENV;
            //viene verificato che esistano configurazione specifiche per l'enviroment
            if (defined("MODULI_CONF")) {
                foreach (MODULI_CONF as $modulo_conf) {
                    if ($this->id == $modulo_conf["id_modulo"]) {
                        if (isset($modulo_conf["anno_inizio"])) {
                            $anno_inizio = (int) $modulo_conf["anno_inizio"];
                        }
                        if (isset($modulo_conf["anno_fine"])) {
                            $anno_fine = (int) $modulo_conf["anno_fine"];
                        }
                        //impostazione dei parametri dell'enviroment          
                        foreach ($modulo_conf["constants"] as $constant => $value) {
                            $this->env_constants[$constant] = $value;
                        }
                        break;
                    }
                }
            }
            //in caso non ci siano configurazioni specifiche vengono usate quelle generiche del modulo
            if (!isset($anno_inizio) || !isset($anno_fine)) {
                $anno_inizio = (int) $config_data->attivazione->anno_inizio;
                $anno_fine = $config_data->attivazione->anno_fine;
            }
            
            $this->anno_inizio = $anno_inizio;
            if ((int) $anno_fine !== 0) {
                $this->anno_fine = (int) $anno_fine;
            } else {
                $this->anno_fine = null;
            }    
        } else {
            throw new Exception("File '" . $mod_config_file . "' non trovato.");
        }
    }
    
    public function getEnvConstants() {
        return $this->env_constants;
    }

    //vengono restituiti i moduli attivi in un anno di budget
    public static function getActiveModulesFromDisk(\AnnoBudget $anno_selezionato = null)
    {
        $di = new \RecursiveDirectoryIterator(MODULES_DISK_PATH);        
        foreach (new \RecursiveIteratorIterator($di) as $filename => $file) {
            if (substr($filename, -strlen(MODULES_CONFIG_FILE)) == MODULES_CONFIG_FILE || substr($filename, -strlen(MODULES_COMMON_FILE)) == MODULES_COMMON_FILE) {
                //estrazione del nome del modulo
                $module_path_parts = explode(DIRECTORY_SEPARATOR, dirname($filename));
                $module_i = null;
                for ($j = 0; $j < count($module_path_parts); $j++) {
                    $module_dir .= DIRECTORY_SEPARATOR . $module_path_parts[$j];
                    if ($module_path_parts[$j] == MODULES_DIR)
                        $module_dir = "";
                }
                //se il modulo è già  stato salvato nell'array viene aggiornata solamente la presenza del file corrente
                for ($i = 0; $i < count($modules); $i++) {
                    if ($modules[$i]["dir"] == $module_dir) {
                        $module_i = $i;
                    }
                }
                //se il modulo ancora non esiste viene aggiunto
                if ($module_i === null) {
                    $modules[] = array("dir" => $module_dir,
                        "config_file" => null,
                        "common_file" => null
                    );
                    $module_i = count($modules) - 1;
                }
                //viene aggiornato l'array in base al file trovato
                if (substr($filename, -strlen(MODULES_CONFIG_FILE)) == MODULES_CONFIG_FILE) {
                    $modules[$module_i]["config_file"] = true;
                }
                if (substr($filename, -strlen(MODULES_COMMON_FILE)) == MODULES_COMMON_FILE) {
                    $modules[$module_i]["common_file"] = true;
                }
            }
        }

        //validazione e salvataggio dei dati
        $active_modules = array();
        foreach ($modules as $module) {
            //la presenza dei file mod_config.xml e common.php in una sottocartella la rende un modulo valido
            if ($module["config_file"] == true && $module["common_file"]) {
                //viene creato l'oggetto modulo per ogni modulo valido e attivo nell'anno
                try {
                    $cur_module = new \core\Modulo($module["dir"]);
                    if ($anno_selezionato == null || ($cur_module->anno_inizio <= $anno_selezionato->descrizione && ($cur_module->anno_fine == 0 || $cur_module->anno_fine >= $anno_selezionato->descrizione))) {
                        $active_modules[] = $cur_module;
                    }
                } catch (Exception $ex) {
                    ffErrorHandler::raise($ex->getMessage());
                }
            }
        }

        unset($modules);
        //verifiche sui moduli (ID univoci e presenza dipendenze)
        //verifica sull'univocità  dell'ID del modulo
        $found_id = array();
        foreach ($active_modules as $module) {
            foreach ($found_id as $comp_id) {
                if ($module->id == $comp_id) {
                    ffErrorHandler::raise("Errore: gli id dei moduli attivi devono risultare univoci.");
                }
            }
            $found_id[] = $module->id;
        }

        unset($found_id);
        usort($active_modules, "moduleCmp");

        return $active_modules;
    }

    //viene identificato il modulo corrente dal percorso della pagina
    public static function getCurrentModule($path = null, AnnoBudget $anno_selezionato = null)
    {
        $cm = \Cm::getInstance();
        if ($path == null) {
            $path = parse_url($_SERVER["REQUEST_URI"])["path"];
        }
        if ($anno_selezionato == null) {
            $anno_selezionato = $cm->oPage->globals["anno"]["value"];
        }
        //vengono estratti tutti i moduli validi dalla directory dei moduli e viene considerato il percorso
        //più lungo che continee il percorso corrente passato (per evitare corrispondenze su moduli nidificati)
        $found = null;
        foreach ($cm->oPage->globals["modules"]["value"] as $module) {
            $strpos = strpos($path, $module->site_path);
            if ($strpos !== false) {
                if (strlen($found->site_path) < strlen($module->site_path)) {
                    $found = $module;
                }
            }
        }
        if ($found !== null) {
            return $found;
        } else {
            return null;
        }
    }

    //viene recuperato il percorso di menu corrente per l'url
    public static function getCurrentModuleMenuItem($url, $privilegesCheck = true)
    {
        $cm = \Cm::getInstance();
        $found = array();
        $path = self::parseModuleUrl($url);

        foreach ($cm->modules["restricted"]["menu"] as $key => $menu_child) {
            $found = array_merge($found, self::matchWithPath($menu_child, $path, $key));

            foreach ($menu_child["elements"] as $subkey => $menu_sub_element) {
                $found = array_merge($found, self::matchWithPath($menu_sub_element, $path, $key, $subkey));
            }
        }

        $return = null;

        //Si itera tra tutte le corrispondenze per trovare quella con il path più lungo, che identifica il percorso con un livello di corrispondenza più alto
        foreach ($found as $item) {
            $item_path = self::parseModuleUrl($item["path"]);
            $return_path = self::parseModuleUrl($return["path"]);

            if (strlen($return_path) < strlen($item_path)) {
                $return = $item;
            } elseif ($return_path == $item_path && !$privilegesCheck) {
                $query_string = parse_url($url)["query"];
                //viene costruito un array associativo contenente i parametri passati in get
                parse_str($query_string, $parsed_query_string);

                //Se la chiave menu_key esiste e coincide con la key del menu, viene selezionato il modulo
                if (array_key_exists("menu_key", $parsed_query_string) && $parsed_query_string["menu_key"] == $item["key"]) {
                    $return = $item;
                }
            }
        }

        //Inizializzazione della class univoca alla sola voce trovata per selezione della voce di menu
        //per estendere inizializzazione class a tutte le voci di menu, vedere commento in funzione matchWithPath()
        $return['class'] = self::initializeUniqueClass($return['class'], $return['key'], $return['subkey']);

        return $return;
    }

    //Settaggio della class univoca rispettando il formato  "menu-key" o "menu-key-subkey" a seconda del caso
    //class string class attuale
    //$key string chiave dell'elemento nell'array delle voci di menu
    //$subkey string sottochiave dell'elemento nell'array delle voci di menu
    public static function initializeUniqueClass($class, $key, $subkey)
    {
        $cm = \Cm::getInstance();
        $uniqueKeyClass = strlen($class) > 0 ? " " : "";
        if (isset($subkey)) {
            $uniqueKeyClass .= "menu-" . $key . "-" . $subkey;
            $cm->modules['restricted']['menu'][$key]['elements'][$subkey]['class'] .= $uniqueKeyClass;
            return $cm->modules['restricted']['menu'][$key]['elements'][$subkey]['class'];
        } else {
            $uniqueKeyClass .= "menu-" . $key;
            $cm->modules['restricted']['menu'][$key]['class'] .= $uniqueKeyClass;
            return $cm->modules['restricted']['menu'][$key]['class'];
        }
    }

    //Verifica corrispondenza http request con voce di menu
    public static function matchWithPath($menu_element, $path, $key, $subkey = null)
    {
        $menu_element_path = self::parseModuleUrl($menu_element["path"]);
        $menu_element["key"] = $key;
        $menu_element["subkey"] = $subkey;

        //viene verificato che la voce di menu abbia una corrispondenza con l'url
        $found = array();
        if (strpos($path, $menu_element_path) !== false) {
            $found[] = $menu_element;
        }

        //viene verificato se nelle action dei menu c'è una corrispondenza con l'URL
        $action_element_match = self::checkActions($menu_element, $path);
        if ($action_element_match != null) {
            $found = array_merge($found, $action_element_match);
        }
        return $found;
    }

    public static function parseModuleUrl($url)
    {
        $parsedUrl = parse_url($url)["path"];
        return rtrim($parsedUrl, "/");
    }

    //viene verificato che un percorso corrisponda ad una action definita per il menu
    public static function checkActions($menu_element, $path)
    {
        $found = null;
        foreach ($menu_element["actions"] as $actionKey => $action) {
            $action["path"] = self::parseModuleUrl($action["path"]);
            if (strpos($path, $action["path"]) !== false) {
                $action["class"] = $menu_element["class"];
                $action["key"] = $menu_element["key"];
                $action["subkey"] = $menu_element["subkey"];
                $found[] = $action;
            }
        }
        return $found;
    }

    //caricamento dei file css di un modulo specifico
    public static function loadCss($currentModule)
    {
        $cm = \Cm::getInstance();
        $n = 0;
        //regola in htaccess
        foreach (glob($currentModule->module_css_dir . DIRECTORY_SEPARATOR . "*.css") as $filename) {
            $css_path = explode(DIRECTORY_SEPARATOR, realpath($filename));
            $css_file = array_pop($css_path);
            $cm->oPage->tplAddCss($currentModule->id . "_" . $n, array(
                "path" => "/" . MODULES_DIR . $currentModule->module_css_path,
                "file" => $css_file,
            ));

            $n++;
        }
    }

}
