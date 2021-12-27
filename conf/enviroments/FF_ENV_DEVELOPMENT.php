<?php
//Percorsi su disco e su Web server
define("FF_DISK_PATH", "C:\wamp\www\budget");
define("FF_SITE_PATH", "/budget");

//Connessione al DB
define("FF_DATABASE_HOST", "localhost");
define("FF_DATABASE_NAME", "budget");
define("FF_DATABASE_USER", "appdbuser");
define("FF_DATABASE_PASSWORD", "pwd");
define("FF_DATABASE_USE_SSL", false);
define("FF_DB_MYSQLI_AVOID_SELECT_DB", true);

//Caching  del framework
define("FF_ENABLE_MEM_TPL_CACHING", false);
define("FF_ENABLE_MEM_PAGE_CACHING", false);

//Sistema utenze
//LDAP (LDAP_SERVER = false se non utilizzato) in ogni caso se USE_MS_ONLINE_LOGIN = true non è necessario valorizzare LDAP_SERVER
define("LDAP_SERVER", false);
define ("LDAP_DC_STRING", "DC=domaincomponent,DC=domaincomponent2,DC=domaincomponent3");
//MS LOGIN
define("MS_LOGIN_REDIRECT_PROTOCOL", "http");        
define('USE_MS_ONLINE_LOGIN', true);
//AZURE
//definizione delle costanti per l'ambiente per l'autenticazione in microsoft login
define("OAUTH2_CLIENT_ID", "");
define("OAUTH2_CLIENT_SECRET", "");
define("OAUTH2_SCOPE", "user.read");
define("AD_TENANT", "");
define("AD_AUTH_URL", "https://login.microsoftonline.com/".AD_TENANT."/oauth2/v2.0/authorize");
define("AD_TOKEN_URL", "https://login.microsoftonline.com/".AD_TENANT."/oauth2/v2.0/token");
define("AD_USERS_URL", "https://graph.microsoft.com/beta/me");
define("AD_USER_NAME_FIELD", "");
define("AD_USER_MATRICOLA_FIELD", "");
define("MS_LOGIN_REDIRECT_URL", MS_LOGIN_REDIRECT_PROTOCOL . '://' . $_SERVER['SERVER_NAME'] . FF_SITE_PATH);
define("MS_LOGIN_LOGOUT_URL", "https://login.microsoftonline.com/".AD_TENANT."/oauth2/logout?post_logout_redirect_uri=".MS_LOGIN_REDIRECT_URL);

// unique application id
define("APPID", "PECNVBLA");

// session name
session_name("PHPSESSPECNVBLA");