<?php
$user = LoggedUser::Instance();

//acl
foreach ($user->user_groups as $group) {
	if ($group == 1 || $group == 2 || $group == 3) {
		$user->user_privileges[] = "strategia_prospettive_edit";
	}
}

//viene impostata la costante per definire se la strategia viene definita solamente dall'elemento radice o da tutti i cdr di programmazione strategica
define("STRATEGIA_CDR_PROGRAMMAZIONE", true);	

// Creazione variabili di supporto per verifica privilegi utente
$prospettive_edit = (
    $user->hasPrivilege("strategia_prospettive_edit")
);

//menu
$allowed_actions = array();
$allowed_actions["prospettive"] =  array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/prospettive?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $prospettive_edit ? "table" : MODULES_ICONHIDE,
    "dialog" => false
);

$menu["programmazione"]["strategia"] = array(
    "key" => "programmazione",
    "subkey" => "strategia",
    "label" => "Strategia",
    "icon" => "",
    "path" => "/area_riservata" . $module->site_path,
    "actions" => $allowed_actions,
    "acl" => "1,2,3",
    //"hide" => !$strategia_privileges,
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["strategia"]);