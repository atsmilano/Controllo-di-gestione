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
$server->setConfig("allow_implicit", true);

if ($client_data["disable_csrf"])
	$server->setConfig('enforce_state', false);
else
	$server->setConfig('enforce_state', true);

$_REQUEST["response_type"] = $_GET["response_type"] = $_POST["response_type"] = "token";
		
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

$loc = $response->getHttpHeader("Location");
$loc_data = parse_url($loc);
parse_str($loc_data["query"], $query_data);

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
