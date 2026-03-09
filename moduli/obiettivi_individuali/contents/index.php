<?php
use ObiettiviIndividuali\AssegnazioneIndividuale;

$user = LoggedUser::getInstance();

if (!$user->hasPrivilege("obiettivi_individuali_admin")
    && !$user->hasPrivilege("resp_cdr_selezionato")
    && !$user->hasPrivilege("resp_padre_ramo_cdr_selezionato")
    ) { 
    ffErrorHandler::raise("L'utente non ha i privilegi per visualizzare i dati.");
}

$anno = $cm->oPage->globals["anno"]["value"];
$cdr_selezionato = $cm->oPage->globals["cdr"]["value"];
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

//popolamento della grid tramite array	
$currentModule = core\Modulo::getCurrentModule();

//******************************************************************************
$grid_fields = array(
    "matricola_dipendente",    
    "tipo_assegnazione",   
    "dipendente",
    "percentuale",
    "assegnazione_individuale",
    "assegnabile",
);

$anagrafica_cdr_selezionato = new AnagraficaCdr($cdr_selezionato->id_anagrafica_cdr);
$cdr_resp_anno_desc = $anagrafica_cdr_selezionato->getDescrizioneEstesa();
$grid_recordset_assegnazione = array();
foreach (AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInData ($cdr_selezionato, $data_riferimento, [$user->matricola_utente_selezionato]) as  $tipo_personale=>$dipendenti_assegnazioni){                                       
    foreach ($dipendenti_assegnazioni as $assegnazioni_individuali_personale) {                     
        $descrizione_assegnazione = "";  
        $assegnabile = true;        
        if (!$assegnazioni_individuali_personale["personale_visualizzato"]["assegnabile"]) {
            if ($assegnazioni_individuali_personale["personale_visualizzato"]["cdr_prevalente"] == null) {
                $descrizione_assegnazione = "Non assegnabile: dipendente cessato\n";
                $assegnabile = false;
            }
            else {
                $resp_cdr_prevalente = $assegnazioni_individuali_personale["personale_visualizzato"]["cdr_prevalente"]->getResponsabile($data_riferimento);
                $descrizione_assegnazione = "Non assegnabile: CdR prevalenza "                
                .$assegnazioni_individuali_personale["personale_visualizzato"]["cdr_prevalente"]->getDescrizioneEstesa()
                ."(".$assegnazioni_individuali_personale["personale_visualizzato"]["perc_cdr_prevalente"]."%) - "
                ." resp. ".$resp_cdr_prevalente->cognome." ".$resp_cdr_prevalente->nome." (".$resp_cdr_prevalente->matricola_responsabile.")\n";                
                $assegnabile = false;
            }                        
        }
        $n_assegnazioni_cdr_selezionato = 0;
        foreach ($assegnazioni_individuali_personale["assegnazioni"] as $assegnazione) {                      
            if ($assegnazione["assegnazione_individuale"]->codice_cdr !== $cdr_selezionato->codice) {                                                
                /*
                $anagrafica_cdr_assegnazione = AnagraficaCdr::factoryFromCodice(
                    $assegnazione["assegnazione_individuale"]->codice_cdr,
                    $data_riferimento
                );                                        
                if ($anagrafica_cdr_assegnazione == null) {
                    $descrizione_assegnazione .= "Obiettivo assegnato da Cdr ". $assegnazione["assegnazione_individuale"]->codice_cdr."\n";
                }                       
                else {
                    $descrizione_assegnazione .= "Obiettivo assegnato da Cdr "
                                                .$anagrafica_cdr_assegnazione->getDescrizioneEstesa()."\n";                        
                }  
                */
            }
            else {
                if ($n_assegnazioni_cdr_selezionato > 0) {
                    $descrizione_assegnazione .= "\n";
                }
                if (OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR > 1) {
                    $descrizione_assegnazione .= ++$n_assegnazioni_cdr_selezionato . ")";  
                }
                if (strlen($assegnazione["assegnazione_individuale"]->rendicontazione)) {
                    $descrizione_assegnazione .= "* ";
                } 
                else {
                    $descrizione_assegnazione .= " ";
                }
                $descrizione_assegnazione .= $assegnazione["assegnazione_individuale"]->descrizione;   
                if ($assegnazione["assegnazione_individuale"]->datetime_chiusura !== null) {
                    $datetime_chiusura = new Datetime($assegnazione["assegnazione_individuale"]->datetime_chiusura);
                    $descrizione_assegnazione .= " (chiusura: ".$datetime_chiusura->format("d/m/Y H:i:s").")";
                } 
                if ($assegnazione["assegnazione_individuale"]->datetime_accettazione !== null) {
                    $datetime_accettazione = new Datetime($assegnazione["assegnazione_individuale"]->datetime_accettazione);
                    $descrizione_assegnazione .= " (accettazione: ".$datetime_accettazione->format("d/m/Y H:i:s").")";                   
                }              
            }                                                                                  
        }
        $grid_recordset_assegnazione[$tipo_personale][] = [
            $assegnazioni_individuali_personale["personale_visualizzato"]["personale"]->matricola,
            $tipo_personale,            
            $assegnazioni_individuali_personale["personale_visualizzato"]["personale"]->cognome . " " . $assegnazioni_individuali_personale["personale_visualizzato"]["personale"]->nome . " (" . $assegnazioni_individuali_personale["personale_visualizzato"]["personale"]->matricola . ")",       
            $assegnazioni_individuali_personale["personale_visualizzato"]["perc_cdr"],
            $descrizione_assegnazione, 
            $assegnabile,
        ];                             
    }
}

