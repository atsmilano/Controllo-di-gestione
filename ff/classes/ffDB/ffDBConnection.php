<?php
/**
 * @ignore
 * @package FormsFramework
 */

/**
 * @ignore
 * @package FormsFramework
 */
class ffDBConnection //db_sql_mysql | db_sql_adodb | text_txt | text_xml
{
	protected static $aConnections		= Array();
	
	/**
	 *
	 * @param type $eType
	 * @return ffDBAdapter
	 */
	static public function factory($eType, $name = null)
    {
		if ($name !== null && array_key_exists($name, ffDBConnection::$aConnections))
			return ffDBConnection::$aConnections[$name];
		
		$ret = null;
        require_once("adapters/" . $eType . "." . FF_PHP_EXT);

		$classname = "ffDBAdapter_" . $eType;
        $ret = $classname::factory();
		
		if ($name !== null)
			ffDBConnection::$aConnections[$name] = $ret;
		
		return $ret;
    }

	private function __construct()
	{
	}
}
