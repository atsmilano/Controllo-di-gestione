<?php
use \core\Modulo;
use \LoggedUser;
use \CoreHelper;

$module = Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));

$user = LoggedUser::getInstance();
$tabs = array();
$tabs[] = array("tab_id"=>"amministratori", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/amministratori", "tab_params"=>$tab_params, "tab_name"=>"Amministratori", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"referenti", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/referenti_cdr", "tab_params"=>$tab_params, "tab_name"=>"Referenti CdR", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"tipologie", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/tipologie", "tab_params"=>$tab_params, "tab_name"=>"Tipologie");    
$tabs[] = array("tab_id"=>"mail_cdr", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/abilitazioni_cdr", "tab_params"=>$tab_params, "tab_name"=>"Abilitazioni Cdr");    

CoreHelper::showTabsPage("fabbisogno", $tabs);