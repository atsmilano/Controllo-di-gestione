<?php
namespace FF\Libs\OAuth2\BaseClient\Exceptions;

class ProtocolException extends \Exception
{
	private $oauth_error_description;
	
    public function __construct($oauth_error_data, Exception $previous = null) {
        $this->oauth_error_description = $oauth_error_data["error_description"];
    
        // make sure everything is assigned properly
        parent::__construct($oauth_error_data["error"], 0, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . " - [{$this->message}] - {$this->oauth_error_description}\n";
    }
}
