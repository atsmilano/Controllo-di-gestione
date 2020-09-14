<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage showfiles
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

define("SHOWFILES_IS_RUNNING", true);
define("FF_SKIP_COMPONENTS", true);
//define("FF_ONLY_INIT", true);
define("CM_ONLY_INIT", true);
//define("CM_DONT_RUN", true);
//define("FF_DB_MYSQLI_AVOID_REAL_CONNECT", true);

require(__DIR__ . "/main.php");

$path_info = $_SERVER["PATH_INFO"];
//if(strpos($path_info, $_SERVER["SCRIPT_NAME"]) === 0) // ??? DA VERIFICARE,DA CORREGGERE ALTROVE
//    $path_info = substr($path_info, strlen($_SERVER["SCRIPT_NAME"]));
    
// WMK
$wmk = array();
if(CM_SHOWFILES_FORCE_PATH && strpos($path_info, "/wmk") !== false)
{
	$arrWmk = explode("/wmk", substr($path_info, strpos($path_info, "/wmk") + strlen("/wmk")));

	if(is_array($arrWmk) && count($arrWmk))
	{
		foreach($arrWmk AS $arrWmk_file)
		{
			if(strlen($arrWmk_file) && is_file(ff_getAbsDir(FF_UPDIR) . FF_UPDIR . $arrWmk_file))
			{
				$wmk[]["file"] = ff_getAbsDir(FF_UPDIR) . FF_UPDIR . $arrWmk_file;
			}
		}
	}
	
	$path_info = substr($path_info, 0, strpos($path_info, "/wmk"));
}

$path_parts = explode("/", trim($path_info, "/"));
$expires = null;
$cached = false;

// CSS / JS STATIC CACHE
$tmp_filename = end($path_parts);
$tmp_mime = ffMimeTypeByFilename($tmp_filename);
if ($tmp_mime == "application/x-javascript")
{
	$cache_dir = CM_JSCACHE_DIR;
	if (CM_JSCACHE_BYDOMAIN)
	{
		$cache_domain_prefix = $_SERVER["HTTP_HOST"];
		if (CM_PAGECACHE_BYDOMAIN_STRIPWWW && strpos($cache_domain_prefix, "www.") === 0)
			$cache_domain_prefix = substr($cache_domain_prefix, 4);
		$cache_dir .= "/" . $cache_domain_prefix;
	}
	
	if (ffHTTP_encoding_isset("gzip") && file_exists($cache_dir . $path_info . ".gz"))
	{
		$cached = true;
		$filepath = $cache_dir . $path_info . ".gz";
		$filename = end($path_parts);
	}
	else if (file_exists($cache_dir . $path_info))
	{
		$cached = true;
		$filepath = $cache_dir . $path_info;
		$filename = end($path_parts);
	} 
	else if (CM_JSCACHE_GROUPHASH)
	{
		$hash_parts = explode("_", trim($path_info, "/"));
		$cache_dir .= "/" . implode("/", str_split($hash_parts[0], CM_JSCACHE_HASHSPLIT));

		if (ffHTTP_encoding_isset("gzip") && file_exists($cache_dir . $path_info . ".gz"))
		{
			$cached = true;
			$filepath = $cache_dir . $path_info . ".gz";
			$filename = end($path_parts);
		}
		else if (file_exists($cache_dir . $path_info))
		{
			$cached = true;
			$filepath = $cache_dir . $path_info;
			$filename = end($path_parts);
		}
	}
	
	if (strlen($filepath))
		$expires = 365;
}
else if ($tmp_mime == "text/css")
{
	$cache_dir = CM_CSSCACHE_DIR;
	if (CM_CSSCACHE_BYDOMAIN)
	{
		$cache_domain_prefix = $_SERVER["HTTP_HOST"];
		if (CM_PAGECACHE_BYDOMAIN_STRIPWWW && strpos($cache_domain_prefix, "www.") === 0)
			$cache_domain_prefix = substr($cache_domain_prefix, 4);
		$cache_dir .= "/" . $cache_domain_prefix;
	}

	if (CM_CSSCACHE_GROUPHASH)
	{
		$hash_parts = explode("_", trim($path_info, "/"));
		$cache_dir .= "/" . implode("/", str_split($hash_parts[0], CM_CSSCACHE_HASHSPLIT));
	}

	if (ffHTTP_encoding_isset("gzip") && file_exists($cache_dir . $path_info . ".gz"))
	{
		$cached = true;
		$filepath = $cache_dir . $path_info . ".gz";
		$filename = $tmp_filename;
	}
	else if (file_exists($cache_dir . $path_info))
	{
		$cached = true;
		$filepath = $cache_dir . $path_info;
		$filename = $tmp_filename;
	} 
	else if (CM_CSSCACHE_GROUPHASH)
	{
		$hash_parts = explode("_", trim($path_info, "/"));
		$cache_dir .= "/" . implode("/", str_split($hash_parts[0], CM_CSSCACHE_HASHSPLIT));

		if (ffHTTP_encoding_isset("gzip") && file_exists($cache_dir . $path_info . ".gz"))
		{
			$cached = true;
			$filepath = $cache_dir . $path_info . ".gz";
			$filename = $tmp_filename;
		}
		else if (file_exists($cache_dir . $path_info))
		{
			$cached = true;
			$filepath = $cache_dir . $path_info;
			$filename = $tmp_filename;
		}
	} 
	
	if (strlen($filepath))
		$expires = 365;
}

/*// MODULES PROPAGATIONS
if (strpos($path_info, CM_MODULES_PATH) === 0)
{
	$parts = explode("/", trim(str_replace(CM_MODULES_PATH, "", $path_info), "/"));
	if (count($parts) >= 2)
	{
		$filepath = CM_MODULES_ROOT . "/". $parts[0] . "/themes";
		$parts = array_slice($parts, 1);
		$filepath .= "/" . implode("/", $parts);
		$filename = end($parts);
		
		$subparts = explode(".", $filename);
		$ext = end($subparts);
		if ($ext == "css" || $ext == "js")
			if (ffHTTP_encoding_isset("gzip") && is_file($filepath . ".gz"))
				$filepath .= ".gz";
	}
}*/

