<?php
if ($cm->isXHR())
	return;

if(defined("FF_THEME_ADMIN") && FF_THEME_ADMIN)
    $cm->oPage->theme = FF_THEME_ADMIN;

$cm->oPage->tplAddCSS("ff.theme");
if ($cm->layout_vars["layer"] === null)
{
    if (strlen($cm->modules["restricted"]["options"]["layout"]["layer"]))
        $cm->oPage->layer = $cm->modules["restricted"]["options"]["layout"]["layer"];
    else
        $cm->oPage->layer = "restricted";
}

if (isset($cm->modules["restricted"]["layout_bypath"]) && count($cm->modules["restricted"]["layout_bypath"]))
{
	foreach ($cm->modules["restricted"]["layout_bypath"] as $key => $value)
	{
		if  (strpos($cm->path_info, $key) === 0)
		{
			if (is_array($value))
			{
				foreach ($value as $section => $option)
				{
					$cm->modules["restricted"]["options"]["layout"][$section] = $option;
				}
			}
			elseif ($value == "nolayout")
			{
				reset($cm->modules["restricted"]["layout_bypath"]);
				return;
			}
		}
	}
	reset($cm->modules["restricted"]["layout_bypath"]);
}

$cm->oPage->addEvent("getLayerDir", "mod_restricted_getLayerDir", ffEvent::PRIORITY_HIGH, -100, ffEvent::BREAK_NOT_EQUAL, null);
function mod_restricted_getLayerDir(ffPage_base $oPage, $file, $lastres = null)
{
	if ($oPage->layer_dir === null)
        return ffCommon_dirname(cm_cascadeFindTemplate("/layouts/" . $file, "restricted"));
		//return ffCommon_dirname(cm_moduleCascadeFindTemplateByPath("restricted", "/layouts/" . $file, $oPage->getTheme(), false));
	else
		return null;
}

$cm->oPage->addEvent("getLayoutDir", "mod_restricted_getLayoutDir", ffEvent::PRIORITY_HIGH, -100, ffEvent::BREAK_NOT_EQUAL, null);
function mod_restricted_getLayoutDir(ffPage_base $oPage, $file, $lastres = null)
{
    return ffCommon_dirname(cm_cascadeFindTemplate("/layouts/" . $file, "restricted"));
	//return ffCommon_dirname(cm_moduleCascadeFindTemplateByPath("restricted", "/layouts/" . $file, $oPage->getTheme(), false));
}

$cm->oPage->addEvent("on_tpl_layer_loaded", "mod_restricted_on_tpl_layer_loaded", ffEvent::PRIORITY_LOW);


/*
if (isset($cm->modules["restricted"]["sections"]))
{
	foreach ($cm->modules["restricted"]["sections"] as $key => $value)
	{
		if (!isset($cm->modules["restricted"]["options"]["layout"][$key]) || strlen($cm->modules["restricted"]["options"]["layout"][$key]))
		{
			if (!isset($cm->oPage->sections[$key]))
			{
				$cm->oPage->addSection($key);
				if (!isset($cm->modules["restricted"]["options"]["layout"][$key]))
					$cm->oPage->sections[$key]["name"] = "restricted";
				else
					$cm->oPage->sections[$key]["name"] = $cm->modules["restricted"]["options"]["layout"][$key];
			}

			$cm->oPage->sections[$key]["events"]->addEvent(
			    "on_load_template"
                , "mod_restricted_cm_on_load_topbar"
                , null
                , 0
                , null
                , null
                , array(
                    $key
                    , (isset($cm->modules["restricted"]["sections"][$key]["attributes"])
                        ? $cm->modules["restricted"]["sections"][$key]["attributes"]
                        : null
                    )
                )
            );
		}
	}
	reset($cm->modules["restricted"]["sections"]);
}*/

/*
if (isset($cm->modules["restricted"]["sections"]["nav"]))
{
	foreach ($cm->modules["restricted"]["sections"]["nav"] as $key => $value)
	{
		if (strpos($key, "__") === 0)
			continue;
		
		if (!isset($cm->modules["restricted"]["options"]["layout"][$key]) || strlen($cm->modules["restricted"]["options"]["layout"][$key]))
		{
			if (!isset($cm->oPage->sections[$key]))
			{
				$cm->oPage->addSection($key);
				if (!isset($cm->modules["restricted"]["options"]["layout"][$key]))
					$cm->oPage->sections[$key]["name"] = "restricted";
				else
					$cm->oPage->sections[$key]["name"] = $cm->modules["restricted"]["options"]["layout"][$key];
			}
            //fullbar
			if(!$default)
				$cm->oPage->sections[$key]["events"]->addEvent(
				    "on_load_template"
                    , "mod_restricted_cm_on_load_navbar"
                    , null
                    , 0
                    , null
                    , null
                    , array(
                        $key
                        , (isset($cm->modules["restricted"]["sections"]["nav"]->$key->__attributes)
                            ? $cm->modules["restricted"]["sections"]["nav"]->$key->__attributes
                            : null
                        )
                    )
                );
		}
	}
	reset($cm->modules["restricted"]["sections"]["nav"]);
}*/
/*
$filename = cm_cascadeFindTemplate("/css/ff.modules.restricted.css", "restricted");
//$filename = cm_moduleCascadeFindTemplateByPath("restricted", "/css/ff.modules.restricted.css", $cm->oPage->theme);
$ret = cm_moduleGetCascadeAttrs($filename);
$cm->oPage->tplAddCSS("ff.modules.restricted.css", array(
	"file" => $filename
	, "path" => $ret["path"]
	, "priority" => cm::LAYOUT_PRIORITY_HIGH
	, "index" => 100
));*/
//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());

