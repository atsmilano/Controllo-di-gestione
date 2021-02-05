<?php
$user = LoggedUser::Instance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");
$tipo_piano = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date);

//il report visualizzerà dati differenti a seconda che l'utnte sia amministratore o responsabile CdR
//predisposizione dati per la grid	
//popolamento della grid tramite array		
//costruzione dell'array dei cdr da verificare
//admin visualizza tutti i cdr mentre responsabile cdr visualizza il proprio ramo gerarchico
$grid_fields = array("ID");
$cdr_piano = $piano_cdr->getCdr();
//vengono recuperati tutti i responsbili per i cdr da verificare e dell'anagrafica corrente per ottimizzare il numero di query
$responsabili_cdr_data = ResponsabileCdr::getResponsabiliCdrInData($dateTimeObject);
foreach ($cdr_piano as $key => $cdr) {
    $cdr_piano[$key]->responsabile = $cdr->getResponsabile($dateTimeObject);    
}
if ($user->hasPrivilege("cdr_view_all")) {
    $view_all = true;
    array_push($grid_fields, "padre_strategico");
    $cdr_to_check = $cdr_piano;                
}
else {
    $view_all = false; 
    $cdr_selezionato = new Cdr($cm->oPage->globals["cdr"]["value"]->id);
    $cdr_to_check = array($cdr_selezionato);
    foreach ($cdr_selezionato->getGerarchia() as $cdr) {        
        $cdr["cdr"]->responsabile = $cdr["cdr"]->getResponsabile($dateTimeObject);
        $cdr_to_check[] = $cdr["cdr"];
    }     
    unset ($cdr);
}
array_push($grid_fields, "cdr_padre", "cdr");

//estrazione di tutti gli obiettivi dell'anno
//si opta per questa soluzione piuttosto che per il recupero degli obiettivi di ogni singolo cdr per ottimizzazione del numero di query
//soluzione db adottata esclusivamente per ottimizzazione risorse
$ob_obiettivi_cdr_anno = array();
$db = ffDb_Sql::factory();
$sql = "
    SELECT 
        obiettivi_obiettivo_cdr.*  
    FROM 
        obiettivi_obiettivo 
    INNER JOIN obiettivi_obiettivo_cdr
        ON obiettivi_obiettivo.ID = obiettivi_obiettivo_cdr.ID_obiettivo
    WHERE     
        (obiettivi_obiettivo.data_eliminazione is null || obiettivi_obiettivo.data_eliminazione <> '0000-00-00')
        AND
        (obiettivi_obiettivo_cdr.data_eliminazione is null || obiettivi_obiettivo.data_eliminazione <> '0000-00-00')
        AND
        obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id);
$db->query($sql);
if ($db->nextRecord()) {
    do {            
        $ob_cdr_obj = new ObiettiviObiettivoCdr();                

        $ob_cdr_obj->id = $db->getField("ID", "Number", true);
        $ob_cdr_obj->id_obiettivo = $db->getField("ID_obiettivo", "Number", true);
        $ob_cdr_obj->codice_cdr = $db->getField("codice_cdr", "Text", true);
        $ob_cdr_obj->codice_cdr_coreferenza = $db->getField("codice_cdr_coreferenza", "Text", true);
        if ($db->getField("ID_tipo_piano_cdr", "Number", true) == 0) {
            $ob_cdr_obj->id_tipo_piano_cdr = null;
        } else {
            $ob_cdr_obj->id_tipo_piano_cdr = $db->getField("ID_tipo_piano_cdr", "Number", true);
        }
        $ob_cdr_obj->peso = $db->getField("peso", "Text", true);
        $ob_cdr_obj->azioni = $db->getField("azioni", "Text", true);
        $ob_cdr_obj->id_parere_azioni = $db->getField("ID_parere_azioni", "Number", true);
        $ob_cdr_obj->note_azioni = $db->getField("note_azioni", "Text", true);
        //data_chiusura_modifiche
        $ob_cdr_obj->data_chiusura_modifiche = CoreHelper::getDateValueFromDB($db->getField("data_chiusura_modifiche", "Date", true));
        $ob_cdr_obj->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));       

        $ob_obiettivi_cdr_anno[]= $ob_cdr_obj;        
    } while ($db->nextRecord());
}

