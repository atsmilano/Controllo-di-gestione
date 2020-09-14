<?php
$cm = cm::getInstance();

$cm->modules["restricted"]["options"] = array();
$cm->modules["restricted"]["menu"] = array();
$cm->modules["restricted"]["menu_bypath"] = array();
$cm->modules["restricted"]["layout_bypath"] = array();

if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING)
{
    $cache = ffCache::getInstance();
	// calculate hash
    $cache_key = (MOD_RES_MEM_CACHING_BYPATH
                    ? $cm->path_info
                    : "default"
                );


    $cm->modules["restricted"]["options"]           = $cache->get($cache_key, "/cm/mod/restricted/options");
    $cm->modules["restricted"]["menu"]              = $cache->get($cache_key, "/cm/mod/restricted/menu");
    $cm->modules["restricted"]["layout_bypath"]     = $cache->get($cache_key, "/cm/mod/restricted/layout_bypath");
    $cm->modules["restricted"]["settings"]          = $cache->get($cache_key, "/cm/mod/restricted/settings");

	if (is_array($cm->modules["restricted"]["menu"]) && count($cm->modules["restricted"]["menu"]))
	{
		foreach ($cm->modules["restricted"]["menu"] as $key => $value)
		{
			$cm->modules["restricted"]["menu_bypath"][$value["path"]][] =& $cm->modules["restricted"]["menu"][$key];
			if (is_array($value["elements"]) && count($value["elements"]))
			{
				foreach ($value["elements"] as $subkey => $subvalue)
				{
					if (strlen($subvalue["path"]))
						$cm->modules["restricted"]["menu_bypath"][$subvalue["path"]][] =& $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];
				}
				reset($value["elements"]);
			}
		}
		reset($cm->modules["restricted"]["menu"]);
	}
}

if (!$cm->modules["restricted"]["menu"])
{
	$cm->addEvent("on_load_module", "mod_restricted_cm_on_load_module");
	if (CM_ENABLE_MEM_CACHING && MOD_RES_MEM_CACHING) {
        $cache = ffCache::getInstance();
        // calculate hash
        $cache_key = (MOD_RES_MEM_CACHING_BYPATH
                    ? $cm->path_info
                    : "default"
                );

        $cache->set($cache_key, $cm->modules["restricted"]["options"], "/cm/mod/restricted/options");
        $cache->set($cache_key, $cm->modules["restricted"]["menu"], "/cm/mod/restricted/menu");
        $cache->set($cache_key, $cm->modules["restricted"]["layout_bypath"], "/cm/mod/restricted/layout_bypath");
        $cache->set($cache_key, $cm->modules["restricted"]["settings"], "/cm/mod/restricted/settings");
    }

	$tmp = cm_confCascadeFind(FF_DISK_PATH, "", "mod_restricted.xml");
	if (is_file($tmp))
		mod_restricted_load_config_file($tmp);

	mod_restricted_load_config_file(cm_confCascadeFind(CM_ROOT . "/conf", "/cm", "mod_restricted.xml"));

	mod_restricted_load_by_path();
}

if(MOD_RES_NAVTABS && !defined("FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID")) 
	define("FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID", true);

function mod_restricted_load_by_path()
{
	$cm = cm::getInstance();

	$script_path_parts = explode("/", $cm->path_info);
	$script_path_tmp = FF_DISK_PATH . "/conf/contents";
	$script_path_count = 0;
	while ($script_path_count < count($script_path_parts) && $script_path_tmp .= $script_path_parts[$script_path_count] . "/")
	{
		if (is_file($script_path_tmp . "mod_restricted.xml"))
		{
			mod_restricted_load_config_file($script_path_tmp . "mod_restricted.xml");
		}
		$script_path_count++;
	}
}

function mod_restricted_cm_on_load_module($cm, $mod)
{
	$tmp = cm_confCascadeFind(CM_MODULES_ROOT . "/" . $mod . "/conf", "/modules/" . $mod, "mod_restricted.xml");
	if (is_file($tmp))
		mod_restricted_load_config_file($tmp);
}

