<?php

class StrategiaCdrProgrammazioneStrategica extends Entity {
    protected static $tablename = "strategia_cdr_programmazione_strategica";

    public static function getCdrProgrammazioneStrategicaAnno(AnnoBudget $anno) {
        $cdr_anno = array();
        foreach (StrategiaCdrProgrammazioneStrategica::getAll() as $cdr) {
            if ($cdr->anno_inizio <= $anno->descrizione && ($cdr->anno_fine == null || $cdr->anno_fine >= $anno->descrizione)) {
                $cdr_anno[] = $cdr->codice_cdr;
            }
        }
        return $cdr_anno;
    }
}