$base_path = ff_getAbsDir(FF_UPDIR) . FF_UPDIR;

if(CM_SHOWFILES_FORCE_PATH && strlen($path_info) && !strlen($filepath))
{
    $mode = null;

	$cache_media["path"] 		= (CM_SHOWFILES_THUMB_IN_CACHE
									? "/cache/" . CM_SHOWFILES_THUMB_PATH
									: FF_UPDIR . "/" . CM_SHOWFILES_THUMB_PATH
								);
	$cache_media["theme_dir"] 	= FF_THEME_DIR;
	$cache_media["theme"] 		= FRONTEND_THEME;
	$cache_media["uploads"] 	= FF_UPDIR;

	$image = pathinfo($path_info); 
	if(strpos($path_info, "/" . $cache_media["theme"]) === 0) 
	{
        /*
        * quando il path inizia con il nome del tema frontend se la trova nel fs
        * Abilita una compia simbolica dell'immagine
        * 
        * Non supporta il sistema di ridimensionamento dinamico
        */			
		$filepath = ff_getAbsDir($cache_media["theme_dir"] . $path_info) . $cache_media["theme_dir"] . $path_info;
		$is_symlink = true;
	}
	elseif(is_file(ff_getAbsDir($cache_media["uploads"] . $path_info) . $cache_media["uploads"] . $path_info))
	{
        /*
         * l'immagine renderizzata e quella originale
         * Abilita una compia simbolica dell'immagine
         */ 		
		$filepath = ff_getAbsDir($cache_media["uploads"] . $path_info) . $cache_media["uploads"] . $path_info;
		$is_symlink = true;
	} 
	else
	{
		$res = cm_resolve_source_path($image, $cache_media["uploads"]);
		if(!$res["filepath"]) {
			$res = cm_resolve_source_path($image, $cache_media["theme_dir"] . "/" . $cache_media["theme"] . "/images");
		}

		$filepath 					= $res["filepath"];
		$mode 						= $res["mode"];
		$source 					= $res["source"];
	}
	
	/*
    * Se non riesce a recuperare il file di origine tenta di recuperarlo da /themes/[theme]/images
    * Abilita una compia simbolica dell'immagine
    * 
    * Non supporta il sistema di ridimensionamento dinamico
    */			
	if(!$filepath && is_file(ff_getAbsDir($cache_media["theme_dir"] . "/" . $cache_media["theme"] . "/images" . $path_info) . $cache_media["theme_dir"] . "/" . $cache_media["theme"] . "/images" . $path_info))
	{
		$filepath = ff_getAbsDir($cache_media["theme_dir"] . "/" . $cache_media["theme"] . "/images" . $path_info) . $cache_media["theme_dir"] . "/" . $cache_media["theme"] . "/images" . $path_info;
		$is_symlink = true;	
	}

	
	/*
    * fa una compia simbolica dell'immagine
    * processa l'immagine se richiesto
    */			
	if($is_symlink && !is_file(ff_getAbsDir($cache_media["path"] . $path_info) . $cache_media["path"] . $path_info))
	{
		if(!is_dir(ff_getAbsDir($cache_media["path"] . $image["dirname"]) . $cache_media["path"] . $image["dirname"]))
			mkdir(ff_getAbsDir($cache_media["path"] . $image["dirname"]) . $cache_media["path"] . $image["dirname"], 0777, true);

		symlink($filepath, ff_getAbsDir($cache_media["path"] . $path_info) . $cache_media["path"] . $path_info);
		
		/*if($output) {
			header("Content-type: image/" . $image["extension"]);
			readfile($filepath);
			exit;
		}*/				
	}

	if($filepath)
	{
		$final_file = cm_showfilesGetThumbPath($path_info, $base_path);
		
		$final_ext = ffGetFilename($final_file, false);
		$filepath_ext = ffGetFilename($filepath, false);
		if($final_ext != $filepath_ext)
			$mode_format = $filepath_ext;

		if(!isset($_REQUEST["__nocache__"]) 
			&& (is_file($final_file) && filectime($final_file) > filectime($filepath))
	    ) {
	    	$is_valid_thumb = true; 
	    }
    }  
    else 
    {	
		/**
	    * Remove Fake Name resolved by strpos(dirname, basename) === 0
	    */
	    if(strpos($path_parts[count($path_parts) - 1], $path_parts[count($path_parts) - 2] . "-") === 0) {
    		$fake_filename = $path_parts[count($path_parts) - 1];
	        $fake_dirname = $path_parts[count($path_parts) - 2];
    		$path_parts[count($path_parts) - 1] = str_replace($fake_dirname . "-", "", $fake_filename);
    		array_splice($path_parts, count($path_parts) - 2, 1);
	    }

	    if(strpos($path_parts[count($path_parts) - 1], ".") === false) {
		    $source["name"] = $path_parts[count($path_parts) - 1];
    		$source["ext"] = "";
	    } else {
		    $source_file = explode(".", $path_parts[count($path_parts) - 1]);

		    $source["ext"] = $source_file[count($source_file) - 1];
		    unset($source_file[count($source_file) - 1]);
		    $source["name"] = implode(".", $source_file);
		}

	    $final_ext = ffGetFilename($path_info, false); //$source["ext"];
	    if(!$final_ext)
	        $final_ext = $source["ext"];

	    /*if($source["name"] != $path_parts[count($path_parts) - 2] 
	        &&  strpos($source["name"], $path_parts[count($path_parts) - 2]) === 0
	    ) {
	        $original_name = $source["name"];
	        $fake_filename = $path_parts[count($path_parts) - 2];
	        $source["name"] = substr($source["name"], strlen($fake_filename) + 1);
	    }*/
	    
	    if(count($path_parts) > 1) 
	    {
	        if($mode === null) {
	            global $ffMimeTypes;

	            if(strpos($path_parts[0], "-") !== false)
	                $tmp_ext = substr($path_parts[0], strrpos($path_parts[0], "-") + 1);

	            if(array_key_exists(strtolower($tmp_ext), $ffMimeTypes)) {
	                if($source["ext"] != $tmp_ext)
	                    $mode_format = $tmp_ext;

	                $mode = substr($path_parts[0], 0, strrpos($path_parts[0], "-"));
	                $source["ext"] = $tmp_ext;
	            } else {
	                $mode = $path_parts[0];
	            }
	            unset($path_parts[0]);
	        }             
	    }
	    $source["path"] = ffcommon_dirname(implode("/", $path_parts));
	    if(strlen($source["path"]))
	        $source["path"] = "/" . $source["path"];

	    $ext = ($source["ext"]
    		? "." . $source["ext"]
    		: ""
	    );
		
	    $source["name"] = str_replace("+", " ", $source["name"]);            
	    if(is_file($base_path . $source["path"] . "/" . $source["name"] . $ext))
	        $filepath = $base_path . $source["path"] . "/" . $source["name"] . $ext;
	    elseif($mode && is_file($base_path . "/" . $mode . $source["path"] . "/" . $source["name"] . $ext)) {
	        $filepath = $base_path . "/" . $mode . $source["path"] . "/" . $source["name"] . $ext;
	        $mode = null;
	    } elseif($fake_filename && is_file($base_path . $source["path"] . "/" . $fake_dirname . "/" . $fake_filename))
	        $filepath = $base_path . $source["path"] . "/" . $fake_dirname . "/" . $fake_filename;
	    elseif($mode && $fake_filename && is_file($base_path . "/" . $mode . $source["path"] . "/" . $fake_dirname . "/" . $fake_filename)) {
	        $filepath = $base_path . "/" . $mode . $source["path"] . "/" . $fake_dirname . "/" . $fake_filename;
	        $mode = null;
	    } elseif(is_file($base_path . ffCommon_dirname($source["path"]) . "/" . $source["name"] . $ext)) {
	        $filepath = $base_path . ffCommon_dirname($source["path"]) . "/" . $source["name"] . $ext;            
	    } else {
	        $filepath = "";

	        if(is_file(ff_getAbsDir(FF_THEME_DIR . $path_info) . FF_THEME_DIR . $path_info))
	        {
	            $filepath = ff_getAbsDir(FF_THEME_DIR . $path_info) . FF_THEME_DIR . $path_info;
	            $mode = null;
	        }
	        elseif(is_file(ff_getAbsDir(FF_THEME_DIR . substr($path_info, strpos($path_info, "/", 1))) . FF_THEME_DIR . substr($path_info, strpos($path_info, "/", 1))))
	        {
	            $mode = null;
	            $base_path = ff_getAbsDir(FF_THEME_DIR) . FF_THEME_DIR;
	            $filepath = $base_path . substr($path_info, strpos($path_info, "/", 1));
	            //if($wizard_mode === null)
	                $mode = basename(substr($path_info, 0, strpos($path_info, "/", 1)));
	        } else { 
	            $mode = basename(substr($path_info, 0, strpos($path_info, "/", 1)));
	            $source_path = substr($path_info, strpos($path_info, "/", 1));
	            if(strpos($mode, "png") !== false) {
	                $source_path = substr($source_path, 0, strrpos($source_path, ".")) . ".png";
	                $mode = str_replace("-png", "", $mode);
	            } elseif(strpos($mode, "jpg") !== false) {
	                $source_path = substr($source_path, 0, strrpos($source_path, ".")) . ".jpg";
	                $mode = str_replace("-jpg", "", $mode);
                } elseif(strpos($mode, "jpeg") !== false) {
                    $source_path = substr($source_path, 0, strrpos($source_path, ".")) . ".jpeg";
                    $mode = str_replace("-jpeg", "", $mode);
	            }

	            if(is_file(ff_getAbsDir(FF_THEME_DIR . "/site/images" . $source_path) . FF_THEME_DIR . "/site/images" . $source_path))
	            {
	                $base_path = ff_getAbsDir(FF_THEME_DIR . "/site/images") . FF_THEME_DIR . "/site/images";
	                $filepath = $base_path . $source_path;
	            } else {
	                $mode = null;
	            }
	        }
	    }
	  
	    if($filepath && $mode) 
	    {    
	        $str_wmk_file = "";
	        if(is_array($wmk) && count($wmk))
	        {
	            $str_wmk_file_time = "";
	            $str_wmk_file_path = "";
	            foreach($wmk AS $wmk_key => $wmk_file)
	            {
	                $str_wmk_file_time .= filectime($wmk_file);
	                $str_wmk_file_path .= $wmk_file;
	            }
	            $str_wmk_file = "-" . md5($str_wmk_file_time . $str_wmk_file_path);
	        }

			$file = pathinfo($filepath);
			$file_name = $file["filename"]; //ffCommon_url_rewrite($file["filename"]);

	        $final_file = cm_showfilesGetThumbPath(substr(ffCommon_dirname($filepath), strlen($base_path)), $base_path) . "/" . $file_name
	        . ($mode_format ? "-" . $mode_format : "")
	        . "-" . $mode
	        . $str_wmk_file 
	        . "." . $final_ext;

	        if(!isset($_REQUEST["__nocache__"])) 
	        {
	            $check_thumb = is_file($final_file);
	            if(!$check_thumb && !$mode_format) {
	                switch($final_ext) {
	                    case "jpg":
                        case "jpeg":
	                        $tmp_final_ext = "png";
	                        break;
	                    case "png":
	                        $tmp_final_ext = "jpg";
	                        break;
	                    default:
	                }
	                $tmp_final_file = cm_showfilesGetThumbPath(substr(ffCommon_dirname($filepath), strlen($base_path)), $base_path) . "/" . $file_name
	                . "-" . $mode
	                . $str_wmk_file 
	                . "." . $tmp_final_ext;
	                
	                if(is_file($tmp_final_file)) {
						$check_thumb = true;
                		$final_file = $tmp_final_file;
                		$final_ext = $tmp_final_ext;
					}
	            }
	            
	            if($check_thumb && /*filectime($final_file) > $imgParams[$mode]["last_update"] 
	                &&*/ filectime($final_file) > filectime($filepath)
	            ) {
	                $is_valid_thumb = true; 
	            }
	        }
	    }
	}
}     
     
