<?php
$fogli = array();

$tracciato_parametro_rilevato = array();
foreach(IndicatoriValoreParametroRilevato::describe(array("Field" => array("ID", "data_importazione"))) as $field_description) {
    $tracciato_parametro_rilevato[] = $field_description->field;
}
$fogli["tracciato"] = array($tracciato_parametro_rilevato);

//costruzione matrici di dati per ogni foglio di lavoro
$fogli["parametri"] = IndicatoriParametro::getMatriceDati();
$fogli["periodi_rendicontazione"] = ObiettiviPeriodoRendicontazione::getMatriceDati();
$fogli["periodi_cruscotto"] = IndicatoriPeriodoCruscotto::getMatriceDati();

//estrazione in excel
CoreHelper::simpleExcelWriter("tracciato_storico_parametri", $fogli);