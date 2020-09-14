<?php
/*
 * Inizializzazione del paraemtro hide sulla base dei permessi dell'utente.
 * hide = true, utente non ha i permessi.
 * Utilizzo del valore MODULES_ICONHIDE per nascondere le icone del menu nel caso in cui non vi siano i permessi adeguati.
 */
//acl
$user = LoggedUser::Instance();

//definizione costanti
//massimo peso attribuibile ai cdr
define("OBIETTIVI_MIN_PESO", 15);
define("OBIETTIVI_MAX_PESO", 1000);
define("OBIETTIVI_NOTE_NUCLEO_DEFAULT", "Si conferma il grado di raggiungimento espresso dal referente per il periodo di rendicontazione.");
define("OBIETTIVI_LABEL_GRAFICO_MAX_LEN", 25);
define("OBIETTIVI_IDENTIFICATORE_PARAMETRO_FORMULA", "@");

foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3) {
        $user->user_privileges[] = "obiettivi_aziendali_edit";
        $user->user_privileges[] = "indicatori_edit";
        $user->user_privileges[] = "nucleo_di_valutazione";
    }
}

// Creazione variabili di supporto per verifica privilegi utente
$obiettivi_aziendali_edit = $user->hasPrivilege("obiettivi_aziendali_edit");
$cdr_resp_view = (
    $user->hasPrivilege("cdr_view_all")
    || $user->hasPrivilege("resp_cdr_selezionato")
    || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")
);
$indicatori_edit = $user->hasPrivilege("indicatori_edit");

//menu
$allowed_actions = array();
$allowed_actions["elenchi_no_associazione"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/elenchi_no_associazione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => "cogs",//$obiettivi_aziendali_edit ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);
$allowed_actions["config_crud"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/configurazioni?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $obiettivi_aziendali_edit ? "table" : MODULES_ICONHIDE,
    "dialog" => false
);
$menu["programmazione"]["obiettivi_aziendali"] = array(
    "key"     => "programmazione",
    "subkey"  => "obiettivi_aziendali",
    "label"   => "Obiettivi aziendali",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/obiettivi_aziendali",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$obiettivi_aziendali_edit,
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["obiettivi_aziendali"]);

$allowed_actions = array();
$allowed_actions["cruscotto"] =  array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $cdr_resp_view ? "pie-chart" : MODULES_ICONHIDE,
    "dialog" => false
);

$menu["programmazione"]["obiettivi"] = array(
    "key"     => "programmazione",
    "subkey"  => "obiettivi",
    "label"   => "Obiettivi CDR",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path."/obiettivi_cdr",
    "actions" => $allowed_actions,
    "acl"     => "1,2,3",
    "hide"    => !$cdr_resp_view,
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["obiettivi"]);

$allowed_actions = array();
$allowed_actions["gestione_periodi"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/periodi_rendicontazione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $obiettivi_aziendali_edit ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);

//cruscotto rendicontazione
$allowed_actions["cruscotto_rendicontazione"] =  array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto_rendicontazione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => /*$cdr_resp_view ?*/ "pie-chart" /*: MODULES_ICONHIDE*/,
    "dialog" => false
);

$menu["controllo"]["obiettivi_individuali"] = array(
    "key"     => "controllo",
    "subkey"  => "obiettivi_individuali",
    "label"   => "Rendicontazione<br>obiettivi individuali",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path."?menu_key=controllo&",
    "actions" => $allowed_actions,
    "acl"     => "1,2,3",
    "hide"    => 0,    
);
mod_restricted_add_menu_sub_element($menu["controllo"]["obiettivi_individuali"]);

//nel caso di utente semplice vengono visualizzate due voci di menu (con riferimento alla stessa pagina) in programmazione e in controllo
if(!$cdr_resp_view) {
    $menu["programmazione"]["assegnazione_obiettivi"] = array(
        "key" => "programmazione",
        "subkey" => "assegnazione_obiettivi",
        "label" => "Obiettivi individuali",
        "icon" => "",
        "path"    => "/area_riservata".$module->site_path."?menu_key=programmazione&",
        "actions" => $allowed_actions,
        "acl" => "1,2,3",
        "hide" => 0,
    );

    mod_restricted_add_menu_sub_element($menu["programmazione"]["assegnazione_obiettivi"]);
}

$allowed_actions = array();
$allowed_actions["gestione_storico"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/indicatori/gestione_storico?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $indicatori_edit ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);
//definizione indicatori
$menu["programmazione"]["indicatori"] = array(
    "key"     => "programmazione",
    "subkey"  => "indicatori",
    "label"   => "Indicatori",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/indicatori",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$indicatori_edit,
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["indicatori"]);

//pannello indicatori visibile solo da amministratori e responsabile cdr nel caso ci sia almeno un indicatore previsto per il pannello
//se l'utente è amministratore è inutile effettuare le operazioni di verifica sugli indicatori per il cdr
$view_cruscotto = false;
if ($indicatori_edit) {
    $view_cruscotto = true;
}
else  if ($cdr_resp_view == true){
    $data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
    $anno = $cm->oPage->globals["anno"]["value"];
    $cdr = $cm->oPage->globals["cdr"]["value"];
    $anagrafica_cdr_obiettivi = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $data_riferimento);   
    if (count($anagrafica_cdr_obiettivi->getIndicatoriCruscottoAnno($anno))>0) {
        $view_cruscotto = true;
    }
}

$allowed_actions =  array();
$allowed_actions["config_periodo_cruscotto"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/indicatori/config_periodo_cruscotto?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $indicatori_edit ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);
$menu["controllo"]["cruscotto_indicatori"] = array(
    "key"           => "controllo"
    ,"subkey"		=> "cruscotto_indicatori"
    , "label"       => "Cruscotto indicatori"
    , "icon"		=> ""
    , "path"		=> "/area_riservata".$module->site_path."/indicatori/cruscotto"																	
    , "actions"     => $allowed_actions
    , "acl"			=> "1,2,3"
    ,"hide"    => !$view_cruscotto
);
mod_restricted_add_menu_sub_element($menu["controllo"]["cruscotto_indicatori"]);