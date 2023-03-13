<?php
use scadenze\AbilitazioneCdr;
use scadenze\Personale;

$user = LoggedUser::getInstance();
if ($user->hasPrivilege("scadenze_admin")) {
    $edit = true;    
}
else if ($user->hasPrivilege("scadenze_referente_cdr")) {    
    $edit = false;
}
else {    
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter visualizzare le scadenze");
}

//$date = $cm->oPage->globals["data_riferimento"]["value"];
$date = new DateTime();

$grid_fields = array(
    "ID",
    "stato",
    "data_scadenza",
    "protocollo",
    "ID_abilitazione_cdr",
    "cdr",    
    "oggetto",
);

$grid_recordset = array();
$abilitazioni_multipairs = array();
$stati_multipairs = array();

$personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);
$scadenze = $personale->getScadenzeCompetenzaInData($date);
        
foreach ($scadenze as $scadenza) {    
    $data_inserimento = new DateTime($scadenza->data_inserimento);
    $abilitazione_cdr = new AbilitazioneCdr($scadenza->id_abilitazione_cdr);       
    $anagrafica_cdr = \AnagraficaCdr::factoryFromCodice($abilitazione_cdr->codice_cdr, $data_inserimento);    
    if ($anagrafica_cdr !== null) {
        $descrizione_cdr = $anagrafica_cdr->getDescrizioneEstesa();            
    }
    else {        
        $descrizione_cdr = "Codice " . $abilitazione_cdr->codice_cdr . " senza corrispondenza al " . $data_inserimento->format("d/m/Y");
    }
   
    
    //abilitazioni_multipairs per popolamento campo ricerca cdr
    if (count($abilitazioni_multipairs) >0) {
        $found = false;
        foreach ($abilitazioni_multipairs as $abilitazione_multipair) {                
            if ($abilitazione_multipair[0]->getValue() == $abilitazione_cdr->id) {
                $found = true;
                break;
            }             
        }   
        if ($found == false) {
            $abilitazioni_multipairs[] = array(
                new ffData($abilitazione_cdr->id, "Number"),
                new ffData($descrizione_cdr, "Text")
            );
        }
    }
    else {
        $abilitazioni_multipairs[] = array(
            new ffData($abilitazione_cdr->id, "Number"),
            new ffData($descrizione_cdr, "Text")
        );
    }
    
    //costruzione multipairs stati
    $stato_scadenza = $scadenza->getStato();
    if (count($stati_multipairs) > 0) {
        $found = false;
        foreach($stati_multipairs as $stato_multipair) {            
            if ($stato_multipair[0]->getValue() == $stato_scadenza["id"]) {
                $found = true;
                break;
            }
            if ($found == false) {
                $stati_multipairs[] = array(
                    new ffData($stato_scadenza["id"], "Number"),
                    new ffData($stato_scadenza["descrizione"], "Text")
                );
            }
        }
    }   
    else {
        $stati_multipairs[] = array(
            new ffData($stato_scadenza["id"], "Number"),
            new ffData($stato_scadenza["descrizione"], "Text")
        );
    }
    
    $grid_recordset[] = array(
        $scadenza->id,
        $stato_scadenza["id"],
        $scadenza->data_scadenza,
        $scadenza->protocollo,
        $abilitazione_cdr->id,
        $descrizione_cdr,        
        $scadenza->oggetto,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "scadenza";
$oGrid->title = "Scadenze";
$oGrid->resources[] = "scadenza";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, 
    "scadenze_scadenza"
);
$oGrid->order_default = "stato";
$oGrid->record_id = "scadenza-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_scadenza";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
//$oGrid->open_adv_search = true;
if ($edit == false) {
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;    
}

//**************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "stato";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $stati_multipairs;
$oField->order_SQL = "stato ASC, data_scadenza DESC";
$oField->label = "Stato";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_scadenza";
$oField->base_type = "Date";
$oField->order_dir = "ASC";
$oField->label = "Data scadenza";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "protocollo";
$oField->base_type = "Text";
$oField->label = "N° protocollo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "oggetto";
$oField->base_type = "Text";
$oField->label = "Oggetto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_search";
$oField->data_source = "ID_abilitazione_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $abilitazioni_multipairs;
$oField->label = "Cdr";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data-scadenza-search";
$oField->data_source = "data_scadenza";
$oField->base_type = "Date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->widget = "datepicker";
$oField->src_interval = true;
$oField->src_operation = "[NAME]";
$oField->interval_from_label = "Data scadenza dal - al:";
$oField->label = "Data scadenza";
$oGrid->addSearchField($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent("<a id='scadenze_estrazione_link' class='link_estrazione' href='".FF_SITE_PATH . $cm->path_info ."/estrazione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."'>"
            . "<div id='scadenze_estrazione' class='estrazione link_estrazione'>Estrazione scadenze .xls</div></a><br>");
$cm->oPage->addContent($oGrid);