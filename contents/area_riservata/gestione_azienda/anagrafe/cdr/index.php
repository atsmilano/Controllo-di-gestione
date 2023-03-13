<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array		
$db = ffDb_Sql::factory();

$grid_fields = array(
    "ID",
    "codice",
    "descrizione",    
    "abbreviazione",
    "ID_tipo_cdr",
    "descrizione_tipo_cdr",
    "data_introduzione",
    "data_termine",
);

$grid_recordset = array();
foreach (AnagraficaCdr::getAll() as $anagrafica_cdr) {
    $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
    $grid_recordset[] = array(
        $anagrafica_cdr->id,
        $anagrafica_cdr->codice,
        $anagrafica_cdr->descrizione,
        $anagrafica_cdr->abbreviazione,
        $anagrafica_cdr->id_tipo_cdr,
        $tipo_cdr->descrizione,
        $anagrafica_cdr->data_introduzione,
        $anagrafica_cdr->data_termine,
    );
}

//visualizzazione della grid delle categorie
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "anagrafica-cdr";
$oGrid->title = "Anagrafe dei CdR";
$oGrid->resources[] = "anagrafica-cdr";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "anagrafica_cdr");
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