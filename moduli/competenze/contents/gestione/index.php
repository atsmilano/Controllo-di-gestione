<?php
$module = core\Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$user = LoggedUser::getInstance();
$tabs = array();
if ($user->hasPrivilege("competenze_admin")) {
    $tabs[] = array("tab_id"=>"competenze_trasversali", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/competenze_trasversali", "tab_params"=>$tab_params, "tab_name"=>"Competenze trasversali");
    $tabs[] = array("tab_id"=>"valori", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/valori", "tab_params"=>$tab_params, "tab_name"=>"Valori");
    $tabs[] = array("tab_id"=>"periodi", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/periodi", "tab_params"=>$tab_params, "tab_name"=>"Periodi");
}
if ($user->hasPrivilege("competenze_cdr_gestione") || $user->hasPrivilege("competenze_admin")) {
    $tabs[] = array("tab_id"=>"profili", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/profili", "tab_params"=>$tab_params, "tab_name"=>"Profili");            
    $tabs[] = array("tab_id"=>"competenze_specifiche", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/competenze_specifiche", "tab_params"=>$tab_params, "tab_name"=>"Competenze specifiche");
    $tabs[] = array("tab_id"=>"competenze_profilo", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/competenze_profilo", "tab_params"=>$tab_params, "tab_name"=>"Competenze profilo");    
    $tabs[] = array("tab_id"=>"valori_attesi_profilo", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/valori_attesi_profilo", "tab_params"=>$tab_params, "tab_name"=>"Valori attesi profilo");
    $tabs[] = array("tab_id"=>"associazione_profili", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione/associazione_profili", "tab_params"=>$tab_params, "tab_name"=>"Associazione profili", "hide_ret_url"=>true);
}
\CoreHelper::showTabsPage("competenze", $tabs);