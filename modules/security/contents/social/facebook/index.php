<?php
$cm->oPage->layer = "empty";

$old_session_name = null;

$framework_css = mod_sec_get_framework_css();
$mod_sec_login = $cm->router->getRuleById("mod_sec_login");

if (mod_security_check_session(false) && get_session("UserNID") != MOD_SEC_GUEST_USER_ID)  
{
    $filename = cm_cascadeFindTemplate("/contents/social/logged.html", "security");
    /*
	if ($filename === null)
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
		//$tpl->set_var("username_class", cm_getClassByDef($framework_css["logout"]["account"]["username"]));
		$tpl->set_var("username", $username);
		$tpl->parse("SectUsername", false);
	}
	if($email) {
		//$tpl->set_var("email_class", cm_getClassByDef($framework_css["logout"]["account"]["email"]));
		$tpl->set_var("email", $email);
		$tpl->parse("SectEmail", false);
	}

    $tpl->set_var("logout_class", cm_getClassByDef($framework_css["logout"]["def"]));
    $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]));
    $tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"]["def"])); 
    $tpl->set_var("login_url", $mod_sec_login->reverse);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));

	$cm->oPage->addContent($tpl);
	return;
}
else
{
	$old_session_name = session_name();
	session_name("modsec_fbsess");
	if (isset($_POST[session_name()]))
		session_id($_POST[session_name()]);
	elseif (isset($_GET[session_name()]))
		session_id($_GET[session_name()]);
	elseif (isset($_COOKIE[session_name()]))
		session_id($_COOKIE[session_name()]);
	session_start();	
}

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;

FacebookSession::setDefaultApplication(MOD_SEC_SOCIAL_FACEBOOK_APPID, MOD_SEC_SOCIAL_FACEBOOK_SECRET);

$helper = new FacebookRedirectLoginHelper(MOD_SEC_SOCIAL_FACEBOOK_CLIENT_REDIR_URI);
$helper->disableSessionStatusCheck();

$loginUrl = $helper->getLoginUrl(explode(",", MOD_SEC_SOCIAL_FACEBOOK_APPSCOPE), null, true, "https");

if ($old_session_name !== null)
	session_name($old_session_name);

ffRedirect($loginUrl);
