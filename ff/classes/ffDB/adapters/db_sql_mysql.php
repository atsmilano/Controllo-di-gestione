<?php
/**
 * @ignore
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * @ignore
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffDBAdapter_db_sql_mysql extends ffDB_Sql implements ffDBAdapter
{
	// CONSTRUCTOR
	function __construct()
	{
		//$this->get_defaults();
	}

    function fieldGet()
	{
	}
	
	function fieldSet()
	{
	}
	
	function recordNext()
	{
	}
	
	function recordPrev()
	{
	}
	
	function recordGet()
	{
	}
	
	function recordDelete()
	{
	}
	
	function recordUpdate()
	{
	}
	
	function recordInsert()
	{
	}
	
	function recordsetGet()
	{
	}
		
	function queryStructure($eType, $sName = "", $lazy = true, $create_globals = false)
	{
		if (!$this->link_id)
		{
			if (!$this->connect())
				return false;
		}
		else
		{
			if (!$this->selectDb())
				return false;
		}
		
		switch ($eType)
		{
			case ffDBAdapter::TYPE_DB:
				if (false !== ($res = mysql_query("SHOW TABLES", $this->link_id)))
				{
					$results = array();

					if (mysql_num_rows($res))
					{
						while ($row = mysql_fetch_row($res))
						{
							$results[$row[0]] = $this->getDBSource($row[0], ffDBAdapter::TYPE_TABLE);
							if (!$lazy)
								$results[$row[0]]->populate();
							
							if ($create_globals)
							{
								global ${$row[0]};
								${$row[0]} = $results[$row[0]];
							}
						}
					}
					
					mysql_free_result($res);
					
					return $results;
				}
				else
					$this->errorHandler("Unable to retrieve tables for Database \"" . $this->database . "\"");
				break;
				
			case ffDBAdapter::TYPE_TABLE:
				$table = $this->getDBSource($sName, ffDBAdapter::TYPE_TABLE);
				if (!$lazy)
					$table->populate();
				if ($create_globals)
				{
					global ${$sName};
					${$sName} = $table;
				}
				return $table;
				break;
				
			case ffDBAdapter::TYPE_COLUMNS:
				if (false !== ($res = mysql_query("SHOW FULL COLUMNS FROM $sName", $this->link_id)))
				{
					if (mysql_num_rows($res))
					{
						$fields = Array();
						
						$i = -1;
						while ($row = mysql_fetch_assoc($res))
						{
							$i++;
							$tmp = new ffDBField($row["Field"]);
							$tmp->iOffset = $i;

							$tmp->sComment = $row["Comment"];
							
							preg_match('/^([a-zA-Z]+)(\(([0-9]+)(\,[0-9]+){0,1}\)){0,1}(\ ([a-zA-Z]+))*$/', $row["Type"], $matches, PREG_OFFSET_CAPTURE);
							//echo "<pre>"; var_dump($matches); die();
							
							switch($matches[1][0])
							{
								case "varchar":
								case "text":
								case "char":
								case "tinytext":
								case "mediumtext":
								case "blob":
								case "varbinary":
									$tmp->eBaseType = "Text";
									break;
									
								case "int":
								case "smallint":
								case "tinyint":
								case "float":
								case "decimal":
									$tmp->eBaseType = "Number";
									break;
									
								case "date":
									$tmp->eBaseType = "Date";
									break;
									
								case "datetime":
									$tmp->eBaseType = "DateTime";
									break;

								case "time":
									$tmp->eBaseType = "Time";
									break;

								default:
									//echo "<pre>"; var_dump($matches); die();
									$this->errorHandler("Unhandled Field Type \"" . $row["Type"] . "\"");
							}
							
							if (isset($matches[3]))
								$tmp->iMaxLenght = (int)$matches[3][0];
							
							if ($row["Null"] == "YES")
								$tmp->bAllowNull = true;
							else
								$tmp->bAllowNull = false;
								
							switch ($row["Key"])
							{
								case "PRI":
									$tmp->bKey = true;
									$tmp->bPrimary = true;
									break;
									
								case "UNI":
									$tmp->bKey = true;
									$tmp->bUnique = true;
									break;
								
								case "MUL":
									$tmp->bKey = true;
									break;
								
								case "":
									$tmp->bKey = false;
									break;

								default:
									$this->errorHandler("Unhandled Key \"" . $row["Key"] . "\"");
							}
							
							switch ($row["Extra"])
							{
								case "auto_increment":
									$tmp->bAutoInc = true;
									break;
									
								case "":
									break;
									
								default:
									$this->errorHandler("Unhandled extra \"" . $row["Extra"] . "\"");
							}
							
							if (count($matches) > 4)
							{
								$i = 6;
								while (isset($matches[$i]))
								{
									switch($matches[$i][0])
									{
										case "unsigned":
											$tmp->bAllowSigned = false;
											break;
											
										default:
											$this->errorHandler("Unhandled option \"" . $matches[$i][0] . "\"");
									}
									$i += 2;
								}
							}
							
							$fields[$tmp->sName] =& $tmp;
							unset($tmp);
						}
						
						mysql_free_result($res);
						return $fields;
					}
					else
						$this->errorHandler("Unable to retrieve Fields for DBSource \"$sName\"");
					
					mysql_free_result($res);
				}
				else
					$this->errorHandler("Unable to retrieve Structure for DBSource \"$sName\"");
				break;
				
				
			case ffDBAdapter::TYPE_INDEXES:
				if (false !== ($res = mysql_query("SHOW INDEX FROM $sName", $this->link_id)))
				{
					if (mysql_num_rows($res))
					{
						$indexes = Array();
						
						$i = -1;
						while ($row = mysql_fetch_assoc($res))
						{
							if (!isset($indexes[$row["Key_name"]]))
							{
								$i++;
								$tmp = new ffDBIndex($row["Key_name"]);
								$tmp->iOffset = $i;
								$tmp->bUnique = ($row["Non_unique"] ? FALSE : TRUE);
								$indexes[$row["Key_name"]] = $tmp;
							}
							
							$indexes[$row["Key_name"]]->addField($row["Column_name"], intval($row["Seq_in_index"]));
						}
						mysql_free_result($res);
						return $indexes;
					}
					mysql_free_result($res);
				}
				else
					$this->errorHandler("Unable to retrieve Indexes for DBSource \"$sName\"");
				break;
				
			default:
				$this->errorHandler("Unable to query for type \"$eType\"");
		}
	}

	function getDBSource($sName, $eType)
	{
		switch($eType)
		{
			case ffDBAdapter::TYPE_TABLE:
				$tmp = new ffDBTable($sName, $this);
				break;
		}
		return $tmp;
	}
}
