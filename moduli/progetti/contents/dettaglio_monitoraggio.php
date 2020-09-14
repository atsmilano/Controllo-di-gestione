<?php
$url = $_REQUEST["ret_url"];

if (strpos($url, "?oracle_erp=") !== false) {
    $index_puntoDomanda = (strpos($url, '?', 0) + 1);
    $index_primaECommerciale  = (strpos($url, '&', $index_puntoDomanda) + 1);
    $start = substr($url, 0, $index_puntoDomanda);
    $end = substr($url, $index_primaECommerciale);
    $url = $start.$end;
    $_REQUEST["ret_url"] = $url;
}

if (isset ($_REQUEST["oracle_erp"])) {
    $oracle_erp = $_REQUEST["oracle_erp"];

    $parameters_index = strpos($url, '?', 0) + 1; // così includo anche il ?
    $first_part = substr($url, 0, $parameters_index);
    $first_part .= "oracle_erp=".urlencode($oracle_erp)."&";
    $url = $first_part.substr($url, $parameters_index);
    $_REQUEST["ret_url"] = $url;
}

$user = LoggedUser::Instance();
$is_monitoraggio_insert = true;
if (isset ($_REQUEST["keys[ID]"])) {
    try {        
        $progetto = new ProgettiProgetto($_REQUEST["keys[ID]"]);
        $numero_monitoraggio = ProgettiMonitoraggio::getNextNumeroMonitoraggio($progetto->id);
    }
    catch (Exception $ex){
        ffErrorHandler::raise("Errore nel passaggio dei parametri relativi al progetto");
    }
}

if (isset ($_REQUEST["keys[ID_progetti_monitoraggio]"])) {
    try {        
        $monitoraggio = new ProgettiMonitoraggio($_REQUEST["keys[ID_progetti_monitoraggio]"]);       
        $is_monitoraggio_insert = false;
    }
    catch (Exception $ex){
        ffErrorHandler::raise("Errore nel passaggio dei parametri relativi al monitoraggio");
    }
}

// Solo il Responsabile di Progetto può inserire/aggiornare/eliminare i monitoraggi
$edit_responsabile_progetto = false;
if ($user->matricola_utente_selezionato == $progetto->matricola_responsabile_progetto) {
    $edit_responsabile_progetto = true;
}

