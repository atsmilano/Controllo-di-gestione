<?php
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID_cdr_prog_strategica",
    "codice_cdr",
    "anno_inizio",
    "anno_fine"
);

$grid_recordset = array();
foreach (StrategiaCdrProgrammazioneStrategica::getAll() as $item) {
    $cdr = AnagraficaCdr::factoryFromCodice($item->codice_cdr, $date);
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice_cdr ." - ".$cdr->descrizione,
        $item->anno_inizio,
        $item->anno_fine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "strategia-cdr-prog-strategica";
$oGrid->title = "CdR Programmazione Strategica";
$oGrid->resources[] = "cdr-prog-strategica";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "strategia_cdr_programmazione_strategica"
);
$oGrid->order_default = "codice_cdr";
$oGrid->record_id = "cdr-prog-strategica-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_cdr_prog_strat";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->use_paging = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdr_prog_strategica";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->label = "Anno inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";
$oField->label = "Anno fine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);