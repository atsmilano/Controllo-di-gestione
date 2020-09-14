<?php
if (!mod_security_check_session(false) || get_session("UserID") == MOD_SEC_GUEST_USER_NAME)
	ffRedirect($cm->oPage->site_path . /*$cm->oPage->page_path . */"/login/?ret_url=" . rawurlencode($_SERVER["REQUEST_URI"]));

$_REQUEST["keys"]["ID"] = get_session("UserNID");

if(!isset($_REQUEST["ret_url"]))
	$cm->oPage->ret_url = $_SERVER["REQUEST_URI"];

$options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->title = ffTemplate::_get_word_by_code("user_profile");
$oRecord->src_table = $options["table_name"];
$oRecord->allow_delete = false;
$oRecord->buttons_options["cancel"]["display"] = false;

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

	}
	reset($cm->modules["security"]["fields"]);
}
$populate_edit_SQL .= "FROM
							" . $options["table_name"] . "
						WHERE
							" . $options["table_name"] . ".ID = " . $oRecord->db[0]->toSql($_REQUEST["keys"]["ID"]) . "
	";


$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");
$oRecord->populate_edit_SQL = $populate_edit_SQL;
$oRecord->auto_populate_edit = true;
$oRecord->del_action = "multi_delete";
$oRecord->del_multi_delete = array(
									"DELETE FROM " . $options["table_dett_name"] . " WHERE ID_users = [ID_VALUE]"
								);
if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_is_admin())
	$oRecord->db = array(mod_security_get_main_db());

$account = null;
$userinfo = null;
$preferences = null;

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
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(strlen(MOD_SEC_USER_AVATAR) && !mod_security_is_defined_field(MOD_SEC_USER_AVATAR))
{
	$uid = $_REQUEST["keys"]["ID"];

	$oField = ffField::factory($cm->oPage);
	$oField->id = MOD_SEC_USER_AVATAR;
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

	if(get_session("UserLevel") > 1)
	{
		$oField->control_type = "file";
		$oField->file_show_delete = true;
		
		$oField->widget = "uploadifive";
	} 
	else 
	{
	    $oField->control_type = "picture_no_link";
	}
	$oRecord->addContent($oField, $account);
}	

