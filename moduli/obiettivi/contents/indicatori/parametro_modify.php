<?php
$cm->oPage->widgetLoad("dialog");
$tmp2 = $cm->oPage->widgets["dialog"]->process(
    "view_storico_dialog" // id del dialog
    , array( // proprietà  del dialog
        "name" => "view_storico_dialog"
        , "title" => "Storico parametri"
        , "url" => ""
        , "callback" => "",
    )
    , $cm->oPage // oggetto pagina associato
);

$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));

$parametro = null;
if (isset($_REQUEST["keys[ID]"])) {
    $id_parametro = $_REQUEST["keys[ID]"];
    
    try {
        $parametro = new IndicatoriParametro($id_parametro);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "parametro-modify";
$oRecord->title = "Parametro";
$oRecord->resources[] = "indicatore-parametro";
$oRecord->src_table = "indicatori_parametro";

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

$oRecord->addContent(null, true, "definizione");
$oRecord->groups["definizione"]["title"] = "Definizione parametro";

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oField->required = true;
$oRecord->addContent($oField, "definizione");

$tipi_parametro = array();
foreach (IndicatoriTipoParametro::getAll() AS $tipo_parametro) {
    $tipi_parametro[] = array(
        new ffData($tipo_parametro->id, "Number"),
        new ffData($tipo_parametro->nome, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo_parametro";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipi_parametro;
$oField->label = "Tipo parametro";
$oField->required = true;
$oRecord->addContent($oField, "definizione");

$oRecord->addContent(null, true, "validita");
$oRecord->groups["validita"]["title"] = "Validità temporale";

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno inizio validità";
$oField->required = true;
$oRecord->addContent($oField, "validita");

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";
$oField->label = "Anno termine validità";
$oRecord->addContent($oField, "validita");

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "view_storico_parametro";
$oBt->label = "Storico parametro";
$oBt->action_type = "submit";
$oBt->aspect = "link";
$oBt->class = "fas fa-camera";
$oBt->jsaction = "gotoStoricoParametri($id_parametro, '', '', '')";
$oRecord->addActionButton($oBt);

if ($parametro !== null) {
    $cm->oPage->addContent(
        $parametro->jsGotoStoricoParametri(
            (FF_SITE_PATH . $path_info . "gestione_storico/storico?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST)),
            "view_storico_dialog"
        )
    );
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);