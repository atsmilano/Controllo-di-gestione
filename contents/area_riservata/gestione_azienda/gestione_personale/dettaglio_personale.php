<?php
$isEdit = false;

if (isset($_REQUEST["keys[id_personale]"])) {
    $isEdit = true;
    $id_personale = $_REQUEST["keys[id_personale]"];
    
    try {
        $personale = new Personale($id_personale);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "personale-modify";
$oRecord->title = $isEdit ? "Modifica personale" : "Nuovo inserimento";
$oRecord->resources[] = "personale";
$oRecord->src_table  = "personale";
$oRecord->allow_update = false;
$oRecord->buttons_options["delete"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "id_personale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola";
$oField->base_type = "Text";
$oField->label = "Matricola";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cognome";
$oField->base_type = "Text";
$oField->label = "Cognome";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
if ($isEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);

if ($isEdit) {
    // Modifica di un dato già esistente
    $grid_fields = array(
        "id_cdc_personale",
        "matricola_personale",
        "codice_cdc",
        "percentuale",
        "data_inizio",
        "data_fine"
    );
    $grid_recordset = array();
    foreach (CdcPersonale::getAll(array("matricola_personale" => $personale->matricola)) as $allocazione) {
        $grid_recordset[] = array(
            $allocazione->id,
            $personale->cognome." ".$personale->nome." (matr. ".$allocazione->matricola_personale.")",
            $allocazione->codice_cdc,
            $allocazione->percentuale ."%",
            $allocazione->data_inizio,
            $allocazione->data_fine
        );
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-cdc-allocazione";
    $oGrid->title = "Storico delle allocazioni su CdC";
    $oGrid->resources[] = "cdc-allocazione";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "cdc_personale");
    $oGrid->record_id = "cdc-allocazione";
    $oGrid->order_default = "data_inizio";
    $oGrid->order_method = "labels";
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    $oGrid->display_edit_url = true;
    $oGrid->display_delete_bt = true;
    $oGrid->full_ajax = true;   
    $oGrid->record_url = getRecordUrl("dettaglio_cdc_allocazione");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_cdc_personale";
    $oField->base_type = "Number";
    $oField->label = "id";
    $oGrid->addKeyField($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdc";
    $oField->base_type = "Text";
    $oField->label = "Centro di Costo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "percentuale";
    $oField->base_type = "Text";
    $oField->label = "Percentuale";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_inizio";
    $oField->base_type = "Date";
    $oField->label = "Data inizio";
    $oField->order_dir = "DESC";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_fine";
    $oField->base_type = "Date";
    $oField->label = "Data fine";
    $oGrid->addContent($oField);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "linked_data";
    $oButton->aspect = "link";
    $oButton->class = "";   
    $oGrid->addGridButton($oButton);

    $oGrid->addEvent("on_before_parse_row", "checkCloseCdcAfferenza");
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);

    $grid_fields = array(
        "id_carriera",
        "matricola_personale",
        "id_tipo_contratto",
        "tipo_contratto",
        "id_ruolo",
        "ruolo",
        "dirigente",
        "id_qualifica_interna",
        "qualifica_interna",
        "id_rapporto_lavoro",
        "rapporto_lavoro",
        "posizione_organizzativa",
        "data_inizio",
        "data_fine"
    );
    $grid_recordset = array();
    foreach (CarrieraPersonale::getAll(array("matricola_personale" => $personale->matricola)) as $carriera) {
        $tipo_contratto = new TipoContratto($carriera->id_tipo_contratto);
        
        $qualifica_interna = new QualificaInterna($carriera->id_qualifica_interna);
        $ruolo = new Ruolo($qualifica_interna->id_ruolo);
        
        $rapporto_lavoro = new RapportoLavoro($carriera->id_rapporto_lavoro);
        $descrizione_rapporto_lavoro = $rapporto_lavoro->descrizione;
        
        $grid_recordset[] = array(
            $carriera->id,
            $personale->cognome." ".$personale->nome." (matr. ".$allocazione->matricola_personale.")",
            $carriera->id_tipo_contratto,
            $tipo_contratto->descrizione,
            $qualifica_interna->id_ruolo,
            $ruolo->descrizione,
            $qualifica_interna->dirigente == "1" ? "Sì" : "No", 
            $carriera->id_qualifica_interna,
            $qualifica_interna->descrizione,
            $carriera->id_rapporto_lavoro,
            $rapporto_lavoro->part_time == "1" 
                ? ($descrizione_rapporto_lavoro. " (part time al ".$carriera->perc_rapporto_lavoro."%)")
                : $descrizione_rapporto_lavoro,
            $carriera->posizione_organizzativa == "1" ? "Sì" : "No", 
            $carriera->data_inizio,
            $carriera->data_fine
        );
    }
    
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-carriera-personale";
    $oGrid->title = "Storico degli eventi di carriera";
    $oGrid->resources[] = "carriera-personale";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "carriera");
    $oGrid->order_default = "data_inizio";
    $oGrid->record_id = "carriera-personale";
    $oGrid->order_method = "labels";
    $oGrid->display_edit_url = true;
    $oGrid->display_delete_bt = true;
    $oGrid->use_paging = false;
    $oGrid->use_search = false;
    $oGrid->full_ajax = true;
    $oGrid->record_url = getRecordUrl("dettaglio_carriera");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_carriera";
//    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oField->label = "id";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_tipo_contratto";
    $oField->base_type = "Number";
    $oField->label = "id_tipo_contratto";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "tipo_contratto";
    $oField->base_type = "Text";
    $oField->label = "Tipo contratto";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_ruolo";
    $oField->base_type = "Number";
    $oField->label = "id_ruolo";
    $oGrid->addKeyField($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ruolo";
    $oField->base_type = "Text";
    $oField->label = "Ruolo";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dirigente";
    $oField->base_type = "Text";
    $oField->label = "Dirigente";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_qualifica_interna";
    $oField->base_type = "Number";
    $oField->label = "id_qualifica_interna";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "qualifica_interna";
    $oField->base_type = "Text";
    $oField->label = "Qualifica interna";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "id_rapporto_lavoro";
    $oField->base_type = "Number";
    $oField->label = "id_rapporto_lavoro";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "rapporto_lavoro";
    $oField->base_type = "Text";
    $oField->label = "Rapporto lavoro";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "posizione_organizzativa";
    $oField->base_type = "Text";
    $oField->label = "Incarico di funzione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_inizio";
    $oField->base_type = "Date";
    $oField->label = "Data inizio";
    $oField->order_dir = "DESC";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_fine";
    $oField->base_type = "Date";
    $oField->label = "Data fine";
    $oGrid->addContent($oField);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "linked_data";
    $oButton->aspect = "link";
    $oButton->class = "";    
    $oGrid->addGridButton($oButton);

    $oGrid->addEvent("on_before_parse_row", "checkCloseCarriera");

    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);    
}

$oRecord->addEvent("on_do_action", "validateAction");
$cm->oPage->addContent($oRecord);

function checkCloseCdcAfferenza($oGrid) {
    if (isset($_REQUEST["keys[id_personale]"])) {
        $cm = cm::getInstance();
        $id_personale = $_REQUEST["keys[id_personale]"];
        $id_cdc_allocazione = $oGrid->key_fields["id_cdc_personale"]->value->getValue();
        $dataInizioObject = DateTime::createFromFormat("Y-m-d", $oGrid->grid_fields["data_inizio"]->value->getValue());
        $data_fine = $oGrid->grid_fields["data_fine"]->value->getValue();
        $dataRiferimentoObject = $cm->oPage->globals["data_riferimento"]["value"];

        if ($data_fine == "" && $dataInizioObject < $dataRiferimentoObject) {
            $url = getRecordUrl("dettaglio_cdc_allocazione") . "?XHR_CTX_TYPE=dialog&XHR_CTX_ID=cdc-allocazione&keys[id_personale]=".$id_personale."&keys[id_cdc_personale_to_close]=".$id_cdc_allocazione;
            $oGrid->grid_buttons["linked_data"]->class = "fa-exchange";
            $oGrid->grid_buttons["linked_data"]->jsaction = "ff.ffPage.dialog.doOpen('cdc-allocazione', '" . $url . "');";
        } else {
            $oGrid->grid_buttons["linked_data"]->class = "";
        }
    }
}

function checkCloseCarriera($oGrid) {
    if (isset($_REQUEST["keys[id_personale]"])) {
        $cm = cm::getInstance();
        $id_personale = $_REQUEST["keys[id_personale]"];
        $id_carriera = $oGrid->key_fields["id_carriera"]->value->getValue();
        $dataInizioObject = DateTime::createFromFormat("Y-m-d", $oGrid->grid_fields["data_inizio"]->value->getValue());
        $data_fine = $oGrid->grid_fields["data_fine"]->value->getValue();
        $dataRiferimentoObject = $cm->oPage->globals["data_riferimento"]["value"];

        if ($data_fine == "" && $dataInizioObject < $dataRiferimentoObject) {
            $url = getRecordUrl("dettaglio_carriera") . "?XHR_CTX_TYPE=dialog&XHR_CTX_ID=carriera&keys[id_personale]=".$id_personale."&keys[id_carriera_to_close]=".$id_carriera;
            $oGrid->grid_buttons["linked_data"]->class = "fa-exchange";
            $oGrid->grid_buttons["linked_data"]->jsaction = "ff.ffPage.dialog.doOpen('cdc-allocazione', '" . $url . "');";
        } else {
            $oGrid->grid_buttons["linked_data"]->class = "";
        }
    }
}

function validateAction($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $matricola = $oRecord->form_fields["matricola"]->value->getValue();
            $error_msg = "Matricola $matricola già presente: impossibile inserire la nuova risorsa umana";
            try {
                $personale = Personale::factoryFromMatricola($matricola);

                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            catch (Exception $e) {
            }

            break;
        case "confirmdelete":
        case "delete":
            // Eliminazione possibile solo se non presenti voci di carriera o afferenze ai CdC
            $matricola = $oRecord->form_fields["matricola"]->value->getValue();
            $error_msg = "Impossibile eliminare la risorsa umana con matricola $matricola perché";
            
            // Verifica su eventuali voci di allocazione cdc
            $cdc_personale_list = CdcPersonale::getAll(array("matricola_personale" => $matricola));
            if (!empty($cdc_personale_list)) {
                $frmAction = null;
                $error_msg .= " presenta afferenze ai CdC";
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
            return true;
            }

            // Ricerca su eventuali voci di carriera
            $carriera_personale_list = CarrieraPersonale::getAll(array("matricola_personale" => $matricola));
            if (!empty($carriera_personale_list)) {
                $frmAction = null;
                $error_msg .= " presenta voci di carriera";
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : $error_msg;
                return true;
            }
            
            break;
        default:
            break;
    }
}

function getRecordUrl($page) {
    $cm = cm::getInstance();
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    return FF_SITE_PATH . $path_info . $page;
}
