<?php
$module = core\Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$tabs = array(
    array("tab_id"=>"personale_cdr", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/report/personale_cdr", "tab_params"=>$tab_params, "tab_name"=>"Personale CdR"),   
);
CoreHelper::showTabsPage("ru_report", $tabs);