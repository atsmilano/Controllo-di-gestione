<?php
if (!defined("MOD_SEC_MULTIDOMAIN")) define ("MOD_SEC_MULTIDOMAIN", false);
if (!defined("MOD_SEC_MULTIDOMAIN_EXTERNAL_DB")) define ("MOD_SEC_MULTIDOMAIN_EXTERNAL_DB", false);
if (!defined("MOD_SEC_GROUPS")) define ("MOD_SEC_GROUPS", false);
if (!defined("MOD_SEC_LOGO")) define ("MOD_SEC_LOGO", "Pre");   // Pre o Post
if (!defined("MOD_SEC_LOGO_PATH")) define ("MOD_SEC_LOGO_PATH", null); //relative path

if (!defined("MOD_SEC_BRAND_ACL")) define ("MOD_SEC_BRAND_ACL", "3"); //relative path

if (!defined("MOD_SEC_GUEST_USER_ID")) 		define ("MOD_SEC_GUEST_USER_ID", null);
if (!defined("MOD_SEC_GUEST_USER_NAME")) 	define ("MOD_SEC_GUEST_USER_NAME", "guest");
if (!defined("MOD_SEC_GUEST_GROUP_ID")) 	define ("MOD_SEC_GUEST_GROUP_ID", null);
if (!defined("MOD_SEC_GUEST_GROUP_NAME")) 	define ("MOD_SEC_GUEST_GROUP_NAME", "guests");

if (!defined("MOD_SEC_ENABLE_GEOLOCALIZATION")) 	define ("MOD_SEC_ENABLE_GEOLOCALIZATION", false);

if (!defined("MOD_SEC_LOGIN_TITLE"))     define ("MOD_SEC_LOGIN_TITLE", false);
if (!defined("MOD_SEC_LOGOUT_TITLE"))     define ("MOD_SEC_LOGOUT_TITLE", false);
if (!defined("MOD_SEC_LOGIN_LABEL"))     define ("MOD_SEC_LOGIN_LABEL", true);

if (!defined("MOD_SEC_PASS_FUNC")) define ("MOD_SEC_PASS_FUNC", "PASSWORD");
if (!defined("MOD_SEC_LOGIN_DOMAIN")) define ("MOD_SEC_LOGIN_DOMAIN", true);
if (!defined("MOD_SEC_LOGIN_BACK_URL")) define ("MOD_SEC_LOGIN_BACK_URL", true);
if (!defined("MOD_SEC_LOGIN_REGISTER")) define ("MOD_SEC_LOGIN_REGISTER", false);

if (!defined("MOD_SEC_FORCE_XHR")) define ("MOD_SEC_FORCE_XHR", false);

if (!defined("MOD_SEC_LOGIN_FORCE_LAYER")) define ("MOD_SEC_LOGIN_FORCE_LAYER", "empty");

