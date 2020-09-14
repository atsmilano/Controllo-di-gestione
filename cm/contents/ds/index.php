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
$globals_cache = ffGlobals::getInstance("__ds_cache__");
$reverse = (string)$cm->router->getRuleById("ds")->reverse;

// Build Menu

$globals->tables = $db->queryStructure(ffDBAdapter::TYPE_DB);

$cache_path = CM_CACHE_PATH . "/ds";
if (!is_dir($cache_path))
	@mkdir($cache_path, 0777, true);

$cache_file = $cache_path . "/mod_restricted.xml";

if (file_exists($cache_file))
{
	mod_restricted_load_config_file($cache_file);
}
else
{
	$fp = fopen($cache_file, "w");

	fwrite($fp, '<?xml version="1.0" encoding="utf-8"?>
<configdata>
	<menu>
		<ds>' . "\n");

	foreach ($globals->tables as $table => $value)
	{
		mod_restricted_add_menu_sub_element(array(
		    "key"           => "ds"
            , "subkey"      => $table
            , "path"        => $reverse . "/" . $table
            , "label"       => $table
            , "location"    => "rightcol"
        ));

		fwrite($fp, '			<' . $table . ' location="rightcol" path="' . $reverse . "/" . $table . '" />' . "\n");
	}
	reset($globals->tables);

	fwrite($fp, '		</ds>
	</menu>
</configdata>');
	fclose($fp);
}

$path = $cm->router->matched_rules["ds"]["params"][1][0];
if (!strlen($path))
	return;

$path = substr($path, 1);
$tmp = $cm->router->matched_rules["ds"]["params"];

$path_parts = explode("/", $path);

$name = $path_parts[0];
$mode = $path_parts[1];

$cm->modules["restricted"]["sel_topbar"]["elements"][$name]["selected"] = true;

$cache_path = CM_CACHE_PATH . "/ds/" . $name;
if (!is_dir($cache_path))
	@mkdir($cache_path, 0777, true);

if ($mode == "modify")
	$cache_file = $cache_path . "/modify.php";
else
	$cache_file = $cache_path . "/index.php";

if (file_exists($cache_file))
{
	require($cache_file);
	return;
}

$globals->tables[$name]->populate();

$id = preg_replace("/[\/\_]/", "-", $path);

cache_store(0, "<?php");
cache_store(0, "// ********************************************************");
cache_store(0, "//  DS - GENERATED FILE FOR DATA_SOURCE " . $name);
cache_store(0, "// ********************************************************");


if ($mode == "modify")
{
	$obj = ffRecord::factory($cm->oPage);
	cache_store(1, '$obj = ffRecord::factory($cm->oPage);');
}
else
{
	$obj = ffGrid::factory($cm->oPage);
	cache_store(1, '$obj = ffGrid::factory($cm->oPage);');
}


$obj->id = $id;
cache_store(1, '$obj->id = "' . $id . '";');
$obj->title = $name;
cache_store(1, '$obj->title = "' . $name . '";');
$obj->resources[] = $name;
cache_store(1, '$obj->resources[] = "' . $name . '";');

$sSQLFIELDS  = "";
$sSQLJOIN = "";

cache_store(2, "// *********** FIELDS ****************");

foreach ($globals->tables[$name]->getFields() as $dbfield)
{
	if ($dbfield->sComment == "HIDE")
		continue;
	
	$field = ffField::factory($cm->oPage);
	cache_store(3, '$field = ffField::factory($cm->oPage);');
	$field->id = $dbfield->sName;
	$tmp_id_cache = cache_store(3, '$field->id = "' . $dbfield->sName . '";');
	$field->base_type = $dbfield->eBaseType;
	cache_store(3, '$field->base_type = "' . $dbfield->eBaseType . '";');
	$field->label = $dbfield->sName;
	cache_store(3, '$field->label = "' . $dbfield->sName . '";');

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
				if ($mode == "modify")
				{
					$field->extended_type = "Selection";
					cache_store(3, '$field->extended_type = "Selection";');
					$field->source_SQL = "SELECT
												`" . $foreign_table . "`.`" . $foreign_key->sName . "`
												, `" . $foreign_table . "`.`" . $foreign_desc->sName . "`
											FROM
												`" . $foreign_table . "`
											ORDER BY
												UPPER(`" . $foreign_table . "`.`" . $foreign_desc->sName . "`)
						";
					cache_store(3, '$field->source_SQL = "SELECT
												`' . $foreign_table . '`.`' . $foreign_key->sName . '`
												, `' . $foreign_table . '`.`' . $foreign_desc->sName . '`
											FROM
												`' . $foreign_table . '`
											ORDER BY
												UPPER(`' . $foreign_table . '`.`' . $foreign_desc->sName . '`)
						";');
					$field->widget = "actex";
					//$field->widget = "activecomboex";
					cache_store(3, '$field->widget = "actex";');
					//cache_store(3, '$field->widget = "activecomboex";');
					$field->actex_update_from_db = true;
					cache_store(3, '$field->actex_update_from_db = true;');

					$field->actex_dialog_url = FF_SITE_PATH . $reverse . "/" . $foreign_table . "/modify?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
					cache_store(3, '$field->actex_dialog_url = FF_SITE_PATH . "' . $reverse . '/' . $foreign_table . '/modify?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);');
					$field->actex_dialog_edit_params = array("keys[" . $foreign_key->sName . "]" => null);
					cache_store(3, '$field->actex_dialog_edit_params = array("keys[' . $foreign_key->sName . ']" => null);');
					$field->actex_dialog_delete_url = $field->actex_dialog_url . "?frmAction=" . preg_replace("/[\/\_]/", "-", $foreign_table . "-modify") . "_confirmdelete";
					cache_store(3, '$field->actex_dialog_delete_url = $field->actex_dialog_url . "?frmAction=' . preg_replace("/[\/\_]/", "-", $foreign_table . "-modify") . '_confirmdelete";');

					$field->resources[] = $foreign_table;
					cache_store(3, '$field->resources[] = "' . $foreign_table . '";');
				} else {
					$field->id .= "_descr";
					$globals_cache->data[3][$tmp_id_cache] = '$field->id = "' . $dbfield->sName . '_descr";';
					$field->base_type = $foreign_desc->eBaseType;
					cache_store(3, '$field->base_type = "' . $foreign_desc->eBaseType . '";');

					$sSQLFIELDS .= "
							, `" . $foreign_table . "_" . $dbfield->sName . "`.`" . $foreign_desc->sName . "` AS `" . $field->id . "`
						";
					$sSQLJOIN .= "
							LEFT JOIN `" . $foreign_table . "` AS `" . $foreign_table . "_" . $dbfield->sName . "` ON
								`" . $foreign_table . "_" . $dbfield->sName . "`.`" . $foreign_key->sName . "` = `" . $name . "`.`" . $dbfield->sName . "`
						";
					$obj->resources[] = $foreign_table;
					cache_store(1, '$obj->resources[] = "' . $foreign_table . '";');
				}
			}
		}
	}

	if ($mode == "modify")
	{
		switch ($field->base_type)
		{
			case "Date":
			case "DateTime":
				$field->widget = "datepicker";
				cache_store(3, '$field->widget = "datepicker";');

			case "Text":
				if (!$dbfield->iMaxLenght)
				{
					$field->extended_type = "Text";
					cache_store(3, '$field->extended_type = "Text";');
				}
				break;
		}
	}

	if ($dbfield->bKey && $dbfield->bPrimary && $dbfield->bAutoInc)
	{
		if ($mode != "modify")
		{
			$obj->order_default = $dbfield->sName;
			cache_store(1, '$obj->order_default = "' . $dbfield->sName . '";');
		}
		$obj->addKeyField($field);
		cache_store(3, '$obj->addKeyField($field);');
	}
	else
	{
		$obj->addContent($field);
		cache_store(3, '$obj->addContent($field);');
	}
	cache_store(3, "");
}

