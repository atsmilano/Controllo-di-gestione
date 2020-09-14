<?php
if(mod_security_check_session(false))	{
	$options = mod_security_get_settings($cm->path_info);

	$filename = cm_cascadeFindTemplate("/applets/welcome/smallbox.html", "security");
	/*
	$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/applets/welcome/smallbox.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/security/themes", "/applets/welcome/smallbox.html", $cm->oPage->theme);
*/
	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("smallbox.html", "main");
	$tpl->set_var("site_path", $cm->oPage->site_path);
	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("ret_url", rawurlencode($_SERVER["REQUEST_URI"]));
	$tpl->set_var("CM_LOCAL_APP_NAME", ffCommon_specialchars(cm_getAppName()));

	$db = ffDB_Sql::factory();
	$tpl->set_var("firstname", ffCommon_specialchars(mod_security_getUserInfo("firstname")->getValue()));
	$tpl->set_var("lastname", ffCommon_specialchars(mod_security_getUserInfo("lastname")->getValue()));
	if(defined("MOD_SEC_USER_AVATAR") && MOD_SEC_USER_AVATAR) {
		$avatar = mod_security_getUserInfo(MOD_SEC_USER_AVATAR)->getValue();

		if(strlen($avatar) && is_file(FF_DISK_PATH . FF_UPDIR . $avatar)) {
			$tpl->set_var("avatar", CM_SHOWFILES . $avatar);
			$tpl->parse("SectAvatar", false);
		}
	}
	
	$out_buffer = $tpl->rpparse("main", false);
}