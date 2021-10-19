<?php
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

$periodi_rendicontazione = array();
foreach(ObiettiviPeriodoRendicontazione::getAll() as $periodo_rendicontazione) {
    $periodi_rendicontazione[$periodo_rendicontazione->id] = $periodo_rendicontazione;
}

$periodi_cruscotto = array();
foreach(IndicatoriPeriodoCruscotto::getAll() as $periodo_cruscotto) {
    $periodi_cruscotto[$periodo_cruscotto->id] = $periodo_cruscotto;
}

$grid_fields = array(
    "ID_indicatori_valore_parametro_rilevato",
    "ID_parametro",
    "parametro",
    "ID_periodo_rendicontazione",
    "periodo_rendicontazione",
    "ID_periodo_cruscotto",
    "periodo_cruscotto", 
    "codice_cdr",
    "cdr",
    "valore",
    "modificabile", 
    "data_riferimento",
    "data_importazione"
);

$grid_recordset = array();
foreach(IndicatoriValoreParametroRilevato::getAll(
        array(),
        array(
            ["fieldname" => "id_parametro", "direction" => "ASC"],
            ["fieldname" => "data_riferimento", "direction" => "DESC"]
        )
    ) as $item) {
    $parametro = new IndicatoriParametro($item->id_parametro);
    $periodo_rendicontazione = $periodi_rendicontazione[$item->id_periodo_rendicontazione];
    $periodo_cruscotto = $periodi_cruscotto[$item->id_periodo_cruscotto];

    if ($periodo_cruscotto !== NULL) {
        $data_riferimento = new DateTime($periodo_cruscotto->data_riferimento_fine);
    }
    if (!empty($item->codice_cdr)) {
        $cdr = AnagraficaCdr::factoryFromCodice($item->codice_cdr, $data_riferimento);
        $cdr_txt = $cdr->codice." - ".$cdr->descrizione;
    }
    else {
        $cdr_txt = "";
    }
    
    $grid_recordset[] = array(
        $item->id,
        $item->id_parametro,
        $parametro->nome,
        $item->id_periodo_rendicontazione,
        $periodo_rendicontazione->descrizione,
        $item->id_periodo_cruscotto,
        $periodo_cruscotto->descrizione,
        $item->codice_cdr,
        $cdr_txt,
        $item->valore,
        CoreHelper::getBooleanValueFromDB($item->modificabile) ? "Si" : "No",
        $item->data_riferimento, $item->data_importazione
    );    
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "storico";
$oGrid->title = "Storico parametri";
$oGrid->resources[] = "storico";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray(
    $grid_fields, $grid_recordset, "indicatori_valore_parametro_rilevato"
);
$oGrid->order_default = "parametro";
$oGrid->record_id = "storico-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_storico";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = false;
$oGrid->display_delete_bt = true;
$oGrid->display_search = true;
$oGrid->use_paging = false;
$oGrid->display_search = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatori_valore_parametro_rilevato";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_parametro";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_rendicontazione";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_cruscotto";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parametro";
$oField->base_type = "Text";
$oField->label = "Parametro";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_rendicontazione";
$oField->base_type = "Text";
$oField->label = "Periodo rendicontazione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_cruscotto";
$oField->base_type = "Text";
$oField->label = "Periodo cruscotto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore";
$oField->base_type = "Text";
$oField->label = "Valore";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "modificabile";
$oField->base_type = "Text";
$oField->label = "Modificabile";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento";
$oField->base_type = "DateTime";
$oField->label = "Data riferimento";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_importazione";
$oField->base_type = "DateTime";
$oField->label = "Data importazione";
$oGrid->addContent($oField);

$parametro_select = array();
foreach(IndicatoriParametro::getAll() as $parametro) {
    $parametro_select[] = array(
        new ffData($parametro->id, "Number"),
        new ffData($parametro->nome, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "parametro_search";
$oField->data_source = "ID_parametro";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $parametro_select;
$oField->multi_select_one_label = "Tutti i parametri";
$oField->label = "Parametro";
$oField->src_operation = "ID_parametro = [VALUE]";
$oGrid->addSearchField($oField);

$periodo_rendicontazione_select = array();
foreach(ObiettiviPeriodoRendicontazione::getAll() as $periodo_rendicontazione) {
    $data_riferimento_inizio = CoreHelper::formatUiDate($periodo_rendicontazione->data_riferimento_inizio);
    $data_riferimento_fine = CoreHelper::formatUiDate($periodo_rendicontazione->data_riferimento_fine);

    $periodo_rendicontazione_select[] = array(
        new ffData($periodo_rendicontazione->id, "Number"),
        new ffData("$periodo_rendicontazione->descrizione ($data_riferimento_inizio - $data_riferimento_fine)", "Text")
    );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_rendicontazione_search";
$oField->data_source = "ID_periodo_rendicontazione";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $periodo_rendicontazione_select;
$oField->multi_select_one_label = "Tutti i periodi rendicontazione";
$oField->label = "Periodo rendicontazione";
$oField->src_operation = "ID_periodo_rendicontazione = [VALUE]";
$oGrid->addSearchField($oField);

$periodo_cruscotto_select = array();
foreach(IndicatoriPeriodoCruscotto::getAll() as $periodo_cruscotto) {
    $data_riferimento_inizio = CoreHelper::formatUiDate($periodo_cruscotto->data_riferimento_inizio);
    $data_riferimento_fine = CoreHelper::formatUiDate($periodo_cruscotto->data_riferimento_fine);

    $periodo_cruscotto_select[] = array(
        new ffData($periodo_cruscotto->id, "Number"),
        new ffData("$periodo_cruscotto->descrizione ($data_riferimento_inizio - $data_riferimento_fine)", "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_cruscotto_search";
$oField->data_source = "ID_periodo_cruscotto";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $periodo_cruscotto_select;
$oField->multi_select_one_label = "Tutti i periodi cruscotto";
$oField->label = "Periodo cruscotto";
$oField->src_operation = "ID_periodo_cruscotto = [VALUE]";
$oGrid->addSearchField($oField);

$cdr_select = array();
foreach(AnagraficaCdr::getAnagraficaInData($data_riferimento) as $cdr) {
    $cdr_select[] = array(
        new ffData($cdr->codice, "Number"),
        new ffData($cdr->codice . " - " . $cdr->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "cdr_search";
$oField->data_source = "codice_cdr";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_select;
$oField->multi_select_one_label = "Tutti i cdr";
$oField->label = "CDR";
$oField->src_operation = "codice_cdr = [VALUE]";
$oGrid->addSearchField($oField);

$cm->oPage->addContent($oGrid);