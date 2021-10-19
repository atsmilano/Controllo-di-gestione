<?php
define("MOD_SECURITY_PACKAGE_PUBLIC", 0);
define("MOD_SECURITY_PACKAGE_PRIVATE", 1);

define("MOD_SEC_CRYPT_CONCAT_PREFIX", "#CRBEG");
define("MOD_SEC_CRYPT_CONCAT_SUFFIX", "CREND#");

define("MOD_SEC_ERROR_LOGIN_DOMAIN_NOT_FOUND", "login_domain_not_found");
define("MOD_SEC_ERROR_LOGIN_TEMP_PASSWORD_EXPIRED", "login_temp_password_expired");
define("MOD_SEC_ERROR_LOGIN_WRONG_USER_OR_PASSWORD", "login_wrong_user_or_password");
define("MOD_SEC_ERROR_LOGIN_USER_NOT_ACTIVE", "login_user_not_active");
define("MOD_SEC_ERROR_LOGIN_USER_NOT_FOUND", "login_wrong_user_not_found");
define("MOD_SEC_ERROR_LOGIN_FILL_ALL_FIELDS", "login_fill_all_fields");

$cm = cm::getInstance();

$cm->addEvent("on_load_module", "mod_security_cm_on_load_module");
if (isset($cm->modules["restricted"]["events"]))
	$cm->modules["restricted"]["events"]->addEvent("on_layout_process", "mod_security_cm_on_layout_process");

if (MOD_SEC_SOCIAL_GOOGLE)
	require("Google/Service/Oauth2.php");

if (MOD_SEC_CSRF_PROTECTION)
{
	$ff = ffGlobals::getInstance("ff");

	$res = $ff->events->addEvent("onRedirect", "mod_sec_csrf_onRedirect");
}

if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB) {
	//cm::_addEvent("showfiles_before_parsing_path", "mod_sec_showfiles_before_parsing_path");
    $ff = ffGlobals::getInstance("ff");
    if($ff->showfiles_events)
        $ff->showfiles_events->addEvent("before_parsing_path", "mod_sec_showfiles_before_parsing_path");
}

$tmp = cm_confCascadeFind(FF_DISK_PATH, "", "mod_security.xml");
if (is_file($tmp))
	mod_security_load_config($tmp);

mod_security_load_config(cm_confCascadeFind(CM_ROOT . "/conf", "/cm", "mod_security.xml"));

cm::_addEvent("jsonParse", "mod_security_jsonParse");

if (MOD_SEC_MULTIDOMAIN)
	ffPage::addEvent("on_factory_done", "mod_security_ffPage_set_events");

/////////////////////////////////////////////////////////////////////////////////////////////////////////
// FUNCTIONS
/////////////////////////////////////////////////////////////////////////////////////////////////////////

function mod_security_jsonParse(&$arData, &$out, &$add_newline, &$standard_encode, &$standard_opts)
{
	if (!is_array($arData))
		ffErrorHandler::raise ("CRITICAL ERROR", E_USER_ERROR, null, get_defined_vars ());
	
	$cm = cm::getInstance();

	$mod_sec_login = $cm->router->getRuleById("mod_sec_login");
		
	if (mod_security_check_session(false, null, true) && mod_security_check_session(false))
	{
		$arData = array_replace_recursive($arData, array(
			"modules" => array(
				"security" => array(
					"loggedin" => true
					, "session_name" => session_name()
					, "session_id" => session_id()
					, "UserNID" => get_session("UserNID")
				)
			)
		));
	}
	else
	{
		$arData = array_replace_recursive($arData, array(
			"modules" => array(
				"security" => array(
					"loggedin" => false
					, "session_name" => session_name()
				)
			)
		));
		
	}
	return;
}

function mod_security_ffPage_set_events ($oPage)
{
	//die("ok");
	$oPage->addEvent("on_tpl_layer_loaded", "mod_security_DOMAINS_on_tpl_layer_loaded", ffEvent::PRIORITY_LOW);
	if (intval($_REQUEST["accounts"]) && mod_security_is_admin())
	{
		if (defined("MOD_SEC_NOACCOUNTSCOMBO"))
			$oPage->register_globals("accounts", $_REQUEST["accounts"]);
		else
			$oPage->register_globals("accounts", $_REQUEST["accounts"], false);
	}
}

function mod_sec_csrf_onRedirect($destination, $http_response_code, $add_params)
{
	if (get_session("__CSRF_REGENERATE__"))
	{
		unset_session("__CSRF_REGENERATE__");
		$destination = ffCommon_url_change_param($destination, MOD_SEC_CSRF_PROTECTION_PARAM, get_session("__FF_SESSION__"));
	}
	return null;
}

function mod_sec_showfiles_before_parsing_path($path_temp, $path_saved, $params, $db)
{
	if (isset($params["ID_domains"]))
	{
		$db = mod_security_get_db_by_domain($params["ID_domains"]);
	}
}

function mod_security_cm_on_layout_process()
{
	$cm = cm::getInstance();
	if (isset($cm->oPage->sections["accountpanel"]))
		$cm->oPage->sections["accountpanel"]["events"]->addEvent("on_process", "mod_security_cm_on_load_account");
	if (isset($cm->oPage->sections["account"]))
		$cm->oPage->sections["account"]["events"]->addEvent("on_process", "mod_security_cm_on_load_account");
    if (isset($cm->oPage->sections["lang"]))
		$cm->oPage->sections["lang"]["events"]->addEvent("on_process", "mod_security_cm_on_load_lang");
	if (isset($cm->oPage->sections["brand"]))
		$cm->oPage->sections["brand"]["events"]->addEvent("on_process", "mod_security_cm_on_load_brand");
}

function mod_security_cm_on_load_module($cm, $mod)
{
	$include_script_path_tmp = FF_DISK_PATH . "/conf/contents";
	$include_script_path_info = $cm->path_info;
	
	do
	{
		if (is_file($include_script_path_tmp . $include_script_path_info . "/mod_security.xml"))
		{
			$tmp = $include_script_path_tmp . $include_script_path_info . "/mod_security.xml";
			break;
		}

		if ($include_script_path_info == "/")
			break;
		
		$include_script_path_info = ffCommon_dirname($include_script_path_info);
	} while (true);
	
	if (is_file($tmp))
		mod_security_load_config($tmp);
	else
	{
		$tmp = cm_confCascadeFind(CM_MODULES_ROOT . "/" . $mod . "/conf", "/modules/" . $mod, "mod_security.xml");
		if (is_file($tmp))
			mod_security_load_config($tmp);
	}

	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
	{
		ffDB_Sql::addEvent("on_factory_done", "mod_security_set_external_db");
	}
}

function mod_security_set_external_db(ffDB_Sql $db)
{
	if (!defined("MOD_SECURITY_SESSION_STARTED"))
		return;

	$IDDomain = mod_security_get_domain();

	if ($IDDomain != 0)
	{
		$globals = ffGlobals::getInstance("mod_security");
		if (!isset($globals->domain_data[0]))
		{
			$tmpdb = new ffDB_Sql();
			$globals->domain_data[0] = $tmpdb->lookup(CM_TABLE_PREFIX . "mod_security_domains", "ID", $IDDomain, array("", "", "", ""), array("db_host" => "Text", "db_name" => "Text", "db_user" => "Text", "db_pass" => "Text"), null, true);
		}

		$db->host =		$globals->domain_data[0]["db_host"];
		$db->database =	$globals->domain_data[0]["db_name"];
		$db->user =		$globals->domain_data[0]["db_user"];
		$db->password =	$globals->domain_data[0]["db_pass"];
	}
}

function mod_security_load_config($file)
{
	if (!strlen($file))
		return;
	
	$cm = cm::getInstance();

	$xml = new SimpleXMLElement("file://" . $file, null, true);

	if (isset($xml->session) && count($xml->session->children()))
	{
		foreach ($xml->session->children() as $key => $value)
		{
			$attrs = $value->attributes();
			$path = (string)$attrs["path"];

			if (!strlen($path))
				ffErrorHandler::raise("mod_security: malformed xml (missing path parameter on session section)", E_USER_ERROR, null, get_defined_vars());

			$cm->modules["security"]["session_bypath"][$path] = $value;
		}
	}

	if (isset($xml->custom_events) && count($xml->custom_events->children()))
	{
		foreach ($xml->custom_events->children() as $key => $value)
		{
			$cm->modules["security"]["custom_events"][$key] = array();
			
			foreach ($value->children() as $subkey => $subvalue)
			{
				$attrs = $subvalue->attributes();
				$cm->modules["security"]["custom_events"][$key][$subkey] = (string)$attrs["func"];
			}
		}
	}
	
	if (isset($xml->auth) && count($xml->auth->children()))
	{
		foreach ($xml->auth->children() as $key => $value)
		{
			$attrs = $value->attributes();
			$path = (string)$attrs["path"];
			if (!strlen($path))
				ffErrorHandler::raise("mod_security: malformed xml (missing path parameter on auth section)", E_USER_ERROR, null, get_defined_vars());

			$cm->modules["security"]["auth_bypath"][$path] = $key;
		}
	}

	if (isset($xml->fields) && count($xml->fields->children()))
	{
		foreach ($xml->fields->children() as $key => $value)
		{
			$key = (string)$key;
			
			if (!isset($cm->modules["security"]["fields"][$key]))
			{
				$cm->modules["security"]["fields"][$key] = array();
				
				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;
					
					$cm->modules["security"]["fields"][$key][$subkey] = $subvalue;
				}
			}
		}
	}

	if (isset($xml->domains_fields) && count($xml->domains_fields->children()))
	{
		foreach ($xml->domains_fields->children() as $key => $value)
		{
			$key = (string)$key;

			if (!isset($cm->modules["security"]["domains_fields"][$key]))
			{
				$cm->modules["security"]["domains_fields"][$key] = array();

				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;

					$cm->modules["security"]["domains_fields"][$key][$subkey] = $subvalue;
				}
			}
		}
	}

	if (isset($xml->groups) && count($xml->groups->children()))
	{
		foreach ($xml->groups->children() as $key => $value)
		{
			$key = (string)$key;

			if (!isset($cm->modules["security"]["groups"][$key]))
			{
				$cm->modules["security"]["groups"][$key] = array();

				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;

					$cm->modules["security"]["groups"][$key][$subkey] = $subvalue;
				}
			}
		}
	}

	if (isset($xml->domains_groups) && count($xml->domains_groups->children()))
	{
		foreach ($xml->domains_groups->children() as $key => $value)
		{
			$key = (string)$key;

			if (!isset($cm->modules["security"]["domains_groups"][$key]))
			{
				$cm->modules["security"]["domains_groups"][$key] = array();

				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;

					$cm->modules["security"]["domains_groups"][$key][$subkey] = $subvalue;
				}
			}
		}
	}

	if (isset($xml->packages) && count($xml->packages->children()))
	{
		foreach ($xml->packages->children() as $key => $value)
		{
			$key = (string)$key;

			if (!isset($cm->modules["security"]["packages"][$key]))
			{
				$cm->modules["security"]["packages"][$key] = array();

				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;

					$cm->modules["security"]["packages"][$key][$subkey] = $subvalue;
				}
			}
		}
	}
	
	if (isset($xml->packages_groups) && count($xml->packages_groups->children()))
	{
		foreach ($xml->packages_groups->children() as $key => $value)
		{
			$key = (string)$key;

			if (!isset($cm->modules["security"]["packages_groups"][$key]))
			{
				$cm->modules["security"]["packages_groups"][$key] = array();

				$attrs = $value->attributes();
				foreach ($attrs as $subkey => $subvalue)
				{
					$subkey = (string)$subkey;
					$subvalue = (string)$subvalue;

					$cm->modules["security"]["packages_groups"][$key][$subkey] = $subvalue;
				}
			}
		}
	}
	
	// -----------------------
	//  profiling
	$cm->modules["security"]["profiling"] = array();
	
	if (isset($xml->profiling) && count($xml->profiling->children()))
	{
		// PATHS
		if (isset($xml->profiling->paths) && isset($xml->profiling->paths->element))
		{
			$tmp = new ffSerializable($xml->profiling);
			
			if (!is_array($tmp->paths->element))
				$cm->modules["security"]["profiling"]["paths"] = _mod_sec_profiling_get_paths(array($tmp->paths->element));
			else
				$cm->modules["security"]["profiling"]["paths"] = _mod_sec_profiling_get_paths($tmp->paths->element);
		}
		
		// STATIC PROFILES
		if (isset($xml->profiling->profiles) && count($xml->profiling->profiles->children()))
		{
			$cm->modules["security"]["profiling"]["profiles"] = array();
			foreach ($xml->profiling->profiles->children() as $key => $value)
			{
				$attrs = $value->attributes();
				$id = (string)$attrs["id"];
				$label = (string)$attrs["label"];
				if (isset($attrs["acl"]))
					$acl = (string)$attrs["acl"];
				else
					$acl = null;
				$cm->modules["security"]["profiling"]["profiles"][] = array(
					"id" => $id
					, "label" => $label
					, "acl" => $acl
				);
			}
		}
	}
}

function _mod_sec_profiling_get_paths($childrens, &$elements = null, $indent = 0)
{
	if ($elements === null)
		$elements = array();
	
	foreach ($childrens as $key => $value)
	{
		$path = $value->__attributes["path"];
		$label = $value->__attributes["label"];
		if (isset($value->__attributes["acl"]))
			$acl = $value->__attributes["acl"];
		else
			$acl = null;
		
		$elements[] = array(
			"path" => $path
			, "label" => $label
			, "acl" => $acl
			, "indent" => $indent
		);
		
		if (isset($value->element))
		{
			if (!is_array($value->element))
				_mod_sec_profiling_get_paths(array($value->element), $elements, $indent + 1);
			else
				_mod_sec_profiling_get_paths($value->element, $elements, $indent + 1);
		}
	}
	
	if ($indent == 0)
		return $elements;
}

function mod_security_getUserInfo($field, $ID_user = null, $db = null, $destroy_session = true)
{
	if ($ID_user === null)
		$ID_user = get_session("UserNID");
	return getUserInfo($ID_user, $field, $db, $destroy_session);
}

function getUserInfo($ID_user, $field, $db = null, $destroy_session = true)
{
	$cm = cm::getInstance();
	if ($db === null)
		$db = ffDB_Sql::factory();

	$options = mod_security_get_settings($cm->path_info);
	$sSQL = "SELECT 1 ";
	if (mod_security_is_default_field($field))
	{
		$sSQL .= ", " . $options["table_name"] . ".`" . $field . "`";
	}
	else if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
	{
		foreach ($cm->modules["security"]["fields"] as $key => $value)
		{
			$sSQL .= ", (SELECT 
												" . $options["table_dett_name"] . ".value
											FROM
												" . $options["table_dett_name"] . "
											WHERE
												" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
												AND " . $options["table_dett_name"] . ".field = " . $db->toSql($key) . "
									) AS `" . $key . "`
				";
		}
		reset($cm->modules["security"]["fields"]);
	}
	$sSQL .= "FROM
								" . $options["table_name"] . "
							WHERE
								" . $options["table_name"] . ".ID = " . $db->toSql($ID_user) . "
		";

	$db->query($sSQL);
	if ($db->nextRecord())
	{
		return $db->getField($field);
	}
	else if ($destroy_session)
    {
        mod_security_destroy_session(false);
        unset($_GET[session_name()], $_POST[session_name()], $_COOKIE[session_name()], $_REQUEST[session_name()]);
        //ffErrorHandler::raise("mod_security: User Not Found!!!", E_USER_ERROR, null, get_defined_vars());
    }
    return new ffData("");
}

function mod_security_setUserInfo($field, $value, $ID_user = null, $db = null)
{
	if ($ID_user === null)
		$ID_user = get_session("UserNID");
	return setUserInfo($ID_user, $field, $value, $db);
}

function setUserInfo($ID_user, $field, $value, $db = null)
{
	$cm = cm::getInstance();
	if ($db === null)
		$db = ffDB_Sql::factory();
	
	if (!isset($cm->modules["security"]["fields"]) || !count($cm->modules["security"]["fields"]) || !isset($cm->modules["security"]["fields"][$field]))
		ffErrorHandler::raise("mod_security: Field don't exists", E_USER_ERROR, null, get_defined_vars());

	$options = mod_security_get_settings($cm->path_info);

	if (mod_security_is_default_field($field))
	{
		$sSQL = "UPDATE 
						" . $options["table_name"] . "
					SET
						" . $options["table_name"] . ".`" . $field . "` = " . $db->toSql($value) . "
					WHERE
						" . $options["table_name"] . ".ID = " . $db->toSql($ID_user) . "
			";
		$db->execute($sSQL);
		if (!$db->affectedRows())
		{
			mod_security_destroy_session(true, $_SERVER["REQUEST_URI"]);
		}
	}
	else
	{
    	$sSQL = "SELECT ID
                        FROM " . $options["table_dett_name"] . " 
                        WHERE " . $options["table_dett_name"] . ".ID_users = " . $db->toSql($ID_user) . "
                            AND " . $options["table_dett_name"] . ".field = " . $db->toSql($field);
        $db->query($sSQL);
        if($db->nextRecord()) {
			$sSQL = "UPDATE " . $options["table_dett_name"] . " SET
                        " . $options["table_dett_name"] . ".value = " . $db->toSql($value) . "
                    WHERE " . $options["table_dett_name"] . ".ID_users = " . $db->toSql($ID_user) . "
                        AND " . $options["table_dett_name"] . ".field = " . $db->toSql($field);
			$db->execute($sSQL);
        } else {
            $sSQL = "INSERT INTO " . $options["table_dett_name"] . " (ID_users, field, value) VALUES (" . $db->toSql($ID_user) . ", " . $db->toSql($field) . ", " . $db->toSql($value) . ")";
            $db->execute($sSQL);
        }
	}
}

