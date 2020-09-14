<?php
$cm->oPage->layer = "empty";

$framework_css = mod_sec_get_framework_css();
$mod_sec_login = $cm->router->getRuleById("mod_sec_login");
$mod_sec_dashboard = $cm->router->getRuleById("mod_sec_dashboard");
if($mod_sec_dashboard)
	$dashboard_ret_url = $mod_sec_dashboard->reverse;
else
	$dashboard_ret_url = $mod_sec_login->reverse;

if (mod_security_check_session(false) && get_session("UserNID") != MOD_SEC_GUEST_USER_ID)
{
    $filename = cm_cascadeFindTemplate("/contents/social/logged.html", "security");
	/*if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/logged.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/logged.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/logged.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("domain", $_SERVER["HTTP_HOST"]);

	/**
	* 	Responsive Parsing
	*/
   	$component_class["base"] = $framework_css["component"]["class"];
   	$component_class["popup"] = "social-popup";
    if($framework_css["component"]["grid"]) {
        if(is_array($framework_css["component"]["grid"]))
            $component_class["grid"] = cm_getClassByFrameworkCss($framework_css["component"]["grid"], "col");
        else {
            $component_class["grid"] = cm_getClassByFrameworkCss("", $framework_css["component"]["grid"]);      
        }
    }   
    $tpl->set_var("container_class", implode(" ", array_filter($component_class))); 
    $tpl->set_var("inner_wrap_class", cm_getClassByDef($framework_css["inner-wrap"]));

	if(MOD_SEC_USER_AVATAR) {
		if(MOD_SEC_GROUPS) {
		    $user_permission = get_session("user_permission");
			$avatar = $user_permission["avatar"];
		} else {
		    $avatar = mod_security_getUserInfo(MOD_SEC_USER_AVATAR, null, $db)->getValue();
		}
		
		$tpl->set_var("avatar_class", cm_getClassByDef($framework_css["logout"]["account"]["avatar"]));
		$tpl->set_var("avatar", mod_sec_get_avatar($avatar, MOD_SEC_USER_AVATAR_MODE));
		$tpl->parse("SectAvatar", false);
	}
		
	$email = ffCommon_specialchars(mod_security_getUserInfo("email", null, $db)->getValue());
	
	$username = ffCommon_specialchars(mod_security_getUserInfo(MOD_SEC_USER_FIRSTNAME, null, $db)->getValue() . " " . mod_security_getUserInfo(MOD_SEC_USER_LASTNAME, null, $db)->getValue());
	if(!$username)
		$username = ffCommon_specialchars(mod_security_getUserInfo(MOD_SEC_USER_FIRSTNAME, null, $db)->getValue());
	if(!$username) {
		if((MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username"))
			$username = ffCommon_specialchars(mod_security_getUserInfo("username", null, $db)->getValue());
		elseif((MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")) {
			$username = $email;
			$email = "";
		}
	}

	if($username) {
		$tpl->set_var("username", $username);
		$tpl->parse("SectUsername", false);
	}
	if($email) {
		$tpl->set_var("email", $email);
		$tpl->parse("SectEmail", false);
	}

    $tpl->set_var("logout_class", cm_getClassByDef($framework_css["logout"]["def"]));
    $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]));
    $tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"]));
    $tpl->set_var("login_url", $dashboard_ret_url);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));

	$cm->oPage->addContent($tpl);
	return;
}

