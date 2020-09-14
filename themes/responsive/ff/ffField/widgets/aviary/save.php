<?php
/*
Aviary
*/
$host_name = $_SERVER["HTTP_HOST"];
if (strpos(php_uname(), "Windows") !== false)
    $tmp_file = str_replace("\\", "/", __FILE__);
else
    $tmp_file = __FILE__;

if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false)
{
	$document_root =  $_SERVER["DOCUMENT_ROOT"];
	if (substr($document_root,-1) == "/")
		$document_root = substr($document_root,0,-1);

	$site_path = str_replace($document_root, "", str_replace("/themes/responsive/ff/ffField/widgets/aviary/save.php", "", $tmp_file)); 
	$disk_path = $document_root . $site_path;
}
elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false)
{
	$document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
	if (substr($document_root,-1) == "/")
		$document_root = substr($document_root,0,-1);

	$site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/themes/responsive/ff/ffField/widgets/aviary/save.php", "", $_SERVER["SCRIPT_FILENAME"]));
	$disk_path = $document_root . str_replace($document_root, "", str_replace("/themes/responsive/ff/ffField/widgets/aviary/save.php", "", $tmp_file));
} else {
	$st_disk_path = str_replace("/themes/responsive/ff/ffField/widgets/aviary/save.php", "", $tmp_file);
	$st_site_path = str_replace("/themes/responsive/ff/ffField/widgets/aviary/save.php", "", $_SERVER["SCRIPT_NAME"]);
}

define("DISABLE_CACHE", true);

define("SHOWFILES_IS_RUNNING", true);
//define("FF_SKIP_COMPONENTS", true);
define("CM_DONT_RUN", true);
define("FF_ERROR_HANDLER_HIDE", true);

require_once($disk_path . "/cm/main.php");

$post_data = $_REQUEST["postdata"];
if(strlen($post_data)) {
	$arrPostData = explode("&", $post_data);
	if(is_array($arrPostData) && count($arrPostData)) {
		foreach($arrPostData AS $arrPostData_value) {
			$tmpPostData = explode("=", $arrPostData_value);
			
			$_POST[$tmpPostData[0]] = $tmpPostData[1];
		}
	}
}


$valid_session = false;
if (isset($_POST[session_name()]))
{
	$valid_session = true;
	session_id($_POST[session_name()]);
}
elseif (isset($_GET[session_name()]))
{
	$valid_session = true;
	session_id($_GET[session_name()]);
}
elseif (isset($_COOKIE[session_name()]))
{
	session_id($_COOKIE[session_name()]);
}

@session_start();


$data_src = $_POST["ref"];
$file = $_POST["img"];
if($valid_session && $data_src)
{
	$ff = get_session("ff");
	if(
		is_array($ff)
		&& array_key_exists("aviary", $ff)
		&& is_array($ff["aviary"])
		&& array_key_exists($data_src, $ff["aviary"])
	)
	{
		$base_path = $ff["aviary"][$data_src]["base_path"];
		$folder = $ff["aviary"][$data_src]["folder"];				


		$targetPath = $folder . '/';
		$fullpath = strtolower(str_replace('//','/',$targetPath . basename($file)));
	}
	else
	{
		$fullpath = "";
		$base_path = FF_DISK_UPDIR;
	}
}
else
{
	$fullpath = str_replace(((strpos(CM_SHOWFILES, "://") !== false) ? "" : FF_SITE_PATH) . CM_SHOWFILES, FF_SITE_UPDIR, $file);
	$base_path = FF_DISK_UPDIR;
}

if(strpos($fullpath, FF_SITE_UPDIR) === 0)
	$base_path = FF_DISK_UPDIR;
elseif(strpos($fullpath, FF_THEME_DIR) === 0)
	$base_path = FF_DISK_PATH . FF_THEME_DIR;

if(!strlen($fullpath))
	$base_path = FF_DISK_UPDIR;

$res = array();

if(strlen($fullpath) && is_file($base_path . $fullpath) && strlen($_REQUEST['url'])) { 
	$image_data = file_get_contents($_REQUEST['url']);

    if(file_put_contents($base_path . $fullpath ,$image_data))
        ffMedia::optimize($base_path . $fullpath);

	$res["status"] = true;
} else {
	$res["error"] = ffTemplate::_get_word_by_code("aviary_invalid_data");
	$res["status"] = false;
}


//ffErrorHandler::raise($base_path . $fullpath, E_USER_ERROR, null, get_defined_vars());

/*
if(strpos($_REQUEST['folder'], "/uploads") === 0)
    $base_path = "";

if(strpos($_REQUEST['folder'], FF_THEME_DIR) === 0)
    $base_path = "";
*/

echo ffCommon_jsonenc($res, true);
exit;
