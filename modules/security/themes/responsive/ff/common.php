<?php
  function mod_sec_get_framework_css() {
	$framework_css = array(
        "component" => array(
            "class" => "loginBox security nopadding"
            , "type" => null        //null OR '' OR "-inline"
            , "grid" => "row"  //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
        )
        , "inner-wrap" => null
        , "logo" => array(
            "class" => "logo-login" 
            , "col" => array( //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
                            "xs" => 12
                            , "sm" => 12
                            , "md" => 12
                            , "lg" => 12
                        )   
        )        
        , "login" => array(
        	"def" => array(
	            "class" => "login" 
	            , "col" => array( //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
	                            "xs" => 12
	                            , "sm" => 12
	                            , "md" => 12
	                            , "lg" => 12
	                        ) 
	        )
	        , "standard" => array(
	        	"def" => array(
		            "class" => "standard-login" 
		            , "col" => false
		        )
		        , "record" => array(
			        "class" => "login-field"
			        , "form" => null
		        )
		        , "field" => array(
	        		"form" => "control"
		        )
		        , "stayconnect" => array(
			        "class" => "stayconnect" 
		        )        
		        , "recover" => array(
		            "class" => "recover"
		            , "util" => "align-right" 
		        )
	        )        
	        , "social" => array(
	        	"def" => array(
		            "class" => "social-login" 
		            , "col" => false
		        )
				, "google" => array(
	                "class" => "google"
	                , "button" => array(
	                    "value" => "primary"
	                    , "params" => array(
	                        "width" => "full"
	                    )
	                )
	            )
	            , "facebook" => array(
	                "class" => "facebook"
	                , "button" => array(
	                    "value" => "primary"
	                    , "params" => array(
	                        "width" => "full"
	                    )
	                )
	            )        
	            , "janrain" => array(
	                "class" => "janrain"
	            )		        
	        )        
        )
        , "logout" => array(
        	"def" => array(
	            "class" => "logout" 
	            , "col" => array( //false OR array(xs, sm, md, lg) OR 'row' OR 'row-fluid'
	                            "xs" => 12
	                            , "sm" => 12
	                            , "md" => 6
	                            , "lg" => 5
	                        ) 
	            , "util" => array(
	            	"align-center"
	            )
	        )
	        , "account" => array(
	        	"def" => array(
		            "class" => "account" 
		            , "col" => false
		            , "util" => "align-center"
		        )
		        , "avatar" => array(
					"class" => "avatar" 
		            , "util" => "corner-circle"		        
		        )
		        , "username" => array(
		        )
		        , "email" => array(
		        )
	        )
        )
 		, "actions" => array(
		    "def" => array(
			    "class" => "actions"
			    , "form" => null
		    )
			, "login" => array(
                "class" => null
                , "button" => array(
                    "value" => "primary" 
                    , "params" => array(
                        "width" => "full"
                    )
                )
            )
        	, "logout" => array(
                "class" => null
                , "button" => array(
                    "value" => "primary"
                    , "params" => array(
                        "width" => "full"
                    )
                )
            )
			, "activation" => array(
	            "class" => null
	            , "button" => array(
	                "value" => "primary"
	                , "params" => array(
	                    "width" => "full"
	                )
	            )
	        )
	        , "recover" => array(
	            "class" => null
	            , "button" => array(
	                "value" => "primary"
	                , "params" => array(
	                    "width" => "full"
	                )
	            )
	        )		    
		)        
        , "links" => array(
        	"def" => array(
            	"class" => "link-login" 
            )
	        , "register" => array(
	            "class" => "register"
	            , "util" => "right"
	        )
	        , "back" => array(
	            "class" => "back"
	            , "util" => "left"
	        )
        )
        , "error" => array(
            "class" => "error"
            , "callout" => "danger"
        )
        , "icons" => array(
			"caret-collapsed" => cm_getClassByFrameworkCss("chevron-right", "icon")
			, "caret" => cm_getClassByFrameworkCss("chevron-down", "icon")
			, "settings" => cm_getClassByFrameworkCss("cog", "icon")
		)
    );

	return $framework_css;
}

