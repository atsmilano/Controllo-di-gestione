<?php
CoreHelper::includeJqueryUi();
$cm->oPage->widgetLoad("dialog");

$tmp = $cm->oPage->widgets["dialog"]->process(
    "delete_valutazione_action_dialog", // id del dialog
    array(
        "name" => "delete_valutazione_action_dialog",
        "title" => "",
        "padre" => "",
        "url" => "",
        "callback" => "location.reload()",
    ),
    $cm->oPage
);
//viene verificato che la valutazione sia autovalutazione o meno (se non viene passato il parametro viene effettuato un redirect
if (isset($_REQUEST["keys[ID_valutazione]"])) {
    try {
        $valutazione = new ValutazioniValutazionePeriodica($_REQUEST["keys[ID_valutazione]"]);
    } catch (Exception $ex) {
        ffRedirect($_GET["ret_url"]);
    }
} else {
    ffRedirect($_GET["ret_url"]);
}


//*******UTENTE E PRIVILEGI************
$user = LoggedUser::getInstance();
$privilegi_utente = $valutazione->getPrivilegiPersonale($user->matricola_utente_selezionato);
if ($user->hasPrivilege("valutazioni_admin"))
    $admin_user = true;
else
    $admin_user = false;

//verifica che l'utente abbia i privilegi per visualizzare la valutazione
if ($admin_user == false && $valutazione->isAutovalutazione() && $privilegi_utente["view_autovalutazione"] !== true)
    ffRedirect($_GET["ret_url"]);
else if ($admin_user == false && !$valutazione->isAutovalutazione() && $privilegi_utente["view_valutazione"] !== true)
    ffRedirect($_GET["ret_url"]);

//****RECORD*********************************
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "valutazione-modify";
if ($valutazione->isAutovalutazione())
    $oRecord->title = "Scheda di Autovalutazione";
else
    $oRecord->title = "Scheda di Valutazione";
$periodo = new ValutazioniPeriodo($valutazione->id_periodo);

$oRecord->resources[] = "valutazione";
$oRecord->src_table = "valutazioni_valutazione_periodica";
$oRecord->allow_insert = false;
if (!$user->hasPrivilege("valutazioni_admin")) {
    $oRecord->allow_delete = false;
}
//visualizzazione del tasto "aggiorna" in base ai privilegi utente
if (
    !(
    ($valutazione->isAutovalutazione() && $privilegi_utente["edit_autovalutazione"] == true) || (!$valutazione->isAutovalutazione() && $privilegi_utente["edit_valutatore"] == true) || (!$valutazione->isAutovalutazione() && $privilegi_utente["edit_valutato"] == true)
    ) &&
    $admin_user == false
)
    $oRecord->allow_update = false;

//evento per il salvataggio dei dati
$oRecord->addEvent("on_do_action", "propagateDelete");
$oRecord->addEvent("on_done_action", "myUpdate");

// *********** FIELDS *****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_valutazione";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

//creazione fieldset referenti
$oRecord->addContent(null, true, "intestazione");
$oRecord->groups["intestazione"]["title"] = $periodo->descrizione;
$anno_valutazione = new ValutazioniAnnoBudget($periodo->id_anno_budget);
if (!$valutazione->isAutovalutazione()) {
    $valutatore = Personale::factoryFromMatricola($valutazione->matricola_valutatore);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "matricola_valutatore";
    $oField->base_type = "Text";
    if ($admin_user == false) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
        $oField->display_value = new ffData($valutatore->cognome . " " . $valutatore->nome . " (matr. " . $valutatore->matricola . ")", "Text");
    } else {
        $valutatori = array();
        foreach (Personale::getAll() as $dipendente) {
            $valutatori[] = array(
                new ffData($dipendente->matricola, "Number"),
                new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
            );
        }
        $oField->extended_type = "Selection";
        $oField->multi_pairs = $valutatori;
    }
    $oField->label = "Valutatore:";
    $oRecord->addContent($oField, "intestazione");
}

$valutato = Personale::factoryFromMatricola($valutazione->matricola_valutato);
$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_valutato";
$oField->base_type = "Text";
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->display_value = new ffData($valutato->cognome . " " . $valutato->nome . " (matr. " . $valutato->matricola . ")", "Text");
$oField->label = "Valutato:";
$oRecord->addContent($oField, "intestazione");

$link_eliminazione = FF_SITE_PATH
    . $cm->path_info
    . "?frmAction=valutazione-modify_delete&ret_url=&"
    . $cm->query_string;


$oRecord->buttons_options["delete"]["jsaction"] = "ff.ffPage.dialog.doOpen('delete_valutazione_action_dialog', '".$link_eliminazione."')";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_categoria";
$oField->base_type = "Text";
$oField->control_type = "label";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID,descrizione FROM valutazioni_categoria";
$oField->store_in_db = false;
$oField->label = "Tipologia scheda:";
$oRecord->addContent($oField, "intestazione");

