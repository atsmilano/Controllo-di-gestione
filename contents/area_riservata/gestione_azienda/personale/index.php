<?php
//******************************************************************************
//Validazione e selezione parametri
//**********

//Tipo piano
$tipi_piano_select = array();
foreach (TipoPianoCdr::getAll() AS $tipo_piano) {
    $first_piano = $tipo_piano;
    $tipi_piano_select[] = array(
        new ffData($tipo_piano->id, "Number"),
        new ffData("Piano organizzativo: " . $tipo_piano->descrizione, "Text")
    );
}
if (count($tipi_piano_select) == 0) {
    ffErrorHandler::raise("Nessuna tipologia di piano cdr definita");
}

if (isset($_GET["distr_teste_piano"])) {
    $tipo_piano = new TipoPianoCdr($_GET["distr_teste_piano"]);
}
else {
    $tipo_piano = $first_piano;
}

//visualizzazione ffield
$oField = ffField::factory($cm->oPage);
$oField->id = "distr_teste_piano";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipi_piano_select;
$oField->setValue($tipo_piano->id);

if (count($tipi_piano_select) == 0) {
    $oField->multi_select_one_label = "Nessun Tipo piano definito.";
}
else {
    $oField->multi_select_one = false;
}

if (count($tipi_piano_select) <= 1) {
    $oField->control_type = "label";
}
$oField->properties["onchange"] = "submit();";
$cm->oPage->addContent($oField->process());

//****
//Data
//viene inizializzato il campo per la selezione della data
$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento";
$oField->base_type = "Date";
$oField->label = "Data di riferimento";
$oField->widget = "datepicker";
//viene impostata la data in base al controllo dei parametri
if (isset($_REQUEST["data_riferimento"]))
    $date = $_REQUEST["data_riferimento"];
else
    $date = date("d/m/Y");
//viene preimpostato il valore del campo di selezione in base alla richiesta
$oField->setValue($date);
$date_parts = explode("/", $date);
$db_date = $date_parts[2] . "-" . $date_parts[1] . "-" . $date_parts[0];
$oField->properties["onchange"] = "submit();";
$oField->parent_page = array(&$cm->oPage);
$cm->oPage->addContent($oField->process());

//******************************************************************************
//popolamento della grid tramite array
$db = ffDb_Sql::factory();
$personale = Personale::getAll();
$source_sql = "";
foreach ($personale as $dipendente) {
    //viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano
    $cdc_afferenza = $dipendente->getCdcAfferenzaInData($tipo_piano, $db_date);
    $cdc_sql = "";
    if (count($cdc_afferenza) > 0) {
        if (strlen($source_sql))
            $source_sql .= "UNION ";
        $source_sql .= "
            SELECT
            " . $db->toSql($dipendente->id) . " AS ID,
            " . $db->toSql($dipendente->matricola) . " AS matricola,
            " . $db->toSql($dipendente->cognome) . " AS cognome,
            " . $db->toSql($dipendente->nome) . " AS nome,						
        ";
        foreach ($cdc_afferenza as $cdc_dipendente) {
            $cdc_sql .= $cdc_dipendente["cdc"]->codice . " - " . $cdc_dipendente["cdc"]->descrizione . "(" . $cdc_dipendente["cdc_personale"]->percentuale . "%)\n";
        }
        $source_sql .= $db->toSql($cdc_sql) . " AS cdc_afferenza ";
    }
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "personale";
$oGrid->title = "Distribuzione teste";
$oGrid->resources[] = "personale";
if (strlen($source_sql) == 0) {
    $oGrid->source_SQL = "
        SELECT * 
        FROM personale 
        WHERE 0=1 
        [AND]
        [WHERE]
        [HAVING]
        [ORDER]
    ";
} else {
    $oGrid->source_SQL = "
        SELECT *
        FROM (" . $source_sql . ") AS personale
	[WHERE]
	[HAVING]
	[ORDER]
    ";
}
$oGrid->order_default = "cognome";
$oGrid->record_id = "personale-modify";
$oGrid->order_method = "labels";
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$record_url = FF_SITE_PATH . $path_info . "gestione_personale/dettaglio_personale";
$oGrid->record_url = $record_url;
$oGrid->display_delete_bt = false;
$oGrid->display_new = false;
$oGrid->full_ajax = false;

// *********** FIELDS ****************

$oField = ffField::factory($cm->oPage);
$oField->id = "id_personale";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cognome";
$oField->base_type = "Text";
$oField->label = "Cognome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola";
$oField->base_type = "Text";
$oField->label = "Matricola";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdc_afferenza";
$oField->base_type = "Text";
$oField->label = "Cdc afferenza";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);
