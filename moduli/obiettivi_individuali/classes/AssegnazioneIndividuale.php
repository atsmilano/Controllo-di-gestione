<?php
namespace ObiettiviIndividuali;

use \DateTime;
use \Cdr;
use \CdcPersonale;
use \ResponsabileCdr;
use \AnnoBudget;
use \Personale;
use \ObiettiviObiettivo;
use \Exception;
use \PianoCdr;
use \TipoPianoCdr;

class AssegnazioneIndividuale extends \Entity
{
    protected static $tablename = "obind_assegnazione_individuale";  
    
    public static function getPersonabileVisualizzabileCdrInData(Cdr $cdr, DateTime $data_riferimento) {
        $personale_visualizzabile_obiettivi_individuali = [];        
        $anno = AnnoBudget::getByFields(["descrizione" => $data_riferimento->format("Y")]);
        $piano_cdr =  new PianoCdr($cdr->id_piano_cdr);
        $tipo_piano_cdr = new TipoPianoCdr($piano_cdr->id_tipo_piano_cdr); 
        //dipendenti assegnati attualmente al cdr
        foreach ($cdr->getPersonaleCdrAfferentiInData($data_riferimento) as $cdr_personale) {
            $personale_visualizzabile_obiettivi_individuali["personale-cdr-attivo"][] = $cdr_personale;
        }

        //responsabili dei cdr figli
        foreach ($cdr->getFigli() as $cdr_figlio) {
            //eventuale responsabile assegnato
            $responsabile_cdr_figlio = $cdr_figlio->getResponsabile($data_riferimento);                                               
            //eventuali responsabili cessati
            foreach (ResponsabileCdr::getResponsabiliCdrAnno($anno, $cdr_figlio) as $responsabile_cdr_figlio) {                
                $personale = Personale::factoryFromMatricola($responsabile_cdr_figlio->matricola_responsabile);
                $cdr_afferenza_prevalente_personale = $personale->getCdrAfferenzaPrevalenteInData($tipo_piano_cdr, $data_riferimento);                
                     
                $found = false;
                foreach ($personale_visualizzabile_obiettivi_individuali["resp-cdr-figli"] as $personale_visualizzato) {
                    if ($personale_visualizzato["personale"]->matricola == $personale->matricola) {
                        $found = true;
                        break;
                    }
                }
                if ($found == false) {
                    $assegnabile = true;
                    $perc_cdr_prevalente = $cdr_afferenza_prevalente_personale["peso_cdr"];
                    if ($cdr_figlio->codice == $cdr_afferenza_prevalente_personale["cdr"]->codice) {                    
                        $cdr_prevalente = $cdr_figlio;
                        $perc_cdr = $perc_cdr_prevalente;
                    }
                    else {                    
                        $cdr_prevalente = $cdr_afferenza_prevalente_personale["cdr"];
                        $perc_cdr = "";
                    }   
                    $personale_visualizzabile_obiettivi_individuali["resp-cdr-figli"][]  = [
                        "assegnabile" => $assegnabile,
                        "cdr_prevalente" => $cdr_prevalente,
                        "perc_cdr_prevalente" => $perc_cdr_prevalente,
                        "perc_cdr" => $perc_cdr,
                        "personale" => $personale,
                    ];
                }
            }
        }
        
        //dipendenti cessati nell'anno con ultima afferenza al cdr        
        foreach ($cdr->getPersonaleCdrTrasferitoInAnnoAllaData($data_riferimento, true) as $cdr_personale) {    
            $personale_visualizzabile_obiettivi_individuali["personale-cdr-cessato"][] = $cdr_personale;
        }                            

        //recupero delle assegnazioni
        foreach ($personale_visualizzabile_obiettivi_individuali as $tipo_personale=>$tipo_personale_visualizzabile) {             
            foreach ($tipo_personale_visualizzabile as $key=>$personale_visualizzabile) {
                if ($tipo_personale !== "resp-cdr-figli") {
                    $personale = Personale::factoryFromMatricola($personale_visualizzabile->matricola_personale);
                    //viene verificata la modificabilità da parte del cdr rispetto all'afferenza                                
                    $cdr_afferenza_prevalente_personale = $personale->getCdrAfferenzaPrevalenteInData($tipo_piano_cdr, $data_riferimento);
                    $perc_cdr_prevalente = $cdr_afferenza_prevalente_personale["peso_cdr"];

                    if ($cdr->codice == $cdr_afferenza_prevalente_personale["cdr"]->codice) {    
                        $assegnabile = true;
                        $cdr_prevalente = $cdr;
                        $perc_cdr = $perc_cdr_prevalente;
                    }
                    else {
                        $assegnabile = false;
                        $cdr_prevalente = $cdr_afferenza_prevalente_personale["cdr"];
                        $perc_cdr = $personale_visualizzabile->percentuale;
                    }
                    $personale_visualizzabile_obiettivi_individuali[$tipo_personale][$key] = [
                        "assegnabile" => $assegnabile,
                        "cdr_prevalente" => $cdr_prevalente,
                        "perc_cdr_prevalente" => $perc_cdr_prevalente,
                        "perc_cdr" => $perc_cdr,
                        "personale" => $personale,
                    ];
                }              
            }   
        }
        return $personale_visualizzabile_obiettivi_individuali;
    }

    public function getObiettivoCollegato() {     
        $obiettivo_collegato = null;  
        if ($this->id_obiettivo != null) {                                                                         
            try {
                $obiettivo_collegato = new ObiettiviObiettivo($this->id_obiettivo);                                                                                
                if ($obiettivo_collegato->data_eliminazione !== null) {                                    
                    $obiettivo_collegato = null;
                }               
            }
            catch (Exception $ex) {                              
                $obiettivo_collegato = null;              
            }
        }
        return $obiettivo_collegato;  
    }

