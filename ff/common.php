<?php
/**
 * framework common functions
 * 
 * @package FormsFramework
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * get parent'dir, no matter if windows or linux
 * @param type $path
 * @return string
 */
function ffCommon_dirname($path)
{
    $res = dirname($path);
    if(dirname("/") == "\\")
        $res = str_replace("\\", "/", $res);

    if($res == ".")
        $res = "";

    return $res;
}

//------------------------------------------------------------------------------------
// http header "location" wrapper
//------------------------------------------------------------------------------------
function ffRedirect($destination, $http_response_code = null, $add_params = null, $response = array())
{
	if ($add_params !== null)
	{
		$parts = explode("#", $destination);
		if (isset($parts[1]))
			$hash = $parts[1];
		
		$subparts = explode("?", $parts[0]);
		$host = $subparts[0];
		if (isset($subparts[1]))
			$query = $subparts[1];
		
		$destination = $host . "?";
		
		if (strlen($query))
		{
			$params_parts = array();
			foreach (explode("&", trim($add_params, "&")) as $key => $value)
			{
				$tmp = explode("=", $value);
				$params_parts[$tmp[0]] = $tmp[1];
			}
			
			$query_parts = array();
			foreach (explode("&", trim($query, "&")) as $key => $value)
			{
				$tmp = explode("=", $value);
				$query_parts[$tmp[0]] = $tmp[1];
			}
			
			$final_parts = array_merge($query_parts, $params_parts);
			foreach ($final_parts as $key => $value)
			{
				$destination .= $key;
				if ($value !== null)
					$destination .= "=" . $value;
				$destination .= "&";
			}
		}
		else
			$destination .= $add_params;
		
		if (strlen($hash))
			$destination .= "#" . $hash;
	}

	//ffErrorHandler::raise("REDIRECT", E_USER_ERROR, null, get_defined_vars());
	
	if (class_exists("ffGlobals"))
	{
		$ff = ffGlobals::getInstance("ff");
		
		$res = $ff->events->doEvent("onRedirect", array(&$destination, &$http_response_code, &$add_params, &$response));
		$rc = end($res);
		if ($rc !== null)
			return $rc;
	}
	
	$tmp_code = ($http_response_code === null ? 302 : $http_response_code);
	if ($tmp_code === 302)
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
	}

	header("Location: " . $destination, true, $tmp_code);
	//header("Location: " . $destination, true, ($http_response_code === null ? 302 : $http_response_code));
	exit;
}

function ffDialog($returnurl, $type, $title, $message, $cancelurl, $confirmurl, $dlg_site_path)
{
	$url = $dlg_site_path;
	
	if (strpos($dlg_site_path, "?") === false)
		$url .= "?";
	else
		$url .= "&";

	$url .= "title=" 			. rawurlencode($title)
			. "&message=" 		. rawurlencode($message)
			. "&type=" 			. rawurlencode($type)
			//. "&cancelurl=" 		. rawurlencode($cancelurl)
			. "&confirmurl=" 		. rawurlencode($confirmurl)
		;
	if ($returnurl)
		return $url;
	else
	{
		ffRedirect($url);
	}
}

