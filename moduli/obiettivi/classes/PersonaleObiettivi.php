<?php
class PersonaleObiettivi extends Personale {    
    public function getObiettiviCdrPersonaleAnno(AnnoBudget $anno) {
        $ob_personale_associati = array();
        $db = ffDB_Sql::factory();

        $sql = "
            SELECT obiettivi_obiettivo_cdr_personale.*
            FROM obiettivi_obiettivo_cdr_personale
                INNER JOIN obiettivi_obiettivo_cdr 
                    ON obiettivi_obiettivo_cdr_personale.ID_obiettivo_cdr = obiettivi_obiettivo_cdr.ID
                INNER JOIN obiettivi_obiettivo
                    ON obiettivi_obiettivo_cdr.ID_obiettivo = obiettivi_obiettivo.ID
            WHERE 
                obiettivi_obiettivo.ID_anno_budget = " . $db->toSql($anno->id) . "
                AND obiettivi_obiettivo_cdr_personale.matricola_personale = " . $db->toSql($this->matricola) . "
        ";
        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $ob_personale_associato = new ObiettiviObiettivoCdrPersonale();
                $ob_personale_associato->id = $db->getField("ID", "Number", true);
                $ob_personale_associato->id_obiettivo_cdr = $db->getField("ID_obiettivo_cdr", "Number", true);
                $ob_personale_associato->matricola_personale = $db->getField("matricola_personale", "Text", true);
                $ob_personale_associato->peso = $db->getField("peso", "Text", true);
                //ultima_modifica
                $ob_personale_associato->data_ultima_modifica = CoreHelper::getDateValueFromDB($db->getField("data_ultima_modifica", "Date", true));
                $ob_personale_associato->data_accettazione = CoreHelper::getDateValueFromDB($db->getField("data_accettazione", "Date", true));
                $ob_personale_associato->data_eliminazione = CoreHelper::getDateValueFromDB($db->getField("data_eliminazione", "Date", true));

                $ob_personale_associati[] = $ob_personale_associato;
            } while ($db->nextRecord());
        }
        return $ob_personale_associati;
    }

    //restituisce il peso totale degli obiettivi assegnati al dipendente in un anno di budget, eventualmente escluso l'obiettivo passato come secondo parametro
    public function getPesoTotaleObiettivi(AnnoBudget $anno, ObiettiviObiettivoCdr $obiettivo_cdr = null) {
        $peso_totale_personale = 0;
        foreach ($this->getObiettiviCdrPersonaleAnno($anno) as $obiettivo_cdr_personale) {
            if ($obiettivo_cdr_personale->data_eliminazione == null &&
                (
                $obiettivo_cdr == null ||
                (
                $obiettivo_cdr->data_eliminazione == null &&
                $obiettivo_cdr->id !== $obiettivo_cdr_personale->id_obiettivo_cdr
                )
                )
            ) {
                $peso_totale_personale += $obiettivo_cdr_personale->peso;
            }
        }
        return $peso_totale_personale;
    }
    
    //restituisce tutti gli obiettivi nell'anno dei cdr di competenza alla data
    public function getObiettiviCdrReponsabilitaData (AnnoBudget $anno, DateTime $date, TipoPianoCdr $tipo_piano) {
        $obiettivi_responsabilità = array();            
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
        foreach ($this->getCdrResponsabilitaPiano($piano_cdr, $date) as $cdr_resp) {     
            $cdr_resp_anno = AnagraficaCdrObiettivi::factoryFromCodice($cdr_resp["cdr"]->codice, $date);            
            foreach ($cdr_resp_anno->getObiettiviCdrAnno($anno) as $ob_cdr_resp) {											                
                //viene verificato che l'obiettivo sia già stato accettato dal dipendente
                if ($obiettivo_cdr->data_eliminazione == null && $ob_cdr_resp->data_chiusura_modifiche !== null && strtotime(date("Y-m-d")) >= strtotime($ob_cdr_resp->data_chiusura_modifiche)) {
                    $obiettivi_responsabilità[] = array(
                                                        "obiettivo" => new ObiettiviObiettivo($ob_cdr_resp->id_obiettivo),
                                                        "obiettivo_cdr" => $ob_cdr_resp,
                                                        "anagrafica_cdr_obiettivo" => $cdr_resp_anno,
                                                        );                                     
                }                
            }
        }
        return $obiettivi_responsabilità;
    }

    //restituisce tutti gli obiettivi (univoci) assegnati ai Cdr del responsabile in una data specifica
    public function getObiettiviReponsabilitaData (AnnoBudget $anno, DateTime $date, TipoPianoCdr $tipo_piano) {
        $obiettivi_responsabilità = array();            
        $piano_cdr = PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
        foreach ($this->getCdrResponsabilitaPiano($piano_cdr, $date) as $cdr_resp) {     
            $cdr_resp_anno = AnagraficaCdrObiettivi::factoryFromCodice($cdr_resp["cdr"]->codice, $date);            
            foreach ($cdr_resp_anno->getObiettiviCdrAnno($anno) as $ob_cdr_resp) {											                
                //vengono considerati solamente gli obiettivi confermati da parte del cdr                
                if ($obiettivo_cdr->data_eliminazione == null) {
                    $found = false;
                    foreach ($obiettivi_responsabilità as $obiettivo_responsabilità) {
                        if ($ob_cdr_resp->id_obiettivo == $obiettivo_responsabilità->id) {
                            $found = true;
                            break;
                        }
                    }
                    if (!($found == true)) {
                        $obiettivi_responsabilità[] = new ObiettiviObiettivo($ob_cdr_resp->id_obiettivo);
                    }
                }
            }
        }
        return $obiettivi_responsabilità;
    }
}
