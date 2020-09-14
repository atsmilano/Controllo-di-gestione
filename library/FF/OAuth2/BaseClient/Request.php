<?php
namespace FF\Libs\OAuth2\BaseClient;

use \OAuth2\TokenType;

class Request
{
	private $url;
	private $fields;
	
	private $ret = null;
	
	public function __construct($access_token, $url, $fields = null) {
		if ($access_token === null)
			throw new \BadFunctionCallException("'access_token' param is required");

		if (!is_object($access_token) || !is_a($access_token, "FF\Libs\OAuth2\BaseClient\TokenType\AccessToken")) 
			throw new \BadFunctionCallException("Wrong access_token");
		
		if ($url === null || !strlen($url)) 
			throw new \BadFunctionCallException("'url' param is required");
		
		if (!is_string($url)) 
			throw new \InvalidArgumentException("'url' must be a string");
		
		if ($fields !== null && !is_array($fields)) 
			throw new \InvalidArgumentException("'fields' must be an array");
		
		$this->url = $url;
		$this->fields = $fields;

		$tmp_fields = (is_array($fields) ? $fields : array());
		$tmp_fields["access_token"] = $access_token->get_token_id();
		
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tmp_fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		
		$this->ret = curl_exec($ch);
		
		
		if ($this->ret === false)
			throw new Exceptions\CurlException($ch);
		else {
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			$err_des = null;
			if (strlen($this->ret))
				$err_des = json_decode($this->ret, true);
			
			if ($http_code === 400)
				throw new Exceptions\ProtocolException(array(
					"error_description" => "Malformed request" . ($err_des !== null ? ": " . $err_des["message"] : "")
					, "error" => "malformed"
				));
			elseif ($http_code === 401)
				throw new Exceptions\ProtocolException(array(
					"error_description" => "Token invalid or unauthorized"
					, "error" => "unauthorized"
				));
			elseif ($http_code !== 200)
				throw new Exceptions\CurlException($ch);
		}
		
		curl_close($ch);
		
		$tmp_data = json_decode($this->ret, true);
		if (ffIsset($tmp_data, "error"))
			throw new Exceptions\ProtocolException($tmp_data);
		
		if (json_last_error() === JSON_ERROR_NONE)
			$this->ret = $tmp_data;
	}
	
	public function get_ret() {
		return $this->ret;
	}
}
