<?php
$server = modsec_getOauth2Server();

$_REQUEST["grant_type"] = $_GET["grant_type"] = $_POST["grant_type"] = "client_credentials";

$response = new OAuth2\Response();
$server->handleTokenRequest(OAuth2\Request::createFromGlobals(), $response);

$response->send();
exit;