if(!strlen($filepath) && !CM_SHOWFILES_SKIP_DB) 
{
	$db = ffDB_Sql::factory();
	$db2 = ffDB_Sql::factory();
		
	$db->query("SELECT * FROM " . CM_TABLE_PREFIX . "showfiles");
	if ($db->nextRecord())
	{
        if(!is_array($imgParams))
            $imgParams = ffCommon_get_image_params();
	    if(is_array($imgParams) && count($imgParams)) 
	    {
	    
	        foreach($imgParams AS $imgParams_value) 
	        {
	            if(strlen($imgParams_value["name"]))
	            {
	                if (strlen($rule_modes))
	                    $rule_modes .= '|';                
	                
	                $rule_modes .= $imgParams_value["name"];
	            }                
	        }
	        $rule_modes = '('. $rule_modes . ')'; 
	    }
	    
		do
		{
			$name		= $db->getField("name", "Text", true);
			$source		= $db->getField("source", "Text", true);
			$field		= $db->getField("field_file", "Text", true);
			
			$path_saved	= $db->getField("path_full", "Text", true);
			$path_temp	= $db->getField("path_temp", "Text", true);
			
			if (MOD_SEC_MULTIDOMAIN)
			{
				$path_saved = str_replace("[ID_DOMAINS]", mod_security_get_domain(), $path_saved);
				$path_temp = str_replace("[ID_DOMAINS]", mod_security_get_domain(), $path_temp);
			}
			
			$tmp_expires	= $db->getField("expires")->getValue();

			$ID_showfiles	= $db->getField("ID");

			// BUILD RULES

			$params = array();
			$params_dbskip = array();
			$rule		= '/^\/' . $name;
			$rule_saved	= $rule . '\/saved';

			$db2->query("SELECT * FROM " . CM_TABLE_PREFIX . "showfiles_where WHERE ID_showfiles = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID");
			if ($db2->nextRecord())
			{
				do
				{
					$rule_saved .= '\/([^\/]+)';
					$params[$db2->getField("name")->getValue()] = null;
					$params_dbskip[$db2->getField("name")->getValue()] = $db2->getField("dbskip")->getValue();
				} while ($db2->nextRecord());
			}
			
			$rule_saved_with_mode = $rule_saved;
			$rule_saved				.= '\/(.+)$/';
			$rule_saved_with_mode	.= '\/' . $rule_modes . '\/(.+)$/';

			$rule_temp				= $rule . '\/temp\/(.+)$/';
			$rule_temp_with_mode	= $rule . '\/temp\/' . $rule_modes . '\/(.+)$/';

			// CHECK RULES
			$saved = null;
			$mode = null;
			$filename = null;

			$matches = array();
			if (preg_match($rule_saved_with_mode, $path_info, $matches))
			{
				$saved = true;

				$i = 0;
				foreach ($params as $key => $value)
				{
					$i++;
					$params[$key] = $matches[$i];
				}
				reset($params);

				$i++;
				$mode = $matches[$i];

				$i++;
				$filename = $matches[$i];
			}
			elseif (preg_match($rule_saved, $path_info, $matches))
			{
				$saved = true;
				$i = 0;
				foreach ($params as $key => $value)
				{
					$i++;
					$params[$key] = $matches[$i];
				}
				reset($params);

				$i++;
				$filename = $matches[$i];
			}
			elseif (preg_match($rule_temp_with_mode, $path_info, $matches))
			{
				$saved = false;

				$mode = $matches[1];
				$filename = $matches[2];
			}
			elseif (preg_match($rule_temp, $path_info, $matches))
			{
				$saved = false;

				$filename = $matches[1];
			}

			if ($saved !== null && $filename !== null)
			{
				if ($tmp_expires)
					$expires = $tmp_expires;
				break;
			}
		} while ($db->nextRecord());

		if ($saved === null || $filename === null) 
		{
			if(CM_SHOWFILES_FORCE_PATH)
			{
				$strError = "NO MATCHED RULES!";
			} 
			else 
			{
				if(CM_SHOWFILES_ENABLE_DEBUG) 
				{
					ffErrorHandler::raise("NO MATCHED RULES", E_USER_ERROR, null, get_defined_vars());
				} 
				else 
				{
					//$res = cm::_doEvent("showfiles_on_error", array("NO MATCHED RULES"));
					$res = $ff->showfiles_events->doEvent("on_error", array("NO MATCHED RULES"));
					$last_res = end($res);
					if ($last_res === null)
					{				
						http_response_code(404);
						die("NO MATCHED RULES");
					}
				}
			}
		}
	}
	else
	{
		if(CM_SHOWFILES_FORCE_PATH)
		{
			$strError = "NO RULES DEFINED!";
		} 
		else 
		{
			if(CM_SHOWFILES_ENABLE_DEBUG) 
			{
				ffErrorHandler::raise("NO RULES DEFINED", E_USER_ERROR, null, get_defined_vars());
			} 
			else 
			{
				//$res = cm::_doEvent("showfiles_on_error", array("NO RULES DEFINED"));
				$res = $ff->showfiles_events->doEvent("on_error", array("NO RULES DEFINED"));
				$last_res = end($res);
				if ($last_res === null)
				{				
					http_response_code(404);
					die("NO RULES DEFINED");
				}
			}
		}
	}

    //cm::_doEvent("showfiles_before_parsing_path", array(&$path_temp, &$path_saved, &$params, &$db));
    $ff->showfiles_events->doEvent("before_parsing_path", array(&$path_temp, &$path_saved, &$params, &$db, $name));

 	if(!is_file($filepath)) 
    {
	    $filepath = $base_path;
	    if (!$saved)
	    {
		    $filepath .= str_replace("[_FILENAME_]", $filename, $path_temp);
	    }
	    else
	    {
		    $sWHERE = "";

		    $filepath .= str_replace("[_FILENAME_]", $filename, $path_saved);  
		    foreach ($params as $key => $value)
		    {
			    $filepath = str_replace("[" . $key . "]", $value, $filepath);
			    if (!$params_dbskip[$key])
			    {
				    if (strlen($sWHERE)) $sWHERE .= " AND ";
				    $sWHERE .= $key . " = " . $db->toSql($value);
			    }
		    }
		    reset($params);

		    if (substr($source, 0, strlen("SELECT")) == "SELECT")
			    $sSQL = $source;
		    else
			    $sSQL = "SELECT * FROM " . $source . " WHERE [WHERE] ";

		    if (strlen($sWHERE))
		    {
			    $sSQL = str_replace("[AND]", " AND ", $sSQL);
			    $sSQL = str_replace("[WHERE]", $sWHERE, $sSQL);
		    } else {
			    $sSQL = str_replace("[AND]", "", $sSQL);
			    $sSQL = str_replace("[WHERE]", "", $sSQL);
		    }

			if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
				$db = mod_security_get_db_by_domain();
			
		    $db->query($sSQL);
		    if (!$db->nextRecord())
			{
			    if(file_exists($filepath))
				{
				    $db_file = $filename;
			    } 
				else 
				{
				    if(CM_SHOWFILES_ENABLE_DEBUG)
					{
					    ffErrorHandler::raise("NO MATCHED RECORD", E_USER_ERROR, null, get_defined_vars());
				    } 
					else 
					{
						//$res = cm::_doEvent("showfiles_on_error", array("NO MATCHED RECORD"));
						$res = $ff->showfiles_events->doEvent("on_error", array("NO MATCHED RECORD"));
						$last_res = end($res);
						if ($last_res === null)
						{				
							http_response_code(404);
							die("NO MATCHED RECORD");
						}
				    }
			    }
		    }
			
		    $db_file = $db->getField($field)->getValue();
			foreach ($db->fields_names as $key => $value)
			{
				$filepath = str_replace("[" . $value . "]", $db->getField($value, "Text", true), $filepath);
			}
			reset($db->fields_names);

		    //cm::_doEvent("showfiles_get_file_from_db", array($name, $source, $sWHERE));
		    $ff->showfiles_events->doEvent("get_file_from_db", array($name, $source, $sWHERE));
		    
		    if (!is_file($filepath))
		    {
			    if (
				    substr(strtolower($db_file), 0, 7) == "http://"
				    || substr(strtolower($db_file), 0, 8) == "https://"
                    || substr($db_file, 0, 2) == "//"
			    )
			    {
				    // try to get from remote
				    @mkdir(ffCommon_dirname($filepath), 0777, true);

				    $ch = curl_init();

				    curl_setopt($ch, CURLOPT_URL			, $db_file);
				    curl_setopt($ch, CURLOPT_FILETIME		, true);
				    curl_setopt($ch, CURLOPT_BINARYTRANSFER	, true);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER	, true);

				    $remote_file	= curl_exec($ch);
				    $remote_mtime	= curl_getinfo($ch, CURLINFO_FILETIME);
				    curl_close($ch);

				    file_put_contents($filepath, $remote_file, LOCK_EX);

				    if (!is_file($filepath))
					{
					    if(CM_SHOWFILES_ENABLE_DEBUG)
						{
						    ffErrorHandler::raise("CANNOT DOWNLOAD IMAGE", E_USER_ERROR, null, get_defined_vars());
					    } 
						else 
						{
							//$res = cm::_doEvent("showfiles_on_error", array("CANNOT DOWNLOAD IMAGE"));
							$res = $ff->showfiles_events->doEvent("on_error", array("CANNOT DOWNLOAD IMAGE"));
							$last_res = end($res);
							if ($last_res === null)
							{				
								http_response_code(404);
								die("CANNOT DOWNLOAD IMAGE");
							}
					    }
				    }
				    touch($filepath, $remote_mtime);
			    }
		    }
		    else
		    {
			    if((substr($db_file, 0, strlen("http://")) == "http://") 
			    	|| (substr($db_file, 0, strlen("https://")) == "https://")
			    	|| (substr($db_file, 0, 2) == "//")
			    ) {
				    // verify remote file for updates

				    $local_mtime = filemtime($filepath);

				    $ch = curl_init();

				    curl_setopt($ch, CURLOPT_URL, $db_file);
				    curl_setopt($ch, CURLOPT_FILETIME, true);
				    curl_setopt($ch, CURLOPT_NOBODY, true);

				    curl_exec($ch);
				    $remote_mtime =  curl_getinfo($ch, CURLINFO_FILETIME);

				    if ($remote_mtime !== $local_mtime || defined("FF_URLPARAM_NOCACHE"))
				    {
					    curl_setopt($ch, CURLOPT_NOBODY, false);
					    curl_setopt($ch, CURLOPT_BINARYTRANSFER	, true);
					    curl_setopt($ch, CURLOPT_RETURNTRANSFER	, true);
					    
					    $remote_file	= curl_exec($ch);
					    $remote_mtime	= curl_getinfo($ch, CURLINFO_FILETIME);
					    curl_close($ch);

					    @unlink($filepath);
					    file_put_contents($filepath, $remote_file, LOCK_EX);

					    if (!is_file($filepath))
						{
						    if(CM_SHOWFILES_ENABLE_DEBUG)
							{
							    ffErrorHandler::raise("CANNOT DOWNLOAD IMAGE", E_USER_ERROR, null, get_defined_vars());
						    } 
							else 
							{
								//$res = cm::_doEvent("showfiles_on_error", array("CANNOT DOWNLOAD IMAGE"));
								$res = $ff->showfiles_events->doEvent("on_error", array("CANNOT DOWNLOAD IMAGE"));
								$last_res = end($res);
								if ($last_res === null)
								{				
									http_response_code(404);
									die("CANNOT DOWNLOAD IMAGE");
								}
						    }
					    }

					    touch($filepath, $remote_mtime);
				    }
				    else
					    curl_close($ch);
			    }
		    }
	    }
    }

    if (!is_file($filepath))
	{
        if(CM_SHOWFILES_ENABLE_DEBUG)
		{
            ffErrorHandler::raise("MISSING SOURCE FILE", E_USER_ERROR, null, get_defined_vars());
        } 
		else 
		{
			//$res = cm::_doEvent("showfiles_on_error", array("MISSING SOURCE FILE"));
			$res = $ff->showfiles_events->doEvent("on_error", array("MISSING SOURCE FILE"));
			$last_res = end($res);
			if ($last_res === null)
			{				
				http_response_code(404);
				die("MISSING SOURCE FILE");
			}
        }
    }
}

