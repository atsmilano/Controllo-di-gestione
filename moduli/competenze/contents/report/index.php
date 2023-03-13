<?php
$cm->oPage->tplAddJs("Chart.min.js", "Chart.min.js", FF_THEME_DIR . "/library/chartjs");
$cm->oPage->tplAddJs("Chart.bundle.min.js", "Chart.bundle.min.js", FF_THEME_DIR . "/library/chartjs");

$module = core\Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$user = LoggedUser::getInstance();
$tabs = array();
$tabs[] = array("tab_id"=>"premessa", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/report/premessa", "tab_params"=>$tab_params, "tab_name"=>"Premessa metodologica");    
$tabs[] = array("tab_id"=>"individuali", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/report/individuali", "tab_params"=>$tab_params, "tab_name"=>"Report individuali");

\CoreHelper::showTabsPage("competenze", $tabs);