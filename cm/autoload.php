<?php
/**
 * framework common functions
 *
 * @package FormsFramework
 * @subpackage common
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2012-2020, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */
if(defined("COMPOSER_PATH") && COMPOSER_PATH)
    require_once (__TOP_DIR__ . COMPOSER_PATH . "/autoload.php");

spl_autoload_register(function ($class) {
    $php_ext = ".php";
    switch ($class) {
        case "cm":
            require(__DIR__ . '/cm' . $php_ext);
            break;
        case "cmRouter":
            require(__DIR__ . '/' . $class . $php_ext);
            break;
        default:
    }

    if(!(defined("COMPOSER_PATH") && COMPOSER_PATH)) {
        switch ($class) {
            case "PHPMailer":
            case "phpmailer":
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmailer" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmaileroauth" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmaileroauthgoogle" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/class.smtp" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/class.pop3" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/extras/EasyPeasyICS" . $php_ext);
                require(__TOP_DIR__ . "/library/phpmailer/extras/ntlm_sasl_client" . $php_ext);
                break;
            case "PHPExcel":
                require(__TOP_DIR__ . "/library/PHPexcel/class.PHPexcel" . $php_ext);
                break;
            default:
        }
    }
});