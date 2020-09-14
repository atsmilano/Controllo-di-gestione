<?php
$anno_budget = $cm->oPage->globals["anno"]["value"];
$date_object = $cm->oPage->globals["data_riferimento"]["value"];

if (isset($_REQUEST["keys[ID_personale]"])) {
    $id_personale = $_REQUEST["keys[ID_personale]"];
    
    try {
        $personale = new PersonaleObiettivi($id_personale);
        $personale_cdr_responsabilita = ResponsabileCdr::getResponsabiliCdrCessatiInAnno($anno_budget, $personale->matricola);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    throw new Exception("Errore nel passaggio dei parametri: ID_personale");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "responsabili-cessati-modify";
$oRecord->title = $personale->cognome." ".$personale->nome." (matr. ".$personale->matricola.
    ") - Assegnazione obiettivi individuali";
$oRecord->resources[] = "responsabili-cessati";
$oRecord->src_table = "obiettivi_obiettivo_cdr";
$oRecord->allow_delete = false;
$oRecord->allow_update = false;
$oRecord->allow_insert = false;

$grid_fields = array("id_obiettivo_cdr", "codice", "titolo", "cdr", "peso");
$grid_recordset = array();

foreach($personale_cdr_responsabilita as $cdr_responsabilita) {
    $cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr_responsabilita->codice_cdr, $date_object);
    $obiettivi_cdr = $cdr->getObiettiviCdrAnno($anno_budget);

    foreach($obiettivi_cdr as $item) {
        $obiettivi_cdr_associati = $item->getObiettivoCdrPersonaleAssociati($personale->matricola);
        if (empty($obiettivi_cdr_associati)) {
            $obiettivo = new ObiettiviObiettivo($item->id_obiettivo);

            $grid_recordset[] = array(
                $item->id,
                $anno_budget->descrizione."-".str_pad($obiettivo->codice_incr_anno, 4, "0", STR_PAD_LEFT),
                $obiettivo->titolo,
                $cdr->codice." - ".$cdr->descrizione,
                $item->peso
            );
        }
    }
}
        
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "assegnazione-cdr";
$oGrid->title = "Obiettivi assegnati ai CdR";
$oGrid->resources[] = "obiettivo-cdr-".$cdr->codice;
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray(
    $grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr"
);
$oGrid->order_default = "cdr";
$oGrid->record_id = "obiettivo-cdr-modify";
$oGrid->order_method = "labels";
$oGrid->display_search = false;
$oGrid->record_url = "";
$oGrid->display_new = false;
$oGrid->use_paging = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "id_obiettivo_cdr";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "id_obiettivo_cdr";
$oField->base_type = "Text";
$oField->label = "Assegnare?";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo";
$oField->base_type = "Text";
$oField->label = "Titolo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->base_type = "Text";
$oField->label = "Peso";
$oGrid->addContent($oField);

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

if (!empty($grid_recordset)) {
    // Viene visualizzato il pulsante se ci sono obiettivi da assegnare
    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "action_button_assegna";
    $oBt->label = "Assegnare obiettivi";
    $oBt->action_type = "submit";
    $oBt->jsaction = "$('#inactive_body').show();$('#conferma_assegnazione').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-arrows-alt";
    $oRecord->addActionButton($oBt);
    $cm->oPage->addContent("
        <div id='inactive_body'></div>
        <div id='conferma_assegnazione' class='conferma_azione'>
            <h3>Confermare l'assegnazione degli obiettivi selezionati?</h3>
            <a id='conferma_si' class='confirm_link'>Conferma</a>
            <a id='conferma_no' class='confirm_link'>Annulla</a>
        </div>
        <script>
            $(function(){ 
                $('table>thead>tr>th.cel-1>input').remove()
            });
            $('#conferma_si').click(function(){
                ff.ajax.ctxDoAction('responsabili-cessati-modify', 'obiettivo_assegnare', 'responsabili-cessati-modify_');
                $('#conferma_assegnazione').hide();
                ff.ajax.ctxClose('responsabili-cessati-modify');
            });
            $('#conferma_no').click(function(){
                $('#inactive_body').hide();
                $('#conferma_assegnazione').hide();
            });


        </script>
    ");
}

CoreHelper::refreshTabOnDialogClose('responsabili-cessati-modify');

$oRecord->addEvent("on_do_action", "myAssegnazioneObiettivo");
$cm->oPage->addContent($oRecord);

function myAssegnazioneObiettivo($oRecord, $frmAction) {
    if ($frmAction == "obiettivo_assegnare") {
        if (isset($_REQUEST["keys[ID_personale]"])) {
            try {
                $id_personale = $_REQUEST["keys[ID_personale]"];
                $personale = new PersonaleObiettivi($id_personale);
                $obiettivo_cdr_list = $_REQUEST["assegnazione-cdr_recordset_keys"];
                $checked = $_REQUEST["assegnazione-cdr_recordset_values"];

                foreach ($checked as $key => $value) {
                    $id_obiettivo_cdr = $obiettivo_cdr_list[$key]["id_obiettivo_cdr"];
                    $obiettivo_cdr = new ObiettiviObiettivoCdr($id_obiettivo_cdr);
                    $obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale();
                    $obiettivo_cdr_personale->id_obiettivo_cdr = $id_obiettivo_cdr;
                    $obiettivo_cdr_personale->matricola_personale = $personale->matricola;
                    $obiettivo_cdr_personale->peso = $obiettivo_cdr->peso;
                    $obiettivo_cdr_personale->save();
                }

                mod_notifier_add_message_to_queue(
                    "Assegnazione obiettivi individuali effettuata con successo",
                    MOD_NOTIFIER_SUCCESS
                );

            } catch (Exception $e) {
                mod_notifier_add_message_to_queue(
                    "Errore durante l'assegnazione degli obiettivi individuali",
                    MOD_NOTIFIER_ERROR
                );

            }
        }
    }

    $oRecord->skip_action = true;
}