// Functions to handle unique session vars
function session_isset($param_name, $data_array = null)
{
	if ($data_array === null)
		$data_array = $_SESSION;
	
	$param_name = APPID . $param_name;
	if(isset($data_array[$param_name])) 
		return true;
	else
		return false;
}

function get_session($param_name, $data_array = null)
{
	if ($data_array === null)
		$data_array = $_SESSION;
	
	$param_name = APPID . $param_name;
	return $data_array[$param_name];
}

function set_session($param_name, $param_value)
{
	$param_name = APPID . $param_name;
	$_SESSION[$param_name] = $param_value;
}

function unset_session($param_name)
{
	$param_name = APPID . $param_name;
	unset($_SESSION[$param_name]);
}
/*
function mod_security_session_unset($param_name)
{
	$param_name = APPID . $param_name;
	unset($_SESSION[$param_name]);
}
*/

/* Check if a session is established and is valid.
 * if session is invalid or not exists and prompt_login is set to true,
 * you will be automatically redirected to the login page.
 */
function mod_security_check_session($prompt_login = true, $path = null, $just_exists = false, $only_check = false)
{
	if (!$just_exists && defined("MOD_SECURITY_SESSION_STARTED"))
		return true;                                
    
	$sessid = $_REQUEST[session_name()];
	if(!$sessid)
		$sessid = $_COOKIE[session_name()];
		
	$valid_session = false;
	$session_started = false;
	
/*		$tmp = exec("ls /tmp", $output);
		echo "<pre>";
		print_r($_REQUEST);
		print_r($output);
//		print "\n" . session_id();
		die("\n" . "sess_" . $sessid);
*/		
	$tmp_path = session_save_path();
    //**************************************************************************
    //ADDED-ATS
    if(!strlen($tmp_path)){
        $tmp_path = sys_get_temp_dir ();
    }
    //**************************************************************************
	if (substr($tmp_path, -1) !== "/")
			$tmp_path .= "/";

	if(!file_exists($tmp_path . "sess_" . $sessid))
	{
		if ($just_exists)
			return false;
		//die("Sessione Inesistente");
	} 
	else 
	{
		if ($just_exists)
			return true;
		
		$session_data = null; // when null, taken from session
		if ($only_check)
		{
			$session_data = Session::unserialize(file_get_contents($tmp_path . "sess_" . $sessid));
		}
		else
		{
			session_start();
			$session_started = true;
		}
		
		//if (!session_isset("ADDR") || !session_isset("HOST") || !session_isset("AGENT") || !session_isset("UserID"))
		if (!session_isset("__FF_SESSION__", $session_data))
		{
			//die("Sessione Corrotta");
		}
		elseif (!MOD_SEC_CSRF_PROTECTION || (get_session("__FF_SESSION__", $session_data) == $_REQUEST[MOD_SEC_CSRF_PROTECTION_PARAM]))
		{
			if (!$only_check)
			{
				if (MOD_SEC_CSRF_PROTECTION)
					cm::getInstance()->oPage->register_globals(MOD_SEC_CSRF_PROTECTION_PARAM, get_session("__FF_SESSION__", $session_data));
			}
			
			$valid_session = true;
		}
		else if (!$only_check)
		{
			// CSRF enabled and wrong key auth, avoid ret_url problem
			$_REQUEST["ret_url"] = "";
			$_SERVER["REQUEST_URI"] = (string)cm::getInstance()->processed_rule["rule"]->reverse;
			$_SERVER["QUERY_STRING"] = "";
		}
	}
	//ADDED-ATS MS LOGIN VERSIONE SENZA REDIRECT(03/12/2018)********************
    if ($valid_session == false) {
        if (USE_MS_ONLINE_LOGIN == true) {              
            session_start();
            $session_started = true;
            
            $require_token = true;
            //se è presente in sessione un access_token ne viene verificatra la scadenza 
            if (get_session('access_token')){                 
                $require_token = false;
                //recupero del token
                $token = get_session('access_token');                
                //verifica sulla scadenza del token
                //nel caso in cui il token sia scaduto viene richiesto un nuovo token                
                if ($token->expire_datetime - time() < 0) {                  
                    unset_session('access_token');
                    $require_token = true;
                }                                                  
                else {
                    //vengono recuperati i nomi dei campi utilizzati per il nome utente e per la matricola
                    if (defined("AD_USER_NAME_FIELD")) {
                        $user_name_field =  AD_USER_NAME_FIELD;
                    }
                    else {
                        ffErrorHandler::raise("Campo AD_USER_NAME_FIELD non definito.");
                    }
                    if (defined("AD_USER_MATRICOLA_FIELD")) {
                        $user_matricola_field = AD_USER_MATRICOLA_FIELD;
                    }
                    else {
                        ffErrorHandler::raise("Campo AD_USER_MATRICOLA_FIELD non definito.");
                    }
                    
                    
                    $user_error = false;
                    //Se ci si trova nel caso di azure ad (esposizione dell'api su AD_USERS_URL)
                    if (!defined("AD_USERS_URL")) {
                        ffErrorHandler::raise("Campo AD_USERS_URL non definito.");
                    }
                    
                    //verifica dellìutilizzo di api o di info nell'access token (Azure AD vs ADFS)
                    if (AD_USERS_URL !== false) {
                        //recupero del nome utente con verifica circa la presenza di errori
                        $user = apiRequest(AD_USERS_URL);                          
                        //se il token è scaduto o comunque viene prodotto errore elimino la sessione (il token poi verrà rinnovato)                        
                        if (isset($user->error)){
                            unset_session ('access_token');
                            ffErrorHandler::raise("AD Error: ".$user->error->message, E_USER_ERROR, null, get_defined_vars());
                            /*
                            $require_token = true;
                            $user_error = true;                            
                            */
                        }
                        else {
                            $db_username = $user->$user_name_field;
                            $user_matricola = $user->$user_matricola_field;
                        }
                        
                    }
                    //altrimenti si recuperano le informazioni dall'access token
                    else {
                        $acces_token_array = explode('.', $token->access_token);
                        $acces_token_json = json_decode(base64_decode($acces_token_array[1]), true);
                        $db_username = $acces_token_json[$user_name_field];
                        $user_matricola = $acces_token_json[$user_matricola_field];
                    }          
                    
                    //se non si sono verificati errori viene verificata l'abilitazione per l'accesso all'aplicativo
                    if ($user_error !== true) {                               
                        //verifica dell'abilitazione
                        $db = ffDb_Sql::factory();
                        $sql = "
                                SELECT 
                                    cm_mod_security_users.*
                                FROM
                                    cm_mod_security_users
                                WHERE
                                    cm_mod_security_users.username = " . $db->toSql($db_username) 
                                ;                               
                        $db->query($sql);
                        if (!$db->nextRecord()){
                            ffErrorHandler::raise("Utente '".$db_username."' non abilitato all'accesso.", E_USER_ERROR, null, get_defined_vars());                            
                        }                                   
                        mod_security_create_session($db_username, null, "", "", null, false, null, null, get_session('access_token'));
                        set_session('ms_user_matricola', $user_matricola);                        
                        $valid_session = true;
                    }                 
                }         
            }
            //nel caso in cui non sia definito l'access token o non sia valido viene richiesto un nuovo access token
            if ($require_token == true) {
                //se non è stato ricevuto un codice di autorizzazione viene effettuato un redirect alla pagina di accesso
                if (!isset($_GET['code'])) {
                    //generazione casuale dello stato e salvataggio come variabile di sessione
                    set_session('state', hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']));
                    unset_session($_SESSION['access_token']);

                    $params = array (
                                        'client_id' => OAUTH2_CLIENT_ID,
                                        'response_type' => 'code',
                                        'response_mode' => 'query',    
                                        'redirect_uri' => MS_LOGIN_REDIRECT_URL,
                                        'scope' => OAUTH2_SCOPE,                        
                                        'state' => get_session('state')
                                    );
                    header('Location: ' . AD_AUTH_URL . '?' . http_build_query($params));
                    die();                      
                }
                //se è stato ricevuto un codice di autorizzazione si procede con il recuperare l'access token
                else {
                    //se è presente il codice vengono verificati gli altri parametri. In caso di errore si ritorna alla pagina per riefettuare il login
                    if(!isset($_GET['state']) || get_session('state') != $_GET['state']) {
                        header('Location: ' . MS_LOGIN_REDIRECT_URL);
                        die();
                    }
                    $access_code = $_GET['code'];
                    //viene recuperato il token per l'accesso all'api
                    $params = array(
                                    'client_id' => OAUTH2_CLIENT_ID,
                                    'client_secret' => OAUTH2_CLIENT_SECRET,
                                    'redirect_uri' => MS_LOGIN_REDIRECT_URL,
                                    'grant_type' => 'authorization_code',
                                    'code' => $access_code
                                    );
                    $token = apiRequest(AD_TOKEN_URL, $params);                    
                    $token->expire_datetime = time() + $token->expires_in;              
                    set_session('access_token', $token);  
                    //il redirect viene effettuato per eliminare dai parametri in get il codice di autorizzazione
                    header('Location: ' . MS_LOGIN_REDIRECT_URL);
                    die();                
                }
            }
        }
    }
    //**************************************************************************       
	if ($valid_session) // step 1: verify user
	{
		$cm = cm::getInstance();
		
		$ID_domain = get_session("DomainID", $session_data);
		if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
			$db = mod_security_get_db_by_domain($ID_domain);
		else
			$db = mod_security_get_main_db();

		$options = mod_security_get_settings($cm->path_info);

		$sSQL = "SELECT
					" . $options["table_name"] . ".*
				FROM
					" . $options["table_name"] . "
				WHERE
					" . $options["table_name"] . ".ID = " . $db->toSql(get_session("UserNID", $session_data), "Number");
		if (MOD_SEC_EXCLUDE_SQL)
			$sSQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;

		$db->query($sSQL);
		if (!$db->nextRecord())
		{
			$valid_session = false;
		}
        else {
            if ($db->getField("status", "Number", true) !== 1) {
                ffErrorHandler::raise("Utente '" . $db->getField("username", "Text", true) . "' non attivo.", E_USER_ERROR, null, get_defined_vars());
            }
        }

		if (MOD_SEC_CRYPT && $valid_session) // check cryptography
		{
			$data = $db->record;

			$Vu = $data["crypt_vu"];
			$salt = hex2bin($data["crypt_su"]);
			$blockdata = hex2bin($data["crypt_eu"]);

			$hash = mod_sec_mykdf($_COOKIE["__FF_VU__"] . get_session("__FF_VU__", $session_data), $salt, 999);

			$Vu1 = substr($hash, 0, 32);
			if ($Vu1 !== $Vu)
				$valid_session = false;
			else
			{
				$Vu2 = substr($hash, 32);
				$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $Vu2, $blockdata, MCRYPT_MODE_CBC, $salt);
				$decrypted = rtrim($decrypted, "\0");

				$parts = explode("|", $decrypted);

				$globals = ffGlobals::getInstance("__mod_sec_crypt__");
				$globals->_crypt_Ku_ = hex2bin($parts[0]);
				$globals->_crypt_KSu_ = hex2bin($parts[1]);
			}
		}

		if($db->getField("password_used", "Number", true) == "1")
		{
			$redir_url = null;
			$changepass_rule = $cm->router->getRuleById("mod_sec_change_password");
			if ($changepass_rule)
				$redir_url = FF_SITE_PATH . (string)$cm->router->getRuleById("mod_sec_change_password")->reverse;

			if ($only_check)
				$valid_session = false;
			else if ($redir_url !== null && $_SERVER["REDIRECT_URL"] !== $redir_url)
				ffRedirect($redir_url . "?ret_url=" . rawurlencode($_SERVER["REQUEST_URI"]));
		}
	}

	if ($valid_session) // step 2: finalize session
	{
		if ($only_check)
			return true;
		
		define ("MOD_SECURITY_SESSION_STARTED", true);
		
		$cm->modules["security"]["events"]->doEvent("mod_security_on_check_session", array($prompt_login));
		
		return true;
	}
	else 
	{                
		if ($only_check)
			return false;
		
		if ($prompt_login)
		{
			if (strlen($sessid))
			{
				if (!$session_started)
					@session_start();					
				mod_security_destroy_session();
			}

			prompt_login();
		} 
		else
			return false;
	}
}

function mod_security_is_admin()
{
	if(get_session("DomainID") == 0)
		return true;
	else		
		return false;
}

// prompt a standard dialog with "Access Denied" message
function access_denied($confirmurl = "", $dlg_site_path = "")
{
	//ffErrorHandler::raise("access_denied", E_USER_ERROR, null, get_defined_vars());
	$cm = cm::getInstance();
	
	if (!strlen($confirmurl))
		$confirmurl = $_REQUEST["ret_url"];
	if (!strlen($confirmurl))
		$confirmurl = $_SERVER["HTTP_REFERER"];
	if (!strlen($confirmurl))
		$confirmurl = FF_SITE_PATH . "/" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);

	if (!strlen($dlg_site_path))
		$dlg_site_path = FF_SITE_PATH . "/dialog";

	ffDialog(false, "okonly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_accessdenied"), null, $confirmurl, $dlg_site_path);
}

// redirect to prompt login with proper vars selected
function prompt_login($ret_url = NULL, $login_url = NULL)
{
	$cm = cm::getInstance();

	if ($ret_url === NULL)
		$ret_url = $_SERVER["REQUEST_URI"];

	if ($cm->isXHR())
	{
		$cm->jsonAddResponse(array(
			"modules" => array(
				"security" => array(
					"prompt_login" => true
				)
			)
		));
		ffRedirect ($login_url ? $login_url : FF_SITE_PATH . mod_security_get_login_path());
	}
	else
	{
		if(strlen($login_url)) {
			$arrUrl = parse_url($login_url);
			if (isset($arrUrl["query"]))
				$arrUrl["query"] = rtrim($arrUrl["query"], "&");
			$login_url = $arrUrl["path"] . "?"
							. (strlen($arrUrl["query"]) ? $arrUrl["query"] . "&" : "")
							. "ret_url=" . rawurlencode($ret_url) 
							. (strlen($arrUrl["fragment"]) ? "#" . $arrUrl["fragment"] : "");
			ffRedirect($login_url);
		}
		else
			ffRedirect (FF_SITE_PATH . mod_security_get_login_path() . "/?ret_url=" . rawurlencode($ret_url));
	}
	exit;
}

function mod_security_get_login_path()
{
	$cm = cm::getInstance();

	$options = mod_security_get_settings($cm->path_info);

	if (isset($options["login_path"]))
	{
		return $options["login_path"];
	}
	else
	{
		$login_path = $cm->router->getRuleById("mod_sec_login");
		if (is_null($login_path))
			return "/login";
		else
			return (string)$login_path->reverse;
	}
}