//------------------------------------------------------------------------------------
// This function process special Forms' tags in a given haystack (string =) as the
// first parameter.
// 
// The second and the third parameter are two array of FormFields, respectevely with
// key fields and data fields.
// 
// The fourth parameter, mode, can be "normal" or SQL. With SQL the replacement are 
// made using tosql from cDb class. When using SQL mode, you may also specify 
// if values must be colon enclosed or not with the fifth parameter.
// 
// The sixth param is the page params, in url form.
// 
// All the tags are enclosed by square brackets and capitalized. Some tags are made by
// a name and a variable (not optional) parameter. Those parameters are shown below
// enclosed by "{" brackets.
// 
//------------------------------------------------------------------------------------
function ffProcessTags($haystack, $Keys, $Data, $mode = "normal", $page_params = "", $ret_url = "", $globals = null, $hidden = null, $db = null)
{
	$mode = strtolower($mode);
	if ($mode == "normal")
	{
		$haystack = str_replace("[DATE]", date("%d/%m/%Y"), $haystack);
		
		if (is_array($Keys) && count($Keys)) 
		{
			$tmp = "";
			foreach ($Keys as $key => $value)
			{
				if (is_object($value) && is_subclass_of($value, "ffField_base"))
					$value = $value->value->getValue(null, FF_SYSTEM_LOCALE);
				elseif (is_object($value) && get_class($value) == "ffData")
					$value = $value->getValue(null, FF_SYSTEM_LOCALE);

				if(is_array($value))
					continue;
					
				$tmp .= "keys[" . $key . "]=" . rawurlencode($value) . "&";

				$haystack = str_replace("[" . $key . "_VALUE]", rawurlencode($value), $haystack);
				$haystack = str_replace("[" . $key . "_VALUEPATH]", str_replace("%2F", "/", rawurlencode($value)), $haystack);
			}
			reset($Keys);
			$haystack = str_replace("[KEYS]", $tmp, $haystack);
		}
		else
		{
			$haystack = str_replace("[KEYS]", "", $haystack);					
		}

		if (is_array($hidden) && count($hidden))
		{
			$tmp = "";
			foreach ($hidden as $key => $value)
			{
				if (is_object($value) && is_subclass_of($value, "ffField_base"))
					$value = $value->value->getValue(null, FF_SYSTEM_LOCALE);
				elseif (is_object($value) && get_class($value) == "ffData")
					$value = $value->getValue(null, FF_SYSTEM_LOCALE);
				
				$tmp .= $key . "=" . rawurlencode($value) . "&";

				$haystack = str_replace("[" . $key . "_VALUE]", rawurlencode($value), $haystack);
				$haystack = str_replace("[" . $key . "_VALUEPATH]", str_replace("%2F", "/", rawurlencode($value)), $haystack);
			}
			reset($hidden);
			$haystack = str_replace("[HIDDEN]", $tmp, $haystack);
		}
		else
		{
			$haystack = str_replace("[HIDDEN]", "", $haystack);					
		}

		$haystack = str_replace("[XHR_CTX_ID]", $_REQUEST["XHR_CTX_ID"], $haystack);
		if (strpos($haystack, "[PAGE_NAME]") !== FALSE)
			$haystack = str_replace("[PAGE_NAME]", basename($_SERVER['SCRIPT_NAME']), $haystack);
		if (strpos($haystack, "[PAGE_PARAMS]") !== FALSE)
			$haystack = str_replace("[PAGE_PARAMS]", $page_params, $haystack);
		if (strpos($haystack, "[ENCODED_PAGE_PARAMS]") !== FALSE)
			$haystack = str_replace("[ENCODED_PAGE_PARAMS]", rawurlencode($page_params), $haystack);
		if (strpos($haystack, "[GLOBALS]") !== FALSE)
			$haystack = str_replace("[GLOBALS]", $globals, $haystack);
		if (strpos($haystack, "[ENCODED_GLOBALS]") !== FALSE)
			$haystack = str_replace("[ENCODED_GLOBALS]", rawurlencode($globals), $haystack);
		if (strpos($haystack, "[RET_URL]") !== FALSE)
			$haystack = str_replace("[RET_URL]", $ret_url, $haystack);
		if (strpos($haystack, "[ENCODED_RET_URL]") !== FALSE)
			$haystack = str_replace("[ENCODED_RET_URL]", rawurlencode($ret_url), $haystack);
		if (strpos($haystack, "[FORWARD_URL]") !== FALSE)
			$haystack = str_replace("[FORWARD_URL]", rawurlencode($_SERVER['SCRIPT_NAME'] . "?" . $page_params), $haystack);
		if (strpos($haystack, "[THIS_URL]") !== FALSE)
			$haystack = str_replace("[THIS_URL]", $_SERVER['REQUEST_URI'], $haystack);
		if (strpos($haystack, "[ENCODED_THIS_URL]") !== FALSE) 
			$haystack = str_replace("[ENCODED_THIS_URL]", rawurlencode($_SERVER['REQUEST_URI']), $haystack);
		if (strpos($haystack, "[QUERY_STRING]") !== FALSE) 
			$haystack = str_replace("[QUERY_STRING]", (substr($_SERVER['PATH_INFO'], 0, 1) !== "/" && array_key_exists('REDIRECT_QUERY_STRING', $_SERVER)
                                                        ? $_SERVER['REDIRECT_QUERY_STRING']
                                                        : $_SERVER['QUERY_STRING']
                                                    ), $haystack);
		if ($Data !== null && is_array($Data) && count($Data)) 
		{
			foreach ($Data as $key => $FormField)
			{
				if (strpos($haystack, "[" . $key . "_VALUE]") !== FALSE)
					$haystack = str_replace("[" . $key . "_VALUE]", $FormField->getValue(null, FF_SYSTEM_LOCALE), $haystack);
			}
			reset($Data);
		}
		
		if ($db !== null && is_array($db->fields_names))
		{
			foreach ($db->fields_names as $field_name)
			{
				$haystack = str_replace("[" . $field_name . "_VALUE]", rawurlencode($db->getField($field_name, "Text", true)), $haystack);
				$haystack = str_replace("[" . $field_name . "_VALUEPATH]", str_replace("%2F", "/", rawurlencode($db->getField($field_name, "Text", true))), $haystack); // ?? mod by Alex
			}
		}
	}
    elseif ($mode == "ori")
    { 
        $haystack = str_replace("[DATE]", date("%d/%m/%Y"), $haystack);

        if (is_array($Keys) && count($Keys)) 
        {
            $tmp = "";
            foreach ($Keys as $key => $value)
            {
                if (is_object($value) && is_subclass_of($value, "ffField_base"))
                    $value = ($value->value_ori->getValue(null, FF_SYSTEM_LOCALE) ? $value->value_ori->getValue(null, FF_SYSTEM_LOCALE) : $value->value->getValue(null, FF_SYSTEM_LOCALE));
                elseif (is_object($value) && get_class($value) == "ffData")
                    $value = $value->getValue(null, FF_SYSTEM_LOCALE);

                if(is_array($value))
                    continue;
                    
                $tmp .= "keys[" . $key . "]=" . rawurlencode($value) . "&";

                $haystack = str_replace("[" . $key . "_VALUE]", rawurlencode($value), $haystack);
                $haystack = str_replace("[" . $key . "_VALUEPATH]", str_replace("%2F", "/", rawurlencode($value)), $haystack);
            }
            reset($Keys);
            $haystack = str_replace("[KEYS]", $tmp, $haystack);
        }
        else
        {
            $haystack = str_replace("[KEYS]", "", $haystack);                    
        }

        if (is_array($hidden) && count($hidden))
        {
            $tmp = "";
            foreach ($hidden as $key => $value)
            {
                if (is_object($value) && is_subclass_of($value, "ffField_base"))
                    $value = ($value->value_ori->getValue(null, FF_SYSTEM_LOCALE) ? $value->value_ori->getValue(null, FF_SYSTEM_LOCALE) : $value->value->getValue(null, FF_SYSTEM_LOCALE));
                elseif (is_object($value) && get_class($value) == "ffData")
                    $value = $value->getValue(null, FF_SYSTEM_LOCALE);
                
                $tmp .= $key . "=" . rawurlencode($value) . "&";

                $haystack = str_replace("[" . $key . "_VALUE]", rawurlencode($value), $haystack);
                $haystack = str_replace("[" . $key . "_VALUEPATH]", str_replace("%2F", "/", rawurlencode($value)), $haystack);
            }
            reset($hidden);
            $haystack = str_replace("[HIDDEN]", $tmp, $haystack);
        }
        else
        {
            $haystack = str_replace("[HIDDEN]", "", $haystack);                    
        }

        if (strpos($haystack, "[PAGE_NAME]") !== FALSE)
            $haystack = str_replace("[PAGE_NAME]", basename($_SERVER['SCRIPT_NAME']), $haystack);
        if (strpos($haystack, "[PAGE_PARAMS]") !== FALSE)
            $haystack = str_replace("[PAGE_PARAMS]", $page_params, $haystack);
        if (strpos($haystack, "[ENCODED_PAGE_PARAMS]") !== FALSE)
            $haystack = str_replace("[ENCODED_PAGE_PARAMS]", rawurlencode($page_params), $haystack);
        if (strpos($haystack, "[GLOBALS]") !== FALSE)
            $haystack = str_replace("[GLOBALS]", $globals, $haystack);
        if (strpos($haystack, "[ENCODED_GLOBALS]") !== FALSE)
            $haystack = str_replace("[ENCODED_GLOBALS]", rawurlencode($globals), $haystack);
        if (strpos($haystack, "[RET_URL]") !== FALSE)
            $haystack = str_replace("[RET_URL]", $ret_url, $haystack);
        if (strpos($haystack, "[ENCODED_RET_URL]") !== FALSE)
            $haystack = str_replace("[ENCODED_RET_URL]", rawurlencode($ret_url), $haystack);
        if (strpos($haystack, "[FORWARD_URL]") !== FALSE)
            $haystack = str_replace("[FORWARD_URL]", rawurlencode($_SERVER['SCRIPT_NAME'] . "?" . $page_params), $haystack);
        if (strpos($haystack, "[THIS_URL]") !== FALSE)
            $haystack = str_replace("[THIS_URL]", $_SERVER['REQUEST_URI'], $haystack);
        if (strpos($haystack, "[ENCODED_THIS_URL]") !== FALSE) 
            $haystack = str_replace("[ENCODED_THIS_URL]", rawurlencode($_SERVER['REQUEST_URI']), $haystack);
        if (strpos($haystack, "[QUERY_STRING]") !== FALSE) 
            $haystack = str_replace("[QUERY_STRING]", (substr($_SERVER['PATH_INFO'], 0, 1) !== "/" && array_key_exists('REDIRECT_QUERY_STRING', $_SERVER)
                                                        ? $_SERVER['REDIRECT_QUERY_STRING']
                                                        : $_SERVER['QUERY_STRING']
                                                    ), $haystack);
        if ($Data !== null && is_array($Data) && count($Data)) 
        {
            foreach ($Data as $key => $FormField)
            {
                if (strpos($haystack, "[" . $key . "_VALUE]") !== FALSE)
                    $haystack = str_replace("[" . $key . "_VALUE]", ($FormField->value_ori->getValue(null, FF_SYSTEM_LOCALE) ? $FormField->value_ori->getValue(null, FF_SYSTEM_LOCALE) : $FormField->value->getValue(null, FF_SYSTEM_LOCALE)), $haystack);
            }
            reset($Data);
        }
    }
	elseif ($mode == "sql")
	{
        $haystack = str_replace("[DATE]", date("%d/%m/%Y"), $haystack);
        
        if ($Keys !== null && is_array($Keys) && count($Keys)) 
        {
			foreach ($Keys as $key => $value)
            {
				if (is_object($value) && is_subclass_of($value, "ffField_base"))
					$value = $value->getValue($value->base_type, FF_SYSTEM_LOCALE);
				elseif (is_object($value) && get_class($value) == "ffData")
					$value = $value->getValue(null, FF_SYSTEM_LOCALE);
					
                $haystack = str_replace("[" . $key . "_VALUE]", $value, $haystack);
            }
            reset($Keys);
        }

        if ($Data !== null && is_array($Data) && count($Data)) 
        {
			foreach ($Data as $key => $FormField)
            {
				if (is_array($FormField->multi_fields) && count($FormField->multi_fields))
				{
					if (is_array($FormField->multi_values) && count($FormField->multi_values))
					{
						foreach ($FormField->multi_fields as $subkey => $subvalue)
						{
							$haystack = str_replace("[" . $key . "_" . $subkey . "_VALUE]", $FormField->multi_values[$subkey]->getValue($subvalue["type"], FF_SYSTEM_LOCALE), $haystack);
						}
						reset($FormField->multi_fields);
					}
				}
				else
					$haystack = str_replace("[" . $key . "_VALUE]", $FormField->getValue($FormField->base_type, FF_SYSTEM_LOCALE), $haystack);
            }
            reset($Data);
        }
    }
    else
    	ffErrorHandler::raise("WRONG MODE SELECTED FOR PROCESS TAGS", E_USER_ERROR, null, get_defined_vars());

	return $haystack;
}

