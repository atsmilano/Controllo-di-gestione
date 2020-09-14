<?php
$globals_crypt = ffGlobals::getInstance("__mod_sec_crypt__");

$text = "<p>key (HEX): " . bin2hex($globals_crypt->_crypt_Ku_) . "</p>";
$text .= "<p>salt (HEX): " . bin2hex($globals_crypt->_crypt_KSu_) . "</p>";

$cm->oPage->addContent($text);
