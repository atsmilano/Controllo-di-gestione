<?php
$logged = mod_security_check_session();

$db = mod_security_get_main_db();
$now = new ffData(date("Y-m-d H:i:s"), "DateTime", "ISO9075");

if (isset($_REQUEST["client_id"]))
{
	$db->query("SELECT * FROM `oauth_clients` WHERE `client_id` = " . $db->toSql($_REQUEST["client_id"]));
	if ($db->nextRecord())
	{
		$client_data = $db->record;
		$_REQUEST["scope"] = $_GET["scope"] = $_POST["scope"] = $client_data["scope"];
	}
}

$server = modsec_getOauth2Server();

if ($client_data["disable_csrf"])
	$server->setConfig('enforce_state', false);
else
	$server->setConfig('enforce_state', true);

$_REQUEST["response_type"] = $_GET["response_type"] = $_POST["response_type"] = "code";
		
$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

// validate the authorize request
if (!$server->validateAuthorizeRequest($request, $response)) {
	modsec_OAuth2Error($response);
	return;
}

$granted = null;

// check before if was already granted
$params = $request->getAllQueryParameters();
$sSQL = "SELECT `granted` FROM `oauth_rel_users` WHERE `client_id` = " . $db->toSql($params["client_id"]) . " AND `ID_user` = " . $db->toSql(get_session("UserNID"));
$db->query($sSQL);
if ($db->nextRecord())
	$granted = $db->record["granted"];

$frmAction = $_REQUEST["frmAction"];

// display an authorization form
if (!$granted && !strlen($frmAction))
{
	$template_file = "authorize.html";

    $filename = cm_cascadeFindTemplate("/contents/oauth2/" . $template_file, "security");
    /*
	$filename = null;
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . rtrim($cm->path_info, "/") . "/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/oauth2/" . $template_file, $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/oauth2/" . $template_file, $cm->oPage->theme);
	*/
	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("http_domain", $_SERVER["HTTP_HOST"]);

	$cm->preloadApplets($tpl);
	$cm->parseApplets($tpl);

	$tpl->set_var("ret_url",			$_REQUEST["ret_url"]);
	$tpl->set_var("encoded_ret_url",	rawurlencode($_REQUEST["ret_url"]));
	$tpl->set_var("encoded_this_url",	rawurlencode($cm->oPage->getRequestUri()));
	$tpl->set_var("query_string",		$_SERVER["QUERY_STRING"]);
	$tpl->set_var("path_info",			$_SERVER["PATH_INFO"]);
	$tpl->set_var("app_title",			ffCommon_specialchars(CM_LOCAL_APP_NAME));
	
	if ($client_data["sso"])
	{
		$sSQL = "SELECT 
						`oauth_clients`.*
					FROM 
						`oauth_clients`
						LEFT JOIN `oauth_rel_users` ON
							`oauth_rel_users`.`client_id` = `oauth_clients`.`client_id`
							AND `oauth_rel_users`.`ID_user` = " . $db->toSql(get_session("UserNID"), "Number") . "
					WHERE 
						`oauth_clients`.`sso` = 1 
						AND (`oauth_rel_users`.`granted` = 0 OR `oauth_rel_users`.`granted` IS NULL)
					ORDER BY 
						`oauth_clients`.`client_id`";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			do
			{
				$tpl->set_var("client_description",	ffCommon_specialchars($db->record["description"]));
				if (strlen($db->record["url_site"]))
				{
					$tpl->set_var("url_site",	$db->record["url_site"]);
					$tpl->parse("SectMultiWithWWW",	false);
					$tpl->set_var("SectMultiWithoutWWW",	"");
				}
				else
				{
					$tpl->parse("SectMultiWithoutWWW",	false);
					$tpl->set_var("SectMultiWithWWW",	"");
				}
				
				if (strlen($db->record["url_privacy"]))
				{
					$tpl->set_var("url_privacy",	$db->record["url_privacy"]);
					$tpl->parse("SectMultiPrivacy",	false);
				}
				else
				{
					$tpl->set_var("SectMultiPrivacy",	"");
				}
				
				$tpl->parse("SectApp", true);
			} while ($db->nextRecord ());
		}
		
		$tpl->parse("SectMultiApp", false);
	}
	else
	{
		$tpl->set_var("client_description",	ffCommon_specialchars($client_data["description"]));
		if (strlen($client_data["url_site"]))
		{
			$tpl->set_var("url_site",	$client_data["url_site"]);
			$tpl->parse("SectSingleWithWWW",	false);
			$tpl->set_var("SectSingleWithoutWWW",	"");
		}
		else
		{
			$tpl->parse("SectSingleWithoutWWW",	false);
			$tpl->set_var("SectSingleWithWWW",	"");
		}
		
		if (strlen($client_data["url_privacy"]))
		{
			$tpl->set_var("url_privacy",	$client_data["url_privacy"]);
			$tpl->parse("SectSinglePrivacy",	false);
		}
		else
		{
			$tpl->set_var("SectSinglePrivacy",	"");
		}
				
		$tpl->parse("SectSingleApp", false);
	}

	if (strlen($server->getAuthorizeController()->getScope()))
	{
		$scopes = explode(" ", $server->getAuthorizeController()->getScope());
		foreach ($scopes as $value)
		{
			$tpl->set_var("scope_name", ffCommon_specialchars($value));
			$tpl->set_var("scope_description", ffCommon_specialchars($db->lookup("oauth_scopes", "scope", $value, null, "description", "Text", true)));
			$tpl->parse("SectScope", true);
		}
		$tpl->parse("SectScopes",	false);
	}
	
	$cm->oPage->layer = "empty";
	$cm->oPage->form_method = "POST";
	$cm->oPage->use_own_form = true;
	$cm->oPage->addContent($tpl);
	return;
}

