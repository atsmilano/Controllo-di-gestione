<?php
/**
 * @ignore
 * @package FormsFramework
 */

/**
 * @ignore
 * @package FormsFramework
 */
interface ffDBAdapter
{
	const TYPE_DB			= 0;
	const TYPE_TABLE			= 1;
	const TYPE_COLUMNS		= 2;
	const TYPE_INDEXES		= 3;
	const TYPE_QUERY		= 4;
	
    function fieldGet();
	function fieldSet();
	function recordNext();
	function recordPrev();
	function recordGet();
	function recordDelete();
	function recordUpdate();
	function recordInsert();
	function recordsetGet();

	public function queryStructure($eType, $sName, $lazy, $create_globals);
	public function getDBSource($sName, $eType);
	
	public function connect();
}