    //matricole_escluse per escludere eventuali dipendenti dall'elenco assegnazioni
    public static function getAssegnazioniIndividualiPersonaleCdrInData (Cdr $cdr, DateTime $data_riferimento, array $matricole_escluse=[]) {
        $assegnazioni_personale = [];
        $anno = AnnoBudget::getByFields(["descrizione" => $data_riferimento->format("Y")]);
        
        $personale_visualizzabile_obiettivi_individuali = AssegnazioneIndividuale::getPersonabileVisualizzabileCdrInData($cdr, $data_riferimento);
        foreach ($personale_visualizzabile_obiettivi_individuali as  $tipo_personale=>$cdr_personale_tipo){            
            foreach ($cdr_personale_tipo as $personale_visualizzabile){     
                if (!in_array($personale_visualizzabile["personale"]->matricola, $matricole_escluse)) {                                    
                    $filters = array(
                                    "ID_anno_budget" => $anno->id,
                                    "matricola_personale" => $personale_visualizzabile["personale"]->matricola,
                                );
                    $assegnazioni = array();
                    $assegnazioni_individuali = AssegnazioneIndividuale::getAll ($filters);
                    foreach ($assegnazioni_individuali as $assegnazione_individuale) {                        
                        $assegnazioni[] = [
                            "assegnazione_individuale" => $assegnazione_individuale,
                            "obiettivo_collegato" => $assegnazione_individuale->getObiettivoCollegato(),
                        ];
                    }                   
                    $assegnazioni_personale[$tipo_personale][] = [
                        "personale_visualizzato" => $personale_visualizzabile,
                        "assegnazioni" => $assegnazioni,
                    ];
                }
            }
        }
        return $assegnazioni_personale;
    }

    public static function getAssegnazioniIndividualiPersonaleCdrInDataByMatricola(Cdr $cdr, DateTime $data_riferimento, $matricola, $tipo){ 
        $assegnazioni_individuali_personale = [];
        $assegnazioni_individuali_personale_cdr = AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInData ($cdr, $data_riferimento);
        foreach ($assegnazioni_individuali_personale_cdr as $tipo_personale=>$dipendenti_assegnazioni){                                                                        
            foreach ($dipendenti_assegnazioni as $assegnazione_individuali_personale) {                                                                     
                if ($assegnazione_individuali_personale["personale_visualizzato"]["personale"]->matricola == $matricola &&
                    $tipo_personale == $tipo) {                                        
                    $assegnazione_individuali_personale["tipo_personale"] = $tipo_personale;
                    $assegnazioni_individuali_personale[] = $assegnazione_individuali_personale;
                }
            }
        }
        return $assegnazioni_individuali_personale;
    }

    public static function getAssegnazioniCdr (Cdr $cdr_selezionato, DateTime $data_riferimento, array $matricole_escluse) {
        $assegnazioni_assegnabili = [];
        foreach (AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInData ($cdr_selezionato, $data_riferimento, $matricole_escluse) as $dipendenti_assegnazioni) {       
            foreach ($dipendenti_assegnazioni as $assegnazioni_individuali_personale) { 
                if ($assegnazioni_individuali_personale["personale_visualizzato"]["assegnabile"]) {
                    foreach ($assegnazioni_individuali_personale["assegnazioni"] as $assegnazione) {           
                        if ($assegnazione["assegnazione_individuale"]->codice_cdr == $cdr_selezionato->codice) {
                            $assegnazioni_assegnabili[] = $assegnazione["assegnazione_individuale"];
                        }
                    }
                }                              
            }
        }
        return $assegnazioni_assegnabili;
    }

    public static function nAssegnazioniAssegnabiliCdr (Cdr $cdr_selezionato, DateTime $data_riferimento, array $matricole_escluse) {
        $n_assegnazioni_assegnabili = 0;        
        foreach (AssegnazioneIndividuale::getAssegnazioniIndividualiPersonaleCdrInData ($cdr_selezionato, $data_riferimento, $matricole_escluse) as $dipendenti_assegnazioni) {       
            foreach ($dipendenti_assegnazioni as $assegnazioni_individuali_personale) { 
                if ($assegnazioni_individuali_personale["personale_visualizzato"]["assegnabile"]) {
                    $n_assegnazioni_assegnabili += OBIETTIVI_INDIVIDUALI_N_MAX_ASSEGNABILI_CDR;
                }                              
            }
        }
        return $n_assegnazioni_assegnabili;
    }

    public static function getAssegnazioniChiudibiliCdr (Cdr $cdr_selezionato, DateTime $data_riferimento, array $matricole_escluse) {
        $assegnazioni_chiudibili = [];
        foreach (AssegnazioneIndividuale::getAssegnazioniCdr ($cdr_selezionato, $data_riferimento, $matricole_escluse) as $assegnazione) {       
            if ($assegnazione->datetime_chiusura == null) {
                $assegnazioni_chiudibili[] = $assegnazione;
            }
        }
        return $assegnazioni_chiudibili;
    }

    public static function getAssegnazioniRiapribiliCdr (Cdr $cdr_selezionato, DateTime $data_riferimento, array $matricole_escluse) {
        $assegnazioni_riapribili = [];
        foreach (AssegnazioneIndividuale::getAssegnazioniCdr ($cdr_selezionato, $data_riferimento, $matricole_escluse) as $assegnazione) {                  
            if ($assegnazione->datetime_chiusura !== null) {
                $assegnazioni_riapribili[] = $assegnazione;
            }
        }
        return $assegnazioni_riapribili;
    }    
}