function FormsProcessSQL($sSQL, $FormFields, $bEnclose = true)
{
	$db = ffDB_Sql::factory();
	
	$sSQL = str_replace(	"[DATE]", 
							$db->toSql(new ffData(date("%d/%m/%Y"), "Date", "ITA"),
												"Date",
												$bEnclose ),
							$sSQL
						);
	foreach ($FormFields as $key => $FormField)
	{
		if (strpos($sSQL, "[" . $key . "_VALUE]") !== FALSE)
			$sSQL = str_replace(	"[" . $key . "_VALUE]", 
									$db->toSql(	$FormField->value, 
												$FormField->base_type,
												$bEnclose ),
									$sSQL
								);
	}
	reset($FormFields);
	return $sSQL;
}

function ffCommon_url_rewrite_strip_word($testo, $strip_words, $char_sep = '-')
{
	if(is_array($strip_words))
		$testo = preg_replace('/\b(' . implode('|', $strip_words) . ')\b/', '', $testo);
	
	$testo = ffCommon_url_rewrite($testo, $char_sep);
	
	return $testo;

}

function ffCommon_url_rewrite($testo, $char_sep = '-', $remove_hypens = FF_URLREWRITE_REMOVEHYPENS)
{
	if ($remove_hypens)
		$testo = ffCommon_remove_hypens($testo);
	$testo = mb_strtolower($testo);

    $testo = preg_replace('/[^\p{L}0-9\-]+/u', ' ', $testo);
    $testo = trim($testo);
    $testo = preg_replace('/ +/', $char_sep, $testo);
    $testo = preg_replace('/' . preg_quote($char_sep) . '+/', $char_sep, $testo);
	return $testo;
}

