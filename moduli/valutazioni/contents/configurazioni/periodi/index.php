<?php
/******************************/
/*           PERIODI          */
/******************************/
$grid_fields = array(
    "id_periodo ",
    "inibizione_visualizzazione_totali",
    "inibizione_visualizzazione_ambiti_totali",
    "inibizione_visualizzazione_data_colloquio",
    "descrizione",
    "data_inizio",
    "data_fine",
);

$grid_recordset = array();
foreach (ValutazioniPeriodo::getAll() as $periodo) {
    $grid_recordset[] = array(
        $periodo->id,
        $periodo->inibizione_visualizzazione_totali==true?"Si":"No",
        $periodo->inibizione_visualizzazione_ambiti_totali==true?"Si":"No",
        $periodo->inibizione_visualizzazione_data_colloquio==true?"Si":"No",
        $periodo->descrizione,
        $periodo->data_inizio,
        $periodo->data_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodi";
$oGrid->title = "Periodi";
$oGrid->resources[] = "periodo";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_periodo");
$oGrid->order_default = "data_inizio";
$oGrid->record_id = "periodo-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_periodo";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "id_periodo";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_totali";
$oField->base_type = "Text";
$oField->label = "Totali nascosti";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_ambiti_totali";
$oField->base_type = "Text";
$oField->label = "Ambiti in totali nascosti";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_data_colloquio";
$oField->base_type = "Text";
$oField->label = "Data colloquio nascosta";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->label = "Data fine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);