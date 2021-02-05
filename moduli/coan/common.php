<?php

/* Inizializzazione del paraemtro hide sulla base dei permessi dell'utente.
 * hide = true, utente non ha i permessi.
 * Utilizzo del valore MODULES_ICONHIDE per nascondere le icone del menu nel caso in cui non vi siano i permessi adeguati.
 */
$user = LoggedUser::Instance();
$cm = Cm::getInstance();

foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3) {
        $user->user_privileges[] = "coan_view";
        $user->user_privileges[] = "coan_admin";
    }
}

if (!$user->hasPrivilege("coan_view")) {
    $anno = $cm->oPage->globals["anno"]["value"];
    try {
        $cdr = $cm->oPage->globals["cdr"]["value"];
        if (CoanCdc::isCdrAssociatoAnno($anno, $cdr) == true) {
            $user->user_privileges[] = "coan_view";
        }
    } catch (Exception $ex) {
        
    }
}

// Creazione variabile di supporto per verifica privilegi utente
$coan_admin = $user->hasPrivilege("coan_admin");
$coan_privileges = (
    $user->hasPrivilege("coan_view") && (
        $user->hasPrivilege("coan_admin") ||
        $user->hasPrivilege("cdr_view_all") ||
        $user->hasPrivilege("resp_cdr_selezionato") ||
        $user->hasPrivilege("resp_padre_ramo_cdr_selezionato") ||
        $coan_admin
    )
);

$allowed_actions = array();
$allowed_actions["coan_import"] = array(
    "path" => FF_SITE_PATH . "/area_riservata" . $module->site_path . "/importazione?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $coan_admin ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);
$allowed_actions["coan_crud"] = array(
    "path" => FF_SITE_PATH . "/area_riservata" . $module->site_path . "/configurazioni?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $coan_admin ? "table" : MODULES_ICONHIDE,
    "dialog" => false
);
//gruppo coan
$menu["controllo"]["coan"] = array(
    "key" => "controllo",
    "subkey" => "coan",
    "label" => "Contabilità analitica",
    "icon" => "",
    "path" => "/area_riservata" . $module->site_path,
    "redir" => "",
    "actions" => $allowed_actions,
    "acl" => "1,2,3",
    "hide" => !$coan_privileges,
);

mod_restricted_add_menu_sub_element($menu["controllo"]["coan"]);
