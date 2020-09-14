<?php
/**
 * @package FormsFramework
 * @ignore
 */

/**
 * @package FormsFramework
 * @ignore
 */
class ffXmlElement
{
	// ----------------------------------
	//  PUBLIC VARS (used for settings)
	
	public $id				= null;
	public $type			= "ffXmlElement";

	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	
	public $father 			= null;
	public $childs			= array();
	public $cdata			= null;
	public $attribs			= array();
	
	private $_cache_get		= array();

	// ---------------------------------------------------------------
	//  PUBLIC FUNCS
	// ---------------------------------------------------------------
	
	function __toString()
	{
		return $this->cdata;
	}
	
	function __get ($name)
	{
		if (isset($this->_cache_get[$name]))
			return $this->_cache_get[$name];
			
		$res = array();
		
		if (count($this->childs))
		{
			foreach ($this->childs as $key => $value)
			{
				if ($this->childs[$key]->type == $name)
					array_push($res, $this->childs[$key]);
			}
			reset($this->childs);
		}
		
		$this->_cache_get[$name] = $res;
		return $res;
	}

/*	function __set ($name, $value)
	{
		ffErrorHandler::raise("magic", E_USER_ERROR, $this, get_defined_vars());
	}
	function __isset ($name)
	{
		ffErrorHandler::raise("magic", E_USER_ERROR, $this, get_defined_vars());
	}
	function __unset ($name)
	{
		ffErrorHandler::raise("magic", E_USER_ERROR, $this, get_defined_vars());
	}
 */
	//  CONSTRUCTOR
	function __construct()
	{
	}
	
	function getFather()
	{
		if ($this->father === null)
			return null;
		else
			return $this->father[0];
	}
		
	function addChild(&$object)
	{
		$class_vars = get_object_vars($object);
		if (get_class($this) == "ffXmlElement" || is_subclass_of($object, "ffXmlElement"))
		{
			$object->father = array(&$this);
		}
		elseif (isset($class_vars["ffXmlElement"]) && $class_vars["ffXmlElement"] !== null)
		{
			$object->ffXmlElement[0]->father = array(&$this);
		}
		
		$key = $this->getElementById($object->id, true);
		if ($key !== null)
			$this->childs[$key] = $object;
		else
			$this->childs[] = $object;
	}
		
	function deleteChild($id)
	{
		$object = $this->getElementById($id);
		if ($object !== null)
		{
			$key = $object->father[0]->getElementById($id, true);
			$object->father[0]->childs[$key] = null;
			unset($object->father[0]->childs[$key]);
			
			$class_vars = get_object_vars($object);
			if (get_class($object) == "ffXmlElement" || is_subclass_of($object, "ffXmlElement"))
				$object->father = null;
			elseif (isset($class_vars["ffXmlElement"]) && $class_vars["ffXmlElement"] !== null)
				$object->IFElement[0]->father = null;
				
			return true;
		}
		else
			return false;
	}
		
	function getElementById($id, $bFindKey = false)
	{
		if ($id === null)
			return null;
		return 
			$this->findObject($this, $id, $bFindKey);
	}
	
	function findObject(&$ffXmlElement, $id, $bFindKey)
	{
		if (isset($ffXmlElement->childs) && count($ffXmlElement->childs))
		{
			foreach ($ffXmlElement->childs as $key => $value)
			{
				$class_vars = get_object_vars($ffXmlElement->childs[$key]);
				if ($ffXmlElement->childs[$key]->id == $id)
				{
					reset($ffXmlElement->childs);
					if ($bFindKey)
						return $key;
					else
						return $ffXmlElement->childs[$key];
				}
				else if (get_class($ffXmlElement->childs[$key]) == "ffXmlElement" || is_subclass_of($ffXmlElement->childs[$key], "ffXmlElement"))
				{
					$retval = $this->findObject($ffXmlElement->childs[$key], $id, $bFindKey);
					if ($retval !== null)
					{
						reset($ffXmlElement->childs);
						return $retval;
					}
				}
				else if (isset($class_vars["ffXmlElement"]) && $class_vars["ffXmlElement"] !== null)
				{
					$retval = $this->findObject($ffXmlElement->childs[$key]->IFElement[0], $id, $bFindKey);
					if ($retval !== null)
					{
						reset($ffXmlElement->childs);
						return $retval;
					}
				}
			}
			reset($ffXmlElement->childs);
		}
		return null;
	}
}
