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

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ShowfilesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = "ShowFiles - Item";
$oRecord->src_table = CM_TABLE_PREFIX . "showfiles";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Name";
$oField->properties["style"]["width"] = "300px";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "source";
$oField->label = "Source (SQL SELECT or Table Name)";
$oField->extended_type = "Text";
$oField->properties["style"]["width"] = "500px";
$oField->properties["style"]["height"] = "100px";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "field_file";
$oField->label = "Filename Field (db's one)";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path_full";
$oField->label = "File Final Path (string with tags)";
$oField->properties["style"]["width"] = "500px";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path_temp";
$oField->label = "File Temporary Path (string with tags)";
$oField->properties["style"]["width"] = "500px";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "expires";
$oField->label = "Expires (relative, in day)";
$oField->properties["style"]["width"] = "500px";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

// ---------------------------------------------------------------
// ---------------------------------------------------------------
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "DetailWhere";
$oDetail->title = "Conditional Fields";
$oDetail->src_table = CM_TABLE_PREFIX . "showfiles_where";
$oDetail->fields_relationship = array("ID_showfiles" => "ID");
$oDetail->order_default = "ID";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_where";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Field Name";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dbskip";
$oField->label = "Skip DB";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