function mod_security_get_locale($lang_default = null, $nocurrent = false) {
	$db = ffDB_Sql::factory();

	$locale = array();
	$locale["lang"] = array();
	
	$sSQL = "SELECT " . FF_PREFIX . "languages.* 
			FROM " . FF_PREFIX . "languages 
			WHERE " . FF_PREFIX . "languages.status > 0
			ORDER BY " . FF_PREFIX . "languages.description";
	$db->query($sSQL);
    if($db->nextRecord())
	{
		$arrLangKey = array();
		if($lang_default === null)
			$lang_default = $db->getField("code", "Text", true);

        do
		{
			$ID_lang = $db->getField("ID", "Number", true);
			$lang_code = $db->getField("code", "Text", true);
			
            $locale["lang"][$lang_code]["ID"] 										= $ID_lang;
            $locale["lang"][$lang_code]["tiny_code"] 								= $db->getField("tiny_code", "Text", true);
            $locale["lang"][$lang_code]["description"] 								= $db->getField("description", "Text", true);
            $locale["lang"][$lang_code]["stopwords"] 								= $db->getField("stopwords", "Text", true);
            $locale["lang"][$lang_code]["prefix"] 									= ($lang_code == $lang_default
													                                    ? ""
													                                    : "/" . $locale["lang"][$lang_code]["tiny_code"]
													                                );
			
			$locale["rev"]["lang"][$locale["lang"][$lang_code]["tiny_code"]] 		= $lang_code;
			
			if(!$nocurrent && $locale["ID_languages"] == $ID_lang)
			{
				$locale["lang"]["current"] 											= $locale["lang"][$lang_code];
				$locale["lang"]["current"]["code"] 									= $lang_code;
			}
			$arrLangKey[$ID_lang] 													= $lang_code;
		} while($db->nextRecord());
		
		if(count($arrLangKey)) {
			$locale["rev"]["key"] 													= $arrLangKey;

			$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "state.*
						, " . FF_PREFIX . "ip2nationCountries.country 		AS country
						, " . FF_PREFIX . "ip2nationCountries.iso_country 	AS country_iso
						, " . FF_PREFIX . "ip2nationCountries.code 			AS country_code
					FROM " . FF_SUPPORT_PREFIX . "state
						INNER JOIN " . FF_PREFIX . "ip2nationCountries ON " . FF_PREFIX . "ip2nationCountries.iso_country = " . FF_SUPPORT_PREFIX . "state.name 
					WHERE " . FF_SUPPORT_PREFIX . "state.ID_lang IN(" . $db->toSql(implode(",", array_keys($arrLangKey)), "Number") . ")";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$country_code 																			= $db->getField("country_code", "Text", true);
					
				    $locale["country"][$country_code]["ID"]													= $db->getField("ID", "Number", true);
				    $locale["country"][$country_code]["name"]												= $db->getField("country", "Text", true);
				    $locale["country"][$country_code]["iso"]												= $db->getField("country_iso", "Text", true);
				    $locale["country"][$country_code]["ID_lang"]											= $db->getField("ID_lang", "Number", true);
				    
				    $locale["rev"]["country"][$country_code] 												= $arrLangKey[$locale["country"][$country_code]["ID_lang"]];
				    $locale["lang"][$arrLangKey[$locale["country"][$country_code]["ID_lang"]]]["country"] 	= $country_code;
				} while($db->nextRecord());
			}
		}
	}
	
	if(!$nocurrent) {
		$sSQL = "SELECT " . FF_PREFIX . "ip2nation.country AS country_code
				FROM " . FF_PREFIX . "ip2nation
				WHERE " . FF_PREFIX . "ip2nation.ip < INET_ATON(" . $db->toSql($_SERVER["REMOTE_ADDR"]) . ")
				ORDER BY " . FF_PREFIX . "ip2nation.ip DESC
				LIMIT 0, 1";
		$db->query($sSQL);
		if($db->nextRecord())
		{
			$country_code = $db->getField("country_code", "Text", true);
			
		    $locale["country"]["current"]												= $locale["country"][$country_code];
		    $locale["country"]["current"]["code"]										= $country_code;

			if(isset($arrLangKey[$locale["country"]["current"]["ID_lang"]])) {
				$locale["lang"]["current"] 												= $locale["lang"][$arrLangKey[$locale["country"]["current"]["ID_lang"]]];
				$locale["lang"]["current"]["code"] 										= $arrLangKey[$locale["country"]["current"]["ID_lang"]];
			}
		}

		if(!array_key_exists("current", $locale["lang"]) && strlen($lang_default))
		{
			$locale["lang"]["current"] 													= $locale["lang"][$lang_default];
			$locale["lang"]["current"]["code"] 											= $lang_default;
		}
	}
	return $locale;
}
	
// create a session with proper vars
//ADDED-ATS aggiunti parametri , $LdapPwd = null, $ms_access_token = null
function mod_security_create_session($UserID = null, $UserNID = null, $Domain = "", $DomainID = "", $permanent_session = MOD_SECURITY_SESSION_PERMANENT, $disable_events = false, $cookiehash = null, $LdapPwd = null, $ms_access_token = null)
{
	$cm = cm::getInstance();

	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $DomainID)
		$db = mod_security_get_db_by_domain($DomainID);
	else
		$db = mod_security_get_main_db();

	$options = mod_security_get_settings($cm->path_info);
	
	if(!$disable_events)
		$cm->doEvent("mod_security_on_create_session", array($UserID, $UserNID, $Domain, $DomainID, $permanent_session));
	
	$old_session_id = session_id();

    mod_security_destroy_session(false);
	unset($_GET[session_name()], $_POST[session_name()], $_COOKIE[session_name()], $_REQUEST[session_name()]);

	if ($options["table_dett_name"] && ($UserID !== null || $UserNID !== null))
	{
		$sSQL = "SELECT
						" . $options["table_name"] . ".*
			";
		if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
		{
            $populate_insert_SQL = "";
			foreach ($cm->modules["security"]["fields"] as $key => $value)
			{
				if (mod_security_is_default_field($key))
					continue;
				
				$sSQL .= ", (SELECT
													" . $options["table_dett_name"] . ".value
												FROM
													" . $options["table_dett_name"] . "
												WHERE
													" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
													AND " . $options["table_dett_name"] . ".field = " . $db->toSql($key) . "
										) AS " . $key . "
					";

				$populate_insert_SQL .= ", '' AS " . $key;
			}
			reset($cm->modules["security"]["fields"]);
		}
		$sSQL .= "FROM
						" . $options["table_name"] . "
					WHERE
						(
			";

		if ($UserNID !== null)
			$sSQL .= " " . $options["table_name"] . ".ID = " . $db->toSql($UserNID) . " ";
		elseif ($UserID !== null)
		{
			if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
				$sSQL .= " " . $options["table_name"] . ".username = " . $db->toSql($UserID) . " ";
			if (MOD_SECURITY_LOGON_USERID == "both")
				$sSQL .= " OR ";
			if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")
				$sSQL .= " " . $options["table_name"] . ".email = " . $db->toSql($UserID) . " ";
		}

		$sSQL .= ")";

		if (MOD_SEC_EXCLUDE_SQL)
			$sSQL .= " AND `" . $options["table_name"] . "`.ID " . MOD_SEC_EXCLUDE_SQL;
		
		$db->query($sSQL);
		if (!$db->nextRecord())
			ffErrorHandler::raise("USER NOT FOUND!", E_USER_ERROR, null, get_defined_vars());
	}
	
	$tmp_user_data = $db->record;
	
	session_regenerate_id(true);
	session_start();
	
	$sessionName = session_name();
	$sessionCookie = session_get_cookie_params();
	if($permanent_session)
	{
		// TOCHECK, bisogna aggiungere qualche controllo in più, così è troppo semplicistico. Inoltre non tiene conto del destroy_session, della protezione CSRF e del CRYPT
		$long_time = time() + (60 * 60 * 24 * 365);
		setcookie($sessionName, session_id(), $long_time, $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
	} 
	else 
	{
		setcookie($sessionName, session_id(), $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);
	}
	
	if ($cookiehash !== null)
	{
		$p1 = substr($cookiehash, 0, 32);
		$p2 = substr($cookiehash, 32);
		
		setcookie("__FF_VU__", $p1, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
		set_session("__FF_VU__", $p2);
	}
	
	$_REQUEST[$sessionName] = session_id();
	
	set_session("__FF_SESSION__", uniqid(APPID, true));
	
	if (MOD_SEC_CSRF_PROTECTION)
		set_session("__CSRF_REGENERATE__", true);

	/**
	* Geolocalization user
	*/
	if(defined("MOD_SEC_ENABLE_GEOLOCALIZATION") && MOD_SEC_ENABLE_GEOLOCALIZATION)
		$user = mod_security_get_locale();
		
	$user["domain"] 		= $Domain;
	$user["ID_domain"] 		= $DomainID;
	$user["ID"]				= intval($tmp_user_data["ID"]);
	$user["username"]		= $tmp_user_data["username"];
	$user["level"]			= intval($tmp_user_data["level"]);
	$user["email"]			= $tmp_user_data["email"];
    $user["lastlogin"]		= $db->getField("lastlogin", "DateTime", true);

	if(defined("MOD_SEC_USER_AVATAR") && MOD_SEC_USER_AVATAR)
		$user["avatar"] = $tmp_user_data[MOD_SEC_USER_AVATAR];
	
	// TOCHECK: da spostare, probabilmente in un evento o da condizionare con costanti
	/*
	$user["ID_languages"]	= $db->getField("ID_languages", "Number", true);
	$user["username_slug"] 	= ($db->getField("username_slug", "Text", true, false)
								? $db->getField("username_slug", "Text", true)
								: ffCommon_url_rewrite($user["username"])
							);
	*/

    //ADDED-ATS*****************************************************************
    if (USE_MS_ONLINE_LOGIN == true){
		set_session("access_token", $ms_access_token);
	}    
    if (LDAP_SERVER !== false){
		set_session("LdapPwd", $LdapPwd);
	}
    //**************************************************************************
    
	if(defined("MOD_SEC_DEFAULT_FIELDS") && strlen(MOD_SEC_DEFAULT_FIELDS)) {
		$arrField = explode(",", MOD_SEC_DEFAULT_FIELDS);
		if(is_array($arrField) && count($arrField)) {
			foreach($arrField AS $key) {
				if($key == "ID" 
					|| $key == "ID_domain" 
					|| $key == "email" 
					|| $key == "username" 
					|| $key == "level"
					|| $key == "avatar"
					|| $key == "groups"
					|| $key == "password"
				)
					continue;

				if(isset($tmp_user_data[$key])) {
					if(defined("MOD_SEC_GROUPS") && MOD_SEC_GROUPS)
						$user[$key] = $tmp_user_data[$key];
					else
						set_session("user_" . $key, $tmp_user_data[$key]);
				}
			}
		}
	}

	if ($options["table_dett_name"])
	{
		if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
		{
			$arrFieldKey = array();
			foreach ($cm->modules["security"]["fields"] as $key => $value)
			{
				if (mod_security_is_default_field($key))
					continue;
					
				$arrFieldKey[] = $db->toSql($key);
			}
			
			if(is_array($arrFieldKey) && count($arrFieldKey))
			{
				$sSQL = "SELECT " . $options["table_dett_name"] . ".field
							, " . $options["table_dett_name"] . ".value
						FROM
							" . $options["table_dett_name"] . "
						WHERE
							" . $options["table_dett_name"] . ".ID_users = " . $db->toSql($user["ID"], "Number") . "
							AND " . $options["table_dett_name"] . ".field IN (" . implode(", ", $arrFieldKey) . ")";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						if(defined("MOD_SEC_GROUPS") && MOD_SEC_GROUPS)
							$user[$db->getField("field", "Text", true)] = $db->getField("value", "Text", true);
						else
							set_session("user_" . $db->getField("field", "Text", true), $db->getField("value", "Text", true));
					} while($db->nextRecord());
				}
			}
		}
	}
	
	/***
	* Advanced Group Alex
	*/
	if (defined("MOD_SEC_GROUPS") && MOD_SEC_GROUPS && $options["table_groups_name"])
	{
		$sSQL = "SELECT " . $options["table_groups_name"] . ".gid AS rel_gid
						, " . $options["table_groups_name"] . ".name AS gid_name
				 FROM " . $options["table_groups_rel_user"] . "
					INNER JOIN " . $options["table_groups_name"] . " ON " . $options["table_groups_name"] . ".gid = " . $options["table_groups_rel_user"] . ".gid
					 	OR " . $options["table_groups_name"] . ".gid = " . $db->toSql($user["primary_gid"], "Number") . "
				 WHERE " . $options["table_groups_rel_user"] . ".uid = " . $db->toSql($user["ID"], "Number") . " 
				 ORDER BY " . $options["table_groups_name"] . ".level DESC";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$user["groups"] = array();

			$user["primary_gid_default"] = $db->getField("rel_gid", "Number", true);
			$user["primary_gid_default_name"] = $db->getField("gid_name", "Text", true);
			do
			{
				$ID_group = $db->getField("rel_gid", "Number", true);
				$group_name = $db->getField("gid_name", "Text", true);
                if($ID_group > 0)
					$user["groups"][$group_name] = $ID_group;
				
				if($user["primary_gid"] == $ID_group) {					    
					$user["primary_gid_name"] = $group_name;

					$user["primary_gid_default"] = $user["primary_gid"];
					$user["primary_gid_default_name"] = $user["primary_gid_name"];
				}
			} while($db->nextRecord());
			
            if(!count($user["groups"]))
			{
                $user["groups"][MOD_SEC_GUEST_USER_NAME] = MOD_SEC_GUEST_USER_ID;
                $user["primary_gid_name"] = MOD_SEC_GUEST_USER_NAME;
            } 

            if(defined("SUPERADMIN_USERNAME") && $user["username"] == SUPERADMIN_USERNAME)
                $user["level"] = 3;

            $sSQL = "SELECT " . $options["table_groups_dett_name"] . ".*
                	FROM " . $options["table_groups_dett_name"] . "
                	WHERE " . $options["table_groups_dett_name"] . ".ID_groups = " . $db->toSql($user["primary_gid"], "Number") . "
                	ORDER BY " . $options["table_groups_dett_name"] . ".`order`, " . $options["table_groups_dett_name"] . ".field";
            $db->query($sSQL);
            if($db->nextRecord())
			{
                do {
                	$user["permissions_custom"][$db->getField("field", "Text", true)] = $db->getField("value", "Text", true);
				} while($db->nextRecord());
			}
		} 
		else 
		{
			mod_security_destroy_session(false);
			unset($_GET[session_name()], $_POST[session_name()], $_COOKIE[session_name()], $_REQUEST[session_name()]);
			access_denied();
		}
	} else {
		set_session("UserLang", $user["lang"]);	
		set_session("UserCountry", $user["country"]);		
	}
	
	if (MOD_SEC_ENABLE_TOKEN && $options["table_token"])
	{
        $sSQL = "SELECT 
					" . $options["table_token"] . ".*
                FROM 
					" . $options["table_token"] . "
                WHERE 
					" . $options["table_token"] . ".ID_user = " . $db->toSql($user["ID"], "Number") . "
                ORDER BY 
					" . $options["table_token"] . ".`type`";
        $db->query($sSQL);
        if($db->nextRecord())
		{
            do 
			{
                $user["token"][$db->getField("type", "Text", true)] = $db->getField("token", "Text", true);
			} while($db->nextRecord());
		}
	}

	if(is_array($user) && count($user))
		set_session("user_permission", $user);

    set_session("Domain"	        , $user["domain"]);
    set_session("DomainID"	    , $user["ID_domain"]);
    set_session("UserNID"	    , $user["ID"]);
    set_session("UserID"	        , $user["username"]);
    set_session("UserLevel"	    , $user["level"]);
    set_session("UserEmail"	    , $user["email"]);

	/**
	* Update LastLogin
	*/
	$sSQL = "UPDATE `" . $options["table_name"] . "` SET 
				lastlogin = " . $db->toSql(date("Y-m-d H:i:s", time()), "DateTime") . "
			WHERE  `" . $options["table_name"] . "`.ID = " . $db->toSql($user["ID"], "Number");
	$db->execute($sSQL);
	
	if (LOGIN_MULTIDOMAIN && $Domain != APPID)
		// NORMAL STUFFS
		setcookie("domain", $Domain, mktime(0, 0, 0, 1, 1, date("Y") + 1), FF_SITE_PATH, DOMAIN, $_SERVER["HTTPS"], true);
	
	if(!$disable_events)
		$cm->doEvent("mod_security_on_created_session", array($user, $old_session_id, $permanent_session));
		
	/*if (!defined("MOD_SECURITY_SESSION_STARTED")) 
		define ("MOD_SECURITY_SESSION_STARTED", true);*/
	
	return $user;
}

