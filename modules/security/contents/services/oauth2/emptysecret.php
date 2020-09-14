<?php
mod_security_check_session();

if (strlen($_REQUEST["client_id"]))
{
	$db = mod_security_get_main_db();

	$sSQL = "UPDATE `oauth_clients` SET `client_secret` = '' WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]);
	$db->execute($sSQL);
}

cm::jsonParse(array(
	"success" => true
));
exit;