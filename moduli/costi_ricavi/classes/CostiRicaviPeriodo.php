<?php

class CostiRicaviPeriodo extends Entity {
    protected static $tablename = "costi_ricavi_periodo";

    public static $tipo_periodo = array(
        1 => "Apertura",
        2 => "Intermedio",
        3 => "Chiusura",
    );

    //restituisce l'ultimo periodo di rendicontazione definito nell'anno (null se nessuno definito)
    public static function getUltimoDefinitoAnno(AnnoBudget $anno) {
        //viene considerato l'ultimo periodo attivo nell'anno per la data selezionata
        $periodi = CostiRicaviPeriodo::getAll(array("ID_anno_budget" => $anno->id));
        if (count($periodi) > 0) {
            return end($periodi);
        }
        else {
            return null;
        }
    }

    public function canDelete() {
        return empty(CostiRicaviImportoPeriodo::getAll(["ID_periodo" => $this->id])) &&
            empty(CostiRicaviValutazioneFpCdr::getAll(["ID_periodo" => $this->id]));
    }
}