function ffCommon_seems_utf8($str) {
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

function ffCommon_utf8_for_xml($string)
{
    return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @since 1.2.1
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function ffCommon_remove_hypens($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	if (ffCommon_seems_utf8($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
		chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
		chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
		chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
		chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
		chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
		chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
		chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
		chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
		chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
		chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
		chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
		chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
		chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
		chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
		chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
		// Decompositions for Latin Extended-B
		chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
		chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '',
		// Vowels with diacritic (Vietnamese)
		// unmarked
		chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
		chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
		// grave accent
		chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
		chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
		chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
		chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
		chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
		chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
		chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
		// hook
		chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
		chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
		chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
		chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
		chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
		chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
		chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
		chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
		chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
		chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
		chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
		chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
		// tilde
		chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
		chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
		chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
		chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
		chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
		chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
		chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
		chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
		// acute accent
		chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
		chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
		chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
		chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
		chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
		chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
		// dot below
		chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
		chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
		chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
		chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
		chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
		chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
		chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
		chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
		chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
		chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
		chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
		chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
		// Vowels with diacritic (Chinese, Hanyu Pinyin)
		chr(201).chr(145) => 'a',
		// macron
		chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
		// acute accent
		chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
		// caron
		chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
		chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
		chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
		chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
		chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
		// grave accent
		chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
		);

		// Used for locale-specific rules
		/*$locale = get_locale();

		if ( 'de_DE' == $locale ) {
			$chars[ chr(195).chr(132) ] = 'Ae';
			$chars[ chr(195).chr(164) ] = 'ae';
			$chars[ chr(195).chr(150) ] = 'Oe';
			$chars[ chr(195).chr(182) ] = 'oe';
			$chars[ chr(195).chr(156) ] = 'Ue';
			$chars[ chr(195).chr(188) ] = 'ue';
			$chars[ chr(195).chr(159) ] = 'ss';
		} elseif ( 'da_DK' === $locale ) {
			$chars[ chr(195).chr(134) ] = 'Ae';
 			$chars[ chr(195).chr(166) ] = 'ae';
			$chars[ chr(195).chr(152) ] = 'Oe';
			$chars[ chr(195).chr(184) ] = 'oe';
			$chars[ chr(195).chr(133) ] = 'Aa';
			$chars[ chr(195).chr(165) ] = 'aa';
		}*/

		$string = strtr($string, $chars);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

function ffCommon_get_param($name)
	{
		if (!isset($_REQUEST[$name]))
			return null;
		else
			return $_REQUEST[$name];
	}

function ffCommon_array_stripslashes(&$item, $key)
	{
		if (is_array($item))
			{
				array_walk($item, "ffCommon_array_stripslashes");
			}
		else
			$item = stripslashes($item);
	}

function ffCommon_get_object_id($object)
	{
		$tmp = "" . $object;
		return substr($tmp, strpos($tmp, "#") + 1);
	}
	
/*function mime_content_type($file)
	{
		$ret = `file -ib "$file"`;
		return trim($ret);
	}*/

/**
 *  Merges two arrays of any dimension
 *
 *  This is the process' core!
 *  Here each array is merged with the current resulting one
 *
 *  @access private
 *  @author Chema Barcala Calveiro <shemari75@mixmail.com>
 *  @param array $array  Resulting array - passed by reference
 *  @param array $array_i Array to be merged - passed by reference
 */
function ffCommon_array_merge_2(&$array, &$array_i)
	{
		// For each element of the array (key => value):
		foreach ($array_i as $k => $v) {
			// If the value itself is an array, the process repeats recursively:
			if (is_array($v))
				{
					if (!isset($array[$k])) 
						{
							$array[$k] = array();
						}
					ffCommon_array_merge_2($array[$k], $v);
		
			// Else, the value is assigned to the current element of the resulting array:
				} 
			else 
				{
					if (isset($array[$k]) && is_array($array[$k]))
						{
							$array[$k][0] = $v;
						} 
					else 
						{
							if (isset($array) && !is_array($array))
								{
									$temp = $array;
									$array = array();
									$array[0] = $temp;
								}
							$array[$k] = $v;
						}
				}
		}
}

/**
 *  Merges any number of arrays of any dimension
 *
 *  The arrays to be merged are passed as arguments to the function,
 *  which uses an external function (array_merge_2) to merge each of them
 *  with the resulting one as it's being constructed
 *
 *  @access public
 *  @author Chema Barcala Calveiro <shemari75@mixmail.com>
 *  @return array Resulting array, once all have been merged
 */

function ffCommon_url_normalize($url, $strip_slashes = false)
{
	$url = preg_replace('/[\s]+/', "-", $url);
	
	do {
		$url = str_replace("--", "-", $url, $count);
	} while ($count > 0);
	
	if ($strip_slashes)
		$url = str_replace("/", "", $url);
	
	$url = preg_replace('/[^\S\.\-\_\/]+/', "", $url);

	return mb_strtolower($url);
}

function ffCommon_url_stripslashes($url)
{
	do {
		$url = str_replace("//", "/", $url, $count);
	} while ($count > 0);

	$url = trim($url, '/');
	$url = "/" . $url;

	return $url;
}

function ffGetFilename($path, $return_name = true)
{
    $file_ext = pathinfo($path, PATHINFO_EXTENSION); 
    $file_basename = basename($path);
    if($file_ext)
        $res = substr($file_basename, 0, strrpos($file_basename, "." . $file_ext));
    else
        $res = $file_basename;
    
    if($return_name)    
    	return $res;
	else
		return $file_ext;
}

function ffCommon_partial_in_array($needle, $haystack)
{
	foreach ($haystack as $key => $value)
	{
		$found = true;
		foreach ($needle as $subkey => $subvalue)
		{
			$val1 = $value[$subkey];
			$val2 = $subvalue;
			
			$found &= ($value[$subkey] == $subvalue);
		}
		reset($needle);
		if ($found)
			return $key;
	}
	return false;
}

function ffCommon_IndexReverseOrder($a, $b)
{
	return ffCommon_IndexOrder($b, $a);
}

function ffCommon_IndexOrder($a, $b)
{
	$ret = null;

	if (is_array($a) && isset($a["index"]))
		$a_index = (int)$a["index"];
	elseif (isset($a->index))
		$a_index = (int)$a->index;
	else
		$a_index = null;

	if (is_array($b) && isset($b["index"]))
		$b_index = (int)$b["index"];
	elseif (isset($b->index))
		$b_index = (int)$b->index;
	else
		$b_index = null;
	
	if($a_index === null && $b_index === null)
	    $ret = 0;
	elseif($a_index === null)
	    $ret = 1;
	elseif($b_index === null)
	    $ret = -1;
	elseif($a_index === $b_index)
	    $ret = 0;
	else 
    	$ret = ($a_index < $b_index) ? -1 : 1;
    	
	if ($ret === 0 && is_array($a) && isset($a["counter"]) && isset($b["counter"]))
    	$ret = ((int)$a["counter"] < (int)$b["counter"]) ? -1 : 1;
	elseif ($ret === 0 && isset($a->counter) && isset($b->counter))
    	$ret = ((int)$a->counter < (int)$b->counter) ? -1 : 1;
    	
	return $ret;
}

function ffCommon_array_natsort($aryOri, $strIndex, $strSortDir = "asc")
{
	//    create our temporary arrays
	$arySort = $aryResult = array();

	//    loop through the array
	foreach ($aryOri as $key => $value)
		//    set up the value in the array
		$arySort[$aryOri[$key][$strIndex]] = $aryOri[$key][$strIndex];
	reset($aryOri);

	//    apply the natural sort
	natsort($arySort);

	//    if the sort type is descending
	if ($strSortDir == "desc")
		//    reverse the array
		arsort($arySort);

	//    loop through the sorted and original data
	foreach ($arySort as $key => $value)
	{
		foreach ($aryOri as $subkey => $subvalue)
		{
			if ($arySort[$key] == $aryOri[$subkey][$strIndex])
				$aryResult[$subkey] = $aryOri[$subkey];
		}
		reset($aryOri);
	}
	reset($arySort);
	//    return the return
	return $aryResult;
}

function ffCommon_charset_encode($string, $charset = null)
{
	if ($string === null || $string === "")
		return "";
	
	if (!is_scalar($string))
		ffErrorHandler::raise("value is not a String", E_USER_ERROR, null, get_defined_vars());

	if ($charset === null)
		$charset = FF_DEFAULT_CHARSET;
	
	if (!mb_check_encoding($string, $charset))
	{
		switch ($charset)
		{
			case "UTF-8":
				$string = utf8_encode($string);
				break;

			default:
				ffErrorHandler::raise($charset . " encoding not implemented yet", E_USER_ERROR, null, get_defined_vars());
		}
	}

	return $string;
}

function ffCommon_charset_decode($string, $charset = null)
{
	if ($charset === null)
		$charset = FF_DEFAULT_CHARSET;
	
	if (mb_check_encoding($string, $charset))
	{
		switch ($charset)
		{
			case "UTF-8":
				$string = utf8_decode($string);
				break;

			default:
				ffErrorHandler::raise($charset . " encoding not implemented yet", E_USER_ERROR, null, get_defined_vars());
		}
	}

	return $string;
}

function ffCommon_specialchars($string, $quote_style = ENT_QUOTES, $charset = null, $double_encode = true, $remove_np = true)
{
	if ($charset === null)
		$charset = FF_DEFAULT_CHARSET;

	$string = ffCommon_charset_encode($string, $charset);

	if ($remove_np)
		return preg_replace("/[\x08\x0B\x0C\x0E-\x1F]/", "", htmlspecialchars($string, $quote_style, $charset, $double_encode));
	else
		return htmlspecialchars($string, $quote_style, $charset, $double_encode);
}

function ffCommon_google_jsonenc($data)
{
	if (ffCommon_google_jsonenc_is_array($data))
	{
		$buffer = ffCommon_google_jsonenc_array($data);
	}
	else
	{
		$buffer = ffCommon_google_jsonenc_object($data);
	}

	return $buffer;
}

function ffCommon_google_jsonenc_array($data)
{
	$buffer = "[";

	foreach ($data as $key => $value)
	{
		if (is_array($value))
		{
			if (ffCommon_google_jsonenc_is_array($value))
			{
				$buffer .= ffCommon_google_jsonenc_array($value);
			}
			else
			{
				$buffer .= ffCommon_google_jsonenc_object($value);
			}
		}
		else
		{
			$buffer .= ffCommon_google_jsonenc_getValue($value);
		}

		$buffer .= ",";
	}
	reset($data);

	$buffer = rtrim(trim($buffer), ",");

	$buffer .= "]";

	return $buffer;
}

function ffCommon_google_jsonenc_object($data)
{
	$buffer = "{";

	foreach ($data as $key => $value)
	{
		$buffer .= $key . ":";

		if (is_array($value))
		{
			if (ffCommon_google_jsonenc_is_array($value))
			{
				$buffer .= ffCommon_google_jsonenc_array($value);
			}
			else
			{
				$buffer .= ffCommon_google_jsonenc_object($value);
			}
		}
		else
		{
			$buffer .= ffCommon_google_jsonenc_getValue($value);
		}

		$buffer .= ",";
	}
	reset($data);

	$buffer = rtrim(trim($buffer), ",");

	$buffer .= "}";

	return $buffer;
}

function ffCommon_google_jsonenc_is_array($data)
{
	$keystring = implode("", array_keys($data));
	return preg_match("/^\d+$/", $keystring);
}

function ffCommon_google_jsonenc_getValue($value)
{
	if (!is_object($value))
	{
		if ($value === null)
			return "null";
		elseif ($value === false)
			return "false";

		return "'" . $value . "'";
	}

	if ($value->ori_value === null)
		return "null";

	if ($value->data_type == "Number")
		return ffCommon_charset_encode($value->getValue(null, FF_SYSTEM_LOCALE), "UTF-8");

	return "'" . ffCommon_charset_encode($value->getValue(null, FF_LOCALE), "UTF-8") . "'";
}

function ffCommon_jsonenc($data, $add_header = false, $add_newline = false)
{
	if($add_header)
		header("Content-type: application/json");

	if (ffCommon_jsonenc_is_array($data))
	{
		$buffer = ffCommon_jsonenc_array($data, $add_newline);
	}
	else
	{
		$buffer = ffCommon_jsonenc_object($data, $add_newline);
	}

	return $buffer;
}

function ffCommon_jsonenc_array($data, $add_newline = false)
{
	if ($add_newline)
		$newline = "\n";
	else
		$newline = "";
	
	$buffer = "[";

	foreach ($data as $key => $value)
	{
		if (is_array($value))
		{
			if (ffCommon_jsonenc_is_array($value))
			{
				$buffer .= ffCommon_jsonenc_array($value, $add_newline);
			}
			else
			{
				$buffer .= ffCommon_jsonenc_object($value, $add_newline);
			}
		}
		else
		{
			$buffer .= ffCommon_jsonenc_getValue($value, $add_newline);
		}

		$buffer .= ",";
	}
	reset($data);

	$buffer = rtrim(trim($buffer), ",");

	$buffer .= "]" . $newline;

	return $buffer;
}

function ffCommon_jsonenc_object($data, $add_newline = false)
{
	if ($add_newline)
		$newline = "\n";
	else
		$newline = "";
	
	$buffer = "{";

    if(is_array($data) && count($data)) {
        foreach ($data as $key => $value) {
            $buffer .= '"' . $key . '" : ';

            if (is_array($value)) {
                if (ffCommon_jsonenc_is_array($value)) {
                    $buffer .= ffCommon_jsonenc_array($value, $add_newline);
                } else {
                    $buffer .= ffCommon_jsonenc_object($value, $add_newline);
                }
            } else {
                $buffer .= ffCommon_jsonenc_getValue($value, $add_newline);
            }

            $buffer .= ",";
        }
    }
	reset($data);

	$buffer = rtrim(trim($buffer), ",");

	$buffer .= "}" . $newline;

	return $buffer;
}

function ffCommon_jsonenc_is_array($data)
{
    if(is_array($data))
    {
        if(!count($data))
            return true;
        else
        {
            $keystring = implode("", array_keys($data));
            return preg_match("/^\d+$/", $keystring);
        }
    }
}

function ffCommon_jsonenc_getValue($value, $add_newline = false)
{
	if ($add_newline)
		$newline = "\n";
	else
		$newline = "";
	
	if (!is_object($value))
	{
		if ($value === null)
			return "null" . $newline;
		elseif ($value === false)
			return "false" . $newline;
		elseif ($value === true)
			return "true" . $newline;

		return '"' . ffCommon_jsonenc_encodeString($value) . '"' . $newline;
	}

	if ($value->ori_value === null)
		return "null" . $newline;

	if ($value->data_type == "Number")
		return ffCommon_charset_encode(ffCommon_jsonenc_encodeString($value->getValue(null, FF_SYSTEM_LOCALE), "UTF-8")) . $newline;

	return "'" . ffCommon_charset_encode(ffCommon_jsonenc_encodeString($value->getValue(null, FF_LOCALE), "UTF-8")) . "'" . $newline;
}

function ffCommon_jsonenc_encodeString($string)
{
	// Escape these characters with a backslash:
	// " \ / \n \r \t \b \f
	$search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"');
	$replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
	$string  = str_replace($search, $replace, $string);

	// Escape certain ASCII characters:
	// 0x08 => \b
	// 0x0c => \f
	$string = str_replace(array(chr(0x08), chr(0x0C), chr(0x1F)), array('\b', '\f', ' '), $string);
	//$string = self::encodeUnicodeString($string);

	return $string;
}

function ffCommon_crossDomains($trustDomains, $add_header = false, $domain = null)
{
	if($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"])
		return true;

	//if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	if (!$domain) {
		$arrDomain = parse_url($_SERVER["HTTP_REFERER"]);
		$domain = $arrDomain["host"];
	}


	foreach ($trustDomains AS $trustDomain) {
		if (strpos($domain, $trustDomain) !== false) {
			if ($add_header) {
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, POST');
			}
			return true;
		}
	}
	//}

	if($add_header) {
		http_response_code(405);
	}
}

if (!function_exists("http_response_code"))
{
	function http_response_code($code = null)
	{
		static $code_sent = null;
		//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
		if ($code !== null)
		{
			$code_sent = $code;
			header(ffGetHTTPStatus($code));
		}
		else
		{
			$code = ($code_sent ? $code_sent : 200);
		}
		return $code;
	}
}

/**
 * ffGetHTTPStatus
 * @param type $code null to get array, code to get the http status'string
 * @return array 
 * @author Alessandro Stucchi
 */
function ffGetHTTPStatus($code = null)
{
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	
	if($code !== null) 
	{
		switch($code) 
		{
			case "100":
				$res = $protocol . " 100 Continue";
				break;
			case "101":
				$res = $protocol . " 101 Switching Protocols";
				break;
			case "200":
				$res = $protocol . " 200 OK";
				break;
			case "201":
				$res = $protocol . " 201 Created";
				break;
			case "202":
				$res = $protocol . " 202 Accepted";
				break;
			case "203":
				$res = $protocol . " 203 Non-Authoritative Information";
				break;
			case "204":
				$res = $protocol . " 204 No Content";
				break;
			case "205":
				$res = $protocol . " 205 Reset Content";
				break;
			case "206":
				$res = $protocol . " 206 Partial Content";
				break;
			case "300":
				$res = $protocol . " 300 Multiple Choices";
				break;
			case "301":
				$res = $protocol . " 301 Moved Permanently";
				break;
			case "302":
				$res = $protocol . " 302 Found";
				break;
			case "303":
				$res = $protocol . " 303 See Other";
				break;
			case "304":
				$res = $protocol . " 304 Not Modified";
				break;
			case "305":
				$res = $protocol . " 305 Use Proxy";
				break;
			case "307":
				$res = $protocol . " 307 Temporary Redirect";
				break;
			case "400":
				$res = $protocol . " 400 Bad Request";
				break;
			case "401":
				$res = $protocol . " 401 Unauthorized";
				break;
			case "402":
				$res = $protocol . " 402 Payment Required";
				break;
			case "403":
				$res = $protocol . " 403 Forbidden";
				break;
			case "404":
				$res = $protocol . " 404 Not Found";
				break;
			case "405":
				$res = $protocol . " 405 Method Not Allowed";
				break;
			case "406":
				$res = $protocol . " 406 Not Acceptable";
				break;
			case "407":
				$res = $protocol . " 407 Proxy Authentication Required";
				break;
			case "408":
				$res = $protocol . " 408 Request Time-out";
				break;
			case "409":
				$res = $protocol . " 409 Conflict";
				break;
			case "410":
				$res = $protocol . " 410 Gone";
				break;
			case "411":
				$res = $protocol . " 411 Length Required";
				break;
			case "412":
				$res = $protocol . " 412 Precondition Failed";
				break;
			case "413":
				$res = $protocol . " 413 Request Entity Too Large";
				break;
			case "414":
				$res = $protocol . " 414 Request-URI Too Large";
				break;
			case "415":
				$res = $protocol . " 415 Unsupported Media Type";
				break;
			case "416":
				$res = $protocol . " 416 Requested range not satisfiable";
				break;
			case "417":
				$res = $protocol . " 417 Expectation Failed";
				break;
			case "500":
				$res = $protocol . " 500 Internal Server Error";
				break;
			case "501":
				$res = $protocol . " 501 Not Implemented";
				break;
			case "502":
				$res = $protocol . " 502 Bad Gateway";
				break;
			case "503":
				$res = $protocol . " 503 Service Unavailable";
				break;
			case "504":
				$res = $protocol . " 504 Gateway Time-out";
				break;
			case "505":
				$res = $protocol . " 505 HTTP Version not supported";
				break;
			default:
				$res = "Unknown";
		}
	} 
	else 
	{
		$res = array(
					array(new ffData("100"), new ffData($protocol . " 100 Continue"))
					, array(new ffData("101"), new ffData($protocol . " 101 Switching Protocols"))
					, array(new ffData("200"), new ffData($protocol . " 200 OK"))
					, array(new ffData("201"), new ffData($protocol . " 201 Created"))
					, array(new ffData("202"), new ffData($protocol . " 202 Accepted"))
					, array(new ffData("203"), new ffData($protocol . " 203 Non-Authoritative Information"))
					, array(new ffData("204"), new ffData($protocol . " 204 No Content"))
					, array(new ffData("205"), new ffData($protocol . " 205 Reset Content"))
					, array(new ffData("206"), new ffData($protocol . " 206 Partial Content"))
					, array(new ffData("300"), new ffData($protocol . " 300 Multiple Choices"))
					, array(new ffData("301"), new ffData($protocol . " 301 Moved Permanently"))
					, array(new ffData("302"), new ffData($protocol . " 302 Found"))
					, array(new ffData("303"), new ffData($protocol . " 303 See Other"))
					, array(new ffData("304"), new ffData($protocol . " 304 Not Modified"))
					, array(new ffData("305"), new ffData($protocol . " 305 Use Proxy"))
					, array(new ffData("307"), new ffData($protocol . " 307 Temporary Redirect"))
					, array(new ffData("400"), new ffData($protocol . " 400 Bad Request"))
					, array(new ffData("401"), new ffData($protocol . " 401 Unauthorized"))
					, array(new ffData("402"), new ffData($protocol . " 402 Payment Required"))
					, array(new ffData("403"), new ffData($protocol . " 403 Forbidden"))
					, array(new ffData("404"), new ffData($protocol . " 404 Not Found"))
					, array(new ffData("405"), new ffData($protocol . " 405 Method Not Allowed"))
					, array(new ffData("406"), new ffData($protocol . " 406 Not Acceptable"))
					, array(new ffData("407"), new ffData($protocol . " 407 Proxy Authentication Required"))
					, array(new ffData("408"), new ffData($protocol . " 408 Request Time-out"))
					, array(new ffData("409"), new ffData($protocol . " 409 Conflict"))
					, array(new ffData("410"), new ffData($protocol . " 410 Gone"))
					, array(new ffData("411"), new ffData($protocol . " 411 Length Required"))
					, array(new ffData("412"), new ffData($protocol . " 412 Precondition Failed"))
					, array(new ffData("413"), new ffData($protocol . " 413 Request Entity Too Large"))
					, array(new ffData("414"), new ffData($protocol . " 414 Request-URI Too Large"))
					, array(new ffData("415"), new ffData($protocol . " 415 Unsupported Media Type"))
					, array(new ffData("416"), new ffData($protocol . " 416 Requested range not satisfiable"))
					, array(new ffData("417"), new ffData($protocol . " 417 Expectation Failed"))
					, array(new ffData("500"), new ffData($protocol . " 500 Internal Server Error"))
					, array(new ffData("501"), new ffData($protocol . " 501 Not Implemented"))
					, array(new ffData("502"), new ffData($protocol . " 502 Bad Gateway"))
					, array(new ffData("503"), new ffData($protocol . " 503 Service Unavailable"))
					, array(new ffData("504"), new ffData($protocol . " 504 Gateway Time-out"))
					, array(new ffData("505"), new ffData($protocol . " 505 HTTP Version not supported"))
				);
	}	
	return $res;
}

function ffCommon_gzUncompress($src, $dst)
{
	$sfp = gzopen($src, "rb");
	$fp = fopen($dst, "w");

	while ($string = gzread($sfp, 4096))
	{
		fwrite($fp, $string, strlen($string));
	}
	gzclose($sfp);
	fclose($fp);
}

function ffCommon_bzUncompress($src, $dst)
{
	$sfp = bzopen($src, "r");
	$fp = fopen($dst, "w");

	while ($string = bzread($sfp, 4096))
	{
		fwrite($fp, $string, strlen($string));
	}
	bzclose($sfp);
	fclose($fp);
}

/**
* Inserts values from $needle after (or before) $key in $subject
* if $key is not found, $needle is appended to $subject using array_merge()
*
* @param $subject
*   array to insert into
* @param $key
*   key of $subject to insert after
* @param $needle
*   array whose values should be inserted
* @param $before
*   insert before the given key. defaults to inserting after
* @return
*   merged array
*/

function ffCommon_arrayInsert($subject, $key, $needle, $before = FALSE){
  $done = FALSE;
  foreach($subject as $subject_key => $subject_val){
    if(!$before){
      $new_array[$subject_key] = $subject_val;
    }
    if($subject_key == $key && !$done){
      foreach($needle as $needle_key => $needle_val) {
        $new_array[$needle_key] = $needle_val;
      }
      $done = TRUE;
    }
    if($before){
      $new_array[$subject_key] = $subject_val;
    }
  }
  if(!$done){
    $new_array = array_merge($subject, $needle);
  }
  return $new_array;
}

function ffHTTP_encoding_isset($encoding, $allowed_encodings = null, $reset = false)
{
	if ($allowed_encodings === null)
	{
		static $_encodings;
		if ($_encodings === null && !$reset)
			$_encodings = array_flip(explode(",", str_replace(" ", "", $_SERVER["HTTP_ACCEPT_ENCODING"])));
		$encodings = $_encodings;
	}
	else
		$encodings = $allowed_encodings;
	
	return isset($encodings[$encoding]);
}

function ffCommon_colNumber2Letter($c){
    $letter = "";
	$c = intval($c);
	if ($c <= 0) 
		$c = 1;
	         
	while($c != 0){
	   $p = ($c - 1) % 26;
	   $c = intval(($c - $p) / 26);
	   $letter = chr(65 + $p) . $letter;
	}
	
	return $letter;
}

function ffCommon_url_change_param($url, $param, $newvalue)
{
	if (strpos($url, "#") !== FALSE)
	{
		$parts = explode("#", $url);
		if (isset($parts[1]))
			$hash = $parts[1];
		else
			$hash = "";
	}
	else
	{
		$hash = null;
		$parts[0] = $url;
	}

	$subparts = explode("?", $parts[0]);
	$host = $subparts[0];
	if (isset($subparts[1]))
		$query = $subparts[1];
	else
		$query = "";

	$newquery = "";
	$changed = false;
	if (strlen($query))
	{
		$query_parts = array();
		foreach (explode("&", trim($query, "&")) as $key => $value)
		{
			$tmp = explode("=", $value);
			if (strtolower($tmp[0]) === strtolower($param))
			{
				$query_parts[$tmp[0]] = $newvalue;
				$changed = true;
			}
			else
				$query_parts[$tmp[0]] = $tmp[1];
		}
	}
	
	if (!$changed)
		$query_parts[$param] = $newvalue;
	
	foreach ($query_parts as $key => $value)
	{
		$newquery .= $key;
		if ($value !== null)
			$newquery .= "=" . $value;
		$newquery .= "&";
	}

	$url = $host;
	
	//if (strlen($newquery))
		$url .= "?" . $newquery;

	if ($hash !== null)
		$url .= "#" . $hash;
	
	return $url;
}

function ffCommon_url_add_param($url, $param, $value = null)
{
	if (is_object($value))
	{
		if ($value instanceof ffData)
			$value = $value->getValue();
		else
			ffErrorHandler::raise ("UNHANDLED DATA TYPE", E_USER_ERROR, NULL, get_defined_vars ());
	}
	
	$url_data = parse_url($url);
	parse_str($url_data["query"], $query_data);
	
	if (is_array($param))
	{
		$query_data = array_merge($query_data, $param);
	}
	else
	{
		$query_data[$param] = $value;
	}
	
	$url_data["query"] = ffCommon_http_build_query($query_data);
	$url = ffCommon_http_build_url($url_data);

	return $url;
}

function ffCommon_url_remove_param($url, $param)
{
	if (strpos($url, "?") === FALSE)
		return $url;
	
	if (strpos($url, "#") !== FALSE)
	{
		$parts = explode("#", $url);
		if (isset($parts[1]))
			$hash = $parts[1];
		else
			$hash = "";
	}
	else
	{
		$hash = null;
		$parts[0] = $url;
	}

	$subparts = explode("?", $parts[0]);
	$host = $subparts[0];
	if (isset($subparts[1]))
		$query = $subparts[1];
	else
		$query = "";

	$newquery = "";
	if (strlen($query))
	{
		foreach (explode("&", trim($query, "&")) as $key => $value)
		{
			$tmp = explode("=", $value);
			if (strtolower($tmp[0]) !== strtolower($param))
			{
				$newquery .= $tmp[0];
				if ($tmp[1] !== null)
					$newquery .= "=" . $tmp[1];
				$newquery .= "&";
			}
		}
	}
	
	$url = $host;
	
	if (strlen($newquery))
		$url .= "?" . $newquery;

	if ($hash !== null)
		$url .= "#" . $hash;
	
	return $url;
}

function ffHTTP_getHeader($hname = "content-type")
{
	$hsent		= false;
	$hvalue		= null;
	$hoption	= null;
	$hoptionval	= null;

	$hlist = headers_list();
	if (is_array($hlist) && count($hlist))
	{
		$hsent = true;
		foreach($hlist as $key => $value)
		{
			$rc = preg_match("/\s*([^:\s]+)\s*:\s*([^;\s]+)(;\s*([^=]+)\s*=(.+))?/", $value, $matches);
			if ($rc && strtolower($matches[1]) == strtolower($hname))
			{
				$hvalue = $matches[2];

				if (count($matches) > 4)
				{
					$hoption = $matches[4];
					$hoptionval = $matches[5];
				}
			}
		}
	}

	if ($hsent)
		return array(
			"value"			=> $hvalue
			, "opt_name"	=> $hoption
			, "opt_value"		=> $hoptionval
		);
	else
		return false;
}

function ffCommon_theme_init($theme = FF_MAIN_THEME)
{
    static $loaded = false;
	if (!$loaded)
	{
	    if(!defined("FF_DEFAULT_THEME"))            define("FF_DEFAULT_THEME", $theme);

		if (
					!is_dir(FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME)
				||	!is_dir(FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME . "/ff")
			)
			ffErrorHandler::raise("FORMS FRAMEWORK: Wrong default theme dir: " . FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME, E_USER_ERROR, null, get_defined_vars());

		// Load other configs..

		// ..theme
		if (@is_file(FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME . "/ff/config.php"))
			require FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME . "/ff/config.php";

        if (@is_file(FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME . "/ff/common.php"))
            require FF_THEME_DISK_PATH . "/" . FF_DEFAULT_THEME . "/ff/common.php";

		$loaded = true;
	}
}

function ffIsset($array, $key)
{
	if (is_array($array) && array_key_exists($key, $array))
		return true;
	else
		return false;
}

function ffUpdateQueryString ($key, $value, $url = null) {
    $arrQuery = array();
    if(!$url)
        $url = $_SERVER["REQUEST_URI"];

    $arrUrl = parse_url($url);
    if($arrUrl["query"]) {
        foreach( explode('&', trim($arrUrl["query"], "&")) as $p ) {
            list($p_key, $p_value) = explode('=', $p);
            if(!preg_match('/[^a-z\-0-9]/i', $p_key)) // TOCHECK, molto probabilmente errato
            	$arrQuery[$p_key] = $p_key . ($p_value ? "=" . $p_value : "");
        }
    }
    
    if($value === false)
        unset($arrQuery[$key]);
    else
        $arrQuery[$key] = $key . ($value ? "=" . $value : "");

    $arrUrl["query"] = implode("&", $arrQuery);
    if($arrUrl["query"])
        $arrUrl["query"] = "?" . $arrUrl["query"];

    $url = implode("", $arrUrl);
    
  return $url;
}

// MANAGE FILES
function ffCommon_manage_files(&$component, $sSQL_Where = "")
{
	$addit_SQL = array();
	$arrFile = array();
	foreach ($component->form_fields as $key => $FormField)
	{
		if ($component->form_fields[$key]->extended_type == "File")
		{
			if ($component->form_fields[$key]->base_type == "Text")
			{
				$storing_path = $component->form_fields[$key]->getFilePath(false);
				
	            if($component->form_fields[$key]->file_full_path)
				{
	                if (
	                    substr(strtolower($component->form_fields[$key]->value->getValue()), 0, 7) != "http://"
	                    && substr(strtolower($component->form_fields[$key]->value->getValue()), 0, 8) != "https://"
	                    && substr($component->form_fields[$key]->value->getValue(), 0, 2) != "//"
	                )
					{
                        if($component->form_fields[$key]->file_tmpname)
						{
                            $arrFileValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->getValue());
							if(is_array($arrFileValue) && count($arrFileValue))
							{
								foreach($arrFileValue AS $file_key => $file_value)
								{
									if(strlen($file_value))
									{
										$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
										$arrFileValue[$file_key] = str_replace($component->form_fields[$key]->getFileBasePath(), "", $component->form_fields[$key]->getFileFullPath($real_file_value, false));
									}
								}
							}
							$component->form_fields[$key]->value->setValue(implode(",", $arrFileValue));
						}
						
						if($sSQL_Where)
						{
							if ($component->form_fields[$key]->base_type == "Text" && $component->form_fields[$key]->crypt_method !== null)
			                {
			                    switch ($component->form_fields[$key]->crypt_method)
			                    {
			                        case "MD5":
			                            $tmpval = new ffData(md5($component->form_fields[$key]->value->getValue($component->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
			                            break;
			                        case "mysql_password":
			                            $tmpval = new ffData($component->db[0]->mysqlPassword($component->form_fields[$key]->value->getValue($component->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
			                            break;
			                        case "mysql_oldpassword":
			                            $tmpval = new ffData($component->db[0]->mysqlOldPassword($component->form_fields[$key]->value->getValue($component->form_fields[$key]->base_type, FF_SYSTEM_LOCALE)));
			                            break;
			                        default:
			                            ffErrorHandler::raise("Crypt method not supported!", E_USER_ERROR, $component, get_defined_vars());
			                    }
			                }
			                else
			                    $tmpval = $component->form_fields[$key]->value;

							 if (!$component->skip_action && $component->form_fields[$key]->store_in_db)
	                            $addit_SQL[] = "UPDATE `" . $component->src_table . "` SET `" . $component->form_fields[$key]->get_data_source(false) . "` = " . $component->db[0]->toSql($tmpval, $component->form_fields[$key]->base_type) . " WHERE " . $sSQL_Where;
						}
	                }
	            }

				if (strlen($storing_path))
				{
					if (strlen($component->form_fields[$key]->file_tmpname))
					{
						$arrFileTmpValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->file_tmpname);								
						$arrFileValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->getValue());
						if($component->form_fields[$key]->default_value !== null && ($component->frmAction == "insert" || !$component->form_fields[$key]->data_type))
							$arrFileOriValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->default_value->getValue());
						else
							$arrFileOriValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->value_ori->getValue());

						$arrFileDelValue = array_diff($arrFileOriValue, $arrFileValue);
						
						if(is_array($arrFileDelValue) && count($arrFileDelValue))
						{
							foreach($arrFileDelValue AS $file_key => $file_value)
							{
								if(strlen($file_value))
								{
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["count"]++;
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["del"]++;
								}
							}
						}

						if ($component->form_fields[$key]->file_make_dir)
							@mkdir($storing_path, $component->form_fields[$key]->file_chmod, true);

						if(is_array($arrFileTmpValue) && count($arrFileTmpValue))
						{
							foreach($arrFileTmpValue AS $file_key => $file_value)
							{
								if(strlen($file_value))
								{
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
									$tmp_filename = $component->form_fields[$key]->getFileFullPath($real_file_value, false);
									if(!array_key_exists($tmp_filename, $arrFile))
									{
										@rename(
												$component->form_fields[$key]->getFileFullPath($arrFileTmpValue[$file_key])
												, $tmp_filename
											);

										if (!is_file($tmp_filename))
											ffErrorHandler::raise("UPLOAD ERROR: " . $tmp_filename, E_USER_ERROR, $component, get_defined_vars());

										@chmod($tmp_filename, 0777);
									}												
									$arrFile[$tmp_filename]["count"]++;
									if(!$component->form_fields[$key]->file_multi)
										break;
								}
							}
						}
					}
					elseif (!strlen($component->form_fields[$key]->getValue()) && strlen($component->form_fields[$key]->value_ori->getValue()))
					{
						$arrFileOriValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->value_ori->getValue());
						if(is_array($arrFileOriValue) && count($arrFileOriValue))
						{
							foreach($arrFileOriValue AS $file_key => $file_value)
							{
								if(strlen($file_value))
								{
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["count"]++;
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["del"]++;
								}
							}
						}
					} 
					else 
					{
						$arrFileValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->getValue());
						if($component->form_fields[$key]->default_value !== null && ($component->frmAction == "insert" || !$component->form_fields[$key]->data_type))
							$arrFileOriValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->default_value->getValue());
						else
							$arrFileOriValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->value_ori->getValue());

						$arrFileDelValue = array_diff($arrFileOriValue, $arrFileValue);
												
						if(is_array($arrFileDelValue) && count($arrFileDelValue)) {
							foreach($arrFileDelValue AS $file_key => $file_value) {
								if(strlen($file_value)) {
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["count"]++;
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["del"]++;
									
									unset($arrFileValue[$file_key]);
								}
							}
						}						
						if(is_array($arrFileValue) && count($arrFileValue)) {
							foreach($arrFileValue AS $file_key => $file_value) {	
								if(strlen($file_value)) {
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);
									$arrFile[$component->form_fields[$key]->getFileFullPath($real_file_value, false)]["count"]++;
								}
							}
						}					
					} 
				}
				else
				{
					$arrFileTmpValue = explode($component->form_fields[$key]->file_separator, $component->form_fields[$key]->file_tmpname);
					if(is_array($arrFileTmpValue) && count($arrFileTmpValue))
					{
						foreach($arrFileTmpValue AS $file_key => $file_value)
						{
							if(strlen($file_value))
								@unlink($component->form_fields[$key]->getFileFullPath($file_value));
						}
					}
				}
			}
			else if ($component->form_fields[$key]->base_type == "Binary")
			{
			}
		}
	}
	reset($component->form_fields);

	if(is_array($arrFile) && count($arrFile)) 
	{
		foreach($arrFile AS $file_path => $file_value)
		{
			if(isset($file_value["del"]) && $file_value["del"] == $file_value["count"])
				@unlink($file_path);
		}
	}
	
	if(is_array($addit_SQL) && count($addit_SQL))
	{
		foreach($addit_SQL AS $addit_SQL_value)
		{
			$component->db[0]->execute($addit_SQL_value);
		}
	}
}

