<?php
$grid_fields = array(
    "ID",
    "cognome",
    "nome",
    "matricola"
);
$grid_recordset = array();
foreach (Personale::getAll() as $dipendente) {
    $grid_recordset[] = array(
        $dipendente->id,
        $dipendente->cognome,
        $dipendente->nome,
        $dipendente->matricola
    );
}
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "personale";
$oGrid->title = "Elenco del personale";
$oGrid->resources[] = "personale";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "personale");
$oGrid->order_default = "cognome";
$oGrid->record_id = "personale-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_personale";
$oGrid->order_method = "labels";
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "checkAnomalie");

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
$oField->order_SQL = "cognome ASC, nome ASC";
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

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

/*
 * Viene verificata la presenza delle seguenti anomalie:
 * 1) Personale senza posizioni sui CdC
 * 2) Personale senza posizioni attive sui CdC
 * 3) Personale con somma percentuali su posizioni attive < 100%
 * 4) Personale senza carriere
 * 5) Personale senza carriere in corso
 * 6) Personale con sovrapposizioni temporali tra carriere
 */
function checkAnomalie($oGrid) {
    $cm = cm::getInstance();
    $dateRiferimentoObject = $cm->oPage->globals["data_riferimento"]["value"];

    $personale = new Personale($oGrid->key_fields["id_personale"]->value->getValue());

    // Verifica che la somma delle percentuali delle posizioni attive sia uguale al 100%, incaso contrario viene generata anomalia
    $cdc_personale_list = CdcPersonale::getAll(array("matricola_personale" => $personale->matricola));

    $anomalia_cdc = false;
    $anomalia_carriera = false;
    if (empty($cdc_personale_list)) {
        // anomalia - non ci sono posizioni attive sui CdC
        $anomalia_cdc = true;
    }
    else { 
        //vincolo non implementato
        /*$percentuale_attiva = 0;
        foreach ($cdc_personale_list as $cdc_personale) {
            $dataFineObject = DateTime::createFromFormat("Y-m-d", $cdc_personale->data_fine);
            // verifica sulle voci attive
            if ((!isset($cdc_personale->data_fine)) ||
                (isset($cdc_personale->data_fine) && $dataFineObject >= $dateRiferimentoObject)
            ) {
                $percentuale_attiva += $cdc_personale->percentuale;
            }
        }

        if ($percentuale_attiva < 100) {
            // anomalia - la percentuale attiva è inferiore a 100
            $anomalia_cdc = true;
        }*/
        $anomalia_cdc = false;
    }

    // Verifiche sulla carriera
    $carriera_personale_list = CarrieraPersonale::getAll(array("matricola_personale" => $personale->matricola));
    if (empty($carriera_personale_list)) {
        // anomalia - nessuna voce di carriera
        $anomalia_carriera = true;
    }
    else {       
        foreach ($carriera_personale_list as $key => $carriera) {
            $dataFineObject = DateTime::createFromFormat("Y-m-d", $carriera->data_fine);
            //Verifica su eventuali sovrapposizioni
            if(sovrapposizioni($carriera_personale_list, $carriera, $key)) {
                $anomalia_carriera = true;
                break;
            }
            // Viene verificato che le voci di carriera siano attive
            if (!((!isset($carriera->data_fine)) ||
                (isset($carriera->data_fine) && $dataFineObject >= $dateRiferimentoObject)
            )) {
                $anomalia_carriera = true;
            } else {
                $anomalia_carriera = false;
                break;
            }
        }
    }

    if($anomalia_cdc || $anomalia_carriera) {
        $oGrid->row_class = "anomalia";
    } else {
        $oGrid->row_class = "";
    }
}

/**
 * Verifica della presenza di sovrapposizioni confrontando i valori degli elementi dello stesso array
 * evitando di ripetere il controllo nel caso in cui una coppia sia già stata valutata nell'iterazione
 * precedente 
 */
function sovrapposizioni($carriera_personale_list, $carriera_attuale, $i) {
    //partenza di $j da $i+1, per evitare controlli già effettuati e controlli sullo stesso elemento
    for($j = $i+1; $j < count($carriera_personale_list); $j++) {
        $carriera = $carriera_personale_list[$j];

        //Creazione degli oggetti DateTime per le date carriera
        $data_inizio = isset($carriera->data_inizio) ?
            DateTime::createFromFormat("Y-m-d", $carriera->data_inizio) :
            null;
        $data_fine = isset($carriera->data_fine) ?
            DateTime::createFromFormat("Y-m-d", $carriera->data_fine) :
            null;
        $data_inizio_attuale = isset($carriera_attuale->data_inizio) ?
            DateTime::createFromFormat("Y-m-d", $carriera_attuale->data_inizio) :
            null;
        $data_fine_attuale = isset($carriera_attuale->data_fine) ?
            DateTime::createFromFormat("Y-m-d", $carriera_attuale->data_fine) :
            null;

        //Definizione dei casi di non sovrapposizione
        $inizioFineAttualePrec = $data_inizio_attuale < $data_inizio &&
            isset($data_fine_attuale) && $data_fine_attuale < $data_inizio;
        $fineEsistenteInizioAttualeSucc = isset($data_fine) &&  $data_inizio_attuale > $data_fine;

        //Verifica su eventuale situazione che viola le condizioni di non sovprapposizione
        if (!($inizioFineAttualePrec || $fineEsistenteInizioAttualeSucc)) {
            return true;
        }
    }
    return false;
}