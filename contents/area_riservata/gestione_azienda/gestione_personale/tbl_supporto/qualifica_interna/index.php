<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array
$grid_fields = array(
    "id_qualifica_interna",
    "codice",
    "descrizione",
    "dirigente",
    "id_ruolo",
    "descrizione_ruolo"
);
$grid_recordset = array();
foreach (QualificaInterna::getAll() as $qualifica_interna) {
    $ruolo = new Ruolo($qualifica_interna->id_ruolo);
   
    $grid_recordset[] = array(
        $qualifica_interna->id,
        $qualifica_interna->codice,
        $qualifica_interna->descrizione,
        $qualifica_interna->dirigente == "1" ? "Sì" : "No",
        $qualifica_interna->id_ruolo,
        $ruolo->descrizione
    );
}

//visualizzazione della grid delle anagrafiche dei responsabili
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "qualifica-interna";
$oGrid->title = "Qualifica interna";
$oGrid->resources[] = "qualifica-interna";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "qualifica_interna");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "qualifica-interna-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_qualifica_interna";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->addEvent("on_before_parse_row", "checkEliminabile");

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "id_qualifica_interna";
$oField->base_type = "Number";
$oField->label = "id_qualifica_interna";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice qualifica";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione qualifica";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dirigente";
$oField->base_type = "Text";
$oField->label = "Dirigente";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "id_ruolo";
$oField->base_type = "Number";
$oField->label = "id_ruolo";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_ruolo";
$oField->base_type = "Text";
$oField->label = "Ruolo";
$oGrid->addContent($oField);



// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//verifica sull'eliminabilità del dato
function checkEliminabile($oGrid) {
    $id_qualifica_interna = $oGrid->key_fields["id_qualifica_interna"]->value->getValue();
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_qualifica_interna" => $id_qualifica_interna));

    if (empty($carriera_personale_list)) {
        $oGrid->display_delete_bt = true;
    } else {
        $oGrid->display_delete_bt = false;
    }
}