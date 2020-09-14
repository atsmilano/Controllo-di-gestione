<?php
function mod_security_cm_on_load_lang($page, $tpl)
{
	if (!mod_security_check_session(false))
		return;

	$cm = cm::getInstance();

	$flag_dim = "16";
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
		foreach($locale["lang"] AS $code => $params) {
			if($code == "current")
				continue;
		
            $tpl->set_var("code", $code);
            $tpl->set_var("description", $params["description"]);
			$tpl->set_var("flag_lang", "flag " . $params["tiny_code"]);

			if($code == $locale["lang"]["current"]["code"]) {
				$tpl->set_var("flag_lang_active", "flag " . $params["tiny_code"]);
				$tpl->parse("SectCurrentLang", false);
			} else {
				$tpl->set_var("show_files", "?lang=" . $code);
				$tpl->parse("SectLang", true);
			}
		}
	}
}

function mod_security_cm_on_load_account($page, $tpl)
{
	if (mod_security_check_session(false))
	{
		if (!MOD_SEC_MULTIDOMAIN_EXTERNAL_DB || mod_security_is_admin())
			$db = mod_security_get_main_db();
		else
			$db = mod_security_get_db_by_domain(null);
		
		$username = "";

		$cm = cm::getInstance();

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
			if ($cm->modules["security"]["fields"]["nickname"])
				$username = mod_security_getUserInfo("nickname", null, $db)->getValue();
			else if (!strlen($username) && $cm->modules["security"]["fields"]["nominativo"])
				$username = mod_security_getUserInfo("nominativo", null, $db)->getValue();
			else if (!strlen($username) && $cm->modules["security"]["fields"]["company_name"])
				$username = mod_security_getUserInfo("company_name", null, $db)->getValue();
			else if (!strlen($username))
				$username = get_session("UserID");
		}
        if(MOD_SEC_GROUPS) {
            $user_permission = get_session("user_permission");
            
            if(is_array($user_permission) && array_key_exists("avatar", $user_permission) && strlen($user_permission["avatar"])) {
                $tpl->set_var("avatar", mod_sec_get_avatar($user_permission["avatar"], MOD_SEC_USER_AVATAR_MODE));
                $tpl->parse("SectUserAvatar", false);
            } 
        }

		$tpl->set_var("nomeutente", $username);  
	}
}