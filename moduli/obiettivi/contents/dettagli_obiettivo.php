<?php
$user = LoggedUser::Instance();
//recupero parametri
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

$obiettivo_cdr = null;
$cdr_padre_obiettivo = null;
$edit = false;
$add_aziendale = false;
//viene recuperato l'obiettivo_cdr (in modifica oppure l'obiettivo_cdr_origine in aggiunta da parte dei cdr) e le entità collegate per determinare i privilegi
//riepilogo dell'obiettivo e delle azioni definite dai padri gerarchici associati all'obiettivo
if (isset($_REQUEST["keys[ID_obiettivo_cdr]"]) && strlen($_REQUEST["keys[ID_obiettivo_cdr]"])) {
    $obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr]"]);
    if ($obiettivo_cdr->id_tipo_piano_cdr != null) {
        $tipo_piano = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
    } else {
        $tipo_piano = TipoPianoCdr::getPrioritaMassima();
    }
    $edit = true;
}
//caso di aggiunta da parte di un responsabile cdr
else if (isset($_REQUEST["ID_obiettivo_cdr_origine"]) && strlen($_REQUEST["ID_obiettivo_cdr_origine"])) {
    $obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["ID_obiettivo_cdr_origine"]);
    if (isset($_REQUEST["id_tipo_piano_cdr"])) {
        $tipo_piano = new TipoPianoCdr($_REQUEST["id_tipo_piano_cdr"]);
    } else {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: id_tipo_piano_cdr necessario per l'assegnazione da parte del responsabile.");
    }
}
//altrimenti viene selezionato il piano con priorità più alta (per assegnazione obiettivo aziendale)
else {
    $tipo_piano = TipoPianoCdr::getPrioritaMassima();
}
if ($obiettivo_cdr !== null) {
    if (isset($_REQUEST["keys[ID_obiettivo]"]) && strlen($_REQUEST["keys[ID_obiettivo]"])) {
        if ($_REQUEST["keys[ID_obiettivo]"] != $obiettivo_cdr->id_obiettivo) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr e ID_obiettivo non coerenti.");
        }
    }
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    //verifica sull'eliminazione dell'obiettivo
    if ($obiettivo->data_eliminazione !== null || $obiettivo_cdr->data_eliminazione !== null) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: elemento eliminato.");
    }
    //se l'obiettivo risulta valido viene recuperato il ruolo dell'utente rispetto all'obiettivo_cdr
    else {
        //viene recuperato il cdr
        $resp_cdr_selezionato = false;
        $resp_padre_cdr_selezionato = false;
        $resp_padre_ramo_cdr_selezionato = false;
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
        $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
        //recupero dei privilegi dell'utente sul cdr
        $personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
        foreach ($cdr->getPrivileges($personale, $date) as $privilege) {
            //privilegi per il responsabile del cdr referente / coreferente
            if ($privilege == "resp_cdr_selezionato") {
                $resp_cdr_selezionato = true;
            }
            //privilegi per il responsabile del cdr padre gerarchico padre_referente            
            if ($privilege == "resp_padre_cdr_selezionato") {
                $resp_padre_cdr_selezionato = true;
            }
            //privilegi per il responsabile di uno dei cdr padri su ramo gerarchico
            if ($privilege == "resp_padre_ramo_cdr_selezionato") {
                $resp_padre_ramo_cdr_selezionato = true;
            }
        }
        //personale_assegnato
        //viene verificato che il dipendente sia collegato all'obiettivo        
        $obiettivo_cdr_personale = null;
        $obiettivi_cdr_personale = $obiettivo_cdr->getObiettivoCdrPersonaleAssociati($personale->matricola);
        if (count($obiettivi_cdr_personale)) {
            $obiettivo_cdr_personale = $obiettivi_cdr_personale[0];
        }
        //in caso di coreferenza viene estratto il padre dell'obiettivo
        if ($obiettivo_cdr->isCoreferenza()) {
            $obiettivo_cdr_padre = $obiettivo_cdr->getObiettivoCdrPadre();
            $cdr_padre_obiettivo = Cdr::factoryFromCodice($obiettivo_cdr_padre->codice_cdr, $piano_cdr);
        }
    }
}
//se ci si trova in aggiunta da parte dell'amministratore viene verificato l'id obiettivo e garantiti i privilegi solamente all'amministratore
else if (isset($_REQUEST["keys[ID_obiettivo]"]) && strlen($_REQUEST["keys[ID_obiettivo]"])) {
    $add_aziendale = true;
    $obiettivo = new ObiettiviObiettivo($_REQUEST["keys[ID_obiettivo]"]);
    if ($obiettivo->data_eliminazione !== null) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo di un obiettivo eliminato.");
    }
    $cm->oPage->title = "Assegnazione CDR ad obiettivo '" . $obiettivo->codice . " - " . $obiettivo->titolo . "'";
}
//se ID_obiettivo_cdr o ID_obiettivo o ID_obiettivo_cdr_origine non sono stati passati viene visualizzato errore
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr / ID_obiettivo / ID obiettivo_origine.");
}

$cm->oPage->title = "Assegnazione CDR ad obiettivo '" . $obiettivo->codice . " - " . $obiettivo->titolo . "'";

