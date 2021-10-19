<?php
/*******************************/
/*           SEZIONI           */
/*******************************/
$grid_fields = array(
    "ID_sezione",
    "codice",
    "descrizione",
);

$grid_recordset = array();
foreach (ValutazioniSezione::getAll() as $sezione) {

    $grid_recordset[] = array(
        $sezione->id,
        $sezione->codice,
        $sezione->descrizione,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "sezioni";
$oGrid->title = "Sezioni";
$oGrid->resources[] = "sezione";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_sezione");
$oGrid->order_default = "codice";
$oGrid->record_id = "sezione-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_sezione";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_sezione";
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

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

/******************************/
/*           AMBITI           */
/******************************/
$grid_fields = array(
    "ID_ambito",
    "sezione",
    "codice",
    "descrizione",    
    "anno_inizio",
    "anno_fine",
);

$grid_recordset = array();
foreach (ValutazioniAmbito::getAll() as $ambito) {
    $sezione_ambito = new ValutazioniSezione($ambito->id_sezione);

    $grid_recordset[] = array(
        $ambito->id,
        $sezione_ambito->codice,
        $ambito->codice,
        $ambito->descrizione,       
        $ambito->anno_inizio,
        $ambito->anno_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "ambiti";
$oGrid->title = "Ambiti";
$oGrid->resources[] = "ambito";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_ambito");
$oGrid->order_default = "codice";
$oGrid->record_id = "ambito-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_ambito";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_ambito";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sezione";
$oField->base_type = "Text";
$oField->label = "Sezione";
$oGrid->addContent($oField);

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
$oField->id = "anno_inizio";
$oField->base_type = "Text";
$oField->label = "Anno inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Text";
$oField->label = "Anno fine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

/******************************/
/*          CATEGORIE         */
/******************************/
$grid_fields = array(
    "ID_categoria",
    "abbreviazione",
    "descrizione",
    "anno_inizio",
    "anno_fine"
);
$grid_recordset = array();
foreach (ValutazioniCategoria::getAll() as $categoria) {
    $grid_recordset[] = array(
        $categoria->id,
        $categoria->abbreviazione,
        $categoria->descrizione,
        $categoria->anno_inizio,
        $categoria->anno_fine,
    );
}

//visualizzazione della grid delle categorie
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "categorie";
$oGrid->title = "Tipologie scheda";
$oGrid->resources[] = "categoria";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_categoria");
$oGrid->order_default = "abbreviazione";
$oGrid->record_id = "categoria-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_categoria";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************

// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_categoria";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Text";
$oField->label = "Anno inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Text";
$oField->label = "Anno fine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

/******************************/
/*           TOTALE           */
/******************************/
$grid_fields = array(
    "ID_totale",
    "descrizione",
    "categorie",
    "ambiti",
    "anno_inizio",
    "anno_fine"
);
$grid_recordset = array();
foreach (ValutazioniTotale::getAll() as $totale) {

    $grid_recordset[] = array(
        $totale->id,
        $totale->descrizione,
        ValutazioniHelper::glueDescrizioni($totale->getCategorieTotale(), "\n"),
        ValutazioniHelper::glueDescrizioniAmbiti($totale->getAmbitiTotale(), "\n", true),
        $totale->anno_inizio,
        $totale->anno_fine,
    );
}

//visualizzazione della grid delle categorie
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "totali";
$oGrid->title = "Totale";
$oGrid->resources[] = "totale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_totale");
$oGrid->order_default = "ID_totale";
$oGrid->record_id = "totale-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_totale";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_totale";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "categorie";
$oField->base_type = "Text";
$oField->label = "Tipologie scheda";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ambiti";
$oField->base_type = "Text";
$oField->label = "Ambiti";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Text";
$oField->label = "Anno inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Text";
$oField->label = "Anno fine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//function checkAmbitoEliminabile($oGrid) {
//    $id_ambito = $oGrid->key_fields["ID_ambito"]->value->getValue();
//    $ambito = new ValutazioniAmbito($id_ambito);
//    $oGrid->display_delete_bt = $ambito->canDelete();
//}
//
//function checkSezioneEliminabile($oGrid) {
//    $id_sezione = $oGrid->key_fields["ID_sezione"]->value->getValue();
//    $sezione = new ValutazioniSezione($id_sezione);
//    $oGrid->display_delete_bt = $sezione->canDelete();
//}
//
//function checkCategoriaEliminabile($oGrid) {
//    $id_categoria = $oGrid->key_fields["ID_categoria"]->value->getValue();
//    $categoria = new ValutazioniCategoria($id_categoria);
//    $oGrid->display_delete_bt = $categoria->canDelete();
//}
//
//function checkTotaleEliminabile($oGrid) {
//    $id_totale = $oGrid->key_fields["ID_totale"]->value->getValue();
//    $totale = new ValutazioniTotale($id_totale);
//    $oGrid->display_delete_bt = $totale->canDelete();
//}

