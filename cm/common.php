<?php
/**
 * @package ContentManager
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

$globals_cm = ffGlobals::getInstance("__cm__");
$globals_cm->field_layout_priority = array(
	array(new ffData(cm::LAYOUT_PRIORITY_TOPLEVEL, "Number"), new ffData(ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_TOPLEVEL")))
	, array(new ffData(cm::LAYOUT_PRIORITY_HIGH, "Number"), new ffData(ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_HIGH")))
	, array(new ffData(cm::LAYOUT_PRIORITY_NORMAL, "Number"), new ffData(ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_NORMAL")))
	, array(new ffData(cm::LAYOUT_PRIORITY_LOW, "Number"), new ffData(ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_LOW")))
	, array(new ffData(cm::LAYOUT_PRIORITY_FINAL, "Number"), new ffData(ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_FINAL")))
);

function cm_getModulesExternalPath()
{
    if (CM_SHOWFILES_MODULES)
        return CM_SHOWFILES . CM_MODULES_PATH;
    else
        return CM_MODULES_PATH;
}
function cm_getAppName()
{
	if (defined("CM_USER_LOCAL_APP_NAME"))
		return CM_USER_LOCAL_APP_NAME;
	else
		return CM_LOCAL_APP_NAME;
}

function cm_confCascadeFind($base_path, $subpath, $file)
{
	$tmp = FF_DISK_PATH . "/conf" . $subpath . "/" . $file;

	if (is_file($tmp))
		return $tmp;
	elseif (is_file($base_path . "/" . $file))
		return $base_path . "/" . $file;
	else
		return null;
}

function cm_showfiles_guessfromurl($url, $default)
{
	if (strpos($url, "?") !== false)
		$url = substr($url, 0, strpos($url, "?") - 1);
	if (strrpos($url, "/") === false)
		$url = $default;
	else
		$url = substr($url, strrpos($url, "/") + 1);

	if (!strlen($url) || strpos($url, "/") !== false)
		$url = $default;

	return $url;
}

function cm_showfiles_get_abs_url($path = null) {
	static $res = null;

	if(!$res) 
	{
		if(substr(strtolower(CM_SHOWFILES), 0, 7) == "http://" || substr(strtolower(CM_SHOWFILES), 0, 8) == "https://" || substr(CM_SHOWFILES, 0, 2) == "//")
			$res = CM_SHOWFILES;
		else 
			$res = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . CM_SHOWFILES;
	}
	
	return $res . $path;
}

function cm_getMainTheme()
{
    if(defined("CM_DEFAULT_THEME"))
        return CM_DEFAULT_THEME;
	else
		ffErrorHandler::raise("CM API: can't guess Main Theme", E_USER_ERROR, null, get_defined_vars());
}

/**
 * Extract URLs from CSS text.
 * @author Alessandro Stucchi
 */
function cm_extract_css_urls($text)
{
    $urls = array();
    $url_pattern     = '(([^\'"\)]*)+)';
    $urlfunc_pattern = 'url\(\s*(?!data:)\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
    $pattern         = '/(' .
         '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
        '|(@import\s*'      . $urlfunc_pattern . ')'      .
        '|('                . $urlfunc_pattern . ')'      .  ')/iu';

    if (!preg_match_all($pattern, $text, $matches)) {
        return preg_last_error();
	}


 	foreach ($matches[9] as $match)
        if (!empty($match))
            $urls[] = preg_replace( '/\\\\(.)/u', '\\1', $match );

    return $urls;
}

