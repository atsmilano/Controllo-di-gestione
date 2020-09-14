<?php
namespace FF\Libs\OAuth2\BaseClient\Exceptions;

class CurlException extends \Exception
{
    public function __construct($ch, Exception $previous = null) {
        // make sure everything is assigned properly
		parent::__construct("HTTP " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . " " . curl_error($ch), curl_errno($ch), $previous);
		
		curl_close($ch);
    }
}