if (!$filepath)
{
    $res = $ff->showfiles_events->doEvent("on_warning", array($path_info, $_SERVER["HTTP_REFERER"], $mode));
    $last_res = end($res);
    if ($last_res)
    {
    	if(isset($last_res["base_path"]))
        	$base_path = $last_res["base_path"];
    	if(isset($last_res["filepath"]))
        	$filepath = $last_res["filepath"];
    	if(isset($last_res["mode"]))
        	$mode = $last_res["mode"];
    	if(isset($last_res["wizard"]))
        	$wizard = $last_res["wizard"];
    }
}

if($filepath && $mode) 
{
    if(!$is_valid_thumb) 
    {
		if(!$final_ext)
			$final_ext = ffGetFilename($filepath, false);
			
        if(!is_array($imgParams))
            $imgParams = ffCommon_get_image_params();

        if(!$imgParams[$mode]) {
            if(!$wizard["mode"]) {
	            if(stripos($mode, "x") !== false) {
	                $wizard["mode"] 				= explode("x", strtolower($mode));
	                $wizard["method"] 				= "proportional";
	                $wizard["resize"] 				= false;
	            } elseif(strpos($mode, "-") !== false) {
	                $wizard["mode"] 				= explode("-", $mode);
	                $wizard["method"] 				= "crop";
	                $wizard["resize"] 				= false;
	            }
			}

            if(count($wizard["mode"]) == 2 && is_numeric($wizard["mode"][0]) && is_numeric($wizard["mode"][1])) {
                $imgParams[$mode] = array(
                                    "dim_x"     => $wizard["mode"][0]
                                    , "dim_y"   => $wizard["mode"][1]
                                    , "format"  => $final_ext 
                                    , "mode"    => $wizard["method"]
                                    , "resize"  => $wizard["resize"]
                                );
            }
        }

        if(is_array($imgParams[$mode])) {
            $str_wmk_file = "";
            if(is_array($wmk) && count($wmk))
            {
                $str_wmk_file_time = "";
                $str_wmk_file_path = "";
                foreach($wmk AS $wmk_key => $wmk_file)
                {
                    $str_wmk_file_time .= filectime($wmk_file);
                    $str_wmk_file_path .= $wmk_file;
                }
                $str_wmk_file = "-" . md5($str_wmk_file_time . $str_wmk_file_path);
            }

			if($mode_format)
				$imgParams[$mode]["format"] = $final_ext;

            if(!$final_file)
            	$final_file = cm_showfilesGetThumbPath(substr(ffCommon_dirname($filepath), strlen($base_path)), $base_path) . "/" . ffGetFilename($filepath) // ffCommon_url_rewrite(ffGetFilename($filepath))
							. ($mode_format ? "-" . $mode_format : "")
                            . "-" . $mode
                            . $str_wmk_file 
                            . "." . $final_ext;

            if(!defined("FF_URLPARAM_NOCACHE") && is_file($final_file)) 
            {
                if(filectime($final_file) > $imgParams[$mode]["last_update"] 
                    && filectime($final_file) > filectime($filepath)
                ) {
                    $is_valid_thumb = true;
                }
            }

            if (!is_file($final_file) || !$is_valid_thumb)
            {
                make_image($filepath, $final_file, $imgParams[$mode], CM_SHOWFILES_EXTEND, $wmk);
            }
        }
    }
} else {
    $final_file = $filepath;
}

