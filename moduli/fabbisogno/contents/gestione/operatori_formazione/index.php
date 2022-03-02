<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_admin")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dei referenti formazione.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date->format("Y-m-d"));

$grid_fields = array(
    "ID", 
    "operatore",
    "data_introduzione",
    "data_termine",
);
$grid_recordset = array();
foreach (FabbisognoFormazione\OperatoreFormazione::getAll() as $referente_formazione) {
    $personale = Personale::factoryFromMatricola($referente_formazione->matricola_personale);
    $grid_recordset[] = array(
        $referente_formazione->id,
        $personale->cognome." ".$personale->nome." (matr.".$personale->matricola.")",
        $referente_formazione->data_introduzione,
        $referente_formazione->data_termine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "operatori-formazione";
$oGrid->title = "Operatori Formazione";
$oGrid->resources[] = "operatore";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields,
    $grid_recordset, 
    "fabbisogno_operatore_formazione"
);
$oGrid->order_default = "operatore";
$oGrid->record_id = "dettaglio-operatore-formazione";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_operatore_formazione";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->use_paging = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_operatore_formazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "operatore";
$oField->base_type = "Text";
$oField->label = "Operatore";
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

//$oGrid->addEvent("on_before_parse_row", "checkReferentiCdr");
$cm->oPage->addContent($oGrid);
/*
function checkReferentiCdr($oGrid) {
    $id_area = $oGrid->key_fields["ID_area"]->value->getValue();
    $area = new ObiettiviArea($id_area);
    $oGrid->display_delete_bt = $area->canDelete();
}*/