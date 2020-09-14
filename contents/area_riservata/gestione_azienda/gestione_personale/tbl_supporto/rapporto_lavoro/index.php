<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array
$grid_fields = array(
    "id_rapporto_lavoro",
    "codice",
    "descrizione",
    "part_time"
);
$grid_recordset = array();
foreach (RapportoLavoro::getAll() as $rapporto_lavoro) {
    
    $grid_recordset[] = array(
        $rapporto_lavoro->id,
        $rapporto_lavoro->codice,
        $rapporto_lavoro->descrizione,
        $rapporto_lavoro->part_time == "1" ? "Sì" : "No",
    );
}

//visualizzazione della grid delle anagrafiche dei responsabili
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "rapporto-lavoro";
$oGrid->title = "Rapporto lavoro";
$oGrid->resources[] = "rapporto-lavoro";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "rapporto_lavoro");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "rapporto-lavoro-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_rapporto_lavoro";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
//**************************************************************************
$oGrid->display_new = true;
$oGrid->display_search = true;
$oGrid->use_search = true;
$oGrid->addEvent("on_before_parse_row", "checkEliminabile");

//**************************************************************************
// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "id_rapporto_lavoro";
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
$oField->id = "part_time";
$oField->base_type = "Text";
$oField->label = "Part time";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//verifica sull'eliminabilità del record
function checkEliminabile($oGrid) {
    $id_rapporto_lavoro = $oGrid->key_fields["id_rapporto_lavoro"]->value->getValue();
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_rapporto_lavoro" => $id_rapporto_lavoro));
    if (empty($carriera_personale_list)) {
        $oGrid->display_delete_bt = true;
    } else {
        $oGrid->display_delete_bt = false;
    }
}