<?php
namespace FF\Libs\OAuth2\BaseClient\GrantType;

use FF\Libs\OAuth2\BaseClient\Exceptions;
use FF\Libs\OAuth2\BaseClient\TokenType;

class ClientCredentials
{
	private $options = array();
	
	function __construct($options = null) {
		if (is_array($options))
			$this->options = array_merge($this->options, $options);
	}
	
	public function getAccessToken($options = array())
	{
		$tmp_opts = array_merge($this->options, $options);
		
		$fields = array(
			"grant_type" => "client_credentials"
			, "client_id" => $tmp_opts["client_id"]
			, "client_secret" => $tmp_opts["client_secret"]
		);
		
		if (ffIsset($this->options, "apikey")) {
			$fields["apikey"] = $tmp_opts["apikey"];
		}
		
		$ch = curl_init($tmp_opts["url_access_token"]);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$ret = curl_exec($ch);
		
		if ($ret === false || curl_errno($ch)) 
			throw new Exceptions\CurlException($ch);
		
		curl_close($ch);
		
		$tmp_data = json_decode($ret, true);
		if (json_last_error() !== JSON_ERROR_NONE)
			throw new Exceptions\ProtocolException(array(
				"error_description" => print_r($ret, true)
				, "error" => "customerror_wrong_data_format"
			));
		if (ffIsset($tmp_data, "error"))
			throw new Exceptions\ProtocolException($tmp_data);
		
		return new TokenType\Bearer($tmp_data);
	}
}
