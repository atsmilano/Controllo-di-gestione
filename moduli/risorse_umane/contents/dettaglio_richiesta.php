<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("ru_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

//recupero globals e info cdr
$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
//viene recuperato il cdr dal piano di priorità massima definito
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $data_riferimento->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $piano_cdr)->cloneAttributesToNewObject("CdrRU");
//******************************************************************************
//gestione privilegi
$privilegi_utente = array(
                        "admin" => false,
                        //richiesta
                        "view_richiesta" => false,
                        "edit_richiesta" => false,
                        //cdr padre
                        "view_cdr_padre" => false,
                        "edit_cdr_padre" => false,
                        //programmazione strategica
                        "view_programmazione_strategica" => false,
                        "edit_programmazione_strategica" => false,
                        //direzione riferimento
                        "view_direzione_riferimento" => false,
                        "edit_direzione_riferimento" => false,
                        //dg
                        "view_dg" => false,
                        "edit_dg" => false,
                        //uo competente
                        "view_uo_competente" => false,
                        "edit_uo_competente" => false,
                        //monitoraggio
                        "view_monitoraggio" => false,
                        "edit_monitoraggio" => false,                        
                        );
if ($user->hasPrivilege("ru_admin")) {
    $privilegi_utente["admin"] = true;
}
//edit richiesta cdr se abilitato e cdr selezionato o inseirmento se cdr abilitato
//cdr padre se abilitato
$nuova_richiesta = true;
$allow_edit = false;
$id_stato_avanzamento = 0;

//vengono replicati i controlli in fase di azioni su record (evento myUpdate)
if (isset ($_REQUEST["keys[ID]"])) {
    $nuova_richiesta = false;
    $richiesta = new RURichiesta($_REQUEST["keys[ID]"]);    
    $cdr_creazione = Cdr::factoryFromCodice($richiesta->codice_cdr_creazione, $piano_cdr)->cloneAttributesToNewObject("CdrRU");    
    $responsabile_cdr = $cdr->getResponsabile($data_riferimento);
    $id_stato_avanzamento = $richiesta->getIdStatoAvanzamento();
    //richiesta modificabile solamente dal responsabile del cdr selezionato, se abilitato    
    if ($id_stato_avanzamento == 1) {        
        $accettazione = $cdr->getAccettazioneAnno($anno);
        if (($accettazione == null || $accettazione->data_accettazione == null) && $user->hasPrivilege("ru_richiesta_edit") && $user->hasPrivilege("resp_cdr_selezionato")){
            //verifica che la modifica sta avvenendo da parte del cdr creatore della richiesta
            if ($richiesta->codice_cdr_creazione !== $cdr->codice) {
                ffErrorHandler::raise("Errore: l'utente non ha la competenza per la modifica della richiesta.");
            }   
            $privilegi_utente["view_richiesta"] = true;
            $privilegi_utente["edit_richiesta"] = true;
            $allow_edit = true;
        }
    }
    else if ($id_stato_avanzamento > 1) {
        $privilegi_utente["view_richiesta"] = true;
        $cdr_padre_abilitato = $cdr_creazione->getCdrPadreAbilitato($anno);
        if ($cdr_padre_abilitato !== null) {
            $responsabile_cdr_padre_abilitato = $cdr_padre_abilitato->getResponsabile($data_riferimento);
        }
    }
    //cdr padre
    if ($id_stato_avanzamento == 2) {
        if ($user->hasPrivilege("ru_richiesta_edit") && $responsabile_cdr_padre_abilitato->matricola_responsabile == $user->matricola_utente_selezionato){
            $privilegi_utente["view_cdr_padre"] = true;
            $privilegi_utente["edit_cdr_padre"] = true;
            $allow_edit = true;
        }
    }
    else if ($id_stato_avanzamento > 2 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 9)) {
        if ($richiesta->id_parere_cdr_padre !== null) {
            $privilegi_utente["view_cdr_padre"] = true;
        }
        $cdr_padre_strategico = $cdr_creazione->getCdrPadreProgrammazioneStrategica($anno);
        if ($cdr_padre_strategico !== null) {
            $responsabile_cdr_padre_strategico = $cdr_padre_strategico->getResponsabile($data_riferimento);
        }        
    }
    if ($id_stato_avanzamento == 3) {        
        if ($user->hasPrivilege("ru_programmazione_strategica_edit") && $responsabile_cdr_padre_strategico->matricola_responsabile == $user->matricola_utente_selezionato){            
            $privilegi_utente["view_programmazione_strategica"] = true;
            $privilegi_utente["edit_programmazione_strategica"] = true; 
            $allow_edit = true;
        }
    }
    else if ($id_stato_avanzamento > 3 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 10)) {
        if ($richiesta->id_parere_cdr_strategico !== null) {
            $privilegi_utente["view_programmazione_strategica"] = true;
        }
        $cdr_direzione_riferimento = $cdr_creazione->getCdrDirezioneRiferimento($anno);
        if ($cdr_direzione_riferimento !== null) {
            $responsabile_direzione_riferimento = $cdr_direzione_riferimento->getResponsabile($data_riferimento);
        }        
    }
    if ($id_stato_avanzamento == 4) {        
        if ($user->hasPrivilege("ru_direzione_riferimento_edit") && $responsabile_direzione_riferimento->matricola_responsabile == $user->matricola_utente_selezionato) {
            $privilegi_utente["view_direzione_riferimento"] = true;
            $privilegi_utente["edit_direzione_riferimento"] = true;
            $allow_edit = true;
        }
    }
    else if ($id_stato_avanzamento > 4 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 11)) {
        if ($richiesta->id_parere_direzione_riferimento !== null) {
            $privilegi_utente["view_direzione_riferimento"] = true;
        }
    }
    if ($id_stato_avanzamento == 5  && $user->hasPrivilege("ru_dg_edit")) {
        if (!$cdr->isDgAnno($anno)) {
            ffErrorHandler::raise("Errore: l'utente non ha la competenza per la modifica della richiesta.");
        }
        $privilegi_utente["view_dg"] = true;
        $privilegi_utente["edit_dg"] = true;
        $allow_edit = true;
    }
    else if ($id_stato_avanzamento > 5 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 12)) {
        $privilegi_utente["view_dg"] = true;
    }
    if ($id_stato_avanzamento == 6  && $user->hasPrivilege("ru_uo_competente_edit")) {
        if (!$cdr->isUOCompetenteAnno($anno)) {
            ffErrorHandler::raise("Errore: l'utente non ha la competenza per la modifica della richiesta.");
        }
        $privilegi_utente["view_uo_competente"] = true;
        $privilegi_utente["edit_uo_competente"] = true;
        $allow_edit = true;
    }
    else if ($id_stato_avanzamento > 6 && !($id_stato_avanzamento >8 && $id_stato_avanzamento < 13)) {
        $privilegi_utente["view_uo_competente"] = true;
    }
    if ($id_stato_avanzamento == 7  && $user->hasPrivilege("ru_uo_competente_edit")) {
        if (!$cdr->isUOCompetenteAnno($anno)) {
            ffErrorHandler::raise("Errore: l'utente non ha la competenza per la modifica della richiesta.");
        }
        $privilegi_utente["view_monitoraggio"] = true;
        $privilegi_utente["edit_monitoraggio"] = true;
        $allow_edit = true;
    }
    else if ($id_stato_avanzamento > 7 && !($id_stato_avanzamento >8 && $id_stato_avanzamento <= 13)) {
        $privilegi_utente["view_monitoraggio"] = true;
    } 
}
else if ($user->hasPrivilege("ru_richiesta_edit") && $user->hasPrivilege("resp_cdr_selezionato")){        
    $accettazione = $cdr->getAccettazioneAnno($anno);     
    if ($accettazione == null || $accettazione->data_accettazione == null) {
        $id_stato_avanzamento = 1;
        $privilegi_utente["view_richiesta"] = true;
        $privilegi_utente["edit_richiesta"] = true;
        $allow_edit = true;
    }
}
//se l'utente non ha nessun privilegio sulla richiesta viene generato errore
if ($id_stato_avanzamento == 0) {
    ffErrorHandler::raise("Errore: impossibile determinare lo stato di avanzamento della richiesta.");
}
//******************************************************************************
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "richiesta";
$oRecord->title = $nuova_richiesta ? "Nuova richiesta risorse umane" : "Dettaglio richiesta risorse umane";
$oRecord->resources[] = "richiesta";
$oRecord->src_table  = "ru_richiesta";