//******************************************************************************
//privilegi dell'utente sull'a pagina'obiettivo_cdr
$user_privileges = array(
    //visibilità
    "view" => false,
    //visualizzazione dell'associazione e pesatura al dipendente
    "view_obiettivo_cdr_dipendente" => false,
    //visualizzazione dei coreferenti associati all'obiettivo
    "view_coreferenti_associati" => false,
    //modifica dei coreferenti associati all'obiettivo
    "edit_coreferenti_associati" => false,
    //modifica della selezione del cdr associato e del peso assegnato
    "edit_cdr_assegnazione_obiettivo_cdr" => false,
    "edit_peso_assegnazione_obiettivo_cdr" => false,
    //modifica delle azioni   
    "edit_azioni" => false,
    //modifica del parere sulle azioni
    "edit_parere_azioni" => false,
    //visualizzazione delle assegnazioni a cdr
    "view_assegnazioni_cdr" => false,
    //modifica delle assegnazioni a cdr e a personale
    "edit_assegnazioni_cdr_personale" => false,
    //modifica della data di chiusura modifica
    "edit_chiusura_modifiche" => false,
    //eliminazione obiettivo cdr
    "delete_obiettivo_cdr" => false,
);
//in aggiunta solamente l'utente amministratore degli obiettivi e il responsabile dell'obiettivo cdr di origine possono effettuare operazioni
if ($edit == true) {
    //l'ordine di assegnazione privilegi è incrementale
    //privilegi mutuamente esclusivi (ad esempio visualizzazione assegnazioni cdr-personale e visualizzazione assegnazione a dipendente selezionato)
    //vengono comunque gestiti in maniera separata per scalabilità della soluzione
    //privilegi per utente assegnato all'obiettivo_cdr
    //personale_assegnato
    if ($obiettivo_cdr_personale !== null) {
        //l'utente assegnato all'obiettivo potrà visualizzarlo solamente nel caso in cui l'obiettivo risulta chiuso
        //if ($obiettivo_cdr->isChiuso()) {
            $user_privileges["view"] = true;
            $user_privileges["view_obiettivo_cdr_dipendente"] = true;
        //}
    }
    //privilegi per utenti con la visualizzazione su tutti i cdr
    if ($user->hasPrivilege("cdr_view_all")) {
        $user_privileges["view"] = true;
        $user_privileges["view_assegnazioni_cdr"] = true;
        if ($obiettivo_cdr->isCoreferenza()) {
            $user_privileges["view_coreferenti_associati"] = true;
        }        
    }
    //privilegi per il responsabile del cdr padre gerarchico
    if ($resp_padre_ramo_cdr_selezionato) {
        $user_privileges["view"] = true;
        $user_privileges["view_assegnazioni_cdr"] = true;
        if ($obiettivo_cdr->isCoreferenza()) {
            $user_privileges["view_coreferenti_associati"] = true;
        }
    }
    //privilegi per il responsabile del cdr padre
    if ($resp_padre_cdr_selezionato) {
        $user_privileges["view"] = true;
        //assegnazioni visualizzabili solamente in modifica        
        $user_privileges["view_assegnazioni_cdr"] = true;
        if ($obiettivo_cdr->isCoreferenza()) {
            $user_privileges["view_coreferenti_associati"] = true;
        }
        //se l'obiettivo non è aziendale si permette la modifuca di assegnazione e peso
        if (!$obiettivo_cdr->isObiettivoCdrAziendale()) {
            //il parere sulle azioni può essere espresso solamente se sono definite azioni
            if (strlen($obiettivo_cdr->azioni) > 0) {
                $user_privileges["edit_parere_azioni"] = true;
            }
            //l'assegnazione ai cdr figli e al personale può essere effettuata se l'obiettivo non è chiuso
            if (!$obiettivo_cdr->isChiuso()) {
                $user_privileges["delete_obiettivo_cdr"] = true;
                $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = true;
            }
        } else if (!$obiettivo_cdr->isObiettivoCdrAziendale() && !$obiettivo_cdr->isChiuso()) {
            $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = true;
        }
    }
}
//privilegi per il responsabile del cdr (referente o coreferente obiettivo)
if ($resp_cdr_selezionato) {
    $user_privileges["view"] = true;
    //in aggiunta il responsabile del cdr è il padre dell'obiettivo che si sta assegnando!
    if ($edit == false) {
        $user_privileges["edit_cdr_assegnazione_obiettivo_cdr"] = true;
        $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = true;
    } else {
        $user_privileges["view_assegnazioni_cdr"] = true;
        if (!$obiettivo_cdr->isChiuso()) {
            $user_privileges["edit_assegnazioni_cdr_personale"] = true;
        }
        if ($obiettivo_cdr->isCoreferenza() || $obiettivo_cdr->isReferenteObiettivoTrasversale()){
            $user_privileges["view_coreferenti_associati"] = true;
        }
        //privilegi aggiuntivi referente coreferente
        if (!$obiettivo_cdr->isCoreferenza()) {
            //le azioni possono essere modificate solamente per gli obiettivi non ancora chiusi e se non è stato ancora espresso un parere su di esse
            if (!$obiettivo_cdr->isChiuso() && !$obiettivo_cdr->id_parere_azioni > 0) {
                $user_privileges["edit_azioni"] = true;
            }
        }
    }
}
//privilegi per l'amministratore degli obiettivi (unico che ha possibilità in inserimento)
if ($user->hasPrivilege("obiettivi_aziendali_edit")) {
    $user_privileges["view"] = true;
    //assegnazioni visualizzabili solamente in modifica
    if ($edit == true) {
        $user_privileges["view_assegnazioni_cdr"] = true;
        //se l'obiettivo è aziendale si permette la modifuca di assegnazione e peso
        if ($obiettivo_cdr->isObiettivoCdrAziendale()) {
            $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = true;
            $user_privileges["view_coreferenti_associati"] = true;
            $user_privileges["edit_coreferenti_associati"] = true;
            $user_privileges["delete_obiettivo_cdr"] = true;
            //il parere sulle azioni può essere espresso solamente se sono definite azioni
            if (strlen($obiettivo_cdr->azioni) > 0) {
                $user_privileges["edit_parere_azioni"] = true;
            }
        }
        $user_privileges["edit_chiusura_modifiche"] = true;
    } else {
        if ($obiettivo_cdr !== null && $obiettivo_cdr->isObiettivoCdrAziendale()) {
            $user_privileges["delete_obiettivo_cdr"] = true;
        }
        $user_privileges["edit_cdr_assegnazione_obiettivo_cdr"] = true;
        $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = true;
    }
}

