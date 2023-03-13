<?php
       $cm->oPage->widgetLoad("dialog");
$tmp2 = $cm->oPage->widgets["dialog"]->process(
    "view_storico_dialog" // id del dialog
    , array( // proprietà  del dialog
        "name" => "view_storico_dialog"
        , "title" => "Storico parametri"
        , "url" => ""
        , "callback" => "",
    )
    , $cm->oPage // oggetto pagina associato
);

$user = LoggedUser::getInstance();
//recupero parametri
$anno_global = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//******************************************************************************
//verifiche sui parametri
$rendicontazione = null;
//verifica sull'eventuale parametro di rendicontazione
if (isset($_REQUEST["keys[ID_rendicontazione]"]) && strlen($_REQUEST["keys[ID_rendicontazione]"])) {
    $rendicontazione = new ObiettiviRendicontazione($_REQUEST["keys[ID_rendicontazione]"]);
    if (isset($_REQUEST["keys[ID_periodo]"]) && strlen($_REQUEST["keys[ID_periodo]"])) {
        if ($_REQUEST["keys[ID_periodo]"] != $rendicontazione->id_periodo_rendicontazione) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_rendicontazione e ID_periodo non coerenti.");
        }
    } else {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_periodo.");
    }
    $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($rendicontazione->id_periodo_rendicontazione);
    if ($periodo_rendicontazione->id_anno_budget !== $anno_global->id) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: anno selezionato e periodo non corrispondono.");
    }
    $obiettivo_cdr = new ObiettiviObiettivoCdr($rendicontazione->id_obiettivo_cdr);   
} else if (isset($_REQUEST["keys[ID_periodo]"]) && strlen($_REQUEST["keys[ID_periodo]"])) {
    $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($_REQUEST["keys[ID_periodo]"]);

    if ($periodo_rendicontazione->id_anno_budget !== $anno_global->id) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: anno selezionato e periodo non corrispondono.");
    }
    if (isset($_REQUEST["keys[ID_obiettivo_cdr]"]) && strlen($_REQUEST["keys[ID_obiettivo_cdr]"])) {
        $obiettivo_cdr = new ObiettiviObiettivoCdr($_REQUEST["keys[ID_obiettivo_cdr]"]);
    } else {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo_cdr.");
    }
} else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_periodo.");
}

$obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
if ($obiettivo->data_eliminazione !== null || $obiettivo_cdr->data_eliminazione !== null) {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: elemento eliminato.");
}

//******************************************************************************
//una volta effettuati i controlli sui parametri vengono definiti i privilegi degli utenti
//viene selezionato il tipo di piano (per obiettivi aziendali quell oa priortiÃ  massima)
if ($obiettivo_cdr->id_tipo_piano_cdr != null) {
    $tipo_piano = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
} else {
    $tipo_piano = TipoPianoCdr::getPrioritaMassima();
}

//viene recuperato il cdr
$resp_cdr_selezionato = false;
$resp_padre_cdr_selezionato = false;
$resp_padre_ramo_cdr_selezionato = false;
$obiettivo_cdr_padre = null;
$desc_referente = "";
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
$cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
//recupero dei privilegi dell'utente sul cdr
$personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
foreach ($cdr->getPrivileges($personale, $date) as $privilege) {
    //privilegi per il responsabile del cdr
    //referente / coreferente
    if ($privilege == "resp_cdr_selezionato") {
        $resp_cdr_selezionato = true;
    }
    //privilegi per il responsabile del cdr padre gerarchico
    //padre_referente            
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

//viene verificato il periodo attivo o meno della rendicontazione
if ($periodo_rendicontazione->data_termine_responsabile == null || (strtotime(date("Y-m-d")) <= strtotime($periodo_rendicontazione->data_termine_responsabile))) {
    $periodo_attivo = true;
} else {
    $periodo_attivo = false;
}

//***********************
$user_privileges = array(
    "view" => false,
    "edit_responsabile" => false,
    "edit_nucleo" => false,
    "edit_coreferenza" => false
);       
$user_privileges["view"] = true;

if ($resp_cdr_selezionato) {    
    //if ($obiettivo_cdr->isChiuso()) {       
    $user_privileges["view"] = true;
    if (!$obiettivo_cdr->isCoreferenza() && $periodo_attivo == true) {
        $user_privileges["edit_responsabile"] = true;
    } else if ($obiettivo_cdr->isCoreferenza()) {
        $obiettivo_cdr_padre = $obiettivo_cdr->getObiettivoCdrPadre();
        $cdr_padre_obiettivo = Cdr::factoryFromCodice($obiettivo_cdr_padre->codice_cdr, $piano_cdr);
        $desc_referente = " (referente obiettivo trasversale)";
    }
    //}                                                                    
}
if ($user->hasPrivilege("nucleo_di_valutazione")) {
    $user_privileges["edit_nucleo"] = true;
    if($obiettivo_cdr->isCoreferenza()) {
        $user_privileges["edit_coreferenza"] = true;
    }
}

if (!$user_privileges["view"]) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina dell'obiettivo.");
}
//se è stato definito il parametro "no_action" si garantisce la sola visualizzazione
if (isset($_REQUEST["no_actions"]) && $_REQUEST["no_actions"] == 1) {
    $user_privileges["edit_responsabile"] = false;
    $user_privileges["edit_nucleo"] = false;
    $user_privileges["edit_coreferenza"] = false;
    $show_rendicontazione_figli = false;
}
else {
    $show_rendicontazione_figli = true;
}
//******************************************************************************
//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "rendicontazione-modify";
$tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
$oRecord->title = "Rendicontazione obiettivo per il CDR '" . $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione . "'";
$oRecord->resources[] = "rendicontazione";
$oRecord->src_table = "obiettivi_rendicontazione";
//vengono sempre inibiti eliminazione ed insarimento
$oRecord->allow_delete = false;