// Based on http://stackoverflow.com/questions/5612656/generating-unique-random-numbers-within-a-range-php
// by edduvs
function ffUniqueRandomNumbers($min, $max, $quantity)
{
	$tot = ($max - $min + 1);
	if ($quantity > $tot)
		$quantity = $tot;
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
}

function ffParamsMerge($defaults, $values)
{
	return array_merge((is_array($defaults) ? $defaults : array()), (is_array($values) ? $values : array()));
}

function ff_datetime_add_month($date)
{
	$tmp = clone $date;
	$tmp->add(new DateInterval("P1M"));
	
	$tmp = DateTime::createFromFormat("Y-m-d H:i:s", date($tmp->format("Y") . "-" . $tmp->format("m") . "-1 00:00:00"));
	return $tmp;
}

function ff_getDecryptedField(ffDB_Sql &$db, $field, $type)
{
	$data = $db->record[$field];
	if (MOD_SEC_CRYPT)
		$data = mod_sec_decrypt_string($data);
	
	return new ffData($data, $type, $db->locale);
}

function ff_getThemeDir($theme)
{
	if ($theme === "responsive" || $theme === "restricted")
		return __TOP_DIR__;
	else
		return __PRJ_DIR__;
}
function ff_getModuleDir($module)
{
    if ($module === "restricted" || $module === "security")
        return __FF_DIR__ . CM_MODULES_PATH . "/" . $module;
    else
        return CM_MODULES_ROOT . "/" . $module;
}
function ff_getThemePath($theme)
{
	if ($theme === "responsive" || $theme === "restricted")
	{
	    if(FF_DISK_PATH != __TOP_DIR__)
	    {
            $a = FF_DISK_PATH;
            $b = substr($a, 0, strlen(FF_SITE_PATH) * -1);
            $c = substr(__TOP_DIR__, strlen($b));
        } else
            $c = FF_SITE_PATH;

		return $c . FF_THEME_DIR;
	}
	else
		return FF_THEME_SITE_PATH;
}