//recupero di tutti gli obiettivi_cdr_personale dell'anno
//vengono recuperate solamente le matricole del personale che ha almeno un obiettivo assegnato
$ob_personale_associati = array();
$sql = "
    SELECT DISTINCT obiettivi_obiettivo_cdr_personale.matricola_personale
    FROM obiettivi_obiettivo_cdr_personale
        INNER JOIN obiettivi_obiettivo_cdr 
            ON obiettivi_obiettivo_cdr_personale.ID_obiettivo_cdr = obiettivi_obiettivo_cdr.ID
        INNER JOIN obiettivi_obiettivo
            ON obiettivi_obiettivo_cdr.ID_obiettivo = obiettivi_obiettivo.ID
    WHERE 
        (obiettivi_obiettivo_cdr_personale.data_eliminazione is null || obiettivi_obiettivo_cdr_personale.data_eliminazione <> '0000-00-00')
        AND
        obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id) . "
";
$db->query($sql);
if ($db->nextRecord()) {
    do {
        $ob_personale_associati[] = $db->getField("matricola_personale", "Text", true);        
    } while ($db->nextRecord());
}

//vengono filtrati tutti i cdr da verificare
$cdr_report_obiettivi = array();
$cdr_report_peso = array();
$cdr_report_personale = array();
//array per i filtri nella grid
$articolazioni_organizzative_obiettivi_filter = array();
$articolazioni_organizzative_peso_filter = array();
$cdr_padri_obiettivi_filter = array();
$cdr_padri_peso_filter = array();
$cdr_padri_personale_filter = array();
$cdr_filter = array();

function getCdrPadre($id_padre, $cdr_piano) {
    foreach ($cdr_piano as $cdr) {
        if ($cdr->id == $id_padre) {
            return $cdr;
        }
    }
}