if ($admin_user == true) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura_autovalutazione";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura autovalutazione";
    $oField->widget = "datepicker";
    $oField->addValidator("date");
    $oRecord->addContent($oField, "intestazione");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_firma_valutatore";
    $oField->base_type = "Date";
    $oField->label = "Data firma valutatore";
    $oField->widget = "datepicker";
    $oField->addValidator("date");
    $oRecord->addContent($oField, "intestazione");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_firma_valutato";
    $oField->base_type = "Date";
    $oField->label = "Data firma valutato";
    $oField->widget = "datepicker";
    $oField->addValidator("date");
    $oRecord->addContent($oField, "intestazione");
}

//*********************RIEPILOGO OBIETTIVI**************************
if ($periodo->visualizzazione_obiettivi != false) {
    $oRecord->addContent(null, true, "obiettivi");
    $oRecord->groups["obiettivi"]["title"] = "Obiettivi " . $anno_valutazione->descrizione;
    //estrazione degli eventuali obiettivi associati al dipendente
    $personale_obiettivi = $valutato->cloneAttributesToNewObject("PersonaleObiettivi");
    $obiettivi_indiviuduali = $personale_obiettivi->getObiettiviCdrPersonaleAnno($anno_valutazione);    
    if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
        $obiettivi_cdr_responsabilita = $personale_obiettivi->getObiettiviCdrReponsabilitaData($anno_valutazione, new DateTime(date($periodo->data_fine)), TipoPianoCdr::getPrioritaMassima());
    }
    else {
        $obiettivi_cdr_responsabilita = $personale_obiettivi->getObiettiviReponsabilitaData($anno_valutazione, new DateTime(date($periodo->data_fine)), TipoPianoCdr::getPrioritaMassima());
    }   
    $no_obiettivi = true;
    if (count($obiettivi_indiviuduali)) {    
        $no_obiettivi = false;
        $oRecord->addContent("<table id='obiettivi_anno_valutato'><thead><tr><th>Obiettivo Individuale</th><th>Cdr</th><th>Peso</th></tr></thead><tbody>", "obiettivi");
        $tot_obiettivi_personale = $personale_obiettivi->getPesoTotaleObiettivi($anno_valutazione);
        foreach ($obiettivi_indiviuduali as $obiettivo_individuale) {
            $obiettivo_cdr = new ObiettiviObiettivoCdr($obiettivo_individuale->id_obiettivo_cdr);
            $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);  
            if ($obiettivo_cdr->id_tipo_piano == 0) {
                $tipo_piano_cdr = TipoPianoCdr::getPrioritaMassima();
            }
            else {
                $tipo_piano_cdr = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano);
            }
            try {
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $periodo->data_fine);
                $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
                $cdr_desc = $cdr->codice . " - " . $cdr->descrizione;
            } catch (Exception $ex) {
                $cdr_desc = "Cdr cessato";
            }
            if ($tot_obiettivi_personale == 0) {
                $peso_perc = 0;
            } else {
                $peso_perc = 100 / $tot_obiettivi_personale * $obiettivo_individuale->peso;
            }                                                                                                                                    
            $obiettivo_desc = "<tr>
                                    <td>".$obiettivo->codice." - ".$obiettivo->titolo."</td>".
                                    "<td>".$cdr_desc."</td>".
                                    "<td>".number_format($peso_perc, 2) . "%</td>
                                </tr>";

            $oRecord->addContent($obiettivo_desc, "obiettivi");
        }
        $oRecord->addContent("</tbody></table>", "obiettivi");
    }
    if (count($obiettivi_cdr_responsabilita)){
        $no_obiettivi = false;
        if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
            $oRecord->addContent("<table id='obiettivi_anno_valutato'><thead><tr><th>Obiettivo Cdr Responsabilità</th><th>Cdr</th><th>Peso</th></tr></thead><tbody>", "obiettivi");
        }
        else {
            $oRecord->addContent("<table id='obiettivi_anno_valutato'><thead><tr><th>Obiettivo Cdr Responsabilità</th></tr></thead><tbody>", "obiettivi");
        }
        foreach($obiettivi_cdr_responsabilita as $obiettivo_cdr_responsabilita) {                    
            if ($periodo->visualizzazione_pesi_obiettivi_responsabile) {
                $obiettivo = $obiettivo_cdr_responsabilita["obiettivo"];
                $cdr_desc = $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->codice . " - " . $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->descrizione;
                $peso_tot_obiettivi_cdr = $obiettivo_cdr_responsabilita["anagrafica_cdr_obiettivo"]->getPesoTotaleObiettivi($anno_valutazione);
                if ($obiettivo_cdr_responsabilita["obiettivo_cdr"]->isReferenteObiettivoTrasversale()){
                    $coreferente = " (referente)";
                }
                else if ($obiettivo_cdr_responsabilita["obiettivo_cdr"]->isCoreferenza()) {
                    $coreferente = " (trasversale)";
                } else {
                    $coreferente = "";
                }
                if ($peso_tot_obiettivi_cdr == 0) {
                    $peso = 0;
                } else {
                    $peso = 100 / $peso_tot_obiettivi_cdr * $obiettivo_cdr_responsabilita["obiettivo_cdr"]->peso;
                }
                $obiettivo_desc = "<tr>
                                        <td>".$obiettivo->codice." - ".$obiettivo->titolo."</td>".
                                        "<td>".$cdr_desc."</td>".
                                        "<td>".number_format($peso, 2) . "%</td>
                                    </tr>";

                $oRecord->addContent($obiettivo_desc, "obiettivi");                        
            }
            else {
                $obiettivo_desc = "<tr>
                                        <td>".$obiettivo_cdr_responsabilita->codice." - ".$obiettivo_cdr_responsabilita->titolo."</td>
                                    </tr>";
                $oRecord->addContent($obiettivo_desc, "obiettivi"); 
            }
        }        
        $oRecord->addContent("</tbody></table>", "obiettivi");    
    }
    if ($no_obiettivi == true) {
        $oRecord->addContent("Nessun obiettivo assegnato nell'anno " . $anno_valutazione->descrizione, "obiettivi");
    }
}

