<?php
$anno_budget = $cm->oPage->globals["anno"]["value"];

$grid_fields = array(
    "ID_personale",
    "cognome", "nome", "matricola_responsabile"
);

$grid_recordset = array();
foreach(ResponsabileCdr::getResponsabiliCdrCessatiInAnno($anno_budget) as $item) {
    $grid_recordset[] = array(
        $item->id_personale,
        $item->cognome, $item->nome, $item->matricola_responsabile
    );    
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "responsabili-cessati";
$oGrid->title = "Elenco responsabili cessati nell'anno";
$oGrid->resources[] = "responsabili-cessati";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "responsabile_cdr"
);
$oGrid->order_default = "cognome";
$oGrid->record_id = "responsabili-cessati-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_resp_cessati";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_paging = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_personale";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cognome";
$oField->base_type = "Text";
$oField->label = "Cognome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile";
$oField->base_type = "Text";
$oField->label = "Matricola";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);