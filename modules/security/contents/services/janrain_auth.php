<?php
$frmAction 		= strtolower($_REQUEST["frmAction"]);
$access_token 	= $_REQUEST["token"];
$sError			= null;

$ret_url		= $_REQUEST["ret_url"];
/*if (!strlen($ret_url))
	$ret_url = $cm->oPage->site_path . "/";*/
$err_url		= $_REQUEST["err_url"];
if (!strlen($err_url))
	$err_url = $ret_url;
	
if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
{
	$domain = $_REQUEST["domain"];
}

switch($frmAction) 
{
	case "login":
		$arrUserParams = array();
		$arrUserField = array();
		$arrUserToken = array();
		$is_valid = false;
		
		if(strlen($access_token))
		{
			$janrain_response = @file_get_contents("https://rpxnow.com/api/v2/auth_info?token=" . $access_token . "&apiKey=" . MOD_SEC_SOCIAL_JANRAIN_APPID . "&format=json&extended=true");
			if($janrain_response !== false)
			{
				$janrain_response = json_decode($janrain_response, true);
				
				if ($janrain_response["stat"] == "fail")
				{
					$sError = $janrain_response["err"]["msg"];
				}
				else
				{
					$arrDefaultFields = explode(",", MOD_SEC_DEFAULT_FIELDS);
					
					$arrUserParams["username"] = $janrain_response["profile"]["displayName"];
					if(MOD_SEC_STRICT_FIELDS) {
						if(strlen(MOD_SEC_USER_FIRSTNAME)) {
							if(array_search(MOD_SEC_USER_FIRSTNAME, $arrDefaultFields) === false)
								$arrUserField[MOD_SEC_USER_FIRSTNAME] = $janrain_response["profile"]["name"]["givenName"];	
							else
								$arrUserParams[MOD_SEC_USER_FIRSTNAME] = $janrain_response["profile"]["name"]["givenName"];	
						}
						if(strlen(MOD_SEC_USER_LASTNAME)) {
							if(array_search(MOD_SEC_USER_LASTNAME, $arrDefaultFields) === false)
								$arrUserField[MOD_SEC_USER_LASTNAME] = $janrain_response["profile"]["name"]["familyName"];
							else
								$arrUserParams[MOD_SEC_USER_LASTNAME] = $janrain_response["profile"]["name"]["familyName"];
						}
						if(strlen(MOD_SEC_USER_AVATAR)) {
							if(strpos($janrain_response["profile"]["photo"], "?") === false)
								$tmp_avatar = $janrain_response["profile"]["photo"];
							else
								$tmp_avatar = substr($janrain_response["profile"]["photo"], 0, strpos($janrain_response["profile"]["photo"], "?"));

							if(array_search(MOD_SEC_USER_AVATAR, $arrDefaultFields) === false)
								$arrUserField[MOD_SEC_USER_AVATAR] = $tmp_avatar;
							else
								$arrUserParams[MOD_SEC_USER_AVATAR] = $tmp_avatar;
						}
					} else {
						if(array_search("name", $arrDefaultFields) === false)
							$arrUserField["name"] = $janrain_response["profile"]["name"]["givenName"];	
						else
							$arrUserParams["name"] = $janrain_response["profile"]["name"]["givenName"];	

						if(array_search("surname", $arrDefaultFields) === false)
							$arrUserField["surname"] = $janrain_response["profile"]["name"]["familyName"];
						else
							$arrUserParams["surname"] = $janrain_response["profile"]["name"]["familyName"];

						if(strpos($janrain_response["profile"]["photo"], "?") === false)
							$tmp_avatar = $janrain_response["profile"]["photo"];
						else
							$tmp_avatar = substr($janrain_response["profile"]["photo"], 0, strpos($janrain_response["profile"]["photo"], "?"));

						if(array_search("avatar", $arrDefaultFields) === false)
							$arrUserField["avatar"] = $tmp_avatar;
						else
							$arrUserParams["avatar"] = $tmp_avatar;
					}
					//email
					if(strlen($janrain_response["profile"]["verifiedEmail"])) {
						$arrUserParams["email"] = $janrain_response["profile"]["verifiedEmail"];
						$arrUserParams["status"] = true; 
					} else {
						$arrUserParams["email"] = $janrain_response["profile"]["email"];
						$arrUserParams["status"] = false; 
					}

					//social profile url
					if(strlen($janrain_response["profile"]["providerSpecifier"])) {
						$arrUserField[$janrain_response["profile"]["providerSpecifier"]] = $janrain_response["profile"]["url"];
					}
					
					if(array_key_exists("accessCredentials", $janrain_response) && is_array($janrain_response["accessCredentials"])) {
						if(array_key_exists("accessToken", $janrain_response["accessCredentials"])) {
							$arrUserToken = array("type" => $janrain_response["profile"]["providerSpecifier"]
													, "token" => $janrain_response["accessCredentials"]["accessToken"]
							);
						} elseif(array_key_exists("oauthToken", $janrain_response["accessCredentials"])) {
							$arrUserToken = array("type" => $janrain_response["profile"]["providerSpecifier"]
													, "token" => $janrain_response["accessCredentials"]["oauthToken"]
							);
						} elseif(array_key_exists("oauthTokenSecret", $janrain_response["accessCredentials"])) {
							$arrUserToken = array("type" => $janrain_response["profile"]["providerSpecifier"]
													, "token" => $janrain_response["accessCredentials"]["oauthTokenSecret"]
							);							
						}
						
					}
/*
					$username = $janrain_response["profile"]["displayName"];
					$name = $janrain_response["profile"]["name"]["givenName"];	
					$surname = $janrain_response["profile"]["name"]["familyName"];
					if(strpos($janrain_response["profile"]["photo"], "?") === false) {
						$avatar = $janrain_response["profile"]["photo"];
					} else {
						$avatar = substr($janrain_response["profile"]["photo"], 0, strpos($janrain_response["profile"]["photo"], "?"));
					}

					//email
					if(strlen($janrain_response["profile"]["verifiedEmail"])) {
						$email = $janrain_response["profile"]["verifiedEmail"];
						$status = true; 
					} else {
						$email = $janrain_response["profile"]["email"];
						$status = false; 
					}

					//social profile url
					if(strlen($janrain_response["profile"]["providerSpecifier"])) {
						$arrUserField[$janrain_response["profile"]["providerSpecifier"]] = $janrain_response["profile"]["url"];
					}

					if(MOD_SEC_STRICT_FIELDS) {
						if(strlen($name)) {
							$arrUserField["name"] = $name;
						}
						if(strlen($surname)) {
							$arrUserField["surname"] = $surname;
						}
						if(strlen($avatar)) {
							$arrUserField["avatar"] = $avatar;
						}
					}
					*/
					if (strlen($arrUserParams["email"]))
						$is_valid = true;
					else
						$sError = ffTemplate::_get_word_by_code("janrain_empty_email");
				}
			}
			else
				$sError = ffTemplate::_get_word_by_code("janrain_not_reachable");
		}
		else
			$sError = ffTemplate::_get_word_by_code("janrain_api_error");

		if($is_valid)
		{
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
						$sError = ffTemplate::_get_word_by_code("janrain_login_domain_not_found");
					}
				}
				else
				{
					$ID_domain = 0;
				}
			}

			if (!strlen($sError))
			{
				$res = mod_security_set_user_by_social("janrain", $arrUserParams, $arrUserField, $arrUserToken, $ID_domain, false, true);
				$sError = $res["error"];
			}
		} 
		else
			$sError = ffTemplate::_get_word_by_code("janrain_login_invalid") . $sError;
		break;
		
	case "logout":
		// DISTRUGGE LA SESSIONE
		$res = $cm->modules["security"]["events"]->doEvent("janrain_logout", array());
		$last_res = end($res);
		if (!$last_res)
		{
			mod_security_destroy_session(false);
		}
		break;
	
	case "cancellogout":
		$cm->modules["security"]["events"]->doEvent("janrain_cancellogout", array());
		break;
}

if ($sError)
{
	$cm->modules["security"]["events"]->doEvent("janrain_error", array(&$sError, &$ret_url, &$err_url));
}

if($cm->isXHR())
{
	if (strlen($sError))
		die($sError);
	if (strlen($ret_url))
		die($ret_url);
	exit;
}

if (strlen($sError))
{
	if (strlen($err_url))
		ffRedirect($err_url, null, "sError=" . rawurlencode($sError));
}
else
{
	if (strlen($ret_url))
		ffRedirect($ret_url);
}

// in tutti gli altri casi interrompe l'esecuzione dello script
exit;