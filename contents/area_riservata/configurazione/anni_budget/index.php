<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("anni_budget_admin")){
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$grid_fields = array(
    "ID",
    "descrizione", 
    "attivo"
);

$grid_recordset = array();
foreach (AnnoBudget::getAll() as $anno) {
    $grid_recordset[] = array(
        $anno->id, 
        $anno->descrizione,        
        $anno->attivo == true ? "Si" : "No"
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "anno";
$oGrid->title = "Anni Budget";
$oGrid->resources[] = "anno";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "anno_budget"
);
$oGrid->order_default = "descrizione";
$oGrid->record_id = "anno-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_anno";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "attivo";
$oField->base_type = "Text";
$oField->label = "Attivo";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);