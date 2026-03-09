<?php
use \core\Modulo;
use \LoggedUser;
use \CoreHelper;

$module = Modulo::getCurrentModule();
$tab_params = $cm->oPage->getUrlParams(array("ret_url"));

$user = LoggedUser::getInstance();
$tabs = array();
$tabs[] = array("tab_id"=>"introduzione", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."cruscotto/introduzione", "tab_params"=>$tab_params, "tab_name"=>"Cruscotto", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"matrice_pesi_cdr", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto/matrice_pesi_cdr", "tab_params"=>$tab_params, "tab_name"=>"Obiettivi - CdR / assegnazione pesi", "hide_ret_url"=>true);
$tabs[] = array("tab_id"=>"matrice_pesi_personale", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto/matrice_pesi_personale", "tab_params"=>$tab_params, "tab_name"=>"Obiettivi - personale / assegnazione pesi");    
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {      
    if ($user->hasPrivilege("obiettivi_individuali_admin")
    || $user->hasPrivilege("resp_cdr_selezionato")
    || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")
    ) {  
        $modulo_obiettivi_individuali = Modulo::getActiveModuleById(19);     
        $tabs[] = array("tab_id"=>"obiettivi_individuali", "tab_link"=>FF_SITE_PATH . "/area_riservata".$modulo_obiettivi_individuali->site_path, "tab_params"=>$tab_params, "tab_name"=>"Obiettivi Individuali");
    }
}
$tabs[] = array("tab_id"=>"report_assegnazioni", "tab_link"=>FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto/report_assegnazioni", "tab_params"=>$tab_params, "tab_name"=>"Report assegnazione CdR");    

CoreHelper::showTabsPage("cruscotto_obiettivi", $tabs);