$is_concluso = false;
if ($progetto->stato == "3") {
    $is_concluso = true;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "monitoraggio";
$oRecord->title = ($is_monitoraggio_insert ? "Nuovo monitoraggio" : "Modifica monitoraggio");
$oRecord->resources[] = "monitoraggio";
$oRecord->src_table = "progetti_monitoraggio";
$oRecord->addHiddenField("ID_progetti_progetto", new ffData($progetto->id, "Number"));
if (!$is_monitoraggio_insert) {
    $oRecord->addHiddenField("ID_tipologia_monitoraggio", new ffData($monitoraggio->id_tipologia_monitoraggio, "Number"));
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_progetti_monitoraggio";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "numero_monitoraggio";
$oField->base_type = "Number";
$oField->label = "Numero monitoraggio";
$oField->default_value = new ffData($numero_monitoraggio, "Number");
$oField->control_type = "label";
$oField->required = true;
if (!$edit_responsabile_progetto) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

foreach (ProgettiLibreriaTipologiaMonitoraggio::getAll(array("record_attivo" => 1)) AS $tipologia_monitoraggio) {
    $tipologia_monitoraggio_select[] = array(
        new ffData ($tipologia_monitoraggio->id, "Number"),
        new ffData ($tipologia_monitoraggio->descrizione_tipologia_monitoraggio, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipologia_monitoraggio";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipologia_monitoraggio_select;
$oField->label = "Tipologia monitoraggio";
$oField->required = true;
if (!$is_monitoraggio_insert) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->default_value = new ffData($monitoraggio->id_tipologia_monitoraggio, "Number");
    $oField->data_type = "";
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_fase";
$oField->base_type = "Text";
$oField->label = "Descrizione fase";
$oField->required = true;
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "costi_sostenuti";
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->label = "Costi sostenuti";
$oField->required = true;
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_utilizzo_risorse";
$oField->base_type = "Text";
$oField->label = "Descrizione utilizzo risorse";
$oField->required = true;
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "note_rispetto_risorse_previste";
$oField->base_type = "Text";
$oField->label = "Note rispetto risorse previste";
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "note_rispetto_tempistiche";
$oField->base_type = "Text";
$oField->label = "Note rispetto tempistiche";
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "note_replicabilita_progetto";
$oField->base_type = "Text";
$oField->label = "Note replicabilita progetto";
if (!$edit_responsabile_progetto || $is_concluso) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oRecord->addContent("<hr />");

$indicatore_list = ProgettiProgettoIndicatore::getAll(array("record_attivo" => 1, "ID_progetto" => $progetto->id));

if (count($indicatore_list) > 0) {
    foreach ($indicatore_list AS $indicatore) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_progetti_progetto_indicatore_" . $indicatore->id;
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->store_in_db = false;
        $oRecord->addKeyField($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "descrizione_" . $indicatore->id;
        $oField->data_source = "descrizione";
        $oField->base_type = "Text";
        $oField->label = "Indicatore";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->default_value = new ffData(
            $indicatore->descrizione,
            "Text"
        );
        $oField->data_type = "";
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "valore_atteso_" . $indicatore->id;
        $oField->data_source = "valore_atteso";
        $oField->base_type = "Text";
        $oField->label = "Valore atteso";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->default_value = new ffData(
            $indicatore->valore_atteso,
            "Text"
        );
        $oField->data_type = "";
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "valore_consuntivo_" . $indicatore->id;
        $oField->data_source = "valore_consuntivo";
        $oField->base_type = "Text";
        $oField->label = "Valore consuntivato";
        $oField->store_in_db = false;
        $oField->required = true;
        if (!$edit_responsabile_progetto || $is_concluso) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
            $valore = ProgettiMonitoraggioIndicatore::factoryFromMonitoraggioIndicatore(
                $monitoraggio->id, $indicatore->id
            );

            $oField->display_value = new ffData(
                $valore->valore,
                "Text"
            );
            $oField->data_type = "";
        }
        else if (!$is_monitoraggio_insert) {
            $valore = ProgettiMonitoraggioIndicatore::factoryFromMonitoraggioIndicatore(
                $monitoraggio->id, $indicatore->id
            );

            $oField->default_value = new ffData(
                $valore->valore,
                "Text"
            );
            $oField->data_type = "";
            $oField->label = "Valore consuntivato (precedente: ".$valore->valore.")";
        }

        $oRecord->addContent($oField);
    }
} else {    
    $oRecord->addContent("<p>Non sono stati trovati indicatori per questo progetto </p>");
    $oRecord->allow_insert = false;
}

$oRecord->addEvent("on_done_action", "myPrjInsertMonitoraggioIndicatore");
$oRecord->addEvent("on_done_action", "myPrjDeleteMonitoraggioIndicatore");
$oRecord->addEvent("on_done_action", "myPrjUpdateMonitoraggioIndicatore");

$oRecord->insert_additional_fields["ID_progetto"] = new ffData($progetto->id, "Number");
$oRecord->insert_additional_fields["time_modifica"] = new ffData(date("Y-m-d H:i:s"), "Datetime");
$oRecord->insert_additional_fields["record_attivo"] = new ffData(1, "Number");

$oRecord->update_additional_fields["time_modifica"] = new ffData(date("Y-m-d H:i:s"), "Datetime");
$oRecord->update_additional_fields["record_attivo"] = new ffData(1, "Number");

//JS per intercettare click sul pulsante Indietro
$oRecord->addContent("
    <script type='text/javascript'>        
        $('div#monitoraggio.ffRecord>div.actions.-sticky.-bottom.col-xs-12.text-right>a.btn.btn-link').click(function (){
            var url = $('div#monitoraggio.ffRecord>div.actions.-sticky.-bottom.col-xs-12.text-right>a.btn.btn-link').attr('href');
            var oracle_erp = '".$oracle_erp."';
            var parameters_index = (url.indexOf('?') + 1);
            var first_part = url.substring(0, parameters_index);
            first_part += ('oracle_erp='+encodeURI(oracle_erp)+'&');
            url = first_part + url.substring(parameters_index);
            $('div#monitoraggio.ffRecord>div.actions.-sticky.-bottom.col-xs-12.text-right>a.btn.btn-link').attr('href', url);
        });                
    </script>
");

if (!$is_monitoraggio_insert) {
    $oRecord->allow_insert = false;
}
if (!$edit_responsabile_progetto || $is_concluso) {
    $oRecord->allow_insert = false;
    $oRecord->allow_update = false;
    $oRecord->allow_delete = false;
}
$cm->oPage->addContent($oRecord);

function myPrjInsertMonitoraggioIndicatore($oRecord, $frmAction) {
    if (!empty($frmAction) && $frmAction == "insert") {
        $progetto = new ProgettiProgetto($oRecord->hidden_fields["ID_progetti_progetto"]->getValue());

        /*
         * Recupero l'ultimo ID inserito nella tabella progetti_monitoraggio:
         * servirà quando si procederà con l'insert nella tabella
         * progetti_monitoraggio_indicatore
         */
        $progetti_monitoraggio_last_id = $oRecord->key_fields["ID_progetti_monitoraggio"]->getValue();

        /*
         * Recupero della lista degli indicatori associati al progetto
         * per recuperare il valore consuntivato
         */
        $query_values = array();
        $indicatore_list = ProgettiProgettoIndicatore::getAll(array("record_attivo" => 1, "ID_progetto" => $progetto->id));
        foreach ($indicatore_list as $indicatore) {
            $valore = $oRecord->form_fields["valore_consuntivo_" . $indicatore->id]->getValue();

            $query_values[] = array(
                "ID_monitoraggio" => $progetti_monitoraggio_last_id,
                "ID_indicatore" => $indicatore->id,
                "valore" => $valore,
                "time_modifica" => date("Y-m-d H:i:s"),
                "record_attivo" => 1
            );
        }

        ProgettiMonitoraggioIndicatore::save($query_values);
        
        $monitoraggio = new ProgettiMonitoraggio($progetti_monitoraggio_last_id);
        $tipologia_monitoraggio = new ProgettiLibreriaTipologiaMonitoraggio($monitoraggio->id_tipologia_monitoraggio);

        // Monitoraggio finale: chiusura del progetto che avrà stato in attesa di validazione
        if ($tipologia_monitoraggio->id == 2) {
            $progetto->stato = new ffData("4", "Text");
            $progetto->time_modifica = new ffData(date("Y-m-d H:i:s"), "Datetime");
            $progetto->record_attivo = new ffData(1, "Number");

            try {
                $save_status = $progetto->saveChiudiProgetto();
            } catch (Exception $exc_query_update) {
                $message = $exc_query_update->getMessage();

                $save_status = false;
            }

            if ($save_status) {
                mod_notifier_add_message_to_queue("Il progetto \"" . $progetto->titolo_progetto . "\" è in attesa di validazione", MOD_NOTIFIER_SUCCESS);
            } else {
                mod_notifier_add_message_to_queue("Problemi nell'inserimetno del monitoraggio finale progetto \"" . $progetto->titolo_progetto . "\": " . $message, MOD_NOTIFIER_ERROR);
            }
        }
    }
}

function myPrjDeleteMonitoraggioIndicatore($oRecord, $frmAction) {
    if (!empty($frmAction) && ($frmAction == "delete" || $frmAction == "confirmdelete")) {      
        $id_monitoraggio_deleted = $oRecord->key_fields["ID_progetti_monitoraggio"]->getValue();

        ProgettiMonitoraggioIndicatore::deleteRecord(array("ID_monitoraggio" => $id_monitoraggio_deleted));
        $progetto = new ProgettiProgetto($oRecord->hidden_fields["ID_progetti_progetto"]->getValue());
        $id_tipologia_monitoraggio = $oRecord->hidden_fields["ID_tipologia_monitoraggio"]->getValue();

        // Se il progetto è in attesa di validazione ed il monitoraggio è finale, allora si procede con il cmabio distato
        if ($progetto->stato == "4" && $id_tipologia_monitoraggio == "2") {
            $progetto->stato = new ffData("1", "Text");
            $progetto->time_modifica = new ffData(date("Y-m-d H:i:s"), "Datetime");
            $progetto->stato = new ffData(1, "Number");

            $progetto->saveChiudiProgetto();
        }
    }
}

function myPrjUpdateMonitoraggioIndicatore($oRecord, $frmAction) {
    if (!empty($frmAction) && $frmAction == "update") {
        $id_progetto = $oRecord->hidden_fields["ID_progetti_progetto"]->getValue();
        // Recupero progetti_monitoraggio.ID appena eliminato
        $id_monitoraggio_updated = $oRecord->key_fields["ID_progetti_monitoraggio"]->getValue();

        $indicatori_lists = ProgettiProgettoIndicatore::getAll(array(
            "record_attivo" => 1,
            "ID_progetto" => $id_progetto
        ));

        $update_me = array();
        foreach ($indicatori_lists as $indicatori) {
            $new_value = $oRecord->form_fields["valore_consuntivo_".$indicatori->id]->getValue();

            $update_me[] = array(
                "ID_monitoraggio" => new ffData($id_monitoraggio_updated, "Number"),
                "ID_indicatore" => new ffData($indicatori->id, "Number"),
                "valore" => new ffData($new_value, "Text"),
                "time_modifica" => new ffData(date("Y-m-d H:i:s"), "Datetime"),
                "record_attivo" => new ffData(1, "Number")
            );
        }
        ProgettiMonitoraggioIndicatore::update($id_monitoraggio_updated, $update_me);
    }
}