<?php
class AnagraficaCdrObiettivi extends AnagraficaCdr {
    //restituisce tutti gli obiettivi non eliminati logicamente per l'anno
    public function getObiettiviCdrAnno(AnnoBudget $anno) {
        $ob_obiettivi_cdr_anno = array();        
        
        $obiettivi_cdr = ObiettiviObiettivoCdr::getAll(array("codice_cdr" => $this->codice));
        foreach(ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo_anno) {
            foreach($obiettivi_cdr as $obiettivo_cdr) {                                                
                if ($obiettivo_anno->data_eliminazione == null
                    && $obiettivo_cdr->data_eliminazione == null
                    && $obiettivo_anno->id == $obiettivo_cdr->id_obiettivo) {
                    $ob_obiettivi_cdr_anno[] = $obiettivo_cdr;
                } 
            }            
        }

        return $ob_obiettivi_cdr_anno;
    }

    //restituisce il peso tottale degli obiettivi assegnati al cdr in un anno di budget, eventualmente escluso l'obiettivo passato come secondo parametro
    //le verifiche sulla data eliminazione vengono effettuate in getObiettiviCdrAnno
    //è possibile passare come parametro un array di oggetti di tipo ObiettiviObiettivoCdr
    //rappresentante gli obiettivi cdr assegnati oer il calcolo del peso
    public function getPesoTotaleObiettivi(AnnoBudget $anno, ObiettiviObiettivo $obiettivo = null, $obiettivi_cdr_anno = null) {
        $peso_totale_cdr = 0;
        if ($obiettivi_cdr_anno == null) {
            $obiettivi_cdr_anno = $this->getObiettiviCdrAnno($anno);
        }
        foreach ($obiettivi_cdr_anno as $obiettivo_cdr) {
            if ($obiettivo == null || $obiettivo->id !== $obiettivo_cdr->id_obiettivo) {
                $peso_totale_cdr += $obiettivo_cdr->peso;
            }
        }
        return $peso_totale_cdr;
    }

    //restituisce i cdr ai quali sono stati assegnati obiettivi aziendali (anche in coreferenza) non eliminati logicamente in un anno di budget
    public static function getCdrObiettiviAziendali(AnnoBudget $anno) {
        $date = CoreHelper::getDataRiferimentoBudget($anno);
        $cdr_obiettivi_aziendali = array();
        foreach (ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo_anno) {
            if ($obiettivo_anno->data_eliminazione == null) {
                foreach ($obiettivo_anno->getObiettivoCdrAssociati() as $obiettivo_cdr_anno) {
                    //non viene utilizzato il metodo isAziendale ma viene discriminato sul tipo piano cdr per considerare anche gli obiettivi di coreferenza
                    if ($obiettivo_cdr_anno->id_tipo_piano_cdr == 0 && $obiettivo_cdr_anno->data_eliminazione == null) {
                        $found = false;
                        foreach ($cdr_obiettivi_aziendali as $cdr_considerato) {
                            if ($obiettivo_cdr_anno->codice_cdr == $cdr_considerato->codice) {
                                $found = true;
                                break;
                            }
                        }
                        if ($found == false) {
                            $cdr_obiettivi_aziendali[] = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_anno->codice_cdr, $date);
                        }
                    }
                }
            }
        }
        return $cdr_obiettivi_aziendali;
    }

    //restituisce true se il cdr passato come argomento ha almeno un obiettivo aziendale
    public function hasObiettiviAziendali(AnnoBudget $anno) {
        foreach ($this->getObiettiviCdrAnno($anno) as $obiettivo_anno) {
            if ($obiettivo_anno->isObiettivoCdrAziendale()) {
                return true;
            }
        }
        return false;
    }

    //restituisce tutti gli indicatori del cdr del cruscotto per l'anno o l'indicatore specifico se passato come parametro
    public function getIndicatoriCruscottoAnno(AnnoBudget $anno, IndicatoriIndicatore $indicatore = null) {
        $indicatori_cruscotto_anno = array();
        $filters = array(
            "ID_anno_budget" => $anno->id,
            "codice_cdr" => $this->codice,
        );
        if ($indicatore !== null) {
            $filters["ID_indicatore"] = $indicatore->id;
        }
        foreach (IndicatoriIndicatoreCdrCruscottoAnno::getAll($filters) as $indicatore_cruscotto_anno) {
            $result = $indicatore_cruscotto_anno;
            $result->indicatore = new IndicatoriIndicatore($indicatore_cruscotto_anno->id_indicatore);
            $indicatori_cruscotto_anno[] = $result;
        }
        return $indicatori_cruscotto_anno;
    }
}