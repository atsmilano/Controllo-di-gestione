<?php
$options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ModSecUtenti";
$oRecord->resources[] = "ModSecUtenti";
$oRecord->title = "Utenti";
$oRecord->src_table = $options["table_name"];
$oRecord->addEvent("on_done_action", "ModSecUtenti_on_done_action");
$oRecord->insert_additional_fields["created"] = new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA");
$oRecord->update_additional_fields["modified"] = new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA");
if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
	$oRecord->db = array(mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]));
else
{
	$oRecord->insert_additional_fields["ID_domains"]= new ffData($_REQUEST["keys"]["ID"], "Number");
	$oRecord->additional_key_fields["ID_domains"] = new ffData($_REQUEST["keys"]["ID"], "Number");
}

$populate_edit_SQL = "SELECT
							" . $options["table_name"] . ".*
	";
if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
{
	foreach ($cm->modules["security"]["fields"] as $key => $value)
	{
		if (mod_security_is_default_field($key))
			continue;

		$populate_edit_SQL .= ", (SELECT 
											" . $options["table_dett_name"] . ".value
										FROM
											" . $options["table_dett_name"] . "
										WHERE
											" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
											AND " . $options["table_dett_name"] . ".field = " . $oRecord->db[0]->toSql($key) . "
								) AS " . $key . "
			";
			
		$populate_insert_SQL .= ", '' AS " . $key;
	}
	reset($cm->modules["security"]["fields"]);
}
$populate_edit_SQL .= "FROM
							" . $options["table_name"] . "
						WHERE
							" . $options["table_name"] . ".ID = " . $oRecord->db[0]->toSql($_REQUEST["keys"]["ID_user"]) . "
	";

$oRecord->populate_edit_SQL = $populate_edit_SQL;
$oRecord->auto_populate_edit = true;
$oRecord->del_action = "multi_delete";
$oRecord->del_multi_delete = array(
									"DELETE FROM " . $options["table_dett_name"] . " WHERE ID_users = [ID_user_VALUE]"
								);
								
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
{
	$oRecord->addContent(null, true, "account");
	$oRecord->addContent(null, true, "userinfo");
	$oRecord->addContent(null, true, "preferences");

	$account = "account";
	$userinfo = "userinfo";
	$preferences = "preferences";

	$oRecord->groups["account"]["title"] = "Account";
	$oRecord->groups["userinfo"]["title"] = "Dati Personali";
	$oRecord->groups["preferences"]["title"] = "Preferenze";
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_user";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(strlen(MOD_SEC_USER_AVATAR))
{
	if (!mod_security_is_defined_field("avatar"))
	{
	    $uid = $_REQUEST["keys"]["ID"];

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "avatar";
	    $oField->label = "Avatar";
	    $oField->base_type = "Text";
	    $oField->extended_type = "File";
	    $oField->file_storing_path = FF_DISK_PATH . FF_UPDIR . "/users/" . $uid;
	    $oField->file_temp_path = FF_DISK_PATH . FF_UPDIR . "/users";
	    $oField->file_max_size = 5000000;
	    $oField->file_show_filename = true; 
	    $oField->file_full_path = true;
	    $oField->file_check_exist = false;
	    $oField->file_normalize = true;
	    $oField->file_show_preview = true;
	    $oField->uploadify_model_thumb = "thumb";
	    $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
	    $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";
	    $oField->control_type = "file";
	    $oField->file_show_delete = true;
	    $oField->widget = "uploadifive";
	    $oRecord->addContent($oField, $account);
	}
}

if (!mod_security_is_defined_field("username") && (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "username";
	$oField->label = "Username";
	$oField->required = true;
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("email"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "email";
	$oField->label = "E-Mail";
	$oField->required = true;
	$oField->addValidator("email");
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("password"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "password";
	$oField->label = "Password";
	$oField->extended_type = "Password";
	$oField->crypt_method = "mysql_password";
	$oRecord->addContent($oField, $account);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "confpassword";
	$oField->label = "Conferma Password";
	$oField->extended_type = "Password";
	$oField->compare = "password";
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("status"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "status";
	$oField->label = "Stato";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array( 
									  array( new ffData("1"),  new ffData("Attivo"))
									, array( new ffData("0"),  new ffData("Sospeso"))
								);
	$oField->default_value = new ffData("1");
	if (get_session("UserLevel") == 1)
	{
		$oField->store_in_db = false;
		$oField->control_type = "label";
	}
	else
		$oField->required = true;
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("level"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "level";
	$oField->label = "Livello";
	$oField->extended_type = "Selection";
	if (get_session("UserLevel") == 1)
	{
		$oField->multi_pairs[] = array( new ffData("1"),  new ffData("Utente"));
		$oField->multi_pairs[] = array( new ffData("2"),  new ffData("Admin"));
		//$oField->multi_pairs[] = array( new ffData("3"),  new ffData("Super Admin"));
		$oField->store_in_db = false;
		$oField->control_type = "label";
	}
	else
	{
		$oField->required = true;
		$oField->multi_pairs[] = array( new ffData("1"),  new ffData("Utente"));
		$oField->multi_pairs[] = array( new ffData("2"),  new ffData("Admin"));
		/*if (get_session("UserLevel") == 3)
			$oField->multi_pairs[] = array( new ffData("3"),  new ffData("Super Admin"));*/
	}
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("expiration"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "expiration";
	$oField->label = "Scadenza";
	$oField->base_type = "Date";
	if (get_session("UserLevel") == 1)
	{
		$oField->store_in_db = false;
		$oField->control_type = "label";
	}
	else
		$oField->widget = "datepicker";
	$oRecord->addContent($oField, $account);
}

mod_security_add_custom_fields($oRecord, true);

$cm->oPage->addContent($oRecord);

function ModSecUtenti_on_done_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();

	$options = mod_security_get_settings($cm->path_info);
	$ID = $oRecord->key_fields["ID_user"]->value;
	if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
		$db = mod_security_get_db_by_domain($_REQUEST["keys"]["ID"]);
	else
		$db = ffDB_Sql::factory();
	
	if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
	{
		switch ($frmAction)
		{
			case "insert":
				foreach ($cm->modules["security"]["fields"] as $key => $value)
				{
					if (mod_security_is_default_field($key))
						continue;
					
					$sSQL = "INSERT INTO
									" . $options["table_dett_name"] . " (ID_users, field, value)
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
				foreach ($cm->modules["security"]["fields"] as $key => $value)
				{
					if (mod_security_is_default_field($key))
						continue;
					
                    $sSQL = "SELECT ID
                                FROM " . $options["table_dett_name"] . "
                                WHERE ID_users = " . $db->toSql($ID) . "
                                    AND field = " . $db->toSql($key);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $sSQL = "UPDATE " . $options["table_dett_name"] . "
                                    SET value = " . $db->toSql($oRecord->form_fields[$key]->value) . "
                                    WHERE ID_users = " . $db->toSql($ID) . "
                                        AND field = " . $db->toSql($key);
                        $db->execute($sSQL);
                    } else {
						$sSQL = "INSERT INTO
										" . $options["table_dett_name"] . " (ID_users, field, value)
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
				break;
		}
	}
	
	return FALSE;
}