<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_referente_cdr")
        && !$user->hasPrivilege("fabbisogno_responsabile_cdr_referente")
        && !$user->hasPrivilege("fabbisogno_responsabile_scientifico_anno") 
        && !$user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno")
        && !$user->hasPrivilege("fabbisogno_responsabile_cdr")
        ) {
    if ($user->hasPrivilege("fabbisogno_operatore_formazione") || $user->hasPrivilege("fabbisogno_admin")) {
        $modulo = Modulo::getCurrentModule();
        ffRedirect(FF_SITE_PATH . "/area_riservata".$modulo->site_path."/gestione?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    } else {
        ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle schede.");
    }    	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"];
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $date->format("Y-m-d"));

$grid_fields = array(
    "ID", 
    "cdr",
    "responsabile_scientifico",
    "referente_segreteria",
    "titolo",
    "data_chiusura",
);
$grid_recordset = array();
if ($user->hasPrivilege("fabbisogno_referente_cdr")) {
    $grid_recordset["referente_cdr"]["title"] = "Richieste con ruolo di referente formazione";
}
if ($user->hasPrivilege("fabbisogno_referente_cdr")) {
    $personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
    $cdr_richiesta_competenza_anno = $personale->getCdrReferenzaAnno($date);
}
if ($user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {
    $grid_recordset["responsabile_cdr_referente"]["title"] = "Richieste del ramo gerarchico di responsabilità";
}
if ($user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {
    if (!isset($personale)) {
        $personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
    }
    $ramo_cdr_competenza_anno = $personale->getCdrResponsbileReferenzaAnno($date);
}

$modulo = Modulo::getCurrentModule();
$cm->oPage->addContent("<a id='fabbisogni_estrazione_link' class='link_estrazione' href='".FF_SITE_PATH . "/area_riservata" . $modulo->site_path."/estrazioni/fabbisogni_competenza.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."'>"
            . "<div id='fabbisogni_estrazione' class='estrazione link_estrazione'>Estrazione fabbisogni competenza .xls</div></a><br>");

foreach (FabbisognoFormazione\Richiesta::getAll(array("ID_anno_budget"=>$anno->id)) as $richiesta) {
    $cdr_richiesta = AnagraficaCdr::factoryFromCodice($richiesta->codice_cdr, $date);
    if (strlen($richiesta->matricola_responsabile_scientifico) ) {
        $responsabile_scientifico = Personale::factoryFromMatricola($richiesta->matricola_responsabile_scientifico);
        $responsabile_scientifico_desc = $responsabile_scientifico->cognome." ".$responsabile_scientifico->nome." (matr.".$responsabile_scientifico->matricola.")";
    }
    else {
        $responsabile_scientifico_desc = "Non definito";
    }
    if (strlen($richiesta->matricola_referente_segreteria)) {
        $referente_segreteria = Personale::factoryFromMatricola($richiesta->matricola_referente_segreteria);
        $referente_desc = $referente_segreteria->cognome." ".$referente_segreteria->nome." (matr.".$referente_segreteria->matricola.")";
    }
    else {
        $referente_desc = "Non definito";
    }    
    $tipo_cdr = new TipoCdr($cdr_richiesta->id_tipo_cdr);
    $grid_record = array(
        $richiesta->id,
        $cdr_richiesta->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_richiesta->descrizione,
        $responsabile_scientifico_desc,
        $referente_desc,
        $richiesta->titolo,
        $richiesta->data_chiusura,
    );
    if ($user->hasPrivilege("fabbisogno_referente_cdr")) {
        foreach ($cdr_richiesta_competenza_anno as $cdr_richiesta_competenza) {                                                
            if ($richiesta->codice_cdr == $cdr_richiesta_competenza->codice) {                
                $grid_recordset["referente_cdr"]["recordset"][] = $grid_record;
                break;
            }
        }                                                   
    }
    if ($user->hasPrivilege("fabbisogno_responsabile_cdr_referente")) {
        foreach ($ramo_cdr_competenza_anno as $cdr_richiesta_competenza) {                                                
            if ($richiesta->codice_cdr == $cdr_richiesta_competenza->codice) {                
                $grid_recordset["responsabile_cdr_referente"]["recordset"][] = $grid_record;
                break;
            }
        }                                                   
    }
    //per responsabile scientifico e per segreteria organizzativa le verifiche sono ridondanti ma vengono introdotte per robustezza
    if ($user->hasPrivilege("fabbisogno_responsabile_scientifico_anno") && $richiesta->matricola_responsabile_scientifico == $user->matricola_utente_selezionato) {
        if(!isset($grid_recordset["responsabile_scientifico"]["title"])) {
            $grid_recordset["responsabile_scientifico"]["title"] = "Richieste con ruolo di responsabile scientifico";
        }
        $grid_recordset["responsabile_scientifico"]["recordset"][] = $grid_record;
    }
    if ($user->hasPrivilege("fabbisogno_segreteria_organizzativa_anno") && $richiesta->matricola_referente_segreteria == $user->matricola_utente_selezionato) {
        if(!isset($grid_recordset["segreteria_organizzativa"]["title"])) {
            $grid_recordset["segreteria_organizzativa"]["title"] = "Richieste con ruolo di segreteria organizzativa";
        }
        $grid_recordset["segreteria_organizzativa"]["recordset"][] = $grid_record;
    }
    //verifica su esistenza $cdr_richiesta ridondante (privilegio fabbisogno_responsabile_cdr garantito se cdr selezionato) ma introdotta per robustezza
    if ($user->hasPrivilege("fabbisogno_responsabile_cdr") && $cdr_richiesta!== null && $richiesta->codice_cdr == $cdr->codice) {
        if(!isset($grid_recordset["responsabile_cdr"]["title"])) {
            $grid_recordset["responsabile_cdr"]["title"] = "Richieste del CdR selezionato";
        }
        $grid_recordset["responsabile_cdr"]["recordset"][] = $grid_record;
    }
}

if(count($grid_recordset)) {
    foreach ($grid_recordset as $key=>$grid) {
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "richieste-".$key;
        $oGrid->title = $grid["title"] . " - anno " . $anno->descrizione;
        $oGrid->resources[] = "richiesta";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
            $grid_fields,
            $grid["recordset"], 
            "fabbisogno_richiesta"
        );
        $oGrid->order_default = "titolo";
        $oGrid->record_id = "dettaglio-richiesta";
        $module = Modulo::getCurrentModule();
        $oGrid->record_url = FF_SITE_PATH . "/area_riservata" . $module->site_path . "/dettaglio_richiesta";
        $oGrid->order_method = "labels";
        //$oGrid->full_ajax = true;
        if (($user->hasPrivilege("fabbisogno_referente_cdr") && $key == "referente_cdr")
            || ($user->hasPrivilege("fabbisogno_responsabile_cdr_referente") && $key == "responsabile_cdr_referente")
            || ($user->hasPrivilege("fabbisogno_admin"))
            || ($user->hasPrivilege("fabbisogno_operatore_formazione"))
                ) {
            $oGrid->display_new = true;
            $oGrid->display_delete_bt = true;
            $oGrid->addEvent("on_before_parse_row", "checkDelete");
        }
        else {
            $oGrid->display_new = false;
            $oGrid->display_delete_bt = false;
        }
        $oGrid->display_search = false;
        $oGrid->use_search = false;
        $oGrid->use_paging = false;    
        
        $oGrid->row_class = "fabbisogno_grid_row";

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_richiesta";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr";
        $oField->base_type = "Text";
        $oField->label = "Cdr";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "titolo";
        $oField->base_type = "Text";
        $oField->label = "Titolo";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "responsabile_scientifico";
        $oField->base_type = "Text";
        $oField->label = "Responsabile scientifico";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "referente_segreteria";
        $oField->base_type = "Text";
        $oField->label = "Segreteria organizzativa";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "data_chiusura";
        $oField->base_type = "Date";
        $oField->label = "Chiusura";
        $oGrid->addContent($oField);

        $cm->oPage->addContent($oGrid);
    }
}
else {
    $cm->oPage->addContent("<h3>Nessuna richiesta di competenza.</h3>");
}

function checkDelete ($oGrid) {    
    $id = $oGrid->key_fields["ID_richiesta"]->value->getValue();
    $richiesta = new FabbisognoFormazione\Richiesta($id);
    if ($richiesta->data_chiusura == null) {
        $oGrid->display_delete_bt = true;
    }
    else {
        $oGrid->display_delete_bt = false;
    }    
}