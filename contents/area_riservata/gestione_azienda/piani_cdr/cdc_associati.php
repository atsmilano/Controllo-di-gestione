<?php
if (isset($_REQUEST["id_cdr"])) {
    $id_cdr = $_REQUEST["id_cdr"];
    try {
        $cdr = new Cdr($id_cdr);
        $piano_cdr = new PianoCdr($cdr->id_piano_cdr);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
} else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: cdr");
}

//CONTROLLI DI COERENZA
//viene verificato che l'id del piano dei cdr corrisponda al piano del cdr padre e nel caso del cdr figlio
if ((int) $id_cdr_padre !== 0 && ($cdr_padre->id_piano_cdr !== $cdr->id_piano_cdr)) {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: piani dei cdr non corrispondenti" . $cdr_padre->id_piano_cdr . " - " . $cdr->id_piano_cdr);
}

//visualizzazione della grid dei cdc associati
$source_sql = "";
$db = ffDb_Sql::factory();
foreach ($cdr->getCdc() as $cdc) {
    if (strlen($source_sql)) {
        $source_sql .= " UNION ";
    }
    $source_sql .= "
        SELECT 
            " . $db->toSql($cdc->id) . " AS ID,
            " . $db->toSql($cdc->codice) . " AS codice,
            " . $db->toSql($cdc->descrizione) . " AS descrizione,
            " . $db->toSql($cdc->abbreviazione) . " AS abbreviazione
    ";
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "cdc";
$oGrid->title = "Centri di costo del Cdr<br>" . $cdr->codice . " - " . $cdr->descrizione;
$oGrid->resources[] = "cdc";
if (strlen($source_sql) > 0) {
    $oGrid->source_SQL = "
        SELECT *
        FROM (" . $source_sql . ") AS cdc                          
        [WHERE]
        [HAVING]
        [ORDER]
    ";
} else {
    $oGrid->source_SQL = "
        SELECT
            '' AS ID,
            '' AS codice,              
            '' AS descrizione,
            '' AS abbreviazione
        FROM cdc
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oGrid->order_default = "descrizione";
$oGrid->record_id = "cdc-modify";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "cdc_modify";
$oGrid->addit_insert_record_param = $oGrid->addit_record_param = "id_cdr=" . $cdr->id . "&";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;

//viene verificato che  il piano sia già stato introdotto,e nel caso vengono impedite aggiunta ed eliminazione
if($piano_cdr->data_introduzione != "") {
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;
}

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "id_cdc";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
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
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Distretto";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);


