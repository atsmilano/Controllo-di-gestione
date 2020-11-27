<?php
class CoanDistretto extends Entity {
    protected static $tablename = "coan_distretto";
    
    public static function getAttiviAnno(AnnoBudget $anno) {
        $result = array();
        $cdc_attivi_anno = CoanCdc::getAttiviAnno($anno);
        
        foreach($cdc_attivi_anno as $cdc) {
            $distretto = new CoanDistretto($cdc->id_distretto);
            if (!in_array($distretto, $result)) {
                $result[] = $distretto;    
            }
        }
        
        return $result;
    }
    
    public function canDelete() {
        return empty(CoanCdc::getAll(["ID_distretto" => $this->id]));
    }
}