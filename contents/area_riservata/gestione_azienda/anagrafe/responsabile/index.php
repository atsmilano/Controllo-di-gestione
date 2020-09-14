<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array
$db = ffDb_Sql::factory();
$source_sql = "";

//vengono estratte tutte le anagrafiche dei responsabili
foreach (ResponsabileCdr::getAll() as $responsabile) {
    if (strlen($source_sql)) {
        $source_sql .= "UNION ";
    }
    $source_sql .= "SELECT			
        " . $db->toSql($responsabile->id) . " AS ID,
        " . $db->toSql($responsabile->cognome . " " . $responsabile->nome . 
            " (matr. ".$responsabile->matricola_responsabile.")") ." AS cognome,
	" . $db->toSql($responsabile->codice_cdr) . " AS codice_cdr,
	" . $db->toSql($responsabile->data_inizio) . " AS data_inizio,
	" . $db->toSql($responsabile->data_fine) . " AS data_fine
    ";
}

//visualizzazione della grid delle anagrafiche dei responsabili
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "anagrafe-responsabili";
$oGrid->title = "Anagrafe dei Responsabili";
$oGrid->resources[] = "anagrafe-responsabile";
if (strlen($source_sql) > 0) {
    $oGrid->source_SQL = "	
        SELECT *
	FROM (" . $source_sql . ") AS responsabile_cdr
        [WHERE]
        [HAVING]
        [ORDER]
    ";
} else {
    $oGrid->source_SQL = "
        SELECT			
            '' AS ID,
            '' AS matricola_responsabile,
            '' AS codice_cdr,
            '' AS data_inizio,
            '' AS data_fine
        FROM responsabile_cdr
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oGrid->order_default = "cognome";

$oGrid->record_id = "responsabile-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "responsabile_modify";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;

$oGrid->display_new = true;
$oGrid->display_delete_bt = true;

$oGrid->display_search = true;

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cognome";
$oField->base_type = "Text";
$oField->label = "Responsabile";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice cdr";
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