//se l'utente non ha il privilegio di visualizzazione della pagina si verifica un errore
if ($user_privileges["view"] == false) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina dell'obiettivo.");
}
//altrimenti se è impostato il parametro no-actions vengono inibite tutte le modifiche
if (isset($_REQUEST["no_actions"]) && $_REQUEST["no_actions"] == 1) {
    $user_privileges["edit_coreferenti_associati"] = false;
    $user_privileges["edit_cdr_assegnazione_obiettivo_cdr"] = false;
    $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] = false;
    $user_privileges["edit_azioni"] = false;
    $user_privileges["edit_parere_azioni"] = false;
    $user_privileges["edit_assegnazioni_cdr_personale"] = false;
    $user_privileges["edit_chiusura_modifiche"] = false;
    $user_privileges["delete_obiettivo_cdr"] = false;
}

//******************************************************************************
//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-cdr-modify";
$oRecord->resources[] = "obiettivo-cdr";
$oRecord->src_table = "obiettivi_obiettivo_cdr";

//viene configurata la visibilità delle azioni in base ai privilegi degli utenti
if (!($user_privileges["edit_cdr_assegnazione_obiettivo_cdr"] == true ||
    $user_privileges["edit_peso_assegnazione_obiettivo_cdr"] == true ||
    $user_privileges["edit_azioni"] == true ||
    $user_privileges["edit_parere_azioni"] == true ||
    $user_privileges["edit_chiusura_modifiche"] == true
    )) {
    $oRecord->allow_update = false;
}
if (!$user_privileges["delete_obiettivo_cdr"] == true) {
    $oRecord->allow_delete = false;
} else {
    $db = ffDb_Sql::factory();
    //viene definita sul record l'eliminazione logica del record piuttosto che quella fisica
    $oRecord->del_action = "update";
    $oRecord->del_update = "data_eliminazione=" . $db->toSql(date("Y-m-d H:i:s"));
}

//evento per la propagazione delle azioni sulle relazioni
$oRecord->addEvent("on_done_action", "editRelations");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//******************************************************************************
//creazione fieldset per il riepilogo dell'obiettivo
$obiettivo_aziendale_desc = "";
if ($obiettivo_cdr !== null && $obiettivo_cdr->isObiettivoCdrAziendale()) {
    $obiettivo_aziendale_desc = "aziendale";
}
$oRecord->addContent(null, true, "riepilogo_obiettivo");
$oRecord->groups["riepilogo_obiettivo"]["title"] = "Riepilogo obiettivo " . $obiettivo_aziendale_desc . " '" . $obiettivo->codice . "'";

//id obiettivo collegato
$oRecord->insert_additional_fields["ID_obiettivo"] = new ffData($obiettivo->id, "Number");
if ($edit == true) {
    //riepilogo delle informazioni dell'obiettivo
    $oRecord->addContent($obiettivo->showHtmlInfo(), "riepilogo_obiettivo");
    if ($obiettivo_cdr !== null && !$obiettivo_cdr->isObiettivoCdrAziendale()) {
        $oRecord->addContent($obiettivo_cdr->showHtmlInfoPadre($date), "riepilogo_obiettivo");
    }
}

//******************************************************************************
//informazioni relative agli indicatori
$obiettivo_indicatori_associati = $obiettivo->getIndicatoriAssociati($where = array(), $order = array("ordine" => "ASC"));
if (count ($obiettivo_indicatori_associati)) {                
    $oRecord->addContent(null, true, "indicatori");
    $oRecord->groups["indicatori"]["title"] = "Indicatori";
    //recupero degli indicatori collegati all'obiettivo
    $grid_fields = array(
        "ID",
        "ID_indicatore",
        "nome",
        "descrizione",
        "valore_target_obiettivo",
    );
    $grid_recordset = array();
    //il valore target dell'obiettivo sarà quello del cdr di coreferenza in caso di coreferenza
    if ($cdr_padre_obiettivo !== null) {
        $cdr_valore_target = $cdr;
    } else {
        $cdr_valore_target = $cdr_padre_obiettivo;
    }
    foreach ($obiettivo_indicatori_associati as $indicatore) {
        $grid_recordset[] = array(
            $indicatore->obiettivo_indicatore->id,
            $indicatore->obiettivo_indicatore->id_indicatore,
            $indicatore->nome,
            $indicatore->descrizione,
            $indicatore->obiettivo_indicatore->getValoreTarget($cdr_valore_target),
        );
    }

    //visualizzazione della grid degli indicatori definiti per l'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivo-indicatore";
    $oGrid->title = "Indicatori associati all'obiettivo";
    $oGrid->resources[] = "obiettivo-indicatore";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_indicatore");
    $oGrid->order_default = "nome";
    $oGrid->record_id = "indicatore-modify";
    $oGrid->order_method = "labels";
    $oGrid->full_ajax = true;

    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $oGrid->record_url = FF_SITE_PATH . $path_info . "indicatori/dettagli_indicatore_obiettivo";
    $oGrid->use_paging = false;
    $oGrid->display_search = false;

    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;

    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_obiettivo_indicatore";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_indicatore";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "nome";
    $oField->base_type = "Text";
    $oField->label = "Nome";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore_target_obiettivo";
    $oField->base_type = "Text";
    $oField->label = "Valore target";
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $oRecord->addContent($oGrid, "indicatori");
    $cm->oPage->addContent($oGrid);
}

