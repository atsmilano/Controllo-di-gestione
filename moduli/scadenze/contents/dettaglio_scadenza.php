<?php
use scadenze\Scadenza;
use scadenze\Personale;
use scadenze\AbilitazioneCdr;

$date = new Datetime(date("Y-m-d H:i:s"));

$user = LoggedUser::getInstance();
if ($user->hasPrivilege("scadenze_admin")) {
    $edit = true;    
}
else if ($user->hasPrivilege("scadenze_referente_cdr")) {    
    $edit = false;       
}
else {    
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter visualizzare le scadenze");
}

//recupero della scadenza
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $scadenza = new Scadenza($_REQUEST["keys[ID]"]);        
        //per i referenti viene verificato che la richiesta sia effettuata per un cdr di competenza
        if ($edit == false) {
            $personale = Personale::factoryFromMatricola($user->matricola_utente_selezionato);            
            if (!$personale->isScadenzaCompetenzaInData($scadenza, $date)) {
                ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter visualizzare le scadenze del CdR");
            }        
        }        
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
    
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "scadenza-modify";
$oRecord->title = $scadenza !== null ? "Modifica ": "Nuova "."scadenza";
$oRecord->resources[] = "scadenza";
$oRecord->src_table  = "scadenze_scadenza";
if ($edit == false){
    $oRecord->allow_update = false;
    $oRecord->allow_delete = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields["matricola_inserimento"] = new ffData($user->matricola_utente_collegato, "Text");
$oRecord->insert_additional_fields["datetime_inserimento"] = $date->format("Y-m-d H:i:s");
if ($edit == true) {
    $oRecord->additional_fields["matricola_ultima_modifica"] = new ffData($user->matricola_utente_collegato, "Text");
    $oRecord->additional_fields["datetime_ultima_modifica"] = $date->format("Y-m-d H:i:s");
}

$cdr_multipairs = array();
//cdr per i quali poter creare una scadenza
$abilitazioni_cdr = \CoreHelper::getObjectsInData ("scadenze\AbilitazioneCdr", $date, "data_riferimento_inizio", "data_riferimento_fine");        
$cdr_attivi_alla_data = AnagraficaCdr::getAnagraficaInData($date);

$found = false;
foreach ($abilitazioni_cdr as $abilitazione_cdr) {
    foreach ($cdr_attivi_alla_data as $cdr_attivo) {
        if ($cdr_attivo->codice == $abilitazione_cdr->codice_cdr) {
            $found = $abilitazione_cdr->id;
            break;
        }
    }
    if ($found !== false) {
        $cdr_multipairs[] = array(new ffData($found, "Number"),new ffData($cdr_attivo->getDescrizioneEstesa(), "Text"));
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_abilitazione_cdr";
$oField->base_type = "Number";
$oField->label = "CdR";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_multipairs;
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;    
}
else {
    $oField->required = true;
}
$oRecord->addContent($oField);

//Data scadenza
$oField = ffField::factory($cm->oPage);
$oField->id = "data_scadenza";
$oField->base_type = "Date";
$oField->label = "Data scadenza";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->widget = "datepicker";  
    $oField->required = true;
}
$oRecord->addContent($oField);

//tipologia
$tipologie_multipairs = array();
foreach (\CoreHelper::getObjectsInData ("scadenze\Tipologia", $date, "data_riferimento_inizio", "data_riferimento_fine") AS $tipologie) {
    $tipologie_multipairs[] = array(
        new ffData($tipologie->id, "Number"),
        new ffData($tipologie->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipologia";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipologie_multipairs;
$oField->label = "Tipologia";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->required = true;
}
$oRecord->addContent($oField);

//Numero di protocollo
$oField = ffField::factory($cm->oPage);
$oField->id = "protocollo";
$oField->base_type = "Text";
$oField->label = "N° protocollo";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//Oggetto
$oField = ffField::factory($cm->oPage);
$oField->id = "oggetto";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Oggetto";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->required = true;
}
$oRecord->addContent($oField);

//Note
$oField = ffField::factory($cm->oPage);
$oField->id = "note";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Note";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_evasione";
$oField->base_type = "Date";
$oField->label = "Data evasione";
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->widget = "datepicker";  
}
$oRecord->addContent($oField);

//creazione fieldset notifiche mail
$oRecord->addContent(null, true, "notifiche");
$oRecord->groups["notifiche"]["title"] = "Notifiche Mail";

$oField = ffField::factory($cm->oPage);
$oField->id = "giorni_promemoria_scadenza";
$oField->base_type = "Number";
$oField->label = "Giorni promemoria scadenza";
$oField->default_value = new ffData(SCADENZE_GIORNI_PROMEMORIA_DEFAULT, "Number");
if ($edit == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "notifiche");

if ($edit == true) {
    if ($scadenza == null) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "invio_mail_inserimento";
        $oField->base_type = "Number";
        $oField->label = "Invio mail inserimento";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData(1, "Number");
        $oField->store_in_db = false;
        $oRecord->addContent($oField, "notifiche");
    } else {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "mail_promemoria_inviata";
        $oField->base_type = "Number";
        $oField->label = "Mail di promemoria inviata";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);        
        $oRecord->addContent($oField, "notifiche");
    }
}

$cm->oPage->addContent($oRecord);

$oRecord->addEvent("on_done_action", "sendMail");
function sendMail($oRecord, $frmAction) {    
    //gestione delle azioni sul record
    switch($frmAction)
    {
        case "insert":
            if($oRecord->form_fields["invio_mail_inserimento"]->getValue() == 1) {
                $mail = new MailGraph(SCADENZE_MAIL_INVIO);

                $oggetto = html_entity_decode($oRecord->form_fields["oggetto"]->getValue());
                
                $mail->subject = "Inserimento in scadenzario dell'attività '" . $oggetto . "'";
                
                if (strlen($oRecord->form_fields["protocollo"]->getValue())) {
                    $mail->subject .= "'. Protocollo n.".$oRecord->form_fields["protocollo"]->getValue();
                }
                $mail->message = "Buongiorno,\n"."questo messaggio segnala che è stata inserita nello scadenzario la seguente attività: '"
                        . $oggetto."'.\n"."\n";
                if (strlen($oRecord->form_fields["protocollo"]->getValue())) {
                    $mail->message .= "Il relativo numero di protocollo è "." ".$oRecord->form_fields["protocollo"]->getValue().".\n"."\n";
                }
                $mail->message .= "Il termine è previsto per il giorno "
                        .$oRecord->form_fields["data_scadenza"]->getValue("Date", "ITA").".\n\n"
                        ."Qualora si ravvisassero errori nell'attribuzione della competenza o nell'identificazione del termine del presente adempimento si chiede la cortesia di segnalarlo.\nCordiali saluti.\n\n";
                
                $abilitazione_cdr = new AbilitazioneCdr($oRecord->form_fields["ID_abilitazione_cdr"]->getValue());                
                foreach($abilitazione_cdr->getContattiMail() as $recipient) {
                    $mail->toRecipients[] = $recipient->mail;
                }        
                $mail->send();                
            }
        break;  
    }        
}