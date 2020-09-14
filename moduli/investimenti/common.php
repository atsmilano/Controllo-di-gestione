<?php
$user = LoggedUser::Instance();
define(INVESTIMENTI_LUNGHEZZA_MAX_DESCRIZIONE_RICHIESTA, 30);

//i ruoli previsti dalla scheda sono rigidi rispetto alla struttura aziendale, si implementa una struttura che permetta
//di gestire in maniera più flessibile questi ruoli, definendo le uoc competenti e le direzioni di riferimento per ogni anno di budget tramite db.
// io privilegi per la scheda sono
//investimenti_view                         -> visualizzazione investimenti nel menu e tab istruzioni e piano investimenti
//investimenti_admin                        -> modifica date per stati avanzamento
//investimenti_linee_guida_edit             -> modifica delle linee guida (istruzioni) per l'anno
//investimenti_richieste_view               -> visualizzazione tab richieste
//investimenti_richieste_edit               -> inserimento e modifica delle richieste
//investimenti_approvazione_view            -> visualizzazione tab approvazione
//investimenti_istruttoria_view             -> visualizzazione tab istruttoria
//investimenti_istruttoria_bilancio_edit    -> modifica delle informazioni del cdr responsabile del bilancio
//investimenti_istruttoria_dip_amm_edit     -> modifica delle informazioni del dip amministrativo
//investiminvestimenti_piano_parere_edit    -> modifica del parere sul piano investimenti

//privilegi dell'utente sul modulo
//privilegi relativi al gruppo utente
foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3){
        $user->user_privileges[] = "investimenti_view";
        $user->user_privileges[] = "investimenti_admin";
        //$user->user_privileges[] = "investimenti_linee_guida_edit";
        //$user->user_privileges[] = "investimenti_richieste_view";
        //$user->user_privileges[] = "investimenti_richieste_edit";
        //$user->user_privileges[] = "investimenti_approvazione_view";
        //$user->user_privileges[] = "investimenti_istruttoria_view";
        //$user->user_privileges[] = "investimenti_istruttoria_bilancio_edit";
        //$user->user_privileges[] = "investimenti_istruttoria_dip_amm_edit";
        //$user->user_privileges[] = "investimenti_piano_parere_edit";
    }
}

$cdr_global = $cm->oPage->globals["cdr"]["value"];
$anno = $cm->oPage->globals["anno"]["value"];
if ($cdr_global !== null){
    $cdr = new CdrInvestimenti($cdr_global->id);
    if ($cdr->isAbilitatoInvestimentiAnno($anno)) {
        //se il padre è responsabile di un cdr abilitato alla visualizzazione della scheda potrà visualizzare
        if ($user->hasPrivilege("resp_padre_cdr_selezionato") || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")){
            if (!$user->hasPrivilege("investimenti_view")) {
                $user->user_privileges[] = "investimenti_view";
            }
            if (!$user->hasPrivilege("investimenti_richieste_view")) {
                $user->user_privileges[] = "investimenti_richieste_view";
            }
        }
        if ($user->hasPrivilege("resp_cdr_selezionato")) {
            //solamente i responsabili abilitati possono utilizzare la scheda
            if (!$user->hasPrivilege("investimenti_view")) {
                $user->user_privileges[] = "investimenti_view";
            }
            if (!$user->hasPrivilege("investimenti_richieste_view")) {
                $user->user_privileges[] = "investimenti_richieste_view";
            }
            if (!$user->hasPrivilege("investimenti_richieste_edit")) {
                $user->user_privileges[] = "investimenti_richieste_edit";
            }
        }
    }
}

//privilegi relativi alla struttura aziendale
//responsabile della direzione generale (cdr radice)
//estrazione del piano cdr
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");
//recupero del cdr

