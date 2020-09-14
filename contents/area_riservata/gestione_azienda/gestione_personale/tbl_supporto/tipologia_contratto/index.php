<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array
$grid_fields = array(
    "id_tipo_contratto",
    "descrizione"
);
$grid_recordset = array();
foreach(TipoContratto::getAll() as $tipo_contratto) {
    
    $grid_recordset[] = array(
        $tipo_contratto->id,
        $tipo_contratto->descrizione
    );
}

//visualizzazione della grid delle anagrafiche dei responsabili
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "tipo-contratto";
$oGrid->title = "Tipologia contratto";
$oGrid->resources[] = "tipo-contratto";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "tipo_contratto");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "tipo-contratto-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_tipo_contratto";
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
$oField->id = "id_tipo_contratto";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkEliminabile($oGrid) {
    $id_tipo_contratto = $oGrid->key_fields["id_tipo_contratto"]->value->getValue();
    $carriera_personale_list = CarrieraPersonale::getAll(array("ID_tipo_contratto" => $id_tipo_contratto));

    if (empty($carriera_personale_list)) {
        $oGrid->display_delete_bt = true;
    } else {
        $oGrid->display_delete_bt = false;
    }
}