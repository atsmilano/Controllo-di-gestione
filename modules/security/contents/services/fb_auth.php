<?php

$frmAction 		= strtolower($_REQUEST["frmAction"]);
$username 		= "";
$password 		= "";
$access_token 	= $_REQUEST["token"];
//$ret_url = $_REQUEST["ret_url"];

if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
{
	$domain = $_REQUEST["domain"];
}

switch($frmAction) {
	case "login":
		$arrUserParams = array();
		$arrUserField = array();
		$arrUserToken = array();
		$is_valid = false;

		if(strlen($access_token)) {
			$FB_response = @file_get_contents("https://graph.facebook.com/me?access_token=" . $access_token);
			if($FB_response !== false) {
				$FB_user_info = json_decode($FB_response, true);
				
				$arrDefaultFields = explode(",", MOD_SEC_DEFAULT_FIELDS);
				
				$arrUserParams["username"] = $FB_user_info["name"];
				if(MOD_SEC_STRICT_FIELDS) {
					if(strlen(MOD_SEC_USER_FIRSTNAME)) {
						if(array_search(MOD_SEC_USER_FIRSTNAME, $arrDefaultFields) === false)
							$arrUserField[MOD_SEC_USER_FIRSTNAME] = $FB_user_info["first_name"];
						else
							$arrUserParams[MOD_SEC_USER_FIRSTNAME] = $FB_user_info["first_name"];
					}
					if(strlen(MOD_SEC_USER_LASTNAME)) {
						if(array_search(MOD_SEC_USER_LASTNAME, $arrDefaultFields) === false)
							$arrUserField[MOD_SEC_USER_LASTNAME] = $FB_user_info["last_name"];
						else
							$arrUserParams[MOD_SEC_USER_LASTNAME] = $FB_user_info["last_name"];
					}
					if(strlen(MOD_SEC_USER_AVATAR)) {
						if(array_search(MOD_SEC_USER_AVATAR, $arrDefaultFields) === false)
							$arrUserField[MOD_SEC_USER_AVATAR] = "https://graph.facebook.com/" . $FB_user_info["id"] . "/picture";
						else
							$arrUserParams[MOD_SEC_USER_AVATAR] = "https://graph.facebook.com/" . $FB_user_info["id"] . "/picture";
					}
				} else {
					if(array_search("name", $arrDefaultFields) === false)
						$arrUserField["name"] = $FB_user_info["first_name"];
					else
						$arrUserParams["name"] = $FB_user_info["first_name"];

					if(array_search("surname", $arrDefaultFields) === false)
						$arrUserField["surname"] = $FB_user_info["last_name"];
					else
						$arrUserParams["surname"] = $FB_user_info["last_name"];

					if(array_search("avatar", $arrDefaultFields) === false)
						$arrUserField["avatar"] = "https://graph.facebook.com/" . $FB_user_info["id"] . "/picture";
					else
						$arrUserParams["avatar"] = "https://graph.facebook.com/" . $FB_user_info["id"] . "/picture";
				}
				$arrUserParams["email"] = $FB_user_info["email"];
				$arrUserParams["status"] = $FB_user_info["verified"];

				//$username = $FB_user_info["name"];

				//$name = $FB_user_info["first_name"];
				//$surname = $FB_user_info["last_name"];
				//$avatar = "https://graph.facebook.com/" . $FB_user_info["id"] . "/picture";

				//$email = $FB_user_info["email"];
				//$status  = $FB_user_info["verified"];
				
				$arrUserField["facebook"] = "https://www.facebook.com/" . $FB_user_info["id"];
/*
				if(MOD_SEC_STRICT_FIELDS) {
					if(strlen($arrUserParams["name"])) {
						$arrUserField["name"] = $arrUserParams["name"];
					}
					if(strlen($arrUserParams["surname"])) {
						$arrUserField["surname"] = $arrUserParams["surname"];
					}					
					if(strlen($arrUserParams["avatar"])) {
						$arrUserField["avatar"] = $arrUserParams["avatar"];
					}
				}
*/


				if(MOD_SEC_ENABLE_TOKEN && strlen(MOD_SEC_FB_APPSECRET)) {
					$extendedAccessToken = file_get_contents('https://graph.facebook.com/oauth/access_token' 
						. '?client_id=' . MOD_SEC_FB_APPID 
						. '&client_secret=' . MOD_SEC_FB_APPSECRET
						. '&grant_type=fb_exchange_token'
						. '&fb_exchange_token=' . $access_token
					);
					if($extendedAccessToken) {
						parse_str($extendedAccessToken, $FB_user_auth);
						if(is_array($FB_user_auth) && array_key_exists("access_token", $FB_user_auth)) {
							$arrUserToken = array("type" => "Facebook"
												, "token" => $FB_user_auth["access_token"]
											);
						}
					}				
				}				

				$is_valid = true;
			}
		}

		if($is_valid) {
			$ID_domain = null;

			if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
			{
				if (strlen($domain))
				{
					$db = mod_security_get_main_db();

					$db->query("SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db->toSql($domain));
					if ($db->nextRecord())
					{
						$ID_domain = $db->getField("ID", "Number")->getValue();
					}
					else
					{
						$sError = ffTemplate::_get_word_by_code("fb_login_domain_not_found");
					}
				}
				else
				{
					$ID_domain = 0;
				}
			}

			if (!strlen($sError))
			{
				$res = mod_security_set_user_by_social("fb", $arrUserParams, $arrUserField, $arrUserToken, $ID_domain);
				$sError = $res["error"];
				if(!strlen($sError)) {
					if($cm->isXHR())
					{
						die();
					} 
					else 
					{
						if (isset($cm->processed_rule["rule"]->options->noredirect))
							ffRedirect($_SERVER["REQUEST_URI"]);
						else
							ffRedirect($_REQUEST["ret_url"]);
					}
				}				
	/*			
				// check username & password
				$options = mod_security_get_settings("/");
				
				if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
					$db = mod_security_get_db_by_domain($ID_domain);
				else
					$db = mod_security_get_main_db();

				$sSQL = "SELECT
											" . $options["table_name"] . ".*
							FROM
											" . $options["table_name"] . "
							WHERE
								1
								AND (";
					 $sSQL .= $options["table_name"] . ".email = " . $db->toSql($arrUserParams["email"], "Text");
				$sSQL .= ")";

				if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && MOD_SEC_LOGIN_DOMAIN)
					$sSQL .= " AND " . $options["table_name"] . ".ID_domains = " . $db->toSql($ID_domain);
				
				if (MOD_SEC_EXCLUDE_SQL)
					$sSQL .= " AND `" . $options["table_name"] . "`.ID " . MOD_SEC_EXCLUDE_SQL;
				
				$db->query($sSQL);
				if (!$db->nextRecord()) {
					$UserParams["username_slug"] = ffCommon_url_rewrite($arrUserParams["username"]);
					$res = $cm->modules["security"]["events"]->doEvent("on_social_do_user_create", array(&$arrUserParams["username"], &$UserParams["username_slug"], &$arrUserParams["avatar"], &$arrUserParams["email"], &$ID_domain));
					$last_res = end($res);
					if (!$last_res)
					{
						$sSQL_manage = "INSERT INTO " . $options["table_name"] . "
										(
											ID
											, username
											, username_slug
											" . (MOD_SEC_STRICT_FIELDS
												? ""
												: "	, name
													, surname
													, avatar"
											) . "
											, email
											, status
											, ID_domains
										)
										VALUES
										(
											null
											, " . $db->toSql($arrUserParams["username"], "Text") . "
											, " . $db->toSql($UserParams["username_slug"], "Text") . "
											" . (MOD_SEC_STRICT_FIELDS
												? ""
												: "	, " . $db->toSql($arrUserParams["name"], "Text") . "
													, " . $db->toSql($arrUserParams["surname"], "Text") . "
													, " . $db->toSql($arrUserParams["avatar"], "Text")
											) . "
											, " . $db->toSql($arrUserParams["email"], "Text") . "
											, " . $db->toSql($arrUserParams["status"], "Text") . "
											, " . $db->toSql($ID_domain, "Number") . "
										)";
						$db->execute($sSQL_manage);
						$UserParams["ID"] = $db->getInsertID(true);
						
						if(is_array($arrUserField) && count($arrUserField)) {
							foreach($arrUserField AS $arrUserField_key => $arrUserField_value) {
								$sSQL_manage = "INSERT INTO " . $options["table_dett_name"] . "
												(
													`ID`
													, `ID_users`
													, `field`
													, `value`
												)
												VALUES
												(
													null
													, " . $db->toSql($UserParams["ID"], "Number") . "
													, " . $db->toSql($arrUserField_key, "Text") . "
													, " . $db->toSql($arrUserField_value, "Text") . "
												)";
								$db->execute($sSQL_manage);
							}
						}
						
						$cm->modules["security"]["events"]->doEvent("on_social_done_user_create", array($UserParams["ID"], !$arrUserParams["status"]));
					}
				} else {
					$UserParams["ID"] = $db->getField("ID", "Number", true);
					$UserParams["username_slug"] = ffCommon_url_rewrite($db->getField("username", "Text", true));
					
					$res = $cm->modules["security"]["events"]->doEvent("on_social_do_user_update", array(&$UserParams["ID"], &$UserParams["username_slug"], &$arrUserParams["avatar"]));
					$last_res = end($res);
					if (!$last_res)
					{
						$UserParams["ID"] = $db->getField("ID", "Number", true);
						$sSQL_manage = "UPDATE " . $options["table_name"] . " 
										SET
											username_slug = " . $db->toSql(ffCommon_url_rewrite($db->getField("username", "Text", true)), "Text") . "
											" . (MOD_SEC_STRICT_FIELDS
												? ""
												: "	, name = " . $db->toSql($arrUserParams["name"], "Text") . "
													, surname = " . $db->toSql($arrUserParams["surname"], "Text") . "
													, avatar = " . $db->toSql($arrUserParams["avatar"], "Text")
											) . "
										WHERE ID = " . $db->toSql($UserParams["ID"], "Number");
								$db->execute($sSQL_manage);
						
						if(is_array($arrUserField) && count($arrUserField)) {
							foreach($arrUserField AS $arrUserField_key => $arrUserField_value) {
								$sSQL_manage = "SELECT " . $options["table_dett_name"] . ".*
												FROM " . $options["table_dett_name"] . "
												WHERE " . $options["table_dett_name"] . ".`ID_users` = " . $db->toSql($UserParams["ID"], "Number") . "
													AND " . $options["table_dett_name"] . ".`field` = " . $db->toSql($arrUserField_key);
								$db->query($sSQL_manage);
								if(!$db->nextRecord()) {
									$sSQL_manage = "INSERT INTO " . $options["table_dett_name"] . "
													(
														`ID`
														, `ID_users`
														, `field`
														, `value`
													)
													VALUES
													(
														null
														, " . $db->toSql($UserParams["ID"], "Number") . "
														, " . $db->toSql($arrUserField_key, "Text") . "
														, " . $db->toSql($arrUserField_value, "Text") . "
													)";
									$db->execute($sSQL_manage);
								}
							}
						}	
											
						$cm->modules["security"]["events"]->doEvent("on_social_done_user_update", array($UserParams["ID"]));
					}
				}
				$db->query($sSQL);
				if ($db->nextRecord())
				{
                    if($db->getField("status", "Number", true) > 0) 
                    {
						if (MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username")
							$userID = $db->getField("username", "Text")->getValue();
						else
							$userID = $db->getField("email", "Text")->getValue();

						$userNID = $db->getField("ID", "Number")->getValue();

						if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_LOGIN_DOMAIN)
						{
							$ID_domain = $db->getField("ID_domains")->getValue();
						}

						mod_security_create_session($userID, $userNID, $domain, $ID_domain);

						$res = $cm->modules["security"]["events"]->doEvent("janrain_logging_in", array($ret_url));
						$last_res = end($res);
						if (!$last_res)
						{
							$cm->modules["security"]["events"]->doEvent("logging_in", array($ret_url));

							if($cm->isXHR())
							{
								die();
							} 
							else 
							{
								if (isset($cm->processed_rule["rule"]->options->noredirect))
									ffRedirect($_SERVER["REQUEST_URI"]);
								else
									ffRedirect($_REQUEST["ret_url"]);
							}
						}
				    }
				    else
                        $sError = ffTemplate::_get_word_by_code("fb_login_user_not_active");
                }
                else
                    $sError = ffTemplate::_get_word_by_code("fb_login_wrong_user");*/
			}
		} 
		else
			$sError = ffTemplate::_get_word_by_code("fb_login_invalid");

		if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
			die($sError);

		if(strlen($_REQUEST["ret_url"]))
			ffRedirect($_REQUEST["ret_url"]);

		break;
	case "logout":
		// DISTRUGGE LA SESSIONE
		mod_security_destroy_session(false);
		if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
			die();
		else
			ffRedirect($_REQUEST["ret_url"]);
	
	case "cancellogout":
		ffRedirect($_REQUEST["ret_url"]);
}