function cm_urls_to_abs($urls, $source)
{
	static $tmp_css_link_replaced = array();
	$cm = cm::getInstance();
	if(is_array($urls) && count($urls))
	{

		foreach($urls AS $url)
		{
			if(isset($tmp_css_link_replaced[$url]))
				continue;

			if(substr($url, 0, 1) != "/"
				&& (substr(strtolower($url), 0, 7) != "http://"
					&& substr(strtolower($url), 0, 8) != "https://"
					&& substr($url, 0, 2) != "//")
			) {
				$arrBufferPath = parse_url(ffcommon_dirname($source) . "/" . $url);
				// echo "<pre>"; print_r($arrBufferPath);
				if(substr(strtolower($source), 0, 7) == "http://"
					|| substr(strtolower($source), 0, 8) == "https://"
					|| substr($source, 0, 2) == "//"
				)
					$relative_buffer_path = cm_canonicalize($arrBufferPath["scheme"] . "://" . $arrBufferPath["host"] . $arrBufferPath["path"])
						. (array_key_exists("query", $arrBufferPath) ? "?" . $arrBufferPath["query"] : "")
						. (array_key_exists("fragment", $arrBufferPath) ? "#" . $arrBufferPath["fragment"] : "");
				else {
					$relative_buffer_path = substr(realpath($arrBufferPath["path"]), strlen(FF_DISK_PATH))
						. (array_key_exists("query", $arrBufferPath) ? "?" . $arrBufferPath["query"] : "")
						. (array_key_exists("fragment", $arrBufferPath) ? "#" . $arrBufferPath["fragment"] : "");

					$relative_buffer_path = str_replace("\\.rep\\ff" , "", $relative_buffer_path); // @CarmineRumma
					$relative_buffer_path = str_replace("\\.rep\\vgallery" , "", $relative_buffer_path); // @CarmineRumma

				}

				if(strpos($relative_buffer_path, FF_THEME_DIR) === 0)
				{

					if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . substr($relative_buffer_path, strpos($relative_buffer_path, "/", strlen(FF_THEME_DIR . "/")))))
					{
						$relative_buffer_path =  "/" . $cm->oPage->theme . substr($relative_buffer_path, strpos($relative_buffer_path, "/", strlen(FF_THEME_DIR . "/")));
					}
					else
					{
						$relative_buffer_path = substr($relative_buffer_path, strlen(FF_THEME_DIR));
					}

					$relative_buffer_path = FF_SITE_PATH . FF_THEME_DIR . $relative_buffer_path;
				}
				elseif(strpos($relative_buffer_path, "/uploads") === 0)
				{
					$relative_buffer_path = CM_MEDIACACHE_SHOWPATH . substr($relative_buffer_path, strlen("/uploads"));
				}

				$tmp_css_link_replaced[$url] = $relative_buffer_path;
			}
		}
	}

	return $tmp_css_link_replaced;
}
function cm_convert_url_in_abs_by_content($content, $source)
{
	$content = str_replace(array(
		"{site_path}"
	, "{showfiles}"
	), array(
		FF_SITE_PATH
	, (CM_MEDIACACHE_SHOWPATH
			? CM_MEDIACACHE_SHOWPATH
			: CM_SHOWFILES
		)
	), $content);

	$tmp_css_url = cm_extract_css_urls($content);
	if(is_array($tmp_css_url))
	{
		$tmp_css_link_replaced = cm_urls_to_abs($tmp_css_url, $source);
		$content = str_replace(array_keys($tmp_css_link_replaced), array_values($tmp_css_link_replaced), $content);
	} elseif(strpos($source, FF_THEME_DISK_PATH) === 0) {
		$arrBufferPath = explode("/", str_replace(FF_THEME_DISK_PATH . "/", "", $source));
		$content = str_replace("../", FF_THEME_DIR . "/" . $arrBufferPath[0] . "/", $content);
	}
	return $content;
}
/*
function cm_extract_css_urls($text) //original (if the url have cinna (,) the regexp failed with PREG_BACKTRACK_LIMIT_ERROR )
{
    $urls = array();
    $url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
    $urlfunc_pattern = 'url\(\s*(?!data:)\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
    $pattern         = '/(' .
         '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
        '|(@import\s*'      . $urlfunc_pattern . ')'      .
        '|('                . $urlfunc_pattern . ')'      .  ')/iu';

    if (!preg_match_all($pattern, $text, $matches)) {
        return preg_last_error();
	}
	
    // @import '...'
    // @import "..."
    foreach ($matches[3] as $match)
        if (!empty($match))
            $urls[] = preg_replace( '/\\\\(.)/u', '\\1', $match );
 
    // @import url(...)
    // @import url('...')
    // @import url("...")
    foreach ($matches[7] as $match)
        if (!empty($match))
            $urls[] = preg_replace( '/\\\\(.)/u', '\\1', $match );
 
    // url(...)
    // url('...')
    // url("...")
    foreach ($matches[11] as $match)
        if (!empty($match))
            $urls[] = preg_replace( '/\\\\(.)/u', '\\1', $match );

            
    return $urls;
}
*/ 
function cm_canonicalize($address)
{
    $address = explode('/', $address);
    $keys = array_keys($address, '..');

    foreach($keys AS $keypos => $key)
    {
        array_splice($address, $key - ($keypos * 2 + 1), 2);
    }

    $address = implode('/', $address);
    $address = str_replace('./', '', $address);
    
    return $address;
}

