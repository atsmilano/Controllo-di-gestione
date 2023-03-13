<?php
//librerie per tooltip
CoreHelper::includeJqueryUi();

$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
//viene recuperato il cdr dal piano di priorità massima definito
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $piano_cdr)->cloneAttributesToNewObject("CdrRU");

//aggiunta del parametro per la gestione del record
$accettazione = $cdr->getAccettazioneAnno($anno);
$user = LoggedUser::getInstance();
if ($user->hasPrivilege("ru_admin")) {
    $is_admin = true;
}
else {
    $is_admin = false;
}

$grid_cdr_recordset = array();
$grid_ramo_da_approvare_recordset = array();
$richieste_aperte = 0;
$tr_title_js = "";
foreach ($cdr->getRichiesteCompetenzaRamoCdrAnno($anno) as $richiesta) {
    $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr)->cloneAttributesToNewObject("CdrRU");
    $ultima_accettazione = $richiesta->getUltimaAccettazioneData($data_riferimento, $cdr);
    $is_approvazione_competenza = $richiesta->isApprovazioneCompetenza($cdr, $anno, $data_riferimento, $piano_cdr);
    
    $qualifica = new QualificaInterna($richiesta->id_qualifica_interna);
    $ruolo = new Ruolo($qualifica->id_ruolo);
    $tipologia = new RUTipoRichiesta($richiesta->id_tipo_richiesta);
    $tipo_cdr = new TipoCdr($cdr_creazione->id_tipo_cdr);
    $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();

    $record = array(
        $richiesta->id,
        $cdr_creazione->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_creazione->descrizione,
        $ruolo->descrizione,
        $qualifica->descrizione,
        $richiesta->qta,
        $tipologia->descrizione,
        $id_stato_avanzamento,       
    );    
    $note_accettazione = ($ultima_accettazione!==null && $ultima_accettazione->data_accettazione!==null)?CoreHelper::formatUiDate($ultima_accettazione->data_accettazione, "Y-m-d H:i:s") . " - " . $ultima_accettazione->note:"Nessuna accettazione";
    $tr_title_js .= "<script>$('.richiesta_".$richiesta->id."').prop('title', '".$note_accettazione."');</script>";
    
    if ($richiesta->codice_cdr_creazione == $cdr->codice) {
        $grid_cdr_recordset[] = $record;
        if ($id_stato_avanzamento  == 1){
            $richieste_aperte++;
        }
    }
    else if($is_approvazione_competenza && $stato_avanzamento["esito"] !== "ko" && $id_stato_avanzamento <= 3){
        $grid_ramo_da_approvare_recordset[] = $record;
    } 
    else if ($stato_avanzamento["esito"] !== "ko" && $id_stato_avanzamento > 2){
        $grid_ramo_non_da_approvare_recordset[] = $record;
    }
}

//verifica possibilità di accettazione
$allow_accettazione = false;
$allow_salvataggio_note = false;
if ($accettazione == null || $accettazione->data_accettazione == null) {
    $allow_salvataggio_note = true;
}
if ($richieste_aperte == 0 && empty($grid_ramo_da_approvare_recordset) && $allow_salvataggio_note == true) {
    $allow_accettazione = true;
}

//record accettazione
//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "accettazione";
$oRecord->title = "Accettazione richieste";
$oRecord->resources[] = "accettazione";
$oRecord->ajax = true;
$oRecord->skip_action = true;
$oRecord->fixed_pre_content = "<p>E' necessario processare tutte le richieste di competenza ed avanzare le proprie per poter procedere con l'accettazione.</p>";        

$oRecord->src_table = "ru_accettazione";
$oRecord->allow_delete = false;
$oRecord->buttons_options["cancel"]["display"] = false;
if ($allow_salvataggio_note == false) {    
    if ($allow_accettazione == false) {
        $oRecord->allow_update = false;
        $oRecord->allow_insert = false;
    }    
}
else {        
    $oRecord->buttons_options["insert"]["label"] = $oRecord->buttons_options["update"]["label"] = "Salvataggio note accettazione";
}
    
if ($is_admin) {
    $oRecord->allow_update = true;
    $oRecord->allow_insert = true;
    if ($allow_accettazione == false) {
        $oRecord->buttons_options["insert"]["label"] = $oRecord->buttons_options["update"]["label"] .= " modifica data";
    }
}

//salvataggio della data di accettazione su ogni sngolo obiettivo_cdr_personale
$oRecord->addEvent("on_do_action", "updateRichieste", null, 0, null, null, array(array("allow_accettazione" => $allow_accettazione, "cdr" => $cdr, "anno"=>$anno)));

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_accettazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->store_in_db = false;

$oRecord->addKeyField($oField);

if (!$allow_accettazione || $is_admin) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_accettazione";
    $oField->label = "Data accettazione"; 
    $oField->base_type = "DateTime";
    $oField->default_value = new ffData($accettazione->data_accettazione, "DateTime");
    if ($is_admin){        
        $oField->widget = "datepicker";
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }    
    $oRecord->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "note";
$oField->label = "Note";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->data_type = "";
$oField->default_value = new ffData($accettazione->note, "Text");
if ($allow_salvataggio_note == false) {
    if (strlen($accettazione->note)==0) {
        $oField->default_value = new ffData("Nessuna nota.", "Text");
        $oField->data_type = "";
    }   
    $oField->control_type = "label";
}
$oField->store_in_db = false;
$oRecord->addContent($oField);

