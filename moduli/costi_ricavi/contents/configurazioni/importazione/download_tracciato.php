<?php
$fogli = array();

$tracciato_costi_ricavi = array();
foreach(CostiRicaviImportoPeriodo::describe(array("Field" => array("ID"))) as $field_description) {
    $tracciato_costi_ricavi[] = $field_description->field;
}
$fogli["tracciato"] = array($tracciato_costi_ricavi);

//costruzione matrici di dati per ogni tabella di supporto
$fogli["anni_budget"] = AnnoBudget::getMatriceDati();
$tipi_periodo = array();
$tipi_periodo[] = array("ID", "descrizione");
foreach(CostiRicaviPeriodo::$tipo_periodo as $id => $tipo_periodo){
    $tipi_periodo[] = array($id, $tipo_periodo);
}
$fogli["tipo_periodo"] = $tipi_periodo;
$fogli["periodi"] = CostiRicaviPeriodo::getMatriceDati();
$fogli["fp"] = CostiRicaviFp::getMatriceDati();
$fogli["conti"] = CostiRicaviConto::getMatriceDati();

//estrazione in excel
CoreHelper::simpleExcelWriter("tracciato_costi_ricavi", $fogli);