/**
 * @author Alessandro Stucchi
 * 3/12/12 - Samuele Diella - Fixed encoding detect and management
 * @deprecated since 3/12/12
 */
function cm_parse_gzip($contents)
{
	if (ffHTTP_encoding_isset("gzip"))
		return gzencode($contents, 9, FORCE_GZIP);
	else
		return $contents;
}

// find valid cahce files and purge old ones
function cm_filecache_find($cache_dir, $path_dir, $group_subdir, $fixed_maxage, $last_valid, $scaledown, $now = null, $encodings = null, $purge_old = true, $reuse = false)
{
	// ***********************************
	// init here to save a bit calculation
	if ($now === null)
		$now = time();
	
	// ***********************************

	$cache_file = null;
	if ($group_subdir)
	{
		$itGroup = new DirectoryIterator($cache_dir);
		foreach($itGroup as $fiGroup)
		{
			if($fiGroup->isDot())
				continue;

			$cache_file = cm_filecache_cycle_subgroup($fiGroup->getPathname(), $path_dir, $fixed_maxage, $last_valid, $scaledown, $now, $encodings, $purge_old, $reuse);
			if ($cache_file !== null)
				return $cache_file;
		}
		return null;
	}
	else
	{
		return cm_filecache_cycle_subgroup($cache_dir, $path_dir, $fixed_maxage, $last_valid, $scaledown, $now, $encodings, $purge_old, $reuse);
	}
}

// true = non expired, false = expired
function cm_filecache_check_expiration($mtime, $ctime, $now, $last_valid)
{
		if (
				(!$last_valid
					|| $now < $last_valid
					|| $ctime >= $last_valid
				) && (
					$mtime > $now
				)
		)
			return true;
	else
		return false;
}

