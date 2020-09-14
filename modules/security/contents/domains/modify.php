<?php
$ext_db_connected = false;

if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && isset($_REQUEST["keys"]["ID"]))
{
	$res = mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]);
	if ($res !== false)
		$ext_db_connected = true;
	else
		mod_notifier_add_message_to_queue("Database not online. Fix db settings or wait for host come back to get extra options", MOD_NOTIFIER_WARNING);
}


$db = ffDB_Sql::factory();
$options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ModSecDomains";
$oRecord->title = "Domains";
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_domains";
$oRecord->addEvent("on_done_action", "ModSecDomains_on_done_action");
$oRecord->insert_additional_fields["creation_date"] = new ffData(date("Y-m-d H:i:s", time()), "DateTime");

$populate_edit_SQL = "SELECT
							cm_mod_security_domains.*
	";

if (isset($cm->modules["security"]["domains_fields"]) && count($cm->modules["security"]["domains_fields"]))
{
	foreach ($cm->modules["security"]["domains_fields"] as $key => $value)
	{
		$populate_edit_SQL .= ", (SELECT
											cm_mod_security_domains_fields.value
										FROM
											cm_mod_security_domains_fields
										WHERE
											cm_mod_security_domains_fields.ID_domains = cm_mod_security_domains.ID
											AND cm_mod_security_domains_fields.field = " . $oRecord->db[0]->toSql($key) . "
								) AS " . $key . "
			";

		$populate_insert_SQL .= ", '' AS " . $key;
	}
	reset($cm->modules["security"]["domains_fields"]);
}
$populate_edit_SQL .= "FROM
							cm_mod_security_domains
						WHERE
							cm_mod_security_domains.ID = " . $oRecord->db[0]->toSql($_REQUEST["keys"]["ID"]) . "
	";

$oRecord->populate_edit_SQL = $populate_edit_SQL;
$oRecord->auto_populate_edit = true;
$oRecord->del_action = "multi_delete";
$oRecord->del_multi_delete = array(
									"DELETE FROM  cm_mod_security_domains_fields WHERE ID_domains = [ID_VALUE]"
								);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = "Name";
$oField->required = true;
$oRecord->addContent($oField);

if (strlen(MOD_SEC_DOMAIN_COMPANY))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = MOD_SEC_DOMAIN_COMPANY;
	$oField->label = "Company";
	$oField->required = true;
	$oRecord->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Status";
$oField->extended_type = "Selection";
$oField->multi_pairs = array( 
								  array( new ffData("0"),  new ffData("Disabled"))
								, array( new ffData("1"),  new ffData("Enabled"))
							);
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "expiration_date";
$oField->label = "Scadenza";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->default_value = new ffdata((date("Y", time()) + 1) . "-" . date("m-d", time()), "Date", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_zone";
$oField->label = "Time Zone";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_security_timezones";
$oField->default_value = new ffData(16, "Number");
$oRecord->addContent($oField);

if(MOD_SEC_OWNER && !isset($_REQUEST["keys"]["ID"])) {
    $oRecord->addContent(null, true, "owner");
    $oRecord->groups["owner"]["title"] = "Owner";

    $oField = ffField::factory($cm->oPage);
    $oField->id = "username";
    $oField->label = "Username";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oField->required = true;
    $oRecord->addContent($oField, "owner");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "email";
    $oField->label = "E-Mail";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oField->required = true;
    $oField->addValidator("email");
    $oRecord->addContent($oField, "owner");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "password";
    $oField->label = "Password";
    $oField->required = true;
    $oField->extended_type = "Password";
    $oField->crypt_method = "mysql_password";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oRecord->addContent($oField, "owner");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "confpassword";
    $oField->label = "Conferma Password";
    $oField->extended_type = "Password";
    $oField->compare = "password";
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oRecord->addContent($oField, "owner");
}

if (MOD_SEC_PACKAGES)
{
	$oRecord->addContent(null, true, "package");
	$oRecord->groups["package"]["title"] = "Package";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_packages";
	$oField->label = "Tipo Pacchetto";
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT
									ID
									, CASE
											WHEN `type` = '0' THEN CONCAT(`name`, ' (pubblico)')
											WHEN `type` = '1' THEN CONCAT(`name`, ' (privato)')
											ELSE `name`
									END
								FROM
									" . CM_TABLE_PREFIX . "mod_security_packages
								ORDER BY
									`order`
		";
	$oRecord->addContent($oField, "package");
}

if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
{
	$oRecord->addContent(null, true, "db");
	$oRecord->groups["db"]["title"] = "Database";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_host";
	$oField->label = "Host";
	$oField->required = true;
	$oRecord->addContent($oField, "db");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_name";
	$oField->label = "Name";
	$oField->required = true;
	$oRecord->addContent($oField, "db");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_user";
	$oField->label = "User";
	$oField->required = true;
	$oRecord->addContent($oField, "db");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_pass";
	$oField->label = "Pass";
	$oRecord->addContent($oField, "db");
}

if (defined("MOD_SEC_MAXUSERS") && MOD_SEC_MAXUSERS)
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "max_users";
	$oField->label = "Limite Utenti";
	$oField->base_type = "Number";
	$oRecord->addContent($oField);
}

