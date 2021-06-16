<?php
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$tabs = array(
    array("tab_id"=>"richieste_approvazione", "tab_link"=>"direzione/approvazione", "tab_params"=>$tab_params, "tab_name"=>"Richeste da approvare"),    
    array("tab_id"=>"richieste_rifiutate", "tab_link"=>"direzione/rifiutate", "tab_params"=>$tab_params, "tab_name"=>"Report richeste di competenza rifiutate"),
);
CoreHelper::showTabsPage("richieste_direzione", $tabs);