//*********************AMBITI***************************************
//vengono estratti tutti gli ambiti valutati nel periodo per la categoria del valutato
$categoria = $valutazione->categoria;
//per ogni ambito previsto per la categoria per il periodo
foreach ($periodo->getAmbitiCategoriaPeriodo($categoria) as $ambito) {
    //in base al metodo di valutazione vengono visualizzati campi differenti ed in base ai privilegi viene data la possibilità o meno di compilare l'ambito
    $view_ambito = false;
    $edit_ambito = false;
    //se la valutazione è un'autovalutazione verranno visualizzati solamente gli ambiti con autovalutazione attiva
    //e verrà permessa la modifica solo agli utenti con privilegio edit_autovalutazione attivo
    if ($valutazione->isAutovalutazione()) {
        //l'ambito deve essere attivo per l'autovalutazione
        if ($periodo->getAutovalutazioneAttivaCategoriaAmbito($categoria, $ambito))
            $view_ambito = true;
        if ($privilegi_utente["edit_autovalutazione"] == true)
            $edit_ambito = true;
    }
    //se la valutazione non è autovalutazione verranno visualizzati tutti gli ambiti attivi per categoria e periodo
    //e verrà permessa la modifica dell'ambito solamente al valutatore
    else {
        $view_ambito = true;
        if ($privilegi_utente["edit_valutatore"] == true)
            $edit_ambito = true;
    }

    //verifica che l'ambito sia valutato nell'anno considerato (controllo di coerenza, in teoria non dovrebbe mai verificarsi il caso)
    if ($ambito->isValutatoCategoriaAnno($categoria, $anno_valutazione) == false) {
        $view_ambito = false;
        $edit_ambito = false;
    }

    if ($view_ambito == true) {
        $visualizzazione_punteggio_categoria_ambito = $periodo->getVisualizzazionePunteggiAttivaCategoriaAmbito($categoria, $ambito);
        $sezione = new ValutazioniSezione($ambito->id_sezione);
        //fieldset della sezione
        $oRecord->addContent(null, true, "sezione_" . $sezione->id);


        if ($oRecord->groups["sezione_" . $sezione->id]["title"] == null) {
            if ($valutazione->isAutovalutazione()) {
                $oRecord->groups["sezione_" . $sezione->id]["title"] = $sezione->codice . ". " . $sezione->descrizione;
            } else {//se non è ancora stato definito il nome della sezione
                $oRecord->groups["sezione_" . $sezione->id]["title"] = $sezione->codice . ". " . $sezione->descrizione;
                if ($periodo->getVisualizzazionePunteggiAttivaCategoriaSezione($categoria, $sezione)) {
                    $oRecord->groups["sezione_" . $sezione->id]["title"] .= " (" . round($valutazione->getTotaleRaggiungimentoSezione($sezione), 2) . " / "
                        . $sezione->getPesoAnno($anno_valutazione, $categoria) . ")";
                }
            }            
            $oRecord->groups["sezione_" . $sezione->id]["hide_title"] = false;
        }

        if ($valutazione->isAutovalutazione()) {
            $nome_ambito = $sezione->codice . "." . $ambito->codice . ". " . $ambito->descrizione;
        } else {
            $tot_ambito = $ambito->getPesoAmbitoCategoriaAnno($categoria, $anno_valutazione);
            $nome_ambito = $sezione->codice . "." . $ambito->codice . ". " . $ambito->descrizione;
            if ($visualizzazione_punteggio_categoria_ambito == true) {
                $nome_ambito .= " (" . round($valutazione->getTotaleRaggiungimentoAmbito($ambito), 2) . " / " . $tot_ambito . ")";
            }
        }
        //in base al metodo di valutazione dell'ambito viene visualizzato un campo / dei campi differenti
        $metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_valutazione);

        //estrazione campi metodo valutazione
        $found = false;
        for ($i = 0; $i < count(ValutazioniAmbito::$metodi_valutazione); $i++) {
            if (ValutazioniAmbito::$metodi_valutazione[$i]["ID"] == $metodo_valutazione) {
                $found = $i;
                $i = count(ValutazioniAmbito::$metodi_valutazione);
            }
        }
        if ($found === false) {
            ffErrorHandler::raise("Configurazione errata metodi valutazione");
        }else
            $nome_campo = ValutazioniAmbito::$metodi_valutazione[$found]["nome_campo"];

        //metodo valutazione: Ins. backoffice
        //il valore è sempre un raggiungimento percentuale
        if ($metodo_valutazione == 1) {
            //solamente admin può modificare il campo con questo metodo di valutazione
            $oField = ffField::factory($cm->oPage);
            $oField->id = $nome_campo . $ambito->id;
            $oField->label = $nome_ambito;
            $oField->store_in_db = false;
            $oField->data_type = "";
            //viene permessa la modifica solo per gli utenti admin
            if ($admin_user == false) {
                $oField->base_type = "Number";
                $oField->default_value = new ffData($valutazione->getPunteggioAmbito($ambito), "Number");
                $oField->control_type = "label";
            } else {
                $oField->base_type = "Number";
                $oField->default_value = new ffData($valutazione->getPunteggioAmbito($ambito), "Number");
                /*
                  $oField->widget = "slider";
                  $oField->min_val = "0";
                  $oField->max_val = 100;
                  $oField->step = "0.01";
                 */
            }
            $oRecord->addContent($oField, "sezione_" . $sezione->id);
        }
        //metodo valutazione: Items
        //neppure gli admin possono modificare il metodo di valutazione
        else if ($metodo_valutazione == 2) {
            //vengono considerati solo gli item dell'ambito corrente
            $items_valutazione = $valutazione->getItemsCategoriaAmbitoValutazione($ambito);
            if (count($items_valutazione) > 0) {
                //viene generato il titolo dell'ambito
                $oRecord->addContent("<h3>" . $nome_ambito . " </h3>", "sezione_" . $sezione->id);

                foreach ($items_valutazione as $item_valutazione) {
                    //visualizzazione dell'area
                    if ($i == 0 || ($area_prec !== $item_valutazione->id_area_item)) {
                        $area_item = new ValutazioniAreaItem($item_valutazione->id_area_item);
                        $area_prec = $item_valutazione->id_area_item;                        
                        if ($valutazione->isAutovalutazione()) {
                            $oRecord->addContent("<h4>" . $area_item->descrizione . "</h4>", "sezione_" . $sezione->id);
                        } else {
                            $punteggio_area_item = $valutazione->getPunteggiAreaItem($area_item);
                            $descrizione_area_item = "<h4>" . $area_item->descrizione;
                            if ($visualizzazione_punteggio_categoria_ambito == true) {
                                $descrizione_area_item .= " (" . $punteggio_area_item["punteggio"] . " / " . $punteggio_area_item["peso"] . ")";
                            }
                            $descrizione_area_item .= "</h4>";
                            $oRecord->addContent($descrizione_area_item, "sezione_" . $sezione->id);
                        }
                    }

                    //estrazione dei punteggi dell'item
                    $punteggio_item = array();
                    foreach ($item_valutazione->getPunteggi() as $punteggio) {
                        if ($visualizzazione_punteggio_categoria_ambito == true) {
                            $descrizione_punteggio = (float)$punteggio->punteggio . " - " . $punteggio->descrizione;
                        } else {
                            $descrizione_punteggio = $punteggio->descrizione;
                        }
                        $punteggio_item[] = array(
                            new ffData((float)$punteggio->punteggio, "Number"),
                            new ffData($descrizione_punteggio."", "Text"),
                        );
                    }
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = $nome_campo . $item_valutazione->id;
                    $oField->base_type = "Number";
                    $oField->extended_type = "Selection";
                    if ($item_valutazione->tipo_visualizzazione == 0) {
                        $oField->control_type = "radio";
                    }
                    $oField->multi_pairs = $punteggio_item;                    
                    $oField->multi_select_one_label = "Nessun punteggio assegnato...";
                    $oField->app_type = "Number";
                    $oField->data_type = "";
                    $oField->store_in_db = false;            
                    $raggiunto_item = $valutazione->getPunteggioItem($item_valutazione);
                    if ($raggiunto_item !== false) {
                        $oField->default_value = new ffData((float) $raggiunto_item, "Number");
                    } 
                    if ($edit_ambito == false) {
                        $oField->control_type = "label";
                    }
                    if ($valutazione->isAutovalutazione()) {
                        $oField->label = $item_valutazione->nome . " - " . $item_valutazione->descrizione;
                    } else {
                        if ($admin_user == false) {
                            $oField->required = true;
                        }
                        $oField->label = $item_valutazione->nome . " - " . $item_valutazione->descrizione;
                        if ($visualizzazione_punteggio_categoria_ambito == true) {
                            $oField->label .= " (" . $raggiunto_item / $item_valutazione->getPunteggioMassimo() * $item_valutazione->peso . "/" . $item_valutazione->peso . ")";
                        }
                    }
                    $oField->class = "combobox ambito_item";
                    $oRecord->addContent($oField, "sezione_" . $sezione->id);
                }
            }
        } else {
            //se nessun metodo specificato ($metodo = 0 casistica che non dovrebbe presentarsi)		
            ffErrorHandler::raise("Errore di configurazione ambito " . $nome_ambito);
        }
    }
}