//visualizzazione action
$oRecord->allow_delete = false;
$oRecord->allow_insert = false;
$oRecord->allow_update = false;
if ($allow_edit) {    
    if ($nuova_richiesta) {        
        $oRecord->allow_insert = true;        
    }
    else {
        $oRecord->allow_update = true;
    }
}
if ($privilegi_utente["admin"]) {
    $oRecord->allow_update = true;
}
        
// KEY FIELD
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//******************************************************************************
//richiesta
if ($privilegi_utente["view_richiesta"]) {
    $oRecord->addContent(null, true, "richiesta");
    $oRecord->groups["richiesta"]["title"] = "Richiesta";
    
    if (!$nuova_richiesta) {        
        $dipendente_creazione = Personale::factoryFromMatricola($richiesta->matricola_creazione);
        //*************************************      
        //cdr creazione
        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr_creazione";
        $oField->base_type = "Text";    
        $oField->label = "Cdr creazione";
        $oField->default_value = new ffData($cdr_creazione->codice." - ".(strlen($cdr_creazione->abbreviazione)>0?$cdr_creazione->abbreviazione." - ":"").$cdr_creazione->descrizione, "Text");
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oRecord->addContent($oField, "richiesta");
        //*************************************
        //matricola creazione
        $oField = ffField::factory($cm->oPage);
        $oField->id = "matricola_creazione";
        $oField->base_type = "Text";    
        $oField->label = "Creata da: ";
        $oField->default_value = new ffData($dipendente_creazione->cognome." ".$dipendente_creazione->nome." (".$dipendente_creazione->matricola.")", "Text");
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oRecord->addContent($oField, "richiesta");
        //*************************************
        //stato avanzamento richiesta
        $stati_avanzamento = RURichiesta::getStatiAvanzamento();
        $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column($stati_avanzamento, 'ID'));        
        $stato_avanzamento = $stati_avanzamento[$index_stato_avanzamento]["descrizione"];
        $oField = ffField::factory($cm->oPage);
        $oField->id = "stato_avanzamento";
        $oField->base_type = "Text";    
        $oField->label = "Stato avanzamento richiesta";
        $oField->default_value = new ffData($stato_avanzamento, "Text");
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oRecord->addContent($oField, "richiesta");
    }
    else {
        $oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");
        $oRecord->insert_additional_fields["matricola_creazione"] = new ffData($user->matricola_utente_selezionato, "Text");
        $oRecord->insert_additional_fields["codice_cdr_creazione"] = new ffData($cdr->codice, "Text");
        $oRecord->insert_additional_fields["data_creazione"] = new ffData(date("Y-m-d H:i:s"), "Datetime");
    }
    //*************************************
    //cdc e cdr richiesta
    //recupero dei cdr e dei cdc della gerarchia di competenza
    $cdr_multipairs = array();
    $cdc_multipairs = array();        
    if (isset($richiesta) && !$privilegi_utente["edit_richiesta"]) {       
        $cdc_predefinito = Cdc::factoryFromCodice($richiesta->codice_cdc, $piano_cdr);
        $cdr_predefinito = new CdrRU($cdc_predefinito->id_cdr);     
        $tipo_cdr = new TipoCdr($cdr_predefinito->id_tipo_cdr);
        $cdc_multipairs[] =
                array(
                    new ffData ($cdr_predefinito->id, "Number"),
                    new ffData ($cdc_predefinito->codice, "Text"),                                               
                    new ffData ($cdc_predefinito->codice." - ". $cdc_predefinito->descrizione, "Text"),                                                 
                    );
        $cdr_multipairs[] =
                    array(
                            new ffData ($cdr_predefinito->id, "Number"),
                            new ffData ($cdr_predefinito->codice." - ". $tipo_cdr->abbreviazione . " - " . $cdr_predefinito->descrizione, "Text"),						
                            );        
    }
    else {
        foreach ($cdr->getGerarchiaCompetenzaRamoCdrAnno($anno) as $cdr_figlio) {
            $cdr_figlio = $cdr_figlio["cdr"];        
            $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);
            $cdr_multipairs[] =
                    array(
                            new ffData ($cdr_figlio->id, "Number"),
                            new ffData ($cdr_figlio->codice." - ". $tipo_cdr->abbreviazione . " - " . $cdr_figlio->descrizione, "Text"),						
                            );
            foreach ($cdr_figlio->getCdc() as $cdc) {
                //preselezione del cdr                 
                if (isset($richiesta) && $richiesta->codice_cdc == $cdc->codice) {   
                    $cdr_predefinito = $cdr_figlio;
                    $cdc_predefinito = $cdc;
                }
                $cdc_multipairs[] =
                    array(
                        new ffData ($cdr_figlio->id, "Number"),
                        new ffData ($cdc->codice, "Text"),                                               
                        new ffData ($cdc->codice." - ". $cdc->descrizione, "Text"),                                                 
                        );
            }
        }
    }
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdr";
    $oField->label = "Cdr destinazione della richiesta";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->base_type = "Number";
        $oField->extended_type = "Selection";
        $oField->widget = "activecomboex";
        $oField->multi_pairs = $cdr_multipairs;
        $oField->actex_child = "codice_cdc";
        $oField->actex_update_from_db = false;

    }
    else {
        $oField->base_type = "Text";
        $tipo_cdr_predefinito = new TipoCdr($cdr_predefinito->id_tipo_cdr);
        $oField->display_value = new ffData($cdr_predefinito->codice." - ". $tipo_cdr_predefinito->abbreviazione . " - " . $cdr_predefinito->descrizione, "Text");
        $oField->control_type = "label";
        $oField->store_in_db = false;    
    }
    $oField->data_type = "";
    $oField->default_value = new ffData($cdr_predefinito->id, "Number");
    $oField->store_in_db = false;
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdc";
    $oField->base_type = "Text";
    $oField->label = "Cdc richiesta";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->extended_type = "Selection";
        $oField->widget = "activecomboex";
        $oField->multi_pairs = $cdc_multipairs;
        $oField->actex_father = "codice_cdr";    
        $oField->actex_update_from_db = false;
        $oField->required = true;    
    }
    else {
        $oField->base_type = "Text";
        $oField->display_value = new ffData($cdc_predefinito->codice." - " . $cdc_predefinito->descrizione, "Text");
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oField->data_type = "";
    $oField->default_value = new ffData($cdc_predefinito->codice, "Text");
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    //tipo richiesta
    $tipo_richiesta_multipairs = array();
    foreach(RUTipoRichiesta::getAll() as $tipo_richiesta){
        if (CoreHelper::annoInIntervallo($anno->descrizione, $tipo_richiesta->anno_inizio, $tipo_richiesta->anno_termine)) {
            $tipo_richiesta_multipairs[] =
                array(
                    new ffData ($tipo_richiesta->id, "Number"),
                    new ffData ($tipo_richiesta->descrizione, "Text"),						
                    );
        }        
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tipo_richiesta";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tipo_richiesta_multipairs;
    $oField->label = "Tipologia richiesta";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "richiesta");    
    //*************************************
    //informazioni aggiuntive
    $oField = ffField::factory($cm->oPage);
    $oField->id = "informazioni_aggiuntive_tipologia";
    $oField->base_type = "Text";
    $oField->label = "Informazioni aggiuntive alla tipologia di richiesta";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    //Quantità
    $oField = ffField::factory($cm->oPage);
    $oField->id = "qta";
    $oField->base_type = "Number";
    $oField->label = "Quantità richiesta";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    //ruolo e qualifica interna
    //recupero dei cdr e dei cdc della gerarchia di competenza
    $ruolo_multipairs = array();
    $qualifica_interna_multipairs = array();
    foreach (Ruolo::getAll() as $ruolo) {        
        $ruolo_multipairs[] =
                array(
                        new ffData ($ruolo->id, "Number"),
                        new ffData ($ruolo->descrizione, "Text"),						
                        );
        foreach (QualificaInterna::getAll(array("ID_ruolo"=>$ruolo->id)) as $qualifica_interna) {
            //preselezione di ruolo e profilo         
            if (isset($richiesta) && $richiesta->id_qualifica_interna == $qualifica_interna->id) {
                $ruolo_predefinito = $ruolo;
                $qualifica_interna_predefinita = $qualifica_interna;
            }
            $qualifica_interna_multipairs[] =
                array(
                    new ffData ($ruolo->id, "Number"),
                    new ffData ($qualifica_interna->id, "Number"),                                               
                    new ffData ($qualifica_interna->descrizione, "Text"),                                                 
                    );
        }
    }
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ruolo";
    $oField->label = "Ruolo";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->base_type = "Number";
        $oField->extended_type = "Selection";
        $oField->widget = "activecomboex";
        $oField->multi_pairs = $ruolo_multipairs;
        $oField->actex_child = "ID_qualifica_interna";
        $oField->actex_update_from_db = false;

    }
    else {
        $oField->base_type = "Text";
        $oField->display_value = new ffData($ruolo_predefinito->descrizione, "Text");
        $oField->control_type = "label";
        $oField->store_in_db = false;    
    }
    $oField->data_type = "";
    $oField->default_value = new ffData($ruolo_predefinito->id, "Number");
    $oField->store_in_db = false;
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_qualifica_interna";    
    $oField->label = "Qualifica interna";
    $oField->base_type = "Number";
    if ($privilegi_utente["edit_richiesta"] == true){         
        $oField->extended_type = "Selection";
        $oField->widget = "activecomboex";
        $oField->multi_pairs = $qualifica_interna_multipairs;
        $oField->actex_father = "ruolo";    
        $oField->actex_update_from_db = false;
        $oField->required = true;    
    }
    else {
        $oField->base_type = "Text";
        $oField->display_value = new ffData($qualifica_interna_predefinita->descrizione, "Text");
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oField->data_type = "";
    $oField->default_value = new ffData($qualifica_interna_predefinita->id, "Number");
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    //motivazioni
    $oField = ffField::factory($cm->oPage);
    $oField->id = "motivazioni";
    $oField->base_type = "Text";
    $oField->label = "Motivazioni";
    if ($privilegi_utente["edit_richiesta"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "richiesta");
    //*************************************
    //data approvazione / rifiuto
    if ($privilegi_utente["edit_richiesta"] == false) {        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_chiusura";
        $oField->label = "Data richiesta"; 
        $oField->base_type = "DateTime";
        $oField->default_value = new ffData($richiesta->data_chiusura, "DateTime");
        if ($privilegi_utente["admin"]){        
            $oField->widget = "datepicker";
        }
        else {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }    
        $oRecord->addContent($oField, "richiesta");                
    }
}
//******************************************************************************
//recupero multipairs supporto
if ($privilegi_utente["view_cdr_padre"] 
        || $privilegi_utente["view_programmazione_strategica"] 
        || $privilegi_utente["view_direzione_riferimento"] 
        || $privilegi_utente["view_dg"]
        || $privilegi_utente["view_uo_competente"]) {
    $parere_multipairs = array();
    foreach(RUParere::getAll() as $parere){
        if (CoreHelper::annoInIntervallo($anno->descrizione, $parere->anno_inizio, $parere->anno_termine)) {
            $parere_multipairs[] =
                array(
                    new ffData ($parere->id, "Number"),
                    new ffData ($parere->descrizione, "Text"),						
                    );
        }        
    }   
    $priorita_multipairs = array();
    foreach(RUPriorita::getAll() as $priorita){
        if (CoreHelper::annoInIntervallo($anno->descrizione, $priorita->anno_inizio, $priorita->anno_termine)) {
            $priorita_multipairs[] =
                array(
                    new ffData ($priorita->id, "Number"),
                    new ffData ($priorita->descrizione, "Text"),						
                    );
        }        
    }
    $tempi_multipairs = array();
    foreach(RUTempi::getAll() as $tempi){
        if (CoreHelper::annoInIntervallo($anno->descrizione, $tempi->anno_inizio, $tempi->anno_termine)) {
            $tempi_multipairs[] =
                array(
                    new ffData ($tempi->id, "Number"),
                    new ffData ($tempi->descrizione, "Text"),						
                    );
        }        
    }
}
//*************************************
//cdr_padre
if ($privilegi_utente["view_cdr_padre"]) {
    $oRecord->addContent(null, true, "cdr_padre");
    $oRecord->groups["cdr_padre"]["title"] = "Approvazione CdR padre";        
    //*************************************    
    //parere    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_parere_cdr_padre";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_multipairs;
    $oField->label = "Parere";
    if ($privilegi_utente["edit_cdr_padre"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "cdr_padre");
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_parere_cdr_padre";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note sul parere";
    if ($privilegi_utente["edit_cdr_padre"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "cdr_padre");
    //*************************************    
    //priorità
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_priorita_cdr_padre";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_multipairs;
    $oField->label = "Priorità";
    if ($privilegi_utente["edit_cdr_padre"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "cdr_padre");
    //*************************************    
    //tempi    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tempi_cdr_padre";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_cdr_padre"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "cdr_padre");      
    //*************************************
    //data approvazione / rifiuto
    if ($privilegi_utente["edit_cdr_padre"] == false) {
        if ($richiesta->data_rifiuto_cdr_padre == null) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_approvazione_cdr_padre";
            $oField->label = "Data approvazione"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_approvazione_cdr_padre, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "cdr_padre");
        }
        else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_rifiuto_cdr_padre";
            $oField->label = "Data rifiuto"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_rifiuto_cdr_padre, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "cdr_padre");
        }                                
    }
}
//******************************************************************************
//programmazione strategica
if ($privilegi_utente["view_programmazione_strategica"]) {
    $oRecord->addContent(null, true, "programmazione_strategica");
    $oRecord->groups["programmazione_strategica"]["title"] = "Approvazione CdR Programmazione strategica";       
    //*************************************    
    //parere    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_parere_cdr_strategico";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_multipairs;
    $oField->label = "Parere";
    if ($privilegi_utente["edit_programmazione_strategica"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "programmazione_strategica");
    //*************************************
    //note
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_parere_cdr_strategico";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note sul parere";
    if ($privilegi_utente["edit_programmazione_strategica"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "programmazione_strategica");
    //*************************************    
    //priorità
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_priorita_cdr_strategico";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_multipairs;
    $oField->label = "Priorità";
    if ($privilegi_utente["edit_programmazione_strategica"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "programmazione_strategica");
    //*************************************    
    //tempi    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tempi_cdr_strategico";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_programmazione_strategica"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "programmazione_strategica");    
    //*************************************
    //data approvazione / rifiuto
    if ($privilegi_utente["edit_programmazione_strategica"] == false) {
        if ($richiesta->data_rifiuto_cdr_strategico == null) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_approvazione_cdr_strategico";
            $oField->label = "Data approvazione"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_approvazione_cdr_strategico, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "programmazione_strategica");
        }
        else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_rifiuto_cdr_strategico";
            $oField->label = "Data rifiuto"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_rifiuto_cdr_strategico, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "programmazione_strategica");
        }                        
    }
}
//******************************************************************************
//direzione riferimento
if ($privilegi_utente["view_direzione_riferimento"]) {
    $oRecord->addContent(null, true, "direzione_riferimento");
    $oRecord->groups["direzione_riferimento"]["title"] = "Approvazione Direzione di riferimento";
    //*************************************    
    //parere    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_parere_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_multipairs;
    $oField->label = "Parere";
    if ($privilegi_utente["edit_direzione_riferimento"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "direzione_riferimento");
    //*************************************
    //note
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_parere_direzione_riferimento";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note sul parere";
    if ($privilegi_utente["edit_direzione_riferimento"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "direzione_riferimento");
    //*************************************    
    //priorità
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_priorita_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_multipairs;
    $oField->label = "Priorità";
    if ($privilegi_utente["edit_direzione_riferimento"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "direzione_riferimento");
    //*************************************    
    //tempi    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tempi_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_direzione_riferimento"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "direzione_riferimento"); 
    //*************************************
    //data approvazione / rifiuto
    if ($privilegi_utente["edit_direzione_riferimento"] == false) {
        if ($richiesta->data_rifiuto_direzione_riferimento == null) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_approvazione_direzione_riferimento";
            $oField->label = "Data approvazione"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_approvazione_direzione_riferimento, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "direzione_riferimento");
        }
        else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_rifiuto_direzione_riferimento";
            $oField->label = "Data rifiuto"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_rifiuto_direzione_riferimento, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "direzione_riferimento");
        }                        
    }
}
//******************************************************************************
//dg
if ($privilegi_utente["view_dg"]) {
    $oRecord->addContent(null, true, "dg");
    $oRecord->groups["dg"]["title"] = "Approvazione Direzione Generale";
    //*************************************    
    //parere    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_parere_dg";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_multipairs;
    $oField->label = "Parere";
    if ($privilegi_utente["edit_dg"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "dg");
    //*************************************
    //note
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_parere_dg";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note sul parere";
    if ($privilegi_utente["edit_dg"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "dg");
    //*************************************    
    //priorità
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_priorita_dg";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_multipairs;
    $oField->label = "Priorità";
    if ($privilegi_utente["edit_dg"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "dg");
    //*************************************    
    //tempi    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tempi_dg";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_dg"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "dg");
    //*************************************
    //data approvazione / rifiuto
    if ($privilegi_utente["edit_dg"] == false) {
        if ($richiesta->data_rifiuto_dg == null) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_approvazione_dg";
            $oField->label = "Data approvazione"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_approvazione_dg, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "dg");
        }
        else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_rifiuto_dg";
            $oField->label = "Data rifiuto"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_rifiuto_dg, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "dg");
        }                        
    }
}
//******************************************************************************
//uo competente
if ($privilegi_utente["view_uo_competente"]) {
    $oRecord->addContent(null, true, "uo_competente");
    $oRecord->groups["uo_competente"]["title"] = "Approvazione UO Competente";
    //*************************************    
    //costo presunto  
    $oField = ffField::factory($cm->oPage);
    $oField->id = "costo_presunto";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Costo presunto da UO competente";
    if ($privilegi_utente["edit_uo_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");       
    //*************************************
    //modalità acquisizione
    $oField = ffField::factory($cm->oPage);
    $oField->id = "modalita_acquisizione";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Modalità di acquisizione";
    if ($privilegi_utente["edit_uo_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");
    //*************************************
    //tempi acquisizione
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tempi_uo_competente";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_uo_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");
    //*************************************
    //anno soddisfacimento richiesta
    $anni_multipairs = array();  
    for($i=0; $i<3; $i++){
        $anno_multipair = (int)($anno->descrizione)+$i;
        $anni_multipairs[] =
            array(
                new ffData ($anno_multipair, "Number"),
                new ffData ($anno_multipair, "Text"),						
                );  
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "anno_soddisfacimento_richiesta";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $anni_multipairs;
    $oField->label = "Anno di soddisfacimento della richiesta";
    if ($privilegi_utente["edit_uo_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");
    //*************************************
    //modalità acquisizione
    $oField = ffField::factory($cm->oPage);
    $oField->id = "fonte_finanziamento_proposta";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Fonte di finanziamento proposta";
    if ($privilegi_utente["edit_uo_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");
    //*************************************
    //non coerente con il piano dei fabbisogni
    $oField = ffField::factory($cm->oPage);
    $oField->id = "incoerenza_piano_fabbisogni";
    $oField->base_type = "Number";
    $oField->label = "Non coerente con il piano dei fabbisogni";
    if ($privilegi_utente["edit_uo_competente"] == true){
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
    }
    else {
        if ($richiesta->incoerenza_piano_fabbisogni == true) {
            $field_value = "No";
        }
        else {
            $field_value = "Si";
        }
        $oField->base_type = "Text";
        $oField->default_value = new ffData($field_value, "Text");
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "uo_competente");
    //*************************************
    //data approvazione / rifiuto / rifiuto
    if ($privilegi_utente["edit_uo_competente"] == false) {
        if ($richiesta->data_rifiuto_uo_competente == null)  {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_approvazione_uo_competente";
            $oField->label = "Data approvazione"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_approvazione_uo_competente, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "uo_competente");      
        }   
        else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_rifiuto_uo_competente";
            $oField->label = "Data rifiuto"; 
            $oField->base_type = "DateTime";
            $oField->default_value = new ffData($richiesta->data_rifiuto_uo_competente, "DateTime");
            if ($privilegi_utente["admin"]){        
                $oField->widget = "datepicker";
            }
            else {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }    
            $oRecord->addContent($oField, "uo_competente");   
        }                          
    }
}
//******************************************************************************
//monitoraggio
if ($privilegi_utente["view_monitoraggio"]) {
    $oRecord->addContent(null, true, "monitoraggio");
    $oRecord->groups["monitoraggio"]["title"] = "Monitoraggio";
    
    //*************************************    
    //importo definitivo  
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_definitivo";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo definitivo";
    if ($privilegi_utente["edit_monitoraggio"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    //*************************************    
    //data acquisizione
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_acquisizione";      
    $oField->label = "Data acquisizione risorsa"; 
    if ($privilegi_utente["edit_monitoraggio"] == true) {        
        $oField->default_value = new ffData($obiettivo_cdr_personale->data_accettazione, "DateTime");
        $oField->base_type = "Date";
        $oField->extended_type = "Date";
        $oField->data_type = "";
        $oField->widget = "datepicker";
        $oField->store_in_db = false;               
    }        
    else {        
        $oField->default_value = new ffData($obiettivo_cdr_personale->data_accettazione, "DateTime");
        $oField->base_type = "DateTime";
        $oField->extended_type = "DateTime";        
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->data_type = "";                             
    }  
    $oRecord->addContent($oField, "monitoraggio");
    //*************************************
    //provvedimento
    $oField = ffField::factory($cm->oPage);
    $oField->id = "provvedimento";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Provvedimento";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    //*************************************
    //fonte finanziamento
    $oField = ffField::factory($cm->oPage);
    $oField->id = "fonte_finanziamento";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Fonte finanziamento";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    //*************************************
    //provvedimento
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_monitoraggio";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note monitoraggio";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    //*************************************
    //data conferma acquisizione
    if ($privilegi_utente["edit_monitoraggio"] == false) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_conferma_acquisizione";
        $oField->label = "Data conferma acquisizione"; 
        $oField->base_type = "DateTime";
        $oField->default_value = new ffData($richiesta->data_conferma_acquisizione, "DateTime");
        if ($privilegi_utente["admin"]){        
            $oField->widget = "datepicker";
        }
        else {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }    
        $oRecord->addContent($oField, "monitoraggio");                
    }
}

//evento per il salvataggio dei dati
$oRecord->addEvent("on_do_action", "myUpdate");

//******************************************************************************
//BUTTON AZIONI
//*********************BUTTON AZIONI*****************************************
$tipo_chiusura = 0;	
if ($privilegi_utente["edit_richiesta"] == true){				
    $confirm_title = "Chiusura richiesta";
    $label = "Chiusura richiesta";
    $tipo_chiusura = 1;
    $html_message = "
                        Chiudendo la richiesta non sar&agrave; pi&ugrave; possibile apportare modifiche alla stessa.							
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_cdr_padre"] == true){				
    $confirm_title = "Conferma Cdr Padre";
    $label = "Conferma parere";
    $tipo_chiusura = 2;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_programmazione_strategica"] == true){				
    $confirm_title = "Conferma Cdr programmazione strategica";
    $label = "Conferma parere";
    $tipo_chiusura = 3;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_direzione_riferimento"] == true){				
    $confirm_title = "Conferma Direzione riferimento";
    $label = "Conferma parere";
    $tipo_chiusura = 4;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_dg"] == true){				
    $confirm_title = "Conferma Direzione Generale";
    $label = "Conferma parere";
    $tipo_chiusura = 5;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_uo_competente"] == true){				
    $confirm_title = "Conferma UO competente";
    $label = "Conferma parere";
    $tipo_chiusura = 6;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_monitoraggio"] == true){				
    $confirm_title = "Conferma monitoraggio";
    $label = "Conferma Acquisizione Risorsa";
    $tipo_chiusura = 7;
    $html_message = "
                        Confermando l&acute;acquisizione la richiesta verrà chiusa definitivamente e non sarà più possibile apportare modifiche.
                        <br><br>
                        Confermare?
                    ";
}
//se è prevista la visualizzazione di un pulsante
if ($tipo_chiusura !== 0){	
	$oBt = ffButton::factory($cm->oPage);
	$oBt->id = "action_button_".$tipo_chiusura;
	$oBt->label = $label;
	$oBt->action_type = "submit";
	$oBt->jsaction = "$('#inactive_body').show();$('#conferma_chiusura').show();";
        $oBt->aspect = "link";
        $oBt->class = "fa-edit btn-success";
	$oRecord->addActionButton($oBt);
	
	$oRecord->addHiddenField("tipo_chiusura", new ffData($tipo_chiusura, "Number"));
	
	$cm->oPage->addContent("<div id='inactive_body'></div>
							<div id='conferma_chiusura' class='conferma_azione'>
								<h3>".$confirm_title."</h3>
								<p>".$html_message."</p>
								<a id='conferma_si' class='confirm_link'>Conferma</a>
								<a id='conferma_no' class='confirm_link'>Annulla</a>
							</div>
							<script>
								$('#conferma_si').click(function(){
									document.getElementById('frmAction').value = 'richiesta_chiusura';
									document.getElementById('frmMain').submit();
								});
								$('#conferma_no').click(function(){
									$('#inactive_body').hide();
									$('#conferma_chiusura').hide();
									$('#investimento_action_button_".$tipo_chiusura."').prop('disabled', false);
									$('#investimento_action_button_".$tipo_chiusura."').prop('style', false);
									$('#investimento_action_button_".$tipo_chiusura."').val('" . $label . "');	
								});
							</script>
							");
}
//FINE BUTTON AZIONI
//******************************************************************************
$cm->oPage->addContent($oRecord);

//******************************************************************************
//gestione azioni personalizzate
function myUpdate($oRecord, $frmAction){
    if ($frmAction == "chiusura" && $oRecord->hidden_fields["tipo_chiusura"] !== null) {                
        $tipo_chiusura = $oRecord->hidden_fields["tipo_chiusura"]->getValue();        
        //viene verificato che il record sia salvato in modifica o inserimento e recuperato eventualmente l'oggetto
        $id = $oRecord->key_fields["ID"]->getValue();
        if (strlen($id)){
            $richiesta = new RURichiesta($id);
        }
        else {
            $richiesta = new RURichiesta();

            $richiesta->id_anno_budget = $oRecord->insert_additional_fields["ID_anno_budget"]->getValue();
            $richiesta->matricola_creazione = $oRecord->insert_additional_fields["matricola_creazione"]->getValue();
            $richiesta->codice_cdr_creazione = $oRecord->insert_additional_fields["codice_cdr_creazione"]->getValue();        
            $richiesta->data_creazione = $oRecord->insert_additional_fields["data_creazione"]->getValue();
        }
        //******************************************
        //verifica privilegi gia eseguita nel record        
        $messaggio_errore = "";              
        switch ($tipo_chiusura){
            //chiusura richiesta 
            case 1:              
                //campi_richiesta
                $richiesta->codice_cdc = $oRecord->form_fields["codice_cdc"]->value->getValue();
                $richiesta->id_tipo_richiesta = $oRecord->form_fields["ID_tipo_richiesta"]->value->getValue();
                $richiesta->informazioni_aggiuntive_tipologia = $oRecord->form_fields["informazioni_aggiuntive_tipologia"]->value->getValue();                
                $richiesta->qta = $oRecord->form_fields["qta"]->value->getValue();    
                $richiesta->id_qualifica_interna = $oRecord->form_fields["ID_qualifica_interna"]->value->getValue();                
                $richiesta->motivazioni = $oRecord->form_fields["motivazioni"]->value->getValue();                               
                //data_chiusura
                $richiesta->data_chiusura = date("Y-m-d H:i:s");                  
                try{				
                    $richiesta->save();
                    mod_notifier_add_message_to_queue("Chiusura richiesta effettuata con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura della richiesta", MOD_NOTIFIER_ERROR);
                }                
            break;
            case 2:                  
                //campi_approvazione
                $richiesta->id_parere_cdr_padre = $oRecord->form_fields["ID_parere_cdr_padre"]->value->getValue();
                $richiesta->note_parere_cdr_padre = $oRecord->form_fields["note_parere_cdr_padre"]->value->getValue();
                $richiesta->id_priorita_cdr_padre = $oRecord->form_fields["ID_priorita_cdr_padre"]->value->getValue();
                $richiesta->id_tempi_cdr_padre = $oRecord->form_fields["ID_tempi_cdr_padre"]->value->getValue();                
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_cdr_padre = new RUParere($oRecord->form_fields["ID_parere_cdr_padre"]->value->getValue());               
                if ($parere_cdr_padre->esito == 1) {
                    if($richiesta->id_priorita_cdr_padre == 0 || $richiesta->id_tempi_cdr_padre == 0) {
                        return CoreHelper::setError($oRecord, "E' obbligatorio specificare priorità e tempi per il parere espresso.");
                    }
                    $richiesta->data_approvazione_cdr_padre = date("Y-m-d H:i:s");
                }
                else {
                    $richiesta->data_rifiuto_cdr_padre = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $richiesta->save();
                    if ($parere_cdr_padre->esito == 1) {
                        mod_notifier_add_message_to_queue("Richiesta approvata dal CdR padre", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Richiesta non approvata dal CdR padre", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma dell'approvazione", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 3:      
                //campi_approvazione
                $richiesta->id_parere_cdr_strategico = $oRecord->form_fields["ID_parere_cdr_strategico"]->value->getValue();
                $richiesta->note_parere_cdr_strategico = $oRecord->form_fields["note_parere_cdr_strategico"]->value->getValue();
                $richiesta->id_priorita_cdr_strategico = $oRecord->form_fields["ID_priorita_cdr_strategico"]->value->getValue();
                $richiesta->id_tempi_cdr_strategico = $oRecord->form_fields["ID_tempi_cdr_strategico"]->value->getValue();
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_cdr_strategico = new RUParere($oRecord->form_fields["ID_parere_cdr_strategico"]->value->getValue());
                if ($parere_cdr_strategico->esito == 1) {                    
                    if($richiesta->id_priorita_cdr_strategico == 0 || $richiesta->id_tempi_cdr_strategico == 0) {
                        return CoreHelper::setError($oRecord, "E' obbligatorio specificare priorità e tempi per il parere espresso.");
                    }
                    $richiesta->data_approvazione_cdr_strategico = date("Y-m-d H:i:s");
                }
                else {
                    $richiesta->data_rifiuto_cdr_strategico = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $richiesta->save();
                    if ($parere_cdr_strategico->esito == 1) {
                        mod_notifier_add_message_to_queue("Richiesta approvata dal CdR strategico", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Richiesta non approvata dal CdR strategico", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma dell'approvazione", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 4:      
                //campi_approvazione
                $richiesta->id_parere_direzione_riferimento = $oRecord->form_fields["ID_parere_direzione_riferimento"]->value->getValue();
                $richiesta->note_parere_direzione_riferimento = $oRecord->form_fields["note_parere_direzione_riferimento"]->value->getValue();
                $richiesta->id_priorita_direzione_riferimento = $oRecord->form_fields["ID_priorita_direzione_riferimento"]->value->getValue();
                $richiesta->id_tempi_direzione_riferimento = $oRecord->form_fields["ID_tempi_direzione_riferimento"]->value->getValue();
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_direzione_riferimento = new RUParere($oRecord->form_fields["ID_parere_direzione_riferimento"]->value->getValue());
                if ($parere_direzione_riferimento->esito == 1) {
                    if($richiesta->id_priorita_direzione_riferimento == 0 || $richiesta->id_tempi_direzione_riferimento == 0) {
                        return CoreHelper::setError($oRecord, "E' obbligatorio specificare priorità e tempi per il parere espresso.");
                    }
                    $richiesta->data_approvazione_direzione_riferimento = date("Y-m-d H:i:s");
                }
                else {
                    $richiesta->data_rifiuto_direzione_riferimento = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $richiesta->save();
                    if ($parere_direzione_riferimento->esito == 1) {
                        mod_notifier_add_message_to_queue("Richiesta approvata dalla Direzione di riferimento", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Richiesta non approvata dalla Direzione di riferimento", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma dell'approvazione", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 5:      
                //campi_approvazione
                $richiesta->id_parere_dg = $oRecord->form_fields["ID_parere_dg"]->value->getValue();
                $richiesta->note_parere_dg = $oRecord->form_fields["note_parere_dg"]->value->getValue();
                $richiesta->id_priorita_dg = $oRecord->form_fields["ID_priorita_dg"]->value->getValue();
                $richiesta->id_tempi_dg = $oRecord->form_fields["ID_tempi_dg"]->value->getValue();
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_dg = new RUParere($oRecord->form_fields["ID_parere_dg"]->value->getValue());
                if ($parere_dg->esito == 1) {
                    if($richiesta->id_priorita_dg == 0 || $richiesta->id_tempi_dg == 0) {
                        return CoreHelper::setError($oRecord, "E' obbligatorio specificare priorità e tempi per il parere espresso.");
                    }
                    $richiesta->data_approvazione_dg = date("Y-m-d H:i:s");
                }
                else {
                    $richiesta->data_rifiuto_dg = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $richiesta->save();
                    if ($parere_dg->esito == 1) {
                        mod_notifier_add_message_to_queue("Richiesta approvata dalla Direzione generale", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Richiesta non approvata dalla Direzione generale", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma dell'approvazione", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 6:     
                //campi uoc competente
                $richiesta->costo_presunto = $oRecord->form_fields["costo_presunto"]->value->getValue();
                $richiesta->modalita_acquisizione = $oRecord->form_fields["modalita_acquisizione"]->value->getValue();
                $richiesta->id_tempi_uo_competente = $oRecord->form_fields["ID_tempi_uo_competente"]->value->getValue();
                $richiesta->anno_soddisfacimento_richiesta = $oRecord->form_fields["anno_soddisfacimento_richiesta"]->value->getValue();
                $richiesta->fonte_finanziamento_proposta = $oRecord->form_fields["fonte_finanziamento_proposta"]->value->getValue();
                $richiesta->incoerenza_piano_fabbisogni = $oRecord->form_fields["incoerenza_piano_fabbisogni"]->value->getValue();                
                //in base all'esito la proposta viene scartata oppure approvata                
                if ($richiesta->incoerenza_piano_fabbisogni !== null) {
                    $richiesta->data_rifiuto_uo_competente = date("Y-m-d H:i:s");                     
                }
                else {
                    if($richiesta->costo_presunto == 0 || $richiesta->id_tempi_uo_competente == 0 || $richiesta->anno_soddisfacimento_richiesta == 0) {
                        return CoreHelper::setError($oRecord, "E' obbligatorio specificare il costo presunto, i tempi e l'anno di soddisfacimento della richiesta.");
                    }
                    $richiesta->data_approvazione_uo_competente = date("Y-m-d H:i:s");
                }                                   
                try{				
                    $richiesta->save();
                    if ($oRecord->form_fields["incoerenza_piano_fabbisogni"]->value->getValue() !== null) {
                        mod_notifier_add_message_to_queue("Richiesta non approvata dalla UO competente", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Richiesta approvata dalla UO competente", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura dell'istruttoria", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 7:    
                //campi monitoraggio                
                $richiesta->importo_definitivo = $oRecord->form_fields["importo_definitivo"]->value->getValue();
                $richiesta->data_acquisizione = $oRecord->form_fields["data_acquisizione"]->value->getValue();
                $richiesta->provvedimento = $oRecord->form_fields["provvedimento"]->value->getValue();
                $richiesta->fonte_finanziamento = $oRecord->form_fields["fonte_finanziamento"]->value->getValue();
                $richiesta->note_monitoraggio = $oRecord->form_fields["note_monitoraggio"]->value->getValue();
                
                $richiesta->data_conferma_acquisizione = date("Y-m-d H:i:s");                                       
                try{				
                    $richiesta->save();
                    mod_notifier_add_message_to_queue("Monitoraggio chiuso con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura del monitoraggio", MOD_NOTIFIER_ERROR);
                }					
            break;                   
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
        ffRedirect($ret_url);
    }
 }