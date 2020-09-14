<?php
if ($cm->modules["security"]["overrides"]["recover_success"]["tpl_file"])
	$template_file = $cm->modules["security"]["overrides"]["recover_success"]["tpl_file"];
else
	$template_file = "success.html";

$filename = cm_cascadeFindTemplate("/contents/recover/" . $template_file, "security");
/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . rtrim($cm->path_info, "/") . "/" . $template_file, $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/security/contents/recover/" . $template_file, $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents/recover/" . $template_file, $cm->oPage->theme);
*/
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file(basename($filename), "main");
$tpl->set_var("site_path", FF_SITE_PATH);
$tpl->set_var("theme", $cm->oPage->theme);

$cm->preloadApplets($tpl);
$cm->parseApplets($tpl);

$ret_url 	= $_REQUEST["ret_url"];
if (!strlen($ret_url))
	$ret_url = $cm->oPage->site_path . "/";

if(strlen($_REQUEST["ret_url"]))
	$login_url = $_REQUEST["ret_url"];
else
	$login_url = mod_security_get_login_path();

$cm->oPage->ret_url = $ret_url;
$tpl->set_var("ret_url", $ret_url);
$tpl->set_var("login_url", $login_url);

$tpl->set_var("encoded_ret_url", rawurlencode($ret_url));
$tpl->set_var("encoded_this_url", rawurlencode($_SERVER["REQUEST_URI"]));
$tpl->set_var("query_string", $_SERVER["QUERY_STRING"]);

$cm->oPage->addContent($tpl, null, "recover_success");
