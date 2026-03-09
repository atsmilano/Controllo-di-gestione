<?php
use ObiettiviIndividuali\AssegnazioneIndividuale;
use LoggedUser;
use Personale;

$user = LoggedUser::getInstance();

if ($user->hasPrivilege("obiettivi_individuali_admin")
|| $user->hasPrivilege("resp_cdr_selezionato")) {
    $edit = true;
}
else {
    if ($user->hasPrivilege("resp_padre_ramo_cdr_selezionato")) {
        $edit = false;        
    }
    else {
        ffErrorHandler::raise("L'utente non ha i privilegi per visualizzare i dati.");
    }
}

$current_date_time = new Datetime(date("Y-m-d H:i:s"));
$anno = $cm->oPage->globals["anno"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$cdr_selezionato = $cm->oPage->globals["cdr"]["value"];

//recupero parametro matricola dipendente
if (isset($_REQUEST["keys[matricola_dipendente]"]) && isset($_REQUEST["keys[tipo_assegnazione]"])) {
    $matricola_dipendente = $_REQUEST["keys[matricola_dipendente]"];
    $tipo_assegnazione = $_REQUEST["keys[tipo_assegnazione]"];

    //******************************************************************************
    //popolamento della grid tramite array	    
    $grid_fields = array(
        "ID_assegnazione",  
        "matricola_dipendente",
        "tipo_assegnazione",        
        "descrizione",              
        "obiettivo_collegato",
        "datetime_chiusura",
        "datetime_accettazione",
    );
    $grid_recordset_assegnazione = array();
    $n_assegnazioni_cdr_selezionato = 0;        
    
    //viene verificato che almeno una delle assegnazioni al personale per la tipologia considerata sia modificabile        
    foreach (AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInDataByMatricola($cdr_selezionato, $data_riferimento, $matricola_dipendente, $tipo_assegnazione) as $assegnazione_individuale_personale) {                                        
        $id_assegnazione = null;
        if (!$assegnazione_individuale_personale["personale_visualizzato"]["assegnabile"]) {
            ffErrorHandler::raise("L'utente non ha i privilegi per effettuare l'assegnazione degli obiettivi individuali al dipendente.");
        }
        else {   
            foreach ($assegnazione_individuale_personale["assegnazioni"] as $assegnazione) {               
                $id_assegnazione = $assegnazione["assegnazione_individuale"]->id;              
                if ($assegnazione["assegnazione_individuale"]->codice_cdr !== $cdr_selezionato->codice) {                                                
                    $anagrafica_cdr_assegnazione = AnagraficaCdr::factoryFromCodice(
                        $assegnazione["assegnazione_individuale"]->codice_cdr,
                        $data_riferimento
                    );                                        
                    if ($anagrafica_cdr_assegnazione == null) {
                        $descrizione_assegnazione = "Obiettivo assegnato da Cdr ". $assegnazione["assegnazione_individuale"]->codice_cdr."\n";
                    }                       
                    else {
                        $descrizione_assegnazione = "Obiettivo assegnato da Cdr "
                                                    .$anagrafica_cdr_assegnazione->getDescrizioneEstesa()."\n";                        
                    }
                    $grid_recordset_assegnazione[$tipo_assegnazione][] = [
                        null,
                        $matricola_dipendente,
                        $tipo_assegnazione,                          
                        $descrizione_assegnazione,          
                        "",
                        "",
                        "", 
                    ];     
                }
                else {          
                    $desc_obiettivo_collegato = "Nessuno";                
                    $obiettivo_collegato = $assegnazione["assegnazione_individuale"] ->getObiettivoCollegato();
                    if ($obiettivo_collegato !== null) {
                        $desc_obiettivo_collegato = $obiettivo_collegato->titolo;
                    }
                    
                    $n_assegnazioni_cdr_selezionato++;
                    $descrizione_assegnazione = $assegnazione["assegnazione_individuale"]->descrizione;  
                    $grid_recordset_assegnazione[$tipo_assegnazione][] = [
                        $id_assegnazione,
                        $matricola_dipendente,
                        $tipo_assegnazione,
                        $descrizione_assegnazione,          
                        $desc_obiettivo_collegato,
                        $assegnazione["assegnazione_individuale"]->datetime_chiusura,
                        $assegnazione["assegnazione_individuale"]->datetime_accettazione, 
                    ];              
                }                
            }     
        }                                                                                                             
    }   
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: matricola personale / tipo assegnazione.");
}

$currentModule = core\Modulo::getCurrentModule();
$personale = Personale::factoryFromMatricola($matricola_dipendente);
$anagrafica_cdr_selezionato = new AnagraficaCdr($cdr_selezionato->id_anagrafica_cdr);
$cdr_resp_anno_desc = $anagrafica_cdr_selezionato->getDescrizioneEstesa();

$desc_tipo_valutazione = $personale->cognome." ".$personale->nome." (".$personale->matricola.") - Obiettivi individuali ricevuti in qualità di ";
switch ($tipo_assegnazione){
    case "resp-cdr-figli":
        $desc_tipo_valutazione .= "responsabile di Cdr afferente nell'anno ".$anno->descrizione." al CdR: '".$cdr_resp_anno_desc."'";
    break;
    case "personale-cdr-attivo":
        $desc_tipo_valutazione .= "dipendente";
    break;
    case "personale-cdr-cessato":
        $desc_tipo_valutazione .= "dipendente assegnato e cessato nell'anno ".$anno->descrizione;
    break;
}
if (OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR > 1) {
    $desc_tipo_valutazione .= " (".$n_assegnazioni_cdr_selezionato."/".OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR.")";
}
                 
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "dettaglio-obiettivi-assegnazione-individuale-".$tipo_assegnazione;      
$oGrid->title = $desc_tipo_valutazione;          
$oGrid->resources[] = "obiettivo-individuale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset_assegnazione[$tipo_assegnazione], "obind_assegnazione_individuale");
$oGrid->order_default = "descrizione";
$oGrid->record_id = "obiettivo-individuale-modify";
$oGrid->order_method = "labels";
$oGrid->record_url = MODULES_SITE_PATH . $currentModule->site_path . "/dettagli_obiettivo_individuale";
//$oGrid->record_insert_url = $oGrid->record_url;
$oGrid->addit_insert_record_param = "keys[matricola_dipendente]=" . $matricola_dipendente . "&" . "keys[tipo_assegnazione]=" . $tipo_assegnazione;
$oGrid->display_navigator = false;
$oGrid->display_search = false;
$oGrid->use_paging = false;  
$oGrid->full_ajax = true; 

//operazioni di inserimento
if (!$edit || (OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR > 0 && $n_assegnazioni_cdr_selezionato >= OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR)) {
    $oGrid->display_new = false;
}
else {
    $oGrid->display_new = true;
}  
$oGrid->addEvent("on_before_parse_row", "checkEdit");        

// *********** FIELDS ****************        
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_assegnazione";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_dipendente";
$oField->data_source = "matricola_dipendente";
$oField->base_type = "Text";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo_assegnazione";
$oField->data_source = "tipo_assegnazione";
$oField->base_type = "Text";
$oGrid->addKeyField($oField);

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
$oField->id = "datetime_chiusura";
$oField->base_type = "Datetime";
$oField->label = "Data chiusura";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "datetime_accettazione";
$oField->base_type = "Datetime";
$oField->label = "Data accettazione";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);  

