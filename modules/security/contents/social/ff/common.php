<?php
// very simple SSO mechanism to demonstrate FF Web OAuth2 and SSO capability
// by Samuele Diella, January 2017
// common php version

define("FF_SSO_OAUTH2_URL", "http://localhost/ma/oauth2");

switch($_SERVER["HTTP_HOST"])
{
	case "sso1.sam":
		define("FF_SSO_CLIENT_ID", "7a388bf0b0e0033f7748ad98ba524f13");
		define("FF_SSO_CLIENT_SECRET", "f4748d23fbf3732ba6bdc75ccee0eb813a42f8fa");
		break;
	case "sso2.sam":
		define("FF_SSO_CLIENT_ID", "b575b118cf680ca3294ca0b467529720");
		define("FF_SSO_CLIENT_SECRET", "");
		break;
	case "sso3.sam":
		define("FF_SSO_CLIENT_ID", "e2fb997b861b736e1c6a3f66b22a5b95");
		define("FF_SSO_CLIENT_SECRET", "daa3064f9994502ecd2af411508b32dd4f7ffa74");
		break;
	case "sso4.sam":
		define("FF_SSO_CLIENT_ID", "998b894cd2dabea07bbed194438fdc86");
		define("FF_SSO_CLIENT_SECRET", "3ae01faa0f87130948263bbc3d66b2c42dbb2459");
		break;
	case "sso5.sam":
		define("FF_SSO_CLIENT_ID", "a77d8151546e64ee5f9b999ce12dac0d");
		define("FF_SSO_CLIENT_SECRET", "");
		break;
}

function common_oauth_api_call($endpoint, $fields, $get = false)
{
	// public client?
	if (isset($fields["client_secret"]) && !strlen($fields["client_secret"]))
		unset($fields["client_secret"]);
	
	if (!$get)
	{
		$ch = curl_init(FF_SSO_OAUTH2_URL . $endpoint);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	}
	else
	{
		$ch = curl_init(FF_SSO_OAUTH2_URL . $endpoint . "?" . http_build_query($fields));
		curl_setopt($ch, CURLOPT_POST, false);
	}
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if (!$ret = curl_exec($ch))
	{
		var_dump(curl_error($ch), $ret, $endpoint); exit;
		throw new Exception("Curl Error:" . curl_error($ch));
	}
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$ret_decoded = json_decode($ret, true);
	if (json_last_error() !== JSON_ERROR_NONE)
		die("server error: " . $ret);
		
	if (200 !== $http_code)
	{
		// oauth2 error
		if (!isset($ret_decoded["error"]))
		{
			$ret_decoded["error"] = "unmanaged_error";
			$ret_decoded["error_description"] = "Woops, something bad happened..";
		}
		
		return $ret_decoded;
	}
	
	return $ret_decoded;
}

function common_internal_oauth_error($ret)
{
	?>
	<h2>Error: <?= $ret["error"] ?></h2>
	<h3>Error: <?= $ret["error_description"] ?></h3>
	<?php
	
	if (isset($_SESSION["userdata"]))
	{
		?>
		<a href="?logout">Logout</a>
		<?php
	}
	exit;
}

function common_external_oauth_error($error = null)
{
	if (is_string($error))
	{
		?>
		<h1><?= $error ?></h1>
		<?php
	}
	else if (is_array($error) && isset($error["error"]))
	{
		?>
		<h1>Error: <?= $error["error"] ?></h1>
		<h2>Error: <?= $error["error_description"] ?></h2>
		<?php
	}
	else
	{
		?>
		<h1>Opps, something wrong!</h1>
		<?php
	}
	
	if (isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"]))
	{
		?>
		<a href="<?= $_REQUEST["ret_url"] ?>">Back</a>
		<?php
	}
	else
	{
		?>
		<button onclick = "window.close()">Close</button>
		<?php
	}
	exit;
}

function common_redirect_back()
{
	if (isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"]))
		header("Location: " . $_REQUEST["ret_url"], true, 302);
	else
		header("Location: /testsso/index.php", true, 302);
	exit;
}