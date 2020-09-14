<?php
header('Access-Control-Allow-Origin: *'); // allow JS access
 
modsec_OAuth2_UserResourceController("userinfo", function ($UserNID, $scopes, $request, $response, $server) {
	
	$outdata = array();
	
	$db = mod_security_get_main_db();
	$options = mod_security_get_settings($cm->path_info);

	$sso_start = isset($_REQUEST["sso_start"]) ? intval($_REQUEST["sso_start"]) : false;
	$sso_next = isset($_REQUEST["sso_next"]) ? intval($_REQUEST["sso_next"]) : false;

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

			$sSQL = "SELECT 
							`oauth_authorization_codes`.* 
						FROM 
							`oauth_authorization_codes`
						WHERE 
							`oauth_authorization_codes`.`client_id` = " . $db->toSql($client_data["client_id"]) . "
							AND `oauth_authorization_codes`.`user_id` = " . $db->toSql($UserNID, "Number") . "
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
				$outdata["next_sso_url"] = ffCommon_http_build_url($url_parse);
			}
		}
	}
	
	cm::jsonParse($outdata);
	exit;
});
