<?php

class AnagraficaCdr extends Entity {

    protected static $tablename = "anagrafica_cdr";

    //metodo per istanziare l'oggetto da codice cdr
    public static function factoryFromCodice($codice, DateTime $date) {
        $calling_class_name = static::class;
        $cdr_anagrafica = null;

        $db = ffDb_Sql::factory();
        $sql = "
            SELECT anagrafica_cdr.*
            FROM anagrafica_cdr
            WHERE anagrafica_cdr.codice = " . $db->toSql($codice);

        $db->query($sql);
        if ($db->nextRecord()) {
            do {
                $cdr_anagrafica = new $calling_class_name();

                $cdr_anagrafica->id = $db->getField("ID", "Number", true);
                $cdr_anagrafica->codice = $db->getField("codice", "Text", true);
                $cdr_anagrafica->descrizione = $db->getField("descrizione", "Text", true);
                $cdr_anagrafica->abbreviazione = $db->getField("abbreviazione", "Text", true);
                $cdr_anagrafica->id_tipo_cdr = $db->getField("ID_tipo_cdr", "Number", true);
                $cdr_anagrafica->data_introduzione = CoreHelper::getDateValueFromDB($db->getField("data_introduzione", "Date", true));
                $cdr_anagrafica->data_termine = CoreHelper::getDateValueFromDB($db->getField("data_termine", "Date", true));

                if (strtotime($cdr_anagrafica->data_introduzione) <= strtotime($date->format("Y-m-d")) && (
                    $cdr_anagrafica->data_termine == null ||
                    strtotime($cdr_anagrafica->data_termine) >= strtotime($date->format("Y-m-d"))
                    )) {
                    break;
                }
            } while ($db->nextRecord());
        }

        return $cdr_anagrafica;
    }

    //restituisce tutti i record dell'anagrafica attivi in una data specifica
    public static function getAnagraficaInData(DateTime $date) {
        $anagrafica_data = array();
        foreach (AnagraficaCdr::getAll() AS $cdr_anagrafica) {
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($cdr_anagrafica->data_introduzione) <= strtotime($date->format("Y-m-d")) && ($cdr_anagrafica->data_termine == null || strtotime($cdr_anagrafica->data_termine) >= strtotime($date->format("Y-m-d")))) {
                $anagrafica_data[] = $cdr_anagrafica;
            }
        }
        return $anagrafica_data;
    }

    //restituisce tutti i record dell'anagrafica attivi in un anno specifico
    public static function getAnagraficaAnno(AnnoBudget $anno) {
        $anagrafica_data = array();
        foreach (AnagraficaCdr::getAll() AS $cdr_anagrafica) {
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (strtotime($cdr_anagrafica->data_introduzione) <= strtotime($anno->descrizione . "-12-31") && ($cdr_anagrafica->data_termine == null || strtotime($cdr_anagrafica->data_termine) >= strtotime($anno->descrizione . "-01-01"))) {
                $anagrafica_data[] = $cdr_anagrafica;
            }
        }
        return $anagrafica_data;
    }

    public static function isCdrInInterval($codice_cdr, DateTime $date_start, DateTime $date_end) {
        foreach (AnagraficaCdr::getAll(["codice" => $codice_cdr]) AS $cdr_anagrafica) {
            //se la data inizio è precedente alla data corrente inclusa e la data fine è successiva alla data corrente inclusa)
            if (
                strtotime($cdr_anagrafica->data_introduzione) <= strtotime($date_start->format("Y-m-d")) && (
                $cdr_anagrafica->data_termine == null ||
                strtotime($cdr_anagrafica->data_termine) >= strtotime($date_end->format("Y-m-d"))
                )
            ) {
                return true;
            }
        }

        return false;
    }
}