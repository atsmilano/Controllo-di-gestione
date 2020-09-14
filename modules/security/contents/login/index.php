<?php
//////////////////////////////////////////////////////////////////////////////////////
// INITS
//////////////////////////////////////////////////////////////////////////////////////
$options = mod_security_get_settings($cm->path_info);

$cm->oPage->form_method = "post";

if(MOD_SEC_LOGIN_FORCE_LAYER)
    $cm->oPage->layer = MOD_SEC_LOGIN_FORCE_LAYER;
else if (ffIsset($cm->modules, "restricted"))
{
	if (ffIsset($cm->modules["restricted"], "layout_bypath") && is_array($cm->modules["restricted"]["layout_bypath"]))
	{
		$max_level = 0;
		$found = false;
		foreach ($cm->modules["restricted"]["layout_bypath"] as $path => $path_opts)
		{
			if (strpos($cm->path_info . "/", $path . "/") === 0)
			{
				$tmp_level = substr_count($path, "/");
				if ($tmp_level > $max_level)
				{
					$max_level = $tmp_level;
					$found = $path_opts;
				}
			}
		}
		
		if ($found !== false)
		{
			if (ffIsset($found, "layer"))
				$cm->oPage->layer = (string)$found["layer"];
			else
				$cm->oPage->layer = "default";
		}
		else
		{
			$cm->oPage->layer = "default";
		}
	}
}
else
	$cm->oPage->layer = "default";

$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));
//$mod_sec_login = $cm->router->getRuleById("mod_sec_login");

$mod_sec_recover = ($cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code) 
						? $cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code)
						: $cm->router->getRuleById("mod_sec_recover")
					);
$mod_sec_recover_username = ($cm->router->getRuleById("mod_sec_recover_username_" . $tiny_lang_code) 
								? $cm->router->getRuleById("mod_sec_recover_username_" . $tiny_lang_code)
								: $cm->router->getRuleById("mod_sec_recover_username")
							);
$mod_sec_register = ($cm->router->getRuleById("mod_sec_register_" . $tiny_lang_code) 
						? $cm->router->getRuleById("mod_sec_register_" . $tiny_lang_code)
						: $cm->router->getRuleById("mod_sec_register")
					);

$social_enabled = (MOD_SEC_SOCIAL_GOOGLE || MOD_SEC_SOCIAL_FACEBOOK || MOD_SEC_SOCIAL_JANRAIN);

$sError = "";
$sErrorCode = null;
if (strlen($_REQUEST["sError"]))
	$sError = strip_tags($_REQUEST["sError"]);

$frmAction = strtolower($_REQUEST["frmAction"]);

$req["username"] = $_REQUEST["username"];
$req["password"] = $_REQUEST["password"];
$req["permanent_session"] = $_REQUEST["stayconnected"]; // TOCHECK: troppo semplcistico, vedi common.php

if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
{
	$req["domain"] = $_POST["domain"];
	if ($frmAction == "" && $req["domain"] == "")
		$req["domain"] = $_COOKIE["domain"];
}
else
{
	$req["domain"] = null;
}

$ID_domain = null;
$logged = mod_security_check_session(false);
if ($logged)
{
	if (get_session("UserID") == MOD_SEC_GUEST_USER_NAME)
		$logged = false;
	else
	{
		if (MOD_SEC_MULTIDOMAIN)
			$ID_domain = mod_security_get_domain();
	}
}
/*
$ret_url        = ($_REQUEST["ret_url"]
                    ? $_REQUEST["ret_url"]
                    : $_SERVER["HTTP_REFERER"]
                );*/

$ret_url = $_REQUEST["ret_url"];

$res = $cm->modules["security"]["events"]->doEvent("on_before_login", array($ret_url, $frmAction));
$rc = end($res);
if ($rc !== null) 
{
    $ret_url = $rc;
} else {
    if (!strlen($ret_url)/* || strpos($ret_url, $mod_sec_login->reverse) !== false*/)
        $ret_url = FF_SITE_PATH . "/";    
}

if(!$ret_url)
    $ret_url = FF_SITE_PATH . "/";    

$cm->oPage->ret_url = $ret_url;


$res = $cm->modules["security"]["events"]->doEvent("on_retrieve_params", array(&$sError, &$frmAction, &$logged, $req));
$rc = end($res);
if ($rc !== null)
{
	if ($rc)
		return;
}

//////////////////////////////////////////////////////////////////////////////////////
// ACTIONS
//////////////////////////////////////////////////////////////////////////////////////