//******************************************************************************
//creazione fieldset assegnazione
$oRecord->addContent(null, true, "assegnazione");
if ($edit == true) {
    $oRecord->groups["assegnazione"]["title"] = "Assegnazione";
} else {
    $oRecord->groups["assegnazione"]["title"] = "Assegnazione (" . $tipo_piano->descrizione . ") all'obiettivo '" . $obiettivo->codice . " - " . $obiettivo->descrizione . "'";
}

$cdr_multipair = array();
//Selezione cdr, utilizzo anagrafica alla data di riferimento
//in inserimento vengono esclusi i cdr già associati all'obiettivo
if ($edit == false) {
    if ($add_aziendale == true) {
        $cdr_selezionabili = AnagraficaCdrObiettivi::getAnagraficaInData($date);
    } else {
        $cdr_selezionabili = $cdr->getFigli();
        foreach ($cdr_selezionabili as $key => $value) {
            $cdr_selezionabili[$key] = new AnagraficaCdr($value->id_anagrafica_cdr);
        }
        $oRecord->insert_additional_fields["ID_tipo_piano_cdr"] = new ffData($tipo_piano->id, "Number");
    }
    foreach ($cdr_selezionabili as $anagrafica_cdr) {
        if (!$obiettivo->isCdrAssociato($anagrafica_cdr)) {
            $cdr_multipair[] = array(
                    new ffData($anagrafica_cdr->codice),
                    new ffData($anagrafica_cdr->codice . " - " . $tipo_cdr->abbreviazione . " - " . $anagrafica_cdr->descrizione, "Number"),
            );
        }
    }
}
//in modifica viene semplicemente recuperato il cdr selezionato
else {
    $anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr->codice_cdr, $date);
    $tot_peso_cdr = $anagrafica_cdr->getPesoTotaleObiettivi($anno, $obiettivo);
    $cdr_multipair[] = array(
            new ffData($anagrafica_cdr->codice),
            new ffData($anagrafica_cdr->codice . " - " . $anagrafica_cdr->descrizione, "Number"),
    );
}

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_multipair;
$oField->multi_select_one_label = "Selezionare il cdr a cui assegnare all'obiettivo...";
//selezione del cdr possibile solamente in inserimento
if (!$user_privileges["edit_cdr_assegnazione_obiettivo_cdr"]) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->required = true;
}
$oField->label = "CDR assegnazione";
$oRecord->addContent($oField, "assegnazione");

//*************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->label = "Peso assegnato";
if (isset($tot_peso_cdr)) {
    $oField->label .= " (tot. peso obiettivi cdr escluso l'obiettivo corrente: " . $tot_peso_cdr . ")";
}
$oField->base_type = "Number";
if (!$user_privileges["edit_peso_assegnazione_obiettivo_cdr"]) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
} else {
    $oField->addValidator("number", array(true, OBIETTIVI_MIN_PESO, OBIETTIVI_MAX_PESO, true, true, true));
}
$oRecord->addContent($oField, "assegnazione");

//*************************************
//Visualizzazione dell'assegnazione e del peso del dipendente collegato
//la condizione $obiettivo_cdr_personale !== null risulta ridondante ma viene mantenuta per robustezza
if ($user_privileges["view_obiettivo_cdr_dipendente"] && $obiettivo_cdr_personale !== null) {
    $oRecord->addContent(null, true, "assegnazione_dipendente");
    $oRecord->groups["assegnazione_dipendente"]["title"] = "Assegnazione al dipendente " . $personale->cognome
        . " " . $personale->nome
        . " (matr." . $personale->matricola . ")";

    //calcolo peso
    $peso_tot_personale = $personale->getPesoTotaleObiettivi($anno);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "peso_dipendente_associato";
    $oField->base_type = "Text";
    $oField->label = "Peso / TOT peso cdr";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($obiettivo_cdr_personale->peso . " / " . $peso_tot_personale . " (" . number_format(CoreHelper::percentuale($obiettivo_cdr_personale->peso, $peso_tot_personale), 2) . "%)", "Text");
    $oRecord->addContent($oField, "assegnazione_dipendente");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_accettazione";      
    $oField->label = "Data accettazione obiettivo"; 
    if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {        
        $oField->default_value = new ffData($obiettivo_cdr_personale->data_accettazione, "DateTime");
        $oField->base_type = "DateTime";
        $oField->extended_type = "DateTime";        
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->data_type = "";                
    }        
    else {        
        $oField->default_value = new ffData($obiettivo_cdr_personale->data_accettazione, "DateTime");
        $oField->base_type = "Date";
        $oField->extended_type = "Date";
        $oField->data_type = "";
        $oField->widget = "datepicker";
        $oField->store_in_db = false;                            
    }  
    $oRecord->addContent($oField, "assegnazione_dipendente");
}

