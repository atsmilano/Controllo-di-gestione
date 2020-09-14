<?php
$UserNID = null;
$rc = mod_security_check_session(false);

if ($rc === true)
{
	$UserNID = get_session("UserNID");
}

$mod_sec_login = $cm->router->getRuleById("mod_sec_login");

cm::jsonParse(
		array(
			"status" => $rc
			, "login_path" => (string)$mod_sec_login->reverse
			, "session_name" => session_name()
			, "session_id" => session_id()
			, "UserNID" => $UserNID
		)
	);
exit;