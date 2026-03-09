<?php
use ObiettiviIndividuali\AssegnazioneIndividuale;
use ObiettiviObiettivo;
use LoggedUser;
use PersonaleObiettivi;
use PianoCdr;
use TipoPianoCdr;

$current_date_time = new Datetime(date("Y-m-d H:i:s"));
$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
$user = LoggedUser::getInstance();

//verifica privilegi
$edit_admin = $edit_responsabile = $edit_valutato = $edit_note = $view_rendicontazione = $view_note = false;

if ($user->hasPrivilege("obiettivi_individuali_admin"))  {
    $edit_admin = true;
}

//recupero parametro matricola dipendente
if (isset($_REQUEST["keys[matricola_dipendente]"])) {
    $matricola_dipendente = $_REQUEST["keys[matricola_dipendente]"];           
    $personale = PersonaleObiettivi::factoryFromMatricola($matricola_dipendente);    
    
    if (isset($_REQUEST["keys[tipo_assegnazione]"])) {
        $tipo_assegnazione = $_REQUEST["keys[tipo_assegnazione]"];

        //verifica che la matricola personale sia gestibile dal responsabile cdr selezionato
        //(maticola in elenco di personale assegnabile)
        $assegnazioni_individuali_personale = AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInDataByMatricola($cdr_selezionato, $data_riferimento, $personale->matricola, $tipo_assegnazione);            
        
        if (!count($assegnazioni_individuali_personale)) {
            ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione dell'obiettivo.");
        }        
        //se dipendente non assegnabile viene generato errore       
        if ($assegnazioni_individuali_personale[0]["personale_visualizzato"]["assegnabile"] == false) {
            ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione dell'obiettivo.");
        }    
        //in caso di assegnazione già presente viene verificata la coerenza con la matricola passata
        if (isset($_REQUEST["keys[ID_assegnazione]"]) != null
        &&  $_REQUEST["keys[ID_assegnazione]"]) {          
            $assegnazione_individuale = null;
            foreach ($assegnazioni_individuali_personale[0]["assegnazioni"] as $assegnazione) {
                if ($assegnazione["assegnazione_individuale"]->id == $_REQUEST["keys[ID_assegnazione]"]) {
                    $assegnazione_individuale = $assegnazione["assegnazione_individuale"];
                }
            }                                        
            if ($assegnazione_individuale == null) {
                ffErrorHandler::raise("Errore nel passaggio dei parametri: matricola personale non coerente con ID_assegnazione.");
            }                  
            //verifica sulla possibilità di accedere all'assegnazione in base al cdr assegnatario
            if ($assegnazione_individuale->codice_cdr !== $cdr_selezionato->codice) {
                ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione dell'obiettivo.");
            }
            //assegnazione modificabile solamente se non risulta chiusa
            if ($assegnazione_individuale->datetime_chiusura == null && $assegnazione_individuale->datetime_accettazione == null) {             
                $edit_responsabile = true;         
            }             
            else {
                $view_rendicontazione = true;
                $view_note = true;
            }
            //note responsabile inseribili solamente nel caso di assegnazione chiusa
            if ($assegnazione_individuale->datetime_chiusura_modifiche == null){
                $edit_note = true;
            }                      
        }  
        else {
            $edit_responsabile = true;                        
        }  
        //il cdr prevalente è quello di riferimento e discrimina fra cdr assegnazione o di responsabilità
        //a seconda che il personale sia dipendente o responsabile di cdr figlio
        $cdr_riferimento = $assegnazioni_individuali_personale[0]["personale_visualizzato"]["cdr_prevalente"];        
    }
    else {
        $tipo_assegnazione = null;             
        if (isset($_REQUEST["keys[ID_assegnazione]"]) != null
        &&  $_REQUEST["keys[ID_assegnazione]"]) { 
            $assegnazione_individuale = new AssegnazioneIndividuale($_REQUEST["keys[ID_assegnazione]"]);
            if ($user->matricola_utente_selezionato == $assegnazione_individuale->matricola_personale) {
                $view_rendicontazione = true;
                $view_note = true;
                $edit_valutato = true;
            }
        }
        if ($assegnazione_individuale == null) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri: matricola personale non coerente con ID_assegnazione.");
        }  
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: matricola personale.");
}