$is_authorized = ($frmAction === 'yes' || $granted === "1");

$sso_net = array();
$sso_current = null;
$sso_start = null;

// login inside network
if ($client_data["sso"] && $is_authorized && !isset($_REQUEST["no_sso"]))
{
	$dbSSO = mod_security_get_main_db();
	$sSQL = "SELECT * FROM `oauth_clients` WHERE `sso` = 1 ORDER BY `client_id`";
	$db->query($sSQL);
	
	if ($db->nextRecord())
	{
		$i = -1;
		$tmp_response = new OAuth2\Response();
		$response_got = false;
		$sso_sate = null;
		$sso_current= null;
		
		do
		{
			$i++;
			$sso_net[$i] = $db->record;
			if ($db->record["client_id"] === $params["client_id"])
				$sso_start = $i;
			
			$sSQL = "SELECT * FROM `oauth_rel_users` WHERE `client_id` = " . $dbSSO->toSql($db->getField("client_id")) . " AND `ID_user` = " . $dbSSO->toSql(get_session("UserNID"), "Number");
			$dbSSO->query($sSQL);
			if ($dbSSO->nextRecord())
			{
				if (!$dbSSO->record["granted"])
				{
					$sSQL = "UPDATE `oauth_rel_users` SET 
									`granted` = " . $dbSSO->toSql($is_authorized ? 1 : 0, "Number") . " 
									, `when` = " . $dbSSO->toSql($now) . " 
									, `by` = " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
								WHERE 
									`client_id` = " . $dbSSO->toSql($db->getField("client_id")) . " 
									AND `ID_user` = " . $dbSSO->toSql(get_session("UserNID"), "Number");
					$dbSSO->execute($sSQL);
				}
			}
			else
			{
				$sSQL = "INSERT INTO `oauth_rel_users` (`client_id`, `ID_user`, `granted`, `when`, `by`) VALUES (
								" . $dbSSO->toSql($db->getField("client_id")) . "
								, " . $dbSSO->toSql(get_session("UserNID"), "Number")  . "
								, " . $dbSSO->toSql($is_authorized ? 1 : 0, "Number") . " 
								, " . $dbSSO->toSql($now) . " 
								, " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
							)";
				$dbSSO->execute($sSQL);
			}
			
			$_REQUEST["client_id"] = $_GET["client_id"] = $_POST["client_id"] = $db->record["client_id"];
			$request = OAuth2\Request::createFromGlobals();
			
			$_REQUEST["sso_state"] = md5(uniqid("APPID", true));
			$server->handleAuthorizeRequest($request, ($i !== $sso_start && !$response_got ? $response : $tmp_response), $is_authorized, get_session("UserNID"));
			if ($i !== $sso_start && !$response_got)
			{
				$response_got = true;
				$sso_sate = $_REQUEST["sso_state"];
				$sso_current = $i;
			}
			
		} while ($db->nextRecord());
		
		if ($i === 0)
		{
			$response = $tmp_response;
			$response_got = true;
			$sso_sate = $_REQUEST["sso_state"];
			$sso_current = $i;
		}
	}
}
else
{
	if ($granted !== null && $granted !== "1")
	{
		$sSQL = "UPDATE `oauth_rel_users` SET 
						`granted` = " . $db->toSql($is_authorized ? 1 : 0, "Number") . " 
						, `when` = " . $db->toSql($now) . " 
						, `by` = " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
					WHERE 
						`client_id` = " . $db->toSql($params["client_id"]) . " 
						AND `ID_user` = " . $db->toSql(get_session("UserNID"), "Number");
		$db->execute($sSQL);
	}
	else if ($granted === null)
	{
		$sSQL = "INSERT INTO `oauth_rel_users` (`client_id`, `ID_user`, `granted`, `when`, `by`) VALUES (
						" . $db->toSql($params["client_id"]) . "
						, " . $db->toSql(get_session("UserNID"), "Number")  . "
						, " . $db->toSql($is_authorized ? 1 : 0, "Number") . " 
						, " . $db->toSql($now) . " 
						, " . $db->toSql($_SERVER["REMOTE_ADDR"]) . "
					)";
		$db->execute($sSQL);
	}
	
	$server->handleAuthorizeRequest($request, $response, $is_authorized, get_session("UserNID"));
}

$loc = $response->getHttpHeader("Location");
$loc_data = parse_url($loc);
parse_str($loc_data["query"], $query_data);

if ($client_data["sso"] && $is_authorized && !isset($_REQUEST["no_sso"]))
{
	if (isset($query_data["state"]))
		unset($query_data["state"]);
	$query_data["sso_state"] = $sso_sate;
	
	$sso_next = $sso_current + 1;
	
	if ($sso_next === $sso_start)
		$sso_next++;
	
	$query_data["sso_start"] = $sso_start;
	if ($sso_next !== count($sso_net))
		$query_data["sso_next"] = $sso_next;
	else
		$query_data["sso_next"] = $sso_start;
	
}

if (isset($_REQUEST["ret_url"]))
	$query_data["ret_url"] = $_REQUEST["ret_url"];

$loc_data["query"] = ffCommon_http_build_query($query_data);
$loc = ffCommon_http_build_url($loc_data);

if ($client_data["json_only"])
{
	$out = $query_data;
	$out["location"] = $loc;
	cm::jsonParse($out);
	exit;
}
	
$response->setHttpHeader("Location", $loc);
$response->send();
exit;