//*************************************
//Grid assegnazione obiettivo_coreferenti
if ($edit == true && $user_privileges["view_coreferenti_associati"]) {
    //in caso di coreferenza vengono estratti i coreferenti dell'obiettivo_cdr_padri, in caso contrario gli eventuali coreferenti dell'obiettivo_cdr
    if ($cdr_padre_obiettivo == null) {
        $coreferenti_associati = $obiettivo_cdr->getObiettiviCdrCoreferentiAssociati();
    } else {
        $coreferenti_associati = $obiettivo_cdr_padre->getObiettiviCdrCoreferentiAssociati();
    }
    //in caso di obiettivo di coreferenza viene aggiunto anche il cdr padre in visualizzazione 
    if ($cdr_padre_obiettivo !== null && !$user_privileges["edit_coreferenti_associati"]) {
        $coreferenti_associati[] = $obiettivo_cdr_padre;
    }
    //se non esistono coreferenti assocaiti e non è possibile modificarli la grid non viene visualizzati
    if (count($coreferenti_associati) > 0 || $user_privileges["edit_coreferenti_associati"]) {
        $oRecord->addContent(null, true, "obiettivo_cdr_coreferente");
        $oRecord->groups["obiettivo_cdr_coreferente"]["title"] = "CDR coreferenti obiettivo aziendale";

        $grid_fields = array(
            "ID",
            "desc_cdr",
            "peso",
            "data_chiusura_modifiche",
        );
        //recupero coreferenti associati all'obiettivo
        $grid_recordset = array();
        foreach ($coreferenti_associati as $obiettivo_cdr_coreferente) {
            //recupero descrizione cdr dal piano attivo del tipo di piano con priorità più alta
            try {
                $anagrafica_cdr_coreferente = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_coreferente->codice_cdr, $date);
                $cdr_coreferente_desc = $anagrafica_cdr_coreferente->codice . " - " . $anagrafica_cdr_coreferente->descrizione;
                //nel caso in cui il cdr sia referente per l'obiettivo viene specificato                
                if ($obiettivo_cdr_padre == $obiettivo_cdr_coreferente) {
                    $cdr_coreferente_desc .= " (referente)";
                }
            } catch (Exception $ex) {
                $cdr_coreferente_desc = "Codice cdr non valido / obsoleto";
            }
            //calcolo peso tot        
            //costruzione record
            $peso_totale_obiettivi = $anagrafica_cdr_coreferente->getPesoTotaleObiettivi($anno);
            $grid_recordset[] = array(
                $obiettivo_cdr_coreferente->id,
                $cdr_coreferente_desc,
                $obiettivo_cdr_coreferente->peso . " / " . $peso_totale_obiettivi . " (" . number_format(CoreHelper::percentuale($obiettivo_cdr_coreferente->peso, $peso_totale_obiettivi), 2) . "%)",
                $obiettivo_cdr_coreferente->data_chiusura_modifiche,
            );
        }
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivo-cdr-coreferente";
        $oGrid->resources[] = "obiettivo-cdr-coreferente";
        $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr");
        $oGrid->order_default = "desc_cdr";
        $oGrid->record_id = "obiettivo-cdr-coreferente-modify";
        $oGrid->order_method = "labels";
        //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
        $path_info_parts = explode("/", $cm->path_info);
        $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
        $oGrid->record_url = FF_SITE_PATH . $path_info . "obiettivo_cdr_coreferente_modify";
        $oGrid->addit_insert_record_param = $oGrid->addit_record_param = "keys[ID_obiettivo]=" . $obiettivo->id . "&";
        $oGrid->display_search = false;
        $oGrid->full_ajax = true;
        $oGrid->display_navigator = false;
        $oGrid->use_paging = false;

        //grid coreferenti modificabile solamente da chi ha i privilegi per farlo
        if (!$user_privileges["edit_coreferenti_associati"]) {
            $oGrid->display_new = false;
            $oGrid->display_edit_url = false;
            $oGrid->display_delete_bt = false;
        }

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo_cdr_coreferente";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->extended_type = "Date";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "desc_cdr";
        $oField->base_type = "Text";
        $oField->label = "CDR";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "peso";
        $oField->base_type = "Text";
        $oField->label = "Peso / TOT peso cdr";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $oRecord->addContent($oGrid, "obiettivo_cdr_coreferente");
        $cm->oPage->addContent($oGrid);
    }
}

