<?php
/**
 * @package ContentManager
 * @subpackage cascade_loader
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

// --------------------------------------------
//  CLASS SECTION

ffPage::addEvent				("on_factory", "ffPage_on_factory"					, ffEvent::PRIORITY_HIGH);
ffGrid::addEvent				("on_factory", "ffGrid_on_factory"					, ffEvent::PRIORITY_HIGH);
ffRecord::addEvent			("on_factory", "ffRecord_on_factory"				, ffEvent::PRIORITY_HIGH);
ffDetails::addEvent			("on_factory", "ffDetails_on_factory"				, ffEvent::PRIORITY_HIGH);
ffPageNavigator::addEvent	("on_factory", "ffPageNavigator_on_factory"			, ffEvent::PRIORITY_HIGH);
ffField::addEvent			("on_factory", "ffField_on_factory"					, ffEvent::PRIORITY_HIGH);
ffButton::addEvent			("on_factory", "ffButton_on_factory"				, ffEvent::PRIORITY_HIGH);

function ffPage_on_factory($disk_path, $site_path, $page_path, $theme, $variant)
{
    if (is_null($theme) || $theme === cm_getMainTheme() || !is_null($variant))
        return null;
    else
    {
        return cm_findCascadeClass("ffPage", $theme);
    }
}

function ffGrid_on_factory($page, $disk_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null;
    else
        return cm_findCascadeClass("ffGrid", $theme, null, $variant["name"]);
}

function ffRecord_on_factory($page, $disk_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null; 
    else
        return cm_findCascadeClass("ffRecord", $theme, null, $variant["name"]);
}

function ffDetails_on_factory($page, $disk_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null;
    else
        return cm_findCascadeClass("ffDetails", $theme, null, $variant["name"]);
}

function ffPageNavigator_on_factory($page, $disk_path, $site_path, $page_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null;
    else
        return cm_findCascadeClass("ffPageNavigator", $theme, null, $variant["name"]);
}

function ffField_on_factory($page, $disk_path, $site_path, $page_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null;
    else
        return cm_findCascadeClass("ffField", $theme, null, $variant["name"]);
}

function ffButton_on_factory($page, $disk_path, $site_path, $page_path, $theme, $variant)
{
    if (!is_null($variant) && isset($variant["path"]))
        return null;
    else
        return cm_findCascadeClass("ffButton", $theme, null, $variant["name"]);
}

function cm_findCascadeClass($class_type, $theme, $id = null, $variant_name = null, $raise_error = true)
{
    $cm = cm::getInstance();

    $registry = ffGlobals::getInstance("_registry_");

    if (!isset($registry->themes))
    {
        $registry->themes = array();
    }
    if (!isset($registry->themes[$theme]) && is_file(ff_getThemeDir($theme) . "/themes/" . $theme . "/theme_settings.xml"))
    {
        $registry->themes[$theme] = new SimpleXMLElement(ff_getThemeDir($theme) . "/themes/" . $theme . "/theme_settings.xml", null, true);
    }

    if ($variant_name === null)
    {
        $tmp = preg_replace('/\\.[^.\\s]{3,4}$/', '', rtrim($cm->oPage->page_path, "/"));
        $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/contents" . $tmp . "/" . $class_type;
        if (is_dir($base_path) && is_file($base_path . "/settings.xml"))
        {
            $config = new SimpleXMLElement($base_path . "/settings.xml", null, true);
            if (isset($config->default_class_suffix))
            {
                $class_name = $class_type . "_" . $config->default_class_suffix;

                if (is_file($base_path . "/" . $class_name . "." . FF_PHP_EXT))
                    return array("base_path" => $base_path . "/" . $class_name . "." . FF_PHP_EXT, "class_name" => $class_name);
            }
        }

        $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/ff/" . $class_type;
        if (is_dir($base_path) && isset($registry->themes[$theme]->default_class_suffix))
        {
            $suffix = $registry->themes[$theme]->default_class_suffix;
            $class_name = $class_type . "_" . $suffix;

            if (is_file($base_path . "/" . $class_name . "." . FF_PHP_EXT))
                return array("base_path" => $base_path . "/" . $class_name . "." . FF_PHP_EXT, "class_name" => $class_name);
        }
    }
    else
    {
        $class_name = $variant_name;

        $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/contents" . $cm->oPage->page_path . "/" . $class_type;
        if (is_file($base_path . "/" . $class_name . "." . FF_PHP_EXT))
            return array("base_path" => $base_path . "/" . $class_name . "." . FF_PHP_EXT, "class_name" => $class_name);

        $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/ff/" . $class_type;
        if (is_file($base_path . "/" . $class_name . "." . FF_PHP_EXT))
            return array("base_path" => $base_path . "/" . $class_name . "." . FF_PHP_EXT, "class_name" => $class_name);
    }

    if ($theme != cm_getMainTheme())
        return cm_findCascadeClass($class_type, cm_getMainTheme(), $id, $variant_name, $raise_error);

    if ($theme == cm_getMainTheme() && $raise_error)
        ffErrorHandler::raise("CM: Unable to find Class", E_USER_ERROR, $this, get_defined_vars());
    else
        return null;
}

// --------------------------------------------
//  TEMPLATES SECTION

ffPage::addEvent				("on_factory_done", "ffPage_set_events"				, ffEvent::PRIORITY_HIGH);
ffGrid::addEvent				("on_factory_done", "ffGrid_set_events"				, ffEvent::PRIORITY_HIGH);
ffRecord::addEvent			("on_factory_done", "ffRecord_set_events"			, ffEvent::PRIORITY_HIGH);
ffDetails::addEvent			("on_factory_done", "ffDetails_set_events"			, ffEvent::PRIORITY_HIGH);
ffPageNavigator::addEvent	("on_factory_done", "ffPageNavigator_set_events"	, ffEvent::PRIORITY_HIGH);
ffField::addEvent			("on_factory_done", "ffField_set_events"			, ffEvent::PRIORITY_HIGH);
ffButton::addEvent			("on_factory_done", "ffButton_set_events"			, ffEvent::PRIORITY_HIGH);

function ffPage_set_events(ffPage_base $page)
{
    $cm = cm::getInstance();
    $getLibs = $cm->router->getRuleById("getlibs");
    if ($getLibs)
    {
        $reverse = (string)$getLibs->reverse;
        $page->struct_properties["getlibs"] = $reverse;
    }

    $page->addEvent("getTemplateDir", "ffPage_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("getLayerDir", "ffPage_getLayerDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("getLayoutDir", "ffPage_getLayoutDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("on_widget_load", "ffPage_on_widget_load" , ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("on_js_parse", "ffPage_on_js_parse" , ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("on_css_parse", "ffPage_on_css_parse" , ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    $page->addEvent("on_tpl_parsed", "ffPage_on_tpl_parsed" , ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
    //$page->addEvent("on_fixed_process_before", "ffPage_on_fixed_process_before" , ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffGrid_set_events(ffGrid_base $grid)
{
    $grid->addEvent("getTemplateDir", "ffGrid_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffRecord_set_events(ffRecord_base $record)
{
    $record->addEvent("getTemplateDir", "ffRecord_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffDetails_set_events(ffDetails_base $details)
{
    $details->addEvent("getTemplateDir", "ffDetails_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffButton_set_events(ffButton_base $button)
{
    $button->addEvent("getTemplateDir", "ffButton_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffField_set_events(ffField_base $field)
{
    $field->addEvent("getTemplateDir", "ffField_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffPageNavigator_set_events(ffPageNavigator_base $navigator)
{
    $navigator->addEvent("getTemplateDir", "ffPageNavigator_getTemplateDir", ffEvent::PRIORITY_HIGH, 0, ffEvent::BREAK_NOT_EQUAL, null);
}

function ffPage_seo_optimize_js($oPage, $content)
{
    $matches = array();
    $rc_scripts = preg_match_all('#\s*<script(\b[^>]*)>([\s\S]*?)<\/script>#ims', $content, $matches);
	if ($rc_scripts)
    {
        for ($i = 0; $i < $rc_scripts; $i++)
        {
			if (preg_match("/type *= *x\-/i", $matches[1][$i])
				|| preg_match("/type *= *template/i", $matches[1][$i])
			) {
				$template = $matches[0][$i];

				if(FF_TEMPLATE_ENABLE_TPL_JS === true) {
					$template = str_replace(
						array("<!--{{", "}}-->")
						, array("{{", "}}")
						, preg_replace('/\s+/', ' ', $template)
					);
				}
				$oPage->tpl[0]->set_var("WidgetsContent", $template);
				$oPage->tpl[0]->parse("SectWidgetsHeaders", true);
				$content = str_replace($matches[0][$i], "", $content);

				continue;
			}
			if (preg_match("/defer *= */i", $matches[1][$i])) // TOCHECK! ??? not needed
                continue;

            $matches_src = array();
            $rc_src = preg_match("/src *= *([\"'])([^\"']+)\\1/i", $matches[1][$i], $matches_src);
            if ($rc_src)
            {
                $oPage->js_buffer[]["path"] = $matches_src[2];
            }
            else if (strlen($matches[2][$i]))
            {
                $oPage->js_buffer[]["content"] = $matches[2][$i];
            }
            $content = str_replace($matches[0][$i], "", $content);
        }
    }
    return trim($content);
}

function ffPage_seo_optimize_css($oPage, $content)
{
    $matches = array();
    $rc_scripts = preg_match_all('#(<!--\[if *(\w* *)?IE( *\d*)?\]>)?\s*<style(\b[^>]*)>([\s\S]*?)<\/style>(\<\!\[endif\]-->)?#ims', $content, $matches);

    if ($rc_scripts)
    {
        for ($i = 0; $i < $rc_scripts; $i++)
        {
            if (strlen($matches[1][$i]))
                continue;

            $type = null;
            $media = null;

            $matches_tmp = array();
            $rc_tmp = preg_match("/type *= *([\"'])([^\"']+)\\1/i", $matches[4][$i], $matches_tmp);
            if ($rc_tmp)
            {
                $type = $matches_tmp[2];
            }

            if (strpos($matches[4][$i], "inline") !== false)
            {
                continue;
            }

            if ($type !== null && strtolower($type) !== "text/css")
                continue;

            $matches_tmp = array();
            $rc_tmp = preg_match("/media *= *([\"'])([^\"']+)\\1/i", $matches[4][$i], $matches_tmp);
            if ($rc_tmp)
            {
                $media = $matches_tmp[2];
            }

            if ($media === null)
                $media = "default";

            $oPage->css_buffer[$media][]["content"] =  $matches[5][$i];
            $content = str_replace($matches[0][$i], "", $content);
        }
    }

    return $content;
}