if (!is_file($final_file))
{
	if(CM_SHOWFILES_ENABLE_DEBUG)
	{
		ffErrorHandler::raise("MISSING FINAL FILE", E_USER_ERROR, null, get_defined_vars());
	}
	else
	{
		//$res = cm::_doEvent("showfiles_on_error", array("MISSING FINAL FILE"));
		$res = $ff->showfiles_events->doEvent("on_error", array("MISSING FINAL FILE"));
		$last_res = end($res);
		if ($last_res === null)
		{
			http_response_code(404);
			die("MISSING FINAL FILE");
		}
	}
}

if ($expires)
{
	// $expires = (60 * 60 * 24 * $expires) * -1; // il -1 dipende da come viene gestito il file in output_header (un if su < 0). Da verificare!!!
	$expires = (60 * 60 * 24 * $expires);
}

if (defined("FF_URLPARAM_GENCACHE"))
	@touch($final_file);

$etag = md5($final_file . filemtime($final_file));

$tmp_content = null;
$size = null;
if (!$cached && ($tmp_mime === "application/x-javascript" || $tmp_mime === "text/css"))
{
	$tmp_content = file_get_contents($final_file);
	$tmp_content = str_replace("{site_path}", FF_SITE_PATH, $tmp_content);
	$tmp_content = str_replace("{showfiles}", CM_SHOWFILES, $tmp_content);
	$size = mb_strlen($tmp_content, "8bit");
}