//*********************TOTALI***************************************
//Per ogni totale previsto nella scheda viene visualizzata una sezione appostia
$oRecord->addContent(null, true, "totali");
$oRecord->groups["totali"]["title"] = "Totali";

//TOTALI VALUTAZIONE*********************************
if (!$valutazione->isAutovalutazione()) {    
    foreach ($valutazione->getTotaliPreCalcolati() as $totale_valutazione) {
        $ambiti_totale_attivi = "";
        $ambiti_totale = $totale_valutazione["totale_obj"]->getAmbitiTotale();
        foreach ($ambiti_totale as $ambito_totale) {
            if ($periodo->getVisualizzazionePunteggiAttivaCategoriaAmbito($categoria, $ambito_totale) == true) {                
                if ($ambito_totale->isValutatoCategoriaAnno($categoria, $anno_valutazione)) {
                    if ($valutazione->isAmbitoValutato($ambito_totale))
                        $nv = "";
                    else
                        $nv = "(nv)";

                    if (strlen($ambiti_totale_attivi) > 0)
                        $plus = " - ";
                    else
                        $plus = "";
                    $sezione = new ValutazioniSezione($ambito_totale->id_sezione);
                    $ambiti_totale_attivi .= $plus . $sezione->codice . "." . $ambito_totale->codice . "." . $nv;
                }
            }
        }
        if ($periodo->inibizione_visualizzazione_totali == false) {
            if ($periodo->inibizione_visualizzazione_ambiti_totali == true || strlen($ambiti_totale_attivi)==0) {
                $ambiti_totale_attivi = "";
            }
            else {
                $ambiti_totale_attivi = " (".$ambiti_totale_attivi.")";
            }
            $oRecord->addContent("<div class='totale'>" . $totale_valutazione["totale_obj"]->descrizione.$ambiti_totale_attivi." = ".$totale_valutazione["totale_calcolo"] . "</div>", "totali");
        }
    }
}
//*********************VALUTATORE***************************************
$oRecord->addContent(null, true, "valutatore");
$oRecord->groups["valutatore"]["title"] = "Valutatore";

