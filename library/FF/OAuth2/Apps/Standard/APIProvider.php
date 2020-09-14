<?php
namespace FF\Libs\OAuth2\Apps\Standard;

use \FF\Libs\OAuth2\BaseClient;
use \FF\Libs\OAuth2\BaseClient\Exceptions;

class APIProvider {
	private $options = array(
		"base_url"	=> null,
		"url_token"	=> "/token",
		"user"		=> null,
		"password"	=> null,
		"apikey"	=> null
	);
	
	private $last_token = null;
	
	public $exceptions = array();
	
	function __construct($options = null) {
		if (is_array($options))
			$this->options = array_merge($this->options, $options);
	}
	
	function request($url, $fields, $token = null)
	{
		$this->exceptions = array();
		
		$tmp_fields = $fields;
		if (!ffIsset($tmp_fields, "apikey") && $this->options["apikey"] !== null)
			$tmp_fields["apikey"] = $this->options["apikey"];
		
		if ($token !== null) {
			if (is_string($token))
				$this->last_token = new BaseClient\TokenType\Bearer(array(
					"access_token" => $token
				));
			else
				$this->last_token = $token;
		}
		
		if ($token === null) {
			$ret = $this->renew_token();
			if (!$ret)
				return FALSE;
		}
		
		try {
			$req = new BaseClient\Request($this->last_token, $this->options["base_url"] . $url, $tmp_fields);
		} catch (\Exception $ex) {
			$this->exceptions[] = $ex;
			
			// try to renews
			if ($ex->getMessage() === "expired_token" || $ex->getMessage() === "invalid_token" || $ex->getMessage() === "unauthorized")
			{
				$ret = $this->renew_token();
				if (!$ret)
					return FALSE;
				
				try {
					$req = new BaseClient\Request($this->last_token, $this->options["base_url"] . $url, $tmp_fields);
				} catch (\Exception $ex) {
					$this->exceptions[] = $ex;
					return FALSE;
				}
			}
			else
				return FALSE;
		}
		
		return $req->get_ret();
	}
	
	private function renew_token() {
		$tmp_fields = [
			'client_id'                => $this->options["user"],
			'client_secret'            => $this->options["password"],
			'url_access_token'         => $this->options["base_url"] . $this->options["url_token"]
		];
		
		if ($this->options["apikey"] !== null)
			$tmp_fields["apikey"] = $this->options["apikey"];
		
		$provider = new BaseClient\GrantType\ClientCredentials($tmp_fields);

		try {
			$this->last_token = $provider->getAccessToken();
		} catch (\Exception $ex) {
			$this->exceptions[] = $ex;
			$this->last_token = null;
			return FALSE;
		}
		
		return TRUE;
	}
	
	function get_exceptions() {
		return $this->exceptions;
	}
	
	function get_last_ex() {
		return end($this->exceptions);
	}
	
	function get_last_token() {
		return $this->last_token;
	}
}
