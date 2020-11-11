<?php

class StrategiaAnno extends Entity {
    protected static $tablename = "strategia_anno";

    public static function getChiusuraAnno(AnnoBudget $anno) {
        $db = ffDB_Sql::factory();
        $strategie_anno = StrategiaAnno::getAll(array("ID_anno_budget" => $anno->id));
        if (count($strategie_anno) > 0) {
            return $strategie_anno[0]->data_chiusura_definizione_strategia;
        } else {
            return null;
        }
    }
}