if (!$valutazione->isAutovalutazione()) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_valutatore";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note";
    if ($privilegi_utente["edit_valutatore"] == false) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
    $oRecord->addContent($oField, "valutatore");
    
    if ($periodo->inibizione_visualizzazione_data_colloquio == false) {
        //se tolto commento togliere anche su verifica privilegi
        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_ultimo_colloquio";
        $oField->base_type = "Date";
        $oField->label = "Data ultimo colloquio con valutato";
        if ($privilegi_utente["edit_valutatore"] == false){
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
        else{
            $oField->widget = "datepicker";
        }
        $oRecord->addContent($oField, "valutatore");     
    }
}

//*********************VALUTATO*****************************************
$oRecord->addContent(null, true, "valutato");
$oRecord->groups["valutato"]["title"] = "Valutato";

$oField = ffField::factory($cm->oPage);
$oField->id = "note_valutato";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Note";
if ($valutazione->isAutovalutazione()) {
    $oRecord->groups["valutato"]["title"] = "Note all'autovalutazione";
    if ($privilegi_utente["edit_autovalutazione"] == false) {
        $oField->control_type = "label";
        $oField->store_in_db = false;
    }
} else if ($privilegi_utente["edit_valutato"] == false) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "valutato");

$oRecord->additional_fields["date_time_ultima_modifica"] =  new ffData(date("Y-m-d H:i:s"),"DateTime");

//*********************BUTTON AZIONI*****************************************

$tipo_chiusura = 0;
if ($valutazione->isAutovalutazione()) {
    if ($privilegi_utente["edit_autovalutazione"] == true) {
        $confirm_title = "Conferma chiusura autovalutazione";
        $label = "Chiusura autovalutazione";
        $tipo_chiusura = 1;
        $html_message = "
							Chiudendo l'autovalutazione non sar&agrave; pi&ugrave; possibile apportare modifiche.							
							<br><br>
							Confermare la chiusura dell&acute;autovalutazione?
						";
    }
} else {
    if ($privilegi_utente["edit_valutatore"] == true) {
        $confirm_title = "Conferma chiusura scheda e note valutatore";
        $label = "Firma valutatore";
        $tipo_chiusura = 2;
        $html_message = "
							Firmando la scheda di valutazione non sar&agrave; pi&ugrave; possibile apportare modifiche 
							e verr&agrave; fornita la possibilit&agrave al valutato di visualizzare la scheda.							
							<br><br>
							Confermare la firma della scheda?
						";
    } else if ($privilegi_utente["edit_valutato"] == true) {
        $confirm_title = "Conferma presa visione e note valutato";
        $label = "Firma presa visione";
        $tipo_chiusura = 3;
        $html_message = "
							Firmando la scheda di valutazione non sar&agrave; pi&ugrave; possibile apportare modifiche 
							e verr&agrave; confermata la presa visione della stessa, confermando le note inserite.
							<br><br>
							Confermare la firma per presa visione?
						";
    }
}

