<?php
$anno_budget = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

if (isset($_REQUEST["keys[ID_personale]"])) {
    $id_personale = $_REQUEST["keys[ID_personale]"];
    
    try {
        $personale = new PersonaleObiettivi($id_personale);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    throw new Exception("Errore nel passaggio dei parametri: ID_personale");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "personale-modify";
$oRecord->title = $personale->cognome." ".$personale->nome." (matr. ".$personale->matricola.
    ") - Elenco obiettivi individuali";
$oRecord->resources[] = "personale";
$oRecord->src_table = "obiettivi_obiettivo_cdr_personale";
$oRecord->allow_delete = false;
$oRecord->allow_update = false;
$oRecord->allow_insert = false;

$grid_fields = array(
    "id_obiettivo_cdr_personale",
    "cdr",
    "codice_obiettivo",
    "titolo_obiettivo",
    "peso_obiettivo_cdr",
    "peso_obiettivo_cdr_personale"
);
$grid_recordset = array();
foreach (ObiettiviObiettivoCdrPersonale::getAll(array("matricola_personale" => $personale->matricola)) as $obiettivo_cdr_personale) {
    if (empty($obiettivo_cdr_personale->data_eliminazione)) {
        $obiettivo_cdr = new ObiettiviObiettivoCdr($obiettivo_cdr_personale->id_obiettivo_cdr);
        $anagrafica_cdr = AnagraficaCdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $data_riferimento);            
        if (empty($obiettivo_cdr->data_eliminazione)) {
            // Obiettivo CdR non eliminato
            $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);                       
            if (empty($obiettivo->data_eliminazione) && $obiettivo->id_anno_budget == $anno_budget->id) {
                // Vengono aggiunti gli obiettivi che NON sono stati eliminati e che appartengono all'anno budget
                $tipo_cdr = new TipoCdr($anagrafica_cdr->id_tipo_cdr);
                $grid_recordset[] = array(
                    $obiettivo_cdr_personale->id, 
                    $anagrafica_cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $anagrafica_cdr->descrizione,
                    $anno_budget->descrizione."-".str_pad($obiettivo->codice_incr_anno, 4, "0", STR_PAD_LEFT),
                    $obiettivo->titolo,                    
                    $obiettivo_cdr->peso,
                    $obiettivo_cdr_personale-> peso
                );
            }
        }
    }
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "assegnazione-cdr-personale";
$oGrid->title = "Obiettivi assegnati alla risorsa umana";
$oGrid->resources[] = "obiettivo-cdr-personale-".$personale->matricola;
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr_personale");
$oGrid->order_default = "codice_obiettivo";
$oGrid->record_id = "obiettivo-cdr-personale-modify";
$oGrid->order_method = "labels";
$oGrid->display_search = false;
$oGrid->record_url = "";
$oGrid->display_new = false;
$oGrid->use_paging = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "id_obiettivo_cdr_personale";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "id_obiettivo_cdr_personale";
$oField->base_type = "Number";
$oField->label = "Eliminare?";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_obiettivo";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo_obiettivo";
$oField->base_type = "Text";
$oField->label = "Titolo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso_obiettivo_cdr";
$oField->base_type = "Number";
$oField->label = "Peso CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso_obiettivo_cdr_personale";
$oField->base_type = "Number";
$oField->label = "Peso CdR personale";
$oGrid->addContent($oField);

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

$cm->oPage->addContent("
    <script>
        $(function(){ 
            $('table>thead>tr>th.cel-1>input').remove()
        });
    </script>
");

if (!empty($grid_recordset)) {
    // Viene visualizzato il pulsante se ci sono obiettivi da eliminare
    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "action_button_elimina";
    $oBt->label = "Eliminare obiettivi";
    $oBt->action_type = "submit";
    $oBt->jsaction = "$('#inactive_body').show();$('#conferma_eliminazione').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-trash-o";
    $oRecord->addActionButton($oBt);
    $cm->oPage->addContent("
        <div id='inactive_body'></div>
        <div id='conferma_eliminazione' class='conferma_azione'>
            <h3>Confermare l'eliminazione degli obiettivi selezionati?</h3>
            <a id='conferma_si' class='confirm_link'>Conferma</a>
            <a id='conferma_no' class='confirm_link'>Annulla</a>
        </div>
        <script>
            $('#conferma_si').click(function(){
                ff.ajax.ctxDoAction('personale-modify', 'obiettivo_eliminare', 'personale-modify_');
                $('#conferma_eliminazione').hide();
                ff.ajax.ctxClose('personale-modify');
            });
            $('#conferma_no').click(function(){
                $('#inactive_body').hide();
                $('#conferma_eliminazione').hide();
            });
        </script>
    ");    
}

CoreHelper::refreshTabOnDialogClose('personale-modify');

$oRecord->addEvent("on_do_action", "myEliminazioneObiettivo");
$cm->oPage->addContent($oRecord);

function myEliminazioneObiettivo($oRecord, $frmAction) {
    if ($frmAction == "obiettivo_eliminare") {
        if (isset($_REQUEST["keys[ID_personale]"])) {
            try {
                $id_personale = $_REQUEST["keys[ID_personale]"];
                $obiettivo_cdr_personale_list = $_REQUEST["assegnazione-cdr-personale_recordset_keys"];
                $checked = $_REQUEST["assegnazione-cdr-personale_recordset_values"];
                
                foreach ($checked as $key => $value) {
                    $id_obiettivo_cdr_personale = $obiettivo_cdr_personale_list[$key]["id_obiettivo_cdr_personale"];
                    $obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale($id_obiettivo_cdr_personale);
                    $obiettivo_cdr_personale->logicalDelete();
                }
                
                mod_notifier_add_message_to_queue(
                    "Eliminazione obiettivi individuali effettuata con successo",
                    MOD_NOTIFIER_SUCCESS
                );
                
            } catch (Exception $ex) {
                mod_notifier_add_message_to_queue(
                    "Errore durante l'assegnazione degli obiettivi individuali",
                    MOD_NOTIFIER_ERROR
                );
            }
        }
    }
    
    $oRecord->skip_action = true;
}