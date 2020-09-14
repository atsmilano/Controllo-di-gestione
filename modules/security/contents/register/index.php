<?php
$globals = ffGlobals::getInstance("mod_security");
$db = ffDB_Sql::factory();

if (mod_security_check_session(false) && get_session("UserID") != MOD_SEC_GUEST_USER_NAME)
{
	if (strlen($cm->oPage->ret_url))
		ffRedirect($cm->oPage->ret_url);
	else
	{
		$path_profile = $cm->router->getRuleById("mod_sec_profile");
		if ($path_profile === null)
		{
			ffRedirect($cm->oPage->site_path . "/");
		}
		else
		{
			ffRedirect($cm->oPage->site_path . (string)$path_profile->reverse);
		}
	}
}

$options = mod_security_get_settings($cm->path_info);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->title = "Inserisci i tuoi dati";
$oRecord->src_table = $options["table_name"];
$oRecord->allow_delete = false;
$oRecord->allow_update = false;
$oRecord->addEvent("on_do_action", "MainRecord_on_do_action");
$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");
$oRecord->addEvent("on_done_action", array(
	"func_name" => "MainRecord_on_done_action_redir",
	"priority" => ffEvent::PRIORITY_LOW,
	"index" => -100
));
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("mod_sec_register_bt");
$oRecord->additional_fields = array(
										  "status" => new ffData('1')
										, "level" => new ffData('1')
									);
$oRecord->insert_additional_fields["created"] = new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA");
if ($cm->modules["security"]["overrides"]["register"]["tpl_file"])
	$oRecord->template_file = $cm->modules["security"]["overrides"]["register"]["tpl_file"];

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if (!mod_security_is_defined_field("username")
		&&
		(MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
		&&
		(MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "username")
	)
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "username";
	$oField->label = "Username";
	$oField->required = true;
	$oRecord->addContent($oField);
}
if (!mod_security_is_defined_field("email") && (MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "email"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "email";
	$oField->label = "E-Mail";
	$oField->required = true;
	$oField->addValidator("email");
	$oRecord->addContent($oField);

	if (MOD_SECURITY_REGISTER_CONFIRM_EMAIL)
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "confirmemail";
		$oField->label = "Conferma E-Mail";
		$oField->compare = "email";
		$oField->addValidator("email");
		$oRecord->addContent($oField);
	}

	if (MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "email")
		$oRecord->additional_fields["username"] = null;
}

