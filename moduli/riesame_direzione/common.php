<?php
$user = LoggedUser::getInstance();

foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3){
        $user->user_privileges[] = "riesame_direzione_view";
        $user->user_privileges[] = "riesame_direzione_admin";
    }
}

if ($user->hasPrivilege("cdr_view_all") || $user->hasPrivilege("resp_cdr_selezionato") || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")){
	$user->user_privileges[] = "riesame_direzione_view";
}

// Creazione variabile di supporto per verifica privilegi utente
$riesame_direzione_view = $user->hasPrivilege("riesame_direzione_view");
    
//gruppo qualita
$menu["qualita"] = array(
    "key"     => "qualita",
    "label"   => "Qualità",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path,
    "redir"   => "",
    "actions" => array(),
    "acl"	  => "1,2,3",
    "hide"    => !$riesame_direzione_view,
);
mod_restricted_add_menu_child($menu["qualita"]);

$menu["qualita"]["riesame_direzione"] = array(
    "key"     => "qualita",
    "subkey"  => "riesame_direzione",
    "label"   => "Riesame della Direzione",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path."/",
    "actions" => array(),
    "acl"	  => "1,2,3",
    "hide"    => !$riesame_direzione_view,
);
mod_restricted_add_menu_sub_element($menu["qualita"]["riesame_direzione"]);

