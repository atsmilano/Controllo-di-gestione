<?php
namespace FF\Libs\OAuth2\BaseClient\TokenType;

class Bearer extends AccessToken
{
	private $scope = null;
	
	public function __construct($options = null) {
		$this->type = "bearer";
		
		parent::__construct($options);
		
		if (ffIsset($options, "scope"))
			$this->scope = $options["scope"];
	}
}
