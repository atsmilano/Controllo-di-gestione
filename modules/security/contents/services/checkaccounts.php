<?php
$db = ffDB_Sql::factory();
$db2 = ffDB_Sql::factory();
$sSQL = "SELECT 
					* 
				FROM 
					cm_mod_security_users 
				WHERE 
					status = 1 
					AND expiration <> '0000-00-00 00:00:00' AND expiration <= " . $db->toSql(new ffData(date("d/m/Y H:i:s"), "DateTime", FF_LOCALE)) ;
if (MOD_SEC_EXCLUDE_SQL)
	$sSQL .= " AND `cm_mod_security_users`.ID " . MOD_SEC_EXCLUDE_SQL;

$sSQL .= " ORDER BY ID DESC";

$db->query($sSQL);
if ($db->nextRecord())
{
	do
	{
		$sSQL2 = "UPDATE cm_mod_security_users SET status = 0 WHERE ID = " . $db2->toSql($db->getField("ID"));
		$db2->execute($sSQL2);

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

		    $mail->Subject = SECURITY_REGISTER_EMAIL_OUTDATED;

            $filename = cm_cascadeFindTemplate("/email/mail_outdated.txt", "security");
		    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/email/mail_outdated.txt", $cm->oPage->theme, false);
		    if ($filename === null)
			    $filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/email/mail_outdated.txt", $cm->oPage->theme);*/
		    $tpl_txt = ffTemplate::factory(ffCommon_dirname($filename));
		    $tpl_txt->load_file("mail_outdated.txt", "main");

            $filename = cm_cascadeFindTemplate("/email/mail_outdated.html", "security");
		    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/email/mail_outdated.html", $cm->oPage->theme, false);
		    if ($filename === null)
			    $filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/email/mail_outdated.html", $cm->oPage->theme);*/
		    $tpl_html = ffTemplate::factory(ffCommon_dirname($filename));
		    $tpl_html->load_file("mail_outdated.html", "main");

		    $tpl_html->set_var("username", $db->getField("username")->getValue());
		    $tpl_txt->set_var("username", $db->getField("username")->getValue());

		    $mail->Body = $tpl_html->rpparse("main", false);
		    $mail->IsHTML(true);

		    $mail->AltBody = $tpl_txt->rpparse("main", false);

		    $mail->AddAddress($db->getField("email")->getValue());
    //		$mail->AddAddress("samuele.diella@gmail.com");

		    if (SECURITY_REGISTER_BCC)
			    $mail->AddBCC(SECURITY_REGISTER_BCC);

		    $mail->Send();
        }
	} while (false && $db->nextRecord());
}

exit;