function mod_restricted_get_setting($name, $DomainID = null, $db = null)
{
	if ($DomainID === null)
	{
		$res = cm::getInstance()->modules["restricted"]["events"]->doEvent("get_domain");
		$rc = end($res);
		if ($rc)
			$DomainID = $rc;
		else if (is_callable("mod_auth_get_domain"))
		{
			$DomainID = mod_auth_get_domain();
		}
	}	
	
	if ($db === null) {
        $db = ffDB_Sql::factory();
	}

	$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_restricted_settings WHERE name = " . $db->toSql(new ffData($name));
    /*if ($DomainID !== null) // è corretto che vengano solo presi quelli generici (domain = 0) in assenza di specificazione
    {*/
		$sSQL .= " AND ID_domains = " . $db->toSql($DomainID);
	//}

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

function mod_restricted_get_all_setting($DomainID = null, $db = null)
{
    if ($db === null) {
        $db = ffDB_Sql::factory();
    }

    $sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_restricted_settings WHERE 1 ";

	if ($DomainID === null)
	{
		$res = cm::getInstance()->modules["restricted"]["events"]->doEvent("get_domain");
		$rc = end($res);
		if ($rc)
			$DomainID = $rc;
		else if (is_callable("mod_auth_get_domain"))
		{
			$DomainID = mod_auth_get_domain();
		}
	}	
	
    /*if ($DomainID !== null) // è corretto che vengano solo presi quelli generici (domain = 0) in assenza di specificazione
    {*/
        $sSQL .= " AND ID_domains = " . $db->toSql($DomainID);
    //}

    $db->query($sSQL);
    if ($db->nextRecord())
    {
        $res = array();
        do {
            $res[$db->getField("name", "Text", true)] = $db->getField("value", "Text", true);
        } while($db->nextRecord());
        
        return $res;
    }
    else
    {
        return null;
    }
}

function mod_restricted_set_setting($name, $value, $DomainID = null, $db = null)
{
	if ($db === null)
		$db = ffDB_Sql::factory();
	elseif (is_array($db))
		$db =& $db[0];

    if (!is_object($db))
        ffErrorHandler::raise("invalid db object", E_USER_ERROR, null, get_defined_vars());
	
    $sSQL = "UPDATE
					" . CM_TABLE_PREFIX . "mod_restricted_settings
				SET
					value = " . $db->toSql($value) . "
				WHERE
					name = " . $db->toSql($name) . "
		";

	if ($DomainID === null)
	{
		$res = cm::getInstance()->modules["restricted"]["events"]->doEvent("get_domain");
		$rc = end($res);
		if ($rc)
			$DomainID = $rc;
		else if (is_callable("mod_security_get_domain") && MOD_SEC_MULTIDOMAIN)
		{
			$DomainID = mod_security_get_domain();
		}
	}	
	
	if ($DomainID !== null)
	{
		$sSQL .= " AND ID_domains = " . $db->toSql($DomainID);
	}

	$db->execute($sSQL);
	if (!$db->affectedRows())
	{
		if ($DomainID !== null)
		{
			$fields = ", ID_domains";
			$values = ", " . $db->toSql($DomainID);
		}
		$sSQL = "INSERT INTO
					" . CM_TABLE_PREFIX . "mod_restricted_settings (
						value
						, name
						" . $fields . "
					) VALUES (
						" . $db->toSql($value) . "
						, " . $db->toSql($name) . "
						" . $values . "
					)
			";
		$db->execute($sSQL);
	}
}

function mod_restricted_load_config_file($file)
{
	$xml = new SimpleXMLElement("file://" . $file, null, true);
	mod_restricted_load_config($xml, $file);
}

function mod_restricted_load_config($xml, $file = null)
{
	$cm = cm::getInstance();
	
    static $sect_compare;
    if($file !== null && $sect_compare == "" && strpos($file, FF_DISK_PATH . "/conf/contents") === 0) {
        $sect_compare = ffCommon_dirname(substr($file, strlen(FF_DISK_PATH . "/conf/contents"))); 
    }

    //carica le env relative al modulo
    if (isset($xml->env)) {
        $cm->load_env_by_xml($xml->env);
    }

    if (isset($xml->sections) && count($xml->sections->children()))
    {
        foreach ($xml->sections->children() as $key => $value)
        {
            if ($key == "comment")
                continue;

            if(!$cm->modules["restricted"]["sections"][$key]) {
                $cm->modules["restricted"]["sections"][$key] = array();
            }
            foreach($value->attributes() AS $attr_key => $attr_value) {
                if(strpos($attr_key, "__") === 0)
                    continue;

                switch($attr_value) {
                    case "true":
                        $cm->modules["restricted"]["sections"][$key]["attributes"][$attr_key] = true;
                        break;
                    case "false":
                        $cm->modules["restricted"]["sections"][$key]["attributes"][$attr_key] = false;
                        break;
                    default:
                        $cm->modules["restricted"]["sections"][$key]["attributes"][$attr_key] = (string) $attr_value;
                }
            }
        }
        /*
		foreach ($xml->sections->children() as $key => $value)
		{
			if ($key == "comment")
				continue;

			$attrs = $value->attributes();
			if (!isset($cm->modules["restricted"]["sections"][$key]) || (string)$attrs->replace == "true")
			{
				$cm->modules["restricted"]["sections"][$key] = new ffSerializable($value);
			}
			else
			{
				foreach ($value->children() as $subkey => $subvalue)
				{
					if ($subkey == "comment")
						continue;

					if (isset($cm->modules["restricted"]["sections"][$key]->$subkey))
					{
						if (count($attrs = $subvalue->attributes()))
						{
							foreach ($attrs as $attr_key => $attr_value)
							{
								$cm->modules["restricted"]["sections"][$key]->$subkey->$attr_key = (string)$attr_value;
							}
						}
					}
					else
						$cm->modules["restricted"]["sections"][$key]->$subkey = new ffSerializable($subvalue);
				}
			}
		}*/
        //ffErrorHandler::raise ("gotcha2!", E_USER_ERROR, $this, get_defined_vars ());
    }

	if (isset($xml->menu) && count($xml->menu->children()))
	{
		foreach ($xml->menu->children() as $key => $value)
		{
			if ($key == "comment")
				continue;

			if (!isset($cm->modules["restricted"]["menu"][$key]))
			{
                $attrs = array();
                foreach($value->attributes() AS $attr_key => $attr_value) {
                    if(strpos($attr_key, "__") === 0)
                        continue;

                    switch($attr_value) {
                        case "true":
                            $attrs[$attr_key] = true;
                            break;
                        case "false":
                            $attrs[$attr_key] = false;
                            break;
                        default:
                            $attrs[$attr_key] = (string) $attr_value;
                    }
                }

				if($attrs["path"] != "/" && strlen($sect_compare) && strpos($attrs["path"], $sect_compare) !== 0 && strpos($cm->path_info, $sect_compare) === 0) {
                    continue;
                }

				$cm->modules["restricted"]["menu"][$key] = array();

				if (!strlen($attrs["path"]))
                    $attrs["path"] = strtolower("/" . $key);

				if (!strlen($attrs["label"]))
                    $attrs["label"] = $key;
				
				$cm->modules["restricted"]["menu"][$key]["name"] = $key;
				$cm->modules["restricted"]["menu"][$key]["path"] = $attrs["path"];
				$cm->modules["restricted"]["menu"][$key]["label"] = $attrs["label"];
				$cm->modules["restricted"]["menu"][$key]["badge"] = $attrs["badge"];
				//$cm->modules["restricted"]["menu"][$key]["is_heading"] = $is_heading;
				$cm->modules["restricted"]["menu"][$key]["icon"] = $attrs["icon"];
				if($attrs["actions"])
					$cm->modules["restricted"]["menu"][$key]["actions"] = explode(",", $attrs["actions"]);
				
				$cm->modules["restricted"]["menu"][$key]["dialog"] = $attrs["dialog"];
				$cm->modules["restricted"]["menu"][$key]["readonly"] = $attrs["readonly"];
                $cm->modules["restricted"]["menu"][$key]["readonly_skip"] = $attrs["readonly_skip"];

				if ($attrs["description"])
					$cm->modules["restricted"]["menu"][$key]["description"] = $attrs["description"];
                $cm->modules["restricted"]["menu"][$key]["description_skip"] = $attrs["description_skip"];

				$cm->modules["restricted"]["menu"][$key]["class"] = $attrs["class"];
				$cm->modules["restricted"]["menu"][$key]["hide"] = $attrs["hide"];
				$cm->modules["restricted"]["menu"][$key]["profiling_skip"] = $attrs["profiling_skip"];
				if ($attrs["profiling_default"] !== null)
				    $cm->modules["restricted"]["menu"][$key]["profiling_default"] = $attrs["profiling_default"];
				if (strlen($attrs["profiling_acl"]))
				    $cm->modules["restricted"]["menu"][$key]["profiling_acl"] = $attrs["profiling_acl"];

				$cm->modules["restricted"]["menu"][$key]["params"] = $attrs["params"];
				$cm->modules["restricted"]["menu"][$key]["globals_exclude"] = $attrs["globals_exclude"];
				if (strlen($attrs["acl"]))
					$cm->modules["restricted"]["menu"][$key]["acl"] = explode(",", $attrs["acl"]);
				if (strlen($attrs["redir"]))
					$cm->modules["restricted"]["menu"][$key]["redir"] = $attrs["redir"];

                if (strlen($attrs["position"]))
                    $cm->modules["restricted"]["menu"][$key]["position"] = $attrs["position"];
                if (strlen($attrs["settings"]))
                    $cm->modules["restricted"]["menu"][$key]["settings"] = $attrs["settings"];

                $cm->modules["restricted"]["menu"][$key]["collapse"] = $attrs["collapse"];
				
				$cm->modules["restricted"]["menu_bypath"][$attrs["path"]][] =& $cm->modules["restricted"]["menu"][$key];

				if($attrs["location"]) {
                    $cm->modules["restricted"]["menu"][$key]["location"] = $attrs["location"];

                    $cm->modules["restricted"]["sections"][$attrs["location"]]["elements"][$key] = $cm->modules["restricted"]["menu"][$key];
                    unset($cm->modules["restricted"]["sections"][$attrs["location"]]["elements"][$key]["elements"]);
				}
				if($attrs["favorite"]) {
                    $cm->modules["restricted"]["sections"]["favorite"]["elements"][$key] = $attrs;
				}
			}

			if (count($value))
			{
				foreach ($value as $subkey => $subvalue)
				{
					if (!isset($cm->modules["restricted"]["menu"][$key]["elements"][$subkey]))
					{
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey] = array();

                        $attrs = array();
                        foreach($subvalue->attributes() AS $attr_key => $attr_value) {
                            if(strpos($attr_key, "__") === 0)
                                continue;

                            switch($attr_value) {
                                case "true":
                                    $attrs[$attr_key] = true;
                                    break;
                                case "false":
                                    $attrs[$attr_key] = false;
                                    break;
                                default:
                                    $attrs[$attr_key] = (string) $attr_value;
                            }
                        }

						if (strlen($attrs["acl"])) {
                            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["acl"] = explode(",", $attrs["acl"]);
                        }
						if ($attrs["description"]) {
                            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["description"] = $attrs["description"];
                        }
						if ($attrs["jsaction"]) {
                            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["jsaction"] = $attrs["jsaction"];
                        }
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["hide"] = $attrs["hide"];
                        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["profiling_skip"] = $attrs["profiling_skip"];

                        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["params"] = $attrs["params"];

                        if (!strlen($attrs["path"])) {
                            $attrs["path"] = strtolower($cm->modules["restricted"]["menu"][$key]["path"] . "/" . $subkey);
                        }
                        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["path"] = $attrs["path"];

						if (!strlen($attrs["label"])) {
                            $attrs["label"] = $subkey;
                        }

						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["label"] = $attrs["label"];
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["badge"] = $attrs["badge"];
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["icon"] = $attrs["icon"];
						if($attrs["actions"]) {
                            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["actions"] = explode(",", $attrs["actions"]);
                        }
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["dialog"] = $attrs["dialog"];
						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["readonly"] = $attrs["readonly"];

						$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["class"] = $attrs["class"];

						$cm->modules["restricted"]["menu_bypath"][$attrs["path"]][] =& $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];

						if($attrs["location"] && isset($cm->modules["restricted"]["sections"][$attrs["location"]])) {
                            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["location"] = $attrs["location"];

						    $cm->modules["restricted"]["sections"][$attrs["location"]]["elements"][$subkey] = $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];
                        }
                        if($attrs["favorite"]) {
                            $cm->modules["restricted"]["sections"]["favorite"]["elements"][$key. "-" . $subkey] = $attrs;
                        }
					}
				}
			}
		}
	}

	if (isset($xml->layout) && count($xml->layout->children()))
	{
		foreach ($xml->layout->children() as $key => $value)
		{
			if ($key == "comment")
				continue;

			$attrs = $value->attributes();
			if ($key == "nolayout")
			{
				$path = (string)$attrs["path"];
				if (!strlen($path))
					ffErrorHandler::raise("mod_restricted: malformed xml (missing path parameter on layout/nolayout section)", E_USER_ERROR, null, get_defined_vars());

				$cm->modules["restricted"]["layout_bypath"][$path] = $key;
			}
			else
			{
				$path = (string)$attrs["path"];
				if (!strlen($path))
					$cm->modules["restricted"]["options"]["layout"][$key] = (string)$value;
				else
				{
					$name = (string)$attrs["name"];
					$cm->modules["restricted"]["layout_bypath"][$path][$key] = $name;
				}
			}
		}
	}

	if (isset($xml->settings) && count($xml->settings->children()))
	{
		foreach ($xml->settings->children() as $key => $value)
		{
			if ($key == "comment")
				continue;

			if (!isset($cm->modules["restricted"]["settings"][$key]))
			{
				$cm->modules["restricted"]["settings"][$key] = new ffSerializable($value);
			}
			else
			{
				foreach ($value->children() as $subkey => $subvalue)
				{
					if ($subkey == "comment")
						continue;

					if (isset($cm->modules["restricted"]["settings"][$key]->$subkey))
					{
						if (count($attrs = $subvalue->attributes()))
						{
							foreach ($attrs as $attr_key => $attr_value)
							{
								$cm->modules["restricted"]["settings"][$key]->$subkey->$attr_key = (string)$attr_value;
							}
						}
					}
					else
						$cm->modules["restricted"]["settings"][$key]->$subkey = new ffSerializable($subvalue);
				}
			}
		}
	}
}


