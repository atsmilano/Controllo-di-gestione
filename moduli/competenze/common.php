<?php
$user = LoggedUser::getInstance();

$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
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
//competenze_cdr_gestione
if ($cm->oPage->globals["cdr"]["value"] != null){
    $cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");
    if ($cdr->isCdrGestioneInData($data_riferimento)) {
        if (!$user->hasPrivilege("competenze_cdr_gestione")) {
            $user->user_privileges[] = "competenze_cdr_gestione";
        }
    }
}

//generazione menu
$allowed_actions = array();
$allowed_actions["gestione"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => $user->hasPrivilege("competenze_admin")/*||$user->hasPrivilege("competenze_cdr_gestione")*/?"table":MODULES_ICONHIDE,
    "dialog" => false
);/*
$allowed_actions["report"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/report?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => "pie-chart",
    "dialog" => false
);*/

//visibilità solo per admin e per chi è coinvolto nel processo
$view_competenze = false;
if(/*count(\MappaturaCompetenze\MappaturaPeriodo::getAll(array("matricola_personale"=>$user->matricola_utente_selezionato)))
    || */count(\MappaturaCompetenze\MappaturaPeriodo::getAll(array("matricola_valutatore"=>$user->matricola_utente_selezionato)))
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