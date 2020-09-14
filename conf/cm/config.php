<?php
define("CM_LOCAL_APP_NAME", "Programmazione e controllo");
define("CM_DEFAULT_THEME", "responsive");
switch (FF_ENV){   
    default:
		define("CM_SHOWFILES_ENABLE_DEBUG", false);
}
