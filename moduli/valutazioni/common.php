<?php
define("VALUTAZIONI_IDENTIFICATORE_PARAMETRO_FORMULA", "@");
//tempo massimo di esecuzione degli script da variare con ini_set per gli script molto onerosi in termini di risorse
define("VALUTAZIONI_MAX_EXECUTION_TIME", 180);
define("VALUTAZIONI_DIFF_ORA_RICALCOLO", "24 hours");
define("VALUTAZIONI_LABEL_GRAFICO_MAX_LEN", 25);
//directory in download/enviroment di destinazione delle schede di valutazione
define("VALUTAZIONI_DOWNLOADABLE_EXTRACTIONS_DIR", "schede_valutazione");
//acl
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
        
foreach ($user->user_groups as $group) {
	if ($group == 1 || $group == 2 || $group == 3) {
		$user->user_privileges[] = "valutazioni_admin";
		$user->user_privileges[] = "nucleo_di_valutazione";
	}
}

$valutazioni_admin = $user->hasPrivilege("valutazioni_admin");
$visualizza_menu = true;

//menu
$allowed_actions = array();
$allowed_actions["valutazioni"] = array(    
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/configurazioni?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $valutazioni_admin ? "table" : MODULES_ICONHIDE,
    "dialog" => false
);

$cruscotto_view = true;

$allowed_actions["cruscotto"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => $cruscotto_view ? "pie-chart" : MODULES_ICONHIDE,
    "dialog" => false
);

//gruppo valutazioni
$menu["valutazioni"] = array(
    "key"     => "valutazioni",
    "label"   => "Valutazioni",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path,
    "redir"	  => "",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$visualizza_menu,
);
mod_restricted_add_menu_child($menu["valutazioni"]);