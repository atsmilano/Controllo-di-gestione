<?php
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("investimenti_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

//recupero globals e info cdr
$cdr = $cm->oPage->globals["cdr"]["value"];

$anno = $cm->oPage->globals["anno"]["value"];
//estrazione del piano cdr
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");
//recupero del cdr e del cdc
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
            
//******************************************************************************
//gestione privilegi
$privilegi_utente = array(
                        "edit_admin" => false,
                        "edit_richiesta" => false,
                        "view_approvazione" => false,
                        "edit_approvazione" => false,
                        "view_istruttoria_avvio" => false,
                        "edit_istruttoria_avvio" => false,   
                        "view_istruttoria_uoc_competente" => false,
                        "edit_istruttoria_uoc_competente" => false,
                        /*"edit_istruttoria_avvio_verifica_copertura" => false, */
                        "view_istruttoria_verifica_copertura" => false,
                        "edit_istruttoria_verifica_copertura" => false,  
                        "edit_proposta_piano_investimenti" => false, 
                        "view_proposta_piano_investimenti_parere" => false, 
                        "edit_proposta_piano_investimenti_parere" => false,
                        "view_monitoraggio" => false, 
                        "edit_monitoraggio" => false,
                        );
$nuova_richiesta = true;
$id_stato_avanzamento = 0;
//vengono replicati i controlli in fase di azioni su record (evento myUpdate)
if (isset ($_REQUEST["keys[ID]"])) {
    $nuova_richiesta = false;
    $investimento = new InvestimentiInvestimento($_REQUEST["keys[ID]"]);
    $id_stato_avanzamento = $investimento->getIdStatoAvanzamento(); 
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column(InvestimentiInvestimento::$stati_investimento, 'ID'));
    $stato_avanzamento = InvestimentiInvestimento::$stati_investimento[$index_stato_avanzamento]["descrizione"];    
    //verifica amministratore
    if ($user->hasPrivilege("investimenti_admin")) {
        $privilegi_utente["edit_admin"] = true;
    }
    //l'utente può modificare la propria richiesa solo in caso sia nello stato di compilazione
    if ($user->hasPrivilege("investimenti_richieste_edit") && $id_stato_avanzamento == 1) {
        $privilegi_utente["edit_richiesta"] = true;
    }
    if ($id_stato_avanzamento > 1) {
        $cdc_richiesta = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
        $cdr_richiesta = new CdrInvestimenti ($cdc_richiesta->id_cdr);       
        $cdr_direzione_riferimento = $cdr_richiesta->getCdrDirezioneRiferimentoAnno($anno);
        $responsabile_direzione_riferimento = $cdr_direzione_riferimento->getResponsabile($dateTimeObject);
    }
    //l'utente può modificare l'approvazione solo nel caso in cui la richiesta sia in stato di approvazione
    //e l'utente sia responsabile della direzione di riferimento
    if ($id_stato_avanzamento == 2) {                
        if ($user->matricola_utente_selezionato == $responsabile_direzione_riferimento->matricola_responsabile) {
            $privilegi_utente["view_approvazione"] = true;
            $privilegi_utente["edit_approvazione"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_approvazione"] = true;
        }
    }
    //il dettaglio della richiesta presenterà i dettagli dell'approvazione una volta confermata
    if ($id_stato_avanzamento > 2) {
        $privilegi_utente["view_approvazione"] = true;
    }
    if ($id_stato_avanzamento == 3) {
        if ($user->hasPrivilege("investimenti_istruttoria_dip_amm_edit")){
            $privilegi_utente["view_istruttoria_avvio"] = true;
            $privilegi_utente["edit_istruttoria_avvio"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_istruttoria_avvio"] = true;
        }
    }
    if ($id_stato_avanzamento > 3 && $id_stato_avanzamento !== 11) {
        $privilegi_utente["view_istruttoria_avvio"] = true;
    }
    if ($id_stato_avanzamento == 4) {
        //viene verificato che il cdr sia eventualmente uoc competente per l'investimento        
        $categoria_uoc_competente_anno = new InvestimentiCategoriaUocCompetenteAnno($investimento->istruttoria_id_categoria_uoc_competente_anno);        
        if ($categoria_uoc_competente_anno->codice_cdr == $cdr->codice) {
            $privilegi_utente["view_istruttoria_uoc_competente"] = true;
            $privilegi_utente["edit_istruttoria_uoc_competente"] = true;            
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_istruttoria_uoc_competente"] = true;
        }
    }
    if ($id_stato_avanzamento > 4 && $id_stato_avanzamento !== 11) {
        $privilegi_utente["view_istruttoria_uoc_competente"] = true;
    }
    /*
    stato eliminato
    if ($id_stato_avanzamento == 5) {
        if ($user->hasPrivilege("investimenti_istruttoria_dip_amm_edit")){
            $privilegi_utente["edit_istruttoria_avvio_verifica_copertura"] = true;
        }
    }    
    */
    if ($id_stato_avanzamento == 6) {
        if ($user->hasPrivilege("investimenti_istruttoria_bilancio_edit")){
            $privilegi_utente["view_istruttoria_verifica_copertura"] = true;
            $privilegi_utente["edit_istruttoria_verifica_copertura"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_istruttoria_verifica_copertura"] = true;
        }
    }
    if ($id_stato_avanzamento > 6 && $id_stato_avanzamento !== 11 && $id_stato_avanzamento !== 13) {
        $privilegi_utente["view_istruttoria_verifica_copertura"] = true;
    }
    if ($id_stato_avanzamento == 7) {
        if ($user->hasPrivilege("investimenti_istruttoria_dip_amm_edit")){
            $privilegi_utente["edit_proposta_piano_investimenti"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["edit_proposta_piano_investimenti"] = true;
        }
    }
    if ($id_stato_avanzamento == 8) {
        if ($user->hasPrivilege("investimenti_piano_parere_edit")){
            $privilegi_utente["view_proposta_piano_investimenti_parere"] = true;
            $privilegi_utente["edit_proposta_piano_investimenti_parere"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_proposta_piano_investimenti_parere"] = true;
        }
    }
    if ($id_stato_avanzamento > 8 && $id_stato_avanzamento !== 11 && $id_stato_avanzamento !== 13/* && $id_stato_avanzamento !== 12*/) {
        $privilegi_utente["view_proposta_piano_investimenti_parere"] = true;
    }
    if ($id_stato_avanzamento == 9) {
        $categoria_uoc_competente_anno = new InvestimentiCategoriaUocCompetenteAnno($investimento->istruttoria_id_categoria_uoc_competente_anno);        
        if ($categoria_uoc_competente_anno->codice_cdr == $cdr->codice) {
            $privilegi_utente["view_monitoraggio"] = true;
            $privilegi_utente["edit_monitoraggio"] = true;
        }
        else if ($privilegi_utente["edit_admin"]){
            $privilegi_utente["view_monitoraggio"] = true;
        }
    }
    if ($id_stato_avanzamento > 9 && $id_stato_avanzamento !== 11 && $id_stato_avanzamento !== 13 && $id_stato_avanzamento !== 12) {
        $privilegi_utente["view_monitoraggio"] = true;
    }
}
else if ($user->hasPrivilege("investimenti_richieste_edit")){
    $privilegi_utente["edit_richiesta"] = true;
}
else if (!$user->hasPrivilege("investimenti_richieste_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

//******************************************************************************
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "investimento";
$oRecord->title = $nuova_richiesta ? "Nuova richiesta d'investimento" : "Richiesta d'investimento";
$oRecord->resources[] = "investimento";
$oRecord->src_table  = "investimenti_investimento";

//******************************************************************************
//gestione action in base a privilegi
if ($privilegi_utente["edit_richiesta"] !== true){
    $oRecord->allow_insert = false;    
    $oRecord->allow_delete = false;
    if (
            $privilegi_utente["edit_admin"]  !== true
            &&
            $privilegi_utente["edit_approvazione"]  !== true
            &&
            $privilegi_utente["edit_istruttoria_uoc_competente"]  !== true
            &&
            $privilegi_utente["edit_istruttoria_verifica_copertura"]  !== true
            &&
            $privilegi_utente["edit_istruttoria_avvio_verifica_copertura"]  !== true  
            &&
            $privilegi_utente["edit_proposta_piano_investimenti_parere"]  !== true
            &&
            $privilegi_utente["edit_monitoraggio"]  !== true
        ){
        $oRecord->allow_update = false;  
    }
}
//******************************************************************************

// KEY FIELD
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//evento per il salvataggio dei dati
$oRecord->addEvent("on_done_action", "myUpdate");

//******************************************************************************
//INIZIO RICHIESTA

//creazione fieldset richiesta
$oRecord->addContent(null, true, "richiesta");
$oRecord->groups["richiesta"]["title"] = "Richiesta";

//se ci si trova in edit richiesta saranno selezionabili solamente i cdr d'afferenza gerarchica del cdr chesta effettuando la richiesta
if ($privilegi_utente["edit_richiesta"] == true) {
    $view_all_cdr = false;
    $cdr_da_visualizzare = $cdr->getGerarchia();
}
//altrimenti i valori verranno recuperati da tutto il piano (per poter essere visualizzati da tutti i cdr coinvolti es. dipartimento amministrativo, uoc ompetenti ecc)
else {
    $view_all_cdr = true;
    $cdr_da_visualizzare = $piano_cdr->getCdr();
}
foreach ($cdr_da_visualizzare as $cdr_figlio) {
    if ($view_all_cdr == false) {
        $cdr_figlio = $cdr_figlio["cdr"];
    }
    $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);
	$cdr_multipairs[] =
		array(
			new ffData ($cdr_figlio->id, "Number"),
			new ffData ($cdr_figlio->codice." - ". $tipo_cdr->abbreviazione . " - " . $cdr_figlio->descrizione, "Text"),						
			);
    foreach ($cdr_figlio->getCdc() as $cdc) {
        //preselezione del cdr
        if (isset($investimento) && $investimento->richiesta_codice_cdc == $cdc->codice) {            
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

//*************************************
if ($id_stato_avanzamento > 1) {
    $cdr_creazione = AnagraficaCdr::factoryFromCodice($investimento->codice_cdr_creazione, $dateTimeObject);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr_creazione";
    $oField->base_type = "Text";    
    $oField->label = "Cdr creazione";
    $oField->default_value = new ffData($cdr_creazione->codice." - ".(strlen($cdr_creazione->abbreviazione)>0?$cdr_creazione->abbreviazione." - ":"").$cdr_creazione->descrizione, "Text");
    $oField->data_type = "";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oRecord->addContent($oField, "richiesta");
}

//*************************************
if ($id_stato_avanzamento > 0){
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

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_codice_cdr";
$oField->label = "Cdr richiesta";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->widget = "activecomboex";
    $oField->multi_pairs = $cdr_multipairs;
    $oField->actex_child = "richiesta_codice_cdc";
    $oField->actex_update_from_db = false;
    
}
else {
    $oField->base_type = "Text";
    $tipo_cdr_predefinito = new TipoCdr($cdr_predefinito->id_tipo_cdr);
    $oField->display_value = new ffData($cdr_predefinito->codice." - ". $tipo_cdr_predefinito->abbreviazione . " - " . $cdr_predefinito->descrizione, "Text");
    $oField->control_type = "label";  
}
$oField->data_type = "";
$oField->default_value = new ffData($cdr_predefinito->id, "Number");
$oField->store_in_db = false;
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_codice_cdc";
$oField->base_type = "Text";
$oField->label = "Cdc richiesta";
if ($privilegi_utente["edit_richiesta"] == true){    
    $oField->extended_type = "Selection";
    $oField->widget = "activecomboex";
    $oField->multi_pairs = $cdc_multipairs;
    $oField->actex_father = "richiesta_codice_cdr";    
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
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_nuova";
$oField->base_type = "Number";
$oField->label = "Nuova richiesta";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->extended_type = "Selection";
    $oField->control_type = "radio";
    //$oField->app_type = "Number";
    $oField->multi_pairs = array (
                                array(new ffData("1", "Number"), new ffData("Nuova richiesta", "Text")),
                                array(new ffData("0", "Number"), new ffData("Bene da sostituire", "Text")),
               );    
    $oField->required = true;
}
else {
    $oField->base_type = "Text";
    $oField->data_type = "";
    if ($investimento->richiesta_nuova == 1) {
        $nuova_richiesta_desc = "Nuova richiesta";
    }
    else {
        $nuova_richiesta_desc = "Bene da sostituire";
    }
    $oField->default_value = new ffData($nuova_richiesta_desc, "Text");   
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_matricola_bene_da_sostituire";
$oField->base_type = "Text";
$oField->label = "Estremi identificativi del bene da sostituire (n. matricola)";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
//categoria del bene richiesto
foreach(InvestimentiCategoria::getAll() as $categoria){
    $categoria_multipairs[] =
            array(
                new ffData ($categoria->id, "Number"),
                new ffData ($categoria->descrizione, "Text"),						
                );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_ID_categoria";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $categoria_multipairs;
$oField->label = "Categoria del bene / servizio richiesto";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_descrizione_bene";
$oField->base_type = "Text";
$oField->label = "Descrizione del bene / servizio richiesto";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_quantita";
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
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_motivo";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Motivo della richiesta";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_motivazioni_supporto";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Motivazioni a supporto";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_eventuali_costi_aggiuntivi";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Eventuali costi per modifiche / integrazioni strutturali o a impianti esistenti e costi dimanutenzione ed eventuali costi per la formazione";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_costo_stimato";
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->label = "Costo stimato";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");
//*************************************
//priorita intervento
foreach(InvestimentiPrioritaIntervento::getAll() as $priorita){
    $priorita_multipairs[] =
            array(
                new ffData ($priorita->id, "Number"),
                new ffData ($priorita->descrizione, "Text"),						
                );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_ID_priorita";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $priorita_multipairs;
$oField->label = "Priorità dell'intervento";
if ($privilegi_utente["edit_richiesta"] == true){
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_tempi";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Tempi di consegna / realizzazione richiesti";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "richiesta_ubicazione_bene";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Ubicazione del bene";
if ($privilegi_utente["edit_richiesta"] !== true){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "richiesta");

//*************************************
if ($id_stato_avanzamento > 1) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "richiesta_data_chiusura";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura della richiesta";
    //data_chiusura
    if ($privilegi_utente["edit_admin"] == true){    
        $oField->widget = "datepicker";  
    }
    else {
        $oField->control_type = "Label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "richiesta");
}

//*************************************
$oRecord->insert_additional_fields["data_creazione"] = new ffData(date("Y-m-d H:i:s"), "Datetime");
$oRecord->insert_additional_fields["codice_cdr_creazione"] = new ffData($cdr->codice, "Text");
$oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");

//FINE RICHIESTA
//******************************************************************************

//******************************************************************************
//INIZIO APPROVAZIONE
if ($privilegi_utente["view_approvazione"] == true){
    //creazione fieldset approvazione
    $oRecord->addContent(null, true, "approvazione");
    $oRecord->groups["approvazione"]["title"] = "Approvazione";
 
    //*************************************
    foreach(InvestimentiParereDirezioneRiferimento::getAll() as $parere_direzione){
        $parere_direzione_multipairs[] =
                array(
                    new ffData ($parere_direzione->id, "Number"),
                    new ffData ($parere_direzione->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "approvazione_ID_parere_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_direzione_multipairs;
    $oField->label = "Parere della direzione di riferimento";
    if ($privilegi_utente["edit_approvazione"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "approvazione");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "approvazione_note_parere_direzione_riferimento";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note sul parere della direzione di riferimento";
    if ($privilegi_utente["edit_approvazione"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "approvazione");
    
    //*************************************
    foreach(InvestimentiPrioritaDirezioneRiferimento::getAll() as $priorita_direzione){
        $priorita_direzione_multipairs[] =
                array(
                    new ffData ($priorita_direzione->id, "Number"),
                    new ffData ($priorita_direzione->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "approvazione_ID_priorita_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_direzione_multipairs;
    $oField->label = "Priorità dell'intervento secondo la direzione di riferimento";
    if (!$privilegi_utente["edit_approvazione"] == true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "approvazione");

    //*************************************
    foreach(InvestimentiTempiDirezioneRiferimento::getAll() as $tempi_direzione){
        $tempi_direzione_multipairs[] =
                array(
                    new ffData ($tempi_direzione->id, "Number"),
                    new ffData ($tempi_direzione->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "approvazione_ID_tempi_stimati_direzione_riferimento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_direzione_multipairs;
    $oField->label = "Tempi stimati dalla direzione di riferimento";
    if (!$privilegi_utente["edit_approvazione"] == true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "approvazione");    
    
    //data_approvazione o scarto
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "approvazione_data";
        $oField->base_type = "Date";
        $oField->label = "Data approvazione";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "approvazione");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "approvazione_data_scarto_direzione_riferimento";
        $oField->base_type = "Date";
        $oField->label = "Data scarto direzione riferimento";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "approvazione");    
    }         
}
//FINE APPROVAZIONE
//******************************************************************************

//******************************************************************************
//INIZIO AVVIO ISTRUTTORIA
//creazione fieldset istruttoria
$oRecord->addContent(null, true, "istruttoria");
$oRecord->groups["istruttoria"]["title"] = "Istruttoria";

if ($privilegi_utente["view_istruttoria_avvio"] == true) {   
    //*************************************         
    foreach (InvestimentiCategoriaUocCompetenteAnno::getCategoriaUocCompetentiAnno($anno) as $categoria_uoc_competente) {
        $cdr_uoc_competente = AnagraficaCdr::factoryFromCodice($categoria_uoc_competente->codice_cdr, $dateTimeObject);
        $categorie_uoc_competenti_multipairs[] =
                array(
                    new ffData ($categoria_uoc_competente->id, "Number"),
                    new ffData ($cdr_uoc_competente->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_ID_categoria_uoc_competente_anno";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $categorie_uoc_competenti_multipairs;
    $oField->app_type = "Number";
    //preselezione della UOC competente in base alla categoria della richiesta in caso non sia ancora stata definita
    if ($investimento->istruttoria_id_categoria_uoc_competente_anno == null) {
        //la getAll() con le due condizioni restituira sempre e solo un valore
        $uoc_competente_suggerita = InvestimentiCategoriaUocCompetenteAnno::getCategoriaUocCompetentiAnno($anno, $investimento->richiesta_id_categoria);                
        $oField->default_value = new ffData($uoc_competente_suggerita[0]->id, "Number");        
        $oField->data_type = "";
    }
    $oField->label = "UOC competente";
    if ($privilegi_utente["edit_istruttoria_avvio"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //data avvio istruttoria
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "istruttoria_data_avvio";
        $oField->base_type = "Date";
        $oField->label = "Data avvio istruttoria";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "istruttoria");   
    }
}
//FINE AVVIO ISTRUTTORIA
//******************************************************************************

//******************************************************************************
//INIZIO ISTRUTTORIA UOC COMPETENTE
if ($privilegi_utente["view_istruttoria_uoc_competente"] == true) {
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_costo_presunto";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Costo presunto da UOC competente";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_modalita_acquisizione";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Modalità di acquisizione";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    foreach(InvestimentiTempiUocCompetente::getAll() as $tempi_uoc_competente){
        $tempi_uoc_competente_multipairs[] =
                array(
                    new ffData ($tempi_uoc_competente->id, "Number"),
                    new ffData ($tempi_uoc_competente->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_ID_tempi_stimati_uoc_competente";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_uoc_competente_multipairs;
    $oField->label = "Tempi stimati";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_anno_soddisfacimento";
    $oField->base_type = "Number";
    $oField->label = "Anno di soddisfacimento della richiesta";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    $fonti_finanziamento_multipairs = array();
    foreach(InvestimentiFonteFinanziamento::getAll(array("ID_anno_budget" => $anno->id)) as $fonte_finanziamento){
        $fonti_finanziamento_multipairs[] =
                array(
                    new ffData ($fonte_finanziamento->id, "Number"),
                    new ffData ($fonte_finanziamento->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_ID_fonte_finanziamento_proposta";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $fonti_finanziamento_multipairs;
    $oField->label = "Fonte di finanziamento proposta";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    $categorie_registro_cespiti_multipairs = array();
    foreach(InvestimentiCategoriaRegistroCespiti::getAll(array("ID_anno_budget" => $anno->id)) as $categoria_registro_cespiti){
        $categorie_registro_cespiti_multipairs[] =
                array(
                    new ffData ($categoria_registro_cespiti->id, "Number"),
                    new ffData ($categoria_registro_cespiti->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_ID_categoria_registro_cespiti_proposta";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $categorie_registro_cespiti_multipairs;
    $oField->label = "Conto e categoria registro cespiti proposta";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "istruttoria");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "istruttoria_non_coerente_piano_investimenti";
    $oField->base_type = "Number";
    $oField->label = "Non coerente con il piano degli investimenti";
    if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){
        //$oField->extended_type = "Selection";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
    }
    else {
        if ($investimento->istruttoria_non_coerente_piano_investimenti == true) {
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
    $oRecord->addContent($oField, "istruttoria");
    
    //data chiusura o scarto uoc competente 
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "istruttoria_data_chiusura_uoc_competente";
        $oField->base_type = "Date";
        $oField->label = "Data chiusura UOC competente";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "istruttoria");              
    
        $oField = ffField::factory($cm->oPage);
        $oField->id = "istruttoria_data_scarto_uoc_competente";
        $oField->base_type = "Date";
        $oField->label = "Data scarto UOC competente";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "istruttoria");        
    }
}

//FINE ISTRUTTORIA UOC COMPETENTE
//******************************************************************************

//******************************************************************************
//INIZIO ISTRUTTORIA VERIFICA COPERTURA
//creazione fieldset verifica_copertura
$oRecord->addContent(null, true, "verifica_copertura");
$oRecord->groups["verifica_copertura"]["title"] = "Verifica copertura";

if ($privilegi_utente["view_istruttoria_verifica_copertura"] == true) {
    //*************************************    
    $categorie_registro_cespiti_multipairs = array();
    foreach(InvestimentiCategoriaRegistroCespiti::getAll(array("ID_anno_budget" => $anno->id)) as $categoria_registro_cespiti){
        $categorie_registro_cespiti_multipairs[] =
                array(
                    new ffData ($categoria_registro_cespiti->id, "Number"),
                    new ffData ($categoria_registro_cespiti->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "verifica_copertura_ID_registro_cespiti";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $categorie_registro_cespiti_multipairs;
    $oField->label = "Conto e categoria registro cespiti";
    if ($privilegi_utente["edit_istruttoria_verifica_copertura"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "verifica_copertura");             
    
    //*************************************    
    $fonti_finanziamento_multipairs = array();
    foreach(InvestimentiFonteFinanziamento::getAll(array("ID_anno_budget" => $anno->id)) as $fonte_finanziamento){
        $fonti_finanziamento_multipairs[] =
                array(
                    new ffData ($fonte_finanziamento->id, "Number"),
                    new ffData ($fonte_finanziamento->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "verifica_copertura_ID_fonte_finanziamento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $fonti_finanziamento_multipairs;
    $oField->label = "Fonte di finanziamento";
    if ($privilegi_utente["edit_istruttoria_verifica_copertura"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "verifica_copertura");
    
    //data fine verifica copertura
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "verifica_copertura_data_fine";
        $oField->base_type = "Date";
        $oField->label = "Data fine verifica copertura";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "verifica_copertura");  
    }
    
    //data proposta piano investimenti
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "proposta_piano_investimenti_data";
        $oField->base_type = "Date";
        $oField->label = "Data proposta Piano Investimento";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "verifica_copertura");
    }
}
            
//FINE ISTRUTTORIA VERIFICA COPERTURA
//******************************************************************************

//******************************************************************************
//INIZIO ISTRUTTORIA PARERE DG
//creazione fieldset verifica_copertura
$oRecord->addContent(null, true, "parere_dg");
$oRecord->groups["parere_dg"]["title"] = "Parere Direzione Generale";

if ($privilegi_utente["view_proposta_piano_investimenti_parere"] == true) {        
    //*************************************
    foreach(InvestimentiParereDg::getAll() as $parere_dg){
        $parere_dg_multipairs[] =
                array(
                    new ffData ($parere_dg->id, "Number"),
                    new ffData ($parere_dg->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dg_ID_parere";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $parere_dg_multipairs;
    $oField->label = "Parere";
    if ($privilegi_utente["edit_proposta_piano_investimenti_parere"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "parere_dg");
    
    //*************************************
    foreach(InvestimentiPrioritaDg::getAll() as $priorita_dg){
        $priorita_dg_multipairs[] =
                array(
                    new ffData ($priorita_dg->id, "Number"),
                    new ffData ($priorita_dg->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dg_ID_priorita";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $priorita_dg_multipairs;
    $oField->label = "Priorità";
    if ($privilegi_utente["edit_proposta_piano_investimenti_parere"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "parere_dg");
    
    //*************************************
    foreach(InvestimentiTempiDg::getAll() as $tempi_dg){
        $tempi_dg_multipairs[] =
                array(
                    new ffData ($tempi_dg->id, "Number"),
                    new ffData ($tempi_dg->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dg_ID_tempi";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $tempi_dg_multipairs;
    $oField->label = "Tempi";
    if ($privilegi_utente["edit_proposta_piano_investimenti_parere"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "parere_dg"); 
    
    //data approvazione o scarto piano investimenti
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "dg_data_validazione_piano_investimenti";
        $oField->base_type = "Date";
        $oField->label = "Data validazione Piano Investimento";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "parere_dg");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "dg_data_scarto_piano_investimenti";
        $oField->base_type = "Date";
        $oField->label = "Data scarto Piano Investimento";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "parere_dg");
    }
}
//FINE ISTRUTTORIA PARERE DG
//******************************************************************************

//******************************************************************************
//INIZIO MONITORAGIGO
//creazione fieldset verifica_copertura
$oRecord->addContent(null, true, "monitoraggio");
$oRecord->groups["monitoraggio"]["title"] = "Monitoraggio";
    
if ($privilegi_utente["view_monitoraggio"] == true) { 
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_importo_definitivo";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo definitivo";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");

    // Data inizio progetto
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_data";
    $oField->base_type = "Date";
    $oField->label = "Data monitoraggio";
    if ($privilegi_utente["edit_monitoraggio"] == true){
        $oField->widget = "datepicker";
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_provvedimento";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Provvedimento";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
 
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_fatture";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Fatture";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_fornitore";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Fornitore";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    
    //*************************************
    $fonti_finanziamento_multipairs = array();
    foreach(InvestimentiFonteFinanziamento::getAll(array("ID_anno_budget" => $anno->id)) as $fonte_finanziamento){
        $fonti_finanziamento_multipairs[] =
                array(
                    new ffData ($fonte_finanziamento->id, "Number"),
                    new ffData ($fonte_finanziamento->descrizione, "Text"),						
                    );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_ID_fonte_finanziamento";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $fonti_finanziamento_multipairs;
    $oField->label = "Fonte di finanziamento";
    if ($privilegi_utente["edit_monitoraggio"] == true){
        $oField->required = true;
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    
    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "monitoraggio_note";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note";
    if ($privilegi_utente["edit_monitoraggio"] !== true){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "monitoraggio");
    
    //data approvazione o scarto piano investimenti
    if ($privilegi_utente["edit_admin"] == true){
        $oField = ffField::factory($cm->oPage);
        $oField->id = "monitoraggio_data_chiusura";
        $oField->base_type = "Date";
        $oField->label = "Data chiusura monitoraggio";
        $oField->widget = "datepicker";  
        $oRecord->addContent($oField, "monitoraggio");
    }
    
    //viene variata la descrizione nel pulsante di aggiornamento nel caso ci si trovi in monitoraggio    
	$oRecord->buttons_options["update"]["label"]="Salva monitoraggio intermedio";
}
//FINE MONITORAGGIO
//******************************************************************************

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
if ($privilegi_utente["edit_approvazione"] == true){				
    $confirm_title = "Conferma parere direzione";
    $label = "Conferma parere";
    $tipo_chiusura = 2;
    $html_message = "
                        Confermando l'approvazione non sar&aacute; pi&ugrave; possibile apportare modifiche alla stessa.
                        La richiesta risulterà scartata e sottoposta ad eventuale revisione in caso di parere non positivo.
                        <br><br>
                        Confermare la chiusura della richiesta?
                    ";
}
if ($privilegi_utente["edit_istruttoria_avvio"] == true){				
    $confirm_title = "Avvio istruttoria";
    $label = "Avvio istruttoria";
    $tipo_chiusura = 3;
    $html_message = "
                        Confermando l'avvio l'istruttoria verrà assegnata alla UOC competente.
                        <br><br>
                        Confermare l'avvio dell'istruttoria?
                    ";
}
if ($privilegi_utente["edit_istruttoria_uoc_competente"] == true){				
    $confirm_title = "Chiusura istruttoria UOC competente";
    $label = "Chiusura istruttoria";
    $tipo_chiusura = 4;
    $html_message = "
                        Confermando la chiusura non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        In caso di richiesta non coerente con il piano degli investimenti questa verrà scartata.
                        <br><br>
                        Confermare la chiusura dell'istruttoria?
                    ";
}
/*
//stato eliminato 
if ($privilegi_utente["edit_istruttoria_avvio_verifica_copertura"] == true){				
    $confirm_title = "Avvio verifica copertura";
    $label = "Avvio verifica copertura";
    $tipo_chiusura = 5;
    $html_message = "
                        Confermare l'avvio della verifica della copertura?
                    ";
}
*/
if ($privilegi_utente["edit_istruttoria_verifica_copertura"] == true){				
    $confirm_title = "Chiusura verifica copertura";
    $label = "Chiusura verifica copertura";
    $tipo_chiusura = 6;
    $html_message = "
                        Confermando la chiusura verrà confermata la verifica della copertura e non sarà più possibile apportare modifiche.
                        <br><br>
                        Confermare la chiusura della verifica copertura?
                    ";
}
if ($privilegi_utente["edit_proposta_piano_investimenti"] == true){				
    $confirm_title = "Proposta Piano Investimenti";
    $label = "Proposta Piano Investimenti";
    $tipo_chiusura = 7;
    $html_message = "
                        Confermando, l'investimento verrà proposto per il piano investimenti.
                        <br><br>
                        Confermare la proposta?
                    ";
}
if ($privilegi_utente["edit_proposta_piano_investimenti_parere"] == true){				
    $confirm_title = "Parere Direzione Generale Piano Investimenti";
    $label = "Conferma parere Direzione Generale Piano Investimenti";
    $tipo_chiusura = 8;
    $html_message = "
                        Confermando non sar&aacute; pi&ugrave; possibile apportare modifiche.
                        In caso di parere positivo l'investimento verrà inserito nel piano, altrimenti verrà scartato.                        
                        <br><br>
                        Confermare il parere?
                    ";
}
if ($privilegi_utente["edit_monitoraggio"] == true){				
    $confirm_title = "Conferma monitoraggio";
    $label = "Conferma Realizzazione Investimento";
    $tipo_chiusura = 9;
    $html_message = "
                        Confermando il monitoraggio la richiesta verrà chiusa definitivamente e non sarà più possibile apportare modifiche.
                        <br><br>
                        Confermare il monitoraggio?
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
									document.getElementById('frmAction').value = 'investimento_chiusura';
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
        if ($id && $investimento == null){
            $investimento = new InvestimentiInvestimento($id);
        }
        else {
            $investimento = new InvestimentiInvestimento();

            $investimento->data_creazione = $oRecord->insert_additional_fields["data_creazione"]->getValue();
            $investimento->codice_cdr_creazione = $oRecord->insert_additional_fields["codice_cdr_creazione"]->getValue();
            $investimento->id_anno_budget = $oRecord->insert_additional_fields["ID_anno_budget"]->getValue();        
        }
     
        //******************************************
        //verifica privilegi gia eseguita nel record        
        $messaggio_errore = "";      
        //viene verificato per ogni azione di chiusura che l'utente abbia i privilegi per farlo
        switch ($tipo_chiusura){
            //chiusura richiesta 
            case 1:                              
                //campi_richiesta
                $investimento->richiesta_codice_cdc = $oRecord->form_fields["richiesta_codice_cdc"]->value->getValue();
                $investimento->richiesta_nuova = $oRecord->form_fields["richiesta_nuova"]->value->getValue();
                $investimento->richiesta_matricola_bene_da_sostituire = $oRecord->form_fields["richiesta_matricola_bene_da_sostituire"]->value->getValue();
                $investimento->richiesta_id_categoria = $oRecord->form_fields["richiesta_ID_categoria"]->value->getValue();
                $investimento->richiesta_descrizione_bene = $oRecord->form_fields["richiesta_descrizione_bene"]->value->getValue();
                $investimento->richiesta_quantita = $oRecord->form_fields["richiesta_quantita"]->value->getValue();
                $investimento->richiesta_motivo = $oRecord->form_fields["richiesta_motivo"]->value->getValue();
                $investimento->richiesta_motivazioni_supporto = $oRecord->form_fields["richiesta_motivazioni_supporto"]->value->getValue();
                $investimento->richiesta_eventuali_costi_aggiuntivi = $oRecord->form_fields["richiesta_eventuali_costi_aggiuntivi"]->value->getValue();
                $investimento->richiesta_costo_stimato = $oRecord->form_fields["richiesta_costo_stimato"]->value->getValue();
                $investimento->richiesta_id_priorita = $oRecord->form_fields["richiesta_ID_priorita"]->value->getValue();
                $investimento->richiesta_tempi = $oRecord->form_fields["richiesta_tempi"]->value->getValue();
                $investimento->richiesta_ubicazione_bene = $oRecord->form_fields["richiesta_ubicazione_bene"]->value->getValue();
                //data_chiusura
                $investimento->richiesta_data_chiusura = date("Y-m-d H:i:s");                  
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Chiusura richiesta effettuata con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura della richiesta", MOD_NOTIFIER_ERROR);
                }                
            break;
            case 2:      
                //campi_approvazione
                $investimento->approvazione_id_parere_direzione_riferimento = $oRecord->form_fields["approvazione_ID_parere_direzione_riferimento"]->value->getValue();
                $investimento->approvazione_note_parere_direzione_riferimento = $oRecord->form_fields["approvazione_note_parere_direzione_riferimento"]->value->getValue();
                $investimento->approvazione_id_priorita_direzione_riferimento = $oRecord->form_fields["approvazione_ID_priorita_direzione_riferimento"]->value->getValue();
                $investimento->approvazione_id_tempi_stimati_direzione_riferimento = $oRecord->form_fields["approvazione_ID_tempi_stimati_direzione_riferimento"]->value->getValue();
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_direzione = new InvestimentiParereDirezioneRiferimento($oRecord->form_fields["approvazione_ID_parere_direzione_riferimento"]->value->getValue());
                if ($parere_direzione->esito == 1) {
                    $investimento->approvazione_data = date("Y-m-d H:i:s");
                }
                else {
                    $investimento->approvazione_data_scarto_direzione_riferimento = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $investimento->save();
                    if ($parere_direzione->esito == 1) {
                        mod_notifier_add_message_to_queue("Conferma approvazione effettuata con successo", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Proposta d'investimento scartata", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma dell'approvazione", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 3:   
                //campi avvio istruttoria                
                $investimento->istruttoria_id_categoria_uoc_competente_anno = $oRecord->form_fields["istruttoria_ID_categoria_uoc_competente_anno"]->value->getValue();               
                //id_uoc_competente anno già inizializzata
                $investimento->istruttoria_data_avvio = date("Y-m-d H:i:s");                                       
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Istruttoria avviata con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante l'avvio dell'istruttoria", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 4:     
                //campi uoc competente
                $investimento->istruttoria_costo_presunto = $oRecord->form_fields["istruttoria_costo_presunto"]->value->getValue();
                $investimento->istruttoria_modalita_acquisizione = $oRecord->form_fields["istruttoria_modalita_acquisizione"]->value->getValue();
                $investimento->istruttoria_id_tempi_stimati_uoc_competente = $oRecord->form_fields["istruttoria_ID_tempi_stimati_uoc_competente"]->value->getValue();
                $investimento->istruttoria_anno_soddisfacimento = $oRecord->form_fields["istruttoria_anno_soddisfacimento"]->value->getValue();
                $investimento->istruttoria_id_fonte_finanziamento_proposta = $oRecord->form_fields["istruttoria_ID_fonte_finanziamento_proposta"]->value->getValue();
                $investimento->istruttoria_id_categoria_registro_cespiti_proposta = $oRecord->form_fields["istruttoria_ID_categoria_registro_cespiti_proposta"]->value->getValue();
                $investimento->istruttoria_non_coerente_piano_investimenti = $oRecord->form_fields["istruttoria_non_coerente_piano_investimenti"]->value->getValue();
                
                //in base all'esito la proposta viene scartata oppure approvata                
                if ($investimento->istruttoria_non_coerente_piano_investimenti !== null) {
                    $investimento->istruttoria_data_scarto_uoc_competente = date("Y-m-d H:i:s");                    
                }
                else {
                    $investimento->istruttoria_data_chiusura_uoc_competente = date("Y-m-d H:i:s");
                }                                   
                try{				
                    $investimento->save();
                    if ($oRecord->form_fields["istruttoria_non_coerente_piano_investimenti"]->value->getValue() !== null) {
                        mod_notifier_add_message_to_queue("Istruttoria chiusa con successo", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Istruttoria scartata in quanto non coerente con il piano investimenti", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura dell'istruttoria", MOD_NOTIFIER_ERROR);
                }					
            break;
            /*
            //stato eliminato
            case 5:                     
                $investimento->verifica_copertura_data_avvio = date("Y-m-d H:i:s");                                       
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Verifica copertura avviata con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante l'avvio della verifica copertura", MOD_NOTIFIER_ERROR);
                }					
            break;            
            */
            case 6:    
                //campi verifica copertura
                $investimento->verifica_copertura_id_registro_cespiti = $oRecord->form_fields["verifica_copertura_ID_registro_cespiti"]->value->getValue();                                
                $investimento->verifica_copertura_id_fonte_finanziamento = $oRecord->form_fields["verifica_copertura_ID_fonte_finanziamento"]->value->getValue();
                
                $investimento->verifica_copertura_data_fine = date("Y-m-d H:i:s");                                       
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Verifica copertura chiusa con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura della verifica copertura", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 7:                  
                //data proposta piano                
                $investimento->proposta_piano_investimenti_data = date("Y-m-d H:i:s");                                       
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Proposta piano investimenti effettuata con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la proposta piano investimenti", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 8:      
                $investimento->dg_id_parere = $oRecord->form_fields["dg_ID_parere"]->value->getValue();
                $investimento->dg_id_priorita = $oRecord->form_fields["dg_ID_priorita"]->value->getValue();
                $investimento->dg_id_tempi = $oRecord->form_fields["dg_ID_tempi"]->value->getValue();
                
                //in base all'esito la proposta viene scartata oppure approvata
                $parere_dg = new InvestimentiParereDg($oRecord->form_fields["dg_ID_parere"]->value->getValue());
                if ($parere_dg->esito == 1) {
                    $investimento->dg_data_validazione_piano_investimenti = date("Y-m-d H:i:s");
                }
                else {
                    $investimento->dg_data_scarto_piano_investimenti = date("Y-m-d H:i:s");
                }                                  
                try{				
                    $investimento->save();
                    if ($parere_dg->esito == 1) {
                        mod_notifier_add_message_to_queue("Inserimento nel piano effettuato con successo", MOD_NOTIFIER_SUCCESS);
                    }
                    else {
                        mod_notifier_add_message_to_queue("Proposta d'investimento scartata", MOD_NOTIFIER_SUCCESS);
                    }
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la conferma del parere", MOD_NOTIFIER_ERROR);
                }					
            break;
            case 9:    
                //campi verifica copertura
                $investimento->monitoraggio_importo_definitivo = $oRecord->form_fields["monitoraggio_importo_definitivo"]->value->getValue();   
                $investimento->monitoraggio_data = $oRecord->form_fields["monitoraggio_data"]->value->getValue();
                $investimento->monitoraggio_provvedimento = $oRecord->form_fields["monitoraggio_provvedimento"]->value->getValue();
                $investimento->monitoraggio_fatture = $oRecord->form_fields["monitoraggio_fatture"]->value->getValue();
                $investimento->monitoraggio_fornitore = $oRecord->form_fields["monitoraggio_fornitore"]->value->getValue();
                $investimento->monitoraggio_id_fonte_finanziamento = $oRecord->form_fields["monitoraggio_ID_fonte_finanziamento"]->value->getValue();
                $investimento->monitoraggio_note = $oRecord->form_fields["monitoraggio_note"]->value->getValue();
                
                $investimento->monitoraggio_data_chiusura = date("Y-m-d H:i:s");                                       
                try{				
                    $investimento->save();
                    mod_notifier_add_message_to_queue("Verifica copertura chiusa con successo", MOD_NOTIFIER_SUCCESS);
                } catch (Exception $ex) {
                    mod_notifier_add_message_to_queue("Errore durante la chiusura della verifica copertura", MOD_NOTIFIER_ERROR);
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