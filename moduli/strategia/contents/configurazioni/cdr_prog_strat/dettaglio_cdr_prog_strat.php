<?php
$isEdit = false;
if (isset($_REQUEST["keys[ID_cdr_prog_strategica]"])) {
    $isEdit = true;
    $id_cdr_prog_strategica = $_REQUEST["keys[ID_cdr_prog_strategica]"];

    try {
        $cdr_ps = new StrategiaCdrProgrammazioneStrategica($id_cdr_prog_strategica);
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdr-prog-strategica-modify";
$oRecord->title = ($isEdit ? "Modifica" : "Nuovo") . " CdR Programmazione Strategica";
$oRecord->resources[] = "cdr-prog-strategica";
$oRecord->src_table  = "strategia_cdr_programmazione_strategica";
$oRecord->allow_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_cdr_prog_strategica";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->label = "Anno inizio";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";
$oField->label = "Anno fine";
$oRecord->addContent($oField);

$cdr_select = array();
foreach(AnagraficaCdr::getAll() as $cdr) {
    $intervallo_validita = " (valido dal ".CoreHelper::formatUiDate($cdr->data_introduzione);
    if ($cdr->data_termine !== null) {
        $intervallo_validita .= " al ".CoreHelper::formatUiDate($cdr->data_termine);
    }
    $intervallo_validita .= ")";
    
    $cdr_select[] = array(
        new ffData($cdr->codice, "Text"),
        new ffData($cdr->codice ." - ".$cdr->descrizione.$intervallo_validita, "Text")
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

$oRecord->addEvent("on_do_action", "validateInput");

$cm->oPage->addContent($oRecord);

function validateInput($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $anno_inizio = $oRecord->form_fields["anno_inizio"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();

            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord,
                    "L'anno fine deve essere maggiore o uguale dell'anno inizio."
                );
                break;
            }

            foreach (StrategiaCdrProgrammazioneStrategica::getAll(["codice_cdr" => $codice_cdr]) as $item) {
                if (!CoreHelper::verificaNonSovrapposizioneIntervalliAnno(
                    $anno_inizio, $anno_fine,
                    $item->anno_inizio, $item->anno_fine
                )) {
                    CoreHelper::setError(
                        $oRecord,
                        "CdR già definito come CdR di programmazione strategica nell'intervallo definito."
                    );
                    break;
                }
            }

            $date_start = new DateTime(date($anno_inizio."-m-d"));            
            $anno_fine = $anno_fine == null ? 9999 : $anno_fine;      
            $date_end = new DateTime($anno_fine."-12-31"); 
            if (!AnagraficaCdr::isCdrInInterval($codice_cdr, $date_start, $date_end)) {
                CoreHelper::setError($oRecord, "CdR non valido nell'intervallo definito.");
            }
            
            break;
        case "update":
            $id_cdr_prog_strategica = $oRecord->key_fields["ID_cdr_prog_strategica"]->value->getValue();
            
            $codice_cdr = $oRecord->form_fields["codice_cdr"]->value->getValue();
            $anno_inizio = $oRecord->form_fields["anno_inizio"]->value->getValue();
            $anno_fine = $oRecord->form_fields["anno_fine"]->value->getValue();                                  
            
            if (!CoreHelper::verificaIntervalloAnni($anno_inizio, $anno_fine)) {
                CoreHelper::setError(
                    $oRecord,
                    "L'anno fine deve essere maggiore o uguale dell'anno inizio."                     
                );
                break;
            }
            
            foreach (StrategiaCdrProgrammazioneStrategica::getAll(["codice_cdr" => $codice_cdr]) as $item) {                
                if ($item->id != $id_cdr_prog_strategica){
                    if(!CoreHelper::verificaNonSovrapposizioneIntervalliAnno($anno_inizio, $anno_fine,$item->anno_inizio, $item->anno_fine)) {
                        CoreHelper::setError(
                            $oRecord,
                            "CdR già definito come CdR di programmazione strategica nell'intervallo definito."
                        );
                    break;                  
                    }
                }
            }                        

            $date_start = new DateTime(date($anno_inizio."-m-d"));
            $anno_fine = $anno_fine == null ? 9999 : $anno_fine;      
            $date_end = new DateTime($anno_fine."-12-31");            
            if (!AnagraficaCdr::isCdrInInterval($codice_cdr, $date_start, $date_end)) {
                CoreHelper::setError($oRecord, "CdR non valido nell'intervallo definito.");
            }
            
            break;
    }
}