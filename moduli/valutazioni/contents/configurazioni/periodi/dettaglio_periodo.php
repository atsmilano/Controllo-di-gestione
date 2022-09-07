<?php
$isEdit = false;
if (isset($_REQUEST["keys[id_periodo]"])) {
    $isEdit = true;
    $id_periodo = $_REQUEST["keys[id_periodo]"];

    try {
        $periodo = new ValutazioniPeriodo($id_periodo);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-modify";
$oRecord->title = $isEdit ? "Modifica periodo '$periodo->descrizione'": "Nuovo periodo";
$oRecord->resources[] = "periodo";
$oRecord->src_table  = "valutazioni_periodo";
$editable = !$isEdit || ($isEdit && $periodo->canDelete());
$oRecord->allow_delete = $editable;

$oRecord->groups["periodo_group"]["title"] = "Periodo";

$oField = ffField::factory($cm->oPage);
$oField->id = "id_periodo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_totali";
$oField->base_type = "Number";
$oField->label = "Visualizzazione totali inibita";
if ($editable){
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
}
else {
    $field_value = $periodo->inibizione_visualizzazione_totali == true?"Si":"No";
    $oField->base_type = "Text";
    $oField->default_value = new ffData($field_value, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_ambiti_totali";
$oField->base_type = "Number";
$oField->label = "Visualizzazione ambiti in totali inibita";
if ($editable){
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
}
else {
    $field_value = $periodo->inibizione_visualizzazione_ambiti_totali == true?"Si":"No";
    $oField->base_type = "Text";
    $oField->default_value = new ffData($field_value, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_data_colloquio";
$oField->base_type = "Number";
$oField->label = "Visualizzazione data colloquio inibita";
if ($editable){
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
}
else {
    $field_value = $periodo->inibizione_visualizzazione_data_colloquio == true?"Si":"No";
    $oField->base_type = "Text";
    $oField->default_value = new ffData($field_value, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "visualizzazione_obiettivi";
$oField->base_type = "Number";
$oField->label = "Visualizzazione obiettivi inibita";
if ($editable){
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
}
else {
    $field_value = $periodo->visualizzazione_obiettivi == true?"Si":"No";
    $oField->base_type = "Text";
    $oField->default_value = new ffData($field_value, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "visualizzazione_pesi_obiettivi_responsabile";
$oField->base_type = "Number";
$oField->label = "Visualizzazione pesi in obiettivi responsabile";
if ($editable){
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
}
else {
    $field_value = $periodo->visualizzazione_pesi_obiettivi_responsabile == true?"Si":"No";
    $oField->base_type = "Text";
    $oField->default_value = new ffData($field_value, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "periodo_group");

$anni_budget = AnnoBudget::getAll();
$anni_budget_menu = array();
foreach($anni_budget as $anno_budget) {
    if($anno_budget->attivo == 1) {
        $anni_budget_menu[] = array(
            new ffData($anno_budget->id, "Number"),
            new ffData($anno_budget->descrizione, "Text"),
        );
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anno_budget";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anni_budget_menu;
$oField->label = "Anno budget";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_inizio";
$oField->base_type = "Date";
$oField->label = "Data inizio";
if ($editable){
    $oField->widget = "datepicker";    
    $oField->required = true;
}
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_fine";
$oField->base_type = "Date";
$oField->label = "Data fine";
if ($editable){
    $oField->widget = "datepicker";    
    $oField->required = true;
}
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_apertura_compilazione";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data apertura compilazione";
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_chiusura_autovalutazione";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data chiusura autovalutazione";
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_chiusura_valutatore";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data chiusura valutatore";
$oRecord->addContent($oField, "periodo_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "data_chiusura_valutato";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oField->label = "Data chiusura valutato";
$oRecord->addContent($oField, "periodo_group");

$oRecord->addContent(null, true, "periodo_categoria_group"); // Utilizzare con il secondo gruppo
$oRecord->groups["periodo_categoria_group"]["title"] = "Periodo - Tipologie scheda";

if($isEdit) {
    $grid_fields = array(
        "ID_periodo_categoria",
        "abbreviazione",
        "descrizione",
        "ambiti"
    );
    $grid_recordset = array();

    foreach ($periodo->getCategoriePeriodo() as $periodo_categoria) {
        //recupero informazioni ambiti
        $descrizioni_categoria_periodo_ambito = array();
        foreach ($periodo->getAmbitiCategoriaPeriodo($periodo_categoria) as $periodo_categoria_ambito) {
            $sezione_categoria_periodo_ambito = new ValutazioniSezione($periodo_categoria_ambito->id_sezione);
            $descrizioni_categoria_periodo_ambito[] = $sezione_categoria_periodo_ambito->codice.".".$periodo_categoria_ambito->codice
                                             .". - ".$periodo_categoria_ambito->descrizione
                                             ." (Autoval. ".($periodo->getAutovalutazioneAttivaCategoriaAmbito($periodo_categoria, $periodo_categoria_ambito)?"Si":"No")
                                             ." - Tot visibili ".($periodo->getVisualizzazionePunteggiAttivaCategoriaAmbito($periodo_categoria, $periodo_categoria_ambito)?"Si":"No")
                                             .")"
                                            ;
        }

        $grid_recordset[] = array(
            $periodo->getIdCategoriaPeriodo($periodo_categoria),
            $periodo_categoria->abbreviazione,
            $periodo_categoria->descrizione,
            implode("\n", $descrizioni_categoria_periodo_ambito),
        );
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "valutazioni_periodo_categoria";    
    $oGrid->resources[] = "periodo-categoria";
    $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_periodo_categoria");
    $oGrid->order_default = "ID_periodo_categoria";
    $oGrid->record_id = "periodo-categoria-modify";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_periodo_categoria";
    $oGrid->record_url = $record_url;
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;
    $oGrid->display_search = false;
    $oGrid->use_search = false;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_periodo_categoria";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField, "periodo_categoria_group");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "abbreviazione";
    $oField->base_type = "Text";
    $oField->label = "Abbreviazione";
    $oGrid->addContent($oField, "periodo_categoria_group");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField, "periodo_categoria_group");

    $oGrid->addEvent("on_before_parse_row", "checkEliminabile");
    $oRecord->addContent($oGrid, "periodo_categoria_group");
    $cm->oPage->addContent($oGrid);
}
$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

//on_do_action
function checkRelations($oRecord, $frmAction) {
    $id_periodo = $oRecord->key_fields["id_periodo"]->value->getValue();
    if(isset($id_periodo) && $id_periodo != "") {
        $periodo = new ValutazioniPeriodo($id_periodo);
    }

    switch($frmAction) {
        case "insert":
        case "update":                        
            $record_update_error_msg = "Il periodo selezionato non può essere modificato.";
            if($frmAction == "update" && !$periodo->canDelete()) {
                $non_editable_fields = array(
                    "descrizione" => $periodo->descrizione,
                    "ID_anno_budget" => $periodo->id_anno_budget,
                    "data_inizio" => $periodo->data_inizio,
                    "data_fine" => $periodo->data_fine,
                    //"data_apertura_compilazione" => $periodo->data_apertura_compilazione,
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }

            $data_inizio_form = $oRecord->form_fields["data_inizio"]->value->getValue();
            $data_fine_form = $oRecord->form_fields["data_fine"]->value->getValue();
            $data_inizio = DateTime::createFromFormat("d/m/Y", $data_inizio_form);
            $data_fine = DateTime::createFromFormat("d/m/Y", $data_fine_form);
            
            // Verifica della coerenza periodo di date, valutare se > o >=
            if($data_fine_form != "" && $data_inizio > $data_fine) {
                return CoreHelper::setError($oRecord, "La data fine deve essere successiva o uguale alla data di inizio.");
            }

            break;
        case "confirmdelete":
            if(!$periodo->delete()) {
                return CoreHelper::setError($oRecord, "Il periodo selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true; //Evito l'esecuzione della query di delete
            break;
    }
}

//on_before_parse_row
function checkEliminabile($oGrid){
    $id_periodo_categoria = $oGrid->key_fields["ID_periodo_categoria"]->value->getValue();
    $periodo_categoria = new ValutazioniPeriodoCategoria($id_periodo_categoria);
    $oGrid->display_delete_bt = $periodo_categoria->canDelete();
}