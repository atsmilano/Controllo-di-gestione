<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array		
$db = ffDb_Sql::factory();
$source_sql = "";

//vengono estratti tutte le categorie
foreach (AnagraficaCdr::getAll() as $anagrafica_cdr) {
    if (strlen($source_sql)) {
        $source_sql .= "UNION ";
    }
    
    $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);

    $source_sql .= "SELECT			
        " . $db->toSql($anagrafica_cdr->id) . " AS ID,
        " . $db->toSql($anagrafica_cdr->codice) . " AS codice,
        " . $db->toSql($anagrafica_cdr->descrizione) . " AS descrizione,
        " . $db->toSql($anagrafica_cdr->abbreviazione) . " AS abbreviazione,
        " . $db->toSql($anagrafica_cdr->id_tipo_cdr) . " AS ID_tipo_cdr,
        " . $db->toSql($tipo_cdr->descrizione ." (" . $tipo_cdr->abbreviazione . ")") ." AS descrizione_tipo_cdr,
        " . $db->toSql($anagrafica_cdr->data_introduzione) . " AS data_introduzione,
        " . $db->toSql($anagrafica_cdr->data_termine) . " AS data_termine
    ";
}

//visualizzazione della grid delle categorie
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "anagrafica-cdr";
$oGrid->title = "Anagrafe dei CdR";
$oGrid->resources[] = "anagrafica-cdr";
if (strlen($source_sql) > 0) {
    $oGrid->source_SQL = "	
        SELECT *
        FROM (" . $source_sql . ") AS anagrafica_cdr
        [WHERE]
        [HAVING]
        [ORDER]
    ";
} else {
    $oGrid->source_SQL .= "
        SELECT			
            '' AS ID,
            '' AS codice,
            '' AS descrizione,
            '' AS abbreviazione,
            '' AS ID_tipo_cdr,
            '' AS descrizione_tipo_cdr,
            '' AS data_introduzione,
            '' AS data_termine
        FROM anagrafica_cdr
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oGrid->order_default = "ID_tipo_cdr";
$oGrid->record_id = "anagrafica-cdr-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "anagrafe_cdr_modify";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
//**************************************************************************
$oGrid->display_new = true;
$oGrid->display_delete_bt = true;
$oGrid->display_search = true;
$oGrid->addEvent("on_before_parse_row", "checkEliminabile");

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
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
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_cdr";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_tipo_cdr";
$oField->base_type = "Text";
$oField->label = "Tipologia CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_introduzione";
$oField->base_type = "Date";
$oField->label = "Data introduzione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine";
$oField->base_type = "Date";
$oField->label = "Data termine";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkEliminabile($oGrid) {
    $oGrid->display_delete_bt = empty(PianoCdr::getPianiCdrCodice($oGrid->grid_fields["codice"]->value->getValue(), "Cdr"));
}