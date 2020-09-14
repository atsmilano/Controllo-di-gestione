<?php
CoreHelper::includeJqueryUi();
$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir.DIRECTORY_SEPARATOR."tpl");
$tpl->load_file("elenchi_no_associazione.html", "main");

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl);