<?php
$db = ffDb_Sql::factory();
$user = LoggedUser::getInstance();

$date = $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d");

if (isset ($_REQUEST["keys[ID]"])) {
    try {        
        $progetto = new ProgettiProgetto($_REQUEST["keys[ID]"]);
    }
    catch (Exception $ex){
        ffErrorHandler::raise("Errore nel passaggio dei parametri");
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "progetto-partner-interni";
$oRecord->title = "Partner interni";
$oRecord->resources[] = "progetto-partner-interni";
$oRecord->src_table  = "progetti_progetto_partner_interni";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_progetti_progetto_partner_interni";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

// Elenco dei CdR
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $date);
$cdr_radice_piano = $piano_cdr->getCdrRadice();
$cdr_anno = $cdr_radice_piano->getGerarchia();
$autocomplete_sql_source = "";
foreach ($cdr_anno AS $obj_cdr) {
    $cdr = $obj_cdr["cdr"];
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    if (strlen($autocomplete_sql_source) > 0) {
        $autocomplete_sql_source .= " UNION ";
    }

    $autocomplete_sql_source .= "
        SELECT ".$db->toSql($cdr->codice)." AS codice_cdr,                         
            ".$db->toSql($cdr->codice." - ".$tipo_cdr->abbreviazione." ".$cdr->descrizione)." AS cdr,
            ".$db->toSql($tipo_cdr->abbreviazione)." AS tipo_cdr,
            ".$db->toSql($cdr->descrizione)." AS descrizione_cdr
    ";
}
$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->data_source = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->label = "CDR - Partner interno";

if (strlen($autocomplete_sql_source) > 0) {
    $oField->source_SQL = "
        SELECT *
        FROM (". $autocomplete_sql_source .") AS cdr
        [WHERE]
        [HAVING]
        ORDER BY tipo_cdr, descrizione_cdr
    ";
}
else {
    $oField->source_SQL = "
        SELECT '' AS codice_cdr,
            '' AS cdr,
            '' AS tipo_cdr,
            '' AS descrizione_cdr
        FROM cdr
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oField->required = true;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_progetto"] = new ffData($progetto->id, "Number");

$cm->oPage->addContent($oRecord);