$oRecord->addEvent("on_done_action", "saveRelations");

//******************************************************************************
//creazione fieldset per il riepilogo dell'obiettivo
$obiettivo_aziendale_desc = "";
if ($obiettivo_cdr->isObiettivoCdrAziendale()) {
    $obiettivo_aziendale_desc = "aziendale";
}
$oRecord->addContent(null, true, "riepilogo_obiettivo");
$oRecord->groups["riepilogo_obiettivo"]["title"] = "Riepilogo obiettivo " . $obiettivo_aziendale_desc . " '" . $obiettivo->codice . "'";

//riepilogo delle informazioni dell'obiettivo
$oRecord->addContent($obiettivo->showHtmlInfo(), "riepilogo_obiettivo");

//******************************************************************************
//creazione fieldset per il riepilogo dell'assegnazione obiettivo-cdr
$oRecord->addContent(null, true, "riepilogo_obiettivo_cdr");
$oRecord->groups["riepilogo_obiettivo_cdr"]["title"] = "Assegnazione Obiettivo";

//riepilogo delle informazioni dell'obiettivo
$oRecord->addContent($obiettivo_cdr->showHtmlInfo($date), "riepilogo_obiettivo_cdr");

//******************************************************************************
//estrazione dell'obiettivo aziendale per verificare se visualizzare la rendicontazione del cdr valutato aziendalmente
$obiettivo_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale();
$rendicontazione_aziendale_obiettivo = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo_rendicontazione);

$anagrafica_cdr_aziendale = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_aziendale->codice_cdr, $date);

//nel caso in cui venga trovata una rendicontazione aziendale differente da quella corrente
if ($obiettivo_cdr->id !== $obiettivo_cdr_aziendale->id) {
    $oRecord->addContent(null, true, "riepilogo_rendicontazione_aziendale");
    $tipo_cdr = new TipoCdr($anagrafica_cdr_aziendale->id_tipo_cdr);
    $oRecord->groups["riepilogo_rendicontazione_aziendale"]["title"] = "Rendicontazione aziendale dell'obiettivo " . $anagrafica_cdr_aziendale->codice . " - " . $tipo_cdr->abbreviazione . " " . $anagrafica_cdr_aziendale->descrizione . $desc_referente;

    if ($rendicontazione_aziendale_obiettivo !== null) {
        $oRecord->addContent($rendicontazione_aziendale_obiettivo->showHtmlInfo(), "riepilogo_rendicontazione_aziendale");
    } else {
        $oRecord->addContent("<div class='form-group clearfix padding'>
                                <label>Rendicontazione aziendale non compilata</label></div>", "riepilogo_rendicontazione_aziendale");
    }
}

//la modifica dell'obiettivo sarÃ  possibile solamente per gli utenti con privilegi di modifica sull'obiettivo o sulle azioni o sui pareri
if ($user_privileges["edit_responsabile"] || $user_privileges["edit_coreferenza"]) {
    $oRecord->allow_insert = true;
    $oRecord->allow_update = true;
}
else if ($user_privileges["edit_nucleo"]) {
    $oRecord->allow_insert = false;
    $oRecord->allow_update = true;
}
else {
    $oRecord->allow_insert = false;
    $oRecord->allow_update = false;
}
//istanza della struttura dati per gli allegati (global per utilizzo in showhtmlinfo)
if (!isset($cm->oPage->globals["allegati_permissions"]["value"])) {
    $allegati_permissions = array(
                            'user_id' => $user->matricola_utente_selezionato,
                            'allegati_permissions' => array(
                                'canDownload' => array(),
                                'canDelete' => array()
                            )
                        ); 
    $cm->oPage->register_globals("allegati_permissions", $allegati_permissions);
}

//*****************************************************************************
//creazione fieldset referenti
$oRecord->addContent(null, true, "referente");
$oRecord->groups["referente"]["title"] = "Rendicontazione";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_rendicontazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField, "referente");
if (!$obiettivo_cdr->isCoreferenza()) {   
    $oField = ffField::factory($cm->oPage);
    $oField->id = "azioni";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Azioni compiute per il raggiungimento dell'obiettivo";
    if (!$user_privileges["edit_responsabile"]) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    } else {
        $oField->required = true;
    }
    $oField->properties["rows"] = "6";
    $oRecord->addContent($oField, "referente");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "provvedimenti";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Provvedimenti (delibere e determinazioni)";
    if (!$user_privileges["edit_responsabile"]) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    } else {
        $oField->required = true;
    }
    $oField->properties["rows"] = "6";
    $oRecord->addContent($oField, "referente");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "criticita";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Criticità  riscontrate e interventi messi in atto per il loro superamento";
    if (!$user_privileges["edit_responsabile"]) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    } else {
        $oField->required = true;
    }
    $oField->properties["rows"] = "6";
    $oRecord->addContent($oField, "referente");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "misurazione_indicatori";
    $oField->base_type = "Text";
    $oField->label = "Misurazione del grado di raggiungimento coerentemente con quanto specificato negli indicatori";
    $oField->extended_type = "Text";
    if (!$user_privileges["edit_responsabile"]) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    } else {
        $oField->required = true;
    }
    $oField->properties["rows"] = "6";
    $oRecord->addContent($oField, "referente");
}