function mod_security_get_user_data($user_key, $ext = null)
{
	$db = mod_security_get_main_db();
	$options = mod_security_get_settings();

	if ($user_key !== null && $options["table_dett_name"])
	{
		$query["from"] 		= $options["table_name"];
		$query["select"] 	= array(
			"ID" 				=> "`" . $options["table_name"] . "`.`ID`"
		, "ID_domains" 		=> "`" . $options["table_name"] . "`.`ID_domains`"
		, "ID_languages" 	=> "`" . $options["table_name"] . "`.`ID_languages`"
		, "username" 		=> "`" . $options["table_name"] . "`.`username`"
		, "username_slug" 	=> "`" . $options["table_name"] . "`.`username_slug`"
		, "level" 			=> "`" . $options["table_name"] . "`.`level`"
		, "email" 			=> "`" . $options["table_name"] . "`.`email`"
		, "lastlogin" 		=> "`" . $options["table_name"] . "`.`lastlogin`"
		, "avatar"			=> (defined("MOD_SEC_USER_AVATAR") && MOD_SEC_USER_AVATAR
				? "`" . $options["table_name"] . "`.`avatar`"
				: "'' AS avatar"
			)
		);

		// defined("MOD_SEC_DEFAULT_FIELDS") && strlen(MOD_SEC_DEFAULT_FIELDS)
		if($ext["default"]) {
			if(is_array($ext["default"]) && count($ext["default"])) {
				foreach($ext["default"] AS $key) {
					if($query["select"][$key])
						continue;

					$query["select"][$key] = "`" . $options["table_name"] . "`.`" . $key. "`";
				}
			}
		}

		if (is_numeric($user_key) && $user_key > 0)
			$query["where"]["ID"] = "`" . $options["table_name"] . "`.`ID` = " . $db->toSql($user_key, "Number");
		else
		{
			if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
				$query["where"]["login"][] = "`" . $options["table_name"] . "`.`username` = " . $db->toSql($user_key);

			if (strpos($user_key, "@") !== false && (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email"))
				$query["where"]["login"][] = "`" . $options["table_name"] . "`.`email` = " . $db->toSql($user_key);

			$query["where"]["login"] = " (" . implode(" OR ", $query["where"]["login"]) . ") ";
		}

		if (MOD_SEC_EXCLUDE_SQL)
			$query["where"]["exclude"]  = "`" . $options["table_name"] . "`.`ID` " . MOD_SEC_EXCLUDE_SQL;

		$sSQL = "SELECT
					" . implode(",", $query["select"]) . "
				FROM
					" . $query["from"] . "
				WHERE
					" . implode(" AND ", $query["where"]) . "
				ORDER BY ID DESC";
		$db->query($sSQL);
		if ($db->nextRecord()) {
			$user = $db->record;
		}
	}

	if ($user["ID"])
	{
		if($ext["geolocalization"])
			$user = array_replace($user, mod_security_get_locale());

		//$cm->modules["security"]["fields"]
		if ($ext["custom"] && $options["table_dett_name"])
		{
			$arrFieldKey = null;
			if(is_array($ext["custom"]) && count($ext["custom"])) {
				foreach ($ext["custom"] as $key => $value) {
					if (mod_security_is_default_field($key))
						continue;

					$arrFieldKey[] = $db->toSql($key);
				}
			}
			if(is_array($arrFieldKey) && count($arrFieldKey))
			{
				$sSQL = "SELECT " . $options["table_dett_name"] . ".field
							, " . $options["table_dett_name"] . ".value
						FROM
							" . $options["table_dett_name"] . "
						WHERE
							" . $options["table_dett_name"] . ".ID_users = " . $db->getField($user["ID"], "Number")
					. (is_array($arrFieldKey) && count($arrFieldKey)
						? " AND " . $options["table_dett_name"] . ".field IN (" . implode(", ", $arrFieldKey) . ")"
						: ""
					);
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$user[$db->getField("field", "Text", true)] = $db->getField("value", "Text", true);
					} while($db->nextRecord());
				}
			}
		}

		//defined("MOD_SEC_GROUPS") && MOD_SEC_GROUPS
		if ($ext["groups"] && $options["table_groups_name"])
		{
			$sSQL = "SELECT " . $options["table_groups_name"] . ".gid AS rel_gid
							, " . $options["table_groups_name"] . ".name AS gid_name
					 FROM " . $options["table_groups_rel_user"] . "
						INNER JOIN " . $options["table_groups_name"] . " ON " . $options["table_groups_name"] . ".gid = " . $options["table_groups_rel_user"] . ".gid
							OR " . $options["table_groups_name"] . ".gid = " . $db->toSql($user["primary_gid"], "Number") . "
					 WHERE " . $options["table_groups_rel_user"] . ".uid = " . $db->toSql($user["ID"], "Number") . " 
					 ORDER BY " . $options["table_groups_name"] . ".level DESC";
			$db->query($sSQL);
			if ($db->nextRecord())
			{
				$user["groups"] = array();

				$user["primary_gid_default"] = $db->getField("rel_gid", "Number", true);
				$user["primary_gid_default_name"] = $db->getField("gid_name", "Text", true);
				do
				{
					$ID_group = $db->getField("rel_gid", "Number", true);
					$group_name = $db->getField("gid_name", "Text", true);
					if($ID_group > 0)
						$user["groups"][$group_name] = $ID_group;

					if($user["primary_gid"] == $ID_group) {
						$user["primary_gid_name"] = $group_name;

						$user["primary_gid_default"] = $user["primary_gid"];
						$user["primary_gid_default_name"] = $user["primary_gid_name"];
					}
				} while($db->nextRecord());

				if(!count($user["groups"]))
				{
					$user["groups"][MOD_SEC_GUEST_USER_NAME] = MOD_SEC_GUEST_USER_ID;
					$user["primary_gid_name"] = MOD_SEC_GUEST_USER_NAME;
				}

				$sSQL = "SELECT " . $options["table_groups_dett_name"] . ".*
						FROM " . $options["table_groups_dett_name"] . "
						WHERE " . $options["table_groups_dett_name"] . ".ID_groups = " . $db->toSql($user["primary_gid"], "Number") . "
						ORDER BY " . $options["table_groups_dett_name"] . ".`order`, " . $options["table_groups_dett_name"] . ".field";
				$db->query($sSQL);
				if($db->nextRecord())
				{
					do {
						$user["permissions_custom"][$db->getField("field", "Text", true)] = $db->getField("value", "Text", true);
					} while($db->nextRecord());
				}
			}
		}

		//MOD_SEC_ENABLE_TOKEN
		if ($ext["token"] && $options["table_token"])
		{
			$sSQL = "SELECT 
					" . $options["table_token"] . ".*
                FROM 
					" . $options["table_token"] . "
                WHERE 
					" . $options["table_token"] . ".ID_user = " . $db->toSql($user["ID"], "Number") . "
                ORDER BY 
					" . $options["table_token"] . ".`type`";
			$db->query($sSQL);
			if($db->nextRecord())
			{
				do
				{
					$user["token"][$db->getField("type", "Text", true)] = $db->getField("token", "Text", true);
				} while($db->nextRecord());
			}
		}
	}
	return $user;
}

/* destroy current session.
	With prompt_login set to true, automatically redirect to login page */
function mod_security_destroy_session($promptlogin = false, $ret_url = null, $disable_events = false)
{
	$cm = cm::getInstance();

	//ffErrorHandler::raise("DEBUG", E_USER_ERROR, null, get_defined_vars());
	@session_unset();
	@session_destroy();

	if(!$disable_events)
		$cm->doEvent("mod_security_on_destroy_session", array());
	
    $sessionName = session_name();
    $sessionCookie = session_get_cookie_params();

    setcookie($sessionName, false, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure'], true);

	if ($ret_url === null)
		$ret_url = $_SERVER["REQUEST_URI"];

	if(!$disable_events)
		$cm->doEvent("mod_security_on_destroyed_session", array());
		
	//REDIRIGE SU LOGIN
	if ($promptlogin)
		prompt_login($ret_url);
}

function mod_security_DOMAINS_on_tpl_layer_loaded(ffPage_base $page, $tpl)
{
	$cm = cm::getInstance();
	if($tpl->isset_var("field_accounts")) 
	{
		if(!defined("MOD_SEC_NOACCOUNTSCOMBO"))
		{
			if (mod_security_is_admin())
			{
				$field = ffField::factory($page);
				$field->id = "accounts";
				$field->base_type = "Number";
				$field->extended_type = "Selection";
				$field->multi_select_one_label = "Superadmin";
				$field->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains ORDER BY nome";
				$field->properties["onchange"] = "this.form.submit()";
				$field->value = new ffData($_REQUEST["accounts"], "Number");
				$field->parent_page = array(&$page);
				$field->db = array(mod_security_get_main_db());
				$tpl->set_var("field_accounts", $field->process());
			}
			elseif(defined("MOD_SEC_DISPLAY_DOMAIN"))
			{
				$tpl->set_var("field_accounts", ucwords(get_session("Domain")));
			}
		}
	}
}

function mod_security_get_domain()
{
	if (mod_security_check_session(false))
	{
		$res = cm::getInstance()->modules["security"]["events"]->doEvent("get_domain");
		$rc = end($res);
		if ($rc !== null)
			return $rc;
		
		if ($rc = get_session("DomainID"))
			return $rc;
		else
			return intval($_REQUEST["accounts"]);
	}
	else
		return null;
}

function mod_security_get_settings($path_info = null)
{
	$cm = cm::getInstance();
	
	if ($path_info === null)
		$path_info = $cm->path_info;
	
	$options["table_name"] = CM_TABLE_PREFIX . "mod_security_users";
	$options["table_dett_name"] = CM_TABLE_PREFIX . "mod_security_users_fields";
	$options["table_groups_name"] = CM_TABLE_PREFIX . "mod_security_groups";
	$options["table_groups_rel_user"] = CM_TABLE_PREFIX . "mod_security_users_rel_groups";
	$options["table_groups_dett_name"] = CM_TABLE_PREFIX . "mod_security_groups_fields";
	$options["table_token"] = CM_TABLE_PREFIX . "mod_security_token";
    $options["table_domains_fields"] = CM_TABLE_PREFIX . "mod_security_domains_fields";
    
	
	$options["session_name"] = session_name();

	if (
			!isset($cm->modules["security"]["session_bypath"]) || !count($cm->modules["security"]["session_bypath"])
		)
		return $options;

	ksort($cm->modules["security"]["session_bypath"], SORT_STRING);
	foreach ($cm->modules["security"]["session_bypath"] as $key => $value)
	{
		$match = false;
		$attrs = $value->attributes();

		$path = rtrim($key, "/");
		$propagate = (string)$attrs["propagate"];
		if ($propagate == "false")
			$propagate = false;
		else
			$propagate = true;

		if ($path == $path_info)
			$match = true;
		elseif ($propagate && strpos($path_info, $path . "/") === 0)
			$match = true;

		if (!$match)
			continue;

		if (isset($value->table))
		{
			if (strlen((string)$value->table))
				$options["table_name"] = (string)$value->table;
			else
				$options["table_name"] = CM_TABLE_PREFIX . "mod_security_users";
		}

		if (isset($value->table_dett))
		{
			if (strlen((string)$value->table_dett))
				$options["table_dett_name"] = (string)$value->table_dett;
			else
				$options["table_dett_name"] = CM_TABLE_PREFIX . "mod_security_users_fields";
		}

		if (isset($value->table_groups))
		{
			if (strlen((string)$value->table_groups))
				$options["table_groups_name"] = (string)$value->table_groups;
			else
				$options["table_groups_name"] = CM_TABLE_PREFIX . "mod_security_groups";
		}
		
		if (isset($value->table_groups_rel_user))
		{
			if (strlen((string)$value->table_groups_rel_user))
				$options["table_groups_rel_user"] = (string)$value->table_groups_rel_user;
			else
				$options["table_groups_rel_user"] = CM_TABLE_PREFIX . "mod_security_users_rel_groups";
		}

		if (isset($value->table_groups_dett_name))
		{
			if (strlen((string)$value->table_groups_dett_name))
				$options["table_groups_dett_name"] = (string)$value->table_groups_dett_name;
			else
				$options["table_groups_dett_name"] = CM_TABLE_PREFIX . "mod_security_groups_fields";
		}
		
		if (isset($value->table_token))
		{
			if (strlen((string)$value->table_token))
				$options["table_token"] = (string)$value->table_token;
			else
				$options["table_token"] = CM_TABLE_PREFIX . "mod_security_token";
		}

        if (isset($value->table_domains_fields))
        {
            if (strlen((string)$value->table_domains_fields))
                $options["table_domains_fields"] = (string)$value->table_domains_fields;
            else
                $options["table_domains_fields"] = CM_TABLE_PREFIX . "mod_security_table_domains_fields";
        }
		
		if (isset($value->login_path) && strlen((string)$value->login_path))
		{
				$options["login_path"] = (string)$value->login_path;
		}

		if (isset($value->session_name) && strlen((string)$value->session_name))
			$options["session_name"] = (string)$value->session_name;
	}
	reset($cm->modules["security"]["session_bypath"]);

	return $options;
}

function mod_security_get_package_settings($package, $db = null)
{
	$settings = array();
	
	if ($db === null)
		$db = mod_security_get_main_db();
	
	$sSQL = "
				SELECT
						" . CM_TABLE_PREFIX . "mod_security_packages.*
					FROM
						" . CM_TABLE_PREFIX . "mod_security_packages
					WHERE
		";
	if (is_numeric($package))
		$sSQL .= " " . CM_TABLE_PREFIX . "mod_security_packages.ID = " . $db->toSql($package);
	else
		$sSQL .= " " . CM_TABLE_PREFIX . "mod_security_packages.name = " . $db->toSql($package);
	$db->query($sSQL);
	if ($db->nextRecord())
	{
		$settings["_name_"] = $db->getField("name")->getValue();
		$settings["_type_"] = $db->getField("type")->getValue();
		$settings["_order_"] = $db->getField("order")->getValue();
	}
	else
		return null;

	$sSQL = "
				SELECT
						" . CM_TABLE_PREFIX . "mod_security_packages_fields.*
					FROM
						" . CM_TABLE_PREFIX . "mod_security_packages_fields
						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_packages ON
							" . CM_TABLE_PREFIX . "mod_security_packages_fields.ID_packages = " . CM_TABLE_PREFIX . "mod_security_packages.ID
		";
	if (is_numeric($package))
		$sSQL .= "AND " . CM_TABLE_PREFIX . "mod_security_packages_fields.ID_packages = " . $db->toSql($package);
	else
		$sSQL .= "AND " . CM_TABLE_PREFIX . "mod_security_packages.name = " . $db->toSql($package);

	$db->query($sSQL);
	if ($db->nextRecord())
	{
		do
		{
			$field = $db->getField("field")->getValue();
			if ($db->getField("unlimited")->getValue())
				$settings[$field] = null;
			else
				$settings[$field] = $db->getField("value")->getValue();
		} while ($db->nextRecord());
	}
	
	return $settings;
}

function mod_security_get_package_setting($setting, $package, $db = null)
{
	if ($db === null)
		$db = mod_security_get_main_db();
	
	$sSQL = "
				SELECT
						" . CM_TABLE_PREFIX . "mod_security_packages_fields.*
					FROM
						" . CM_TABLE_PREFIX . "mod_security_packages_fields
						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_packages ON
							" . CM_TABLE_PREFIX . "mod_security_packages_fields.ID_packages = " . CM_TABLE_PREFIX . "mod_security_packages.ID
					WHERE
						cm_mod_security_packages_fields.field = " . $db->toSql($setting) . "
		";
	if (is_numeric($package))
		$sSQL .= "AND " . CM_TABLE_PREFIX . "mod_security_packages_fields.ID_packages = " . $db->toSql($package);
	else
		$sSQL .= "AND " . CM_TABLE_PREFIX . "mod_security_packages.name = " . $db->toSql($package);

	$db->query($sSQL);
	if ($db->nextRecord())
	{
		if ($db->getField("unlimited")->getValue())
			return null;
		else
			return $db->getField("value")->getValue();
	}
	else
		return null;
}

function mod_security_get_package_setting_by_domain($setting, $DomainID, $db = null)
{
	if ($db === null)
		$db = mod_security_get_main_db();

	if (is_numeric($DomainID) || (
				is_object($DomainID) && get_class($DomainID) == "ffData" && $DomainID->data_type == "Number"
			)
		)
		$chiave = "ID";
	else
		$chiave = "nome";

	$package = $db->lookup(CM_TABLE_PREFIX . "mod_security_domains", $chiave, $DomainID, null, "ID_packages", "Number", true);
	if ($package !== null)
		return mod_security_get_package_setting($setting, $package, $db);
	else
		return null;
}

function mod_security_get_cascade_package_setting($setting, $DomainID = null, $db = null)
{
	if ($DomainID === null)
		$DomainID = mod_security_get_domain();

	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $db === null && $DomainID)
	{
		$res = mod_restricted_get_setting($setting, null, mod_security_get_db_by_domain($DomainID));
	}
	else
	{
		$res = mod_restricted_get_setting($setting, $DomainID, $db);
	}
    
	if (!strlen($res))
		return mod_security_get_package_setting_by_domain($setting, $DomainID, $db);
	else
		return $res;
}

/**
 *
 * @param mixed $DomainID
 * @return ffDB_sql
 */
function mod_security_get_db_by_domain($DomainID = null)
{
	 if ($DomainID === null)
		$DomainID = mod_security_get_domain();

	if (!MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $DomainID)
		ffErrorHandler::raise("MOD_SEC_MULTIDOMAIN_EXTERNAL_DB need to be set in order to connect to another DB", E_USER_ERROR, null, get_defined_vars());
	
	if ($DomainID === null)
		ffErrorHandler::raise("Wrong Function Call, DomainID not set", E_USER_ERROR, null, get_defined_vars());

	$db = ffDB_Sql::factory();
	
	$globals = ffGlobals::getInstance("mod_security");
	if (!isset($globals->domain_data[$DomainID]))
	{
		$tmpdb = new ffDB_Sql();
		$globals->domain_data[$DomainID] = $tmpdb->lookup(CM_TABLE_PREFIX . "mod_security_domains", "ID", $DomainID, array("", "", "", ""), array("db_host" => "Text", "db_name" => "Text", "db_user" => "Text", "db_pass" => "Text"), null, true);
	}
	
	$db->halt_on_connect_error = false;
	$db->on_error = "ignore";
	$rc = $db->connect($globals->domain_data[$DomainID]["db_name"], $globals->domain_data[$DomainID]["db_host"], $globals->domain_data[$DomainID]["db_user"], $globals->domain_data[$DomainID]["db_pass"]);
	$db->halt_on_connect_error = true;
	$db->on_error = "halt";
	if ($rc === false)
		return false;
	else
		return $db;
}

/**
 *
 * @return ffDB_sql
 */
function mod_security_get_main_db()
{
	$db = ffDB_Sql::factory();
	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB) $db->connect(FF_DATABASE_NAME, FF_DATABASE_HOST, FF_DATABASE_USER, FF_DATABASE_PASSWORD);
	return $db;
}

