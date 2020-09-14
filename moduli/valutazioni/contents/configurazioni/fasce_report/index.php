<?php

/******************************/
/*           FASCE            */
/******************************/
$grid_fields = array(
    "id_fascia",
    "min",
    "max",
    "data_inizio",
    "data_fine"
);

$grid_recordset = array();
foreach (ValutazioniFasciaPunteggio::getAll() as $fascia_punteggio) {
    $grid_recordset[] = array(
        $fascia_punteggio->id,
        $fascia_punteggio->min,
        $fascia_punteggio->max,
        $fascia_punteggio->data_inizio,
        $fascia_punteggio->data_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "fasce_punteggio";
$oGrid->title = "Fasce di punteggio";
$oGrid->resources[] = "fascia_punteggio";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_fascia_punteggio");
$oGrid->order_default = "min";
$oGrid->record_id = "fascia-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . DIRECTORY_SEPARATOR . "dettaglio_fascia";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "id_fascia";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "min";
$oField->base_type = "Number";
$oField->label = "Punteggio minimo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "max";
$oField->base_type = "Number";
$oField->label = "Punteggio massimo";
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
$oGrid->addEvent("on_before_parse_row", "checkEliminabile");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkEliminabile($oGrid) {
    $oGrid->display_delete_bt = ValutazioniFasciaPunteggio::isFasciaEliminabile();
}