<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");

if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze dei profili per il CdR.");		
}

if (isset($_REQUEST["keys[ID_profilo]"])) {
    try {
        $profilo = new MappaturaCompetenze\Profilo($_REQUEST["keys[ID_profilo]"]);
        $found = false;
        foreach ($cdr->getProfiliResponsabile($user->matricola_utente_selezionato) as $profilo_cdr) {
            if ($profilo->id == $profilo_cdr->id){
                $found = true;
                break;
            }            
        }
        if ($found == false) {
            throw new Exception(
                "Errore nel passaggio dei parametri: profilo non previsto per il CdR e responsabile."
            );
        }        
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}

$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("tabella_mappatura.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("id_profilo", $profilo->id);

//viene estratto l'array di valori attesi selezionabili
$valori_attesi = $profilo->getValoriAssegnabili();

$tpl->set_var("colspan_sezione", 3);

//competenze trasversali
foreach ($profilo->getCompetenzeTrasversaliProfilo() as $competenza_trasversale_profilo) {
    $competenza_trasversale = new \MappaturaCompetenze\CompetenzaTrasversale($competenza_trasversale_profilo->id_competenza_trasversale);
    $tpl->set_var("id_competenza", "competenza_trasversale_".$competenza_trasversale->id);
    $tpl->set_var("nome_competenza", $competenza_trasversale->nome);
    $tpl->set_var("descrizione_competenza", $competenza_trasversale->descrizione);
    
    foreach ($valori_attesi as $valore_atteso) {
        $tpl->set_var("valore_atteso_id", $valore_atteso->id);
        if ($competenza_trasversale_profilo->id_valore_atteso == $valore_atteso->id ) {
            $tpl->set_var("valore_atteso_selected", "selected");
        }
        else {
            $tpl->set_var("valore_atteso_selected", "");
        }
        $tpl->set_var("valore_atteso_valore", $valore_atteso->valore);
        $tpl->set_var("valore_atteso_descrizione", $valore_atteso->descrizione);
        $tpl->parse("SectOptionsValoriAttesi", true);
    }        
    $tpl->parse("SectValoriAttesiEdit", true);
    $tpl->parse("SectCompetenza", true);
    $tpl->set_var("SectOptionsValoriAttesi", null);
    $tpl->set_var("SectValoriAttesiEdit", null);
}
$tpl->set_var("nome_sezione", "Competenze trasversali");
$tpl->parse("SectSezione", true);
$tpl->set_var("SectCompetenza", null);

//competenze specifiche
foreach ($profilo->getCompetenzeSpecificheProfilo() as $competenza_specifica_profilo) {
    $competenza_specifica = new \MappaturaCompetenze\CompetenzaSpecifica($competenza_specifica_profilo->id_competenza_specifica);
    $tpl->set_var("id_competenza", "competenza_specifica_".$competenza_specifica_profilo->id_competenza_specifica);
    $tpl->set_var("nome_competenza", $competenza_specifica->nome);
    $tpl->set_var("descrizione_competenza", $competenza_specifica->descrizione);
    
    foreach ($valori_attesi as $valore_atteso) {
        $tpl->set_var("valore_atteso_id", $valore_atteso->id);
        if ($competenza_specifica_profilo->id_valore_atteso == $valore_atteso->id ) {
            $tpl->set_var("valore_atteso_selected", "selected");
        }
        else {
            $tpl->set_var("valore_atteso_selected", "");
        }
        $tpl->set_var("valore_atteso_valore", $valore_atteso->valore);
        $tpl->set_var("valore_atteso_descrizione", $valore_atteso->descrizione);
        $tpl->parse("SectOptionsValoriAttesi", true);
    }        
    $tpl->parse("SectValoriAttesiEdit", true);
    $tpl->parse("SectCompetenza", true);
    $tpl->set_var("SectOptionsValoriAttesi", null);
    $tpl->set_var("SectValoriAttesiEdit", null);
}
$tpl->set_var("nome_sezione", "Competenze specifiche");
$tpl->parse("SectSezione", true);
$tpl->set_var("SectCompetenza", null);

$tpl->parse("SectValoriAttesiActions", true);

$cm->oPage->addContent($tpl);