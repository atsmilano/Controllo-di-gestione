<?php
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));
$tabs = array(
    array("tab_id"=>"richieste_approvazione", "tab_link"=>"istruttoria/approvazione", "tab_params"=>$tab_params, "tab_name"=>"Richeste da approvare"),    
    array("tab_id"=>"richieste_acquisite", "tab_link"=>"istruttoria/acquisite", "tab_params"=>$tab_params, "tab_name"=>"Richeste acquisite"),  
    array("tab_id"=>"richieste_rifiutate", "tab_link"=>"istruttoria/rifiutate", "tab_params"=>$tab_params, "tab_name"=>"Report richeste di competenza rifiutate"),
);
CoreHelper::showTabsPage("richieste_uo_competente", $tabs);