switch($frmAction)
{
	case "login":
		$ret =	mod_sec_check_login($req["username"], $req["password"], $req["domain"], $options, $req["permanent_session"], $logged, $sError, false);
		if ($ret["logged"] === true)
		{
			$cm->jsonAddResponse(array(
					"modules" => array(
						"security" => array(
							"action" => "login"
						)
					)
				));
			return _modsec_login_redirect($cm->oPage->ret_url, "login");
		}
		
		$sError			= $ret["error"];
		$sErrorCode		= $ret["error_code"];
		$logged			= $ret["logged"];
		$userID			= $ret["UserID"];
		$userNID		= $ret["UserNID"];
		$domain			= $ret["domain"];
		$ID_domain		= $ret["ID_domain"];
		$cookiehash		= $ret["cookiehash"];
		break;
		
	case "logout":
		if(MOD_SEC_ENABLE_TOKEN && $social_enabled)
		{
			// DESTROY TOKENS
			$social_logout_check = $_REQUEST["social_logout_check"];
			if (is_array($social_logout_check) && count($social_logout_check))
			{
				foreach ($social_logout_check as $type => $check)
				{
					if ($check)
						mod_security_accesstoken_revoke($type, null, get_session("UserNID"), $ID_domain, $cm->path_info);
				}
			}
		}
		// DISTRUGGE LA SESSIONE
		mod_security_destroy_session(false);
		$logged = false;

		$cm->jsonAddResponse(array(
				"modules" => array(
					"security" => array(
						"action" => "logout"
					)
				)
			));
		return _modsec_login_redirect($cm->oPage->ret_url, "logout");
	
	case "cancellogout":
		return _modsec_login_redirect($cm->oPage->ret_url, "cancellogout");
}

//////////////////////////////////////////////////////////////////////////////////////
// OUTPUT
//////////////////////////////////////////////////////////////////////////////////////
$template_file = mod_sec_login_getTemplate($logged);

$res = $cm->modules["security"]["events"]->doEvent("onOutput", array($logged, &$sErrorCode, &$sError, &$template_file));
$rc = end($res);
if ($rc !== null)
{
	if ($rc)
		return;
}
$tpl = mod_sec_login_tpl_load($logged, $template_file);
$cm->oPage->addContent($tpl, null, "login");

if(MOD_SEC_CSS_PATH !== false && isset($cm->router->matched_rules["mod_sec_login"]))
{

	if(MOD_SEC_CSS_PATH)
		$filename = MOD_SEC_CSS_PATH;
	else
        $filename = cm_cascadeFindTemplate("/css/ff.modules.security.css", "security");
		//$filename = cm_moduleCascadeFindTemplateByPath("security", "/css/ff.modules.security.css", $cm->oPage->theme);

	$ret = cm_moduleGetCascadeAttrs($filename);
	$cm->oPage->tplAddCSS("ff.modules.security.css", array(
		"file" => $filename
		, "path" => $ret["path"]
		, "priority" => cm::LAYOUT_PRIORITY_HIGH
		, "index" => 100
	));
}

$res = $cm->modules["security"]["events"]->doEvent("onTplLoad", array(&$tpl, $logged, &$sErrorCode, &$sError));
$rc = end($res);
if ($rc !== null)
{
	if ($rc)
		return;
}

if (MOD_SEC_LOGIN_BACK_URL)
{
	$back_url = (ffIsset($_SERVER, "HTTP_REFERER") && strlen($_SERVER["HTTP_REFERER"]) && !ffIsset($_REQUEST, "ret_url")
					? $_SERVER["HTTP_REFERER"] 
					: FF_SITE_PATH . "/"
			);
	$tpl->set_var("back_url", $back_url);
	$tpl->parse("SectBack", false);
}

$tpl->set_var("username", $username);

if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
{
	$tpl->set_var("domain", $domain);
	$tpl->parse("SectDomain", false);
	$tpl->set_var("focus_target", "domain");
}
else
{
	$tpl->set_var("SectDomain", "");
	$tpl->set_var("focus_target", "username");
}

if (!strlen($sError) && strlen($sErrorCode))
{
	$sError = ffTemplate::_get_word_by_code($sErrorCode);
}

if (strlen($sError))
{
	$tpl->set_var("sError", mod_sec_process_error($sError));
	$tpl->parse("SectError", false);
}

//////////////////////////////////////////////////////////////////////////////////////
// SOCIAL STUFFS
//////////////////////////////////////////////////////////////////////////////////////
$social_url = (string)$cm->router->getRuleById("mod_sec_social")->reverse;

if (MOD_SEC_SOCIAL_GOOGLE)
{
	$tpl->set_var("social_url_google", FF_SITE_PATH . $social_url . "/google");
	$tpl->parse("SectSocialLoginGoogle", false);
	$tpl->parse("SectSocialLogoutGoogle", false);
}

if (MOD_SEC_SOCIAL_FACEBOOK)
{
	$tpl->set_var("social_url_facebook", FF_SITE_PATH . $social_url . "/facebook");
	$tpl->parse("SectSocialLoginFacebook", false);
	$tpl->parse("SectSocialLogoutFacebook", false);
}

if($social_enabled)
	$tpl->set_var("class_social", " social");
else 
	$tpl->set_var("class_social", "");

