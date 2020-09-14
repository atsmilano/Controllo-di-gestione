<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage contents
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

$db = ffDB_Sql::factory();
if ($_REQUEST["keys"]["ID"])
{
	$path = $db->lookup(CM_TABLE_PREFIX . "layout", "ID", new ffData($_REQUEST["keys"]["ID"]), null, "path", null, true);
	$res = cm_getLayoutDepsByPath(ffCommon_dirname($path));
	//ffErrorHandler::raise("debug", E_USER_ERROR, null, get_defined_vars());
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->resources[] = "cmLayout";
$oRecord->title = "Layout";
$oRecord->src_table = CM_TABLE_PREFIX . "layout";
$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");
//$oRecord->addContent(null, true, "layout");
//$oRecord->groups["layout"]["title"] = "Layout";

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("layout_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_general")
                                         , "cols" => 1
                                         , "tab" => "general"
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = "path";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "reset_cascading";
$oField->label = "reset rules";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Ignora tutti i settaggi che potrebbe ereditare ripartendo con i defaults";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "ignore_defaults";
$oField->label = "ignore defaults";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Ignora i defaults presi da ff_settings.xml (si può usare insieme a reset rules)";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "ignore_defaults_main";
$oField->label = "ignore defaults Main";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Ignora i defaults presi da ff_settings.xml solo del Main Theme (si può usare insieme a reset rules)";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_cascading";
$oField->label = "propagate";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Propaga i settaggi per tutti i sotto-percorsi";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_ff_js";
$oField->label = "exclude ff.js";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData("Includi"))
	, array(new ffData("1"), new ffData("Escludi"))
);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Esclude il caricamento di ff.js";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_form";
$oField->label = "exclude form";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData("Includi"))
	, array(new ffData("1"), new ffData("Escludi"))
);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Esclude il form di default";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_gzip";
$oField->label = "enable gzip";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData("No"))
	, array(new ffData("1"), new ffData("Si"))
);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Abilita la compressione GZip nelle pagine";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "compact_js";
$oField->label = "compact js";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData("No"))
	, array(new ffData("1"), new ffData("Comprimi"))
	, array(new ffData("2"), new ffData("Comprimi e Minimizza"))
);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Abilita la compressione di tutti i javascript in uno";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "compact_css";
$oField->label = "compact css";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = "Eredita";
$oField->multi_pairs = array(
	array(new ffData("0"), new ffData("No"))
	, array(new ffData("1"), new ffData("Comprimi"))
	, array(new ffData("2"), new ffData("Comprimi e Minimizza")) 
);
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
	$oField->description = "Abilita la compressione di tutti i css in uno";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = "title";
