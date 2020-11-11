<?php
$db = ffDb_Sql::factory();
$user = LoggedUser::Instance();

$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

$is_newProject = true;
$is_preso_in_carico = false;
$is_concluso = false;
$is_attesa_validazione = false;
if (isset ($_REQUEST["keys[ID]"])) {
    $is_newProject = false;

    try {
        $progetto = new ProgettiProgetto($_REQUEST["keys[ID]"]);
        
        if ($progetto->stato == "1" || $progetto->stato == "2") {
            $is_preso_in_carico = true;
        }
        else if ($progetto->stato == "3") {
            $is_concluso = true;
        }
        /*         
         * Gestione del nuovo stato "In attesa di validazione"
         */
        else if ($progetto->stato == "4") {
            $is_attesa_validazione = true;
        }
    }
    catch (Exception $ex){
        ffErrorHandler::raise("Errore nel passaggio dei parametri");
    }
}

// Recupero il codice CdR selezionato, solo se è un nuovo progetto
if ($is_newProject) {
    $cdr = $cm->oPage->globals["cdr"]["value"];
}

// Elenco dei possibili privilegi
$view = false;
$edit_responsabile_cdr = false;
$edit_responsabile_progetto = false;
$edit_responsabile_riferimento = false;

/**
 * Ruolo: Amministrazione
 * Solo view
 * */
if ($user->hasPrivilege("progetti_admin")){
    $view = true;
}

/**
 * Ruolo: Responsabile di CdR
 * Può creare progetti
 * Può modificare il titolo di un progetto già creato
 * --------------------------------------------------
 * Ruolo: Responsabile ramo gerarchico
 * Solo view
 * */
if ($user->hasPrivilege("resp_cdr_selezionato") && 
    ($is_newProject || ($user->matricola_utente_selezionato == $progetto->matricola_utente_creazione))) {
    $view = true;
    $edit_responsabile_cdr = true;
}
elseif ($user->hasPrivilege("resp_ramo_gerarchico")){
    $view = true;
}

/**
 * Ruolo: Responsabile di Progetto
 * Compila la scheda del progetto
 * Definisce il monitoraggio
 * Crea una nuova revisione
 * */
if ($user->matricola_utente_selezionato == $progetto->matricola_responsabile_progetto) {
    $view = true;
    $edit_responsabile_progetto = true;
}

/**
 * Ruolo: Responsabile di Riferimento
 * Approva o meno il progetto
 * */
if ($user->matricola_utente_selezionato == $progetto->matricola_responsabile_riferimento_approvazione) {
    $view = true;
    $edit_responsabile_riferimento = true;
}

if ($view == false) {
    ffErrorHandler::raise("L'utente non ha il permesso di visualizzare il progetto");
}

/*
 * Se il progetto è concluso, viene presentato solo in visualizzazione
 * Rif "Re: scheda progetti"
 * Anche il progetto "In attesa di validazione" va considerato
 * come un progetto concluso in cui l'utente non può più
 * inserire/modificare contenuti
 */
/*
 * Il progetto "In attesa di validazione" è ancora modificabile
 * nella sola parte dei monitoraggi, perciò non è bloccante
 */
if ($is_concluso) {
    $edit_responsabile_cdr = false;
    $edit_responsabile_progetto = false;
    $edit_responsabile_riferimento = false;
}

/*
 * Il Direttore di Riferimento deve approvare il monitoraggio finale
 */
