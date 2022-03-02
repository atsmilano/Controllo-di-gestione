<?php

namespace FabbisognoFormazione;

class Personale extends \Personale
{
    public function getCdrReferenzaAnno (\DateTime $date) {
        $cdr_competenza_anno = array();
        $tipo_piano = \TipoPianoCdr::getPrioritaMassima();
        $piano_cdr = \PianoCdr::getAttivoInData($tipo_piano, $date->format("Y-m-d"));
        foreach(\CoreHelper::getObjectsInData(__NAMESPACE__."\ReferenteCdr", $date, "data_introduzione", "data_termine") as $referente_cdr){
            if ($referente_cdr->matricola_personale == $this->matricola) {
                $found = false;
                foreach ($cdr_competenza_anno as $cdr_competenza) {
                    if ($referente_cdr->codice_cdr == $cdr_competenza->codice) {
                        $found = true;
                    }
                }
                if ($found == false){
                    $cdr = \Cdr::factoryFromCodice($referente_cdr->codice_cdr, $piano_cdr);
                    foreach($cdr->getGerarchia() as $cdr_gerarchia) {
                        $found = false;
                        foreach ($cdr_competenza_anno as $cdr_competenza) {
                            if ($cdr_gerarchia["cdr"]->codice_cdr == $cdr_competenza->codice) {
                                $found = true;
                            }
                        }
                        if ($found == false) {
                            $cdr_competenza_anno[] = $cdr_gerarchia["cdr"];
                        }
                    }
                }
            }
        }
        return $cdr_competenza_anno;
    }

    public function isReferenteCdrInData (\DateTime $date) {
        foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\ReferenteCdr", $date, "data_introduzione", "data_termine") as $referenti_in_data){
            if ($this->matricola == $referenti_in_data->matricola_personale){
                return true;
            }
        }
        return false;
    }

    public function getCdrResponsbileReferenzaAnno (\DateTime $date) {
        $cdr_responsabilita_referenza = array();
        $piano_cdr = \PianoCdr::getAttivoInData(\TipoPianoCdr::getPrioritaMassima(), $date->format("Y-m-d"));
        $cdr_responsabilita = $this->getCdrResponsabilitaPiano($piano_cdr, $date);
        foreach ($cdr_responsabilita as $cdr) {
            foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\ReferenteCdr", $date, "data_introduzione", "data_termine") as $referenti_in_data){
                if ($cdr["cdr"]->codice == $referenti_in_data->codice_cdr){
                    $found = false;
                    foreach ($cdr_responsabilita_referenza as $cdr_competenza) {
                        if ($cdr["cdr"]->codice == $cdr_competenza->codice) {
                            $found = true;
                        }
                    }
                    if ($found == false){
                        foreach($cdr["cdr"]->getGerarchia() as $cdr_gerarchia) {
                            $found = false;
                            foreach ($cdr_responsabilita_referenza as $cdr_competenza) {
                                if ($cdr_gerarchia["cdr"]->codice == $cdr_competenza->codice) {
                                    $found = true;
                                }
                            }
                            if ($found == false) {
                                $cdr_responsabilita_referenza[] = $cdr_gerarchia["cdr"];
                            }
                        }
                    }
                }
            }
        }
        return $cdr_responsabilita_referenza;
    }

    public function isResponsabileCdrReferenteInData (\DateTime $date) {
        $cdr_responsabilita_referenza = array();
        $piano_cdr = \PianoCdr::getAttivoInData(\TipoPianoCdr::getPrioritaMassima(), $date->format("Y-m-d"));
        $cdr_responsabilita = $this->getCdrResponsabilitaPiano($piano_cdr, $date);
        foreach ($cdr_responsabilita as $cdr) {
            foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\ReferenteCdr", $date, "data_introduzione", "data_termine") as $referenti_in_data){
                if ($cdr["cdr"]->codice == $referenti_in_data->codice_cdr){
                    return true;
                }
            }
        }
        return false;
    }

    public function isOperatoreFormazioneInData (\DateTime $date) {
        foreach (\CoreHelper::getObjectsInData(__NAMESPACE__."\OperatoreFormazione", $date, "data_introduzione", "data_termine") as $referente_in_data){
            if ($this->matricola == $referente_in_data->matricola_personale){
                return true;
            }
        }
        return false;
    }
}