foreach ($grid_recordset_assegnazione as  $tipo_personale => $grid_recordset_tipo_personale){        
    if (count($grid_recordset_tipo_personale)){         
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "obiettivi-assegnazione-dipendente-".$tipo_personale;
        switch ($tipo_personale){
            case "resp-cdr-figli":
                $oGrid->title = "Responsabili dei Cdr afferenti nell'anno ".$anno->descrizione." al CdR: '".$cdr_resp_anno_desc."'";
            break;
            case "personale-cdr-attivo":
                $oGrid->title = "Personale assegnato al Cdr: '".$cdr_resp_anno_desc."'";
            break;
            case "personale-cdr-cessato":
                $oGrid->title = "Personale assegnato e trasferito / cessato nell'anno ".$anno->descrizione." al Cdr: '".$cdr_resp_anno_desc."'";
            break;
        }
       
        $oGrid->resources[] = "obiettivo-individuale";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset_tipo_personale, "obind_assegnazione_individuale");
        $oGrid->order_default = "dipendente";
        $oGrid->record_id = "obiettivo-dipendente-modify";
        $oGrid->order_method = "labels";
        $oGrid->record_url = MODULES_SITE_PATH . $currentModule->site_path . "/dettagli_dipendente";
        $oGrid->display_navigator = false;
        $oGrid->display_search = false;
        $oGrid->use_paging = false;  
        $oGrid->full_ajax = true;              

        //operazioni di inserimento
        $oGrid->display_new = false;        
        $oGrid->display_delete_bt = false;
            
        $oGrid->addEvent("on_before_parse_row", "checkEdit");              

        // *********** FIELDS ****************        
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
        $oField->id = "dipendente";
        $oField->base_type = "Text";
        $oField->label = "Dipendente";
        $oGrid->addContent($oField);

        if ($tipo_personale !== "resp-cdr-figli") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "percentuale";
            $oField->base_type = "Text";
            $oField->label = "% su Cdr";
            $oGrid->addContent($oField);
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "assegnazione_individuale";
        $oField->base_type = "Text";
        $oField->label = "Assegnazioni individuali";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "assegnabile";
        $oField->base_type = "Text";
        $oField->label = "Assegnabile";
        $oField->display_label = false;
        $oField->display = false;
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $cm->oPage->addContent($oGrid);
    } 
    else {
        $cm->oPage->addContent("Nessun dipendente assegnato al Cdr per l'anno selezionato.<br>"); 
    }        
}

function checkEdit ($oGrid) {
    $oGrid->display_edit_url = (bool)$oGrid->grid_fields["assegnabile"]->getValue();
}