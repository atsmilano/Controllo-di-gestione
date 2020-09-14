<?php
// very simple SSO mechanism to demonstrate FF Web OAuth2 and SSO capability
// by Samuele Diella, January 2017
// WebAuth startpage

require __DIR__ . "/common.php";

$cm->oPage->layer = "empty";

if (mod_security_check_session(false) && get_session("UserNID") != MOD_SEC_GUEST_USER_ID)
{
    $filename = cm_cascadeFindTemplate("/contents/social/logged.html", "security");
	/*if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/logged.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/logged.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/logged.html", $cm->oPage->theme);
*/
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
    $tpl->set_var("login_url", $mod_sec_login->reverse);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));	
    
    
	$cm->oPage->addContent($tpl);
} else {
	session_start();
	
	$_SESSION["state"] = sha1(uniqid(APPID, true));

	$authUrl = MOD_SEC_SOCIAL_FF_OAUTH2_URL . "/webauth?client_id=" . MOD_SEC_SOCIAL_FF_CLIENT_ID . "&state=" . rawurlencode($_SESSION["state"]) . "&ret_url=" . rawurlencode("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	ffRedirect($authUrl);
}
