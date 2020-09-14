<?php

// ----------------------------------------
//  		FRAMEWORK FORMS vAlpha
//		      PLUGIN EXTRAS (autocomplete)
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
	ffRedirect(($arrUrl["path"] == "/aparsedata" ? "/" : str_replace("/aparsedata", "", $arrUrl["path"])), 301); // TO FIX
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

$father_value    = $_REQUEST["fv"];

$search_value = $_REQUEST["term"];
$search_value = str_replace("%", "\%", $search_value);
$search_value = str_replace(" ", "%", $search_value);
$search_value = str_replace("*", "%", $search_value);

$data_src = $_REQUEST["data_src"];

$ff = get_session("ff");
$actex_sql					= $ff["autocomplete"][$data_src]["sql"];
$actex_main_db				= $ff["autocomplete"][$data_src]["main_db"];
$hide_result_on_query_empty = $ff["autocomplete"][$data_src]["hide_result_on_query_empty"];
$actex_field                = $ff["autocomplete"][$data_src]["field"];
$image_field				= $ff["autocomplete"][$data_src]["image_field"];

$compare					= $ff["autocomplete"][$data_src]["compare"];
$compare_having				= $ff["autocomplete"][$data_src]["compare_having"];
$operation					= $ff["autocomplete"][$data_src]["operation"];
$limit						= $ff["autocomplete"][$data_src]["limit"];

if(!$operation)
	$operation = "LIKE [%[VALUE]%]";
	
//$actex_sql = get_session("autocomplete_sql_" . $data_src);
//$actex_main_db = get_session("autocomplete_main_db_" . $data_src);

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

if (!strlen($search_value) && $hide_result_on_query_empty) 
	die(cm::jsonParse(array()));

$strCompareWhere = "";
$strCompareHaving = "";
$sSqlWhere = "";
$relevance = array();
$relevance_search = array();
if($search_value)
	$relevance_search = explode("%", $search_value);

if ($actex_main_db)
	$db = mod_security_get_main_db();
else
	$db = ffDB_Sql::factory();

if ($operation && strpos($operation, "[VALUE]") !== false)
{
	$strOperation = " " . str_replace("[VALUE]", $db->toSql(new ffData($search_value), "Text", false), $operation) . " ";
	if (strpos($operation, "[") !== false && strpos($operation, "]") !== false)
	{
		$strOperation = str_replace("[", "'", $strOperation);
		$strOperation = str_replace("]", "'", $strOperation);
	} 
	else 
	{
		$strOperation = "";
	}
}

if (!strlen($strOperation)) 
{
	$strOperation = " LIKE '%" . $db->toSql(new ffData($search_value), "Text", false) . "%' COLLATE utf8_general_ci";
}

if (is_array($compare)) 
{
	foreach ($compare AS $compare_value) 
	{
		if (!strlen($compare_value))
			continue;

		if (strlen($strCompareWhere))
			$strCompareWhere .= " OR ";

		$strCompareWhere .= $compare_value . $strOperation;

        if(count($relevance_search)) {
            foreach($relevance_search AS $relevance_term) {
                $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_value . ") = 1, 0, 1)";
            }
        }
	}
} 
elseif (strlen($compare)) 
{
	$strCompareWhere .= $compare . $strOperation;
    if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare . ") = 1, 0, 1)";
        }
    }
}

if (is_array($compare_having)) 
{
	foreach ($compare_having AS $compare_value) 
	{
		if (!strlen($compare_value))
			continue;

		if (strlen($strCompareHaving))
			$strCompareHaving .= " OR ";

		$strCompareHaving .= $compare_value . $strOperation;

        if(count($relevance_search)) {
            foreach($relevance_search AS $relevance_term) {
                $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_value . ") = 1, 0, 1)";
            }
        }
    }
} 
elseif (strlen($compare_having)) 
{
	$strCompareHaving .= $compare_having . $strOperation;

    if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_having . ") = 1, 0, 1)";
        }
    }
}

if (!strlen($strCompareHaving) && !strlen($strCompareWhere)) 
{
	$wizard_field = substr($actex_sql, strpos(strtoupper($actex_sql), "SELECT") + 7, strrpos(strtoupper($actex_sql), "FROM") - (strpos(strtoupper($actex_sql), "SELECT") + 7));

	$arrWizardField = explode(" AS ", $wizard_field);
	if (is_array($arrWizardField) && count($arrWizardField)) 
	{
		$first = true;
		foreach ($arrWizardField AS $field_value) 
		{
			if (!strlen($field_value))
				continue;

			if ($first) 
			{
				$first = false;
				continue;
			}
            $field_wizard = "";

			if (strrpos(ltrim($field_value, "`"), "`") !== false) 
			{
				$field_wizard = substr(ltrim($field_value, "`"), 0, strrpos(ltrim($field_value, "`"), "`"));
			} 
			elseif (strpos(ltrim($field_value, ","), ",") !== false) 
			{
				$field_wizard = substr(ltrim($field_value, ","), 0, strpos(ltrim($field_value, ","), ","));
			} 
			elseif (strpos(ltrim($field_value), " ") !== false) 
			{
				$field_wizard = substr(ltrim($field_value), 0, strpos(ltrim($field_value), " "));
			} 

            if($field_wizard) {
                if (strlen($strCompareHaving))
                    $strCompareHaving .= " OR ";

                $strCompareHaving .= $field_wizard . $strOperation;

                if(count($relevance_search)) {
                    foreach($relevance_search AS $relevance_term) {
                        $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $field_wizard . ") = 1, 0, 1)";
                    }
                }
            }
		}
	}
}