if (isset($_GET['code'])) 
{
	$client = mod_sec_social_get_google_client();

	$client->authenticate($_GET['code']);
	$access_token = $client->getAccessToken();

	$oauth2 = new Google_Service_Oauth2($client);
	
	$ret = $oauth2->userinfo->get();
	
    $arrUserParams["email"] = $ret["email"];

    if(!strlen($username)) {
        $username = $ret["name"];
    }
	if(!strlen($username)) {
        $arrUsername = explode("@", $arrUserParams["email"]);

        $username = $arrUsername[0] . " " . substr($arrUsername[1], 0, strpos($arrUsername[1], "."));
        $username = ucwords(str_replace(array(".", "-", "_"), array(" "), $username));
    }
        
    $arrUserParams["username"] = $username;
	if(MOD_SEC_STRICT_FIELDS)
	{
		if(strlen(MOD_SEC_USER_FIRSTNAME)) 
		{
			if(array_search(MOD_SEC_USER_FIRSTNAME, $arrDefaultFields) === false)
				$arrUserField[MOD_SEC_USER_FIRSTNAME] = $ret["familyName"];	
			else
				$arrUserParams[MOD_SEC_USER_FIRSTNAME] = $ret["familyName"];	
		}
		
		if(strlen(MOD_SEC_USER_LASTNAME)) 
		{
			if(array_search(MOD_SEC_USER_LASTNAME, $arrDefaultFields) === false)
				$arrUserField[MOD_SEC_USER_LASTNAME] = $ret["givenName"];
			else
				$arrUserParams[MOD_SEC_USER_LASTNAME] = $ret["givenName"];
		}
		
		/*if(strlen(MOD_SEC_USER_AVATAR)) 
		{
			if(strpos($ret["picture"], "?") === false)
				$tmp_avatar = $ret["picture"];
			else
				$tmp_avatar = substr($ret["picture"], 0, strpos($ret["picture"], "?"));

			if(array_search(MOD_SEC_USER_AVATAR, $arrDefaultFields) === false)
				$arrUserField[MOD_SEC_USER_AVATAR] = $tmp_avatar;
			else
				$arrUserParams[MOD_SEC_USER_AVATAR] = $tmp_avatar;
		}*/
	} 
	else 
	{
		if(array_search("name", $arrDefaultFields) === false)
			$arrUserField["name"] = $ret["givenName"];	
		else
			$arrUserParams["name"] = $ret["givenName"];	

		if(array_search("surname", $arrDefaultFields) === false)
			$arrUserField["surname"] = $ret["familyName"];
		else
			$arrUserParams["surname"] = $ret["familyName"];

		/*if(strpos($ret["picture"], "?") === false)
			$tmp_avatar = $ret["picture"];
		else
			$tmp_avatar = substr($ret["picture"], 0, strpos($ret["picture"], "?"));

		if(array_search("avatar", $arrDefaultFields) === false)
			$arrUserField["avatar"] = $tmp_avatar;
		else
			$arrUserParams["avatar"] = $tmp_avatar;*/
	}
	
	//email
	$arrUserParams["email"] = $ret["email"];
	if($ret["verifiedEmail"] == "1") 
		$arrUserParams["status"] = true; 
	else
		$arrUserParams["status"] = false; 

	//social profile url
	/*if(strlen($ret["profile"]["providerSpecifier"])) 
	{
		$arrUserField[$ret["profile"]["providerSpecifier"]] = $ret["profile"]["url"];
	}*/

	// token
	$UserToken["token"] = $access_token;
	$UserToken["type"] = "google";
		
	$res = mod_security_set_user_by_social("google", $arrUserParams, $arrUserField, $UserToken, null, false, true);
	$sError = $res["error"];
	
	if (strlen($sError))
    {
        $cm->modules["security"]["events"]->doEvent("google_error", array(&$sError, &$ret_url, &$err_url));

        $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
        /*if ($filename === null)
            $filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/error.html", $cm->oPage->theme, false);
        if ($filename === null)
            $filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/error.html", $cm->oPage->theme, false);
        if ($filename === null)
            $filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/error.html", $cm->oPage->theme);*/

        $tpl = ffTemplate::factory(ffCommon_dirname($filename));
        $tpl->load_file(basename($filename), "main");

        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("theme", $cm->oPage->theme);
        $tpl->set_var("domain", $_SERVER["HTTP_HOST"]);
        
        $tpl->set_var("sError", mod_sec_process_error(ffCommon_specialchars($sError)));

		/**
		* 	Responsive Parsing
		*/
		$component_class["base"] = $framework_css["component"]["class"];
   		$component_class["popup"] = "social-popup";
	    if($framework_css["component"]["grid"]) {
	        if(is_array($framework_css["component"]["grid"]))
	            $component_class["grid"] = cm_getClassByFrameworkCss($framework_css["component"]["grid"], "col");
	        else {
	            $component_class["grid"] = cm_getClassByFrameworkCss("", $framework_css["component"]["grid"]);      
	        }
	    }   
	    $tpl->set_var("container_class", implode(" ", array_filter($component_class))); 
	    $tpl->set_var("inner_wrap_class", cm_getClassByDef($framework_css["inner-wrap"]));		
	    
	    $tpl->set_var("login_class", cm_getClassByDef($framework_css["logout"]["def"]));
	    $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
	    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]));
	    $tpl->set_var("logout_button_class", cm_getClassByDef($framework_css["actions"]["logout"]));
	    $tpl->set_var("logout_url", $mod_sec_login->reverse);
	    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));
        
        $cm->oPage->addContent($tpl);
        return;
    }

    $filename = cm_cascadeFindTemplate("/contents/social/success.html", "security");
    /*if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/success.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/success.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/success.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("domain", $_SERVER["HTTP_HOST"]);

	/**
	* 	Responsive Parsing
	*/
   	$component_class["base"] = $framework_css["component"]["class"];
    if($framework_css["component"]["grid"]) {
        if(is_array($framework_css["component"]["grid"]))
            $component_class["grid"] = cm_getClassByFrameworkCss($framework_css["component"]["grid"], "col");
        else {
            $component_class["grid"] = cm_getClassByFrameworkCss("", $framework_css["component"]["grid"]);      
        }
    }   
    $tpl->set_var("container_class", implode(" ", array_filter($component_class))); 
    $tpl->set_var("inner_wrap_class", cm_getClassByDef($framework_css["inner-wrap"]));

	if(MOD_SEC_USER_AVATAR) {
		if(MOD_SEC_GROUPS) {
		    $user_permission = get_session("user_permission");
			$avatar = $user_permission["avatar"];
		} else {
		    $avatar = mod_security_getUserInfo(MOD_SEC_USER_AVATAR, null, $db)->getValue();
		}

		$tpl->set_var("avatar_class", cm_getClassByDef($framework_css["logout"]["account"]["avatar"]));
		$tpl->set_var("avatar", mod_sec_get_avatar($avatar, MOD_SEC_USER_AVATAR_MODE));
		$tpl->parse("SectAvatar", false);
	}
		
	$email = ffCommon_specialchars(mod_security_getUserInfo("email", null, $db)->getValue());
	
	$username = ffCommon_specialchars(mod_security_getUserInfo(MOD_SEC_USER_FIRSTNAME, null, $db)->getValue() . " " . mod_security_getUserInfo(MOD_SEC_USER_LASTNAME, null, $db)->getValue());
	if(!$username)
		$username = ffCommon_specialchars(mod_security_getUserInfo(MOD_SEC_USER_FIRSTNAME, null, $db)->getValue());
	if(!$username) {
		if((MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "username"))
			$username = ffCommon_specialchars(mod_security_getUserInfo("username", null, $db)->getValue());
		elseif((MOD_SECURITY_LOGON_USERID == "both" || MOD_SECURITY_LOGON_USERID == "email")) {
			$username = $email;
			$email = "";
		}
	}

	if($username) {
		$tpl->set_var("username", $username);
		$tpl->parse("SectUsername", false);
	}
	if($email) {
		$tpl->set_var("email", $email);
		$tpl->parse("SectEmail", false);
	}

    $tpl->set_var("logout_class", cm_getClassByDef($framework_css["logout"]["def"]));
    $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]));
    $tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"])); 
    $tpl->set_var("login_url", $dashboard_ret_url);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));	
	
	$cm->oPage->addContent($tpl);
}
else
{
    $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
	/*if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/error.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/error.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/error.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");

	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("domain", $_SERVER["HTTP_HOST"]);

	$tpl->set_var("sError", mod_sec_process_error(ffCommon_specialchars("Unable to find code")));

	$tpl->set_var("login_url", $dashboard_ret_url);

	$cm->oPage->addContent($tpl);
}
