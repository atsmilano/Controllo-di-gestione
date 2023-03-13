<?php

class CoanDistretto extends Entity
{

    protected static $tablename = "coan_distretto";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanCdc",
            "keys" => array(
                "ID_distretto" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );
    
    public static function getAttiviAnno(AnnoBudget $anno)
    {
        $result = array();
        $cdc_attivi_anno = CoanCdc::getAttiviAnno($anno);

        foreach ($cdc_attivi_anno as $cdc) {
            $distretto = new CoanDistretto($cdc->id_distretto);
            if (!in_array($distretto, $result)) {
                $result[] = $distretto;
            }
        }

        return $result;
    }

}