function cm_filecache_cycle_subgroup($subpath, $path_dir, $fixed_maxage, $last_valid, $scaledown, $now, $encodings, $purge_old, $reuse)
{
	$cache_uncompressed = null;
	$cache_compressed = null;
	$cache_file = null;
	$last_uncompressed = null;
	$last_compressed = null;
	
	$find_dir = $subpath . $path_dir . "/";
	if (!file_exists($find_dir))
		return null;

	$itFiles = new DirectoryIterator($find_dir);
	foreach($itFiles as $fiFile)
	{
		if($fiFile->isDot())
			continue;

		$filematch = $fiFile->getPathname();

		$fmtime = $fiFile->getMTime();
		$fctime = $fiFile->getCTime();
		
		$fexp = cm_filecache_check_expiration($fmtime, $fctime, $now, $last_valid); // normal expire time. TRUE = VALID
		
		if ($fexp || $reuse)
		{
			$filename = $fiFile->getFilename();
			$file_parts = explode(".", $filename); // schema: (0)ETAG.(1)MIME.(2)MAXAGE.(3)COMPRESSION
			if (count($file_parts) > 1)  // wrong format
			{
				if (isset($file_parts[3])) // compressed section
				{
					if (is_numeric($file_parts[3])) // wrong format
					{
						if ($purge_old)
						{
							//die("errore 1");
							@unlink($filematch);
						}
					}
					else if (ffHTTP_encoding_isset($file_parts[3], $encodings))
					{
						if ($cache_compressed === null)
						{
							if (!$fexp) // try to reuse
							{
								if (
										!$last_valid
										|| $now < $last_valid
										|| $fctime >= $last_valid
									)  // only when not over last valid
								{
									if ($last_compressed !== null)
									{
										if ($last_compressed["fmtime"] >= $fmtime)
											continue;
										
										if ($purge_old)
										{
											//die("errore 2");
											@unlink($last_compressed["file"]);
										}
									}
									
									$last_compressed = array(
											"file" => $filematch
											, "filename" => $filename
											, "file_parts" => $file_parts
											, "fmtime" => $fmtime
											, "fctime" => $fctime
											, "compressed" => $file_parts[3]
											, "reused" => true
										);
								}
								else
								{
									if ($purge_old)
									{
										//die("errore 3");
										@unlink($filematch);
									}
									
									continue;
								}
							}
							else // valid!
							{
								$cache_compressed = array(
										"file" => $filematch
										, "filename" => $filename
										, "file_parts" => $file_parts
										, "fmtime" => $fmtime
										, "fctime" => $fctime
										, "compressed" => $file_parts[3]
										, "reused" => false
									);
							}
						}
						else if ($purge_old)
						{
							//die("errore 5");
							@unlink($filematch);
						}
					}
				}
				else // uncompressed section
				{
					if ($cache_uncompressed === null)
					{
						if (!$fexp) // try to reuse
						{
							if (
									!$last_valid
									|| $now < $last_valid
									|| $fctime >= $last_valid
								)  // only when not over last valid
							{
								if ($last_uncompressed !== null)
								{
									if ($last_uncompressed["fmtime"] >= $fmtime)
										continue;

									if ($purge_old)
									{
										//die("errore 6");
										@unlink($last_uncompressed["file"]);
									}
								}

								$last_uncompressed = array(
										"file" => $filematch
										, "filename" => $filename
										, "file_parts" => $file_parts
										, "fmtime" => $fmtime
										, "fctime" => $fctime
										, "compressed" => false
										, "reused" => true
									);
							}
							else
							{
								if ($purge_old)
								{
									//die("errore 6");
									@unlink($filematch);
								}

								continue;
							}
						}
						else // valid!
						{
							$cache_uncompressed = array(
								"file" => $filematch
								, "filename" => $filename
								, "file_parts" => $file_parts
								, "fmtime" => $fmtime
								, "fctime" => $fctime
								, "compressed" => false
								, "reused" => false
							);
						}
					}
					else if ($purge_old)
					{
						//die("errore 8");
						@unlink($filematch);
					}
				}
			}
			else if ($purge_old)
			{
				//die("errore 9");
				@unlink($filematch);
			}
		}
		else if ($purge_old)
		{
			//die("errore 10");
			@unlink($filematch);
		}
	}
	
	if ($reuse)
	{
		if (!$cache_compressed && $last_compressed)
			$cache_compressed = $last_compressed;
		
		if (!$cache_uncompressed && $last_uncompressed)
			$cache_uncompressed = $last_uncompressed;
	}

	if ($cache_compressed || $cache_uncompressed)
		$cache_file = array(
			"compressed" => $cache_compressed
			, "uncompressed" => $cache_uncompressed
		);
	
	return $cache_file;
}

