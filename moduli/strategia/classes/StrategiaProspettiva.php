<?php
class StrategiaProspettiva extends Entity {    
    protected static $tablename = "strategia_prospettiva";

    public static function getProspettiveAnno(AnnoBudget $anno) {
        $prospettive_strategia_anno = array();
        foreach (StrategiaProspettiva::getAll() as $prospettiva) {
            if ($prospettiva->anno_introduzione <= $anno->descrizione && ($prospettiva->anno_termine == 0 || $prospettiva->anno_termine == null || $prospettiva->anno_termine >= $anno->descrizione)) {
                $prospettive_strategia_anno[] = $prospettiva;
            }
        }
        return $prospettive_strategia_anno;
    }
}
