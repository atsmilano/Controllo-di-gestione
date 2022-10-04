<?php
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("fabbisogno_admin") && !$user->hasPrivilege("fabbisogno_operatore_formazione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione delle schede.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];	
$date = $cm->oPage->globals["data_riferimento"]["value"];
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $date->format("Y-m-d"));

$modulo = Modulo::getCurrentModule();

$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("elenco_schede.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//generazione del filtro sui cdr
$cdr_radice_piano = $piano_cdr->getCdrRadice();
$cdr_anno = $cdr_radice_piano->getGerarchia();
$codici_cdr_anno = array();

if (count($cdr_anno) > 0) {
    //generazione del filtro sul cdr
    $cdr_selezionato = 0;
    if (isset($_REQUEST["cdr_select"])) {
        $cdr_filtro = $_REQUEST["cdr_select"];
    } else {
        $cdr_filtro = 0;
    }
    foreach ($cdr_anno as $cdr_associato) {
        if ($cdr_associato["cdr"]->id == $cdr_filtro) {
            $tpl->set_var("cdr_selected", "selected='selected'");
            $cdr_selezionato = $cdr_associato["cdr"];            
        } else {
            $tpl->set_var("cdr_selected", "");
        }
        $tpl->set_var("cdr_id", $cdr_associato["cdr"]->id);
        $indent = "";
        for ($i = 0; $i < $cdr_associato["livello"]; $i++) {
            $indent .= "----";
        }
        $tpl->set_var("cdr_indent", $indent);
        $tpl->set_var("cdr_descrizione", $cdr_associato["cdr"]->codice . " - " . $cdr_associato["cdr"]->descrizione);
        $tpl->parse("SectOptionCdr", true);
    }
    if ($cdr_selezionato == 0) {
        $cdr_selezionato = $cdr_radice_piano;
    }
} else {
    ffErrorHandler::raise("Nessun CDR disponibile");
}
$gerarchia_cdr_selezionato = $cdr_selezionato->getGerarchia();
$cm->oPage->addContent($tpl);

//grid schede
$grid_fields = array(
    "ID", 
    "cdr",
    "responsabile_scientifico",
    "referente_segreteria",
    "titolo",
    "data_chiusura",
);
$grid_recordset = array();
foreach (FabbisognoFormazione\Richiesta::getAll(array("ID_anno_budget"=>$anno->id)) as $richiesta) {
    $show = false;
    foreach($gerarchia_cdr_selezionato as $cdr_gerarchia_selezionata) {
        if ($richiesta->codice_cdr == $cdr_gerarchia_selezionata["cdr"]->codice){
            $show = true;
            break;
        }
    }
    if ($show == true) {
        $cdr = AnagraficaCdr::factoryFromCodice($richiesta->codice_cdr, $date);
        if (strlen($richiesta->matricola_responsabile_scientifico) ) {
            $responsabile_scientifico = Personale::factoryFromMatricola($richiesta->matricola_responsabile_scientifico);
            $responsabile_scientifico_desc = $responsabile_scientifico->cognome." ".$responsabile_scientifico->nome." (matr.".$responsabile_scientifico->matricola.")";
        }
        else {
            $responsabile_scientifico_desc = "Non definito";
        }        
        if (strlen($richiesta->matricola_referente_segreteria) ) {
            $referente_segreteria = Personale::factoryFromMatricola($richiesta->matricola_referente_segreteria);            
            $referente_desc = $referente_segreteria->cognome." ".$referente_segreteria->nome." (matr.".$referente_segreteria->matricola.")";
        }
        else {
            $referente_desc = "Non definito";
        }       
        $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
        $grid_recordset[] = array(
            $richiesta->id,
            $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
            $responsabile_scientifico_desc,
            $referente_desc,
            $richiesta->titolo,
            $richiesta->data_chiusura,
        );
    }
}

$cm->oPage->addContent("<a id='fabbisogni_estrazione_xls' class='link_estrazione' href='" . FF_SITE_PATH . "/area_riservata" . $modulo->site_path."/estrazioni/fabbisogni_competenza.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."cdr_select=".$cdr_selezionato->id."'>"
            . "<div id='fabbisogni_estrazione' class='estrazione link_estrazione'>Estrazione fabbisogni competenza .xls</div></a><br>");
$cm->oPage->addContent("<a id='fabbisogni_estrazione_xml' class='link_estrazione' href='" . FF_SITE_PATH . "/area_riservata" . $modulo->site_path."/estrazioni/fabbisogni_xml.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."'>"
            . "<div id='fabbisogni_estrazione_xml' class='estrazione link_estrazione'>Estrazione fabbisogni competenza selezionati .xml</div></a><br>");


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "richieste";
$oGrid->title = "Richieste fabbisogno " . $anno->descrizione;
$oGrid->resources[] = "richiesta";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields,
    $grid_recordset, 
    "fabbisogno_richiesta"    
);
$oGrid->order_default = "titolo";
$oGrid->record_id = "dettaglio-richiesta";
$module = Modulo::getCurrentModule();
$oGrid->record_url = FF_SITE_PATH . "/area_riservata" . $module->site_path . "/dettaglio_richiesta";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_search = false;
$oGrid->use_search = false;
$oGrid->use_paging = false;
//tpl personalizzato per estrazione xml
$oGrid->template_dir = $modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl";
$oGrid->template_file = "grid_fabbisogno.html";

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

$oGrid->addEvent("on_before_parse_row", "initGrid");
$cm->oPage->addContent($oGrid);

function initGrid ($oGrid) {    
    //inizializzazione id per le checkbox
    $tpl = $oGrid->tpl[0];
    $tpl->set_var("id", $oGrid->key_fields["ID_richiesta"]->value->getValue());
    
    //verifica eliminazione record
    $id = $oGrid->key_fields["ID_richiesta"]->value->getValue();
    $richiesta = new FabbisognoFormazione\Richiesta($id);
    if ($richiesta->data_chiusura == null) {
        $oGrid->display_delete_bt = true;
    }
    else {
        $oGrid->display_delete_bt = false;
    }            
}