<?php
if (!count($cm->modules["restricted"]["settings"]))
{
	settings_noone();
	return;
}

$path_info = basename($cm->real_path_info);

$globals = ffGlobals::getInstance("__mod_restricted__");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "settings";
//$oRecord->title = "Settings";
$oRecord->buttons_options["insert"]["label"] = "Save Settings";
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->addEvent("on_done_action", "settings_on_do_action");
$oRecord->skip_action = true;
$oRecord->tab = "right";

if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->getTheme() . "/javascript/cm-settings.js")) {
	$cm->oPage->tplAddJs("cmsettings", array(
		"file" => "cm-settings.js"
		, "path" => FF_THEME_DIR . "/" . $cm->oPage->getTheme() . "/javascript"
	));
}

$db = ffDB_Sql::factory();

$res = $cm->modules["restricted"]["events"]->doEvent("get_domain");
$rc = end($res);
if ($rc)
	$globals->DomainID = $rc;
else if (is_callable("mod_security_get_domain") && MOD_SEC_MULTIDOMAIN)
{
	$globals->DomainID = mod_security_get_domain();
}
else
	$globals->DomainID = 0;

$addSql = " AND " . CM_TABLE_PREFIX . "mod_restricted_settings.ID_domains = " . $db->toSql($globals->DomainID) . " ";

$added_fields = 0;

