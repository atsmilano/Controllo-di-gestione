<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_cdc]"])) {
    $isEdit = true;
    $id_cdc = $_REQUEST["keys[ID_cdc]"];

    try {
        $cdc = new CoanCdc($id_cdc);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdc-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Cdc";
$oRecord->resources[] = "cdc";
$oRecord->src_table  = "coan_cdc";
$oRecord->allow_delete = !$isEdit || ($isEdit && $cdc->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdc";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oRecord->addContent($oField);

$cdc_standard_regionale_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach(CoanCdcStandardRegionale::getAll(array(), $order) as $item) {
    $cdc_standard_regionale_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." - ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdc_standard_regionale";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdc_standard_regionale_select;
$oField->required = true;
$oField->label = "Cdc standard regionale";
$oRecord->addContent($oField);

$cdr_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach(AnagraficaCdr::getAll(array(), $order) as $cdr) {
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    $intervallo_validita = " (valido dal ".CoreHelper::formatUiDate($cdr->data_introduzione);
    if ($cdr->data_termine !== null) {
        $intervallo_validita .= " al ".CoreHelper::formatUiDate($cdr->data_termine);
    }
    $intervallo_validita .= ")";
    
    $cdr_select[] = array(
        new ffData($cdr->codice, "Text"),
        new ffData($cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione.$intervallo_validita, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "codice_cdr";
$oField->base_type = "Text";
$oField->label = "Codice CdR";
$oField->extended_type = "Selection";
$oField->multi_pairs = $cdr_select;
$oField->required = true;
$oRecord->addContent($oField);

$distretto_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach(CoanDistretto::getAll(array(), $order) as $item) {
    $distretto_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." - ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_distretto";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $distretto_select;
$oField->required = true;
$oField->label = "Distretto";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";
$oField->label = "Anno introduzione";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";
$oField->label = "Anno termine";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_termine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (CoanCdc::getAll(["codice" => $codice]) as $item) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $item->anno_introduzione, $item->anno_termine
                )) {
                    CoreHelper::setError($oRecord, "Codice già utilizzato per l'intervallo definito.");
                    break;
                }
            }

            $date_start = new DateTime(date($anno_inizio."-m-d"));
            if ($anno_inizio != date("Y")) {
                $date_start = new DateTime($anno_inizio."-01-01");
            }
            $anno_fine = $anno_fine == 0 ? 9999 : $anno_fine;      
            $date_end = new DateTime($anno_fine."-12-31");
            
            if (!AnagraficaCdr::isCdrInInterval($codice_cdr, $date_start, $date_end)) {
                CoreHelper::setError($oRecord, "CdR non valido nell'intervallo definito.");
            }
            
            break;
        case "update":
            $id_cdc = $oRecord->key_fields["ID_cdc"]->value->getValue();

            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_introduzione"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_termine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (CoanCdc::getAll(["codice" => $codice]) as $item) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $item->anno_introduzione, $item->anno_termine
                ) && $id_cdc != $item->id) {
                    CoreHelper::setError($oRecord, "Codice già utilizzato per l'intervallo definito.");
                    break;
                }
            }

            $date_start = new DateTime(date($anno_inizio."-m-d"));
            if ($anno_inizio != date("Y")) {
                $date_start = new DateTime($anno_inizio."-01-01");
            }
            $anno_fine = $anno_fine == 0 ? 9999 : $anno_fine;      
            $date_end = new DateTime($anno_fine."-12-31");
            
            if (!AnagraficaCdr::isCdrInInterval($codice_cdr, $date_start, $date_end)) {
                CoreHelper::setError($oRecord, "CdR non valido nell'intervallo definito.");
            }
            
            break;
        case "delete":
        case "confirmdelete":
            $id_cdc = $oRecord->key_fields["ID_cdc"]->value->getValue();
            $cdc = new CoanCdc($id_cdc);
            
            if (!$cdc->canDelete()) {
                CoreHelper::setError($oRecord, "Cdc utilizzato in consuntivo periodico: impossibile eliminare.");   
            }
            
            break;
    }
}