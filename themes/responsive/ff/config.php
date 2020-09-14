<?php
if (!defined("FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID"))		define("FF_THEME_RESTRICTED_RANDOMIZE_COMP_ID", false);
if (!defined("FF_THEME_RESTRICTED_LIBS_CACHE"))				define("FF_THEME_RESTRICTED_LIBS_CACHE", (FF_ENV === FF_ENV_PRODUCTION ? true : false));
if (!defined("FF_THEME_RESTRICTED_LIBS_MEMCACHE"))			define("FF_THEME_RESTRICTED_LIBS_MEMCACHE", false);

if (!defined("FF_THEME_FRAMEWORK_CSS"))						define("FF_THEME_FRAMEWORK_CSS", "bootstrap");
if (!defined("FF_THEME_FONT_ICON"))							define("FF_THEME_FONT_ICON", "fontawesome");