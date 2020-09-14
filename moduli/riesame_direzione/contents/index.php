<?php
$user = LoggedUser::Instance();

if (!$user->hasPrivilege("riesame_direzione_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

try {
    $cdr = $cm->oPage->globals["cdr"]["value"];          
    $anno = $cm->oPage->globals["anno"]["value"];
    $dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];    
    //visualizzazione della grid delle scehde dei cdr figli
    $source_sql = "";
    $db = ffDb_Sql::factory();

    //introduzione per l'anno
    $cm->oPage->addContent("<h2>Riesame della direzione</h2>");    
    $cm->oPage->addContent("<a class='estrazione link_estrazione' href='estrazione_cdr.php?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)."'>Estrazione .xls</a>");
    $cm->oPage->addContent("<div id='introduzione'>");    
    $introduzione_anno = RiesameDirezioneIntroduzione::getIntroduzioneAnno($anno);
    if ($introduzione_anno !== false){
        $cm->oPage->addContent("<p class='introduzione_riesame'>".$introduzione_anno->testo."</p>");        
    }
    $cm->oPage->addContent("</div>");       
    
    $cdr_figli_to_order = $cdr->getFigli();
    //vengon ordinati i figli in base alla tipologia di cdr e ordine alfabetico
    foreach ($cdr_figli_to_order as $cdr_figlio){	
        $cdr_figli[] = $cdr_figlio;
        $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);    
        $descrizioni_cdr[] = $cdr_figlio->descrizione;
        $tipi_cdr[] = $tipo_cdr->abbreviazione;
    }			
    array_multisort($tipi_cdr, SORT_ASC, $descrizioni_cdr, SORT_ASC, $cdr_figli);
    //viene aggiunto come primo elemento dell'array il cdr attuale, se non ci sono figli viene utilizzato solo il cdr attuale
    if (count($cdr_figli)) {
        array_unshift($cdr_figli, $cdr);
    }
    else {
        $cdr_figli[] = $cdr;
    }
    
    //indice per l'ordinamento
    $i = 0;
    foreach ($cdr_figli as $cdr_figlio){   
        $responsabile_cdr_figlio = $cdr_figlio->getResponsabile($dateTimeObject);
        //recupero di eventuale riesame per il cdr e l'anno
        try{
            $riesame = RiesameDirezioneRiesame::factoryFromCdrAnno($cdr_figlio, $anno);
            $id = $riesame->id;
            $id_stato_avanzamento = $riesame->getIdStato();        
        } catch (Exception $ex) {
            $riesame = null;
            $id = null;
            $id_stato_avanzamento = 0;
        }    
        //descrizione stato avanzamento    
        $stato_avanzamento = RiesameDirezioneRiesame::$stati_riesame[array_search($id_stato_avanzamento, array_column(RiesameDirezioneRiesame::$stati_riesame, 'ID'))]["descrizione"];
        if ($id_stato_avanzamento == 2) {
            $stato_avanzamento .= " (data chiusura: " . date("d/m/Y", strtotime($riesame->data_chiusura)) . ")";
        }

        if (strlen($source_sql)){
            $source_sql .= " UNION ";        
        }              
        $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);
        $source_sql .= "
            SELECT 
                " . $db->toSql($id) . " AS ID,
                " . $db->toSql($cdr_figlio->id) . " AS ID_cdr,
                " . $db->toSql($cdr_figlio->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_figlio->descrizione) . " AS cdr,
                " . $db->toSql($responsabile_cdr_figlio->matricola_responsabile) . " AS matricola_responsabile,
                " . $db->toSql($responsabile_cdr_figlio->nome." ".$responsabile_cdr_figlio->cognome." (matr. ".$responsabile_cdr_figlio->matricola_responsabile.")") . " AS responsabile,
                " . $db->toSql($stato_avanzamento) . " AS stato_avanzamento,
                CAST(" . $db->toSql($i++) . " AS UNSIGNED) AS grid_order
        ";       
    }

    //costruzione grid
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "riesame";
    $oGrid->title = "";
    $oGrid->resources[] = "riesame";
    if (strlen($source_sql) > 0) {
        $oGrid->source_SQL = "
            SELECT *
            FROM (".$source_sql.") AS riesame_direzione_riesame                          
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    else {
        $oGrid->source_SQL = "
            SELECT
                '' AS ID,
                '' AS ID_cdr,
                '' AS cdr,             
                '' AS matricola_responsabile,
                '' AS responsabile,
                '' AS stato_avanzamento,
                '' AS grid_order
            FROM riesame_direzione_riesame
            WHERE 1=0
            [AND]
            [WHERE]
            [HAVING]
            [ORDER]
        ";
    }
    $oGrid->order_default = "grid_order";
    $oGrid->record_id = "riesame-modify";
    $oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio";
    $oGrid->use_paging = false;
    $oGrid->display_search = false;
    //i record saranno sempre modificabili ( gestione interna al record per inserimento/modifica) e mai eliminabili
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;

    $oGrid->addevent("on_before_parse_row", "recordInit");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_cdr";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "grid_order";
    $oField->base_type = "Number";
    $oField->display = false;
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "cdr";
    $oField->base_type = "Text";
    $oField->label = "CDR";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "matricola_responsabile";
    $oField->base_type = "Text";
    $oField->display = false;
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "responsabile";
    $oField->base_type = "Text";
    $oField->label = "Responsabile";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "stato_avanzamento";
    $oField->base_type = "Text";
    $oField->label = "Stato avanzamento";
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $cm->oPage->addContent($oGrid);
} catch (Exception $ex) {
    $cm->oPage->addContent("<p>Nessun CDR selezionato, impossibile visualizzare.</p>");
}

function recordInit ($oGrid){
    //viene evidenziato il cdr di responsabilità diretta
    $cm = cm::getInstance();
    $user = LoggedUser::Instance();
    if ($oGrid->grid_fields["matricola_responsabile"]->value->getValue() == $user->matricola_utente_selezionato){
        $oGrid->row_class = "evidenza";
    }
    else {
        $oGrid->row_class = "";
    }
}