function ff_getAbsDir($path, $return_abs = true)
{
    if (__FF_DIR__ != __PRJ_DIR__
        && (strpos($path, "/themes/library") === 0
            || strpos($path, "/themes/restricted") === 0
            || strpos($path, "/themes/responsive") === 0
            || strpos($path, "/modules") === 0
        )
    )
        if($return_abs)
            return __FF_DIR__;
        else
            return true;
    else
        if($return_abs)
            return FF_DISK_PATH;
        else
            return false;
}

if(!function_exists('hash_equals'))
{
    function hash_equals($str1, $str2)
    {
        if(strlen($str1) != strlen($str2))
        {
            return false;
        }
        else
        {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--)
            {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}


/**
 * URL constants as defined in the PHP Manual under "Constants usable with
 * http_build_url()".
 *
 * @see http://us2.php.net/manual/en/http.constants.php#http.constants.url
 * @see https://github.com/jakeasmith/http_build_url
 */
if (!defined('HTTP_URL_REPLACE')) {
    define('HTTP_URL_REPLACE', 1);
}
if (!defined('HTTP_URL_JOIN_PATH')) {
    define('HTTP_URL_JOIN_PATH', 2);
}
if (!defined('HTTP_URL_JOIN_QUERY')) {
    define('HTTP_URL_JOIN_QUERY', 4);
}
if (!defined('HTTP_URL_STRIP_USER')) {
    define('HTTP_URL_STRIP_USER', 8);
}
if (!defined('HTTP_URL_STRIP_PASS')) {
    define('HTTP_URL_STRIP_PASS', 16);
}
if (!defined('HTTP_URL_STRIP_AUTH')) {
    define('HTTP_URL_STRIP_AUTH', 32);
}
if (!defined('HTTP_URL_STRIP_PORT')) {
    define('HTTP_URL_STRIP_PORT', 64);
}
if (!defined('HTTP_URL_STRIP_PATH')) {
    define('HTTP_URL_STRIP_PATH', 128);
}
if (!defined('HTTP_URL_STRIP_QUERY')) {
    define('HTTP_URL_STRIP_QUERY', 256);
}
if (!defined('HTTP_URL_STRIP_FRAGMENT')) {
    define('HTTP_URL_STRIP_FRAGMENT', 512);
}
if (!defined('HTTP_URL_STRIP_ALL')) {
    define('HTTP_URL_STRIP_ALL', 1024);
}


/**
 * Build a URL.
 *
 * The parts of the second URL will be merged into the first according to
 * the flags argument.
 *
 * @param mixed $url (part(s) of) an URL in form of a string or
 * associative array like parse_url() returns
 * @param mixed $parts same as the first argument
 * @param int $flags a bitmask of binary or'ed HTTP_URL constants;
 * HTTP_URL_REPLACE is the default
 * @param array $new_url if set, it will be filled with the parts of the
 * composed url like parse_url() would return
 * @return string
 */
function ffCommon_http_build_url($url, $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = array())
{
	is_array($url) || $url = parse_url($url);
	is_array($parts) || $parts = parse_url($parts);

	isset($url['query']) && is_string($url['query']) || $url['query'] = null;
	isset($parts['query']) && is_string($parts['query']) || $parts['query'] = null;

	$keys = array(
		'user',
		'pass',
		'port',
		'path',
		'query',
		'fragment'
	);

	// HTTP_URL_STRIP_ALL and HTTP_URL_STRIP_AUTH cover several other flags.
	if ($flags & HTTP_URL_STRIP_ALL) {
		$flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_PATH | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT;
	} elseif ($flags & HTTP_URL_STRIP_AUTH) {
		$flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS;
	}

	// Schema and host are alwasy replaced
	foreach (array(
		'scheme',
		'host'
	) as $part) {
		if (isset($parts[$part])) {
			$url[$part] = $parts[$part];
		}
	}

	if ($flags & HTTP_URL_REPLACE) {
		foreach ($keys as $key) {
			if (isset($parts[$key])) {
				$url[$key] = $parts[$key];
			}
		}
	} else {
		if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
			if (isset($url['path']) && substr($parts['path'], 0, 1) !== '/') {
				$url['path'] = rtrim(str_replace(basename($url['path']), '', $url['path']), '/') . '/' . ltrim($parts['path'], '/');
			} else {
				$url['path'] = $parts['path'];
			}
		}

		if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
			if (isset($url['query'])) {
				parse_str($url['query'], $url_query);
				parse_str($parts['query'], $parts_query);

				$url['query'] = ffCommon_http_build_query(array_replace_recursive($url_query, $parts_query));
			} else {
				$url['query'] = $parts['query'];
			}
		}
	}

	foreach ($keys as $key) {
		$strip = 'HTTP_URL_STRIP_' . strtoupper($key);
		if ($flags & constant($strip)) {
			unset($url[$key]);
		}
	}

	$parsed_string = '';

	if (isset($url['scheme'])) {
		$parsed_string .= $url['scheme'] . '://';
	}

	if (isset($url['user'])) {
		$parsed_string .= $url['user'];

		if (isset($url['pass'])) {
			$parsed_string .= ':' . $url['pass'];
		}

		$parsed_string .= '@';
	}

	if (isset($url['host'])) {
		$parsed_string .= $url['host'];
	}

	if (isset($url['port'])) {
		$parsed_string .= ':' . $url['port'];
	}

	if (!empty($url['path'])) {
		$parsed_string .= $url['path'];
	} else {
		$parsed_string .= '/';
	}

	if (isset($url['query'])) {
		$parsed_string .= '?' . $url['query'];
	}

	if (isset($url['fragment'])) {
		$parsed_string .= '#' . $url['fragment'];
	}

	$new_url = $url;

	return $parsed_string;
}

function ffCommon_http_build_query($parts, $encode = true)
{
	$query = "";
	foreach ($parts as $key => $value)
	{
		$query .= $key . "=" . ($encode ? rawurlencode($value) : $value) . "&";
	}
	
	return rtrim($query, "&");
}

function ffArrIsset()
{
	$nargs = func_num_args();
	if ($nargs < 2)
		ErrorHandler::raise ("Wrong arrIsset Usage, minimum 2 params required", E_USER_ERROR, NULL, get_defined_vars ());
	
	$args = func_get_args();
	
	$parray = $args[0];
	for ($i = 0; $i + 1 < $nargs; $i++)
	{
		if (!is_array($parray) || !array_key_exists($args[$i + 1], $parray))
			return false;
		$parray = $parray[$args[$i + 1]];
	}
	
	return true;
}

function ffCommon_start_abs_path($n = 1) {
    return substr(FF_DISK_PATH, 0, $n);
}