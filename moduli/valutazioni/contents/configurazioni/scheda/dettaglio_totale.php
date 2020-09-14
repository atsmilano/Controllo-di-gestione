<?php
if (isset($_REQUEST["keys[ID_totale]"])) {
    $isEdit = true;
    $id_totale = $_REQUEST["keys[ID_totale]"];

    try {
        $totale = new ValutazioniTotale($id_totale);

    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "totale-modify";
$oRecord->title = $isEdit ? "Modifica totale '$totale->descrizione''" : "Nuovo totale";
$oRecord->resources[] = "totale";
$oRecord->src_table  = "valutazioni_totale";
$editable = !$isEdit || ($isEdit && $totale->canDelete());
$oRecord->allow_delete = $editable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_totale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

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

$oField = ffField::factory($cm->oPage);
$oField->id = "ordine_visualizzazione";
$oField->base_type = "Number";
$oField->label = "Ordine di visualizzazione";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

// TOTALE - CATEGORIE
$oRecord->addContent(null, true, "totale_categorie");
$oRecord->groups["totale_categorie"]["title"] = "Totale - Tipologie scheda";
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
        $relation_exists = false;
        try {
            $totale_categoria = ValutazioniTotaleCategoria::factoryFromTotaleCategoria($id_totale, $categoria->id);
            $relation_exists = true;
            $totale_categoria_editable = $totale_categoria->canDelete();
        } catch (Exception $ex) {
            // Tecnicamente dovrebbe accadere solo per categorie non associate
            $totale_categoria_editable = true;
        }
        CoreHelper::disableNonEditableOField($oField, $totale_categoria_editable);
    }
    $oRecord->addContent($oField, "totale_categorie");
}

// TOTALE - AMBITI
$oRecord->addContent(null, true, "totale_ambiti");
$oRecord->groups["totale_ambiti"]["title"] = "Totale - Ambiti";
$ambiti = ValutazioniAmbito::getAll();

foreach($ambiti as $ambito) {
    $sezione = new ValutazioniSezione($ambito->id_sezione);
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ambito_".$ambito->id;
    $oField->label = $sezione->codice . "." . $ambito->codice . ". " . $ambito->descrizione;
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->store_in_db = false;
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    if($isEdit) {
        $relation_exists = false;
        try {
            $totale_ambito = ValutazioniTotaleAmbito::factoryFromTotaleAmbito($id_totale, $ambito->id);
            $relation_exists = true;
            $totale_ambito_editable = $totale_ambito->canDelete();
        } catch (Exception $ex) {
            // Tecnicamente dovrebbe accadere solo per ambiti non associati
            $totale_ambito_editable = true;
        }
        CoreHelper::disableNonEditableOField($oField, $totale_ambito_editable);
        $oField->default_value = $relation_exists;
    }
    $oRecord->addContent($oField, "totale_ambiti");
}

function inizializzaTotaleRelations($oRecord, $frmAction) {
    $id_totale = $oRecord->key_fields["ID_totale"]->value->getValue();
    if($frmAction == "" && isset($id_totale)) {
        $totale = new ValutazioniTotale($id_totale);

        //Inizializzazione checkbox categorie
        $categorie_associate = $totale->getCategorieTotale();
        ValutazioniHelper::inizializzaCheckbox($oRecord, "categoria_", $categorie_associate);

        //Inizializzazione checkbox ambiti
        $ambiti_associati = $totale->getAmbitiTotale();
        ValutazioniHelper::inizializzaCheckbox($oRecord, "ambito_", $ambiti_associati);
    }
}

function updateCategoriaAssociata($id_totale, $categorie_associate, $key, $fieldValue) {
    $id_categoria = ValutazioniHelper::getIdFromFieldKey($key);
    $relationExists = isset($categorie_associate[$id_categoria]);

    if(!$relationExists && $fieldValue) {
        //Caso insert
        $totale_categoria = new ValutazioniTotaleCategoria();
        $totale_categoria->id_totale = $id_totale;
        $totale_categoria->id_categoria = $id_categoria;
        $totale_categoria->insert();
    } elseif($relationExists && !$fieldValue) {
        //Caso delete
        $totale_categoria = ValutazioniTotaleCategoria::factoryFromTotaleCategoria($id_totale, $id_categoria);
        return $totale_categoria->delete();        
    }
    return true;
}

function updateAmbitoAssociato($id_totale, $ambiti_associati, $key, $fieldValue) {
    $id_ambito = ValutazioniHelper::getIdFromFieldKey($key);
    $relationExists = isset($ambiti_associati[$id_ambito]);

    if(!$relationExists && $fieldValue) {
        //Caso insert
        $totale_ambito = new ValutazioniTotaleAmbito();
        $totale_ambito->id_totale = $id_totale;
        $totale_ambito->id_ambito = $id_ambito;
        $totale_ambito->insert();        
    } elseif($relationExists && !$fieldValue) {
        //Caso delete
        $totale_ambito = ValutazioniTotaleAmbito::factoryFromTotaleAmbito($id_totale, $id_ambito);
        return $totale_ambito->delete();
    }

    return true;
}

function updateTotaleRelations($oRecord, $frmAction) {
    if($frmAction == "insert" || $frmAction == "update") {
        $id_totale = $oRecord->key_fields["ID_totale"]->value->getValue();
        $totale = new ValutazioniTotale($id_totale);
        $categorie_associate = $totale->getCategorieTotale();
        $ambiti_associati = $totale->getAmbitiTotale();

        foreach($oRecord->form_fields as $key => $formField) {
            if(strpos($key, "categoria_") !== false) {
                if(!updateCategoriaAssociata($totale->id, $categorie_associate, $key, $formField->value->getValue())) {
                    return CoreHelper::setError($oRecord,"Operazione non riuscita");
                }
            } elseif(strpos($key, "ambito_") !== false) {
                if(!updateAmbitoAssociato($totale->id, $ambiti_associati, $key, $formField->value->getValue())) {//In realtà non si dovrebbe entrare in questa casistica, checkbox sono disabilitati se non modificabili
                    return CoreHelper::setError($oRecord,"Operazione non riuscita");
                }
            }
        }
    }
}
$oRecord->addEvent("on_do_action", "checkRelations");
$oRecord->addEvent("on_do_action", "inizializzaTotaleRelations");
$oRecord->addEvent("on_done_action", "updateTotaleRelations");
$cm->oPage->addContent($oRecord);

//on_do_action
function checkRelations($oRecord, $frmAction) {
    $id_totale = $oRecord->key_fields["ID_totale"]->value->getValue();
    if(isset($id_totale) && $id_totale != "") {
        $totale = new ValutazioniTotale($id_totale);
    }

    switch($frmAction) {
        case "confirmdelete":
            if(!$totale->delete()) {
                return CoreHelper::setError($oRecord,"Il totale selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "Il totale selezionato non può essere modificato.";
            //In questo caso, tale condizione non dovrebbe essere mai vera (vincoli grafici)
            if(!$totale->canDelete()) {
                $non_editable_fields = array(
                    "anno_inizio" => $totale->anno_inizio,
                    "ordine_visualizzazione" => $totale->ordine_visualizzazione,
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }
            break;
    }
}