if (!defined("MOD_SECURITY_REGISTER_PRIVACY"))	define("MOD_SECURITY_REGISTER_PRIVACY", false);
if (!defined("MOD_SECURITY_REGISTER_PRIVACY_TEXT"))	define("MOD_SECURITY_REGISTER_PRIVACY_TEXT", true);
if (!defined("MOD_SECURITY_REGISTER_PRIVACY_CHECKBOX"))	define("MOD_SECURITY_REGISTER_PRIVACY_CHECKBOX", false);
if (!defined("MOD_SECURITY_REGISTER_CONDITION"))	define("MOD_SECURITY_REGISTER_CONDITION", false);
if (!defined("MOD_SECURITY_REGISTER_CONDITION_CHECKBOX"))	define("MOD_SECURITY_REGISTER_CONDITION_CHECKBOX", false);
if (!defined("SECURITY_REGISTER_EMAIL_ENABLE_SMTP"))	define("SECURITY_REGISTER_EMAIL_ENABLE_SMTP", true);
if (!defined("SECURITY_REGISTER_EMAIL_SMTP_SECURITY"))	define("SECURITY_REGISTER_EMAIL_SMTP_SECURITY", "");
if (!defined("SECURITY_REGISTER_EMAIL_HOST")) define("SECURITY_REGISTER_EMAIL_HOST", "");
if (!defined("SECURITY_REGISTER_EMAIL_USER")) define("SECURITY_REGISTER_EMAIL_USER", "");
if (!defined("SECURITY_REGISTER_EMAIL_PASS")) define("SECURITY_REGISTER_EMAIL_PASS", "");
if (!defined("SECURITY_REGISTER_EMAIL_FROM")) define("SECURITY_REGISTER_EMAIL_FROM", "");
if (!defined("SECURITY_REGISTER_EMAIL_FROM_NAME")) define("SECURITY_REGISTER_EMAIL_FROM_NAME", "Modulo Security");
if (!defined("SECURITY_REGISTER_EMAIL_SUBJECT")) define("SECURITY_REGISTER_EMAIL_SUBJECT", "Registrazione utente");
if (!defined("SECURITY_REGISTER_EMAIL_OUTDATED")) define("SECURITY_REGISTER_EMAIL_OUTDATED", "Account Scaduto");
if (!defined("SECURITY_REGISTER_BCC")) define("SECURITY_REGISTER_BCC", "notifiche@xodusweb.com");
if (!defined("SECURITY_REGISTER_MAIL_TPL")) define("SECURITY_REGISTER_MAIL_TPL", null); // full path. if set, omit extension. modsec will search for .txt and .html

if (!defined("MOD_SECURITY_REGISTER_CREATE_SESSION")) define("MOD_SECURITY_REGISTER_CREATE_SESSION", true);

if (!defined("MOD_SEC_ACL_LEVEL"))		define ("MOD_SEC_ACL_LEVEL", "1,2,3");
if (!defined("MOD_SEC_ACL_STATUS"))		define ("MOD_SEC_ACL_STATUS", "1,2,3");
if (!defined("MOD_SEC_ACL_EXPIRATION")) define ("MOD_SEC_ACL_EXPIRATION", "1,2,3");

if (!defined("MOD_SEC_MAXUSERS")) define ("MOD_SEC_MAXUSERS", false); //  limita il numero di utenti
if (!defined("MOD_SECURITY_USERS_SHOW_LEVELS_ALL")) define("MOD_SECURITY_USERS_SHOW_LEVELS_ALL", false);
if (!defined("MOD_SECURITY_USERS_SHOW_LEVELS_ACL")) define("MOD_SECURITY_USERS_SHOW_LEVELS_ACL", null);
if (!defined("MOD_SECURITY_USERS_SHOW_SAME_LEVEL")) define("MOD_SECURITY_USERS_SHOW_SAME_LEVEL", false); // mostra solamente gli utenti del medesimo livello
if (!defined("MOD_SECURITY_USERS_MODIFY_SAME_LEVEL")) define("MOD_SECURITY_USERS_MODIFY_SAME_LEVEL", true); // come sopra, ma per la modifica
if (!defined("MOD_SECURITY_USERS_DELETE_SELF")) define("MOD_SECURITY_USERS_DELETE_SELF", false);

if (!defined("MOD_SEC_PACKAGES")) define ("MOD_SEC_PACKAGES", false);
if (!defined("MOD_SEC_OWNER")) define ("MOD_SEC_OWNER", false);

if (!defined("MOD_SECURITY_REGISTER_MINIMAL")) define ("MOD_SECURITY_REGISTER_MINIMAL", false);