//buttons
if ($allow_accettazione == true) {    
    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "accettazione_bt";
    $oBt->label = "Salvataggio e chiusura richieste";
    $oBt->action_type = "submit";
    $oBt->jsaction = "$('#inactive_body').show();$('#conferma_chiusura').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit";
    $oRecord->addActionButton($oBt);
    
    $cm->oPage->addContent("<div id='inactive_body'></div>
                            <div id='conferma_chiusura' class='conferma_azione'>
                                    <h3>Confermare la chiusura delle richieste?</h3>
                                    <p>Procedendo non sarà più possibile approvare nuove richieste o inoltrarne di proprie.</p>
                                    <a id='conferma_si' class='confirm_link'>Conferma</a>
                                    <a id='conferma_no' class='confirm_link'>Annulla</a>
                            </div>
                            <script>
                                    $('#conferma_si').click(function(){                                                                                       
                                            ff.ajax.doRequest({'component' : 'accettazione', 'action' : 'accettazione_chiusura'})
                                            $('#inactive_body').hide();
                                            $('#conferma_chiusura').hide();
                                            $('#accettazione_accettazione_bt').prop('disabled', false);
                                            $('#accettazione_accettazione_bt').prop('style', false);
                                    });
                                    $('#conferma_no').click(function(){
                                            $('#inactive_body').hide();
                                            $('#conferma_chiusura').hide();
                                            $('#accettazione_accettazione_bt').prop('disabled', false);
                                            $('#accettazione_accettazione_bt').prop('style', false);	
                                    });
                            </script>
                            ");
}    
        
//codice_cdr
$oRecord->insert_additional_fields["codice_cdr"] = new ffData($cdr->codice, "Text");
//ID_anno_budget
$oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");

//salvataggio data accettazione
function updateRichieste($oRecord, $frmAction, $args) {
    if ($frmAction == "insert" || $frmAction == "update" || $frmAction == "chiusura") {                  
        $allow_accettazione = $args["allow_accettazione"];
        $anno = $args["anno"];
        $cdr = $args["cdr"]; 
        $accettazione = $cdr->getAccettazioneAnno($anno);
        $user = LoggedUser::getInstance();
        if ($user->hasPrivilege("ru_admin")) {
            $is_admin = true;
        }
        else {
            $is_admin = false;
        }
        if($accettazione == null) {
            $accettazione = new RUAccettazione();
        }
        if ($allow_accettazione || $is_admin) {                                                          
            if ($frmAction == "chiusura") {
                $accettazione->data_accettazione = date("Y-m-d H:i:s"); 
            }            
            else {
                $accettazione->data_accettazione = null;
            }            
            if ($is_admin && ($oRecord->form_fields["data_accettazione"]->value_ori->getValue() !== $oRecord->form_fields["data_accettazione"]->value->getValue())) {
                $accettazione->data_accettazione = $oRecord->form_fields["data_accettazione"]->value->getValue("Date", "ISO9075");               
            }            
        }    
        $accettazione->codice_cdr = $cdr->codice;            
        $accettazione->id_anno_budget = $anno->id;            
        $accettazione->note = $oRecord->form_fields["note"]->value->getValue();
        $accettazione->save();
        $cm = cm::getInstance();
        $module = core\Modulo::getCurrentModule();
        ffRedirect(FF_SITE_PATH . "/area_riservata".$module->site_path."/richieste/richieste_competenza?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    }
}

if (count($grid_ramo_da_approvare_recordset)) {   
    $oGrid = RURichiesta::getGridRichieste("richieste-ramo-approvare-cdr", "Richieste di competenza da approvare", $grid_ramo_da_approvare_recordset);    
    $oGrid->order_default = "stato_avanzamento";
    $oGrid->display_new = false;    
    $oGrid->display_delete_bt = false;
    $oGrid->display_search = false;
    $oGrid->use_search = false;
    $oGrid->addEvent("on_before_parse_row", "initGrid");
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
}

$oGrid = RURichiesta::getGridRichieste("richieste-cdr", "Richieste effettuate dal Cdr", $grid_cdr_recordset);
//creazione garantita solamente ai cdr abilitati nell'anno
if ($cdr->isCdrAbilitatoAnno($anno) && $allow_salvataggio_note) {
    $oGrid->display_new = true;
}
else {
    $oGrid->display_new = false;
}
$oGrid->display_delete_bt = false;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

if (count($grid_ramo_non_da_approvare_recordset)) {   
    $oGrid = RURichiesta::getGridRichieste("richieste-ramo-no-approvare-cdr", "Richieste del ramo gerarchico", $grid_ramo_non_da_approvare_recordset);
    $oGrid->display_new = false;    
    $oGrid->display_delete_bt = false;
    $oGrid->display_search = false;
    $oGrid->use_search = false;
    $oGrid->addEvent("on_before_parse_row", "initGrid");
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
}

$cm->oPage->addContent($oRecord);

function initGrid ($oGrid) {    
    $oGrid->row_class = "richiesta_". $oGrid->key_fields["ID"]->value->getValue();
}

//javascript per gestione tooltip
$cm->oPage->addContent("<script>$(document).tooltip();</script>");
$cm->oPage->addContent($tr_title_js);