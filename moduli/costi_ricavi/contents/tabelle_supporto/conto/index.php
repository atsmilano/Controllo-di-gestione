<?php
$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID_conto",
    "codice",
    "descrizione",
    "fattore_produttivo",
    "codice_cdr",
    "evidenza",
    "anno_inizio",
    "anno_fine"
);

$grid_recordset = array();
foreach (CostiRicaviConto::getAll() as $item) {
    $fp = new CostiRicaviFp($item->id_fp);
    $cdr = AnagraficaCdr::factoryFromCodice($item->codice_cdr, $date);
    
    if ($item->evidenza === '0') {
        $evidenza = "No";
    }
    else if ($item->evidenza === '1') {
        $evidenza = "Sì";
    }
    else {
        $evidenza = "";
    }  
    
    $grid_recordset[] = array(
        $item->id,
        $item->codice,
        $item->descrizione,
        $fp->codice." ".$fp->descrizione,
        $item->codice_cdr ." - ".$cdr->descrizione,
        $evidenza,
        $item->anno_inizio,
        $item->anno_fine
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "conto";
$oGrid->title = "Conto";
$oGrid->resources[] = "conto";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "costi_ricavi_conto"
);
$oGrid->order_default = "ID_conto";
$oGrid->record_id = "conto-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_conto";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_conto";
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

$oField = ffField::factory($cm->oPage);
$oField->id = "fattore_produttivo";
$oField->base_type = "Text";
$oField->label = "Fattore produttivo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "evidenza";
$oField->base_type = "Text";
$oField->label = "Evidenza";
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

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID_conto"]->value->getValue();
    $item = new CostiRicaviConto($id);
    $oGrid->display_delete_bt = $item->canDelete();
}