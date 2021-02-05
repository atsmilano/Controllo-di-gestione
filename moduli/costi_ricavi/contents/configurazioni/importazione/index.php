<?php
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))))."importazione";
$download_url = FF_SITE_PATH . $path_info . "/download_tracciato?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
$upload_url = FF_SITE_PATH . $path_info . "/upload_tracciato?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . "/tpl");
$tpl->load_file("importazione.html", "main");

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

$tpl->set_var("download_tracciato_url", $download_url);
$tpl->parse("DownloadTracciato", false);

$tpl->set_var("upload_tracciato_url", $upload_url);
$tpl->parse("UploadTracciato", false);

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl->rpparse("main", true));