function mod_security_is_default_field($field)
{
	if (in_array($field, explode(",", MOD_SEC_DEFAULT_FIELDS)))
		return true;
	else
		return false;
}

function mod_security_is_defined_field($field)
{
	$cm = cm::getInstance();
	if (isset($cm->modules["security"]["fields"][$field]))
		return true;
	else
		return false;
}

function mod_security_add_custom_fields(&$oRecord, $from_domains = false)
{
	$cm = cm::getInstance();
	if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
	{
		foreach ($cm->modules["security"]["fields"] as $key => $value)
		{
			$enable = true;
			$group = "preferences";

			$oField = ffField::factory($cm->oPage);
			$oField->id = $key;
			
			if (!mod_security_is_default_field($key))
				$oField->store_in_db = false;

			foreach ($value as $subkey => $subvalue)
			{
				switch ($subkey)
				{
					case "file_show_delete":
						if ($subvalue == "true")
							$subvalue = true;
						elseif ($subvalue == "false")
							$subvalue = false;
						break;

					default:
						$subvalue = str_replace("[FF_SITE_PATH]", FF_SITE_PATH, $subvalue);
						$subvalue = str_replace("[FF_DISK_PATH]", FF_DISK_PATH, $subvalue);
						$subvalue = str_replace("[GLOBALS]", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST), $subvalue);
						$subvalue = str_replace("[ID_DOMAIN]", mod_security_get_domain(), $subvalue);
				}

				switch ($subkey)
				{
					case "validators":
						$validators = explode(",", $subvalue);
						foreach($validators as $validators_key => $validators_value)
						{
							$validators_value = trim($validators_value);
							if ($validators_value == "")
								continue;

							$oField->addValidator($validators_value);
						}
						break;

					case "group":
						$group = $subvalue;
						break;

					case "enable_acl":
						if (!$from_domains)
							$enable = mod_sec_check_acl($subvalue);
						break;

					case "acl":
						if (!$from_domains && !mod_sec_check_acl($subvalue))
						{
							//$oField->store_in_db = false;
							$oField->control_type = "label";
						}
						break;
					case "default_value":
						if(substr($subvalue, 0, 1) == "_")
							$oField->default_value = new ffData(ffTemplate::_get_word_by_code($subvalue));
						else
							$oField->default_value = new ffData($subvalue);
						break;
					default:
						if ($subvalue === "true")
							$subvalue = true;
						elseif($subvalue === false)
							$subvalue = false;
						if (property_exists($oField, $subkey))
						{
							$tmp = '$oField->' . $subkey . ' = "' . $subvalue . '";';
							eval($tmp);
						}
				}
			}
			reset($value);

			if (strlen($group))
			{
				if ((cm_getMainTheme () == "restricted" || $cm->oPage->getTheme() == "restricted") && !isset($oRecord->groups[$group]))
				{
						$oRecord->addContent(null, true, $group);
				}
				if (isset($cm->modules["security"]["groups"][$group]["title"]))
					$oRecord->groups[$group]["title"] = $cm->modules["security"]["groups"][$group]["title"];
				else 
					$oRecord->groups[$group]["title"] = ffTemplate::_get_word_by_code($group);
			}
			
			switch($oField->extended_type)
			{
				case "Boolean":
					if ($oField->control_type == "label")
					{
						$oField->extended_type = "Selection";
						$oField->multi_pairs = array(
							array(new ffData(0, $oField->base_type), new ffData("No"))
							, array(new ffData(1, $oField->base_type), new ffData("Si"))
						);
					}
					else
					{
						$oField->unchecked_value = new ffData(0, $oField->base_type);
						$oField->checked_value = new ffData(1, $oField->base_type);
					}
					break;
				case "Radio":
					if ($oField->control_type == "label")
					{
						$oField->extended_type = "Selection";
						$oField->multi_pairs = array(
							array(new ffData(0, $oField->base_type), new ffData("No"))
							, array(new ffData(1, $oField->base_type), new ffData("Si"))
						);
					}
					else
					{
					    $oField->control_type = "radio";
					    $oField->extended_type = "Selection";
						$oField->multi_pairs = array (
					        array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("no"))),
					        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes")))
			            );
					}
					break;
				case "Selection":
					/*if (!$from_domains && MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_is_admin())
						$oField->db = array(mod_security_get_main_db());
					else*/if ($from_domains && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
						$oField->db = array(mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]));
			}

			if ($enable) $oRecord->addContent($oField, $group);
		}
		reset($cm->modules["security"]["fields"]);
	}
}

function mod_security_domain_add_custom_fields($oRecord)
{
	$cm = cm::getInstance();
	if (isset($cm->modules["security"]["domains_fields"]) && count($cm->modules["security"]["domains_fields"]))
	{
		foreach ($cm->modules["security"]["domains_fields"] as $key => $value)
		{
			if (isset($value["hide"]))
				continue;

			$group = "additional";

			$oField = ffField::factory($cm->oPage);
			$oField->id = $key;
			$oField->store_in_db = false;

			foreach ($value as $subkey => $subvalue)
			{
				switch ($subkey)
				{
					case "file_show_delete":
						if ($subvalue == "true")
							$subvalue = true;
						elseif ($subvalue == "false")
							$subvalue = false;
						break;

					default:
						$subvalue = str_replace("[FF_SITE_PATH]", FF_SITE_PATH, $subvalue);
						$subvalue = str_replace("[FF_DISK_PATH]", FF_DISK_PATH, $subvalue);
						$subvalue = str_replace("[GLOBALS]", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST), $subvalue);
						$subvalue = str_replace("[ID_DOMAIN]", mod_security_get_domain(), $subvalue);
				}

				switch ($subkey)
				{
					case "validators":
						$validators = explode(",", $subvalue);
						foreach($validators as $validators_key => $validators_value)
						{
							$validators_value = trim($validators_value);
							if ($validators_value == "")
								continue;

							$oField->addValidator($validators_value);
						}
						break;

					case "group":
						$group = $subvalue;

						if ((cm_getMainTheme () == "restricted" || $cm->oPage->getTheme() == "restricted") && !isset($oRecord->groups[$group]))
						{
								$oRecord->addContent(null, true, $group);
						}
						/*if (isset($cm->modules["security"]["domains_groups"][$group]["title"]))
							$oRecord->groups[$group]["title"] = $cm->modules["security"]["domains_groups"][$group]["title"];*/
						break;

					default:
						$tmp = '$oField->' . $subkey . ' = "' . $subvalue . '";';
						eval($tmp);
				}
			}
			reset($value);

			switch($oField->extended_type)
			{
				case "Boolean":
					if ($oField->control_type == "label")
					{
						$oField->extended_type = "Selection";
						$oField->multi_pairs = array(
							array(new ffData(0, $oField->base_type), new ffData("No"))
							, array(new ffData(1, $oField->base_type), new ffData("Si"))
						);
					}
					else
					{
						$oField->unchecked_value = new ffData(0, $oField->base_type);
						$oField->checked_value = new ffData(1, $oField->base_type);
					}
					break;

				case "Selection":
					$oField->db = array(mod_security_get_main_db());
			}

			$oRecord->addContent($oField, $group);
		}
		reset($cm->modules["security"]["domains_fields"]);
	}
}

/**
 * The letter l (lowercase L) and the number 1
 * have been removed, as they can be mistaken
 * for each other.
 */
function mod_sec_createRandomPassword($length = MOD_SEC_RANDOMPASS_LENGTH, $strength = MOD_SEC_RANDOMPASS_STRENGTH)
{
    srand((double)microtime()*1000000);

	$vowels = 'aeuyio';
	$consonants = 'bdghjmnpqrstvz';

	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUYIO";
	}
	if ($strength & 4) {
		$consonants .= '123456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}

	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	
	return $password;
}

/**
 *
 * @param type $path
 * @param type $others
 * @param type $modify can be false (view), true (modify), "insert" and "delete" (last two with MOD_SEC_PROFILING_ADDITIONAL_PRIVS)
 * @param type $strict
 * @param type $profile
 * @param type $usernid
 * @param type $path_info
 * @return boolean 
 */
function mod_sec_checkprofile_bypath($path, $others = false, $modify = false, $strict = true, $profile = null, $usernid = null, $path_info = null)
{
	if (!MOD_SEC_PROFILING)
		return true;
	
	if (defined("MOD_SECURITY_SESSION_STARTED") && get_session("UserLevel") == 3)
		return true;
	
	$permissions = mod_sec_getperms_bypath($path, $profile, $usernid, $path_info);
	
	if ($permissions === null)
		return MOD_SEC_PROFILING_DEFAULT;
	else if (is_bool($permissions))
		return $permissions;
	
	if (MOD_SEC_PROFILING_MULTI)
	{
		$rc = false;
		foreach ($permissions as $value)
		{
			$rc |= mod_sec_checkpermission($value, $others, $modify, $strict);
		}
		return $rc;
	}
	else
	{
		return mod_sec_checkpermission($permissions, $others, $modify, $strict);
	}
}

function mod_sec_checkpermission($permissions, $others = false, $modify = false, $strict = true)
{
	if (MOD_SEC_PROFILING_EXTENDED)
	{
		if (!$others)
		{
			if ($modify === true)
			{
				if ($strict)
					return $permissions["modify_own"];
				else
					return $permissions["modify_own"] | $permissions["modify_others"];
			}
			else if ($modify === false)
			{
				if ($strict)
					return $permissions["view_own"];
				else
					return $permissions["modify_others"] | $permissions["view_others"] | $permissions["modify_own"] | $permissions["view_own"];
			}
			else if ($modify === "insert")
			{
				if ($strict)
					return $permissions["insert_own"];
				else
					return $permissions["insert_own"] | $permissions["insert_others"];
			}
			else if ($modify === "delete")
			{
				if ($strict)
					return $permissions["delete_own"];
				else
					return $permissions["delete_own"] | $permissions["delete_others"];
			}
		}
		else
		{
			if ($modify === true)
				return $permissions["modify_others"];
			else if ($modify === false)
			{
				if ($strict)
					return $permissions["view_others"];
				else
					return $permissions["modify_others"] | $permissions["view_others"];
			}
			else if ($modify === "insert")
			{
				if ($strict)
					return $permissions["insert_others"];
				else
					return $permissions["insert_others"] | $permissions["insert_others"];
			}
			else if ($modify === "delete")
			{
				if ($strict)
					return $permissions["delete_others"];
				else
					return $permissions["delete_others"] | $permissions["delete_others"];
			}
		}
	}
	else
	{
		if ($strict)
			return $permissions["view_own"];
		else
			return ($permissions["view_own"] | $permissions["modify_own"] | $permissions["view_others"] | $permissions["modify_others"]);
	}
	
	return false; // to catch errors
}

function mod_sec_profile_has($check, $usernid = null, $path_info = null)
{
	if ($usernid === null && defined("MOD_SECURITY_SESSION_STARTED"))
		$usernid = get_session("UserNID");
	
	if ($usernid === null)
		return null;
	
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_PROFILING_MAINDB)
	{
		$db = mod_security_get_main_db();
		if (mod_security_get_domain())
			$db2 = mod_security_get_db_by_domain();
		else
			$db2 = mod_security_get_main_db();
	}
	else
	{
		$db = ffDB_Sql::factory();
		$db2 = ffDB_Sql::factory();
	}
	
	$profile = mod_sec_getprofile_byuser($usernid, $path_info);
	
	if (MOD_SEC_PROFILING_MULTI)
	{
		if (is_array($profile) && count($profile))
		{
			foreach ($profile as $value)
			{
				$db2->query(
						"SELECT 
								`cm_mod_security_profiles`.* 
							FROM 
								`cm_mod_security_profiles`
							WHERE 
								`cm_mod_security_profiles`.`ID` = " . $db2->toSql($value) . "
							"
					);
				if ($db2->nextRecord())
				{
					$data = $db2->record;
					if (is_string($check) && $data["special"] === $check)
						return true;
				}
			}
		}
	}
	else
	{
		if (intval($profile))
		{
			$db2->query(
					"SELECT 
							`cm_mod_security_profiles`.* 
						FROM 
							`cm_mod_security_profiles`
						WHERE 
							`cm_mod_security_profiles`.`ID` = " . $db2->toSql($profile) . "
						"
				);
			if ($db2->nextRecord())
			{
				$data = $db2->record;
				if (is_string($check) && $data["special"] === $check)
					return true;
			}
		}
	}
	
	return false;
}

function mod_sec_getperms_bypath($path, $profile = null, $usernid = null, $path_info = null)
{
	if (MOD_SEC_PROFILING_SKIPSYSTEM === "*")
		return null;

    $cm = cm::getInstance();
	$menu = $cm->modules["restricted"]["menu_bypath"];
	
	$default_permission = null;  // cache for future use

	if (strlen(MOD_SEC_PROFILING_SKIPSYSTEM))
	{
		$toskip_system = explode(",", MOD_SEC_PROFILING_SKIPSYSTEM);

		$check_path = rtrim($path, "/");
		if ($check_path === "")
			$check_path = "/";
		while (true)
		{
			if (isset($menu[$check_path]))
			{
				foreach ($menu[$check_path] as $value)
				{
					if (isset($value["profiling_default"]))
						$default_permission = $value["profiling_default"]; // cache for future use
					
					if (
							$value["profiling_skip"]
							|| in_array($check_path, $toskip_system)
						)
						return null;
				}
			}

			if ($check_path === "/")
				break;

			$check_path = substr($check_path, 0, strrpos($check_path, "/"));
			if ($check_path === "")
				$check_path = "/";
		}

		if (isset($cm->modules["security"]["profiling"]["paths"]))
		{
			foreach ($cm->modules["security"]["profiling"]["paths"] as $key => $value)
			{
				$check_path = rtrim($path, "/");
				if ($check_path === "")
					$check_path = "/";
				while (true)
				{
					if (
							$value["path"] == $check_path
							&& isset($value["profiling_default"])
						)
						$default_permission = $value["profiling_default"]; // cache for future use
					
					if (
							$value["path"] == $check_path
							&& (
								$value["profiling_skip"]
								|| in_array($check_path, $toskip_system)
							)
						)
						return null;

					if ($check_path === "/")
						break;

					$check_path = substr($check_path, 0, strrpos($check_path, "/"));
					if ($check_path === "")
						$check_path = "/";
				}
			}
		}
	}
		
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_PROFILING_MAINDB)
	{
		$db = mod_security_get_main_db();
		if (mod_security_get_domain())
			$db2 = mod_security_get_db_by_domain();
		else
			$db2 = mod_security_get_main_db();
	}
	else
	{
		$db = ffDB_Sql::factory();
		$db2 = ffDB_Sql::factory();
	}
	
	if ($usernid === null && defined("MOD_SECURITY_SESSION_STARTED"))
		$usernid = get_session("UserNID");
	
	if ($usernid === null && $profile === null)
		return null;
//		ffErrorHandler::raise("wrong mod_sec_checkprofile_bypath use, cannot determine profile", E_USER_ERROR, null, get_defined_vars());
	
	if ($profile === null)
		$profile = mod_sec_getprofile_byuser($usernid, $path_info);
	
	if (!$profile)
	{
		if (strlen(MOD_SEC_PROFILING_SKIPSYSTEM)) // benefit from cache to avoid redundand cycle
			return $default_permission;
		
		// try to get default permission
		$check_path = rtrim($path, "/");
		if ($check_path === "")
			$check_path = "/";
		while (true)
		{
			if (isset($menu[$check_path]))
			{
				foreach ($menu[$check_path] as $value)
				{
					if (isset($value["profiling_default"]))
						return $value["profiling_default"];
				}
			}

			if ($check_path === "/")
				break;

			$check_path = substr($check_path, 0, strrpos($check_path, "/"));
			if ($check_path === "")
				$check_path = "/";
		}

		if (isset($cm->modules["security"]["profiling"]["paths"]))
		{
			foreach ($cm->modules["security"]["profiling"]["paths"] as $key => $value)
			{
				$check_path = rtrim($path, "/");
				if ($check_path === "")
					$check_path = "/";
				while (true)
				{
					if (
							$value["path"] == $check_path
							&& isset($value["profiling_default"])
						)
						return $value["profiling_default"];

					if ($check_path === "/")
						break;

					$check_path = substr($check_path, 0, strrpos($check_path, "/"));
					if ($check_path === "")
						$check_path = "/";
				}
			}
		}
		
		return null;
	}
	
	if (MOD_SEC_PROFILING_MULTI)
	{
		foreach ($profile as $value)
		{
			$permissions[] = $db2->lookup(
					"SELECT 
							`cm_mod_security_profiles_pairs`.* 
						FROM 
							`cm_mod_security_profiles_pairs`
						WHERE 
							`cm_mod_security_profiles_pairs`.`ID_profile` = " . $db->toSql($value) . "
							AND `cm_mod_security_profiles_pairs`.`path` = " . $db->toSql($path) . "
						"
					, null
					, null
					, null
					, array(
							"view_own"			=> "Text"
							, "view_others"		=> "Text"
							, "modify_own"		=> "Text"
							, "modify_others"	=> "Text"
							, "insert_own"		=> "Text"
							, "insert_others"	=> "Text"
							, "delete_own"		=> "Text"
							, "delete_others"	=> "Text"
					)
					, null
					, true
				);
		}
	}
	else
	{
		$permissions = $db2->lookup(
				"SELECT 
						* 
					FROM 
						cm_mod_security_profiles_pairs 
					WHERE 
						ID_profile = " . $db->toSql($profile) . "
						AND path = " . $db->toSql($path) . "
					"
				, null
				, null
				, null
				, array(
						"view_own"			=> "Text"
						, "view_others"		=> "Text"
						, "modify_own"		=> "Text"
						, "modify_others"	=> "Text"
						, "insert_own"		=> "Text"
						, "insert_others"	=> "Text"
						, "delete_own"		=> "Text"
						, "delete_others"	=> "Text"
				)
				, null
				, true
			);
	}
	
	return $permissions;
}

