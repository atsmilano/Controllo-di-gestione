<?php
//predisposizione dati per la grid	
//popolamento della grid tramite array
$grid_fields = array(
    "id_ruolo",
    "descrizione"
);
$grid_recordset = array();
foreach(Ruolo::getAll() as $ruolo) {
    
    $grid_recordset[] = array(
        $ruolo->id,
        $ruolo->descrizione
    );
}

//visualizzazione della grid delle anagrafiche dei responsabili
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "ruolo";
$oGrid->title = "Ruolo";
$oGrid->resources[] = "ruolo";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "ruolo");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "ruolo-modify";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_ruolo";
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
$oField->id = "id_ruolo";
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
    $id_ruolo = $oGrid->key_fields["id_ruolo"]->value->getValue();
    $qualifica_interna_list = QualificaInterna::getAll(array("ID_ruolo" => $id_ruolo));
    if (empty($qualifica_interna_list)) {
        $oGrid->display_delete_bt = true;
    } else {
        $oGrid->display_delete_bt = false;
    }
}