//viene estratta l'anagrafe del personale per ottimizzazioni
$personale = array();
foreach (PersonaleObiettivi::getAll() as $dipendente) {
    $personale[$dipendente->matricola] = $dipendente;
}
//contatore per generare id univoci (non utilizzati)
$i=0;
foreach ($cdr_to_check as $cdr) {
    //vengono verificati i cdr per l'inserimento nei report
    //vengono considerati solamente i cdr con almeno un dipendente assegnato oppure di responsabilità diretta    
    //recupero del personale cdt        
    $to_check = false;
    $personale_cdr = $cdr->getPersonaleCdcAfferentiInData($date);   
    if (count($personale_cdr)) {
        //dal personale vengono eliminati i responsabili e rieffettuato il controllo
        foreach ($personale_cdr as $key => $dipendente_cdr) {
            $is_responsabile = false;
            foreach($responsabili_cdr_data as $responsabile_cdr){
                if ($responsabile_cdr->matricola_responsabile == $dipendente_cdr->matricola_personale) {
                    $is_responsabile = true;
                    break;
                }
            }
            if ($is_responsabile){
                unset($personale_cdr[$key]);
            }
        }
        if (count($personale_cdr)) {
            $to_check = true;
        }        
    }
    else {
        //viene verificato che il cdr sia di responsabilità diretta
        $resp_diretta = false;
        foreach($responsabili_cdr_data as $responsabile_cdr){
            if ($responsabile_cdr->codice_cdr == $cdr->codice) {
                $resp_diretta = true;
                break;
            }
        }
        if ($resp_diretta == true) {
            $to_check = true;
        }        
    }
    //se il cdr rispetta i criteri di inclusione nel report vengono effettuate le verifiche
    if ($to_check == true) {
        $add_to_report_obiettivi = false;
        $add_to_report_peso = false;
        $add_to_report_personale = false;
        $anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $dateTimeObject);               
                
        //viene verificato che il cdr non abbia obiettivi assegnati
        $obiettivi_assegnati_cdr = array();
        foreach($ob_obiettivi_cdr_anno as $obiettivo_cdr) {
            if ($obiettivo_cdr->codice_cdr == $cdr->codice
                && ($obiettivo_cdr->id_tipo_piano_cdr == 0 || $obiettivo_cdr->id_tipo_piano_cdr == $tipo_piano->id)
                ) {
                $obiettivi_assegnati_cdr[] = $obiettivo_cdr;
            }
        }
        if (!count($obiettivi_assegnati_cdr)) {            
            $add_to_report_obiettivi = true;
        }
        //altrimenti viene verificato che i pesi siano assegnati (somma dei pesi > 0)
        else {            
            if($anagrafica_cdr->getPesoTotaleObiettivi($anno, null, $obiettivi_assegnati_cdr) == 0) {
                $add_to_report_peso = true;                
            }
            //vengono verificati i dipendenti senza assegnazione          
            if (count($personale_cdr)){                   
                $personale_senza_obiettivi = array();
                foreach($personale_cdr as $dipendente) {
                    $dipendente = $personale[$dipendente->matricola_personale];
                    
                    if (!in_array($dipendente->matricola, $ob_personale_associati)) {                       
                        $add_to_report_personale = true;
                        $personale_senza_obiettivi[] = $dipendente->cognome
                                                        ." "
                                                        .$dipendente->nome
                                                        ." (matr. "
                                                        .$dipendente->matricola
                                                        .")";
                    }     
                }
            }
        }
        if ($add_to_report_obiettivi || $add_to_report_peso || $add_to_report_personale) {
            $record_to_add = array($i);            
            if ($view_all) {
                //viene recuperato il padre strategico
                $cdr_padre_strategico = new CdrStrategia($cdr->id);
                $cdr_padre_strategico = $cdr_padre_strategico->getPadreStrategico($anno);
                $responsabile_cdr_padre_strategico = $cdr_padre_strategico->getResponsabile($dateTimeObject);                
                $articolazione_organizzativa_to_add = $cdr_padre_strategico->codice
                                    ." - "
                                    .$cdr_padre_strategico->descrizione
                                    ." (".$responsabile_cdr_padre_strategico->cognome
                                    ." "
                                    .$responsabile_cdr_padre_strategico->nome
                                    ." matr."
                                    .$responsabile_cdr_padre_strategico->matricola_responsabile
                                    .")";
                $record_to_add[] = $articolazione_organizzativa_to_add;                 
            }
            if ($cdr->id_padre !== 0) {
                $cdr_padre = getCdrPadre($cdr->id_padre, $cdr_piano);                
            }
            else {
                $cdr_padre = new Cdr();
                $cdr_padre->codice = "";
                $cdr_padre->descrizione = "Nessuno";
            }               
            $cdr_padre_to_add = $cdr_padre->codice
                                    ." - "
                                    .$cdr_padre->descrizione
                                    ." (".$cdr_padre->responsabile->cognome
                                    ." "
                                    .$cdr_padre->responsabile->nome
                                    ." matr. "
                                    .$cdr_padre->responsabile->matricola_responsabile
                                    .")";                        
            $cdr_to_add = $cdr->codice
                                    ." - "
                                    .$cdr->descrizione
                                    . " (".$cdr->responsabile->cognome
                                    ." "
                                    .$cdr->responsabile->nome
                                    ." matr. "
                                    .$cdr->responsabile->matricola_responsabile
                                    .")";
            $record_to_add[] = $cdr_padre_to_add;
            $record_to_add[] = $cdr_to_add;
                                                
            if ($add_to_report_obiettivi){
                $cdr_report_obiettivi[] = $record_to_add;                
                if ($view_all) {
                    $articolazioni_organizzative_obiettivi_filter[] = $articolazione_organizzativa_to_add;
                }                
                $cdr_padri_obiettivi_filter[] = $cdr_padre_to_add;                
            }
            if($add_to_report_peso){
                $cdr_report_peso[] = $record_to_add;
                if ($view_all) {
                    $articolazioni_organizzative_peso_filter[] = $articolazione_organizzativa_to_add;
                }
                $cdr_padri_peso_filter[] = $cdr_padre_to_add;
            }                        
            if ($add_to_report_personale) {
                foreach ($personale_senza_obiettivi as $dipendente) {
                    $record_personale_to_add = $record_to_add;
                    $record_personale_to_add[] = $dipendente;
                    $cdr_report_personale[] = $record_personale_to_add;
                    unset($record_personale_to_add);
                }    
                $cdr_padri_personale_filter[] = $cdr_padre_to_add;
                $cdr_filter[] = $cdr_to_add;
            }
            unset($articolazione_organizzativa_to_add);            
            unset($cdr_padre_to_add);
            unset($cdr_to_add);
        }      
    }
    $i++;
}
unset ($cdr);
unset ($cdr_to_check);

//generazione filtri ricerca
if ($view_all) {    
    $articolazioni_organizzative_obiettivi_multipairs = array();
    foreach(array_unique($articolazioni_organizzative_obiettivi_filter) as $art_org) {
        $articolazioni_organizzative_obiettivi_multipairs[] = array(
                                                new ffData($art_org, "Text"),
                                                new ffData($art_org, "Text"),
                                                );
    }
    unset($art_org);
    unset($articolazioni_organizzative_obiettivi_filter);
    
    $articolazioni_organizzative_peso_multipairs = array();
    foreach(array_unique($articolazioni_organizzative_peso_filter) as $art_org) {
        $articolazioni_organizzative_peso_multipairs[] = array(
                                                new ffData($art_org, "Text"),
                                                new ffData($art_org, "Text"),
                                                );
    }
    unset($art_org);
    unset($articolazioni_organizzative_peso_filter);
}

