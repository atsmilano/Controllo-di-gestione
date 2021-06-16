<?php
$module = Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$tabs = array(
    array("tab_id"=>"richieste_competenza", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/richieste/richieste_competenza", "tab_params"=>$tab_params, "tab_name"=>"Fabbisogno CdR e afferenti"),
    array("tab_id"=>"richieste_ramo", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/richieste/richieste_ramo", "tab_params"=>$tab_params, "tab_name"=>"Report richeste di competenza"),
    array("tab_id"=>"richieste_rifiutate", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/richieste/richieste_rifiutate", "tab_params"=>$tab_params, "tab_name"=>"Report richeste di competenza rifiutate"),
);
CoreHelper::showTabsPage("richieste", $tabs);