<?php
//vengono visualizzati tutti gli obiettivi assegnati all'utente nell'anno
$user = LoggedUser::getInstance();

$personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
$anno = $cm->oPage->globals["anno"]["value"];
$tot_obiettivi_personale = $personale->getPesoTotaleObiettivi($anno);
//aggiunta del parametro per la gestione del record
try {
    $accettazione_obiettivo = ObiettiviAccettazione::factoryFromDipendenteAnno($personale, $anno);

    if (!isset($_REQUEST["keys[ID_accettazione]"]) || $_REQUEST["keys[ID_accettazione]"] != $accettazione_obiettivo->id) {
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
$accettazione_permessa = false;
$obiettivi_cdr_personale_anno = $personale->getObiettiviCdrPersonaleAnno($anno);
$currentModule = core\Modulo::getCurrentModule();
$record_url = MODULES_SITE_PATH . $currentModule->site_path . "/dettagli_obiettivo";

if (count($obiettivi_cdr_personale_anno) > 0) {
    $grid_fields = array(
        "ID",
        "codice",
        "titolo",
        "cdr",
        "peso",
        "data_accettazione",
    );
    $grid_recordset_obiettivi_performance = array();
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

                //vengono considerati solamente gli obiettivi confermati da parte del cdr                                
                if (($obiettivo->data_eliminazione == null) &&
                    ($obiettivo_cdr->data_chiusura_modifiche !== null && strtotime(date("Y-m-d")) >= strtotime($obiettivo_cdr->data_chiusura_modifiche))) {
                    if ($ob_personale->data_accettazione == null) {
                        $accettazione_permessa = true;
                    }
                    
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
                    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
                    $grid_recordset_obiettivi_performance[] = array(
                        $obiettivo_cdr->id,
                        $obiettivo->codice . $coreferente,
                        $obiettivo->titolo,
                        $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
                        number_format($peso_perc, 2) . "%",
                        $ob_personale->data_accettazione,
                    );              
                }
            }
        }
    }

    if (count($grid_recordset_obiettivi_performance) > 0) {
        //visualizzazione della grid dei cdr associati all'obiettivo
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivi-cdr-personale-accettati";
        $oGrid->title = "Obiettivi di performance organizzativa assegnati";
        $oGrid->resources[] = "obiettivo-cdr";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset_obiettivi_performance, "obiettivi_obiettivo_cdr");
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
        $oField->id = "data_accettazione";
        $oField->base_type = "Date";
        $oField->label = "Data accettazione";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $cm->oPage->addContent($oGrid);
    }    
}

//assegnazione individuale obiettivi (se modulo attivo)
if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
    $obiettiviIndividualiModule = \core\Modulo::getActiveModuleById(19);
    //******************************************************************************
    //popolamento della grid tramite array	    
    $grid_fields = array(
        "ID_assegnazione",          
        "cdr",        
        "descrizione",              
        "obiettivo_collegato",
        "data_chiusura",
        "data_accettazione",
    );
    $grid_recordset_assegnazione = array();    
    
    //viene verificato che almeno una delle assegnazioni al personale per la tipologia considerata sia modificabile        
    $filters = array("matricola_personale" => $personale->matricola, "ID_anno_budget" => $anno->id);
    foreach (ObiettiviIndividuali\AssegnazioneIndividuale::getAll($filters) as $assegnazione) {                                                                                               
        if ($assegnazione->datetime_chiusura !== null) {                  
            if ($assegnazione->datetime_accettazione == null) {
                $accettazione_permessa = true;
            }
            
            $anagrafica_cdr_assegnazione = AnagraficaCdr::factoryFromCodice(
                $assegnazione->codice_cdr,
                $dateTimeObject
            );                                        
            if ($anagrafica_cdr_assegnazione == null) {
                $descrizione_cdr_assegnazione = $assegnazione->codice_cdr;
            }                       
            else {
                $descrizione_cdr_assegnazione = $anagrafica_cdr_assegnazione->getDescrizioneEstesa();                        
            }
            $desc_obiettivo_collegato = "Nessuno";                
            $obiettivo_collegato = $assegnazione->getObiettivoCollegato();
            if ($obiettivo_collegato !== null) {
                $desc_obiettivo_collegato = $obiettivo_collegato->titolo;
            }                                          
            $grid_recordset_assegnazione[] = [
                $assegnazione->id,            
                $descrizione_cdr_assegnazione,            
                $assegnazione->descrizione,          
                $desc_obiettivo_collegato,
                $assegnazione->datetime_chiusura,
                $assegnazione->datetime_accettazione,
            ];
        }                                                                                                                                                          
    } 

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivi-assegnazione-individuale";      
    $oGrid->title = "Obiettivi individuali assegnati";          
    $oGrid->resources[] = "obiettivo-individuale";
    $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset_assegnazione, "obind_assegnazione_individuale");
    $oGrid->order_default = "cdr";
    $oGrid->record_id = "obiettivo-individuale-modify";
    $oGrid->order_method = "labels";
    $oGrid->addit_insert_record_param = $oGrid->addit_record_param = "keys[matricola_dipendente]=" . $user->matricola_utente_selezionato;
    $oGrid->record_url = MODULES_SITE_PATH . $obiettiviIndividualiModule->site_path . "/dettagli_obiettivo_individuale";            

    $oGrid->display_navigator = false;
    $oGrid->display_search = false;
    $oGrid->use_paging = false;  

    $oGrid->display_new = false;    
    $oGrid->display_delete_bt = false;
    
    // *********** FIELDS ****************        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_assegnazione";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr";
    $oField->base_type = "Text";
    $oField->label = "Cdr";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Obiettivo individuale";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "obiettivo_collegato";
    $oField->base_type = "Text";
    $oField->label = "Obiettivo collegato";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_accettazione";
    $oField->base_type = "Date";
    $oField->label = "Data accettazione";
    $oGrid->addContent($oField);   
    
    $cm->oPage->addContent($oGrid);
}

