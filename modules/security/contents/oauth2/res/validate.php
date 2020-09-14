<?php
header('Access-Control-Allow-Origin: *'); // allow JS access
 
modsec_OAuth2_ResourceController("userinfo", function ($scopes, $request, $response, $server) {
	
	$outdata = array();
	
	cm::jsonParse(array(
		"valid" => true
	));
	exit;
});