//////////////////////////////////////////////////////////////////////////////////////
// LOGIN SECTION
//////////////////////////////////////////////////////////////////////////////////////
if (!$logged)
{
	if(MOD_SEC_PASSWORD_RECOVER && $mod_sec_recover)
	{
		$tpl->set_var("recover", (string)$mod_sec_recover->reverse);
		$tpl->parse("SectRecover", false);
	} 

	if(MOD_SEC_USERNAME_RECOVER_USERNAME && $mod_sec_recover_username)
	{     
		$tpl->set_var("recover", (string)$mod_sec_recover_username->reverse);
		$tpl->parse("SectRecoverUsername", false);
	} 

	if(MOD_SEC_LOGIN_REGISTER && $mod_sec_register)
	{
		$tpl->set_var("register", (string)$mod_sec_register->reverse);
		$tpl->parse("SectRegister", false);
	}

	if(MOD_SEC_SOCIAL_JANRAIN)
	{
		$tpl->set_var("janrain_appname", ffCommon_url_rewrite(MOD_SEC_JANRAIN_APPNAME));

		$tpl->parse("SectJanRainLogin", false);
		$tpl->parse("SectJanrain", false);
	} 

	if($social_enabled)
		$tpl->parse("SectSocialLogin", false);

	
	if (MOD_SEC_LOGIN_STANDARD)
	{
		if ($cm->oPage->getXHRCtx())
		{
			$tpl->set_var("login_bt_confirm", "javascript:ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {
						'action' : 'login'
				});");
		}
		else if (MOD_SEC_FORCE_XHR || $cm->oPage->isXHR())
		{
			$tpl->set_var("login_bt_confirm", "javascript:ff.ajax.doRequest({
						'action' : 'login'
						, 'formName'	: 'ffLoginBox'
						, 'url'			: '" . $_SERVER["REQUEST_URI"] . "'
				});");
		}
		else
		{
			$tpl->set_var("login_bt_confirm", "jQuery('#frmAction', this.form).val('login'); this.form.submit();");
		}
		
		$tpl->parse("SectStandardLogin", false);
	}
}
else
{
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();

	if(MOD_SEC_ENABLE_TOKEN && $social_enabled)
	{
		// CHECK VALID TOKENS
		$valid_token = false;
		$sSQL = "SELECT
					*
				FROM
					`" . $options["table_token"] . "`
				WHERE `" . $options["table_token"] . "`.`type` = 'live'
					AND `" . $options["table_token"] . "`.`ID_user` = " . $db->toSql(get_session("UserNID"), "Number");
		if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
			$sSQL .= " AND `ID_domain` = " . $db->toSql($ID_domain);
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			do
			{
				$at_type = $db->getField("type", "Text", true);
				$rc = mod_security_accesstoken_check($at_type, $db->getField("token", "Text", true), get_session("UserNID"), $ID_domain, $cm->path_info);
				if ($rc)
					$tpl->parse("SectSocialLogout_" . $at_type, true);
				$valid_token |= $rc;
			} while ($db->nextRecord());
		}

		if ($valid_token)
			$tpl->parse("SectSocialLogout", false);
	}

	if (MOD_SEC_LOGIN_DOMAIN && mod_security_is_admin())
	{
		$tpl->set_var("firstname", ffCommon_specialchars(mod_security_getUserInfo("firstname", null, mod_security_get_main_db())->getValue()));
		$tpl->set_var("lastname", ffCommon_specialchars(mod_security_getUserInfo("lastname", null, mod_security_get_main_db())->getValue()));
	}

	if ($cm->oPage->getXHRCtx())
	{
		if (strlen($_REQUEST["ret_url"]))
			$tpl->set_var("logout_bt_cancel", "javascript:ff.ajax.ctxGoToUrl('" . $_REQUEST["XHR_CTX_ID"] . "', '" . $_REQUEST["ret_url"] . "')");
		else
			$tpl->set_var("logout_bt_cancel", "javascript:ff.ajax.ctxClose('" . $cm->oPage->getXHRCtx() . "');");

		$tpl->set_var("logout_bt_confirm", "javascript:ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {
				'action' : 'logout'
			});");
	}
	else if (MOD_SEC_FORCE_XHR || $cm->oPage->isXHR())
	{
		$tpl->set_var("logout_bt_confirm", "javascript:ff.ajax.doRequest({
					'action'		: 'logout'
					, 'formName'	: 'ffLoginBox'
					, 'url'			: '" . $_SERVER["REQUEST_URI"] . "'
			});");

		$tpl->set_var("logout_bt_cancel", "javascript:ff.ajax.doRequest({
					'action'		: 'cancellogout'
					, 'formName'	: 'ffLoginBox'
					, 'url'			: '" . $_SERVER["REQUEST_URI"] . "'
			});");
	}
	else
	{
		$tpl->set_var("logout_bt_confirm", "jQuery('#frmAction', this.form).val('logout'); this.form.submit();");
		$tpl->set_var("logout_bt_cancel", "jQuery('#frmAction', this.form).val('cancellogout'); this.form.submit();");
	}
}

$res = $cm->modules["security"]["events"]->doEvent("onTplLoaded", array(&$tpl, $logged, &$sErrorCode, &$sError));
