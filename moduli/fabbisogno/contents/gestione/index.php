<?php
$module = Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));

$user = LoggedUser::getInstance();
$tabs = array();
$tabs[] = array("tab_id"=>"schede", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/schede", "tab_params"=>$tab_params, "tab_name"=>"Schede Anno", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"referenti_formazione", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/referenti_formazione", "tab_params"=>$tab_params, "tab_name"=>"Referenti Formazione");    
if ($user->hasPrivilege("fabbisogno_admin")) {
    $tabs[] = array("tab_id"=>"operatori_formazione", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/operatori_formazione", "tab_params"=>$tab_params, "tab_name"=>"Operatori Formazione");    
}
\CoreHelper::showTabsPage("fabbisogno", $tabs);