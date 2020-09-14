<?php
/**
 * @package ContentManager
 * @subpackage common
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

define("CM_ROOT", __DIR__);

define("CM_CACHE_PATH", "/cache");
if(!defined("CM_CACHE_DISK_PATH")) define("CM_CACHE_DISK_PATH", FF_DISK_PATH . CM_CACHE_PATH);

define("CM_MODULES_PATH", "/modules");
define("CM_MODULES_ROOT", __TOP_DIR__ . CM_MODULES_PATH);

define("CM_CONTENT_ROOT",  __PRJ_DIR__ . "/contents");

/**
 * E possibile definire la costante nei seguenti modi:
 *  - /cm/showfiles.php
 *  - /media
 *  NB: impostando a /media è necessario aggiungere le seguenti regole nell'.htaccess
 *
 * #############
 * # Error
 * #------------
 * errorDocument 404 [FF_SITE_PATH]/cm/error\.php
 * errorDocument 403 [FF_SITE_PATH]/cm/error\.php
 *
 *
 * #############
 * # Asset
 * #------------
 *
 * #showfiles
 * RewriteCond   %{HTTP_HOST}  	[DOMAIN_NAME].[DOMAIN_EXT]$
 * RewriteCond   %{REQUEST_URI}	^[FF_SITE_PATH]/domains/[PROJECT_NAME]/cm/showfiles\.php(.*)
 * RewriteRule  ^(.*)  [FF_SITE_PATH]/cm/showfiles\.php?_ffq_=%1 [L,QSA]
 *
 * #css | js
 * RewriteCond   %{HTTP_HOST}  	[DOMAIN_NAME].[DOMAIN_EXT]$
 * RewriteCond   %{REQUEST_URI}  	^[FF_SITE_PATH]/domains/[PROJECT_NAME]/asset
 * RewriteRule   ^asset/(.*)    [FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/$1 [L]
 *
 * #media
 * RewriteCond   %{HTTP_HOST}  	[DOMAIN_NAME].[DOMAIN_EXT]$
 * RewriteCond   %{REQUEST_URI}  	^[FF_SITE_PATH]/domains/[PROJECT_NAME]/media
 * RewriteRule   ^media/(.*)    [FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/.thumbs/$1 [L]
 *
 * #static
 * RewriteCond   %{HTTP_HOST}  	[DOMAIN_NAME].[DOMAIN_EXT]$
 * RewriteCond   %{REQUEST_URI}	^[FF_SITE_PATH]/domains/[PROJECT_NAME]/static(.*)
 * RewriteRule   ^(.*)    [FF_SITE_PATH]/cm/static\.php?_ffq_=%1 [L,QSA]
 *
 * #############
 * # Using Sub Domains for Render Images
 * #------------
 *
 * #media
 * RewriteCond %{HTTP_HOST}           ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 * RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cache/.thumbs
 * RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cm/error\.php
 * RewriteRule ^(.*)                  [FF_SITE_PATH]/cache/.thumbs/$0 [L,QSA]
 *
 * #media:404 goto static
 * RewriteCond %{HTTP_HOST}           ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 * RewriteCond %{REQUEST_FILENAME}    ^!-f
 * RewriteRule ^cache/.thumbs/(.*)     [DOMAIN_PROTOCOL]://static.[DOMAIN_NAME].[DOMAIN_EXT]/$1 [L,R=302,E=nocache:1]
 *
 * #static
 * RewriteCond %{HTTP_HOST}           ^static\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 * RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cm/static\.php
 * RewriteRule ^(.*)                  [FF_SITE_PATH]/cm/static\.php/$0 [L,QSA]
 */
if(!defined("CM_SHOWFILES")) define("CM_SHOWFILES", FF_SITE_PATH . "/cm/showfiles." . FF_PHP_EXT);