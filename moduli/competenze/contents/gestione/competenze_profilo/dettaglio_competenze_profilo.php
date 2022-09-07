<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");

if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle competenze dei profili per il CdR.");		
}

if (isset($_REQUEST["keys[ID_profilo]"])) {
    try {
        $profilo = new MappaturaCompetenze\Profilo($_REQUEST["keys[ID_profilo]"]);
        $found = false;
        foreach ($cdr->getProfiliResponsabile($user->matricola_utente_selezionato) as $profilo_cdr) {
            if ($profilo->id == $profilo_cdr->id){
                $found = true;
                break;
            }            
        }
        if ($found == false) {
            throw new Exception(
                "Errore nel passaggio dei parametri: profilo non previsto per il CdR e responsabile."
            );
        }        
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "profilo-modify";
$oRecord->title = "Competenze del profilo '".$profilo->descrizione."'";
$oRecord->resources[] = "profilo";
$oRecord->src_table  = "competenze_profilo";
$oRecord->allow_insert = false;
$oRecord->allow_delete = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_profilo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//competenze trasversali
$oRecord->addContent(null, true, "competenze_trasversali");
$oRecord->groups["competenze_trasversali"]["title"] = "Competenze trasversali";
    
$oField = ffField::factory($cm->oPage);
$oField->id = "competenze_trasversali";
$oField->label = "Competenze trasversali del profilo";
$oField->data_type = "callback";
$oField->data_source = "preload_competenze_trasversali_relations";
$oField->base_type = "Text";
$competenze_trasversali = $profilo->getCompetenzeTrasversaliAssegnabili();
if (count($competenze_trasversali) > 0){
    $competenze_trasversali_multipairs = array();
    foreach ($competenze_trasversali AS $competenza_trasversale) {    
        $competenze_trasversali_multipairs[] = array(
                            new ffData ($competenza_trasversale->id, "Number"),
                            new ffData ($competenza_trasversale->nome." - ".$competenza_trasversale->descrizione, "Text")
                            );
    }         	
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $competenze_trasversali_multipairs;
    $oField->control_type = "input";
    $oField->widget = "checkgroup";
    $oField->grouping_separator = ",";
    $oField->store_in_db = false;
}
else {
    $oField->label = "Nessuna competenza trasversale definita";
    $oField->data_type = "";	
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "competenze_trasversali");

//precompilazione delle competenze trasversali
function preload_competenze_trasversali_relations($form_fields, $key, $first_access){
    if($first_access) {       
        //condizione ridondante (record acceduto solo in modifica) ma mantenuta per robustezza
        if(isset($_REQUEST["keys"]["ID_profilo"])){
            $profilo = new \MappaturaCompetenze\Profilo($_REQUEST["keys"]["ID_profilo"]);            
            foreach ($profilo->getCompetenzeTrasversaliProfilo() as $competenza_trasversale_profilo){					
                if(strlen($competenze_trasversali_profilo)){ 
                        $competenze_trasversali_profilo .= ",";
                }
                $competenze_trasversali_profilo .= ($competenza_trasversale_profilo->id_competenza_trasversale); 
            }			
        }
        return new ffdata($competenze_trasversali_profilo);
    } 
    else{
        return $form_fields[$key]->value;
    }
}       
$oRecord->addEvent("on_done_action", "competenzeTrasversaliUpdate");

//competenze specifiche
$oRecord->addContent(null, true, "competenze_specifiche");
$oRecord->groups["competenze_specifiche"]["title"] = "Competenze specifiche";
    
$oField = ffField::factory($cm->oPage);
$oField->id = "competenze_specifiche";
$oField->label = "Competenze specifiche del profilo";
$oField->data_type = "callback";
$oField->data_source = "preload_competenze_specifiche_relations";
$oField->base_type = "Text";
$competenze_specifiche = $profilo->getCompetenzeSpecificheAssegnabili();
if (count($competenze_specifiche) > 0){
    $competenze_specifiche_multipairs = array();
    foreach ($competenze_specifiche AS $competenza_specifica) {    
        $competenze_specifiche_multipairs[] = array(
                            new ffData ($competenza_specifica->id, "Number"),
                            new ffData ($competenza_specifica->nome." - ".$competenza_specifica->descrizione, "Text")
                            );
    }         	
    $oField->extended_type = "Selection";
    $oField->multi_pairs = $competenze_specifiche_multipairs;
    $oField->control_type = "input";
    $oField->widget = "checkgroup";
    $oField->grouping_separator = ",";
    $oField->store_in_db = false;
}
else {
    $oField->label = "Nessuna competenza specifica definita";
    $oField->data_type = "";	
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField, "competenze_specifiche");

//precompilazione delle competenze specifiche
function preload_competenze_specifiche_relations($form_fields, $key, $first_access){
    if($first_access) {       
        //condizione ridondante (record acceduto solo in modifica) ma mantenuta per robustezza
        if(isset($_REQUEST["keys"]["ID_profilo"])){
            $profilo = new \MappaturaCompetenze\Profilo($_REQUEST["keys"]["ID_profilo"]);            
            foreach ($profilo->getCompetenzeSpecificheProfilo() as $competenza_specifica_profilo){					
                if(strlen($competenze_specifiche_profilo)){ 
                        $competenze_specifiche_profilo .= ",";
                }
                $competenze_specifiche_profilo .= ($competenza_specifica_profilo->id_competenza_specifica); 
            }			
        }
        return new ffdata($competenze_specifiche_profilo);
    } 
    else{
        return $form_fields[$key]->value;
    }
}
$oRecord->addEvent("on_done_action", "competenzeSpecificheUpdate");

$cm->oPage->addContent($oRecord);

function competenzeTrasversaliUpdate($oRecord, $frmAction) {
    if($oRecord->form_fields["competenze_trasversali"]->getValue() !== "") {
        $competenze_trasversali_profilo = explode(",", $oRecord->form_fields["competenze_trasversali"]->getValue());
    }
    else {
        $competenze_trasversali_profilo = array();
    }
    $profilo = new \MappaturaCompetenze\Profilo($oRecord->key_fields["ID_profilo"]->value->getValue());
    //gestione delle azioni sul record
    switch($frmAction)
    {        
        case "insert":							            
            //vengono inserite tute le relazioni create					
            foreach ($competenze_trasversali_profilo as $id_competenza_trasversale) {                
                $competenza_trasversale_profilo = new \MappaturaCompetenze\ProfiloCompetenzaTrasversale();
                $competenza_trasversale_profilo->id_profilo = $profilo->id;
                $competenza_trasversale_profilo->id_competenza_trasversale = $id_competenza_trasversale;
                $competenza_trasversale_profilo->save(array("ID_profilo", "ID_competenza_trasversale"));
            }
        break;								
        case "update":	
            //************************************
            //eliminazione delle relazioni non mantenute		
            foreach($profilo->getCompetenzeTrasversaliProfilo() as $competenza_trasversale_profilo) {
                $mantenuto = false;
                foreach ($competenze_trasversali_profilo as $id_competenza_trasversale) {       
                    if ($competenza_trasversale_profilo->id_competenza == $id_competenza_trasversale) {
                        $mantenuto = true;
                        break;
                    }                                                                        
                }
                if ($mantenuto == false) {
                    $competenza_trasversale_profilo->delete();
                }                                
            }
            //************************************
            //creazione o mantenimento delle relazioni selezionate in caso non esistano già
            foreach ($competenze_trasversali_profilo as $id_competenza_trasversale) {       
                if(\MappaturaCompetenze\ProfiloCompetenzaTrasversale::getByFields(array("ID_profilo"=>$profilo->id, "ID_competenza_trasversale"=>$id_competenza_trasversale))==null){
                    $competenza_trasversale_profilo = new \MappaturaCompetenze\ProfiloCompetenzaTrasversale();
                    $competenza_trasversale_profilo->id_profilo = $profilo->id;
                    $competenza_trasversale_profilo->id_competenza_trasversale = $id_competenza_trasversale;
                    $competenza_trasversale_profilo->save(array("ID_profilo", "ID_competenza_trasversale"));
                }                                                                                   
            }
        break;		
        case "delete":			
        case "confirmdelete":
            //propagazione sulla relazione tipo_cdr_padre
            foreach(ProfiloCompetenzaTrasversale::getAll(array("ID_profilo"=>$oRecord->key_fields["ID"]->value->getValue())) as $competenza_trasversale_profilo) {
                $competenza_trasversale_profilo->delete();
            }				
        break;
    }
}

function competenzeSpecificheUpdate($oRecord, $frmAction) {
    if($oRecord->form_fields["competenze_specifiche"]->getValue() !== "") {
        $competenze_specifiche_profilo = explode(",", $oRecord->form_fields["competenze_specifiche"]->getValue());
    }
    else {
        $competenze_specifiche_profilo = array();
    }
    $profilo = new \MappaturaCompetenze\Profilo($oRecord->key_fields["ID_profilo"]->value->getValue());
    //gestione delle azioni sul record
    switch($frmAction)
    {        
        case "insert":							            
            //vengono inserite tute le relazioni create					
            foreach ($competenze_specifiche_profilo as $id_competenza_specifica) {                
                $competenza_specifica_profilo = new \MappaturaCompetenze\ProfiloCompetenzaSpecifica();
                $competenza_specifica_profilo->id_profilo = $profilo->id;
                $competenza_specifica_profilo->id_competenza_specifica = $id_competenza_specifica;
                $competenza_specifica_profilo->save(array("ID_profilo", "ID_competenza_specifica"));
            }
        break;								
        case "update":	
            //************************************
            //eliminazione delle relazioni non mantenute		
            foreach($profilo->getCompetenzeSpecificheProfilo() as $competenza_specifica_profilo) {
                $mantenuto = false;
                foreach ($competenze_specifiche_profilo as $id_competenza_specifica) {       
                    if ($competenza_specifica_profilo->id_competenza == $id_competenza_specifica) {
                        $mantenuto = true;
                        break;
                    }                                                                        
                }
                if ($mantenuto == false) {
                    $competenza_specifica_profilo->delete();
                }                                
            }
            //************************************
            //creazione o mantenimento delle relazioni selezionate in caso non esistano già
            foreach ($competenze_specifiche_profilo as $id_competenza_specifica) {       
                if(\MappaturaCompetenze\ProfiloCompetenzaSpecifica::getByFields(array("ID_profilo"=>$profilo->id, "ID_competenza_specifica"=>$id_competenza_specifica))==null){
                    $competenza_specifica_profilo = new \MappaturaCompetenze\ProfiloCompetenzaSpecifica();
                    $competenza_specifica_profilo->id_profilo = $profilo->id;
                    $competenza_specifica_profilo->id_competenza_specifica = $id_competenza_specifica;
                    $competenza_specifica_profilo->save(array("ID_profilo", "ID_competenza_specifica"));
                }                                                                                   
            }
        break;		
        case "delete":			
        case "confirmdelete":
            //propagazione sulla relazione tipo_cdr_padre
            foreach(ProfiloCompetenzaSpecifica::getAll(array("ID_profilo"=>$oRecord->key_fields["ID"]->value->getValue())) as $competenza_specifica_profilo) {
                $competenza_specifica_profilo->delete();
            }				
        break;
    }
}