function mod_sec_getprofile_byuser($UserNID = null, $path_info = null)
{
	if ($UserNID === null)
		if (defined("MOD_SECURITY_SESSION_STARTED"))
			$UserNID = get_session("UserNID");
		else
			return null;
	
	if ($path_info === null)
		$path_info = cm::getInstance()->path_info;
	
	$options = mod_security_get_settings($path_info);
	
	$db = ffDB_Sql::factory();
	$profile = null;
	
	if (!MOD_SEC_PROFILING_MULTI)
		$profile = $db->lookup("SELECT `profile` FROM " . $options["table_name"] . " WHERE ID = " . $db->toSql($UserNID), null, null, null, null, null, true);
	else
	{
		$sSQL = "SELECT 
						`ID_profile`
					FROM 
						`cm_mod_security_rel_profiles_users`
					WHERE 
						`ID_user` = " . $db->toSql($UserNID) . "
						AND `enabled` = '1'
			";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$profile = array();
			do
			{
				$profile[] = $db->getField("ID_profile")->getValue();
			} while ($db->nextRecord());
		}
	}
	
	if (!$profile)
		return null;
	else
		return $profile;
}

function process_sql_exclude($sSQL = null)
{
	if (!MOD_SEC_EXCLUDE_SQL)
		return;

	$cm = cm::getInstance();
	$options = mod_security_get_settings($cm->path_info);

	if ($sSQL === null)
		$sSQL = MOD_SEC_EXCLUDE_SQL;
	
	$sSQL = str_replace("[TABLE_NAME]", $options["table_name"], $sSQL);
	
	return $sSQL;
}

function mod_sec_check_acl($acl, $level = null)
{
	if ($level === null)
		$level = get_session("UserLevel");
	
	$level .= ""; // lo trasforma in stringa
	
	if (!strlen($acl) || !strlen($level) || strpos($acl, $level) !== false)
		return true;
	else
		return false;
	
	/*$acl = explode(",", $acl);
	if (is_array($acl) && count($acl))
	{
		$acl = array_flip($acl);
		if (!isset($acl[$level]))
			return false;
	}
	return true;*/
}

function mod_sec_profiling_element_add($path, $label, $profile, $indent = 0, $acl = null)
{
	$globals = ffGlobals::getInstance("mod_security");
	
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !MOD_SEC_PROFILING_MAINDB)
		$db = mod_security_get_db_by_domain();
	else
		$db = mod_security_get_main_db();
	
	$record = array();
	
	$record["path"] = new ffData($path);
	$record["label"] = "";
	for ($i = 0; $i < $indent; $i++)
	{
		$record["label"] .= MOD_SEC_PROFILING_INDENTSTRING;
	}
	
	$record["label"] .= " " . $label;

	if ($acl !== null)
	{
		$tmp = mod_sec_get_acl_desc($acl);
		if (strlen($tmp))
			$record["label"] .= " (ACL: " . $tmp . ")";
	}
	
	$record["label"] = new ffData($record["label"]);
	
	if ($profile)
	{
		$record["ID_profile"] = new ffData($profile, "Number");
		$sSQL = "SELECT * FROM cm_mod_security_profiles_pairs WHERE ID_profile = " . $db->toSql($profile) . " AND path = " . $db->toSql($path) . " ";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$record["ID_detail"]		= $db->getField("ID", "Number");
			$record["view_own"]			= $db->getField("view_own");
			$record["view_others"]		= $db->getField("view_others");
			$record["modify_own"]		= $db->getField("modify_own");
			$record["modify_others"]	= $db->getField("modify_others");
		}
	}
	
	$globals->recordset[] = $record;
}

function mod_sec_get_acl_desc($acl)
{
	$tmp = "";
	if (mod_sec_check_acl($acl, 1))
		$tmp .= "Utente";
	if (mod_sec_check_acl($acl, 2))
	{
		if (strlen($tmp)) $tmp .= ", ";
		$tmp .= "Admin";
	}
	if (mod_sec_check_acl($acl, 3))
	{
		if (strlen($tmp)) $tmp .= ", ";
		$tmp .= "Super";
	}
	return $tmp;
}

function mod_sec_profiling_update_profiles()
{
	$cm = cm::getInstance();
	$ids = "";
	
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !MOD_SEC_PROFILING_MAINDB)
		$db = mod_security_get_db_by_domain();
	else
		$db = mod_security_get_main_db();

	if (isset($cm->modules["security"]["profiling"]["profiles"]))
	{
		foreach ($cm->modules["security"]["profiling"]["profiles"] as $key => $value)
		{
			$sSQL = "SELECT * FROM cm_mod_security_profiles WHERE special = " . $db->toSql($value["id"]);
			$db->query($sSQL);
			if ($db->nextRecord())
			{
				if (strlen($ids))
					$ids .= ", ";
				$ids .= $db->getField("ID")->getValue();
				if ($db->getField("acl")->getValue() != $value["acl"])
				{
					$sSQL = "UPDATE cm_mod_security_profiles SET acl = " . $db->toSql($value["acl"]) . " WHERE ID = " . $db->toSql($db->getField("ID"));
					$db->execute($sSQL);
				}
			}
			else
			{
				$sSQL = "INSERT INTO cm_mod_security_profiles (
								`nome`
								, `created_time`
								, `created_user`
								, `enabled`
								, `special`
								, `acl`
							) VALUES (
								" . $db->toSql($value["label"]) . "
								, NOW()
								, 0
								, 1
								, " . $db->toSql($value["id"]) . "
								, " . $db->toSql($value["acl"]) . "
							)
					";
				$db->execute($sSQL);
				if (strlen($ids))
					$ids .= ", ";
				$ids .= $db->getInsertID(true);
			}
		}
	}
	
	return $ids;
}

function mod_security_get_domain_field($field, $ID_domain = null, $db = null)
{
    $cm = cm::getInstance();

    if ($db === null)
        $db = ffDB_Sql::factory();
    elseif (is_array($db))
        $db =& $db[0];

    if ($ID_domain === null)
        $ID_domain = mod_security_get_domain();

    $options = mod_security_get_settings($cm->path_info);

    if (!is_object($db))
        ffErrorHandler::raise("invalid db object", E_USER_ERROR, null, get_defined_vars());

    $sSQL = "SELECT * FROM " . $options["table_domains_fields"] . " WHERE field = " . $db->toSql(new ffData($field));

    if ($ID_domain !== null)
    {
        $sSQL .= " AND ID_domains = " . $db->toSql($ID_domain);
    }

    $db->query($sSQL);
    if ($db->nextRecord())
    {
        return $db->getField("value")->getValue();
    }
    else
    {
        return "";
    }
}

function mod_security_set_domain_field($field, $value, $ID_domain = null, $db = null)
{
    $cm = cm::getInstance();

    if ($db === null)
        $db = ffDB_Sql::factory();
    elseif (is_array($db))
        $db =& $db[0];

    if (!is_object($db))
        ffErrorHandler::raise("invalid db object", E_USER_ERROR, null, get_defined_vars());

    if ($ID_domain === null)
        $ID_domain = mod_security_get_domain();

    $options = mod_security_get_settings($cm->path_info);
	
    if ($ID_domain !== null)
    {
        $sSQL_and = " AND ID_domains = " . $db->toSql($ID_domain);
    }
    $sSQL = "SELECT ID
                FROM " . $options["table_domains_fields"] . "
                WHERE field = " . $db->toSql($field) . $sSQL_and;
    $db->query($sSQL);
    if($db->nextRecord())
	{
        $sSQL = "UPDATE " . $options["table_domains_fields"] . " SET
                        value = " . $db->toSql($value) . "
                    WHERE field = " . $db->toSql($field) . $sSQL_and;
        $db->execute($sSQL);
    }
    else
    {
        if ($ID_domain !== null)
        {
            $fields = ", ID_domains";
            $values = ", " . $db->toSql($ID_domain);
        }
        $sSQL = "INSERT INTO
                    " . $options["table_domains_fields"] . " (
                        value
                        , field
                        " . $fields . "
                    ) VALUES (
                        " . $db->toSql($value) . "
                        , " . $db->toSql($field) . "
                        " . $values . "
                    )
            ";
        $db->execute($sSQL);
    }
}

function mod_security_set_user_by_social($social, $UserParams, $UserField, $UserToken = array(), $ID_domain = null, $disable_events = false, $skip_redirect = false, $path_info = null)
{
	$cm = cm::getInstance();

    if ($ID_domain === null)
        $ID_domain = mod_security_get_domain();

    $permanent_session = MOD_SECURITY_SESSION_PERMANENT; //todo: da recuperare dentro i social login

	$UserParams["ID_domain"] = $ID_domain;
	$UserParams["username_slug"] = ffCommon_url_rewrite($UserParams["username"]);
	
	// check username & password
	$options = mod_security_get_settings($path_info);

	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();

    $cm->modules["security"]["events"]->doEvent("social_login_on_retrieve_params", array(&$UserParams, &$UserField, &$ID_domain));

    $sSQL_user_field = "";
	$arrDefaultFields = explode(",", MOD_SEC_DEFAULT_FIELDS);
	if(is_array($UserParams) && count($UserParams)) {
		foreach($UserParams AS $UserParams_key => $UserParams_value) {
			if(array_search($UserParams_key, $arrDefaultFields) !== false) {
				$sSQL_user_field["insert"]["head"] .= ", `" . $UserParams_key . "`";
				$sSQL_user_field["insert"]["body"] .= ", " . $db->toSql($UserParams_value);
				
				if($UserParams_key != "email") {
					$sSQL_user_field["update"] .= ", `" . $UserParams_key . "` = " . $db->toSql($UserParams_value);
				}			
			}
		}
	}

	$sSQL = "SELECT
					" . $options["table_name"] . ".*
				FROM
					" . $options["table_name"] . "
				WHERE
					1
					AND (";
	$sSQL .= $options["table_name"] . ".email = " . $db->toSql($UserParams["email"], "Text");
	$sSQL .= ")";

	if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
		$sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);
	
	if (MOD_SEC_EXCLUDE_SQL)
		$sSQL .= " AND `" . $options["table_name"] . "`.ID " . MOD_SEC_EXCLUDE_SQL;
	
	$db->query($sSQL);
	if (!$db->nextRecord())
	{
		if($disable_events)
		{
			$last_res = false;
		} 
		else 
		{
			$res = $cm->modules["security"]["events"]->doEvent("on_social_do_user_create", array(&$UserParams["username"], &$UserParams["username_slug"], &$UserParams["avatar"], &$UserParams["email"], &$ID_domain));
			$last_res = end($res);
			if (!$last_res)
			{
				$res = $cm->modules["security"]["events"]->doEvent("do_user_create", array(&$UserParams["username"], &$UserParams["username_slug"], &$UserParams["avatar"], &$UserParams["email"], &$ID_domain));
				$last_res = end($res);
			}
		}
		
		if (!$last_res)
		{
			$sSQL_manage = "INSERT INTO " . $options["table_name"] . "
							(
								ID
								, `created`
								, `lastlogin`	
								" . $sSQL_user_field["insert"]["head"] . "
							)
							VALUES
							(
								null
								, NOW()
								, NOW()
								" . $sSQL_user_field["insert"]["body"] . "
							)";
			$db->execute($sSQL_manage);
			$UserParams["ID"] = $db->getInsertID(true);
			$status = "insert";
			
			if(is_array($UserField) && count($UserField)) {
				foreach($UserField AS $UserField_key => $UserField_value) {
					$sSQL_manage = "INSERT INTO " . $options["table_dett_name"] . "
									(
										`ID`
										, `ID_users`
										, `field`
										, `value`
									)
									VALUES
									(
										null
										, " . $db->toSql($UserParams["ID"], "Number") . "
										, " . $db->toSql($UserField_key, "Text") . "
										, " . $db->toSql($UserField_value, "Text") . "
									)";
					$db->execute($sSQL_manage);
				}
			}
			if(!$disable_events) {
				$cm->modules["security"]["events"]->doEvent("on_social_done_user_create", array($UserParams["ID"], !$UserParams["status"], $skip_redirect));
				$cm->modules["security"]["events"]->doEvent("done_user_create", array($UserParams["ID"], !$UserParams["status"], $skip_redirect));
			}
		}
	} 
	else 
	{
		$UserParams["ID"] = $db->getField("ID", "Number", true);
		if($disable_events)
		{
			$last_res = false;
		}
		else
		{
			$res = $cm->modules["security"]["events"]->doEvent("on_social_do_user_update", array(&$UserParams["ID"], &$UserParams["username_slug"], &$UserParams["avatar"], $skip_redirect));
			$last_res = end($res);
			if (!$last_res)
			{
				$res = $cm->modules["security"]["events"]->doEvent("do_user_update", array(&$UserParams["ID"], &$UserParams["username_slug"], &$UserParams["avatar"], $skip_redirect));
				$last_res = end($res);
			}
		}
		
		if (!$last_res)
		{
			$UserParams["ID"] = $db->getField("ID", "Number", true);
			$status = "update";
			if(strlen($sSQL_user_field["update"])) {
				$sSQL_manage = "UPDATE " . $options["table_name"] . " 
						SET `lastlogin` = NOW()
							" . $sSQL_user_field["update"] . "
						WHERE ID = " . $db->toSql($UserParams["ID"], "Number");
				$db->execute($sSQL_manage);
			}
			if(is_array($UserField) && count($UserField)) {
				foreach($UserField AS $UserField_key => $UserField_value) {
					$sSQL_manage = "SELECT " . $options["table_dett_name"] . ".*
									FROM " . $options["table_dett_name"] . "
									WHERE " . $options["table_dett_name"] . ".`ID_users` = " . $db->toSql($UserParams["ID"], "Number") . "
										AND " . $options["table_dett_name"] . ".`field` = " . $db->toSql($UserField_key);
					$db->query($sSQL_manage);
					if(!$db->nextRecord()) {
						$sSQL_manage = "INSERT INTO " . $options["table_dett_name"] . "
										(
											`ID`
											, `ID_users`
											, `field`
											, `value`
										)
										VALUES
										(
											null
											, " . $db->toSql($UserParams["ID"], "Number") . "
											, " . $db->toSql($UserField_key, "Text") . "
											, " . $db->toSql($UserField_value, "Text") . "
										)";
						$db->execute($sSQL_manage);
					}
				}
			}
			
			if (!$disable_events)
			{								
				$cm->modules["security"]["events"]->doEvent("on_social_done_user_update", array($UserParams["ID"], $UserParams["username_slug"], $UserParams["avatar"], $skip_redirect));
				$cm->modules["security"]["events"]->doEvent("done_user_update", array($UserParams["ID"], $UserParams["username_slug"], $UserParams["avatar"], $skip_redirect));
			}
		}
	}
	
	$db->query($sSQL);
	if ($db->nextRecord())
	{
	    if($db->getField("status", "Number", true) > 0) 
	    {
			if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
				$userID = $db->getField("username", "Text")->getValue();
			else
				$userID = $db->getField("email", "Text")->getValue();

			$userNID = $db->getField("ID", "Number")->getValue();

			if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_LOGIN_DOMAIN)
			{
				$ID_domain = $db->getField("ID_domains")->getValue();
			}

			if(MOD_SEC_ENABLE_TOKEN 
				&& is_array($UserToken)
				&& array_key_exists("type", $UserToken)
				&& array_key_exists("token", $UserToken)
			) {
				mod_security_set_accesstoken($userNID, $UserToken["token"], $UserToken["type"]);
			}

			mod_security_create_session($userID, $userNID, null, $ID_domain, $permanent_session, $disable_events || $skip_redirect );
			if(!$disable_events) 
			{
				$res = $cm->modules["security"]["events"]->doEvent($social . "_logging_in", array($userNID, $UserParams, $UserField));
				$last_res = end($res);
				if (!$last_res)
				{
					$cm->modules["security"]["events"]->doEvent("logging_in", array($userNID, $UserParams, $UserField));
				}
			}

			$sError = false;
		}
		else
		{
			if(!$disable_events) 
			{	
				$cm->modules["security"]["events"]->doEvent($social . "_done_user_not_active", array($UserParams["ID"], $UserParams["username_slug"], $UserParams["avatar"], $skip_redirect));
				$cm->modules["security"]["events"]->doEvent("done_user_not_active", array($UserParams["ID"], $UserParams["username_slug"], $UserParams["avatar"], $skip_redirect));
			}
	        $sError = ffTemplate::_get_word_by_code($social . "_login_user_not_active");
		}
	}
	else 
	{
	    $sError = ffTemplate::_get_word_by_code($social . "_login_wrong_user");
	}

    return array(
			"user" => $UserParams
    		, "error" => $sError
    		, "status" => $status
    	);
}