//indicatori********************************************************************
//viene aggiunta la dichiarazione della funzione per il calcolo del risultato dell'indicatore
//url della pagina per il calcolo del risultato dell'indicatore
$indicatori_associati = $obiettivo->getIndicatoriAssociati();

if (count($indicatori_associati)) {
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    if ($user_privileges["edit_responsabile"]) {
        $url_calcolo_risultati = FF_SITE_PATH . $path_info . "indicatori/calcolo_risultato?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
        $url_calcolo_raggiungimento_indicatore = FF_SITE_PATH . $path_info . "indicatori/calcolo_raggiungimento_indicatore?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
        $url_calcolo_raggiungimento_obiettivo = FF_SITE_PATH . $path_info . "indicatori/calcolo_raggiungimento_obiettivo?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);

        $oRecord->addContent("
                            <script>
                                function calcoliIndicatore(id_obiettivo_indicatore) {
                                    $('#" . $oRecord->id . "_risultato_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.readonly').text('Calcolo...');
                                    $('#" . $oRecord->id . "_raggiungimento_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.raggiungimento_indicatore').text('Calcolo...');
                                    $('#" . $oRecord->id . "_perc_raggiungimento').siblings('.form-control.readonly').text('Calcolo...');    

                                    var parametri = [];
                                    $('input.parametro_'+id_obiettivo_indicatore).each(function(){
                                        id_elemento = $(this).attr('id');  
                                        id_parametro_indicatore = id_elemento.split('_')[3];                                
                                        parametri.push({id_parametro_indicatore:id_parametro_indicatore, valore:$(this).val()});		
                                    });

                                    var data = {
                                        id_obiettivo_indicatore: id_obiettivo_indicatore,
                                        parametri: parametri,
                                    };
                                    var request = $.ajax({
                                                    url: '" . $url_calcolo_risultati . "',
                                                    type: 'GET',
                                                    data: data,									
                                                    cache: false,
                                                    contentType: false,
                                    });
                                    request.done(function(data) {
                                        response = JSON.parse(data);
                                        if(response.esito == 'success') {
                                            $('#" . $oRecord->id . "_risultato_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.readonly').text(response.risultato);                                   

                                            var risultato_data = {
                                                id_obiettivo_indicatore: id_obiettivo_indicatore,
                                                risultato: response.risultato,
                                                cdr_associato: '" . $cdr->id . "',
                                            };

                                            var risultato_request = $.ajax({
                                                                url: '" . $url_calcolo_raggiungimento_indicatore . "',
                                                                type: 'GET',
                                                                data: risultato_data,									
                                                                cache: false,
                                                                contentType: false,
                                            });
                                            risultato_request.done(function(data) {
                                                risultato_response = JSON.parse(data);
                                                if(risultato_response.esito == 'success') {
                                                    $('#" . $oRecord->id . "_raggiungimento_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.raggiungimento_indicatore').text(risultato_response.risultato+'%');

                                                    var raggiungimenti_indicatori = [];
                                                    $('span.raggiungimento_indicatore').each(function(){ 
                                                        id_input = $(this).siblings('input').attr('id');                                                     
                                                        id_obiettivo_indicatore_ragg = id_input.split('_')[3]; 
                                                        raggiungimenti_indicatori.push({id_obiettivo_indicatore:id_obiettivo_indicatore_ragg,valore:$(this).text().replace('%','')});
                                                    });

                                                    var ragg_obiettivo_data = {
                                                        raggiungimenti_indicatori: raggiungimenti_indicatori,
                                                    };

                                                    var ragg_obiettivo_request = $.ajax({
                                                                url: '" . $url_calcolo_raggiungimento_obiettivo . "',
                                                                type: 'GET',
                                                                data: ragg_obiettivo_data,									
                                                                cache: false,
                                                                contentType: false,
                                                    });

                                                    ragg_obiettivo_request.done(function(data) {
                                                        ragg_obiettivo_response = JSON.parse(data);
                                                        if(ragg_obiettivo_response.esito == 'success') {
                                                            $('#" . $oRecord->id . "_perc_raggiungimento').siblings('.form-control.readonly').text(ragg_obiettivo_response.risultato);
                                                            $('#" . $oRecord->id . "_perc_raggiungimento').val(ragg_obiettivo_response.risultato);
                                                        }
                                                        else {                                                        
                                                            $('#" . $oRecord->id . "_perc_raggiungimento').siblings('.form-control.readonly').text(response.messaggio);
                                                        }
                                                    });
                                                }
                                                else {                                                
                                                    $('#" . $oRecord->id . "_raggiungimento_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.raggiungimento_indicatore').text(response.messaggio);
                                                    $('#" . $oRecord->id . "_perc_raggiungimento').siblings('.form-control.readonly').text(response.messaggio);
                                                }
                                            });
                                            risultato_request.fail(function() {
                                            });
                                            risultato_request.always(function() {
                                            });                                 
                                        }
                                        else {
                                            $('#" . $oRecord->id . "_risultato_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.readonly').text(response.messaggio);
                                            $('#" . $oRecord->id . "_raggiungimento_indicatore_'+id_obiettivo_indicatore).siblings('.form-control.raggiungimento_indicatore').text(response.messaggio);
                                            $('#" . $oRecord->id . "_perc_raggiungimento').siblings('.form-control.readonly').text(response.messaggio);
                                        }
                                        $('.btn.btn-success').show();                                  
                                    });    
                                    request.fail(function() {
                                    });
                                    request.always(function() {
                                    });
                                }                                                
                            </script>
                            ");
    }
    //i javascript per il calcolo del risultato vengono raccolti e visualizzati dopo il record
    $script_calcolo_risultato = "";
    $raggiungimento_indicatori = array();
    foreach ($indicatori_associati as $indicatore) {
        $oRecord->addContent("<div class='fieldset_indicatore'>", $indicatore->nome);

        $oRecord->addContent(null, true, $indicatore->nome);
        $oRecord->groups[$indicatore->nome]["title"] = "Indicatore: '" . $indicatore->nome . "'";

        $oRecord->addContent("<div class='left_indicatore'>", $indicatore->nome);

        //informazioni indicatore
        $oField = ffField::factory($cm->oPage);
        $oField->id = "descrizione_indicatore_" . $indicatore->obiettivo_indicatore->id;
        $oField->base_type = "Text";
        $oField->extended_type = "Text";
        $oField->data_type = "";
        $oField->label = "Descrizione indicatore";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($indicatore->descrizione, "Text");        
        $oRecord->addContent($oField, $indicatore->nome);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "istruzioni_indicatore_" . $indicatore->obiettivo_indicatore->id;
        $oField->base_type = "Text";
        $oField->extended_type = "Text";
        $oField->data_type = "";
        $oField->label = "Istruzioni indicatore";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->default_value = new ffData($indicatore->istruzioni, "Text");        
        $oRecord->addContent($oField, $indicatore->nome);

        $oRecord->addContent("<div class='parametri_indicatore'>", $indicatore->nome);

        $parametri_calcolo = array();
        foreach ($indicatore->getParametri() as $parametro) {
            $oRecord->addContent("<div class='internal_left_indicatore'>", $indicatore->nome);
            $oField = ffField::factory($cm->oPage);
            //tipo di campo in base alla tipologia del parametro
            $tipo_parametro = new IndicatoriTipoParametro($parametro->id_tipo_parametro);
            $oField = $tipo_parametro->configureField($oField);
            $oField->data_type = "";
            //informazioni del field indipendenti dalla tipologia
            $oField->id = "parametro_" . $indicatore->obiettivo_indicatore->id . "_" . $parametro->parametro_indicatore->id;

            //in caso di coreferenza i valori da proporre sono quelli del referente
            //la seconda condizione ($obiettivo_cdr_padre!==null) viene introdotta per gestire l'arrivo dal bersaglio (in cui viene passato l'id della rendicontazione del padre)
            //e quindi l'id_obiettivo_cdr che ne viene ricavato è quello aziendale
            //EDIT vengono sempre recuperati i parametri per il cdr
            /*if ($obiettivo_cdr->isCoreferenza() && $obiettivo_cdr_padre !== null) {
                $obiettivo_cdr_valore_parametro = $obiettivo_cdr_padre;
            } else {*/
                $obiettivo_cdr_valore_parametro = $obiettivo_cdr;
            //}
            //viene recuperato il valore del parametro
            $valore_parametro = $parametro->parametro_indicatore->getValoreParametroIndicatoreRendicontazione($periodo_rendicontazione, $obiettivo_cdr_valore_parametro);
            $valore_parametro_rilevato = "";
            if ($valore_parametro !== null) {
                if ($valore_parametro["rilevato"] !== null) {
                    $valore_parametro_rilevato = " (rilevato: " . $valore_parametro["rilevato"] . ")";
                }
                $oField->default_value = new ffData($valore_parametro["utilizzato"], $oField->base_type);
                if ($valore_parametro["modificabile"] !== true) {
                    $oField->control_type = "label";
                    $oField->store_in_db = false;
                }
            }

            if (!$user_privileges["edit_responsabile"]) {
                $oField->control_type = "label";
                $oField->store_in_db = false;
            }
            $parametri_calcolo[] = array("id_parametro_indicatore" => $parametro->parametro_indicatore->id, "valore" => $valore_parametro["utilizzato"]);
            //classi per la gestione del campo
            $oField->class = "field_parametro";
            //viene assegnata in maniera uniforme a tutti i campi la classe all'input tramite jquery (il framework assegna la classe a <span> se control_type = "label")            
            $script_calcolo_risultato .= "
                $('#" . $oRecord->id . "_parametro_" . $indicatore->obiettivo_indicatore->id . "_" . $parametro->parametro_indicatore->id . "').addClass('parametro_" . $indicatore->obiettivo_indicatore->id . "')
            ";

            $oField->label = $parametro->nome . $valore_parametro_rilevato;

            //script per il ricalcolo del risultato all'aggiornamento del parametro
            $script_calcolo_risultato .= "
                $('#" . $oRecord->id . "_parametro_" . $indicatore->obiettivo_indicatore->id . "_" . $parametro->parametro_indicatore->id . "').change(function() {                                       
                    $('.btn.btn-success').hide();
                    calcoliIndicatore(" . $indicatore->obiettivo_indicatore->id . ");                                                    
                }); 
            ";

            $oField->store_in_db = false;
            $oRecord->addContent($oField, $indicatore->nome);

            if ($user->hasPrivilege("indicatori_edit")) {
                // L'utente amministratore vede il link per consultare lo storico parametro
                $oRecord->addContent('<span class="cogs-storico">
                    <a href="#"
                        onclick="gotoStoricoParametri('.$parametro->parametro_indicatore->id.', '
                            . ''.$periodo_rendicontazione->id.', \'\', \''.$cdr->codice.'\')"
                    >
                        <i class="fa fa-cogs fa-3x"></i>
                    </a>
                    </span>', $indicatore->nome
                );

                //link storico
                $oRecord->addContent(
                    $parametro->jsGotoStoricoParametri(
                        (FF_SITE_PATH . $path_info . "indicatori/gestione_storico/storico?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)), 
                        "view_storico_dialog"
                    ), $indicatore->nome
                );
            }
            $oRecord->addContent("</div>", $indicatore->nome);
        }
        $oRecord->addContent("</div>", $indicatore->nome);
        $oRecord->addContent("</div>", $indicatore->nome);

        $oRecord->addContent("<div class='valori_calcolati_indicatore'>", $indicatore->nome);

        //recupero del valore target        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "risultato_indicatore_" . $indicatore->obiettivo_indicatore->id;
        $oField->base_type = "Text";
        $oField->label = "Risultato";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        //calcolo risultato indicatori a caricamento pagina
        $risultato_calcolo_indicatore = $indicatore->calcoloRisultatoIndicatore($parametri_calcolo)["risultato"];
        $oField->default_value = new ffData($risultato_calcolo_indicatore !== null ? $risultato_calcolo_indicatore : "Non calcolabile", "Text");
        $oField->data_type = "";
        $oRecord->addContent($oField, $indicatore->nome);        
        if ($obiettivo_cdr->isCoreferenza()) {
            $cdr_valore_target = $cdr_padre_obiettivo;
        } else {
            $cdr_valore_target = $cdr;
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "valore_target_indicatore_" . $indicatore->obiettivo_indicatore->id;
        $oField->base_type = "Text";
        $oField->label = "Valore target";
        $oField->data_type = "";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $valore_target_indicatore = $indicatore->obiettivo_indicatore->getValoreTarget($cdr_valore_target);
        $oField->default_value = new ffData($valore_target_indicatore, "Text");
        $oRecord->addContent($oField, $indicatore->nome);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "raggiungimento_indicatore_" . $indicatore->obiettivo_indicatore->id;
        $oField->base_type = "Text";
        $oField->label = "Raggiungimento";
        $oField->class = "raggiungimento_indicatore";
        $oField->control_type = "label";
        $oField->store_in_db = false;
        //calcolo raggiungimento al caricamento della pagina
        $raggiungimento_indicatore = $indicatore->calcoloRaggiungimentoIndicatore($risultato_calcolo_indicatore, $valore_target_indicatore)["risultato"];
        $raggiungimento_indicatori[] = array("obiettivo_indicatore" => $indicatore->obiettivo_indicatore, "raggiungimento" => $raggiungimento_indicatore);
        $oField->default_value = new ffData($raggiungimento_indicatore . "%", "Text");
        $oField->data_type = "";
        $oRecord->addContent($oField, $indicatore->nome);

        $oRecord->addContent("</div>", $indicatore->nome);
        $oRecord->addContent("</div>", $indicatore->nome);
    }
}

//******************************************************************************
$oRecord->addContent(null, true, "allegati");
$oRecord->groups["allegati"]["title"] = "Allegati alla rendicontazione";

if ($periodo_rendicontazione->allegati == 1) {
    // Form di upload visibile solo per i periodi che prevedono allegati
    // Tabella sempre visibile
    // Ruolo view: solo visualizzazione
    // Ruolo edit_responsabile: visualizzazione download ed eliminazione
    $html = "<label>Allegati</label>";
    
    //Privilegi sugli allegati    
    $rendicontazione_allegati = null;
    if ($obiettivo_cdr_padre !== null) {
        //l'introduzione di una nuova variabile $rendicontazione_allegati è ridondante, basterebbe valorizzare $rendicontazione
        //perchè comunque verrà utilizzata solo in caso di $obiettivo_cdr_padre = null.
        //si decide di introdurre comunque una nuova variabile per flessibilità di eventuali future modifiche
        $rendicontazione_allegati = $obiettivo_cdr_padre->getRendicontazionePeriodo($periodo_rendicontazione);
    }
    else if ($rendicontazione !== null){
        $rendicontazione_allegati = $rendicontazione;
    }
    if ($rendicontazione_allegati !== null) {
        $allegati = ObiettiviRendicontazioneAllegato::getAll(['rendicontazione_id' => $rendicontazione_allegati->id]);
    }
    else {
        $allegati = array();
    }

    //START GRANT PERMISSIONS
    $allegati_permissions = $cm->oPage->globals["allegati_permissions"]["value"];
    if ($allegati_permissions['user_id'] == $user->matricola_utente_selezionato) {
        foreach ($allegati as $allegato) {
            $allegati_permissions['allegati_permissions']['canDownload'][] = $allegato->filename_md5;
            if ($user_privileges["edit_responsabile"]) {         
                $allegati_permissions['allegati_permissions']['canDelete'][] = $allegato->filename_md5;
            }   
        }
    }
    $cm->oPage->register_globals("allegati_permissions", $allegati_permissions);   
    $allegati_helper = new AllegatoHelper();
    
    //in fase di inserimento non è possibile allegare file        
    if ($rendicontazione_allegati == null) {
        $html .= '<p id="no_allegati_user_friendly">Gli allegati possono essere caricati successivamente al salvataggio della rendicontazione</p><br />';
        $oRecord->addContent($html, "allegati");
    } else {
        //messaggio in caso non ci siano allegati
        if (count($allegati) == 0) {
            $html .= '<p id="no_allegati_user_friendly">Nessun allegato caricato per il periodo di rendicontazione</p><br />';
        } else {
            $html .= '
            <table id="allegati-ajax-table" class="table table-striped table-responsive">
                <thead>
                    <tr>
                        <th class="cel-1 text-nowrap ffField text active">Nome File</th>
                        <th class="cel-1 text-nowrap ffField text active">Elimina</th>
                    </tr>
                </thead>
            ';
            $html .= "<tbody>";
            $key_allegato = 0;
            foreach ($allegati as $allegato) {               
                if ($user_privileges["view"]) {
                    if ($user_privileges["edit_responsabile"]) {
                        $txt_elimina = $allegati_helper->getDeleteLink($allegato->filename_md5, "Elimina");
                    }
                    else {
                        $txt_elmina = "-";
                    }
                    $txt_download = $allegati_helper->getDownloadLink($allegato->filename_md5, $allegato->filename_plain);
                    
                    $html .= '<tr id="al-' . $key_allegato . '" >';
                    $html .= "<td>" . $txt_download . "</td>";                        
                    if(!$user_privileges["edit_responsabile"]) {                    
                        $html .= "<td>-</td>";
                    }
                    else {
                        $html .= "<td class=\"delete\">" . $txt_elimina . "</td>";
                    }
                    $html .= '</tr>';
                    $key_allegato++;
                }
            }
        }
        $html .= "</tbody>";
        $html .= "</table>";
        $oRecord->addContent($html, "allegati");

        if ($user_privileges["edit_responsabile"]) {
            $html = "<p>Carica Allegato</p>";
            $oRecord->addContent($html, "allegati");
            $oRecord->addContent(
                $allegati_helper->getUploadForm(
                    'ObiettiviRendicontazioneAllegato', array(
                    'rendicontazione_id' => $rendicontazione_allegati->id,
                    'user_id' => $user->matricola_utente_selezionato,
                    'anno_riferimento' => $anno_global->descrizione
                    )
                ), "allegati"
            );
        }
        $oRecord->addContent("<br />", "allegati");
    }
} else {
    $oRecord->addContent("<label>Allegati - Caricamento allegati non previsto per il periodo di rendicontazione</label><br /><br />", "allegati");
}

//******************************************************************************
//se l'obiettivo è di coreferenza non vengono visualizzati i campi di raggiungimento obiettivo e nucleo
if (!$obiettivo_cdr->isCoreferenza()) {
    $oRecord->addContent(null, true, "raggiungimento");
    $oRecord->groups["raggiungimento"]["title"] = "Raggiungimento obiettivo";
    if ($periodo_rendicontazione->hide_raggiungibile != 1) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "raggiungibile";    
        $oField->label = "Si ritiene l'obiettivo raggiungibile al 31-12";
        if (!$user_privileges["edit_responsabile"]) {
            $oField->base_type = "Text";
            $oField->data_type = "";
            $oField->control_type = "label";           
            $oField->default_value = new ffData($rendicontazione->raggiungibile == 1 ? "Si" : ($rendicontazione->raggiungibile === null ? "ND" : "No"), "Text");
            $oField->store_in_db = false;
        } else {
            $oField->base_type = "Number";
            $oField->extended_type = "Selection";
            $oField->control_type = "radio";    
            $oField->multi_pairs = array(
                array(new ffData("1", "Number"), new ffData("Si", "Text")),
                array(new ffData("0", "Number"), new ffData("No", "Text")),
            );
            $oField->required = true;
        }
        $oRecord->addContent($oField, "raggiungimento");         
    }
    
    //richieste revisioni
    if ($periodo_rendicontazione->id_campo_revisione != null) {    
        $campo_revisione = new ObiettiviCampoRevisione($periodo_rendicontazione->id_campo_revisione);
        $multipairs_scelte_campo_revisione = array();
        $default_scelta = null;
        foreach($campo_revisione->getScelte() as $scelta){
            if ($scelta->id == $rendicontazione->id_scelta_campo_revisione) {
                $default_scelta = $scelta->descrizione;
            }
            $multipairs_scelte_campo_revisione[] = array(new ffData($scelta->id, "Number"), new ffData($scelta->descrizione, "Text"));
        }
        if ($default_scelta == null){
            $default_scelta = "ND";
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_scelta_campo_revisione";    
        $oField->label = $campo_revisione->nome;
        if (!$user_privileges["edit_responsabile"]) {            
            $oField->base_type = "Text";
            $oField->data_type = "";
            $oField->control_type = "label";        
            $oField->default_value = new ffData($default_scelta, "Text");
            $oField->store_in_db = false;        
        } else {
            $oField->base_type = "Number";
            $oField->extended_type = "Selection";
            $oField->control_type = "radio";    
            $oField->multi_pairs = $multipairs_scelte_campo_revisione;
            $oField->required = true;
        }    
        $oField->class = "richiesta_revisione";
        $oRecord->addContent($oField, "raggiungimento");                
    }
}
if (!$obiettivo_cdr->isCoreferenza() || ($obiettivo_cdr->isCoreferenza() && $rendicontazione !== null) || $user_privileges["edit_coreferenza"]) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "perc_raggiungimento";
    $oField->base_type = "Number";
    //raggiungimento non modificabile se l'utente non ne ha i privilegi   
    if (!($user_privileges["edit_responsabile"] || $user_privileges["edit_coreferenza"])) {
        if ($obiettivo_cdr->perc_raggiungimento === null) {
            $oField->default_value = new ffData("0", "Number");
        }
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    //in caso di indicatori definiti il raggiungimento non è modificabile in quanto calcolato in automatico ma viene comunque salvato a db
    else if (count($indicatori_associati)) {
        $oField->control_type = "label";
    } else {
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "100";
        $oField->step = "1";
        $oField->required = true;
        $oField->addValidator("number", array(true, 0, 100, true, true));
    }
    $oField->label = "Percentuale raggiungimento";
    $oRecord->addContent($oField, "raggiungimento");

    //***********************************************
    //eventuali campi nucleo (se obiettivo direzione e se ci si trova in modifica nell'obiettivo)
    if ($obiettivo_cdr->isObiettivoCdrAziendale() && $rendicontazione !== null) {
        //creazione fieldset referenti
        $oRecord->addContent(null, true, "nucleo");
        $oRecord->groups["nucleo"]["title"] = "Validazione rendicontazione";

        $oField = ffField::factory($cm->oPage);
        $oField->id = "perc_nucleo";
        $oField->base_type = "Number";
        $oField->addValidator("number", array(true, 0, 100, true, true));
        if (!$user_privileges["edit_nucleo"]) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        } else {
            $oField->widget = "slider";
            $oField->min_val = "0";
            $oField->max_val = "100";
            $oField->step = "1";
        }
        $oField->label = "Percentuale raggiungimento validata";
        $oRecord->addContent($oField, "nucleo");

        $oField = ffField::factory($cm->oPage);
        $oField->id = "note_nucleo";
        $oField->base_type = "Text";
        $oField->extended_type = "Text";
        $oField->label = "Note validazione rendicontazione";
        if (!$user_privileges["edit_nucleo"]) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        } else
            $oField->required = true;
        $oRecord->addContent($oField, "nucleo");

        $oRecord->addEvent("on_do_action", "preloadNoteNucleo");
    }
}

function preloadNoteNucleo($oRecord) {
    if ($oRecord->form_fields["note_nucleo"]->getValue() == null) {
        $oRecord->form_fields["note_nucleo"]->setValue(OBIETTIVI_NOTE_NUCLEO_DEFAULT);
    }
    if ($oRecord->form_fields["note_nucleo"]->getValue() !== OBIETTIVI_NOTE_NUCLEO_DEFAULT) {
        $oRecord->form_fields["note_nucleo"]->class = "nucleo_non_favorevole";
    }
}

//******************************************************************************
//se sono presenti assegnazioni ai cdr figli viene visualizzato un button per l'apertura del dialog

if ($show_rendicontazione_figli == true){
    $cdr_figli = $cdr->getFigli();
    if (count($cdr_figli) && !$obiettivo_cdr->isCoreferenza()) {                        
        //viene caricato il template specifico per le rendicontazioni dei cdr afferenti
        $modulo = core\Modulo::getCurrentModule();
        $tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
        $tpl->load_file("rendicontazioni_cdr_afferenti.html", "main");   
        $show_rendicontazione_figli = false;
        foreach($cdr_figli as $cdr_figlio) {
            $obiettivo_cdr_figlio = ObiettiviObiettivoCdr::factoryFromObiettivoCdr($obiettivo, $cdr_figlio);
            if ($obiettivo_cdr_figlio !== null && $obiettivo_cdr_figlio->id_tipo_piano_cdr != 0) {
                if ( $show_rendicontazione_figli == false) {
                    $show_rendicontazione_figli = true;
                }
                $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);
                $cdr_figlio_desc = $cdr_figlio->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_figlio->descrizione;                                                
                $rendicontazione_figlio_periodo = $obiettivo_cdr_figlio->getRendicontazionePeriodo($periodo_rendicontazione);                
                if ($rendicontazione_figlio_periodo !== null) {
                    $cdr_figlio_desc .= " - ".$rendicontazione_figlio_periodo->perc_raggiungimento."%";
                    $rendicontazione_figlio = $rendicontazione_figlio_periodo->showHtmlInfo();                    
                } 
                else {
                    $cdr_figlio_desc .= " - NC";
                    $rendicontazione_figlio = "Rendicontazione non compilata";                     
                }
                $tpl->set_var("cdr_figlio", $cdr_figlio_desc);
                $tpl->set_var("rendicontazione", $rendicontazione_figlio);
                $tpl->parse("RendicontazioneCdrAfferente", false);
                $tpl->parse("CdrAfferente", true);
            }                      
        }
        $cm->oPage->addContent($tpl->rpparse("main", true));

        if ($show_rendicontazione_figli == true) {
            //visualizzazione button
            $oBt = ffButton::factory($cm->oPage);
            $oBt->id = "action_button_rendicontazioni_figli";
            $oBt->label = "Rendicontazioni CdR afferenti";
            $oBt->action_type = "submit";
            $oBt->jsaction = "$('#inactive_body').show();$('#rendicontazioni_figli').show();";
            $oBt->aspect = "link";
            $oBt->class = "fa-sitemap";
            $oRecord->addActionButton($oBt);
        }
    }   
}