if (count($grid_recordset_obiettivi_performance) > 0 || count($grid_recordset_assegnazione) >0) {
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

    function accettaObiettivoCdrPersonale($oRecord) {
        $cm = cm::getInstance();
        $user = LoggedUser::getInstance();
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
        if (OBIETTIVI_MODULO_ASSEGNAZIONE_INDIVIDUALE_ATTIVO == true) {
            $personale = PersonaleObiettivi::factoryFromMatricola($user->matricola_utente_selezionato);
            $anno = $cm->oPage->globals["anno"]["value"];
            $filters = array("matricola_personale" => $personale->matricola, "ID_anno_budget" => $anno->id);            
            foreach (ObiettiviIndividuali\AssegnazioneIndividuale::getAll($filters) as $assegnazione) {                                                                            
                if ($assegnazione->datetime_chiusura !== null) {  
                    if ($assegnazione->datetime_accettazione == null) {
                        $assegnazione->datetime_accettazione = date("Y-m-d H:i:s");
                        $assegnazione->save(["datetime_accettazione"]);
                    }                    
                }                                                                                                                                                          
            }
        }
        //viene forzato il redirect per ovviare ad un problema di explorer		
        $path_info_parts = explode("/", $cm->path_info);
        $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
        $url = FF_SITE_PATH . $path_info . "accettazione_obiettivi_individuali";
        ffRedirect($url . "?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    }
}

//******************************************************************************
//obiettivi dei cdr i quali il dipendente è responsabile sul piano selezionato
$grid_fields = array(
    "ID",
    "codice",
    "titolo",
    "cdr",
    "peso",
    "data_chiusura",
);
$grid_recordset_responsabile = array();
$obiettivi_responsabile_assegnati = false;
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date);

$cdr_resp_piano = $personale->getCdrResponsabilitaPiano($piano_cdr, $dateTimeObject);
foreach ($cdr_resp_piano as $key=>$cdr_resp){    
    $cdr_resp_piano[$key]["livello"] = $cdr_resp["cdr"]->getLivelloGerarchico();
}
//definito in classe Personale
usort($cdr_resp_piano, "cdrLevelCmp");

foreach ($cdr_resp_piano as $cdr_resp) {     
    $cdr_resp_anno = AnagraficaCdrObiettivi::factoryFromCodice($cdr_resp["cdr"]->codice, $dateTimeObject);
    $tipo_cdr = new TipoCdr($cdr_resp_anno->id_tipo_cdr);
    $cdr_resp_anno_desc = $cdr_resp_anno->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_resp_anno->descrizione;
    $grid_recordset_responsabile[$cdr_resp_anno_desc] = array();
    $peso_tot_obiettivi_cdr = $cdr_resp_anno->getPesoTotaleObiettivi($anno);
    foreach ($cdr_resp_anno->getObiettiviCdrAnno($anno) as $ob_cdr_resp) {
        //recupero del cdr											
        $obiettivo = new ObiettiviObiettivo($ob_cdr_resp->id_obiettivo);
        //rendicontazione dell'ultimo periodo attivo    
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

                $grid_recordset_responsabile[$cdr_resp_anno_desc][] = array(
                    $ob_cdr_resp->id,
                    $obiettivo->codice . $coreferente,
                    $obiettivo->titolo,
                    $cdr_resp_anno_desc,                    
                    (int)$peso . "%",
                    $ob_cdr_resp->data_chiusura_modifiche,
                );   
                $obiettivi_responsabile_assegnati = true;           
            }
        }
    }
}
if ($obiettivi_responsabile_assegnati == true) {    
    $i=0;
    foreach ($grid_recordset_responsabile as $desc=>$records) {       
        if (count($grid_recordset_responsabile[$desc]) > 0){
            $oGrid = ffGrid::factory($cm->oPage);
            $oGrid->id = "obiettivi-cdr-personale-responsabile-".++$i;
            $oGrid->title = "Obiettivi (chiusi) assegnati in qualit&aacute; di responsabile di CDR: '".$desc."'";
            $oGrid->resources[] = "obiettivo-cdr";
            $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $records, "obiettivi_obiettivo_cdr");
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
            $oField->id = "data_chiusura";
            $oField->base_type = "Date";
            $oField->label = "Data chiusura";
            $oGrid->addContent($oField);            

            // *********** ADDING TO PAGE ****************
            $cm->oPage->addContent($oGrid);
        }
    }            
}

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