function mod_restricted_add_menu_child($data) {
    $cm = cm::getInstance();
    
    if (ffIsset($data, "key"))					$key 		= $data["key"];
    if (ffIsset($data, "path"))					$path 		= $data["path"];
    if (ffIsset($data, "label"))				$label 		= $data["label"];
    if (ffIsset($data, "badge"))				$badge 		= $data["badge"];
    if (ffIsset($data, "icon"))					$icon 		= $data["icon"];
    if (ffIsset($data, "actions"))				$actions 	= $data["actions"];
    if (ffIsset($data, "params"))				$params 	= $data["params"];
    if (ffIsset($data, "visible"))				$visible 	= $data["visible"];
    if (ffIsset($data, "acl"))					$acl 		= $data["acl"];
    if (ffIsset($data, "location"))				$location 	= $data["location"];
    if (ffIsset($data, "position"))				$position 	= $data["position"];
    if (ffIsset($data, "settings"))				$settings 	= $data["settings"];
    if (ffIsset($data, "redir"))				$redir 		= $data["redir"];
    if (ffIsset($data, "class"))				$class 		= $data["class"];
    if (ffIsset($data, "readonly"))				$readonly 	        = $data["readonly"];
    if (ffIsset($data, "readonly_skip"))		$readonly_skip	    = $data["readonly_skip"];
    if (ffIsset($data, "description"))			$description 	    = $data["description"];
    if (ffIsset($data, "description_skip"))		$description_skip	= $data["description_skip"];
    if (ffIsset($data, "dialog"))				$dialog 	= $data["dialog"];
    if (ffIsset($data, "favorite"))				$favorite 	= $data["favorite"];
    if (ffIsset($data, "collapse"))				$collapse 	= $data["collapse"];
    if (ffIsset($data, "rel"))					$rel 		= $data["rel"];
    if (ffIsset($data, "profiling_skip"))		$profiling_skip 	= $data["profiling_skip"];
    //ADDED-ATS REFACTOR GESTIONE MODULI (24/09/2019)***************************
    if (ffIsset($data, "hide"))                $hide = $data["hide"];
    //**************************************************************************

    if (!isset($cm->modules["restricted"]["menu"][$key]))
    {
        $cm->modules["restricted"]["menu"][$key] = array();

        //$attrs = $value->attributes();

       /* $path = (string)$attrs["path"];
        $label = (string)$attrs["label"];
        $params = (string)$attrs["params"];
        $acl = (string)$attrs["acl"];
        $redir = (string)$attrs["redir"];
        */
        if (!strlen($path))
            $path = strtolower("/" . $key);

        if (!strlen($label))
            $label = $key;

        $cm->modules["restricted"]["menu"][$key]["path"] = $path;
        $cm->modules["restricted"]["menu"][$key]["label"] = $label;
        $cm->modules["restricted"]["menu"][$key]["badge"] = $badge;
        $cm->modules["restricted"]["menu"][$key]["icon"] = $icon;
        $cm->modules["restricted"]["menu"][$key]["actions"] = $actions;
        $cm->modules["restricted"]["menu"][$key]["dialog"] = $dialog;
        $cm->modules["restricted"]["menu"][$key]["readonly"] = $readonly;
        $cm->modules["restricted"]["menu"][$key]["readonly_skip"] = $readonly_skip;
        $cm->modules["restricted"]["menu"][$key]["description"] = $description;
        $cm->modules["restricted"]["menu"][$key]["description_skip"] = $description_skip;
        $cm->modules["restricted"]["menu"][$key]["class"] = $class;
        $cm->modules["restricted"]["menu"][$key]["params"] = $params;
        $cm->modules["restricted"]["menu"][$key]["visible"] = $visible;
        //ADDED-ATS REFACTOR GESTIONE MODULI (24/09/2019)***************************
        $cm->modules["restricted"]["menu"][$key]["hide"] = $hide;
        //**************************************************************************
        
        if (strlen($acl))
            $cm->modules["restricted"]["menu"][$key]["acl"] = explode(",", $acl);

        if ($location)
            $cm->modules["restricted"]["menu"][$key]["location"] = $location;
        if ($position)
            $cm->modules["restricted"]["menu"][$key]["position"] = $position;
        if ($settings)
            $cm->modules["restricted"]["menu"][$key]["settings"] = $settings;

        if (strlen($redir))
            $cm->modules["restricted"]["menu"][$key]["redir"] = $redir;

		if($collapse !== null)
			$cm->modules["restricted"]["menu"][$key]["collapse"] = $collapse;
        
        $cm->modules["restricted"]["menu"][$key]["profiling_skip"] = $profiling_skip;

        $cm->modules["restricted"]["menu"][$key]["rel"] = $rel;
        
        $cm->modules["restricted"]["menu_bypath"][$path][] =& $cm->modules["restricted"]["menu"][$key];
        
        if($favorite)
			$cm->modules["restricted"]["sections"]["favorite"][] =& $cm->modules["restricted"]["menu"][$key];

    }
    
}

