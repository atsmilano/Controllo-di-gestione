<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN EXTRAS (activecomboex)
//			   by Samuele Diella
// ----------------------------------------

// impedisce a google d'indicizzare il servizio
if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false)
{
	die('<html>
<head>
<title>no resource</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noindex,nofollow" />
</head>
</html>');
}

// impedisce l'accesso diretto ai browser
if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	$arrUrl = parse_url($_SERVER["REQUEST_URI"]);
	ffRedirect(($arrUrl["path"] == "/parsedata" ? "/" : str_replace("/parsedata", "", $arrUrl["path"])), 301); // TO FIX
}

//require_once("../../../../../../ff/main.php");
//require_once("../../../../../../modules/security/common.php");

//if ($plgCfg_ActiveComboEX_UseOwnSession)
if (isset($_POST[session_name()]))
	session_id($_POST[session_name()]);
elseif (isset($_GET[session_name()]))
	session_id($_GET[session_name()]);
elseif (isset($_COOKIE[session_name()]))
	session_id($_COOKIE[session_name()]);
@session_start();
//else
//	mod_security_check_session();

$php_array = array();

$father_value	= $_REQUEST["father_value"];
$data_src		= $_REQUEST["data_src"];
$selected_value = $_REQUEST["sel_val"];

$ff = get_session("ff");
$actex_sql					= $ff["activecomboex"][$data_src]["sql"];
$actex_field				= $ff["activecomboex"][$data_src]["field"];
$actex_operation			= $ff["activecomboex"][$data_src]["operation"];
$actex_skip_empty			= $ff["activecomboex"][$data_src]["skip_empty"];
$actex_group				= $ff["activecomboex"][$data_src]["group"];
$actex_attr					= $ff["activecomboex"][$data_src]["attr"];
$actex_main_db				= $ff["activecomboex"][$data_src]["main_db"];
$hide_result_on_query_empty = $ff["activecomboex"][$data_src]["hide_result_on_query_empty"];
$actex_preserve_field		= $ff["activecomboex"][$data_src]["preserve_field"];
//$actex_preserve_having		= $ff["activecomboex"][$data_src]["preserve_having"];

if(!strlen(trim($actex_sql)))
{
	/*
	// in assenza di sql è evidentemente un accesso tramite sessione scaduta
	// per cui estromette la pagina dall'indice di google
	if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false)
	{
		die('<html>
<head>
<title>no resource</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noindex,nofollow" />
</head>
</html>');
	}*/
	
	// non dovrebbe mai essere vuota l'SQL
	ffErrorHandler::raise("debug", E_USER_ERROR, null, get_defined_vars());
}

if(!strlen($father_value) && $hide_result_on_query_empty)
	die(cm::jsonParse(array()));

//$bFindWhereTag = preg_match("/\[WHERE\]/", $actex_sql);
$bFindWhereOptions = preg_match("/(\[AND\]|\[OR\])/", $actex_sql);

//$bFindHavingTag = preg_match("/\[HAVING\]/", $actex_sql);
$bFindHavingOptions = preg_match("/(\[HAVING_AND\]|\[HAVING_OR\])/", $actex_sql);

if ($actex_main_db)
	$db = mod_security_get_main_db();
else
	$db = ffDB_Sql::factory();

$sSQL = $actex_sql;
$sSqlWhere = "";
$sSqlHaving = "";

if($father_value == "null")
	$father_value = "";

if ($actex_preserve_field && $selected_value !== "")
{
	$tmp = "";
	if (strpos($actex_preserve_field, ".") === false && strpos($actex_preserve_field, "`") === false)
		$tmp .= "`" . $actex_preserve_field . "`";
	else
		$tmp .= $actex_preserve_field;
	$tmp .= " = " . $db->toSql($selected_value) . " ";

	$sSqlHaving .= $tmp;
	$sSqlWhere .= $tmp;
}

if (strlen($actex_field) && (strlen($father_value) || !$actex_skip_empty))
{
	if (strlen($sSqlWhere))
	{
		$sSqlWhere = "(" . $sSqlWhere . " OR ";
		$sSqlHaving = "(" . $sSqlHaving . " OR ";
	}

	switch($actex_operation)
	{
		case "IN":
			if(strlen($father_value)) 
			{
				$sSqlWhere .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
				$sSqlHaving .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
			} 
			else 
			{
				$sSqlWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
				$sSqlHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
			}
			break;

		case "LIKE":
			$sSqlWhere .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
			$sSqlHaving .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
			break;
		case "<>":
			$sSqlWhere .= " $actex_field <> " . $db->toSql(new ffData($father_value));
			$sSqlHaving .= " $actex_field <> " . $db->toSql(new ffData($father_value));
			break;
		case "=":
		default:
			$sSqlWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
			$sSqlHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
	}

	if (strpos($sSqlWhere, "(") === 0)
	{
		$sSqlWhere .= ")";
		$sSqlHaving .= ")";
	}
}
	
