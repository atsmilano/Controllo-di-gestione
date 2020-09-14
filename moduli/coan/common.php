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
	}
}

if (!$user->hasPrivilege("coan_view")) {
    $anno = $cm->oPage->globals["anno"]["value"];
    try {                
        $cdr = $cm->oPage->globals["cdr"]["value"];
        if(CoanCdc::isCdrAssociatoAnno($anno, $cdr) == true){
            $user->user_privileges[] = "coan_view";
        }                        
    } catch (Exception $ex) {

    }        
}

// Creazione variabile di supporto per verifica privilegi utente
$coan_privileges = (
    $user->hasPrivilege("coan_view") && (
        $user->hasPrivilege("cdr_view_all") ||
        $user->hasPrivilege("resp_cdr_selezionato") ||
        $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")
    )
);

//gruppo coan
$menu["controllo"]["coan"] = array(
    "key"     => "controllo",
    "subkey"  => "coan",
    "label"   => "Contabilità analitica",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path,
    "redir"	  => "",
    "actions" => array(),
    "acl"     => "1,2,3",
    "hide"    => !$coan_privileges,
);

mod_restricted_add_menu_sub_element($menu["controllo"]["coan"]);