function mod_restricted_add_menu_sub_element($data) {
    $cm = cm::getInstance();

    if (ffIsset($data, "key"))					$key 				= $data["key"];
    if (ffIsset($data, "subkey"))				$subkey 			= $data["subkey"];
    if (ffIsset($data, "path"))					$path 				= $data["path"];
    if (ffIsset($data, "label"))				$label 				= $data["label"];
    if (ffIsset($data, "badge"))				$badge 				= $data["badge"];
    if (ffIsset($data, "icon"))					$icon 				= $data["icon"];
    if (ffIsset($data, "actions"))				$actions 			= $data["actions"];
    if (ffIsset($data, "params"))				$params 			= $data["params"];
    if (ffIsset($data, "acl"))					$acl 				= $data["acl"];
    if (ffIsset($data, "location"))				$location 			= $data["location"];
    if (ffIsset($data, "position"))				$position 			= $data["position"];
    if (ffIsset($data, "settings"))				$settings 			= $data["settings"];
    if (ffIsset($data, "hide"))					$hide 				= $data["hide"];
    if (ffIsset($data, "description"))			$description		= $data["description"];
    if (ffIsset($data, "profiling_skip"))		$profiling_skip 	= $data["profiling_skip"];
    if (ffIsset($data, "readonly"))				$readonly 			= $data["readonly"];
    if (ffIsset($data, "dialog"))				$dialog 			= $data["dialog"];
    if (ffIsset($data, "class"))				$class 				= $data["class"];
    if (ffIsset($data, "favorite"))				$favorite 			= $data["favorite"];
    if (ffIsset($data, "rel"))					$rel 				= $data["rel"];

	if ($subkey && !isset($cm->modules["restricted"]["menu"][$key]["elements"][$subkey]))
	{
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey] = array();

		if (strlen($acl))
			$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["acl"] = explode(",", $acl);

		if ($description)
			$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["description"] = $description;

        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["hide"] = $hide;

        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["profiling_skip"] = $profiling_skip;

		if (strlen($location))
			$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["location"] = $location;
        if (strlen($position))
            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["position"] = $position;
        if (strlen($settings))
            $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["settings"] = $settings;
		//if (!$is_heading)
		//{
			$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["params"] = $params;

			if (!strlen($path))
				$path = strtolower($cm->modules["restricted"]["menu"][$key]["path"] . "/" . $subkey);
			$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["path"] = $path;
			$cm->modules["restricted"]["menu_bypath"][$path][] =& $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];
			
		//}

		if (!strlen($label))
			$label = $subkey;

		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["label"] = $label;
        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["badge"] = $badge;
        $cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["icon"] = $icon;
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["actions"] = $actions;
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["dialog"] = $dialog;
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["readonly"] = $readonly;
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["class"] = $class;
		$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["rel"] = $rel;
		//$cm->modules["restricted"]["menu"][$key]["elements"][$subkey]["is_heading"] = $is_heading;

		if($location && isset($cm->modules["restricted"]["sections"][$location])) {
			$cm->modules["restricted"]["sections"][$location]["elements"][$subkey] = $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];
		}		
		
		if($favorite)
            $cm->modules["restricted"]["sections"]["favorite"][] =& $cm->modules["restricted"]["menu"][$key]["elements"][$subkey];

	}
}

