<?php
$user = LoggedUser::Instance();
//verifica privilegi utente
if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei periodi di rendicontazione.");
}

$anno = $cm->oPage->globals["anno"]["value"];

//******************************************************************************
//popolamento della grid tramite array		
$grid_fields = array(
    "ID",
    "descrizione", 
    "data_riferimento_inizio",
    "data_riferimento_fine",
    "ordinamento_anno",
    "allegati",
    "campo_revisione",
    "data_termine_responsabile",
);

$grid_recordset = array();
foreach (ObiettiviPeriodoRendicontazione::getAll(array("ID_anno_budget" => $anno->id)) as $periodo_rendicontazione) {
    if ($periodo_rendicontazione->id_campo_revisione != null){
        $campo_revisione = new ObiettiviCampoRevisione($periodo_rendicontazione->id_campo_revisione);
    }     
        
    $grid_recordset[] = array(
        $periodo_rendicontazione->id, 
        $periodo_rendicontazione->descrizione,
        $periodo_rendicontazione->data_riferimento_inizio,
        $periodo_rendicontazione->data_riferimento_fine,
        $periodo_rendicontazione->ordinamento_anno,
        $periodo_rendicontazione->allegati==1?"Si":"No",
        $periodo_rendicontazione->id_campo_revisione!=null?$campo_revisione->nome:"Nessuno",
        $periodo_rendicontazione->data_termine_responsabile,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "periodi_rendicontazione";
$oGrid->title = "Periodi rendicontazione";
$oGrid->resources[] = "periodo-rendicontazione";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, 
    $grid_recordset, 
    "obiettivi_periodo_rendicontazione"
);
$oGrid->order_default = "ordinamento_anno";
$oGrid->record_id = "periodo-rendicontazione-modify";
$oGrid->order_method = "labels";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "/periodi_rendicontazione_modify";
$oGrid->full_ajax = true;
$oGrid->display_delete_bt = true;
$oGrid->display_search = false;
$oGrid->addEvent("on_before_parse_row", "checkCanDelete");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento_anno";
$oField->base_type = "Number";
$oField->label = "Ordinamento anno";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio periodo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data fine periodo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "allegati";
$oField->base_type = "Text";
$oField->label = "Allegati consentiti";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "campo_revisione";
$oField->base_type = "Text";
$oField->label = "Campo revisione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_termine_responsabile";
$oField->base_type = "Date";
$oField->label = "Data termine rendicontazione";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkCanDelete($oGrid) {
    $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($oGrid->key_fields["ID"]->value->getValue());
    $oGrid->display_delete_bt = $periodo_rendicontazione->canDelete();
}