$cdr_padri_obiettivi_multipairs = array();
foreach(array_unique($cdr_padri_obiettivi_filter) as $padri) {    
    $cdr_padri_obiettivi_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_obiettivi_filter);

$cdr_padri_peso_multipairs = array();
foreach(array_unique($cdr_padri_peso_filter) as $padri) {    
    $cdr_padri_peso_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_peso_filter);

$cdr_padri_personale_multipairs = array();
foreach(array_unique($cdr_padri_personale_filter) as $padri) {
    $cdr_padri_personale_multipairs[] = array(
                                            new ffData($padri, "Text"),
                                            new ffData($padri, "Text"),
                                            );
}
unset($padri);
unset($cdr_padri_personale_filter);
  
$cdr_multipairs = array();
foreach(array_unique(array_unique($cdr_filter)) as $cdr_search) {
    $cdr_multipairs[] = array(
                                            new ffData($cdr_search, "Text"),
                                            new ffData($cdr_search, "Text"),
                                            );
}
unset($cdr_search);
unset($cdr_filter);

//grid report obiettivi*********************************************************
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "assegnazioni";
$oGrid->title = "CdR senza obiettivi assegnati";
$oGrid->resources[] = "cdr"; 
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $cdr_report_obiettivi, "obiettivi_obiettivo_cdr");
$oGrid->order_default =$view_all?"padre_strategico":"cdr_padre";    
//visualizzazione della grid dei cdr associati all'obiettivo       
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;

//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

//filters
if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico_search";
    $oField->data_source = "padre_strategico";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $articolazioni_organizzative_obiettivi_multipairs;
    $oField->label = "Articolazione organizzativa";
    $oGrid->addSearchField($oField);
}
    
$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre_search";
$oField->data_source = "cdr_padre";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_padri_obiettivi_multipairs;
$oField->label = "Cdr padre";
$oGrid->addSearchField($oField);

$cm->oPage->addContent($oGrid);

//grid report peso**************************************************************
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "pesi";
$oGrid->title = "CdR con peso 0";
$oGrid->resources[] = "peso";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $cdr_report_peso, "obiettivi_obiettivo_cdr");
$view_all?$oGrid->order_default = "padre_strategico":$oGrid->order_default = "cdr_padre";      
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;
//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);

//filters
if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico_search";
    $oField->data_source = "padre_strategico";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $articolazioni_organizzative_peso_multipairs;
    $oField->label = "Articolazione organizzativa";
    $oGrid->addSearchField($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre_search";
$oField->data_source = "cdr_padre";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_padri_peso_multipairs;
$oField->label = "Cdr padre";
$oGrid->addSearchField($oField);

$cm->oPage->addContent($oGrid);

//grid report personale*********************************************************
array_push($grid_fields, "dipendente", "cdr");
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "dipendenti";
$oGrid->title = "Personale senza obiettivi assegnati";
$oGrid->resources[] = "personale";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $cdr_report_personale, "obiettivi_obiettivo_cdr_personale");
$oGrid->order_default = "cdr";        
$oGrid->record_id = "";
$oGrid->order_method = "labels";
$oGrid->record_url = "";
$oGrid->use_paging = false;
$oGrid->full_ajax = true;
//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_url = false;

//$oGrid->open_adv_search = true;
//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico";
    $oField->base_type = "Text";
    $oField->label = "Articolazione organizzativa";
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre";
$oField->base_type = "Text";
$oField->label = "CdR Padre";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "Cdr";
$oGrid->addContent($oField);
    
$oField = ffField::factory($cm->oPage);
$oField->id = "dipendente";
$oField->base_type = "Text";
$oField->label = "Dipendente";
$oGrid->addContent($oField);

//filters
if ($view_all) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "padre_strategico_search";
    $oField->data_source = "padre_strategico";
    $oField->base_type = "Text";
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $articolazioni_organizzative_peso_multipairs;
    $oField->label = "Articolazione organizzativa";
    $oGrid->addSearchField($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_padre_search";
$oField->data_source = "cdr_padre";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_padri_personale_multipairs;
$oField->label = "Cdr padre";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_search";
$oField->data_source = "cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_multipairs;
$oField->label = "Cdr";
$oGrid->addSearchField($oField);

$cm->oPage->addContent($oGrid);