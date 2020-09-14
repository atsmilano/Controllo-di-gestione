<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage contents
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

$db = ffDBConnection::factory("db_sql_" . FF_DB_INTERFACE);
$globals = ffGlobals::getInstance("ds");

$globals->tables = $db->queryStructure(ffDBAdapter::TYPE_DB);

$path = $cm->router->matched_rules["ds_services"]["params"][1][0];
if (!strlen($path))
	ffErrorHandler::raise("wrong service use, missing parameters", E_USER_ERROR, null, get_defined_vars());

$path_parts = explode("/", substr($path, 1));

$ds_name = $path_parts[0];

if (!strlen($ds_name))
	ffErrorHandler::raise ("no Data Source specified", E_USER_ERROR, null, get_defined_vars ());

if (!isset($globals->tables[$ds_name]))
	ffErrorHandler::raise ("wrong Data Source specified", E_USER_ERROR, null, get_defined_vars ());

$globals->tables[$ds_name]->populate();

$sSQLFIELDS  = "";
$sSQLJOIN = "";

$aFields = $globals->tables[$ds_name]->getFields();
$fields = array();

foreach ($aFields as $dbfield)
{
	if ($dbfield->bKey && !$dbfield->bPrimary)
	{
		$foreign_table = null;
		$foreign_key = null;
		$foreign_desc = null;
		$foreign_alt_desc = null;

		if (strlen($dbfield->sComment))
		{
			$rc = preg_match_all("/FK\(([a-zA-Z0-9_\-]+)(,?([a-zA-Z0-9_\-]+))?\)/", $dbfield->sComment, $matches);
			if ($rc > 0)
			{
				$foreign_table = $matches[1][0];
				if ($matches[3][0])
					$foreign_key = $matches[3][0];
			}
		}
		elseif (strpos($dbfield->sName, "ID_") === 0)
			$foreign_table = substr($dbfield->sName, strpos($dbfield->sName, "_") + 1);

		if ($foreign_table && isset($globals->tables[$foreign_table]))
		{
			$globals->tables[$foreign_table]->populate();
			foreach ($globals->tables[$foreign_table]->getFields() as $foreign_field)
			{
				if ($foreign_field->sName === $foreign_key)
				{
					$foreign_key = $foreign_field;
				}
				elseif ($foreign_field->bKey && $foreign_field->bPrimary && $foreign_field->bAutoInc && $foreign_key === null)
				{
					$foreign_key = $foreign_field;
				}
				elseif ($foreign_field->eBaseType == "Text" && (strpos(strtolower($foreign_field->sName), "title") === 0 || strpos(strtolower($foreign_field->sName), "titolo") === 0))
				{
					$foreign_desc = $foreign_field;
				}
				elseif ($foreign_field->eBaseType == "Text" && strpos(strtolower($foreign_field->sName), "desc") === 0)
				{
					if ($foreign_desc === null || (strpos(strtolower($foreign_desc->sName), "desc") !== 0 && strpos(strtolower($foreign_desc->sName), "tit") !== 0))
						$foreign_desc = $foreign_field;
				}
				elseif ($foreign_desc === null && $foreign_field->eBaseType == "Text")
				{
					$foreign_desc = $foreign_field;
				}
				elseif ($foreign_alt_desc === null)
				{
					$foreign_alt_desc = $foreign_field;
				}
			}

			if (is_string($foreign_key))
				ffErrorHandler::raise ("foreign key not found!", E_USER_ERROR, null, get_defined_vars ());

			if ($foreign_desc === null)
				$foreign_desc = $foreign_alt_desc;

			if ($foreign_key !== null && $foreign_desc !== null)
			{
				$fields[] = $dbfield->sName . "_descr";
				$sSQLFIELDS .= "
						, `" . $foreign_table . "_" . $dbfield->sName . "`.`" . $foreign_desc->sName . "` AS `" . $dbfield->sName . "_descr`
					";
				$sSQLJOIN .= "
						LEFT JOIN `" . $foreign_table . "` AS `" . $foreign_table . "_" . $dbfield->sName . "` ON
							`" . $foreign_table . "_" . $dbfield->sName . "`.`" . $foreign_key->sName . "` = `" . $ds_name . "`.`" . $dbfield->sName . "`
					";
				$obj->resources[] = $foreign_table;
			}
			reset($globals->tables[$foreign_table]->getFields());
		}
	}

	if ($dbfield->bKey && $dbfield->bPrimary && $dbfield->bAutoInc)
	{
		$KeyField = $dbfield;
	}
	$fields[] = $dbfield->sName;
}
reset($aFields);

$source_SQL = "SELECT
						`" . $ds_name . "`.*
						" . $sSQLFIELDS . "
					FROM
						`" . $ds_name . "`
						" . $sSQLJOIN . "
";

$aIndexes = $globals->tables[$ds_name]->getIndexes();
if (isset($aIndexes["PRIMARY"]) && count($path_parts) > 1)
{
	if ((count($path_parts) - 1) / 2 !== count($aIndexes["PRIMARY"]->aFields))
		ffErrorHandler::raise ("Wrong Fields for Primary Key", E_USER_ERROR, null, get_defined_vars ());
	$sSQLWhere = "";
	foreach($aIndexes["PRIMARY"]->aFields as $key => $value)
	{
		$KeyField = $aFields[$key];
		if (strlen($sSQLWhere))
			$sSQLWhere .= " AND ";
		$sSQLWhere .= "`" . $ds_name . "`.`" . $KeyField->sName . "` = " . $db->toSql($path_parts[$value["seq"] * 2], $KeyField->eBaseType);
	}
	reset ($aIndexes["PRIMARY"]->aFields);
	
	$source_SQL .= " WHERE " . $sSQLWhere;
}

$result = array();

$db->query($source_SQL);
if ($db->nextRecord()) {
	do
	{
		$index = count($result);

		foreach ($fields as $key => $value)
		{
			$result[$index][$value] = $db->getField($value)->getValue();
		}
		reset($fields);
	} while ($db->nextRecord());
}

cm::jsonParse($result);
exit;