//in caso di responsabile di cdr viene visualizzato il cdr di competenza
$record_desc = "";
if ($tipo_assegnazione == "resp-cdr-figli") {
    $found = false;
    $cdr_desc = "";
    $cdr_figli_selezionato = $cdr_selezionato->getFigli();
    foreach ($cdr_selezionato->getFigli() as $cdr_figlio) {
        $found = true;
        $responsabile = $cdr_figlio->getResponsabile($data_riferimento);
        $cdr_desc .= "";
        if ($responsabile->matricola_responsabile == $matricola_dipendente) {
            if (strlen ($cdr_desc)) {
                $cdr_desc .= " / ";
            }            
            $anagrafica_cdr_figlio = new AnagraficaCdr ($cdr_figlio->id_anagrafica_cdr);
            $cdr_desc .= $anagrafica_cdr_figlio->getDescrizioneEstesa();
        }
    }
    if ($found == true) {
        $record_desc = " - Responsabile CdR " . $cdr_desc;
    }
    else  {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: dipendente non responsabile per Cdr figlio.");
    }    
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-individuale-modify";
$oRecord->title = $assegnazione_individuale !== null ? "Modifica ": "Nuova "."assegnazione individuale: ".$personale->cognome." ".$personale->nome." (".$personale->matricola.")" . $record_desc;
$oRecord->resources[] = "obiettivo-individuale";
$oRecord->src_table  = "obind_assegnazione_individuale";
if (!$edit_admin && !$edit_responsabile && !$edit_valutato && !$edit_note) {    
    $oRecord->allow_insert = false;
    $oRecord->allow_update = false;
    $oRecord->allow_delete = false;
}
else {
    if ($tipo_assegnazione == null) {
        $oRecord->allow_insert = false;
        $oRecord->allow_delete = false;
    }
    else {
        $n_obiettivi_assegnati_cdr = 0;        
        foreach ($assegnazioni_individuali_personale[0]["assegnazioni"] as $assegnazione) {            
            if ($assegnazione["assegnazione_individuale"]->codice_cdr == $cdr_selezionato->codice) {
                $n_obiettivi_assegnati_cdr++;
            }
        }        
        if (OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR > 0 && $n_obiettivi_assegnati_cdr >= OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR) {                        
            $oRecord->allow_insert = false;
        }        
        if ($assegnazione_individuale !== null && ($assegnazione_individuale->datetime_chiusura !== null || $assegnazione_individuale->datetime_accettazione !== null)) {
            $oRecord->allow_delete = false;            
        }          
    }    
    if ($edit_valutato == true){        
        if ($assegnazione_individuale !== null && $assegnazione_individuale->datetime_accettazione !== null && $assegnazione_individuale->datetime_chiusura_rendicontazione == null) {
            $oRecord->allow_update = true;
        }
        else {
            $edit_valutato = false;
            if (!$edit_admin) {
                $oRecord->allow_update = false;
            }            
        }       
    }  
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_assegnazione";
$oField->data_source  ="ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields["ID_anno_budget"] = $anno->id;
$oRecord->insert_additional_fields["codice_cdr"] = $cdr_selezionato->codice;
$oRecord->insert_additional_fields["matricola_personale"] = new ffData($personale->matricola, "Text");
$oRecord->insert_additional_fields["matricola_responsabile_assegnazione"] = new ffData($user->matricola_utente_selezionato, "Text");
$oRecord->insert_additional_fields["matricola_inserimento"] = new ffData($user->matricola_utente_collegato, "Text");
$oRecord->insert_additional_fields["datetime_inserimento"] = $current_date_time->format("Y-m-d H:i:s");

$oRecord->addContent(null, true, "assegnazione");
$oRecord->groups["assegnazione"]["title"] = "Assegnazione";

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->label = "Obiettivo individuale (L'obiettivo deve essere facilmente comprensibile, oggettivamente misurabile e relativo ad un intervallo di tempo definito. Il relativo livello di performance raggiunto sarà oggetto della scheda di valutazione del dipendente)";
if ($edit_responsabile == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
else {
    $oField->required = true;;
}
$oRecord->addContent($oField, "assegnazione");

//obiettivi cdr
$obiettivi_cdr = array();

//verifica che il personale abbia obiettivi_cdr_associati
$no_obiettivi = true;
$obiettivi_multipairs = array();
//per i responsabili vengono considerati tutti gli obiettivi assegnati ai cdr di competenza se figli del cdr selezionato
if ($tipo_assegnazione == "resp-cdr-figli") {
    $piano_cdr = new PianoCdr($cdr_selezionato->id_piano_cdr);
    $tipo_piano = new TipoPianoCdr($piano_cdr->id_tipo_piano_cdr);
    $obiettivi_cdr_personale = $personale->getObiettiviCdrReponsabilitaData($anno, $data_riferimento, $tipo_piano, true);        
    foreach ($obiettivi_cdr_personale as $obiettivo_cdr) {        
        $is_cdr_figlio = false;
        foreach ($cdr_figli_selezionato as $cdr_figlio) {            
            if ($cdr_figlio->codice == $obiettivo_cdr["obiettivo_cdr"]->codice_cdr) {
                $is_cdr_figlio = true;                
                break;
            }
        }        
        if ($is_cdr_figlio == true) {
            $no_obiettivi = false;             
            $obiettivo = $obiettivo_cdr["obiettivo"];      
            $obiettivi_multipairs[] = array(
                new ffData($obiettivo->id, "Number"),
                new ffData($obiettivo->codice . " - " . $obiettivo->titolo, "Text")
            );
        }                        
    }
}
//altrimenti vengono considerati tutti gli obiettivi assegnati
else {
    $obiettivi_cdr_personale = $personale->getObiettiviCdrPersonaleAnno($anno);
    if (count($obiettivi_cdr_personale)) {
        foreach ($obiettivi_cdr_personale as $obiettivo_cdr_personale) {  
            if($obiettivo_cdr_personale->data_eliminazione == null) {
                $obiettivo_cdr = new ObiettiviObiettivoCdr($obiettivo_cdr_personale->id_obiettivo_cdr);            
                if ($obiettivo_cdr->codice_cdr == $cdr_selezionato->codice) {                
                    $no_obiettivi = false;
                    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
                    if ($obiettivo->data_eliminazione == null) {
                        $obiettivi_multipairs[] = array(
                            new ffData($obiettivo->id, "Number"),
                            new ffData($obiettivo->codice . " - " . $obiettivo->titolo, "Text")
                        );
                    }
                }
            }
        }
    }
}

//in caso non ci siano assegnazioni vengono considerati tutti gli obiettivi del cdr
if ($no_obiettivi == true) {
    $anagrafica_cdr_obiettivo = AnagraficaCdrObiettivi::factoryFromCodice($cdr_selezionato->codice, $data_riferimento);
            
    if ($anagrafica_cdr_obiettivo !== null) {
        foreach ($anagrafica_cdr_obiettivo->getObiettiviCdrAnno($anno) as $obiettivo_cdr) {
            $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
            $obiettivi_multipairs[] = array(
                new ffData($obiettivo->id, "Number"),
                new ffData($obiettivo->codice . " - " . $obiettivo->titolo, "Text")
            );
        }
    }
}
//viene comunque considerato l'eventuale obiettivo collegato
if ($assegnazione_individuale !== null) {
    try {
        $obiettivo = new ObiettiviObiettivo($assegnazione_individuale->id_obiettivo);   
        $obiettivi_multipairs[] = array(
            new ffData($obiettivo->id, "Number"),
            new ffData($obiettivo->codice . " - " . $obiettivo->titolo, "Text")
        );
    } catch (\Throwable $th) {
        //throw $th;
    }     
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $obiettivi_multipairs;
$oField->label = "Obiettivo Collegato";
$oField->multi_select_one_label = "Nessun obiettivo associato";
if ($edit_responsabile == false){
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "assegnazione");

if ($edit_admin == true) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "datetime_chiusura";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura";
    $oField->widget = "datepicker";      
    $oRecord->addContent($oField, "assegnazione");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "datetime_accettazione";
    $oField->base_type = "Date";
    $oField->label = "Data accettazione";
    $oField->widget = "datepicker";      
    $oRecord->addContent($oField, "assegnazione");
}

$oRecord->addContent(null, true, "rendicontazione-group");
$oRecord->groups["rendicontazione-group"]["title"] = "Rendicontazione";

if ($view_rendicontazione) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "rendicontazione";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Rendicontazione obiettivo individuale";
    if ($edit_valutato == false){
        $oField->control_type = "label";
        $oField->store_in_db = false;
        if (!isset($assegnazione_individuale) || !strlen($assegnazione_individuale->rendicontazione)) {
            $oField->data_type = "";
            $oField->default_value = new ffData("Nessuna rendicontazione definita", "Text");
        }
    }
    else {
        //$oField->required = true;;
    }
    $oRecord->addContent($oField, "rendicontazione-group");
}

if ($edit_admin == true) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "datetime_chiusura_rendicontazione";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura rendicontazione";
    $oField->widget = "datepicker";      
    $oRecord->addContent($oField, "rendicontazione-group");
}

$oRecord->addContent(null, true, "note-responsabile-group");
$oRecord->groups["note-responsabile-group"]["title"] = "Note responsabile";

if ($view_note) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "note_responsabile";
    $oField->base_type = "Text";
    $oField->extended_type = "Text";
    $oField->label = "Note alla rendicontazione del responsabile";
    if ($edit_note == false){
        $oField->control_type = "label";
        $oField->store_in_db = false;          
        if (!isset($assegnazione_individuale) || !strlen($assegnazione_individuale->note_responsabile)) {
            $oField->data_type = "";
            $oField->default_value = new ffData("Nessuna nota definita", "Text");
        }
    }
    $oRecord->addContent($oField, "note-responsabile-group");
}

