<?php
$globals = ffGlobals::getInstance("mod_notifier");

$queue = "mod_notifier_message_queue";
if (strlen($_REQUEST["queue"]))
{
	if (strpos($_REQUEST["queue"], $queue) === 0)
		$queue = $_REQUEST["queue"];
	else
		$queue .= "_" . $_REQUEST["queue"];
}

$results = array();

if (!MOD_NOTIFIER_SKIP)
{
	$session_started = mod_security_check_session(false) || defined("MOD_NOTIFIER_SESSION_STARTED");

	if (!$session_started && MOD_NOTIFIER_USE_OWN_SESSION)
	{
		if (isset($_POST[session_name()]))
			session_id($_POST[session_name()]);
		elseif (isset($_GET[session_name()]))
			session_id($_GET[session_name()]);
		elseif (isset($_COOKIE[session_name()]))
			session_id($_COOKIE[session_name()]);
		@session_start();

		if (!defined("MOD_NOTIFIER_SESSION_STARTED"))
			define("MOD_NOTIFIER_SESSION_STARTED", true);

		$session_started = true;
	}
	
	if ($session_started)
	{
		$message_queue = get_session($queue);

		if (is_array($message_queue) && count($message_queue))
		{
			foreach ($message_queue as $key => $value)
			{
				$results[] = array(
					"content"		=> mod_notifier_parse_message(
											$value["text"]
											, $value["type"]
											, $value["html"]
											, $value["options"]
										)
					, "options"		=> $value["options"]
				);
			}
			$message_queue = array();
			set_session($queue, $message_queue);
		}
	}
}

cm::jsonParse(array(
		"success" => true
		, "modules" => array(
			"notifier" => array(
				"messages" => $results
			)
		)
	)
);
exit;
