<?php
// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN EXTRAS (actex)
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
	ffRedirect(($arrUrl["path"] == "/actexparse" ? "/" : str_replace("/actexparse", "", $arrUrl["path"])), 301); // TO FIX
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

$php_array = array();

$father_value	= $_REQUEST["father_value"];
$data_src		= $_REQUEST["data_src"];
$selected_value = $_REQUEST["sel_val"];
$search_value = str_replace("%", "\%", $search_value);
$search_value = str_replace(" ", "%", $search_value);
$search_value = str_replace("*", "%", $search_value);


$ff = get_session("ff");
$actex_sql					= $ff["actex"][$data_src]["sql"];
$actex_field				= $ff["actex"][$data_src]["field"];
$actex_having_field			= ($ff["actex"][$data_src]["field_having"]
								? $ff["actex"][$data_src]["field_having"]
								: $actex_field
							);
$actex_operation			= $ff["actex"][$data_src]["operation"];
$actex_skip_empty			= $ff["actex"][$data_src]["skip_empty"];
$actex_group				= $ff["actex"][$data_src]["group"];
$actex_attr					= $ff["actex"][$data_src]["attr"];
$actex_main_db				= $ff["actex"][$data_src]["main_db"];
$hide_result_on_query_empty = $ff["actex"][$data_src]["hide_result_on_query_empty"];
$actex_preserve_field		= $ff["actex"][$data_src]["preserve_field"];
$limit						= $ff["actex"][$data_src]["limit"];
$crypt						= $ff["actex"][$data_src]["crypt"];
$crypt_modsec				= $ff["actex"][$data_src]["crypt_modsec"];
$crypt_concat				= $ff["actex"][$data_src]["crypt_concat"];
$multi_crypt				= $ff["actex"][$data_src]["multi_crypt"];
$multi_crypt_modsec			= $ff["actex"][$data_src]["multi_crypt_modsec"];
$multi_crypt_concat			= $ff["actex"][$data_src]["multi_crypt_concat"];


//$actex_preserve_having		= $ff["actex"][$data_src]["preserve_having"];

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
	ffErrorHandler::raise("debug empty query", E_USER_ERROR, null, get_defined_vars());
}

if(strlen($father_value) && $hide_result_on_query_empty) {
	cm::jsonParse(array(
			"success" => true
		, "widget" => array(
				"actex" => array(
					"D$data_src" => array()
				)
			)
		)
	);
	exit;
}

//$bFindWhereTag = preg_match("/\[WHERE\]/", $actex_sql);
$bFindWhereOptions = preg_match("/(\[AND\]|\[OR\])/", $actex_sql);

//$bFindHavingTag = preg_match("/\[HAVING\]/", $actex_sql);
$bFindHavingOptions = preg_match("/(\[HAVING_AND\]|\[HAVING_OR\])/", $actex_sql);

$db = ffDB_Sql::factory();

$sSQL = $actex_sql;
$sSqlWhere = "";
$sSqlHaving = "";
$relevance = array();
$relevance_search = array();
if($search_value)
	$relevance_search = explode("%", $search_value);

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
				$sSqlWhere .= " $actex_field IN(" . $db->toSql(new ffData($father_value), "Text", false) . ")"; 
				$sSqlHaving .= "$actex_having_field IN(" . $db->toSql(new ffData($father_value), "Text", false) . ")"; 
			} 
			else 
			{
				$sSqlWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
				$sSqlHaving .= " $actex_having_field = " . $db->toSql(new ffData($father_value));
			}
			break;
		case "FIND_IN_SET":
			if(strlen($father_value)) 
			{
				$sSqlWhere .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
				$sSqlHaving .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_having_field)"; 
			} 
			else 
			{
				$sSqlWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
				$sSqlHaving .= " $actex_having_field = " . $db->toSql(new ffData($father_value));
			}
			break;
		case "LIKE":
			$sSqlWhere .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
			$sSqlHaving .= " $actex_having_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
			break;
		case "<>":
			$sSqlWhere .= " $actex_field <> " . $db->toSql(new ffData($father_value));
			$sSqlHaving .= " $actex_having_field <> " . $db->toSql(new ffData($father_value));
			break;
		case "=":
		default:
			$sSqlWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
			$sSqlHaving .= " $actex_having_field = " . $db->toSql(new ffData($father_value));
	}

	if (strpos($sSqlWhere, "(") === 0)
	{
		$sSqlWhere .= ")";
		$sSqlHaving .= ")";
	}
	
	if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $actex_having_field . ") = 1, 0, 1)";
        }
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

if(count($relevance)) {
    $sSQL = str_replace("[ORDER]", " ORDER BY " . implode(", ", $relevance), $sSQL);
    $sSQL = str_replace("[COLON]", ", ", $sSQL);
} else {
	if(preg_match("/(\[COLON\])/", $sSQL))
		$sSQL = str_replace("[ORDER]", " ORDER BY ", $sSQL); 
	else
		$sSQL = str_replace("[ORDER]", "", $sSQL); 

    $sSQL = str_replace("[COLON]", "", $sSQL);
}

if($limit > 0)
	$sSQL = str_replace("[LIMIT]", " LIMIT " . $limit, $sSQL);
else
	$sSQL = str_replace("[LIMIT]", "", $sSQL);

$sSQL = str_replace("[FATHER_VALUE]", $db->toSql($father_value), $sSQL); 

if (is_array($_REQUEST["ffActex_parent_data"]) && count($_REQUEST["ffActex_parent_data"]))
{
	foreach ($_REQUEST["ffActex_parent_data"] as $key => $value)
	{
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
		if($actex_group && $db->record[$actex_group])
		{
			$php_array[$i]["group"] = ffCommon_charset_encode($db->getField($actex_group, "Text", true));
		}
		if(is_array($actex_attr) && count($actex_attr)) 
		{
			foreach($actex_attr AS $actex_attr_key => $actex_attr_value) 
			{
				if(is_array($actex_attr_value)) 
				{
					if(strlen($actex_attr_value["field"]) && $db->record[$actex_attr_value["field"]])
						$php_array[$i][$actex_attr_value["field"]] = ffCommon_charset_encode($db->getField($actex_attr_value["field"], "Text", true));

					$php_array[$i][$actex_attr_value["field"]] = $actex_attr_value["prefix"] . $php_array[$i][$actex_attr_value["field"]] . $actex_attr_value["postfix"];
				} 
				else if(strlen($actex_attr_value) && $db->record[$actex_attr_value]) 
				{
					$php_array[$i][$actex_attr_value] = ffCommon_charset_encode($db->getField($actex_attr_value, "Text", true));
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