if ($edit_admin == true) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "datetime_chiusura_modifiche";
    $oField->base_type = "Date";
    $oField->label = "Data chiusura modifiche";
    $oField->widget = "datepicker";      
    $oRecord->addContent($oField, "note-responsabile-group");
}

$oRecord->addEvent("on_do_action", "checkConditions");
$cm->oPage->addContent($oRecord);

$cm->oPage->AddContent("
    <script>        
        ff.struct.get('comps').unset('dettaglio-obiettivi-assegnazione-individuale-".$tipo_assegnazione."');"  
        ."ff.ffPage.dialog.get('obiettivo-individuale-modify').params.callback =" 
        ."'ff.ffPage.dialog.close(\'obiettivo-dipendente-modify\');"        
        ."ff.ffGrid.dialogOpen(\'obiettivo-dipendente-modify\');"            
        ."';        
    </script>
");

function checkConditions($oRecord, $frmAction) {         
    $cm = cm::getInstance();
    $anno = $cm->oPage->globals["anno"]["value"];
    $cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
    $user = LoggedUser::getInstance();
    $matricola_dipendente = $_REQUEST["keys[matricola_dipendente]"];           
    $personale = PersonaleObiettivi::factoryFromMatricola($matricola_dipendente);

    $filters = array("matricola_personale" => $personale->matricola, "codice_cdr" => $cdr_selezionato->codice, "ID_anno_budget" => $anno->id);
    $assegnazioni_individuali_personale = AssegnazioneIndividuale::getAll($filters);
    
    if ($user->hasPrivilege("obiettivi_individuali_admin"))  {
        $edit_admin = true;
    }

    switch ($frmAction) {                 
        case "insert":                                    
            if (OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR > 0 && count($assegnazioni_individuali_personale) >= OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR) {
                CoreHelper::setError($oRecord, 
                "Raggiunto il numero massimo di assegnazioni possibili (".OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR.") per il dipendente per il CdR");
                $oRecord->skip_action = true;
            }
        break;
        case "update":    
            $edit_valutato = false;                 
            
            $assegnazione_individuale = new AssegnazioneIndividuale($oRecord->key_fields["ID_assegnazione"]->value->getValue());                 
            //verifica sulla possibilità di accedere all'assegnazione in base al cdr assegnatario            
            
            if ($user->matricola_utente_selezionato == $assegnazione_individuale->matricola_personale && $assegnazione_individuale->datetime_chiusura_rendicontazione == null) {
                $edit_valutato = true;                
            }
            //TODO discriminare campi compilabili rispetto alle date
            if (!$edit_admin && !$edit_valutato && $assegnazione_individuale->codice_cdr !== $cdr_selezionato->codice) {
                ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione dell'obiettivo.");
            }
            //assegnazione modificabile solamente se non risulta chiusa e solo da parte del valutato o dell'amministratore
            if ($assegnazione_individuale->datetime_chiusura_modifiche !== null) {             
                if (($edit_valutato && !edit_admin) || (!$edit_valutato && !$edit_admin)) {                        
                    CoreHelper::setError($oRecord, 
                    "Non è possibile modificare un'assegnazione chiusa");
                    $oRecord->skip_action = true;   
                }                                                      
            } 
            elseif ($edit_valutato && $assegnazione_individuale->datetime_chiusura_rendicontazione !== null) {
                CoreHelper::setError($oRecord, 
                    "Non è possibile modificare una rendicontazione chiusa");
                    $oRecord->skip_action = true;   
            }                
            break;
        case "delete":
        case "confirmdelete":
            if ($user->hasPrivilege("obiettivi_individuali_admin"))  {
                $edit_admin = true;
            }
            $assegnazione_individuale = new AssegnazioneIndividuale($oRecord->key_fields["ID_assegnazione"]->value->getValue());
            foreach ($assegnazioni_individuali_personale as $assegnazione) {
                if ($assegnazione->id == $_REQUEST["keys[ID_assegnazione]"]) {
                    $assegnazione_individuale = $assegnazione;
                }
            }                                        
            if (!$edit_admin && $assegnazione_individuale == null) {
                ffErrorHandler::raise("Errore nel passaggio dei parametri: matricola personale non coerente con ID_assegnazione.");
            }                  
            //verifica sulla possibilità di accedere all'assegnazione in base al cdr assegnatario
            if (!$edit_admin && $assegnazione_individuale->codice_cdr !== $cdr_selezionato->codice) {
                ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione dell'obiettivo.");
            }
            //assegnazione modificabile solamente se non risulta chiusa
            if (!$edit_admin && ($assegnazione_individuale->datetime_chiusura !== null || $assegnazione_individuale->datetime_accettazione !== null)) {             
                CoreHelper::setError($oRecord, 
                "Non è possibile eliminare un'assegnazione chiusa");
                $oRecord->skip_action = true;
            } 
            break;
    }    
}