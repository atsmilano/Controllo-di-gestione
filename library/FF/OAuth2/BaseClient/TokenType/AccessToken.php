<?php
namespace FF\Libs\OAuth2\BaseClient\TokenType;

abstract class AccessToken
{
	protected $token_id = null;
	protected $expires_in = null;
	protected $type = null;


	public function __construct($options = null) {
		if (ffIsset($options, "access_token"))
			$this->token_id = $options["access_token"];
			
		if (ffIsset($options, "expires_in"))
			$this->expires_in = $options["expires_in"];
	}
	
	public function get_token_id() {
		return $this->token_id;
	}
	
	public function get_expires_in() {
		return $this->expires_in;
	}
}
