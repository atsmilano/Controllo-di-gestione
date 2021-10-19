<?php
$user = LoggedUser::getInstance();
//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"];

if (isset($_REQUEST["id_periodo_cruscotto"]) && !empty($_REQUEST["id_periodo_cruscotto"])) {
    try {
        $periodo_cruscotto = new IndicatoriPeriodoCruscotto($_REQUEST["id_periodo_cruscotto"]);
    }
    catch (Exception $e) {
        ffErrorHandler::raise($e->getMessage());
    }
}
else {
    $periodo_cruscotto = null;
}

//popolamento della grid tramite array		
$grid_fields = array(
    "ID",
    "codice",
    "nome",
    "descrizione",
    "risultato",
    "valore_target",
    "raggiungimento",
);

$grid_recordset = array();
$anagrafica_cdr = new AnagraficaCdrObiettivi($cdr->id_anagrafica_cdr);
$indicatori_bersaglio = array();
foreach ($anagrafica_cdr->getIndicatoriCruscottoAnno($anno) as $indicatore_cruscotto_anno) {
    $risultati = $indicatore_cruscotto_anno->indicatore->getValoriCruscottoCdr($cdr, $anno, $periodo_cruscotto);

    $grid_recordset[] = array(
        $indicatore_cruscotto_anno->id,
        $indicatore_cruscotto_anno->indicatore->codice,
        $indicatore_cruscotto_anno->indicatore->nome,
        $indicatore_cruscotto_anno->indicatore->descrizione,
        $risultati["risultato"] !== null ? $risultati["risultato"] : "ND",
        $risultati["valore_target"] !== null ? $risultati["valore_target"] : "ND",
        $risultati["raggiungimento"] !== null ? $risultati["raggiungimento"] : "ND",
    );

    if ($risultati["valore_target"] !== null) {
        $indicatori_bersaglio[] = array("ID" => $indicatore_cruscotto_anno->id,
            "nome" => $indicatore_cruscotto_anno->indicatore->nome,
            "raggiungimento" => $risultati["raggiungimento"],
            "nome" => $indicatore_cruscotto_anno->indicatore->nome
        );
    }
}

// Filtro periodo cruscotto
$periodo_cruscotto_select = array();

foreach (IndicatoriPeriodoCruscotto::getAll(array("ID_anno_budget" => $anno->id), array(array("fieldname" => "data_riferimento_fine", "direction" => "DESC"))) AS $item) {
    $data_riferimento_inizio = CoreHelper::formatUiDate($item->data_riferimento_inizio);
    $data_riferimento_fine = CoreHelper::formatUiDate($item->data_riferimento_fine);

    $periodo_cruscotto_select[] = array(
        new ffData ($item->id, "Number"),
        new ffData ("$item->descrizione ($data_riferimento_inizio - $data_riferimento_fine)", "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "id_periodo_cruscotto";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $periodo_cruscotto_select;
$oField->label = "Periodo cruscotto";
$oField->multi_select_one_label = "Tutti i periodi cruscotto";
$oField->properties["onchange"] = "submit();";
$oField->setValue($periodo_cruscotto->id);
$cm->oPage->addContent($oField);
$cm->oPage->addContent("<br />");

//*************************
//Visualizzazione bersaglio
if (count($indicatori_bersaglio)) {
    CoreHelper::includeJqueryUi();

    $modulo = Modulo::getCurrentModule();

    //viene caricato il template specifico per la pagina
    $tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
    $tpl->load_file("cruscotto_indicatori.html", "main");
    
    $tpl->set_var("module_theme_path", $modulo->module_theme_full_path);

    /* parte dello stile viene gestito dinamicamente in questa sezione per gestire variazioni di dimensioni in maniera parametrizzata */
    //creazione del bersaglio e delle valutazioni
    //viene impostato il diametro dell'immagine bersaglio, e l'offset per centrare il bersaglio  
    define("TARGET_DIAMETER", 300);
    define("SHOT_DIAMETER", TARGET_DIAMETER / 40);

    $tpl->set_var("target_diameter", TARGET_DIAMETER);
    $tpl->set_var("shot_diameter", SHOT_DIAMETER);
    //l'offset viene calcolato per immagini delle valutazioni grandi 16 px!!!
    $offset = ((TARGET_DIAMETER / 2) - (SHOT_DIAMETER / 2));

    foreach ($indicatori_bersaglio as $indicatore_bersaglio) {
        //visualizzazione del colpo sul bersaglio
        //calcolo della posizione del raggiungimento
        //calcolo della distanza dal centro        
        $radius = (TARGET_DIAMETER / 2) - ((TARGET_DIAMETER / 2) * $indicatore_bersaglio["raggiungimento"] * 0.01);
        //distribuzione su un angolo casuale
        $degrees = rand(0, 360);
        //calcolo della posizione sugli assi tramite trigonometria
        $pos_x = $radius * cos($degrees);
        $pos_y = $radius * sin($degrees);
        //visualizzazione nel target    
        $shot_img = "circle.png";

        $tpl->set_var("id", $indicatore_bersaglio["ID"]);
        $tpl->set_var("nome_indicatore", $indicatore_bersaglio["nome"]);
        $tpl->set_var("shot_image", $shot_img);

        $tpl->set_var("y_offset", ($offset + $pos_y));
        $tpl->set_var("x_offset", ($offset + $pos_x));

        $tpl->parse("SectShot", true);
    }
    $tpl->parse("Bersaglio", true);
    $cm->oPage->addContent($tpl->rpparse("main", true));
}

//visualizzazione della grid (nel caso in cui ci siano obiettivi per l'anno)
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "indicatori";
$oGrid->title = "Indicatori anno " . $anno->descrizione;
$oGrid->resources[] = "indicatore-cruscotto";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_indicatore");
$oGrid->order_default = "nome";
$oGrid->record_id = "indicatore-cruscotto-modify";
$oGrid->order_method = "labels";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "dettagli_indicatore_cruscotto";
$oGrid->full_ajax = true;
//privilegi sulle operazioni
if (!$user->hasPrivilege("indicatori_edit")) {
    $oGrid->display_new = false;
    $oGrid->display_delete_bt = false;
}
$oGrid->display_navigator = false;
$oGrid->use_paging = false;
//inizializzazione grid per gestione evidenziazione righe da bersaglio
$oGrid->addEvent("on_before_parse_row", "initGrid");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatore_cruscotto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "risultato";
$oField->base_type = "Text";
$oField->label = "Risultato";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore_target";
$oField->base_type = "Text";
$oField->label = "Valore target";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "raggiungimento";
$oField->base_type = "Text";
$oField->label = "Raggiungimento";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//inizializzazione grid per gestione evidenziazione righe da bersaglio
function initGrid($oGrid) {
    $oGrid->row_class = "ID_" . $oGrid->key_fields["ID_indicatore_cruscotto"]->value->getValue();
}