if (!mod_security_is_defined_field("username") && (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "username";
	$oField->label = "Username";
    if(MOD_SEC_PROFILE_USERNAME_READONLY)
        $oField->control_type = "label";
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("email"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "email";
	$oField->label = "E-Mail";
	$oField->required = true;
	$oField->addValidator("email");
	if (MOD_SEC_CRYPT && MOD_SEC_CRYPT_EMAIL)
	{
		$oField->crypt = true;
		$oField->crypt_modsec = true;
	}
	$oRecord->addContent($oField, $account);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "confirmemail";
	$oField->label = "Conferma E-Mail";
	$oField->compare = "email";
	$oRecord->addContent($oField, $account);
}

if (!mod_security_is_defined_field("password"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "password";
	$oField->label = "Password";
	$oField->extended_type = "Password";
	if (MOD_SEC_CRYPT)
	{
		$oField->store_in_db = false;
	}
	else
	{
		switch (MOD_SEC_PASS_FUNC)
		{
			case "MD5":
				$oField->crypt_method = "MD5";
				break;

			default:
				$oField->crypt_method = "mysql_password";
				break;
		}
	}
	$oField->addValidator("password");
	$oRecord->addContent($oField, $account);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "confpassword";
	$oField->label = "Conferma Password";
	$oField->extended_type = "Password";
	$oField->compare = "password";
	$oRecord->addContent($oField, $account);
}

/*if (MOD_SECURITY_LOGON_USERID == "email")
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "email";
	$oField->label = "E-Mail";
	$oField->required = true;
	$oField->addValidator("email");
	$oRecord->addContent($oField, $account);
}*/

mod_security_add_custom_fields($oRecord);

$cm->oPage->addContent($oRecord);

if (MOD_SEC_OAUTH2_SERVER)
{
	$db = mod_security_get_main_db();

	$oRecord->addContent(null, true, "rel_users");
	$oRecord->groups["rel_users"]["title"] = "Authorized Apps";

	$obj2 = ffGrid::factory($cm->oPage);
	$obj2->id = "oauth-rel-users";
	$obj2->title = "Authorized Apps";
	$obj2->resources[] = "oauth_rel_users";
	$obj2->source_SQL = "SELECT
									`oauth_rel_users`.*
									, `oauth_clients`.`description`
								FROM
									`oauth_rel_users`
									INNER JOIN `oauth_clients` ON
										`oauth_rel_users`.`client_id` = `oauth_clients`.`client_id`
								WHERE
									`oauth_rel_users`.`ID_user` = " . $db->toSql(get_session("UserNID")) . "
									AND `oauth_rel_users`.`granted` = 1
									[AND] [WHERE]
								[HAVING]
								[ORDER]
			";
	$obj2->record_id = "oauth-rel-users-modify";
	$obj2->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
	$obj2->order_default = "client_id";
	$obj2->display_new = false;
	$obj2->display_delete_bt = false;
	$obj2->display_edit_url = false;

	$field = ffField::factory($cm->oPage);
	$field->id = "client_id";
	$obj2->addKeyField($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "description";
	$field->label = "Description";
	$obj2->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "when";
	$field->label = "When";
	$field->base_type = "DateTime";
	$obj2->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "by";
	$field->label = "By (IP address)";
	$obj2->addContent($field);

	$bt = ffButton::factory($cm->oPage);
	$bt->id = "revoke";
	$bt->label = "Revoke";
	$bt->jsaction = "ff.modules.security.oauth2.revokeApp('[client_id_VALUE]')";
	$bt->class .= " noactivebuttons";
	$obj2->addGridButton($bt);

	$cm->oPage->tplAddJs("ff.modules.security.oauth2");

	$cm->oPage->addContent($obj2);
	$oRecord->addContent($obj2, "rel_users");
}

function MainRecord_on_done_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();

	$options = mod_security_get_settings($cm->path_info);
	$ID = $oRecord->key_fields["ID"]->value;
	
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_is_admin())
		$db = mod_security_get_main_db();
	else
		$db = ffDB_Sql::factory();

	if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
	{
		switch ($frmAction)
		{
			case "update":
				if (
						MOD_SEC_CRYPT 
						&& ($frmAction == "insert" || $frmAction == "update") 
						&& strlen($oRecord->form_fields["password"]->value->getValue())
					)
				{
					$globals_crypt = ffGlobals::getInstance("__mod_sec_crypt__");

					// generate new crypt stuff

					$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

					$salt = mcrypt_create_iv($iv_size, MCRYPT_RAND);
					$password = $oRecord->form_fields["password"]->value->getValue();

					$hash = mod_sec_mykdf($password, $salt, 1000);
					$Vu1 = substr($hash, 0, 32);
					$Vu2 = substr($hash, 32);

					$Eu = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $Vu2, bin2hex($globals_crypt->_crypt_Ku_) . "|" . bin2hex($globals_crypt->_crypt_KSu_), MCRYPT_MODE_CBC, $salt);

					$sSQL = "UPDATE " .  $options["table_name"] . " SET
									`crypt_vu` = " . $db->toSql($Vu1) . "
									, `crypt_su` = " . $db->toSql(bin2hex($salt)) . "
									, `crypt_eu` = " . $db->toSql(bin2hex($Eu)) . "
								WHERE
									`ID` = " . $db->toSql($ID);
					$db->execute($sSQL);

					if ($ID->getValue() == get_session("UserNID"))
					{
						$cookiehash = mod_sec_mykdf($password, $salt, 1);

						$p1 = substr($cookiehash, 0, 32);
						$p2 = substr($cookiehash, 32);

						$sessionCookie = session_get_cookie_params();
						setcookie("__FF_VU__", $p1, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
						set_session("__FF_VU__", $p2);
					}
				}				
				
				foreach ($cm->modules["security"]["fields"] as $key => $value)
				{
					if (mod_security_is_default_field($key) || (ffIsset($value, "enable_acl") && !mod_sec_check_acl($value["enable_acl"])))
						continue;
					
                                        $sSQL = "SELECT ID
                                                    FROM " . $options["table_dett_name"] . "
                                                    WHERE ID_users = " . $db->toSql($ID) . "
                                                        AND field = " . $db->toSql($key);
                                        $db->query($sSQL);
                                        if($db->nextRecord()) {
                                            $sSQL = "UPDATE
                                                                            " . $options["table_dett_name"] . "
                                                                    SET
                                                                            value = " . $db->toSql($oRecord->form_fields[$key]->value) . "
                                                                    WHERE
                                                                            ID_users = " . $db->toSql($ID) . "
                                                                            AND field = " . $db->toSql($key) . "
                                                            ";
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

	if (is_callable("mod_notifier_add_message_to_queue"))
		mod_notifier_add_message_to_queue("Dati aggiornati con successo", MOD_NOTIFIER_SUCCESS);

	return false;
}