if (!isset($_REQUEST["keys"]["ID"]) || (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !$ext_db_connected))
{
	$cm->oPage->addContent($oRecord);
}
else
{
	$cm->oPage->addContent(null, true, "accounts");
	$cm->oPage->addContent($oRecord, "accounts", null, array("title" => "Dati Generici"));

	$oRecord->addContent(null, true, "additional");
	$oRecord->groups["additional"]["title"] = "Dati Aggiuntivi";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "owner";
	$oField->label = "Owner";
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
	{
		$oField->db = array(mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]));
		if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
			$oField->source_SQL = "SELECT ID, username FROM " . $options["table_name"];
		else
			$oField->source_SQL = "SELECT ID, email FROM " . $options["table_name"];
	}
	else
	{
		if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
			$oField->source_SQL = "SELECT ID, username FROM " . $options["table_name"] . " WHERE ID_domains = " . $db->toSql($_REQUEST["keys"]["ID"]);
		else
			$oField->source_SQL = "SELECT ID, email FROM " . $options["table_name"] . " WHERE ID_domains = " . $db->toSql($_REQUEST["keys"]["ID"]);
	}
	$oRecord->addContent($oField, "additional");

	mod_security_domain_add_custom_fields($oRecord);
	
	// USERS
	$strAddSearchFields = "";
	$strKeyDescrizione = "";
	if(strlen(MOD_SEC_USER_FIRSTNAME)) {
		if(strlen($strKeyDescrizione))
			$strKeyDescrizione .= ",";

		$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_FIRSTNAME);

		$strAddSearchFields .= ", (SELECT 
										" . $options["table_dett_name"] . ".value
									FROM
										" . $options["table_dett_name"] . "
									WHERE
										" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
										AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_FIRSTNAME) . "
								) AS firstname
					";
	}
	if(strlen(MOD_SEC_USER_LASTNAME)) {
		if(strlen($strKeyDescrizione))
			$strKeyDescrizione .= ",";

		$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_LASTNAME);

		$strAddSearchFields .= ", (SELECT 
										" . $options["table_dett_name"] . ".value
									FROM
										" . $options["table_dett_name"] . "
									WHERE
										" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
										AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_LASTNAME) . "
								) AS lastname
					";
	}
	if(strlen(MOD_SEC_USER_COMPANY)) {
		if(strlen($strKeyDescrizione))
			$strKeyDescrizione .= ",";

		$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_COMPANY);

		$strAddSearchFields .= ", (SELECT 
										" . $options["table_dett_name"] . ".value
									FROM
										" . $options["table_dett_name"] . "
									WHERE
										" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
										AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_COMPANY) . "
								) AS company
					";
	}

	$strKeyTelCell = "";
	$key_tel = "tel";
	$key_cell = "cell";
	if(strlen($key_tel)) {
		if(strlen($strKeyTelCell))
			$strKeyTelCell .= ",";

		$strKeyTelCell .= $db->toSql($key_tel);
	}
	if(strlen($key_cell)) {
		if(strlen($strKeyTelCell))
			$strKeyTelCell .= ",";

		$strKeyTelCell .= $db->toSql($key_cell);
	}

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "MainGrid";
	//$oGrid->title = "Utenti";
	if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
		$oGrid->order_default = "username";
	else
		$oGrid->order_default = "email";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify_user";
	$oGrid->record_id = "ModSecUtenti";
	$oGrid->addEvent("on_before_parse_row", "MainGrid_on_before_parse_row");
	$oGrid->source_SQL = "SELECT
								" . $options["table_name"] . ".* 
								, (SELECT 
										GROUP_CONCAT(IF(`" . $options["table_dett_name"] . "`.value = " . $db->toSql(MOD_SEC_USER_COMPANY) . "
														, CONCAT(' / ', `" . $options["table_dett_name"] . "`.value)
														, `" . $options["table_dett_name"] . "`.value
													) 
											SEPARATOR ' '
										)
									FROM
										`" . $options["table_dett_name"] . "`
									WHERE
										`" . $options["table_dett_name"] . "`.ID_users = `" . $options["table_name"] . "`.ID
										AND `" . $options["table_dett_name"] . "`.field IN (" . $strKeyDescrizione . ")
								) AS descrizione
								, (SELECT 
										GROUP_CONCAT(`" . $options["table_dett_name"] . "`.value SEPARATOR ' / ')
									FROM
										`" . $options["table_dett_name"] . "`
									WHERE
										`" . $options["table_dett_name"] . "`.ID_users = " . $options["table_name"] . ".ID
										AND `" . $options["table_dett_name"] . "`.field IN (" . $strKeyTelCell . ")
								) AS telcell
								$strAddSearchFields
		";
	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
	{
		$ext_db = mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]);
		$oGrid->db = array(&$ext_db);
		$oGrid->source_SQL .= " FROM
									" . $options["table_name"];
		if (MOD_SEC_EXCLUDE_SQL)
			$oGrid->source_SQL .= " WHERE " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL . " [AND] ";
		$oGrid->source_SQL .= "
								[WHERE]
								[HAVING]
								[ORDER]
			";
	}
	else
	{
		$oGrid->source_SQL .= " FROM
									" . $options["table_name"] . "
								WHERE
									" . $options["table_name"] . ".ID_domains = " . $oGrid->db[0]->toSql($_REQUEST["keys"]["ID"]);
		if (MOD_SEC_EXCLUDE_SQL)
			$oGrid->source_SQL .= " AND " . $options["table_name"] . ".ID " . MOD_SEC_EXCLUDE_SQL;
		$oGrid->source_SQL .= " [AND] [WHERE]
								[HAVING]
								[ORDER]
			";
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user";
	$oField->data_source = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi visualizzati
	if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "username";
		$oField->label = "Username";
		$oGrid->addContent($oField);
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "email";
	$oField->label = "E-Mail";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "status";
	$oField->label = "Status";
	$oField->extended_type = "Selection";
	$oField->multi_select_one = false;
	$oField->multi_pairs = array(
									array( new ffData("0"),  new ffData("Disabled")),
									array( new ffData("1"),  new ffData("Enabled"))
								);
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "descrizione";
	$oField->label = "Descrizione";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "telcell";
	$oField->label = "tel / cellulare";
	$oGrid->addContent($oField);

	// Campi di ricerca
	if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "username";
		$oField->label = "Username";
		$oField->src_operation 	= "[NAME] LIKE [VALUE]";
		$oField->src_prefix 	= "%";
		$oField->src_postfix 	= "%";
		$oGrid->addSearchField($oField);
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "status";
	$oField->label = "Status";
	$oField->extended_type = "Selection";
	$oField->multi_select_one_label = "All";
	$oField->multi_pairs = array(
									array( new ffData("0"),  new ffData("Disabled")),
									array( new ffData("1"),  new ffData("Enabled"))
								);
	$oGrid->addSearchField($oField);

	if(strlen(MOD_SEC_USER_FIRSTNAME))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "firstname";
		$oField->label = "Nome";
		$oField->src_operation 	= "[NAME] LIKE [VALUE]";
		$oField->src_prefix 	= "%";
		$oField->src_postfix 	= "%";
		$oGrid->addSearchField($oField);
	}

	if(strlen(MOD_SEC_USER_LASTNAME))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "lastname";
		$oField->label = "Cognome";
		$oField->src_operation 	= "[NAME] LIKE [VALUE]";
		$oField->src_prefix 	= "%";
		$oField->src_postfix 	= "%";
		$oGrid->addSearchField($oField);
	}
	
	if(strlen(MOD_SEC_USER_COMPANY))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "company";
		$oField->label = "Ragione Sociale";
		$oField->src_operation 	= "[NAME] LIKE [VALUE]";
		$oField->src_prefix 	= "%";
		$oField->src_postfix 	= "%";
		$oGrid->addSearchField($oField);
	}

	$cm->oPage->addContent($oGrid, "accounts", null, array("title" => "Utenti"));

	function MainGrid_on_before_parse_row($oComponent)
	{
		$db = $oComponent->db[0];

		/*if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && isset($_REQUEST["keys"]["ID"]))
			$db2 = mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]);
		else
			$db2 = null;

		$descrizione = mod_security_getUserInfo("firstname", $db->getField("ID"), $db2)->getValue() . " " . mod_security_getUserInfo("lastname", $db->getField("ID"), $db2)->getValue();
		if ($company = mod_security_getUserInfo("company", $db->getField("ID"), $db2)->getValue())
		{
			if ($descrizione != " ")
				$descrizione .= " / ";
			else
				$descrizione = "";

			$descrizione .= $company;
		}

		$oComponent->grid_fields["descrizione"]->setValue($descrizione);

		$telcell = $oComponent->db[0]->getField("tel")->getValue();
		if ($oComponent->db[0]->getField("cell")->getValue())
		{
			if ($telcell != " ")
				$telcell .= " / ";

			$telcell .= $oComponent->db[0]->getField("cell")->getValue();
		}

		$oComponent->grid_fields["telcell"]->setValue($telcell);*/

		if ($db->getField("ID")->getValue() == get_session("UserNID"))
			$oComponent->visible_delete_bt = false;
		else
			$oComponent->visible_delete_bt = true;
	}

	if (!count($cm->modules["restricted"]["settings"]))
	{
		$cm->oPage->addContent('<div class="warning">No settings defined</div>', "accounts", "Settings", array("title" => "Settings"));
		return;
	} else {
		$oRecord2 = ffRecord::factory($cm->oPage);
		$oRecord2->id = "settings";
		$oRecord2->title = "Settings";
		$oRecord2->buttons_options["insert"]["label"] = "Save Settings";
		$oRecord2->buttons_options["cancel"]["display"] = false;
		$oRecord2->addEvent("on_do_action", "settings_on_do_action");
		if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
			$oRecord2->db = array(mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]));
		else
			$addSql = " AND " . CM_TABLE_PREFIX . "mod_restricted_settings.ID_domains = " . $db->toSql($_REQUEST["keys"]["ID"]) . " ";

		foreach ($cm->modules["restricted"]["settings"] as $key => $value)
		{
			if (!isset($oRecord2->groups[$key]))
				$oRecord2->addContent(null, true, $key);

			$oRecord2->groups[$key]["title"] = $value->label;

			foreach (get_object_vars($value) as $subkey => $subvalue)
			{
				if (!is_object($subvalue))
					continue;
				
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
	                                    )
	                                , (
	                                    SELECT
	                                            " . CM_TABLE_PREFIX . "mod_restricted_settings.value
	                                        FROM
	                                            " . CM_TABLE_PREFIX . "mod_restricted_settings
	                                        WHERE
	                                            " . CM_TABLE_PREFIX . "mod_restricted_settings.name = " . $db->toSql($subkey) . "
	                                            " . $addSql . "
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

				switch (strtolower($subvalue->extended_type))
				{
					default:
						break;

					case "file":
						$oField->base_type = "Text";
						$oField->extended_type = "File";
						$oField->control_type = "file";
						//$oField->file_temp_path			= FF_DISK_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_temp_path);
						$oField->file_storing_path		= FF_DISK_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_storing_path);
						$oField->file_show_delete		= str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), ($subvalue->file_show_delete == "true" ? true : false));
						$oField->file_saved_view_url	= FF_SITE_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_saved_view_url);
						$oField->file_saved_preview_url = FF_SITE_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_saved_preview_url);
						//$oField->file_temp_view_url		= FF_SITE_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_temp_view_url);
						//$oField->file_temp_preview_url	= FF_SITE_PATH . str_replace("[ID_VALUE]", intval($_REQUEST["keys"]["ID"]), $subvalue->file_temp_preview_url);
						$oField->file_avoid_temporary	= true;
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

					case "selection":
						$oField->extended_type = "Selection";
						if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
							$oField->db = array(mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]));

                        if(strlen($subvalue->source_SQL)) {
                            $subvalue->source_SQL = str_replace("[ID_DOMAINS]", $globals->DomainID, $subvalue->source_SQL);
                            $subvalue->source_SQL = str_replace("[FF_PREFIX]", FF_PREFIX, $subvalue->source_SQL);
                            $subvalue->source_SQL = str_replace("[FF_SUPPORT_PREFIX]", FF_SUPPORT_PREFIX, $subvalue->source_SQL);
                            $subvalue->source_SQL = str_replace("[CM_TABLE_PREFIX]", CM_TABLE_PREFIX, $subvalue->source_SQL);
                            $subvalue->source_SQL = str_replace("[FF_LOCALE]", FF_LOCALE, $subvalue->source_SQL);
                            $subvalue->source_SQL = str_replace("[FF_DATABASE_NAME]", FF_DATABASE_NAME, $subvalue->source_SQL);
                        }
						$oField->source_SQL = str_replace("[ID_DOMAINS]", $_REQUEST["keys"]["ID"], $subvalue->source_SQL);
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

				$oField->widget = $subvalue->widget;

	            if (strlen($subvalue->default)) {
	                $oField->default_value = new ffData($subvalue->default);
	            }

				$oRecord2->addContent($oField, $key);
			}
		}
		reset($cm->modules["restricted"]["settings"]);

		$sSQL = "SELECT " . $sSQL;

		//$oRecord2->src_table = CM_TABLE_PREFIX . "mod_restricted_settings";
		$oRecord2->auto_populate_insert = true;
		$oRecord2->populate_insert_SQL = $sSQL;

		$cm->oPage->addContent($oRecord2, "accounts", "Settings");
	}
	
	function settings_on_do_action ($oRecord, $frmAction)
	{
		$cm = cm::getInstance();

		switch ($frmAction)
		{
			case "insert":
				if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
					$db = mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]);
				else
				{
					$db = mod_security_get_main_db();
					$addSql = "AND ID_domains = " . $db->toSql($_REQUEST["keys"]["ID"]);
				}
				
				foreach ($cm->modules["restricted"]["settings"] as $key => $value)
				{
					foreach (get_object_vars($value) as $subkey => $subvalue)
					{
						if (!is_object($subvalue))
							continue;

						$sSQL = "SELECT ID
                                    FROM " . CM_TABLE_PREFIX . "mod_restricted_settings
                                    WHERE name = " . $db->toSql($subkey) . "
                                        " . $addSql;
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_restricted_settings
	                                    SET
	                                            value = " . $db->toSql($oRecord->form_fields[$subkey]->value) . "
	                                    WHERE
	                                            name = " . $db->toSql($subkey) . "
	                                            " . $addSql;
                            $db->execute($sSQL);
                        } else {
							if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
								$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_restricted_settings
											(
												name
												, value
											) VALUES (
												" . $db->toSql($subkey) . "
												, " . $db->toSql($oRecord->form_fields[$subkey]->value) . "
											)
									";
							else
								$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_restricted_settings
											(
												ID_domains
												, name
												, value
											) VALUES (
												" . $db->toSql($_REQUEST["keys"]["ID"]) . "
												, " . $db->toSql($subkey) . "
												, " . $db->toSql($oRecord->form_fields[$subkey]->value) . "
											)
									";
							$db->execute($sSQL);
						}
					}
				}
				reset($cm->modules["restricted"]["settings"]);
				
				if (is_callable("mod_notifier_add_message_to_queue"))
					mod_notifier_add_message_to_queue("Settings Saved", MOD_NOTIFIER_SUCCESS);
				ffRedirect($_SERVER["REQUEST_URI"] . "&tabs_accounts=2");
				return true;
		}
	}
}

