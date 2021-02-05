<?php
/* Inizializzazione del paraemtro hide sulla base dei permessi dell'utente.
 * hide = true, utente non ha i permessi.
 * Utilizzo del valore MODULES_ICONHIDE per nascondere le icone del menu nel caso in cui non vi siano i permessi adeguati.
 */
$user = LoggedUser::Instance();

foreach ($user->user_groups as $group) {
	if ($group == 1 || $group == 2 || $group == 3){
		$user->user_privileges[] = "costi_ricavi_admin";
		$user->user_privileges[] = "costi_ricavi_view";
	}	
}
		
//recupero cdr, menu visualizzato solamente nel caso di cdr con conti associati o per gli amministratori
$admin_view = $user->hasPrivilege("costi_ricavi_admin");
$cdr_global = $cm->oPage->globals["cdr"]["value"];
if($cdr_global !== null) {
    $cdr = new CdrCostiRicavi($cdr_global->id);
    $anno = $cm->oPage->globals["anno"]["value"];
    //se il cdr selezionato è ente acquirente vengono dati i privilegi
    if ($cdr->getContiAnno($anno)>0) {
        if ($user->hasPrivilege("resp_padre_cdr_selezionato") || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")) {
            if (!$user->hasPrivilege("costi_ricavi_view")) {
                $user->user_privileges[] = "costi_ricavi_view";		
            }		
        }		
        if ($user->hasPrivilege("resp_cdr_selezionato")) {
            if (!$user->hasPrivilege("costi_ricavi_edit")) {
                $user->user_privileges[] = "costi_ricavi_edit";		
            }		
        }
        
    }
}

$costi_ricavi_view = false;
if (
    $user->hasPrivilege("costi_ricavi_edit") ||
    $user->hasPrivilege("costi_ricavi_view") || 
    $admin_view){
    $costi_ricavi_view = true;
}

//menu
$allowed_actions = array();
$allowed_actions["configurazione"] =  array(
    "path"   => FF_SITE_PATH . "/area_riservata".$module->site_path."/configurazioni?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon"   => $admin_view ? "cogs" : MODULES_ICONHIDE,
    "dialog" => false
);
$allowed_actions["costi_ricavi_crud"] =  array(
    "path"   => FF_SITE_PATH . "/area_riservata".$module->site_path."/tabelle_supporto?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon"   => $admin_view ? "table" : MODULES_ICONHIDE,
    "dialog" => false
);

//gruppo costi_ricavi
$menu["programmazione"]["costi_ricavi"] = array(
    "key"     => "programmazione",
    "subkey"  => "costi_ricavi",
    "label"   => "P&C costi e ricavi",
    "icon"    => "",
    "path"    => "/area_riservata".$module->site_path,
    "actions" => $allowed_actions,
    "acl"     => "1,2,3",
    "hide"    => !$costi_ricavi_view
);
mod_restricted_add_menu_sub_element($menu["programmazione"]["costi_ricavi"]);