if ($mode == "modify")
{
	$obj->src_table = $name;
	cache_store(1, '$obj->src_table = "' . $name . '";');
}
else
{
	$obj->source_SQL = "SELECT
								`" . $name . "`.*
								" . $sSQLFIELDS . "
							FROM
								`" . $name . "`
								" . $sSQLJOIN . "
							[WHERE]
							[HAVING]
							[ORDER]
		";
	cache_store(1, '$obj->source_SQL = "SELECT
								`' . $name . '`.*
								' . $sSQLFIELDS . '
							FROM
								`' . $name . '`
								' . $sSQLJOIN . '
							[WHERE]
							[HAVING]
							[ORDER]
		";');
	$obj->record_id = $id . "-modify";
	cache_store(1, '$obj->record_id = "' . $id . '-modify";');
	$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
	cache_store(1, '$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";');
/*	$obj->full_ajax = true;
	cache_store(1, '$obj->full_ajax = true;');*/
}

cache_store(4, "// *********** ADDING TO PAGE ****************");
cache_store(4, "");

$cm->oPage->addContent($obj);
cache_store(4, '$cm->oPage->addContent($obj);');

cache_store(5, "// ********************************************************");
cache_store(5, "//  DS - DONE GENERATING FILE FOR DATA_SOURCE " . $name);
cache_store(5, "// ********************************************************");

flush_cache($cache_file);

function cache_store($level, $string)
{
	$globals = ffGlobals::getInstance("__ds_cache__");
	
	$globals->data[$level][] = $string;

	if ($globals->max_level < $level)
		$globals->max_level = $level;

	return count($globals->data[$level]) - 1;
}

function flush_cache($cache_file)
{
	$globals = ffGlobals::getInstance("__ds_cache__");

	$fp = fopen($cache_file, "w");

	for ($i = 0; $i <= $globals->max_level; $i++)
	{
		if ($globals->data[$i])
		{
			foreach ($globals->data[$i] as $value)
			{
				fwrite($fp, $value . "\n");
			}
			fwrite($fp, "\n");
		}
	}
	fclose($fp);
}