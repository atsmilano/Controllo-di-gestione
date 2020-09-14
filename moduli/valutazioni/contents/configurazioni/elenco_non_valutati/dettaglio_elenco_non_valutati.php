<?php
if (isset($_REQUEST["periodo"])) {
    $id_periodo = $_REQUEST["periodo"];
    
    try {
        $valutazioni_periodo = new ValutazioniPeriodo($id_periodo);
    } catch (Exception $ex) {
        die(json_encode("Impossibile creare il periodo con ID $id_periodo"));
    }
}

if (isset($_REQUEST["matricola"])) {
    $matricola = $_REQUEST["matricola"];
    
    try {
        $personale = Personale::factoryFromMatricola($matricola);
        $valutazioni_personale = new ValutazioniPersonale($personale->id, $valutazioni_periodo);
        //unset($personale);
    } catch (Exception $ex) {
        die(json_encode("Impossibile creare il personale con matricola $matricola"));
    }
}
else {
    die(json_encode("Errore nel passaggio dei parametri"));
}

$anno = $cm->oPage->globals["anno"]["value"];
$annoBudget = new ValutazioniAnnoBudget($anno->id);
$data_riferimento = $cm->oPage->globals["data_riferimento"]["value"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "matricola";
$oRecord->title = "Apertura scheda di valutazione per ".$valutazioni_personale->nome." ".$valutazioni_personale->cognome." (matr. $valutazioni_personale->matricola)";
$oRecord->resources[] = "matricola";
$oRecord->src_table = "valutazioni_valutazione_periodica";
$oRecord->allow_update = false;
$oRecord->allow_insert = !$valutazioni_periodo->existsValutazioniAttivePeriodoMatricola($valutazioni_personale->matricola);
$oRecord->buttons_options["insert"]["label"] = "Apri scheda di valutazione";
$oRecord->allow_delete = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_valutatore";
$oField->base_type = "Text";
$oField->label = "Valutatore:";
$oField->required = true;
$oField->default_value = new ffData($valutazioni_personale->valutatore_suggerito->matricola_responsabile, "Text");
$valutatori = array();

foreach (Personale::getAll() as $dipendente) {
    if ($dipendente->isAttivoInData($data_riferimento->format("Y-m-d"))) {
        $valutatori[] = array(
            new ffData($dipendente->matricola, "Number"),
            new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
        );
    }
}
$oField->extended_type = "Selection";
$oField->multi_pairs = $valutatori;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "categoria";
$oField->data_source = "ID_categoria";
$oField->base_type = "Number";
$oField->label = "Categoria:";
$oField->required = true;
$oField->default_value = new ffData($valutazioni_personale->categoria->id, "Number");
$categorie = array();
foreach($annoBudget->getCategorieAnno() as $categoria) {
    $categorie[] = array(
        new ffData($categoria->id, "Number"),
        new ffData($categoria->descrizione . " (" . $categoria->abbreviazione. ")", "Text"),
    );
}
$oField->extended_type = "Selection";
$oField->multi_pairs = $categorie;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["ID_periodo"] = new ffData($id_periodo, "Number");
$oRecord->insert_additional_fields["matricola_valutato"] = new ffData($matricola, "Number");

$oRecord->addEvent("on_do_action", "checkRelations");
$oRecord->addEvent("on_done_action", "checkAutovalutazione");

function checkRelations($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $id_periodo = $_REQUEST["periodo"];
            $valutazioni_periodo = new ValutazioniPeriodo($id_periodo);

            $matricola_valutato = $_REQUEST["matricola"];
            $personale = Personale::factoryFromMatricola($matricola_valutato);
            $valutazioni_personale = new ValutazioniPersonale($personale->id, $valutazioni_periodo);

            if ($valutazioni_periodo->existsValutazioniAttivePeriodoMatricola($valutazioni_personale->matricola)) {
                return CoreHelper::setError(
                    $oRecord,
                    "Scheda di valutazione esistente per ".$valutazioni_personale->nome." ".$valutazioni_personale->cognome." (matr. $valutazioni_personale->matricola)"
                    . "nel periodo di valutazione ".$valutazioni_periodo->descrizione
                );
            }
            break;
    }
}

function checkAutovalutazione($oRecord, $frmAction) {
    switch ($frmAction) {
        case "insert":
            $id_periodo = $_REQUEST["periodo"];
            $valutazioni_periodo = new ValutazioniPeriodo($id_periodo);
            $matricola_valutato = $_REQUEST["matricola"];

            $isAutovalutazioneAttiva = $valutazioni_periodo->getAutovalutazioneAttivaPeriodo(
                new ValutazioniCategoria($oRecord->form_fields["categoria"]->value->getValue())
            );

            if ($isAutovalutazioneAttiva) {
              $valutazione = new ValutazioniValutazionePeriodica();
              $valutazione->matricola_valutato = $matricola_valutato;
              $valutazione->matricola_valutatore = $matricola_valutato;
              $valutazione->id_periodo = $id_periodo;
              $valutazione->id_categoria = $oRecord->form_fields["categoria"]->value->getValue();
              $valutazione->save();
            }
            break;
    }
}
$cm->oPage->addContent($oRecord);