<?php
$globals = ffGlobals::getInstance("mod_notifier");

$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/notifier/applets/box/box.html", $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/notifier/themes", "/applets/box/box.html", $cm->oPage->theme);

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("box.html", "main");

$tpl->set_var("theme", $cm->oPage->theme);
$tpl->set_var("site_path", $cm->oPage->site_path);
$tpl->set_var("mod-notifier-get-message", (string)$cm->router->getRuleById("mod-notifier-get-message")->reverse);

$queue = "mod_notifier_message_queue";
$append = "";
if (isset($applet_params["name"]))
{
	if ($applet_params["name"] == "dialog")
	{
		$dialog = $cm->oPage->getXHRCtx();
		if ($dialog !== false)
		{
			$append = $dialog;
			$dialog = true;
		}
		else
			$append = $applet_params["name"];
	}
	else
		$append = $applet_params["name"];
}

if (strlen($append))
	$queue .= "_" . $append;

$tpl->set_var("queue", $append);
$tpl->set_var("notifier_class", ($dialog === true ? "dialogSubTitle ": "") . "mod-notifier-messages " . $queue);

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
				$message = mod_notifier_parse_message(
					$value["text"]
					, $value["type"]
					, $value["html"]
					, $value["options"]
				);

				$message = str_replace("\"", "\\\"", $message);
				$message = str_replace("\n", "", $message);
				$message = str_replace("\r", "", $message);
				$tpl->set_var("content", $message);

				$tpl->set_var("SectOption", "");
				$i = 0;
				foreach($value["options"] as $key => $value)
				{
					$i++;
					if ($i > 1)
						$tpl->set_var("comma", ", ");
					else
						$tpl->set_var("comma", "");

					$tpl->set_var("name", $key);
					if ($value === null)
						$tpl->set_var("value", "null");
					else if (is_bool($value))
					{
						if ($value)
							$tpl->set_var("value", "true");
						else
							$tpl->set_var("value", "false");
					}
					else if (is_string($value))
						$tpl->set_var("value", "\"" . str_replace("\"", "\\\"", $value) . "\"");
					else if (is_numeric($value))
						$tpl->set_var("value", $value);
					$tpl->parse("SectOption", true);
				}

				$tpl->parse("SectMessage", true);
			}
			
			$message_queue = array();
			set_session($queue, $message_queue);
		}
	}
}

$tpl->set_var("modules_external_path", CM_MODULES_PATH);
$filename = cm_moduleCascadeFindTemplateByPath("notifier", "/javascript/ff.ffPage.notifier.js", $cm->oPage->theme);
$ret = cm_moduleGetCascadeAttrs($filename);
$cm->oPage->tplAddJs("ff.ffPage.notifier", array(
	"file" => $filename
	, "path" => $ret["path"]
	, "priority" => cm::LAYOUT_PRIORITY_HIGH
	, "index" => -1000
));

if(MOD_NOTIFIER_DISABLE_AJAX) // by Alex
{
	$tpl->set_var("SectHeaders", "");
	$tpl->set_var("SectFooters", "");	
} 
else 
{
    $cm->oPage->tplAddJs("ff.ajax");

    if ($dialog === true)
		$tpl->parse("SectDialog", false);
	else
		$tpl->set_var("SectDialog", "");

    $tpl->parse("SectMessageAsync", false);

	$tpl->parse("SectHeaders", false);
	$tpl->parse("SectFooters", false);
}

$out_buffer = $tpl->rpparse("main", false);