if ($is_attesa_validazione && 
    $user->matricola_utente_selezionato == $progetto->matricola_responsabile_riferimento_approvazione) {
    $edit_responsabile_riferimento = true;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "progetto";
$oRecord->title = $is_newProject ? "Nuovo progetto" : "Progetto";
$oRecord->resources[] = "progetto";
$oRecord->src_table  = "progetti_progetto";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo_progetto";
$oField->base_type = "Text";
$oField->label = "Titolo progetto";
$oField->required = true;
/*
 * Solo il Responsabile di CdR può modificare il titolo,
 * a condizione che il progetto non sia stato approvato
 */
if (!$edit_responsabile_cdr || $is_preso_in_carico || $is_attesa_validazione) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

// Select per indicare il "Responsabile di Progetto"
foreach (Personale::getAll() AS $personale) {
    $anagrafe = $personale->cognome . " " . $personale->nome . " (matr. " . $personale->matricola . ")";

    $personale_select[] = array(
        new ffData ($personale->matricola, "Text"),
        new ffData ($anagrafe, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile_progetto";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $personale_select;
$oField->label = "Responsabile del Progetto";
$oField->required = true;
/*
 * Solo il Responsabile di CdR può modificare il Responsabile di Progetto
 * a condizione che il progetto non sia stato approvato
 */
if ((!$edit_responsabile_cdr && !$is_newProject) || $is_preso_in_carico || $is_attesa_validazione) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

/*
 * Campi descrittivi del progetto, i primi quattro field
 * sono bloccati in quanto informazioni di riepilogo.
 * La modifica sarà effettuata SOLO dal Responsabile di Progetto.
 */
if (!$is_newProject) {
    // Numero di progetto
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->label = "Numero Progetto";
    $oRecord->addContent($oField);
    
    $utente_creazione = Personale::factoryFromMatricola($progetto->matricola_utente_creazione);
    $oField = ffField::factory($cm->oPage);
    $oField->id = "matricola_utente_creazione";
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->display_value = new ffData(
        $utente_creazione->cognome." ".$utente_creazione->nome." (matr. ".$progetto->matricola_utente_creazione.")",
        "Text"
    );
    $oField->store_in_db = false;
    $oField->label = "Responsabile creazione progetto";
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdr_proponente";
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->label = "CdR Proponente";
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_creazione";
    $oField->base_type = "Datetime";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->label = "Data creazione";
    $oRecord->addContent($oField);

    $oRecord->addContent("<hr>");

    // Select per selezionare tipo di progetto (P1, P2, ecc...)
    foreach (ProgettiTipoProgetto::getAll() AS $libreria_tipo_progetto) {
        $libreria_tipo_progetto_select[] = array(
            new ffData ($libreria_tipo_progetto->id, "Number"),
            new ffData ($libreria_tipo_progetto->codice." (".$libreria_tipo_progetto->descrizione.")", "Text")
        );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tipo_progetto";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $libreria_tipo_progetto_select;
    $oField->label = "Impatto del progetto sull'organizzazione";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->multi_select_one_label = "Nessun elemento selezionato";
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "capofila";
    $oField->base_type = "Text";
    $oField->label = "Capofila - indicare l'ATS capofila o il Partner esterno capofila";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "team_progetto";
    $oField->base_type = "Text";
    $oField->label = "Team di progetto - Indicare il gruppo di lavoro dedicato al progetto";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $partner_interni_list = ProgettiProgettoPartnerInterni::getAll(
        array(
            "ID_progetto" => $progetto->id
        )
    );
    $grid_sql_source = "";
    foreach($partner_interni_list AS $partner_interni) {       
        if (strlen($grid_sql_source) > 0) {
            $grid_sql_source .= " UNION ";
        }

        $tipo_piano_cdr = Cdr::getTipoPianoPriorita($partner_interni->codice_cdr, $date);
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);
        $cdr = Cdr::factoryFromCodice($partner_interni->codice_cdr, $piano_cdr);
        $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);

        $grid_sql_source .= "
            SELECT ".$db->toSql($partner_interni->id)." AS ID_progetti_progetto_partner_interni,
                ".$db->toSql($partner_interni->codice_cdr)." AS codice_cdr,
                ".$db->toSql($tipo_cdr->abbreviazione." ".$cdr->descrizione)." AS descrizione_cdr
        ";
    }
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "progetti-progetto-partner-interni";
    $oGrid->title = "Partner interni - Selezione dei CdR che collaborano al progetto";
    $oGrid->resources[] = "progetto-partner-interni";
    $oGrid->full_ajax = true;
    if (strlen($grid_sql_source) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (". $grid_sql_source .") AS progetti_progetto_partner_interni
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT '' AS ID_progetti_progetto_partner_interni,  
                '' AS codice_cdr,
                '' AS descrizione_cdr
            FROM progetti_progetto_partner_interni
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    $oGrid->order_default = "codice_cdr";
    $oGrid->record_id = "progetto-partner-interni";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_progetto_partner_interni";
    $oGrid->record_url = $record_url;
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = true;
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_progetti_progetto_partner_interni";
    $oField->data_source = "ID_progetti_progetto_partner_interni";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdr";
    $oField->base_type = "Text";
    $oField->label = "CdR partner interno";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_cdr";
    $oField->base_type = "Text";
    $oField->label = "Descrizione CdR partner interno";
    $oGrid->addContent($oField);
    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "partner_esterni";
    $oField->base_type = "Text";
    $oField->label = "Partner esterni - Elenco dei partner esterni che partecipano
        al progetto (Aziende, Società, Gruppi di volontariato, Associazioni, Enti, ecc.)
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "obiettivo_generale_progetto";
    $oField->base_type = "Text";
    $oField->label = "Descrivere lo scopo/le finalità del progetto proposto";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_progetto";
    $oField->base_type = "Text";
    $oField->label = "Descrizione sintetica dei contenuti del progetto comprensivi di
        motivazioni ed esigenze
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "tema_progetto";
    $oField->base_type = "Text";
    $oField->label = "Specificare le tematiche sulle quali interviene il progetto (tematiche
        organizzative, gestionali, sviluppo di nuove attività, promozione della salute,
        igiene degli alimenti, presa in carico delle cronicità, ecc.)
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "modalita_progetto";
    $oField->base_type = "Text";
    $oField->label = "Indicare le principali modalità attuative che si intendono 
        attuare per la realizzazione del progetto
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "target_progetto";
    $oField->base_type = "Text";
    $oField->label = "Indicare i destinatari del progetto  es..(ruoli e figure 
        professionali, ambiti organizzativi, cittadini con patologie croniche, 
        ultrasessantacinquenni, donne in gravidanza, ecc)
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "analisi_contesto_progetto";
    $oField->base_type = "Text";
    $oField->label = "Analisi del contesto da cui si è generato il progetto - 
        Indicare l’analisi del problema o del bisogno, eventuali collegamenti ad 
        obiettivi nazionali, regionali o di Agenzia (ad es. i dati oggettivi che 
        avvalorano o giustificano l’implementazione del progetto)    
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);

    // Territorio di applicazione (ATS, Extra ATS, ATS ed extra-ATS)
    foreach (ProgettiTerritorioApplicazione::getAll() AS $libreria) {
        $libreria_select[] = array(
            new ffData ($libreria->id, "Number"),
            new ffData ($libreria->descrizione, "Text")
        );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_territorio_applicazione";
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $libreria_select;
    $oField->label = "Territorio di applicazione";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->multi_select_one_label = "Nessun elemento selezionato";
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "metodi_progetto";
    $oField->base_type = "Text";
    $oField->label = "Metodi, strumenti e azioni che si intendono sviluppare per 
        l’attuazione del progetto
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "risultati_attesi_progetto";
    $oField->base_type = "Text";
    $oField->label = "Specificare i risultati attesi dall’attuazione del progetto (
        esempio variazione dell’offerta / domanda, 
        miglioramento dell’efficienza/efficacia con indicazioni quantitative)
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "cambiamenti_altri_enti";
    $oField->base_type = "Text";
    $oField->label = "Indicare i cambiamenti richiesti  ai settori organizzativi 
        coinvolti direttamente o indirettamente nel progetto e necessari per 
        il successo dell’iniziativa
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_inizio_progetto";
    $oField->base_type = "Date";
    $oField->label = "Data inizio progetto";
    $oField->widget = "datepicker";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_fine_progetto";
    $oField->base_type = "Date";
    $oField->label = "Data fine progetto";
    $oField->widget = "datepicker";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $fase_tempo_realizzazione_list = ProgettiProgettoFaseTempoRealizzazione::getAll(
        array(
            "ID_progetto" => $progetto->id
        )
    );
    $grid_sql_source = "";
    foreach($fase_tempo_realizzazione_list AS $fase_tempo_realizzazione) {
        if (strlen($grid_sql_source) > 0) {
            $grid_sql_source .= " UNION ";
        }

        $grid_sql_source .= "
            SELECT ".$db->toSql($fase_tempo_realizzazione->id)." AS ID_progetti_progetto_fase_tempo_realizzazione,
                ".$db->toSql($fase_tempo_realizzazione->descrizione_fase)." AS descrizione_fase,
                ".$db->toSql($fase_tempo_realizzazione->data_inizio_fase)." AS data_inizio_fase,
                ".$db->toSql($fase_tempo_realizzazione->data_fine_fase)." AS data_fine_fase
        ";
    }
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-progetto-fase-tempo-realizzazione";
    $oGrid->title = "Fasi e tempi di realizzazione";
    $oGrid->resources[] = "progetto-fase-tempo-realizzazione";
    $oGrid->full_ajax = true;
    if (strlen($grid_sql_source) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (". $grid_sql_source .") AS progetti_progetto_fase_tempo_realizzazione
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT '' AS ID_progetti_progetto_fase_tempo_realizzazione,
                '' AS descrizione_fase,
                '' AS data_inizio_fase,
                '' AS data_fine_fase
            FROM progetti_progetto_fase_tempo_realizzazione
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]            
        ";
    }
    $oGrid->order_default = "ID_progetti_progetto_fase_tempo_realizzazione";
    $oGrid->record_id = "progetto-fase-tempo-realizzazione";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_progetto_fase_tempo_realizzazione";
    $oGrid->record_url = $record_url;
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = true;
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_progetti_progetto_fase_tempo_realizzazione";
    $oField->base_type = "Number";
    $oField->label = "ID";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_fase";
    $oField->base_type = "Text";
    $oField->label = "Descrizione fase";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_inizio_fase";
    $oField->base_type = "Date";
    $oField->label = "Data inizio fase";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_fine_fase";
    $oField->base_type = "Date";
    $oField->label = "Data fine fase";
    $oGrid->addContent($oField);
    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "frequenza_monitoraggio";
    $oField->base_type = "Text";
    $oField->label = "Frequenza del monitoraggio (mensile, trimestrale, semestrale, ecc.)";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "metodo_monitoraggio";
    $oField->base_type = "Text";
    $oField->label = "Indicare  gli strumenti e la modalità che si 
        intende applicare per realizzare un efficace controllo 
        sullo stato d’attuazione del progetto
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $indicatore_list = ProgettiProgettoIndicatore::getAll(
        array(
            "ID_progetto" => $progetto->id
        )
    );
    $grid_sql_source = "";
    foreach($indicatore_list AS $indicatore) {
        if (strlen($grid_sql_source) > 0) {
            $grid_sql_source .= " UNION ";
        }

        $grid_sql_source .= "
            SELECT ".$db->toSql($indicatore->id)." AS ID_progetti_progetto_indicatore,
                ".$db->toSql($indicatore->descrizione)." AS descrizione,
                ".$db->toSql($indicatore->valore_atteso)." AS valore_atteso
        ";
    }
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-progetto-indicatore";
    $oGrid->title = "Indicatori atti a verificare gli obiettivi del progetto";
    $oGrid->resources[] = "progetto-indicatore";
    $oGrid->full_ajax = true;
    if (strlen($grid_sql_source) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (". $grid_sql_source .") AS progetti_progetto_indicatore
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT '' AS ID_progetti_progetto_indicatore,
                '' AS descrizione,
                '' AS valore_atteso
            FROM progetti_progetto_indicatore
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]            
        ";
    }
    $oGrid->order_default = "ID_progetti_progetto_indicatore";
    $oGrid->record_id = "progetto-indicatore";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_progetto_indicatore";
    $oGrid->record_url = $record_url;
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    $oGrid->display_edit_url = false;    
    $oGrid->display_delete_bt = false;
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oGrid->display_new = false;
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_progetti_progetto_indicatore";
    $oField->base_type = "Number";
    $oField->label = "ID";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore_atteso";
    $oField->base_type = "Text";
    $oField->label = "Valore attesto";
    $oGrid->addContent($oField);
    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "budget";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Budget - previsione delle risorse finanziarie 
        necessarie per l’attuazione del progetto
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "risorse_gia_disponibili";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->control_type = "radio";
    $oField->multi_pairs = array (
        array(
            new ffData("1", "Number"),
            new ffData("Si", "Text")
        ),
        array(
            new ffData("2", "Number"),
            new ffData("Parzialmente", "Text")
        ),
        array(
            new ffData("0", "Number"),
            new ffData("No", "Text")
        ),
    );
    $oField->label = "Risorse già disponibili (personale, attrezzature, spazi, ecc)?";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "costi_indotti";
    $oField->base_type = "Text";
    $oField->label = "Indicare la tipologia dei costi indotti dal progetto, 
        impiego di risorse già disponibili necessarie all’attuazione del progetto
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_totale_costi_indotti";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Stimare il valore dei costi indotti complessivi";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_risorse_aggiuntive";
    $oField->base_type = "Text";
    $oField->label = "Descrizione risorse aggiuntive richieste 
        da acquisire (personale, attrezzature, spazi, attività di formazione, ecc.)
    ";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_risorse_aggiuntive";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo risorse aggiuntive richieste (indicare una stima)";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "materiali";
    $oField->base_type = "Text";
    $oField->label = "Risorse Materiali (beni e servizi) da acquisire";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_materiali";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo risorse materiali (beni e servizi) da acquisire";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "spazi";
    $oField->base_type = "Text";
    $oField->label = "Spazi necessari";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "risorse_professionali_coinvolte";
    $oField->base_type = "Text";
    $oField->label = "Risorse professionali esterne da acquisire";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_risorse_professionali_coinvolte";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo per eventuali risorse professionali esterne da acquisire";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "altro";
    $oField->base_type = "Text";
    $oField->label = "Altre risorse non presenti da acquisire per l’attuazione del progetto";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo_altro";
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oField->label = "Importo altre risorse da acquisire";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $risorse_finanziarie_disponibili = array();
    foreach (ProgettiRisorseFinanziarieDisponibili::getAll() as $item) {
        $risorse_finanziarie_disponibili[] = array(
            new ffData($item->id, "Number"),
            new ffData($item->descrizione, "Text"),
        );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_risorse_finanziarie_disponibili";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->control_type = "radio";
    $oField->multi_pairs = $risorse_finanziarie_disponibili;
    $oField->label = "Sono disponibili le risorse finanziare da dedicare al progetto?";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $finanziamento_list = ProgettiProgettoFinanziamento::getAll(
        array(
            "ID_progetto" => $progetto->id
        )
    );
    $grid_sql_source = "";
    foreach($finanziamento_list AS $finanziamento) {
        if (strlen($grid_sql_source) > 0) {
            $grid_sql_source .= " UNION ";
        }

        $grid_sql_source .= "
            SELECT ".$db->toSql($finanziamento->id)." AS ID_progetti_progetto_finanziamento,
                ".$db->toSql($finanziamento->importo)." AS importo,
                ".$db->toSql($finanziamento->origine)." AS origine,
                ".$db->toSql($finanziamento->descrizione)." AS descrizione,
                ".$db->toSql($finanziamento->atto)." AS atto
        ";
    }
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-progetto-finanziamento";
    $oGrid->title = "Finanziamenti dedicati al progetto";
    $oGrid->resources[] = "progetto-finanziamento";

    $oGrid->full_ajax = true;
    if (strlen($grid_sql_source) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (". $grid_sql_source .") AS progetti_progetto_finanziamento
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT '' AS ID_progetti_progetto_finanziamento,
                '' AS importo,
                '' AS origine,
                '' AS descrizione,
                '' AS atto
            FROM progetti_progetto_finanziamento
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]            
        ";
    }
    $oGrid->order_default = "ID_progetti_progetto_finanziamento";
    $oGrid->record_id = "progetto-finanziamento";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_progetto_finanziamento";
    $oGrid->record_url = $record_url;
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = true;
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_progetti_progetto_finanziamento";
    $oField->base_type = "Number";
    $oField->label = "ID";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "importo";
    $oField->base_type = "Number";
    $oField->label = "Importo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "origine";
    $oField->base_type = "Text";
    $oField->label = "Origine";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "atto";
    $oField->base_type = "Text";
    $oField->label = "Atto";
    $oGrid->addContent($oField);

    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "oracle_erp";
    $oField->base_type = "Text";
    $oField->label = "Cod. Oracle - ERP";
    if (!$edit_responsabile_progetto) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    if ($progetto->stato != "1") {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    if ($is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    if (isset ($_REQUEST['oracle_erp'])) {
        $oracle_erp = $_REQUEST['oracle_erp'];

        $oField->value = new ffData($oracle_erp, "Text");
    }
    $oRecord->addContent($oField);
    
    foreach (Personale::getAll() as $personale) {
        $anagrafe = $personale->cognome . " " . $personale->nome . " (matr. " . $personale->matricola_responsabile . ")";
        $personale_select[] = array(
            new ffData ($personale->matricola_responsabile, "Text"),
            new ffData ($anagrafe, "Text")
        );
    }
    $oField = ffField::factory($cm->oPage);
    $oField->id = "matricola_responsabile_riferimento_approvazione";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $personale_select;
    $oField->label = "Direttore di Riferimento";
    if (!$edit_responsabile_progetto || $is_preso_in_carico || $is_attesa_validazione) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    if ($is_attesa_validazione){
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);

    $oRecord->addContent("<hr>");
    
    $numero_totale_indicatori = ProgettiProgettoIndicatore::getNumeroTotaleIndicatori($progetto->id);
    $monitoraggio_list = ProgettiMonitoraggio::getAll(
        array(
            "ID_progetto" => $progetto->id
        )
    );
    $grid_sql_source = "";
    foreach($monitoraggio_list AS $monitoraggio) {
        if (strlen($grid_sql_source) > 0) {
            $grid_sql_source .= " UNION ";
        }       
        $tipologia_monitoraggio = new ProgettiTipologiaMonitoraggio($monitoraggio->id_tipologia_monitoraggio);
        
        $numero_totale_indicatori_non_consuntivati = ProgettiProgettoIndicatore::getNumeroTotaleIndicatoriNonConsuntivati($progetto->id, $monitoraggio->id);

        $grid_sql_source .= "
            SELECT ".$db->toSql($monitoraggio->id, "Number")." AS ID_progetti_monitoraggio,
                ".$db->toSql($monitoraggio->numero_monitoraggio)." AS numero_monitoraggio,
                ".$db->toSql($tipologia_monitoraggio->descrizione)." AS ID_tipologia_monitoraggio,
                ".$db->toSql($monitoraggio->descrizione_fase)." AS descrizione_fase,
                ".$db->toSql($monitoraggio->costi_sostenuti)." AS costi_sostenuti,
                ".$db->toSql($monitoraggio->descrizione_utilizzo_risorse)." AS descrizione_utilizzo_risorse,
                ".$db->toSql($monitoraggio->note_rispetto_risorse_previste)." AS note_rispetto_risorse_previste,
                ".$db->toSql($monitoraggio->note_rispetto_tempistiche)." AS note_rispetto_tempistiche,
                ".$db->toSql($monitoraggio->note_replicabilita_progetto)." AS note_replicabilita_progetto,
                ".$db->toSql(($numero_totale_indicatori - $numero_totale_indicatori_non_consuntivati) ."/". $numero_totale_indicatori) ." AS riepilogo_indicatori
        ";
    }
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "dettaglio-monitoraggio";
    $oGrid->title = "Monitoraggio";
    $oGrid->resources[] = "monitoraggio";
    if (strlen($grid_sql_source) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (". $grid_sql_source .") AS progetti_monitoraggio
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT '' AS ID_progetti_monitoraggio,
                '' AS numero_monitoraggio,
                '' AS ID_tipologia_monitoraggio,
                '' AS descrizione_fase,
                '' AS costi_sostenuti,
                '' AS descrizione_utilizzo_risorse,
                '' AS note_rispetto_risorse_previste,
                '' AS note_rispetto_tempistiche,
                '' AS note_replicabilita_progetto
            FROM progetti_monitoraggio
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]            
        ";
    }
    $oGrid->order_default = "ID_progetti_monitoraggio";
    $oGrid->record_id = "monitoraggio";    
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $record_url = FF_SITE_PATH . $path_info . "dettaglio_monitoraggio";
    $oGrid->record_url = $record_url;
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_progetti_monitoraggio";
    $oField->base_type = "Number";
    $oField->label = "ID";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "numero_monitoraggio";
    $oField->base_type = "Number";
    $oField->label = "Numero monitoraggio";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_tipologia_monitoraggio";
    $oField->base_type = "Text";
    $oField->label = "Tipologia monitoraggio";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_fase";
    $oField->base_type = "Text";
    $oField->label = "Descrizione fase";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione_utilizzo_risorse";
    $oField->base_type = "Text";
    $oField->label = "Descr. utilizzo risorse";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "costi_sostenuti";
    $oField->base_type = "Number";
    $oField->label = "Costi sostenuti";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "riepilogo_indicatori";
    $oField->base_type = "Text";
    $oField->label = "Ind. consuntivati/Ind. totali";
    $oGrid->addContent($oField);

    $oGrid->display_edit_url = true;
    // Stato = 1 => progetto approvato
    if ($progetto->stato == "1") {

        if (!$edit_responsabile_progetto) {
            $oGrid->display_new = false;
            $oGrid->display_delete_bt = false;            
        }
    }
    else {
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;
    }    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);

    $oRecord->addContent("<hr>");

    if ($progetto->stato == "1" || // Approvato
        $progetto->stato == "4" || // In attesa di validazione, ma cmq approvato
        $progetto->stato == "3"    // Concluso, ma cmq approvato
    ) {        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_approvazione";
        $oField->base_type = "DateTime";
        $oField->control_type = "label";
        $oField->label = "Data approvazione";
        $oRecord->addContent($oField);
    }
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note";
    $oField->base_type = "Text";
    $oField->label = "Note approvazione";
    if (!$edit_responsabile_riferimento) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "numero_revisione";
    $oField->base_type = "Number";
    $oField->label = "Numero revisione";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oRecord->addContent($oField);

    //Responsabile di Progetto  può emettere una nuova revisione se il progetto è stato preso in carico: viene ceato il pulsante per la nuova revisione     
    if ($edit_responsabile_progetto && $is_preso_in_carico) {

        // Pulsante per emettere una nuova revisione
        $oBt = ffButton::factory($cm->oPage);
        $oBt->id = "prj_nuova_revisione";
        $oBt->label = "Nuova revisione";
        $oBt->action_type = "submit";
        $oBt->jsaction = "$('#inactive_body').show();$('#conferma_prj_nuova_revisione').show();";
        $oBt->aspect = "link";
        $oBt->class = "fa-edit";
        $oRecord->addActionButton($oBt);

        $oRecord->addEvent("on_do_action", "myPrjNuovaRevisione");

        $cm->oPage->addContent("
            <div id='inactive_body'></div>
            <div id='conferma_prj_nuova_revisione'>
                <h3>Conferma una nuova revisione del progetto \"" . $progetto->titolo_progetto . "\"</h3>
                <p>Confermando, il progetto dovrà essere nuovamente approvato dal Direttore di Riferimento</p>
                <a id='conferma_si_prj_nuova_revisione' class='conferma_si confirm_link'>Conferma</a>
                <a id='conferma_no_prj_nuova_revisione' class='conferma_no confirm_link'>Annulla</a>
            </div>
            <script>
                $('.conferma_si').click(function(){
                    if ($('#conferma_prj_nuova_revisione').is(':visible')) {
                        document.getElementById('frmAction').value = 'progetto_prj_nuova_revisione';
                    }    
                    
                    document.getElementById('frmMain').submit();
                });
                $('.conferma_no').click(function(){
                    $('#inactive_body').hide();
                    
                    if ($('#conferma_prj_nuova_revisione').is(':visible')) {
                        $('#conferma_prj_nuova_revisione').hide();          
                    }
        
                });
            </script>
        ");
    }

    /*
     * Se utente Responsabile di Riferimento ed il progetto NON è stato preso in carico,
     * viene visualizzato pulsante per approvarlo/non approvarlo
     */
    if ($edit_responsabile_riferimento && !$is_preso_in_carico && !$is_attesa_validazione) {
        // Pulsante per approvare il progetto
        $oBt = ffButton::factory($cm->oPage);
        $oBt->id = "prj_approvare";
        $oBt->label = "Approvare";
        $oBt->action_type = "submit";
        $oBt->jsaction = "$('#inactive_body').show();$('#conferma_prj_approvare').show();";
        $oBt->aspect = "link";
        $oBt->class = "fa-edit";
        $oRecord->addActionButton($oBt);

        // Pulsante per NON approvare il progetto
        $oBt = ffButton::factory($cm->oPage);
        $oBt->id = "prj_non_approvare";
        $oBt->label = "Non approvare";
        $oBt->action_type = "submit";
        $oBt->jsaction = "$('#inactive_body').show();$('#conferma_prj_non_approvare').show();";
        $oBt->aspect = "link";
        $oBt->class = "fa-edit";
        $oRecord->addActionButton($oBt);

        $oRecord->addEvent("on_do_action", "myPrjStatoApprovazione");

        $cm->oPage->addContent("
            <div id='inactive_body'></div>
            <div id='conferma_prj_approvare'>
                <h3>Conferma approvazione del progetto \"" . $progetto->titolo_progetto . "\"</h3>                
                <a id='conferma_si_prj_approvare' class='conferma_si confirm_link'>Conferma</a>
                <a id='conferma_no_prj_approvare' class='conferma_no confirm_link'>Annulla</a>
            </div>
            <div id='conferma_prj_non_approvare'>
                <h3>Conferma <strong>NON</strong> approvazione del progetto \"" . $progetto->titolo_progetto . "\"</h3>                            
                <a id='conferma_si_prj_non_approvare' class='conferma_si confirm_link'>Conferma</a>
                <a id='conferma_no_prj_non_approvare' class='conferma_no confirm_link'>Annulla</a>
            </div>
            <script>
                $('.conferma_si').click(function(){
                    if ($('#conferma_prj_approvare').is(':visible')) {
                        document.getElementById('frmAction').value = 'progetto_prj_approvare';          
                    }            
                    else if ($('#conferma_prj_non_approvare').is(':visible')) {
                        document.getElementById('frmAction').value = 'progetto_prj_non_approvare';
                    }
                    
                    document.getElementById('frmMain').submit();
                });
                $('.conferma_no').click(function(){
                    $('#inactive_body').hide();
                    
                    if ($('#conferma_prj_approvare').is(':visible')) {
                        $('#conferma_prj_approvare').hide();         
                    }            
                    else if ($('#conferma_prj_non_approvare').is(':visible')) {
                        $('#conferma_prj_non_approvare').hide();
                    }      
                });
            </script>
        ");
    }

    if (($is_attesa_validazione && $edit_responsabile_riferimento) || $progetto->stato == "3") {
        $oRecord->addContent("<hr>");
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "validazione_finale";
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
        $oField->required = true;
        $oField->label = "Validazione finale";
        if ($progetto->stato == "3") {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "note_validazione_finale";
        $oField->base_type = "Text";
        $oField->label = "Note validazione";
        if ($progetto->stato == "3") {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
        $oRecord->addContent($oField);

        if ($is_attesa_validazione && $edit_responsabile_riferimento) {
            // Pulsante per effettuare la validazione finale
            $oBt = ffButton::factory($cm->oPage);
            $oBt->id = "prj_validazione_finale";
            $oBt->label = "Effettua validazione finale";
            $oBt->action_type = "submit";
            $oBt->jsaction = "$('#inactive_body').show();$('#conferma_prj_validazione_finale').show();";
            $oBt->aspect = "link";
            $oBt->class = "fa-edit";
            $oRecord->addActionButton($oBt);

            $oRecord->addEvent("on_do_action", "myPrjValidazioneFinale");

            $cm->oPage->addContent("
                <div id='inactive_body'></div>
                <div id='conferma_prj_validazione_finale'>
                    <h3>Conferma validazione finale del progetto \"" . $progetto->titolo_progetto . "\"</h3>
                    <a id='conferma_si_prj_validazione_finale' class='conferma_si confirm_link'>Conferma</a>
                    <a id='conferma_no_prj_validazione_finale' class='conferma_no confirm_link'>Annulla</a>
                </div>
                
                <script>
                    $('.conferma_si').click(function(){
                        if ($('#conferma_prj_validazione_finale').is(':visible')) {
                            document.getElementById('frmAction').value = 'progetto_prj_validazione_finale';
                        }     
                        
                        document.getElementById('frmMain').submit();
                    });
                    $('.conferma_no').click(function(){
                        $('#inactive_body').hide();
                        
                        if ($('#conferma_prj_validazione_finale').is(':visible')) {
                            $('#conferma_prj_validazione_finale').hide();
                        }           
                    });
                </script>
            ");
        }
    }
}

$oRecord->allow_delete = false;

// Il Responsabile di CdR può inserire nuovi progetti
if (!$edit_responsabile_cdr){
    $oRecord->allow_insert = false;
}

// Senza ruoli di edit non si può fare update
if (!$edit_responsabile_cdr && !$edit_responsabile_progetto && !$edit_responsabile_riferimento){
    $oRecord->allow_update = false;
}

if ($edit_responsabile_cdr && ($is_preso_in_carico || $is_attesa_validazione)) {
    $oRecord->allow_update = false;
}

if ($edit_responsabile_progetto && $is_attesa_validazione) {
    $oRecord->allow_update = false;
}

if ($is_newProject) {
    $oRecord->insert_additional_fields["matricola_utente_creazione"] = new ffData($user->matricola_utente_selezionato, "Text");
    $oRecord->insert_additional_fields["codice_cdr_proponente"] = new ffData($cdr->codice, "Text");
    $oRecord->insert_additional_fields["data_creazione"] = new ffData(date("Y-m-d H:i:s"), "Datetime");
    $oRecord->insert_additional_fields["stato"] = new ffData("0", "Text");
    $oRecord->insert_additional_fields["numero_revisione"] = new ffData(1, "Number");
}
else {

    if ($edit_responsabile_riferimento) {
        $oRecord->allow_update = false;
    }
}

$oRecord->addContent("
    <script type='text/javascript'>
        $('#dettaglio-monitoraggio>div>a.btn').click(function (){
            var oracle_erp = $('#progetto_oracle_erp').val();
            if (oracle_erp.length === 0) {
            }
            else {
                var url = $('#dettaglio-monitoraggio>div>a.btn').attr('href');
                var parameters_index = (url.indexOf('?') + 1);
                var first_part = url.substring(0, parameters_index);
                first_part += ('oracle_erp='+encodeURI(oracle_erp)+'&');
                url = first_part + url.substring(parameters_index);
                $('#dettaglio-monitoraggio>div>a.btn').attr('href', url);
            }
        });
        
        if ('$oracle_erp'.length === 0) {
            
        }
        else {
            $('#progetto_oracle_erp').attr('value', '$oracle_erp');
        }
        
    </script>
");

// Un progetto non approvato può avere solamente una nuova reviosne
if ($progetto->stato == "2") {
    $oRecord->allow_update = false;
}

$cm->oPage->addContent($oRecord);

function myPrjNuovaRevisione($oRecord, $frmAction) {
    if (!empty($frmAction) && $frmAction == "prj_nuova_revisione") {
        $message = "";

        $progetto = new ProgettiProgetto($oRecord->key_fields["ID"]->value->getValue());

        $progetto->oracle_erp = new ffData(null, "Text");
        $progetto->stato = new ffData("0", "Text");
        $progetto->data_approvazione = new ffData(null, "Datetime");
        $progetto->note = new ffData(null, "Text");
        $progetto->numero_revisione = new ffData($progetto->numero_revisione + 1, "Number");

        // Progetto revisionato
        $message = "Progetto \"".$progetto->titolo_progetto."\" in attesa di una nuova revisione";

        try {
            $save_status = $progetto->save();
        }
        catch (Exception $exc_query_update) {
            $message = $exc_query_update->getMessage();

            $save_status = false;
        }

        if ($save_status) {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_SUCCESS);
        }
        else {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_ERROR);
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

function myPrjStatoApprovazione($oRecord, $frmAction) {
    if (!empty($frmAction) && ($frmAction == "prj_approvare" || $frmAction == "prj_non_approvare")) {
        $message = "";

        $progetto = new ProgettiProgetto($oRecord->key_fields["ID"]->value->getValue());

        $progetto->note = new ffData($oRecord->form_fields["note"]->value->getValue(), "Text");

        if ($frmAction == "prj_approvare") {
            // Progetto approvato
            $progetto->stato = new ffData(1, "Number");
            $progetto->data_approvazione = new ffData(date("Y-m-d H:i:s"), "Datetime");

            $message = "Progetto \"".$progetto->titolo_progetto."\" approvato";
        } else if ($frmAction == "prj_non_approvare") {
            // Progetto NON approvato
            $progetto->stato = new ffData(2, "Number");
            $progetto->data_approvazione = new ffData(null, "Datetime");

            $message = "Progetto \"".$progetto->titolo_progetto."\" <strong>NON</strong> approvato";
        }

        try {
            $save_status = $progetto->save();
        }
        catch (Exception $exc_query_update) {
            $message = $exc_query_update->getMessage();

            $save_status = false;
        }

        if ($save_status) {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_SUCCESS);
        }
        else {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_ERROR);
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

function myPrjValidazioneFinale($oRecord, $frmAction) {
    if (!empty($frmAction) && $frmAction == "prj_validazione_finale") {
        $message = "";

        $progetto = new ProgettiProgetto($oRecord->key_fields["ID"]->value->getValue());

        $progetto->stato = new ffData(3, "Number");
        $progetto->validazione_finale = new ffData($oRecord->form_fields["validazione_finale"]->value->getValue(), "Text");
        $progetto->note_validazione_finale = new ffData($oRecord->form_fields["note_validazione_finale"]->value->getValue(), "Text");
        $progetto->data_validazione_finale = new ffData(date("Y-m-d H:i:s"), "Datetime");

        if ($progetto->validazione_finale == "1") {
            $message = "Progetto \"".$progetto->titolo_progetto."\" validato";
        }
        else if ($progetto->validazione_finale == "0") {
            $message = "Progetto \"".$progetto->titolo_progetto."\" <strong>NON</strong> validato";
        }

        try {
            $save_status = $progetto->saveValidazioneFinale();
        }
        catch (Exception $exc_query_update) {
            $message = $exc_query_update->getMessage();

            $save_status = false;
        }

        if ($save_status) {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_SUCCESS);
        }
        else {
            mod_notifier_add_message_to_queue($message, MOD_NOTIFIER_ERROR);
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