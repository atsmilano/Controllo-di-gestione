<?php
namespace scadenze;

class Personale extends \Personale
{
    public function isAmministratoreInData(\DateTime $data) {
        foreach (\CoreHelper::getObjectsInData("scadenze\Amministratore", $data, "data_riferimento_inizio", "data_riferimento_fine") as $amministratore){
            if ($amministratore->matricola == $this->matricola) {
                return true;
            }
        }
        return false;
    }
    
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
    
    public function getScadenzeCompetenzaInData(\DateTime $date) {
        $user = \LoggedUser::getInstance();
        $scadenze = array();             
        if ($user->hasPrivilege("scadenze_admin")) {
            return Scadenza::getAll();
        }
        else if ($user->hasPrivilege("scadenze_referente_cdr")) {
            foreach ($this->getCdrReferenzaAnno($date) as $cdr_referenza_anno) {
                $abilitazione_cdr = AbilitazioneCdr::getAll(array("codice_cdr"=>$cdr_referenza_anno->codice));
                foreach($abilitazione_cdr as $abilitazione) {
                    $scadenze = array_merge($scadenze, Scadenza::getAll(array("ID_abilitazione_cdr"=>$abilitazione->id)));
                }                                                                                
            }
        }
        return $scadenze;
    }
    
    public function isScadenzaCompetenzaInData(Scadenza $scadenza, \DateTime $date) {
        foreach ($this->getScadenzeCompetenzaInData($date) as $scadenza_competenza) {
            if ($scadenza_competenza->id == $scadenza->id) {
                return true;
            }
        }
        return false;
    }
}