function mod_security_set_accesstoken($UserNID, $access_token, $type, $ID_domain = null, $path_info = null)
{
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain !== null)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();
	
	$options = mod_security_get_settings($path_info);
	
	if($UserNID > 0 && strlen($type))
	{
		if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
			$sSQL_and = " AND `" . $options["table_token"] . "`.`ID_domain` = " . $db->toSql($ID_domain);
		$sSQL = "SELECT ID
					FROM `" . $options["table_token"] . "`
					WHERE `" . $options["table_token"] . "`.`ID_user` = " . $db->toSql($UserNID, "Number") . "
						AND `" . $options["table_token"] . "`.`type` = " . $db->toSql($type) . $sSQL_and;
		$db->query($sSQL);
		if($db->nextRecord())
		{
			$sSQL = "UPDATE `" . $options["table_token"] . "` SET 
									`" . $options["table_token"] . "`.`token` = " . $db->toSql($access_token) . "
								WHERE  `" . $options["table_token"] . "`.`ID_user` = " . $db->toSql($UserNID, "Number") . "
									AND `" . $options["table_token"] . "`.`type` = " . $db->toSql($type) . $sSQL_and;

			$db->execute($sSQL);
		} 
		else 
		{
			$sSQL = "INSERT INTO `" . $options["table_token"] . "`
					(
						`ID_user`
						, `type`
						, `token`
				";
			if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
				$sSQL .= " , `ID_domain`";
			$sSQL .= "
					)
					VALUES
					(
						" . $db->toSql($UserNID, "Number") . "
						, " . $db->toSql($type) . "
						, " . $db->toSql($access_token) . "
				";
			if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
				$sSQL .= " , " . $db->toSql($ID_domain);
			$sSQL .= "
					)";
			$db->execute($sSQL);
		}
		
		$user_permission = get_session("user_permission");
		$user_permission[$type] = $access_token;
		set_session("user_permission", $user_permission);
	}
	else
		$res = true;
	
	return $res;
}

function mod_security_accesstoken_check($type, $token, $ID_user = null, $ID_domain = null, $path_info = null)
{
	$token_status = null;
	
	switch ($type)
	{
		case "google":
			$client = mod_sec_social_get_google_client();
			$client->setAccessToken($token);
			$token_status = !$client->isAccessTokenExpired();
			break;
		
		case "facebook":
			//use Facebook\FacebookSession;

			/*FacebookSession::setDefaultApplication(MOD_SEC_SOCIAL_FACEBOOK_APPID, MOD_SEC_SOCIAL_FACEBOOK_SECRET);

			// If you already have a valid access token:
			$session = new FacebookSession($token);
			//$session = FacebookSession::newAppSession();

			// To validate the session:
			try {
				$session->validate();
			*/	$token_status = true;
			/*} catch (FacebookRequestException $ex) {
				// Session not valid, Graph API returned an exception with the reason.
				//echo $ex->getMessage();
				$token_status = false;
			} catch (\Exception $ex) {
				// Graph API returned info, but it may mismatch the current app or have expired.
				//echo $ex->getMessage();
				$token_status = false;
			}*/
			break;
	}
	
	if ($ID_user !== null && !$token_status)
		mod_security_accesstoken_clean ($ID_user, $type, $ID_domain, $path_info);

	return $token_status;
}

function mod_security_accesstoken_revoke($type, $token, $ID_user = null, $ID_domain = null, $path_info = null)
{
	if ($token === null)
	{
		if ($ID_user === null)
			ffErrorHandler::raise ("ID_user required in order to revoke an unspecified token", E_USER_ERROR, null, get_defined_vars());
		
		if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain !== null)
			$db = mod_security_get_db_by_domain($ID_domain);
		else
			$db = mod_security_get_main_db();

		$options = mod_security_get_settings($path_info);
		
		$sSQL = "SELECT
							*
						FROM
							`" . $options["table_token"] . "`
						WHERE 
							`" . $options["table_token"] . "`.`type` = " . $db->toSql($type) . "
							AND `" . $options["table_token"] . "`.`ID_user` = " . $db->toSql($ID_user, "Number");
		if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
			$sSQL .= " AND `ID_domain` = " . $db->toSql($ID_domain);
		$token = $db->lookup($sSQL, null, null, null, "token", "Text", true);

		if ($token === null || $token === false)
			return;
		
	}
	
	switch ($type)
	{
		case "google":
			$client = mod_sec_social_get_google_client();
			$client->setAccessToken($token);
			$client->revokeToken();
			break;
	}
	
	mod_security_accesstoken_clean($ID_user, $type, $ID_domain, $path_info);	
	return;
}

function mod_security_accesstoken_clean($ID_user, $type = null, $ID_domain = null, $path_info = null)
{
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain !== null)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();
	
	$options = mod_security_get_settings($path_info);
	
	$sSQL = "DELETE FROM `" . $options["table_token"] . "` WHERE `ID_user` = " . $db->toSql($ID_user);
	if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
		$sSQL .= " AND `ID_domain` = " . $db->toSql($ID_domain);
	
	if ($type !== null)
		$sSQL .= " AND `type` = " . $db->toSql($type);
	
	$db->execute($sSQL);
}

function mod_sec_social_get_google_client()
{
	$client = new Google_Client();
	
	$client->setApplicationName(MOD_SEC_SOCIAL_GOOGLE_APP_NAME);
	$client->setClientId(MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID);
	$client->setClientSecret(MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET);
	$client->setRedirectUri(MOD_SEC_SOCIAL_GOOGLE_CLIENT_REDIR_URI);
	
	$arrScope = explode(",", MOD_SEC_SOCIAL_GOOGLE_APPSCOPE);
	if(is_array($arrScope) && count($arrScope)) {
		$googleScope = array();
		foreach($arrScope AS $scope) {
			switch($scope) {
				case "PLUS_LOGIN":
					$googleScope[] = Google_Service_Oauth2::PLUS_LOGIN;
					break;
				case "PLUS_ME":
					$googleScope[] = Google_Service_Oauth2::PLUS_ME;
					break;
				case "USERINFO_EMAIL":
					$googleScope[] = Google_Service_Oauth2::USERINFO_EMAIL;
					break;
				case "USERINFO_PROFILE":
					$googleScope[] = Google_Service_Oauth2::USERINFO_PROFILE;
					break;
				default:
			}	
		}
		$client->setScopes($googleScope);
	}
	
	return $client;
}

function mod_sec_mykdf($data, $salt, $iterations = 1000)
{
	for ($i = 0; $i < $iterations; $i++)
	{
		$data = hash_hmac("sha256", $data, $salt);
	}
	return $data;
}

function mod_sec_decrypt_string($string)
{
	if (is_object($string) && $string instanceof ffData)
	{
		$decrypted = mod_sec_decrypt_string($string->getValue("Text"));
		$string->ori_value = $decrypted;
		$string->value_text = $decrypted;
		return $string;
	}
	
	if (is_object($string))
	{
		ffErrorHandler::raise("Unhandled Data", E_USER_ERROR, null, get_defined_vars());
	}
	
	if (!strlen($string))
		return $string;
	
	$globals = ffGlobals::getInstance("__mod_sec_crypt__");
	
	if (!isset($globals->_crypt_Ku_) || !isset($globals->_crypt_KSu_))
		ffErrorHandler::raise ("Crypt data not present", E_USER_ERROR, null, get_defined_vars());
	
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $globals->_crypt_Ku_, $string, MCRYPT_MODE_CBC, $globals->_crypt_KSu_), "\0");
}

function mod_sec_crypt_string($string)
{
	if (is_object($string) && $string instanceof ffData)
	{
		$crypted = mod_sec_crypt_string($string->getValue("Text"));
		$string->ori_value = $crypted;
		$string->value_text = $crypted;
		return $string;
	}
	
	if (is_object($string))
	{
		ffErrorHandler::raise("Unhandled Data", E_USER_ERROR, null, get_defined_vars());
	}
	
	if (!strlen($string))
		return $string;
	
	$globals = ffGlobals::getInstance("__mod_sec_crypt__");
	
	if (!isset($globals->_crypt_Ku_) || !isset($globals->_crypt_KSu_))
		ffErrorHandler::raise ("Crypt data not present", E_USER_ERROR, null, get_defined_vars());
	
	return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $globals->_crypt_Ku_, $string, MCRYPT_MODE_CBC, $globals->_crypt_KSu_);
}

function mod_sec_decrypt_concat($string)
{
	if (is_object($string) && $string instanceof ffData)
	{
		$decrypted = mod_sec_decrypt_concat($string->getValue("Text"));
		$string->ori_value = $decrypted;
		$string->value_text = $decrypted;
		return $string;
	}
	
	if (is_object($string))
	{
		ffErrorHandler::raise("Unhandled Data", E_USER_ERROR, null, get_defined_vars());
	}
	
	if (!strlen($string))
		return $string;
	
	$final = "";
	$offset = 0;
	
	while (true)
	{
		$next_begin = strpos($string, MOD_SEC_CRYPT_CONCAT_PREFIX , $offset);
		if ($next_begin === false)
		{
			$final .= substr($string, $offset);
			break;
		}
		else
		{
			$next_end = strpos($string, MOD_SEC_CRYPT_CONCAT_SUFFIX, $next_begin);
			if ($next_end === false)
			{
				$final .= substr($string, $offset);
				break;
			}
			
			$final .= substr($string, $offset, $next_begin - $offset);
			
			$slice = substr($string, $next_begin + (strlen(MOD_SEC_CRYPT_CONCAT_PREFIX)), $next_end - $next_begin - strlen(MOD_SEC_CRYPT_CONCAT_SUFFIX));
			$slice = mod_sec_decrypt_string($slice);
			$final .= $slice;
			
			$offset = $next_end + strlen(MOD_SEC_CRYPT_CONCAT_SUFFIX);
		}
	}
	
	return $final;
}

function mod_sec_get_avatar($avatar, $mode = null, $theme = null, $svg = false)
{
    $cm = cm::getInstance();

    if($avatar
        && (
            substr(strtolower($avatar), 0, 7) == "http://"
            || substr(strtolower($avatar), 0, 8) == "https://"
        )
    )
        $res = $avatar;
    elseif($avatar && is_file(FF_DISK_PATH . FF_UPDIR . $avatar))
	    $res = (substr(strtolower(CM_SHOWFILES), 0, 7) == "http://"
				|| substr(strtolower(CM_SHOWFILES), 0, 8) == "https://"
					? ""
					: FF_SITE_PATH
				) . CM_SHOWFILES . ($mode
	                            ? "/" . $mode
	                            : ""
            				) . $avatar;
    elseif($avatar && is_file(FF_DISK_PATH . $avatar))
        $res = FF_SITE_PATH . $avatar;
    else {
        $res = $cm->doEvent("mod_sec_no_avatar", array(&$mode, &$theme, &$svg));
        $res = end($res);
    }

    if($res === null) {
        if(!$svg && is_file(ff_getAbsDir(FF_THEME_DIR . "/" . $cm->oPage->getTheme()) . FF_THEME_DIR . "/" . $cm->oPage->getTheme() . "/images/noavatar.svg"))
            $res = ff_getThemePath($cm->oPage->getTheme()) . "/" . $cm->oPage->getTheme() . "/images/noavatar.svg";
        elseif(is_file(ff_getAbsDir(FF_THEME_DIR . "/" . $cm->oPage->getTheme()) . FF_THEME_DIR . "/" . $cm->oPage->getTheme() . "/images/noavatar.png"))
            $res = ($mode
                    ? CM_SHOWFILES . "/" . $mode
                    : ff_getThemePath($cm->oPage->getTheme())
                ) . "/" . $cm->oPage->getTheme() . "/images/noavatar.png";
        elseif($theme && !$svg && is_file(ff_getAbsDir(FF_THEME_DIR . "/" . $theme) . FF_THEME_DIR . "/" . $theme . "/images/noavatar.svg"))
            $res = ff_getThemePath($theme) . "/" . $theme . "/images/noavatar.svg";
        elseif($theme && is_file(ff_getAbsDir(FF_THEME_DIR . "/" . $theme) . FF_THEME_DIR . "/" . $theme . "/images/noavatar.png"))
            $res = ($mode
                    ? CM_SHOWFILES . "/" .$mode
                    : ff_getThemePath($theme)
                ) . "/" . $theme . "/images/noavatar.png";
        elseif(!$svg && is_file(ff_getAbsDir(FF_THEME_DIR . "/" . cm_getMainTheme()) . FF_THEME_DIR . "/" . cm_getMainTheme() . "/images/noavatar.svg"))
            $res = ff_getThemePath(cm_getMainTheme()) . "/" . cm_getMainTheme() . "/images/noavatar.svg";
        elseif(is_file(ff_getAbsDir(FF_THEME_DIR . "/" . cm_getMainTheme()) . FF_THEME_DIR . "/" . cm_getMainTheme() . "/images/noavatar.png"))
            $res = ($mode
                    ? CM_SHOWFILES . "/" . $mode
                    : ff_getThemePath(cm_getMainTheme())
                ) . "/" . cm_getMainTheme() . "/images/noavatar.png";
    }

    return $res;
}

// taken from: http://php.net/manual/en/function.session-decode.php#108037
class Session {
    public static function unserialize($session_data) {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::unserialize_php($session_data);
                break;
            case "php_binary":
                return self::unserialize_phpbinary($session_data);
                break;
            default:
                throw new Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }

