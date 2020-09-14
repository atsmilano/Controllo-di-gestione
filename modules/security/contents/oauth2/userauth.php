<?php
$server = modsec_getOauth2Server();

$_REQUEST["grant_type"] = $_GET["grant_type"] = $_POST["grant_type"] = "password";

$response = new OAuth2\Response();
$server->handleTokenRequest(OAuth2\Request::createFromGlobals(), $response);
if ($response->getStatusCode() === 200)
{
	$token_data = $server->getStorages()["access_token"]->getAccessToken($response->getParameter("access_token"));
	
	$db = mod_security_get_main_db();
	$now = new ffData(date("Y-m-d H:i:s"), "DateTime", "ISO9075");
	
	$sSQL = "SELECT * FROM `oauth_rel_users` WHERE `client_id` = " . $db->toSql($token_data["client_id"]) . " AND `ID_user` = " . $db->toSql($token_data["user_id"], "Number");
	$db->query($sSQL);
	if ($db->nextRecord())
	{
		if (!$db->record["granted"])
		{
			$sSQL = "UPDATE `oauth_rel_users` SET 
							`granted` = " . $db->toSql(1, "Number") . " 
							, `when` = " . $db->toSql($now) . " 
							, `by` = " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
						WHERE 
							`client_id` = " . $db->toSql($token_data["client_id"]) . " 
							AND `ID_user` = " . $db->toSql($token_data["user_id"], "Number");
			$db->execute($sSQL);
		}
	}
	else
	{
		$sSQL = "INSERT INTO `oauth_rel_users` (`client_id`, `ID_user`, `granted`, `when`, `by`) VALUES (
						" . $db->toSql($token_data["client_id"]) . "
						, " . $db->toSql($token_data["user_id"], "Number")  . "
						, " . $db->toSql(1, "Number") . " 
						, " . $db->toSql($now) . " 
						, " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
					)";
		$db->execute($sSQL);
	}
}

$response->send();
exit;
