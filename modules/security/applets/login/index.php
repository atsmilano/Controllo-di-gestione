<?php
$options = mod_security_get_settings($cm->path_info);

$filename = cm_cascadeFindTemplate("/applets/login/smallbox.html", "security");
/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/applets/login/smallbox.html", $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/security/themes", "/applets/login/smallbox.html", $cm->oPage->theme);
*/
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("smallbox.html", "main");
$tpl->set_var("site_path", $cm->oPage->site_path);
$tpl->set_var("theme", $cm->oPage->theme);
$tpl->set_var("ret_url", rawurlencode($_SERVER["REQUEST_URI"]));
$tpl->set_var("CM_LOCAL_APP_NAME", ffCommon_specialchars(cm_getAppName()));

$logged = mod_security_check_session(false);
if ($logged)
{
	if (get_session("UserID") == MOD_SEC_GUEST_USER_NAME)
		$logged = false;
}

if (!$logged)
{
	$tpl->parse("Login", false);
	$tpl->set_var("Logout", "");
}
else
{
	$db = ffDB_Sql::factory();
	$tpl->set_var("firstname", ffCommon_specialchars(mod_security_getUserInfo("firstname")->getValue()));
	$tpl->set_var("lastname", ffCommon_specialchars(mod_security_getUserInfo("lastname")->getValue()));

	$tpl->parse("Logout", FALSE);
	$tpl->set_var("Login", "");
}


$out_buffer = $tpl->rpparse("main", false);
