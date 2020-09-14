<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("investimenti_richieste_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

$cdr_global = $cm->oPage->globals["cdr"]["value"];
$cdr = new CdrInvestimenti($cdr_global->id);

$anno = $cm->oPage->globals["anno"]["value"];
//visualizzazione della grid delle richieste d'investimento effettuate dal cdr
$source_sql = "";
$db = ffDb_Sql::factory();
foreach ($cdr->getInvestimentiAnno($anno) as $investimento) {
    $index_stato_avanzamento = array_search($investimento->getIdStatoAvanzamento(), array_column(InvestimentiInvestimento::$stati_investimento, 'ID'));
    $stato_avanzamento = InvestimentiInvestimento::$stati_investimento[$index_stato_avanzamento]["descrizione"];

    if (strlen($source_sql)){
        $source_sql .= " UNION ";
    }
    //estrazione del piano cdr
    $date = $cm->oPage->globals["data_riferimento"]["value"]->format("Y-m-d");
    //recupero del cdr e del cdc
    $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
    $piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);
    $cdc_richiesta = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
    $cdr_richiesta = new Cdr ($cdc_richiesta->id_cdr);
    //categoria bene o servizio
    $categoria_bene = new InvestimentiCategoria ($investimento->richiesta_id_categoria);
    //priorita intervento
    $priorita_intervento = new InvestimentiPrioritaIntervento($investimento->richiesta_id_priorita);   
    $source_sql .= "
        SELECT 
            " . $db->toSql($investimento->id) . " AS ID,
            " . $db->toSql($cdr_richiesta->codice . " - " . $cdr_richiesta->descrizione) . " AS cdr_richiesta,
            " . $db->toSql($categoria_bene->descrizione) . " AS categoria_bene,
            " . $db->toSql(CoreHelper::cutText($investimento->richiesta_descrizione_bene, INVESTIMENTI_LUNGHEZZA_MAX_DESCRIZIONE_RICHIESTA)) . " AS descrizione_bene,
            " . $db->toSql($investimento->istruttoria_costo_presunto) . " AS costo_presunto,
            " . $db->toSql($priorita_intervento->descrizione) . " AS priorita_intervento,         
            " . $db->toSql($stato_avanzamento) . " AS stato_avanzamento
    ";   
}
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "investimenti";
$oGrid->title = "Richieste investimento";
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
//possibilità di inserimento modifica ed eliminazione garantita solamente agli utenti con il privilegi
if (!$user->hasPrivilege("investimenti_richieste_view")) {        
    $oGrid->display_edit_url = false;
}
if (!$user->hasPrivilege("investimenti_richieste_edit")) {    
    $oGrid->display_new = false;
}
$oGrid->addEvent("on_before_parse_row", "initGrid");
        
// Grid pronta, passo alle colonne
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

function initGrid ($oGrid){   
    $investimento = new InvestimentiInvestimento($oGrid->key_fields["ID"]->value->getValue());
    if ($id_stato_avanzamento = $investimento->getIdStatoAvanzamento() == 1) {
        $oGrid->display_delete_bt = true;
    }
    else {
        $oGrid->display_delete_bt = false;
    }
}