function mod_restricted_check_no_permission($params, $profile_check = null) {
//    $toskip = explode(",", MOD_SEC_PROFILING_SKIPSYSTEM);
//    $profile_check = MOD_SEC_PROFILING && (MOD_SEC_PROFILING_SKIPSYSTEM !== "*") && !in_array($key, $toskip) && !$value["profiling_skip"];

    if($profile_check === null) {
        $profile_check = !$params["profiling_skip"];
    }
    if($params["settings"] && defined($params["settings"])) {
        $user_setting = !constant($params["settings"]);
    }

    return (
        !mod_restricted_checkacl_bylevel($params["acl"])
        || ($profile_check && !mod_sec_checkprofile_bypath($params["path"]))
        || $user_setting
        //|| $params["hide"]
    );
}

function mod_restricted_checkacl_bylevel($acl, $level = null, $usernid = null, $path_info = null)
{
	if ($acl === null)
		return true;
	
	if ($level === null)
	{
		if ($usernid === null)
		{
            $level = get_session("UserLevel");
        }
    else
		{
			if ($path_info === null)
				$path_info = cm::getInstance()->path_info;
			
			$options = mod_security_get_settings($path_info);

			$db = ffDB_Sql::factory();
			$level = $db->lookup("SELECT level FROM " . $options["table_name"] . " WHERE ID = " . $db->toSql($usernid));
			if (!$level)
				ffErrorHandler::raise("wrong mod_restricted_checkacl_bylevel use, cannot determine level", E_USER_ERROR, null, get_defined_vars());
		}
	}
	
/*	if (defined("MOD_SECURITY_SESSION_STARTED") && $level == 3)
		return true;
*/	
	if (in_array(get_session("UserLevel"), $acl))
		return true;
}
/*
function mod_res_removelabel($topbar, $label)
{
	$mod_data =& cm::getInstance()->modules["restricted"];
	
	foreach ($mod_data["menu"][$topbar]["elements"] as $key => $value)
	{
		if ($value["is_heading"] && $value["label"] == $label)
		{
			unset($mod_data["menu"][$topbar]["elements"][$key]);
			break;
		}
	}
	reset($mod_data["menu"][$topbar]["elements"]);
}*/

