<?php
//vengono visualizzati tutti gli obiettivi assegnati all'utente nell'anno
$user = LoggedUser::Instance();

$personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
$anno = $cm->oPage->globals["anno"]["value"];
$tot_obiettivi_personale = $personale->getPesoTotaleObiettivi($anno);
//aggiunta del parametro per la gestione del record
try {
    $accettazione_obiettivo = ObiettiviAccettazione::factoryFromDipendenteAnno($personale, $anno);

    if (!isset($_REQUEST["keys[ID_accettazione]"])) {
        $url = FF_SITE_PATH . $cm->path_info . "?keys[ID_accettazione]=" . $accettazione_obiettivo->id . "&" . $_SERVER['QUERY_STRING'];
        ffRedirect($url);
        die();
    }
} catch (Exception $e) {
    if (isset($_REQUEST["keys[ID_accettazione]"])) {
        unset($_REQUEST["keys[ID_accettazione]"]);
    }
}

$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

$cm->oPage->addContent("<div id='obiettivi_individuali'>");

//popolamento della grid tramite array	
$db = ffDb_Sql::factory();
$accettazione_permessa = false;
$obiettivi_cdr_personale_anno = $personale->getObiettiviCdrPersonaleAnno($anno);
$currentModule = Modulo::getCurrentModule();
$record_url = MODULES_SITE_PATH . $currentModule->site_path . "/dettagli_obiettivo";

