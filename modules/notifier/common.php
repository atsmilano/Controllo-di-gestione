<?php
/*if (!MOD_NOTIFIER_SKIP)*/
	cm::_addEvent("jsonParse", "mod_notifier_jsonParse");

function mod_notifier_jsonParse(&$arData, &$out, &$add_newline, &$standard_encode, &$standard_opts)
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("mod_notifier");

	$queue = "mod_notifier_message_queue";
	if (strlen($_REQUEST["queue"]))
		$queue .= "_" . $_REQUEST["queue"];

	$results = array();

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
		$full_results = array();
		$deferred = array();
		
		$message_queues = get_session("__MOD_NOTIFIER_QUEUES__");
		if (is_array($message_queues) && count($message_queues)) {
			foreach ($message_queues as $queue => $idontcare)
			{
				$results = array();
				
				$queue_name = "mod_notifier_message_queue";
				if (strlen($queue))
					$queue_name .= "_" . $queue;

				$message_queue = get_session($queue_name);
				if (is_array($message_queue) && count($message_queue))
				{
					$tmp_keys = array_keys($message_queue);
					$modified = false;
					foreach ($tmp_keys as $key)
					{
						if ($message_queue[$key]["defer"])
						{
							$deferred[$queue_name] = true;
							continue;
						}
						
						$modified = true;
						$results[] = array(
							"content"		=> mod_notifier_parse_message(
													$message_queue[$key]["text"]
													, $message_queue[$key]["type"]
													, $message_queue[$key]["html"]
													, $message_queue[$key]["options"]
												)
							, "options"		=> $message_queue[$key]["options"]
						);
						unset($message_queue[$key]);
					}
					if ($modified)
					{
						set_session($queue_name, $message_queue);
						$full_results[$queue_name] = $results;
					}
				}
			}
		}
		
		$notifier = array();
		if (count($full_results))
			$notifier["queues"] = $full_results;
		
		if (count($deferred))
			$notifier["deferred"] = array_keys($deferred);
		
		if (count($notifier))
			$arData = array_replace_recursive($arData, array(
				"modules" => array(
					"notifier" => $notifier
				)
			));
	}

	return;
}

/**
 * 
 * @param type $text
 * @param type $type
 * @param type $queue
 * @param type $html
 * @param type $options Opzioni disponibili:
 *							auto_hide	: (number - MOD_NOTIFIER_HIDE_TIMEOUT) Determina se il box del notifier deve scomparire dopo un certo timeout.
 *											se false, non scompare
 *							url			: (string - null) rende il box cliccabile con collegamento ad un url
 *							url_target	: (string - "_self") la finestra di destinazione dell'url
 *							url_hide	: (bool - false) fa scomparire il box quando si clicca sull'url
 *							close_bt	: (bool - true) visualizza il pulsante di chiusura
 */
function mod_notifier_add_message_to_queue($text, $type = MOD_NOTIFIER_ERROR, $queue = MOD_NOTIFIER_DEFAULT_QUEUE, $html = MOD_NOTIFIER_HTML, $options = array(), $defer = MOD_NOTIFIER_DEFAULT_DEFER)
{
	if (MOD_NOTIFIER_SKIP)
		return;
	
	if (is_array($text))
	{
		$type = ffIsset($text, "type") ? $text["type"] : MOD_NOTIFIER_ERROR;
		$queue = ffIsset($text, "queue") ? $text["queue"] : MOD_NOTIFIER_DEFAULT_QUEUE;
		$html = ffIsset($text, "html") ? $text["html"] : MOD_NOTIFIER_HTML;
		$options = ffIsset($text, "options") ? $text["options"] : array();
		$defer = ffIsset($text, "defer") ? $text["defer"] : MOD_NOTIFIER_DEFAULT_DEFER;
		$text = ffIsset($text, "text") ? $text["text"] : "";
	}
	
	// defaults. Tutto ciò che non dipende da settaggi lato server sono nel JS
	if (!array_key_exists("auto_hide", $options))
		$options["auto_hide"] = MOD_NOTIFIER_HIDE_TIMEOUT;

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
		$queue_name = "mod_notifier_message_queue";
		if (strlen($queue))
			$queue_name .= "_" . $queue;

		$message_queues = get_session("__MOD_NOTIFIER_QUEUES__");
		$message_queues[$queue] = true;
		set_session("__MOD_NOTIFIER_QUEUES__", $message_queues);

		$message_queue = get_session($queue_name);
		$message_queue[] = array(
				"text" => $text
				, "type" => $type
				, "html" => $html
				, "options" => $options
				, "defer" => $defer
			);
		set_session($queue_name, $message_queue);
	}
}

function mod_notifier_parse_message($text, $type, $html, $options)
{
	$cm = cm::getInstance();
	
	$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/notifier/applets/box/message.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/notifier/themes", "/applets/box/message.html", $cm->oPage->theme);

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("message.html", "main");

	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("site_path", $cm->oPage->site_path);
	
	if (!$html)
		$text = ffCommon_specialchars($text);
	
	if (ffIsset($options, "nl2br") && $options["nl2br"])
		$text  = nl2br($text);

	$tpl->set_var("text", $text);
	$tpl->set_var("type", $type);
	
	if (!array_key_exists("close_bt", $options) || $options["close_bt"] === true)
		$tpl->parse("SectCloseBt", false);
	
	/*if (array_key_exists("url", $options) && $options["url"] !== null)
	{
		$tpl->set_var("url", $url);
		$tpl->parse("SectUrlBefore", false);
		$tpl->parse("SectUrlAfter", false);
	}*/

	return $tpl->rpparse("main", false);
}