function cm_filecache_groupwrite($top_cache_dir, $cache_dir, $path_dir, $file, $buffer, $expires, $max_group_dirs, &$cache_group_dir, &$cache_disk_fail)
{
	$rc_cache = true;
	$cache_disk_fail = false;

	if ($cache_group_dir === null)
		$cache_group_dir = 0;
	else if ($cache_group_dir)
		$cache_group_dir--;
	
	do
	{
		$cache_group_dir++;

		if (!is_dir($cache_dir . "/" . $cache_group_dir))
			$cache_new_groupdir = true;
		else
			$cache_new_groupdir = false;

		if ($cache_new_groupdir || !is_dir($cache_dir . "/" . $cache_group_dir . $path_dir))
			$rc_cache = @mkdir($cache_dir . "/" . $cache_group_dir . $path_dir, 0777, true);
		if ($rc_cache)
		{
			if ($rc_cache = @file_put_contents($cache_dir . "/" . $cache_group_dir . $path_dir . "/" . $file, $buffer, LOCK_EX))
				@chmod($cache_dir . "/" . $cache_group_dir . $path_dir . "/" . $file, 0777);
			if (!$rc_cache && $cache_new_groupdir)
				$cache_disk_fail = true;
		}
		else if ($cache_new_groupdir)
			$cache_disk_fail = true;

	} while (!$rc_cache && !$cache_disk_fail && $cache_group_dir < $max_group_dirs);

	if ($rc_cache && $expires !== null)
	{
		$rc_cache = @touch($cache_dir . "/" . $cache_group_dir . $path_dir . "/" . $file, $expires);
		if (!$rc_cache)
		{
			//die("errore 11");
			@unlink($cache_dir . "/" . $cache_group_dir . $path_dir . "/" . $file);
		}
	} 
	else if ($cache_group_dir == $max_group_dirs)
	{
		@touch($cache_dir . "/maxgroup_limit_reached");
		if ($cache_dir != $top_cache_dir)
			@touch($top_cache_dir . "/maxgroup_limit_reached");
	}
	
	if ($cache_disk_fail)
	{
		@touch($cache_dir . "/disk_fail");
		if ($cache_dir != $top_cache_dir)
			@touch($top_cache_dir . "/disk_fail");
	}
	
	return $rc_cache;
}

function cm_filecache_write($path, $file, $buffer, $expires)
{
	$rc_cache = true;
	
	if (!is_dir($path))
		$rc_cache = @mkdir($path, 0777, true);	
	if ($rc_cache)
	{
		if ($rc_cache = @file_put_contents($path . "/" . $file, $buffer, LOCK_EX))
			@chmod($path . "/" . $file, 0777);
		if ($rc_cache && $expires !== null)
		{
			$rc_cache = @touch($path . "/" . $file, $expires);
			if (!$rc_cache)
			{
				//die("errore 12");
				@unlink($path . "/" . $file);
			}
		}
	}
	
	return $rc_cache;
}

function cm_filecache_empty_dir($path, $clean_back = false) 
{
    if(!file_exists($path) || !is_dir($path))
		return true;

	// first, empty the selected directory
	
	$directoryIterator = new DirectoryIterator($path);
    foreach($directoryIterator as $fileInfo)
	{
        $filePath = $fileInfo->getPathname();
        if(!$fileInfo->isDot())
		{
            if($fileInfo->isFile())
			{
				//die("errore 13");
                @unlink($filePath);
            } 
			elseif($fileInfo->isDir())
			{
				cm_filecache_empty_dir($filePath);
            }
        }
    }
	
	// second, walk recursivly backward to clean empty directories
	
	if (!$clean_back)
		return;
	
	@rmdir($path);
	
	while (($path = ffCommon_dirname($path)) !== "/")
	{
		$empty = true;
		
		$directoryIterator = new DirectoryIterator($path);
		foreach($directoryIterator as $fileInfo)
		{
			$filePath = $fileInfo->getPathname();
			if($fileInfo->isDot())
				continue;

			if($fileInfo->isFile() || $fileInfo->isDir())
			{
				$empty = false;
				break;
			}
		}
		
		if ($empty)
			@rmdir($path);
		else
			break;
	}
}