//ADDED-ATS*********************************************************************
function mod_security_get_logo($logo = null, $restricted = false)
{
    $cm = cm::getInstance();        

    if($logo && is_file(FF_DISK_PATH . $logo))
        $logo_url = $logo;
    elseif($restricted && is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/images/logo/".LOGO_RESTRICTED_FILENAME))
        $logo_url = ff_getThemePath($cm->oPage->getTheme()) . "/" . $cm->oPage->getTheme() . "/images/logo/".LOGO_RESTRICTED_FILENAME;
    elseif(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/images/logo/".LOGO_LOGIN_FILENAME))
        $logo_url = ff_getThemePath($cm->oPage->getTheme()) . "/" . $cm->oPage->getTheme() . "/images/logo/".LOGO_LOGIN_FILENAME;    
    elseif(is_file(FF_THEME_DISK_PATH . "/" . cm_getMainTheme() . "/images/".LOGO_NOBRAND_FILENAME))
        $logo_url = ff_getThemePath(cm_getMainTheme()) . "/" . cm_getMainTheme() . "/images/".LOGO_NOBRAND_FILENAME;

    return $logo_url;
}


function mod_security_get_logo_qualita($stampa = false)
{
    $cm = cm::getInstance();
    
    if ($stampa == false) {
        $logo_filename = LOGO_QUALITA_FILENAME;
    }
    else {
        $logo_filename = LOGO_QUALITA_STAMPA_FILENAME;
    }

    $logo = FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/images/logo/".$logo_filename;
    if(is_file($logo))     
        return ff_getThemePath($cm->oPage->getTheme()) . "/" . $cm->oPage->getTheme() . "/images/logo/".$logo_filename;        
    else
        return null;   
}
//******************************************************************************

function mod_security_cm_on_load_brand($page, $tpl)
{
	if (!mod_security_check_session(false))
		return;

	$cm = cm::getInstance();

	$framework_css = mod_restricted_get_framework_css(); 
	$ID_domain = mod_security_get_domain();
	
	$tpl->set_var("logo_class", cm_getClassByDef($framework_css["logo"]));
	if($ID_domain)
		$host_name = get_session("Domain");
	else
		$host_name = CM_LOCAL_APP_NAME;

    $logo_url = mod_security_get_logo(MOD_RESTRICTED_LOGO_PATH, true);    

    //ADDED-ATS*****************************************************************
    
        $logo_qualita = mod_security_get_logo_qualita();        
        if($logo_qualita) {           
            $tpl->set_var("logo_qualita_url", $logo_qualita);
            $tpl->set_var("logo_qualita_name", "qualità");
            $tpl->parse("SectLogoQualita", false);
        }    
    //**************************************************************************
    
	if(get_session("UserLevel") >= MOD_SEC_BRAND_ACL) {
	    if($logo_url) {
			$tpl->set_var("logo_url", $logo_url);
			$tpl->set_var("logo_name", $host_name);
			$tpl->parse("SectLogo", false);
	    } else {
	        if($ID_domain)
	            $tpl->set_var("host_name", get_session("Domain"));
	        else
	            $tpl->set_var("host_name", CM_LOCAL_APP_NAME);
	    }                

		$tpl->set_var("nav_left_class", "domain");//cm_getClassByDef($framework_css["fullbar"]["nav"]["left"]));
		$tpl->set_var("more_icon", '<i class="' . $framework_css["icons"]["settings"] . '"></i>');
		$tpl->set_var("toggle_properties", $framework_css["collapse"]["action"]);
		$tpl->set_var("panel_class", cm_getClassByDef($framework_css["dropdown"]["container"]));
		$tpl->set_var("panel_header_class", cm_getClassByDef($framework_css["dropdown"]["header"]));
		$tpl->set_var("panel_body_class", cm_getClassByDef($framework_css["dropdown"]["body"]["def"]));
		$tpl->set_var("panel_links_class", cm_getClassByDef($framework_css["dropdown"]["body"]["links"]));
		$tpl->set_var("panel_footer_class", cm_getClassByDef($framework_css["dropdown"]["footer"]));

		/*$mod_sec_domains = $cm->router->getRuleById("mod_sec_domains");
		if($mod_sec_domains->reverse) {
			$tpl->set_var("manage_domains", FF_SITE_PATH . $mod_sec_domains->reverse);
			$tpl->set_var("domains_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["domains"]));
			$tpl->parse("SectDomains", false);
		}
		$mod_sec_profiling = $cm->router->getRuleById("mod_sec_profiling");	
		if($mod_sec_profiling->reverse) {
			$tpl->set_var("manage_profiling", FF_SITE_PATH . $mod_sec_profiling->reverse);
			$tpl->set_var("profiling_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["profiling"]));
			$tpl->parse("SectProfiling", false);
		}
        $mod_restricted_settings = $cm->router->getRuleById("mod_restricted_settings");
        if($mod_restricted_settings->reverse) {
            $tpl->set_var("manage_settings", FF_SITE_PATH . $mod_restricted_settings->reverse);
            $tpl->set_var("settings_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["settings"]));
            $tpl->parse("SectSettings", false);
        }*/

	    if($page->sections["admin"]) {
			if($page->tpl_layer[0]->isset_var("brand") && !$page->tpl_layer[0]->isset_var("admin")) {

				$tpl->set_var("admin", $page->sections["admin"]["tpl"]->rpparse("SectMenu", false));
			}
		}    
	    
		$tpl->parse("SectBrandName", false);

		if(MOD_SEC_MULTIDOMAIN && !defined("MOD_SEC_NOACCOUNTSCOMBO"))
		{
			if (mod_security_is_admin())
			{
				//if(!$ID_domain)
				//	$host_class = " hidden";
					
				//$tpl->set_var("host_class", cm_getClassByDef($framework_css["fullbar"]["nav"]["left"]) . $host_class);
				//$tpl->set_var("host_name", get_session("Domain"));
				//$tpl->set_var("host_icon", cm_getClassByFrameworkCss("external-link", "icon-tag"));
				
				$field = ffField::factory($page);
				$field->id = "accounts";
				$field->base_type = "Number";
				$field->widget = "actex";
				$field->actex_update_from_db = true;
				$field->multi_select_one_label = ffTemplate::_get_word_by_code("master_domain");
				$field->source_SQL = "SELECT ID, nome FROM " . CM_TABLE_PREFIX . "mod_security_domains ORDER BY nome";
				$mod_sec_setparams = $cm->router->getRuleById("mod_sec_setparams");
				if($mod_sec_setparams->reverse) {
					$field->actex_on_change  = "function(obj, old_value, action) {
						if(action == 'change') {
							jQuery.get('" . $mod_sec_setparams->reverse . "?accounts=' + obj.value, function(data) {
								if(data['id'] > 0) {
									jQuery('#domain-title').text(data['name']);
									jQuery('#domain-title').attr('href', 'http://' + data['name']);
									jQuery('#domain-title').parent().removeClass('hidden');
								} else {
									jQuery('#domain-title').parent().addClass('hidden');
								}
								jQuery('body').addClass('loading');
								window.location.reload();
							});
						}
					}";
				} else {
					$field->actex_on_change  = "function(obj, old_value, action) {
						if(action == 'change') {
							if(obj.value > 0) {
								window.location.href = ff.urlAddParam(window.location.href, 'accounts', obj.value);
							} else {
								window.location.href = ff.urlAddParam(window.location.href, 'accounts').replace('accounts&', '');
							}
						}					
					}";
				}
				$field->value = new ffData($ID_domain, "Number");
				$field->parent_page = array(&$page);
				$field->db = array(mod_security_get_main_db());
				$tpl->set_var("domain_switch", $field->process());
				
				$tpl->parse("SectMultiDomain", false);
			}
			
		}	
		$tpl->parse("SectBrandInfo", false);
	    $tpl->parse("SectBrandPanel", false);
	} elseif($logo_url) {
		$tpl->set_var("logo_url", $logo_url);	
    	$tpl->parse("SectBrandNoPanel", false);
	}
}

