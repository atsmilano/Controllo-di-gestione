<?php
mod_security_check_session();

if (!strlen($_REQUEST["client_id"]))
	$cm->responseCode(500);

$db = mod_security_get_main_db();

$sSQL = "DELETE FROM `oauth_rel_users` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]) . " AND `ID_user` = " . $db->toSql(get_session("UserNID"), "Number");
$db->execute($sSQL);

$sSQL = "DELETE FROM `oauth_access_tokens` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]) . " AND `user_id` = " . $db->toSql(get_session("UserNID"), "Number");
$db->execute($sSQL);

$sSQL = "DELETE FROM `oauth_refresh_tokens` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]) . " AND `user_id` = " . $db->toSql(get_session("UserNID"), "Number");
$db->execute($sSQL);

$sSQL = "DELETE FROM `oauth_authorization_codes` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]) . " AND `user_id` = " . $db->toSql(get_session("UserNID"), "Number");
$db->execute($sSQL);

cm::jsonParse(array(
	"success" => true,
	"refresh" => true,
	"resources" => array(
		"oauth_rel_users"
	)
));
exit;