$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
$cdr_radice = $piano_cdr->getCdrRadice();
$responsabile_cdr_radice = $cdr_radice->getResponsabile($dateTimeObject);
if ($responsabile_cdr_radice->matricola_responsabile == $user->matricola_utente_selezionato){
    if (!$user->hasPrivilege("investimenti_view")) {
        $user->user_privileges[] = "investimenti_view";
    }
    if (!$user->hasPrivilege("investimenti_linee_guida_edit")) {
        $user->user_privileges[] = "investimenti_linee_guida_edit";
    }
    if (!$user->hasPrivilege("investimenti_piano_parere_edit")) {
        $user->user_privileges[] = "investimenti_piano_parere_edit";
    }
}
//vengono verificati i privilegi in base ai cdr di responsabilità nell'anno dell'utente
$responsabile = Personale::factoryFromMatricola($user->matricola_utente_selezionato);
foreach($responsabile->getCdrResponsabilitaPiano($piano_cdr, $dateTimeObject) as $cdr_resp) {
    $cdr_inv = new CdrInvestimenti($cdr_resp["cdr"]->id);

    if ($cdr_inv->isDirezioneRiferimentoAnno($anno)) {
        if (!$user->hasPrivilege("investimenti_view")) {
            $user->user_privileges[] = "investimenti_view";
        }
        if (!$user->hasPrivilege("investimenti_approvazione_view")) {
            $user->user_privileges[] = "investimenti_approvazione_view";
        }
    }
    if ($cdr_inv->isCdrBilancioAnno($anno)) {
        if (!$user->hasPrivilege("investimenti_view")) {
            $user->user_privileges[] = "investimenti_view";
        }
        if (!$user->hasPrivilege("investimenti_istruttoria_view")) {
            $user->user_privileges[] = "investimenti_istruttoria_view";
        }
        if (!$user->hasPrivilege("investimenti_istruttoria_bilancio_edit")) {
            $user->user_privileges[] = "investimenti_istruttoria_bilancio_edit";
        }
    }
    if ($cdr_inv->isDipartimentoAmministrativoAnno($anno)) {
        if (!$user->hasPrivilege("investimenti_view")) {
            $user->user_privileges[] = "investimenti_view";
        }
        if (!$user->hasPrivilege("investimenti_istruttoria_view")) {
            $user->user_privileges[] = "investimenti_istruttoria_view";
        }
        if (!$user->hasPrivilege("investimenti_istruttoria_dip_amm_edit")) {
            $user->user_privileges[] = "investimenti_istruttoria_dip_amm_edit";
        }
    }
    //se un cdr ha almeno una categoria di competenza significa che è una uoc competente
    if(count($cdr_inv->getCategorieCompetenzaAnno($anno))>0){
        if (!$user->hasPrivilege("investimenti_view")) {
            $user->user_privileges[] = "investimenti_view";
        }
        if (!$user->hasPrivilege("investimenti_istruttoria_view")) {
            $user->user_privileges[] = "investimenti_istruttoria_view";
        }
    }
}

// Creazione variabili di supporto per verifica privilegi utente
$investimenti_view = $user->hasPrivilege("investimenti_view");
$richieste_view = $investimenti_view && $user->hasPrivilege("investimenti_richieste_view");
$approvazione_view = $investimenti_view && $user->hasPrivilege("investimenti_approvazione_view");
$istruttoria_view = $investimenti_view && $user->hasPrivilege("investimenti_istruttoria_view");

$allowed_actions = array();
$menu["investimenti"] = array(
    "key"     => "investimenti",
    "label"   => "Investimenti",
    "icon"	  => "",
    "path"    => "",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$investimenti_view,
);
mod_restricted_add_menu_child($menu["investimenti"]);

$allowed_actions = array();
$allowed_actions["investimenti_view"] =  array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/cruscotto?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),
    "icon" => $investimenti_view ? "pie-chart" : MODULES_ICONHIDE,
    "dialog" => false
);

$menu["investimenti"]["indicazioni"] = array(
    "key"     => "investimenti",
    "subkey"  => "indicazioni",
    "label"   => "Indicazioni",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path,
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$investimenti_view,
);
mod_restricted_add_menu_sub_element($menu["investimenti"]["indicazioni"]);

//Visualizzazione delle voci di menu in base ai privilegi dell'utente
$allowed_actions = array();
$menu["investimenti"]["richieste"] = array(
    "key"     => "investimenti",
    "subkey"  => "richieste",
    "label"   => "Richieste",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/richieste",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$richieste_view,
);
mod_restricted_add_menu_sub_element($menu["investimenti"]["richieste"]);

$allowed_actions = array();
$menu["investimenti"]["approvazione"] = array(
    "key"     => "investimenti",
    "subkey"  => "approvazione",
    "label"   => "Approvazione",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/approvazione",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$approvazione_view,
);
mod_restricted_add_menu_sub_element($menu["investimenti"]["approvazione"]);

$allowed_actions = array();
$menu["investimenti"]["istruttoria"] = array(
    "key"     => "investimenti",
    "subkey"  => "istruttoria",
    "label"   => "Istruttoria",
    "icon"	  => "",
    "path"	  => "/area_riservata".$module->site_path."/istruttoria",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$istruttoria_view,
);
mod_restricted_add_menu_sub_element($menu["investimenti"]["istruttoria"]);
    
$allowed_actions = array();
$menu["investimenti"]["piano_investimenti"] = array(
    "key"     => "investimenti",
    "subkey"  => "piano_investimenti",
    "label"   => "Piano Investimenti",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path."/piano_investimenti",
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$investimenti_view,
);
mod_restricted_add_menu_sub_element($menu["investimenti"]["piano_investimenti"]);
