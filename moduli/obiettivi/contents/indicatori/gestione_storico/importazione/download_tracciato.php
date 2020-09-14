<?php
$fogli = array();

$tracciato_parametro_rilevato = array();
foreach(IndicatoriValoreParametroRilevato::describe(array("Field" => array("ID", "data_importazione"))) as $field_description) {
    $tracciato_parametro_rilevato[] = $field_description->field;
}
$fogli["tracciato"] = array($tracciato_parametro_rilevato);

//costruzione matrici di dati per ogni foglio di lavoro
$first = true;
foreach(IndicatoriParametro::getAll() as $indicatore_parametro) {    
    $intestazione = array();
    $record= array();
    foreach ($indicatore_parametro as $attributo => $valore){
        if($first == true) {
            $intestazione[] = $attributo;
        }
        $record[] = $valore;
    }   
    if ($first == true) {
        $fogli["parametri"][] = $intestazione;
        $first = false;
    }
    $fogli["parametri"][] = $record;    
}

//periodi rendicontazione
$first = true;
foreach(ObiettiviPeriodoRendicontazione::getAll() as $periodo_rendicontazione) {    
    $intestazione = array();
    $record= array();
    foreach ($periodo_rendicontazione as $attributo => $valore){
        if($first == true) {
            $intestazione[] = $attributo;
        }
        $record[] = $valore;
    }   
    if ($first == true) {
        $fogli["periodi_rendicontazione"][] = $intestazione;
        $first = false;
    }
    $fogli["periodi_rendicontazione"][] = $record;    
}

//Periodi Cruscotto
$first = true;
foreach(IndicatoriPeriodoCruscotto::getAll() as $periodo_cruscotto) {    
    $intestazione = array();
    $record= array();
    foreach ($periodo_cruscotto as $attributo => $valore){
        if($first == true) {
            $intestazione[] = $attributo;
        }
        $record[] = $valore;
    }   
    if ($first == true) {
        $fogli["periodi_cruscotto"][] = $intestazione;
        $first = false;
    }
    $fogli["periodi_cruscotto"][] = $record;    
}

//estrazione in excel
CoreHelper::simpleExcelWriter("tracciato_storico_parametri", $fogli);