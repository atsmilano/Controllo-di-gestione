<?php
// very simple SSO mechanism to demonstrate FF Web OAuth2 and SSO capability
// by Samuele Diella, January 2017
// WebAuth callback
require __DIR__ . "/common.php";

if (isset($_REQUEST["error"]))
{
	common_external_oauth_error($_REQUEST);
}

if (!isset($_REQUEST["code"]) && !isset($_REQUEST["state"])) // code = auth_code flow , state = CSRF protection
{
	common_external_oauth_error();
}

session_start();

// already logged?
if (isset($_SESSION["userdata"]))
{
	// inside SSO chain?
	if (isset($_REQUEST["sso_start"]) && isset($_REQUEST["sso_next"]))
	{
		// get next url
		$tmp_fields = array(
			"access_token" => $_SESSION["access_token"]
		);

		if (isset($_REQUEST["ret_url"]))
			$tmp_fields["ret_url"] = $_REQUEST["ret_url"];
		if (isset($_REQUEST["sso_start"]))
			$tmp_fields["sso_start"] = $_REQUEST["sso_start"];
		if (isset($_REQUEST["sso_next"]))
			$tmp_fields["sso_next"] = $_REQUEST["sso_next"];

		$api_ret = common_oauth_api_call("/res/ssonext", $tmp_fields);

		if (isset($api_ret["error"]))
		{
			if ($api_ret["error"] === "expired_token")
			{
				// try to refresh token
				$api_ret = common_oauth_api_call("/refreshtoken", array(
					"client_id" => MOD_SEC_SOCIAL_FF_CLIENT_ID,
					"client_secret" => MOD_SEC_SOCIAL_FF_CLIENT_SECRET,
					"refresh_token" => $_SESSION["refresh_token"]
				));

				if (isset($api_ret["error"]))
				{
					common_external_oauth_error($api_ret);			
				}
				else
				{
					$_SESSION["access_token"] = $api_ret["access_token"];
					if (isset($api_ret["refresh_token"]))
						$_SESSION["refresh_token"] = $api_ret["refresh_token"];

					// issue request again
					$api_ret = common_oauth_api_call("/res/ssonext", $tmp_fields);
					
					if (isset($api_ret["error"]))
					{
						// avoid loop
						common_external_oauth_error($api_ret);			
					}
				}
			}
			else
			{
				common_external_oauth_error($api_ret);			
			}
		}		
		
		if (isset($api_ret["next_sso_url"]) && strlen($api_ret["next_sso_url"]))
		{
			header("Location: " . $api_ret["next_sso_url"], true, 302);
			exit;
		}
		else
		{
			common_redirect_back();
		}
	}
	else
	{
		common_redirect_back();
	}
}


if (!isset($_REQUEST["sso_state"]) && (
		!isset($_REQUEST["state"]) || $_REQUEST["state"] !== $_SESSION["state"]
	))
{
	common_external_oauth_error("CSRF attack detected, you have been reported!");
}

// try to get token
$tmp_fields = array(
	"code" => $_REQUEST["code"],
	"client_id" => FF_SSO_CLIENT_ID,
	"client_secret" => FF_SSO_CLIENT_SECRET
);

if (isset($_REQUEST["ret_url"]))
	$tmp_fields["ret_url"] = $_REQUEST["ret_url"];
if (isset($_REQUEST["sso_state"]))
	$tmp_fields["sso_state"] = $_REQUEST["sso_state"];
if (isset($_REQUEST["sso_start"]))
	$tmp_fields["sso_start"] = $_REQUEST["sso_start"];
if (isset($_REQUEST["sso_next"]))
	$tmp_fields["sso_next"] = $_REQUEST["sso_next"];

$api_ret = common_oauth_api_call("/webtoken", $tmp_fields);

if (isset($api_ret["error"]))
	common_external_oauth_error($api_ret);

$token_request_data = $api_ret;
$_SESSION["access_token"] = $token_request_data["access_token"];
if (isset($token_request_data["refresh_token"]))
	$_SESSION["refresh_token"] = $token_request_data["refresh_token"];

// use token to get userinfo
$api_ret = common_oauth_api_call("/res/userinfo", array(
	"access_token" => $_SESSION["access_token"]
));

if (isset($api_ret["error"]))
	common_external_oauth_error($api_ret);

$userinfo_data = $api_ret;

// we have everything wee need, now create valid session! (mod_security_create_session)
$_SESSION["userdata"] = $userinfo_data["userinfo"];

// inside sso?
if (isset($token_request_data["next_sso_url"]))
{
	header("Location: " . $token_request_data["next_sso_url"], true, 302);
	exit;
}

// outside popup?
if (isset($_REQUEST["ret_url"]) && strlen($_REQUEST["ret_url"]))
{
	header("Location: " . $_REQUEST["ret_url"], true, 302);
	/*?>
		<h1>Logged in!</h1>
		<script type="text/javascript">
			setTimeout(function () {
				top.location.href = "<?= $_REQUEST["ret_url"] ?>";
				window.close();
			}, 2000);
		</script>
	<?php*/
	exit;
}
else
{
	//close popup and refresh previous page
	?>
		<h1>Logged in!</h1>
		<script type="text/javascript">
			setTimeout(function () {
				window.opener.top.location.reload();
				window.close();
			}, 2000);
		</script>
	<?php
	exit;
}
