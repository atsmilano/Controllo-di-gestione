<?php
// impedisce a google d'indicizzare il servizio
if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false)
{
	die('<html>
<head>
<title>no resource</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noindex,nofollow" />
</head>
</html>');
}

// impedisce l'accesso diretto ai browser
if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	$cm->responseCode(404);
}

$cm->oPage->layer = "empty";
$cm->oPage->template_file = "ffPage_getlibs.html";
$cm->oPage->template_dir = FF_DISK_PATH . "/cm/contents/getlibs";
$cm->oPage->js_loaded = array();
$cm->oPage->sections = array();
$cm->oPage->resetJS();
$cm->oPage->resetCSS();
$cm->oPage->use_own_js = false;
$cm->oPage->compact_js = false;
$cm->oPage->compact_css = false;

//$globals = ffGlobals::getInstance();
//$globals->test = 1;
if (strlen($_REQUEST["widgets"]))
{
	$widgets = $_REQUEST["widgets"];
	$widgets = explode(",", $widgets);

	foreach ($widgets as $widget)
	{
		$parts = explode("/", $widget);

		switch ($parts[0])
		{
			case "ffPage":
				$cm->oPage->widgetLoad($parts[1]);
				break;

			case "ffField":
				$field = ffField::factory($cm->oPage);
				$field->widget = $parts[1];

				$cm->oPage->addContent($field);
				break;

			default:
				$cm->oPage->tplAddJs($widget);
			//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars()); // sistema questa procedura e vergognati
		}
	}
}
elseif (strlen($_REQUEST["lib"]))
{
	$cm->oPage->tplAddJs($_REQUEST["lib"]);
}
else
{
	$cm->responseCode(500);
}
