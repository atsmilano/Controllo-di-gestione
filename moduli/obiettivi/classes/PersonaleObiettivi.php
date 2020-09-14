<?php
class PersonaleObiettivi extends Personale {
    public static function factoryFromMatricola($matricola) {
        $db = ffDb_Sql::factory();

        $sql = "
            SELECT personale.ID
            FROM personale
            WHERE personale.matricola = " . $db->toSql($matricola)
        ;
        $db->query($sql);
        if ($db->nextRecord()) {
            return new PersonaleObiettivi($db->getField("ID", "Number", true));
        }
        throw new Exception("Impossibile creare l'oggetto Personale con matricola = " . $matricola);
    }

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

}
