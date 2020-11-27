<?php

class CostiRicaviImportoPeriodo extends Entity {
    protected static $tablename = "costi_ricavi_importo_periodo";

    public static function factoryFromPeriodoConto(CostiRicaviPeriodo $periodo, CostiRicaviConto $conto) {
        $filters = array(
            "ID_periodo" => $periodo->id,
            "ID_conto" => $conto->id,
        );
        $importi_periodo = CostiRicaviImportoPeriodo::getAll($filters);
        //getAll() restituirà al più un elemento
        if (count($importi_periodo) > 0) {
            return $importi_periodo[0];
        } else {
            return null;
        }
    }
}