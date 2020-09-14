<?php
/**
 * Abstract Data Fieldset and Relations Rapresentation Class File
 *
 * @ignore
 * @package FormsFramework
 * @subpackage Abstract Data Fieldset Rapresentation Class
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * ffDBTable è la classe preposta alla rappresentazione astratta dell'insieme di campi che compongono
 * un unità elaborativa sulla cui base vengono sviluppati gli elementi d'interfaccia con l'utente.
 * all'interno di un contesto MVC, può essere rapportata al modulo.
 * @ignore
 * @package FormsFramework
 * @subpackage Abstract Data Fieldset Rapresentation Class
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffDBTable extends ffDBSource
{
	public $bPopulateOnDemand		= true;
	
	private $aIndexes				= Array();
	
	private $maxResults				= null;
	private $order					= null;
	private $filters				= null;
	
	/*private $aRelations				= Array();
	
	public function addRelation($aSrcFields, $sDstSource, $aDstFields, $eType)
	{
	}*/
	
	function getIndexes()
	{
		if (is_array($this->aIndexes) && count($this->aIndexes))
		{
			return $this->aIndexes;
		}
		elseif (!$this->isPopulated() && $this->bPopulateOnDemand)
		{
			$this->populate();
			return $this->aIndexes;
		}
		else
			ffErrorHandler::raise("Empty DBTable", E_USER_ERROR, $this, get_defined_vars());
	}

	public function getSql($context = null)
	{
		$tmpSQL = "SELECT * FROM `" . $this->sName . "`";
		
		if ($this->filters !== null)
		{
			$whereSQL = "";
			foreach ($this->filters as $element)
			{
				if (strlen($whereSQL))
					$whereSQL .= " AND ";
				$whereSQL .= $element["field"] . " " . $element["operation"] . " " . $this->pDBConnection->toSql($element["value"]);
			}
			$tmpSQL .= " WHERE " . $whereSQL;
		}
		
		if ($this->order !== null)
		{
			$orderSQL = "";
			foreach ($this->order as $element)
			{
				if (strlen($orderSQL))
					$orderSQL .= ", ";
				$orderSQL .= $element["field"] . " " . ($element["asc"] ? "ASC" : "DESC");
			}
			$tmpSQL .= " ORDER BY " . $orderSQL;
		}
		
		if ($this->maxResults !== null)
		{
			$tmpSQL .= " LIMIT " . $this->maxResults;
		}
		
		return $tmpSQL;
	}
	
	public function setOrderByField($field, $ascending = true, $add = true)
	{
		if ($this->order === null || $add = false)
			$this->order = Array();
		
		$this->order[] = Array(
			"field" => $field
			, "asc" => $ascending
		);
	}
	
	public function resetOrder()
	{
		$this->order = null;
	}
	
	public function setMaxResults($rowcount)
	{
		$this->maxResults = $rowcount;
	}
	
	public function resetMaxResults()
	{
		$this->maxResults = null;
	}
	
	public function populate()
	{
		if ($this->pDBConnection !== null && strlen($this->sName))
		{
			$rc = $this->pDBConnection->queryStructure(ffDBAdapter::TYPE_COLUMNS, $this->sName);
			if (is_array($rc) && count($rc))
			{
				$this->aDBFields = $rc;
				$this->bPopulated = true;
			}
			else
				ffErrorHandler::raise("Empty DBTable", E_USER_ERROR, $this, get_defined_vars());

			$rc = $this->pDBConnection->queryStructure(ffDBAdapter::TYPE_INDEXES, $this->sName);
			if (is_array($rc) && count($rc))
			{
				$this->aIndexes = $rc;
			}
		}
		else
			ffErrorHandler::raise("Unable to populate DBTable, missing connection or name", E_USER_ERROR, $this, get_defined_vars());
		
		return $this;
	}
	
	public function filterByField($sFieldName, $oFieldValue, $operation = "=", $add = true, $having = false)
	{
		if ($this->filters === null || $add = false)
			$this->filters = Array();
		
		$this->filters[] = Array(
			"field" => $sFieldName
			, "value" => $oFieldValue
			, "operation" => $operation
			, "having" => $having
		);
	}
}