function ModSecDomains_on_done_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();
	$ID = $oRecord->key_fields["ID"]->value;
	$db = ffDB_Sql::factory();

	if (isset($cm->modules["security"]["domains_fields"]) && count($cm->modules["security"]["domains_fields"]))
	{
		switch ($frmAction)
		{
			case "confirmdelete":
				$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_fields WHERE ID_users IN(SELECT ID FROM " . CM_TABLE_PREFIX . "mod_security_users WHERE ID_domains = " . $db->toSql($ID) . ")";
				$db->execute($sSQL);
				
				$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users WHERE ID_domains = " . $db->toSql($ID);
				$db->execute($sSQL);
				
				$cm->modules["security"]->events->doEvent("domain_delete", array($ID));
				break;

			case "insert": // TOCHECK: mai utilizzata per via del wizard?
				foreach ($cm->modules["security"]["domains_fields"] as $key => $value)
				{
					$sSQL = "INSERT INTO
									cm_mod_security_domains_fields (ID_domains, field, value)
								VALUES
									(
									  " . $db->toSql($ID) . "
									, " . $db->toSql($key) . "
									, " . $db->toSql($oRecord->form_fields[$key]->value) . "
									)
							";
					$db->execute($sSQL);
				}
				break;

			case "update":
				foreach ($cm->modules["security"]["domains_fields"] as $key => $value)
				{
					$sSQL = "SELECT ID
                                FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                                WHERE 
                                	ID_domains = " . $db->toSql($ID) . "
									AND field = " . $db->toSql($key);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
						$sSQL = "UPDATE
										cm_mod_security_domains_fields
									SET
										value = " . $db->toSql($oRecord->form_fields[$key]->value) . "
									WHERE
										ID_domains = " . $db->toSql($ID) . "
										AND field = " . $db->toSql($key) . "
								";
						$db->execute($sSQL);
					} else {
						$sSQL = "INSERT INTO
										cm_mod_security_domains_fields (ID_domains, field, value)
									VALUES
										(
										  " . $db->toSql($ID) . "
										, " . $db->toSql($key) . "
										, " . $db->toSql($oRecord->form_fields[$key]->value) . "
										)
								";
						$db->execute($sSQL);
					}
				}
				
				$cm->modules["security"]->events->doEvent("domain_update", array($ID));
				break;
		}
	}

	switch ($frmAction)
	{
		case "confirmdelete":
			$cm->modules["security"]["events"]->doEvent("domain_delete", array($ID));
			break;

		case "update":
			$cm->modules["security"]["events"]->doEvent("domain_update", array($ID));
			break;

		case "insert": // TOCHECK: mai utilizzata per via del wizard?
            if(MOD_SEC_OWNER && isset($oRecord->form_fields["username"]) && isset($oRecord->form_fields["email"]) && isset($oRecord->form_fields["password"])) {
                $username = $oRecord->form_fields["username"]->getValue();
                $email = $oRecord->form_fields["email"]->getValue();
                $password = $oRecord->form_fields["password"]->getValue();
				
                $sSQL = "INSERT INTO cm_mod_security_users 
                        (
                            ID
                            , ID_domains
                            , username
                            , password
                            , level
                            , status
                            , email
                        )
                        VALUES
                        (
                            NULL
                            , " . $db->toSql($oRecord->key_fields["ID"]->value) . "
                            , " . $db->toSql($username) . "
                            , " . $db->toSql($password) . "
                            , '2'
                            , '1'
                            , " . $db->toSql($email) . "
                        )";
                
                $db->execute($sSQL);
            }
        
            if (!MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
                $addSql = " AND " . CM_TABLE_PREFIX . "mod_restricted_settings.ID_domains = " . $db->toSql($oRecord->key_fields["ID"]->value);
        
            if (@is_array($cm->modules["restricted"]["settings"]))
			{
				foreach ($cm->modules["restricted"]["settings"] as $key => $value)
				{
					foreach (get_object_vars($value) as $subkey => $subvalue)
					{
						if (!is_object($subvalue))
							continue;

						$subvalue->default = str_replace("[ACCOUNT_MAIL]", get_session("UserEmail"), $subvalue->default);
						$subvalue->default = str_replace("[ACCOUNT_USERNAME]", get_session("UserID"), $subvalue->default);
						$subvalue->default = str_replace("[SETTING_DOMAIN_NAME]", $oRecord->form_fields["nome"]->getValue(), $subvalue->default);
						if(MOD_SEC_OWNER) {
							$subvalue->default = str_replace("[SETTING_USERNAME]", $username, $subvalue->default);
							$subvalue->default = str_replace("[SETTING_MAIL]", $email, $subvalue->default);
						}

						$sSQL = "SELECT ID
                                    FROM " . CM_TABLE_PREFIX . "mod_restricted_settings
                                    WHERE name = " . $db->toSql($subkey) . "
                                        " . $addSql;
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_restricted_settings
	                                    SET
	                                            value = " . $db->toSql($subvalue->default) . "
	                                    WHERE
	                                            name = " . $db->toSql($subkey) . "
	                                            " . $addSql;
                            $db->execute($sSQL);
                        } else {
							if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
								$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_restricted_settings
											(
												name
												, value
											) VALUES (
												" . $db->toSql($subkey) . "
												, " . $db->toSql($subvalue->default) . "
											)
									";
							else
								$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_restricted_settings
											(
												ID_domains
												, name
												, value
											) VALUES (
												" . $db->toSql($oRecord->key_fields["ID"]->value) . "
												, " . $db->toSql($subkey) . "
												, " . $db->toSql($subvalue->default) . "
											)
									";
							$db->execute($sSQL);
						}
					}
				}
				reset($cm->modules["restricted"]["settings"]);
			}
        
			$cm->modules["security"]["events"]->doEvent("domain_insert", array($ID));
			
			ffRedirect($_SERVER["REQUEST_URI"] . "&keys[ID]=" . rawurlencode($oRecord->key_fields["ID"]->value->getValue()));
	}
}