$globals = ffGlobals::getInstance("__mod_restricted__");
$globals->access = false;
$cm->modules["restricted"]["sel_topbar"] = null;
$cm->modules["restricted"]["sel_navbar"] = null;

$filename = "/modules/restricted/themes/" . cm_getMainTheme() . "/javascript/ff.modules.restricted.js";
$cm->oPage->tplAddJs("ff.modules.restricted", array(
    "file" =>  basename($filename)
    , "path" => ffCommon_dirname($filename)
));

//------------------------------------------------------------------------------------------------------------------------------------------------
// Rileva la topbar e la navbar selezionata

if ($cm->modules["restricted"]["sel_topbar"] === null)
{
    $restricted_path = $cm->router->getRuleById("restricted")->reverse;

	$path_parts = explode("/", ltrim($cm->real_path_info, "/"));

	if($cm->modules["restricted"]["menu_bypath"][$restricted_path . "/" . $path_parts[0]]) {
        $cm->modules["restricted"]["sel_topbar"] =& $cm->modules["restricted"]["menu_bypath"][$restricted_path . "/" . $path_parts[0]][0];
        $cm->modules["restricted"]["sel_topbar"]["selected"] = true;
        $cm->modules["restricted"]["sel_navbar"] = null;

        if($cm->modules["restricted"]["sel_topbar"]["elements"][$path_parts[1]]) {
            $cm->modules["restricted"]["sel_navbar"] =& $cm->modules["restricted"]["sel_topbar"]["elements"][$path_parts[1]];
            $cm->modules["restricted"]["sel_navbar"]["selected"] = true;

        }
    }
}

if ($cm->modules["restricted"]["sel_topbar"] === null)
{
	if (isset($cm->modules["restricted"]["menu"]["default"]))
	{
		$cm->modules["restricted"]["sel_topbar"] =& $cm->modules["restricted"]["menu"]["default"];
		$cm->modules["restricted"]["sel_topbar"]["selected"] = true;
	}
	elseif (isset($cm->modules["restricted"]["menu_bypath"]["/"]))
	{
		$cm->modules["restricted"]["sel_topbar"] =& $cm->modules["restricted"]["menu_bypath"]["/"][0];
		$cm->modules["restricted"]["sel_topbar"]["selected"] = true;
	}
}

if ($cm->modules["restricted"]["sel_topbar"] === null ) {
    ffDialog(false, "okonly", "Access Denied", "Restricted Area not Set", FF_SITE_PATH . "/", FF_SITE_PATH . "/", FF_SITE_PATH . "/dialog");
} else if (mod_restricted_check_no_permission($cm->modules["restricted"]["sel_topbar"])) {
	foreach ($cm->modules["restricted"]["menu"] as $key => $value)
	{
		if (mod_restricted_check_no_permission($value)) {
            ffRedirect(FF_SITE_PATH . $value["path"] . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
        }
	}
	ffDialog(false, "okonly", "Access Denied", "Access Denied", FF_SITE_PATH . "/", FF_SITE_PATH . "/",  FF_SITE_PATH . "/dialog");
}
else
{
    if ($cm->modules["restricted"]["sel_navbar"] === null && strlen($cm->modules["restricted"]["sel_topbar"]["redir"])) {
        ffRedirect(FF_SITE_PATH . $cm->modules["restricted"]["sel_topbar"]["redir"] . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    }
	if ($cm->modules["restricted"]["sel_navbar"] !== null && 
		(
            mod_restricted_check_no_permission($cm->modules["restricted"]["sel_navbar"])

/*			!mod_restricted_checkacl_bylevel($cm->modules["restricted"]["sel_navbar"]["acl"])
			|| (
					(!ffIsset($cm->modules["restricted"]["sel_navbar"], "hide") || !$cm->modules["restricted"]["sel_navbar"]["hide"])
					&& (
							!$cm->modules["restricted"]["sel_navbar"]["profiling_skip"]
							&& !mod_sec_checkprofile_bypath($cm->modules["restricted"]["sel_navbar"]["path"])
					)
				)*/
		)
	)
	{
		if (count($cm->modules["restricted"]["sel_topbar"]["elements"]))
		{
			foreach ($cm->modules["restricted"]["sel_topbar"]["elements"] as $key => $value)
			{
				if (mod_restricted_check_no_permission($value)) {
                    ffRedirect(FF_SITE_PATH . $value["path"] . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
                }
			}
		}
		ffDialog(false, "okonly", "Access Denied", "Insufficent Permission", FF_SITE_PATH . "/", FF_SITE_PATH . "/",  FF_SITE_PATH . "/dialog");
	}
}

$cm->oPage->addEvent("on_tpl_layer_loaded", function($oPage, $tpl_layer) {
    $cm = cm::getInstance();

    if (isset($cm->modules["restricted"]["sections"]))
    {
        foreach ($cm->modules["restricted"]["sections"] as $key => $value)
        {
            if (!isset($cm->modules["restricted"]["options"]["layout"][$key]) || strlen($cm->modules["restricted"]["options"]["layout"][$key]))
            {
                $cm->oPage->sections[$key]["attributes"] =  $value["attributes"];
                if(is_callable("on_load_section_" . $key)) {
                    if ($cm->oPage->sections[$key]["events"] === null) {
                        $cm->oPage->sections[$key]["events"] = new ffEvents();
                    }

                    $cm->oPage->sections[$key]["events"]->addEvent("on_load_template", "on_load_section_" . $key);
                }
            }
        }
        reset($cm->modules["restricted"]["sections"]);
    }
});

$res = $cm->modules["restricted"]["events"]->doEvent("on_layout_process", array(&$cm->modules["restricted"]));
/*$rc = end($res);
if ($rc !== null)
	return; // TODO: da verificare
*/