if (strlen($res["title"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["title"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "main_theme";
$oField->label = "main theme";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("default"), new ffData("Default"))
	, array(new ffData("restricted"), new ffData("Restricted"))
    , array(new ffData("responsive"), new ffData("Responsive"))
);
$oField->required = true;
if (strlen($res["main_theme"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["main_theme"]["value"];
$oRecord->addContent($oField, "general");

$system_themes = array("default", "restricted", "library", "gallery");

$add_theme = glob(FF_DISK_PATH . FF_THEME_DIR . "/*", GLOB_ONLYDIR);
$arrTheme = array();

if(is_array($add_theme) && count($add_theme)) {
    foreach($add_theme AS $real_dir) {
        if(is_dir($real_dir) && array_search(basename($real_dir), $system_themes) === false) {
        	$arrTheme[] = array(new ffData(basename($real_dir)), new ffData(ucfirst(basename($real_dir))));
        }
    }
}


$oField = ffField::factory($cm->oPage);
$oField->id = "theme";
$oField->label = "additional theme";
if(count($arrTheme)) {
	$oField->extended_type = "Selection";
	$oField->multi_pairs = $arrTheme;
}
if (strlen($res["theme"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["theme"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "page";
$oField->label = "page";
//$oField->default_value = new ffData("default");
if (strlen($res["page"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["page"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "layer";
$oField->label = "layer";
//$oField->default_value = new ffData("main");
if (strlen($res["layer"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["layer"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "class_body";
$oField->label = "classe body";
if (strlen($res["class_body"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["class_body"]["value"];
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "domains";
$oField->label = "domini permessi";
$oField->base_type = "Text";
$oField->extended_type = "Text";
if (strlen($res["domains"]["value"]))
	$oField->fixed_post_content = "inherited: " . $res["domains"]["value"];
$oRecord->addContent($oField, "general");


/*$oField = ffField::factory($cm->oPage);
$oField->id = "reset_css";
$oField->label = "Reset CSS";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("");
$oField->checked_value = new ffData("1");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reset_js";
$oField->label = "Reset JS";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("");
$oField->checked_value = new ffData("1");
$oRecord->addContent($oField);
*/
$cm->oPage->addContent($oRecord);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("sections");
$oRecord->setTabTitle("sections", ffTemplate::_get_word_by_code("layout_sections"));

$oRecord->addContent(null, true, "sections"); 
$oRecord->groups["sections"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_sections")
                                         , "cols" => 1
                                         , "tab" => "sections"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailSect";
$oDetail->title = "Sezioni";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_sect";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sect";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome Sezione";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = "Nome Variante";
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/

$oField = ffField::factory($cm->oPage);
$oField->id = "theme_include";
$oField->label = "Abilita solo per i temi";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "sections");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
/*
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailInhSect";
$oDetail->title = "Sezioni Ereditate";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_sect";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sect";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome Sezione";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = "Nome Variante";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "sections");
$cm->oPage->addContent($oDetail);
*/
// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("css");
$oRecord->setTabTitle("css", ffTemplate::_get_word_by_code("layout_css"));

$oRecord->addContent(null, true, "css"); 
$oRecord->groups["css"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_css")
                                         , "cols" => 1
                                         , "tab" => "css"
                                      );
                                      
$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_sort"));
$oDetail->id = "DetailCss";
$oDetail->title = "CSS";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_css";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";
$oDetail->sort_order_field = "order";
/*$oDetail->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oDetail
            , array(
                "resource_id" =>  "publishing_node"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );*/

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_css";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->label = "Nome File";
//$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = "Percorso";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "theme_include";
$oField->label = "Tema";
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_compact";
$oField->label = "Exclude Compact";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "priority";
$oField->base_type = "Number";
$oField->label = "priority";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_DEFAULT");
//$oField->multi_select_one = false;
$oField->multi_pairs = ffGlobals::getInstance("__cm__")->field_layout_priority;
$oField->default_value = new ffData(cm::LAYOUT_PRIORITY_DEFAULT, "Number");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "index";
$oField->base_type = "Number";
$oField->label = "Index";
//$oField->widget = "slider";
$oField->min_val = -100;
$oField->max_val = 100;
$oField->step = 1;
$oField->default_value = new ffData(0, "Number");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "css");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("js");
$oRecord->setTabTitle("js", ffTemplate::_get_word_by_code("layout_js"));

$oRecord->addContent(null, true, "js"); 
$oRecord->groups["js"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_js")
                                         , "cols" => 1
                                         , "tab" => "js"
                                      );
                                      
$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_sort"));
$oDetail->id = "DetailJs";
$oDetail->title = "Javascript";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_js";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";
$oDetail->sort_order_field = "order";
/*$oDetail->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oDetail
            , array(
                "resource_id" =>  "publishing_node"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );*/

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_js";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$js_plugin = array();
$js_plugin_path = glob(FF_DISK_PATH . "/themes/library/plugins/*");
if(is_array($js_plugin_path) && count($js_plugin_path)) {
    foreach ($js_plugin_path AS $real_file) {
        if(is_dir($real_file)) {
            $real_file = str_replace(FF_DISK_PATH, "", $real_file);

            $js_plugin[] = array(new ffData($real_file . "/" . basename($real_file) . ".js"), new ffData(basename($real_file)));
        }
    }
}
         
$oField = ffField::factory($cm->oPage);
$oField->id = "plugin_path";
$oField->label = "plugin";
$oField->label = "plugin";
$oField->extended_type = "Selection";
$oField->multi_pairs = $js_plugin;
$oField->multi_select_noone = true;
$oField->multi_select_noone_val = new ffData("");
$oField->multi_select_one = false;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "js_path";
$oField->label = "js path";
$oDetail->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->label = "Nome File";
//$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = "Percorso";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "theme_include";
$oField->label = "Tema";
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_compact";
$oField->label = "Exclude Compact";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "priority";
$oField->base_type = "Number";
$oField->label = "priority";
$oField->extended_type = "Selection";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("cm::LAYOUT_PRIORITY_DEFAULT");
$oField->multi_pairs = ffGlobals::getInstance("__cm__")->field_layout_priority;
//$oField->default_value = new ffData(cm::LAYOUT_PRIORITY_NORMAL, "Number");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "index";
$oField->base_type = "Number";
$oField->label = "Index";
//$oField->widget = "slider";
$oField->min_val = -100;
$oField->max_val = 100;
$oField->step = 1;
$oField->default_value = new ffData(0, "Number");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "js");
$cm->oPage->addContent($oDetail);


// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("meta");
$oRecord->setTabTitle("meta", ffTemplate::_get_word_by_code("layout_meta"));

$oRecord->addContent(null, true, "meta"); 
$oRecord->groups["meta"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_meta")
                                         , "cols" => 1
                                         , "tab" => "meta"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailMeta";
$oDetail->title = "Meta";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_meta";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_meta";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = "Tipo";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("name"), new ffData("name"))
	, array(new ffData("property"), new ffData("property"))
	, array(new ffData("http-equiv"), new ffData("http-equiv"))
);
$oField->multi_select_one = false;
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "content";
$oField->label = "Contenuto";
$oField->required = true;
$oDetail->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = "visible";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = "propagate";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "meta");
$cm->oPage->addContent($oDetail);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oRecord->addTab("cdn");
$oRecord->setTabTitle("cdn", ffTemplate::_get_word_by_code("layout_cdn"));

$oRecord->addContent(null, true, "cdn"); 
$oRecord->groups["cdn"] = array(
                                         "title" => ffTemplate::_get_word_by_code("layout_cdn")
                                         , "cols" => 1
                                         , "tab" => "cdn"
                                      );

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailCDN";
$oDetail->title = "CDN";
$oDetail->src_table = CM_TABLE_PREFIX . "layout_cdn";
$oDetail->fields_relationship = array("ID_layout" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdn";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Nome";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = "Tipo";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
    array(new ffData("css"), new ffData("Css"))
    , array(new ffData("js"), new ffData("Javascript"))
);
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "url";
$oField->label = "Url";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Stato";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "cdn");
$cm->oPage->addContent($oDetail);

function cm_getLayoutDepsByPath($layout_path)
{ 
	$db = ffDB_Sql::factory();

	$layout_vars = array();
	$layout_vars["main_theme"] = null;
	$layout_vars["theme"] = null;
	$layout_vars["page"] = null;
	$layout_vars["layer"] = null;
	$layout_vars["title"] = null;
	$layout_vars["class_body"] = null;
	$layout_vars["domains"] = null;
	$layout_vars["sect"] = array();
	$layout_vars["css"] = array();
	$layout_vars["js"] = array();
	$layout_vars["meta"] = array();
	$layout_vars["exclude_ff_js"] = false;

	$tmp = $layout_path;
	$paths = "";
	do
	{
		if (strlen($paths))
			$paths .= " OR ";
		$paths .= "path = '" . $db->toSql(new ffData($tmp), NULL, false) . "'";
	} while($tmp != "/" && $tmp = ffCommon_dirname($tmp));

	$sSQL = "SELECT
					*
				FROM
					" . CM_TABLE_PREFIX . "layout
				WHERE
					" . $paths . "
				ORDER BY
					path ASC
			";

	$db->query($sSQL);
	if ($db->nextRecord())
	{
		$db2 = ffDB_Sql::factory();
		do
		{
			$ID = $db->getField("ID")->getValue();
			$bMatchPath = $db->getField("path")->getValue() == $layout_path;

			if(!$db->getField("enable_cascading")->getValue() && !$bMatchPath)
				continue;

			if ($db->getField("reset_cascading")->getValue())
			{
				$layout_vars = array();
				$layout_vars["main_theme"] = null;
				$layout_vars["theme"] = null;
				$layout_vars["page"] = null;
				$layout_vars["layer"] = null;
				$layout_vars["title"] = null;
				$layout_vars["class_body"] = null;
				$layout_vars["domains"] = null;
				$layout_vars["sect"] = array();
				$layout_vars["css"] = array();
				$layout_vars["js"] = array();
				$layout_vars["meta"] = array();
				$layout_vars["exclude_ff_js"] = true;
			}

			if (strlen($db->getField("main_theme")->getValue()))
			{
				$layout_vars["main_theme"]["id"] = $ID;
				$layout_vars["main_theme"]["value"] = $db->getField("main_theme")->getValue();
			}

			$layout_vars["exclude_ff_js"] = $db->getField("exclude_ff_js")->getValue();
			
			if (strlen($db->getField("theme")->getValue()))
			{
				$layout_vars["theme"]["id"] = $ID;
				$layout_vars["theme"]["value"] = $db->getField("theme")->getValue();
			}

			if (strlen($db->getField("page")->getValue()))
			{
				$layout_vars["page"]["id"] = $ID;
				$layout_vars["page"]["value"] = $db->getField("page")->getValue();
			}

			if (strlen($db->getField("layer")->getValue()))
			{
				$layout_vars["layer"]["id"] = $ID;
				$layout_vars["layer"]["value"] = $db->getField("layer")->getValue();
			}

			if (strlen($db->getField("title")->getValue()))
			{
				$layout_vars["title"]["id"] = $ID;
				$layout_vars["title"]["value"] = $db->getField("title")->getValue();
			}

			if (strlen($db->getField("class_body")->getValue()))
			{
				$layout_vars["class_body"]["id"] = $ID;
				$layout_vars["class_body"]["value"] = $db->getField("class_body")->getValue();
			}
			if (strlen($db->getField("domains")->getValue()))
			{
				$layout_vars["domains"]["id"] = $ID;
				$layout_vars["domains"]["value"] = $db->getField("domains")->getValue();
			}
			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_sect WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					$layout_vars["sect"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["sect"][$db2->getField("name")->getValue()]["value"] = $db2->getField("value")->getValue();
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_css WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					if(!strlen($db2->getField("priority")->getValue()))
						$priority = "top";
					else
						$priority = $db2->getField("priority")->getValue();

					$layout_vars["css"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["css"][$db2->getField("name")->getValue()]["value"]["path"] = ($db2->getField("path")->getValue() ? $db2->getField("path")->getValue() : null);
					$layout_vars["css"][$db2->getField("name")->getValue()]["value"]["file"] = $db2->getField("file")->getValue();
					$layout_vars["css"][$db2->getField("name")->getValue()]["exclude_compact"] =  $db2->getField("exclude_compact")->getValue();
					$layout_vars["css"][$db2->getField("name")->getValue()]["priority"] = $priority;
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_js WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					if(!strlen($db2->getField("priority")->getValue()))
						$priority = "top";
					else
						$priority = $db2->getField("priority")->getValue();

					if(strlen($db2->getField("plugin_path")->getValue()))
					{
						if(file_exists(FF_DISK_PATH . $db2->getField("plugin_path")->getValue())) {
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["id"] = $ID;
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["value"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["value"]["file"] = basename($db2->getField("plugin_path")->getValue());
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
							$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue()))]["priority"] = $priority;
						}
						if(strlen($db2->getField("js_path")->getValue()))
						{
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["path"] = "/themes/" . $layout_vars["theme"] . "/javascript" . ffCommon_dirname($db2->getField("js_path")->getValue());
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["file"] = basename($db2->getField("js_path")->getValue());
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
							$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["priority"] = $priority;
						}
						else
						{
							if(file_exists(FF_DISK_PATH . ffCommon_dirname($db2->getField("plugin_path")->getValue()) . "/observe.js"))
							{
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["id"] = $ID;
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["value"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["value"]["file"] = "observe.js";
                                $layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
                                $layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["priority"] = $priority;
							}
                            elseif(file_exists(FF_DISK_PATH . ffCommon_dirname($db2->getField("plugin_path")->getValue()) . "/" . basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe.js"))
							{
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["id"] = $ID;
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["path"] = ffCommon_dirname($db2->getField("plugin_path")->getValue());
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["file"] = basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe.js";
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
								$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["priority"] = $priority;
							}
						}
					}
					elseif (strlen($db2->getField("js_path")->getValue()))
					{
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["id"] = $ID;
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["path"] = "/themes/" . $layout_vars["theme"] . "/javascript" . ffCommon_dirname($db2->getField("js_path")->getValue());
						$layout_vars["js"][basename($db2->getField("js_path")->getValue())]["value"]["file"] = basename($db2->getField("js_path")->getValue());
						$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
						$layout_vars["js"][basename(ffCommon_dirname($db2->getField("plugin_path")->getValue())) . ".observe"]["priority"] = $priority;
					}
					elseif (strlen($db2->getField("file")->getValue()))
					{
						$layout_vars["js"][$db2->getField("name")->getValue()]["id"] = $ID;
						$layout_vars["js"][$db2->getField("name")->getValue()]["value"]["path"] = ($db2->getField("path")->getValue() ? $db2->getField("path")->getValue() : null);
						$layout_vars["js"][$db2->getField("name")->getValue()]["value"]["file"] = $db2->getField("file")->getValue();
						$layout_vars["js"][$db2->getField("name")->getValue()]["exclude_compact"] = $db2->getField("exclude_compact")->getValue();
						$layout_vars["js"][$db2->getField("name")->getValue()]["priority"] = $priority;
					}
				} while ($db2->nextRecord());
			}

			$sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout_meta WHERE ID_layout = " . $db2->toSql($db->getField("ID")) . " ORDER BY ID";
			$db2->query($sSQL);
			if ($db2->nextRecord())
			{
				do
				{
					if(!$db2->getField("cascading")->getValue() && !$bMatchPath)
						continue;

					$layout_vars["meta"][$db2->getField("name")->getValue()]["id"] = $ID;
					$layout_vars["meta"][$db2->getField("name")->getValue()]["value"] = $db2->getField("content")->getValue();
				} while ($db2->nextRecord());
			}
		} while($db->nextRecord());
	}

	return $layout_vars;
}

function MainRecord_on_done_action(ffRecord_base $oRecord, $frmAction)
{
	if (CM_ENABLE_MEM_CACHING)
		ffCache::getInstance(CM_CACHE_ADAPTER)->clear("__cm_layout__");
}