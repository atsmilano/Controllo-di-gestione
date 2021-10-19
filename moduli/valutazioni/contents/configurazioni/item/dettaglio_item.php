<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_item]"])) {
    $isEdit = true;
    $id_item = $_REQUEST["keys[ID_item]"];

    try {
        $item = new ValutazioniItem($id_item);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

if (isset($_REQUEST["keys[ID_area_item]"])) {
    $id_area_item = $_REQUEST["keys[ID_area_item]"];
    
    try {
        $area_item = new ValutazioniAreaItem($id_area_item);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "item-modify";
$oRecord->title = $isEdit ? "Modifica item" : "Nuovo item";
$oRecord->resources[] = "item";
$oRecord->src_table  = "valutazioni_item";
$editable = !$isEdit || ($isEdit && $item->canDelete());
$oRecord->allow_delete = $editable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_item";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->base_type = "Number";
$oField->label = "Peso";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$aree_item = ValutazioniAreaItem::getAll();
$aree_item_menu = array();
foreach($aree_item as $ai) {
    $aree_item_menu[] = array(
        new ffData($ai->id, "Number"),
        new ffData($ai->descrizione, "Text"),
    );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_item";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $aree_item_menu;
$oField->label = "Area item";
$oField->required = true;
if (!$isEdit) {
    $oField->default_value = new ffData($area_item->id, "Number");
    $oField->data_type = "";
}
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_esclusione";
$oField->base_type = "Number";
$oField->label = "Anno esclusione";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordine_visualizzazione";
$oField->base_type = "Number";
$oField->label = "Ordine di visualizzazione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$tipi_visualizzazione_item = array(
                                    array(new ffData(0, "Number"), new ffData("Radio", "Text")),
                                    array(new ffData(1, "Number"), new ffData("Box selezione", "Text")),
                                    );
$oField = ffField::factory($cm->oPage);
$oField->id = "tipo_visualizzazione";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipi_visualizzazione_item;
$oField->label = "Tipo di visualizzazione";
$oField->required = true;
$oRecord->addContent($oField);

CoreHelper::refreshTabOnDialogClose($oRecord->id);

// ITEM - CATEGORIE
$oRecord->groups["item_categoria"]["title"] = "Item - Tipologie scheda";
$categorie = ValutazioniCategoria::getAll();

foreach($categorie as $categoria) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "categoria_".$categoria->id;
    $oField->label = $categoria->abbreviazione;
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->store_in_db = false;
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);    
    if($isEdit) {
        try {
            $item_categoria = ValutazioniItemCategoria::factoryFromItemCategoria($id_item, $categoria->id);
            $item_categoria_editable = $item_categoria->canDelete();
        } catch (Exception $ex) {
            // Tecnicamente dovrebbe accadere solo per categorie non associate
            $item_categoria_editable = true;
        }

        CoreHelper::disableNonEditableOField($oField, $item_categoria_editable);
    }
    $oRecord->addContent($oField, "item_categoria");
}

$oRecord->addContent(null, true, "punteggi_item");
$oRecord->groups["punteggi_item"]["title"] = "Punteggi associati all'item";

// PUNTEGGI - ITEM
if($isEdit) {
    $grid_fields = array(
        "ID_punteggio_item",
        "descrizione",
        "punteggio",
    );

    $grid_recordset = array();

    $punteggi_item = ValutazioniPunteggioItem::getAll(array("ID_item" => $item->id));

    foreach ($punteggi_item as $punteggio_item) {
        $grid_recordset[] = array(
            $punteggio_item->id,
            $punteggio_item->descrizione,
            $punteggio_item->punteggio,
        );
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "valutazioni_punteggio_item";
    $oGrid->resources[] = "punteggio-item";
    $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_punteggio_item");
    $oGrid->order_default = "ID_punteggio_item";
    $oGrid->record_id = "punteggio-item-modify";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_punteggio_item";
    $oGrid->record_url = $record_url;
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;
    $oGrid->display_new = true;
    $oGrid->display_search = false;
    $oGrid->use_search = false;
    $oGrid->fixed_post_content = '<script>jQuery("#' . $oGrid->id . '").jTableFullClick();</script>';

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_punteggio_item";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField, "punteggi_item");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oField->required = true;
    $oGrid->addContent($oField, "punteggi_item");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "punteggio";
    $oField->base_type = "Text";
    $oField->label = "Punteggio";
    $oField->required = true;
    $oGrid->addContent($oField, "punteggi_item");

    $oGrid->addEvent("on_before_parse_row", "checkPunteggioItemEliminabile");
    $oRecord->addContent($oGrid, "punteggi_item");
    $cm->oPage->addContent($oGrid);
}

$oRecord->addEvent("on_do_action", "checkRelations");
$oRecord->addEvent("on_do_action", "inizializzaItemRelations");
$oRecord->addEvent("on_done_action", "updateItemRelations");

$cm->oPage->addContent($oRecord);

function inizializzaItemRelations($oRecord, $frmAction) {
    $ID_item = $oRecord->key_fields["ID_item"]->value->getValue();
    if($frmAction == "" && isset($ID_item)) {
        $item = new ValutazioniItem($ID_item);
        $categorie_associate = $item->getCategorieAssociate();
        ValutazioniHelper::inizializzaCheckbox($oRecord, "categoria_", $categorie_associate);
    }
}

function updateCategoriaAssociata($id_item, $categorie_associate, $key, $fieldValue) {
    $id_categoria = ValutazioniHelper::getIdFromFieldKey($key);
    $relationExists = isset($categorie_associate[$id_categoria]);

    if(!$relationExists && $fieldValue) {
        //Caso insert
        $item_categoria = new ValutazioniItemCategoria();
        $item_categoria->id_item = $id_item;
        $item_categoria->id_categoria = $id_categoria;
        $item_categoria->insert();
    } elseif($relationExists && !$fieldValue) {
        //Caso delete
        $item_categoria = ValutazioniItemCategoria::factoryFromItemCategoria($id_item, $id_categoria);
        return $item_categoria->delete();        
    }

    return true;
}

function updateItemRelations($oRecord, $frmAction) {
    if($frmAction == "insert" || $frmAction == "update") {
        $ID_item = $oRecord->key_fields["ID_item"]->value->getValue();
        $item = new ValutazioniItem($ID_item);
        $categorie_associate = $item->getCategorieAssociate();

        foreach($oRecord->form_fields as $key => $formField) {
            if(strpos($key, "categoria_") !== false) {
               if(!updateCategoriaAssociata($item->id, $categorie_associate, $key, $formField->value->getValue())) {
                   return CoreHelper::setError($oRecord, "Operazione non riuscita");
               }
            }
        }
    }
}

function checkRelations($oRecord, $frmAction) {
    $id_item = $oRecord->key_fields["ID_item"]->value->getValue();
    if(isset($id_item) && $id_item != "") {
        $item = new ValutazioniItem($id_item);
    }

    switch($frmAction) {
        case "confirmdelete":
            if(!$item->delete()) {
                return CoreHelper::setError($oRecord,"L'item selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "L'item selezionato non può essere modificato.";
            if(!$item->canDelete()) {
                $non_editable_fields = array(
                    "peso" => $item->peso,
                    "ID_area_item" => $item->id_area_item,
                    "anno_introduzione" => $item->anno_introduzione,
                    "ordine_visualizzazione" => $item->ordine_visualizzazione,
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }
            break;
    }

}

function checkPunteggioItemEliminabile($oGrid) {
    $id_punteggio_item = $oGrid->key_fields["ID_punteggio_item"]->value->getValue();
    $punteggio_item = new ValutazioniPunteggioItem($id_punteggio_item);
    $oGrid->display_delete_bt = $punteggio_item->canDelete();
}