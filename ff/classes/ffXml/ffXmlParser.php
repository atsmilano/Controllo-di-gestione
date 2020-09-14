<?php
/**
 * @package FormsFramework
 * @ignore
 */

/**
 * @package FormsFramework
 * @ignore
 */
class ffXmlParser
{
	public $root_tag 	= "ffxml";
	
	// ---------------------------------------------------------------
	//  PRIVATE VARS (used by code, don't touch or may be explode! :-)
	
	var 	$xml_parser 		= null;
	
	var 	$xml_lastref		= null;
	var 	$xml_lastname		= "";
	var 	$xml_start_tag		= false;
	var 	$xml_end_tag		= false;
			
	var 	$inside_data		= false;
	
	static 	$singleton			= null;

	// ---------------------------------------------------------------
	//  PUBLIC FUNCS
	// ---------------------------------------------------------------

	//  CONSTRUCTOR
	function __construct()
	{
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
	}
	
	static function fromFile($file, $root_element = null, $root_tag = null)
	{
		if (ffXmlParser::$singleton === null)
			ffXmlParser::$singleton = new ffXmlParser();
			
		if ($root_tag !== null)
			ffXmlParser::$singleton->root_tag = $root_tag;
			
		ffXmlParser::$singleton->xml_lastref		= null;
		ffXmlParser::$singleton->xml_lastname		= "";
		ffXmlParser::$singleton->xml_start_tag		= false;
		ffXmlParser::$singleton->xml_end_tag		= false;
		ffXmlParser::$singleton->inside_data		= false;

		return ffXmlParser::$singleton->parseFile($file, $root_element);
	}
	
	function addElement($name, $attribs)
	{
		$tmp = new ffXmlElement();
		$tmp->type 	= $name;
		
		if (isset($attribs["id"]))
		{
			$tmp->id = $attribs["id"];
			unset($attribs["id"]);
		}
		
		$tmp->attribs 	= $attribs;
		
		$this->xml_lastref[0]->addChild($tmp);
		$this->xml_lastname = $name;
		$this->xml_lastref[0] = $tmp;
	}
		
	function startElement($xml_parser, $name, $attribs)
	{
		if (strtoupper($name) == strtoupper($this->root_tag))
			$this->xml_start_tag = true;
		else if ($this->xml_start_tag === false)
			ffErrorHandler::raise("ffXml: Start tag not found.", E_USER_ERROR, $this, get_defined_vars());
		else
		{
			$this->addElement($name, $attribs);
		}

		$this->inside_data = true;
	}
		
	function characterData($xml_parser, $data)
	{
		if ($this->inside_data)
			$this->xml_lastref[0]->cdata .= $data;
	}
		
	function endElement($xml_parser, $name)
	{
		if (strtoupper($name) == strtoupper($this->root_tag))
		{
			$this->xml_end_tag = true;
			return;
		}
		else
		{
			$this->xml_lastref[0] = $this->xml_lastref[0]->father[0];
		}
		
		$this->inside_data = false;
	}
		
	function parseFile($xml_file, $root_element = null)
	{
		if (!($fp = @fopen($xml_file, "r")))
			ffErrorHandler::raise("ffXml: File not found or access denied.", E_USER_ERROR, $this, get_defined_vars());
			
		$new_root_element = null;
		if ($root_element === null)
		{
			$new_root_element = new ffXmlElement();
			$this->xml_lastref = array(&$new_root_element);
		}
		else
			$this->xml_lastref = $root_element;
		
		while ($data = fread($fp, 4096))
		{
			if (!xml_parse($this->xml_parser, $data, feof($fp)))
			{
				fclose($fp);
				ffErrorHandler::raise(
					"ffXml: " . xml_error_string(xml_get_error_code($this->xml_parser)) . " 
					at line " . xml_get_current_line_number($this->xml_parser) . " while parsing entity " .$this->xml_lastname
					, E_USER_ERROR, $this, get_defined_vars());
			}
		}

		fclose($fp);

		if ($this->xml_end_tag === false)
			ffErrorHandler::raise("ffXml: End tag not found.", E_USER_ERROR, $this, get_defined_vars());
		elseif ($new_root_element !== null)
			return $new_root_element;
		else
			return true;
	}

	function parseData($xml_data, $root_element = null)
	{
		if (!strlen($xml_data))
			ffErrorHandler::raise("ffXml: Empty XML data.", E_USER_ERROR, $this, get_defined_vars());
			
		$new_root_element = null;
		if ($root_element === null)
		{
			$new_root_element = new ffXmlElement();
			$this->xml_lastref = array(&$new_root_element);
		}
		else
			$this->xml_lastref = $root_element;
		
		if (!xml_parse($this->xml_parser, $xml_data, true))
		{
			ffErrorHandler::raise(
				"ffXml: " . xml_error_string(xml_get_error_code($this->xml_parser)) . " 
				at line " . xml_get_current_line_number($this->xml_parser) . " while parsing entity " .$this->xml_lastname
				, E_USER_ERROR, $this, get_defined_vars());
		}

		if ($this->xml_end_tag === false)
			ffErrorHandler::raise("ffXml: End tag not found.", E_USER_ERROR, $this, get_defined_vars());
		elseif ($new_root_element !== null)
			return $new_root_element;
		else
			return true;
	}
}
