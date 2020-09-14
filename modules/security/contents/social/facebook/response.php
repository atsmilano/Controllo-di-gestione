<?php
$cm->oPage->layer = "empty";

$old_session_name = null;

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
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
    $tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"]));
    $tpl->set_var("login_url", $dashboard_ret_url);
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

$_SESSION['FBRLH_' . 'state'] = $_GET["state"];

try {
	$session = $helper->getSessionFromRedirect();

	if ($session) 
	{
		try {
            $user_profile = (new FacebookRequest(
                $session, 'GET', '/me?fields=id,email,first_name,last_name,is_verified,birthday,cover,name,gender'
            ))->execute()->getGraphObject(GraphUser::className());


            $arrDefaultFields = explode(",", MOD_SEC_DEFAULT_FIELDS);
            $arrUserParams["email"] = $user_profile->getEmail();

            if(!$username) {
                $username = $user_profile->getName();
            }

            if(!$username) {
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
                        $arrUserField[MOD_SEC_USER_FIRSTNAME] = $user_profile->getFirstName();
                    else
                        $arrUserParams[MOD_SEC_USER_FIRSTNAME] = $user_profile->getFirstName();
                }

                if(strlen(MOD_SEC_USER_LASTNAME))
                {
                    if(array_search(MOD_SEC_USER_LASTNAME, $arrDefaultFields) === false)
                        $arrUserField[MOD_SEC_USER_LASTNAME] = $user_profile->getLastName();
                    else
                        $arrUserParams[MOD_SEC_USER_LASTNAME] = $user_profile->getLastName();
                }

                $cover = $user_profile->getProperty("cover");
                if($cover) {
                    if(array_search(MOD_SEC_USER_AVATAR, $arrDefaultFields) === false)
                        $arrUserField[MOD_SEC_USER_AVATAR] = $cover->getProperty("source");
                    else
                        $arrUserParams[MOD_SEC_USER_AVATAR] = $cover->getProperty("source");
                }
            }
            else
            {
                if(array_search("name", $arrDefaultFields) === false)
                    $arrUserField["name"] = $user_profile->getFirstName();
                else
                    $arrUserParams["name"] = $user_profile->getFirstName();

                if(array_search("surname", $arrDefaultFields) === false)
                    $arrUserField["surname"] = $user_profile->getLastName();
                else
                    $arrUserParams["surname"] = $user_profile->getLastName();

                $cover = $user_profile->getProperty("cover");
                if($cover) {
                    if (array_search("avatar", $arrDefaultFields) === false)
                        $arrUserField["avatar"] = $cover->getProperty("source");
                    else
                        $arrUserParams["avatar"] = $cover->getProperty("source");
                }

                if(array_search("gender", $arrDefaultFields) === false)
                    $arrUserField["gender"] = $user_profile->getGender();
                else
                    $arrUserParams["gender"] = $user_profile->getGender();

                if(array_search("birthday", $arrDefaultFields) === false)
                    $arrUserField["birthday"] = $user_profile->getBirthday();
                else
                    $arrUserParams["birthday"] = $user_profile->getBirthday();
            }

            if($arrUserParams["email"])
                $arrUserParams["status"] = true;

            // token
            $UserToken["token"] = $session->getToken();
            $UserToken["type"] = "facebook";

            if ($old_session_name !== null)
			{
				session_destroy();
				session_name($old_session_name);
				$old_session_name = null;
			}
			
			$res = mod_security_set_user_by_social("facebook", $arrUserParams, $arrUserField, $UserToken, null, false, true);
			$sError = $res["error"];

			if (strlen($sError))
			{
				$cm->modules["security"]["events"]->doEvent("facebook_error", array(&$sError, &$ret_url, &$err_url));

                $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
				/*if ($filename === null)
					$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/social/error.html", $cm->oPage->theme, false);
				if ($filename === null)
					$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/social/error.html", $cm->oPage->theme, false);
				if ($filename === null)
					$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/social/error.html", $cm->oPage->theme);
*/
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
			    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
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
            
            $tpl->set_var("logout_class", cm_getClassByDef($framework_css["logout"]["def"]));
            $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
            $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
            $tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"]));
            $tpl->set_var("login_url", $dashboard_ret_url);
            $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));
            
			$cm->oPage->addContent($tpl);
			
		} catch (FacebookRequestException $e) {
			if ($old_session_name !== null)
			{
				session_destroy();
				session_name($old_session_name);
				$old_session_name = null;
			}
			
			echo "Exception occured, code: " . $e->getCode();
			echo " with message: " . $e->getMessage();
			exit;
			
		}
	} else {
		if ($old_session_name !== null)
		{
			session_destroy();
			session_name($old_session_name);
			$old_session_name = null;
		}
		
		$cm->modules["security"]["events"]->doEvent("facebook_error", array(&$sError, &$ret_url, &$err_url));

        $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
		/*
        if ($filename === null)
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
	    
	    $tpl->set_var("sError", mod_sec_process_error($_REQUEST["error_description"]));

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
	    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
	    $tpl->set_var("logout_button_class", cm_getClassByDef($framework_css["actions"]["logout"]));
	    $tpl->set_var("logout_url", $mod_sec_login->reverse);
	    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));

		$cm->oPage->addContent($tpl);
	}
} catch(FacebookRequestException $ex) {
  // When Facebook returns an error
	if ($old_session_name !== null)
	{
		session_destroy();
		session_name($old_session_name);
		$old_session_name = null;
	}
	
	$cm->modules["security"]["events"]->doEvent("facebook_error", array(&$sError, &$ret_url, &$err_url));

    $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
    /*
	if ($filename === null)
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
    
    $tpl->set_var("sError", mod_sec_process_error(ffCommon_specialchars($ex->getErrorType() . " - (" . $ex->getCode() . "/" . $ex->getSubErrorCode() . ") " . $ex->getMessage())));

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
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
    $tpl->set_var("logout_button_class", cm_getClassByDef($framework_css["actions"]["logout"]));
    $tpl->set_var("logout_url", $mod_sec_login->reverse);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));

	$cm->oPage->addContent($tpl);
	return;
} catch(\Exception $ex) {
  // When validation fails or other local issues
	if ($old_session_name !== null)
	{
		session_destroy();
		session_name($old_session_name);
		$old_session_name = null;
	}
	
	$cm->modules["security"]["events"]->doEvent("facebook_error", array(&$sError, &$ret_url, &$err_url));

    $filename = cm_cascadeFindTemplate("/contents/social/error.html", "security");
    /*
	if ($filename === null)
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

	$tpl->set_var("sError", mod_sec_process_error(ffCommon_specialchars("VALIDATION FAILED")));

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
    $tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
    $tpl->set_var("logout_button_class", cm_getClassByDef($framework_css["actions"]["logout"])); 
    $tpl->set_var("logout_url", $mod_sec_login->reverse);
    $tpl->set_var("error_class", cm_getClassByDef($framework_css["error"]));
    
	$cm->oPage->addContent($tpl);
	return;
}

if ($old_session_name !== null)
{
	session_destroy();
	session_name($old_session_name);
	$old_session_name = null;
}