foreach ($cm->modules["restricted"]["settings"] as $key => $value)
{
	if (isset($value->acl))
	{
		$acl = explode(",", (string)$value->acl);
		if (!in_array(get_session("UserLevel"), $acl))
			continue;
	}
	if(strlen($path_info) && strpos($key, $path_info) !== 0) {
		continue;
	}
	if (!isset($oRecord->groups[$key]))
	{
		$oRecord->addContent(null, true, $key);

	    $oRecord->groups[$key]["title"] = ($value->label ? $value->label: $key);
	    if ($value->tab)
	    {
	    	$tabid = ($value->tab === true ? $key : $value->tab);
        	$oRecord->groups[$key]["tab"] = $tabid;
        	$oRecord->setTabTitle($tabid, ($value->label ? $value->label: $key));
        }
    }
    
	foreach (get_object_vars($value) as $subkey => $subvalue)
	{
		if (!is_object($subvalue) || is_callable($subvalue)) continue;
		
		if (isset($subvalue->acl))
		{
			$acl = explode(",", (string)$subvalue->acl);
			if (!in_array(get_session("UserLevel"), $acl))
				continue;
		}

		if (strlen($sSQL)) $sSQL .= " , ";

		$subvalue->default = str_replace("[ACCOUNT_MAIL]", get_session("UserEmail"), $subvalue->default);
		$subvalue->default = str_replace("[ACCOUNT_USERNAME]", get_session("UserID"), $subvalue->default);

		if($subvalue->default) {       
			$sSQL .= "(
						IF(EXISTS(
									SELECT " . CM_TABLE_PREFIX . "mod_restricted_settings.value
									FROM
										" . CM_TABLE_PREFIX . "mod_restricted_settings
									WHERE
										" . CM_TABLE_PREFIX . "mod_restricted_settings.name = " . $db->toSql($subkey) . "
										" . $addSql . "
									LIMIT 1
								)
							, (
								SELECT
										" . CM_TABLE_PREFIX . "mod_restricted_settings.value
									FROM
										" . CM_TABLE_PREFIX . "mod_restricted_settings
									WHERE
										" . CM_TABLE_PREFIX . "mod_restricted_settings.name = " . $db->toSql($subkey) . "
										" . $addSql . "
									LIMIT 1
							)
							, " . $db->toSql($subvalue->default) . "
						)
					) AS " . $db->toSql($subkey);
		} else {
			$sSQL .= "(
							SELECT
									" . CM_TABLE_PREFIX . "mod_restricted_settings.value
								FROM
									" . CM_TABLE_PREFIX . "mod_restricted_settings
								WHERE
									" . CM_TABLE_PREFIX . "mod_restricted_settings.name = " . $db->toSql($subkey) . "
									" . $addSql . "
								LIMIT 1 
						) AS " . $db->toSql($subkey);
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = $subkey;
		$oField->label = $subvalue->label;

		$span = $subvalue->span;
		if ($span < 1)
			$span = 1;

		$oField->span = $span;

		if ($subvalue->required)
			$oField->required = false;

		if (strlen($subvalue->base_type))
			$oField->base_type = $subvalue->base_type;

		if (strlen($subvalue->base_type))
			$oField->app_type = $subvalue->app_type;

		if(strlen($subvalue->source_SQL)) {
			$subvalue->source_SQL = str_replace("[ID_DOMAINS]", $globals->DomainID, $subvalue->source_SQL);
			$subvalue->source_SQL = str_replace("[FF_PREFIX]", FF_PREFIX, $subvalue->source_SQL);
            $subvalue->source_SQL = str_replace("[FF_SUPPORT_PREFIX]", FF_SUPPORT_PREFIX, $subvalue->source_SQL);
			$subvalue->source_SQL = str_replace("[CM_TABLE_PREFIX]", CM_TABLE_PREFIX, $subvalue->source_SQL);
			$subvalue->source_SQL = str_replace("[FF_LOCALE]", FF_LOCALE, $subvalue->source_SQL);
			$subvalue->source_SQL = str_replace("[FF_DATABASE_NAME]", FF_DATABASE_NAME, $subvalue->source_SQL);
		}

		if ($subvalue->main_db == "true")
		{
			$oField->db[0] = mod_security_get_main_db();
		}
		
		if (isset($subvalue->description))
			$oField->description = $subvalue->description;
		
		if ($subvalue->crypt)
			$oField->crypt = ($subvalue->crypt == "true" || intval($subvalue->crypt) ? true : false);
		if ($subvalue->crypt_concat)
			$oField->crypt_concat = ($subvalue->crypt_concat == "true" || intval($subvalue->crypt_concat) ? true : false);
		if ($subvalue->crypt_modsec)
			$oField->crypt_modsec = ($subvalue->crypt_modsec == "true" || intval($subvalue->crypt_modsec) ? true : false);
		
		if ($subvalue->multi_crypt)
			$oField->multi_crypt = ($subvalue->multi_crypt == "true" || intval($subvalue->multi_crypt) ? true : false);
		if ($subvalue->multi_crypt_concat)
			$oField->multi_crypt_concat = ($subvalue->multi_crypt_concat == "true" || intval($subvalue->multi_crypt_concat) ? true : false);
		if ($subvalue->multi_crypt_modsec)
			$oField->multi_crypt_modsec = ($subvalue->multi_crypt_modsec == "true" || intval($subvalue->multi_crypt_modsec) ? true : false);
		
		switch (strtolower($subvalue->extended_type))
		{
			default:
				break;

			case "text":
				$oField->extended_type = "Text";
				$oField->properties["style"]["width"] = "500px";
				$oField->properties["style"]["height"] = "200px";
				break;

			case "boolean":
				$oField->extended_type = "Boolean";
				$oField->checked_value = new ffData("1");
				$oField->unchecked_value = new ffData("0");
				break;

			case "password":
				$oField->extended_type = "Password";
				break;

			case "selection":
				$oField->extended_type = "Selection";
				$oField->source_DS = $subvalue->source_DS;
				$oField->source_SQL = $subvalue->source_SQL;
                if(isset($subvalue->select_one))
                    $oField->multi_select_one = ($subvalue->select_one == "true" ? true : false);
                if(isset($subvalue->select_one_label))
                    $oField->multi_select_one_label = $subvalue->select_one_label;
				
				if (isset($subvalue->pair) && count($subvalue->pair))
				{
					$oField->multi_pairs = array();
					if (count($subvalue->pair) === 1)
					{
						$oField->multi_pairs[] = array(new ffData($subvalue->pair->key, $oField->base_type), new ffData($subvalue->pair->value, $oField->get_app_type()));
					}
					else foreach ($subvalue->pair as $value)
					{
						$oField->multi_pairs[] = array(new ffData($value->key, $oField->base_type), new ffData($value->value, $oField->get_app_type()));
					}
				}
				
				break;
				
            case "selectionpath":
                $selection_file = glob(FF_DISK_PATH . $subvalue->source . "/*");
                if(is_array($selection_file) && count($selection_file)) {

                    $tmp_sql = "";
                    foreach($selection_file AS $real_file) {
                        if(strlen($tmp_sql))
                            $tmp_sql .= " UNION ";
                        
                        $tmp_sql .= "(SELECT " . $db->toSql(basename($real_file)) . " AS ID
                            , " . $db->toSql(basename($real_file)) . " AS name)";
                    }

                    $oField->extended_type = "Selection";
                    $oField->source_SQL = "SELECT * FROM (" . $tmp_sql . ") AS tbl_src ORDER BY tbl_src.name";
                    if(isset($subvalue->select_one))
                        $oField->multi_select_one = ($subvalue->select_one == "true" ? true : false);
                    if(isset($subvalue->select_one_label))
                        $oField->multi_select_one_label = $subvalue->select_one_label;
                }
                break;
				
			case "checkgroup":
				$oField->base_type = "Text";
				$oField->extended_type = "Selection";
				$oField->source_SQL = $subvalue->source_SQL;
				$oField->control_type = "input";
				$oField->widget = "checkgroup";
				$oField->grouping_separator = ",";
				break;
			
			case "activecombo":
			case "activecomboex":
			case "actex":
				// OBSOLETO
				$oField->widget = "actex";
				$oField->actex_update_from_db = true;
				$oField->source_SQL = $subvalue->source_SQL;
				if ($subvalue->main_db == "true")
					$oField->actex_use_main_db = true;
                if(isset($subvalue->select_one))
                    $oField->multi_select_one = ($subvalue->select_one == "true" ? true : false);
                if(isset($subvalue->select_one_label))
                    $oField->multi_select_one_label = $subvalue->select_one_label;
				break;
				
			case "file":
				$oField->extended_type = "File";
				$oField->file_temp_path			= mod_res_extended_type_file_parse($subvalue->file_temp_path);
				$oField->file_storing_path		= mod_res_extended_type_file_parse($subvalue->file_storing_path);
				$oField->file_saved_view_url	= mod_res_extended_type_file_parse($subvalue->file_saved_view_url);
				$oField->file_saved_preview_url = mod_res_extended_type_file_parse($subvalue->file_saved_preview_url);
				$oField->file_temp_view_url		= mod_res_extended_type_file_parse($subvalue->file_temp_view_url);
				$oField->file_temp_preview_url	= mod_res_extended_type_file_parse($subvalue->file_temp_preview_url);
				break;
		}
		
		switch (strtolower($subvalue->widget))
		{
			case "activecomboex":
			case "actex":
				$oField->source_DS = $subvalue->source_DS;
				$oField->source_SQL = $subvalue->source_SQL;
				$oField->actex_update_from_db = $subvalue->actex_update_from_db;
				$oField->actex_dialog_url = $subvalue->actex_dialog_url;
				$oField->resources = array_merge($oField->resources, explode(",", $subvalue->resources));
				if (isset($subvalue->actex_use_main_db))		$oField->actex_use_main_db = $subvalue->actex_use_main_db;
				if (isset($subvalue->select_one))				$oField->select_one = ($subvalue->select_one == "true" ? true : false);
				if (isset($subvalue->multi_select_one_label))	$oField->select_one = $subvalue->multi_select_one_label;
				break;
		}

		switch (strtolower($subvalue->control_type))
		{
			default:
				break;

			case "radio":
				$oField->control_type = "radio";
				break;
		}
		
		if(!strlen($oField->widget))
			$oField->widget = $subvalue->widget;


		$oRecord->addContent($oField, $key);
		$added_fields++;
	}
}
reset($cm->modules["restricted"]["settings"]);