if (MOD_SECURITY_REGISTER_AUTOGEN_PASSWD)
{
	$globals->rand_password = mod_sec_createRandomPassword();
	$oRecord->insert_additional_fields["password"] = $db->mysqlPassword($globals->rand_password);
}
else if (!mod_security_is_defined_field("password"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "password";
	$oField->label = "Password";
	$oField->extended_type = "Password";
	$oField->crypt_method = "mysql_password";
	$oField->required = true;
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "confpassword";
	$oField->label = "Conferma Password";
	$oField->extended_type = "Password";
	$oField->compare = "password";
	$oRecord->addContent($oField);
}

if (!MOD_SECURITY_REGISTER_MINIMAL)
	mod_security_add_custom_fields($oRecord);

if(MOD_SECURITY_REGISTER_PRIVACY) {
	$oRecord->addContent(null, true, "privacy"); 
	$oRecord->groups["privacy"] = array(
	            "title" => ffTemplate::_get_word_by_code("register_privacy")
	            , "cols" => 1
	     );
	//$oRecord->setTabTitle("privacy", ffTemplate::_get_word_by_code("register_privacy"));
	if(MOD_SECURITY_REGISTER_CONDITION) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "condition_text";
		$oField->container_class = "condition-text";
		$oField->label = "";
		$oField->base_type = "Text";
		$oField->extended_type = "Text";
		$oField->control_type = "textarea";
		$oField->default_value = new ffData(ffTemplate::_get_word_by_code("condition_text"), "Text");
		$oField->properties["readonly"] = "readonly";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oRecord->addContent($oField, "privacy");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "condition_check";
		$oField->container_class = "condition-check";
		$oField->label = ffTemplate::_get_word_by_code("condition_check");
		if (MOD_SECURITY_REGISTER_CONDITION_CHECKBOX)
		{
			$oField->control_type = "checkbox";
			$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
			$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
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
		$oField->required = true;
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oRecord->addContent($oField, "privacy");		
	}
	
	if (MOD_SECURITY_REGISTER_PRIVACY_TEXT)
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "privacy_text";
		$oField->container_class = "privacy-text";
		$oField->label = "";
		$oField->base_type = "Text";
		$oField->extended_type = "Text";
		$oField->control_type = "textarea";
		$oField->default_value = new ffData(ffTemplate::_get_word_by_code("register_privacy_text"), "Text");
		$oField->properties["readonly"] = "readonly";
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oRecord->addContent($oField, "privacy");
	}
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "privacy_check";
	$oField->container_class = "register_check";
	$oField->label = ffTemplate::_get_word_by_code("register_privacy_check");
	if (MOD_SECURITY_REGISTER_PRIVACY_CHECKBOX)
	{
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
		$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
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
	$oField->required = true;
	$oField->data_type = "";
	$oField->store_in_db = false;
	$oRecord->addContent($oField, "privacy");	
}	
	
if (ffIsset($cm->modules["security"], "custom_events") && ffIsset($cm->modules["security"]["custom_events"], "register") && count($cm->modules["security"]["custom_events"]["register"]))
	foreach($cm->modules["security"]["custom_events"]["register"] as $key => $value)
	{
		$oRecord->addEvent($key, $value);
	}
	
$cm->oPage->addContent($oRecord);

function MainRecord_on_done_action_redir($oRecord, $frmAction)
{
	$cm = cm::getInstance();
	//$globals = ffGlobals::getInstance("mod_security");

	if (strlen($cm->oPage->ret_url))
		$ret_url = $cm->oPage->ret_url;
	else
		$ret_url = $cm->oPage->site_path . "/";

	$mod_sec_register_success = $cm->router->getRuleById("mod_sec_register_success");
	if ($mod_sec_register_success === null)
	{
		$mod_sec_register = $cm->router->getRuleById("mod_sec_register");
		if ($mod_sec_register === null)
		{
			$destination = $ret_url;
			$oRecord->redirect($cm->oPage->site_path . "/" . ltrim($destination, "/"));
		}
		else
			$destination = $cm->oPage->site_path . (string)$mod_sec_register->reverse . "/success";
	}
	else
		$destination = $cm->oPage->site_path . (string)$mod_sec_register_success->reverse;

	if ($destination)
		$oRecord->redirect($destination . "?ret_url=" . rawurlencode($ret_url));
	else
		$oRecord->redirect($ret_url);
}

function MainRecord_on_done_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("mod_security");

	$options = mod_security_get_settings($cm->path_info);
	$ID = $oRecord->key_fields["ID"]->value;
	$db = ffDB_Sql::factory();

	switch($frmAction)
	{
		case "insert":
			if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
			{
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
			}

			if (MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "email")
			{
                $enable_sendmail = true;
                if(function_exists("check_function")) {
                    if(!check_function("class.phpmailer"))
                        $enable_sendmail = false;
                }
                if($enable_sendmail) {
                    $mail = new PHPMailer();
				    $mail->SetLanguage("it", FF_DISK_PATH . "/library/phpmailer/language/");
				    $mail->CharSet = "utf-8";

				    if (SECURITY_REGISTER_EMAIL_ENABLE_SMTP)
				    {
					    $mail->IsSMTP();
					    $mail->SMTPSecure	= SECURITY_REGISTER_EMAIL_SMTP_SECURITY;
					    $mail->SMTPAuth     = true;
					    $mail->Host         = SECURITY_REGISTER_EMAIL_HOST;
					    $mail->Username     = SECURITY_REGISTER_EMAIL_USER;
					    $mail->Password     = SECURITY_REGISTER_EMAIL_PASS;
				    }
				    else
					    $mail->IsMail();

				    $mail->From     = SECURITY_REGISTER_EMAIL_FROM;
				    $mail->FromName = SECURITY_REGISTER_EMAIL_FROM_NAME;

				    $mail->Subject = SECURITY_REGISTER_EMAIL_SUBJECT;
					
					if (SECURITY_REGISTER_MAIL_TPL)
					{
						$tpl_txt = ffTemplate::factory(ffCommon_dirname(SECURITY_REGISTER_MAIL_TPL));
						$tpl_txt->load_file(basename(SECURITY_REGISTER_MAIL_TPL) . ".txt", "main");
						
						$tpl_html = ffTemplate::factory(ffCommon_dirname(SECURITY_REGISTER_MAIL_TPL));
						$tpl_html->load_file(basename(SECURITY_REGISTER_MAIL_TPL) . ".html", "main");
					}
					else
					{
                        $filename = cm_cascadeFindTemplate("/email/mail_register.txt", "security");
						/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/email/mail_register.txt", $cm->oPage->theme, false);
						if ($filename === null)
							$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/email/mail_register.txt", $cm->oPage->theme);
*/
						$tpl_txt = ffTemplate::factory(ffCommon_dirname($filename));
						$tpl_txt->load_file("mail_register.txt", "main");

						$filename = cm_cascadeFindTemplate("/email/mail_register.html", "security");
						/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/email/mail_register.html", $cm->oPage->theme, false);
						if ($filename === null)
							$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/email/mail_register.html", $cm->oPage->theme);*/
						$tpl_html = ffTemplate::factory(ffCommon_dirname($filename));
						$tpl_html->load_file("mail_register.html", "main");
					}

				    $hostname = $_SERVER["HTTP_HOST"];
				    if (strpos($_SERVER["HTTP_HOST"], "http://") === false)
					    $hostname = "http://" . $hostname;

				    $tpl_txt->set_var("hostname"	, $hostname);
				    $tpl_html->set_var("hostname"	, $hostname);

				    foreach ($oRecord->form_fields as $key => $value)
				    {
					    $tpl_html->set_var($key, $value->getDisplayValue());
					    $tpl_txt->set_var($key, $value->getDisplayValue());
				    }
				    reset($oRecord->form_fields);

				    if (MOD_SECURITY_REGISTER_AUTOGEN_PASSWD)
				    {
					    $tpl_html->set_var("password", $globals->rand_password);
					    $tpl_txt->set_var("password", $globals->rand_password);
				    }

				    if (MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "email")
				    {
					    $tpl_html->set_var("username", $oRecord->form_fields["email"]->value->getValue());
					    $tpl_txt->set_var("username", $oRecord->form_fields["email"]->value->getValue());
				    }
				    
				    $res = $cm->modules["security"]["events"]->doEvent("on_mailreg_tpl_processed", array(&$cm->modules["security"], &$tpl_txt, &$tpl_html));

				    $mail->Body = $tpl_html->rpparse("main", false);
				    $mail->IsHTML(true);

				    $mail->AltBody = $tpl_txt->rpparse("main", false);

				    $desc = "";
				    if (isset($oRecord->form_fields["firstname"]))
					    $desc .= $oRecord->form_fields["firstname"]->value->getValue();
				    if (isset($oRecord->form_fields["lastname"]))
				    {
					    if (strlen($desc))
						    $desc .= " ";
					    $desc .= $oRecord->form_fields["lastname"]->value->getValue();
				    }
				    $mail->AddAddress($oRecord->form_fields["email"]->value->getValue(), $desc);

				    if (defined(SECURITY_REGISTER_BCC))
					    $mail->AddBCC(SECURITY_REGISTER_BCC);

					$mail->Send();
                }
			}

			if (MOD_SECURITY_REGISTER_CREATE_SESSION)
			{
				if (
						(MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
						&&
						(MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "username")
					)
					mod_security_create_session($oRecord->form_fields["username"]->value->getValue(), $oRecord->key_fields["ID"]->value->getValue());
				else
					mod_security_create_session($oRecord->form_fields["email"]->value->getValue(), $oRecord->key_fields["ID"]->value->getValue());
				set_session("UserLevel", 1);
			}
			
			$cm->modules["security"]["events"]->doEvent("done_user_create", array($ID, $oRecord->additional_fields["status"], false));
	}
}

function MainRecord_on_do_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();

	switch($frmAction)
	{
		case "insert":
			
			$db = ffDB_Sql::factory();
			$sSQL = "SELECT
							*
						FROM
							`" . $oRecord->src_table . "`
						WHERE
							1
				";
			if (MOD_SEC_EXCLUDE_SQL)
				$sSQL .= " AND `" . $oRecord->src_table . "`.ID " . MOD_SEC_EXCLUDE_SQL;

			if (
					(MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
					&&
					(MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "username")
				)
			{
				$tmp_SQL = $sSQL . " AND `username` = " . $db->toSql($oRecord->form_fields["username"]->value);
				$db->query($tmp_SQL);
				if ($db->nextRecord())
				{
					$oRecord->strError = "L'username desiderato è già in utilizzo";
					return true;
				}
			}

			if (
					(MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "both" || MOD_SECURITY_REGISTER_SHOWUSERID == "email")
					&& strlen($oRecord->form_fields["email"]->value->getValue())
				)
			{
				$tmp_SQL = $sSQL . " AND `email` = " . $db->toSql($oRecord->form_fields["email"]->value);
				$db->query($tmp_SQL);
				if ($db->nextRecord())
				{
					$oRecord->strError = "L'E-Mail inserita è già in utilizzo";
					return true;
				}
			}

			if (MOD_SECURITY_LOGON_USERID == "email" || MOD_SECURITY_REGISTER_SHOWUSERID == "email")
				$oRecord->additional_fields["username"] = $oRecord->form_fields["email"]->value;

			break;
	}
}