if ($edit == true) {
    //creazione fieldset azioni
    $oRecord->addContent(null, true, "azioni");
    $oRecord->groups["azioni"]["title"] = "Azioni";

    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "azioni";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";

    //se il cdr è coreferente vengono visualizzate le azioni del padre
    if ($obiettivo_cdr->isCoreferenza()) {
        $oField->label = "Azioni definite dal CDR: '" . $cdr_padre_obiettivo->codice . " - " . $cdr_padre_obiettivo->descrizione . "' (referente obiettivo trasversale)";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->data_type = "";
        if (!strlen($obiettivo_cdr_padre->azioni)) {
            $oField->default_value = new ffData("Nessuna azione definita", "Text");
        } else {
            $oField->default_value = new ffData($obiettivo_cdr_padre->azioni, "Text");
        }
    } else {
        $oField->label = "Azioni definite dal CDR";
        if (!$user_privileges["edit_azioni"]) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
            if (!strlen($obiettivo_cdr->azioni)) {
                $oField->data_type = "";
                $oField->default_value = new ffData("Nessuna azione definita", "Text");
            }
        }
    }
    $oRecord->addContent($oField, "azioni");

    //*************************************
    //parere sulle azioni
    foreach (ObiettiviParereAzioni::getAttiveAnno($anno) AS $pareri_azioni) {
        $pareri_azioni_select[] = array(
            new ffData($pareri_azioni->id, "Number"),
            new ffData($pareri_azioni->descrizione, "Text")
        );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_parere_azioni";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $pareri_azioni_select;
    $oField->multi_select_one_label = "Nessun parere espresso...";
    //se il cdr è coreferente vengono visualizzate le azioni del padre
    if ($obiettivo_cdr->isCoreferenza()) {
        $oField->label = "Parere sulle azioni del CDR: '" . $cdr_padre_obiettivo->codice . " - " . $cdr_padre_obiettivo->descrizione . "' (referente obiettivo trasversale)";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->data_type = "";
        $oField->default_value = new ffData($obiettivo_cdr_padre->id_parere_azioni, "Number");
    } else {
        $oField->label = "Parere sulle azioni";
        if (!$user_privileges["edit_parere_azioni"]) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
    }
    $oRecord->addContent($oField, "azioni");

    //*************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_azioni";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    //se il cdr è coreferente vengono visualizzate le azioni del padre
    if ($obiettivo_cdr->isCoreferenza()) {
        $oField->label = "Note Azioni del CDR: '" . $cdr_padre_obiettivo->codice . " - " . $cdr_padre_obiettivo->descrizione . "' (referente obiettivo trasversale)";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->data_type = "";
        $oField->default_value = new ffData($obiettivo_cdr_padre->note_azioni, "Text");
    } else {
        $oField->label = "Note Azioni";
        if (!$user_privileges["edit_parere_azioni"]) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
    }
    $oRecord->addContent($oField, "azioni");

    //creazione fieldset chiusura modifiche
    $oRecord->addContent(null, true, "chiusura_modifiche");
    $oRecord->groups["chiusura_modifiche"]["title"] = "Data chiusura modifiche azioni ed assegnazioni";

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura_modifiche";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura";
    if (!$user_privileges["edit_chiusura_modifiche"]) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    } else {
        $oField->widget = "datepicker";
        $oField->addValidator("date");
    }
    $oRecord->addContent($oField, "chiusura_modifiche");
}

//*************************************    
if ($edit == true && $user_privileges["view_assegnazioni_cdr"]) {
    //**************************************************************************
    //Grid assegnazione obiettivo_cdr
    //viene recuperato il cdr su tutti i tipi di piano in cui è presente
    $cdr_piani = Cdr::getCdrPianiFromCodice($obiettivo_cdr->codice_cdr, $date->format("Y-m-d"));
    foreach ($cdr_piani as $cdr_piano) {
        $tipo_piano_corrente = $cdr_piano["tipo_piano_cdr"];
        $cdr_piano_corrente = $cdr_piano["cdr"];

        $oRecord->addContent(null, true, "cdr_assegnati" . $tipo_piano_corrente->id);
        $oRecord->groups["cdr_assegnati" . $tipo_piano_corrente->id]["title"] = $tipo_piano_corrente->descrizione . " - Elenco CDR ai quali l&acute;obiettivo è stato assegnato";

        $grid_fields = array(
            "ID",
            "codice_cdr",
            "desc_cdr",
            "responsabile_cdr",
            "peso",
        );
        //vengono estratti i figli del cdr per il piano selezionato e verificata l'associazione
        $grid_recordset = array();
        foreach ($cdr_piano_corrente->getFigli() as $cdr_figlio) {
            $obiettivo_cdr_figlio = ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_figlio);
            //se esiste l'obiettivo per il figlio e in caso di assegnazione aziendale non risulta trasversale
            if ($obiettivo_cdr_figlio !== null && !($obiettivo_cdr_figlio->isObiettivoCdrAziendale() && $obiettivo_cdr_figlio->isCoreferenza())) {
                $anagrafica_figlio = AnagraficaCdrObiettivi::factoryFromCodice($cdr_figlio->codice, $date);
                $peso_totale_obiettivi = $anagrafica_figlio->getPesoTotaleObiettivi($anno);
                $responsabile_cdr_figlio = $cdr_figlio->getResponsabile($date);
                //costruzione record
                $grid_recordset[] = array(
                    $obiettivo_cdr_figlio->id,
                    $cdr_figlio->codice,
                    $cdr_figlio->descrizione,
                    $responsabile_cdr_figlio->cognome . " " . $responsabile_cdr_figlio->nome . " (matr. " . $responsabile_cdr_figlio->matricola_responsabile . ")",
                    number_format($obiettivo_cdr_figlio->peso) . " / " . $peso_totale_obiettivi . " (" . number_format(CoreHelper::percentuale($obiettivo_cdr_figlio->peso, $peso_totale_obiettivi), 2) . "%)",
                );
            }
        }
        if (count($grid_recordset) > 0 || $user_privileges["edit_assegnazioni_cdr_personale"]){
            $oGrid = ffGrid::factory($cm->oPage);
            $oGrid->id = "obiettivo_cdr_" . $tipo_piano_corrente->id;
            $oGrid->title = $tipo_piano_corrente->descrizione . " - Elenco CDR ai quali l&acute;obiettivo è stato assegnato";
            $oGrid->resources[] = "obiettivo-cdr";
            $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr");
            $oGrid->order_default = "desc_cdr";
            $oGrid->record_id = "obiettivo-cdr-modify";
            $oGrid->order_method = "labels";
            //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
            $path_info_parts = explode("/", $cm->path_info);
            $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
            $oGrid->record_url = FF_SITE_PATH . $path_info . "dettagli_obiettivo";
            //parametri aggiuntivi
            $oGrid->addit_insert_record_param = "id_tipo_piano_cdr=" . $tipo_piano_corrente->id . "&
                                                                            ID_obiettivo_cdr_origine=" . $obiettivo_cdr->id . "&";
            $oGrid->display_search = false;        
            $oGrid->display_navigator = false;
            $oGrid->use_paging = false;
            //operazioni consentite in base ai privilegi dell'utente
            if (!$user_privileges["edit_assegnazioni_cdr_personale"]) {
                $oGrid->display_new = false;
                $oGrid->display_edit_url = false;
                $oGrid->display_delete_bt = false;
            }

            // *********** FIELDS ****************
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_obiettivo_cdr";
            $oField->data_source = "ID";
            $oField->base_type = "Number";
            $oField->label = "id";
            $oGrid->addKeyField($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "codice_cdr";
            $oField->base_type = "Text";
            $oField->label = "Codice CDR";
            $oGrid->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "desc_cdr";
            $oField->base_type = "Text";
            $oField->label = "CDR";
            $oGrid->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "responsabile_cdr";
            $oField->base_type = "Text";
            $oField->label = "Responsabile CDR";
            $oGrid->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "peso";
            $oField->base_type = "Text";
            $oField->label = "Peso obiettivo / tot peso obiettivi cdr";
            $oGrid->addContent($oField);

            // *********** ADDING TO PAGE ****************
            $oRecord->addContent($oGrid, "cdr_assegnati" . $tipo_piano_corrente->id);
            $cm->oPage->addContent($oGrid);
        }
    }
}

