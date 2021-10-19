<?php
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];
$user = LoggedUser::getInstance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla modifica del valore storico.");
}

if (isset($_REQUEST["keys[ID_indicatori_valore_parametro_rilevato]"])) {
    $id_indicatore_valore_parametro_rilevato = $_REQUEST["keys[ID_indicatori_valore_parametro_rilevato]"];
    try {
        $valore_parametro_rilevato = new IndicatoriValoreParametroRilevato($id_indicatore_valore_parametro_rilevato);
        $canEdit = true;
        
        $parametro = new IndicatoriParametro($valore_parametro_rilevato->id_parametro);
        $periodo_rendicontazione = new ObiettiviPeriodoRendicontazione($valore_parametro_rilevato->id_periodo_rendicontazione);
        $periodo_cruscotto = new IndicatoriPeriodoCruscotto($valore_parametro_rilevato->id_periodo_cruscotto);
        
        if ($periodo_cruscotto !== NULL) {
            $data_riferimento = new DateTime($periodo_cruscotto->data_riferimento_fine);
        }
        if (!empty($valore_parametro_rilevato->codice_cdr)) {
            $cdr = AnagraficaCdr::factoryFromCodice($valore_parametro_rilevato->codice_cdr, $data_riferimento);
            $cdr_txt = $cdr->codice." - ".$cdr->descrizione;
        }
        else {
            $cdr_txt = "";
        }
    }
    catch (Exception $e) {
        ffErrorHandler::raise($e->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "storico-modify";
$oRecord->title = "Modifica";
$oRecord->resources[] = "storico";
$oRecord->src_table = "indicatori_valore_parametro_rilevato";
$oRecord->allow_update = $canEdit;
$oRecord->allow_delete = true;
$oRecord->allow_insert = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatori_valore_parametro_rilevato";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_parametro";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_rendicontazione";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_periodo_cruscotto";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parametro";
$oField->base_type = "Text";
$oField->label = "Parametro";
$oField->display_value = new ffData($parametro->nome, "Text");
$oField->control_type = "label";
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_rendicontazione";
$oField->base_type = "Text";
$oField->label = "Periodo rendicontazione";
$oField->display_value = new ffData($periodo_rendicontazione->descrizione, "Text");
$oField->control_type = "label";
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "periodo_cruscotto";
$oField->base_type = "Text";
$oField->label = "Periodo cruscotto";
$oField->display_value = new ffData($periodo_cruscotto->descrizione, "Text");
$oField->control_type = "label";
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cdr";
$oField->base_type = "Text";
$oField->label = "CdR";
$oField->display_value = new ffData($cdr_txt, "Text");
$oField->control_type = "label";
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "valore";
$oField->base_type = "Text";
$oField->label = "Valore";
$oField->required = true;
if (!$canEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "modificabile";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = array (
    array(
        new ffData("1", "Number"),
        new ffData("Si", "Text")
    ),
    array(
        new ffData("0", "Number"),
        new ffData("No", "Text")
    ),
);
$oField->label = "Modificabile";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_riferimento";
$oField->base_type = "DateTime";
$oField->label = "Data riferimento";
$oField->widget = "datepicker";
$oField->required = true;
if (!$canEdit) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_importazione";
$oField->base_type = "DateTime";
$oField->label = "Data importazione";
$oField->widget = "datepicker";
$oField->control_type = "label";
$oField->store_in_db = false;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);