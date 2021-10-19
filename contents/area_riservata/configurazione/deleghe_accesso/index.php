<?php
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("deleghe_admin")){
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$grid_fields = array(
    "ID",
    "delegante", 
    "delegato",
    "moduli_delega"
);

$grid_recordset = array();
foreach (DelegaAccesso::getAll() as $delega) {
    $delegante = Personale::factoryFromMatricola($delega->matricola_utente);
    $delegato = Personale::factoryFromMatricola($delega->matricola_delegato);
    $moduli_delega = "";
    foreach ($delega->getModuliDelega() as $modulo_delega){	        
        $moduli_delega .= $modulo_delega->dir_path."\n";
    }	
    $grid_recordset[] = array(
        $delega->id, 
        $delegante->cognome." ".$delegante->nome." (".$delegante->matricola.")",        
        $delegato->cognome." ".$delegato->nome." (".$delegato->matricola.")",
        $moduli_delega,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "delega";
$oGrid->title = "Deleghe accesso";
$oGrid->resources[] = "delega";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "delega_accesso"
);
$oGrid->order_default = "delegante";
$oGrid->record_id = "delega-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_delega";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_search = false;
$oGrid->use_search = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "delegante";
$oField->base_type = "Text";
$oField->label = "Delegante";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "delegato";
$oField->base_type = "Text";
$oField->label = "Delegato";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "moduli_delega";
$oField->base_type = "Text";
$oField->label = "Moduli delega";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);