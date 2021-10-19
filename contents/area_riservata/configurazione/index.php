<?php
CoreHelper::includeJqueryUi();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory((__DIR__) . "/tpl");
$tpl->load_file("configurazione.html", "main");

$user = LoggedUser::getInstance();

if ($user->hasPrivilege("anni_budget_admin")){
    $tpl->parse("SectAnniBudget", false);
}
if ($user->hasPrivilege("moduli_admin")) {
    $tpl->parse("SectModuli", false);
}

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl);