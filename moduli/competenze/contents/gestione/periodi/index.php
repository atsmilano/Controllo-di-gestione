<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei periodi.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID", 
    "descrizione",    
    "data_riferimento_inizio",
    "data_riferimento_fine",
    "data_termine_responsabile",
);
$grid_recordset = array();
foreach (MappaturaCompetenze\Periodo::getAll() as $periodo) {
    $grid_recordset[] = array(
        $periodo->id,
        $periodo->descrizione,
        $periodo->data_riferimento_inizio,
        $periodo->data_riferimento_fine,
        $periodo->data_termine_responsabile,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodo";
$oGrid->title = "Periodi";
$oGrid->resources[] = "periodo";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "competenze_periodo"
);
$oGrid->order_default = "data_riferimento_fine";
$oGrid->record_id = "periodo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_periodo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data fine";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine_responsabile";
$oField->base_type = "Date";
$oField->label = "Data termine compilazione";
$oGrid->addContent($oField);

//$oGrid->addEvent("on_before_parse_row", "checkPeriodoEliminabile");
$cm->oPage->addContent($oGrid);
/*
function checkPeriodoEliminabile($oGrid) {
    $id_periodo = $oGrid->key_fields["ID_periodo"]->value->getValue();
    $periodo = new MappaturaCompetenze\Periodo($id_periodo);
    $oGrid->display_delete_bt = $periodo->canDelete();
}*/