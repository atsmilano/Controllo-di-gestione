<?php
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$tabs = array(
    array("tab_id"=>"richieste_approvazione", "tab_link"=>"dg/approvazione", "tab_params"=>$tab_params, "tab_name"=>"Richeste da approvare"),    
    array("tab_id"=>"richieste_rifiutate", "tab_link"=>"dg/rifiutate", "tab_params"=>$tab_params, "tab_name"=>"Report richeste di competenza rifiutate"),
);
CoreHelper::showTabsPage("richieste_dg", $tabs);