if ($edit == true) {
    //**************************************************************************
    //Grid associazione a personale    
    $oRecord->addContent(null, true, "personale_assegnato");
    $oRecord->groups["personale_assegnato"]["title"] = "Elenco Personale al quale l&acute;Obiettivo è stato assegnato";

    $grid_fields = array(
        "ID",
        "cognome",
        "nome",
        "matricola",
        "peso",
    );
    $grid_recordset = array();
    foreach ($obiettivo_cdr->getObiettivoCdrPersonaleAssociati() as $ob_cdr_per) {
        //se l'obiettivo_cdr_personale non è stato eliminato logicamente
        if ($ob_cdr_per->data_eliminazione == null) {
            //l'obiettivo-personale viene visualizzato solamente se la matricola è presente in anagrafica
            try {
                $personale = PersonaleObiettivi::factoryFromMatricola($ob_cdr_per->matricola_personale);
                $peso_tot_personale = $personale->getPesoTotaleObiettivi($anno);
                //costruzione record
                $grid_recordset[] = array(
                    $ob_cdr_per->id,
                    $personale->cognome,
                    $personale->nome,
                    $personale->matricola,
                    number_format($ob_cdr_per->peso) . " / " . $peso_tot_personale . " (" . number_format(CoreHelper::percentuale($ob_cdr_per->peso, $peso_tot_personale), 2) . "%)",
                );
            } catch (Exception $ex) {
                
            }
        }
    }
    if (count($grid_recordset) > 0 || $user_privileges["edit_assegnazioni_cdr_personale"]) {            
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivo_cdr_personale";
        $oGrid->title = "Elenco Personale al quale l&acute;Obiettivo è stato assegnato";
        $oGrid->resources[] = "obiettivo-cdr-personale";
        $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "personale");
        $oGrid->order_default = "cognome";
        $oGrid->record_id = "obiettivo-cdr-personale-modify";
        $oGrid->order_method = "labels";
        //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
        $path_info_parts = explode("/", $cm->path_info);
        $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
        $oGrid->record_url = FF_SITE_PATH . $path_info . "assegnazione_obiettivo_personale";
        //parametri aggiuntivi    
        $oGrid->full_ajax = true;
        $oGrid->display_search = false;
        $oGrid->display_navigator = false;
        $oGrid->use_paging = false;

        //operazioni consentite in base ai privilegi dell'utente
        if (!$user_privileges["edit_assegnazioni_cdr_personale"]) {
            $oGrid->display_new = false;
            $oGrid->display_edit_url = false;
            $oGrid->display_delete_bt = false;
        }

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo_cdr_personale";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cognome";
        $oField->base_type = "Text";
        $oField->label = "Cognome";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "nome";
        $oField->base_type = "Text";
        $oField->label = "Nome";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "matricola";
        $oField->base_type = "Text";
        $oField->label = "Matricola";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "peso";
        $oField->base_type = "Text";
        $oField->label = "Peso";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $oRecord->addContent($oGrid, "personale_assegnato");
        $cm->oPage->addContent($oGrid);
    }

    //**************************************************************************
    //Grid rendicontazione
    $oRecord->addContent(null, true, "rendicontazione");
    $oRecord->groups["rendicontazione"]["title"] = "Rendicontazioni obiettivo anno " . $anno->descrizione;

    $grid_fields = array(
        "ID_rendicontazione",
        "perc_raggiungimento",
        "perc_nucleo",
        "ID_periodo",
        "descrizione",
        "data_riferimento_inizio",
        "data_riferimento_fine",
        "ordinamento_anno",
    );
    $grid_recordset = array();
    foreach (ObiettiviPeriodoRendicontazione::getPeriodiRendicontazioneAnno($anno) as $periodo_rendicontazione) {
        //viene verificato che esista una valutazione collegata                    
        $ob_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale($anno);

        $rendicontazione_aziendale = $ob_cdr_aziendale->getRendicontazionePeriodo($periodo_rendicontazione);
        $id_rendicontazione = "";
        $ragg_cdr = "NV";
        $rendicontazione_periodo = $obiettivo_cdr->getRendicontazionePeriodo($periodo_rendicontazione);
        if ($rendicontazione_periodo !== null) {
            $id_rendicontazione = $rendicontazione_periodo->id;
        }
        $ragg_nucleo = "NV";
        if ($rendicontazione_aziendale !== null) {
            $rendicontazione_valutata_nucleo = $rendicontazione_aziendale->getValutazioneNucleo();
            if (strlen($rendicontazione_valutata_nucleo["rendicontazione"]->note_nucleo) > 0) {
                $ragg_nucleo = (int) $rendicontazione_valutata_nucleo["rendicontazione"]->perc_nucleo . "%";
            }
        }
        if ($obiettivo_cdr->isCoreferenza()) {
            $coreferenza_desc = " (trasversale)";
            $ragg_cdr = $rendicontazione_aziendale->perc_raggiungimento . "%";
        } else {
            $coreferenza_desc = "";
            $ragg_cdr = (int) $rendicontazione_periodo->perc_raggiungimento . "%";
        }

        //costruzione record
        $grid_recordset[] = array(
            $id_rendicontazione,
            $ragg_cdr . $coreferenza_desc,
            $ragg_nucleo,
            $periodo_rendicontazione->id,
            $periodo_rendicontazione->descrizione,
            $periodo_rendicontazione->data_riferimento_inizio,
            $periodo_rendicontazione->data_riferimento_fine,
            $periodo_rendicontazione->ordinamento_anno
        );
    }
    if (count($grid_recordset) > 0) {   
        $cm->oPage->addContent("<div id='rendicontazioni_obiettivo'>");
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "rendicontazione";
        $oGrid->title = "Rendicontazione obiettivo anno " . $anno->descrizione;
        $oGrid->resources[] = "rendicontazione";
        $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_periodo_rendicontazione");
        $oGrid->order_default = "ordinamento_anno";
        $oGrid->record_id = "rendicontazione-modify";
        $oGrid->order_method = "labels";
        //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
        $path_info_parts = explode("/", $cm->path_info);
        $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
        $oGrid->record_url = FF_SITE_PATH . $path_info . "rendicontazione";
        $oGrid->display_search = false;
        $oGrid->display_navigator = false;
        $oGrid->use_paging = false;

        //la visualizzazione della rendicontazione è sempre possibile per chi ha privilegio di visualizzazione dell'obiettivo a meno che l'obiettivo non risulti chiuso
        $oGrid->use_order = false;
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;   

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_rendicontazione";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_periodo";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ordinamento_anno";
        $oField->base_type = "Number";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "descrizione";
        $oField->base_type = "Text";
        $oField->label = "Descrizione";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_riferimento_inizio";
        $oField->base_type = "Date";
        $oField->label = "Data inizio periodo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_riferimento_fine";
        $oField->base_type = "Date";
        $oField->label = "Data fine periodo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "perc_raggiungimento";
        $oField->base_type = "Text";
        $oField->label = "Raggiungimento CDR";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "perc_nucleo";
        $oField->base_type = "Text";
        $oField->label = "Raggiungimento Nucleo (NVP) di Dipartimento";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $oRecord->addContent($oGrid, "rendicontazioni");
        $cm->oPage->addContent($oGrid);    
    }
}