function checkEdit ($oGrid) {
    //vengono verificati i privilegi di modifica dell'utente
    $user = LoggedUser::getInstance();
    $modificabile = false;
    if ($user->hasPrivilege("resp_cdr_selezionato")) {
        $cm = cm::getInstance();
        $cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
        $data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

        $matricola_dipendente = $oGrid->key_fields["matricola_dipendente"]->getValue("Text", true);
        //vengono garantiti i privilegi di modifica solamente per le assegnazioni effettuate per il cdr           
        $tipo_assegnazione = str_replace("dettaglio-obiettivi-assegnazione-individuale-","",$oGrid->id);                                 
        //verrà recuperata al più un oggetto AssegnazioniIndividualiPersonaleCdrInDataByMatricola
        foreach (AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInDataByMatricola($cdr_selezionato, $data_riferimento, $matricola_dipendente, $tipo_assegnazione) as $assegnazione_individuale_personale) {
            //viene verificato che l'assegnazione sia di competenza del cdr selezionato            
            foreach ($assegnazione_individuale_personale["assegnazioni"] as $assegnazione) {  
                if ($oGrid->key_fields["ID_assegnazione"]->value->getValue() == $assegnazione["assegnazione_individuale"]->id) {                   
                    if ($assegnazione["assegnazione_individuale"]->codice_cdr == $cdr_selezionato->codice) {                        
                        $modificabile = true;                        
                        break 2;
                    }
                }
            }                                               
        }           
    }
    $oGrid->display_edit_url = $modificabile;
    //condizione inutile, applicata per eliminare problema di aggiornamento dovuto dal tasto di eliminazione della grid
    if ($modificabile == true) {
        if ($assegnazione["assegnazione_individuale"]->datetime_chiusura !== null || $assegnazione["assegnazione_individuale"]->datetime_accettazione !== null) {
            $oGrid->display_delete_bt = false;
        }
        else {
            $oGrid->display_delete_bt = false;
        }
    }
    else {
        $oGrid->display_delete_bt = false;
    }      
}