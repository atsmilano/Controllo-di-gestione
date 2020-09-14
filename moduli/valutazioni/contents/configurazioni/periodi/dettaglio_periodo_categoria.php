<?php
if (isset($_REQUEST["keys[id_periodo]"])) {
    $id_periodo = $_REQUEST["keys[id_periodo]"];
    try {
        $periodo = new ValutazioniPeriodo($id_periodo);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
} else {
    ffErrorHandler::raise("id_periodo non definito");
}

$isEdit = false;
if (isset($_REQUEST["keys[ID_periodo_categoria]"])) {
    $isEdit = true;
    $id_periodo_categoria = $_REQUEST["keys[ID_periodo_categoria]"];
    try {
        $periodo_categoria = new ValutazioniPeriodoCategoria($id_periodo_categoria);
        $categoria = $periodo->getCategoriaPeriodo($id_periodo_categoria);
    }
    catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "periodo-categoria-modify"; // Lo stesso di $oGrid->record_id (index.php)
$oRecord->title = $isEdit ? "Modifica tipologia scheda" : "Nuova tipologia scheda";
$oRecord->title .= " per il periodo '$periodo->descrizione'";
$oRecord->resources[] = "periodo-categoria"; // Lo stesso di $oGrid->resources[] (index.php)
$oRecord->src_table  = "valutazioni_periodo_categoria";
$editable = !$isEdit || ($isEdit && $periodo_categoria->canDelete());
$oRecord->allow_delete = $editable;
$oRecord->allow_update = $editable;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_categoria";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
if($isEdit) {
    $periodo_categoria_ambiti = ValutazioniPeriodoCategoriaAmbito::getAll(array("ID_periodo_categoria" => $id_periodo_categoria));
}
$categoria_select = array();
foreach(ValutazioniCategoria::getAll() as $obj) {
    $filters = array(
        "ID_periodo" => $id_periodo,
        "ID_categoria" => $obj->id
    );
    $relationExists = count(ValutazioniPeriodoCategoria::getAll($filters)) > 0;

    $categoriaSelezionabile = true;
    if($isEdit) {
        $categoriaSelezionabile = isCategoriaSelezionabile($periodo_categoria_ambiti, $obj, $periodo_categoria);
    }

    if((!$relationExists && $categoriaSelezionabile) || ($isEdit && $categoria->id == $obj->id)) {
        $categoria_select[] = array(
            new ffData($obj->id, "Number"),
            new ffData($obj->descrizione." (".$obj->abbreviazione.")", "Text")
        );
    }
}

$oRecord->allow_insert = !$isEdit && count($categoria_select) > 0;
if(!$isEdit && !$oRecord->allow_insert) {
    CoreHelper::setError($oRecord, "Inserimento disabilitato: nessuna categoria selezionabile.");
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_categoria";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $categoria_select;
$oField->label = "Tipologia scheda";
$oField->required = true;
CoreHelper::disableNonEditableOField($oField, $editable);
$oRecord->addContent($oField);

//Aggiungo l'ID_periodo alla query di inserimento
$oRecord->insert_additional_fields["ID_periodo"] = new ffData($id_periodo, "Number");

if ($isEdit) {
    $oRecord->addContent(null, true, "periodo_categoria_ambito_group"); // Utilizzare con il secondo gruppo
    $oRecord->groups["periodo_categoria_ambito_group"]["title"] = "Periodo - Tipologia scheda - Ambiti";

    $grid_fields = array(
        "ID_periodo_categoria_ambito",
        "descrizione",
        "autovalutazione_attiva",
        "inibizione_visualizzazione_punteggi"
    );
    $grid_recordset = array();

    $periodo_categorie_ambiti = ValutazioniPeriodoCategoriaAmbito::getAll(array("id_periodo_categoria" => $id_periodo_categoria));
    foreach ($periodo_categorie_ambiti as $periodo_categoria_ambito) {
        $ambito = new ValutazioniAmbito($periodo_categoria_ambito->id_ambito);
        $autovalutazione_attiva = "";
        if(isset($periodo_categoria_ambito->autovalutazione_attiva) && $periodo_categoria_ambito->autovalutazione_attiva != "") {
            $autovalutazione_attiva = $periodo_categoria_ambito->autovalutazione_attiva
                ? "Si"
                : "No"
            ;
        }
        $inibizione_visualizzazione_punteggi = "";
        if(isset($periodo_categoria_ambito->inibizione_visualizzazione_punteggi) && $periodo_categoria_ambito->inibizione_visualizzazione_punteggi != "") {
            $inibizione_visualizzazione_punteggi = $periodo_categoria_ambito->inibizione_visualizzazione_punteggi
                ? "Si"
                : "No"
            ;
        }
        $grid_recordset[] = array(
            $periodo_categoria_ambito->id,
            $ambito->descrizione,
            $autovalutazione_attiva,
            $inibizione_visualizzazione_punteggi
        );
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "valutazioni_periodo_categoria_ambito";    
    $oGrid->resources[] = "periodo-categoria-ambito";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "valutazioni_periodo_categoria_ambito");
    $oGrid->order_default = "ID_periodo_categoria_ambito";
    $oGrid->record_id = "periodo-categoria-ambito-modify";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_periodo_categoria_ambito";
    $oGrid->record_url = $record_url;
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;
    $oGrid->display_search = false;
    $oGrid->use_search = false;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_periodo_categoria_ambito";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField, "periodo_categoria_ambito_group");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField, "periodo_categoria_ambito_group");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "autovalutazione_attiva";
    $oField->base_type = "Text";
    $oField->label = "Autovalutazione attiva";
    $oGrid->addContent($oField, "periodo_categoria_ambito_group");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "inibizione_visualizzazione_punteggi";
    $oField->base_type = "Text";
    $oField->label = "Inibizione visualizzazione punteggi";
    $oGrid->addContent($oField, "periodo_categoria_ambito_group");
    $oGrid->addEvent("on_before_parse_row", "checkPeriodoCategoriaAmbitoEliminabile");
    $js = "     const dialog_url = ff.ffPage.dialog.get('periodo-modify').params.url;
     const refresh = 'ff.ffPage.dialog.goToUrl(\'periodo-modify\',\''+dialog_url+'\')';
     ff.ffPage.dialog.get('periodo-categoria-modify').params.callback = refresh;
     //console.log(ff.ffPage.dialog.get('periodo-categoria-modify').params);";

    $oGrid->buttons_options["delete"]["class"] = "deletebt";
    $oRecord->addContent($oGrid, "periodo_categoria_ambito_group");
    $cm->oPage->addContent($oGrid);
}

$oRecord->addEvent("on_do_action", "checkRelations");

$cm->oPage->addContent($oRecord);

function checkPeriodoCategoriaAmbitoEliminabile($oGrid) {
    $id_periodo_categoria_ambito = $oGrid->key_fields["ID_periodo_categoria_ambito"]->value->getValue();
    $periodo_categoria_ambito = new ValutazioniPeriodoCategoriaAmbito($id_periodo_categoria_ambito);
    $oGrid->display_delete_bt = $periodo_categoria_ambito->canDelete();
}

function checkDetailRelations($oDetail, $frmAction) {
    if($frmAction == "detail_delete") {
        //Viene recuperato l'indice del "detail" rimosso
        $delete_row = $oDetail->parent[0]->retrieve_param($oDetail->id, "delete_row");

        //Viene recuperato l'id dell'istanza nel db corrispondenete al detail rimosso
        $deleted_key = $oDetail->recordset_ori[$delete_row]["ID_valutazioni_periodo_categoria_ambito"]->getValue();

        //Viene recuperata l'istanza PeriodoCategoriaAmbito tramite ID
        if(isset($deleted_key) && $deleted_key != "") {
            $periodo_categoria_ambito = new ValutazioniPeriodoCategoriaAmbito($deleted_key);
            if(!$periodo_categoria_ambito->canDelete()) {
                return CoreHelper::setError($oDetail, "L'elemento non può essere cancellato.");
            }
        }
    }
}

function checkRelations($oRecord, $frmAction) {
    //Viene recuperata l'istanza PeriodoCategoria
    $id_periodo_categoria = $oRecord->key_fields["ID_periodo_categoria"]->value->getValue();
    if(isset($id_periodo_categoria) && $id_periodo_categoria != "") {
        $periodo_categoria = new ValutazioniPeriodoCategoria($id_periodo_categoria);
    }

    switch($frmAction) {
        case "confirmdelete":
            /*
             * Condizione TRUE solo quando a un elemento modificabile viene aggiunta una relazione tale da renderlo
             * non più modificabile. Aggiornata la pagina/chiuso e riaperto il dialog, la condizione non può più verificarsi
             * per via dei vincoli lato interfaccia utente (pulsanti nascosti, action inibite).
             */
            if(!$periodo_categoria->delete()) {
                return CoreHelper::setError($oRecord, "L'elemento periodo-categoria selezionato non può essere eliminato.");
            }
            $oRecord->skip_action = true; //Si bypassa l'esecuzione della query di delete del record
            break;
        case "update":
            $record_update_error_msg = "L'elemento periodo-tipologia scheda selezionato non può essere modificato.";

            $id_categoria = $oRecord->form_fields["ID_categoria"];
            if($id_categoria->value->getValue() != $id_categoria->value_ori->getValue()) {
                $new_categoria = new ValutazioniCategoria($id_categoria->value->getValue());
                $periodo_categoria_ambiti = ValutazioniPeriodoCategoriaAmbito::getAll(array("ID_periodo_categoria" => $id_periodo_categoria));
                if(!isCategoriaSelezionabile($periodo_categoria_ambiti, $new_categoria, $periodo_categoria)) {
                    $error_msg = "
                        La tipologia scheda selezionata non è valida: l'attuale istanza periodo - tipologia scheda 
                        possiede relazioni con ambiti non valutati per la tipologia scheda selezionata 
                        (".$new_categoria->descrizione.") nell'anno di budget a cui appartiene il periodo";

                        return CoreHelper::setError($oRecord, $error_msg);
                }
            }
            if(!$periodo_categoria->canDelete()) {
                $non_editable_fields = array(
                    "ID_categoria" => $periodo_categoria->id_categoria
                );

                if (CoreHelper::isNonEditableFieldUpdated($oRecord, $non_editable_fields)) {
                    return CoreHelper::setError($oRecord, $record_update_error_msg);
                }
            }
            break;
    }
}

function isCategoriaSelezionabile($periodo_categoria_ambiti, ValutazioniCategoria $categoria, ValutazioniPeriodoCategoria $periodo_categoria) {
    foreach($periodo_categoria_ambiti as $periodo_categoria_ambito) {
        $ambito = new ValutazioniAmbito($periodo_categoria_ambito->id_ambito);
        $periodo = new ValutazioniPeriodo($periodo_categoria->id_periodo);
        $annoBudget = new ValutazioniAnnoBudget($periodo->id_anno_budget);

        if(!$ambito->isValutatoCategoriaAnno($categoria, $annoBudget)) {
            return false;
        }
    }

    return true;
}