function cm_libsExtend(&$libs, $addon)
{
	if (!is_array($addon))
		ffErrorHandler::raise("Wrong extend usage", E_USER_ERROR, null, get_defined_vars());
		
	foreach ($addon as $key => $elements)
	{
		if (!ffIsset($libs, $key))
		{
			$libs[$key] = $elements;
		}
		else
		{
			$default = $libs[$key]["default"];
			foreach ($elements AS $subkey => $subelements)
			{
				if ($subkey === "all")
				{
					foreach ($libs[$key] as $src_key => $src_value)
					{
						if ($src_key === "default")
							continue;

						$libs[$key][$src_key] = array_merge_recursive($libs[$key][$src_key], $subelements);
					}
				}
				else if ($subkey === "default")
				{
					$libs[$key][$default] = array_merge_recursive($libs[$key][$default], (is_array($subelements) ? $subelements : $elements[$default]));
				}
				else if (ffIsset($libs[$key], $subkey))
				{
					$libs[$key][$subkey] = array_merge_recursive($libs[$key][$subkey], $subelements);
				}
				else
				{
					foreach ($libs as $src_key => $src_value)
					{
						if ($src_key === "default")
							continue;

						if (preg_match($subkey, $src_key) === 1)
							$libs[$key][$src_key] = array_merge_recursive($libs[$key][$src_key], $subelements);
					}
				}
			}
		}
	}
}

function cm_loadlibs(&$libs, $path, $name, $prefix = "", $force_reload = false, $descend = true)
{
	if(!file_exists($path) || !is_dir($path))
		return true;

	if (ffIsset($libs, $prefix . "/" . $name))
		return true;

	if (file_exists($path . "/libs.json"))
	{
		$tmp = file_get_contents($path . "/libs.json");
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_DEFAULT"', CM::LAYOUT_PRIORITY_DEFAULT, $tmp);
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_TOPLEVEL"', CM::LAYOUT_PRIORITY_TOPLEVEL, $tmp);
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_HIGH"', CM::LAYOUT_PRIORITY_HIGH, $tmp);
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_NORMAL"', CM::LAYOUT_PRIORITY_NORMAL, $tmp);
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_LOW"', CM::LAYOUT_PRIORITY_LOW, $tmp);
		$tmp = str_replace('"CM::LAYOUT_PRIORITY_FINAL"', CM::LAYOUT_PRIORITY_FINAL, $tmp);
		$libs[$prefix . "/" . $name] = json_decode($tmp, true);
	}
	
	if (file_exists($path . "/libs.php"))
	{
		$tmp = include($path . "/libs.php");
		if (isset($libs[$prefix . "/" . $name]))
			$libs[$prefix . "/" . $name] = array_merge_recursive($libs[$prefix . "/" . $name], $tmp);
		else
			$libs[$prefix . "/" . $name] = $tmp;
	}
	
	if (!$descend)
		return;

	$directoryIterator = new DirectoryIterator($path);
	foreach($directoryIterator as $fileInfo)
	{
		if ($fileInfo->isDot() || $fileInfo->isFile())
			continue;

		$fileName = $fileInfo->getBasename();
		cm_loadlibs($libs, $path . "/" . $fileName, $fileName, $prefix . "/" . $name, $force_reload);
	}
}

function cm_loadlibs_save($libs, $override = false) {
	$cache_file = CM_CACHE_DISK_PATH . "/libs.php";
	if($override || !file_exists($cache_file)) {
		@mkdir(CM_CACHE_DISK_PATH, 0777, true);
		$tmp_libs_var = var_export($libs, true);
		file_put_contents($cache_file, "<?php\n\nreturn $tmp_libs_var;\n\n", LOCK_EX);
	}
}