if (count($obiettivi_cdr_personale_anno) > 0) {
    $grid_fields = array(
        "ID",
        "codice",
        "titolo",
        "cdr",
        "peso",
        "desc_periodo",
        "raggiungimento",
    );
    $grid_recordset_da_confermare = array();
    $grid_recordset_confermati = array();
    //Record_url				
    foreach ($obiettivi_cdr_personale_anno as $ob_personale) {
        if ($ob_personale->data_eliminazione == null) {
            $obiettivo_cdr = new ObiettiviObiettivoCdr($ob_personale->id_obiettivo_cdr);
            if ($obiettivo_cdr->data_eliminazione == null) {
                if ($obiettivo_cdr->id_tipo_piano_cdr == null) {
                    //selezione di un piano dei cdr predefinito				
                    $tipo_piano_cdr = Cdr::getTipoPianoPriorita($obiettivo_cdr->codice_cdr, $date);
                } else {
                    $tipo_piano_cdr = new TipoPianoCdr($obiettivo_cdr->id_tipo_piano_cdr);
                }
                //recupero del cdr
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);
                $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
                //viene istanziato il cdr come oggetto differente per poter recuperare il peso (il metodo statico su cdr ritorna un oggetto cdr)				
                $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
                //rendicontazione dell'ultimo periodo attivo
                $periodo_riferimento = ObiettiviPeriodoRendicontazione::getUltimoDefinitoAnno($anno);
                $raggiungimento = "NV";
                if ($periodo_riferimento !== null) {
                    $periodo_desc = $periodo_riferimento->descrizione . " (" . date("d/m/Y", strtotime($periodo_riferimento->data_riferimento_inizio)) . " - " . date("d/m/Y", strtotime($periodo_riferimento->data_riferimento_fine)) . ")";
                    $obiettivo_cdr_aziendale = $obiettivo_cdr->getObiettivoCdrAziendale();
                    $rendicontazione_aziendale = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo_riferimento);
                    if ($rendicontazione_aziendale !== null) {
                        $rendicontazione_valutata_nucleo = $rendicontazione_aziendale->getValutazioneNucleo();
                        if (strlen($rendicontazione_valutata_nucleo["rendicontazione"]->note_nucleo) > 0) {
                            $raggiungimento = $rendicontazione_valutata_nucleo["rendicontazione"]->perc_nucleo . "%";
                        }
                    }
                    if ($obiettivo_cdr->isCoreferenza()) {
                        $rendicontazione_cdr = $rendicontazione_aziendale;
                    } else {
                        $rendicontazione_cdr = $obiettivo_cdr->getRendicontazionePeriodo($periodo_riferimento);
                    }
                    if ($rendicontazione_cdr !== null) {
                        $raggiungimento .= "*";
                    }
                } else {
                    $periodo_desc = "Nessun periodo aperto nell'anno";
                }

                //vengono considerati solamente gli obiettivi confermati da parte del cdr
                if ($obiettivo->data_eliminazione == null) {
                    if ($obiettivo_cdr->isReferenteObiettivoTrasversale()){
                        $coreferente = " (referente)";
                    }
                    else if ($obiettivo_cdr->isCoreferenza()) {
                        $coreferente = " (trasversale)";
                    } else {
                        $coreferente = "";
                    }
                    if ($tot_obiettivi_personale == 0) {
                        $peso_perc = 0;
                    } else {
                        $peso_perc = 100 / $tot_obiettivi_personale * $ob_personale->peso;
                    }
                    //viene verificato che l'obiettivo sia già stato accettato dal dipendente
                    if (($ob_personale->data_accettazione == null) &&
                        ($obiettivo_cdr->data_chiusura_modifiche !== null && strtotime(date("Y-m-d")) >= strtotime($obiettivo_cdr->data_chiusura_modifiche))) {
                        $grid_recordset_da_confermare[] = array(
                            $obiettivo_cdr->id,
                            $obiettivo->codice . $coreferente,
                            $obiettivo->titolo,
                            $cdr->codice . " - " . $cdr->descrizione,
                            number_format($peso_perc, 2) . "%",
                            $periodo_desc,
                            $raggiungimento,
                        );
                    } else if ($ob_personale->data_accettazione !== null) {
                        $grid_recordset_confermati[] = array(
                            $obiettivo_cdr->id,
                            $obiettivo->codice . $coreferente,
                            $obiettivo->titolo,
                            $cdr->codice . " - " . $cdr->descrizione,
                            number_format($peso_perc, 2) . "%",
                            $periodo_desc,
                            $raggiungimento,
                        );
                    }
                }
            }
        }
    }

    if (count($grid_recordset_da_confermare) > 0) {
        $accettazione_permessa = true;
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivo-cdr-personale";
        $oGrid->title = "Obiettivi individuali assegnati in attesa di presa visione";
        $oGrid->resources[] = "obiettivo-cdr";
        $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset_da_confermare, "obiettivi_obiettivo_cdr");
        $oGrid->order_default = "cdr";
        $oGrid->record_id = "obiettivo-cdr-modify";
        $oGrid->order_method = "labels";
        $oGrid->display_search = false;
        $oGrid->record_url = $record_url;

        //operazioni di inserimento ed eliminazione non permesse
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;

        $oGrid->addEvent("on_before_parse_row", "initGrid");

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo_cdr";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "codice";
        $oField->base_type = "Text";
        $oField->label = "Codice";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "titolo";
        $oField->base_type = "Text";
        $oField->label = "Titolo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr";
        $oField->base_type = "Text";
        $oField->label = "Cdr";
        $oField->order_SQL = "cdr ASC, codice ASC";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "peso";
        $oField->base_type = "Text";
        $oField->label = "Peso";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "desc_periodo";
        $oField->base_type = "Text";
        $oField->label = "Periodo rendicontazione";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "raggiungimento";
        $oField->base_type = "Text";
        $oField->label = "Raggiungimento";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $cm->oPage->addContent($oGrid);
    }

    if (count($grid_recordset_confermati) > 0) {
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivi-cdr-personale-accettati";
        $oGrid->title = "Obiettivi individuali assegnati";
        $oGrid->resources[] = "obiettivo-cdr";
        $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset_confermati, "obiettivi_obiettivo_cdr");
        $oGrid->order_default = "cdr";
        $oGrid->record_id = "obiettivo-cdr-modify";
        $oGrid->order_method = "labels";
        $oGrid->record_url = $record_url;
        $oGrid->display_search = false;

        //operazioni di inserimento ed eliminazione non permesse
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;

        $oGrid->addEvent("on_before_parse_row", "initGrid");

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_obiettivo_cdr";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "codice";
        $oField->base_type = "Text";
        $oField->label = "Codice";
        $oField->order_field = true;
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "titolo";
        $oField->base_type = "Text";
        $oField->label = "Titolo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr";
        $oField->base_type = "Text";
        $oField->label = "Cdr";
        $oField->order_SQL = "cdr ASC, codice ASC";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "peso";
        $oField->base_type = "Text";
        $oField->label = "Peso";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "desc_periodo";
        $oField->base_type = "Text";
        $oField->label = "Periodo rendicontazione";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "raggiungimento";
        $oField->base_type = "Text";
        $oField->label = "Raggiungimento";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $cm->oPage->addContent($oGrid);
    }

    if (count($grid_recordset_da_confermare) > 0 || count($grid_recordset_confermati) > 0) {
        //record accettazione
        //definizione del record
        $oRecord = ffRecord::factory($cm->oPage);
        $oRecord->id = "obiettivi-accettazione";
        $oRecord->title = "Note per presa visione degli obiettivi assegnati";
        $oRecord->resources[] = "accettazione";

        $oRecord->src_table = "obiettivi_accettazione";
        $oRecord->allow_delete = false;
        if ($accettazione_permessa == false) {
            $oRecord->allow_update = false;
            $oRecord->allow_insert = false;
        }

        //salvataggio della data di accettazione su ogni sngolo obiettivo_cdr_personale
        $oRecord->addEvent("on_done_action", "accettaObiettivoCdrPersonale");

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_accettazione";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oRecord->addKeyField($oField);

        if ($accettazione_obiettivo->data_accettazione_dipendente !== null) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "data_accettazione_dipendente";
            $oField->label = "Data ultima presa visione";
            $oField->base_type = "DateTime";
            $oField->control_type = "label";
            $oField->store_in_db = false;
            $oRecord->addContent($oField);
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "note_dipendente";
        if ($accettazione_permessa == false) {
            $oField->label = "Note Dipendente.";
        } else {
            $oField->label = "Note Dipendente. Se si ritiene opportuno è possibile inserire delle note e/o commenti.";
        }
        $oField->base_type = "Text";
        $oField->extended_type = "Text";
        if ($accettazione_permessa == false) {
            $oField->control_type = "label";
            $oField->store_in_db = false;
        }
        $oRecord->addContent($oField);

        $oRecord->buttons_options["insert"]["label"] = $oRecord->buttons_options["update"]["label"] = "Firma per presa visione";

        //matricola_personale
        $oRecord->insert_additional_fields["matricola_personale"] = new ffData($user->matricola_utente_selezionato, "Text");
        //ID_anno_budget
        $oRecord->insert_additional_fields["ID_anno_budget"] = new ffData($anno->id, "Number");
        //data_accettazione_dipendente
        $oRecord->additional_fields["data_accettazione_dipendente"] = new ffData(date("Y-m-d H:i:s"), "DateTime");

        $cm->oPage->addContent($oRecord);
    }

    function accettaObiettivoCdrPersonale($oRecord) {
        $cm = cm::getInstance();
        $user = LoggedUser::Instance();
        $personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
        $anno = $cm->oPage->globals["anno"]["value"];
        foreach ($personale->getObiettiviCdrPersonaleAnno($anno) as $ob_personale) {
            if ($ob_personale->data_eliminazione == null) {
                $obiettivo_cdr = new ObiettiviObiettivoCdr($ob_personale->id_obiettivo_cdr);
                if ($obiettivo_cdr->data_eliminazione == null) {
                    //recupero del cdr											
                    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
                    //vengono considerati solamente gli obiettivi confermati da parte del cdr
                    if ($obiettivo->data_eliminazione == null) {
                        //viene verificato che l'obiettivo sia già stato accettato dal dipendente
                        if (($ob_personale->data_accettazione == null) &&
                            ($obiettivo_cdr->data_chiusura_modifiche !== null && strtotime(date("Y-m-d")) >= strtotime($obiettivo_cdr->data_chiusura_modifiche))) {
                            $ob_personale->data_accettazione = date("Y-m-d H:i:s");
                            $ob_personale->save();
                        }
                    }
                }
            }
        }
        //viene forzato il redirect per ovviare ad un problema di explorer		
        $path_info_parts = explode("/", $cm->path_info);
        $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
        $url = FF_SITE_PATH . $path_info . "obiettivi_individuali";
        ffRedirect($url . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    }

}

