<?php

/** This file is part of KCFinder project
 *
 *      @desc Base configuration file
 *   @package KCFinder
 *   @version 2.2
 *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
 * @copyright 2010 KCFinder Project
 *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
 *      @link http://kcfinder.sunhater.com
 */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

$domain                     = $_SERVER["HTTP_HOST"];
$disk_path                  = str_replace("/themes/library/kcfinder", "", __DIR__);
$site_path                  = str_replace($_SERVER["DOCUMENT_ROOT"], "", $disk_path);

$cache_path                 = "/cache";
$thumb_path                 = "/_thumb";
$updir_path                 = "/uploads";

$site_updir                 = $site_path . $updir_path;
$disk_updir                 = $disk_path . $updir_path;

if(is_dir($disk_path . "/library/gallery/models/auth")) {
    require_once($disk_path . "/library/gallery/models/vgCommon.php");
    require_once($disk_path . "/library/gallery/models/auth/Auth.php");

    Auth::getInstance("session")->check();
}


$_CONFIG = array(

    'disabled'              => false,
    'readonly'              => false,
    'denyZipDownload'       => true,

    'theme'                 => "oxygen",

    'uploadURL'             => ($site_path ? $site_path : "/" ),
    'uploadDir'             => $disk_path,

    'dirPerms'              => 0777,
    'filePerms'             => 0777,

    'deniedExts'            => "exe com msi bat php cgi pl",

    'types'                 => array("uploads" => ""),

    'mime_magic'            => "",

    'maxImageWidth'         => 0,
    'maxImageHeight'        => 0,

    'thumbWidth'            => 100,
    'thumbHeight'           => 100,

    'thumbsDir'             => $cache_path . $thumb_path,
    'hideDir'               => array($thumb_path),

    'jpegQuality'           => 90,

    'cookieDomain'          => $domain,
    'cookiePath'            => "/",
    'cookiePrefix'          => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION

    '_check4htaccess'       => true,
    //'_tinyMCEPath'        => "/tiny_mce",

    '_sessionVar'           => &$_SESSION['KCFINDER'],
    //'_sessionLifetime'    => 30,
    //'_sessionDir'         => "/full/directory/path",

    //'_sessionDomain'      => ".mysite.com",
    //'_sessionPath'        => "/my/path",
);
