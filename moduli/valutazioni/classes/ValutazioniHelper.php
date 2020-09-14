<?php
class ValutazioniHelper {
    const ERROR_ACTION_PESO = "Il peso non può essere %s in quanto esistono schede di valutazione associate alla tipologia scheda nell'anno di budget";

    public static function getIdFromFieldKey($key) {
        $splittedKey = explode("_", $key);
        return intval($splittedKey[count($splittedKey)-1]);
    }

    public static function inizializzaCheckbox($oRecord, $key_prefix, $relations) {
        foreach($oRecord->form_fields as $key => $categoriaField) {
            if (strpos($key, $key_prefix) !== false) {
                $id = ValutazioniHelper::getIdFromFieldKey($key);
                $checked = isset($relations[$id]);
                $oRecord->form_fields[$key]->value->setValue($checked);
            }
        }
    }

    public static function canDeleteCategoriaAnno($instance) {
        $valutazioniPeriodicheCategoria =
            ValutazioniValutazionePeriodica::getSchedeCategoriaPeriodo($instance->id_categoria);

        foreach($valutazioniPeriodicheCategoria as $valutazionePeriodicaCategoria) {
            $periodo = new ValutazioniPeriodo($valutazionePeriodicaCategoria->id_periodo);
            if($periodo->id_anno_budget == $instance->id_anno_budget) {
                return false;
            }
        }
        return true;
    }

    public static function canDeleteCategoriaPeriodo($periodo_categoria) {
        $valutazioniPeriodicheCategoriaPeriodo =
            ValutazioniValutazionePeriodica::getSchedeCategoriaPeriodo($periodo_categoria->id_categoria, $periodo_categoria->id_periodo);
        return count($valutazioniPeriodicheCategoriaPeriodo) == 0;
    }

    public static function disabilitaCheckbox($canDelete, $oRecord, $key) {
        if(!$canDelete) {
            $oRecord->form_fields[$key]->properties["disabled"] = "disabled";
        }
    }

    public static function glueDescrizioni($classInstances, $separator, $attribute = "descrizione") {
        $descrizioni = array();
        foreach($classInstances as $classInstance) {
            $descrizioni[] = $classInstance->$attribute;
        }

        return implode($separator, $descrizioni);
    }

    public static function glueDescrizioniAmbiti($ambiti, $separator) {
        $descrizioni = array();
        foreach($ambiti as $ambito) {
            $sezione = new ValutazioniSezione($ambito->id_sezione);
            $descrizioni[] = $sezione->codice . "." . $ambito->codice . ". " . $ambito->descrizione;
        }
        return implode($separator, $descrizioni);
    }

    public static function setSavePesoError($messaggio) {
        die(json_encode(array('esito' => 'error', 'messaggio' => $messaggio)));
    }
}