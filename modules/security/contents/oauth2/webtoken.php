<?php
$server = modsec_getOauth2Server();

$_REQUEST["grant_type"] = $_GET["grant_type"] = $_POST["grant_type"] = "authorization_code";

$sso_start = isset($_REQUEST["sso_start"]) ? intval($_REQUEST["sso_start"]) : false;
$sso_next = isset($_REQUEST["sso_next"]) ? intval($_REQUEST["sso_next"]) : false;

$response = new OAuth2\Response();
$server->handleTokenRequest(OAuth2\Request::createFromGlobals(), $response);

$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
if ($response->getStatusCode() !== 200)
{
	/*if (strlen($_REQUEST["client_id"]))
	{
		$db->query("SELECT * FROM `oauth_clients` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]));
		if ($db->nextRecord())
		{
			$client_data = $db->record;
			if ($client_data["json_only"])
			{
				var_dump($response);
				exit;
			}
		}
	}*/
	
	$response->send();
	exit;
}

if ($sso_start !== false && $sso_next !== false)
{
	$sso_net = array();
	$db = mod_security_get_main_db();
	
	$sSQL = "SELECT `oauth_clients`.* FROM `oauth_clients` WHERE `oauth_clients`.`sso` = 1 ORDER BY `oauth_clients`.`client_id`";
	$db->query($sSQL);
	if ($db->nextRecord())
	{
		do
		{
			$sso_net[] = $db->record;
		} while ($db->nextRecord());
	}
	
	if (isset($sso_net[$sso_next]))
	{
		$client_data = $sso_net[$sso_next];
		$token_data = $server->getStorages()["access_token"]->getAccessToken($response->getParameter("access_token"));
		
		$sSQL = "SELECT 
						`oauth_authorization_codes`.* 
					FROM 
						`oauth_authorization_codes`
					WHERE 
						`oauth_authorization_codes`.`client_id` = " . $db->toSql($client_data["client_id"]) . "
						AND `oauth_authorization_codes`.`user_id` = " . $db->toSql($token_data["user_id"], "Number") . "
						AND `oauth_authorization_codes`.`expires` > NOW()
					ORDER BY
						`expires` DESC
					LIMIT 0,1
				";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$oauth_code_data = $db->record;
			$state = md5(uniqid(APPID, true));
			//$sSQL = "UPDATE `oauth_authorization_codes` set `sso_state` = " . $db->toSql($state) . " WHERE `authorization_code` = " . $db->toSql($oauth_code_data["authorization_code"]);
			//$db->execute($sSQL);

			$url_parse = parse_url($client_data["redirect_uri"]);
			parse_str($url_parse["query"], $query_data);
			$query_data["code"] = $oauth_code_data["authorization_code"];
			$query_data["sso_state"] = $oauth_code_data["sso_state"];
			
			if ($sso_next !== $sso_start)
			{
				$sso_next++;
				if ($sso_next === $sso_start)
					$sso_next++;
				if ($sso_next === count($sso_net))
					$sso_next = $sso_start;

				$query_data["sso_start"] = $sso_start;
				$query_data["sso_next"] = $sso_next;
			}
			
			if (isset($_REQUEST["ret_url"]))
				$query_data["ret_url"] = $_REQUEST["ret_url"];

			$url_parse["query"] = ffCommon_http_build_query($query_data);			
			$response->setParameter("next_sso_url", ffCommon_http_build_url($url_parse));
		}
	}
}

$response->send();
exit;
