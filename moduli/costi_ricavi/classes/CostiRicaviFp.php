<?php

class CostiRicaviFp extends Entity {
    protected static $tablename = "costi_ricavi_fp";

    //vengono recuperati tutti i conti associati al fattore produttivo, 
    //eventualmente filtrati per singolo cdr
    public function getContiAssociatiAnno(AnnoBudget $anno, Cdr $cdr = null) {
        $conti_attivi_anno  = CostiRicaviConto::getAttiviAnno($anno);
        
        if (count($conti_attivi_anno) > 0) {
            $conti_associati = array();
            
            foreach ($conti_attivi_anno as $conto) {
                if ($conto->id_fp == $this->id) {
                    if ($cdr !== null) {
                        if ($conto->codice_cdr == $cdr->codice) {
                            $conti_associati[] = $conto;
                        }
                    }
                    else {
                        $conti_associati[] = $conto;
                    }
                }
            }

            return $conti_associati;            
        }
        else {
            return null;
        }
    }

    public static function getFpAnno(AnnoBudget $anno) {
        $fp_anno = array();
        //la relazione è su cdr-conto, vengono quindi estratti tutti i conti del cdr ed 
        //estratti univocamente gli fp
        $conti_anno = CostiRicaviConto::getAttiviAnno($anno);

        if (count($conti_anno) > 0) {
            $fp_anno = array();
            foreach ($conti_anno as $conto_anno) {
                $found = false;
                foreach ($fp_anno as $fp) {
                    if ($fp->id == $conto_anno->id_fp) {
                        $found = true;
                        break;
                    }
                }
                if ($found == false) {
                    $fp_anno[] = new CostiRicaviFp($conto_anno->id_fp);
                }
            }
        }

        return $fp_anno;
    }

    public function canDelete() {
        return empty(CostiRicaviConto::getAll(["ID_fp" => $this->id])) &&
            empty(CostiRicaviValutazioneFpCdr::getAll(["ID_fp" => $this->id]));
    }
}