if (!$added_fields)
{
	settings_noone();
	return;
}

if (!strlen($sSQL))
	ffErrorHandler::raise ("unable to load settings", E_USER_ERROR, null, get_defined_vars ());

$sSQL = "SELECT " . $sSQL;

//$oRecord->src_table = CM_TABLE_PREFIX . "mod_restricted_settings";
$oRecord->auto_populate_insert = true;
$oRecord->populate_insert_SQL = $sSQL;

$cm->oPage->addContent($oRecord);

function settings_on_do_action ($oRecord, $frmAction)
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("__mod_restricted__");

	switch ($frmAction)
	{
		case "insert":
			$db = ffDB_Sql::factory();
			foreach ($cm->modules["restricted"]["settings"] as $key => $value)
			{
				foreach (get_object_vars($value) as $subkey => $subvalue)
				{
					if (!is_object($subvalue) || !isset($oRecord->form_fields[$subkey])) continue;
					
					$tmp_val = $oRecord->form_fields[$subkey]->value;
					if ($oRecord->form_fields[$subkey]->crypt)
					{
						if ($oRecord->form_fields[$subkey]->crypt_modsec)
						{
							$tmp_val = mod_sec_crypt_string($tmp_val);
						}
					}

					$sSQL = "SELECT ID
								FROM " . CM_TABLE_PREFIX . "mod_restricted_settings
								WHERE name = " . $db->toSql($subkey) . "
									AND ID_domains = " . $db->toSql($globals->DomainID);
					$db->query($sSQL);
					if($db->nextRecord()) {
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_restricted_settings
												SET
														value = " . $db->toSql($tmp_val) . "
												WHERE
														name = " . $db->toSql($subkey) . "
														AND
														ID_domains = " . $db->toSql($globals->DomainID) . "
								";
						$db->execute($sSQL);
					} else {
						$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_restricted_settings
												(
														ID_domains
														, name
														, value
												) VALUES (
														" . $db->toSql($globals->DomainID) . "
														, " . $db->toSql($subkey) . "
														, " . $db->toSql($tmp_val) . "
												)
								";
						$db->execute($sSQL);
					}
				}
			}
			reset($cm->modules["restricted"]["settings"]);

			if (is_callable("mod_notifier_add_message_to_queue"))
				mod_notifier_add_message_to_queue("Settings Saved", MOD_NOTIFIER_SUCCESS);
			ffRedirect($_SERVER["REQUEST_URI"]);
			break;
	}
}

function settings_noone()
{
	$cm = cm::getInstance();
	if (function_exists("mod_notifier_add_message_to_queue"))
		mod_notifier_add_message_to_queue("Nessun settaggio disponibile per quest'utenza", MOD_NOTIFIER_WARNING);
	else
	$cm->oPage->fixed_pre_content = <<<EOD
<div class="warning">Nessun settaggio disponibile per quest'utenza</div>
EOD;
}

function mod_res_extended_type_file_parse($url)
{
	$globals = ffGlobals::getInstance("__mod_restricted__");
	$url = str_replace("[FF_DISK_PATH]", FF_DISK_PATH, $url);
	$url = str_replace("[FF_SITE_PATH]", FF_SITE_PATH, $url);
	$url = str_replace("[FF_PHP_EXT]", FF_PHP_EXT, $url);
	$url = str_replace("[ID_DOMAINS]", $globals->DomainID, $url);
	return $url;
}