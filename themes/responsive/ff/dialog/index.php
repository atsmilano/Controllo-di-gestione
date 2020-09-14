<?php
/**
 * @ignore
 * @package theme_responsive
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */
 
if (!class_exists("cm"))
	ffErrorHandler::raise ("responsive theme will not work without CM", E_USER_ERROR, null, get_defined_vars ());

$cm = cm::getInstance();

// get globals & parameters
$title			= $_REQUEST["title"];
if(substr($title, 0, 1) == "_") {
    $title = ffTemplate::_get_word_by_code(substr($title, 1)); 
}
$cm->oPage->title = $title;

$message		= $_REQUEST["message"];
if(strpos($message, "[") !== false) {
	$res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $message, $message_tags);
	if(is_array($message_tags) && count($message_tags)) {
		foreach($message_tags[0] AS $message_tags_key => $message_tags_value) {
			if(substr($message_tags[1][$message_tags_key], 0, 1) == "_") {
				$message = str_replace($message_tags_value, ffTemplate::_get_word_by_code(substr($message_tags[1][$message_tags_key], 1)), $message);
			}
		}		
	}
} elseif(substr($message, 0, 1) == "_") {
    $message = ffTemplate::_get_word_by_code(substr($message, 1));
}

$confirmurl		= $_REQUEST["confirmurl"];
$cancelurl		= $_REQUEST["cancelurl"];
$ret_url        = $_REQUEST["ret_url"];
if(!$cancelurl) {
    $confirmurl .= "&ret_url=" . rawurlencode($ret_url);
    $cancelurl = (isset($_REQUEST["XHR_CTX_ID"])
                    ? "[CLOSEDIALOG]"
                    : $ret_url
                );
}

$type			= (isset($_REQUEST["type"])
					? $_REQUEST["type"]
					: (strlen($confirmurl)
						? (strlen($cancelurl)
							? "yesno" 
							: "okonly"
						)
						: ""
					)
				);
$dlg_site_path	= $_REQUEST["dlg_site_path"];

// get action
$choiche = $_REQUEST["choiche"];

if (function_exists("cm_moduleCascadeFindTemplate"))
{
	$filename = null;
	
	if (isset($_REQUEST["XHR_CTX_ID"]))
	{
        $filename = cm_cascadeFindTemplate("/ff/dialog/form_dialog.html");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/form_dialog.html", $cm->oPage->getTheme(), false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/ff/dialog/form_dialog.html", $cm->oPage->getTheme());*/
	} else {
        $filename = cm_cascadeFindTemplate("/ff/dialog/form.html");
        /*if ($filename === null)
            $filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/form.html", $cm->oPage->getTheme(), false);
        if ($filename === null)
            $filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/ff/dialog/form.html", $cm->oPage->getTheme());*/
    }
	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file(basename($filename), "main");
	
	if (isset($_REQUEST["XHR_CTX_ID"]))
	{
		if ($confirmurl == "[CLOSEDIALOG]")
			$confirmurl = "ff.ffPage.dialog.close('" . $_REQUEST["XHR_CTX_ID"] . "')";
		else if ($confirmurl == "[CLOSE_CTX]")
			$confirmurl = "ff.ajax.ctxClose('" . $_REQUEST["XHR_CTX_ID"] . "')";
		else if (strpos(rawurldecode($confirmurl), "javascript:") === 0)
			$confirmurl = rawurldecode($confirmurl);
		else
			$confirmurl = "ff.ajax.ctxGoToUrl('" . $_REQUEST["XHR_CTX_ID"] . "', '" . $confirmurl . "')";
		
		if ($cancelurl == "[CLOSEDIALOG]")
			$cancelurl = "ff.ffPage.dialog.close('" . $_REQUEST["XHR_CTX_ID"] . "')";
		else if ($cancelurl == "[CLOSE_CTX]")
			$cancelurl = "ff.ajax.ctxClose('" . $_REQUEST["XHR_CTX_ID"] . "')";
		else
			$cancelurl	= "ff.ajax.ctxGoToUrl('" . $_REQUEST["XHR_CTX_ID"] . "', '" . $cancelurl . "')";
	}
}
else
{
	$tpl = ffTemplate::factory(__DIR__);
	$tpl->load_file("form.html", "main");
}
 
$tpl->set_var("confirm_class", cm_getClassByFrameworkCss("ActionButtonDelete", "button", "activebuttons"));
$tpl->set_var("cancel_class", cm_getClassByFrameworkCss("ActionButtonCancel", "button"));

$tpl->set_var("center_class", strtolower($type)); // cm_getClassByFrameworkCss("", "row"));
$tpl->set_var("message_class", cm_getClassByFrameworkCss("warning", "icon", "5x"));
$tpl->set_var("message_icon", cm_getClassByFrameworkCss("warning", "icon-tag", "5x"));
$tpl->set_var("site_path", FF_SITE_PATH);
$tpl->set_var("theme", $cm->oPage->getTheme()); // TOCHECK

$tpl->set_var("message", $message);
$tpl->set_var("confirmurl", $confirmurl);
$tpl->set_var("cancelurl", $cancelurl);
   
switch (strtolower($type))
{
	case "yesno":
		$tpl->set_var("OKOnly", "");
        $tpl->set_var("CancelOnly", "");
		$tpl->parse("YesNo", false);
		if ($choiche) 
		{
			if ($choiche == "yes")
				ffRedirect($confirmurl);
			else
				ffRedirect($cancelurl);
		}
		if(!$cm->isXHR())
        	http_response_code(300);
		break;
	case "okonly":
		$tpl->set_var("YesNo", "");
        $tpl->set_var("CancelOnly", "");
		$tpl->parse("OKOnly", false);
		if (strlen($choiche))
		{
			ffRedirect($confirmurl);
		}
        if(!$cm->isXHR())
        	http_response_code(401);
		break;
    case "cancelonly":   
        $tpl->set_var("YesNo", "");
        $tpl->parse("CancelOnly", false);
        $tpl->set_var("OKOnly", "");
        if (strlen($choiche))
        {
            ffRedirect($cancelurl);
        } 
        if(!$cm->isXHR())
        	http_response_code(302);
        break;
	default:
		$tpl->set_var("YesNo", "");
        $tpl->set_var("CancelOnly", "");
		$tpl->set_var("OKOnly", "");
		if(!$cm->isXHR())
        	http_response_code(404);
}

$ff = ffGlobals::getInstance("ff");

$res = $ff->events->doEvent("dialog_onProcess", array($tpl));
$rc = end($res);
if ($rc === null)
	$cm->oPage->addContent($tpl);
