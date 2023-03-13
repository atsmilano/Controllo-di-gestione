<?php
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//recupero della richiesta
if (isset($_REQUEST["keys[ID_richiesta]"])) {
    try {
        $richiesta = new FabbisognoFormazione\Richiesta($_REQUEST["keys[ID_richiesta]"]);
        if ($richiesta->id_anno_budget !== $anno->id) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri.");
        }
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

//******************************************************************************
//privilegi utente
$privilegi_utente["view_richiesta"] = false;
$privilegi_utente["edit_richiesta"] = false;
$privilegi_utente["edit_segreteria_organizzativa"] = false;
$privilegi_utente["edit_resp_scientifico"] = false;
$privilegi_utente["chiusura"] = false;
$privilegi_utente["edit_data_chiusura"] = false;
$privilegi_utente["delete_richiesta"] = false;

//verifica privilegi utente
$personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
$cdr_competenza_multipairs = array();

if ($user->hasPrivilege("fabbisogno_admin") || $user->hasPrivilege("fabbisogno_operatore_formazione")) {
    $privilegi_utente["edit_richiesta"] = true;
    $privilegi_utente["edit_segreteria_organizzativa"] = true;
    $privilegi_utente["edit_resp_scientifico"] = true;
    $privilegi_utente["chiusura"] = true;
    $privilegi_utente["delete_richiesta"] = true;
    $privilegi_utente["edit_data_chiusura"] = true;

    //cdr aziendali per i quali poter creare una scheda
    foreach (AnagraficaCdr::getAnagraficaInData($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
    }
}
else if($personale->isReferenteCdrInData($date)) {
    //referente cdr
    //verifica su cdr di competenza in data
    $found = false;
    foreach ($personale->getCdrReferenzaAnno($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
        if ($richiesta->codice_cdr == $cdr_competenza->codice) {
            $found = true;
            break;
        }
    }
    if ($richiesta !== null) {
        if ($found == true) {
            $privilegi_utente["edit_richiesta"] = true;
            $privilegi_utente["edit_segreteria_organizzativa"] = true;
            $privilegi_utente["edit_resp_scientifico"] = true;
            $privilegi_utente["chiusura"] = true;
        }
    }
    else {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
        $privilegi_utente["edit_resp_scientifico"] = true;
        $privilegi_utente["chiusura"] = true;
    }
}
else if($personale->isResponsabileCdrReferenteInData($date)) {
    //resposnabile cdr referente
    //verifica su cdr di competenza in data
    $found = false;
    foreach ($personale->getCdrResponsbileReferenzaAnno($date) as $cdr_competenza) {
        $cdr_competenza_multipairs[] = array(new ffData($cdr_competenza->codice, "Text"),new ffData($cdr_competenza->codice." - ".$cdr_competenza->descrizione, "Text"));
        if ($richiesta->codice_cdr == $cdr_competenza->codice) {
            $found = true;
        }
    }
    if ($richiesta !== null) {
        if ($found == true) {
            $privilegi_utente["edit_richiesta"] = true;
            $privilegi_utente["edit_segreteria_organizzativa"] = true;
            $privilegi_utente["edit_resp_scientifico"] = true;
            $privilegi_utente["chiusura"] = true;
            $privilegi_utente["delete_richiesta"] = true;
        }
    }
    else {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
        $privilegi_utente["edit_resp_scientifico"] = true;
        $privilegi_utente["chiusura"] = true;
    }
}

if ($privilegi_utente["edit_richiesta"]!==true && $richiesta !== null) {
    //resposnabile scientifico
    if ($richiesta->matricola_responsabile_scientifico == $personale->matricola) {
        $privilegi_utente["edit_richiesta"] = true;
        $privilegi_utente["edit_segreteria_organizzativa"] = true;
    }
    //referente segreteria
    else if ($richiesta->matricola_referente_segreteria == $personale->matricola){
        $privilegi_utente["edit_richiesta"] = true;
    }
    //responsabile cdr
    else {
        foreach ($personale->getCodiciCdrResponsabilitaAnno($anno) as $cdr_resp) {
            if ($richiesta->codice == $cdr_resp->codice) {
                $privilegi_utente["view_richiesta"] = true;
                break;
            }
        }
    }
}
if ($privilegi_utente["edit_richiesta"] == true) {
    $privilegi_utente["view_richiesta"] = true;
}
if ($privilegi_utente["view_richiesta"] == false) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla richiesta di fabbisogno.");
}
//******************************************************************************
$modulo = core\Modulo::getCurrentModule();
$allow_edit = false;
$allow_riapertura = false;
if ($privilegi_utente["edit_richiesta"]) {
    if ($richiesta == null || $richiesta->data_chiusura == null){
        $allow_edit = true;
    }
    else if ($privilegi_utente["edit_data_chiusura"] && $richiesta->data_chiusura !== null) {
        $allow_riapertura = true;
    }
}
if (isset($richiesta)) {
    $cm->oPage->addContent("<a id='fabbisogni_estrazione_link' class='link_estrazione' href='".FF_SITE_PATH . "/area_riservata" . $modulo->site_path."/estrazioni/scheda_fabbisogno.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."ID=".$richiesta->id."' target='_blank'>"
                . "<div id='fabbisogni_estrazione' class='estrazione link_estrazione'>Estrazione scheda .pdf</div></a><br>");
    //estrazione modulo programma consentita solamente se la richiesta è chiusa
    if ($allow_edit == false) {
        if ($richiesta->id_tipologia == 10){
            $cm->oPage->addContent("
                <label>Scelta della tipologia per l'estrazione del modulo:&nbsp;</label>
                <select id='scelta_modulo' name='scelta_modulo'>
                    <option value='1'>Evento residenziale</option>
                    <option value='2'>Formazione sul campo</option>
                    <option value='3'>Formazione a distanza</option>
                </select>
                <script>                    
                    $('#programma_modulo_link').click(function () {
                        selected_value = $('#scelta_modulo :selected').attr('value');
                        url = $('#programma_modulo_link').attr('href');
                        if (url.search('ID_modulo') > 0) {
                            url = url.replace(/(ID_modulo=).*?(&|$)/, '$1' + selected_value + '$2');
                        } else {
                            url = url + '&ID_modulo=' + selected_value;
                        }                         
                        $('#programma_modulo_link').attr('href', url);
                    });
                </script> 
                ");
        }
        $cm->oPage->addContent("<a id='programma_modulo_link' class='link_estrazione' href='".FF_SITE_PATH . "/area_riservata" . $modulo->site_path."/estrazioni/modulo_programma.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."ID=".$richiesta->id."' target='_blank'>"
                    . "<div id='programma_modulo' class='estrazione link_modulo'>Estrazione modulo programma .doc</div></a><br>");
    }        
}
    
$si_no_multipairs = array(
                        array(new ffData(1, "Number"),new ffData("Si", "Text")),
                        array(new ffData(0, "Number"),new ffData("No", "Text")),
                    );

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "dettaglio-richiesta";
$oRecord->title = $richiesta !== null ? "Modifica ": "Nuova "."richiesta fabbisogno formazione";
$oRecord->resources[] = "richiesta";
$oRecord->src_table  = "fabbisogno_richiesta";

if (!$allow_edit && !$allow_riapertura) {
    $oRecord->allow_update = false;
}
if (!$privilegi_utente["delete_richiesta"] || $richiesta == null || $richiesta->data_chiusura !== null) {
    $oRecord->allow_delete = false;    
}
$oRecord->buttons_options["delete"]["display"] = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_richiesta";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");
$oRecord->insert_additional_fields["matricola_creazione"] = new ffData($user->matricola_utente_collegato, "Text");
$oRecord->additional_fields["matricola_ultima_modifica"] = new ffData($user->matricola_utente_collegato, "Text");
$oRecord->additional_fields["datetime_ultima_modifica"] = new ffData(date("Y-m-d H:i:s"));

//multi_pairs dipendenti attivi nell'anno
$dipendenti_multipairs = array();
foreach (Personale::getAll() as $dipendente) {
    //if ($dipendente->isAttivoAnno($anno)) {
        $dipendenti_multipairs[] = array(
            new ffData($dipendente->matricola, "Number"),
            new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
        );
    //}
}

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oField->default_value = new ffData($richiesta->codice_cdr, "Text");
if ($privilegi_utente["edit_resp_scientifico"] && $allow_edit == true) {
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $cdr_competenza_multipairs;
    $oField->required = true;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $cdr_richiesta = AnagraficaCdr::factoryFromCodice($richiesta->codice_cdr, $date);
    $tipo_cdr = new TipoCdr($cdr_richiesta->id_tipo_cdr);
    $oField->default_value = new ffData($cdr_richiesta->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_richiesta->descrizione, "Text");
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_responsabile_scientifico";
$oField->base_type = "Text";
$oField->label = "Responsabile scientifico";
$oField->default_value = new ffData($richiesta->matricola_responsabile_scientifico, "Text");
if ($privilegi_utente["edit_resp_scientifico"] && $allow_edit == true) {
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $dipendenti_multipairs;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    if (strlen($richiesta->matricola_responsabile_scientifico)){
        $responsabile_scientifico = \Personale::factoryFromMatricola($richiesta->matricola_responsabile_scientifico);
        $oField->default_value = new ffData($responsabile_scientifico->cognome." ".$responsabile_scientifico->nome . " (matr. " . $responsabile_scientifico->matricola . ")", "Text");
    }
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_referente_segreteria";
$oField->base_type = "Text";
$oField->label = "Referente segreteria organizzativa";
$oField->default_value = new ffData($richiesta->matricola_referente_segreteria, "Text");
if ($privilegi_utente["edit_segreteria_organizzativa"] && $allow_edit == true) {
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $dipendenti_multipairs;
}
else {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    try {
        $referente_segreteria = \Personale::factoryFromMatricola($richiesta->matricola_referente_segreteria);
        $referente = $referente_segreteria->cognome." ".$referente_segreteria->nome . " (matr. " . $referente_segreteria->matricola . ")";
    } catch (Exception $ex) {
        $referente = "Nessun referente";
    }
    $oField->default_value = new ffData($referente, "Text");
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_segreteria_organizzativa";
$oField->base_type = "Text";
$oField->label = "Mail segreteria organizzativa";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "telefono_segreteria_organizzativa";
$oField->base_type = "Text";
$oField->label = "Telefono segreteria organizzativa";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo";
$oField->base_type = "Text";
$oField->label = "Titolo evento (Il titolo, che sarà riportato nel PF, dovrà restare identico in tutti i successivi documenti)";
$oField->encode_entities = false;
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->required = true;
}
$oRecord->addContent($oField);

//destinatari
$destinatari_multipairs = array();
foreach (\FabbisognoFormazione\Destinatari::getAll() AS $destinatari) {
    $destinatari_multipairs[] = array(
        new ffData($destinatari->id, "Number"),
        new ffData($destinatari->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_destinatari";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $destinatari_multipairs;
$oField->label = "Destinatari (Profili professionali destinatari dell'iniziativa formativa)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->id_destinatari, "Number");
}
$oRecord->addContent($oField);

//aperto a destinatari esterni
$oField = ffField::factory($cm->oPage);
$oField->id = "apertura_destinatari_esterni";
$oField->label = "Apertura a destinatari esterni";
if ($allow_edit == true) {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $si_no_multipairs;
}
else {
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->apertura_destinatari_esterni==1?"Si":"No", "Text");
}
$oRecord->addContent($oField);

//aperto a destinatari esterni
$oField = ffField::factory($cm->oPage);
$oField->id = "quota_iscrizione_destinatari_esterni";
$oField->label = "E' prevista una quota di iscrizione (per i destinatari esterni)";
if ($allow_edit == true) {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $si_no_multipairs;
}
else {
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->quota_iscrizione_destinatari_esterni==1?"Si":"No", "Text");
}
$oRecord->addContent($oField);

//classe priorita
$classi_priorita_multipairs = array();
foreach (\FabbisognoFormazione\ClassePriorita::getAll() AS $classe_priorita) {
    $classi_priorita_multipairs[] = array(
        new ffData($classe_priorita->id, "Number"),
        new ffData($classe_priorita->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_classe_priorita";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $classi_priorita_multipairs;
$oField->label = "Classe di Priorità (Hanno priorità 'alta' gli eventi formativi ex lege e quelli che saranno sicuramente realizzati = da inserire subito nel PF. Hanno priorità 'media e bassa' gli eventi per i quali non esiste certezza di realizzazione e che potranno essere inseriti fra gli 'Extra piano')";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->id_classe_priorita, "Number");
}
$oRecord->addContent($oField);

//formazione obbligatoria
$oField = ffField::factory($cm->oPage);
$oField->id = "formazione_obbligatoria";
$oField->label = "Formazione obbligatoria (ex lege)";
if ($allow_edit == true) {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $si_no_multipairs;
}
else {
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->formazione_obbligatoria==1?"Si":"No", "Text");
}
$oRecord->addContent($oField);

//descrizione evento
$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->encode_entities = false;
$oField->label = "Descrizione evento (Premessa, inquadramento del problema/bisogno da cui ha origine l’iniziativa di formazione e motivazioni alla base della sua realizzazione - elementi che rendono necessario/utile/auspicabile l’evento formativo in questione. Può essere sintetizzata anche la finalità/obiettivo generale del progetto)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//area di riferimento obiettivi regionali
try {
    $obiettivo_riferimento_predefito = new \FabbisognoFormazione\ObiettivoRiferimento($richiesta->id_obiettivo_riferimento);
    $area_riferimento_predefinita = new \FabbisognoFormazione\AreaRiferimento($richiesta->id_area_riferimento);
} catch (Exception $ex) {
    $obiettivo_riferimento_predefito = 0;
    $area_riferimento_predefinita = 0;
}
$area_riferimento_multipairs = array();
foreach (\FabbisognoFormazione\AreaRiferimento::getAll() as $area_riferimento) {
    $area_riferimento_multipairs[] = array(
                            new ffData ($area_riferimento->id, "Number"),
                            new ffData ($area_riferimento->descrizione, "Text")
                            );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_riferimento";
$oField->label = "Area di riferimento Obiettivi Nazionali";
if ($allow_edit == true){
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->widget = "activecomboex";
    $oField->multi_pairs = $area_riferimento_multipairs;
    $oField->actex_child = "ID_obiettivo_riferimento";
}
else {
    $oField->base_type = "Text";
    $oField->display_value = new ffData($area_riferimento_predefinita->descrizione, "Text");
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//obiettivo di riferimento
$obiettivi_riferimento_multipairs = array();
foreach (\FabbisognoFormazione\ObiettivoRiferimento::getAll() as $obiettivo_riferimento){
    $obiettivi_riferimento_multipairs[] = array(
                            new ffData ($obiettivo_riferimento->id_area_riferimento, "Number"),
                            new ffData ($obiettivo_riferimento->id, "Number"),
                            new ffData ($obiettivo_riferimento->codice.". ".$obiettivo_riferimento->descrizione, "Text")
                            );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_riferimento";
$oField->base_type = "Number";
$oField->label = "Obiettivo Nazionale";
if ($allow_edit == true){
    $oField->extended_type = "Selection";
    $oField->widget = "activecomboex";
    $oField->multi_pairs = $obiettivi_riferimento_multipairs;
    $oField->actex_father = "ID_area_riferimento";
}
else {
    $oField->base_type = "Text";
    $oField->display_value = new ffData($obiettivo_riferimento_predefito->codice.". ".$obiettivo_riferimento_predefito->descrizione, "Text");
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oField->multi_select_one_label = "Selezionare un'area di riferimento...";
$oRecord->addContent($oField);

//obiettivi formativi
$oField = ffField::factory($cm->oPage);
$oField->id = "obiettivi_formativi";
$oField->base_type = "Text";
$oField->encode_entities = false;
$oField->extended_type = "Text";
$oField->label = "Obiettivi formativi specifici
(Devono indicare azioni specifiche ed essere:
- pertinenti, conformi allo scopo da raggiugere;
- centrati sul discente ('al termine del percorso il partecipante sarà in grado di...');
- definiti con precisione;
- perseguibili, effettivamente raggiungibili, realistici, non sovradimensionati ma neppure sottodimensionati;
- osservabili - il cui raggiungimento sia verificabile e quindi percepibile e descrivibile al termine dell’azione stessa)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//modalita valutazione apprendimento
$modalita_valutazione_apprendimento_multipairs = array();
foreach (\FabbisognoFormazione\ModalitaValutazioneApprendimento::getAll() AS $modalita_valutazione_apprendimento) {
    $modalita_valutazione_apprendimento_multipairs[] = array(
        new ffData($modalita_valutazione_apprendimento->id, "Number"),
        new ffData($modalita_valutazione_apprendimento->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_modalita_valutazione_apprendimento";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $modalita_valutazione_apprendimento_multipairs;
$oField->label = "Modalità di valutazione apprendimento";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->id_modalita_valutazione_apprendimento, "Number");
}
$oRecord->addContent($oField);

//valutazione ricaduta
$oField = ffField::factory($cm->oPage);
$oField->id = "valutazione_ricaduta";
$oField->label = "Valutazione Ricaduta (Da prevedere per almeno 2 eventi per Dipartimento)";
if ($allow_edit == true) {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $si_no_multipairs;
}
else {
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->valutazione_ricaduta==1?"Si":"No", "Text");
}
$oRecord->addContent($oField);

//trimestre avvio
$trimestre_avvio_multipairs = array();
foreach (\FabbisognoFormazione\TrimestreAvvio::getAll() AS $trimestre_avvio) {
    $trimestre_avvio_multipairs[] = array(
        new ffData($trimestre_avvio->id, "Number"),
        new ffData($trimestre_avvio->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_trimestre_avvio";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $trimestre_avvio_multipairs;
$oField->label = "Trimestre di avvio";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->id_trimestre_avvio, "Number");
}
$oRecord->addContent($oField);

//docenti
$docenti_multipairs = array();
foreach (\FabbisognoFormazione\Docenti::getAll() AS $docente) {
    $docenti_multipairs[] = array(
        new ffData($docente->id, "Number"),
        new ffData($docente->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "docenti";
$oField->data_type = "callback";
$oField->data_source = "preload_docenti_relations";
$oField->base_type = "Text";
$oField->store_in_db = false;
if (count($docenti_multipairs) > 0){
    $oField->label = "Docenti";
    if ($allow_edit == true) {
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $docenti_multipairs;
        $oField->control_type = "input";
        $oField->widget = "checkgroup";
        $oField->grouping_separator = ",";
    }
    else {
        $oField->control_type = "label";
        $docenti_richiesta = "";
        foreach (\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$_REQUEST["keys"]["ID_richiesta"])) as $docente_richiesta){
            $docente = new \FabbisognoFormazione\Docenti($docente_richiesta->id_docenti);
            $docenti_richiesta .= $docente->descrizione."\n";
        }
        $oField->data_type = "";
        $oField->default_value = new ffData($docenti_richiesta, "Text");
    }
}
else {
    $oField->label = "Nessun tipo docenza definito";
    $oField->data_type = "";
    $oField->control_type = "label";
}
$oRecord->addContent($oField);
//precompilazione dei docenti
function preload_docenti_relations($form_fields, $key, $first_access){
    if($first_access) {
        //$docenti_richiesta = "";
        //condizione ridondante (record acceduto solo in modifica) ma mantenuta per robustezza
        if(isset($_REQUEST["keys"]["ID_richiesta"])){
            foreach (\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$_REQUEST["keys"]["ID_richiesta"])) as $docente_richiesta){
                if(strlen($docenti_richiesta)){
                    $docenti_richiesta .= ",";
                }
                $docenti_richiesta .= ($docente_richiesta->id_docenti);
            }
        }
        return new ffdata($docenti_richiesta);
    }
    else{
        return $form_fields[$key]->value;
    }
}

//posti
$oField = ffField::factory($cm->oPage);
$oField->id = "n_posti";
$oField->base_type = "Number";
$oField->label = "Posti a disposizione (N° partecipanti previsti per ciascuna edizione)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//ore
$oField = ffField::factory($cm->oPage);
$oField->id = "n_ore";
$oField->base_type = "Number";
$oField->label = "Ore formative (Per ciascuna edizione)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//edizioni
$oField = ffField::factory($cm->oPage);
$oField->id = "n_edizioni";
$oField->base_type = "Number";
$oField->label = "N° Edizioni (Numero di volte in cui lo stesso evento viene svolto)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//costi edizione
$oField = ffField::factory($cm->oPage);
$oField->id = "costi_edizione";
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oField->label = "Costi per singola edizione (Indicare l’importo riferito alla singola edizione)";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

//accreditamento ecm
$oField = ffField::factory($cm->oPage);
$oField->id = "accreditamento_ecm";
$oField->label = "Accreditamento ECM (Indicare se si intende accreditare ECM)";
if ($allow_edit == true) {
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $si_no_multipairs;
}
else {
    $oField->base_type = "Text";
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->accreditamento_ecm==1?"Si":"No", "Text");
}
$oRecord->addContent($oField);

//tipologia formativa
$tipologie_multipairs = array();
foreach (\FabbisognoFormazione\Tipologia::getAll() AS $tipologia) {
    $tipologie_multipairs[] = array(
        new ffData($tipologia->id, "Number"),
        new ffData($tipologia->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipologia";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipologie_multipairs;
$oField->label = "Tipologia formativa";
if ($allow_edit !== true) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
    $oField->data_type = "";
    $oField->default_value = new ffData($richiesta->id_tipologia, "Number");
}
$oRecord->addContent($oField);
//se sono previsti i privilegi di chiusura
if ($allow_edit && $privilegi_utente["chiusura"] == true){
    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "chiusura";
    $oBt->label = "Chiusura scheda";
    $oBt->action_type = "submit";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit btn-success";    
    $oRecord->addActionButton($oBt);
    if ($cm->isXhr() == true) {        
        $oBt->frmAction = "chiusura";    
    }
    else {
        $oBt->jsaction = "$('#inactive_body').show();$('#conferma_chiusura').show();";
        $cm->oPage->addContent("<div id='inactive_body'></div>
                                                    <div id='conferma_chiusura' class='conferma_azione'>
                                                            <h3>Conferma chiusura</h3>
                                                            <p>Confermando la chiusura, la scheda non sarà più modificabile.</p>
                                                            <a id='conferma_si' class='confirm_link'>Conferma</a>
                                                            <a id='conferma_no' class='confirm_link'>Annulla</a>
                                                    </div>
                                                    <script>
                                                            $('#conferma_si').click(function(){
                                                                    document.getElementById('frmAction').value = 'dettaglio-richiesta_chiusura';
                                                                    document.getElementById('frmMain').submit();
                                                            });
                                                            $('#conferma_no').click(function(){
                                                                    $('#inactive_body').hide();
                                                                    $('#conferma_chiusura').hide();
                                                                    $('#richiesta_action_button_').prop('disabled', false);
                                                                    $('#richiesta_action_button_').prop('style', false);
                                                                    $('#richiesta_action_button_').val('Chiusura scheda');
                                                            });
                                                    </script>
                                                    ");
    }
}

if ($richiesta !== null && $richiesta->data_chiusura !== null){
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura richiesta";
    if($allow_riapertura == true) {
        $oField->widget = "datepicker";
        $oField->addValidator("date");
    }
    else {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField);
}

$oRecord->addEvent("on_do_action", "chiusuraRichiesta");
$oRecord->addEvent("on_done_action", "docentiUpdate");

$cm->oPage->addContent($oRecord);

function chiusuraRichiesta($oRecord, $frmAction) {    
    //TODO verifica privilegiutenti su varie operazioni
    //gestione delle azioni sul record
    if ($frmAction == "chiusura") {                
        $richiesta = new \FabbisognoFormazione\Richiesta($oRecord->key_fields["ID_richiesta"]->value->getValue());
        //viene verificato che il record sia salvato in modifica o inserimento e recuperato eventualmente l'oggetto
        $id = $oRecord->key_fields["ID_richiesta"]->getValue();
        $messaggio_errore = "";
        if (strlen($id)){
            try {
                $richiesta = new FabbisognoFormazione\Richiesta($id);
            } catch (Exception $ex) {
                $messaggio_errore = $ex->getMessage();
            }
            $fields_to_update = array();
        }
        else {
            $richiesta = new FabbisognoFormazione\Richiesta();

            $richiesta->id_anno_budget = $oRecord->insert_additional_fields["ID_anno_budget"]->getValue();
            $richiesta->matricola_creazione = $oRecord->insert_additional_fields["matricola_creazione"]->getValue();
            $fields_to_update = array("ID_anno_budget","matricola_creazione");
        }
        $richiesta->codice_cdr_creazione = $oRecord->additional_fields["matricola_ultima_modifica"]->getValue();
        $richiesta->data_creazione = $oRecord->additional_fields["datetime_ultima_modifica"]->getValue();
        $fields_to_update[] = "matricola_ultima_modifica";
        $fields_to_update[] = "datetime_ultima_modifica";
        //campi_richiesta
        $validation_errors = "";
        foreach ($oRecord->form_fields as $field) {                  
            if ($field->extended_type == "Selection"){
                if (!strlen($field->value->getValue("Text"))) {   
                    $validation_errors .= "'".$field->label."' obbligatorio.<br>";
                }                
            }            
            else if ($field->id !== "costi_edizione" && $field->base_type == "Number" && !(int)$field->value->getValue()>0){
                $validation_errors .= "'".$field->label."' obbligatorio.<br>";
            }
            else if (!strlen($field->value->getValue())) {
                $validation_errors .= "'".$field->label."' obbligatorio.<br>";
            }
            if ($field->id !== "docenti") {
                if ($field->id == "costi_edizione") {
                    $richiesta->{strtolower($field->id)} = $field->value->getValue("Number");                    
                }
                else {
                    $richiesta->{strtolower($field->id)} = $field->value->getValue();                    
                }
                $fields_to_update[] = $field->id;
            }            
        }
        if (strlen($validation_errors)) {
            CoreHelper::setError($oRecord, $validation_errors);               
            return true;
        }
        $richiesta->data_chiusura = date("Y-m-d H:i:s");
        $fields_to_update[] = "data_chiusura";
        try{
            $id_richiesta = $richiesta->save($fields_to_update);
            $oRecord->key_fields["ID_richiesta"]->value->setValue($id_richiesta);
            mod_notifier_add_message_to_queue("Chiusura richiesta effettuata con successo", MOD_NOTIFIER_SUCCESS);
        } catch (Exception $ex) {
            mod_notifier_add_message_to_queue("Errore durante la chiusura della richiesta", MOD_NOTIFIER_ERROR);
        }
    }
}

function docentiUpdate($oRecord, $frmAction, $id_richiesta) {
    //TODO verifica privilegiutenti su varie operazioni
    if($oRecord->form_fields["docenti"]->getValue() !== "") {
        $docenti_richiesta = explode(",", $oRecord->form_fields["docenti"]->getValue());
    }
    else {
        $docenti_richiesta = array();
    }
    //gestione delle azioni sul record
    switch($frmAction)
    {
        case "insert":
            //vengono inserite tute le relazioni create
            foreach ($docenti_richiesta as $id_docenti) {
                $docente_richiesta = new \FabbisognoFormazione\DocentiRichiesta();
                $docente_richiesta->id_richiesta = $oRecord->key_fields["ID_richiesta"]->value->getValue();
                $docente_richiesta->id_docenti= $id_docenti;
                $docente_richiesta->save(array("ID_richiesta", "ID_docenti"));
            }
        break;
        case "update":
        case "chiusura":
            $richiesta = new \FabbisognoFormazione\Richiesta($oRecord->key_fields["ID_richiesta"]->value->getValue());
            if ($oRecord->form_fields["docenti"]->control_type !== "label") {
                //************************************
                //eliminazione delle relazioni non mantenute
                foreach(\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$_REQUEST["keys"]["ID_richiesta"])) as $docente_richiesta) {
                    $mantenuto = false;
                    foreach ($docenti_richiesta as $id_docenti) {
                        if ($docente_richiesta->id_docenti == $id_docenti) {
                            $mantenuto = true;
                            break;
                        }
                    }
                    if ($mantenuto == false) {
                        $docente_richiesta->delete();
                    }
                }
                //************************************
                //creazione o mantenimento delle relazioni selezionate in caso non esistano già
                foreach ($docenti_richiesta as $id_docenti) {
                    if(\FabbisognoFormazione\DocentiRichiesta::getByFields(array("ID_richiesta"=>$richiesta->id, "ID_docenti"=>$id_docenti))==null){
                        $docente_richiesta = new \FabbisognoFormazione\DocentiRichiesta();
                        $docente_richiesta->id_richiesta = $richiesta->id;
                        $docente_richiesta->id_docenti = $id_docenti;
                        $docente_richiesta->save(array("ID_richiesta", "ID_docenti"));
                    }
                }
            }
        break;
        case "delete":
        case "confirmdelete":
            //propagazione sulla relazione
            foreach(\FabbisognoFormazione\DocentiRichiesta::getAll(array("ID_richiesta"=>$oRecord->key_fields["ID_richiesta"]->value->getValue())) as $docente_richiesta) {
                $docente_richiesta->delete();
            }
        break;
    }
        
    if ($frmAction == "chiusura"){        
        $cm = cm::getInstance();
        if ($cm->isXhr() == true) {
            ffRedirect($cm->path_info."?keys[ID_richiesta]=".$richiesta->id."&".$cm->query_string);
        }else {
            if (isset($_GET["ret_url"])){
                $ret_url = $_GET["ret_url"];
            }
            else {
                $ret_url = FF_SITE_PATH;
            }
            ffRedirect($ret_url);
        }
    }           
}