<?php
if (isset($_REQUEST["periodo_select"])) {
    $periodo_coan = new CoanPeriodo($_REQUEST["periodo_select"]);
    $anno_budget = new AnnoBudget($periodo_coan->id_anno_budget);
    
    $result = array();
    $result["cdr"] = CoanCdc::getCdrAssociatiCdc($periodo_coan);
    $result["cdc_standard_regionali"] = CoanCdcStandardRegionale::getAttiviAnno($anno_budget);
    $result["distretti"] = CoanDistretto::getAttiviAnno($anno_budget);
    
    die(json_encode($result));
}
else {
    throw new Exception("Errore");
}