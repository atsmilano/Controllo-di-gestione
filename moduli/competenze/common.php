<?php
$user = LoggedUser::getInstance();

$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$data_attuale = new DateTime();
/*
Ruoli:
"competenze_admin"
"competenze_cdr_gestione"
*/
//competenze_admin
foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3){
        $user->user_privileges[] = "competenze_admin";
    }
}

if (!$user->hasPrivilege("competenze_admin")){    
    $personale = MappaturaCompetenze\Personale::factoryFromMatricola($user->matricola_utente_collegato);
    if ($personale->isAmministratoreInData($data_attuale)) {
        $user->user_privileges[] = "competenze_admin";
    }
}

$personale = MappaturaCompetenze\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
//competenze_cdr_gestione
if ($cm->oPage->globals["cdr"]["value"] != null){
    $cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");
    if ($cdr->isCdrGestioneInData($data_riferimento)) {
        if (!$user->hasPrivilege("competenze_cdr_gestione")) {
            $user->user_privileges[] = "competenze_cdr_gestione";
        }
    }
}

//visualizzazione report (è sufficiente che l'utente abbia una sola mappatura associata)
if ($personale->hasMappatureRuoloValutatore()) {
    $user->user_privileges[] = "competenze_valutatore";
}
if ($personale->hasMappatureRuoloValutato()) {
    $user->user_privileges[] = "competenze_valutato";
}

$view_report = false;
if ($user->hasPrivilege("competenze_admin") 
        || $user->hasPrivilege("competenze_cdr_gestione") 
        || $user->hasPrivilege("competenze_valutatore") 
        || $user->hasPrivilege("competenze_valutato")) {
    $view_report = true;
}

//generazione menu
$allowed_actions = array();
$allowed_actions["gestione"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => $user->hasPrivilege("competenze_admin")/*||$user->hasPrivilege("competenze_cdr_gestione")*/?"table":MODULES_ICONHIDE,
    "dialog" => false
);
$allowed_actions["report"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/report?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => $view_report?"pie-chart":MODULES_ICONHIDE,
    "dialog" => false
);

//visibilità solo per admin e per chi è coinvolto nel processo
$view_competenze = false;
if($user->hasPrivilege("competenze_valutatore")
    || $user->hasPrivilege("competenze_admin")
    ) {
    $view_competenze = true;
}

$menu["competenze"] = array(
    "key"     => "competenze",
    "label"   => "Mappatura<br>competenze",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path,
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$view_competenze,
);
mod_restricted_add_menu_child($menu["competenze"]);