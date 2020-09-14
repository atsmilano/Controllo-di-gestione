<?php
$server = modsec_getOauth2Server();

$_REQUEST["grant_type"] = $_GET["grant_type"] = $_POST["grant_type"] = "refresh_token";

$response = new OAuth2\Response();
$server->handleTokenRequest(OAuth2\Request::createFromGlobals(), $response)->send();
exit;
