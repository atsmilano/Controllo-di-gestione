<?php
/**
 * @ignore
 * @package ContentManager
 * @subpackage contents
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2010, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->resources[] = "cmSettingsModes";
$oRecord->title = "ShowFiles - Mode";
$oRecord->src_table = CM_TABLE_PREFIX . "showfiles_modes";
$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");

$oRecord->addTab("advanced");
$oRecord->setTabTitle("advanced", ffTemplate::_get_word_by_code("settings_modes_advanced"));

$oRecord->addTab("watermark");
$oRecord->setTabTitle("watermark", ffTemplate::_get_word_by_code("settings_modes_watermark"));

$oRecord->addContent(null, true, "advanced");
$oRecord->addContent(null, true, "watermark");

$oRecord->groups["advanced"]["title"] = "Advanced Settings";
$oRecord->groups["advanced"]["cols"] = 1;
$oRecord->groups["advanced"]["tab"] = "advanced";
$oRecord->groups["watermark"]["title"] = "Watermark";
$oRecord->groups["watermark"]["cols"] = 1;
$oRecord->groups["watermark"]["tab"] = "watermark";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Name";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "format";
$oField->label = "format";
$oField->fixed_post_content = "(png, jpg)";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
		array(new ffData('png'), new ffData('png'))
		, array(new ffData('jpg'), new ffData('jpg'))
);
$oRecord->addContent($oField, "advanced");

if(!CM_SHOWFILES_OPTIMIZE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "format_jpg_quality";
	$oField->label = "Jpg Quality";
	$oField->base_type = "Number";
	$oField->default_value = new ffData("75", "Number");
	$oField->widget = "slider";
	$oField->min_val = "0";
	$oField->max_val = "100";
	$oField->step = "5";
	$oRecord->addContent($oField, "advanced");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "theme";
$oField->label = "Tema";
$oField->default_value = new ffData("default");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "dim_x";
$oField->label = "fixed width";
$oField->default_value = new ffData("200");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "dim_y";
$oField->label = "fixed height";
$oField->default_value = new ffData("200");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "max_x";
$oField->label = "max width";
$oField->default_value = new ffData("200");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "max_y";
$oField->label = "max height";
$oField->default_value = new ffData("200");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "bgcolor";
$oField->label = "Colore di sfondo (HEX)";
$oField->default_value = new ffData("FFFFFF");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "alpha";
$oField->base_type = "Number";
$oField->default_value = new ffData(127, "Number");
$oField->label = "Alpha (0-127)";
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "127";
$oField->step = "1";
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "mode";
$oField->label = "Tipo Resize";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("proportional"), new ffData("Proporzionale"))
								, array(new ffData("stretch"), new ffData("Riempimento"))
								, array(new ffData("crop"), new ffData("Crop"))
							);
$oField->multi_select_one = false;
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "when";
$oField->label = "Quando";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("ever"), new ffData("Sempre"))
								, array(new ffData("smaller"), new ffData("Se più piccolo"))
								, array(new ffData("bigger"), new ffData("Se più grande"))
							);
$oField->multi_select_one = false;
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "alignment";
$oField->label = "Allineamento";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("center"), new ffData("center"))
								, array(new ffData("middle-left"), new ffData("middle-left"))
								, array(new ffData("middle-right"), new ffData("middle-right"))
								, array(new ffData("top-left"), new ffData("top-left"))
								, array(new ffData("top-middle"), new ffData("top-middle"))
								, array(new ffData("top-right"), new ffData("top-right"))
								, array(new ffData("bottom-left"), new ffData("bottom-left"))
								, array(new ffData("bottom-middle"), new ffData("bottom-middle"))
								, array(new ffData("bottom-right"), new ffData("bottom-right"))
							);
$oField->multi_select_one = false;
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_enable";
$oField->label = "Abilita";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0");
$oField->checked_value = new ffData("1");
$oRecord->addContent($oField, "watermark");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_image";
$oField->label = "Immagine";
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->control_type = "file";
$oField->file_temp_path = FF_DISK_PATH . "/uploads/showfiles";
$oField->file_storing_path = FF_DISK_PATH . "/uploads/showfiles/[ID_VALUE]/watermarks";
$oField->file_show_delete = TRUE;
$oField->file_saved_view_url		= FF_SITE_PATH . "/cm/showfiles." . FF_PHP_EXT . "/saved/[ID_VALUE]/[_FILENAME_]";
$oField->file_saved_preview_url		= FF_SITE_PATH . "/cm/showfiles." . FF_PHP_EXT . "/saved/[ID_VALUE]/thumb/[_FILENAME_]";
$oField->file_temp_view_url			= FF_SITE_PATH . "/cm/showfiles." . FF_PHP_EXT . "/temp/[_FILENAME_]";
$oField->file_temp_preview_url		= FF_SITE_PATH . "/cm/showfiles." . FF_PHP_EXT . "/temp/thumb/[_FILENAME_]";
$oField->file_allowed_mime = array(
										"image/png"
										, "image/gif"
										, "image/jpeg"
								);
$oRecord->addContent($oField, "watermark");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_alignment";
$oField->label = "Allineamento";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("center"), new ffData("center"))
								, array(new ffData("middle-left"), new ffData("middle-left"))
								, array(new ffData("middle-right"), new ffData("middle-right"))
								, array(new ffData("top-left"), new ffData("top-left"))
								, array(new ffData("top-middle"), new ffData("top-middle"))
								, array(new ffData("top-right"), new ffData("top-right"))
								, array(new ffData("bottom-left"), new ffData("bottom-left"))
								, array(new ffData("bottom-middle"), new ffData("bottom-middle"))
								, array(new ffData("bottom-right"), new ffData("bottom-right"))
							);
$oField->multi_select_one = false;
$oRecord->addContent($oField, "watermark");

$cm->oPage->addContent($oRecord);

function MainRecord_on_done_action($component, $action) {
	if (strlen($action) && CM_ENABLE_MEM_CACHING)
		ffCache::getInstance(CM_CACHE_ADAPTER)->clear("__showfiles_modes__");
}