if(CM_SHOWFILES_OPTIMIZE && defined("FF_URLPARAM_NOCACHE"))
{
    $mimetype = ffMimeTypeByFilename($final_file);
    if(strpos($mimetype, "image") === 0) 
        ffImageOptimize($final_file, $mimetype);
}

//header_remove(); nei sogni sfrenati è per togliere i cookie, trovare il modo di applicarlo alle aree pubbliche
//output_header($final_file, "inline", ($filename ? $filename : basename($final_file)), "text/plain", $expires, null, false, false, $size);
ffCommon_output_header(array(
	"file"			=> $final_file,
	"disposition"	=> "inline",
	"fake_filename" => ($filename ? $filename : basename($final_file)),
	"max_age"		=> $expires,
	"size"			=> $size,
	"etag"			=> $etag
));

// implementare If-Modified-Since

if (!defined("FF_URLPARAM_NOCACHE") && strlen($_SERVER["HTTP_IF_NONE_MATCH"]) && substr($_SERVER["HTTP_IF_NONE_MATCH"], 0, strlen($etag)) == $etag)
{
	http_response_code(304);
	exit;
}

if ($tmp_content === null)
	readfile($final_file);
else
	echo $tmp_content;
exit; // useless, but make me sleep

///////////////////////////////////////////////////////////////////////////////////////////////////////
// FUCNTIONS
///////////////////////////////////////////////////////////////////////////////////////////////////////