function mod_security_cm_on_load_lang($page, $tpl)
{
	if (!mod_security_check_session(false))
		return;

	$cm = cm::getInstance();

	$framework_css = mod_restricted_get_framework_css(); 
	
	$flag_dim = "16";
	if(MOD_SEC_GROUPS) {
		$user_permission = get_session("user_permission");
		$locale["lang"] = $user_permission["lang"];
	}
	if(!$locale["lang"])
		$locale = mod_security_get_locale();
	
	if(is_array($locale["lang"]) && count($locale["lang"])) {
        $filename = cm_cascadeFindTemplate("/css/lang-flags" . $flag_dim . ".css", "security");
		//$filename = cm_moduleCascadeFindTemplateByPath("restricted", "/css/lang-flags" . $flag_dim . ".css", $cm->oPage->theme);
		$ret = cm_moduleGetCascadeAttrs($filename);
		$cm->oPage->tplAddCSS("lang-flags" . $flag_dim . ".css", array(
			"file" => $filename
			, "path" => $ret["path"]
		));	

		$tpl->set_var("flag_dim", "f" . $flag_dim);
		$tpl->set_var("toggle_properties", $framework_css["collapse"]["action"]);
		$tpl->set_var("panel_class", cm_getClassByDef($framework_css["dropdown"]["container"]));
		$tpl->set_var("panel_body_class", cm_getClassByDef($framework_css["dropdown"]["body"]["def"]));
		foreach($locale["lang"] AS $code => $params) {
			if($code == "current")
				continue;

            $tpl->set_var("code", $code);
            $tpl->set_var("description", $params["description"]);
			$tpl->set_var("flag_lang", "flag " . $params["tiny_code"]);

			if($code == $locale["lang"]["current"]["code"]) {
				$tpl->set_var("current_class", $framework_css["current"]);
				$tpl->set_var("flag_lang_active", "flag " . $params["tiny_code"]);
				$tpl->parse("SectCurrentLang", false);
			} else {
			$mod_sec_setparams = $cm->router->getRuleById("mod_sec_setparams");
				if($mod_sec_setparams->reverse) {
					$tpl->set_var("lang_url", "
						var that = this;
						jQuery.get('" . FF_SITE_PATH . $mod_sec_setparams->reverse . "?lang=" . $code . "', function(data) {
						jQuery('body').addClass('loading');
						window.location.reload();	
						});");
				} else {
					$tpl->set_var("lang_url", "ff.urlAddParam(window.location.href, 'lang', " . $code . ")");
				}
			
				$tpl->set_var("show_files", "?lang=" . $code);
				$tpl->parse("SectLang", true);
			}
		}
	}
}

function mod_security_cm_on_load_account($page, $tpl)
{
	if (!mod_security_check_session(false))
		return;

	$cm = cm::getInstance();
	$username = "";
	$framework_css = mod_restricted_get_framework_css(); 

	if (MOD_SEC_MULTIDOMAIN)
		$ID_domain = mod_security_get_domain();	
		
	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();	
	
	$tpl->set_var("toggle_properties", $framework_css["collapse"]["action"]);
	$tpl->set_var("img_class", cm_getClassByDef($framework_css["image"]));
	$tpl->set_var("panel_class", cm_getClassByDef($framework_css["dropdown"]["container"]));
	$tpl->set_var("panel_body_class", cm_getClassByDef($framework_css["dropdown"]["body"]["def"]));
	$tpl->set_var("panel_img_class", cm_getClassByDef($framework_css["dropdown"]["body"]["img"]));
	$tpl->set_var("panel_desc_class", cm_getClassByDef($framework_css["dropdown"]["body"]["desc"]));
	$tpl->set_var("panel_links_class", cm_getClassByDef($framework_css["dropdown"]["body"]["links"]));
	$tpl->set_var("panel_footer_class", cm_getClassByDef($framework_css["dropdown"]["footer"]));
	$tpl->set_var("profile_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["profile"]));
	$tpl->set_var("users_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["users"]));
	$tpl->set_var("logout_class", cm_getClassByDef($framework_css["dropdown"]["actions"]["logout"]));

	if(MOD_SEC_GROUPS) {
		$user_permission = get_session("user_permission");

		if(MOD_SEC_USER_AVATAR) 
			$avatar = $user_permission["avatar"];

		$username = $user_permission["name"] . " " . $user_permission["surname"] . "(" . $user_permission["username"] . ")";
		if($user_permission["primary_gid_name"])
			$arrInfo[] = ucfirst($user_permission["primary_gid_name"]);
		//if($user_permission["email"])
		//	$arrInfo[] = '<a href="mailto:' . $user_permission["email"] .'">' . $user_permission["email"] . '</a>';
		//if($user_permission["tel"])
		//	$arrInfo[] = '<a href="tel:' . $user_permission["tel"] .'">' . $user_permission["tel"] . '</a>';

		if($arrInfo) {
			$tpl->set_var("info", implode("<br />", $arrInfo) . "<br />");
		}
	} else {
		if(MOD_SEC_USER_AVATAR) 
			$avatar = mod_security_getUserInfo(MOD_SEC_USER_AVATAR, null, $db)->getValue();
	
		if ($cm->modules["security"]["fields"]["firstname"])
			$username .= mod_security_getUserInfo("firstname", null, $db)->getValue();
		if ($cm->modules["security"]["fields"]["lastname"])
		{
			if (strlen($username))
				$username .= " ";
			$username .= mod_security_getUserInfo("lastname", null, $db)->getValue();
		}

		if (!strlen($username))
		{
			if (ffIsset($cm->modules["security"]["fields"], "nickname"))
				$username = mod_security_getUserInfo("nickname", null, $db)->getValue();
			if (!strlen($username) && ffIsset($cm->modules["security"]["fields"], "nominativo"))
				$username = mod_security_getUserInfo("nominativo", null, $db)->getValue();
			if (!strlen($username) && ffIsset($cm->modules["security"]["fields"], "company_name"))
				$username = mod_security_getUserInfo("company_name", null, $db)->getValue();
			if (!strlen($username))
				$username = get_session("UserID");
		}	
	}
	
	if(MOD_SEC_USER_AVATAR) {
		$tpl->set_var("avatar", mod_sec_get_avatar($avatar, MOD_SEC_USER_AVATAR_MODE));
		$tpl->parse("SectUserAvatar", false);
		$tpl->parse("SectAvatar", false);
	} else {
		//$tpl->set_var("user_account", ffCommon_specialchars($username));
	}
	
	$tpl->set_var("account", ffCommon_specialchars($username));
	
	$mod_sec_profile = $cm->router->getRuleById("mod_sec_profile");	
	if($mod_sec_profile->reverse) {
		$tpl->set_var("user_profile", FF_SITE_PATH . $mod_sec_profile->reverse . "?ret_url=" . ($_REQUEST["ret_url"]
			? rawurldecode($_REQUEST["ret_url"])
			: ($cm->path_info != $mod_sec_profile->reverse
				? rawurldecode($_SERVER["REQUEST_URI"])
				: "/"
			)
		));
        if (!defined("USER_VIEW_PROFILE"))define("USER_VIEW_PROFILE", true);
        if (USER_VIEW_PROFILE == true) {
            $tpl->parse("SectProfile", false);
        }
        else {
            $tpl->parse("SectProfileNoEdit", false);
        }
	}
	
	$mod_sec_users = $cm->router->getRuleById("mod_sec_users");	
	if($user_permission["permissions"]["AREA_USERS_SHOW_MODIFY"] && $mod_sec_users->reverse) {
		$tpl->set_var("manage_users", FF_SITE_PATH . $mod_sec_users->reverse);
		$tpl->parse("SectUsers", false);
	}
	
}

/*
$user_state = ($logged ? "logout" : "login");
if ($cm->isXHR() && $frmAction)
{
	$cm->jsonAddResponse(array(
			"modules" => array(
				"security" => array(
					"action" => $user_state
				)
			)
		));

	if($sError)
		$cm->jsonAddResponse(array(
			"success" => false 
			, "modules" => array(
				"security" => array(
					"error" => _modsec_process("error", $sError)
				)
			)
		));
	else if($is_loggedin)
		$cm->jsonAddResponse(array(
			"modules" => array(
				"security" => array(
					"message" => _modsec_process("logout", true, false)
				)
			)
			, "doredirects" => true
			, "url" => $ret_url			
		));
	_modsec_login_redirect($ret_url);

} else {
	$cm->oPage->addContent(_modsec_process($user_state, $sError), null, $user_state);
}
 */
/*
cm::getInstance()->modules["security"]["events"]->addEvent("onOutput", function ($logged, $sErrorCode, $sError, $template_file) {
	if (strlen($sError))
	{
		cm::getInstance()->oPage->addContent(mod_sec_process_error($sError));
		return true;
	}
});*/
cm::getInstance()->modules["security"]["events"]->addEvent("on_retrieve_params", function ($sError, $frmAction, $logged, $req) {
	$cm = cm::getInstance();

	if(MOD_SEC_OAUTH2_SERVER && strpos($cm->oPage->ret_url, $cm->router->getRuleById("mod_sec_oauth2")->reverse) === 0 && strpos($cm->oPage->ret_url, "ret_url") === false)
		$cm->oPage->layer = "empty";
	
	switch($frmAction)
	{
		case "login":
            $options = mod_security_get_settings($cm->path_info);

			$ret =	mod_sec_check_login($req["username"], $req["password"], $req["domain"], $options, $req["permanent_session"], $logged, $sError, false);
			if($ret["error"])
			{
				if($cm->isXHR()) {
					$cm->jsonAddResponse(array(
						"success" => false
						 , "modules" => array(
							"security" => array(
								"action" => "login"
								, "error" => mod_sec_process_error($ret["error_code"])
							)
						)
						, "doredirects" => false
					));
					cm::jsonParse($cm->json_response);
					exit;
				} else {
					ffRedirect(FF_SITE_PATH . $cm->path_info . "?username=" . $req["username"] . "&password=" . "&error=" . $ret["error_code"]);
				}
			}
			elseif ($ret["logged"] === true)
			{
				$tpl = mod_sec_login_tpl_load(true);
				mod_sec_process_logout($tpl, true, true);

				$cm->jsonAddResponse(array(
					"modules" => array(
						"security" => array(
							"action" => "login"
							, "message" => $tpl->rpparse("main", false)
						)
					)
					, "doredirects" => true
					, "url" => $cm->oPage->ret_url
				));
			}

			return _modsec_login_redirect(null, "login");
		case "logout":
            $social_enabled = (MOD_SEC_SOCIAL_GOOGLE || MOD_SEC_SOCIAL_FACEBOOK || MOD_SEC_SOCIAL_JANRAIN);
			if(MOD_SEC_ENABLE_TOKEN && $social_enabled)
			{
				// DESTROY TOKENS
				$social_logout_check = $_REQUEST["social_logout_check"];
				if (is_array($social_logout_check) && count($social_logout_check))
				{
					foreach ($social_logout_check as $type => $check)
					{
						if ($check)
							mod_security_accesstoken_revoke($type, null, get_session("UserNID"), get_session("DomainID"), $cm->path_info);
					}
				}
			}
			// DISTRUGGE LA SESSIONE
			mod_security_destroy_session(false);

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
});

function mod_sec_login_getFrameworkCss($logged) {
	static $res = null;
	$cm = cm::getInstance();
	
	$type = ($logged ? "logout" : "login");
	if(!$res[$type]) {
		$framework_css = mod_sec_get_framework_css();
		
		$logo = ($logged || $_REQUEST["XHR_CTX_ID"] ? false : MOD_SEC_LOGO);

		if($cm->oPage->layer == "default" && !$cm->isXHR()) {
			$framework_css["component"]["class"] = trim(str_replace("nopadding", "", $framework_css["component"]["class"]));
			$framework_css["component"]["grid"] = "row";
		}

		if(!$logo) {  
			$framework_css["login"]["def"]["col"] = array( 
									                "xs" => 12
									                , "sm" => 12
									                , "md" => 12
									                , "lg" => 12
									            ) ;
			unset($framework_css["login"]["def"]["push"]);
			$framework_css["logout"]["def"]["col"] = array( 
														"xs" => 12
														, "sm" => 12
														, "md" => 12
														, "lg" => 12
													) ;
			unset($framework_css["logout"]["def"]["push"]);
			$framework_css["inner-wrap"]["col"] = array( 
													"xs" => 12
													, "sm" => 12
													, "md" => 6
													, "lg" => 6 
												);
			$framework_css["inner-wrap"]["push"] = array( 
													"xs" => 0
													, "sm" => 0
													, "md" => 3
													, "lg" => 3 
												);
		}

		/**
		* Container Class
		*/
		
		$component_class["base"] = $framework_css["component"]["class"];
		if($framework_css["component"]["grid"]) {
			if(is_array($framework_css["component"]["grid"]))
			    $component_class["grid"] = cm_getClassByFrameworkCss($framework_css["component"]["grid"], "col");
			else {
			    $component_class["grid"] = cm_getClassByFrameworkCss("", $framework_css["component"]["grid"]);      
			}
		} 
		
		$res[$type] = array(
			"framework_css" => $framework_css
			, "component_class" => $component_class
			, "logo" => $logo
		
		);
	}
	return $res[$type];
}
	
cm::getInstance()->modules["security"]["events"]->addEvent("onTplLoad", function ($tpl, $logged, $sErrorCode, $sError) {
	//function _modsec_process($action, $sError = null, $logo = MOD_SEC_LOGO) {
	$cm = cm::getInstance();

	$res				= mod_sec_login_getFrameworkCss($logged);
	$framework_css 		= $res["framework_css"];
	$component_class 	= $res["component_class"];
	$logo 				= $res["logo"];

	if($logged)
		$component_class = mod_sec_process_logout($tpl, $logged, $sError);
	else
		$component_class = mod_sec_process_login($tpl, $logged, $sError);

	if($logo) {
		if(MOD_SEC_LOGO_PATH === false) {
		    $tpl->set_var("SectLogoImg" . $logo, "");
		} else {
            $logo_url = mod_security_get_logo(MOD_SEC_LOGO_PATH);

		    $tpl->set_var("logo_login", $logo_url);
		    $tpl->parse("SectLogoImg" . $logo, false);
		}
		$tpl->set_var("logo_class", cm_getClassByDef($framework_css["logo"]));
		$tpl->parse("SectLogo" . $logo, false);
	}

	if($framework_css["inner_wrap"])
	{
		$tpl->set_var("inner_wrap_start", '<div class="'. cm_getClassByDef($framework_css["inner-wrap"]) . '">');
		$tpl->set_var("inner_wrap_end", '</div>');
	}
	
	return true;
});

function mod_sec_process_login(&$tpl, $logged, $sError = null) 
{
	$cm = cm::getInstance();
	$count_links = 0;
	
	$res				= mod_sec_login_getFrameworkCss($logged);
	$framework_css 		= $res["framework_css"];
	$component_class 	= $res["component_class"];
	$logo 				= $res["logo"];
		
	
		
	$tiny_lang_code = strtolower(substr(FF_LOCALE, 0, 2));
	$mod_sec_activation = ($cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code) 
	                        ? $cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)
	                        : $cm->router->getRuleById("mod_sec_activation")
	                    );
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
	$mod_sec_social_url = (string)$cm->router->getRuleById("mod_sec_social")->reverse;	                    

	if(MOD_SEC_LOGIN_TITLE)
		$tpl->parse("SectLoginTitle", false);

	$tpl->set_var("sError", mod_sec_process_error($sError, $framework_css));

	/**
	* Standard Login Parsing
	*/ 
	if(MOD_SEC_LOGIN_STANDARD) {
		/**
		* Login Actions
		*/
		$tpl->set_var("row_class", cm_getClassByDef($framework_css["login"]["standard"]["record"]));
		$tpl->set_var("field_class", cm_getClassByDef($framework_css["login"]["standard"]["field"]));
		
		if(MOD_SEC_ENABLE_TOKEN)
		{
		    $tpl->set_var("stayconnect_class", cm_getClassByDef($framework_css["login"]["standard"]["stayconnect"]));
		    $tpl->parse("SectStayConnected", false);
		}

		if(MOD_SEC_USERNAME_RECOVER_USERNAME && $mod_sec_recover_username)
		{     
		    $tpl->set_var("recover_class", cm_getClassByDef($framework_css["login"]["standard"]["recover"]));
		    $tpl->set_var("recover", (string)$mod_sec_recover_username->reverse);
		    $tpl->parse("SectRecoverUsername", false);
		} 

		if(MOD_SEC_PASSWORD_RECOVER && $mod_sec_recover)
		{     
			$tpl->set_var("recover_class", cm_getClassByDef($framework_css["login"]["standard"]["recover"]));
		    $tpl->set_var("recover", (string)$mod_sec_recover->reverse);
			$tpl->parse("SectRecoverPassword", false);
		} 

		if(MOD_SEC_LOGIN_REGISTER_URL && $mod_sec_register)
		{
			$count_links++;
		    if(is_string(MOD_SEC_LOGIN_REGISTER_URL))
		        $register_link = MOD_SEC_LOGIN_REGISTER_URL;
		    elseif($mod_sec_register)
		        $register_link = (string)$mod_sec_register->reverse;

		    $tpl->set_var("register_class", cm_getClassByDef($framework_css["links"]["register"]));
		    $tpl->set_var("register", $register_link);
		    $tpl->parse("SectRegister", false);
		} 	

		/**
		* Login Label
		*/
		if(MOD_SEC_LOGIN_LABEL) {
		    $tpl->parse("SectDomainLabel", false);
		    $tpl->parse("SectUsernameLabel", false);
		    $tpl->parse("SectPasswordLabel", false);
		}

		/**
		* Login Field
		*/
		if (MOD_SEC_MULTIDOMAIN && MOD_SEC_LOGIN_DOMAIN)
		{
			$domain = $_POST["domain"];
			if ($_REQUEST["frmAction"] == "" && $domain == "")
				$domain = $_COOKIE["domain"];
			$tpl->set_var("domain", $domain);
			$tpl->parse("SectDomain", false);
			$tpl->set_var("focus_target", "domain");
		}
		else
		{
			$domain = null;
			$tpl->set_var("SectDomain", "");
			$tpl->set_var("focus_target", "username");
		}
	
		$tpl->set_var("username", ffCommon_specialchars($_REQUEST["username"]));
		
		$tpl->set_var("url", $cm->oPage->site_path . $cm->oPage->page_path);
		
		$tpl->set_var("login_button_class", cm_getClassByDef($framework_css["actions"]["login"]));	
		$tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
		$tpl->set_var("login_standard_class", cm_getClassByDef($framework_css["login"]["standard"]["def"]));
		$tpl->parse("SectStandardLogin", false);
	}

	/**
	* Social Login Parsing
	*/ 
	if (MOD_SEC_SOCIAL_GOOGLE)
	{
	    $tpl->set_var("social_class", cm_getClassByDef($framework_css["login"]["social"]["google"]));
	    $tpl->set_var("social_icon", cm_getClassByFrameworkCss("google", "icon-tag"));
	    $tpl->set_var("social_url_google", FF_SITE_PATH . $mod_sec_social_url . "/google");
	    $tpl->parse("SectSocialLoginGoogle" . ucfirst(MOD_SEC_SOCIAL_POS), false);
	    $tpl->parse("SectSocialLogoutGoogle", false);
	}

	if (MOD_SEC_SOCIAL_FACEBOOK)
	{
	    $tpl->set_var("social_class", cm_getClassByDef($framework_css["login"]["social"]["facebook"]));
	    $tpl->set_var("social_icon", cm_getClassByFrameworkCss("facebook", "icon-tag"));
	    $tpl->set_var("social_url_facebook", FF_SITE_PATH . $mod_sec_social_url . "/facebook");
	    $tpl->parse("SectSocialLoginFacebook" . ucfirst(MOD_SEC_SOCIAL_POS), false);
	    $tpl->parse("SectSocialLogoutFacebook", false);
	}

	if(MOD_SEC_SOCIAL_JANRAIN)
	{
	    $tpl->set_var("social_class", cm_getClassByDef($framework_css["login"]["social"]["janrain"]));
	    $tpl->set_var("janrain_appname", ffCommon_url_rewrite(MOD_SEC_SOCIAL_JANRAIN_APPNAME));
	   
	    $tpl->parse("SectJanRainLogin", false);
	    $tpl->parse("SectJanrainJS", false);
	} 

	if(MOD_SEC_SOCIAL_GOOGLE || MOD_SEC_SOCIAL_FACEBOOK || MOD_SEC_SOCIAL_JANRAIN) { 
		$component_class["social"] = "social";

		if(MOD_SEC_LOGIN_STANDARD)
			$framework_css["login"]["social"]["def"]["class"] .= " " . MOD_SEC_SOCIAL_POS . "-standard-login"; 
			
	    $tpl->set_var("login_social_class", cm_getClassByDef($framework_css["login"]["social"]["def"]));
		$tpl->parse("SectSocialLogin" . ucfirst(MOD_SEC_SOCIAL_POS), false);
	}
	
	if (MOD_SEC_LOGIN_BACK_URL)
	{
	    $count_links++;
	    $tpl->set_var("back_class", cm_getClassByDef($framework_css["links"]["back"]));
	    $tpl->set_var("back_url", FF_SITE_PATH . "/"); 
	    $tpl->parse("SectLoginBack", false);
	}    

	if($count_links) {
		$tpl->set_var("link_class", cm_getClassByDef($framework_css["links"]["def"]));
		$tpl->parse("SectLoginLinks", false);
	}		
	
	$tpl->set_var("login_class", cm_getClassByDef($framework_css["login"]["def"]));  
	$tpl->parse("SectLogin", false);

	
	$tpl->set_var("container_class", implode(" ", array_filter($component_class))); 

	//return $component_class;
}


function mod_sec_process_logout(&$tpl, $logged, $skip_action = false) 
{
	$cm = cm::getInstance();
	$count_links = 0;	

	$res				= mod_sec_login_getFrameworkCss($logged);
	$framework_css 		= $res["framework_css"];
	$component_class 	= $res["component_class"];
	$logo 				= $res["logo"];	

	if (MOD_SEC_MULTIDOMAIN)
		$ID_domain = mod_security_get_domain();	

	/**
	* Logout Title
	*/
	if(MOD_SEC_LOGOUT_TITLE) {
		if($skip_action)
			$tpl->set_var("logout_title", ffTemplate::_get_word_by_code("logout_noaction_title"));
		else
			$tpl->set_var("logout_title", ffTemplate::_get_word_by_code("logout_title"));
	    $tpl->parse("SectLogoutTitle", false);
	}

	if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && $ID_domain)
		$db = mod_security_get_db_by_domain($ID_domain);
	else
		$db = mod_security_get_main_db();

	    
	if(MOD_SEC_ENABLE_TOKEN && !$skip_action && (MOD_SEC_SOCIAL_GOOGLE || MOD_SEC_SOCIAL_FACEBOOK || MOD_SEC_SOCIAL_JANRAIN))
	{
		$options = mod_security_get_settings($cm->path_info);
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
	
	$tpl->set_var("account_class", cm_getClassByDef($framework_css["logout"]["account"]["def"]));
	if(!$skip_action)
	{
		$tpl->set_var("logout_button_class", cm_getClassByDef($framework_css["actions"]["logout"]));	
		$tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));	
		$tpl->parse("SectStandardLogout", false); 
	}
	
	if (MOD_SEC_LOGIN_BACK_URL)
	{
	    $count_links++;
	    $tpl->set_var("back_class", cm_getClassByDef($framework_css["links"]["back"]));
	    $tpl->set_var("back_url", $cm->oPage->ret_url); 
	    $tpl->parse("SectLogoutBack", false);
	}    

	if($count_links) {
		$tpl->set_var("link_class", cm_getClassByDef($framework_css["links"]["def"]));
		$tpl->parse("SectLogoutLinks", false);
	}

	$tpl->set_var("logout_class", cm_getClassByDef($framework_css["logout"]["def"]));
	$tpl->parse("SectLogout", false); 	
	
	$tpl->set_var("container_class", implode(" ", array_filter($component_class))); 

	//return $component_class;
}

function mod_sec_process_error($sError, $framework_css = null) {
	if($sError) {
		if(!$framework_css)
			$framework_css = mod_sec_get_framework_css();
	
		$strError = '<div class="' . cm_getClassByDef($framework_css["error"]) . '">' . $sError . '</div>';
	}
	return $strError;
}
