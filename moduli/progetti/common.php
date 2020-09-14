<?php
/*
 * hide = true, utente non ha i permessi.
 * Utilizzo del valore MODULES_ICONHIDE per nascondere le icone del menu nel caso in cui non vi siano i permessi adeguati.
 */
$user = LoggedUser::Instance();

foreach ($user->user_groups as $group) {
	if ($group == 1 || $group == 2 || $group == 3) {
		$user->user_privileges[] = "progetti_admin";
	}
}
$view_menu = ProgettiProgetto::viewMenuByMatricola($user->matricola_utente_selezionato);

// Creazione variabili di supporto per verifica privilegi utente
$progetti_admin = $user->hasPrivilege("progetti_admin");
$progetti_privileges = $progetti_admin || $user->hasPrivilege("resp_cdr_selezionato") || $view_menu;

$allowed_actions = array();
$allowed_actions["progetti_view_all"] =  array(    
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/index_amministratore?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $progetti_admin ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);

$menu["programmazione"]["progetti"] = array(
    "key"     => "programmazione",
    "subkey"  => "progetti",
    "label"   => "Progetti",
    "icon"    => "",
    "path"    => "/area_riservata".$module->site_path,
    "redir"	  => "",
    "actions" => $allowed_actions,
    "acl"     => "1,2,3",
    "hide"    => !$progetti_privileges
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["progetti"]);