function mod_res_remove_element($topbar, $navbar = null)
{
	$mod_data =& cm::getInstance()->modules["restricted"];

	if ($navbar !== null)
	{
		$path = $mod_data["menu"][$topbar]["elements"][$navbar]["path"];
		unset($mod_data["menu_bypath"][$path]);
		unset($mod_data["menu"][$topbar]["elements"][$navbar]);
	}
	else
	{
		if (isset($mod_data["menu"][$topbar]["elements"]) && count($mod_data["menu"][$topbar]["elements"]))
		{
			foreach ($mod_data["menu"][$topbar]["elements"] as $key => $value)
			{
				$path = $mod_data["menu"][$topbar]["elements"][$key]["path"];
				if (strlen($path))
				{
					//echo $key . " " . $path . "<br />";
					unset($mod_data["menu_bypath"][$path]);
				}
			}
		}
		$path = $mod_data["menu"][$topbar]["path"];
		unset($mod_data["menu_bypath"][$path]);
		unset($mod_data["menu"][$topbar]);
	}
}

function mod_res_disable_element($topbar, $navbar = null)
{
	$mod_data =& cm::getInstance()->modules["restricted"];

	if ($navbar !== null && ffArrIsset($mod_data, "menu", $topbar, "elements", $navbar))
	{
		if (!isset($mod_data["menu"][$topbar]["elements"][$navbar]["old_acl"]))
		{
			$mod_data["menu"][$topbar]["elements"][$navbar]["old_acl"] = $mod_data["menu"][$topbar]["elements"][$navbar]["acl"];
			$mod_data["menu"][$topbar]["elements"][$navbar]["acl"] = array(0);
		}
	}
	else if (ffArrIsset($mod_data, "menu", $topbar))
	{
		if (isset($mod_data["menu"][$topbar]["elements"]) && count($mod_data["menu"][$topbar]["elements"]))
		{
			foreach ($mod_data["menu"][$topbar]["elements"] as $key => $value)
			{
				if (!isset($mod_data["menu"][$topbar]["elements"][$key]["old_acl"]))
				{
					$mod_data["menu"][$topbar]["elements"][$key]["old_acl"] = $mod_data["menu"][$topbar]["elements"][$key]["acl"];
					$mod_data["menu"][$topbar]["elements"][$key]["acl"] = array(0);
				}
			}
			reset($mod_data["menu"][$topbar]["elements"]);
		}
		if (!isset($mod_data["menu"][$topbar]["acl"]))
		{
			$mod_data["menu"][$topbar]["old_acl"] = $mod_data["menu"][$topbar]["acl"];
			$mod_data["menu"][$topbar]["acl"] = array(0);
		}
	}
}