//campi aggiuntivi in inserimento
$oRecord->insert_additional_fields["ID_obiettivo_cdr"] = new ffData($obiettivo_cdr->id, "Number");
$oRecord->insert_additional_fields["ID_periodo_rendicontazione"] = new ffData($periodo_rendicontazione->id, "Number");
//data dell'ultima modifica
if ($user_privileges["edit_responsabile"]) {
    $oRecord->additional_fields["time_ultima_modifica_referente"] = new ffData(date("Y-m-d H:i:s"), "DateTime", "ISO9075");
}
// *********** ADDING TO PAGE ****************
if ($periodo_rendicontazione->allegati == 1) {      
    $permission_cookie = $allegati_helper->encodePermissions($cm->oPage->globals["allegati_permissions"]["value"]);
    //Call before every output or will not work!!! IMPORTANT
    setcookie('p_2_#', $permission_cookie, time() + 600, '/');
    //END GRANT PERMISSIONS
}
    
$cm->oPage->addContent($oRecord);

$cm->oPage->addContent(
    "<script>" . $script_calcolo_risultato . "</script>
    <div id='inactive_body'></div>
    <div id='conferma_delete_allegato'>
        <h3>Conferma eliminazione allegato</h3>
        <a id='conferma_si_eliminare' class='conferma_si confirm_link'>Conferma</a>
        <a id='conferma_no_eliminare' class='conferma_no confirm_link'>Annulla</a>
    </div>
");

//viene propagata la valutazione a tutto il personale associato all'obiettivo_cdr
function saveRelations($oRecord) {
    $rendicontazione_obiettivo = new ObiettiviRendicontazione($oRecord->key_fields["ID_rendicontazione"]->value->getValue());
    $obiettivo_cdr = new ObiettiviObiettivoCdr($rendicontazione_obiettivo->id_obiettivo_cdr);
    //vengono salvati ObiettivoCdrPersonale			
    $periodo = new ObiettiviPeriodoRendicontazione($rendicontazione_obiettivo->id_periodo_rendicontazione);
    foreach ($obiettivo_cdr->getObiettivoCdrPersonaleAssociati() as $obiettivo_cdr_personale) {
        if ($obiettivo_cdr_personale->data_eliminazione == null) {
            $valutazione = ObiettiviValutazionePersonale::factoryFromObiettivoCdrPersonalePeriodo($obiettivo_cdr_personale, $periodo);
            if ($valutazione == null) {
                $valutazione = new ObiettiviValutazionePersonale();
            }
            $valutazione->perc_raggiungimento = $oRecord->form_fields["perc_raggiungimento"]->value->getValue();
            $valutazione->time_ultimo_aggiornamento = date("Y-m-d H:i:s");
            if ($valutazione->id == null) {
                $valutazione->id_periodo_rendicontazione = $periodo->id;
                $valutazione->id_obiettivo_cdr_personale = $obiettivo_cdr_personale->id;
            }
            $valutazione->save();
        }
    }

    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    //vengono salvati i valori dei parametri degli indicatori
    foreach ($obiettivo->getIndicatoriAssociati() as $indicatore) {
        //vengono recuperati tutti i parametri dell'indicatore        
        foreach ($indicatore->getParametri() as $parametro) {
            $parametro_indicatore = $parametro->parametro_indicatore;
            //viene recuperato il valore del parametro dalla grid            
            //viene salvato il valore solamente se il parametro è modificabile (verifica su control type)                  
            if ($oRecord->form_fields["parametro_" . $indicatore->obiettivo_indicatore->id . "_" . $parametro_indicatore->id]->control_type !== "label") {
                $parametro_indicatore->setValoreParametroIndicatoreRendicontazione($rendicontazione_obiettivo, $oRecord->form_fields["parametro_" . $indicatore->obiettivo_indicatore->id . "_" . $parametro_indicatore->id]->value->getValue());
            }
        }
    }
}