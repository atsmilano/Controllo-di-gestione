<?php 
$user = LoggedUser::getInstance();
//verifica che il cdr sia di responsabilità dell'utente
if (!$user->hasPrivilege("riesame_direzione_view")){
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per visualizzare la pagina");
}

$edit_admin = false;
if ($user->hasPrivilege("riesame_direzione_admin")){
    $edit_admin = true;
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];

if (isset($_REQUEST["keys[ID_cdr]"])){
    $cdr_riesame = new Cdr($_REQUEST["keys[ID_cdr]"]);
    $tipo_cdr_riesame = new TipoCdr($cdr_riesame->id_tipo_cdr);
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_cdr");
}

if (isset($_REQUEST["keys[ID]"]) && strlen($_REQUEST["keys[ID]"])){
    $riesame = new RiesameDirezioneRiesame($_REQUEST["keys[ID]"]); 
    $id_stato = $riesame->getIdStato();
}
else {
    $riesame = null;
    $id_stato = 0;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "riesame-modify";
$oRecord->title = "Riesame Direzione anno ".$anno->descrizione ." - ".$tipo_cdr_riesame->abbreviazione." ".$cdr_riesame->descrizione." (".$cdr_riesame->codice.")";
$oRecord->resources[] = "riesame";
$oRecord->src_table = "riesame_direzione_riesame";
$oRecord->allow_delete = false;

//se il riesame non è compilato e l'utente è responsabile del cdr che si sta considerando è permessa la modifica
$allow_edit = false;
if ($id_stato !== 2){
    $responsabile_cdr_riesame = $cdr_riesame->getResponsabile($dateTimeObject);        
    if ($responsabile_cdr_riesame->matricola_responsabile == $user->matricola_utente_selezionato) {
        $allow_edit = true;
    }
}
if ($allow_edit == false) {
    $oRecord->allow_insert = false;
    if ($edit_admin == true) {
        $oRecord->allow_update = true;
    }
    else {
        $oRecord->allow_update = false;
    }
}
//evento per il salvataggio dei dati
$oRecord->addEvent("on_done_action", "myUpdate");

// *********** FIELDS ****************
//Campi tabella riesame_direzione_riesame
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields["codice_cdr"] =  new ffData($cdr_riesame->codice, "Text"); 
$oRecord->insert_additional_fields["ID_anno_budget"] =  new ffData($anno->id, "Number"); 

//data chiusura
if ($edit_admin == true && $riesame !== null){
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura";
    $oField->widget = "datepicker";  
    $oRecord->addContent($oField, "approvazione");    
} 

//generazione dinamica dei campi e delle sezioni
$id_sezione_attuale = 0;
//vengono visualizzati i campi nell'ordine in base al db, le sezioni vengono create automaticamente al cambio di sezione
//in caso di sequenza di campi appartenenti ad esempio a sezione1 - sezione2 - sezione1 in ogni caso verrà visualizzata la prima sezione ed il terzo campo accodato a quella
foreach (RiesameDirezioneCampo::getCampiAnno($anno) as $campo) {
    if ($id_sezione_attuale !== $campo->id_sezione) {
        //cambio sezione attuale
        $id_sezione_attuale = $campo->id_sezione;
        $sezione = new RiesameDirezioneSezione($id_sezione_attuale);
        
        //creazione fieldset sezione
        $oRecord->addContent(null, true, "sezione_".$sezione->id);
        $oRecord->groups["sezione_".$sezione->id]["title"] = $sezione->descrizione;
        
    }
    
    //costruzione dinamica del campo
    $oField = ffField::factory($cm->oPage);
    $oField->id = "campo_".$campo->id;    
    //recupero del valore del campo
    $valore = null;    
    if ($riesame !== null) {
        $valore = $campo->getValoreCampoRiesame($riesame);
    }
    
    $oField->data_type = "";
    //tipo del campo in base a definizione db
    if ($campo->id_tipo_campo == 1){
        $oField->base_type = "Text";
        $oField->extended_type = "Text";
        $oField->default_value = new ffData($valore, "Text");
    }
    else if ($campo->id_tipo_campo == 2) {        
        $oField->base_type = "Number";
        $oField->extended_type = "Selection";
        $oField->control_type = "radio";
        $oField->multi_pairs = array (
                                    array(new ffData("1", "Number"), new ffData("Si", "Text")),
                                    array(new ffData("2", "Number"), new ffData("No", "Text")),
                   );
        $oField->default_value = new ffData($valore, "Number");        
    }
    else {
        ffErrorHandler::raise("Errore di configurazione dei campi");
    }  
    //modificabilità del campo
    if ($allow_edit !== true){
        $oField->multi_select_one_label = "";
        $oField->control_type = "label";        
    }
    $oField->store_in_db = false;
    $oField->label = $campo->descrizione;
    $oRecord->addContent($oField, "sezione_".$sezione->id);
}

//*********************BUTTON AZIONI*****************************************	
if ($allow_edit == true){				
    $confirm_title = "Chiusura riesame";
    $label = "Chiusura riesame";
    $html_message = "
                        Chiudendo il riesame non sar&agrave; pi&ugrave; possibile apportare modifiche.							
                        <br><br>
                        Confermare la chiusura del riesame?
                    ";
	
	$oBt = ffButton::factory($cm->oPage);
	$oBt->id = "action_button_chiusura";
	$oBt->label = $label;
	$oBt->action_type = "submit";
	$oBt->jsaction = "$('#inactive_body').show();$('#conferma_chiusura').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit btn-success";
	$oRecord->addActionButton($oBt);
			
	$cm->oPage->addContent("<div id='inactive_body'></div>
							<div id='conferma_chiusura' class='conferma_azione'>
								<h3>".$confirm_title."</h3>
								<p>".$html_message."</p>
								<a id='conferma_si' class='confirm_link'>Conferma</a>
								<a id='conferma_no' class='confirm_link'>Annulla</a>
							</div>
							<script>
								$('#conferma_si').click(function(){
									document.getElementById('frmAction').value = 'riesame-modify_chiusura';
									document.getElementById('frmMain').submit();
								});
								$('#conferma_no').click(function(){
									$('#inactive_body').hide();
									$('#conferma_chiusura').hide();
									$('#action_button_chiusura').prop('disabled', false);
									$('#action_button_chiusura').prop('style', false);
									$('#action_button_chiusura').val('" . $label . "');	
								});
							</script>
							");
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

//funzione per il salvataggio dei dati
function myUpdate($oRecord, $frmAction){ 
    $cm = cm::getInstance();
    $dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
    $riesame = new RiesameDirezioneRiesame($oRecord->key_fields["ID"]->value->getValue());    
    $id_stato = $riesame->getIdStato();    
    //viene modificato il valore dei campi solamente se il riesame non risulta chiuso
    if ($id_stato !== 2) {                
        //in caso di chiusura viene salvata la data chiusura 
        if ($frmAction == "chiusura") {
            //se ci si trova in inserimento (oggetto riesame senza id) vengono salvati i campi
            if ($riesame->id == null){
                $riesame->codice_cdr = $oRecord->insert_additional_fields["codice_cdr"]->getValue();
                $riesame->id_anno_budget = $oRecord->insert_additional_fields["ID_anno_budget"]->getValue();
            }            
            $riesame->data_chiusura = date("Y-m-d H:i:s");              
            try{				
                $riesame->id = $riesame->save();
                mod_notifier_add_message_to_queue("Riesame chiuso con successo", MOD_NOTIFIER_SUCCESS);
            } catch (Exception $ex) {
                mod_notifier_add_message_to_queue("Errore durante la chiusura del riesame", MOD_NOTIFIER_ERROR);
            }	        
            //viene vsualizzato l'esito
            if (strlen($messaggio_errore) > 0) {
                ffErrorHandler::raise($messaggio_errore);
            }
            if (isset($_GET["ret_url"])){
                $ret_url = $_GET["ret_url"];
            }
            else {
                $ret_url = FF_SITE_PATH;
            }
        }
        //la verifica del cdr viene sempre fatta a monte del record, viene utilizzato l'id in richiesta piuttosto che recuperare
        //il cdr dal codice per velocità        
        $cdr_riesame = new Cdr($_REQUEST["keys[ID_cdr]"]);
        $responsabile_cdr_riesame = $cdr_riesame->getResponsabile($dateTimeObject);
        $user = LoggedUser::getInstance();        
        if ($responsabile_cdr_riesame->matricola_responsabile == $user->matricola_utente_selezionato) {
            $cm = cm::getInstance();
            $anno = $cm->oPage->globals["anno"]["value"];
            foreach (RiesameDirezioneCampo::getCampiAnno($anno) as $campo) {
                $valore = $oRecord->form_fields["campo_".$campo->id]->value->getValue();
                $campo->salvaValoreCampoRiesame($riesame, $valore);
            }
        }
        if ($frmAction == "chiusura") {
            ffRedirect($ret_url);
        }
    }
}