function ffPage_seo_optimize($oPage)
{
    $content = $oPage->output_buffer["html"];
    if(!$content)
        return;

    if(CM_CACHE_IMG_SET_DIMENSION)
    {
        //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $content = mb_convert_encoding($content, 'html-entities', 'utf-8');

        $doc->loadHTML($content);
        if(CM_CACHE_IMG_LAZY_LOAD)
        {
            $arrSource= $doc->getElementsByTagName('source');
            if($arrSource->length) {
                for($i =0; $i<$arrSource->length; ++$i) {
                    $imgNode = $arrSource->item($i);
                    if($imgNode->hasAttribute("data-srcset"))
                        $imgSourceSrc = $imgNode->getAttribute("data-srcset");
                    elseif($imgNode->hasAttribute("srcset")) {
                        $imgSourceSrc = $imgNode->getAttribute("srcset");

                        $imgNode->removeAttribute("srcset");
                        $imgNode->setAttribute("data-srcset", $imgSourceSrc);
                    }

                    if(CM_CACHE_PATH_CONVERT_SHOWFILES)
                        $imgNode->setAttribute("data-srcset", cmCache_convert_imagepath_to_showfiles($imgSourceSrc));
                }
            }
        }

        $arrFrame = $doc->getElementsByTagName('iframe');
        if($arrFrame->length) {
            for($i =0; $i<$arrFrame->length; ++$i) {
                $frameNode = $arrFrame->item($i);
                if($frameNode->hasAttribute("src") && !$frameNode->hasAttribute("data-src")) {
                    $frameNodeClass = $frameNode->getAttribute("class");
                    $frameNodeSrc = $frameNode->getAttribute("src");
                    $frameNode->removeAttribute("src");
                    $frameNode->setAttribute("data-src", $frameNodeSrc);

                    if(strpos($frameNodeClass, "lazy") === false)
                        $frameNode->setAttribute("class", ($frameNodeClass ? $frameNodeClass . " " : "") . "lazy");
                }
            }
        }

        $arrImg = $doc->getElementsByTagName('img');
        if($arrImg->length) {
            $arrImgFinal = array();
            for($i =0; $i<$arrImg->length; ++$i) {
                $imgNodeSrc = "";
                $imgNode = $arrImg->item($i);
                //$imgNodeClass = $imgNode->getAttribute("class");
                //if(CM_CACHE_IMG_LAZY_LOAD && strpos($imgNodeClass, "lazy fake") !== false)
                //	continue;
                $imgNodeClass = $imgNode->getAttribute("class");
                $enable_lazy = CM_CACHE_IMG_LAZY_LOAD && strpos($imgNodeClass, "nolazy") === false;
                if($imgNode->hasAttribute("data-src")) {
					$imgNodeSrc = $imgNode->getAttribute("data-src");
					$imgNodeSrcExt = substr($imgNodeSrc, -4);
					if($enable_lazy && $imgNodeSrcExt != ".jpg" && $imgNodeSrcExt != ".png" && $imgNodeSrcExt != ".gif") {
						$enable_lazy = false;
					}
				} elseif($imgNode->hasAttribute("src")) {
                    $imgNodeSrc = $imgNode->getAttribute("src");
                    $imgNodeSrcExt = substr($imgNodeSrc, -4);
                    if($enable_lazy && ($imgNodeSrcExt == ".jpg" || $imgNodeSrcExt == ".png" || $imgNodeSrcExt == ".gif")) {
                        $imgNode->removeAttribute("src");
                        $imgNode->setAttribute("data-src", $imgNodeSrc);
                    } else {
						$enable_lazy = false;
					}
                }

				if(strpos($imgNodeSrc, "/") !== 0 && strpos($imgNodeSrc, "http") !== 0)
					continue;

                if(CM_CACHE_PATH_CONVERT_SHOWFILES) {
                    if($imgNode->hasAttribute("srcset") && strlen($imgNode->getAttribute("srcset")))
                    {
                        $imgNodeSrcSet = explode(",", $imgNode->getAttribute("srcset"));

                        if(is_array($imgNodeSrcSet) && count($imgNodeSrcSet))
                        {
                            $arrSrcSetNew = array();
                            foreach($imgNodeSrcSet AS $srcset_key => $srcset)
                            {
                                $arrSrcSet = explode(" ", trim($srcset));
                                $arrSrcSetNew[] = cmCache_convert_imagepath_to_showfiles($arrSrcSet[0]) . " " . $arrSrcSet[1];
                            }

                            $imgNode->setAttribute("srcset", implode(", ", $arrSrcSetNew));
                            if(!$imgNode->hasAttribute("sizes"))
                                $imgNode->setAttribute("sizes", "100vw");
                        }
                    }

                    $imgNode->setAttribute(($enable_lazy ? "data-" : "") . "src", cmCache_convert_imagepath_to_showfiles($imgNodeSrc, $imgNode->getAttribute("width"), $imgNode->getAttribute("height")));
                }
                if($enable_lazy && $imgNode->hasAttribute("data-src")) {
                    $imgNode->setAttribute("src", "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==");
                    if(strpos($imgNodeClass, "lazy") === false)
                        $imgNode->setAttribute("class", ($imgNodeClass ? $imgNodeClass . " " : "") . "lazy");
                }
                if($imgNode->hasAttributes() && strlen($imgNodeSrc)) {
                    if($imgNode->hasAttribute("style")) {
                        $arrImgStyle = explode(";", $imgNode->getAttribute("style"));
                        if(is_array($arrImgStyle) && count($arrImgStyle)) {
                            foreach($arrImgStyle AS $arrImgStyle_key => $arrImgStyle_value) {
                                if(strlen($arrImgStyle_value)) {
                                    $arrImgStyleRules = explode(":", $arrImgStyle_value);
                                    $style_key_normalized = trim(strtolower($arrImgStyleRules[0]));
                                    switch($style_key_normalized) {
                                        case "width":
                                        case "height":
                                            $arrImgFinal[$imgNodeSrc][$style_key_normalized] = trim(str_replace("px", "", $arrImgStyleRules[1]));
                                            $imgNode->setAttribute($style_key_normalized, $arrImgFinal[$imgNodeSrc][$style_key_normalized]);
                                            unset($arrImgStyle[$arrImgStyle_key]);
                                            break;
                                        default:
                                    }
                                }
                            }
                        }
                        if(count($arrImgStyle))
                            $imgNode->setAttribute("style", implode(";", $arrImgStyle));
                        else
                            $imgNode->removeAttribute("style");
                    }

                    if(!$imgNode->hasAttribute("alt") || !$imgNode->getAttribute("alt")) {
                        if(!isset($arrImgFinal[$imgNodeSrc]["alt"])) {
                            $arrImgFinal[$imgNodeSrc]["alt"] = trim(ffCommon_url_rewrite_strip_word(str_replace(range(0,9),'', ffGetFilename($imgNodeSrc)), "", " "));
                        }

                        $imgNode->setAttribute("alt", $arrImgFinal[$imgNodeSrc]["alt"]);
                    }
                    if(!$imgNode->hasAttribute("title") || !$imgNode->getAttribute("title")) {
                        if(!isset($arrImgFinal[$imgNodeSrc]["title"])) {
                            $arrImgFinal[$imgNodeSrc]["title"] = ucwords(trim(ffCommon_url_rewrite(str_replace(range(0,9),'',ffGetFilename($imgNodeSrc)), " ")));
                        }

                        $imgNode->setAttribute("title", $arrImgFinal[$imgNodeSrc]["title"]);
                    }

                    if($imgNode->parentNode->nodeName != "picture" && $imgNode->parentNode->nodeName != "figure" && $imgNode->parentNode->nodeName != "source" && !($imgNode->hasAttribute("width") && $imgNode->hasAttribute("height"))) {
                        if(!isset($arrImgFinal[$imgNodeSrc]["width"]) || !isset($arrImgFinal[$imgNodeSrc]["height"])) {
                            $img_src = $imgNodeSrc;
                            if (!(substr(strtolower($img_src), 0, 7) == "http://"
                                || substr(strtolower($img_src), 0, 8) == "https://"
                                || substr($img_src, 0, 2) == "//")
                            ) {
                                if(strpos($img_src, CM_SHOWFILES) === false) {
                                    if(@is_file(FF_DISK_PATH . "/" . ltrim($img_src, "/"))) {
                                        $img_src = FF_DISK_PATH . "/" . ltrim($img_src, "/");
                                    } else {
                                        $img_src = "";
                                    }
                                } else {
                                    $img_src =  "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $_SERVER["HTTP_HOST"] . $img_src;
                                }
                            }

                            if(strlen($img_src)
                                && (strpos($img_src, "http") !== 0
                                    && strpos($img_src, "?") !== false
                                )
                            ) {
                                switch (ffMimeTypeByExtension(ffGetFilename($img_src, false)))
                                {
                                    case "image/jpeg":
                                    case "image/png":
                                    case "image/gif":
                                        $attrs = @getimagesize($img_src);
                                        if(is_array($attrs) && $attrs[0] > 0 && $attrs[1] > 0) {
                                            $arrImgFinal[$imgNodeSrc]["width"] = round($attrs[0]);
                                            $arrImgFinal[$imgNodeSrc]["height"] = round($attrs[1]);
                                        }
                                        break;
                                    case "image/svg+xml":
                                        $xml = @simplexml_load_file($img_src);
                                        if($xml) {
                                            $attrs = $xml->attributes();

                                            $arrImgFinal[$imgNodeSrc]["width"] = round(str_ireplace("px", "", (string) $attrs->width));
                                            $arrImgFinal[$imgNodeSrc]["height"] = round(str_ireplace("px", "", (string) $attrs->height));
                                        }
                                        break;
                                    default:
                                }
                            }
                        }

                        if(!$imgNode->getAttribute("width"))
                            $imgNode->setAttribute("width", ($arrImgFinal[$imgNodeSrc]["width"] ? $arrImgFinal[$imgNodeSrc]["width"] : "auto"));
                        if(!$imgNode->getAttribute("height"))
                            $imgNode->setAttribute("height", ($arrImgFinal[$imgNodeSrc]["height"] ? $arrImgFinal[$imgNodeSrc]["height"] : "auto"));
                    }
                } else {
                    $imgNode->parentNode->removeChild($imgNode);
                }
            }
        }

        $newdoc = new DOMDocument();
        $body = $doc->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child){
            $newdoc->appendChild($newdoc->importNode($child, true));
        }

        $content = $newdoc->saveHTML();
        if(CM_CACHE_IMG_LAZY_LOAD)
            $content = str_replace("></source>", " />", $content);

        if(CM_CACHE_PATH_CONVERT_SHOWFILES && CM_MEDIACACHE_SHOWPATH) {
            $content = str_replace(CM_SHOWFILES . "/", CM_MEDIACACHE_SHOWPATH . "/", $content);
        }

        if(CM_CACHE_IMG_LAZY_LOAD_CSS) {
            $oPage->css_buffer["default"][]["content"] =  '
				IMG.lazy {border: 1px solid #cacaca;}
				.lazyloader { border: 1px solid #cacaca;}
				.lazyloader + IMG.lazy, .lazyloader + PICTURE { display:none;}';
        }
    }

    /**
     * converte gli style in linea in un unico stylesheet
     */
    if(CM_CACHE_CSS_INLINE_TO_STYLE)
    {
        preg_match_all('/<[^<]*style=\"([^\"]*)\"[^>]*>/', $content, $arrStyle);
        if(is_array($arrStyle) && is_array($arrStyle[0]) && count($arrStyle[0])) {
            $style["prefix"] = "inline";
            $style["count"] = 1;
            $style["css"] = "";
            $style["elem"] = array();

            foreach($arrStyle[0] AS $arrStyle_key => $arrStyle_value) {
                if(!strlen($arrStyle_value))
                    continue;

                preg_match("/<.*id=\"([^\"]*)\".*/", $arrStyle_value, $matches);
                if(is_array($matches) && count($matches)) {
                    $style_elem_id = $matches[1];
                    $replace_style = '';

                } else {
                    $style_elem_id = $style["prefix"] . $style["count"];
                    $replace_style = 'id="' . $style_elem_id . '"';
                    $style["count"]++;
                }
                $style["css"] .= ' #' . $style_elem_id . ' {' . $arrStyle[1][$arrStyle_key] . (substr($arrStyle[1][$arrStyle_key], -1) == ";" ? "" : ";" ) . '} ';
                $style["elem"][] = array("old" => $arrStyle_value, "new" => str_replace('style="' . $arrStyle[1][$arrStyle_key] . '"', $replace_style, $arrStyle_value));
            }

            if(is_array($style["elem"]) && count($style["elem"])) {
                foreach($style["elem"] AS $key_elem => $tag_elem) {
//                        $content = preg_replace("#" . $value_elem["old"] . "#i", $value_elem["new"], $content, 1);
                    if(strlen($tag_elem["old"]) < 100)
                        $content = preg_replace("/" . preg_quote($tag_elem["old"], "/") . "/i", $tag_elem["new"], $content, 1);
                }

                $oPage->css_buffer["default"][]["content"] =  $style["css"];
            }
        }
    }

	$content = cmCache_normalizeUrl($content);

	$oPage->tpl[0]->set_var("content", $content);
}

function cmCache_normalizeUrl($content) {
	if(CM_CACHE_PATH_CONVERT_SHOWFILES && CM_MEDIACACHE_SHOWPATH) {
//		$arrFind[] 			= CM_SHOWFILES . "/";
//		$arrReplace[] 		= CM_MEDIACACHE_SHOWPATH . "/";
	}
	if($_SERVER["HTTPS"]) {
		$arrFind[] 			= 'http://' . $_SERVER["HTTP_HOST"];
		$arrReplace[] 		= 'https://' .  $_SERVER["HTTP_HOST"];
		if(strpos($_SERVER["HTTP_HOST"], "www.") === 0) {
			$domain = substr($_SERVER["HTTP_HOST"], 4);
			$arrFind[] 		= 'http://' . $domain;
			$arrReplace[] 	= 'https://www.' .  $domain;
		}
		$content = str_replace($arrFind, $arrReplace, $content);
	}
	return $content;
}

function cmCache_convert_imagepath_to_showfiles($src, $width = null, $height = null)
{
    $showfiles = (CM_MEDIACACHE_SHOWPATH
        ? CM_MEDIACACHE_SHOWPATH
        : CM_SHOWFILES
    );

    $image = pathinfo($src);
	if(strpos($src, FF_SITE_PATH . FF_THEME_DIR . '/') === 0)
	{

	} elseif(strpos($src, FF_SITE_PATH . FF_UPDIR . '/') === 0)
    {
        $mode = "";
        if($width > 0 && $height > 0)
            $mode = "-" . $width . "x" . $height;

        $src = str_replace(
            FF_SITE_PATH . FF_UPDIR . '/'
            , $showfiles . '/'
            , $image["dirname"] . "/" . $image["filename"] . $mode . "." . $image["extension"]
        );
    }
    elseif($showfiles != CM_SHOWFILES)
    {
        $showfiles_orig = null;
        if(strpos($src, CM_SHOWFILES . '/') === 0)
            $showfiles_orig = CM_SHOWFILES . '/';
        if(strpos($src, FF_SITE_PATH . CM_SHOWFILES . '/') === 0)
            $showfiles_orig = FF_SITE_PATH . CM_SHOWFILES . '/';
        if(strpos($src, FF_SITE_PATH . "/cm/showfiles" . '/') === 0)
            $showfiles_orig = FF_SITE_PATH . "/cm/showfiles" . '/';
        if(strpos($src, FF_SITE_PATH . "/cm/showfiles.php" . '/') === 0)
            $showfiles_orig = FF_SITE_PATH . "/cm/showfiles.php" . '/';

        if($showfiles_orig)
        {
            $imageOrig["url"] 	= str_replace($showfiles_orig, "", $src);
            $imageOrig["path"] 	= explode("/", $imageOrig["url"]);

            $imageOrig["mode"] = array_shift($imageOrig["path"]);
            $imageOrig["url"] = "/" . implode("/", $imageOrig["path"]);
            $imageOrig["dirname"] = ffCommon_dirname($imageOrig["url"]);

			if(strpos($imageOrig["mode"], "-png") === strlen($imageOrig["mode"]) - 4) {
				$imageOrig["ext"] = "png";
				$imageOrig["mode"] = $imageOrig["ext"] . "-" . substr($imageOrig["mode"], 0, -4);
			} elseif(strpos($imageOrig["mode"], "-jpg") === strlen($imageOrig["mode"]) - 4) {
				$imageOrig["ext"] = "jpg";
				$imageOrig["mode"] = $imageOrig["ext"] . "-" . substr($imageOrig["mode"],0 , -4);
			} elseif(strpos($imageOrig["mode"], "-svg") === strlen($imageOrig["mode"]) - 4) {
				$imageOrig["ext"] = "svg";
				$imageOrig["mode"] = $imageOrig["ext"] . "-" . substr($imageOrig["mode"],0 , -4);
			} elseif(strpos($imageOrig["mode"], "-jpeg") === strlen($imageOrig["mode"]) - 5) {
				$imageOrig["ext"] = "jpeg";
				$imageOrig["mode"] = $imageOrig["ext"] . "-" . substr($imageOrig["mode"],0 , -5);
			}

			$imageOrig["basename"] = ($imageOrig["ext"]
				? ffGetFilename($imageOrig["url"]) . "." . $imageOrig["ext"]
				: basename($imageOrig["url"])
			);

			if(strpos($imageOrig["mode"], "x") !== false) {
				$arrMode = explode("x", $imageOrig["mode"]);
			} elseif(strpos($imageOrig["mode"], "-") !== false) {
				$arrMode = explode("-", $imageOrig["mode"]);
			}
			if(is_array($arrMode) && count($arrMode) == 2 && is_numeric($arrMode[0]) && is_numeric($arrMode[1]) ) {
				$is_mode = true;
			}


			if($is_mode) // is_file(FF_DISK_PATH . FF_UPDIR . $imageOrig["dirname"] . "/" . $imageOrig["basename"])
			{
				$imageOrig["mode"] = "-" . $imageOrig["mode"];
			}
			else
			{
				$imageOrig["url"] = "/". $imageOrig["mode"] . $imageOrig["url"];
				$imageOrig["mode"] = "";
				$showfiles = CM_SHOWFILES;

				cmCache_writeLog("SRC: " . $src . " REFERER: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], "resource_no_media");
			}

            //if(!$imageOrig["mode"] && $width > 0 && $height > 0)
            //	$imageOrig["mode"] = "-" . $width . "x" . $height;

            $src = $showfiles . ffCommon_dirname($imageOrig["url"]) . "/" . $image["filename"] . $imageOrig["mode"] . "." . $image["extension"];
        }
    }

    if($_SERVER["HTTPS"])
        $src = str_replace("http://", "https://", $src);

    return $src;
}

function cmCache_writeLog($data, $filename = "log") //writeLog
{
	if(DEBUG_LOG === true) {
		$log_path = CM_CACHE_PATH . "/logs";
		if(!is_dir($log_path))
			mkdir($log_path, 0777, true);

		$file = $log_path . '/' . date("Y-m-d") . "_" . $filename . '.txt';
		if(!is_file($file)) {
			$set_mod = true;
		}

		if($handle = @fopen($file, 'a'))
		{
            if(is_array($data)) {
                $string = print_r($data, true);
            } else {
                $string = $data;
            }

			if(@fwrite($handle, date("Y-m-d H:i:s", time()) . " " . $string . "\n") === FALSE)
			{
				$i18n_error = true;
			}
			@fclose($handle);

			if($set_mod && !$i18n_error)
				chmod($file, 0777);
		}
	}
}

function ffPage_on_tpl_parsed(ffPage_base $oPage)
{
    if ($oPage->isXHR())
        return;

    $cm = cm::getInstance();
    $now = time();
    //$defer_loading = array();
    $enable_gzip_file = (CM_CACHE_STORAGE_SAVING_MODE
        ? false
        : $oPage->compress
    );

    ffPage_seo_optimize($oPage); // TODO: da rivedere

    // ********************************************
    //  CSS MINIFY / COMPRESSION
    if ($oPage->compact_css && is_array($oPage->css_buffer) && count($oPage->css_buffer))
    {
        if (CM_CACHE_PURGE_CSS)
        {
            $oPage->tpl[0]->DBlocks["main"] = ffPage_seo_optimize_css($oPage, $oPage->tpl[0]->DBlocks["main"]);
            foreach ($oPage->tpl[0]->ParsedBlocks as $key => $value)
            {
                $oPage->tpl[0]->ParsedBlocks[$key] = ffPage_seo_optimize_css($oPage, $value);
            }
        }

        if (CM_CSSCACHE_DEFERLOADING && is_array($oPage->css_buffer) && count($oPage->css_buffer))
            $allow_css_defer_loading = true;

        $cache_dir = CM_CSSCACHE_DIR;
        if (CM_CSSCACHE_BYDOMAIN)
        {
            $cache_domain_prefix = $_SERVER["HTTP_HOST"];
            if (CM_PAGECACHE_BYDOMAIN_STRIPWWW && strpos($cache_domain_prefix, "www.") === 0)
                $cache_domain_prefix = substr($cache_domain_prefix, 4);
            $cache_dir .= "/" . $cache_domain_prefix;
        }

        $cache_group_dir = 0;

        foreach ($oPage->css_buffer AS $css_buffer_media => $css_buffer_path)
        {
            $rc_cache = false;
            $compressed = null;
            $compressed_subpath = "";
            $uncompressed = null;
            $uncompressed_subpath = "";
            $uncompressed_file = "";
            $parsed_externals = false;
            $css_file_path = "";
            $css_file_key = null;

            // check for smart paths
            $css_smart = CM_CSSCACHE_SMARTURLS;
            $css_smart_name = "";
            $css_expire = CM_CSSCACHE_DEFAULT_EXPIRES;

            $res = $cm->doEvent("on_compact_css", array());
            $rc = end($res);
            if ($rc !== null)
            {
                if (array_key_exists("smart", $rc))
                    $css_smart = $rc["smart"];

                if (array_key_exists("smart_name", $rc))
                    $css_smart_name = $rc["smart_name"];

                if (array_key_exists("expire", $rc))
                    $css_expire = $rc["expire"];
            }

            if ($css_smart)
            {
                $css_file_key = $css_smart_name;
                if ($css_buffer_media != "default")
                    $css_file_key .= "_" . ffCommon_url_rewrite($css_buffer_media);

                if ($enable_gzip_file)
                {
                    $finfo = new SplFileInfo($cache_dir . "/" . $css_smart_name . ".css.gz");
                    $compressed = $finfo->isFile() && cm_filecache_check_expiration($finfo->getMTime(), $finfo->getCTime(), $now, CM_CSSCACHE_LAST_VALID);
                }

                $finfo = new SplFileInfo($cache_dir . "/" . $css_smart_name . ".css");
                if ($uncompressed = $finfo->isFile() && cm_filecache_check_expiration($finfo->getMTime(), $finfo->getCTime(), $now, CM_CSSCACHE_LAST_VALID))
                    $uncompressed_file = $cache_dir . "/" . $css_smart_name . ".css";

                /*$cache_file = cm_filecache_find($cache_dir, $css_smart_name, CM_CSSCACHE_GROUPDIRS, true, CM_CSSCACHE_LAST_VALID, CM_CSSCACHE_SCALEDOWN, $now, null, false);
                if ($cache_file)
                {
                    $css_file_path = str_replace($cache_dir, "", $cache_file["filedir"]);
                }*/
            }
            else
            {
                $parsed_externals = true; // avoid useless cycle

                $max_mtime = 0;
                foreach ($css_buffer_path AS $css_buffer_key => $css_buffer_value)
                {
                    if (strlen($css_buffer_value["content"]))
                        $css_file_key .= $css_buffer_value["content"];
                    elseif (substr(strtolower($css_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($css_buffer_value["path"]), 0, 8) === "https://" || substr($css_buffer_value["path"], 0, 2) === "//")
                    {
                        if (0 &&!$allow_css_defer_loading)
                        { //cosi facendo si forza il caricamento dei file esterni ance se si ha impostato exlude_compact a false
                            $link_properties = "";
                            $link_properties .= ' rel="stylesheet"';
                            $link_properties .= ' type="text/css"';

                            $oPage->tpl[0]->set_var("css_path", $css_buffer_value["path"]);
                            $oPage->tpl[0]->set_var("link_properties", $link_properties);

                            /*$oPage->tpl[0]->set_var("css_rel", "stylesheet");
                            $oPage->tpl[0]->set_var("css_type", "text/css");*/
                            //$oPage->tpl[0]->set_var("SectCssMedia", "");
                            $oPage->tpl[0]->set_var("CssEmbed", "");
                            $oPage->tpl[0]->parse("SectCssLink", false);
                            $oPage->tpl[0]->parse("SectCss", true);
                        }
                        else
                        {
                            $oPage->page_defer["css"][] = array(
                                "path" => $css_buffer_value["path"]
                            , "media" => $css_buffer_media
                            );
                            $oPage->page_defer["css-embed"][] = '<link rel="stylesheet" type="text/css" ' . ($css_buffer_media == "default" ? "" : 'media="' . $css_buffer_media . '" ') . 'href="' . $css_buffer_value["path"] . '"/>';
                            //$oPage->page_defer["css|" . $css_buffer_media][] = '"' . $css_buffer_value["path"] . '"';
                        }
                    }
                    else
                    {
                        if (strpos($css_buffer_value["path"], ".min.css") === false
                            && @is_file(ffCommon_dirname($css_buffer_value["path"]) . "/" . ffGetFilename($css_buffer_value["path"]) . ".min.css")
                        )
                        {
                            $css_buffer_path[$css_buffer_key]["path"] = ffCommon_dirname($css_buffer_value["path"]) . "/" . ffGetFilename($css_buffer_value["path"]) . ".min.css";
                        }

                        if (@is_file($css_buffer_path[$css_buffer_key]["path"]))
                        {
                            $css_file_key .= $css_buffer_path[$css_buffer_key]["path"];
							$tmp_mtime = filemtime($css_buffer_path[$css_buffer_key]["path"]);
                            if ($tmp_mtime > $max_mtime)
                                $max_mtime = $tmp_mtime;
                        }
                    }
                    $css_file_key .= "_";
                }
                reset($css_buffer_path);

                $css_file_key = sha1($css_file_key);
                if (CM_CSSCACHE_GROUPHASH)
                {
                    $parts = str_split($css_file_key, CM_CSSCACHE_HASHSPLIT);
                    $cache_dir .= "/" . implode("/", $parts);
                }

                //if ($max_mtime)
                //    $css_file_key .= "_" . $max_mtime;

                if (!CM_CSSCACHE_GROUPDIRS)
                {
                    if(CM_CACHE_STORAGE_SAVING_MODE)
                        $cache_subdir_storing = substr($css_file_key, 0, CM_CACHE_STORAGE_SAVING_MODE) . "/";

					if($enable_gzip_file)
					{

						if(file_exists($cache_dir . "/" . $cache_subdir_storing. $css_file_key . ".css.gz")
							&& (filemtime($cache_dir . "/" . $cache_subdir_storing. $css_file_key . ".css.gz") - $css_expire) >= $max_mtime
						) {
							$compressed = true;
						}
						$compressed_subpath = $cache_subdir_storing;
					}

					if(file_exists($cache_dir . "/" . $cache_subdir_storing . $css_file_key . ".css")
						&& (filemtime($cache_dir . "/" . $cache_subdir_storing . $css_file_key . ".css") - $css_expire) >= $max_mtime
					) {
						$uncompressed = true;
						$uncompressed_file = $cache_dir . "/" . $cache_subdir_storing . $css_file_key . ".css";
					}
					$uncompressed_subpath = $cache_subdir_storing;
				}
                else if (file_exists($cache_dir))
                {
                    $itGroup = new DirectoryIterator($cache_dir);
                    foreach($itGroup as $fiGroup)
                    {
                        if ($fiGroup->isDot())
                            continue;

						if ($enable_gzip_file && !$compressed) {

							if(file_exists($fiGroup->getPathname() . "/" . $css_file_key . ".css.gz")
								&& (filemtime($fiGroup->getPathname() . "/" . $css_file_key . ".css.gz") - $css_expire) >= $max_mtime
							) {
								$compressed = true;
							}
							$compressed_subpath = $fiGroup->getBasename() . "/";
						}
						if(!$uncompressed)
						{
							if(file_exists($fiGroup->getPathname() . "/" . $css_file_key . ".css")
								&& (filemtime($fiGroup->getPathname() . "/" . $css_file_key . ".css") - $css_expire) >= $max_mtime
							) {
								$uncompressed = true;
								$uncompressed_file = $fiGroup->getPathname() . "/" . $css_file_key . ".css";
							}
							$uncompressed_subpath = $fiGroup->getBasename() . "/";
						}

                        if ($compressed && $uncompressed)
                            break;
                    }
                }
            }

            if ($enable_gzip_file && $compressed && ffHTTP_encoding_isset("gzip"))
            {
                $css_file_path = $compressed_subpath;
            }
            elseif($uncompressed)
            {
                $css_file_path = $uncompressed_subpath;
            }
            else // make cache
            {
                if (!$uncompressed)
                {
                    $str_css_buffer = "";
                    $count_exclude_cssmin = 0;
                    $count_include_cssmin = 0;
                    foreach ($css_buffer_path AS $css_buffer_key => $css_buffer_value)
                    {
                        $tmp_css_data = "";

                        if (strlen($css_buffer_value["content"]))
                        {
                            $tmp_css_data = $css_buffer_value["content"];
                        }
                        elseif (substr(strtolower($css_buffer_value["path"]), 0, 7) !== "http://" && substr(strtolower($css_buffer_value["path"]), 0, 8) !== "https://" && substr($css_buffer_value["path"], 0, 2) !== "//")
                        {
                            if ($res = @file_get_contents($css_buffer_value["path"]))
                            {
                                $res = trim($res);
                                if (!strlen($res))
                                    continue;

                                $tmp_css_data = $res;
                            }
                            //else
                            //	ffErrorHandler::raise ("Unable to open CSS file", E_USER_ERROR, null, get_defined_vars());
                        }

                        if (strlen($tmp_css_data))
                        {
                            /*$server_url = "http://" . $_SERVER["HTTP_HOST"];
                            $server_url_http = "http://" . $_SERVER["HTTP_HOST"];

                            if ((substr(strtolower($css_buffer_value["path"]), 0, 7) != "http://" && substr(strtolower($css_buffer_value["path"]), 0, 8) != "https://" && substr($css_buffer_value["path"], 0, 2) != "//") && file_exists($css_buffer_value["path"]))
                            {
                                preg_match("/@import[\s]+url[\s]*\([\s]*\"?[\s]*([^\"\)]+)[\s]*\"?[\s]*\)\;?/", $tmp_css_data, $matches, PREG_OFFSET_CAPTURE, $offset);
                                while (0 !== ($rc = preg_match("/@import[\s]+url[\s]*\([\s]*\"?[\s]*([^\"\)]+)[\s]*\"?[\s]*\)/", $tmp_css_data, $matches, PREG_OFFSET_CAPTURE, $offset)))
                                {
                                    $match = $matches[1][0];

                                    // normalize url
                                    if (strpos($match, "http://") === 0)
                                    {
                                        $match = substr($match, strlen("http://"));
                                    }
                                    elseif (strpos($match, "https://") === 0)
                                    {
                                        $match = substr($match, strlen("https://"));
                                    }
                                    if (strpos($match, $_SERVER["HTTP_HOST"]) === 0)
                                    {
                                        $match = substr($match, strlen($_SERVER["HTTP_HOST"]));
                                    }
                                    if (strlen(FF_SITE_PATH) && strpos($match, FF_SITE_PATH) === 0)
                                    {
                                        $match = substr($match, strlen(FF_SITE_PATH));
                                    }

                                    // check file existance on locale accessible path
                                    if (strpos($match, "/") === 0)
                                        $final_file = realpath(FF_DISK_PATH . $match);
                                    else
                                        $final_file = realpath(ffCommon_dirname($css_buffer_value["path"]) . "/" . $match);

                                    if (strlen($final_file) && strpos($final_file, FF_DISK_PATH) === 0 && file_exists($final_file))
                                    {
                                        $tmp_import = file_get_contents($final_file);
                                        $tmp_css_data = substr($tmp_css_data, 0, $matches[0][1]) . $tmp_import . substr($tmp_css_data, $matches[0][1] + strlen($matches[0][0]));
                                        $offset = $matches[0][1];
                                        continue;
                                    }

                                    $offset = $matches[0][1] + strlen($matches[0][0]);
                                }
                            }
        */

                            /*$tmp_css_data = str_replace("{site_path}", FF_SITE_PATH, $tmp_css_data);
                            $tmp_css_data = str_replace("{showfiles}", CM_SHOWFILES, $tmp_css_data);
                            $tmp_css_url = cm_extract_css_urls($tmp_css_data);

                            if (is_array($tmp_css_url))
                            {
                                $tmp_css_link_replaced = array();
                                foreach($tmp_css_url AS $tmp_css_url_value)
                                {
                                    if (isset($tmp_css_link_replaced[$tmp_css_url_value]))
                                        continue;
                                    if (
                                        substr($tmp_css_url_value, 0, 1) !== "/"
                                        && (substr(strtolower($tmp_css_url_value), 0, 7) !== "http://"
                                            && substr(strtolower($tmp_css_url_value), 0, 8) !== "https://"
                                            && substr($tmp_css_url_value, 0, 2) !== "//")
                                    )
                                    {
                                        $arrBufferPath = parse_url(ffcommon_dirname($css_buffer_value["path"]) . "/" . $tmp_css_url_value);
                                        if (
                                            substr(strtolower($css_buffer_value["path"]), 0, 7) === "http://"
                                            || substr(strtolower($css_buffer_value["path"]), 0, 8) === "https://"
                                            || substr($css_buffer_value["path"], 0, 2) === "//"
                                        )
                                            $relative_buffer_path = cm_canonicalize($arrBufferPath["scheme"] . "://" . $arrBufferPath["host"] . $arrBufferPath["path"])
                                                . (array_key_exists("query", $arrBufferPath) ? "?" . $arrBufferPath["query"] : "")
                                                . (array_key_exists("fragment", $arrBufferPath) ? "#" . $arrBufferPath["fragment"] : "");
                                        else
                                            $relative_buffer_path = substr(realpath($arrBufferPath["path"]), strlen(FF_DISK_PATH))
                                                . (array_key_exists("query", $arrBufferPath) ? "?" . $arrBufferPath["query"] : "")
                                                . (array_key_exists("fragment", $arrBufferPath) ? "#" . $arrBufferPath["fragment"] : "");

                                        if(strpos($relative_buffer_path, FF_THEME_DIR) === 0)
                                        {
                                            if(file_exists(ff_getThemeDir($oPage->theme) . FF_THEME_DIR . "/" . $oPage->theme . substr($relative_buffer_path, strpos($relative_buffer_path, "/", strlen(FF_THEME_DIR . "/")))))
                                            {
                                                $relative_buffer_path =  "/" . $oPage->theme . substr($relative_buffer_path, strpos($relative_buffer_path, "/", strlen(FF_THEME_DIR . "/")));
                                            }
                                            else
                                            {
                                                $relative_buffer_path = substr($relative_buffer_path, strlen(FF_THEME_DIR));
                                            }

                                            $relative_buffer_path = FF_SITE_PATH . FF_THEME_DIR . $relative_buffer_path;
                                        }
                                        elseif(strpos($relative_buffer_path, "/uploads") === 0 && CM_MEDIACACHE_SHOWPATH)
                                        {
                                            $relative_buffer_path = CM_MEDIACACHE_SHOWPATH . substr($relative_buffer_path, strlen("/uploads"));
                                        }

                                        if($relative_buffer_path)
                                            $tmp_css_data = str_replace($tmp_css_url_value, $relative_buffer_path, $tmp_css_data);

                                        $tmp_css_link_replaced[$tmp_css_url_value] = true;
                                    }
                                }
                            } elseif(strpos($css_buffer_value["path"], FF_THEME_DISK_PATH) === 0) {
                                $arrBufferPath = explode("/", str_replace(FF_THEME_DISK_PATH . "/", "", $css_buffer_value["path"]));
                                $tmp_css_data = str_replace("../", FF_THEME_DIR . "/" . $arrBufferPath[0] . "/", $tmp_css_data);
                            }*/

                            $tmp_css_data = cm_convert_url_in_abs_by_content($tmp_css_data, $css_buffer_value["path"]);

                            if (strpos($css_buffer_value["path"], ".min.css") === false)
                            {
                                $str_css_buffer .= $tmp_css_data;
                                $count_include_cssmin++;
                            }
                            else
                            {
                                $exclude_cssmin[$count_exclude_cssmin] = $tmp_css_data;
                                $str_css_buffer .= "/*!". $count_exclude_cssmin . "*/";
                                $count_exclude_cssmin++;
                            }
                        }
                    }

					$str_css_buffer = cmCache_normalizeUrl($str_css_buffer);

					if ($oPage->compact_css == 2 && $count_include_cssmin)
                    {
                        //$before = microtime();
                        switch (CM_CSSCACHE_MINIFIER)
                        {
                            case "gminify": // lite
                                require_once(__TOP_DIR__ . "/library/gminify/CSS.php");
                                $str_css_buffer = Minify_CSS::minify($str_css_buffer);
                                break;

                            case "cssmin": // medium
                                if (!class_exists("CssMin"))
                                    require(__TOP_DIR__ . "/library/cssmin/CssMin.php");
                                $str_css_buffer = CssMin::minify($str_css_buffer);
                                break;

                            case "minify": // medium
                                if (!class_exists("CSSmin"))
                                    require(__TOP_DIR__ . "/library/minify/min/lib/CSSmin.php");
                                $str_css_buffer = CSSmin::minify($str_css_buffer);
                                break;

                            case "yui": // strong
                                require_once(__TOP_DIR__ . "/library/gminify/YUICompressor.php");
                                Minify_YUICompressor::$jarFile = __TOP_DIR__ . "/library/gminify/yuicompressor-2.4.8.jar";
                                if (!file_exists(CM_CSSCACHE_DIR))
                                {
                                    @mkdir(CM_CSSCACHE_DIR, 0777, true);
                                }
                                Minify_YUICompressor::$tempDir = CM_CSSCACHE_DIR;
                                $str_css_buffer = Minify_YUICompressor::minifyCss($str_css_buffer, array('nomunge' => true, 'line-break' => 1000));
                                break;
                        }
                        /*$after = microtime();
                        echo "before: " . $before . "<br />";
                        echo "after: " . $after . "<br />";
                        die();*/
                    }

                    // put back already compressed resources
                    if (is_array($exclude_cssmin) && count($exclude_cssmin))
                    {
                        foreach($exclude_cssmin AS $exclude_cssmin_key => $exclude_cssmin_value)
                        {
                            $str_css_buffer = str_replace("/*!". $exclude_cssmin_key . "*/", $exclude_cssmin_value, $str_css_buffer);
                        }
                    }

                    if (CM_SHOWFILES_FORCE_PATH && CM_CSSCACHE_RENDER_PATH && strlen($str_css_buffer))
                    { //manipolazione percorsi dei file media per avere la gestione della cache
                        $str_css_buffer = str_replace(FF_SITE_PATH . FF_UPDIR . '/', CM_SHOWFILES . '/', $str_css_buffer);
                        //if (CM_CSSCACHE_RENDER_THEME_PATH)
                        //    $str_css_buffer = str_replace(FF_SITE_PATH . THEME_DIR . '/', CM_SHOWFILES . '/', $str_css_buffer);
                    }

                    // write it uncompressed
                    if (CM_CSSCACHE_GROUPDIRS && !$css_smart)
                    {
                        $rc_cache = cm_filecache_groupwrite(CM_CSSCACHE_DIR, $cache_dir, "", $css_file_key . ".css", $str_css_buffer, $now + $css_expire, CM_CSSCACHE_MAXGROUPDIRS, $cache_group_dir, $cache_disk_fail);
                        if ($rc_cache)
                            $css_file_path = $cache_group_dir . "/";
                    }
                    else
                    {
                        $rc_cache = cm_filecache_write($cache_dir . "/" . $cache_subdir_storing, $css_file_key . ".css", $str_css_buffer, $now + $css_expire);
                        if ($rc_cache)
                            $css_file_path = $cache_subdir_storing;
                    }
                }

                // manage compression
                if ($enable_gzip_file && !$compressed)
                {
                    if ($uncompressed)
                        $str_css_buffer = file_get_contents($uncompressed_file);

                    if (CM_SHOWFILES_FORCE_PATH && CM_CSSCACHE_RENDER_PATH && strlen($str_css_buffer))
                    { //manipolazione percorsi dei file media per avere la gestione della cache
                        $str_css_buffer = str_replace(FF_SITE_PATH . FF_UPDIR . '/', CM_SHOWFILES . '/', $str_css_buffer);
                        //if (CM_CSSCACHE_RENDER_THEME_PATH)
                         //   $str_css_buffer = str_replace(FF_SITE_PATH . FF_THEME_DIR . '/', CM_SHOWFILES . '/', $str_css_buffer);
                    }

                    if (CM_CSSCACHE_GROUPDIRS && !$css_smart)
                    {
                        $rc_cache = cm_filecache_groupwrite(CM_CSSCACHE_DIR, $cache_dir, "", $css_file_key . ".css.gz", gzencode($str_css_buffer), $now + $css_expire, CM_CSSCACHE_MAXGROUPDIRS, $cache_group_dir, $cache_disk_fail);
                        if ($rc_cache)
                            $css_file_path = $cache_group_dir . "/";
                    }
                    else
                    {
                        $rc_cache = cm_filecache_write($cache_dir . "/" . $cache_subdir_storing, $css_file_key . ".css.gz", gzencode($str_css_buffer), $now + $css_expire);
                        if ($rc_cache)
                            $css_file_path = $cache_subdir_storing;
                    }
                }

                if (!$rc_cache)
                {
                    ffErrorHandler::raise("Unable to write CSS cache", E_USER_ERROR, null, get_defined_vars());
                }
            }

            if ($allow_css_defer_loading)
            {
                if (!$parsed_externals) // true when !js_smart
                {
                    foreach ($css_buffer_path AS $css_buffer_key => $css_buffer_value)
                    {
                        if (substr(strtolower($css_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($css_buffer_value["path"]), 0, 8) === "https://" || substr($css_buffer_value["path"], 0, 2) === "//")
                        {
                            $oPage->page_defer["css"][] = array(
                                "path" => $css_buffer_value["path"]
                            , "media" => $css_buffer_media
                            );
                            $oPage->page_defer["css-embed"][] = '<link rel="stylesheet" type="text/css" ' . ($css_buffer_media == "default" ? "" : 'media="' . $css_buffer_media . '" ') . 'href="' . $css_buffer_value["path"] . '"/>';
                            //$oPage->page_defer["css|" . $css_buffer_media][] = '"' . $css_buffer_value["path"] . '"';
                        }
                    }
                    reset($css_buffer_path);
                }

                $oPage->page_defer["css"][] = array(
                    "path" => $css_buffer_value["path"]
                , "media" => $css_buffer_media
                );
                $oPage->page_defer["css-embed"][] = '<link rel="stylesheet" type="text/css" ' . ($css_buffer_media == "default" ? "" : 'media="' . $css_buffer_media . '" ') . 'href="' . CM_CSSCACHE_SHOWPATH . "/" . $css_file_path . $css_file_key . ".css" . '"/>';
                //$oPage->page_defer["css|" . $css_buffer_media][] = '"' . CM_CSSCACHE_SHOWPATH . "/" . $css_file_path . $css_file_key . ".css" . '"';
            }
            else
            {
                if (!$parsed_externals) // true when !js_smart
                {
                    foreach ($css_buffer_path AS $css_buffer_key => $css_buffer_value)
                    {
                        if (substr(strtolower($css_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($css_buffer_value["path"]), 0, 8) === "https://" || substr($css_buffer_value["path"], 0, 2) === "//")
                        {
                            $link_properties = "";
                            $link_properties .= ' rel="stylesheet"';
                            $link_properties .= ' type="text/css"';

                            $oPage->tpl[0]->set_var("css_path", $css_buffer_value["path"]);
                            $oPage->tpl[0]->set_var("link_properties", $link_properties);

                            /*$oPage->tpl[0]->set_var("css_rel", "stylesheet");
                            $oPage->tpl[0]->set_var("css_type", "text/css");*/
                            //$oPage->tpl[0]->set_var("SectCssMedia", "");
                            $oPage->tpl[0]->set_var("CssEmbed", "");
                            $oPage->tpl[0]->parse("SectCssLink", false);
                            $oPage->tpl[0]->parse("SectCss", true);
                        }
                    }
                    reset($css_buffer_path);
                }

                $link_properties = "";
                $link_properties .= ' rel="stylesheet"';
                $link_properties .= ' type="text/css"';
                if ($css_buffer_media !== "default")
                    $link_properties .= ' media="' . $css_buffer_media . '"';

                $oPage->tpl[0]->set_var("css_path", CM_CSSCACHE_SHOWPATH . "/" . $css_file_path . $css_file_key . ".css");
                $oPage->tpl[0]->set_var("link_properties", $link_properties);

                /*$oPage->tpl[0]->set_var("css_rel", "stylesheet");
                $oPage->tpl[0]->set_var("css_type", "text/css");*/
                //$oPage->tpl[0]->set_var("SectCssMedia", "");
                $oPage->tpl[0]->set_var("CssEmbed", "");
                $oPage->tpl[0]->parse("SectCssLink", false);
                $oPage->tpl[0]->parse("SectCss", true);
            }
        }
    }

    // ********************************************
    //  JS MINIFY / COMPRESSION
    if ($oPage->compact_js)
    {
        if (CM_CACHE_PURGE_JS)
        {
            $oPage->tpl[0]->DBlocks["main"] = ffPage_seo_optimize_js($oPage, $oPage->tpl[0]->DBlocks["main"]);
            foreach ($oPage->tpl[0]->ParsedBlocks as $key => $value)
            {
                $oPage->tpl[0]->ParsedBlocks[$key] = ffPage_seo_optimize_js($oPage, $value);
            }
        }

        if (CM_JSCACHE_DEFERLOADING && is_array($oPage->js_buffer) && count($oPage->js_buffer))
            $allow_js_defer_loading = true;

        if (is_array($oPage->js_buffer) && count($oPage->js_buffer))
        {
            $cache_dir = CM_JSCACHE_DIR;
            if (CM_JSCACHE_BYDOMAIN)
            {
                $cache_domain_prefix = $_SERVER["HTTP_HOST"];
                if (CM_PAGECACHE_BYDOMAIN_STRIPWWW && strpos($cache_domain_prefix, "www.") === 0)
                    $cache_domain_prefix = substr($cache_domain_prefix, 4);
                $cache_dir .= "/" . $cache_domain_prefix;
            }

            $rc_cache = false;
            $compressed = null;
            $compressed_subpath = "";
            $uncompressed = null;
            $uncompressed_subpath = "";
            $uncompressed_file = "";
            $parsed_externals = false;
            $js_file_path = "";
            $js_file_key = null;

            $cache_group_dir = 0;

            // check for smart paths
            $js_smart = CM_JSCACHE_SMARTURLS;
            $js_smart_name = "";
            $js_expire = CM_JSCACHE_DEFAULT_EXPIRES;

            $res = $cm->doEvent("on_compact_js", array());
            $rc = end($res);
            if ($rc !== null)
            {
                if (array_key_exists("smart", $rc))
                    $js_smart = $rc["smart"];

                if (array_key_exists("smart_name", $rc))
                    $js_smart_name = $rc["smart_name"];

                if (array_key_exists("expire", $rc))
                    $js_expire = $rc["expire"];
            }

            if ($js_smart)
            {
                $js_file_key = $js_smart_name;

                if ($enable_gzip_file)
                {
                    $finfo = new SplFileInfo($cache_dir . "/" . $js_smart_name . ".js.gz");
                    $compressed = $finfo->isFile() && cm_filecache_check_expiration($finfo->getMTime(), $finfo->getCTime(), $now, CM_CSSCACHE_LAST_VALID);
                }

                $finfo = new SplFileInfo($cache_dir . "/" . $js_smart_name . ".js");
                if ($uncompressed = $finfo->isFile() && cm_filecache_check_expiration($finfo->getMTime(), $finfo->getCTime(), $now, CM_CSSCACHE_LAST_VALID))
                    $uncompressed_file = $cache_dir . "/" . $js_smart_name . ".js";

                /*$cache_file = cm_filecache_find($cache_dir, $js_smart_name, CM_JSCACHE_GROUPDIRS, true, CM_JSCACHE_LAST_VALID, CM_JSCACHE_SCALEDOWN, $now, null, false);
                if ($cache_file)
                {
                    $js_file_path = str_replace($cache_dir, "", $cache_file["filedir"]);
                }*/
            }
            else
            {
                $parsed_externals = true; // avoid useless cycle
                $max_mtime = 0;
                foreach ($oPage->js_buffer AS $js_buffer_key => $js_buffer_value)
                {
                    if (strlen($js_buffer_value["content"]))
                        $js_file_key .= $js_buffer_value["content"];
                    elseif (substr(strtolower($js_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($js_buffer_value["path"]), 0, 8) === "https://" || substr($js_buffer_value["path"], 0, 2) === "//")
                    {
                        if (!$allow_js_defer_loading)
                        {
                            $oPage->tpl[0]->set_var("js_path", $js_buffer_value["path"]);
                            $oPage->tpl[0]->set_var("js_embed", "");
                            $oPage->tpl[0]->parse("SectJsSrc", false);
                            $oPage->tpl[0]->parse("SectJs", true);
                        }
                        else
                        {
                            $oPage->page_defer["js"][] = $js_buffer_value["path"];
                            //$oPage->page_defer["js"][] = '"' . $js_buffer_value["path"] . '"';
                        }
                    }
                    else
                    {
                        if (
                            strpos($js_buffer_value["path"], ".min.js") === false
                            && @is_file(ffCommon_dirname($js_buffer_value["path"]) . "/" . ffGetFilename($js_buffer_value["path"]) . ".min.js")
                        )
                        {
                            $oPage->js_buffer[$js_buffer_key]["path"] = ffCommon_dirname($js_buffer_value["path"]) . "/" . ffGetFilename($js_buffer_value["path"]) . ".min.js";
                        }

                        if (@is_file($oPage->js_buffer[$js_buffer_key]["path"]))
                        {
                            $js_file_key .= $oPage->js_buffer[$js_buffer_key]["path"];
							$tmp_mtime = filemtime($oPage->js_buffer[$js_buffer_key]["path"]);
							if ($tmp_mtime > $max_mtime)
								$max_mtime = $tmp_mtime;
                        }
                    }
                    $js_file_key .= "_";
                }
                reset($oPage->js_buffer);

                $js_file_key = sha1($js_file_key);
                if (CM_JSCACHE_GROUPHASH)
                {
                    $parts = str_split($js_file_key, CM_JSCACHE_HASHSPLIT);
                    $cache_dir .= "/" . implode("/", $parts);
                }

                //if ($max_mtime)
                //    $js_file_key .= "_" . $max_mtime;

                if (!CM_JSCACHE_GROUPDIRS)
                {
                    if(CM_CACHE_STORAGE_SAVING_MODE)
                        $cache_subdir_storing = substr($js_file_key, 0, CM_CACHE_STORAGE_SAVING_MODE) . "/";

					if($enable_gzip_file)
					{
						if(file_exists($cache_dir . "/" . $cache_subdir_storing . $js_file_key . ".js.gz")
							&& (filemtime($cache_dir . "/" . $cache_subdir_storing . $js_file_key . ".js.gz") - $js_expire) >= $max_mtime
						) {
							$compressed = true;
						}
						$compressed_subpath = $cache_subdir_storing;
					}
					if(file_exists($cache_dir . "/" . $cache_subdir_storing . $js_file_key . ".js")
						&& (filemtime($cache_dir . "/" . $cache_subdir_storing . $js_file_key . ".js") - $js_expire) >= $max_mtime
					) {
						$uncompressed = true;
						$uncompressed_file = $cache_dir . "/" . $cache_subdir_storing . $js_file_key . ".js";
					}

					$uncompressed_subpath = $cache_subdir_storing;
                }
                else if (file_exists($cache_dir))
                {
                    $itGroup = new DirectoryIterator($cache_dir);
                    foreach($itGroup as $fiGroup)
                    {
                        if ($fiGroup->isDot())
                            continue;

						if ($enable_gzip_file && !$compressed) {

							if(file_exists($fiGroup->getPathname() . "/" . $js_file_key . ".js.gz")
								&& (filemtime($fiGroup->getPathname() . "/" . $js_file_key . ".js.gz") - $js_expire) >= $max_mtime
							) {
								$compressed = true;
							}
							$compressed_subpath = $fiGroup->getBasename() . "/";
						}
						if(!$uncompressed)
						{
							if(file_exists($fiGroup->getPathname() . "/" . $js_file_key . ".js")
								&& (filemtime($fiGroup->getPathname() . "/" . $js_file_key . ".js") - $js_expire) >= $max_mtime
							) {
								$uncompressed = true;
								$uncompressed_file = $fiGroup->getPathname() . "/" . $js_file_key . ".js";
							}
							$uncompressed_subpath = $fiGroup->getBasename() . "/";
						}

                        if ($compressed && $uncompressed)
                            break;
                    }
                }
            }

            if ($enable_gzip_file && $compressed && ffHTTP_encoding_isset("gzip"))
            {
                $js_file_path = $compressed_subpath;
            }
            elseif($uncompressed)
            {
                $js_file_path = $uncompressed_subpath;
            }
            else // make cache
            {
                if (!$uncompressed)
                {
                    $str_js_buffer = "";
                    $count_exclude_jsmin = 0;
                    $count_include_jsmin = 0;
                    //$str_js_compressed_buffer = "";

                    foreach ($oPage->js_buffer AS $js_buffer_key => $js_buffer_value)
                    {
                        if (strlen($js_buffer_value["content"]))
                        {
                            $str_js_buffer .= "\n" . $js_buffer_value["content"];
                            $count_include_jsmin++;
                        }
                        elseif (substr(strtolower($js_buffer_value["path"]), 0, 7) !== "http://" && substr(strtolower($js_buffer_value["path"]), 0, 8) !== "https://" && substr($js_buffer_value["path"], 0, 2) !== "//")
                        {
                            if (false !== ($res = @file_get_contents($js_buffer_value["path"])))
                            {
                                $res = trim($res);
                                if (!strlen($res))
                                    continue;

                                if (substr($res, -1) != ";")
                                    $res .= ";";

                                if (strpos($js_buffer_value["path"], ".min.js") === false)
                                {
                                    $str_js_buffer .= "\n" . $res;
                                    $count_include_jsmin++;
                                }
                                else
                                {
                                    $exclude_jsmin[$count_exclude_jsmin] = $res;
                                    $str_js_buffer .= "/*!". $count_exclude_jsmin . "*/\n";
                                    $count_exclude_jsmin++;
                                }
                            }
                            //else
                            //ffErrorHandler::raise ("Unable to open JS file", E_USER_ERROR, null, get_defined_vars());
                        }
                    }

					$str_js_buffer = cmCache_normalizeUrl($str_js_buffer);

                    if ($oPage->compact_js == 2 && $count_include_jsmin)
                    {
                        //$before = microtime();
                        switch (CM_JSCACHE_MINIFIER)
                        {
                            case "jsmin":
                                if (!class_exists("JSMin"))
                                    require(__TOP_DIR__ . "/library/jsmin/JSMin.php");
                                $str_js_buffer = JSMin::minify($str_js_buffer);
                                break;

                            case "minify":
                                if (!class_exists("JSMin"))
                                    require(__TOP_DIR__ . "/library/minify/min/lib/JSMin.php");
                                $str_js_buffer = JSMin::minify($str_js_buffer);
                                break;

                            case "pecl_jsmin":
                                //do { $str_js_buffer = preg_replace("/\n[\s]*\n/", "\n", $str_js_buffer, -1, $count); } while ($count);
                                //$str_js_buffer = str_replace("\r\n", "\n", $str_js_buffer);
                                $str_js_buffer = jsmin($str_js_buffer);
                                break;

                            case "yui":
                                require_once(__TOP_DIR__ . "/library/gminify/YUICompressor.php");
                                Minify_YUICompressor::$jarFile = __TOP_DIR__ . "/library/gminify/yuicompressor-2.4.8.jar";
                                if (!file_exists(CM_JSCACHE_DIR))
                                {
                                    @mkdir(CM_JSCACHE_DIR, 0777, true);
                                }
                                Minify_YUICompressor::$tempDir = CM_JSCACHE_DIR;
                                $str_js_buffer = Minify_YUICompressor::minifyJs($str_js_buffer, array('nomunge' => true, 'line-break' => 1000));
                                break;
                        }
                        /*$after = microtime();
                        echo "before: " . $before . "<br />";
                        echo "after: " . $after . "<br />";*/
                    }

                    // put back already minified files
                    if (is_array($exclude_jsmin) && count($exclude_jsmin))
                    {
                        foreach($exclude_jsmin AS $exclude_jsmin_key => $exclude_jsmin_value)
                        {
                            $str_js_buffer = str_replace("/*!". $exclude_jsmin_key . "*/\n;", $exclude_jsmin_value, $str_js_buffer); // needed for ; adding bug
                            $str_js_buffer = str_replace("/*!". $exclude_jsmin_key . "*/\n", $exclude_jsmin_value, $str_js_buffer);
                        }
                    }

                    // write it uncompressed
                    if (CM_JSCACHE_GROUPDIRS && !$js_smart)
                    {
                        $rc_cache = cm_filecache_groupwrite(CM_JSCACHE_DIR, $cache_dir, "", $js_file_key . ".js", $str_js_buffer, $now + $js_expire, CM_JSCACHE_MAXGROUPDIRS, $cache_group_dir, $cache_disk_fail);
                        if ($rc_cache)
                            $js_file_path = $cache_group_dir . "/";
                    }
                    else
                    {
                        $rc_cache = cm_filecache_write($cache_dir . "/" . $cache_subdir_storing, $js_file_key . ".js", $str_js_buffer, $now + $js_expire);
                        if ($rc_cache)
                            $js_file_path = $cache_subdir_storing;
                    }
                }

                // manage compression
                if ($enable_gzip_file && !$compressed)
                {
                    if ($uncompressed)
                        $str_js_buffer = file_get_contents($uncompressed_file);

                    if (CM_JSCACHE_GROUPDIRS && !$js_smart)
                    {
                        $rc_cache = cm_filecache_groupwrite(CM_JSCACHE_DIR, $cache_dir, "", $js_file_key . ".js.gz", gzencode($str_js_buffer), $now + $js_expire, CM_JSCACHE_MAXGROUPDIRS, $cache_group_dir, $cache_disk_fail);
                        if ($rc_cache)
                            $js_file_path = $cache_group_dir . "/";
                    }
                    else
                    {
                        $rc_cache = cm_filecache_write($cache_dir . "/" . $cache_subdir_storing, $js_file_key . ".js.gz", gzencode($str_js_buffer), $now + $js_expire);
                        if ($rc_cache)
                            $js_file_path = $cache_subdir_storing;
                    }
                }

                if (!$rc_cache)
                {
                    ffErrorHandler::raise("Unable to write JS cache", E_USER_ERROR, null, get_defined_vars());
                }
            }

            if (!$allow_js_defer_loading)
            {
                if (!$parsed_externals) // true when !js_smart
                {
                    foreach ($oPage->js_buffer AS $js_buffer_key => $js_buffer_value)
                    {
                        if (substr(strtolower($js_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($js_buffer_value["path"]), 0, 8) === "https://" || substr($js_buffer_value["path"], 0, 2) === "//")
                        {
                            $oPage->tpl[0]->set_var("js_path", $js_buffer_value["path"]);
                            $oPage->tpl[0]->set_var("js_embed", "");
                            $oPage->tpl[0]->parse("SectJsSrc", false);
                            $oPage->tpl[0]->parse("SectJs", true);
                        }
                    }
                    reset($oPage->js_buffer);
                }

                $oPage->tpl[0]->set_var("js_path", CM_JSCACHE_SHOWPATH . "/" . $js_file_path . $js_file_key . ".js");
                $oPage->tpl[0]->set_var("js_embed", "");
                $oPage->tpl[0]->parse("SectJsSrc", false);
                $oPage->tpl[0]->parse("SectJs", true);
            }
            else
            {
                if (!$parsed_externals)  // true when !js_smart
                {
                    foreach ($oPage->js_buffer AS $js_buffer_key => $js_buffer_value)
                    {
                        if (substr(strtolower($js_buffer_value["path"]), 0, 7) === "http://" || substr(strtolower($js_buffer_value["path"]), 0, 8) === "https://" || substr($js_buffer_value["path"], 0, 2) === "//")
                        {
                            $oPage->page_defer["js"][] = $js_buffer_value["path"];
                            //$oPage->page_defer["js"][] = '"' . $js_buffer_value["path"] . '"';
                        }
                    }
                    reset($oPage->js_buffer);
                }

                $oPage->page_defer["js"][] = CM_JSCACHE_SHOWPATH . "/" . $js_file_path . $js_file_key . ".js";
                //$oPage->page_defer["js"][] = '"' . CM_JSCACHE_SHOWPATH . "/" . $js_file_path . $js_file_key . ".js" . '"';
            }
        }
    }

    /* if (count($oPage->page_defer))
     {
         foreach($oPage->page_defer AS $page_defer_key => $page_defer_value)
         {
             if (is_array($oPage->page_defer[$page_defer_key]) && count($oPage->page_defer[$page_defer_key]))
             {
                 $oPage->tpl[0]->set_var("defer_paths", implode(",", $oPage->page_defer[$page_defer_key]));
                 if (strpos($page_defer_key, "css|") === 0)
                 {
                     $tmp = explode("|", $page_defer_key);
                     $page_defer_key = $tmp[0];
                     $cssdefer_media = $tmp[1];
                     if ($cssdefer_media === "default")
                         $cssdefer_media = "";
                     else
                         $cssdefer_media = ", '" . $cssdefer_media . "'";
                 }
                 else
                     $cssdefer_media = "";
                 $oPage->tpl[0]->set_var("defer_type", $page_defer_key);
                 $oPage->tpl[0]->set_var("cssdefer_media", $cssdefer_media);
                 $oPage->tpl[0]->parse("SectJSDeferType", true);
             }
         }

         $oPage->tpl[0]->parse("SectJSDefer", false);
         if ($allow_css_defer_loading)
             $oPage->tpl[0]->parse("SectAboveTheFold", false);
     }*/

    if (count($oPage->page_defer)) // TOCHECK
    {
        if(is_array($oPage->page_defer["css"]) && count($oPage->page_defer["css"])) {
            if($oPage->above_the_fold) {
                $link_properties = ' id="above-the-fold" inline="inline"';
                $oPage->tpl[0]->set_var("link_properties", $link_properties);
                $oPage->tpl[0]->set_var("css_embed", file_get_contents($oPage->above_the_fold));
                $oPage->tpl[0]->parse("SectCssEmbed", false);
                $oPage->tpl[0]->parse("SectCss", true);
            }

            $oPage->tpl[0]->set_var("defer_css", implode("", $oPage->page_defer["css-embed"]));
            $oPage->tpl[0]->parse("SectCSSDefer", false);
        }

        if(is_array($oPage->page_defer["js"]) && count($oPage->page_defer["js"])) {
            $oPage->tpl[0]->set_var("defer_js", str_replace("\\/", "/", json_encode($oPage->page_defer["js"])));
            $oPage->tpl[0]->parse("SectJSDefer", false);
        }
    }
}

/*
function ffPage_on_fixed_process_before(ffPage_base $oPage)
{
	if ($oPage->use_own_form !== null)
		return;

	$cm = cm::getInstance();

	if ($cm->layout_vars["exclude_form"])
	{
		$oPage->tpl[0]->set_var("SectFormHeader", "");
		$oPage->tpl[0]->set_var("SectFormFooter", "");
	}
	else
	{
		$oPage->tpl[0]->parse("SectFormHeader", false);
		$oPage->tpl[0]->parse("SectFormFooter", false);
	}
}*/

function ffPage_getTemplateDir(ffPage_base $oPage, $template_file)
{
    return cm_findCascadeTemplate("ffPage", $oPage->getTheme(), $template_file);
}

function ffPage_getLayerDir(ffPage_base $oPage, $file)
{
    $tmp = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/layouts/" . ltrim($file, "/"), $oPage->getTheme(), false);
    if ($tmp !== null)
        return ffCommon_dirname($tmp);
    else
        return null;
}

function ffPage_getLayoutDir(ffPage_base $oPage, $file)
{
    $tmp = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/layouts/" . ltrim($file, "/"), $oPage->getTheme(), false);
    if ($tmp !== null)
        return ffCommon_dirname($tmp);
    else
        return null;
}

function ffGrid_getTemplateDir(ffGrid_base $grid)
{
    if ($grid->template_dir !== null)
        return $grid->template_dir;
    else
        return cm_findCascadeTemplate("ffGrid", $grid->getTheme(), $grid->template_file, $grid->id);
}

function ffRecord_getTemplateDir(ffRecord_base $record)
{
    if ($record->template_dir !== null)
        return $record->template_dir;
    else
        return cm_findCascadeTemplate("ffRecord", $record->getTheme(), $record->template_file, $record->id);
}

function ffDetails_getTemplateDir(ffDetails_base $details)
{
    if ($details->template_dir !== null)
        return $details->template_dir;
    else
        return cm_findCascadeTemplate("ffDetails", $details->getTheme(), $details->template_file, $details->id);
}

function ffButton_getTemplateDir(ffButton_base $button)
{
    if ($button->template_dir !== null)
        return $button->template_dir;
    else
        return cm_findCascadeTemplate("ffButton", $button->getTheme(), $button->getTemplateFile(), $button->parent[0]->id);
}

function ffField_getTemplateDir(ffField_base $field, $control_type)
{
    if ($field->template_dir !== null)
        return $field->template_dir;
    else
        return cm_findCascadeTemplate("ffField", $field->getTheme(), $field->getTemplateFile($control_type), $field->parent[0]->id);
}

function ffPageNavigator_getTemplateDir(ffPageNavigator_base $navigator)
{
    if ($navigator->template_dir !== null)
        return $navigator->template_dir;
    else
        return cm_findCascadeTemplate("ffPageNavigator", $navigator->getTheme(), $navigator->template_file, $navigator->parent[0]->id);
}

function cm_findCascadeTemplate($class_type, $theme, $template_file, $id = null)
{
    if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo "<br />";
    $cm = cm::getInstance();

    if (!is_null($id))
        $suffixes[] = $id;
    $suffixes[] = $class_type;

    if (isset($cm->oPage->components[$id]->user_vars["appletid"]))
        $applet_data = $cm->loaded_applets[$cm->oPage->components[$id]->user_vars["appletid"]];
    else
        $applet_data = null;

    foreach ($suffixes as $key => $suffix)
    {
//	ffErrorHandler::raise("asd", E_USER_ERROR, $template_file, get_defined_vars());
        $tmp = preg_replace('/\\.[^.\\s]{3,4}$/', '', rtrim($cm->oPage->page_path, "/"));

        $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/contents" . $tmp . "/" . $suffix;
        if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
        if (is_file($base_path . "/" . $template_file))
        {
            return $base_path;
        }

        if (isset($applet_data["module"]))
        {
            $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/modules/" . $applet_data["module"] . "/applets/" . $applet_data["name"]  . "/" . $suffix;
            if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
            if (is_file($base_path . "/" . $template_file))
            {
                return $base_path;
            }

            $base_path = CM_MODULES_ROOT . "/" . $applet_data["module"] . "/themes/" . $theme . "/applets/" . $applet_data["name"]  . "/" . $suffix;
            if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
            if (is_file($base_path . "/" . $template_file))
            {
                return $base_path;
            }
        }
        elseif (strlen((string)$cm->processed_rule["rule"]->destination->module))
        {
            $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/modules/" . (string)$cm->processed_rule["rule"]->destination->module . "/contents" . rtrim(ffCommon_dirname($cm->script_name), "/") . "/" . $suffix;
            if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
            if (is_file($base_path . "/" . $template_file))
            {
                return $base_path;
            }

            $base_path = CM_MODULES_ROOT . "/" . (string)$cm->processed_rule["rule"]->destination->module . "/themes/" . $theme . "/contents" . rtrim(ffCommon_dirname($cm->script_name), "/") . "/" . $suffix;
            if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
            if (is_file($base_path . "/" . $template_file))
            {
                return $base_path;
            }
        }
        else
        {
            $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . rtrim(ffCommon_dirname($cm->script_name), "/") . "/" . $suffix;
            if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
            if (is_file($base_path . "/" . $template_file))
            {
                return $base_path;
            }
        }
    }

    if (isset($applet_data["module"]))
    {
        $base_path = CM_MODULES_ROOT . "/" . $applet_data["module"] . "/themes/" . $theme . "/ff/" . $class_type;
        if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
        if (is_file($base_path . "/" . $template_file))
        {
            return $base_path;
        }
    }
    if (strlen((string)$cm->processed_rule["rule"]->destination->module))
    {
        $base_path = CM_MODULES_ROOT . "/" . (string)$cm->processed_rule["rule"]->destination->module . "/themes/" . $theme . "/ff/" . $class_type;
        if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
        if (is_file($base_path . "/" . $template_file))
        {
            return $base_path;
        }
    }

    $base_path = ff_getThemeDir($theme) . "/themes/" . $theme . "/ff/" . $class_type;
    if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $base_path . "/" . $template_file . "<br />";
    if (is_file($base_path . "/" . $template_file))
        return $base_path;

    if ($theme != cm_getMainTheme())
        return cm_findCascadeTemplate($class_type, cm_getMainTheme(), $template_file, $id);

    if ($theme == cm_getMainTheme())
        ffErrorHandler::raise("CM: Unable to find Template", E_USER_ERROR, $template_file, get_defined_vars());
}

function ffPage_on_widget_load(ffPage_base $oPage, $name, $path, $ref)
{
    if ($path !== null)
    {
        $realpath = $path . "/" . $name;
        if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
        {
            return array("realpath" => $realpath);
        }
    }

    if ($ref == null)
        $ref = $oPage;

    switch (true)
    {
        case (is_subclass_of($ref, "ffPage_base")):
            return cm_findCascadeWidget($name, $oPage->getTheme(), "ffPage");
        case (is_subclass_of($ref, "ffField_base")):
            return cm_findCascadeWidget($name, $oPage->getTheme(), "ffField", $ref->id);
        case (is_subclass_of($ref, "ffGrid_base")):
            return cm_findCascadeWidget($name, $oPage->getTheme(), "ffGrid", $ref->id);
        case (is_subclass_of($ref, "ffRecord_base")):
            return cm_findCascadeWidget($name, $oPage->getTheme(), "ffRecord", $ref->id);
        case (is_subclass_of($ref, "ffDetails_base")):
            return cm_findCascadeWidget($name, $oPage->getTheme(), "ffDetails", $ref->id);

        default:
            return null;
    }
}

function cm_findCascadePlugin($name, $theme)
{
    $cm = cm::getInstance();

    $realpath = FF_THEME_DISK_PATH . "/" . $theme . "/contents" . $cm->oPage->page_path . "/ffPage/plugins/" . $name;
    if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
    {
        return array("realpath" => $realpath, "source_path" => ff_getThemePath($theme) . "/" . $theme);
    }

    $realpath = FF_THEME_DISK_PATH . "/" . $theme . "/ff/ffPage/plugins/" . $name;
    if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
    {
        return array("realpath" => $realpath, "source_path" => ff_getThemePath($theme) . "/" . $theme);
    }

    if ($theme != cm_getMainTheme())
        return cm_findCascadePlugin($name, cm_getMainTheme());

    if ($theme == cm_getMainTheme())
        ffErrorHandler::raise("CM: Unable to find Plugin", E_USER_ERROR, null, get_defined_vars());
}

function cm_findCascadeWidget($name, $theme, $class, $id = null)
{
    $cm = cm::getInstance();

    if ($id !== null)
    {
        $tmp = preg_replace('/\\.[^.\\s]{3,4}$/', '', rtrim($cm->oPage->page_path, "/"));
        $realpath = FF_THEME_DISK_PATH . "/" . $theme . "/contents" . $tmp . "/" . $id . "/widgets/" . $name;
        if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
        {
            return array("realpath" => $realpath, "source_path" => ff_getThemePath($theme) . "/" . $theme);
        }
    }

    $tmp = preg_replace('/\\.[^.\\s]{3,4}$/', '', rtrim($cm->oPage->page_path, "/"));
    $realpath = FF_THEME_DISK_PATH . "/" . $theme . "/contents" . $tmp . "/" . $class . "/widgets/" . $name;
    if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
    {
        return array("realpath" => $realpath, "source_path" => ff_getThemePath($theme) . "/" . $theme);
    }

    $realpath = FF_THEME_DISK_PATH . "/" . $theme . "/ff/" . $class . "/widgets/" . $name;
    if (is_file($realpath . "/ffWidget." . FF_PHP_EXT))
    {
        return array("realpath" => $realpath, "source_path" => ff_getThemePath($theme) . "/" . $theme);
    }

    if ($theme != cm_getMainTheme())
        return cm_findCascadeWidget($name, cm_getMainTheme(), $class, $id);

    if ($theme == cm_getMainTheme())
        ffErrorHandler::raise("CM: Unable to find Widget", E_USER_ERROR, null, get_defined_vars());
}

function cm_cascadeFindTemplate($path, $module = false, $raise_error = false)
{
    $cm = cm::getInstance();

    $filename = cm_moduleCascadeFindTemplate(ff_getThemeDir($cm->oPage->getTheme()) . FF_THEME_DIR, "/contents" . $cm->path_info . "/" . basename($path), $cm->oPage->theme, $raise_error);
    if ($module && $filename === null)
        $filename = cm_moduleCascadeFindTemplate(ff_getThemeDir($cm->oPage->getTheme()) . FF_THEME_DIR, "/modules/" . $module . $path, $cm->oPage->theme, $raise_error);
    if ($module && $filename === null)
        $filename = cm_moduleCascadeFindTemplate(ff_getModuleDir($module) . "/themes", $path, $cm->oPage->theme, $raise_error);
    if ($filename === null)
        $filename = cm_moduleCascadeFindTemplate(ff_getThemeDir(cm_getMainTheme()) . FF_THEME_DIR, "/contents" . $cm->path_info . "/" . basename($path), $cm->oPage->theme, $raise_error);
    if ($filename === null)
        $filename = cm_moduleCascadeFindTemplate(ff_getThemeDir(cm_getMainTheme()) . FF_THEME_DIR, "/modules/" . $module . $path, $cm->oPage->theme, $raise_error);
    if ($module && $filename === null)
        $filename = cm_moduleCascadeFindTemplate(ff_getModuleDir($module) . "/themes", $path, $cm->oPage->theme, $raise_error);
    if (!$module && $filename === null)
        $filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, $path, $cm->oPage->theme, $raise_error);

    if($raise_error && $filename === null) {
        ffErrorHandler::raise("CM: Unable to find the Template", E_USER_ERROR, null, get_defined_vars());
    }
    return $filename;
}

function cm_resolveResourceFromTemplate($path, $source, $dest = "")
{
    return str_replace(array((strpos(__PRJ_DIR__, $path) === 0 ? __PRJ_DIR__ : __TOP_DIR__), $source), array(FF_SITE_PATH, $dest), $path);
}

function cm_moduleCascadeFindTemplate($base_path, $file, $theme, $raise_error = true)
{
    $realpath = rtrim($base_path, '/') . "/" . trim($theme, '/') . "/" . ltrim($file, '/');
    if (defined("FF_URLPARAM_SHOWCASCADELOADER")) echo $realpath . "<br />";
    if (is_file($realpath))
        return $realpath;

    if ($theme != cm_getMainTheme())
        return cm_moduleCascadeFindTemplate($base_path, $file, cm_getMainTheme(), $raise_error);

    if ($theme == cm_getMainTheme() && $raise_error)
        ffErrorHandler::raise("CM: Unable to find the Template", E_USER_ERROR, null, get_defined_vars());
    else
        return null;
}

function cm_moduleCascadeFindTemplateByPath($module, $file, $theme, $raise_error = true)
{
    if ($theme === null)
    {
        $realpath = realpath(CM_MODULES_ROOT . "/" . $module . "/themes/" . ltrim($file, '/'));
        if (is_file($realpath))
            return $realpath;
    }
    else
    {
        // INTO GLOBAL THEME DIR
        $realpath = realpath(FF_THEME_DISK_PATH . "/" . trim($theme, '/') . "/modules/" . $module . "/" . ltrim($file, '/'));
        if (is_file($realpath))
            return $realpath;

        $realpath = realpath(CM_MODULES_ROOT . "/" . $module . "/themes/" . trim($theme, '/') . "/" . ltrim($file, '/'));
        if (is_file($realpath))
            return $realpath;
    }

    if ($theme !== null && $theme !== cm_getMainTheme())
        return cm_moduleCascadeFindTemplateByPath($module, $file, cm_getMainTheme(), $raise_error);

    if ($theme === cm_getMainTheme())
        return cm_moduleCascadeFindTemplateByPath($module, $file, null, $raise_error);

    if ($theme === null && $raise_error)
        ffErrorHandler::raise("CM: Unable to find the Template", E_USER_ERROR, null, get_defined_vars());
    else
        return null;
}

function ffPage_on_js_parse($page, $name, $path, $file)
{
    if ($path !== null)
    {
        return null;
    }
    else
    {
        if ($file === null)
            $tmp_file = $name . ".js";
        else
            $tmp_file = $file;

        return cm_findCascadeJS($page, null, $name, $tmp_file);
    }
}

function ffPage_on_css_parse($page, $name, $path, $file)
{
    if ($path !== null)
        return null;
    else
    {
        if ($file === null)
            $tmp_file = $name . ".css";
        else
            $tmp_file = $file;

        return cm_findCascadeCSS($page, $page->getTheme(), $tmp_file);
    }
}

function cm_findCascadeJS($page, $theme, $name, $file)
{
    if ($theme === null)
    {
        $realfile = FF_THEME_DISK_PATH . "/library/" . $name . "/" . $file;
        if (is_file($realfile))
        {
            return array(
                "file" => $file
            , "path" => FF_THEME_DIR . "/library/" . $name . "/"
            );
        }

        return cm_findCascadeJS($page, $page->getTheme(), $name, $file);
    }
    else
    {
        $realfile = ff_getThemeDir($theme) . FF_THEME_DIR . "/" . $theme . "/javascript/" . $file;
        if (is_file($realfile))
        {
            return array(
                "file" => $file
            , "path" => FF_THEME_DIR . "/" . $theme . "/javascript/"
            );
        }

        if ($theme != cm_getMainTheme())
            return cm_findCascadeJS($page, cm_getMainTheme(), $name, $file);
    }

    if ($theme == cm_getMainTheme())
        ffErrorHandler::raise("CM: Unable to find the JS", E_USER_ERROR, $page, get_defined_vars());
}

function cm_findCascadeCSS($page, $theme, $file)
{

    $realfile = ff_getThemeDir($theme) . FF_THEME_DIR . "/" . $theme . "/css/" . $file;
    if (is_file($realfile))
    {
        return array(
            "file" => $file
        , "path" => FF_THEME_DIR . "/" . $theme . "/css/"
        );
    }

    if ($theme != cm_getMainTheme()) {
        $registry = ffGlobals::getInstance("_registry_");

        if(isset($registry->ignore_defaults_main) && $registry->ignore_defaults_main)
            return null;
        else
            return cm_findCascadeCSS($page, cm_getMainTheme(), $file);
    }


    if ($theme == cm_getMainTheme())
        ffErrorHandler::raise("CM: Unable to find the CSS", E_USER_ERROR, $page, get_defined_vars());
}

function cm_moduleGetCascadeAttrs($file)
{
    $module = false;

    if(dirname("/") == "\\")
        $file = str_replace("\\", "/", $file);

    if(strpos($file, ffCommon_dirname(__DIR__)) === 0)
        $base_path = ffCommon_dirname(__DIR__);
    elseif(strpos($file, FF_DISK_PATH) === 0)
        $base_path = FF_DISK_PATH;

    if (strpos($file, $base_path . FF_THEME_DIR) !== 0)
    {
        $module = true;
        $rc = preg_match("/^" . preg_quote($base_path . CM_MODULES_PATH, "/"). "\/([^\/]+)\/themes\/([^\/]+)\/.*/", $file, $matches);
        if (!$rc)
            ffErrorHandler::raise ("Unable to find proper theme in module file", E_USER_ERROR, null, get_defined_vars());

        $theme = $matches[2];
        $path = str_replace($base_path . CM_MODULES_PATH . "/" . $matches[1] . "/themes/", cm_getModulesExternalPath() . "/" . $matches[1] . "/", $file);
    }
    else
    {
        $rc = preg_match("/^" . preg_quote($base_path, "/"). "\/themes\/([^\/]+)\/.*/", $file, $matches);
        if (!$rc)
            ffErrorHandler::raise ("Unable to find proper theme in module file", E_USER_ERROR, null, get_defined_vars());

        $theme = $matches[1];
        $path = str_replace($base_path . FF_THEME_DIR, FF_THEME_SITE_PATH, $file);
    }

    $out = array(
        "module" => $module
    , "theme" => $theme
    , "path" => $path
    );

    //echo "<pre>"; var_dump($out); exit;

    return $out;
}
