<?php
$user = LoggedUser::getInstance();

$anno = $cm->oPage->globals["anno"]["value"];
if ($cm->oPage->globals["cdr"]["value"]->codice !== null){
    //viene recuperato il cdr dal piano di priorità massima definito
    $cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d")))->cloneAttributesToNewObject("CdrRU");
    //vengono recuperati i ruoli per il modulo
    /*
    "ru_admin"
    "ru_view"
    "ru_richiesta_edit"
    "ru_programmazione_strategica_edit"
    "ru_direzione_riferimento_edit"
    "ru_dg_edit"
    "ru_uo_competente_edit"
    */
    //admin
    foreach ($user->user_groups as $group) {
        if ($group == 1 || $group == 2 || $group == 3){
            $user->user_privileges[] = "ru_view";
            $user->user_privileges[] = "ru_admin";
        }
    }
    //cdr_abilitato
    if ($cdr->isCdrAbilitatoAnno($anno)) {
        if (!$user->hasPrivilege("ru_view")) {
            $user->user_privileges[] = "ru_view";
        }
        $user->user_privileges[] = "ru_richiesta_edit";
    }
    //cdr_programmazione_strategica
    if ($cdr->isProgrammazioneStrategicaAnno($anno)) {
        if (!$user->hasPrivilege("ru_view")) {
            $user->user_privileges[] = "ru_view";
        }  
        $user->user_privileges[] = "ru_programmazione_strategica_edit";
    }
    //cdr_direzione_riferimento
    if ($cdr->isDirezioneRiferimentoAnno($anno)) {
        if (!$user->hasPrivilege("ru_view")) {
            $user->user_privileges[] = "ru_view";
        }
        $user->user_privileges[] = "ru_direzione_riferimento_edit";
    }
    //direzione generale
    if ($cdr->isDgAnno($anno)) {
        if (!$user->hasPrivilege("ru_view")) {
            $user->user_privileges[] = "ru_view";
        }
        $user->user_privileges[] = "ru_dg_edit";
    }
    //uo competente
    if ($cdr->isUOCompetenteAnno($anno)) {
        if (!$user->hasPrivilege("ru_view")) {
            $user->user_privileges[] = "ru_view";
        }
        $user->user_privileges[] = "ru_uo_competente_edit";
    }
}

//generazione menu
$allowed_actions = array();
$menu["risorse_umane"] = array(
    "key"     => "risorse_umane",
    "label"   => "Risorse umane",
    "icon"	  => "",
    "path"    => "",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$user->hasPrivilege("ru_view"),
);
mod_restricted_add_menu_child($menu["risorse_umane"]);

$allowed_actions["report"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/report",    
    "icon"   => $user->hasPrivilege("ru_view") ? "pie-chart" : MODULES_ICONHIDE,
    "dialog" => false
);
$menu["risorse_umane"]["richieste"] = array(
    "key"     => "risorse_umane",
    "subkey"  => "richieste",
    "label"   => "Richieste CDR",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/richieste",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !($user->hasPrivilege("ru_admin") || $user->hasPrivilege("ru_richiesta_edit") || $user->hasPrivilege("ru_programmazione_strategica_edit")),
);
mod_restricted_add_menu_sub_element($menu["risorse_umane"]["richieste"]);

$allowed_actions = array();
$menu["risorse_umane"]["approvazione_direzione"] = array(
    "key"     => "risorse_umane",
    "subkey"  => "approvazione_direzione",
    "label"   => "Approvazione direzione",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/direzione",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !($user->hasPrivilege("ru_admin") || $user->hasPrivilege("ru_direzione_riferimento_edit")),
);
mod_restricted_add_menu_sub_element($menu["risorse_umane"]["approvazione_direzione"]);

$menu["risorse_umane"]["approvazione_dg"] = array(
    "key"     => "risorse_umane",
    "subkey"  => "approvazione_dg",
    "label"   => "Approvazione DG",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/dg",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !($user->hasPrivilege("ru_admin") || $user->hasPrivilege("ru_dg_edit")),
);
mod_restricted_add_menu_sub_element($menu["risorse_umane"]["approvazione_dg"]);

$menu["risorse_umane"]["istruttoria"] = array(
    "key"     => "risorse_umane",
    "subkey"  => "istruttoria",
    "label"   => "Istruttoria e monitoraggio",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/istruttoria",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !($user->hasPrivilege("ru_admin") || $user->hasPrivilege("ru_uo_competente_edit")),
);
mod_restricted_add_menu_sub_element($menu["risorse_umane"]["istruttoria"]);