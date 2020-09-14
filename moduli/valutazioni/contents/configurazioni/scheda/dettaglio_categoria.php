<?php
if (isset($_REQUEST["keys[ID_categoria]"])) {
    $isEdit = true;
    $id_categoria = $_REQUEST["keys[ID_categoria]"];

    try {
        $categoria = new ValutazioniCategoria($id_categoria);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "categoria-modify";
$oRecord->title = $isEdit ? "Modifica tipologia scheda '$categoria->descrizione'" : "Nuova tipologia scheda";
$oRecord->resources[] = "categoria";
$oRecord->src_table = "valutazioni_categoria";
$editable = !$isEdit || ($isEdit && $categoria->canDelete());
$oRecord->allow_delete = $editable;
$oRecord->allow_update = true;
$oRecord->allow_delete = true;

CoreHelper::refreshTabOnDialogClose($oRecord->id);

// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_categoria";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addKeyField($oField);
	
$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->label = "Anno inizio";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";
$oField->label = "Anno fine";
$oRecord->addContent($oField);


if ($isEdit == true) {
    $oRecord->addContent(null, true, "regole");
    $oRecord->groups["regole"]["title"] = "Regole per l'apertura della scheda";
    
    //recupero degli indicatori collegati all'obiettivo
    $grid_fields = array(
                    "ID",
                    "ordine",
                    "attributo",
                    "valore",
    );
    $grid_recordset = array();
    
    $i=1;
    foreach ($categoria->getRegole() as $regola) {   
        $attributo = ValutazioniRegolaCategoria::getAttributi($regola->id_attributo);
        $grid_recordset[] = array(                                
                                $regola->id,
                                $i,
                                $attributo[0]["descrizione"],
                                $regola->valore,
                            );
        $i++;
    }
    //visualizzazione della grid degli indicatori definiti per l'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "categoria-regola";
    $oGrid->title = "Regole per l'apertura delle schede";
    $oGrid->resources[] = "categoria-regola";        
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_regola_categoria");
    $oGrid->order_default = "ordine";
    $oGrid->record_id = "categoria-regola-modify";
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;
   
    $path_info_parts = explode("/", $cm->path_info);	
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_regola_categoria";   
    $oGrid->use_paging = false;
    $oGrid->display_search = false;

    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_regola";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ordine";
    $oField->base_type = "Number";
    $oField->label = "N. attributo";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "attributo";
    $oField->base_type = "Text";
    $oField->label = "Attributo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore";
    $oField->base_type = "Text";
    $oField->label = "Valore";		
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $oRecord->addContent($oGrid, "regole");
    $cm->oPage->addContent($oGrid);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "formula_appartenenza_personale";
    $oField->base_type = "Text";
    $oField->label = "Formula identificazione personale";
    $oRecord->addContent($oField, "regole");
}

$oRecord->addEvent("on_do_action", "checkRelations");
// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

//on_do_action
function checkRelations($oRecord, $frmAction) {
    $id_categoria = $oRecord->key_fields["ID_categoria"]->value->getValue();
    if(isset($id_categoria) && $id_categoria != "") {
        $categoria = new ValutazioniCategoria($id_categoria);
    }

    switch($frmAction) {
        case "confirmdelete":
            if(!$categoria->delete()) {
                return CoreHelper::setError($oRecord,"La tipologia scheda selezionata non può essere eliminata.");
            }
            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "La tipologia scheda selezionata non può essere modificata.";
            //In questo caso, tale condizione non dovrebbe essere mai vera (vincoli grafici)
            if(!$categoria->canDelete()) {
                $non_editable_fields = array(
                    "anno_inizio" => $categoria->anno_inizio,
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }
            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();
            if($anno_fine) {
                if($categoria->hasRelationsAfterAnnoFine($anno_fine)) {
                    return CoreHelper::setError($oRecord, "Anno fine non valido: la tipologia scheda ha riferimenti ad altri elementi successivamente all'anno fine impostato");
                }
            }
            break;
    }
}