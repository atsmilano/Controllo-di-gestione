<?php
$db = ffDB_Sql::factory();
$options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "inituser";
$oRecord->title = "CRYPT initialization";
$oRecord->buttons_options["insert"]["label"] = "Init User";
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->skip_action = true;
$oRecord->addEvent("on_do_action", "crypt_init_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "user";
$oField->label = "Utente da inizializzare";
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
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "password";
$oField->label = "Password";
$oField->extended_type = "Password";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "datakey";
$oField->label = "Chiave Dati";
$oField->description = "Lasciare vuoto per auto-inizializzazione";
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "datasalt";
$oField->label = "Seed Dati";
$oField->description = "Lasciare vuoto per auto-inizializzazione";
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function crypt_init_on_do_action($obj, $action)
{
    $cm = cm::getInstance();

	if ($action == "insert")
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		
		$addmessage = "";
		$datakey = $obj->form_fields["datakey"]->value->getValue();
		if (!strlen($datakey))
		{
			$Ku = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$addmessage .= "<br />Generated key (HEX): " . bin2hex($Ku); 
		}
		else
		{
			$Ku = $datakey;
		}
		
		$datasalt = $obj->form_fields["datasalt"]->value->getValue();
		if (!strlen($datasalt))
		{
			$KSu = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$addmessage .= "<br />Generated salt (HEX): " . bin2hex($KSu); 
		}
		else
		{
			$KSu = $datasalt;
		}
		
		$salt = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		$password = $obj->form_fields["password"]->value->getValue();
		
		$hash = mod_sec_mykdf($password, $salt, 1000);
		
		$Vu1 = substr($hash, 0, 32);
		$Vu2 = substr($hash, 32);
		
		$Eu = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $Vu2, bin2hex($Ku) . "|" . bin2hex($KSu), MCRYPT_MODE_CBC, $salt);
		
		$db = ffDB_Sql::factory();
		$options = mod_security_get_settings($cm->path_info);
		
		$sSQL = "UPDATE " .  $options["table_name"] . " SET
						`crypt_vu` = " . $db->toSql($Vu1) . "
						, `crypt_su` = " . $db->toSql(bin2hex($salt)) . "
						, `crypt_eu` = " . $db->toSql(bin2hex($Eu)) . "
					WHERE
						`ID` = " . $db->toSql($obj->form_fields["user"]->value);
		$db->execute($sSQL);
		
		$obj->form_fields = array();

		$obj->addContent("<h1>User Updated$addmessage</h1>");
		return true;
	}
}