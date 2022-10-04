<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_conto]"])) {
    $isEdit = true;
    $id_conto = $_REQUEST["keys[ID_conto]"];

    try {
        $conto = new CostiRicaviConto($id_conto);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "conto-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " Conto";
$oRecord->resources[] = "conto";
$oRecord->src_table  = "costi_ricavi_conto";
$oRecord->allow_delete = !$isEdit || ($isEdit && $conto->canDelete());

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_conto";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->required = true;
$oField->label = "Codice";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->required = true;
$oField->label = "Descrizione";
$oRecord->addContent($oField);

$fp_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach (CostiRicaviFp::getAll(array(), $order) as $item) {
    $fp_select[] = array(
        new ffData($item->id, "Number"),
        new ffData($item->codice." ".$item->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fp";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $fp_select;
$oField->required = true;
$oField->label = "Fattore produttivo";
$oRecord->addContent($oField);

$cdr_select = array();
$order = array(array("fieldname"=>"codice", "direction"=>"ASC"));
foreach(AnagraficaCdr::getAll(array(), $order) as $cdr) {
    $intervallo_validita = " (valido dal ".CoreHelper::formatUiDate($cdr->data_introduzione);
    if ($cdr->data_termine !== null) {
        $intervallo_validita .= " al ".CoreHelper::formatUiDate($cdr->data_termine);
    }
    $intervallo_validita .= ")";
    
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
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
    
$oField = ffField::factory($cm->oPage);
$oField->id = "evidenza";
$oField->base_type = "Text";
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
$oField->label = "Evidenza";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->required = true;
$oField->label = "Anno inizio";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";
$oField->label = "Anno fine";
$oRecord->addContent($oField);

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_inizio"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (CostiRicaviConto::getAll(["codice" => $codice]) as $item) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $item->anno_inizio, $item->anno_fine
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
            $id_conto = $oRecord->key_fields["ID_conto"]->value->getValue();
            
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();
            $codice = $oRecord->form_fields["codice"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_inizio"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord, 
                    "L'anno termine deve essere maggiore o uguale dell'anno introduzione."
                );
            }
            
            foreach (CostiRicaviConto::getAll(["codice" => $codice]) as $item) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine, 
                    $item->anno_fine, $item->anno_fine
                ) && $id_conto != $item->id) {
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
            $id_conto = $oRecord->key_fields["ID_conto"]->value->getValue();
            $conto = new CostiRicaviConto($id_conto);
            
            if (!$conto->canDelete()) {
                CoreHelper::setError($oRecord, "Impossibile eliminare Conto perché in uso.");   
            }
            break;
    }
}