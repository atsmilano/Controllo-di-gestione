<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_area_item]"])) {
    $isEdit = true;
    $id_area_item = $_REQUEST["keys[ID_area_item]"];

    try {
        $area_item = new ValutazioniAreaItem($id_area_item);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "area-item-modify";
$oRecord->title = $isEdit ? "Modifica area item '$area_item->descrizione'" : "Nuova area item";
$oRecord->resources[] = "area-item";
$oRecord->src_table  = "valutazioni_area_item";
$editable = !$isEdit || ($isEdit && $area_item->canDelete());
$oRecord->allow_delete = $editable;

CoreHelper::refreshTabOnDialogClose($oRecord->id);

$oRecord->groups["area_item_group"]["title"] = "Area Item";
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_item";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField, "area_item_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField, "area_item_group");

$ambiti = ValutazioniAmbito::getAll();
$ambiti_menu = array();
foreach($ambiti as $ambito) {
    $sezione = new ValutazioniSezione($ambito->id_sezione);
    $ambiti_menu[] = array(
        new ffData($ambito->id, "Number"),
        new ffData($sezione->codice . "." . $ambito->codice . ". " .$ambito->descrizione, "Text"),
    );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_ambito";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $ambiti_menu;
$oField->label = "Ambito";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "area_item_group");

$oField = ffField::factory($cm->oPage);
$oField->id = "ordine_visualizzazione";
$oField->base_type = "Number";
$oField->label = "Ordine di visualizzazione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField, "area_item_group");

$cm->oPage->addContent($oRecord);

$oRecord->addContent(null, true, "item_group");
$oRecord->groups["item_group"]["title"] = "Item";

if($isEdit) {
    $grid_fields = array(
        "ID_item",
        "descrizione",
        "area_item",
        "categorie",
        "peso",
        "anno_introduzione",
        "anno_esclusione",
    );

    $grid_recordset = array();
    foreach (ValutazioniItem::getAll(array("ID_area_item" => $area_item->id)) as $item) {
        $grid_recordset[] = array(
            $item->id,
            $item->descrizione,
            $area_item->descrizione,
            ValutazioniHelper::glueDescrizioni($item->getCategorieAssociate(), "\n", "abbreviazione"),
            $item->peso,
            $item->anno_introduzione,
            $item->anno_esclusione,
        );
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "items";    
    $oGrid->resources[] = "item";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_item");
    $oGrid->order_default = "ID_item";
    $oGrid->record_id = "item-modify";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_item";
    $oGrid->record_url = $record_url;
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;
    $oGrid->display_new = true;
    $oGrid->display_search = false;
    $oGrid->use_search = false;
    $oGrid->fixed_post_content = '<script>jQuery("#' . $oGrid->id . '").jTableFullClick();</script>';

    //**************************************************************************
    // *********** FIELDS ****************

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_item";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "area_item";
    $oField->base_type = "Text";
    $oField->label = "Area item";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "categorie";
    $oField->base_type = "Text";
    $oField->label = "Tipologie scheda";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "peso";
    $oField->base_type = "Number";
    $oField->label = "Peso";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "anno_introduzione";
    $oField->base_type = "Text";
    $oField->label = "Anno introduzione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "anno_esclusione";
    $oField->base_type = "Text";
    $oField->label = "Anno esclusione";
    $oGrid->addContent($oField);

    $oGrid->addEvent("on_before_parse_row", "checkItemEliminabile");
    $oRecord->addContent($oGrid, "item_group");
    $oRecord->addEvent("on_do_action", "checkRelations");
    $cm->oPage->addContent($oGrid);
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function checkItemEliminabile($oGrid) {
    $id_item = $oGrid->key_fields["ID_item"]->value->getValue();
    $item = new ValutazioniItem($id_item);
    $oGrid->display_delete_bt = $item->canDelete();
}

function checkRelations($oRecord, $frmAction) {
    $id_area_item = $oRecord->key_fields["ID_area_item"]->value->getValue();
    if(isset($id_area_item) && $id_area_item != "") {
        $area_item = new ValutazioniAreaItem($id_area_item);
    }

    switch($frmAction) {
        case "confirmdelete":
            if(!$area_item->delete()) {
                return CoreHelper::setError($oRecord,"L'area item selezionata non può essere eliminata.");
            }
            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "L'elemento non può essere modificato";
            if(!$area_item->canDelete()) {
                $non_editable_fields = array(
                    'ID_ambito' => $area_item->id_ambito,
                    'ordine_visualizzazione' => $area_item->ordine_visualizzazione
                );
                if(CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }

            break;
    }
}