//******************************************************************************
//obiettivi dei cdr i quali il dipendente è responsabile
$grid_fields = array(
    "ID",
    "codice",
    "titolo",
    "cdr",
    "peso",
    "desc_periodo",
    "raggiungimento",
);
$grid_recordset_responsabile = array();
foreach ($personale->getCodiciCdrResponsabilitaAnno($anno) as $codice_cd_resp) {    
    $cdr_resp_anno = AnagraficaCdrObiettivi::factoryFromCodice($codice_cd_resp, $dateTimeObject);
    $peso_tot_obiettivi_cdr = $cdr_resp_anno->getPesoTotaleObiettivi($anno);
    foreach ($cdr_resp_anno->getObiettiviCdrAnno($anno) as $ob_cdr_resp) {
        //recupero del cdr											
        $obiettivo = new ObiettiviObiettivo($ob_cdr_resp->id_obiettivo);
        //rendicontazione dell'ultimo periodo attivo
        $periodo_riferimento = ObiettiviPeriodoRendicontazione::getUltimoDefinitoAnno($anno);
        $raggiungimento = "NV";
        if ($periodo_riferimento !== null) {
            $periodo_desc = $periodo_riferimento->descrizione . " (" . date("d/m/Y", strtotime($periodo_riferimento->data_riferimento_inizio)) . " - " . date("d/m/Y", strtotime($periodo_riferimento->data_riferimento_fine)) . ")";
            $obiettivo_cdr_aziendale = $ob_cdr_resp->getObiettivoCdrAziendale();
            $rendicontazione_aziendale = $obiettivo_cdr_aziendale->getRendicontazionePeriodo($periodo_riferimento);
            if ($rendicontazione_aziendale !== null) {
                $rendicontazione_valutata_nucleo = $rendicontazione_aziendale->getValutazioneNucleo();
                if (strlen($rendicontazione_valutata_nucleo["rendicontazione"]->note_nucleo) > 0) {
                    $raggiungimento = $rendicontazione_valutata_nucleo["rendicontazione"]->perc_nucleo . "%";
                }
            }
            $rendicontazione_cdr = $ob_cdr_resp->getRendicontazionePeriodo($periodo_riferimento);
            if ($rendicontazione_cdr !== null) {
                $raggiungimento .= "*";
            }
        } else {
            $periodo_desc = "Nessun periodo aperto nell'anno";
        }

        //vengono considerati solamente gli obiettivi confermati da parte del cdr
        if ($obiettivo->data_eliminazione == null) {
            //viene verificato che l'obiettivo sia già stato accettato dal dipendente
            if ($ob_cdr_resp->data_chiusura_modifiche !== null && strtotime(date("Y-m-d")) >= strtotime($ob_cdr_resp->data_chiusura_modifiche)) {
                if ($ob_cdr_resp->isReferenteObiettivoTrasversale()){
                    $coreferente = " (referente)";
                }
                else if ($ob_cdr_resp->isCoreferenza()) {
                    $coreferente = " (trasversale)";
                } else {
                    $coreferente = "";
                }
                if ($peso_tot_obiettivi_cdr == 0) {
                    $peso = 0;
                } else {
                    $peso = 100 / $peso_tot_obiettivi_cdr * $ob_cdr_resp->peso;
                }

                $grid_recordset_responsabile[] = array(
                    $ob_cdr_resp->id,
                    $obiettivo->codice . $coreferente,
                    $obiettivo->titolo,
                    $cdr_resp_anno->codice . " - " . $cdr_resp_anno->descrizione,
                    number_format($peso, 2) . "%",
                    $periodo_desc,
                    $raggiungimento,
                );
            }
        }
    }
}