function mod_res_enable_element($topbar, $navbar = null)
{
	$mod_data =& cm::getInstance()->modules["restricted"];

	if ($navbar !== null)
	{
		if (isset($mod_data["menu"][$topbar]["elements"][$navbar]["old_acl"]))
		{
			$mod_data["menu"][$topbar]["elements"][$navbar]["acl"] = $mod_data["menu"][$topbar]["elements"][$navbar]["old_acl"];
			unset($mod_data["menu"][$topbar]["elements"][$navbar]["old_acl"]);
		}
	}
	else
	{
		if (isset($mod_data["menu"][$topbar]["elements"]) && count($mod_data["menu"][$topbar]["elements"]))
		{
			foreach ($mod_data["menu"][$topbar]["elements"] as $key => $value)
			{
				if (isset($mod_data["menu"][$topbar]["elements"][$key]["old_acl"]))
				{
					$mod_data["menu"][$topbar]["elements"][$key]["acl"] = $mod_data["menu"][$topbar]["elements"][$key]["old_acl"];
					unset($mod_data["menu"][$topbar]["elements"][$key]["old_acl"]);
				}
			}
			reset($mod_data["menu"][$topbar]["elements"]);
		}
		if (isset($mod_data["menu"][$topbar]["old_acl"]))
		{
			$mod_data["menu"][$topbar]["acl"] = $mod_data["menu"][$topbar]["old_acl"];
			unset($mod_data["menu"][$topbar]["old_acl"]);
		}
	}
}

function mod_res_access_denied($confirm_url = null)
{
	$cm = cm::getInstance();

	$res_path = (string)$cm->router->getRuleById("restricted")->reverse;
	
	if ($confirm_url === null)
	{
		if (isset($_REQUEST["ret_url"]))
			$confirm_url = $_REQUEST["ret_url"];
		else
			$confirm_url = FF_SITE_PATH . $res_path . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
	}
	
	access_denied($confirm_url, FF_SITE_PATH . $res_path . "/dialog");
}