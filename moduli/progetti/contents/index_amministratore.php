<?php
$user = LoggedUser::Instance();

$view = false;
$edit_responsabile_cdr = false;
$edit_responsabile_progetto = false;
$edit_responsabile_riferimento = false;

if ($user->hasPrivilege("progetti_admin")){
    $view = true;
}

/**
 * Ruolo: Responsabile di CdR
 * Visualizzazione pulsante Aggiungi
 * --------------------------
 * Ruolo: Responsabile ramo gerarchico
 * Solo view
 * */
if ($user->hasPrivilege("resp_cdr_selezionato")){
    $view = true;
    $edit_responsabile_cdr = true;
}
elseif ($user->hasPrivilege("resp_ramo_gerarchico")){
    $view = true;
}

$db = ffDb_Sql::factory();

/*
 * Recupero di tutti progetti attivi.
 * Successivamente verranno discriminati per visibilità utente
 * in base a Responsabile di CdR e Responsabile di Riferimento
 */
$progetti_visibili = ProgettiProgetto::getAll();

$source_sql = "";
foreach ($progetti_visibili as $progetto) {

    /**
     * Ruolo: Responsabile di Progetto
     * Può procedere con la modifica del progetto per completarlo
     * */
    if ($user->matricola_utente_selezionato == $progetto->matricola_responsabile_progetto) {
        $view = true;
        $edit_responsabile_progetto = true;
    }

    /**
     * Ruolo: Responsabile di Riferimento
     * Può modificare il progetto per approvarlo
     * */
    if ($user->matricola_utente_selezionato == $progetto->matricola_responsabile_riferimento_approvazione) {
        $view = true;
        $edit_responsabile_riferimento = true;
    }

    /**
     * Se il ruolo utente è
     * Responsabile di CdR che ha creato il progetto
     * Responsabile di Progetto
     * Responsabile di Riferimento
     * sarà possibile visualizzare il progetto
     * */
    if (
        $user->hasPrivilege("progetti_admin") ||
        $user->matricola_utente_selezionato == $progetto->matricola_utente_creazione ||
        $user->matricola_utente_selezionato == $progetto->matricola_responsabile_progetto ||
        $user->matricola_utente_selezionato == $progetto->matricola_responsabile_riferimento_approvazione
    ) {       
        if (strlen($source_sql))
            $source_sql .= " UNION ";

        // Recupero delle info dell'utente che ha creato il progretto
        $utente_creazione = Personale::factoryFromMatricola($progetto->matricola_utente_creazione);

        // Recupero delle info dell'utente che è Responsabile di Progetto
        $responsabile_progetto = Personale::factoryFromMatricola($progetto->matricola_responsabile_progetto);

        /**
         * Recupero delle info dell'utente che è Responsabile di Riferimento
         * factoryFromMatricola può sollevare un'eccezione: sfruttando l'eccezione si procede viualizzando 'Da definire'
         * */
        try {
            $responsabile_riferimento = Personale::factoryFromMatricola($progetto->matricola_responsabile_riferimento_approvazione);

            $anagrafe_responsabile_riferimento = $responsabile_riferimento->cognome ." ".
                $responsabile_riferimento->nome." (matr. ".$progetto->matricola_responsabile_riferimento_approvazione.")";
        }
        catch (Exception $exc_responsabile_riferimento) {
            $anagrafe_responsabile_riferimento = "Da definire";
        }

        // Recupero delle informazioni sul tipo di progetto (P1, P2, ecc...), se presenti
        $tipo_progetto = new ProgettiTipoProgetto($progetto->id_tipo_progetto);
        $anagrafe_tipo_progetto = 'Da definire';
        if ($tipo_progetto->codice) {
            $anagrafe_tipo_progetto = $tipo_progetto->codice." (".$tipo_progetto->descrizione.")";
        }

        $source_sql .= "
            SELECT 
                " . $db->toSql($progetto->id, "Number") . " as ID,
                " . $db->toSql($progetto->numero_progetto, "Number") . " as numero_progetto,
                " . $db->toSql($progetto->titolo_progetto) . " as titolo_progetto,
                " . $db->toSql($progetto->data_creazione) . " as data_creazione,
                " . $db->toSql($anagrafe_tipo_progetto) ." as tipo_progetto,
                " . $db->toSql($utente_creazione->cognome . " " . $utente_creazione->nome . " (matr. " .
                        $progetto->matricola_utente_creazione . ")") ." as matricola_utente_creazione,
                " . $db->toSql($responsabile_progetto->cognome . " " . $responsabile_progetto->nome .
                    " (matr. " . $progetto->matricola_responsabile_progetto . ")") ." as matricola_responsabile_progetto,
                " . $db->toSql($anagrafe_responsabile_riferimento) . " as matricola_responsabile_riferimento_approvazione,
                " . $db->toSql($progetto->codice_cdr_proponente) . " as codice_cdr_proponente,
                " . $db->toSql($progetto->getDescrizioneStato()) . " as stato
        ";
    }
}
if ($view == false) {
    ffErrorHandler::raise("L'utente non ha il permesso di visualizzare il progetto");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "progetti";
$oGrid->title = "Progetti";
$oGrid->resources[] = "progetto";
if (strlen($source_sql) > 0) {
    $oGrid->source_SQL = "
        SELECT *
        FROM (".$source_sql.") AS progetti_progetto                          
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
else {
    $oGrid->source_SQL = "
        SELECT
            '' AS ID,
            '' AS numero_progetto,
            '' AS titolo_progetto,
            '' AS data_creazione,
            '' AS tipo_progetto,
            '' AS matricola_utente_creazione,
            '' AS matricola_responsabile_progetto,
            '' AS matricola_responsabile_riferimento_approvazione,
            '' AS codice_cdr_proponente,
            '' AS stato
        FROM progetti_progetto
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oGrid->order_default = "ID";
$oGrid->record_id = "progetto";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$record_url = FF_SITE_PATH . $path_info . "dettaglio_progetto";
$oGrid->record_url = $record_url;
$oGrid->use_paging = false;

/*
 * Gestione pulsanti
 * Se l'utente non ha privilegio di Responsabile di CdR viene inibito l'inserimento
 * --------------------------------------------
 * MOdifica solamente per
 *  Responsabile di CdR
 *  Responsabile di Progetto
 *  Responsabile di Riferimento
 */
$oGrid->display_delete_bt = false;
if (!$edit_responsabile_cdr) {
    $oGrid->display_new = false;
}
if (!$edit_responsabile_cdr && !$edit_responsabile_progetto && !$edit_responsabile_riferimento) {
    $oGrid->display_edit_url = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "numero_progetto";
$oField->base_type = "Number";
$oField->label = "Numero Progetto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo_progetto";
$oField->base_type = "Text";
$oField->label = "Titolo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_creazione";
$oField->base_type = "Datetime";
$oField->label = "Data creazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo_progetto";
$oField->base_type = "Text";
$oField->label = "Tipo progetto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_utente_creazione";
$oField->base_type = "Text";
$oField->label = "Utente creazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile_progetto";
$oField->base_type = "Text";
$oField->label = "Resp. progetto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile_riferimento_approvazione";
$oField->base_type = "Text";
$oField->label = "Dir. riferimento";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr_proponente";
$oField->base_type = "Text";
$oField->label = "CdR Proponente";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "stato";
$oField->base_type = "Text";
$oField->label = "Stato";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);