if (strlen($actex_field) && strlen($father_value))
{

    if($strCompareWhere)
        $strCompareWhere = "(" . $strCompareWhere . " AND ";
    if($strCompareHaving)
        $strCompareHaving = "(" . $strCompareHaving . " AND ";

    switch($actex_operation)
    {
        case "IN":
            if(strlen($father_value)) 
            {
                if($strCompareWhere)
                    $strCompareWhere .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
                if($strCompareHaving)
                    $strCompareHaving .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
            } 
            else 
            {
                if($strCompareWhere)
                    $strCompareWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
                if($strCompareHaving)
                    $strCompareHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
            }
            break;

        case "LIKE":
            if($strCompareWhere)
                $strCompareWhere .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
            break;
        case "<>":
            if($strCompareWhere)
                $strCompareWhere .= " $actex_field <> " . $db->toSql(new ffData($father_value));
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field <> " . $db->toSql(new ffData($father_value));
            break;
        case "=":
        default:
            if($strCompareWhere)                
                $strCompareWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
    }

    if (strpos($strCompareWhere, "(") === 0)
    {
            $strCompareWhere .= ")";
    }
    if (strpos($strCompareHaving, "(") === 0)
    {
            $strCompareHaving .= ")";
    }
}

if (strlen($strCompareWhere)) 
{
	$bFindWhereTag = preg_match("/\[WHERE\]/", $actex_sql);
	$bFindWhereOptions = preg_match("/(\[AND\]|\[OR\])/", $actex_sql);

	if (!$bFindWhereOptions)
		$sSqlWhere .= " WHERE ";

	$sSqlWhere .= " ( " . $strCompareWhere . ") ";
}
if (strlen($strCompareHaving)) 
{
	$bFindHavingTag = preg_match("/\[HAVING\]/", $actex_sql);
	$bFindHavingOptions = preg_match("/(\[HAVING_AND\]|\[HAVING_OR\])/", $actex_sql);

	if (!$bFindHavingOptions)
		$sSqlHaving .= " HAVING ";

	$sSqlHaving .= " ( " . $strCompareHaving . ") ";
}

$sSQL = $actex_sql;
if ($sSqlWhere) 
{
	$sSQL = str_replace("[AND]", "AND", $sSQL);
	$sSQL = str_replace("[OR]", "OR", $sSQL);
	$sSQL = str_replace("[WHERE]", $sSqlWhere, $sSQL);
} 
else 
{
	$sSQL = str_replace("[AND]", "", $sSQL);
	$sSQL = str_replace("[OR]", "", $sSQL);
	$sSQL = str_replace("[WHERE]", "", $sSQL);
}

if ($sSqlHaving) 
{
	$sSQL = str_replace("[HAVING_AND]", "AND", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "OR", $sSQL);
	$sSQL = str_replace("[HAVING]", $sSqlHaving, $sSQL);
} 
else 
{
	$sSQL = str_replace("[HAVING_AND]", "", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "", $sSQL);
	$sSQL = str_replace("[HAVING]", "", $sSQL);
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

$db->query($sSQL);
$i = -1;
if ($db->nextRecord()) 
{
	$count_field = $db->numFields();
	do 
	{
		$i++;
		if ($count_field == 1) 
		{
			$php_array[$i]["value"] = ffCommon_charset_encode($db->getField($db->fields_names[0], "Text", true));
		} 
		else 
		{
			$php_array[$i]["value"] = ffCommon_charset_encode($db->getField($db->fields_names[0], "Text", true));

			if ($count_field >= 2) 
			{
				$php_array[$i]["label"] = ffCommon_charset_encode(trim($db->getField($db->fields_names[1], "Text", true)));
			}
			if ($count_field >= 3 && $db->fields_names[2] != $image_field) 
			{
				$php_array[$i]["cat"] = ffCommon_charset_encode($db->getField($db->fields_names[2], "Text", true));
			}
			if (isset($db->record[$image_field])) 
			{
				$php_array[$i]["image"] = ffCommon_charset_encode($db->record[$image_field]);
			}
		}
	} while ($db->nextRecord());
}

header("Content-type: application/json");
echo json_encode($php_array);
//cm::jsonParse($php_array);
exit;