// *********** ADDING TO PAGE ****************    
$cm->oPage->addContent($oRecord);

//propagazione dell'eliminazione sulle relazioni, salvataggio informazioni obiettivo-cdr-personale
function editRelations($oRecord, $frmAction) {    
    switch ($frmAction) {
        case "insert":            
        case "update":
            $user = LoggedUser::Instance();           
            //solo in caso di utente amministratore degli obiettivi sarà permessa la modifica
            if ($user->hasPrivilege("obiettivi_aziendali_edit") && isset($oRecord->form_fields["data_accettazione"])) {
                $obiettivo_cdr = new ObiettiviObiettivoCdr($oRecord->key_fields["ID_obiettivo_cdr"]->value->getValue());
                $personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);                
                $obiettivo_cdr_personale = null;
                $obiettivi_cdr_personale = $obiettivo_cdr->getObiettivoCdrPersonaleAssociati($personale->matricola);
                if (count($obiettivi_cdr_personale)) {
                    $obiettivo_cdr_personale = $obiettivi_cdr_personale[0];
                }
                $data_accettazione_record = $oRecord->form_fields["data_accettazione"]->value->getValue();
                //vengono formattate le date senza considerare il time per aggiornare il record solo in caso di variazione                           
                $formatted_data_accettazione_record = CoreHelper::formatUiDate($data_accettazione_record, "d/m/Y", "Y-m-d");                                                
                $formatted_data_accettazione_db = CoreHelper::formatUiDate($obiettivo_cdr_personale->data_accettazione, "Y-m-d H:i:s", "Y-m-d");
                                
                if ($formatted_data_accettazione_record != $formatted_data_accettazione_db) {                    
                    $obiettivo_cdr_personale->data_accettazione = CoreHelper::formatUiDate($data_accettazione_record, "d/m/Y", "Y-m-d H:i:s");                   
                    $obiettivo_cdr_personale->save();                    
                }                
            }
            break;
        case "delete":
        case "confirmdelete":
            //recupero parametri
            $obiettivo_cdr = new ObiettiviObiettivoCdr($oRecord->key_fields["ID_obiettivo_cdr"]->value->getValue());
            $cm = cm::getInstance();
            $date = $cm->oPage->globals["data_riferimento"]["value"];

            //recupero delle dipendenze dell'obiettivo_cdr, eliminazione e propagazione su tutti obiettivi_cdr_personale collegati
            //propagazione dell'eliminazione gestita tramite metodo dell'oggetto
            foreach ($obiettivo_cdr->getDipendenze($date) as $obiettivo_cdr_dipendenza) {
                $obiettivo_cdr_dipendenza->logicalDelete();
            }
            // Elimino tutto ciò che è collegato all'obiettivo principale
            $obiettivo_cdr->logicalDelete();
            
            break;
    }
}