<?php
if (isset($_REQUEST["anno_budget"])) {
    $id_anno_budget = $_REQUEST["anno_budget"];
    $anno_budget = new AnnoBudget($id_anno_budget);
    
    $result = array();
    $result["cdr"] = CoanCdc::getCdrAssociatiCdc($anno_budget);
    $result["cdc_standard_regionali"] = CoanCdcStandardRegionale::getAttiviAnno($anno_budget);
    $result["distretti"] = CoanDistretto::getAttiviAnno($anno_budget);
    
    die(json_encode($result));
}
else {
    throw new Exception("Errore");
}