if (!defined("MOD_SECURITY_REGISTER_SHOWUSERID"))		define ("MOD_SECURITY_REGISTER_SHOWUSERID", "both"); // can be "both", "username" or "email"
if (!defined("MOD_SECURITY_REGISTER_CONFIRM_EMAIL"))	define ("MOD_SECURITY_REGISTER_CONFIRM_EMAIL", true);
if (!defined("MOD_SECURITY_REGISTER_AUTOGEN_PASSWD"))	define ("MOD_SECURITY_REGISTER_AUTOGEN_PASSWD", false);
if (!defined("MOD_SEC_RANDOMPASS_LENGTH"))				define ("MOD_SEC_RANDOMPASS_LENGTH", 8); // characters
if (!defined("MOD_SEC_RANDOMPASS_STRENGTH"))			define ("MOD_SEC_RANDOMPASS_STRENGTH", 7); // bitflag: 1=upper cons,2=upper vowels,4=number,8=symbols
if (!defined("MOD_SECURITY_REGISTER_MORESTEP"))			define ("MOD_SECURITY_REGISTER_MORESTEP", null); // null od il numero di step rimanenti
if (!defined("MOD_SECURITY_LOGON_USERID"))				define ("MOD_SECURITY_LOGON_USERID", "both"); // can be "both", "username" or "email"

if (!defined("MOD_SEC_USERNAME_RECOVER_USERNAME"))    define ("MOD_SEC_USERNAME_RECOVER_USERNAME", false);   //se definito non funziona piu 

if (!defined("MOD_SEC_PASSWORD_RECOVER"))				define ("MOD_SEC_PASSWORD_RECOVER", true);   //se definito non funziona piu
if (!defined("MOD_SEC_PASSWORD_RECOVER_INTERVAL"))		define ("MOD_SEC_PASSWORD_RECOVER_INTERVAL", "3600"); // secondi entro i quali non è possibile generare una nuova password
if (!defined("MOD_SEC_PASSWORD_RECOVER_SUCCESS"))		define ("MOD_SEC_PASSWORD_RECOVER_SUCCESS", false); // se true, visualizza un messaggio di completamento dell'operazione, se false esegue un redirect al login

if (!defined("MOD_SEC_USER_AVATAR"))		define ("MOD_SEC_USER_AVATAR",		"avatar");
if (!defined("MOD_SEC_USER_AVATAR_MODE"))	define ("MOD_SEC_USER_AVATAR_MODE",	"");
if (!defined("MOD_SEC_USER_FIRSTNAME"))		define ("MOD_SEC_USER_FIRSTNAME",	"firstname");
if (!defined("MOD_SEC_USER_LASTNAME"))		define ("MOD_SEC_USER_LASTNAME",	"lastname");
if (!defined("MOD_SEC_USER_COMPANY"))		define ("MOD_SEC_USER_COMPANY",		"company");
if (!defined("MOD_SEC_USER_ROLE"))		define ("MOD_SEC_USER_ROLE",		"role");
if (!defined("MOD_SEC_USER_TEL"))		define ("MOD_SEC_USER_TEL",		"tel");
if (!defined("MOD_SEC_USER_CELL"))		define ("MOD_SEC_USER_CELL",		"cell");

if (!defined("MOD_SEC_DOMAIN_COMPANY"))		define ("MOD_SEC_DOMAIN_COMPANY",	"company_name");


if (!defined("MOD_SEC_PROFILE_USERNAME_READONLY"))        define ("MOD_SEC_PROFILE_USERNAME_READONLY",    false);

