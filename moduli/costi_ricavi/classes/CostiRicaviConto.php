<?php

class CostiRicaviConto extends Entity {
    protected static $tablename = "costi_ricavi_conto";

    public function getImportiPeriodo(CostiRicaviPeriodo $periodo) {
        $importo_periodo = CostiRicaviImportoPeriodo::factoryFromPeriodoConto($periodo, $this);
        if ($importo_periodo == null) {
            return 0;
        } else {
            return $importo_periodo;
        }
    }

    public static function getAttiviAnno(AnnoBudget $anno) {
        $item_anno = array();
        
        foreach (CostiRicaviConto::getAll() as $item) {
            if ($item->anno_inizio <= $anno->descrizione && 
                ($item->anno_fine == 0 || $item->anno_fine >= $anno->descrizione)
            ) {
                $item_anno[] = $item;
            }
        }
        
        return $item_anno;
    }
    
    public function canDelete() {
        return empty(CostiRicaviImportoPeriodo::getAll(["ID_conto" => $this->id]));
    }
}