<?php
use scadenze\AbilitazioneCdr;

$date = $cm->oPage->globals["data_riferimento"]["value"];

$grid_fields = array(
    "ID",
    "cdr",
    "data_riferimento_inizio",
    "data_riferimento_fine",
    "contatti_mail"
);

$grid_recordset = array();
foreach (AbilitazioneCdr::getAll() as $abilitazione) {
    $anagrafica_cdr = AnagraficaCdr::factoryFromCodice($abilitazione->codice_cdr, $date);
    if ($anagrafica_cdr !== null) {        
        $anagrafica_desc = $anagrafica_cdr->getDescrizioneEstesa();
    }
    else {
        $anagrafica_desc = $abilitazione->codice_cdr . (" (inattivo al ".$date->format("d/m/Y").")");
    }
    
    $contatti_mail = "";    
    foreach ($abilitazione->getContattiMail() as $mail) {
        $contatti_mail .= " " . $mail->mail . "\n";
    }
        
    $grid_recordset[] = array(
        $abilitazione->id,
        $anagrafica_desc,
        $abilitazione->data_riferimento_inizio,
        $abilitazione->data_riferimento_fine,
        $contatti_mail,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "abilitazioni-cdr";
$oGrid->title = "Abilitazioni Cdr";
$oGrid->resources[] = "abilitazione";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "scadenze_abilitazione_cdr"
);
$oGrid->order_default = "cdr";
$oGrid->record_id = "dettaglio-abilitazione";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_inizio";
$oField->base_type = "Date";
$oField->label = "Data riferimento inizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento_fine";
$oField->base_type = "Date";
$oField->label = "Data riferimento fine";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contatti_mail";
$oField->base_type = "Text";
$oField->label = "Contatti Mail";
$oGrid->addContent($oField);

$oGrid->addEvent("on_before_parse_row", "checkRelations");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function checkRelations($oGrid) {
    $id = $oGrid->key_fields["ID"]->value->getValue();
    $abilitazione = new AbilitazioneCdr($id);
    $oGrid->display_delete_bt = $abilitazione->isDeletable();
}