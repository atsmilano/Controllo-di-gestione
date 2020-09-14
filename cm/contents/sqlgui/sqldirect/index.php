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

$obj = ffRecord::factory($cm->oPage);
$obj->id = "sqldirect";
$obj->skip_action = true;
$obj->buttons_options["cancel"]["display"] = false;
$obj->buttons_options["insert"]["label"] = "Esegui";
$obj->addEvent("on_do_action", function ($obj, $action) {
	if ($action === "insert")
		return true;
});

$fld = ffField::factory($cm->oPage);
$fld->id = "sql";
$fld->label = "SQL Text";
$fld->extended_type = "Text";
$fld->properties["rows"] = "20";
$fld->required = true;
$obj->addContent($fld);

$cm->oPage->addContent($obj);

if ($_REQUEST["frmAction"] !== "sqldirect_insert")
	return;

$sSQL = trim($_REQUEST["sqldirect_sql"]);
if (!strlen($sSQL))
	return;

//$db = ffDBConnection::factory("db_sql_" . FF_DB_INTERFACE);
$db = ffDB_Sql::factory();
$db->on_error = "ignore";
$globals = ffGlobals::getInstance("sqlgui");
$type = null;

if (stripos($sSQL, "SELECT") === 0 || stripos($sSQL, "SHOW") === 0)
	$type = "query";
else if (stripos($sSQL, "INSERT") === 0 || stripos($sSQL, "UPDATE") === 0 || stripos($sSQL, "DELETE") === 0)
	$type = "cmd";
else
	$type = "other";

if ($type === "query")
{
	$before = microtime(true);
	$db->query($sSQL);
	$exec_time = microtime(true) - $before;
	
	if ($db->errno)
	{
		$cm->oPage->addContent("<div class='error'><h1 class='error'>MySql Error #" . ffCommon_specialchars($db->errno) . "</h1><h2>" . ffCommon_specialchars($db->error) . "</h2></div>");
		return;
	}
	
	$cm->oPage->addContent("<div class='info'><h1>Execution Time: " . $exec_time . "</h1></div>");
	
	if (!$db->nextRecord())
	{
		$cm->oPage->addContent("<div class='info'><h1>No Results</h1></div>");
		return;
	}
	
	$obj2 = ffGrid::factory($cm->oPage);
	$obj2->id = "sqlresult";
	$obj2->title = "SQL Result";
	$obj2->source_SQL = $sSQL;
	$obj2->SQL_passthrough = true;
	$obj2->display_new = false;
	$obj2->display_edit_bt = false;
	$obj2->display_edit_url = false;
	$obj2->display_delete_bt = false;
	$obj2->use_order = false;
	$obj2->use_search = false;
	
	foreach ($db->record as $key => $value)
	{
		$fld = ffField::factory($cm->oPage);
		$fld->id = $key;
		$fld->label = $key;
		$obj2->addContent($fld);
	}
	
	$cm->oPage->addContent($obj2);
}
elseif ($type === "cmd")
{
	$before = microtime(true);
	$db->execute($sSQL);
	$exec_time = microtime(true) - $before;
	
	if ($db->errno)
	{
		$cm->oPage->addContent("<div class='error'><h1 class='error'>MySql Error #" . ffCommon_specialchars($db->errno) . "</h1><h2>" . ffCommon_specialchars($db->error) . "</h2></div>");
		return;
	}
	
	$cm->oPage->addContent("<div class='info'><h1>Execution Time: " . $exec_time . "</h1></div>");
	
	if (stripos($sSQL, "INSERT") === 0)
	{
		$cm->oPage->addContent("<div class='info'><h1>Insert ID: " . $db->getInsertID(true) . "</h1></div>");
	}
	else if (stripos($sSQL, "INSERT") === 0 || stripos($sSQL, "UPDATE") === 0 || stripos($sSQL, "DELETE") === 0)
	{
		$cm->oPage->addContent("<div class='info'><h1>Affected rows: " . $db->affectedRows() . "</h1></div>");
	}
	return;
}
else
{
	$before = microtime(true);
	$db->query($sSQL);
	$exec_time = microtime(true) - $before;
	
	if ($db->errno)
	{
		$cm->oPage->addContent("<div class='error'><h1 class='error'>MySql Error #" . ffCommon_specialchars($db->errno) . "</h1><h2>" . ffCommon_specialchars($db->error) . "</h2></div>");
		return;
	}
	
	$cm->oPage->addContent("<div class='info'><h1>Execution Time: " . $exec_time . "</h1></div>");
	$cm->oPage->addContent("<div class='info'><h1>Done.</h1></div>");
}