if (!defined("MOD_SEC_PROFILING"))					define ("MOD_SEC_PROFILING",					false);
if (!defined("MOD_SEC_PROFILING_EXTENDED"))			define ("MOD_SEC_PROFILING_EXTENDED",			false);
if (!defined("MOD_SEC_PROFILING_ADDITIONAL_PRIVS"))	define ("MOD_SEC_PROFILING_ADDITIONAL_PRIVS",	false); // depends on MOD_SEC_PROFILING_EXTENDED
if (!defined("MOD_SEC_PROFILING_INDENTSTRING"))		define ("MOD_SEC_PROFILING_INDENTSTRING",		"-");
if (!defined("MOD_SEC_PROFILING_SKIPSYSTEM"))		define ("MOD_SEC_PROFILING_SKIPSYSTEM",			"/admin,/accounts"); // menu elements to skip. With '*' the whole menu will be skipped
if (!defined("MOD_SEC_PROFILING_MAINDB"))			define ("MOD_SEC_PROFILING_MAINDB",				false); // depends on MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB
if (!defined("MOD_SEC_ACL_PROFILE"))				define ("MOD_SEC_ACL_PROFILE",					"3");
if (!defined("MOD_SEC_PROFILING_MULTI"))			define ("MOD_SEC_PROFILING_MULTI",				false);
if (!defined("MOD_SEC_SHOW_PROFILE_LEVEL"))			define ("MOD_SEC_SHOW_PROFILE_LEVEL",			3);
if (!defined("MOD_SEC_PROFILING_DEFAULT"))			define ("MOD_SEC_PROFILING_DEFAULT",		true); // allowed or not when lacking profile

if (!defined("MOD_SEC_LOGIN_STANDARD"))		define ("MOD_SEC_LOGIN_STANDARD",		true);
if (!defined("MOD_SEC_SOCIAL_POS"))		define ("MOD_SEC_SOCIAL_POS",		"pre");

if (!defined("MOD_SEC_SOCIAL_GOOGLE"))	define ("MOD_SEC_SOCIAL_GOOGLE",	false);
if (MOD_SEC_SOCIAL_GOOGLE)
{
	if (!defined("MOD_SEC_SOCIAL_GOOGLE_APP_NAME"))			die ("MOD_SEC_SOCIAL_GOOGLE_APP_NAME is required");
	if (!defined("MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID"))		die ("MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID is required");
	if (!defined("MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET"))	die ("MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET is required");
	if (!defined("MOD_SEC_SOCIAL_GOOGLE_CLIENT_REDIR_URI"))	die ("MOD_SEC_SOCIAL_GOOGLE_CLIENT_REDIR_URI is required"); //example: DOMAIN_PROTOCOL . "://" . DOMAIN_NAME . /restricted/login/social/google/response
	if (!defined("MOD_SEC_SOCIAL_GOOGLE_APPSCOPE"))    define ("MOD_SEC_SOCIAL_GOOGLE_APPSCOPE", "USERINFO_PROFILE,USERINFO_EMAIL");
}

if (!defined("MOD_SEC_SOCIAL_FACEBOOK"))	define ("MOD_SEC_SOCIAL_FACEBOOK",	false);
if (MOD_SEC_SOCIAL_FACEBOOK)
{
	if (!defined("MOD_SEC_SOCIAL_FACEBOOK_APPID"))			die ("MOD_SEC_SOCIAL_FACEBOOK_APPID is required");
	if (!defined("MOD_SEC_SOCIAL_FACEBOOK_SECRET"))			die ("MOD_SEC_SOCIAL_FACEBOOK_SECRET is required");
	if (!defined("MOD_SEC_SOCIAL_FACEBOOK_CLIENT_REDIR_URI"))	die ("MOD_SEC_SOCIAL_FACEBOOK_CLIENT_REDIR_URI is required"); //example: DOMAIN_PROTOCOL . "://" . DOMAIN_NAME . /restricted/login/social/google/response
	if (!defined("MOD_SEC_SOCIAL_FACEBOOK_APPSCOPE"))    define ("MOD_SEC_SOCIAL_FACEBOOK_APPSCOPE", "public_profile,email");
}

if (!defined("MOD_SEC_SOCIAL_JANRAIN"))    define ("MOD_SEC_SOCIAL_JANRAIN",    false);
if (MOD_SEC_SOCIAL_JANRAIN)
{
    if (!defined("MOD_SEC_SOCIAL_JANRAIN_APPID"))            die ("MOD_SEC_SOCIAL_JANRAIN_APPID is required");
    if (!defined("MOD_SEC_SOCIAL_JANRAIN_APPNAME"))            die ("MOD_SEC_SOCIAL_JANRAIN_APPNAME is required");
}