function make_image($source_file, $final_file, $params, $extend = false, $wmk = null) {
	// NEED PROCESSING
	$default_params = array(
		"dim_x" => 1
		, "dim_y" => 1
		, "resize" => false
		, "alignment" => "center"
		, "mode" => "proportional"
		, "transparent" => true
		, "bgcolor" => "FFFFFF"
		, "alpha" =>  "0"
		, "format" => "png"
		, "format_jpg_quality" => 77
	
	);
	$params = array_replace_recursive($default_params, $params);

	if($extend)
	{
		if(!$params["dim_x"] > 0)
			$params["dim_x"] = 1;

		if(!$params["dim_y"] > 0)
			$params["dim_y"] = 1;
			
		if($params["resize"] && $params["mode"] != "crop") {
		    $params["max_x"] = $params["dim_x"];
		    $params["max_y"] = $params["dim_y"];

		    $params["dim_x"] = null;
		    $params["dim_y"] = null;
		} else {
		    $params["max_x"]  = null;
		    $params["max_y"]  = null;
		}
		    
		if($params["format"] == "png" && $params["transparent"]) {
		    $params["bgcolor_csv"] = $params["bgcolor"];
		    $params["alpha_csv"] = 127;

		    $params["bgcolor_new"] = $params["bgcolor"];
		    $params["alpha_new"] = 127;
		} else {
		    $params["bgcolor_csv"] = null;
		    $params["alpha_csv"] = 0;

		    $params["bgcolor_new"] = $params["bgcolor"];
		    $params["alpha_new"] =  $params["alpha"];
		}
		if($params["force_icon"])
    		$params["filesource"] = ff_getAbsDir($params["force_icon"]) . $params["force_icon"];
		else
    		$params["filesource"] = $source_file;

		$params["wmk_word_enable"] = (is_dir($source_file)
	        ? $params["enable_thumb_word_dir"]
	        : $params["enable_thumb_word_file"]
	    );
	} else {
		if ($params["dim_x"] == 0)
			$params["dim_x"] = null;

		if ($params["dim_y"] == 0)
			$params["dim_y"] = null;

		if ($params["dim_x"] || $params["max_x"] == 0)
			$params["max_x"] = null;

		if ($params["dim_y"] || $params["max_y"] == 0)
			$params["max_y"] = null;

		$params["bgcolor_csv"] = $params["bgcolor"];
		$params["alpha_csv"] = $params["alpha"];

		$params["bgcolor_new"] = $params["bgcolor"];
		$params["alpha_new"] = $params["alpha"];
		
		$params["filesource"] = $source_file;
		$params["frame_color"] = null;
		$params["frame_size"] = 0;
		$params["wmk_method"] = "proportional";
		
		$params["wmk_word_enable"] = false;

	}
	if(!strlen($params["format"]))
		$params["format"] = "jpg";

	$cCanvas = new ffCanvas();

	$cCanvas->cvs_res_background_color_hex 			= $params["bgcolor_csv"];
	$cCanvas->cvs_res_background_color_alpha 		= $params["alpha_new"];
	$cCanvas->format 								= $params["format"];
    $cCanvas->format_jpg_quality 					= $params["format_jpg_quality"];
    $cCanvas->optimize                                 = CM_SHOWFILES_OPTIMIZE;

	$cThumb = new ffThumb($params["dim_x"], $params["dim_y"]);
	$cThumb->new_res_max_x 							= $params["max_x"];
	$cThumb->new_res_max_y 							= $params["max_y"];
	$cThumb->disk_path 								= ff_getAbsDir("");
	$cThumb->src_res_path 							= $params["filesource"];
	$cThumb->theme 									= CM_SHOWFILES_THEME;
	$cThumb->icon_path 								= CM_SHOWFILES_ICON_PATH;

	$cThumb->new_res_background_color_hex 			= $params["bgcolor_new"];
	$cThumb->new_res_background_color_alpha			= $params["alpha_new"];

	$cThumb->new_res_frame_size 					= $params["frame_size"];
	$cThumb->new_res_frame_color_hex 				= $params["frame_color"];

	$cThumb->new_res_method 						= $params["mode"];
	$cThumb->new_res_resize_when 					= $params["when"];
	$cThumb->new_res_align 							= $params["alignment"];
	
	//Default Watermark Image
	if ($params["wmk_enable"])
	{
		$cThumb_wmk = new ffThumb($params["dim_x"], $params["dim_y"]);
		$cThumb_wmk->new_res_max_x 					= $params["max_x"];
		$cThumb_wmk->new_res_max_y 					= $params["max_y"];
		$cThumb_wmk->disk_path 						= ff_getAbsDir("");
		$cThumb_wmk->src_res_path 					= $params["wmk_file"];
		$cThumb_wmk->theme 							= CM_SHOWFILES_THEME;
		$cThumb_wmk->icon_path 						= CM_SHOWFILES_ICON_PATH;

		//$cThumb->new_res_background_color_hex = $params["bgcolor"];
		$cThumb_wmk->new_res_background_color_alpha	= "127";

		$cThumb_wmk->new_res_method 				= $params["mode"];
		$cThumb_wmk->new_res_resize_when 			= $params["when"];
		$cThumb_wmk->new_res_align 					= $params["wmk_alignment"];
		$cThumb_wmk->new_res_method 				= $params["wmk_method"];
		
		$cThumb->watermark 							= $cThumb_wmk;
		
		//$cCanvas->addChild($cThumb_wmk);
	}
	
	//Multi Watermark Image
	if(is_array($wmk) && count($wmk))
	{
		foreach($wmk AS $wmk_key => $wmk_file)
		{
			$cThumb_wmk = new ffThumb($params["dim_x"], $params["dim_y"]);
			$cThumb_wmk->new_res_max_x 						= $params["max_x"];
			$cThumb_wmk->new_res_max_y 						= $params["max_y"];
			$cThumb_wmk->disk_path 							= ff_getAbsDir("");
			$cThumb_wmk->src_res_path 						= $wmk_file["file"];
			$cThumb_wmk->theme 								= CM_SHOWFILES_THEME;
			$cThumb_wmk->icon_path 							= CM_SHOWFILES_ICON_PATH;

			//$cThumb->new_res_background_color_hex = $params["bgcolor"];
			$cThumb_wmk->new_res_background_color_alpha		= "127";

			$cThumb_wmk->new_res_method						= $params["mode"];
			$cThumb_wmk->new_res_resize_when 				= $params["when"];
			$cThumb_wmk->new_res_align 						= $params["wmk_alignment"];
			$cThumb_wmk->new_res_method 					= $params["wmk_method"];

			$cThumb->watermark[] 							= $cThumb_wmk;
		}
	}

	//Watermark Text
	if($params["wmk_word_enable"]) {
	    $cThumb->new_res_font["caption"] = $params["shortdesc"];
	    if(preg_match('/^[A-F0-9]{1,}$/is', strtoupper($params["word_color"])))
	        $cThumb->new_res_font["color"] = $params["word_color"];
	    if(is_numeric($params["word_size"]) && $params["word_size"] > 0)
	        $cThumb->new_res_font["size"] = $params["word_size"];
	    if(strlen($params["word_type"]))
	        $cThumb->new_res_font["type"] = $params["word_type"]; 
	    if(strlen($params["word_align"]))
	        $cThumb->new_res_font["align"] = $params["word_align"]; 
	}
	
	$cCanvas->addChild($cThumb);

	@mkdir(ffCommon_dirname($final_file), 0777, true);
	$cCanvas->process($final_file);
	
	$ff = ffGlobals::getInstance("ff");
	$res = $ff->showfiles_events->doEvent("make_file", array($source_file, $final_file, $params, $extend, $wmk));
	/*$last_res = end($res);
	if ($last_res === null)
	{				
	}*/
}

