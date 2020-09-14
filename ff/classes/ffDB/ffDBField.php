<?php
/**
 * Abstract Data Field Rapresentation Class File
 * 
 * @ignore
 * @package FormsFramework
 * @subpackage Abstract Data Field Rapresentation Class
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * ffDBField è la classe preposta alla rappresentazione astratta dei campi utilizzati per la gestione dei dati
 *
 * @ignore
 * @package FormsFramework
 * @subpackage Abstract Data Field Rapresentation Class
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffDBField extends ffCommon
{
	var $sName		= "";
	var $iOffset 	= null;

	var $eBaseType;
	
	var $bAllowNull;

	var $bKey;
	var $bPrimary;
	var $bUnique;
	
	var $bAutoInc;
	
	var $iMinLenght;
	var $iMaxLenght;
	
	var $bAllowNumeric;
	var $bAllowAlpha;
	var $bAllowSymbols;
	
	var $bAllowSigned;
	var $bAllowFloat;
	
	var $pParent;
	
	var $sComment;
	var $sTable;

	// SPECIFIC DATA CONNECTION OPTIONS
	
	function __construct($sName)
	{
		$this->get_defaults();
		
		if (!strlen($sName))
			ffErrorHandler::raise("u must enter a valid name for ffDBField objects", E_USER_ERROR, $this, get_defined_vars());
			
		$this->sName = $sName;
	}
}
