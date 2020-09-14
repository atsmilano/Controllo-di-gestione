<?php
$user = LoggedUser::Instance();
$gestione_azienda_privileges = $user->hasPrivilege("gestione_azienda");
$configurazione_privileges = $user->hasPrivilege("anni_budget_admin") 
                            || $user->hasPrivilege("moduli_admin")
                            || $user->hasPrivilege("deleghe_admin");

$menu["configurazione"] = array(
    "key"     => "configurazione",
    "label"   => "Configurazione",
    "icon"    => "cog",
    "path"    => "",
    "redir"   => "/area_riservata/configurazione?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "actions" => array(),
    "acl"     => "1,2,3",
    "hide"    => !$configurazione_privileges,
);
mod_restricted_add_menu_child($menu["configurazione"]);

//if ($user->hasPrivilege("gestione_azienda")) {
//Gestione azienda**************************************************************
$menu["gestione_azienda"] = array(
    "key"     => "gestione_azienda",
    "label"   => "Azienda",
    "icon"    => "building-o",
    "path"    => "",
    "redir"   => "/area_riservata/",
    "actions" => array(),
    "acl"     => "1,2,3",
    "hide"    => !$gestione_azienda_privileges,
);
mod_restricted_add_menu_child($menu["gestione_azienda"]);

//piani_cdr
$menu["gestione_azienda"]["piani_cdr"] = array(
    "key"     => "gestione_azienda",
    "subkey"  => "piani_Cdr",
    "label"   => "Piani cdr",
    "icon"    => "sitemap",
    "path"    => "/area_riservata/gestione_azienda/piani_cdr",
    "actions" => array(
        "support_tables" => array(
            "path"   => FF_SITE_PATH . "/area_riservata/gestione_azienda/configurazione_cdr?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
            "icon"   => $gestione_azienda_privileges ? "table" : MODULES_ICONHIDE,
            "dialog" => false
        ),
    ),
    "acl"     => "1,2,3",
    "hide"    => !$gestione_azienda_privileges,
);
mod_restricted_add_menu_sub_element($menu["gestione_azienda"]["piani_cdr"]);

//distribuzione teste
$menu["gestione_azienda"]["distribuzione_teste"] = array(
    "key"    => "gestione_azienda",
    "subkey" => "distribuzione_teste",
    "label"  => "Distribuzione teste",
    "icon"   => "group",
    "path"   => "/area_riservata/gestione_azienda/personale",
    "acl"    => "1,2,3",
    "hide"   => !$gestione_azienda_privileges,
);
mod_restricted_add_menu_sub_element($menu["gestione_azienda"]["distribuzione_teste"]);

// Anagrafiche
$menu["gestione_azienda"]["anagrafe"] = array(
    "key"    => "gestione_azienda",
    "subkey" => "anagrafe",
    "label"  => "Anagrafica",
    "icon"   => "address-book",
    "path"   => "/area_riservata/gestione_azienda/anagrafe",
    "acl"    => "1,2,3",
    "hide"   => !$gestione_azienda_privileges,
);
mod_restricted_add_menu_sub_element($menu["gestione_azienda"]["anagrafe"]);

// Gestione del Personale
$menu["gestione_azienda"]["gestione_personale"] = array(
    "key"     => "gestione_azienda",
    "subkey"  => "gestione_personale",
    "label"   => "Gestione del Personale",
    "icon"    => "id-card",
    "path"    => "/area_riservata/gestione_azienda/gestione_personale",
    "actions" => array(
        "support_tables" => array(
            "path"   => FF_SITE_PATH . "/area_riservata/gestione_azienda/gestione_personale/tbl_supporto/index?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
            "icon"   => $gestione_azienda_privileges ? "table" : MODULES_ICONHIDE,
            "dialog" => false,
        ),
    ),
    "acl"  => "1,2,3",
    "hide" => !$gestione_azienda_privileges,
);
mod_restricted_add_menu_sub_element($menu["gestione_azienda"]["gestione_personale"]);

//******************************************************************************
//GENERAZIONE DEL MENU IN BASE AI MODULI****************************************
require(FF_DISK_PATH . "/conf/contents/load_modules.php");