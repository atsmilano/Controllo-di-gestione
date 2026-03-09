<?php
use \core\Modulo;
use \LoggedUser;
use \CoreHelper;

$module = Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));

$user = LoggedUser::getInstance();
$tabs = array();
$tabs[] = array("tab_id"=>"amministratori", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/elenchi_no_associazione/obiettivi_individuali", "tab_params"=>$tab_params, "tab_name"=>"Obiettivi individuali", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"referenti", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/elenchi_no_associazione/responsabili_cessati", "tab_params"=>$tab_params, "tab_name"=>"Responsabili cessati", "hide_ret_url"=>true);

CoreHelper::showTabsPage("obiettivi_report", $tabs);