<?php
$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("estrazioni.html", "main");

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//selezione periodo
foreach(ValutazioniPeriodo::getAll() as $periodo){
	$data_inizio = new DateTime($periodo->data_inizio);	
	$tpl->set_var("periodo_id", $periodo->id);
	$tpl->set_var("periodo_descrizione", $periodo->descrizione);
	$tpl->set_var("periodo_data_inizio", $data_inizio->format("d/m/Y"));			
	$tpl->parse("SectOptionPeriodi", true);	
}

$cm->oPage->addContent($tpl);