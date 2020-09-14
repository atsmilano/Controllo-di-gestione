<?php
$user = LoggedUser::Instance();

if (!$user->hasPrivilege("investimenti_view")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla pagina.");
}

$anno = $cm->oPage->globals["anno"]["value"];
//visualizzazione della grid delle richieste d'investimento effettuate dal cdr
$source_sql = "";
$db = ffDb_Sql::factory();

//estrazione del piano cdr
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");
//recupero del cdr e del cdc
$tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
$piano_cdr = PianoCdr::getAttivoInData ($tipo_piano_cdr, $date);

$cdr_global = $cm->oPage->globals["cdr"]["value"];
$cdr = null;
if ($cdr_global->id !== 0){
    $cdr = new CdrInvestimenti($cdr_global->id);
}

//amministratore e direzione generale possono visualizzare anche le proposte rifiutate dal dg
$view_richieste_rifiutate = false;
if ($user->hasPrivilege("investimenti_piano_parere_edit") || $privilegi_utente["edit_admin"]) {
    $view_richieste_rifiutate = true;
}

foreach (InvestimentiInvestimento::getAll(array("ID_anno_budget" => $anno->id)) as $investimento) {
    $id_stato_avanzamento = $investimento->getIdStatoAvanzamento();
    $index_stato_avanzamento = array_search($id_stato_avanzamento, array_column(InvestimentiInvestimento::$stati_investimento, 'ID'));        
    //vengono visualizzati solamente gli investimenti che si trovano dallo stato attesa parere direzione in poi
    //solo il dg può visualizzare gli investimenti in attesa del parere, tutti gli altri utenti vedono solamente gli investimenti inseriti nel piano    
    if ($investimento->getIdStatoAvanzamento() >= 8 
        && 
        $investimento->getIdStatoAvanzamento() !== 11
        &&
        //la direzione generale visualizza anche le richieste che ha rifiutato
        ($investimento->getIdStatoAvanzamento() !== 12 ||  $view_richieste_rifiutate == true)
        &&
        $investimento->getIdStatoAvanzamento() !== 13    
        ) {                   
        $cdc_richiesta = Cdc::factoryFromCodice($investimento->richiesta_codice_cdc, $piano_cdr);
        $cdr_richiesta = new CdrInvestimenti ($cdc_richiesta->id_cdr);
        $cdr_creazione = AnagraficaCdr::factoryFromCodice($investimento->codice_cdr_creazione, $dateTimeObject);               

        //il dg vede le richieste dallo stato 8, gli altri utenti dal successico
        if (
            $user->hasPrivilege("investimenti_piano_parere_edit") && ($investimento->getIdStatoAvanzamento() >= 8)
            ||
            $investimento->getIdStatoAvanzamento() > 8
            ) {
            $stato_avanzamento = InvestimentiInvestimento::$stati_investimento[$index_stato_avanzamento]["descrizione"];
               
            //categoria bene o servizio
            $categoria_bene = new InvestimentiCategoria ($investimento->richiesta_id_categoria);
            //priorita intervento
            $priorita_intervento = new InvestimentiPrioritaIntervento($investimento->richiesta_id_priorita);   
            if (strlen($source_sql)){
                $source_sql .= " UNION ";
            }
            $source_sql .= "
                SELECT 
                    " . $db->toSql($investimento->id) . " AS ID,
                    " . $db->toSql($cdr_creazione->codice . " - " . $cdr_creazione->descrizione) . " AS cdr_creazione,
                    " . $db->toSql($cdr_richiesta->codice . " - " . $cdr_richiesta->descrizione) . " AS cdr_richiesta,
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
$oGrid->title = "Piano degli investimenti";
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