if (strlen($sSqlWhere))
{
	if (!$bFindWhereOptions)
		$sSqlWhere = " WHERE " . $sSqlWhere;
	if (!$bFindHavingOptions)
		$sSqlHaving = " HAVING " . $sSqlHaving;

	$sSQL = str_replace("[WHERE]", $sSqlWhere, $sSQL);
	$sSQL = str_replace("[AND]", "AND", $sSQL);
	$sSQL = str_replace("[OR]", "OR", $sSQL);
	$sSQL = str_replace("[HAVING]", $sSqlHaving, $sSQL);
	$sSQL = str_replace("[HAVING_AND]", "AND", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "OR", $sSQL);
}
else
{
	$sSQL = str_replace("[WHERE]", "", $sSQL);
	$sSQL = str_replace("[AND]", "", $sSQL);
	$sSQL = str_replace("[OR]", "", $sSQL);
	$sSQL = str_replace("[HAVING]", "", $sSQL); 
	$sSQL = str_replace("[HAVING_AND]", "", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "", $sSQL);
}
if(preg_match("/(\[COLON\])/", $sSQL))
	$sSQL = str_replace("[ORDER]", " ORDER BY ", $sSQL); 
else
	$sSQL = str_replace("[ORDER]", "", $sSQL); 

$sSQL = str_replace("[COLON]", "", $sSQL);
$sSQL = str_replace("[LIMIT]", "", $sSQL);

$sSQL = str_replace("[FATHER_VALUE]", $db->toSql($father_value), $sSQL); 

if (is_array($_REQUEST["ffActex_parent_data"]) && count($_REQUEST["ffActex_parent_data"]))
{
	foreach ($_REQUEST["ffActex_parent_data"] as $key => $value)
	{
		if (preg_match("/(^[^_]+_recordset)\[[^\]]+\](\[[^\]]+\])/", rawurldecode($key), $matches))
		{
			$sSQL = str_replace("[" . $matches[1] . $matches[2] . "_VALUE]", $db->toSql($value), $sSQL); 
		}
		else
			$sSQL = str_replace("[" . $key . "_VALUE]", $db->toSql($value), $sSQL); 
	}
	reset($_REQUEST["ffActex_parent_data"]);
}

$db->query($sSQL);
$i = -1;
if ($db->nextRecord())
{
	do
	{
		$i++;
		$php_array[$i]["value"] = ffCommon_charset_encode($db->getField($db->fields_names[0], "Text", true));
		$php_array[$i]["desc"] = ffCommon_charset_encode($db->getField($db->fields_names[1], "Text", true));
		if($actex_group && array_search($actex_group, $db->fields_names))
		{
			$php_array[$i]["group"] = ffCommon_charset_encode($db->getField($actex_group, "Text", true));
		}
		if(is_array($actex_attr) && count($actex_attr)) 
		{
			foreach($actex_attr AS $actex_attr_key => $actex_attr_value) 
			{
				if(is_array($actex_attr_value)) 
				{
					if(strlen($actex_attr_value["field"]) && array_search($actex_attr_value["field"], $db->fields_names))
						$php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value["field"], "Text", true));

					$php_array[$i]["attr"][$actex_attr_key] = $actex_attr_value["prefix"] . $php_array[$i]["attr"][$actex_attr_key] . $actex_attr_value["postfix"];
				} 
				else if(strlen($actex_attr_value) && array_search($actex_attr_value, $db->fields_names)) 
				{
					$php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value, "Text", true));
				}
			}
		}
		//$php_array[$i]["value"] = ffCommon_charset_encode($db->getResult(NULL, 0)->getValue());
		//$php_array[$i]["desc"] = ffCommon_charset_encode($db->getResult(NULL, 1)->getValue());
	}
	while ($db->nextRecord());
}

cm::jsonParse(array(
		"success" => true
		, "widget" => array(
			"actex" => array(
				"D$data_src" => (strlen($father_value) ? array("F$father_value" => $php_array) : $php_array)
			)
		)
	)
);
exit;
/*
header("Content-type: application/json; charset=utf-8");
die(json_encode($php_array));*/