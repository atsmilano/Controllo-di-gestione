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
 * ffDBSource è la classe preposta alla rappresentazione astratta dell'insieme di campi che compongono
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
abstract class ffDBSource extends ffCommon
{
	protected $sName				= "";
	protected $pDBConnection		= null;
	
	protected $aDBFields			= Array();
	
	protected $bPopulated			= false;
	
	protected static $aSources		= Array();
	
	const TYPE_TABLE					= 0;
	const TYPE_QUERY				= 1;
	
	abstract public function populate();
	
	abstract public function getSql($context = null);
	
	abstract public function setOrderByField($field, $ascending = true, $add = true);
	abstract public function resetOrder();
	abstract public function setMaxResults($rowcount);
	abstract public function resetMaxResults();

	/**
	 * restituisce una sorgente dati
	 * @param String $sName
	 * @return ffDBSource
	 */
	static public function getSource($sName)
	{
		if (array_key_exists($sName, ffDBSource::$aSources))
			return ffDBSource::$aSources[$sName];
		else
			ffErrorHandler::raise("Source not found", E_USER_ERROR, null, get_defined_vars());
	}
	
	/*static public function factory($sName, $eType)
	{
		$ret = null;
		
		switch ($eType)
		{
			case ffDBSource::TYPE_TABLE:
				$ret = new ffDBTable($sName, $pDBConnection)
				break;
			
			case ffDBSource::TYPE_QUERY:
				break;
		}
	}*/
			
	function __construct($sName, $pDBConnection)
	{
		$this->get_defaults();
		
		$this->sName = $sName;
		$this->bindToDBConnection($pDBConnection);
		
		ffDBSource::$aSources[$sName] = $this;
		
		return $this;
	}
	
	function getName()
	{
		return $this->sName;
	}
	
	function bindToDBConnection($pDBConnection)
	{
		$this->pDBConnection = $pDBConnection;
	}
	
	function addDBField($pDBField)
	{
		$this->aDBFields[$pDBField->sName] =& $pDBField;
	}
	
	function getFields()
	{
		if (is_array($this->aDBFields) && count($this->aDBFields))
		{
			return $this->aDBFields;
		}
		elseif (!$this->isPopulated() && $this->bPopulateOnDemand)
		{
			$this->populate();
			return $this->aDBFields;
		}
		else
			ffErrorHandler::raise("Empty DBTable", E_USER_ERROR, $this, get_defined_vars());
	}

	function isPopulated()
	{
		return $this->bPopulated;
	}
}