if (count($grid_recordset_responsabile) > 0) {
    //Record_url
    //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivi-cdr-personale-responsabile";
    $oGrid->title = "Obiettivi (chiusi) assegnati in qualit&aacute; di responsabile di CDR";
    $oGrid->resources[] = "obiettivo-cdr";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset_responsabile, "obiettivi_obiettivo_cdr");
    $oGrid->order_default = "cdr";
    $oGrid->record_id = "obiettivo-cdr-modify";
    $oGrid->order_method = "labels";
    $oGrid->record_url = $record_url;
    $oGrid->display_navigator = false;
    $oGrid->use_paging = false;

    //operazioni di inserimento ed eliminazione non permesse
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;

    $oGrid->addEvent("on_before_parse_row", "initGrid");

    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_obiettivo_cdr";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oField->label = "id";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice";
    $oField->base_type = "Text";
    $oField->label = "Codice";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "titolo";
    $oField->base_type = "Text";
    $oField->label = "Titolo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr";
    $oField->base_type = "Text";
    $oField->label = "Cdr";
    $oField->order_SQL = "cdr ASC, codice ASC";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "peso";
    $oField->base_type = "Text";
    $oField->label = "Peso";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "desc_periodo";
    $oField->base_type = "Text";
    $oField->label = "Periodo rendicontazione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "raggiungimento";
    $oField->base_type = "Text";
    $oField->label = "Raggiungimento";
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $cm->oPage->addContent($oGrid);
}

//viene visualizzata una notifica nel caso in cui al dipendente non sia assegnato nessun obiettivo
if (!(count($grid_recordset_responsabile) > 0) && !(count($grid_recordset_da_confermare) > 0) && !(count($grid_recordset_confermati) > 0)) {
    $cm->oPage->addContent("<p>Nessun obiettivo chiuso assegnato al dipendente.</p>");
}
$cm->oPage->addContent("</div>");

function initGrid($oGrid) {
    $cm = cm::getInstance();
    $obiettivo_cdr = new ObiettiviObiettivoCdr($oGrid->key_fields["ID_obiettivo_cdr"]->value->getValue());
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    $tipo_obiettivo = new ObiettiviTipo($obiettivo->id_tipo);
    if ($tipo_obiettivo->class !== null) {
        $class = "row_obiettivo_cdr_".$obiettivo_cdr->id;
        $oGrid->row_class = $class;
        $cm->oPage->addContent("<script>$('.".$class."').css('background-color','#".$tipo_obiettivo->class."');</script>");
    } else {
        $oGrid->row_class = "";
    }    
}