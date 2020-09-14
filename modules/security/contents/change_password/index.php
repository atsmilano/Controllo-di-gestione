<?php
mod_security_check_session(false);

if(MOD_SEC_CSS_PATH !== false && isset($cm->router->matched_rules["mod_sec_change_password"]))
{
	if(MOD_SEC_CSS_PATH)
		$filename = MOD_SEC_CSS_PATH;
	else
        $filename = cm_cascadeFindTemplate("/css/ff.modules.security.css", "security");
		//$filename = cm_moduleCascadeFindTemplateByPath("security", "/css/ff.modules.security.css", $cm->oPage->theme);

	$ret = cm_moduleGetCascadeAttrs($filename);
	$cm->oPage->tplAddCSS("ff.modules.security.css", array(
		"file" => $filename
		, "path" => $ret["path"]
		, "priority" => cm::LAYOUT_PRIORITY_HIGH
	));
}

$_REQUEST["keys"]["ID"] = get_session("UserNID");

$globals = ffGlobals::getInstance("mod_security");
$globals->options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ModSecChangePassword";
$oRecord->title = "Resetta Password";
$oRecord->src_table = $globals->options["table_name"];
$oRecord->addEvent("on_do_action", "ModSecChangePassword_on_do_action");
$oRecord->addEvent("on_done_action", "ModSecChangePassword_on_done_action");
$oRecord->allow_delete = false;
$oRecord->allow_insert = false;
$oRecord->widget_discl_enable = false;
$oRecord->buttons_options["cancel"]["display"] = false;

if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
{
	$oRecord->insert_additional_fields["ID_domains"]= new ffData(mod_security_get_domain(), "Number");
	$oRecord->additional_key_fields["ID_domains"] = new ffData(mod_security_get_domain(), "Number");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "password";
$oField->label = "Nuova Password";
$oField->required = true;
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
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "confirmnewpassword";
$oField->label = "Conferma Password";
$oField->required = true;
$oField->extended_type = "Password";
$oField->compare = "password";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function ModSecChangePassword_on_do_action($oRecord, $frmAction)
{
	switch($frmAction)
	{
		case "update":
			if (!strlen($oRecord->form_fields["password"]->value->getValue()))
			{
				$oRecord->strError = "Il campo password è obbligatorio";
				return true;
			}
			break;
	}
}

function ModSecChangePassword_on_done_action($oRecord, $frmAction)
{
	switch ($frmAction)
	{
		case "update":
			$cm = cm::getInstance();
			$globals = ffGlobals::getInstance("mod_security");

			$ID = $oRecord->key_fields["ID"]->value;
			$db = ffDB_Sql::factory();

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

				$sSQL = "UPDATE " .  $globals->options["table_name"] . " SET
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

			$db = ffDB_Sql::factory();
			$sSQL = "UPDATE
						" . $globals->options["table_name"] . "
					SET
						" . $globals->options["table_name"] . ".password_used = ''
						, " . $globals->options["table_name"] . ".temp_password = ''
						, " . $globals->options["table_name"] . ".password_generated_at = ''
					WHERE " . $globals->options["table_name"] . ".ID = " . $db->toSql(get_session("UserNID"), "Number") . "
				";
			$db->execute($sSQL);
		break;
	}
}