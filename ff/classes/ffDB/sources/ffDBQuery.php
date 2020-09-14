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
 * ffDBQuery è la classe preposta alla rappresentazione astratta dell'insieme di campi che compongono
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
class ffDBQuery extends ffDBSource
{
	public $bPopulateOnDemand		= true;
	
	private $sSql					= null;
	
	private $pDBRes					= null;
	
	private static $_pSQLParser		= null;
	private static $_pSQLCreator		= null;
	public	$aParsed				= null;
	private $sOutSql				= null;
	
	/**
	 * restituisce una sorgente dati
	 * @param String $sName
	 * @return ffDBQuery
	 */
	static public function getSource($sName)
	{
		return parent::getSource($sName);
	}
	
	public function __construct($sName, $pDBConnection, $sSql) 
	{
		parent::__construct($sName, $pDBConnection);
		
		$this->setSql($sSql);
		
		return $this;
	}
	
	function populate()
	{
		try
		{
			$this->aParsed = $this->getSQLParser()->parse($this->sSql);
			$this->bPopulated = true;
		} 
		catch (Exception $exc) 
		{
			ffErrorHandler::raise("Wrong SQL", E_USER_ERROR, $this, get_defined_vars());
		}
		//$this->sOutSql = $sSql; // avoid useless processing
		
		return $this;
	}
	
	public function getSQLParser()
	{
		if (ffDBQuery::$_pSQLParser === null)
			ffDBQuery::$_pSQLParser = new PHPSQLParser();
		
		return ffDBQuery::$_pSQLParser;
	}
	
	public function getSQLCreator()
	{
		if (ffDBQuery::$_pSQLCreator === null)
			ffDBQuery::$_pSQLCreator = new PHPSQLCreator();
		
		return ffDBQuery::$_pSQLCreator;
	}
	
	public function setSql($sSql)
	{
		$this->sSql = $sSql;
		
		if (!$this->bPopulateOnDemand)
			$this->populate();
	}
	
	public function create()
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = $this->getSQLCreator()->create($this->aParsed);
		return $this->sOutSql;
	}

	public function getSql($context = null)
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		if ($this->sOutSql !== null)
		{
			$res = $this->doEvent("on_getSql", array($this, $this->sOutSql, $context));
			$last_res = end($res);
			if (!is_null($last_res))
				return $last_res;
			else
				return $this->sOutSql;
		}
		
		try 
		{
			$this->create();
			$res = $this->doEvent("on_getSql", array($this, $this->sOutSql, $context));
			$last_res = end($res);
			if (!is_null($last_res))
				return $last_res;
			else
				return $this->sOutSql;
		} 
		catch (Exception $exc) 
		{
			ffErrorHandler::raise("Error in object", E_USER_ERROR, $this, get_defined_vars());
		}

	}
	
	public function setOrderByField($field, $ascending = true, $add = true)
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		if ($add)
			$index = null;
		else
			$this->resetOrder();
		
		$this->setOrder($index, "colref", "`" .$field . "`", $field, false, ($ascending ? "ASC" : "DESC"));
		
		return $this;
	}
	
	public function resetOrder()
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = null;
		unset($this->aParsed["ORDER"]);
		
		return $this;
	}
	
	public function filterByValue($sFieldName, $oFieldValue, $operation = "=", $add = true, $having = false, $or = false)
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = null;
		
		if ($having)
			$arr_key = "HAVING";
		else
			$arr_key = "WHERE";
		
		if (count($this->aParsed[$arr_key]))
			$this->aParsed[$arr_key][] = array(
					"expr_type"		=> "operator"
					, "base_expr"	=> ($or ? "OR" : "AND")
					, "sub_tree"	=> false
				);
		
		$this->aParsed[$arr_key][] = array(
				"expr_type"		=> "colref"
				, "base_expr"	=> $sFieldName
				, "no_quotes"	=> trim($sFieldName, "`")
				, "sub_tree"	=> false
			);
		$this->aParsed[$arr_key][] = array(
				"expr_type"		=> "operator"
				, "base_expr"	=> $operation
				, "sub_tree"	=> false
			);
		$this->aParsed[$arr_key][] = array(
				"expr_type"		=> "const"
				, "base_expr"	=> $this->pDBConnection->toSql($oFieldValue)
				, "sub_tree"	=> false
			);
		
		return $this;
	}
	
	public function setOrder($index = null, $expr_type = "colref", $base_expr = "", $no_quotes = "", $sub_tree = false, $direction = "ASC")
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = null;
		
		if ($index === null)
			$index = count($this->aParsed["ORDER"]);
		
		$order = array(
			"expr_type" => $expr_type
			, "base_expr" => $base_expr
			, "no_quotes" => $no_quotes
			, "sub_tree" => $sub_tree
			, "direction" => $direction
		);
		
		$this->aParsed["ORDER"][$index] = $order;
		
		return $this;
	}
	
	public function resetMaxResults()
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = null;
		unset($this->aParsed["LIMIT"]);
		
		return $this;
	}
	
	public function setMaxResults($rowcount)
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->setLimit(0, $rowcount);
		
		return $this;
	}
	
	public function setLimit($offset, $rowcount)
	{
		if (!$this->isPopulated() && $this->bPopulateOnDemand)
			$this->populate();
		
		$this->sOutSql = null;
		
		$this->aParsed["LIMIT"] = array(
			"offset" => $offset
			, "rowcount" => $rowcount
		);
		
		return $this;
	}
}
