<?php
use scadenze\Personale;

define("SCADENZE_MAIL_INVIO", "");
define("SCADENZE_GIORNI_PROMEMORIA_DEFAULT", 14);

$user = LoggedUser::getInstance();

$anno = $cm->oPage->globals["anno"]["value"];
//$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$data_riferimento = new DateTime();
/*
Ruoli:
"scadenze_admin"
*/
//competenze_admin
//gli amministratori della piattaforma sono anche amministratori delle scadenze
foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3){
        $user->user_privileges[] = "scadenze_admin";
    }
}

//in caso l'utente non abbia già il privilegio da admin per le scadenze
//viene verificata l'eventuale presenza nella tabella degli amministratori
$personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);
if ($user->hasPrivilege("scadenze_admin")) {     
    if ($personale->isAmministratoreInData($data_riferimento)) {
        $user->user_privileges[] = "scadenze_admin";
    }
}

//generazione del menu
$allowed_actions = array();
$allowed_actions["gestione"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => $user->hasPrivilege("scadenze_admin")?"table":MODULES_ICONHIDE,
    "dialog" => false
);

//visibilità solo per admin e per i referenti dei cdr
$view_scadenze = false;
if ($user->hasPrivilege("scadenze_admin")) {
    $view_scadenze = true;
}
else {
    if ($personale->isReferenteCdrInData($data_riferimento)) {
        if (!$user->hasPrivilege("scadenze_referente_cdr")) {
            $user->user_privileges[] = "scadenze_referente_cdr";
            $view_scadenze = true;
        }
    }
}

$menu["scadenze"] = array(
    "key"     => "scadenze",
    "label"   => "Scadenze",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path,
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$view_scadenze,
);
mod_restricted_add_menu_child($menu["scadenze"]);