<?php
class CdrCostiRicavi extends Cdr {
    //estrazione di tutti i conti associati ad un cdr nell'anno passato come parametro
    public function getContiAnno(AnnoBudget $anno) {
        $conti_attivi_anno = CostiRicaviConto::getAttiviAnno($anno);
        
        if (count($conti_attivi_anno) > 0) {
            $conti_associati = array();
            
            foreach ($conti_attivi_anno as $conto) {
                if ($conto->codice_cdr == $this->codice) {
                    $conti_associati[] = $conto;
                }
            }
            
            return $conti_associati;
        }
        else {
            return null;
        }
    }

    //estrazione di tutti i fattori produttivi associati ad un cdr nell'anno passato come parametro
    public function getFpAnno(AnnoBudget $anno) {
        $fp_cdr_anno = array();
        //la relazione è su cdr-conto, vengono quindi estratti tutti i conti del cdr ed 
        //estratti univocamente gli fp
        $conti_cdr_anno = $this->getContiAnno($anno);
        if (count($conti_cdr_anno) > 0) {
            $fp_cdr_anno = array();
            foreach ($conti_cdr_anno as $conto_cdr_anno) {
                $found = false;
                foreach ($fp_cdr_anno as $fp) {
                    if ($fp->id == $conto_cdr_anno->id_fp) {
                        $found = true;
                        break;
                    }
                }
                if ($found == false) {
                    $fp_cdr_anno[] = new CostiRicaviFp($conto_cdr_anno->id_fp);
                }
            }
        }

        return $fp_cdr_anno;
    }

    //estrazione di tutti i codici cdr che compaiono nei conti nell'anno
    public static function getCodiciCdrAssociatiContoAnno($anno) {
        $cdr_anno = array();
        //la relazione è su cdr-conto, vengono quindi estratti tutti i conti del cdr 
        //ed estratti univocamente gli fp
        $conti_anno = CostiRicaviConto::getAttiviAnno($anno);

        if (count($conti_anno) > 0) {
            $cdr_anno = array();
            foreach ($conti_anno as $conto_anno) {
                $found = false;
                foreach ($cdr_anno as $cdr) {
                    if ($cdr == $conto_anno->codice_cdr) {
                        $found = true;
                        break;
                    }
                }
                if ($found == false) {
                    $cdr_anno[] = $conto_anno->codice_cdr;
                }
            }
        }
        return $cdr_anno;
    }
}