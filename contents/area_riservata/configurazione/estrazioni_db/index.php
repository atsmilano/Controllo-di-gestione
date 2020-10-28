<?php
//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory((__DIR__) . "/tpl");
$tpl->load_file("estrazioni_db.html", "main");

$tpl->set_var("images_path", FF_SITE_PATH."/themes/ats/images/");

//campo di selezione della tabella da estrarre
//viene costruito il multipair per la selezione delle tabelle
$tables_select = array();

foreach (Entity::getDbTables() as $table) {
    $tables_select[] = array(new ffData ($table, "Text"),
                            new ffData ($table, "Text")
                        );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "tables";
$oField->label = "Tabella DB";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tables_select;
$oField->multi_select_one = false;
$tpl->set_var("table_selection_field", $oField->process());

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl);