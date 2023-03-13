<?php
$user = LoggedUser::getInstance();

if (!$user->hasPrivilege("investimenti_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("investimenti.html", "main");

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//visualizzazione linee guida eventuali dell'anno
$anno_selezionato = $cm->oPage->globals["anno"]["value"];
$anno = new ValutazioniAnnoBudget($anno_selezionato->id);
$tpl->set_var("anno_desc", $anno->descrizione);
$linee_guida_anno = InvestimentiLineeGuidaAnno::factoryFromAnno($anno);
if ($linee_guida_anno !== false) {
    $tpl->set_var("descrizione_linee_guida", $linee_guida_anno->descrizione);
    $tpl->parse("LineeGuida", false);
}
else {
    $tpl->parse("NoLineeGuida", false);
}

if ($user->hasPrivilege("investimenti_linee_guida_edit")) {
    //visualizzazione del link di modifica delle linee guida (se utente abilitato)
    $tpl->set_var("record_url", FF_SITE_PATH . $cm->path_info . "/linee_guida_anno_modify?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."keys[ID]=".$linee_guida_anno->id
                                        ."&ret_url=". rawurlencode($_SERVER["REQUEST_URI"]));
    $tpl->parse("LinkModifica", false);
}

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl);