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

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "MainGrid";
$oGrid->title = "Layout";
$oGrid->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "layout [WHERE] [ORDER]";
$oGrid->order_default = "path";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "MainRecord";
$oGrid->resources[] = "cmLayout";
$oGrid->use_search = false;
//$oGrid->use_paging = false;
if (cm_getMainTheme() == "restricted" || $cm->oPage->getTheme() == "restricted")
{
	$oGrid->full_ajax = true;
	$oGrid->dialog_options["add"]["width"] = "1000";
	$oGrid->dialog_options["edit"]["width"] = "1000";
}
else
	$oGrid->full_ajax = false;

// Campo chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzazione
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = "path";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "domains";
$oField->label = "Domini";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "main_theme";
$oField->label = "main theme";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "theme";
$oField->label = "theme";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "page";
$oField->label = "page";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "layer";
$oField->label = "layer";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = "title";
$oGrid->addContent($oField, false);

$cm->oPage->addContent($oGrid);
