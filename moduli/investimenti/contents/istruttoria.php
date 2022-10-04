<?php
$user = LoggedUser::getInstance();

if (!$user->hasPrivilege("investimenti_istruttoria_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

$anno = $cm->oPage->globals["anno"]["value"];
//visualizzazione della grid delle richieste d'investimento effettuate dal cdr
$source_sql = "";
$db = ffDb_Sql::factory();

//estrazione del piano cdr
$date = $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d");
//recupero del cdr e del cdc
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
$cdr_global = $cm->oPage->globals["cdr"]["value"];
$cdr = null;
if ($cdr_global->id !== 0){
    $cdr = $cdr_global->cloneAttributesToNewObject("CdrInvestimenti");
}

foreach (InvestimentiInvestimento::getAll(array("ID_anno_budget" => $anno->id)) as $investimento) {
    $id_stato_avanzamento = $investimento->getIdStatoAvanzamento();
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column(InvestimentiInvestimento::$stati_investimento, 'ID'));        
    //vengono visualizzati solamente gli investimenti che si trovano dallo stato di istruttoria in poi
    $uoc_competente_view = false;
    if ($cdr !== null && $investimento->istruttoria_id_categoria_uoc_competente_anno !== 0) {
        $categoria_uoc_competente_anno = new InvestimentiCategoriaUocCompetenteAnno($investimento->istruttoria_id_categoria_uoc_competente_anno);
        
        if ($categoria_uoc_competente_anno->codice_cdr == $cdr->codice) {
            $uoc_competente_view = true;
        }
    }

    if ($investimento->getIdStatoAvanzamento() >= 3 && $investimento->getIdStatoAvanzamento() !== 11) {                   
        $cdc_richiesta = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
        $cdr_richiesta = new CdrInvestimenti ($cdc_richiesta->id_cdr);
        $cdr_creazione = Cdr::factoryFromCodice($investimento->codice_cdr_creazione, $piano_cdr);               

        //il direttore del dipartimento amministrativo può vedere tutte le richieste dallo stato 3 in poi (implicito nel controllo dello stato)
        //l'uoc competente dallo stato 4 in poi
        //l'uoc bilancio dallo stato 6 in poi
        //il dg dallo stato 8
        if (
                $user->hasPrivilege("investimenti_istruttoria_dip_amm_edit")
                ||
                ($uoc_competente_view == true && ($investimento->getIdStatoAvanzamento() >= 4))
                ||
                ($user->hasPrivilege("investimenti_istruttoria_bilancio_edit") && ($investimento->getIdStatoAvanzamento() >= 6))
                ||
                ($user->hasPrivilege("investimenti_piano_parere_edit") && ($investimento->getIdStatoAvanzamento() >= 8))                
            ) {
            $stato_avanzamento = InvestimentiInvestimento::$stati_investimento[$index_stato_avanzamento]["descrizione"];
               
            //categoria bene o servizio
            $categoria_bene = new InvestimentiCategoria ($investimento->richiesta_id_categoria);
            //priorita intervento
            $priorita_intervento = new InvestimentiPrioritaIntervento($investimento->richiesta_id_priorita);   
            if (strlen($source_sql)){
                $source_sql .= " UNION ";
            }
            $tipo_cdr_creazione = new TipoCdr($cdr_creazione->id_tipo_cdr);
            $tipo_cdr_richiesta = new TipoCdr($cdr_richiesta->id_tipo_cdr);
            $source_sql .= "
                SELECT 
                    " . $db->toSql($investimento->id) . " AS ID,
                    " . $db->toSql($cdr_creazione->codice . " - " . $tipo_cdr_creazione->abbreviazione . " " . $cdr_creazione->descrizione) . " AS cdr_creazione,
                    " . $db->toSql($cdr_richiesta->codice . " - " . $tipo_cdr_richiesta->abbreviazione . " " . $cdr_richiesta->descrizione) . " AS cdr_richiesta,
                    " . $db->toSql($categoria_bene->descrizione) . " AS categoria_bene,
                    " . $db->toSql(CoreHelper::cutText($investimento->richiesta_descrizione_bene, INVESTIMENTI_LUNGHEZZA_MAX_DESCRIZIONE_RICHIESTA)) . " AS descrizione_bene,
                    " . $db->toSql($investimento->istruttoria_costo_presunto) . " AS costo_presunto,
                    " . $db->toSql($priorita_intervento->descrizione) . " AS priorita_intervento,         
                    " . $db->toSql($stato_avanzamento) . " AS stato_avanzamento
            ";             
        }                         
    }
}
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "investimenti";
$oGrid->title = "Istruttoria richieste investimento";
$oGrid->resources[] = "investimento";
if (strlen($source_sql) > 0) {
    $oGrid->source_SQL = "
        SELECT *
        FROM (".$source_sql.") AS investimenti_investimento                          
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
else {
    $oGrid->source_SQL = "
        SELECT
            '' AS ID,
            '' AS cdr_creazione,
            '' AS cdr_richiesta,             
            '' AS categoria_bene,
            '' AS descrizione_bene,
            '' AS costo_presunto,
            '' AS priorita_intervento,      
            '' AS stato_avanzamento
        FROM investimenti_investimento
        WHERE 1=0
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
}
$oGrid->order_default = "ID";
$oGrid->record_id = "investimento";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettaglio_richiesta";
$oGrid->use_paging = false;      
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
        
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_richiesta";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "Cod.";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_creazione";
$oField->base_type = "Text";
$oField->label = "Cdr creazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_richiesta";
$oField->base_type = "Text";
$oField->label = "Cdr richiesta";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "categoria_bene";
$oField->base_type = "Text";
$oField->label = "Categoria bene/servizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione_bene";
$oField->base_type = "Text";
$oField->label = "Descr. bene/servizio";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "costo_presunto";
$oField->base_type = "Number";
$oField->label = "Costo presunto";
$oGrid->addContent($oField);
            
$oField = ffField::factory($cm->oPage);
$oField->id = "priorita_intervento";
$oField->base_type = "Text";
$oField->label = "Priorità";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "stato_avanzamento";
$oField->base_type = "Text";
$oField->label = "Stato d'avanzamento";
$oGrid->addContent($oField);  

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);