<?php
$user = LoggedUser::getInstance();

$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"];
/*
Ruoli:
"fabbisogno_admin"
"fabbisogno_operatore_formazione"
"fabbisogno_referente_cdr"
"fabbisogno_responsabile_scientifico_anno"
"fabbisogno_segreteria_organizzativa_anno"
"fabbisogno_responsabile_cdr"
*/
//fabbisogno_admin
foreach ($user->user_groups as $group) {
    if ($group == 1 || $group == 2 || $group == 3){
        $user->user_privileges[] = "fabbisogno_admin";
    }
}

$personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato)->cloneAttributesToNewObject("FabbisognoFormazione\Personale");
//fabbisogno operatore formazione
if ($personale->isOperatoreFormazioneInData($data_riferimento)) {
    if (!$user->hasPrivilege("fabbisogno_operatore_formazione")) {
        $user->user_privileges[] = "fabbisogno_operatore_formazione";
    }
}
//fabbisogno referente cdr
if ($personale->isReferenteCdrInData($data_riferimento)) {
    if (!$user->hasPrivilege("fabbisogno_referente_cdr")) {
        $user->user_privileges[] = "fabbisogno_referente_cdr";
    }
}
//fabbisogno referente di un cdr con referente assegnato
if ($personale->isResponsabileCdrReferenteInData($data_riferimento)) {
    if (!$user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {
        $user->user_privileges[] = "fabbisogno_responsabile_cdr_referente";
    }
}

foreach (\FabbisognoFormazione\Richiesta::getAll(array("ID_anno_budget"=>$anno->id)) as $richiesta_anno) {
    //"fabbisogno responsabile scientifico anno"
    if ($richiesta_anno->matricola_responsabile_scientifico == $user->matricola_utente_selezionato){
        if (!$user->hasPrivilege("fabbisogno_responsabile_scientifico_anno")) {
            $user->user_privileges[] = "fabbisogno_responsabile_scientifico_anno";
        }
    }
    //"fabbisogno segreteria organizzativa anno"
    if ($richiesta_anno->matricola_referente_segreteria == $user->matricola_utente_selezionato){       
        if (!$user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno")) {
            $user->user_privileges[] = "fabbisogno_segreteria_organizzativa_anno";
        }
    }
    
    //fabbisogno responsabile cdr
    if ($cdr!== null && ($user->hasPrivilege("resp_cdr_selezionato") || $user->hasPrivilege("resp_padre_ramo_cdr_selezionato")) && $richiesta_anno->codice_cdr = $cdr->codice){
        if (!$user->hasPrivilege("fabbisogno_responsabile_cdr")) {
            $user->user_privileges[] = "fabbisogno_responsabile_cdr";
        }        
    }
}

//generazione menu
$allowed_actions = array();
$allowed_actions["gestione"] = array(
    "path" => FF_SITE_PATH . "/area_riservata".$module->site_path."/gestione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST),    
    "icon"   => ($user->hasPrivilege("fabbisogno_admin")||$user->hasPrivilege("fabbisogno_operatore_formazione"))?"table":MODULES_ICONHIDE,
    "dialog" => false
);

//visibilità solo per admin e per chi è coinvolto nel processo
$view_fabbisogno = false;
if($user->hasPrivilege("fabbisogno_admin") 
        || $user->hasPrivilege("fabbisogno_operatore_formazione")
        || $user->hasPrivilege("fabbisogno_referente_cdr")
        || $user->hasPrivilege("fabbisogno_responsabile_cdr_referente")
        || $user->hasPrivilege("fabbisogno_responsabile_scientifico_anno") 
        || $user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno")
        || $user->hasPrivilege("fabbisogno_responsabile_cdr")
        ) {
    $view_fabbisogno = true;
}

$menu["fabbisogno-formazione"] = array(
    "key"     => "fabbisogno-formativo",
    "label"   => "Fabbisogno<br>formativo",
    "icon"	  => "",
    "path"    => "/area_riservata".$module->site_path,
    "actions" => $allowed_actions,
    "acl"	  => "1,2,3",
    "hide"    => !$view_fabbisogno,
);
mod_restricted_add_menu_child($menu["fabbisogno-formazione"]);