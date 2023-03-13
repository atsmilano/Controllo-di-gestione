<?php
namespace scadenze;

class Scadenza extends \Entity
{
    protected static $tablename = "scadenze_scadenza";  
    private $stato = array();
    
    public function getStato() {
        //definizione stato
        if (!($this->data_evasione == null)){
            $this->stato["id"] = 5;
            $this->stato["descrizione"] = "Evasa";
        }
        else {
            $current_date_time = new \DateTime();            
            $scadenza_date_time = new \DateTime($this->data_scadenza);
            $next_sunday_date_time = new \DateTime();
            $next_sunday_date_time->modify('Next Sunday');
            
            if (date_diff($scadenza_date_time, $current_date_time)->format("%r%a") > 0) {
                $this->stato["id"] = 1;
                $this->stato["descrizione"] = "Scaduta";
            }
            //per gli stati 2 e 3 verranno inviate notifiche mail
            else if ((date_diff($next_sunday_date_time, $scadenza_date_time)->format("%r%a") <= 0)
                    && (date_diff($next_sunday_date_time, $scadenza_date_time)->format("%r%a") >= -7)){  
                $this->stato["id"] = 2;
                $this->stato["descrizione"] = "Scadenza in settimana";
            }
            else if (date_diff($current_date_time, $scadenza_date_time)->format("%r%a") <= $this->giorni_promemoria_scadenza) {
                $this->stato["id"] = 3;
                $this->stato["descrizione"] = "In scadenza";
            }
            else {
                $this->stato["id"] = 4;
                $this->stato["descrizione"] = "Aperta";
            }            
        }                            
        return $this->stato;
    }
}
