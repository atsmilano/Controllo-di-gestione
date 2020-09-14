<?php
header('Access-Control-Allow-Origin: *'); // allow JS access
 
modsec_OAuth2_UserResourceController("userinfo", function ($UserNID, $scopes, $request, $response, $server) {
	$cm = cm::getInstance();
	$outdata = array();
	
	$db = mod_security_get_main_db();
	$options = mod_security_get_settings($cm->path_info);

	$strAddFields = "";
	if(strlen(MOD_SEC_USER_FIRSTNAME)) {
		$strAddFields .= ", (SELECT 
										" . $options["table_dett_name"] . ".value
									FROM
										" . $options["table_dett_name"] . "
									WHERE
										" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
										AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_FIRSTNAME) . "
								) AS firstname
					";
	}
	if(strlen(MOD_SEC_USER_LASTNAME)) {
		$strAddFields .= ", (SELECT 
										" . $options["table_dett_name"] . ".value
									FROM
										" . $options["table_dett_name"] . "
									WHERE
										" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
										AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_LASTNAME) . "
								) AS lastname
					";
	}

	$stop_exec = false;
	$res = cm::getInstance()->modules["security"]["events"]->doEvent("OAuth2_resource_userinfo", array($UserNID, $scopes, $response, &$strAddFields));
	foreach ($res as $key => $value)
	{
		if (is_array($value))
		{
			if ($value["mode"] === "merge")
				$outdata = array_merge_recursive($outdata, $value["data"]);
			else
				$outdata = $value["data"];
			
			if ($value["skip_default"])
				$stop_exec = true;
		}
	}
	
	if (!$stop_exec)
	{
		$sSQL = "SELECT 
					`" . $options["table_name"] . "`.`username`
					, `" . $options["table_name"] . "`.`email`
					, `" . $options["table_name"] . "`.`time_zone`
					$strAddFields
				FROM 
					`" . $options["table_name"] . "`
				WHERE
					`ID` = " . $db->toSql($UserNID);
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$outdata = array_merge_recursive($db->record, $outdata);
			//unset($outdata["email"]);
		}
		else
		{
			$response->setError(500, "The user is not existing anymore");
			modsec_OAuth2Error($response);
			return;
		}
	}
	
	cm::jsonParse(array(
		"userinfo" => $outdata
	));
	exit;
});
