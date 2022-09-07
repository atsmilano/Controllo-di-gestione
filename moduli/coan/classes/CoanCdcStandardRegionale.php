<?php
class CoanCdcStandardRegionale extends Entity {
    protected static $tablename = "coan_cdc_standard_regionale";
    
    public static function getAttiviAnno(AnnoBudget $anno) {
        $result = array();
        $cdc_attivi_anno = CoanCdc::getAttiviAnno($anno);
        
        foreach($cdc_attivi_anno as $cdc) {
            $cdc_standard_regionale = new CoanCdcStandardRegionale($cdc->id_cdc_standard_regionale);
            if (!in_array($cdc_standard_regionale, $result)) {
                $result[] = $cdc_standard_regionale;
            }
        }
        
        return $result;
    }
    
    public function canDelete() {
        return empty(CoanCdc::getAll(["ID_cdc_standard_regionale" => $this->id]));
    }
}