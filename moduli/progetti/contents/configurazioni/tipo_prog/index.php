<?php
$grid_fields = array(
    "ID_tipo_progetto",
    "codice",
    "descrizione"
);

$grid_recordset = array();
foreach (ProgettiTipoProgetto::getAll() as $item) {
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipo-progetto";
$oGrid->title = "Tipo Progetto";
$oGrid->resources[] = "tipo-progetto";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "progetti_tipo_progetto"
);
$oGrid->order_default = "ID_tipo_progetto";
$oGrid->record_id = "tipo-progetto-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_tipo_progetto";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_progetto";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_tipo_progetto"]->value->getValue();
    $item = new ProgettiTipoProgetto($id);
    $oGrid->display_delete_bt = $item->canDelete();
}