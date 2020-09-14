<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_periodo_categoria_ambito]"])) {
    $isEdit = true;
    $id_periodo_categoria_ambito = $_REQUEST["keys[ID_periodo_categoria_ambito]"];
    try {
        $periodo_categoria_ambito = new ValutazioniPeriodoCategoriaAmbito($id_periodo_categoria_ambito);
        $ambito_selected = new ValutazioniAmbito($periodo_categoria_ambito->id_ambito);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

if (isset($_REQUEST["keys[ID_periodo_categoria]"])) {
    $id_periodo_categoria = $_REQUEST["keys[ID_periodo_categoria]"];
    try {
        $periodo_categoria = new ValutazioniPeriodoCategoria($id_periodo_categoria);
        $categoria = new ValutazioniCategoria($periodo_categoria->id_categoria);
        $periodo = new ValutazioniPeriodo($periodo_categoria->id_periodo);
        $annoBudget = new ValutazioniAnnoBudget($periodo->id_anno_budget);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
} else {
    ffErrorHandler::raise("id_periodo_categoria non definito");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-categoria-ambito-modify";
$oRecord->title = $isEdit ? "Modifica ambito" : "Nuovo ambito";
$oRecord->resources[] = "periodo-categoria-ambito";
$oRecord->src_table  = "valutazioni_periodo_categoria_ambito";
$editable = !$isEdit || ($isEdit && $periodo_categoria_ambito->canDelete());
$oRecord->allow_delete = $editable;
$oRecord->allow_update = $editable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_categoria_ambito";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$ambito_select = array();
foreach(ValutazioniAmbito::getAll() as $ambito) {
    $filters = array(
        "ID_periodo_categoria" => $id_periodo_categoria,
        "ID_ambito" => $ambito->id,
    );
    $relationExists = count(ValutazioniPeriodoCategoriaAmbito::getAll($filters)) > 0;
    if((!$relationExists || ($isEdit && $ambito->id == $ambito_selected->id)) && $ambito->isValutatoCategoriaAnno($categoria, $annoBudget)) {
        $sezione = new ValutazioniSezione($ambito->id_sezione);
        $ambito_select[] = array(
            new ffData($ambito->id, "Number"),
            new ffData($sezione->codice . "." . $ambito->codice.". ".$ambito->descrizione, "Text")
        );
    }
}

$oRecord->allow_insert = !$isEdit && count($ambito_select) > 0;
if(!$isEdit && !$oRecord->allow_insert) {
    CoreHelper::setError($oRecord, "Inserimento disabilitato: nessun ambito selezionabile.");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_ambito";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $ambito_select;
$oField->label = "Ambito";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "autovalutazione_attiva";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = array (
    array(
        new ffData("1", "Number"),
        new ffData("Si", "Text")
    ),
    array(
        new ffData("0", "Number"),
        new ffData("No", "Text")
    ),
);
$oField->label = "Autovalutazione attiva";
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "inibizione_visualizzazione_punteggi";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = array (
    array(
        new ffData("1", "Number"),
        new ffData("Si", "Text")
    ),
    array(
        new ffData("0", "Number"),
        new ffData("No", "Text")
    ),
);
$oField->label = "Inibizione visualizzazione punteggi";
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

//Viene aggiunto l'ID_periodo_categoria alla query di inserimento
$oRecord->insert_additional_fields["ID_periodo_categoria"] = new ffData($id_periodo_categoria, "Number");

$oRecord->addEvent("on_do_action", "checkRelations");
$cm->oPage->addContent($oRecord);

//N.B. per i radio button presenti in questa form, se non viene specificato alcun valore, viene salvata una stringa vuota
function checkRelations($oRecord, $frmAction) {
    //Viene recuperata l'istanza PeriodoCategoriaAmbito
    $id_periodo_categoria_ambito = $oRecord->key_fields["ID_periodo_categoria_ambito"]->value->getValue();
    if(isset($id_periodo_categoria_ambito) && $id_periodo_categoria_ambito != "") {
        $periodo_categoria_ambito = new ValutazioniPeriodoCategoriaAmbito($id_periodo_categoria_ambito);
    }

    switch($frmAction) {
        case "confirmdelete":
            if (!$periodo_categoria_ambito->delete()) {
                return CoreHelper::setError($oRecord, "L'elemento periodo-categoria-ambito selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true; //Viene bypassata l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "L'elemento periodo-categoria-ambito selezionato non può essere modificato.";
            if (!$periodo_categoria_ambito->canDelete()) {
                $non_editable_fields = array(
                    "ID_ambito" => $periodo_categoria_ambito->id_ambito,
                    "autovalutazione_attiva" => $periodo_categoria_ambito->autovalutazione_attiva,
                    "inibizione_visualizzazione_punteggi" => $periodo_categoria_ambito->inibizione_visualizzazione_punteggi
                );
                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }
            break;
    }

}
$cm->oPage->addContent("
<script>
     const dialog_url = ff.ffPage.dialog.get('periodo-categoria-modify').params.url;
     const refresh = 'ff.ffPage.dialog.goToUrl(\'periodo-categoria-modify\',\''+dialog_url+'\')';
     ff.ffPage.dialog.get('periodo-categoria-ambito-modify').params.callback = refresh;
     console.log(ff.ffPage.dialog.get('periodo-categoria-ambito-modify').params);
</script>
");