if (!defined("MOD_SEC_SOCIAL_FF"))    define ("MOD_SEC_SOCIAL_FF",    false);
if (MOD_SEC_SOCIAL_FF)
{
    if (!defined("MOD_SEC_SOCIAL_FF_CLIENT_ID"))            	die ("MOD_SEC_SOCIAL_FF_CLIENT_ID is required");
    if (!defined("MOD_SEC_SOCIAL_FF_CLIENT_SECRET"))        	die ("MOD_SEC_SOCIAL_FF_CLIENT_SECRET is required");
    if (!defined("MOD_SEC_SOCIAL_FF_OAUTH2_URL"))           	die ("MOD_SEC_SOCIAL_FF_OAUTH2_URL is required");
}

if (!defined("MOD_SEC_OAUTH2_SERVER")) define ("MOD_SEC_OAUTH2_SERVER", false);

if (!defined("MOD_SEC_EXCLUDE_SQL")) define("MOD_SEC_EXCLUDE_SQL", null);

if (!defined("MOD_SEC_DEFAULT_FIELDS")) define("MOD_SEC_DEFAULT_FIELDS" , "ID,ID_domains,username,username_slug,password,level,status,expiration,email,time_zone,created,password_generated_at,temp_password,password_used,ID_packages,lastlogin"
    . (strlen(MOD_SEC_USER_AVATAR) ? "," . MOD_SEC_USER_AVATAR : "")
    . (strlen(MOD_SEC_USER_FIRSTNAME) ? "," . MOD_SEC_USER_FIRSTNAME : "")
    . (strlen(MOD_SEC_USER_LASTNAME) ? "," . MOD_SEC_USER_LASTNAME : "")
    . (strlen(MOD_SEC_USER_COMPANY) ? "," . MOD_SEC_USER_COMPANY : "")
    . (strlen(MOD_SEC_USER_ROLE) ? "," . MOD_SEC_USER_ROLE : "")
    . (strlen(MOD_SEC_USER_TEL) ? "," . MOD_SEC_USER_TEL : "")
    . (strlen(MOD_SEC_USER_CELL) ? "," . MOD_SEC_USER_CELL : "")
);

if (!defined("MOD_SEC_STRICT_FIELDS")) define("MOD_SEC_STRICT_FIELDS", true);

if (!defined("MOD_SEC_ENABLE_TOKEN")) define("MOD_SEC_ENABLE_TOKEN", false);

if (!defined("MOD_SEC_CSRF_PROTECTION")) define("MOD_SEC_CSRF_PROTECTION", false); // Cross-site request forgery
if (!defined("MOD_SEC_CSRF_PROTECTION_PARAM")) define("MOD_SEC_CSRF_PROTECTION_PARAM", "_CSRF_"); // Cross-site request forgery

if (!defined("MOD_SEC_CSS_PATH")) 		define ("MOD_SEC_CSS_PATH", null);

// CRYPT FUNCTIONS - BETA

if (!defined("MOD_SEC_CRYPT")) 		define ("MOD_SEC_CRYPT", false);
if (!defined("MOD_SEC_CRYPT_EMAIL")) 	define ("MOD_SEC_CRYPT_EMAIL", false);

//ADDED-ATS*********************************************************************
if (!defined("USE_MS_ONLINE_LOGIN")){
    define ("USE_MS_ONLINE_LOGIN", false);    
}
else {
    define ("LDAP_SERVER", false);
}
//******************************************************************************

if (!defined("MOD_SECURITY_LDAP_SERVER")) 	define ("MOD_SECURITY_LDAP_SERVER", false);
if (!defined("MOD_SECURITY_SESSION_PERMANENT")) 	define ("MOD_SECURITY_SESSION_PERMANENT", false);


