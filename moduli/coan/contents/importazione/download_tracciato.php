<?php
$fogli = array();

$tracciato_coan = array();
foreach(CoanConsuntivoPeriodo::describe(array("Field" => array("ID"))) as $field_description) {
    $tracciato_coan[] = $field_description->field;
}
$fogli["tracciato"] = array($tracciato_coan);

//costruzione matrici di dati per ogni tabella di supporto
$fogli["periodo"] = CoanPeriodo::getMatriceDati();
$fogli["FP_primo"] = CoanFpPrimo::getMatriceDati();
$fogli["FP_secondo"] = CoanFpSecondo::getMatriceDati();
$fogli["FP_terzo"] = CoanFpTerzo::getMatriceDati();
$fogli["FP_quarto"] = CoanFpQuarto::getMatriceDati();
$fogli["conti"] = CoanConto::getMatriceDati();
$fogli["cdc_standard_regionali"] = CoanCdcStandardRegionale::getMatriceDati();
$fogli["distretti"] = CoanDistretto::getMatriceDati();
$fogli["cdc"] = CoanCdc::getMatriceDati();    

//estrazione in excel
CoreHelper::simpleExcelWriter("tracciato_coan", $fogli);