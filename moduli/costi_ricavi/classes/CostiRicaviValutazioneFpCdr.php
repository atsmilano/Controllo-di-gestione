<?php

class CostiRicaviValutazioneFpCdr extends Entity {
    protected static $tablename = "costi_ricavi_valutazione_fp_cdr";

    public static function factoryFromPeriodoFpCdr(CostiRicaviPeriodo $periodo, CostiRicaviFp $fp, Cdr $cdr) {
        $filters = array(
            "ID_periodo" => $periodo->id,
            "ID_fp" => $fp->id,
            "codice_cdr" => $cdr->codice,
        );
        $valutazioni_periodo_fp_cdr = CostiRicaviValutazioneFpCdr::getAll($filters);
        //getAll() restituirà al più un elemento
        if (count($valutazioni_periodo_fp_cdr) > 0) {
            return $valutazioni_periodo_fp_cdr[0];
        } else {
            return null;
        }
    }
}