//se è prevista la visualizzazione di un pulsante
if ($tipo_chiusura !== 0) {
    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "firma_button_" . $tipo_chiusura;
    $oBt->label = $label;
    $oBt->action_type = "submit";
    $oBt->jsaction = "$('#inactive_body').show();$('#conferma_chiusura').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit";
    $oRecord->addActionButton($oBt);

    $oRecord->addHiddenField("tipo_chiusura", new ffData($tipo_chiusura, "Number"));

    $cm->oPage->addContent("<div id='inactive_body'></div>
							<div id='conferma_chiusura' class='conferma_azione'>
								<h3>" . $confirm_title . "</h3>
								<p>" . $html_message . "</p>
								<a id='conferma_si' class='confirm_link'>Conferma</a>
								<a id='conferma_no' class='confirm_link'>Annulla</a>
							</div>
							<script>
								$('#conferma_si').click(function(){
									document.getElementById('frmAction').value = 'valutazione-modify_chiusura';
									document.getElementById('frmMain').submit();
								});
								$('#conferma_no').click(function(){
									$('#inactive_body').hide();
									$('#conferma_chiusura').hide();
									$('#valutazione-modify_firma_button_" . $tipo_chiusura . "').prop('disabled', false);
									$('#valutazione-modify_firma_button_" . $tipo_chiusura . "').prop('style', false);
									$('#valutazione-modify_firma_button_" . $tipo_chiusura . "').val('" . $label . "');	
								});
							</script>
							");
}

if ($valutazione->isAutovalutazione() || $privilegi_utente["edit_valutatore"] !== true) {
    $oRecord->buttons_options["update"]["label"] = "Salvataggio modifiche";
} else {
    $oRecord->buttons_options["update"]["label"] = "Salvataggio modifiche / Ricalcolo totali";
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

//propagazione dell'eliminazione all'autovalutazione
function propagateDelete($oRecord, $frmAction){

    switch($frmAction) {
        case "confirmdelete":
            $valutazione = new ValutazioniValutazionePeriodica($oRecord->key_fields["ID_valutazione"]->value->getValue());
            $autovalutazione_collegata = $valutazione->getAutovalutazioneCollegata();
            if ($autovalutazione_collegata) {
                if(!$autovalutazione_collegata->delete()){
                    return CoreHelper::setError($oRecord,"L'autovalutazione collegata non può essere eliminata.");
                }
            }
            if(!$valutazione->delete()){
                return CoreHelper::setError($oRecord,"La valutazione non può essere eliminata.");
            }
            $oRecord->skip_action = true;
        break;
    }        
}

//salvataggio dei punteggi degli ambiti in base ai metodi di valutazione
//non viene discriminata la action sul form in quanto l'unica ammessa è update
function myUpdate($oRecord, $frmAction) {
    $cm = cm::getInstance();
    $user = LoggedUser::getInstance();        
    if($frmAction !== "delete" && $frmAction !== "confirmdelete") {
        $valutazione = new ValutazioniValutazionePeriodica($oRecord->key_fields["ID_valutazione"]->value->getValue());
        $autovalutazione_collegata = $valutazione->getAutovalutazioneCollegata();
        $periodo = new ValutazioniPeriodo($valutazione->id_periodo);
        $anno_valutazione = new ValutazioniAnnoBudget($periodo->id_anno_budget);
        $privilegi_utente = $valutazione->getPrivilegiPersonale($user->matricola_utente_selezionato);
        $categoria = $valutazione->categoria;

        if ($user->hasPrivilege("valutazioni_admin")) {
            $admin_user = true;
            //aggiornamento dei campi cambiati           
            if ($admin_user == true) {
                if (!$valutazione->isAutovalutazione()) {
                    //se il campo firma_valutatore è variato
                    if ($oRecord->form_fields["data_firma_valutatore"]->value_ori->getValue() !== $oRecord->form_fields["data_firma_valutatore"]->value->getValue()) {
                        if ($autovalutazione_collegata !== false) {
                            $autovalutazione_collegata->data_firma_valutatore = $oRecord->form_fields["data_firma_valutatore"]->value->getValue("Date", "ISO9075");
                            $autovalutazione_collegata->save();
                        }
                    }

                    //se il campo  firma_valutatore è stato azzerato (scheda riaperta) o vale 0 viene azzerato anche data_firma_valutato
                    if (strlen($oRecord->form_fields["data_firma_valutatore"]->value->getValue()) == 0) {
                        $valutazione->data_firma_valutato = "";
                        $valutazione->save();
                        if ($autovalutazione_collegata !== false) {
                            $autovalutazione_collegata->data_firma_valutato = "";
                            $autovalutazione_collegata->save();
                        }
                    }
                    //altrimenti viene impostato il valore da record
                    else if ($oRecord->form_fields["data_firma_valutato"]->value_ori->getValue() !== $oRecord->form_fields["data_firma_valutato"]->value->getValue()) {
                        if ($autovalutazione_collegata !== false) {
                            $autovalutazione_collegata->data_firma_valutato = $oRecord->form_fields["data_firma_valutato"]->value->getValue("Date", "ISO9075");
                            $autovalutazione_collegata->save();
                        }
                    }
                } else {
                    $valutazione_collegata = $valutazione->getValutazioneCollegata();
                    //se il campo chiusura autovalutazione
                    if ($oRecord->form_fields["data_chiusura_autovalutazione"]->value_ori->getValue() !== $oRecord->form_fields["data_chiusura_autovalutazione"]->value->getValue()) {
                        if ($valutazione_collegata !== false) {
                            $valutazione_collegata->data_chiusura_autovalutazione = $oRecord->form_fields["data_chiusura_autovalutazione"]->value->getValue("Date", "ISO9075");
                            $valutazione_collegata->save();
                        }
                    }
                }
            }
        } else {
            $admin_user = false;
        }

        //vengono estratti tutti gli ambiti valutati nel periodo per la categoria del valutato
        //per ogni ambito previsto per la categoria per il periodo
        foreach ($periodo->getAmbitiCategoriaPeriodo($categoria) as $ambito) {
            //verifica della possibiltà di modificare l'ambito
            $edit_ambito = false;
            //se la valutazione è un'autovalutazione verranno visualizzati solamente gli ambiti con autovalutazione attiva
            //e verrà permessa la modifica solo agli utenti con privilegio edit_autovalutazione attivo
            if ($valutazione->isAutovalutazione()) {
                //l'ambito deve essere attivo per l'autovalutazione
                if (($periodo->getAutovalutazioneAttivaCategoriaAmbito($categoria, $ambito)) &&
                    $privilegi_utente["edit_autovalutazione"] == true)
                    $edit_ambito = true;
            }
            //se la valutazione non è autovalutazione verrà permessa la modifica dell'ambito solamente al valutatore
            else if ($user->hasPrivilege("valutazioni_admin") == true || $privilegi_utente["edit_valutatore"] == true) {
                $edit_ambito = true;
            }

            //verifica che l'ambito sia valutato nell'anno considerato (controllo di coerenza, in teoria non dovrebbe mai verificarsi il caso)
            if ($ambito->isValutatoCategoriaAnno($categoria, $anno_valutazione) == false) {
                $edit_ambito = false;
            }

            //nel caso in cui l'ambito possa essere modificato
            if ($edit_ambito == true) {
                //in base al metodo di valutazione dell'ambito viene visualizzato un campo / dei campi differenti
                $metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $anno_valutazione);
                $sezione = new ValutazioniSezione($ambito->id_sezione);

                //estrazione campi metodo valutazione
                $found = false;
                for ($i = 0; $i < count(ValutazioniAmbito::$metodi_valutazione); $i++) {
                    if (ValutazioniAmbito::$metodi_valutazione[$i]["ID"] == $metodo_valutazione) {
                        $found = $i;
                        $i = count(ValutazioniAmbito::$metodi_valutazione);
                    }
                }
                if ($found === false)
                    ffErrorHandler::raise("Configurazione errata metodi valutazione");
                else
                    $nome_campo = ValutazioniAmbito::$metodi_valutazione[$found]["nome_campo"];

                $field_name = $nome_campo . $i;

                //metodo valutazione: Ins. backoffice
                if ($metodo_valutazione == 1) {
                    //viene convertito il punteggio nel formato corretto per il db
                    $punteggio = floatval(str_replace(',', '.', str_replace('.', '', $oRecord->form_fields[$nome_campo . $ambito->id]->value->getValue())));

                    $ambito_success = $valutazione->salvaPunteggioAmbito($ambito, $punteggio);

                    if ($ambito_success !== true)
                        $error = true;                    
                }
                //metodo valutazione: Items
                else if ($metodo_valutazione == 2) {
                    $ambito_success = true;
                    foreach ($valutazione->getItemsCategoriaAmbitoValutazione($ambito) as $item_valutazione) {
                        //viene convertito il punteggio nel formato corretto per il db
                        $punteggio = floatval(str_replace(',', '.', str_replace('.', '', $oRecord->form_fields[$nome_campo . $item_valutazione->id]->value->getValue())));

                        $success = $valutazione->salvaPunteggioItem($item_valutazione, $punteggio);
                        if ($success !== true) {
                            $ambito_success = false;
                        }                        
                    }
                    if ($ambito_success !== true)
                        $error = true;
                } else {
                    //se nessun metodo specificato ($metodo = 0 casistica che non dovrebbe presentarsi)
                    ffErrorHandler::raise("Errore di configurazione ambito " . $sezione->codice . "." . $ambito->codice);
                }
                $valutazione->saveAmbitoPrecalcolato($ambito);
            }
        }
        //vengono salvati i totali nella tabella specifica
        $valutazione->saveTotaliPreCalcolati();
        //notifiche
        if ($error == true) {
            mod_notifier_add_message_to_queue("Errore durante l'aggiornamento della scheda", MOD_NOTIFIER_ERROR);
        } else {
            mod_notifier_add_message_to_queue("Scheda aggiornata correttamente", MOD_NOTIFIER_SUCCESS);
            //nel caso di salvataggio andato a buon fine e richiesta di chiusura viene effettuata la chiusura della scheda
            if ($frmAction == "chiusura" && $oRecord->hidden_fields["tipo_chiusura"] !== null) {
                $tipo_chiusura = $oRecord->hidden_fields["tipo_chiusura"]->getValue();

                //verifica che l'utente abbia i privilegi per visualizzare la valutazione
                if ($admin_user == false && $valutazione->isAutovalutazione() && $privilegi_utente["view_autovalutazione"] !== true)
                    ffRedirect($_GET["ret_url"]);
                else if ($admin_user == false && !$valutazione->isAutovalutazione() && $privilegi_utente["view_valutazione"] !== true)
                    ffRedirect($_GET["ret_url"]);

                if (!($tipo_chiusura >= 1 && $tipo_chiusura <= 3)) {
                    ffErrorHandler::raise("Errore nel passaggio dei parametri");
                }

                //viene verificato per ogni azione di chiusura che l'utente abbia i privilegi per farlo
                switch ($tipo_chiusura) {
                    case 1:
                        if ($privilegi_utente["edit_autovalutazione"] == true) {
                            $valutazione->data_chiusura_autovalutazione = date("Y-m-d H:i:s");
                            $valutazione_collegata = $valutazione->getValutazioneCollegata();
                            $valutazione_collegata->data_chiusura_autovalutazione = date("Y-m-d H:i:s");
                            try {
                                $valutazione->save();
                                $valutazione_collegata->save();
                                mod_notifier_add_message_to_queue("Chiusura autovalutazione effettuata con successo", MOD_NOTIFIER_SUCCESS);
                            } catch (Exception $ex) {
                                mod_notifier_add_message_to_queue("Errore durante la chiusura dell'autovalutazione", MOD_NOTIFIER_ERROR);
                            }
                        } else {
                            ffErrorHandler::raise("Azione non consentita");
                        }
                        break;
                    case 2:
                        if ($privilegi_utente["edit_valutatore"] == true) {                           
                            $valutazione->data_firma_valutatore = date("Y-m-d H:i:s");
                            $valutazione->note_valutatore = $oRecord->form_fields["note_valutatore"]->value->getValue();
                            if ($autovalutazione_collegata !== false) {                                
                                $autovalutazione_collegata->data_firma_valutatore = date("Y-m-d H:i:s");                                
                            }
                            try {
                                $valutazione->save();
                                if ($autovalutazione_collegata !== false) {
                                    $autovalutazione_collegata->save();
                                }
                                mod_notifier_add_message_to_queue("Chiusura valutatore effettuata con successo", MOD_NOTIFIER_SUCCESS);
                            } catch (Exception $ex) {
                                mod_notifier_add_message_to_queue("Errore durante la chiusura del valutatore", MOD_NOTIFIER_ERROR);
                            }
                        } else {
                            ffErrorHandler::raise("Azione non consentita");
                        }
                        break;
                    case 3:
                        if ($privilegi_utente["edit_valutato"] == true) {
                            $valutazione->data_firma_valutato = date("Y-m-d H:i:s");
                            $valutazione->note_valutato = $oRecord->form_fields["note_valutato"]->value->getValue();
                            if ($autovalutazione_collegata !== false) {
                                $autovalutazione_collegata->data_firma_valutato = date("Y-m-d H:i:s");                                
                            }
                            try {
                                $valutazione->save();
                                if ($autovalutazione_collegata !== false) {
                                    $autovalutazione_collegata->save();
                                }
                                mod_notifier_add_message_to_queue("Chiusura valutato effettuata con successo", MOD_NOTIFIER_SUCCESS);
                            } catch (Exception $ex) {
                                mod_notifier_add_message_to_queue("Errore durante la chiusura del valutato", MOD_NOTIFIER_ERROR);
                            }
                        } else {
                            ffErrorHandler::raise("Azione non consentita");
                        }
                        break;
                }
                if(isset($_GET["ret_url"])) {
                    $ret_url = $_GET["ret_url"];
                } else {
                    $ret_url = FF_SITE_PATH;
                }
                ffRedirect($ret_url);
            }
        }
    }
}
