<?php
/**
 * VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @package VGallery
 *  @subpackage core
 *  @author Alessandro Stucchi <wolfgan@gmail.com>
 *  @copyright Copyright (c) 2004, Alessandro Stucchi
 *  @license http://opensource.org/licenses/gpl-3.0.html
 *  @link https://github.com/wolfgan43/vgallery
 */

$layout = ($_REQUEST["layout"]
    ? $_REQUEST["layout"]
    : "restricted"
);

$cm->oPage->layer = $layout;

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LayoutModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->skip_action = true;
$oRecord->src_table = "cm_mod_security_users";
$oRecord->hide_all_controls = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layouts";
$oField->extended_type = "Selection";
foreach(glob(__TOP_DIR__ . "/modules/restricted/themes/responsive/layouts/layer_*") as $real_file) {
    $filename = str_replace("layer_", "", basename($real_file, ".html"));

    $oField->multi_pairs[] = array(new ffData( $filename), new ffData(ucfirst($filename)));
}
$oField->multi_select_one = false;
$oField->default_value = new ffData($layout);
$oRecord->addContent($oField);

$js = '<script type="text/javascript">
jQuery("#LayoutModify_layouts").change(function() {
    window.location.href="?layout=" + jQuery(this).find(":selected").attr("value");
});
</script>';

$cm->oPage->addContent($oRecord);
$cm->oPage->addContent($js);
//$cm->oPage->layer_dir = __DIR__;
  //$cm->oPage->layer = "fullbar";