    private static function unserialize_php($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    private static function unserialize_phpbinary($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            $num = ord($session_data[$offset]);
            $offset += 1;
            $varname = substr($session_data, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}

function modsec_getOauth2Server()
{
	if (ffIsset($_REQUEST, "__OAUTH2DEBUG__"))
	{
		$parts = explode("/", $_SERVER["REQUEST_URI"]);
		@mkdir(CM_CACHE_PATH . "/oauth2", 0777, true);
		$fp = fopen(CM_CACHE_PATH . "/oauth2/" . end($parts) . "_" . uniqid(), "w+");
		fwrite($fp, print_r($_REQUEST, true));
		fclose($fp);
	}
	
	static $server = null;
	
	if ($server !== null)
		return $server;
	
	$storage = new OAuth2\Storage\FF();

	$server = new OAuth2\Server($storage);

	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
	$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
	$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
	$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));

	return $server;
}

function modsec_OAuth2Error($response)
{
	$cm = cm::getInstance();
	
	$template_file = "error.html";
    $filename = cm_cascadeFindTemplate("/contents/oauth2/" . $template_file, "security");
    /*
	$filename = null;
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . rtrim($cm->path_info, "/") . "/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/oauth2/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/oauth2/" . $template_file, $cm->oPage->theme);
	*/
	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("http_domain", $_SERVER["HTTP_HOST"]);

	$cm->preloadApplets($tpl);
	$cm->parseApplets($tpl);

	$tpl->set_var("ret_url",			$_REQUEST["ret_url"]);
	$tpl->set_var("encoded_ret_url",	rawurlencode($_REQUEST["ret_url"]));
	$tpl->set_var("encoded_this_url",	rawurlencode($cm->oPage->getRequestUri()));
	$tpl->set_var("query_string",		$_SERVER["QUERY_STRING"]);
	$tpl->set_var("path_info",			$_SERVER["PATH_INFO"]);
	$tpl->set_var("app_title",			ffCommon_specialchars(CM_LOCAL_APP_NAME));
	
	$parameters = $response->getParameters();
	
	$tpl->set_var("error", ffCommon_specialchars($parameters["error"]));
	$tpl->set_var("error_description", ffCommon_specialchars($parameters["error_description"]));
	//$tpl->set_var("error_uri", $parameters["error_uri"]);
	
	if (isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"]))
		$tpl->parse("SectRetUrl", false);
	else
		$tpl->parse("SectPopup", false);

	$cm->oPage->layer = "empty";
	$cm->oPage->form_method = "POST";
	$cm->oPage->use_own_form = true;
	$cm->oPage->addContent($tpl);
}

function modsec_OAuth2_UserResourceController($scopeRequired, $callback)
{
	$server = modsec_getOauth2Server();

	$request = OAuth2\Request::createFromGlobals();
	$response = new OAuth2\Response();

	if (!$server->verifyResourceRequest($request, $response, $scopeRequired))
	{
		$response->send();
		exit;
	}

	$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	$UserNID = $token["user_id"];
	if (!intval($UserNID))
	{
		$response->setError(401, "wrong_token_type", "The token spupplied is not linked with any user");
		$response->send();
		exit;
	}

	$scopes = array_flip(explode(" ", $token["scope"]));
	
	$ret = call_user_func_array($callback, array($UserNID, $scopes, $request, $response, $server));
}

function modsec_OAuth2_ResourceController($scopeRequired, $callback)
{
	$server = modsec_getOauth2Server();

	$request = OAuth2\Request::createFromGlobals();
	$response = new OAuth2\Response();

	if (!$server->verifyResourceRequest($request, $response, $scopeRequired))
	{
		$response->send();
		exit;
	}

	$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	$scopes = array_flip(explode(" ", $token["scope"]));
	$ret = call_user_func_array($callback, array($scopes, $request, $response, $server));
}

function mod_sec_check_login($username, $password, $domain = null, $options = null, $permanent_session = MOD_SECURITY_SESSION_PERMANENT, $logged = false, $sError = null, $onlycheck = true)
{
	$valid = false;
	$sErrorCode = null;
	
	$cm = cm::getInstance();
	
	if ($options === null)
		$options = mod_security_get_settings($cm->path_info);

    $tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));
    $mod_sec_activation = ($cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)
        ? $cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)
        : $cm->router->getRuleById("mod_sec_activation")
    );

	if (strlen($username) && strlen($password))
	{
		$ID_domain = null;

		if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
		{
			if (strlen($domain))
			{
				$db = mod_security_get_main_db();

				$db->query("SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db->toSql($domain));
				if ($db->nextRecord())
				{
					$ID_domain = $db->getField("ID", "Number")->getValue();
				}
				else
				{
					$sErrorCode = MOD_SEC_ERROR_LOGIN_DOMAIN_NOT_FOUND;
				}
			}
			else
			{
				$ID_domain = 0;
			}
		}

		if ($sErrorCode === null)
		{
			// check username & password

			if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
				$db = mod_security_get_db_by_domain($ID_domain);
			else
				$db = mod_security_get_main_db();
            
            //ADDED-ATS*****************************************************************
            //Nel caso venga utilizzata autenticazione su server ldap
            if (LDAP_SERVER !== false){
                //verifica utente su ldap ( i parametri vengono verificati nella funzione)
                if (mod_security_ldapCheckUser (LDAP_SERVER, $username, $password) == true)
                {
                    //verifica utente su applicativo (verifica abilitazione)
                    $sSQL = "SELECT
                                                " . $options["table_name"] . ".*
                                FROM
                                                " . $options["table_name"] . "
                                WHERE
                                    (DATE(expiration) = '0000-00-00' OR DATE(expiration) > CURDATE())
                                    AND (" . $options["table_name"] . ".username = " . $db->toSql($username, "Text") . ")";
                    if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
                    $sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);

                    if (MOD_SEC_EXCLUDE_SQL)
                        $sSQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;

                    $db->query($sSQL);

                    if ($db->nextRecord())
                    {
                        if($db->getField("status", "Number", true) > 0)
                        {								
                            $userID = $db->getField("username", "Text")->getValue();
                            $userNID = $db->getField("ID", "Number")->getValue();

                            if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_LOGIN_DOMAIN)								
                                $ID_domain = $db->getField("ID_domains")->getValue();								

                            if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
                                $db2 = mod_security_get_db_by_domain($ID_domain);
                            else
                                $db2 = mod_security_get_main_db();
                            $sSQL2 = "UPDATE
                                        " . $options["table_name"] . "
                                    SET " . $options["table_name"] . ".lastlogin = " . $db2->toSql(new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA")) . "

                                    WHERE " . $options["table_name"] . ".ID = " . $db2->toSql($db->getField("ID")) . "
                                        ";
                            $db2->execute($sSQL2);

                            /* vecchia versione mod_sec
                            mod_security_create_session($userID, $userNID, $domain, $ID_domain, null, false, null, $password);
                            $cm->modules["security"]["events"]->doEvent("logging_in", array($ret_url));

                            if($cm->isXHR())
                            {
                                if(strlen($ret_url))
                                    ffRedirect($ret_url);
                                exit;
                            } 
                            else 
                            {
                                if (isset($cm->processed_rule["rule"]->options->noredirect))
                                    ffRedirect($_SERVER["REQUEST_URI"]);
                                else
                                    ffRedirect($ret_url);
                            }*/

                            //nuova versione                             
                            mod_security_create_session($userID, $userNID, $domain, $ID_domain, null, false, $cookiehash, $password);

                            $logged = true;

                            $cm->modules["security"]["events"]->doEvent("logging_in", array($cm->oPage->ret_url));

                        }
                        else
                            $sError = ffTemplate::_get_word_by_code("login_user_not_active");							
                    }
                    else
                        $sError = ffTemplate::_get_word_by_code("ldap_user_not_qualified");
                }
                else
                    $sError = ffTemplate::_get_word_by_code("ldap_login_wrong_user_or_password");					
            }		
            else {
            //**************************************************************************

                $userfound = false;
                $wrongpass = false;

                if (MOD_SEC_CRYPT)
                {

                    $sSQL = "SELECT
                                                " . $options["table_name"] . ".*
                                FROM
                                                " . $options["table_name"] . "
                                WHERE
                                    (DATE(expiration) = '0000-00-00' OR DATE(expiration) > CURDATE())
                                    AND (";
                    if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
                         $sSQL .= $options["table_name"] . ".username = " . $db->toSql($username, "Text");
                    if (MOD_SECURITY_LOGON_USERID == "both")
                         $sSQL .= " OR ";
                    if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")
                         $sSQL .= $options["table_name"] . ".email = " . $db->toSql($username, "Text");
                    $sSQL .= ")";

                    if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
                        $sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);

                    if (MOD_SEC_EXCLUDE_SQL)
                        $sSQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;

                    $db->query($sSQL);
                    if ($db->nextRecord())
                    {
                        // check for password

                        $data = $db->record;

                        $Vu = $data["crypt_vu"];
                        $salt = hex2bin($data["crypt_su"]);
                        $blockdata = hex2bin($data["crypt_eu"]);

                        //$hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 64);
                        $hash = mod_sec_mykdf($password, $salt, 1000);

                        $Vu1 = substr($hash, 0, 32);
                        if ($Vu1 !== $Vu)
                            $wrongpass = true;
                        else
                        {
                            $Vu2 = substr($hash, 32);
                            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $Vu2, $blockdata, MCRYPT_MODE_CBC, $salt);
                            $decrypted = rtrim($decrypted, "\0");

                            $parts = explode("|", $decrypted);

                            $globals_crypt = ffGlobals::getInstance("__mod_sec_crypt__");
                            $globals_crypt->_crypt_Ku_ = hex2bin($parts[0]);
                            $globals_crypt->_crypt_KSu_ = hex2bin($parts[1]);
                            $userfound = true;

                            $cookiehash = mod_sec_mykdf($password, $salt, 1);
                        }
                    }
                }
                else
                {
                    $sSQL = "SELECT
                                                " . $options["table_name"] . ".*
                                                , " . MOD_SEC_PASS_FUNC . "(" . $db->toSql($password) . ") AS encoded_password
                                FROM
                                                " . $options["table_name"] . "
                                WHERE
                                    (DATE(expiration) = '0000-00-00' OR DATE(expiration) > CURDATE())
                                    AND (";
                    if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
                         $sSQL .= $options["table_name"] . ".username = " . $db->toSql($username, "Text");
                    if (MOD_SECURITY_LOGON_USERID == "both")
                         $sSQL .= " OR ";
                    if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")
                         $sSQL .= $options["table_name"] . ".email = " . $db->toSql($username, "Text");
                    $sSQL .= ")";

                    if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
                        $sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);

                    if (MOD_SEC_EXCLUDE_SQL)
                        $sSQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;
                    $db->query($sSQL);
                    if ($db->nextRecord())
                    {
                        $userfound = true;
                        $wrongpass = $db->getField("password")->getValue() != $db->getField("encoded_password")->getValue();
                    }
                }

                if ($userfound)
                {
                    if($db->getField("status", "Number", true) > 0)
                    {
                        if(MOD_SECURITY_LDAP_SERVER) {
                            $wrongpass = false;
                            $ldapconn = ldap_connect(MOD_SECURITY_LDAP_SERVER);
                            if ($ldapconn) {
                                // binding to ldap server
                                $ldapbind = ldap_bind($ldapconn, $username, $password);
                                if (!$ldapbind)
                                    $wrongpass = true;

                            } else {
                                $wrongpass = true;
                            }
                        }                        

                        if(!$wrongpass ||
                            $db->getField("temp_password")->getValue() == $db->getField("encoded_password")->getValue())
                        {
                            if(!$wrongpass ||
                                    (time() - $db->getField("password_generated_at", "DateTime")->getValue("Timestamp")) < 3600)
                            {
                                $valid = true;

                                if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
                                    $userID = $db->getField("username", "Text")->getValue();
                                else
                                    $userID = $db->getField("email", "Text")->getValue();

                                $userNID = $db->getField("ID", "Number")->getValue();

                                if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_LOGIN_DOMAIN)
                                    $ID_domain = $db->getField("ID_domains")->getValue();

                                if (!$onlycheck)
                                {
                                    if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
                                        $db2 = mod_security_get_db_by_domain($ID_domain);
                                    else
                                        $db2 = mod_security_get_main_db();
                                    /*$sSQL2 = "UPDATE
                                                " . $options["table_name"] . "
                                            SET " . $options["table_name"] . ".lastlogin = " . $db2->toSql(new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA")) . "

                                            WHERE " . $options["table_name"] . ".ID = " . $db2->toSql($db->getField("ID")) . "
                                                ";
                                    $db2->execute($sSQL2);*/

                                    if ($db->getField("temp_password")->getValue() == $db->getField("encoded_password")->getValue())
                                    {
                                        $sSQL2 = "UPDATE
                                                    " . $options["table_name"] . "
                                                SET " . $options["table_name"] . ".password_used = '1'

                                                WHERE " . $options["table_name"] . ".ID = " . $db2->toSql($db->getField("ID")) . "
                                                    ";
                                        $db2->execute($sSQL2);
                                    }
                                    mod_security_create_session($userID, $userNID, $domain, $ID_domain, $permanent_session, false, $cookiehash);
                                    $logged = true;

                                    $cm->modules["security"]["events"]->doEvent("logging_in", array($cm->oPage->ret_url));
                                }
                            }
                            else
                                $sErrorCode = MOD_SEC_ERROR_LOGIN_TEMP_PASSWORD_EXPIRED;
                        }
                        else
                            $sErrorCode = MOD_SEC_ERROR_LOGIN_WRONG_USER_OR_PASSWORD;
                    }
                    else
                        $sErrorCode = MOD_SEC_ERROR_LOGIN_USER_NOT_ACTIVE . ($mod_sec_activation && $mod_sec_activation->reverse
                                        ? ' <a href="' . FF_SITE_PATH . $mod_sec_activation->reverse . '">' . ffTemplate::_get_word_by_code("login_active_link") . '</a>'
                                        : ""
                                    );
                }
                else
                    $sErrorCode = MOD_SEC_ERROR_LOGIN_USER_NOT_FOUND;
            }
		}
	}
	else
		$sErrorCode = MOD_SEC_ERROR_LOGIN_FILL_ALL_FIELDS;
	
	return array(
		"valid" => $valid,
		"error" => ($sErrorCode ? true : false),
		"error_code" => $sErrorCode,
		"logged" => $logged,
		"UserID" => $userID, 
		"UserNID" => $userNID, 
		"domain" => $domain, 
		"ID_domain" => $ID_domain,
		"cookiehash" => $cookiehash
	);
}

function _modsec_login_redirect($ret_url, $context)
{
	$cm = cm::getInstance();
	$res = $cm->modules["security"]["events"]->doEvent("onRedirect", array($ret_url, $context));
	$rc = end($res);
	if ($rc !== null)
	{
		if ($rc)
			exit;
		else
			return;
	}
	
	if($cm->isXHR())
	{
		if(strlen($ret_url) && !isset($cm->processed_rule["rule"]->options->noredirect))
			ffRedirect($ret_url);

		if ($cm->oPage->getXHRCtx())
			$cm->jsonAddResponse(array(
					"close" => true
				));

		cm::jsonParse($cm->json_response);
		exit;
	}
	else
	{
		if (isset($cm->processed_rule["rule"]->options->noredirect))
			ffRedirect($cm->oPage->getRequestUri());
		else
			ffRedirect(strlen($_REQUEST["ret_url"]) ? $_REQUEST["ret_url"] : $ret_url);
	}
}

function mod_sec_login_getTemplate($logged)
{
	$cm = cm::getInstance();
	$browser = $cm->getBrowser();
	if($browser["name"] == "MSIE" && $browser["majorver"] <= 6)
		$postfix = "_ie6";

	if ($logged)
	{
		if ($cm->modules["security"]["overrides"]["logout"]["tpl_file"])
			$template_file = $cm->modules["security"]["overrides"]["logout"]["tpl_file"];
		else
			$template_file = "logout" . $postfix . ".html";
	}
	else
	{
		if ($cm->modules["security"]["overrides"]["login"]["tpl_file"])
			$template_file = $cm->modules["security"]["overrides"]["login"]["tpl_file"];
		else
			$template_file = "login" . $postfix . ".html";
	}

	return $template_file;
}

function mod_sec_login_tpl_load($logged, $template_file = null) {
	$cm = cm::getInstance();

	if($template_file === null)
		$template_file = mod_sec_login_getTemplate($logged);

    $filename = cm_cascadeFindTemplate("/contents/login/" . $template_file, "security");



	/*$filename = null;
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . rtrim($cm->path_info, "/") . "/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/login/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/login/" . $template_file, $cm->oPage->theme);
*/
	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("http_domain", $_SERVER["HTTP_HOST"]);

	$cm->preloadApplets($tpl);
	$cm->parseApplets($tpl);

	$tpl->set_var("ret_url",			$cm->oPage->ret_url);
	$tpl->set_var("encoded_ret_url",	rawurlencode($cm->oPage->ret_url));
	$tpl->set_var("encoded_this_url",	rawurlencode($cm->oPage->getRequestUri()));
	$tpl->set_var("query_string",		$_SERVER["QUERY_STRING"]);
	$tpl->set_var("path_info",			$_SERVER["PATH_INFO"]);
	$tpl->set_var("app_title",			ffCommon_specialchars(CM_LOCAL_APP_NAME));

	return $tpl;
}

//ADDED-ATS*********************************************************************
//LDAP functions
function mod_security_ldapEscape ($val)
{
	//$val = str_replace(array('(', ')'), array('\28', '\29'), $val);
	for ($i = 0; $i<strlen($val); $i++) {
		$char = substr($val, $i, 1);
		if (ord($char)<32) {
			$hex = dechex(ord($char));
			if (strlen($hex) == 1) $hex = '0' . $hex;
			$val = str_replace($char, '\\' . $hex, $val);
		}
	}
	return $val;
}

function mod_security_ldapConnect ($ldap_server_addr, $username, $password)
{
	//verifica sulla password. Una password vuota genererebbe una connessione anonima ed aggirerebbe la verifica dell'utente
	if (!strlen($password)>0)
	{
		return false;
		exit;
	}
	else
	{
		//escape di username e password per sicurezza
		$username= mod_security_ldapEscape($username);
		$password= mod_security_ldapEscape($password);
	}
	$ldapconn = ldap_connect($ldap_server_addr);
	if ($ldapconn  == false)
	{
		ffErrorHandler::raise("Cannot connect to LDAP server", E_USER_ERROR, $this, get_defined_vars());
		exit;
	}
	else
	{
		//set connection options
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

		//bind
		$ldapbind = @ldap_bind($ldapconn, $username, $password);
		if ($ldapbind == true){
			return $ldapconn;
		}
		else{
			return false;
		}
	}
}

function mod_security_ldapCheckUser ($ldap_server_addr, $username, $password)
{
	$ldapconn = mod_security_ldapConnect ($ldap_server_addr, $username, $password);
	if ($ldapconn !== false)	
		return true;			
	else
		return false;
}

function mod_security_ldapGetUserInfo ($ldap_server_addr, $username, $password, $ldaptree, $fields)
{		
	$ldapconn = mod_security_ldapConnect ($ldap_server_addr, $username, $password);
	if($ldapconn !== false)
	{
		if(!is_array($fields))
		return false;

		//recupero informazioni utente			
		$filter = "(|(samaccountname=".$username."))";
		$search_result = ldap_search($ldapconn, $ldaptree, $filter);
		if ($search_result == false)
			return false;
		else
		{
			$data = ldap_get_entries($ldapconn, $search_result);				
			$result = array();
			for ($i=0; $i<count($fields); $i++)
			{
				$field = $fields[$i];
				$result[$field] = $data[0][$field][0];
			}
			return $result;										
		}
	}
	else
	{
		//se la connessione ldap non è andata a buon fine significa che l'utente non è più autenticato e viene distrutta la sessione
		mod_security_destroy_session(false);
	}
}

//******************************************************************************
//MS LOGIN functions
function apiRequest($url, $post = false, $headers = array()) {   
    $ch = curl_init($url);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if($post) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }   
    $headers[] = 'Accept: application/json';
    if(get_session('access_token')) {
        $token = get_session('access_token');
        $headers[] = 'Authorization: Bearer ' . $token->access_token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //var_dump(curl_getinfo($ch));
    $response = curl_exec($ch);
    //var_dump($response);
    return json_decode($response);    
}

//******************************************************************************