function cm_showfilesGetThumbPath ($path, $base_path = null)
{
	if(CM_SHOWFILES_THUMB_IN_CACHE)
		return ff_getAbsDir("/cache/" . CM_SHOWFILES_THUMB_PATH . $path) . "/cache/" . CM_SHOWFILES_THUMB_PATH . $path;
	else
		return $base_path . $path . "/" . CM_SHOWFILES_THUMB_PATH;
}

function cm_resolve_source_path($image, $base_path) {
	$mode = null;

	/*
	* default: cerca l'immagine di origine in uploads
	* supporta il sistema di ridimensionamento dinamico
	*/
	$source["dirname"] 			= ($image["dirname"] == "/" ? "" : $image["dirname"]);
	$source["extension"] 		= $image["extension"];

	if(strpos($image["filename"], "-png-") !== false) {
		$file 					= explode("-png-", $image["filename"]);
		$mode 					= $file[1];
		$source["extension"] 	= "png";
		$source["filename"] 	= $file[0];
	} elseif(strpos($image["filename"], "-jpg-") !== false) {
		$file 					= explode("-jpg-", $image["filename"]);
		$mode 					= $file[1];
		$source["extension"] 	= "jpg";
		$source["filename"] 	= $file[0];
	} elseif(strpos($image["filename"], "-jpeg-") !== false) {
		$file 					= explode("-jpeg-", $image["filename"]);
		$mode 					= $file[1];
		$source["extension"] 	= "jpeg";
		$source["filename"] 	= $file[0];
	} else {
		$file 					= explode("-", $image["filename"]);

		for($i = 0; $i<=3; $i++) {
			if(!count($file))
				break;

			$mode = array_pop($file) . ($mode ? "-" : "") . $mode;
			$filename = implode("-", $file);

			if(is_file(FF_DISK_PATH . $base_path . $source["dirname"] . "/" . $filename . "." . $source["extension"])) {
				$source["filename"] = $filename;
				break;
			}
		}
	}

	if($source["filename"] && $source["extension"]) {
		$source["basename"] 	= $source["filename"] . "." . $source["extension"];

		$filepath 				= FF_DISK_PATH . $base_path . $source["dirname"] . "/" . $source["basename"];
	} else {
		$mode					= null;
	}

	return array(
		"filepath" 				=> $filepath
	, "mode" 				=> $mode
	, "source" 				=> $source
	);
}